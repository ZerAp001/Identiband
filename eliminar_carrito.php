<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$id_carrito = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_carrito > 0) {
    // Usamos prepare y marcadores para mayor seguridad
    $stmt = $conexion->prepare("DELETE FROM carrito WHERE id_carrito = :id_c AND id_usuario = :id_u");
    
    // Ejecutamos pasando los parámetros de forma segura
    $stmt->execute(['id_c' => $id_carrito, 'id_u' => $id_usuario]);
}

header("Location: carrito.php");
exit;
?>