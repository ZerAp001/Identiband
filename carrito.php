<?php 
session_start();

include 'includes/head_general.php';
include 'includes/header.php';
include 'includes/db.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    header("Location: login.php");
    exit;
}

// Consulta carrito
$query = "SELECT c.*, p.nombre_modelo, p.precio, p.imagen_url
          FROM carrito c
          JOIN productos p ON c.id_producto = p.id_producto
          WHERE c.id_usuario = $usuario_id";

$resultado = mysqli_query($conexion, $query);
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
    // Obtenemos el mínimo de envío gratis para la barra de progreso
    $config_progress_query = mysqli_query($conexion, "SELECT envio_gratis FROM configuracion LIMIT 1");
    $config_progress = mysqli_fetch_assoc($config_progress_query);
    $meta_envio = floatval($config_progress['envio_gratis']);
    
    // Si $subtotal_general ya fue calculado antes o después, aseguramos su uso. 
    // Como el resumen está abajo, calculamos el subtotal aquí de forma rápida para la barra:
    $subtotal_barra = 0;
    // Si la consulta principal ya corrió, podemos recorrer o calcular. 
    // Para evitar duplicar lógica, podemos mover la consulta del mínimo antes o calcularlo aquí:
    $query_barra = "SELECT c.cantidad, p.precio FROM carrito c JOIN productos p ON c.id_producto = p.id_producto WHERE c.id_usuario = $usuario_id";
    $res_barra = mysqli_query($conexion, $query_barra);
    while($it = mysqli_fetch_assoc($res_barra)) {
        $subtotal_barra += $it['precio'] * $it['cantidad'];
    }

    $porcentaje = ($meta_envio > 0) ? min(100, ($subtotal_barra / $meta_envio) * 100) : 100;
    $faltante = max(0, $meta_envio - $subtotal_barra);
    ?>

    <!-- BARRA DE PROGRESO DE ENVÍO GRATIS -->
    <div class="card bg-dark border-secondary p-3 mb-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold text-light">
                <?php if ($subtotal_barra >= $meta_envio): ?>
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
                 style="width: <?php echo $porcentaje; ?>%; background: linear-gradient(135deg, var(--identi-primary), var(--identi-secondary));" 
                 aria-valuenow="<?php echo $porcentaje; ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
            </div>
        </div>
    </div>
    
    <div class="row">

        <!-- Tabla de los productos. -->
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

                    <?php
                    $subtotal_general = 0;

                    if (mysqli_num_rows($resultado) > 0):

                        while ($item = mysqli_fetch_assoc($resultado)):

                            $subtotal = $item['precio'] * $item['cantidad'];
                            $subtotal_general += $subtotal;
                    ?>

                        <tr class="align-middle">

                            <td>
                                <strong><?php echo $item['nombre_modelo']; ?></strong>

                                <div class="small text-white-50 mt-2">

                                    <?php if (!empty($item['color_elegido'])): ?>
                                        Color: <?php echo $item['color_elegido']; ?><br>
                                    <?php endif; ?>

                                    <?php if (!empty($item['material_elegido'])): ?>
                                        Material: <?php echo $item['material_elegido']; ?><br>
                                    <?php endif; ?>

                                    <?php if (!empty($item['variante_paquete'])): ?>
                                        Presentación: <?php echo $item['variante_paquete']; ?>
                                    <?php endif; ?>

                                </div>
                            </td>

                            <td>
                                $<?php echo number_format($item['precio'], 2); ?>
                            </td>

                            <td>
                                <?php echo $item['cantidad']; ?>
                            </td>

                            <td class="text-info">
                                $<?php echo number_format($subtotal, 2); ?>
                            </td>

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

                    <?php
                        endwhile;
                    else:
                    ?>

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

            <div class="card bg-dark border-secondary p-4 shadow">

                <h4 class="text-info">Resumen</h4>

                <hr class="border-secondary">

                <?php
                $config_query = mysqli_query(
    $conexion,
    "SELECT envio_gratis FROM configuracion LIMIT 1"
);

$config = mysqli_fetch_assoc($config_query);

$minimo_envio_gratis = floatval($config['envio_gratis']);

if ($subtotal_general <= 0) {
    $envio = 0;
    $texto_envio = "$0 MXN";
}
elseif ($subtotal_general >= $minimo_envio_gratis) {
    $envio = 0;
    $texto_envio = "Gratis";
}
else {
    $envio = 79;
    $texto_envio = "$79 MXN";
}

                $total_final = $subtotal_general + $envio;
                ?>

                <div class="d-flex justify-content-between mb-2">
                    <span>Productos:</span>
                    <span>$<?php echo number_format($subtotal_general, 2); ?></span>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <span>Envío:</span>
                    <span class="text-info"><?php echo $texto_envio; ?></span>
                </div>

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