<?php
include 'includes/db.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php#productos");
    exit;
}

//Selección de materiales y cantidad de los productos.
$id_producto = intval($_GET['id']);

$query = "SELECT * FROM productos WHERE id_producto = $id_producto";
$resultado = mysqli_query($conexion, $query);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    header("Location: index.php#productos");
    exit;
}

$producto = mysqli_fetch_assoc($resultado);

$es_personalizable = (
    stripos($producto['nombre_modelo'], 'Personalizable') !== false
    ||
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
        "SELECT id_favorito
         FROM favoritos
         WHERE id_usuario = $uid
         AND id_producto = $id_producto"
    );

    $es_favorito = mysqli_num_rows($checkFav) > 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $producto['nombre_modelo']; ?></title>

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

    <div class="row g-5">

        <div class="col-lg-6">
            <img
                src="assets/<?php echo $producto['imagen_url']; ?>"
                class="img-fluid rounded shadow"
                onerror="this.src='assets/pulsera.png'"
                alt="Producto"
            >
        </div>

        <div class="col-lg-6">

            <span class="badge bg-info text-dark mb-3">
                <?php echo $producto['tipo']; ?>
            </span>

            <h1 class="text-white fw-bold">
                <?php echo $producto['nombre_modelo']; ?>
            </h1>

            <h2 class="text-info fw-bold mb-4">
                $<?php echo number_format($producto['precio'], 2); ?>
            </h2>

            <form action="procesar_carrito.php" method="GET">

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $producto['id_producto']; ?>"
                >

                <div class="mb-3">
                    <label class="form-label text-white">
                        Cantidad
                    </label>

                    <input
                        type="number"
                        name="cantidad"
                        min="1"
                        value="1"
                        required
                        class="form-control"
                    >
                </div>

                <?php if ($es_personalizable): ?>
                    <div class="mb-3">
                        <label class="form-label text-white">
                            Color de pulsera
                        </label>

                        <select
                            name="color"
                            class="form-select"
                            required
                        >
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
                        <label class="form-label text-white">
                            Material
                        </label>

                        <select
                            name="material"
                            class="form-select"
                            required
                        >
                            <option value="">Seleccionar material</option>
                            <option value="Oro">Oro</option>
                            <option value="Plata">Plata</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-3">

                    <button
                        type="submit"
                        class="btn btn-identi btn-lg"
                    >
                        <i class="bi bi-cart-plus me-2"></i>
                        Agregar al carrito
                    </button>

                    <?php if (!$es_favorito): ?>

                        <a
                            href="procesar_favoritos.php?id=<?php echo $producto['id_producto']; ?>"
                            class="btn btn-outline-light btn-lg"
                        >
                            <i class="bi bi-heart me-2"></i>
                            Añadir a favoritos
                        </a>

                    <?php else: ?>

                        <a
                            href="favoritos.php"
                            class="btn btn-danger btn-lg"
                        >
                            <i class="bi bi-heart-fill me-2"></i>
                            Ya está en favoritos
                        </a>

                    <?php endif; ?>

                </div>
            </form>

            <hr class="border-secondary my-4">

            <h4 class="text-white">
                Descripción del producto
            </h4>

            <p class="text-white-50">
                <?php echo !empty($producto['descripcion'])
                    ? $producto['descripcion']
                    : 'Producto premium con tecnología NFC diseñado para eventos modernos y control inteligente de accesos.';
                ?>
            </p>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>