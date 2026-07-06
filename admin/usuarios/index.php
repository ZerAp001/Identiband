<?php
include '../includes/auth.php';
include '../../includes/db.php';
include '../includes/header.php';

/* BUSCADOR */

$busqueda = $_GET['buscar'] ?? '';

$where = '';

if($busqueda != ''){

    $busqueda = mysqli_real_escape_string(
        $conexion,
        $busqueda
    );

    $where = "
    WHERE usuarios.nombre LIKE '%$busqueda%'
    OR usuarios.email LIKE '%$busqueda%'
    ";
}

/* QUERY */

$query = mysqli_query($conexion, "

SELECT

    usuarios.*,

    COUNT(pedidos.id_pedido) total_pedidos,

    COALESCE(SUM(ventas.monto_total),0)
    AS total_gastado

FROM usuarios

LEFT JOIN pedidos
ON usuarios.id_usuario = pedidos.id_usuario

LEFT JOIN ventas
ON usuarios.id_usuario = ventas.id_usuario
AND ventas.estado_pago='completado'

$where

GROUP BY usuarios.id_usuario

ORDER BY usuarios.id_usuario DESC

");
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

    <div>

        <h1 class="admin-title">
            Usuarios
        </h1>

        <p class="admin-subtitle">
            Gestión completa de clientes
        </p>

    </div>

    <!-- BUSCADOR -->

    <form method="GET" class="search-admin">

        <i class="bi bi-search"></i>

        <input
            type="text"
            name="buscar"
            placeholder="Buscar usuario..."
            value="<?= $_GET['buscar'] ?? '' ?>"
        >

    </form>

</div>

<div class="admin-card">

    <div class="table-responsive">

        <table class="table admin-table align-middle">

            <thead>

                <tr>

                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Pedidos</th>
                    <th>Total gastado</th>
                    <th>Registro</th>
                    <th>Estado</th>

                </tr>

            </thead>

            <tbody>

            <?php while($usuario = mysqli_fetch_assoc($query)): ?>

                <?php
                $inicial = strtoupper(
                    substr($usuario['nombre'],0,1)
                );
                ?>

                <tr>

                    <!-- USUARIO -->

                    <td>

                        <div class="user-box">

                            <div class="user-avatar">

                                <?= $inicial ?>

                            </div>

                            <div>

                                <strong>

                                    <?= $usuario['nombre'] ?>
                                    <?= $usuario['apellidos'] ?>

                                </strong>

                                <div class="user-phone">

                                    <?= $usuario['telefono']
                                    ?: 'Sin teléfono' ?>

                                </div>

                            </div>

                        </div>

                    </td>

                    <!-- EMAIL -->

                    <td>

                        <?= $usuario['email'] ?>

                    </td>

                    <!-- PEDIDOS -->

                    <td>

                        <span class="badge-status badge-info">

                            <?= $usuario['total_pedidos'] ?>
                            pedidos

                        </span>

                    </td>

                    <!-- GASTADO -->

                    <td class="text-success fw-bold">

                        $<?= number_format(
                            $usuario['total_gastado'],
                            2
                        ) ?>

                    </td>

                    <!-- FECHA -->

                    <td>

                        <?= date(
                            'd/m/Y',
                            strtotime(
                                $usuario['fecha_registro']
                            )
                        ) ?>

                    </td>

                    <!-- ESTADO -->

                    <td>

                        <?php if($usuario['total_gastado'] > 0): ?>

                            <span class="badge-status badge-success">
                                Cliente activo
                            </span>

                        <?php else: ?>

                            <span class="badge-status badge-warning">
                                Sin compras
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include '../includes/footer.php'; ?>