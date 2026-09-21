<?php
include '../includes/auth.php';
include '../../includes/db.php';
include '../includes/header.php';

/* BUSCADOR */
$busqueda = $_GET['buscar'] ?? '';
$where = '';

if ($busqueda !== '') {
    $busqueda = mysqli_real_escape_string($conexion, $busqueda);
    $where = " WHERE u.nombre LIKE '%$busqueda%' OR u.apellidos LIKE '%$busqueda%' OR u.email LIKE '%$busqueda%' ";
}

/* QUERY OPTIMIZADA (Sustituye la consulta con producto cartesiano) */
$querySql = "
SELECT 
    u.*,
    COALESCE(p.total_pedidos, 0) AS total_pedidos,
    COALESCE(v.total_gastado, 0) AS total_gastado
FROM usuarios u
LEFT JOIN (
    SELECT id_usuario, COUNT(id_pedido) AS total_pedidos 
    FROM pedidos 
    GROUP BY id_usuario
) p ON u.id_usuario = p.id_usuario
LEFT JOIN (
    SELECT id_usuario, SUM(monto_total) AS total_gastado 
    FROM ventas 
    WHERE estado_pago = 'completado' 
    GROUP BY id_usuario
) v ON u.id_usuario = v.id_usuario
$where
ORDER BY u.id_usuario DESC
";

$query = mysqli_query($conexion, $querySql);
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="admin-title">Gestión de Clientes (CRM)</h1>
        <p class="admin-subtitle">Segmentación y fidelización de usuarios</p>
    </div>

    <!-- BUSCADOR -->
    <form method="GET" class="search-admin">
        <i class="bi bi-search"></i>
        <input 
            type="text" 
            name="buscar" 
            placeholder="Buscar por nombre o correo..." 
            value="<?= htmlspecialchars($busqueda) ?>"
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
                    <th>Puntos</th>
                    <th>Segmento CRM</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while($usuario = mysqli_fetch_assoc($query)): ?>
                <?php 
                $inicial = strtoupper(substr($usuario['nombre'], 0, 1));
                $gastado = (float)$usuario['total_gastado'];
                
                // Lógica de segmentación de clientes
                if ($gastado >= 3000) {
                    $segmentoLabel = 'Cliente VIP';
                    $segmentoClass = 'badge-danger'; // O una clase personalizada dorada/purpura
                } elseif ($gastado > 0 && $usuario['total_pedidos'] >= 2) {
                    $segmentoLabel = 'Frecuente';
                    $segmentoClass = 'badge-success';
                } elseif ($gastado > 0) {
                    $segmentoLabel = 'Comprador';
                    $segmentoClass = 'badge-info';
                } else {
                    $segmentoLabel = 'Prospecto';
                    $segmentoClass = 'badge-warning';
                }
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
                                    <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>
                                </strong>
                                <div class="user-phone">
                                    <?= htmlspecialchars($usuario['telefono'] ?: 'Sin teléfono') ?>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- EMAIL -->
                    <td>
                        <?= htmlspecialchars($usuario['email']) ?>
                    </td>

                    <!-- PEDIDOS -->
                    <td>
                        <span class="badge-status badge-info">
                            <?= $usuario['total_pedidos'] ?> pedidos
                        </span>
                    </td>

                    <!-- GASTADO -->
                    <td class="text-success fw-bold">
                        $<?= number_format($usuario['total_gastado'], 2) ?>
                    </td>

                    <!-- PUNTOS -->
                    <td>
                        <span class="fw-bold text-primary">
                            <?= number_format($usuario['puntos_fidelidad'] ?? 0) ?> pts
                        </span>
                    </td>

                    <!-- SEGMENTO CRM -->
                    <td>
                        <span class="badge-status <?= $segmentoClass ?>">
                            <?= $segmentoLabel ?>
                        </span>
                    </td>

                    <!-- FECHA -->
                    <td>
                        <?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?>
                    </td>

                    <!-- DETALLES -->
                    <td>
                      <a href="detalle.php?id=<?= $usuario['id_usuario'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Ficha CRM">
                         <i class="bi bi-eye"></i> Detalle
                         </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>