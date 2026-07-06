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

$cantidad = isset($_GET['cantidad']) ? intval($_GET['cantidad']) : 1;
if ($cantidad < 1) {
    $cantidad = 1;
}

$color = isset($_GET['color']) ? mysqli_real_escape_string($conexion, $_GET['color']) : NULL;
$material = isset($_GET['material']) ? mysqli_real_escape_string($conexion, $_GET['material']) : NULL;

/*
Tomamos variante_info desde productos
para guardar si es Pack x6, x12, x18, etc.
*/
$query_producto = mysqli_query(
    $conexion,
    "SELECT variante_info FROM productos WHERE id_producto = $id_producto"
);

$variante_paquete = NULL;

if ($query_producto && mysqli_num_rows($query_producto) > 0) {
    $producto = mysqli_fetch_assoc($query_producto);
    $variante_paquete = mysqli_real_escape_string($conexion, $producto['variante_info']);
}

// Verificar si ya existe exactamente el mismo producto con mismas personalizaciones
$check = mysqli_query(
    $conexion,
    "SELECT * FROM carrito
     WHERE id_usuario = $id_usuario
     AND id_producto = $id_producto
     AND IFNULL(color_elegido,'') = IFNULL('$color','')
     AND IFNULL(material_elegido,'') = IFNULL('$material','')"
);

if (mysqli_num_rows($check) > 0) {
    mysqli_query(
        $conexion,
        "UPDATE carrito
         SET cantidad = cantidad + $cantidad
         WHERE id_usuario = $id_usuario
         AND id_producto = $id_producto"
    );
} else {
    mysqli_query(
        $conexion,
        "INSERT INTO carrito
        (
            id_usuario,
            id_producto,
            cantidad,
            color_elegido,
            material_elegido,
            variante_paquete
        )
        VALUES
        (
            $id_usuario,
            $id_producto,
            $cantidad,
            " . ($color ? "'$color'" : "NULL") . ",
            " . ($material ? "'$material'" : "NULL") . ",
            " . ($variante_paquete ? "'$variante_paquete'" : "NULL") . "
        )"
    );
}

header("Location: carrito.php");
exit;
?>