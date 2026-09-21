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

// Obtener productos del carrito
$query_carrito = mysqli_query($conexion, "
    SELECT c.*, p.nombre_modelo, p.precio, p.imagen_url
    FROM carrito c
    JOIN productos p ON c.id_producto = p.id_producto
    WHERE c.id_usuario = $usuario_id
");

if (!$query_carrito || mysqli_num_rows($query_carrito) == 0) {
    header("Location: carrito.php");
    exit;
}

// Obtener límite para envío gratis de la base de datos
$config_query = mysqli_query($conexion, "SELECT envio_gratis FROM configuracion LIMIT 1");
$config = mysqli_fetch_assoc($config_query);
$minimo_envio_gratis = floatval($config['envio_gratis'] ?? 1000);

// Cálculos del subtotal de productos
$subtotal_general = 0;
$productos_checkout = [];

while ($item = mysqli_fetch_assoc($query_carrito)) {
    $subtotal_general += ($item['precio'] * $item['cantidad']);
    $productos_checkout[] = $item;
}

// Determinar costo de envío
if ($subtotal_general >= $minimo_envio_gratis || $subtotal_general <= 0) {
    $costo_envio = 0;
    $texto_envio = "Gratis";
} else {
    $costo_envio = 79;
    $texto_envio = "$79 MXN";
}

// --- RECUPERAR EL DESCUENTO DE LA SESIÓN Y CALCULAR TOTAL REAL ---
$descuento = $_SESSION['descuento_aplicado'] ?? ($_SESSION['cupon_aplicado']['descuento'] ?? 0);
$total_final = max(0, ($subtotal_general + $costo_envio) - $descuento);
?>

<div class="container py-5" style="min-height: 80vh;">
    <h2 class="fw-bold text-info mt-4"><i class="bi bi-credit-card"></i> Checkout de compra</h2>
    <p class="text-white-50 mb-4">Completa tus datos de envío y selecciona tu método de pago.</p>

    <form action="procesar_checkout.php" method="POST">
        <div class="row">
            
            <!-- FORMULARIO DE ENVÍO Y PAGO -->
            <div class="col-lg-8 mb-4">
                <div class="card bg-dark border-secondary p-4 shadow mb-4">
                    <h4 class="text-info mb-3">Dirección de envío</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light">Nombre de quien recibe</label>
                            <input type="text" name="nombre_recibe" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Teléfono de contacto</label>
                            <input type="tel" name="telefono" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-light">Calle y número</label>
                            <input type="text" name="calle_numero" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Colonia</label>
                            <input type="text" name="colonia" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Municipio / Alcaldía</label>
                            <input type="text" name="municipio" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Estado</label>
                            <input type="text" name="estado" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light">Código Postal</label>
                            <input type="text" name="codigo_postal" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-light">Referencias (opcional)</label>
                            <textarea name="referencias" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card bg-dark border-secondary p-4 shadow">
                    <h4 class="text-info mb-3">Método de pago</h4>
                    <select name="metodo_pago" class="form-select bg-dark text-light border-secondary" required>
                        <option value="">Selecciona método de pago</option>
                        <option value="tarjeta">Tarjeta de Crédito / Débito</option>
                        <option value="paypal">PayPal</option>
                        <option value="oxxo">Pago en OXXO</option>
                    </select>
                </div>
            </div>

            <!-- RESUMEN DE COMPRA -->
            <div class="col-lg-4">
                <div class="card bg-dark border-secondary p-4 shadow text-light">
                    <h4 class="text-info mb-3">Resumen</h4>
                    <hr class="border-secondary">

                    <!-- LISTA DE PRODUCTOS -->
                    <div class="mb-3">
                        <?php foreach ($productos_checkout as $prod): ?>
                            <div class="mb-2">
                                <strong class="d-block"><?= htmlspecialchars($prod['nombre_modelo']) ?></strong>
                                <small class="text-white-50">Cantidad: <?= $prod['cantidad'] ?></small><br>
                                <small class="text-info">$<?= number_format($prod['precio'] * $prod['cantidad'], 2) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="border-secondary">

                    <!-- DESGLOSE DE PRECIOS -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($subtotal_general, 2) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Envío:</span>
                        <span class="text-info"><?= $texto_envio ?></span>
                    </div>

                    <!-- RENGLÓN DE DESCUENTO SI EXISTE UN CUPÓN -->
                    <?php if ($descuento > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Descuento (Cupón):</span>
                            <span>-$<?= number_format($descuento, 2) ?></span>
                        </div>
                    <?php endif; ?>

                    <hr class="border-secondary">

                    <div class="d-flex justify-content-between mb-4">
                        <span class="fs-5 fw-bold">Total:</span>
                        <span class="fs-4 fw-bold text-info">$<?= number_format($total_final, 2) ?></span>
                    </div>

                    <button type="submit" class="btn btn-info w-100 fw-bold py-2 mb-3">
                        FINALIZAR COMPRA
                    </button>

                    <a href="carrito.php" class="btn btn-outline-secondary w-100 btn-sm text-light border-secondary">
                        &larr; Volver al carrito
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>