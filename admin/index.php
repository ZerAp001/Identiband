<?php
include 'includes/auth.php';
include '../includes/db.php';

/* KPIs */
// --- KPIs SIMPLES ---
// Usamos query() porque son consultas fijas, no necesitan prepare()
$totalUsuarios = $conexion->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalProductos = $conexion->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$totalPedidos = $conexion->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$ingresos = $conexion->query("SELECT SUM(monto_total) FROM ventas WHERE estado_pago='completado'")->fetchColumn() ?? 0;

/* PEDIDOS */
$pendientes = $conexion->query("SELECT COUNT(*) FROM pedidos WHERE estado='Pendiente'")->fetchColumn();
$enviados = $conexion->query("SELECT COUNT(*) FROM pedidos WHERE estado='Enviado'")->fetchColumn();
$entregados = $conexion->query("SELECT COUNT(*) FROM pedidos WHERE estado='Entregado'")->fetchColumn();

/* GRÁFICA INGRESOS */
$ventasMes = $conexion->query("SELECT MONTH(fecha_venta) as mes, SUM(monto_total) as total FROM ventas WHERE estado_pago='completado' GROUP BY MONTH(fecha_venta) ORDER BY MONTH(fecha_venta)")->fetchAll(PDO::FETCH_ASSOC);

$meses = [];
$totales = [];
foreach ($ventasMes as $row) {
    $meses[] = $row['mes'];
    $totales[] = $row['total'];
}

/* PRODUCTOS TOP */
$topProductos = $conexion->query("SELECT productos.nombre_modelo, SUM(detalle_ventas.cantidad) as total FROM detalle_ventas INNER JOIN productos ON detalle_ventas.id_producto = productos.id_producto GROUP BY productos.id_producto ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$nombresProductos = [];
$ventasProductos = [];
foreach ($topProductos as $row) {
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