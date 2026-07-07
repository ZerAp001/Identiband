<?php
// Archivo para la conexión con MySQL configurado para Railway
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Si las variables de entorno no existen (por si se prueba localmente), se pueden poner valores por defecto, pero Railway usará los de arriba.
$host = $host ? $host : "localhost";
$user = $user ? $user : "root";
$pass = $pass ? $pass : "1234";
$db   = $db   ? $db   : "identiband_db";

$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");
?>