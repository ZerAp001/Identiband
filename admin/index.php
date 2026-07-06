<?php
include 'includes/auth.php';
include '../includes/db.php';

/* KPIs */

$totalUsuarios = mysqli_fetch_assoc(
    mysqli_query($conexion,
    "SELECT COUNT(*) total FROM usuarios")
)['total'];

$totalProductos = mysqli_fetch_assoc(
    mysqli_query($conexion,
    "SELECT COUNT(*) total FROM productos")
)['total'];

$totalPedidos = mysqli_fetch_assoc(
    mysqli_query($conexion,
    "SELECT COUNT(*) total FROM pedidos")
)['total'];

$ingresos = mysqli_fetch_assoc(
    mysqli_query($conexion,
    "SELECT SUM(monto_total) total
     FROM ventas
     WHERE estado_pago='completado'")
)['total'] ?? 0;

/* PEDIDOS */

$pendientes = mysqli_fetch_assoc(
    mysqli_query($conexion,
    "SELECT COUNT(*) total
     FROM pedidos
     WHERE estado='Pendiente'")
)['total'];

$enviados = mysqli_fetch_assoc(
    mysqli_query($conexion,
    "SELECT COUNT(*) total
     FROM pedidos
     WHERE estado='Enviado'")
)['total'];

$entregados = mysqli_fetch_assoc(
    mysqli_query($conexion,
    "SELECT COUNT(*) total
     FROM pedidos
     WHERE estado='Entregado'")
)['total'];

/* GRÁFICA INGRESOS */

$ventasMes = mysqli_query($conexion, "

SELECT

MONTH(fecha_venta) mes,
SUM(monto_total) total

FROM ventas

WHERE estado_pago='completado'

GROUP BY MONTH(fecha_venta)

ORDER BY MONTH(fecha_venta)

");

$meses = [];
$totales = [];

while($row = mysqli_fetch_assoc($ventasMes)){

    $meses[] = $row['mes'];
    $totales[] = $row['total'];
}

/* PRODUCTOS TOP */

$topProductos = mysqli_query($conexion, "

SELECT
productos.nombre_modelo,
SUM(detalle_ventas.cantidad) total

FROM detalle_ventas

INNER JOIN productos
ON detalle_ventas.id_producto = productos.id_producto

GROUP BY productos.id_producto

ORDER BY total DESC

LIMIT 5

");

$nombresProductos = [];
$ventasProductos = [];

while($row = mysqli_fetch_assoc($topProductos)){

    $nombresProductos[] = $row['nombre_modelo'];
    $ventasProductos[] = $row['total'];
}

include 'includes/header.php';
?>

<div class="container-fluid">

    <!-- TITULO -->

    <div class="mb-5">

        <h1 class="admin-title">
            Dashboard
        </h1>

        <p class="admin-subtitle">
            Estadísticas generales de IDENTIBAND
        </p>

    </div>

    <!-- CARDS -->

    <div class="row g-4">

        <div class="col-xl-3 col-md-6">

            <div class="admin-card stats-card border-primary-custom">

                <div class="stats-icon text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div class="stats-info">

                    <h6>Usuarios</h6>

                    <h2><?= $totalUsuarios ?></h2>

                </div>

            </div>

        </div>

        <div class="col-xl-3 col-md-6">

            <div class="admin-card stats-card border-success-custom">

                <div class="stats-icon text-success">
                    <i class="bi bi-box-seam"></i>
                </div>

                <div class="stats-info">

                    <h6>Productos</h6>

                    <h2><?= $totalProductos ?></h2>

                </div>

            </div>

        </div>

        <div class="col-xl-3 col-md-6">

            <div class="admin-card stats-card border-warning-custom">

                <div class="stats-icon text-warning">
                    <i class="bi bi-bag-fill"></i>
                </div>

                <div class="stats-info">

                    <h6>Pedidos</h6>

                    <h2><?= $totalPedidos ?></h2>

                </div>

            </div>

        </div>

        <div class="col-xl-3 col-md-6">

            <div class="admin-card stats-card border-info-custom">

                <div class="stats-icon text-info">
                    <i class="bi bi-currency-dollar"></i>
                </div>

                <div class="stats-info">

                    <h6>Ingresos</h6>

                    <h2>
                        $<?= number_format($ingresos,2) ?>
                    </h2>

                </div>

            </div>

        </div>

    </div>

    <!-- ESTADOS -->

    <div class="row g-4 mt-1">

        <div class="col-lg-4">

            <div class="admin-card">

                <h5 class="mb-4">
                    Estado de pedidos
                </h5>

                <div class="status-box">

                    <div>
                        <span class="status-dot bg-warning"></span>
                        Pendientes
                    </div>

                    <strong><?= $pendientes ?></strong>

                </div>

                <div class="status-box">

                    <div>
                        <span class="status-dot bg-info"></span>
                        Enviados
                    </div>

                    <strong><?= $enviados ?></strong>

                </div>

                <div class="status-box border-0">

                    <div>
                        <span class="status-dot bg-success"></span>
                        Entregados
                    </div>

                    <strong><?= $entregados ?></strong>

                </div>

            </div>

        </div>

        <!-- CHART -->

        <div class="col-lg-8">

            <div class="admin-card">

                <h5 class="mb-4">
                    Ingresos mensuales
                </h5>

                <canvas id="ventasChart"></canvas>

            </div>

        </div>

    </div>

    <!-- PRODUCTOS TOP -->

    <div class="row mt-4">

        <div class="col-lg-6">

            <div class="admin-card">

                <h5 class="mb-4">
                    Productos más vendidos
                </h5>

                <canvas id="productosChart"></canvas>

            </div>

        </div>

        <!-- RESUMEN -->

        <div class="col-lg-6">

            <div class="admin-card h-100">

                <h5 class="mb-4">
                    Resumen rápido
                </h5>

                <div class="summary-item">

                    <span>
                        Ingreso promedio
                    </span>

                    <strong class="text-success">

                        $<?= number_format(
                            $ingresos / max($totalPedidos,1),
                            2
                        ) ?>

                    </strong>

                </div>

                <div class="summary-item">

                    <span>
                        Productos registrados
                    </span>

                    <strong class="text-info">

                        <?= $totalProductos ?>

                    </strong>

                </div>

                <div class="summary-item">

                    <span>
                        Clientes registrados
                    </span>

                    <strong class="text-primary">

                        <?= $totalUsuarios ?>

                    </strong>

                </div>

                <div class="summary-item border-0">

                    <span>
                        Pedidos completados
                    </span>

                    <strong class="text-warning">

                        <?= $entregados ?>

                    </strong>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- CHARTS -->

<script>

const ventasCtx =
document.getElementById('ventasChart');

new Chart(ventasCtx, {

    type: 'line',

    data: {

        labels:
        <?= json_encode($meses) ?>,

        datasets: [{

            label: 'Ingresos',

            data:
            <?= json_encode($totales) ?>,

            tension: 0.4,

            fill: true,

            borderWidth: 3,

            pointRadius: 5

        }]
    },

    options: {

        responsive: true,

        plugins: {

            legend: {

                labels: {
                    color: 'white'
                }
            }
        },

        scales: {

            x: {

                ticks: {
                    color: 'white'
                }
            },

            y: {

                ticks: {
                    color: 'white'
                }
            }
        }
    }
});

/* PRODUCTOS */

const productosCtx =
document.getElementById('productosChart');

new Chart(productosCtx, {

    type: 'doughnut',

    data: {

        labels:
        <?= json_encode($nombresProductos) ?>,

        datasets: [{

            data:
            <?= json_encode($ventasProductos) ?>,

            borderWidth: 0

        }]
    },

    options: {

        responsive: true,

        plugins: {

            legend: {

                labels: {
                    color: 'white'
                }
            }
        }
    }
});

</script>

<?php include 'includes/footer.php'; ?>