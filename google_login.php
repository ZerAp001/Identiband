<?php
include 'includes/db.php';
session_start();

header('Content-Type: application/json');

// Obtener los datos JSON enviados por el fetch de JS
$data = json_decode(file_get_contents('php://input'), true);
$jwt = $data['token'] ?? '';

if (empty($jwt)) {
    echo json_encode(['success' => false, 'message' => 'Token no recibido']);
    exit;
}

// Decodificar el payload del JWT de Google
$token_parts = explode('.', $jwt);
if (count($token_parts) !== 3) {
    echo json_encode(['success' => false, 'message' => 'Token inválido']);
    exit;
}

$payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $token_parts[1])), true);

if (!$payload || !isset($payload['email'])) {
    echo json_encode(['success' => false, 'message' => 'No se pudo leer la información de Google']);
    exit;
}

$google_id = mysqli_real_escape_string($conexion, $payload['sub']);
$email     = mysqli_real_escape_string($conexion, $payload['email']);
$nombre    = mysqli_real_escape_string($conexion, $payload['name'] ?? 'Usuario Google');

// 1. Buscar si el usuario ya existe por google_id o por email
$query = mysqli_query($conexion, "SELECT id_usuario FROM usuarios WHERE google_id = '$google_id' OR email = '$email' LIMIT 1");

if (mysqli_num_rows($query) > 0) {
    $usuario = mysqli_fetch_assoc($query);
    $id_usuario = $usuario['id_usuario'];
    
    // Vincular google_id si aún no lo tenía
    mysqli_query($conexion, "UPDATE usuarios SET google_id = '$google_id' WHERE id_usuario = $id_usuario");
} else {
    // 2. Si no existe, creamos la cuenta automáticamente
    $insert = mysqli_query($conexion, "INSERT INTO usuarios (nombre, email, google_id) VALUES ('$nombre', '$email', '$google_id')");
    $id_usuario = mysqli_insert_id($conexion);
}

// Crear sesión para IDENTIBAND
$_SESSION['usuario_id'] = $id_usuario;
$_SESSION['nombre'] = $nombre;          // <-- Agrega esta línea
$_SESSION['usuario_nombre'] = $nombre;

echo json_encode(['success' => true]);
exit;