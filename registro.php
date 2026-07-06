<?php
session_start();
include 'includes/db.php';

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellidos = mysqli_real_escape_string($conexion, $_POST['apellidos']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validar si el correo ya existe
    $check_email = mysqli_query($conexion, "SELECT email FROM usuarios WHERE email = '$email'");
    
    if (mysqli_num_rows($check_email) > 0) {
        $mensaje = "Este correo ya está registrado.";
        $tipo_alerta = "danger";
    } elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_alerta = "danger";
    } else {
        // Encriptar la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre, apellidos, email, password) VALUES ('$nombre', '$apellidos', '$email', '$password_hash')";
        
        if (mysqli_query($conexion, $sql)) {
            $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión.";
            $tipo_alerta = "success";
            // Opcional: Redirigir al login tras 2 segundos
            header("refresh:2;url=login.php");
        } else {
            $mensaje = "Error al registrar: " . mysqli_error($conexion);
            $tipo_alerta = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Identiband - Registro</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-identi-gradient" style="min-height: 100vh; display: flex; align-items: center; padding: 40px 0;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-dark text-white border-secondary shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold" style="color: var(--identi-cyan);">CREAR CUENTA</h2>
                        <p class="text-white-50">Únete a la era inteligente de Identiband</p>
                    </div>

                    <?php if($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_alerta; ?> py-2 small text-center">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small">Nombre</label>
                                <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Contraseña</label>
                            <input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Mínimo 6 caracteres" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small">Confirmar Contraseña</label>
                            <input type="password" name="confirm_password" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-identi btn-lg text-dark fw-bold">Registrarse</button>
                            <a href="login.php" class="btn btn-outline-light mt-2">Volver al Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>