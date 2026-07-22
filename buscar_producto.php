<?php
include 'includes/db.php';

// Filtra las palabras que se colocan en el buscador.
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

if ($busqueda == '') {
    header("Location: index.php");
    exit;
}

// 1. Preparamos la consulta con el marcador :busqueda
$stmt = $conexion->prepare("SELECT id_producto FROM productos WHERE nombre_modelo LIKE :busqueda LIMIT 1");

// 2. Ejecutamos añadiendo los % al valor de la variable
$stmt->execute(['busqueda' => "%$busqueda%"]);

// 3. Obtenemos el producto directamente
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($producto) {
    header("Location: detalle_producto.php?id=" . $producto['id_producto']);
    exit;
} else {
    echo "
    <script>
        alert('Este producto no existe');
        window.location='index.php#productos';
    </script>";
}
?>