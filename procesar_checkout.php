<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// 1. Valida el carrito.

$query_carrito = mysqli_query(
    $conexion,
    "SELECT c.*, p.precio, p.nombre_modelo, p.stock
     FROM carrito c
     JOIN productos p ON c.id_producto = p.id_producto
     WHERE c.id_usuario = $id_usuario"
);

if (!$query_carrito || mysqli_num_rows($query_carrito) == 0) {
    header("Location: carrito.php");
    exit;
}

// 🔧 VALIDAR STOCK ANTES DE COMPRAR
while ($check = mysqli_fetch_assoc($query_carrito)) {
    if ($check['cantidad'] > $check['stock']) {
        $_SESSION['mensaje'] = "No hay suficiente stock de: " . $check['nombre_modelo'];
        header("Location: carrito.php");
        exit;
    }
}

// 🔧 RESET RESULTADO
mysqli_data_seek($query_carrito, 0);

// 2. Recibe los datos del formulario.

$nombre_recibe = mysqli_real_escape_string(
    $conexion,
    $_POST['nombre_recibe'] ?? ''
);

$telefono = mysqli_real_escape_string(
    $conexion,
    $_POST['telefono'] ?? ''
);

$calle_numero = mysqli_real_escape_string(
    $conexion,
    $_POST['calle_numero'] ?? ''
);

$colonia = mysqli_real_escape_string(
    $conexion,
    $_POST['colonia'] ?? ''
);

$municipio = mysqli_real_escape_string(
    $conexion,
    $_POST['municipio'] ?? ''
);

$estado = mysqli_real_escape_string(
    $conexion,
    $_POST['estado'] ?? ''
);

$codigo_postal = mysqli_real_escape_string(
    $conexion,
    $_POST['codigo_postal'] ?? ''
);

$referencias = mysqli_real_escape_string(
    $conexion,
    $_POST['referencias'] ?? ''
);

$metodo_pago = mysqli_real_escape_string(
    $conexion,
    $_POST['metodo_pago'] ?? ''
);

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
$productos_guardar = [];

while ($item = mysqli_fetch_assoc($query_carrito)) {
    $subtotal = $item['precio'] * $item['cantidad'];
    $total_productos += $subtotal;

    $productos_guardar[] = $item;
}

if ($total_productos >= 1000) {
    $costo_envio = 0;
} else {
    $costo_envio = 79;
}

$monto_total = $total_productos + $costo_envio;

// Guardar la dirección

mysqli_query(
    $conexion,
    "INSERT INTO direcciones
    (
        id_usuario,
        calle_numero,
        colonia,
        municipio_alcaldia,
        estado,
        codigo_postal,
        referencias,
        es_principal
    )
    VALUES
    (
        $id_usuario,
        '$calle_numero',
        '$colonia',
        '$municipio',
        '$estado',
        '$codigo_postal',
        '$referencias',
        1
    )"
);

// Referencia para oxxo.

$referencia_oxxo = NULL;

if ($metodo_pago == 'oxxo') {
    $referencia_oxxo = 'OXXO-' . rand(10000000, 99999999);
}

// Guardado en ventas.

mysqli_query(
    $conexion,
    "INSERT INTO ventas
    (
        id_usuario,
        total_productos,
        costo_envio,
        monto_total,
        metodo_pago,
        estado_pago,
        referencia_oxxo
    )
    VALUES
    (
        $id_usuario,
        $total_productos,
        $costo_envio,
        $monto_total,
        '$metodo_pago',
        'completado',
        " . ($referencia_oxxo ? "'$referencia_oxxo'" : "NULL") . "
    )"
);

$id_venta = mysqli_insert_id($conexion);

// Guardar pedido con relación a la venta
mysqli_query(
    $conexion,
    "INSERT INTO pedidos
    (id_usuario, total, estado, id_venta)
    VALUES
    ($id_usuario, $monto_total, 'Pendiente', $id_venta)"
);

// Guardado del detalle de venta.

foreach ($productos_guardar as $producto) {
    $id_producto = $producto['id_producto'];
    $cantidad = $producto['cantidad'];
    $precio_unitario = $producto['precio'];

    mysqli_query(
        $conexion,
        "INSERT INTO detalle_ventas
        (
            id_venta,
            id_producto,
            cantidad,
            precio_unitario_momento
        )
        VALUES
        (
            $id_venta,
            $id_producto,
            $cantidad,
            $precio_unitario
        )"
    );

    // Descontar stock
    mysqli_query(
        $conexion,
        "UPDATE productos
         SET stock = stock - $cantidad
         WHERE id_producto = $id_producto"
    );
}

//Vaciar carrito después de pagar

mysqli_query(
    $conexion,
    "DELETE FROM carrito
     WHERE id_usuario = $id_usuario"
);

header("Location: pedidos.php");
exit;
?>