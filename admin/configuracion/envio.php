<?php
include '../includes/auth.php';
include '../../includes/db.php';

// Usamos ->query() porque es una consulta fija sin variables externas
$res = $conexion->query("SELECT * FROM configuracion LIMIT 1");
$config = $res->fetch(PDO::FETCH_ASSOC);

// --- PARTE DE ESCRITURA (UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo = floatval($_POST['envio']);

    // Usamos ->prepare() para la seguridad
    $stmt = $conexion->prepare("UPDATE configuracion SET envio_gratis = :nuevo");
    
    // Ejecutamos pasando el valor
    $stmt->execute(['nuevo' => $nuevo]);

    header("Location: envio.php");
    exit;
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="admin-title">
            Configuración de Envíos
        </h1>

        <p class="admin-subtitle">
            Configura el monto mínimo para envío gratis
        </p>

    </div>

</div>

<div class="admin-card">

    <form method="POST">

        <div class="row">

            <div class="col-md-6">

                <label class="form-label text-light mb-3">

                    <i class="bi bi-truck"></i>
                    Monto mínimo para envío gratis

                </label>

                <div class="input-group input-modern">

                    <span class="input-group-text">
                        $
                    </span>

                    <input
                        type="number"
                        step="0.01"
                        name="envio"
                        class="form-control"
                        value="<?= $config['envio_gratis'] ?>"
                        required
                    >

                </div>

                <small class="text-secondary mt-2 d-block">
                    Si el cliente supera este monto,
                    el envío será gratuito.
                </small>

            </div>

        </div>

        <button class="btn-admin mt-4">

            <i class="bi bi-check-circle"></i>
            Guardar configuración

        </button>

    </form>

</div>

<?php include '../includes/footer.php'; ?>