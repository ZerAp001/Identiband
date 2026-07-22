<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// 1. Valida el carrito.
try {
    // Iniciamos una transacción para asegurar la integridad de toda la compra
    $conexion->beginTransaction();

    // 1. Consultar el carrito del usuario de forma segura
    $stmt_carrito = $conexion->prepare("
        SELECT c.*, p.precio, p.nombre_modelo, p.stock
        FROM carrito c
        JOIN productos p ON c.id_producto = p.id_producto
        WHERE c.id_usuario = :id_usuario
    ");
    $stmt_carrito->execute(['id_usuario' => $id_usuario]);
    $productos_carrito = $stmt_carrito->fetchAll(PDO::FETCH_ASSOC);

    if (empty($productos_carrito)) {
        header("Location: carrito.php");
        exit;
    }

    // 🔧 VALIDAR STOCK ANTES DE COMPRAR
    foreach ($productos_carrito as $check) {
        if ($check['cantidad'] > $check['stock']) {
            $_SESSION['mensaje'] = "No hay suficiente stock de: " . $check['nombre_modelo'];
            header("Location: carrito.php");
            exit;
        }
    }

    // 2. Recibe los datos del formulario (ya no se necesita mysqli_real_escape_string)
    $nombre_recibe = $_POST['nombre_recibe'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $calle_numero = $_POST['calle_numero'] ?? '';
    $colonia = $_POST['colonia'] ?? '';
    $municipio = $_POST['municipio'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
    $referencias = $_POST['referencias'] ?? '';
    $metodo_pago = $_POST['metodo_pago'] ?? '';

    // 3. Validación de datos básicos.
    if (
        empty($nombre_recibe) ||
        empty($telefono) ||
        empty($calle_numero) ||
        empty($colonia) ||
        empty($municipio) ||
        empty($estado) ||
        empty($codigo_postal) ||
        empty($metodo_pago)
    ) {
        header("Location: checkout.php");
        exit;
    }

    // Cálculos totales.
    $total_productos = 0;
    foreach ($productos_carrito as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $total_productos += $subtotal;
    }

    if ($total_productos >= 1000) {
        $costo_envio = 0;
    } else {
        $costo_envio = 79;
    }

    $monto_total = $total_productos + $costo_envio;

    // Guardar la dirección de forma segura
    $stmt_dir = $conexion->prepare("
        INSERT INTO direcciones
        (
            id_usuario, calle_numero, colonia, municipio_alcaldia, 
            estado, codigo_postal, referencias, es_principal
        )
        VALUES
        (
            :id_usuario, :calle_numero, :colonia, :municipio, 
            :estado, :codigo_postal, :referencias, 1
        )
    ");
    $stmt_dir->execute([
        'id_usuario' => $id_usuario,
        'calle_numero' => $calle_numero,
        'colonia' => $colonia,
        'municipio' => $municipio,
        'estado' => $estado,
        'codigo_postal' => $codigo_postal,
        'referencias' => $referencias
    ]);

    // Referencia para oxxo.
    $referencia_oxxo = null;
    if ($metodo_pago == 'oxxo') {
        $referencia_oxxo = 'OXXO-' . rand(10000000, 99999999);
    }

    // Guardado en ventas.
    $stmt_venta = $conexion->prepare("
        INSERT INTO ventas
        (
            id_usuario, total_productos, costo_envio, 
            monto_total, metodo_pago, estado_pago, referencia_oxxo
        )
        VALUES
        (
            :id_usuario, :total_productos, :costo_envio, 
            :monto_total, :metodo_pago, 'completado', :referencia_oxxo
        )
    ");
    $stmt_venta->execute([
        'id_usuario' => $id_usuario,
        'total_productos' => $total_productos,
        'costo_envio' => $costo_envio,
        'monto_total' => $monto_total,
        'metodo_pago' => $metodo_pago,
        'referencia_oxxo' => $referencia_oxxo
    ]);

    // Obtenemos el ID de la venta recién insertada de forma nativa en PDO
    $id_venta = $conexion->lastInsertId();

    // Guardar pedido con relación a la venta
    $stmt_pedido = $conexion->prepare("
        INSERT INTO pedidos (id_usuario, total, estado, id_venta)
        VALUES (:id_usuario, :total, 'Pendiente', :id_venta)
    ");
    $stmt_pedido->execute([
        'id_usuario' => $id_usuario,
        'total' => $monto_total,
        'id_venta' => $id_venta
    ]);

    // Preparar sentencias para detalle y actualización de stock (optimización dentro del loop)
    $stmt_detalle = $conexion->prepare("
        INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario_momento)
        VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario)
    ");

    $stmt_stock = $conexion->prepare("
        UPDATE productos
        SET stock = stock - :cantidad
        WHERE id_producto = :id_producto
    ");

    // Guardado del detalle de venta y descuento de stock.
    foreach ($productos_carrito as $producto) {
        $stmt_detalle->execute([
            'id_venta' => $id_venta,
            'id_producto' => $producto['id_producto'],
            'cantidad' => $producto['cantidad'],
            'precio_unitario' => $producto['precio']
        ]);

        $stmt_stock->execute([
            'cantidad' => $producto['cantidad'],
            'id_producto' => $producto['id_producto']
        ]);
    }

    // Vaciar carrito después de pagar
    $stmt_vaciar = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = :id_usuario");
    $stmt_vaciar->execute(['id_usuario' => $id_usuario]);

    // Si todo salió bien, confirmamos los cambios en la base de datos
    $conexion->commit();

} catch (Exception $e) {
    // Si algo falla, revertimos cualquier cambio hecho en las tablas
    $conexion->rollBack();
    $_SESSION['mensaje'] = "Hubo un error al procesar tu compra. Inténtalo de nuevo.";
    header("Location: carrito.php");
    exit;
}

header("Location: pedidos.php");
exit;
?>