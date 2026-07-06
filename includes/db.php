<?php
// Archivo para la conexión con mySQL
$host = "localhost";
$user = "root";

$pass = "1234";

$db = "identiband_db";

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");
?>