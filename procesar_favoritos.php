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

// 1. Verificar si ya existe en favoritos con las mismas opciones
$stmt_verificar = $conexion->prepare("
    SELECT * FROM favoritos
    WHERE id_usuario = :id_usuario
    AND id_producto = :id_producto
    AND color_elegido = :color
    AND material_elegido = :material
    AND variante_paquete = :variante
");

$stmt_verificar->execute([
    'id_usuario' => $id_usuario,
    'id_producto' => $id_producto,
    'color' => $color,
    'material' => $material,
    'variante' => $variante
]);

// 2. Si no existe, lo insertamos
if ($stmt_verificar->rowCount() == 0) {
    $stmt_insert = $conexion->prepare("
        INSERT INTO favoritos
        (id_usuario, id_producto, color_elegido, material_elegido, variante_paquete)
        VALUES
        (:id_usuario, :id_producto, :color, :material, :variante)
    ");
    
    $stmt_insert->execute([
        'id_usuario' => $id_usuario,
        'id_producto' => $id_producto,
        'color' => $color,
        'material' => $material,
        'variante' => $variante
    ]);
}

header("Location: favoritos.php");
exit;
?>