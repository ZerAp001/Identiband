<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Identiband</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <header class="bg-identi-gradient py-5 mt-0">
        <div class="container px-5">
            <div class="row gx-5 align-items-center justify-content-center">
                <div class="col-lg-8 col-xl-7 col-xxl-6">
                    <div class="my-5 text-center text-xl-start">
                        <h1 class="display-3 fw-bolder text-white mb-2">IDENTIBAND</h1>
                        <p class="lead fw-normal text-white-50 mb-4">
                            "Más que una pulsera, la llave de tu evento."
                        </p>
                        <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-xl-start">
                            <a class="btn btn-identi btn-lg px-4 me-sm-3" href="#productos">
                                Ver Productos
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5 col-xxl-6 d-none d-xl-block text-center">
                    <img class="img-fluid rounded-3 my-5" 
                         src="assets/pulsera.png" 
                         alt="Identiband NFC" 
                         style="filter: drop-shadow(0 0 20px rgba(0, 210, 255, 0.5));" />
                </div>
            </div>
        </div>
    </header>

    <section class="py-5 border-bottom" id="beneficios" style="background-color: var(--identi-dark);">
        <div class="container px-5 my-5">
            <div class="row gx-5 justify-content-center text-center mb-5">
                <div class="col-lg-8">
                    <h2 class="fw-bolder text-white">¿Por qué elegir Identiband?</h2>
                    <p class="lead mb-0" style="color: var(--identi-gray);">Seguridad, tecnología y comodidad en un solo lugar.</p>
                </div>
            </div>
            <div class="row gx-5">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="feature bg-identi-gradient text-white rounded-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 4rem; height: 4rem;">
                        <i class="bi bi-truck fs-2" style="color: var(--identi-cyan);"></i>
                    </div>
                    <h2 class="h4 fw-bolder text-white">Envío Gratis</h2>
                    <p class="text-white-50">En pedidos mayores a $1,000 MXN. Recibe tus pulseras en la puerta de tu casa u oficina con envío 100% gratis.</p>
                </div>
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="feature bg-identi-gradient text-white rounded-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 4rem; height: 4rem;">
                        <i class="bi bi-chat-dots fs-2" style="color: var(--identi-cyan);"></i>
                    </div>
                    <h2 class="h4 fw-bolder text-white">Soporte Técnico</h2>
                    <p class="text-white-50">Contamos con atención personalizada para la configuración de tus lectores y pulseras.</p>
                </div>
                <div class="col-lg-4">
                    <div class="feature bg-identi-gradient text-white rounded-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 4rem; height: 4rem;">
                        <i class="bi bi-shield-check fs-2" style="color: var(--identi-cyan);"></i>
                    </div>
                    <h2 class="h4 fw-bolder text-white">Pago Seguro</h2>
                    <p class="text-white-50">Aceptamos tarjetas bancarias, PayPal y depósitos en Oxxo con total seguridad.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="background-color: #1e293b;">
        <div class="container px-5 my-5">
            <div class="row gx-5 align-items-center">
                <div class="col-lg-6">
                    <img class="img-fluid rounded mb-5 mb-lg-0 shadow-lg" src="assets/nosotros.jpg" alt="Equipo Identiband" />
                </div>
                <div class="col-lg-6">
                    <h2 class="fw-bolder text-white mb-3">Nuestra Propuesta de Valor</h2>
                    <p class="lead fw-normal text-white-50 mb-4">
                        IDENTIBAND es una solución tecnológica que digitaliza eventos mediante pulseras NFC. Optimizamos la gestión de accesos, garantizando seguridad y una experiencia fluida para tus invitados.
                    </p>
                    <p class="text-white-50 mb-4">
                        Nacemos de la necesidad de modernizar los eventos en México, eliminando boletos físicos y listas de papel obsoletas para dar paso a la era inteligente.
                    </p>
                   <a class="text-decoration-none fw-bold" href="tecnologia.php" style="color: var(--identi-cyan);">
                     Conoce más sobre nuestra tecnología
                      <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" id="productos" style="background-color: var(--identi-dark);">
        <div class="container px-4 px-lg-5 mt-5">
            <h2 class="text-white fw-bolder mb-4 text-center">Nuestros Modelos</h2>

            <div class="d-flex justify-content-center gap-2 mb-5 flex-wrap">
    <button type="button" class="btn btn-outline-info" onclick="filterSelection('Todos')">Todos</button>
    <button type="button" class="btn btn-outline-info" onclick="filterSelection('Individual')">Individuales</button>
    <button type="button" class="btn btn-outline-info" onclick="filterSelection('Paquete')">Paquetes</button>
    <button type="button" class="btn btn-outline-info" onclick="filterSelection('Premium')">Premium</button>
 </div>

            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php
                include 'includes/db.php';
                $query = "SELECT * FROM productos";
                $resultado = mysqli_query($conexion, $query);

                if ($resultado) {
                    while ($row = mysqli_fetch_assoc($resultado)) {
                ?>
                <div class="col mb-5 product-item <?php echo $row['tipo']; ?>" style="display: block;">
                    <div class="card h-100 border-secondary bg-dark text-white product-card">
                        <div class="badge badge-tipo bg-info text-dark position-absolute" style="top: 0.5rem; right: 0.5rem">
                            <?php echo $row['tipo']; ?>
                        </div>
                        
                        <img class="card-img-top" src="assets/<?php echo $row['imagen_url']; ?>" 
                             onerror="this.src='assets/pulsera.png'" alt="Producto Identiband" />

                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bolder"><?php echo $row['nombre_modelo']; ?></h5>
                            <span class="text-info fs-5">$<?php echo number_format($row['precio'], 2); ?></span>
                            <p class="small text-white-50 mt-2"><?php echo $row['variante_info'] ?? $row['descripcion']; ?></p>
                        </div>
                        
                        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                            <div class="text-center d-grid gap-2">
                                <?php
                                $requiere_personalizacion =
                                (
                                    stripos($row['nombre_modelo'], 'Personalizable') !== false
                                    ||
                                    stripos($row['nombre_modelo'], 'Premium') !== false
                                    ||
                                    stripos($row['nombre_modelo'], '1 color') !== false
                                    );
                                ?>
                                
                        <?php if ($requiere_personalizacion): ?>
                            <a
                             class="btn btn-identi mt-auto fw-bold"
                             href="detalle_producto.php?id=<?php echo $row['id_producto']; ?>"
                             >  Personalizar y comprar
                            </a>
                            
                    <?php else: ?>
                        <a
                        class="btn btn-identi mt-auto fw-bold"
                        href="procesar_carrito.php?id=<?php echo $row['id_producto']; ?>"
                        > Agregar al carrito
                           </a>
                           <?php endif; ?>
                                <a class="btn btn-outline-light btn-sm" href="detalle_producto.php?id=<?php echo $row['id_producto']; ?>">Ver detalles</a>
                            </div>
                        </div>
                    </div>
                  </div>
                 <?php 
                    }
                } 
                ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="/Identiband/js/scripts.js"></script>

</body>
</html>