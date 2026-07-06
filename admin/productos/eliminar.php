<?php
include '../includes/auth.php';
include '../../includes/db.php';

$id = intval($_GET['id']);

mysqli_query(
    $conexion,
    "DELETE FROM productos
     WHERE id_producto = $id"
);

header("Location: index.php");
exit;
?>