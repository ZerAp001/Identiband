<?php
include 'includes/head_general.php';
include 'includes/header.php';
include 'includes/db.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: pedidos.php");
    exit;
}

$id_pedido = intval($_GET['id']);

// Obtiene información del pedido
// 1. Obtener pedido (seguro para el usuario)
$stmt = $conexion->prepare("SELECT * FROM pedidos WHERE id_pedido = :id_pedido AND id_usuario = :id_usuario");
$stmt->execute(['id_pedido' => $id_pedido, 'id_usuario' => $usuario_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    header("Location: pedidos.php");
    exit;
}

// 2. Obtener venta relacionada
$stmt_venta = $conexion->prepare("
    SELECT v.* 
    FROM ventas v 
    JOIN pedidos p ON v.id_venta = p.id_venta 
    WHERE p.id_pedido = :id_pedido AND p.id_usuario = :id_usuario
");
$stmt_venta->execute(['id_pedido' => $id_pedido, 'id_usuario' => $usuario_id]);
$venta = $stmt_venta->fetch(PDO::FETCH_ASSOC);

// 3. Obtener productos de esa venta
$detalle_productos = [];
if ($venta) {
    $stmt_detalle = $conexion->prepare("
        SELECT dv.*, p.nombre_modelo 
        FROM detalle_ventas dv 
        JOIN productos p ON dv.id_producto = p.id_producto 
        WHERE dv.id_venta = :id_venta
    ");
    $stmt_detalle->execute(['id_venta' => $venta['id_venta']]);
    $detalle_productos = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container py-5" style="min-height:80vh;">

    <div class="row mt-5">

        <div class="col-12 border-bottom border-secondary mb-4 pb-3">
            <h2 class="fw-bold text-info">
                <i class="bi bi-receipt-cutoff me-2"></i>
                Detalle del Pedido
            </h2>

            <p class="text-white-50">
                Resumen completo de tu compra.
            </p>
        </div>

    </div>

    <div class="row">

        <!-- IZQUIERDA -->
        <div class="col-lg-8">

            <div class="card bg-dark border-secondary shadow mb-4">
                <div class="card-body p-4">

                    <h4 class="text-info mb-4">
                        Información del pedido
                    </h4>

                    <div class="mb-3">
                        <strong>ID Pedido:</strong>
                        #IB-<?php echo $pedido['id_pedido']; ?>
                    </div>

                    <div class="mb-3">
                        <strong>Fecha:</strong>
                        <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                    </div>

                    <div class="mb-3">
                        <strong>Estado:</strong>

                        <?php if ($pedido['estado'] == 'Pendiente'): ?>
                            <span class="badge bg-warning text-dark">
                                En camino
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success">
                                <?php echo $pedido['estado']; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <strong>Total pagado:</strong>
                        <span class="text-info fw-bold">
                            $<?php echo number_format($pedido['total'], 2); ?>
                        </span>
                    </div>

                </div>
            </div>

            <div class="card bg-dark border-secondary shadow">
                <div class="card-body p-4">

                    <h4 class="text-info mb-4">
                        Productos comprados
                    </h4>

                    <?php if (!empty($detalle_productos)): ?>
    
    <?php foreach ($detalle_productos as $prod): ?>
        <div class="mb-3 border-bottom border-secondary pb-3">
            <strong>
                <?php echo htmlspecialchars($prod['nombre_modelo']); ?>
            </strong>

            <div class="small text-white-50">
                Cantidad:
                <?php echo $prod['cantidad']; ?>
            </div>

            <div class="text-info">
                Precio unitario:
                $<?php echo number_format($prod['precio_unitario_momento'], 2); ?>
            </div>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

                    <?php else: ?>

                        <p class="text-white-50">
                            No se encontraron productos asociados.
                        </p>

                    <?php endif; ?>

                </div>
            </div>

        </div>

        <!-- DERECHA -->
        <div class="col-lg-4">

            <div class="card bg-dark border-secondary shadow">
                <div class="card-body p-4">

                    <h4 class="text-info mb-4">
                        Información de pago
                    </h4>

                    <?php if ($venta): ?>

                        <div class="mb-3">
                            <strong>Método de pago:</strong><br>
                            <?php echo ucfirst($venta['metodo_pago']); ?>
                        </div>

                        <div class="mb-3">
                            <strong>Estado del pago:</strong><br>

                            <?php if ($venta['estado_pago'] == 'completado'): ?>
                                <span class="badge bg-success">
                                    Pago aprobado
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    <?php echo ucfirst($venta['estado_pago']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($venta['referencia_oxxo'])): ?>

                            <div class="alert alert-info">
                                <strong>Referencia OXXO:</strong><br>
                                <?php echo $venta['referencia_oxxo']; ?>
                            </div>

                        <?php endif; ?>

                    <?php else: ?>

                        <p class="text-white-50">
                            No se encontró información de pago.
                        </p>

                    <?php endif; ?>

                    <a
                        href="pedidos.php"
                        class="btn btn-outline-light w-100 mt-3"
                    >
                        ← Volver a mis pedidos
                    </a>

                </div>
            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>