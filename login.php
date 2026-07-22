<?php
session_start();
include 'includes/db.php';

// Si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

// En caso de ingreso de datos incorrectos.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Preparamos la consulta con un marcador de posición para el email
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email");
    
    // 2. Ejecutamos pasando el email de forma segura
    $stmt->execute(['email' => $email]);
    
    // 3. Obtenemos el registro del usuario
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario existe (fetch devuelve un array si hay coincidencia)
    if ($usuario) {

        // Verificamos contraseña
        if (password_verify($password, $usuario['password'])) {

            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];

            // Detectar admin
            if ($email === 'admin@identiband.com') {

                $_SESSION['admin'] = true;

                header("Location: admin/index.php");
                exit;
            }

            // Usuario normal
            header("Location: index.php");
            exit();

        } else {
            $error = "Contraseña incorrecta.";
        }

    } else {
        $error = "El correo no está registrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Identiband - Iniciar Sesión</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-identi-gradient" style="min-height: 100vh; display: flex; align-items: center;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card bg-dark text-white border-secondary shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <a href="index.php"><img src="assets/pulsera.png" width="80" alt="Logo" onerror="this.src='https://via.placeholder.com/80?text=IB'"></a>
                        <h2 class="fw-bold mt-3" style="color: var(--identi-cyan);">IDENTIBAND</h2>
                        <p class="text-white-50">Ingresa tus credenciales</p>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Contraseña</label>
                            <div class="input-group">
                                <input type="password" id="passInput" name="password" class="form-control bg-dark text-white border-secondary" required>
                                <button class="btn btn-outline-secondary border-secondary" type="button" onclick="togglePass()">
                                    <i id="eyeIcon" class="bi bi-eye text-info"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="rem">
                            <label class="form-check-label small text-white-50" for="rem">Recordarme en este equipo</label>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-identi btn-lg text-dark fw-bold">Entrar</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="small mb-0">¿No tienes cuenta? <a href="registro.php" style="color: var(--identi-cyan); text-decoration: none;">Regístrate aquí</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para mostrar/ocultar contraseña
function togglePass() {
    const passInput = document.getElementById('passInput');
    const eyeIcon = document.getElementById('eyeIcon');
    if (passInput.type === 'password') {
        passInput.type = 'text';
        eyeIcon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passInput.type = 'password';
        eyeIcon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>