<?php
include '../includes/auth.php';
include '../../includes/db.php';

require '../../dompdf/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

/* FECHAS */

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-d');

/* CONSULTA */

$query = mysqli_query($conexion, "

SELECT
    pedidos.id_pedido,
    usuarios.nombre,
    usuarios.apellidos,
    pedidos.total,
    pedidos.estado,
    pedidos.fecha_pedido

FROM pedidos

INNER JOIN usuarios
ON pedidos.id_usuario = usuarios.id_usuario

WHERE DATE(pedidos.fecha_pedido)
BETWEEN '$inicio' AND '$fin'

ORDER BY pedidos.fecha_pedido DESC

");

$totalVentas = 0;

$html = '

<h1 style="text-align:center;">
Reporte IDENTIBAND
</h1>

<p>
Periodo:
<strong>'.$inicio.'</strong>
a
<strong>'.$fin.'</strong>
</p>

<table width="100%" border="1" cellspacing="0" cellpadding="8">

<tr>

<th>ID</th>
<th>Cliente</th>
<th>Total</th>
<th>Estado</th>
<th>Fecha</th>

</tr>

';

while($pedido = mysqli_fetch_assoc($query)){

    $totalVentas += $pedido['total'];

    $html .= '

    <tr>

        <td>#'.$pedido['id_pedido'].'</td>

        <td>
            '.$pedido['nombre'].' '.$pedido['apellidos'].'
        </td>

        <td>
            $'.number_format($pedido['total'], 2).'
        </td>

        <td>
            '.$pedido['estado'].'
        </td>

        <td>
            '.$pedido['fecha_pedido'].'
        </td>

    </tr>

    ';
}

$html .= '

</table>

<h2 style="margin-top:30px;">
Ingresos totales:
$'.number_format($totalVentas, 2).'
</h2>

';

/* GENERAR PDF */

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream(
    "reporte_identiband.pdf",
    ["Attachment" => true]
);