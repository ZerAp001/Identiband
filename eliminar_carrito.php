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
    $sql = "DELETE FROM carrito 
            WHERE id_carrito = $id_carrito 
            AND id_usuario = $id_usuario";

    mysqli_query($conexion, $sql);
}

header("Location: carrito.php");
exit;
?>