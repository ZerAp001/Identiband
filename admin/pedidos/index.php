<?php
include '../includes/auth.php';
include '../../includes/db.php';
include '../includes/header.php';

$where = [];
$filtros = []; // Este array guardará los valores para la consulta segura

// Filtrar por estado
if (!empty($_GET['estado'])) {
    $where[] = "pedidos.estado = :estado";
    $filtros['estado'] = $_GET['estado']; // PDO escapa esto automáticamente
}

// Filtrar fecha inicio
if (!empty($_GET['fecha_inicio'])) {
    $where[] = "DATE(pedidos.fecha_pedido) >= :fecha_inicio";
    $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
}

// Filtrar fecha fin
if (!empty($_GET['fecha_fin'])) {
    $where[] = "DATE(pedidos.fecha_pedido) <= :fecha_fin";
    $filtros['fecha_fin'] = $_GET['fecha_fin'];
}

// Consulta base
$sql = "
    SELECT pedidos.*, usuarios.nombre, usuarios.apellidos
    FROM pedidos
    INNER JOIN usuarios
    ON pedidos.id_usuario = usuarios.id_usuario
";

// Agregar filtros
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Ordenar
$sql .= " ORDER BY pedidos.fecha_pedido DESC";

// Ejecutar consulta usando el array de filtros
$stmt = $conexion->prepare($sql);
$stmt->execute($filtros);

// Guardado de datos en array
$pedidos_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="admin-title">Gestión de Pedidos</h1>
        <p class="admin-subtitle">Administra todos los pedidos de IDENTIBAND</p>
    </div>
</div>

<form method="GET" class="row g-3 mb-4">

    <div class="col-md-3">
        <input type="date"
               name="fecha_inicio"
               class="form-control admin-input"
               value="<?= $_GET['fecha_inicio'] ?? '' ?>">
    </div>

    <div class="col-md-3">
        <input type="date"
               name="fecha_fin"
               class="form-control admin-input"
               value="<?= $_GET['fecha_fin'] ?? '' ?>">
    </div>

    <div class="col-md-3">
        <select name="estado" class="form-select admin-input">
            <option value="">Todos los estados</option>

            <option value="Pendiente"
                <?= (($_GET['estado'] ?? '') == 'Pendiente') ? 'selected' : '' ?>>
                Pendiente
            </option>

            <option value="Enviado"
                <?= (($_GET['estado'] ?? '') == 'Enviado') ? 'selected' : '' ?>>
                Enviado
            </option>

            <option value="Entregado"
                <?= (($_GET['estado'] ?? '') == 'Entregado') ? 'selected' : '' ?>>
                Entregado
            </option>
        </select>
    </div>

    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">
            🔍 Filtrar
        </button>
    </div>

</form>

<div class="admin-card">
    <div class="table-modern">
        <table class="table align-middle admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pedidos_data as $pedido): ?>
                    <tr>
                        <td>#<?= $pedido['id_pedido'] ?></td>
                        <td><strong><?= $pedido['nombre'] ?> <?= $pedido['apellidos'] ?></strong></td>
                        <td class="fw-bold text-info">$<?= number_format($pedido['total'], 2) ?></td>
                        <td>
                            <?php
                            $estado = $pedido['estado'];
                            switch($estado){
                                case 'Pendiente': echo '<span class="badge-status badge-warning">Pendiente</span>'; break;
                                case 'Pagado': echo '<span class="badge-status badge-info">Pagado</span>'; break;
                                case 'Enviado': echo '<span class="badge-status badge-primary">Enviado</span>'; break;
                                case 'Entregado': echo '<span class="badge-status badge-success">Entregado</span>'; break;
                                case 'Cancelado': echo '<span class="badge-status badge-danger">Cancelado</span>'; break;
                                default: echo '<span class="badge-status badge-warning">'.$estado.'</span>'; break;
                            }
                            ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="detalle.php?id=<?= $pedido['id_pedido'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye-fill"></i></a>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#estadoModal<?= $pedido['id_pedido'] ?>">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach($pedidos_data as $pedido): ?>
    <div class="modal fade" id="estadoModal<?= $pedido['id_pedido'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-0 shadow-lg">
                <form action="actualizar_estado.php" method="POST">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Cambiar estado #<?= $pedido['id_pedido'] ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
                        <label class="form-label mb-2">Estado del pedido</label>
                        <select name="estado" class="form-select bg-secondary text-white border-0">
                            <option <?= $pedido['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option <?= $pedido['estado'] == 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                            <option <?= $pedido['estado'] == 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                            <option <?= $pedido['estado'] == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                            <option <?= $pedido['estado'] == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="submit" class="btn-admin">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>