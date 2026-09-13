<?php 
include 'includes/head_general.php'; 
include 'includes/header.php'; 
include 'includes/db.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) { 
    echo "<script>window.location='login.php';</script>"; 
    exit; 
}

// Consulta para obtener pedidos
$query_pedidos = "SELECT * FROM pedidos WHERE id_usuario = $usuario_id ORDER BY fecha_pedido DESC";
$res_pedidos = mysqli_query($conexion, $query_pedidos);
?>

<div class="container py-5" style="min-height: 80vh;">
    <div class="row mt-5">
        <div class="col-12 border-bottom border-secondary mb-4 pb-2">
            <h2 class="fw-bold text-info"><i class="bi bi-bag-check-fill me-2"></i> Mis Pedidos</h2>
            <p class="text-white-50">Rastrea el avance de tus pulseras IDENTIBAND en tiempo real.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if($res_pedidos && mysqli_num_rows($res_pedidos) > 0): ?>
                <?php while($ped = mysqli_fetch_assoc($res_pedidos)): 
                    $estado_raw = strtolower(trim($ped['estado']));
                    $paso_actual = 1;
                    $badge_class = "bg-warning text-dark";
                    $texto_estado = "Pendiente";

                    // Determinación de paso y badge visual
                    if ($estado_raw == 'pendiente' || $estado_raw == 'recibido' || $estado_raw == 'pagado') {
                        $paso_actual = 1;
                        $texto_estado = "Recibido";
                        $badge_class = "bg-info text-dark";
                    } elseif ($estado_raw == 'en preparación' || $estado_raw == 'procesando') {
                        $paso_actual = 2;
                        $texto_estado = "En Preparación";
                        $badge_class = "bg-warning text-dark";
                    } elseif ($estado_raw == 'en camino' || $estado_raw == 'enviado') {
                        $paso_actual = 3;
                        $texto_estado = "En Camino";
                        $badge_class = "bg-primary text-white";
                    } elseif ($estado_raw == 'entregado') {
                        $paso_actual = 4;
                        $texto_estado = "Entregado";
                        $badge_class = "bg-success text-white";
                    }
                ?>
                    <!-- CARD POR PEDIDO -->
                    <div class="card bg-dark border-secondary mb-4 shadow-lg">
                        <div class="card-header bg-dark border-secondary d-flex flex-wrap justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="fw-bold text-info fs-5">Pedido #IB-<?php echo $ped['id_pedido']; ?></span>
                                <span class="text-white-50 small">
                                    <i class="bi bi-calendar3 me-1"></i><?php echo date('d/m/Y', strtotime($ped['fecha_pedido'])); ?>
                                </span>
                                <span class="badge <?php echo $badge_class; ?> px-3 py-2 fw-semibold">
                                    <?php echo $texto_estado; ?>
                                </span>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <span class="text-white me-3">Total: <strong class="text-info fs-5">$<?php echo number_format($ped['total'], 2); ?></strong></span>
                                <a href="detalle_pedido.php?id=<?php echo $ped['id_pedido']; ?>" class="btn btn-outline-info btn-sm fw-semibold">
                                    <i class="bi bi-eye me-1"></i> Ver detalles
                                </a>
                            </div>
                        </div>

                        <div class="card-body py-4">
                            <!-- LÍNEA DE TIEMPO HORIZONTAL -->
                            <div class="order-tracker">
                                <!-- Paso 1 -->
                                <div class="tracker-step <?php echo ($paso_actual > 1) ? 'completed' : (($paso_actual == 1) ? 'active' : ''); ?>">
                                    <div class="tracker-icon">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div class="tracker-text">Recibido</div>
                                </div>
                                
                                <!-- Paso 2 -->
                                <div class="tracker-step <?php echo ($paso_actual > 2) ? 'completed' : (($paso_actual == 2) ? 'active' : ''); ?>">
                                    <div class="tracker-icon">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div class="tracker-text">En Preparación</div>
                                </div>
                                
                                <!-- Paso 3 -->
                                <div class="tracker-step <?php echo ($paso_actual > 3) ? 'completed' : (($paso_actual == 3) ? 'active' : ''); ?>">
                                    <div class="tracker-icon">
                                        <i class="bi bi-truck"></i>
                                    </div>
                                    <div class="tracker-text">En Camino</div>
                                </div>
                                
                                <!-- Paso 4 -->
                                <div class="tracker-step <?php echo ($paso_actual == 4) ? 'completed active' : ''; ?>">
                                    <div class="tracker-icon">
                                        <i class="bi bi-house-check-fill"></i>
                                    </div>
                                    <div class="tracker-text">Entregado</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card bg-dark border-secondary p-5 text-center text-white-50">
                    <i class="bi bi-cart-x display-4 d-block mb-3 text-info"></i>
                    <h5 class="text-white fw-bold">Todavía no has realizado ninguna compra</h5>
                    <p class="mb-3">Explora nuestro catálogo y adquiere tus pulseras NFC hoy mismo.</p>
                    <div>
                        <a href="catalogo.php" class="btn btn-info fw-bold">Ir al catálogo</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* CSS para la línea de tiempo */
.order-tracker {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
    margin: 20px 0 10px 0;
}

/* Línea conectora trasera */
.order-tracker::before {
    content: '';
    position: absolute;
    top: 22px;
    left: 12%;
    right: 12%;
    height: 4px;
    background-color: #333a42;
    z-index: 1;
}

.tracker-step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}

.tracker-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background-color: #161b22;
    border: 2px solid #30363d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px auto;
    color: #6e7681;
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.tracker-text {
    font-size: 0.85rem;
    color: #8b949e;
    font-weight: 500;
}

/* Paso completado (Pasos anteriores) */
.tracker-step.completed .tracker-icon {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #ffffff;
}

.tracker-step.completed .tracker-text {
    color: #0d6efd;
    font-weight: 600;
}

/* Paso actual activo */
.tracker-step.active .tracker-icon {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
    color: #000000;
    box-shadow: 0 0 15px rgba(13, 202, 240, 0.6);
    transform: scale(1.1);
}

.tracker-step.active .tracker-text {
    color: #0dcaf0;
    font-weight: bold;
}
</style>

<?php include 'includes/footer.php'; ?>