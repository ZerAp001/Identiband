<?php
include 'db.php';

// Contador de los productos en el carrito
function obtenerConteoCarrito($conexion, $usuario_id) {
    $sql = "SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = $usuario_id";
    $res = mysqli_query($conexion, $sql);
    $data = mysqli_fetch_assoc($res);
    return $data['total'] ? $data['total'] : 0;
}
?>