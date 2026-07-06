<?php
include '../includes/auth.php';
include '../../includes/db.php';
include '../includes/header.php';

/* FILTROS */

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-d');

/* CONSULTA */

$query = mysqli_query($conexion, "

SELECT
    pedidos.id_pedido,
    usuarios.nombre,
    usuarios.apellidos,
    pedidos.total,
    pedidos.estado,
    pedidos.fecha_pedido

FROM pedidos

INNER JOIN usuarios
ON pedidos.id_usuario = usuarios.id_usuario

WHERE DATE(pedidos.fecha_pedido)
BETWEEN '$inicio' AND '$fin'

ORDER BY pedidos.fecha_pedido DESC

");

/* KPIs */

$totalVentas = 0;
$totalPedidos = 0;

$pedidos = [];

while($row = mysqli_fetch_assoc($query)){

    $pedidos[] = $row;

    $totalVentas += $row['total'];

    $totalPedidos++;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h1 class="admin-title">
            Reportes
        </h1>

        <p class="admin-subtitle">
            Generación de reportes PDF
        </p>

    </div>

    <a
        href="generar_pdf.php?inicio=<?= $inicio ?>&fin=<?= $fin ?>"
        class="btn-admin"
        target="_blank"
    >
        <i class="bi bi-file-earmark-pdf-fill"></i>
        Descargar PDF
    </a>

</div>

<!-- FILTROS -->

<form method="GET" class="row g-3 mb-4">

    <div class="col-md-4">

        <label class="form-label text-light">
            Fecha inicio
        </label>

        <input
            type="date"
            name="inicio"
            class="form-control admin-input"
            value="<?= $inicio ?>"
        >

    </div>

    <div class="col-md-4">

        <label class="form-label text-light">
            Fecha fin
        </label>

        <input
            type="date"
            name="fin"
            class="form-control admin-input"
            value="<?= $fin ?>"
        >

    </div>

    <div class="col-md-4 d-flex align-items-end">

        <button class="btn btn-primary w-100">
            🔍 Generar reporte
        </button>

    </div>

</form>

<!-- KPIs -->

<div class="row g-4 mb-4">

    <div class="col-md-6">

        <div class="admin-card">

            <h5 class="text-info">
                Total de pedidos
            </h5>

            <h2>
                <?= $totalPedidos ?>
            </h2>

        </div>

    </div>

    <div class="col-md-6">

        <div class="admin-card">

            <h5 class="text-success">
                Ingresos generados
            </h5>

            <h2>
                $<?= number_format($totalVentas, 2) ?>
            </h2>

        </div>

    </div>

</div>

<!-- TABLA -->

<div class="admin-card">

    <div class="table-responsive">

        <table class="table admin-table align-middle">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach($pedidos as $pedido): ?>

                    <tr>

                        <td>
                            #<?= $pedido['id_pedido'] ?>
                        </td>

                        <td>

                            <?= $pedido['nombre'] ?>
                            <?= $pedido['apellidos'] ?>

                        </td>

                        <td class="fw-bold text-success">

                            $<?= number_format($pedido['total'], 2) ?>

                        </td>

                        <td>

                            <?= $pedido['estado'] ?>

                        </td>

                        <td>

                            <?= date(
                                'd/m/Y H:i',
                                strtotime($pedido['fecha_pedido'])
                            ) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include '../includes/footer.php'; ?>