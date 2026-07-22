<?php
include 'db.php';

// Contador de los productos en el carrito
function obtenerConteoCarrito($conexion, $usuario_id) {
    // 1. Preparamos la consulta para evitar inyecciones SQL
    $stmt = $conexion->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = :usuario_id");
    
    // 2. Ejecutamos pasando el valor
    $stmt->execute(['usuario_id' => $usuario_id]);
    
    // 3. Obtenemos el resultado
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 4. Retornamos el valor
    return $data['total'] ? $data['total'] : 0;
}