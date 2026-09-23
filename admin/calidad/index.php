<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// --- ACCIÓN 1: REGISTRAR / ESCANEAR NUEVO UID NFC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar_uid') {
    $id_venta = (int)$_POST['id_venta'];
    $uid_nfc = trim(mysqli_real_escape_string($conexion, $_POST['uid_nfc']));
    $estado_calidad = mysqli_real_escape_string($conexion, $_POST['estado_calidad']);
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);

    if (!empty($uid_nfc)) {
        // Verificar si el UID ya fue registrado anteriormente
        $check_duplicate = mysqli_query($conexion, "SELECT id_registro, id_venta FROM nfc_registros WHERE uid_nfc = '$uid_nfc'");
        if (mysqli_num_rows($check_duplicate) > 0) {
            $dup = mysqli_fetch_assoc($check_duplicate);
            $mensaje = "El UID <strong>$uid_nfc</strong> ya está registrado en la orden #{$dup['id_venta']}.";
            $tipo_mensaje = "warning";
        } else {
            $sql = "INSERT INTO nfc_registros (id_venta, uid_nfc, estado_calidad, observaciones) 
                    VALUES ($id_venta, '$uid_nfc', '$estado_calidad', '$observaciones')";

            if (mysqli_query($conexion, $sql)) {
                $mensaje = "UID NFC <strong>$uid_nfc</strong> vinculado exitosamente a la Orden #$id_venta.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al registrar UID: " . mysqli_error($conexion);
                $tipo_mensaje = "danger";
            }
        }
    } else {
        $mensaje = "Por favor ingresa un código UID válido.";
        $tipo_mensaje = "warning";
    }
}

// --- ACCIÓN 2: ELIMINAR REGISTRO UID ---
if (isset($_GET['eliminar_uid'])) {
    $id_elim = (int)$_GET['eliminar_uid'];
    mysqli_query($conexion, "DELETE FROM nfc_registros WHERE id_registro = $id_elim");
    header("Location: index.php");
    exit;
}

// --- BÚSQUEDA RÁPIDA POR UID ---
$busqueda_uid = trim($_GET['buscar_uid'] ?? '');
$where_search = "";
if (!empty($busqueda_uid)) {
    $uid_clean = mysqli_real_escape_string($conexion, $busqueda_uid);
    $where_search = "WHERE n.uid_nfc LIKE '%$uid_clean%'";
}

