<?php
include '../includes/auth.php';
include '../../includes/db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    mysqli_query($conexion, "
        UPDATE cupones 
        SET estado = IF(estado = 'activo', 'inactivo', 'activo') 
        WHERE id_cupon = $id
    ");
}

header("Location: index.php");
exit;