<?php
session_start();

// Validación del usuario

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    header("Location: login.php");
    exit();
}

include __DIR__ . '/includes/db.php';

// Validación del carrito con la BD.
// 1. Verificar si el carrito está vacío
$stmt = $conexion->prepare("SELECT COUNT(*) FROM carrito WHERE id_usuario = :u_id");
$stmt->execute(['u_id' => $usuario_id]);
$total_carrito = $stmt->fetchColumn();

if ($total_carrito == 0) {
    $_SESSION['mensaje'] = "Tu carrito está vacío. Selecciona productos para comprar.";
    header("Location: carrito.php");
    exit();
}

// 2. Consultar productos del carrito
$stmt = $conexion->prepare("
    SELECT c.*, p.nombre_modelo, p.precio 
    FROM carrito c 
    JOIN productos p ON c.id_producto = p.id_producto 
    WHERE c.id_usuario = :u_id
");
$stmt->execute(['u_id' => $usuario_id]);
$productos_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal_general = 0;
foreach ($productos_carrito as &$item) { // Usamos & para modificar el array original
    $item['subtotal'] = $item['precio'] * $item['cantidad'];
    $subtotal_general += $item['subtotal'];
}
unset($item); // Limpiamos la referencia

// 3. Costo por envío
$minimo_envio_gratis = floatval($conexion->query("SELECT envio_gratis FROM configuracion LIMIT 1")->fetchColumn());

if ($subtotal_general >= $minimo_envio_gratis) {
    $envio = 0;
    $texto_envio = "Gratis";
} else {
    $envio = 79;
    $texto_envio = "$79 MXN";
}

$total_final = $subtotal_general + $envio;
?>

<?php include __DIR__ . '/includes/head_general.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Formulario de finalización de compra. -->

<div class="container py-5" style="min-height: 80vh;">

    <div class="row mt-5">

        <div class="col-12 border-bottom border-secondary mb-4 pb-3">
            <h2 class="fw-bold text-info">
                <i class="bi bi-credit-card me-2"></i>
                Checkout de compra
            </h2>
            <p class="text-white-50">
                Completa tus datos de envío y selecciona tu método de pago.
            </p>
        </div>

    </div>

    <form action="procesar_checkout.php" method="POST">

        <div class="row">

            <div class="col-lg-8">

                <!-- Dirección -->
                <div class="card bg-dark border-secondary shadow mb-4">
                    <div class="card-body p-4">

                        <h4 class="text-info mb-4">Dirección de envío</h4>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nombre de quien recibe</label>
                                <input type="text" name="nombre_recibe"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teléfono de contacto</label>
                                <input type="text" name="telefono"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Calle y número</label>
                                <input type="text" name="calle_numero"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Colonia</label>
                                <input type="text" name="colonia"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Municipio / Alcaldía</label>
                                <input type="text" name="municipio"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <input type="text" name="estado"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Código Postal</label>
                                <input type="text" name="codigo_postal"
                                    class="form-control bg-dark text-white border-secondary" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Referencias (opcional)</label>
                                <textarea name="referencias" rows="3"
                                    class="form-control bg-dark text-white border-secondary"></textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Método de pago -->
                <div class="card bg-dark border-secondary shadow">

                    <div class="card-body p-4">

                        <h4 class="text-info mb-4">Método de pago</h4>

                        <select name="metodo_pago" id="metodo_pago"
                            class="form-select bg-dark text-white border-secondary"
                            required onchange="mostrarMetodoPago()">

                            <option value="">Selecciona método de pago</option>
                            <option value="tarjeta">Tarjeta bancaria</option>
                            <option value="paypal">PayPal</option>
                            <option value="oxxo">Pago en OXXO</option>

                        </select>

                        <div id="bloque_tarjeta" style="display:none;" class="mt-3">
                            <input type="text" name="titular" placeholder="Titular"
                                class="form-control bg-dark text-white border-secondary mb-2">
                            <input type="text" name="numero_tarjeta" placeholder="Número"
                                class="form-control bg-dark text-white border-secondary mb-2">
                            <input type="text" name="expiracion" placeholder="MM/AA"
                                class="form-control bg-dark text-white border-secondary mb-2">
                            <input type="text" name="cvv" placeholder="CVV"
                                class="form-control bg-dark text-white border-secondary mb-2">
                        </div>

                        <div id="bloque_paypal" style="display:none;" class="mt-3">
                            <input type="email" name="paypal_email"
                                class="form-control bg-dark text-white border-secondary"
                                placeholder="Correo PayPal">
                        </div>

                        <div id="bloque_oxxo" style="display:none;" class="mt-3">
                            <div class="alert alert-info">
                                Se generará referencia de pago OXXO.
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Proceso del lado derecho. -->
            <div class="col-lg-4">

                <div class="card bg-dark border-secondary shadow">
                    <div class="card-body p-4">

                        <h4 class="text-info mb-4">Resumen</h4>

                        <?php foreach ($productos_carrito as $item): ?>

                            <div class="mb-3 border-bottom border-secondary pb-2">
                                <strong><?= $item['nombre_modelo'] ?></strong>
                                <div class="small text-white-50">
                                    Cantidad: <?= $item['cantidad'] ?>
                                </div>
                                <div class="text-info">
                                    $<?= number_format($item['subtotal'], 2) ?>
                                </div>
                            </div>

                        <?php endforeach; ?>

                        <hr class="border-secondary">

                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>$<?= number_format($subtotal_general, 2) ?></span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Envío:</span>
                            <span class="text-info"><?= $texto_envio ?></span>
                        </div>

                        <hr class="border-secondary">

                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong class="text-info fs-4">
                                $<?= number_format($total_final, 2) ?>
                            </strong>
                        </div>

                        <button class="btn btn-info w-100 fw-bold">
                            FINALIZAR COMPRA
                        </button>

                        <a href="carrito.php" class="btn btn-outline-light w-100 mt-3">
                            ← Volver al carrito
                        </a>

                    </div>
                </div>

            </div>

        </div>

    </form>

</div>

<script>
function mostrarMetodoPago() {
    let m = document.getElementById("metodo_pago").value;

    document.getElementById("bloque_tarjeta").style.display = "none";
    document.getElementById("bloque_paypal").style.display = "none";
    document.getElementById("bloque_oxxo").style.display = "none";

    if (m === "tarjeta") document.getElementById("bloque_tarjeta").style.display = "block";
    if (m === "paypal") document.getElementById("bloque_paypal").style.display = "block";
    if (m === "oxxo") document.getElementById("bloque_oxxo").style.display = "block";
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>