<?php
include '../includes/auth.php';
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id_pedido']);
    $estado = $_POST['estado']; // Ya no necesitas mysqli_real_escape_string

    // Usamos marcadores de posición (:estado, :id) para mayor seguridad
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = :estado WHERE id_pedido = :id");
    
    // Pasamos los valores en un array al ejecutar
    $stmt->execute([
        'estado' => $estado,
        'id'     => $id
    ]);
}

header("Location: index.php");
exit;
?>