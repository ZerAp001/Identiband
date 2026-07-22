<?php 
include 'includes/head_general.php'; 
include 'includes/header.php'; 
include 'includes/db.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) { 
    echo "<script>window.location='login.php';</script>"; 
    exit; 
}

// Consulta para obtener pedidos de forma segura
$stmt = $conexion->prepare("
    SELECT * 
    FROM pedidos 
    WHERE id_usuario = :usuario_id 
    ORDER BY fecha_pedido DESC
");

// Ejecutamos pasando el ID del usuario
$stmt->execute(['usuario_id' => $usuario_id]);

// Obtenemos todos los pedidos en un array listo para recorrer
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5" style="min-height: 80vh;">
    <div class="row mt-5">
        <div class="col-12 border-bottom border-secondary mb-4 pb-2">
            <h2 class="fw-bold text-info"><i class="bi bi-bag-check-fill me-2"></i> Mis Pedidos</h2>
            <p class="text-white-50">Historial de tus compras inteligentes.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-dark table-hover border-secondary align-middle">
                    <thead class="table-secondary text-dark">
                        <tr>
                            <th>ID Pedido</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($pedidos)): ?>
    <?php foreach ($pedidos as $ped): ?>
        <tr>
            <td class="fw-bold">#IB-<?php echo htmlspecialchars($ped['id_pedido']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($ped['fecha_pedido'])); ?></td>
            <td class="text-info">$<?php echo number_format($ped['total'], 2); ?></td>
            <td>
                <?php if ($ped['estado'] == 'Pendiente'): ?>
                    <span class="badge bg-warning text-dark">
                        En camino
                    </span>
                <?php elseif ($ped['estado'] == 'Entregado'): ?>
                    <span class="badge bg-success">
                        Entregado
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary">
                        <?php echo htmlspecialchars($ped['estado']); ?>
                    </span>
                <?php endif; ?>
            </td>
            <td>
                <a href="detalle_pedido.php?id=<?php echo $ped['id_pedido']; ?>" class="btn btn-outline-info btn-sm"> Ver detalles </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center py-5 text-white-50">
            <i class="bi bi-cart-x display-4 d-block mb-3"></i>
            Todavía no has realizado ninguna compra.
        </td>
    </tr>
<?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>