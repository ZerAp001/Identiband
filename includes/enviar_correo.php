<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

function enviarCorreoConfirmacion($id_venta, $correo_destino, $nombre_cliente, $productos, $total_productos, $costo_envio, $monto_total, $metodo_pago, $referencia_oxxo = null) {
    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURACIÓN DEL SERVIDOR SMTP ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';             // Servidor SMTP (ej: smtp.gmail.com)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pachecovargas124@gmail.com';        // Tu correo emisor
        $mail->Password   = 'odtsafksytgyxlxj';     // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // --- REMITENTE Y DESTINATARIO ---
        $mail->setFrom('tu_correo@gmail.com', 'IDENTIBAND');
        $mail->addAddress($correo_destino, $nombre_cliente);

        // --- CONSTRUCCIÓN DE LA LISTA DE PRODUCTOS ---
        $filas_productos = '';
        foreach ($productos as $p) {
            $subtotal_item = $p['precio'] * $p['cantidad'];
            $filas_productos .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #30363d; color: #ffffff;'>{$p['nombre_modelo']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #30363d; color: #ffffff; text-align: center;'>{$p['cantidad']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #30363d; color: #00d2ff; text-align: right;'>$" . number_format($subtotal_item, 2) . " MXN</td>
                </tr>
            ";
        }

        $texto_metodo = strtoupper($metodo_pago);
        $bloque_oxxo = '';
        if ($metodo_pago === 'oxxo' && $referencia_oxxo) {
            $bloque_oxxo = "
                <div style='background-color: #1e293b; padding: 15px; border-radius: 8px; margin-top: 15px; border: 1px solid #00d2ff;'>
                    <p style='color: #00d2ff; margin: 0; font-weight: bold;'>Ficha de Pago OXXO:</p>
                    <p style='color: #ffffff; font-size: 18px; font-weight: bold; margin: 5px 0 0 0;'>$referencia_oxxo</p>
                </div>
            ";
        }

        // --- CONTENIDO DEL CORREO (PLANTILLA HTML) ---
        $mail->isHTML(true);
        $mail->Subject = "¡Confirmación de Pedido #{$id_venta} - IDENTIBAND!";
        
        $mail->Body = "
        <div style='background-color: #0d1117; color: #c9d1d9; font-family: Arial, sans-serif; padding: 30px; max-width: 600px; margin: 0 auto; border-radius: 10px; border: 1px solid #30363d;'>
            
            <div style='text-align: center; margin-bottom: 25px;'>
                <h1 style='color: #00d2ff; margin: 0; font-size: 28px; letter-spacing: 1px;'>IDENTIBAND</h1>
                <p style='color: #8b949e; margin-top: 5px;'>Más que una pulsera, la llave de tu evento.</p>
            </div>

            <h2 style='color: #ffffff; border-bottom: 1px solid #30363d; padding-bottom: 10px;'>¡Gracias por tu compra, {$nombre_cliente}!</h2>
            <p>Hemos recibido tu pedido <strong>#{$id_venta}</strong> y ya estamos procesándolo.</p>

            <h3 style='color: #00d2ff; margin-top: 25px;'>Resumen del Pedido</h3>
            <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                <thead>
                    <tr style='background-color: #161b22; color: #8b949e;'>
                        <th style='padding: 10px; text-align: left;'>Producto</th>
                        <th style='padding: 10px; text-align: center;'>Cant.</th>
                        <th style='padding: 10px; text-align: right;'>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    {$filas_productos}
                </tbody>
            </table>

            <div style='margin-top: 20px; text-align: right; line-height: 1.8;'>
                <p style='margin: 0;'>Subtotal: <strong>$" . number_format($total_productos, 2) . " MXN</strong></p>
                <p style='margin: 0;'>Envío: <strong>$" . number_format($costo_envio, 2) . " MXN</strong></p>
                <p style='margin: 0; font-size: 20px; color: #00d2ff;'>Total Pago: <strong>$" . number_format($monto_total, 2) . " MXN</strong></p>
            </div>

            <div style='margin-top: 20px; padding: 15px; background-color: #161b22; border-radius: 6px;'>
                <p style='margin: 0;'><strong>Método de pago:</strong> {$texto_metodo}</p>
                {$bloque_oxxo}
            </div>

            <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #30363d; color: #8b949e; font-size: 12px;'>
                <p>Si tienes alguna duda sobre tu envío, responde directamente a este correo.</p>
                <p>© IDENTIBAND - Todos los derechos reservados.</p>
            </div>
        </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // En caso de fallo, registra el error pero permite que el flujo continúe
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}