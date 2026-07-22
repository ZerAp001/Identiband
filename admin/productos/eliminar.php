<?php
include '../includes/auth.php';
include '../../includes/db.php';

$id = intval($_GET['id']);

// 1. Preparamos la sentencia DELETE
$stmt = $conexion->prepare("DELETE FROM productos WHERE id_producto = :id");

// 2. Ejecutamos pasando el ID
$stmt->execute(['id' => $id]);

header("Location: index.php");
exit;
?>