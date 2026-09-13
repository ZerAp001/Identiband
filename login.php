<?php
session_start();
include 'includes/db.php';

// Si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$mensaje_exito = "";

// Detectar si regresa de restablecer la contraseña exitosamente
if (isset($_GET['reset']) && $_GET['reset'] === 'exito') {
    $mensaje_exito = "¡Contraseña actualizada con éxito! Ya puedes iniciar sesión.";
}

// En caso de ingreso de datos incorrectos.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM usuarios WHERE email = '$email'";
    $resultado = mysqli_query($conexion, $query);

    if (mysqli_num_rows($resultado) > 0) {

        $usuario = mysqli_fetch_assoc($resultado);

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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card bg-dark text-white border-secondary shadow-lg">
                <div class="card-body p-4 p-md-5">
                    
                    <!-- ENCABEZADO Y LOGO -->
                    <div class="text-center mb-4">
                        <a href="index.php">
                            <img src="assets/pulsera.png" width="80" alt="Logo" onerror="this.src='https://via.placeholder.com/80?text=IB'">
                        </a>
                        <h2 class="fw-bold mt-3" style="color: var(--identi-cyan);">IDENTIBAND</h2>
                        <p class="text-white-50 small mb-0">Ingresa tus credenciales</p>
                    </div>

                    <!-- ALERTAS DE ERROR O ÉXITO -->
                    <?php if($error): ?>
                        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if($mensaje_exito): ?>
                        <div class="alert alert-success py-2 small text-center"><?php echo $mensaje_exito; ?></div>
                    <?php endif; ?>

                    <!-- FORMULARIO DE INICIO DE SESIÓN -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Contraseña</label>
                            <div class="input-group">
                                <input type="password" id="passInput" name="password" class="form-control bg-dark text-white border-secondary" required>
                                <button class="btn btn-outline-secondary border-secondary" type="button" onclick="togglePass()">
                                    <i id="eyeIcon" class="bi bi-eye text-info"></i>
                                </button>
                            </div>
                        </div>

                        <!-- RECORDARME Y RECUPERAR CONTRASEÑA EN UNA SOLA LÍNEA -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check mb-0">
                                <input type="checkbox" name="remember" class="form-check-input" id="rem">
                                <label class="form-check-label small text-white-50" for="rem">Recordarme</label>
                            </div>
                            <a href="recuperar.php" class="small text-decoration-none" style="color: var(--identi-cyan);">¿Olvidaste tu contraseña?</a>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-identi btn-lg text-dark fw-bold">Entrar</button>
                        </div>
                    </form>

                    <!-- SEPARADOR VISUAL -->
                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1 border-secondary m-0">
                        <span class="px-2 small text-white-50" style="font-size: 0.75rem;">O CONTINÚA CON</span>
                        <hr class="flex-grow-1 border-secondary m-0">
                    </div>

                    <!-- BOTÓN OFICIAL DE GOOGLE SIGN-IN -->
                    <div id="g_id_onload"
                         data-client_id="529591964070-lnpo1opd1hljj0snlibhpcp29bfkv3q1.apps.googleusercontent.com"
                         data-callback="handleCredentialResponse"
                         data-auto_prompt="false"
                         data-auto_select="false">
                    </div>

                    <div class="d-flex justify-content-center mb-4">
                        <div class="g_id_signin"
                             data-type="standard"
                             data-size="large"
                             data-theme="outline"
                             data-text="sign_in_with"
                             data-shape="rectangular"
                             data-logo_alignment="left">
                        </div>
                    </div>

                    <!-- ENLACE A REGISTRO -->
                    <div class="text-center">
                        <p class="small mb-0 text-white-50">¿No tienes cuenta? <a href="registro.php" style="color: var(--identi-cyan); text-decoration: none; font-weight: bold;">Regístrate aquí</a></p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS DE JAVASCRIPT -->
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

// Función callback para Google Sign-In
function handleCredentialResponse(response) {
    const jwtToken = response.credential;

    fetch('google_login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ token: jwtToken })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            alert('Error al iniciar sesión con Google: ' + data.message);
        }
    })
    .catch(err => console.error(err));
}
</script>

<!-- Librería oficial de Google Identity Services -->
<script src="https://accounts.google.com/gsi/client" async defer></script>

</body>
</html>