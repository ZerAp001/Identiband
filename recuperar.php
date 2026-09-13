<?php
include 'includes/db.php';

/* 
// DESCOMENTAR CUANDO PASES A PRODUCCIÓN CON CREDENCIALES REALES DE GMAIL
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/Exception.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
*/

$mensaje = '';
$tipo_alerta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conexion, trim($_POST['email']));

    // Verificar si el correo existe
    $query = mysqli_query($conexion, "SELECT id_usuario, nombre FROM usuarios WHERE email = '$email'");
    if (mysqli_num_rows($query) > 0) {
        $usuario = mysqli_fetch_assoc($query);

        // Generar token único y seguro (64 caracteres hexadecimales)
        $token = bin2hex(random_bytes(32));
        // Expiración en 1 hora
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token y expiración en la BD
        mysqli_query($conexion, "UPDATE usuarios SET reset_token = '$token', reset_expira = '$expiracion' WHERE id_usuario = " . $usuario['id_usuario']);

        // Enlace de recuperación
        $link_recuperacion = "restablecer.php?token=" . $token;

        // MODO PRUEBA LOCAL: Genera el enlace directamente en pantalla
        $mensaje = "<strong>[MODO DE PRUEBA LOCAL]</strong> Solicitud generada para <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>.<br><br>"
                 . "<a href='{$link_recuperacion}' style='background-color:#00d2ff; color:#0d1117; padding:10px 15px; text-decoration:none; font-weight:bold; border-radius:5px; display:block; text-align:center; box-sizing:border-box;'>"
                 . "Restablecer Contraseña Aquí</a>";
        $tipo_alerta = "exito";

    } else {
        // Por seguridad, no indicamos explícitamente si el correo no existe
        $mensaje = "Si el correo está registrado, recibirás un mensaje con las instrucciones.";
        $tipo_alerta = "exito";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - IDENTIBAND</title>
    <!-- Incluye tus estilos / Bootstrap aquí -->
</head>
<body style="background-color: #0d1117; color: #ffffff; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0;">
    <div style="background: #161b22; padding: 30px; border-radius: 10px; width: 100%; max-width: 400px; border: 1px solid #30363d;">
        <h2 style="color: #00d2ff; text-align: center; margin-bottom: 10px;">Recuperar Contraseña</h2>
        <p style="color: #8b949e; font-size: 14px; text-align: center;">Ingresa tu correo electrónico registrado y te enviaremos las instrucciones.</p>

        <?php if (!empty($mensaje)): ?>
            <div style="padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; background-color: <?= $tipo_alerta === 'exito' ? '#1f6feb' : '#da3633' ?>; color: white;">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; color: #c9d1d9;">Correo Electrónico:</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #30363d; background: #0d1117; color: white; box-sizing: border-box;">
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background: #00d2ff; color: #0d1117; border: none; font-weight: bold; border-radius: 5px; cursor: pointer;">Enviar enlace</button>
        </form>

        <p style="text-align: center; margin-top: 15px; font-size: 14px;">
            <a href="login.php" style="color: #00d2ff; text-decoration: none;">Volver al inicio de sesión</a>
        </p>
    </div>
</body>
</html>