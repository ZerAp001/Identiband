<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: favoritos.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$id_producto = intval($_GET['id']);

// Usamos prepare para una eliminación limpia y segura
$stmt = $conexion->prepare("DELETE FROM favoritos WHERE id_usuario = :id_u AND id_producto = :id_p");

// Ejecutamos pasando los marcadores en un array
$stmt->execute(['id_u' => $id_usuario, 'id_p' => $id_producto]);

header("Location: favoritos.php");
exit;
?>