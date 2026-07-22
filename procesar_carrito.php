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
$color = isset($_GET['color']) ? $_GET['color'] : null;
$material = isset($_GET['material']) ? $_GET['material'] : null;

/*
Tomamos variante_info desde productos
para guardar si es Pack x6, x12, x18, etc.
*/
$stmt_prod = $conexion->prepare("SELECT variante_info FROM productos WHERE id_producto = :id_producto");
$stmt_prod->execute(['id_producto' => $id_producto]);
$producto_info = $stmt_prod->fetch(PDO::FETCH_ASSOC);

$variante_paquete = null;
if ($producto_info) {
    $variante_paquete = $producto_info['variante_info'];
}

// Verificar si ya existe exactamente el mismo producto con mismas personalizaciones
$stmt_check = $conexion->prepare("
    SELECT * FROM carrito
    WHERE id_usuario = :id_usuario
    AND id_producto = :id_producto
    AND IFNULL(color_elegido, '') = IFNULL(:color, '')
    AND IFNULL(material_elegido, '') = IFNULL(:material, '')
");

$stmt_check->execute([
    'id_usuario' => $id_usuario,
    'id_producto' => $id_producto,
    'color' => $color ?? '',
    'material' => $material ?? ''
]);

if ($stmt_check->rowCount() > 0) {
    // Si ya existe, actualizamos la cantidad sumando la nueva
    $stmt_update = $conexion->prepare("
        UPDATE carrito
        SET cantidad = cantidad + :cantidad
        WHERE id_usuario = :id_usuario
        AND id_producto = :id_producto
    ");
    $stmt_update->execute([
        'cantidad' => $cantidad,
        'id_usuario' => $id_usuario,
        'id_producto' => $id_producto
    ]);
} else {
    // Si no existe, insertamos el nuevo registro
    $stmt_insert = $conexion->prepare("
        INSERT INTO carrito
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
            :id_usuario,
            :id_producto,
            :cantidad,
            :color,
            :material,
            :variante_paquete
        )
    ");
    
    $stmt_insert->execute([
        'id_usuario' => $id_usuario,
        'id_producto' => $id_producto,
        'cantidad' => $cantidad,
        'color' => $color,
        'material' => $material,
        'variante_paquete' => $variante_paquete
    ]);
}
header("Location: carrito.php");
exit;
?>