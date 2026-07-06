<?php
session_start();
session_unset();
session_destroy();

// Elimina la cookie de "recordar" usuario.
if (isset($_COOKIE['recordar_token'])) {
    setcookie("recordar_token", "", time() - 3600, "/");
}

header("Location: index.php");
exit();
?>