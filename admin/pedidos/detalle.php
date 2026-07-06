<?php
include '../includes/auth.php';
include '../../includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$query = mysqli_query($conexion, "

SELECT
    pedidos.*,
    usuarios.nombre,
    usuarios.apellidos,
    usuarios.email

FROM pedidos

INNER JOIN usuarios
ON pedidos.id_usuario = usuarios.id_usuario

WHERE pedidos.id_pedido = $id

");

if(mysqli_num_rows($query) == 0){
    header("Location: index.php");
    exit;
}

$pedido = mysqli_fetch_assoc($query);

include '../includes/header.php';
?>

<div class="mb-4">

    <h1 class="admin-title">
        Pedido #<?= $pedido['id_pedido'] ?>
    </h1>

    <p class="admin-subtitle">
        Información detallada del pedido
    </p>

</div>

<div class="row g-4">

    <div class="col-lg-6">

        <div class="admin-card">

            <h4 class="mb-4">
                Cliente
            </h4>

            <p>
                <strong>Nombre:</strong>
                <?= $pedido['nombre'] ?>
                <?= $pedido['apellidos'] ?>
            </p>

            <p>
                <strong>Email:</strong>
                <?= $pedido['email'] ?>
            </p>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="admin-card">

            <h4 class="mb-4">
                Pedido
            </h4>

            <p>

                <strong>Total:</strong>

                $<?= number_format($pedido['total'],2) ?>

            </p>

            <p>

                <strong>Estado:</strong>

                <?= $pedido['estado'] ?>

            </p>

            <p>

                <strong>Fecha:</strong>

                <?= $pedido['fecha_pedido'] ?>

            </p>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>