// Consultar registros vinculados
$query_registros = mysqli_query($conexion, "
    SELECT n.*, v.id_usuario, u.nombre AS nombre_cliente, u.email
    FROM nfc_registros n
    LEFT JOIN ventas v ON n.id_venta = v.id_venta
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    $where_search
    ORDER BY n.id_registro DESC
");

// Consultar ventas recientes para el selector rápido del formulario
$query_ventas_recientes = mysqli_query($conexion, "SELECT id_venta FROM ventas ORDER BY id_venta DESC LIMIT 30");

include '../includes/header.php'; 
?>

<div class="container-fluid py-4">
    <!-- ENCABEZADO Y BÚSQUEDA -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold text-white mb-0">
            <i class="bi bi-qr-code-scan text-info me-2"></i>Calidad y Registro UID NFC
        </h2>
        
        <div class="d-flex gap-2">
            <!-- BUSCADOR DE UID PARA GARANTÍAS / AUTENTICIDAD -->
            <form method="GET" class="d-inline-flex gap-1">
                <input type="text" name="buscar_uid" value="<?= htmlspecialchars($busqueda_uid) ?>" placeholder="Buscar UID NFC..." class="form-control form-control-sm bg-dark text-light border-secondary">
                <button type="submit" class="btn btn-sm btn-outline-info"><i class="bi bi-search"></i></button>
                <?php if (!empty($busqueda_uid)): ?>
                    <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </form>

            <button class="btn btn-info fw-bold text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoUID">
                <i class="bi bi-plus-lg me-1"></i> Vincular Nuevo UID
            </button>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show fw-bold" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- TABLA DE REGISTROS UID NFC -->
    <div style="background-color: #161b22; border: 1px solid #30363d; border-radius: 8px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; color: #ffffff; font-family: sans-serif;">
            <thead>
                <tr style="background-color: #0d1117; border-bottom: 2px solid #30363d;">
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; width: 80px;">ID</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; width: 100px;">Orden #</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">UID del Chip NFC (Serial)</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Cliente Vinculado</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; width: 140px;">Calidad</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Observaciones</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: center; width: 90px;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query_registros && mysqli_num_rows($query_registros) > 0): ?>
                    <?php while ($reg = mysqli_fetch_assoc($query_registros)): ?>
                        <?php
                            $badge_class = match($reg['estado_calidad']) {
                                'Aprobado' => 'bg-success text-white',
                                'Defectuoso' => 'bg-danger text-white',
                                'Reemplazado' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                        ?>
                        <tr style="border-bottom: 1px solid #21262d;">
                            <td style="padding: 14px 16px; color: #8b949e; vertical-align: top;">
                                #<?= $reg['id_registro'] ?>
                            </td>
                            <td style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; vertical-align: top;">
                                #<?= $reg['id_venta'] ?>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span style="font-family: monospace; font-size: 1.05rem; color: #0dcaf0; background-color: #0d1117; padding: 4px 8px; border-radius: 4px; border: 1px solid #30363d; display: inline-block;">
                                    <i class="bi bi-cpu me-1"></i> <?= htmlspecialchars($reg['uid_nfc']) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top;">
                                <strong style="display: block; color: #ffffff;"><?= htmlspecialchars($reg['nombre_cliente'] ?? 'Cliente Desconocido') ?></strong>
                                <small style="color: #8b949e;"><?= htmlspecialchars($reg['email'] ?? '') ?></small>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span class="badge <?= $badge_class ?> fw-bold" style="padding: 6px 10px;">
                                    <?= htmlspecialchars($reg['estado_calidad']) ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; color: #8b949e; font-size: 0.85rem; vertical-align: top;">
                                <?= htmlspecialchars($reg['observaciones'] ?: 'Sin observaciones') ?>
                            </td>
                            <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                <a href="?eliminar_uid=<?= $reg['id_registro'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Desvincular este registro UID?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="padding: 40px; text-align: center; color: #8b949e;">
                            <i class="bi bi-qr-code-scan fs-2 d-block mb-2"></i>
                            No hay UIDs grabados<?= !empty($busqueda_uid) ? ' para la búsqueda "' . htmlspecialchars($busqueda_uid) . '"' : '' ?>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL REGISTRAR / ESCANEAR UID -->
<div class="modal fade" id="modalNuevoUID" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-info fw-bold"><i class="bi bi-cpu"></i> Vincular UID NFC a Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="registrar_uid">
                    
                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Orden / Pedido Destino</label>
                        <select name="id_venta" class="form-select bg-dark text-light border-secondary" required>
                            <option value="">Seleccionar Orden #...</option>
                            <?php if ($query_ventas_recientes): ?>
                                <?php while ($v = mysqli_fetch_assoc($query_ventas_recientes)): ?>
                                    <option value="<?= $v['id_venta'] ?>">Orden #<?= $v['id_venta'] ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">UID del Chip NFC (Escáner o Manual)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-broadcast"></i></span>
                            <input type="text" name="uid_nfc" class="form-control bg-dark text-light border-secondary font-monospace" placeholder="Ej: 04:A2:4B:82:1C:60:80" required autofocus>
                        </div>
                        <small class="text-white-50">Aproxima la pulsera al lector NFC USB o app móvil para autocompletar.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Prueba de Control de Calidad</label>
                        <select name="estado_calidad" class="form-select bg-dark text-light border-secondary" required>
                            <option value="Aprobado">Aprobado (Chip responde OK)</option>
                            <option value="Defectuoso">Defectuoso (Lectura fallida)</option>
                            <option value="Reemplazado">Reemplazado por defecto</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Observaciones / Notas de Taller</label>
                        <textarea name="observaciones" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Ej: Pulsera color negro. Prueba de lectura con smartphone exitosa."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info fw-bold text-dark">Guardar Registro NFC</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>