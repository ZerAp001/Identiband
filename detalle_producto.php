<?php
include 'includes/db.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php#productos");
    exit;
}

$id_producto = intval($_GET['id']);

// PROCESAR ENVÍO DE RESEÑA
$mensaje_resena = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_resena'])) {
    if (!isset($_SESSION['usuario_id'])) {
        $mensaje_resena = '<div class="alert alert-warning">Debes iniciar sesión para publicar una reseña.</div>';
    } else {
        $uid_resena = $_SESSION['usuario_id'];
        $calificacion = intval($_POST['calificacion'] ?? 0);
        $comentario = mysqli_real_escape_string($conexion, trim($_POST['comentario'] ?? ''));

        if ($calificacion >= 1 && $calificacion <= 5 && !empty($comentario)) {
            $insert_resena = "INSERT INTO resenas (id_producto, id_usuario, calificacion, comentario) 
                              VALUES ($id_producto, $uid_resena, $calificacion, '$comentario')";
            if (mysqli_query($conexion, $insert_resena)) {
                $mensaje_resena = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>¡Gracias! Tu reseña ha sido publicada con éxito.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
            } else {
                $mensaje_resena = '<div class="alert alert-danger">Error al guardar la reseña. Inténtalo de nuevo.</div>';
            }
        } else {
            $mensaje_resena = '<div class="alert alert-warning">Por favor, selecciona una calificación y escribe un comentario.</div>';
        }
    }
}

// OBTENER INFORMACIÓN DEL PRODUCTO
$query = "SELECT * FROM productos WHERE id_producto = $id_producto";
$resultado = mysqli_query($conexion, $query);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    header("Location: index.php#productos");
    exit;
}

$producto = mysqli_fetch_assoc($resultado);

// CALCULAR PROMEDIO Y TOTAL DE RESEÑAS
$query_promedio = "SELECT AVG(calificacion) as promedio, COUNT(*) as total FROM resenas WHERE id_producto = $id_producto";
$res_promedio = mysqli_query($conexion, $query_promedio);
$data_promedio = mysqli_fetch_assoc($res_promedio);
$promedio_estrellas = round($data_promedio['promedio'] ?? 0, 1);
$total_resenas = $data_promedio['total'] ?? 0;

// OBTENER LISTA DE RESEÑAS
$query_list_resenas = "SELECT r.*, COALESCE(u.nombre, 'Cliente') AS nombre_usuario 
                       FROM resenas r 
                       LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario 
                       WHERE r.id_producto = $id_producto 
                       ORDER BY r.fecha DESC";
$res_list_resenas = mysqli_query($conexion, $query_list_resenas);

$es_personalizable = (
    stripos($producto['nombre_modelo'], 'Personalizable') !== false ||
    stripos($producto['nombre_modelo'], '1 color') !== false
);

$es_premium = (
    stripos($producto['nombre_modelo'], 'Premium') !== false
);

$es_favorito = false;

if (isset($_SESSION['usuario_id'])) {
    $uid = $_SESSION['usuario_id'];
    $checkFav = mysqli_query(
        $conexion,
        "SELECT id_favorito FROM favoritos WHERE id_usuario = $uid AND id_producto = $id_producto"
    );
    $es_favorito = mysqli_num_rows($checkFav) > 0;
}

