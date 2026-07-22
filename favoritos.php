<?php 
include 'includes/head_general.php';
include 'includes/header.php';
include 'includes/db.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) { 
    header("Location: login.php");
    exit; 
}

// 1. Preparamos la consulta con JOIN y el marcador de posición
$stmt = $conexion->prepare("
    SELECT f.*, p.nombre_modelo, p.precio, p.imagen_url, p.variante_info
    FROM favoritos f
    JOIN productos p ON f.id_producto = p.id_producto
    WHERE f.id_usuario = :usuario_id
");

// 2. Ejecutamos pasando el ID del usuario de forma segura
$stmt->execute(['usuario_id' => $usuario_id]);

// 3. Obtenemos todos los favoritos
$favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5" style="min-height: 80vh;">
    <div class="row mt-5">
        <div class="col-12 border-bottom border-secondary mb-4 pb-2">
            <h2 class="fw-bold text-info">
                <i class="bi bi-heart-fill me-2"></i> Mis Favoritos
            </h2>
            <p class="text-white-50">
                Gestiona los modelos que más te gustan de Identiband.
            </p>
        </div>
    </div>

    <div class="row">
     <?php if (!empty($favoritos)): ?>
    
    <?php foreach ($favoritos as $fav): ?>
        <div class="col-md-4 col-lg-3 mb-4">
            <div class="card bg-dark border-secondary h-100 shadow-sm">

                <img 
                    src="assets/<?php echo htmlspecialchars($fav['imagen_url']); ?>" 
                    class="card-img-top p-3" 
                    alt="Modelo"
                    onerror="this.src='assets/pulsera.png'"
                >

                <div class="card-body text-center">
                    <h5 class="card-title text-white">
                        <?php echo htmlspecialchars($fav['nombre_modelo']); ?>
                    </h5>

                    <p class="text-white-50 small">
                        <?php echo htmlspecialchars($fav['variante_info']); ?>
                    </p>

                    <p class="text-info fw-bold mb-3">
                        $<?php echo number_format($fav['precio'], 2); ?>
                    </p>

                    <div class="d-grid gap-2">
                        <a href="detalle_producto.php?id=<?php echo $fav['id_producto']; ?>" 
                           class="btn btn-identi btn-sm fw-bold">
                            Añadir al carrito
                        </a>

                        <a href="eliminar_favorito.php?id=<?php echo $fav['id_producto']; ?>" 
                           class="btn btn-outline-danger btn-sm border-0">
                            <i class="bi bi-trash"></i> Eliminar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

        <?php else: ?>

            <div class="col-12 text-center py-5">
                <i class="bi bi-heart text-secondary display-1"></i>

                <h3 class="mt-3 text-white-50">
                    Aún no tienes favoritos
                </h3>

                <p>
                    Explora nuestros modelos y guarda los que más te gusten.
                </p>

                <a href="index.php#productos" class="btn btn-outline-info">
                    Ver Catálogo
                </a>
            </div>

        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>