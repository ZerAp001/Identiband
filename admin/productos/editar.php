<?php
include '../includes/auth.php';
include '../../includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$query = mysqli_query(
    $conexion,
    "SELECT * FROM productos
     WHERE id_producto = $id"
);

if (!$query || mysqli_num_rows($query) == 0) {
    header("Location: index.php");
    exit;
}

$producto = mysqli_fetch_assoc($query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = mysqli_real_escape_string(
        $conexion,
        $_POST['nombre']
    );

    $descripcion = mysqli_real_escape_string(
        $conexion,
        $_POST['descripcion']
    );

    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $tipo = $_POST['tipo'];

  $imagen = $_POST['imagen'];

    mysqli_query(
        $conexion,
        "UPDATE productos
        SET
            nombre_modelo = '$nombre',
            descripcion = '$descripcion',
            precio = '$precio',
            stock = '$stock',
            tipo = '$tipo',
            imagen_url = '$imagen'
        WHERE id_producto = $id"
    );

    header("Location: index.php");
    exit;
}

include '../includes/header.php';
?>

<h1 class="admin-title mb-4">
    Editar producto
</h1>

<div class="admin-card">

<form method="POST" enctype="multipart/form-data">

    <div class="text-center mb-4">

        <img
            src="../../assets/<?= $producto['imagen_url'] ?>"
            width="160"
            class="rounded"
        >

    </div>

    <div class="mb-3">

        <label class="form-label">
            Nombre
        </label>

        <input
            type="text"
            name="nombre"
            class="form-control"
            value="<?= $producto['nombre_modelo'] ?>"
            required
        >

    </div>

    <div class="mb-3">

        <label class="form-label">
            Descripción
        </label>

        <textarea
            name="descripcion"
            class="form-control"
            rows="4"
            required
        ><?= $producto['descripcion'] ?></textarea>

    </div>

    <div class="row">

        <div class="col-md-4 mb-3">

            <label class="form-label">
                Precio
            </label>

            <input
                type="number"
                step="0.01"
                name="precio"
                class="form-control"
                value="<?= $producto['precio'] ?>"
                required
            >

        </div>

        <div class="col-md-4 mb-3">

            <label class="form-label">
                Stock
            </label>

            <input
                type="number"
                name="stock"
                class="form-control"
                value="<?= $producto['stock'] ?>"
                required
            >

        </div>

        <div class="col-md-4 mb-3">

            <label class="form-label">
                Tipo
            </label>

            <select
                name="tipo"
                class="form-select"
            >

                <option
                    value="Individual"
                    <?= $producto['tipo'] == 'Individual' ? 'selected' : '' ?>
                >
                    Individual
                </option>

                <option
                    value="Paquete"
                    <?= $producto['tipo'] == 'Paquete' ? 'selected' : '' ?>
                >
                    Paquete
                </option>

                <option
                    value="Premium"
                    <?= $producto['tipo'] == 'Premium' ? 'selected' : '' ?>
                >
                    Premium
                </option>

            </select>

        </div>

    </div>

 <div class="mb-4">

    <label class="form-label mb-3">
        Seleccionar imagen
    </label>

    <div class="image-selector">

        <?php
        $imagenes = glob('../../assets/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

        foreach($imagenes as $img):

            $nombre = basename($img);

            $checked = ($producto['imagen_url'] == $nombre)
                ? 'checked'
                : '';
        ?>

            <label class="image-option">

                <input
                    type="radio"
                    name="imagen"
                    value="<?= $nombre ?>"
                    <?= $checked ?>
                >

                <img
                    src="../../assets/<?= $nombre ?>"
                    alt="<?= $nombre ?>"
                >

                <span><?= $nombre ?></span>

            </label>

        <?php endforeach; ?>

    </div>

</div>

    <button class="btn-admin">
        Guardar cambios
    </button>

</form>

</div>

<?php include '../includes/footer.php'; ?>