// Función auxiliar para renderizar estrellas
function renderEstrellas($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= round($rating)) {
            $html .= '<i class="bi bi-star-fill text-warning me-1"></i>';
        } else {
            $html .= '<i class="bi bi-star text-secondary me-1"></i>';
        }
    }
    return $html;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $producto['nombre_modelo']; ?> - IDENTIBAND</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container py-5">

    <a href="index.php#productos" class="btn btn-outline-info mb-4">
        <i class="bi bi-arrow-left"></i> Volver al catálogo
    </a>

    <?php echo $mensaje_resena; ?>

    <div class="row g-5">

        <div class="col-lg-6">
            <img
                src="assets/<?php echo $producto['imagen_url']; ?>"
                class="img-fluid rounded shadow border border-secondary"
                onerror="this.src='assets/pulsera.png'"
                alt="Producto"
            >
        </div>

        <div class="col-lg-6">

            <span class="badge bg-info text-dark mb-2">
                <?php echo $producto['tipo']; ?>
            </span>

            <!-- RESUMEN DE CALIFICACIÓN -->
            <div class="d-flex align-items-center mb-3">
                <span class="me-2"><?php echo renderEstrellas($promedio_estrellas); ?></span>
                <span class="text-white fw-bold me-2"><?php echo $promedio_estrellas; ?></span>
                <span class="text-white-50 small">(<?php echo $total_resenas; ?> valoraciones)</span>
            </div>

            <h1 class="text-white fw-bold">
                <?php echo $producto['nombre_modelo']; ?>
            </h1>

            <h2 class="text-info fw-bold mb-4">
                $<?php echo number_format($producto['precio'], 2); ?>
            </h2>

            <form action="procesar_carrito.php" method="GET">

                <input type="hidden" name="id" value="<?php echo $producto['id_producto']; ?>">

                <div class="mb-3">
                    <label class="form-label text-white">Cantidad</label>
                    <input type="number" name="cantidad" min="1" value="1" required class="form-control bg-dark text-white border-secondary">
                </div>

                <?php if ($es_personalizable): ?>
                    <div class="mb-3">
                        <label class="form-label text-white">Color de pulsera</label>
                        <select name="color" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">Seleccionar color</option>
                            <option value="Negro">Negro</option>
                            <option value="Blanco">Blanco</option>
                            <option value="Azul">Azul</option>
                            <option value="Rojo">Rojo</option>
                            <option value="Rosa">Rosa</option>
                            <option value="Morado">Morado</option>
                            <option value="Verde">Verde</option>
                            <option value="Dorado">Dorado</option>
                        </select>
                    </div>
                <?php endif; ?>

                <?php if ($es_premium): ?>
                    <div class="mb-4">
                        <label class="form-label text-white">Material</label>
                        <select name="material" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">Seleccionar material</option>
                            <option value="Oro">Oro</option>
                            <option value="Plata">Plata</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-identi btn-lg">
                        <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
                    </button>

                    <?php if (!$es_favorito): ?>
                        <a href="procesar_favoritos.php?id=<?php echo $producto['id_producto']; ?>" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-heart me-2"></i>Añadir a favoritos
                        </a>
                    <?php else: ?>
                        <a href="favoritos.php" class="btn btn-danger btn-lg">
                            <i class="bi bi-heart-fill me-2"></i>Ya está en favoritos
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <hr class="border-secondary my-4">

            <h4 class="text-white">Descripción del producto</h4>
            <p class="text-white-50">
                <?php echo !empty($producto['descripcion'])
                    ? $producto['descripcion']
                    : 'Producto premium con tecnología NFC diseñado para eventos modernos y control inteligente de accesos.';
                ?>
            </p>

        </div>
    </div>

    <!-- SECCIÓN DE RESEÑAS Y VALORACIONES -->
    <div class="row mt-5 pt-4 border-top border-secondary">
        <div class="col-lg-7 mb-4 mb-lg-0">
            <h3 class="text-white fw-bold mb-4">
                <i class="bi bi-chat-square-text-fill text-info me-2"></i>Opiniones de Clientes
            </h3>

            <?php if ($res_list_resenas && mysqli_num_rows($res_list_resenas) > 0): ?>
                <div class="d-flex flex-column gap-3">
                    <?php while ($r = mysqli_fetch_assoc($res_list_resenas)): ?>
                        <div class="card bg-dark border-secondary p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong class="text-info me-2"><?php echo htmlspecialchars($r['nombre_usuario']); ?></strong>
                                    <small class="text-white-50"><?php echo date('d/m/Y', strtotime($r['fecha'])); ?></small>
                                </div>
                                <div><?php echo renderEstrellas($r['calificacion']); ?></div>
                            </div>
                            <p class="text-white-50 m-0 small">
                                <?php echo nl2br(htmlspecialchars($r['comentario'])); ?>
                            </p>
                        </div>
                    <?php endwhile; ?>
                </div>
           <?php else: ?>
             <div class="alert alert-dark bg-dark border-secondary text-white">
             <i class="bi bi-info-circle text-info me-2"></i>Aún no hay opiniones para este producto. ¡Sé el primero en calificarlo!
             </div>
           <?php endif; ?>
        </div>

        <!-- FORMULARIO PARA DEJAR RESEÑA -->
        <div class="col-lg-5">
            <div class="card bg-dark border-secondary p-4 shadow">
                <h4 class="text-white fw-bold mb-3">Deja tu opinión</h4>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <form action="detalle_producto.php?id=<?php echo $id_producto; ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-white">Calificación</label>
                            <select name="calificacion" class="form-select bg-dark text-white border-secondary" required>
                                <option value="5">⭐⭐⭐⭐⭐ (5/5) Excelente</option>
                                <option value="4">⭐⭐⭐⭐ (4/5) Muy bueno</option>
                                <option value="3">⭐⭐⭐ (3/5) Regular</option>
                                <option value="2">⭐⭐ (2/5) Malo</option>
                                <option value="1">⭐ (1/5) Pésimo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Comentario</label>
                            <textarea name="comentario" rows="4" class="form-control bg-dark text-white border-secondary" placeholder="¿Qué te pareció el producto y la experiencia NFC?" required></textarea>
                        </div>

                        <button type="submit" name="submit_resena" class="btn btn-info w-100 fw-bold">
                            <i class="bi bi-send me-1"></i> Publicar Reseña
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="bi bi-lock-fill text-info fs-1 d-block mb-2"></i>
                        <p class="text-white-50 small mb-3">Debes iniciar sesión para compartir tu experiencia con la comunidad.</p>
                        <a href="login.php" class="btn btn-outline-info btn-sm fw-bold">Iniciar Sesión</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>