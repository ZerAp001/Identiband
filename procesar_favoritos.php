<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: index.php#productos");
    exit;
}

$id_producto = intval($_GET['id']);
$id_usuario = $_SESSION['usuario_id'];

$color = $_GET['color'] ?? '';
$material = $_GET['material'] ?? '';
$variante = $_GET['variante'] ?? '';

$verificar = mysqli_query(
    $conexion,
    "SELECT * FROM favoritos
     WHERE id_usuario = $id_usuario
     AND id_producto = $id_producto
     AND color_elegido = '$color'
     AND material_elegido = '$material'
     AND variante_paquete = '$variante'"
);

if (mysqli_num_rows($verificar) == 0) {
    mysqli_query(
        $conexion,
        "INSERT INTO favoritos
        (id_usuario, id_producto, color_elegido, material_elegido, variante_paquete)
        VALUES
        ($id_usuario, $id_producto, '$color', '$material', '$variante')"
    );
}

header("Location: favoritos.php");
exit;
?>