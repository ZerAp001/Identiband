<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// --- ACCIÓN 1: AGREGAR NUEVO PROVEEDOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $empresa = mysqli_real_escape_string($conexion, $_POST['nombre_empresa']);
    $contacto = mysqli_real_escape_string($conexion, $_POST['contacto_nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $categoria = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['direccion']);
    $notas = mysqli_real_escape_string($conexion, $_POST['notas']);

    $sql = "INSERT INTO proveedores (nombre_empresa, contacto_nombre, email, telefono, categoria, direccion, notas)
            VALUES ('$empresa', '$contacto', '$email', '$telefono', '$categoria', '$direccion', '$notas')";

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "Proveedor <strong>$empresa</strong> registrado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al registrar proveedor: " . mysqli_error($conexion);
        $tipo_mensaje = "danger";
    }
}

// --- ACCIÓN 2: ELIMINAR PROVEEDOR ---
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM proveedores WHERE id_proveedor = $id_eliminar");
    header("Location: index.php");
    exit;
}

// Consultar todos los proveedores
$query_proveedores = mysqli_query($conexion, "SELECT * FROM proveedores ORDER BY nombre_empresa ASC");

include '../includes/header.php'; 
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white mb-0">
            <i class="bi bi-building text-info me-2"></i>Gestión de Proveedores (SRM)
        </h2>
        
        <button class="btn btn-info fw-bold text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoProveedor">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Proveedor
        </button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show fw-bold" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- TABLA DE PROVEEDORES -->
    <div style="background-color: #161b22; border: 1px solid #30363d; border-radius: 8px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; color: #ffffff; font-family: sans-serif;">
            <thead>
                <tr style="background-color: #0d1117; border-bottom: 2px solid #30363d;">
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Empresa / Proveedor</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Contacto</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Categoría</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Teléfono / Email</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold;">Dirección / Notas</th>
                    <th style="padding: 14px 16px; color: #0dcaf0; font-weight: bold; text-align: center; width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query_proveedores && mysqli_num_rows($query_proveedores) > 0): ?>
                    <?php while ($prov = mysqli_fetch_assoc($query_proveedores)): ?>
                        <tr style="border-bottom: 1px solid #21262d;">
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top;">
                                <strong style="display: block; color: #ffffff; font-size: 1rem;"><?= htmlspecialchars($prov['nombre_empresa']) ?></strong>
                            </td>
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top;">
                                <?= htmlspecialchars($prov['contacto_nombre'] ?? 'N/A') ?>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span class="badge bg-secondary text-white p-2">
                                    <?= htmlspecialchars($prov['categoria'] ?? 'General') ?>
                                </span>
                            </td>
                            <td style="padding: 14px 16px; color: #ffffff; vertical-align: top; font-size: 0.9rem;">
                                <?php if (!empty($prov['telefono'])): ?>
                                    <i class="bi bi-telephone text-info me-1"></i><?= htmlspecialchars($prov['telefono']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($prov['email'])): ?>
                                    <i class="bi bi-envelope text-info me-1"></i><?= htmlspecialchars($prov['email']) ?>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; color: #8b949e; font-size: 0.85rem; vertical-align: top;">
                                <?= htmlspecialchars($prov['direccion'] ?? 'Sin dirección') ?>
                                <?php if (!empty($prov['notas'])): ?>
                                    <br><small class="text-info">Nota: <?= htmlspecialchars($prov['notas']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 14px 16px; text-align: center; vertical-align: top;">
                                <a href="?eliminar=<?= $prov['id_proveedor'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este proveedor?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #8b949e;">
                            <i class="bi bi-building fs-2 d-block mb-2"></i>
                            No hay proveedores registrados en la base de datos.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL AGREGAR PROVEEDOR -->
<div class="modal fade" id="modalNuevoProveedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-info fw-bold"><i class="bi bi-building"></i> Registrar Nuevo Proveedor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Nombre de la Empresa / Razon Social</label>
                        <input type="text" name="nombre_empresa" class="form-control bg-dark text-light border-secondary" placeholder="Ej: NFC Tag Supplier Ltd." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Nombre de Contacto</label>
                            <input type="text" name="contacto_nombre" class="form-control bg-dark text-light border-secondary" placeholder="Ej: Carlos López">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Categoría Principal</label>
                            <select name="categoria" class="form-select bg-dark text-light border-secondary" required>
                                <option value="Chips NFC">Chips NFC</option>
                                <option value="Silicona y Goma">Silicona y Goma</option>
                                <option value="Grabado y Metales">Grabado y Metales</option>
                                <option value="Empaques y Cajas">Empaques y Cajas</option>
                                <option value="Logística">Logística</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control bg-dark text-light border-secondary" placeholder="ventas@proveedor.com">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Teléfono de Contacto</label>
                            <input type="text" name="telefono" class="form-control bg-dark text-light border-secondary" placeholder="+52 55 1234 5678">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Dirección Física / Ubicación</label>
                        <input type="text" name="direccion" class="form-control bg-dark text-light border-secondary" placeholder="Ciudad de México, México">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Notas o Términos de Entrega</label>
                        <textarea name="notas" class="form-control bg-dark text-light border-secondary" rows="2" placeholder="Ej: Tiempo de entrega 3-5 días hábiles. Pedido mínimo 100 uds."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info fw-bold text-dark">Guardar Proveedor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>