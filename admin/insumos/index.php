<?php
session_start();

// 1. Conexión a la base de datos (Sube 2 niveles hasta la raíz)
include '../../includes/db.php';

// 2. Verificar sesión de usuario
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// --- ACCIÓN 1: AGREGAR NUEVO INSUMO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre_insumo']);
    $categoria = mysqli_real_escape_string($conexion, $_POST['categoria']);
    $unidad = mysqli_real_escape_string($conexion, $_POST['unidad_medida']);
    $stock_actual = (int)$_POST['stock_actual'];
    $stock_minimo = (int)$_POST['stock_minimo'];
    $costo = (float)$_POST['costo_unitario'];
    $proveedor = mysqli_real_escape_string($conexion, $_POST['proveedor']);

    $sql = "INSERT INTO insumos (nombre_insumo, categoria, unidad_medida, stock_actual, stock_minimo, costo_unitario, proveedor)
            VALUES ('$nombre', '$categoria', '$unidad', $stock_actual, $stock_minimo, $costo, '$proveedor')";

    if (mysqli_query($conexion, $sql)) {
        $mensaje = "Insumo registrado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al registrar el insumo: " . mysqli_error($conexion);
        $tipo_mensaje = "danger";
    }
}

// --- ACCIÓN 2: ACTUALIZAR STOCK RÁPIDO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_stock') {
    $id_insumo = (int)$_POST['id_insumo'];
    $nuevo_stock = (int)$_POST['nuevo_stock'];

    $sql = "UPDATE insumos SET stock_actual = $nuevo_stock WHERE id_insumo = $id_insumo";
    if (mysqli_query($conexion, $sql)) {
        $mensaje = "Stock actualizado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar stock.";
        $tipo_mensaje = "danger";
    }
}

// --- ACCIÓN 3: ELIMINAR INSUMO ---
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    mysqli_query($conexion, "DELETE FROM insumos WHERE id_insumo = $id_eliminar");
    header("Location: index.php");
    exit;
}

// Consultar todos los insumos
$query = mysqli_query($conexion, "SELECT * FROM insumos ORDER BY (stock_actual <= stock_minimo) DESC, nombre_insumo ASC");

// 3. Incluir el ENCABEZADO Y MENÚ DEL ADMIN
include '../includes/header.php'; 
?>

<!-- REGLAS DE ALTO CONTRASTE Y LEGIBILIDAD -->
<style>
    .tabla-insumos th {
        color: #0dcaf0 !important; /* Azul cian brillante */
        font-weight: 700 !important;
        font-size: 0.95rem;
        background-color: #161b22 !important;
        border-bottom: 2px solid #30363d !important;
    }
    .tabla-insumos td {
        color: #ffffff !important; /* Texto blanco puro */
        font-size: 0.95rem;
        background-color: #0d1117 !important;
    }
    .tabla-insumos .texto-claro {
        color: #e6edf3 !important; /* Blanco legible */
    }
    .input-stock {
        background-color: #161b22 !important;
        color: #0dcaf0 !important;
        font-weight: bold;
        border: 1px solid #30363d !important;
    }
    .input-stock:focus {
        background-color: #21262d !important;
        color: #ffffff !important;
        border-color: #0dcaf0 !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white"><i class="bi bi-box-seam-fill text-info"></i> Gestión de Materia Prima e Insumos (MRP)</h2>
        <button class="btn btn-info fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalNuevoInsumo">
            <i class="bi bi-plus-lg"></i> Nuevo Insumo
        </button>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show fw-bold" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- TABLA DE INSUMOS -->
    <div class="card bg-dark border-secondary shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0 tabla-insumos">
                    <thead>
                        <tr>
                            <th class="ps-3">Estatus</th>
                            <th>Insumo</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Costo Un.</th>
                            <th>Proveedor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query) > 0): ?>
                            <?php while ($insumo = mysqli_fetch_assoc($query)): ?>
                                <?php $bajo_stock = $insumo['stock_actual'] <= $insumo['stock_minimo']; ?>
                                <tr class="border-secondary">
                                    <td class="ps-3">
                                        <?php if ($bajo_stock): ?>
                                            <span class="badge bg-danger text-white fs-7 py-2 px-2"><i class="bi bi-exclamation-triangle-fill"></i> Reorden Necesario</span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white fs-7 py-2 px-2"><i class="bi bi-check-circle-fill"></i> Suficiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong class="text-white fs-6"><?= htmlspecialchars($insumo['nombre_insumo']) ?></strong></td>
                                    <td><span class="badge bg-secondary text-white px-2 py-2"><?= htmlspecialchars($insumo['categoria']) ?></span></td>
                                    <td>
                                        <form method="POST" class="d-flex align-items-center gap-1" style="max-width: 140px;">
                                            <input type="hidden" name="accion" value="actualizar_stock">
                                            <input type="hidden" name="id_insumo" value="<?= $insumo['id_insumo'] ?>">
                                            <input type="number" name="nuevo_stock" value="<?= $insumo['stock_actual'] ?>" class="form-control form-control-sm input-stock text-center" required>
                                            <button type="submit" class="btn btn-sm btn-info text-dark fw-bold" title="Guardar"><i class="bi bi-save-fill"></i></button>
                                        </form>
                                    </td>
                                    <td class="texto-claro fw-bold"><?= $insumo['stock_minimo'] ?> <?= htmlspecialchars($insumo['unidad_medida']) ?></td>
                                    <td class="text-info fw-bold">$<?= number_format($insumo['costo_unitario'], 2) ?></td>
                                    <td class="texto-claro fw-bold"><?= htmlspecialchars($insumo['proveedor'] ?? 'N/A') ?></td>
                                    <td class="text-center">
                                        <a href="?eliminar=<?= $insumo['id_insumo'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este insumo del registro?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-white">No hay insumos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR NUEVO INSUMO -->
<div class="modal fade" id="modalNuevoInsumo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-info fw-bold"><i class="bi bi-box-seam-fill"></i> Registrar Nuevo Insumo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    
                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Nombre del Insumo / Materia Prima</label>
                        <input type="text" name="nombre_insumo" class="form-control bg-dark text-light border-secondary" placeholder="Ej: Chip NFC NTAG216" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Categoría</label>
                        <select name="categoria" class="form-select bg-dark text-light border-secondary" required>
                            <option value="Chip NFC">Chip NFC</option>
                            <option value="Silicona/Goma">Silicona/Goma</option>
                            <option value="Metal/Oro/Plata">Metal/Oro/Plata</option>
                            <option value="Empaque">Empaque</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Stock Inicial</label>
                            <input type="number" name="stock_actual" class="form-control bg-dark text-light border-secondary" value="0" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Punto de Reorden (Mínimo)</label>
                            <input type="number" name="stock_minimo" class="form-control bg-dark text-light border-secondary" value="10" min="1" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Unidad de Medida</label>
                            <input type="text" name="unidad_medida" class="form-control bg-dark text-light border-secondary" value="unidades" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-white fw-bold">Costo Unitario ($)</label>
                            <input type="number" step="0.01" name="costo_unitario" class="form-control bg-dark text-light border-secondary" value="0.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white fw-bold">Proveedor (Opcional)</label>
                        <input type="text" name="proveedor" class="form-control bg-dark text-light border-secondary" placeholder="Ej: Proveedor de Silicona S.A.">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info fw-bold text-dark">Guardar Insumo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// 4. Incluir el PIE DE PÁGINA DEL ADMIN
include '../includes/footer.php'; 
?>