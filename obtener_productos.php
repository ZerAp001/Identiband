<?php
include 'includes/db.php';

$orden = $_GET['orden'] ?? 'default';

$order_sql = "ORDER BY p.id_producto DESC";
switch ($orden) {
    case 'mas_valorados':
        $order_sql = "ORDER BY promedio_calificacion DESC, total_resenas DESC, p.id_producto DESC";
        break;
    case 'precio_asc':
        $order_sql = "ORDER BY p.precio ASC";
        break;
    case 'precio_desc':
        $order_sql = "ORDER BY p.precio DESC";
        break;
}

$query = "SELECT p.*, 
                 COALESCE(AVG(r.calificacion), 0) AS promedio_calificacion, 
                 COUNT(r.id_resena) AS total_resenas 
          FROM productos p 
          LEFT JOIN resenas r ON p.id_producto = r.id_producto 
          GROUP BY p.id_producto 
          $order_sql";

$resultado = mysqli_query($conexion, $query);

if ($resultado) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $promedio = round($row['promedio_calificacion'], 1);
        $total_resenas = $row['total_resenas'];
        $requiere_personalizacion = (
            stripos($row['nombre_modelo'], 'Personalizable') !== false ||
            stripos($row['nombre_modelo'], 'Premium') !== false ||
            stripos($row['nombre_modelo'], '1 color') !== false
        );
?>
<div class="col mb-5 product-item <?php echo $row['tipo']; ?>" style="display: block;">
    <div class="card h-100 border-secondary bg-dark text-white product-card">
        <div class="badge badge-tipo bg-info text-dark position-absolute" style="top: 0.5rem; right: 0.5rem">
            <?php echo $row['tipo']; ?>
        </div>
        
        <img class="card-img-top" src="assets/<?php echo $row['imagen_url']; ?>" 
             onerror="this.src='assets/pulsera.png'" alt="Producto Identiband" />

        <div class="card-body p-4 text-center">
            <h5 class="fw-bolder"><?php echo $row['nombre_modelo']; ?></h5>
            
            <div class="mb-2">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= round($promedio)) {
                        echo '<i class="bi bi-star-fill text-warning me-1 small"></i>';
                    } else {
                        echo '<i class="bi bi-star text-secondary me-1 small"></i>';
                    }
                }
                ?>
                <span class="small text-white-50">(<?php echo $total_resenas; ?>)</span>
            </div>

            <span class="text-info fs-5">$<?php echo number_format($row['precio'], 2); ?></span>
            <p class="small text-white-50 mt-2"><?php echo $row['variante_info'] ?? $row['descripcion']; ?></p>
        </div>
        
        <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
            <div class="text-center d-grid gap-2">
                <?php if ($requiere_personalizacion): ?>
                    <a class="btn btn-identi mt-auto fw-bold" href="detalle_producto.php?id=<?php echo $row['id_producto']; ?>">
                        Personalizar y comprar
                    </a>
                <?php else: ?>
                    <a class="btn btn-identi mt-auto fw-bold" href="procesar_carrito.php?id=<?php echo $row['id_producto']; ?>">
                        Agregar al carrito
                    </a>
                <?php endif; ?>
                <a class="btn btn-outline-light btn-sm" href="detalle_producto.php?id=<?php echo $row['id_producto']; ?>">Ver detalles</a>
            </div>
        </div>
    </div>
</div>
<?php 
    }
} 
?>