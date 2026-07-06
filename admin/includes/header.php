<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>IDENTIBAND Admin</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CSS -->
    <link rel="stylesheet"
          href="/IDENTIBAND/admin/assets/css/admin.css">

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- SIDEBAR -->

    <aside class="admin-sidebar">

        <div class="admin-logo">

            <h2>IDENTIBAND</h2>

            <span>ADMIN PANEL</span>

        </div>

       <ul class="admin-sidebar-menu">

    <li>
        <a href="/IDENTIBAND/admin/index.php"
           class="<?= $current == 'index.php' &&
                     strpos($_SERVER['PHP_SELF'], '/admin/productos/') === false &&
                     strpos($_SERVER['PHP_SELF'], '/admin/pedidos/') === false &&
                     strpos($_SERVER['PHP_SELF'], '/admin/usuarios/') === false &&
                     strpos($_SERVER['PHP_SELF'], '/admin/configuracion/') === false
                     ? 'active' : '' ?>">

            <i class="bi bi-grid-fill"></i>
            Dashboard
        </a>
    </li>

    <li>
        <a href="/IDENTIBAND/admin/productos/index.php"
           class="<?= strpos($_SERVER['PHP_SELF'], '/admin/productos/') !== false
                     ? 'active' : '' ?>">

            <i class="bi bi-box-seam"></i>
            Productos
        </a>
    </li>

    <li>
        <a href="/IDENTIBAND/admin/pedidos/index.php"
           class="<?= strpos($_SERVER['PHP_SELF'], '/admin/pedidos/') !== false
                     ? 'active' : '' ?>">

            <i class="bi bi-bag-check-fill"></i>
            Pedidos
        </a>
    </li>

    <li>
        <a href="/IDENTIBAND/admin/usuarios/index.php"
           class="<?= strpos($_SERVER['PHP_SELF'], '/admin/usuarios/') !== false
                     ? 'active' : '' ?>">

            <i class="bi bi-people-fill"></i>
            Usuarios
        </a>
    </li>

    <li>
        <a href="/IDENTIBAND/admin/configuracion/envio.php"
           class="<?= strpos($_SERVER['PHP_SELF'], '/admin/configuracion/') !== false
                     ? 'active' : '' ?>">

            <i class="bi bi-truck"></i>
            Envíos
        </a>
    </li>

    <li>
    <a href="/IDENTIBAND/admin/reportes/index.php"
       class="<?= strpos($_SERVER['PHP_SELF'], '/admin/reportes/') !== false
                 ? 'active' : '' ?>">

         <i class="bi bi-file-earmark-bar-graph-fill"></i>
         Reportes
       </a>
     </li>
</ul>

    </aside>

    <!-- MAIN -->

    <main class="admin-main">

        <!-- TOPBAR -->

        <div class="admin-topbar">

            <div>

                <h4 class="mb-0 text-white">
                    Panel Administrativo
                </h4>

                <small class="text-light">
                    Bienvenido,
                    <?= $_SESSION['nombre'] ?? 'Administrador' ?>
                </small>

            </div>

            <a href="/IDENTIBAND/admin/logout.php"
               class="btn btn-danger">

                <i class="bi bi-box-arrow-right"></i>
                Salir

            </a>

        </div>

        <!-- CONTENT -->

        <div class="admin-content">