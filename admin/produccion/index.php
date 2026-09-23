<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Cambiar estado operativo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    $id_venta = (int)$_POST['id_venta'];
    $nuevo_estado = mysqli_real_escape_string($conexion, $_POST['nuevo_estado']);

    $sql = "UPDATE ventas SET estado_produccion = '$nuevo_estado' WHERE id_venta = $id_venta";
    if (mysqli_query($conexion, $sql)) {
        $mensaje = "Estatus actualizado a: <strong>$nuevo_estado</strong> para la orden #$id_venta.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar: " . mysqli_error($conexion);
        $tipo_mensaje = "danger";
    }
}

// Filtro por estado
$filtro_estado = $_GET['estado'] ?? 'todos';
$where_clause = "";

if ($filtro_estado !== 'todos') {
    $estado_clean = mysqli_real_escape_string($conexion, $filtro_estado);
    if ($filtro_estado === 'Pendiente') {
        $where_clause = "WHERE v.estado_produccion = 'Pendiente' OR v.estado_produccion IS NULL OR v.estado_produccion = ''";
    } else {
        $where_clause = "WHERE v.estado_produccion = '$estado_clean'";
    }
}

// Consulta principal a la tabla ventas
$query_ordenes = mysqli_query($conexion, "
    SELECT v.*, u.nombre AS nombre_cliente, u.email
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    $where_clause
    ORDER BY v.id_venta DESC
");

include '../includes/header.php'; 
?>

<div class="container-fluid py-4">
    <!-- ENCABEZADO Y FILTROS -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white mb-0">
            <i class="bi bi-tools text-info me-2"></i>Taller de Producción y Grabado
        </h2>
        
        <div class="btn-group" role="group">
            <a href="index.php?estado=todos" class="btn btn-sm <?= $filtro_estado === 'todos' ? 'btn-info text-dark fw-bold' : 'btn-outline-info' ?>">Todos</a>
            <a href="index.php?estado=Pendiente" class="btn btn-sm <?= $filtro_estado === 'Pendiente' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' ?>">Pendientes</a>
            <a href="index.php?estado=En Grabado" class="btn btn-sm <?= $filtro_estado === 'En Grabado' ? 'btn-primary fw-bold' : 'btn-outline-primary' ?>">En Grabado</a>
            <a href="index.php?estado=Control de Calidad" class="btn btn-sm <?= $filtro_estado === 'Control de Calidad' ? 'btn-info fw-bold' : 'btn-outline-info' ?>">Calidad</a>
            <a href="index.php?estado=Empacado" class="btn btn-sm <?= $filtro_estado === 'Empacado' ? 'btn-secondary fw-bold' : 'btn-outline-secondary' ?>">Empacados</a>
            <a href="index.php?estado=Listo para Envío" class="btn btn-sm <?= $filtro_estado === 'Listo para Envío' ? 'btn-success fw-bold' : 'btn-outline-success' ?>">Listos para Envío</a>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show fw-bold" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- TABLA PRINCIPAL DE PRODUCCIÓN -->
    <div style="background-color: #161b22; border: 1px solid #30363d; border-radius: 8px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; color: #ffffff; font-family: sans-serif;">
            <thead>
                <tr style="background-color: #0d1117; border-bottom: 2px solid #30363d;">
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: left; width: 90px;">Orden #</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: left;">Cliente</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: left;">Detalle de Productos & Grabado</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: left; width: 150px;">Fecha</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: left; width: 140px;">Estado Actual</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: center; width: 220px;">Cambiar Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query_ordenes && mysqli_num_rows($query_ordenes) > 0): ?>
                    <?php while ($orden = mysqli_fetch_assoc($query_ordenes)): ?>
                        <?php
                            $id_v = (int)$orden['id_venta'];
                            $estado_actual = $orden['estado_produccion'] ?: 'Pendiente';
                            
                            $raw_fecha = $orden['fecha_venta'] ?? $orden['fecha'] ?? null;
                            $fecha_formateada = $raw_fecha ? date('d/m/Y H:i', strtotime($raw_fecha)) : 'N/A';

                            // Subconsulta con el nombre corregido 'detalle_ventas'
                            $query_items = mysqli_query($conexion, "
                                SELECT dv.*, p.nombre_modelo
                                FROM detalle_ventas dv
                                LEFT JOIN productos p ON dv.id_producto = p.id_producto
                                WHERE dv.id_venta = $id_v
                            ");
                        ?>
                        <tr style="border-bottom: 1px solid #21262d;">
                            <td style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; vertical-align: top;">
                                #<?= $orden['id_venta'] ?>
                            </td>
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top;">
                                <strong style="display: block; color: #ffffff;"><?= htmlspecialchars($orden['nombre_cliente'] ?? 'Cliente #' . $orden['id_usuario']) ?></strong>
                                <span style="color: #8b949e; font-size: 0.85rem;"><?= htmlspecialchars($orden['email'] ?? '') ?></span>
                            </td>
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top;">
                                <?php if ($query_items && mysqli_num_rows($query_items) > 0): ?>
                                    <?php while ($item = mysqli_fetch_assoc($query_items)): ?>
                                        <div style="background-color: #21262d; border: 1px solid #30363d; padding: 8px 10px; border-radius: 4px; margin-bottom: 6px;">
                                            <strong style="color: #ffffff; font-size: 0.9rem;">
                                                <?= $item['cantidad'] ?>x <?= htmlspecialchars($item['nombre_modelo'] ?? 'Producto') ?>
                                            </strong>
                                            <?php if (!empty($item['texto_personalizado'])): ?>
                                                <div style="margin-top: 4px; padding: 4px 6px; background-color: #161b22; border: 1px dashed #0dcaf0; color: #0dcaf0; font-family: monospace; font-size: 0.8rem; border-radius: 3px;">
                                                    <i class="bi bi-pencil-fill"></i> "<?= htmlspecialchars($item['texto_personalizado']) ?>"
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <span style="color: #8b949e; font-size: 0.85rem;">Sin detalle de productos</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; color: #8b949e; font-size: 0.85rem; vertical-align: top;">
                                <?= $fecha_formateada ?>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span class="badge bg-warning text-dark fw-bold" style="padding: 6px 10px; font-size: 0.8rem;">
                                    <?= htmlspecialchars($estado_actual) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                <form method="POST" style="display: flex; gap: 6px; justify-content: center;">
                                    <input type="hidden" name="accion" value="cambiar_estado">
                                    <input type="hidden" name="id_venta" value="<?= $orden['id_venta'] ?>">
                                    <select name="nuevo_estado" class="form-select form-select-sm bg-dark text-light border-secondary" style="width: auto;">
                                        <option value="Pendiente" <?= $estado_actual === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="En Grabado" <?= $estado_actual === 'En Grabado' ? 'selected' : '' ?>>En Grabado</option>
                                        <option value="Control de Calidad" <?= $estado_actual === 'Control de Calidad' ? 'selected' : '' ?>>Control de Calidad</option>
                                        <option value="Empacado" <?= $estado_actual === 'Empacado' ? 'selected' : '' ?>>Empacado</option>
                                        <option value="Listo para Envío" <?= $estado_actual === 'Listo para Envío' ? 'selected' : '' ?>>Listo para Envío</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-info text-dark fw-bold">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #8b949e;">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No hay compras registradas bajo el filtro seleccionado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>