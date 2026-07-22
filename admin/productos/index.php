<?php
include '../includes/auth.php';
include '../../includes/db.php';
include '../includes/header.php';

$where = [];
$filtros = []; // Array para guardar los valores seguros

// Buscar por nombre
if (!empty($_GET['buscar'])) {
    $where[] = "nombre_modelo LIKE :buscar";
    // Agregamos los símbolos % directamente en el valor que guardamos en el array
    $filtros['buscar'] = "%" . $_GET['buscar'] . "%";
}

// Filtrar por tipo
if (!empty($_GET['tipo'])) {
    $where[] = "tipo = :tipo";
    $filtros['tipo'] = $_GET['tipo'];
}

// Consulta base
$sql = "SELECT * FROM productos";

// Agregar filtros
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Ordenar
$sql .= " ORDER BY id_producto DESC";

// Ejecutar consulta
$stmt = $conexion->prepare($sql);
$stmt->execute($filtros);

// Obtener los datos
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="admin-title">
            Productos
        </h1>

        <p class="admin-subtitle">
            Gestiona el catálogo de IDENTIBAND
        </p>
    </div>

    <a href="crear.php" class="btn-admin">
        <i class="bi bi-plus-circle"></i>
        Nuevo producto
    </a>

</div>

<form method="GET" class="row g-2 mb-4">

    <div class="col-md-4">
        <input type="text"
               name="buscar"
               class="form-control admin-input"
               placeholder="Buscar producto..."
               value="<?= $_GET['buscar'] ?? '' ?>">
    </div>

    <div class="col-md-4">
        <select name="tipo" class="form-select admin-input">

            <option value="">Todos los tipos</option>

            <option value="Individual"
                <?= (($_GET['tipo'] ?? '') == 'Individual') ? 'selected' : '' ?>>
                Individual
            </option>

            <option value="Paquete"
                <?= (($_GET['tipo'] ?? '') == 'Paquete') ? 'selected' : '' ?>>
                Paquete
            </option>

            <option value="Premium"
                <?= (($_GET['tipo'] ?? '') == 'Premium') ? 'selected' : '' ?>>
                Premium
            </option>

        </select>
    </div>

    <div class="col-md-4">
        <button class="btn-admin w-100">
            🔍 Buscar
        </button>
    </div>

</form>

<div class="admin-card">

    <div class="table-responsive">

        <table class="table admin-table align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Tipo</th>
                    <th>Variante</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

                <?php while($producto = mysqli_fetch_assoc($query)): ?>

                <tr>

                    <td>
                        #<?= $producto['id_producto'] ?>
                    </td>

                    <td>
                        <img
                            src="../../assets/<?= $producto['imagen_url'] ?>"
                            width="70"
                            class="rounded"
                        >
                    </td>

                    <td>
                        <strong>
                            <?= $producto['nombre_modelo'] ?>
                        </strong>
                    </td>

                    <td class="text-info fw-bold">
                        $<?= number_format($producto['precio'],2) ?>
                    </td>

                    <td>

                        <?php if($producto['stock'] <= 5): ?>

                            <span class="badge bg-danger">
                                <?= $producto['stock'] ?>
                            </span>

                        <?php else: ?>

                            <span class="badge bg-success">
                                <?= $producto['stock'] ?>
                            </span>

                        <?php endif; ?>

                    </td>

                   <td>
                       <?= $producto['tipo'] ?>
                </td>

                    <td>

                        <span class="badge bg-primary">
                              <?= $producto['variante_info'] ?>
                      </span>

                 </td>

                    <td>

                        <div class="d-flex gap-2">

                            <a
                                href="editar.php?id=<?= $producto['id_producto'] ?>"
                                class="btn btn-warning btn-sm"
                            >
                                <i class="bi bi-pencil-fill"></i>
                            </a>

                            <a
                                href="eliminar.php?id=<?= $producto['id_producto'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Eliminar producto?')"
                            >
                                <i class="bi bi-trash-fill"></i>
                            </a>

                        </div>

                    </td>

                </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include '../includes/footer.php'; ?>