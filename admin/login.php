<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Usuario admin simple (puedes luego hacerlo en DB)
    if ($email === 'admin@identiband.com' && $password === '1234') {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit;
    }

    $error = "Credenciales incorrectas";
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Correo admin" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button>Entrar</button>
</form>

<?php if (!empty($error)) echo $error; ?>