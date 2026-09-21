<?php
include '../includes/auth.php';
include '../../includes/db.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = strtoupper(trim(mysqli_real_escape_string($conexion, $_POST['codigo'])));
    $tipo = $_POST['tipo_descuento'];
    $valor = (float)$_POST['valor_descuento'];
    $monto_minimo = (float)($_POST['monto_minimo_compra'] ?? 0);
    $usos_maximos = !empty($_POST['usos_maximos']) ? (int)$_POST['usos_maximos'] : "NULL";
    $fecha_expiracion = $_POST['fecha_expiracion'];

    // Validar duplicados
    $check = mysqli_query($conexion, "SELECT id_cupon FROM cupones WHERE codigo = '$codigo'");
    
    if (mysqli_num_rows($check) > 0) {
        $error = "El código '$codigo' ya existe.";
    } elseif (empty($codigo) || $valor <= 0 || empty($fecha_expiracion)) {
        $error = "Por favor completa todos los campos requeridos con valores válidos.";
    } else {
        $sql = "INSERT INTO cupones 
                (codigo, tipo_descuento, valor_descuento, monto_minimo_compra, usos_maximos, fecha_expiracion, estado) 
                VALUES ('$codigo', '$tipo', $valor, $monto_minimo, $usos_maximos, '$fecha_expiracion 23:59:59', 'activo')";

        if (mysqli_query($conexion, $sql)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Error al guardar el cupón: " . mysqli_error($conexion);
        }
    }
}

include '../includes/header.php';
?>

<div class="mb-4">
    <h1 class="admin-title">Nuevo Cupón</h1>
    <p class="admin-subtitle">Crea un código promocional para tus clientes</p>
</div>

<div class="admin-card col-lg-8">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label font-bold">Código del Cupón *</label>
                <input type="text" name="codigo" class="form-control" placeholder="EJ: IDENTI2026" required style="text-transform:uppercase;">
            </div>

            <div class="col-md-6">
                <label class="form-label">Tipo de Descuento *</label>
                <select name="tipo_descuento" class="form-select" required>
                    <option value="porcentaje">Porcentaje (%)</option>
                    <option value="fijo">Monto Fijo ($)</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Valor del Descuento *</label>
                <input type="number" step="0.01" name="valor_descuento" class="form-control" placeholder="10 o 50.00" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Monto Mínimo de Compra ($)</label>
                <input type="number" step="0.01" name="monto_minimo_compra" class="form-control" value="0.00">
            </div>

            <div class="col-md-6">
                <label class="form-label">Usos Máximos (Dejar vacío para ilimitado)</label>
                <input type="number" name="usos_maximos" class="form-control" placeholder="Ej: 100">
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha de Expiración *</label>
                <input type="date" name="fecha_expiracion" class="form-control" required min="<?= date('Y-m-d') ?>">
            </div>

            <div class="col-12 mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Guardar Cupón</button>
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>