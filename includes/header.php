<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

// Actualiza el contador del carrito.
$conteo_carrito = 0;

if (isset($_SESSION['usuario_id'])) {
    $u_id = $_SESSION['usuario_id'];

    // Preparamos la consulta para ser segura
    $stmt = $conexion->prepare("SELECT COALESCE(SUM(cantidad), 0) AS total FROM carrito WHERE id_usuario = :u_id");
    
    // Ejecutamos pasando el parámetro
    $stmt->execute(['u_id' => $u_id]);
    
    // Obtenemos el resultado directamente
    $data_carrito = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Asignamos el valor
    $conteo_carrito = $data_carrito['total'];
}
?>

<style>
    .navbar {
        background-color: #0d1117 !important;
    }

    .nav-link {
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: var(--identi-cyan) !important;
    }

    .dropdown-menu {
        min-width: 200px;
        border: 1px solid #30363d;
    }

    .dropdown-item:hover {
        background-color: var(--identi-cyan) !important;
        color: #000 !important;
    }

    .cart-badge {
        font-size: 0.7rem;
        padding: 0.35em 0.65em;
    }

    .search-input {
        max-width: 400px;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top border-bottom border-secondary py-3">
    <div class="container-fluid px-lg-5">

        <!-- Logo y título Identiband. -->
      <a
         class="navbar-brand fw-bold fs-3 d-flex align-items-center"
            href="index.php"
            style="color: var(--identi-cyan); letter-spacing: 1px;"
         >
         <img
          src="/identiband/assets/logo.png"
                alt="IDENTIBAND"
                class="me-2"
                style="
                width: 48px;
                height: 48px;
                object-fit: contain;
                  display: block;
                     "
                        >
             IDENTIBAND
       </a>

        <!-- Menú hamburguesa en móvil -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navIdentiband"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navIdentiband">

            <!-- Buscador -->
            <form
                action="buscar_producto.php"
                method="GET"
                class="d-flex mx-auto search-input my-3 my-lg-0"
            >
                <div class="input-group">

                    <input
                        class="form-control bg-dark text-white border-secondary"
                        type="search"
                        name="busqueda"
                        placeholder="Buscar pulseras..."
                        aria-label="Search"
                        required
                    >

                    <button class="btn btn-outline-info" type="submit">
                        <i class="bi bi-search"></i>
                    </button>

                </div>
            </form>

            <!-- Menú de acceso rápido -->
            <ul class="navbar-nav align-items-center">

                <li class="nav-item">
                    <a class="nav-link px-3" href="index.php">
                        Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link px-3" href="index.php#beneficios">
                        Beneficios
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link px-3" href="index.php#productos">
                        Productos
                    </a>
                </li>

                <!-- Carrito -->
                <li class="nav-item mx-lg-2">
                    <a
                        class="nav-link position-relative d-inline-block"
                        href="carrito.php"
                    >
                        <i class="bi bi-cart3 fs-5"></i>

                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info text-dark cart-badge"
                            id="cart-count"
                        >
                            <?php echo $conteo_carrito; ?>
                        </span>
                    </a>
                </li>

                <!-- Acceso para el loggin del usuario. -->
                <?php if (isset($_SESSION['usuario_id'])): ?>

                    <li class="nav-item dropdown ms-lg-3">

                        <a
                            class="nav-link dropdown-toggle text-info fw-bold"
                            href="javascript:void(0)"
                            id="userMenu"
                            role="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                        >
                            <i class="bi bi-person-circle me-1"></i>
                            Hola, <?php echo explode(' ', $_SESSION['nombre'])[0]; ?>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary shadow-lg">

                            <li>
                                <a class="dropdown-item text-white py-2" href="pedidos.php">
                                    <i class="bi bi-bag me-2"></i>
                                    Mis Pedidos
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item text-white py-2" href="carrito.php">
                                    <i class="bi bi-cart me-2"></i>
                                    Mi Carrito
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item text-white py-2" href="favoritos.php">
                                    <i class="bi bi-heart me-2"></i>
                                    Mis Favoritos
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider border-secondary">
                            </li>

                            <li>
                                <a class="dropdown-item text-danger py-2" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Cerrar Sesión
                                </a>
                            </li>

                        </ul>
                    </li>

                <?php else: ?>

                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-identi px-4 fw-bold" href="login.php">
                            Entrar
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>