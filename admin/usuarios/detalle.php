<?php
include '../includes/auth.php';
include '../../includes/db.php';

$id_usuario = (int)($_GET['id'] ?? 0);

if ($id_usuario <= 0) {
    header("Location: index.php");
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

/* ACTUALIZAR PUNTOS DE FIDELIDAD */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajustar_puntos'])) {
    $nuevos_puntos = (int)$_POST['puntos_fidelidad'];
    
    if ($nuevos_puntos >= 0) {
        $updatePuntos = mysqli_query($conexion, "
            UPDATE usuarios 
            SET puntos_fidelidad = $nuevos_puntos 
            WHERE id_usuario = $id_usuario
        ");
        if ($updatePuntos) {
            $mensaje = "Puntos actualizados correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar puntos.";
            $tipo_mensaje = "danger";
        }
    }
}

/* DATOS DEL USUARIO Y MÉTRICAS ACUMULADAS */
$queryUsuario = mysqli_query($conexion, "
    SELECT 
        u.*,
        COALESCE(p.total_pedidos, 0) AS total_pedidos,
        COALESCE(v.total_gastado, 0) AS total_gastado
    FROM usuarios u
    LEFT JOIN (
        SELECT id_usuario, COUNT(id_pedido) AS total_pedidos 
        FROM pedidos 
        WHERE id_usuario = $id_usuario
        GROUP BY id_usuario
    ) p ON u.id_usuario = p.id_usuario
    LEFT JOIN (
        SELECT id_usuario, SUM(monto_total) AS total_gastado 
        FROM ventas 
        WHERE id_usuario = $id_usuario AND estado_pago = 'completado'
        GROUP BY id_usuario
    ) v ON u.id_usuario = v.id_usuario
    WHERE u.id_usuario = $id_usuario
");

$usuario = mysqli_fetch_assoc($queryUsuario);

if (!$usuario) {
    header("Location: index.php");
    exit;
}

/* HISTORIAL DE PEDIDOS */
$queryPedidos = mysqli_query($conexion, "
    SELECT * FROM pedidos 
    WHERE id_usuario = $id_usuario 
    ORDER BY fecha_pedido DESC
");

include '../includes/header.php';

// Determinar segmento
$gastado = (float)$usuario['total_gastado'];
if ($gastado >= 3000) {
    $segmentoLabel = 'Cliente VIP';
    $segmentoClass = 'badge-danger';
} elseif ($gastado > 0 && $usuario['total_pedidos'] >= 2) {
    $segmentoLabel = 'Frecuente';
    $segmentoClass = 'badge-success';
} elseif ($gastado > 0) {
    $segmentoLabel = 'Comprador';
    $segmentoClass = 'badge-info';
} else {
    $segmentoLabel = 'Prospecto';
    $segmentoClass = 'badge-warning';
}
?>

<div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-left"></i> Volver a Usuarios
        </a>
        <h1 class="admin-title">
            <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>
        </h1>
        <p class="admin-subtitle">Ficha CRM y métricas individuales</p>
    </div>
    <div>
        <span class="badge-status <?= $segmentoClass ?> fs-6 px-3 py-2">
            <?= $segmentoLabel ?>
        </span>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- TARJETAS DE MÉTRICAS -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <small class="text-muted text-uppercase fw-bold">Total Gastado (LTV)</small>
            <h3 class="text-success fw-bold mt-2">$<?= number_format($usuario['total_gastado'], 2) ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <small class="text-muted text-uppercase fw-bold">Pedidos Realizados</small>
            <h3 class="text-primary fw-bold mt-2"><?= $usuario['total_pedidos'] ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <small class="text-muted text-uppercase fw-bold">Puntos Actuales</small>
            <h3 class="text-warning fw-bold mt-2"><?= number_format($usuario['puntos_fidelidad']) ?> pts</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card text-center p-3">
            <small class="text-muted text-uppercase fw-bold">Fecha de Registro</small>
            <h3 class="text-secondary fs-5 fw-bold mt-2"><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></h3>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- INFORMACIÓN DE CONTACTO Y PUNTOS -->
    <div class="col-lg-4">
        <div class="admin-card mb-4">
            <h5 class="fw-bold mb-3">Información de Contacto</h5>
            <ul class="list-unstyled mb-0">
                <li class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></li>
                <li class="mb-2"><strong>Teléfono:</strong> <?= htmlspecialchars($usuario['telefono'] ?: 'No registrado') ?></li>
                <li class="mb-2"><strong>Estado Cuenta:</strong> <span class="badge bg-secondary text-capitalize"><?= $usuario['estado'] ?></span></li>
            </ul>
        </div>

        <!-- FORMULARIO DE AJUSTE MANUAL DE PUNTOS -->
        <div class="admin-card">
            <h5 class="fw-bold mb-3">Ajustar Puntos de Fidelidad</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Cantidad de Puntos</label>
                    <input type="number" name="puntos_fidelidad" class="form-control" value="<?= $usuario['puntos_fidelidad'] ?>" min="0" required>
                </div>
                <button type="submit" name="ajustar_puntos" class="btn btn-primary w-100">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>

    <!-- HISTORIAL DE COMPRAS -->
    <div class="col-lg-8">
        <div class="admin-card">
            <h5 class="fw-bold mb-3">Historial de Pedidos</h5>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Fecha</th>
                            <th>Monto Total</th>
                            <th>Estado Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($queryPedidos) > 0): ?>
                        <?php while($pedido = mysqli_fetch_assoc($queryPedidos)): ?>
                            <tr>
                                <td>#<?= $pedido['id_pedido'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></td>
                                <td class="fw-bold">$<?= number_format($pedido['monto_total'], 2) ?></td>
                                <td>
                                    <span class="badge-status <?= $pedido['estado_pago'] === 'completado' ? 'badge-success' : 'badge-warning' ?>">
                                        <?= ucfirst($pedido['estado_pago']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Este usuario aún no ha realizado pedidos.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>