<?php
include '../includes/auth.php';
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $tipo = $_POST['tipo'];

    $imagen = $_POST['imagen'];

    mysqli_query(
        $conexion,
        "INSERT INTO productos
        (
            nombre_modelo,
            descripcion,
            precio,
            stock,
            tipo,
            imagen_url
        )
        VALUES
        (
            '$nombre',
            '$descripcion',
            '$precio',
            '$stock',
            '$tipo',
            '$imagen'
        )"
    );

    header("Location: index.php");
    exit;
}

include '../includes/header.php';
?>

<h1 class="admin-title mb-4">
    Nuevo producto
</h1>

<div class="admin-card">

<form method="POST" enctype="multipart/form-data">

    <div class="mb-3">
        <label class="form-label">Nombre</label>

        <input
            type="text"
            name="nombre"
            class="form-control"
            required
        >
    </div>

    <div class="mb-3">
        <label class="form-label">Descripción</label>

        <textarea
            name="descripcion"
            class="form-control"
            rows="4"
        ></textarea>
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
                <option>Individual</option>
                <option>Paquete</option>
                <option>Premium</option>
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
        ?>

            <label class="image-option">

                <input
                    type="radio"
                    name="imagen"
                    value="<?= $nombre ?>"
                    required
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
        <i class="bi bi-check-circle"></i>
        Guardar producto
    </button>

</form>

</div>

<?php include '../includes/footer.php'; ?>