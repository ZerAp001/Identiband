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

mysqli_query(
    $conexion,
    "DELETE FROM favoritos
     WHERE id_usuario = $id_usuario
     AND id_producto = $id_producto"
);

header("Location: favoritos.php");
exit;
?>