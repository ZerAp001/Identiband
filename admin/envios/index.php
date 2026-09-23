<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// --- ASIGNAR O ACTUALIZAR DATOS DE ENVÍO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_envio') {
    $id_venta = (int)$_POST['id_venta'];
    $paqueteria = mysqli_real_escape_string($conexion, $_POST['paqueteria']);
    $numero_guia = mysqli_real_escape_string($conexion, $_POST['numero_guia']);
    
    // Actualizamos datos de guía y cambiamos el estado_produccion a 'Listo para Envío' o mantenemos el despacho
    $sql = "UPDATE ventas SET 
                paqueteria = '$paqueteria', 
                numero_guia = '$numero_guia',
                fecha_envio = NOW(),
                estado_produccion = 'Listo para Envío'
            WHERE id_venta = $id_venta";

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "Datos de guía guardados correctamente para la orden #$id_venta.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al guardar guía: " . mysqli_error($conexion);
        $tipo_mensaje = "danger";
    }
}

// Filtro por paqueteria o estado
$filtro_estado = $_GET['estado'] ?? 'todos';
$where_clause = "";

if ($filtro_estado === 'con_guia') {
    $where_clause = "WHERE v.numero_guia IS NOT NULL AND v.numero_guia != ''";
} elseif ($filtro_estado === 'sin_guia') {
    $where_clause = "WHERE v.numero_guia IS NULL OR v.numero_guia = ''";
}

// Consultar ventas
$query_envios = mysqli_query($conexion, "
    SELECT v.*, u.nombre AS nombre_cliente, u.email
    FROM ventas v
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    $where_clause
    ORDER BY v.id_venta DESC
");

// Función auxiliar para obtener la URL de rastreo según la paquetería
function obtenerUrlRastreo($paqueteria, $guia) {
    $guia = trim($guia);
    return match(strtolower($paqueteria)) {
        'dhl' => "https://www.dhl.com/mx-es/home/rastreo.html?tracking-id=" . $guia,
        'fedex' => "https://www.fedex.com/fedextrack/?trknbr=" . $guia,
        'estafeta' => "https://www.estafeta.com/Rastreo-de-envio?trackingSpec=" . $guia,
        'redpack' => "https://www.redpack.com.mx/",
        'paquetexpress' => "https://www.paquetexpress.com.mx/",
        default => "#"
    };
}

include '../includes/header.php'; 
?>

<div class="container-fluid py-4">
    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white mb-0">
            <i class="bi bi-truck text-info me-2"></i>Logística y Gestión de Envíos
        </h2>
        
        <div class="btn-group" role="group">
            <a href="index.php?estado=todos" class="btn btn-sm <?= $filtro_estado === 'todos' ? 'btn-info text-dark fw-bold' : 'btn-outline-info' ?>">Todos</a>
            <a href="index.php?estado=sin_guia" class="btn btn-sm <?= $filtro_estado === 'sin_guia' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' ?>">Pendientes de Guía</a>
            <a href="index.php?estado=con_guia" class="btn btn-sm <?= $filtro_estado === 'con_guia' ? 'btn-success fw-bold' : 'btn-outline-success' ?>">Con Guía Asignada</a>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show fw-bold" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- TABLA DE LOGÍSTICA -->
    <div style="background-color: #161b22; border: 1px solid #30363d; border-radius: 8px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; color: #ffffff; font-family: sans-serif;">
            <thead>
                <tr style="background-color: #0d1117; border-bottom: 2px solid #30363d;">
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; width: 90px;">Orden #</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Cliente y Destinatario</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Dirección de Envío</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; width: 180px;">Paquetería</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; width: 220px;">Número de Guía</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: center; width: 140px;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query_envios && mysqli_num_rows($query_envios) > 0): ?>
                    <?php while ($orden = mysqli_fetch_assoc($query_envios)): ?>
                        <?php
                            $id_v = (int)$orden['id_venta'];
                            $tiene_guia = !empty($orden['numero_guia']);
                            $url_rastreo = $tiene_guia ? obtenerUrlRastreo($orden['paqueteria'], $orden['numero_guia']) : '#';
                        ?>
                        <tr style="border-bottom: 1px solid #21262d;">
                            <td style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; vertical-align: top;">
                                #<?= $orden['id_venta'] ?>
                            </td>
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top;">
                                <strong style="display: block; color: #ffffff;"><?= htmlspecialchars($orden['nombre_recibe'] ?? $orden['nombre_cliente'] ?? 'Cliente') ?></strong>
                                <span style="color: #8b949e; font-size: 0.85rem;"><?= htmlspecialchars($orden['telefono'] ?? '') ?></span><br>
                                <span style="color: #8b949e; font-size: 0.85rem;"><?= htmlspecialchars($orden['email'] ?? '') ?></span>
                            </td>
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top; font-size: 0.9rem;">
                                <?= htmlspecialchars($orden['calle_numero'] ?? 'Calle N/A') ?>, <?= htmlspecialchars($orden['colonia'] ?? '') ?><br>
                                <span style="color: #8b949e;">
                                    <?= htmlspecialchars($orden['municipio'] ?? '') ?>, <?= htmlspecialchars($orden['estado'] ?? '') ?>, C.P. <?= htmlspecialchars($orden['codigo_postal'] ?? '') ?>
                                </span>
                                <?php if (!empty($orden['referencias'])): ?>
                                    <br><small style="color: #0dcaf0;">Ref: <?= htmlspecialchars($orden['referencias']) ?></small>
                                <?php endif; ?>
                            </td>
                            <form method="POST">
                                <input type="hidden" name="accion" value="guardar_envio">
                                <input type="hidden" name="id_venta" value="<?= $orden['id_venta'] ?>">
                                
                                <td style="padding: 14px 16px; vertical-align: top;">
                                    <select name="paqueteria" class="form-select form-select-sm bg-dark text-light border-secondary" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="DHL" <?= ($orden['paqueteria'] ?? '') === 'DHL' ? 'selected' : '' ?>>DHL</option>
                                        <option value="FedEx" <?= ($orden['paqueteria'] ?? '') === 'FedEx' ? 'selected' : '' ?>>FedEx</option>
                                        <option value="Estafeta" <?= ($orden['paqueteria'] ?? '') === 'Estafeta' ? 'selected' : '' ?>>Estafeta</option>
                                        <option value="Redpack" <?= ($orden['paqueteria'] ?? '') === 'Redpack' ? 'selected' : '' ?>>Redpack</option>
                                        <option value="Paquetexpress" <?= ($orden['paqueteria'] ?? '') === 'Paquetexpress' ? 'selected' : '' ?>>Paquetexpress</option>
                                        <option value="Otro" <?= ($orden['paqueteria'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro / Local</option>
                                    </select>
                                </td>
                                <td style="padding: 14px 16px; vertical-align: top;">
                                    <input type="text" name="numero_guia" value="<?= htmlspecialchars($orden['numero_guia'] ?? '') ?>" placeholder="Ej: 1234567890" class="form-control form-control-sm bg-dark text-light border-secondary" required>
                                    <?php if ($tiene_guia && $url_rastreo !== '#'): ?>
                                        <a href="<?= $url_rastreo ?>" target="_blank" class="small text-info text-decoration-none mt-1 d-inline-block">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>Rastrear paquete
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                    <button type="submit" class="btn btn-sm btn-info text-dark fw-bold w-100">
                                        <i class="bi bi-save me-1"></i> Guardar
                                    </button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #8b949e;">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No hay registros de envío bajo el filtro seleccionado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>