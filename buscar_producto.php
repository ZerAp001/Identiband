<?php
include 'includes/db.php';

// Filtra las palabras que se colocan en el buscador.
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

if ($busqueda == '') {
    header("Location: index.php");
    exit;
}

$query = "SELECT id_producto 
          FROM productos 
          WHERE nombre_modelo LIKE '%$busqueda%'
          LIMIT 1";

$resultado = mysqli_query($conexion, $query);

if (mysqli_num_rows($resultado) > 0) {
    $producto = mysqli_fetch_assoc($resultado);

    header("Location: detalle_producto.php?id=" . $producto['id_producto']);
    exit;
} else {
    echo "
    <script>
        alert('Este producto no existe');
        window.location='index.php#productos';
    </script>
    ";
}
?>