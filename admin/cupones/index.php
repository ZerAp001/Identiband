<?php
include '../includes/auth.php';
include '../../includes/db.php';
include '../includes/header.php';

/* OBTENER CUPONES */
$query = mysqli_query($conexion, "
    SELECT * 
    FROM cupones 
    ORDER BY id_cupon DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="admin-title">Cupones de Descuento</h1>
        <p class="admin-subtitle">Gestión de códigos promocionales y fidelización</p>
    </div>
    <div>
        <a href="nuevo.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Crear Cupón
        </a>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Descuento</th>
                    <th>Mínimo Compra</th>
                    <th>Usos</th>
                    <th>Expiración</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while($cupon = mysqli_fetch_assoc($query)): ?>
                <?php
                $hoy = date('Y-m-d H:i:s');
                $expirado = $cupon['fecha_expiracion'] < $hoy;
                $agotado = !is_null($cupon['usos_maximos']) && $cupon['usos_actuales'] >= $cupon['usos_maximos'];
                ?>
                <tr>
                    <td>
                        <strong class="text-uppercase tracking-wider">
                            <?= htmlspecialchars($cupon['codigo']) ?>
                        </strong>
                    </td>
                    <td>
                        <?= $cupon['tipo_descuento'] === 'porcentaje' ? 'Porcentaje (%)' : 'Monto Fijo ($)' ?>
                    </td>
                    <td class="fw-bold text-success">
                        <?= $cupon['tipo_descuento'] === 'porcentaje' 
                            ? number_format($cupon['valor_descuento'], 0) . '%' 
                            : '$' . number_format($cupon['valor_descuento'], 2) ?>
                    </td>
                    <td>
                        $<?= number_format($cupon['monto_minimo_compra'], 2) ?>
                    </td>
                    <td>
                        <?= $cupon['usos_actuales'] ?> / <?= $cupon['usos_maximos'] ?? '∞' ?>
                    </td>
                    <td>
                        <?= date('d/m/Y', strtotime($cupon['fecha_expiracion'])) ?>
                    </td>
                    <td>
                        <?php if ($cupon['estado'] === 'inactivo'): ?>
                            <span class="badge-status badge-warning">Inactivo</span>
                        <?php elseif ($expirado): ?>
                            <span class="badge-status badge-danger">Expirado</span>
                        <?php elseif ($agotado): ?>
                            <span class="badge-status badge-secondary">Agotado</span>
                        <?php else: ?>
                            <span class="badge-status badge-success">Activo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="cambiar_estado.php?id=<?= $cupon['id_cupon'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-repeat"></i> <?= $cupon['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>