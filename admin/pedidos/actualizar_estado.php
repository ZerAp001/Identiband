<?php
include '../includes/auth.php';
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['id_pedido']);

    $estado = mysqli_real_escape_string(
        $conexion,
        $_POST['estado']
    );

    mysqli_query(
        $conexion,
        "UPDATE pedidos
         SET estado = '$estado'
         WHERE id_pedido = $id"
    );
}

header("Location: index.php");
exit;
?>