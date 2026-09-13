<?php
include 'includes/db.php';

$token = $_GET['token'] ?? '';
$mensaje = '';
$token_valido = false;
$usuario_id = null;

if (!empty($token)) {
    $token_clean = mysqli_real_escape_string($conexion, $token);
    $ahora = date('Y-m-d H:i:s');

    // Verificar token existente y no expirado
    $query = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE reset_token = '$token_clean' AND reset_expira > '$ahora'");
    
    if (mysqli_num_rows($query) > 0) {
        $token_valido = true;
        $usuario = mysqli_fetch_assoc($query);
        $usuario_id = $usuario['id_usuario'];
    } else {
        $mensaje = "El enlace de recuperación es inválido o ha expirado.";
    }
} else {
    $mensaje = "No se proporcionó un token de recuperación.";
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $nueva_password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirm_password'] ?? '';

    if (strlen($nueva_password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva_password !== $confirmar_password) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        // Encriptar nueva contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

        // Actualizar contraseña y limpiar el token
        mysqli_query($conexion, "UPDATE usuarios SET password = '$password_hash', reset_token = NULL, reset_expira = NULL WHERE id_usuario = $usuario_id");

        header("Location: login.php?reset=exito");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - IDENTIBAND</title>
</head>
<body style="background-color: #0d1117; color: #ffffff; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0;">
    <div style="background: #161b22; padding: 30px; border-radius: 10px; width: 100%; max-width: 400px; border: 1px solid #30363d;">
        <h2 style="color: #00d2ff; text-align: center; margin-bottom: 10px;">Nueva Contraseña</h2>

        <?php if (!empty($mensaje)): ?>
            <div style="padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; background-color: #da3633; color: white;">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <?php if ($token_valido): ?>
            <form action="" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #c9d1d9;">Nueva Contraseña:</label>
                    <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #30363d; background: #0d1117; color: white; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; color: #c9d1d9;">Confirmar Contraseña:</label>
                    <input type="password" name="confirm_password" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #30363d; background: #0d1117; color: white; box-sizing: border-box;">
                </div>
                <button type="submit" style="width: 100%; padding: 12px; background: #00d2ff; color: #0d1117; border: none; font-weight: bold; border-radius: 5px; cursor: pointer;">Guardar Contraseña</button>
            </form>
        <?php else: ?>
            <p style="text-align: center; margin-top: 15px;">
                <a href="recuperar.php" style="color: #00d2ff;">Solicitar un nuevo enlace</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>