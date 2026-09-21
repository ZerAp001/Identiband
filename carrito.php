<?php 
session_start();
include 'includes/db.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    header("Location: login.php");
    exit;
}

/* 1. ACCIÓN: REMOVER CUPÓN (Procesar antes de renderizar HTML) */
if (isset($_GET['remover_cupon'])) {
    unset($_SESSION['cupon_codigo']);
    unset($_SESSION['descuento_aplicado']);
    unset($_SESSION['cupon_aplicado']);
    header("Location: carrito.php");
    exit;
}

// 2. Calcular subtotal real de productos
$subtotal_general = 0;
$query_subtotal = mysqli_query($conexion, "
    SELECT SUM(c.cantidad * p.precio) AS subtotal 
    FROM carrito c 
    JOIN productos p ON c.id_producto = p.id_producto 
    WHERE c.id_usuario = $usuario_id
");
if ($row_sub = mysqli_fetch_assoc($query_subtotal)) {
    $subtotal_general = (float)($row_sub['subtotal'] ?? 0);
}

// Inicializar mensaje de cupón
$mensaje_cupon = '';
$tipo_mensaje_cupon = '';

/* 3. ACCIÓN: APLICAR CUPÓN (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_cupon']) && $_POST['accion_cupon'] === 'aplicar') {
    $codigo_ingresado = strtoupper(trim(mysqli_real_escape_string($conexion, $_POST['codigo_cupon'])));

    if (!empty($codigo_ingresado)) {
        $fecha_actual = date('Y-m-d H:i:s');
        
        $queryCupon = mysqli_query($conexion, "
            SELECT * FROM cupones 
            WHERE codigo = '$codigo_ingresado' 
              AND estado = 'activo' 
              AND fecha_expiracion >= '$fecha_actual'
        ");

        if ($cupon = mysqli_fetch_assoc($queryCupon)) {
            // Validar límite de usos
            if (!is_null($cupon['usos_maximos']) && $cupon['usos_actuales'] >= $cupon['usos_maximos']) {
                $mensaje_cupon = "Este cupón ha alcanzado su límite máximo de usos.";
                $tipo_mensaje_cupon = "danger";
            }
            // Validar monto mínimo de compra
            elseif ($subtotal_general < $cupon['monto_minimo_compra']) {
                $mensaje_cupon = "El monto mínimo de compra para este cupón es de $" . number_format($cupon['monto_minimo_compra'], 2);
                $tipo_mensaje_cupon = "warning";
            } 
            else {
                // Calcular descuento
                if ($cupon['tipo_descuento'] === 'porcentaje') {
                    $monto_descuento = ($subtotal_general * $cupon['valor_descuento']) / 100;
                } else {
                    $monto_descuento = (float)$cupon['valor_descuento'];
                }

                // Guardar en variables de sesión sincronizadas
                $_SESSION['cupon_codigo'] = $cupon['codigo'];
                $_SESSION['descuento_aplicado'] = $monto_descuento;
                $_SESSION['cupon_aplicado'] = [
                    'id_cupon' => $cupon['id_cupon'],
                    'codigo' => $cupon['codigo'],
                    'descuento' => $monto_descuento,
                    'tipo' => $cupon['tipo_descuento']
                ];

                $mensaje_cupon = "¡Cupón aplicado correctamente!";
                $tipo_mensaje_cupon = "success";
            }
        } else {
            $mensaje_cupon = "El código ingresado no existe o ha expirado.";
            $tipo_mensaje_cupon = "danger";
        }
    }
}

// Obtener valor de descuento actual
$descuento_aplicado = $_SESSION['descuento_aplicado'] ?? 0;

// Consulta de productos en el carrito
$query = "SELECT c.*, p.nombre_modelo, p.precio, p.imagen_url
          FROM carrito c
          JOIN productos p ON c.id_producto = p.id_producto
          WHERE c.id_usuario = $usuario_id";

$resultado = mysqli_query($conexion, $query);

// --- AHORA SÍ INCLUIMOS EL HTML Y ENCABEZADOS ---
include 'includes/head_general.php';
include 'includes/header.php';
?>

<!-- MENSAJE DE SISTEMA -->
<?php if (isset($_SESSION['mensaje'])): ?>
    <div class="container mt-3">
        <div class="alert alert-warning text-center">
            <?php echo $_SESSION['mensaje']; ?>
        </div>
    </div>
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>

<!-- Vista de productos del carrito -->
<div class="container py-5" style="min-height: 80vh;">

    <h2 class="fw-bold mb-4 mt-5 text-info">
        <i class="bi bi-cart3"></i> Tu Carrito
    </h2>

    <?php
    $config_progress_query = mysqli_query($conexion, "SELECT envio_gratis FROM configuracion LIMIT 1");
    $config_progress = mysqli_fetch_assoc($config_progress_query);
    $meta_envio = floatval($config_progress['envio_gratis'] ?? 1000);

    $porcentaje = ($meta_envio > 0) ? min(100, ($subtotal_general / $meta_envio) * 100) : 100;
    $faltante = max(0, $meta_envio - $subtotal_general);
    ?>

    <!-- BARRA DE PROGRESO DE ENVÍO GRATIS -->
    <div class="card bg-dark border-secondary p-3 mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold text-light">
                <?php if ($subtotal_general >= $meta_envio): ?>
                    <i class="bi bi-check-circle-fill text-success"></i> ¡Felicidades! Tienes <span class="text-success">envío gratis</span>.
                <?php else: ?>
                    <i class="bi bi-truck text-info"></i> Agrega <span class="text-info fw-bold">$<?php echo number_format($faltante, 2); ?> MXN</span> más para obtener <span class="text-info">envío gratis</span>.
                <?php endif; ?>
            </span>
            <span class="small text-white-50"><?php echo round($porcentaje); ?>%</span>
        </div>
        <div class="progress" style="height: 10px; background-color: #21262d;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" 
                 style="width: <?php echo $porcentaje; ?>%; background: linear-gradient(135deg, var(--identi-primary, #0d6efd), var(--identi-secondary, #0dcaf0));" 
                 aria-valuenow="<?php echo $porcentaje; ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
    </div>
    
    <div class="row">

        <!-- Tabla de los productos -->
        <div class="col-lg-8">
            <div class="table-responsive">
                <table class="table table-dark table-hover border-secondary">
                    <thead>
                        <tr class="text-info">
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cant.</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($resultado) > 0): ?>
                        <?php while ($item = mysqli_fetch_assoc($resultado)): ?>
                            <?php $subtotal_item = $item['precio'] * $item['cantidad']; ?>
                            <tr class="align-middle">
                                <td>
                                    <strong><?php echo htmlspecialchars($item['nombre_modelo']); ?></strong>
                                    <div class="small text-white-50 mt-2">
                                        <?php if (!empty($item['color_elegido'])): ?>
                                            Color: <?php echo htmlspecialchars($item['color_elegido']); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($item['material_elegido'])): ?>
                                            Material: <?php echo htmlspecialchars($item['material_elegido']); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($item['variante_paquete'])): ?>
                                            Presentación: <?php echo htmlspecialchars($item['variante_paquete']); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>$<?php echo number_format($item['precio'], 2); ?></td>

                                <td><?php echo $item['cantidad']; ?></td>

                                <td class="text-info">$<?php echo number_format($subtotal_item, 2); ?></td>

                                <td>
                                    <a 
                                        href="eliminar_carrito.php?id=<?php echo $item['id_carrito']; ?>" 
                                        class="text-danger"
                                        onclick="return confirm('¿Eliminar este producto del carrito?')"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-white-50">
                                Tu carrito está vacío.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección de resumen -->
        <div class="col-lg-4">
            <div class="card bg-dark border-secondary p-4 shadow text-light">

                <h4 class="text-info">Resumen</h4>
                <hr class="border-secondary">

                <!-- TARJETA DE CUPÓN -->
                <div class="card bg-dark border-secondary p-3 shadow-sm mb-3">
                    <h5 class="fw-bold fs-6 mb-3 text-light">¿Tienes un cupón de descuento?</h5>

                    <?php if ($mensaje_cupon): ?>
                        <div class="alert alert-<?= $tipo_mensaje_cupon ?> py-2 px-3 fs-7 mb-3">
                            <?= htmlspecialchars($mensaje_cupon) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['cupon_codigo'])): ?>
                        <!-- CUPÓN APLICADO -->
                        <div class="d-flex justify-content-between align-items-center bg-body-tertiary p-2 rounded border border-secondary">
                            <div>
                                <small class="text-white-50 d-block">Cupón activo:</small>
                                <strong class="text-success"><?= htmlspecialchars($_SESSION['cupon_codigo']) ?></strong>
                                <span class="badge bg-success ms-1">-$<?= number_format($descuento_aplicado, 2) ?></span>
                            </div>
                            <a href="carrito.php?remover_cupon=1" class="btn btn-sm btn-outline-danger" title="Quitar cupón">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- FORMULARIO INGRESO -->
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="accion_cupon" value="aplicar">
                            <input 
                                type="text" 
                                name="codigo_cupon" 
                                class="form-control bg-dark text-light border-secondary text-uppercase" 
                                placeholder="Ej: IDENTI2026" 
                                required
                            >
                            <button type="submit" class="btn btn-info">Aplicar</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php
                $config_query = mysqli_query($conexion, "SELECT envio_gratis FROM configuracion LIMIT 1");
                $config = mysqli_fetch_assoc($config_query);
                $minimo_envio_gratis = floatval($config['envio_gratis'] ?? 1000);

                if ($subtotal_general <= 0) {
                    $envio = 0;
                    $texto_envio = "$0 MXN";
                } elseif ($subtotal_general >= $minimo_envio_gratis) {
                    $envio = 0;
                    $texto_envio = "Gratis";
                } else {
                    $envio = 79;
                    $texto_envio = "$79 MXN";
                }

                $total_final = max(0, ($subtotal_general + $envio) - $descuento_aplicado);
                ?>

                <div class="d-flex justify-content-between mb-2">
                    <span>Productos:</span>
                    <span>$<?php echo number_format($subtotal_general, 2); ?></span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Envío:</span>
                    <span class="text-info"><?php echo $texto_envio; ?></span>
                </div>

                <!-- RENGLÓN DE DESCUENTO SI EXISTE UN CUPÓN -->
                <?php if ($descuento_aplicado > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Descuento (Cupón):</span>
                        <span>-$<?php echo number_format($descuento_aplicado, 2); ?></span>
                    </div>
                <?php endif; ?>

                <hr class="border-secondary">

                <div class="d-flex justify-content-between mb-4">
                    <span>Total final:</span>
                    <span class="fs-4 fw-bold text-info">
                        $<?php echo number_format($total_final, 2); ?>
                    </span>
                </div>

                <a href="checkout.php" class="btn btn-info w-100 fw-bold">
                    PROCEDER AL PAGO
                </a>

            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>