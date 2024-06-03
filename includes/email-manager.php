<?php
// Modificar la función de envío de email para usar las configuraciones
function rk_send_confirmation_email($customer_email, $reservation_id, $status, $reservation_details) {
    // Determinar el asunto del correo según el estado
    switch ($status) {
        case 'confirmed':
            $subject = get_option('koalum_email_subject', 'Estado de su Reserva: Aceptada');
            if (empty($subject)) {
                $subject = 'Estado de su Reserva: Aceptada';
            }
            break;
        case 'cancelled':
            $subject = get_option('koalum_rejection_email_subject', 'Estado de su Reserva: Rechazada');
            if (empty($subject)) {
                $subject = 'Estado de su Reserva: Rechazada';
            }
            break;
        case 'pending':
        default:
            $subject = get_option('koalum_pending_email_subject', 'Estado de su Reserva: Pendiente');
            if (empty($subject)) {
                $subject = 'Estado de su Reserva: Pendiente';
            }
            break;
    }

    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Inicializar la variable $content
    $content = '';

    // Asegurarse de que todos los índices están presentes en $reservation_details
    $name = isset($reservation_details['name']) ? $reservation_details['name'] : '';
    $date = isset($reservation_details['date']) ? $reservation_details['date'] : '';
    $time = isset($reservation_details['time']) ? $reservation_details['time'] : '';
    $people = isset($reservation_details['people']) ? $reservation_details['people'] : '';

    // Elegir la plantilla de correo basada en el estado actual de la reserva
    switch ($status) {
        case 'confirmed':
            $content = get_option('koalum_email_content', "
                <p><strong>Hola {$name},</strong></p>
                <p><strong>Su reserva ha sido aceptada para el día {$date} a las {$time} para {$people} personas.</strong></p>
                <p><strong>Gracias por reservar con nosotros.</strong></p>
                <p><strong>Si desea cancelar su reserva póngase en contacto con nosotros.</strong></p>
                <p>
                <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/contacto/' class='button button-contact'>Contactar</a>
                <a href='tel:654456794' class='button button-call'>Llamar</a>
                <a href='https://wa.me/654456794' class='button button-whatsapp'>WhatsApp</a>
            </p>
            ");
            if (empty($content)) {
                $content = "
                    <p><strong>Hola {$name},</strong></p>
                    <p><strong>Su reserva ha sido aceptada para el día {$date} a las {$time} para {$people} personas.</strong></p>
                    <p><strong>Gracias por reservar con nosotros.</strong></p>
                    <p><strong>Si desea cancelar su reserva póngase en contacto con nosotros.</strong></p>
                    <p>
                    <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/contacto/' class='button button-contact'>Contactar</a>
                    <a href='tel:654456794' class='button button-call'>Llamar</a>
                    <a href='https://wa.me/654456794' class='button button-whatsapp'>WhatsApp</a>
                </p>
                ";
            }
            $background_color = '#28a745'; // Verde fuerte para confirmadas
            break;
        case 'cancelled':
            $content = get_option('koalum_rejection_email_content', "
                <p><strong>Hola {$name},</strong></p>
                <p><strong>Lamentamos informarle que su reserva para el día {$date} a las {$time} ha sido rechazada.</strong></p>
                <p><strong>Pruebe a reservar en otro momento o póngase en contacto con nosotros.</strong></p>
                <p>
                    <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/contacto/' class='button button-contact'>Contactar</a>
                    <a href='tel:654456794' class='button button-call'>Llamar</a>
                    <a href='https://wa.me/654456794' class='button button-whatsapp'>WhatsApp</a>
                    <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/prueba-plugin/' class='button button-reserve'>Reservar</a>
                </p>
            ");
            if (empty($content)) {
                $content = "
                    <p><strong>Hola {$name},</strong></p>
                    <p><strong>Lamentamos informarle que su reserva para el día {$date} a las {$time} ha sido rechazada.</strong></p>
                    <p><strong>Pruebe a reservar en otro momento o póngase en contacto con nosotros.</strong></p>
                    <p>
                        <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/contacto/' class='button button-contact'>Contactar</a>
                        <a href='tel:654456794' class='button button-call'>Llamar</a>
                        <a href='https://wa.me/654456794' class='button button-whatsapp'>WhatsApp</a>
                        <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/prueba-plugin/' class='button button-reserve'>Reservar</a>
                    </p>
                ";
            }
            $background_color = '#FF2D00'; // Rojo para canceladas
            break;
        case 'pending': // Considerar 'pending' explícitamente para claridad
        default:
            $content = get_option('koalum_pending_email_content', "
                <p><strong>Hola {$name},</strong></p>
                <p><strong>Su reserva está pendiente para el día {$date} a las {$time} para {$people} personas.</strong></p>
                <p><strong>En breve recibirá un email indicando si su reserva ha sido aceptada o rechazada.</strong></p>
            ");
            if (empty($content)) {
                $content = "
                    <p><strong>Hola {$name},</strong></p>
                    <p><strong>Su reserva está pendiente para el día {$date} a las {$time} para {$people} personas.</strong></p>
                    <p><strong>En breve recibirá un email indicando si su reserva ha sido aceptada o rechazada.</strong></p>
                ";
            }
            $background_color = '#ffc107'; // Amarillo fuerte para pendientes
            break;
    }

    // Integrar el contenido en la plantilla
    $message = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                margin: 0;
                padding: 0;
                font-weight: bold; /* Letra en negrita en todo */
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background-color: ' . $background_color . ';
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                background-color: #343a40; /* Gris oscuro */
                color: #fff;
                padding: 10px 0;
                text-align: center;
                border-radius: 5px 5px 0 0;
                font-weight: bold; /* Letra en negrita */
            }
            .content {
                padding: 20px;
                line-height: 1.6;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                color: #666;
                padding: 10px 0;
            }
            .button {
                display: inline-block;
                padding: 10px 15px;
                margin: 10px 5px;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold; /* Letra en negrita para botones */
            }
            .button-contact {
                background-color: #007bff; /* Azul fuerte */
                font-weight: bold;
            }
            .button-call {
                background-color: #17a2b8; /* Azul claro */
                font-weight: bold;
            }
            .button-whatsapp {
                background-color: #004B08; /* Verde fuerte */
                font-weight: bold;
            }
            .button-reserve {
                background-color: #ffc107; /* Amarillo fuerte */
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . $subject . '</h1>
            </div>
            <div class="content">
                ' . $content . '
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Javi Cotilla. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>';

    wp_mail($customer_email, $subject, $message, $headers);
}



// Función para enviar un correo electrónico al administrador del sitio con enlaces para confirmar o cancelar la reserva
function rk_send_admin_notification_email($customer_email, $reservation_id) {
    global $wpdb;
    $admin_email = get_option('admin_email', get_bloginfo('admin_email'));
    $confirm_link = admin_url('admin-post.php?action=confirm_reservation&reservation_id=' . $reservation_id);
    $cancel_link = admin_url('admin-post.php?action=cancel_reservation&reservation_id=' . $reservation_id);

    // Obtener detalles de la reserva
    $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reservas WHERE ID = %d", $reservation_id));
    if (!$reservation) {
        return; // No hacer nada si la reserva no existe
    }

    $subject = 'Nueva Reserva Recibida';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    $message = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                margin: 0;
                padding: 0;
                font-weight: bold; /* Letra en negrita en todo */
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffc107; /* Fondo amarillo */
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                background-color: #343a40; /* Gris oscuro */
                color: #fff;
                padding: 10px 0;
                text-align: center;
                border-radius: 5px 5px 0 0;
                font-weight: bold; /* Letra en negrita */
            }
            .content {
                padding: 20px;
                line-height: 1.6;
                font-weight: bold; /* Letra en negrita */
            }
            .footer {
                text-align: center;
                font-size: 12px;
                color: #666;
                padding: 10px 0;
                font-weight: bold; /* Letra en negrita */
            }
            .button {
                display: inline-block;
                padding: 10px 15px;
                margin: 10px 5px;
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold; /* Letra en negrita para botones */
            }
            .button-confirm {
                background-color: #28a745; /* Verde fuerte */
            }
            .button-cancel {
                background-color: #dc3545; /* Rojo fuerte */
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . $subject . '</h1>
            </div>
            <div class="content">
                <p><strong>Nueva reserva de ' . $customer_email . '.</strong></p>
                <p><strong>Detalles de la reserva:</strong></p>
                <ul>
                    <li><strong>Fecha: ' . $reservation->fecha_reserva . '</strong></li>
                    <li><strong>Hora: ' . $reservation->hora_reserva . '</strong></li>
                    <li><strong>Número de personas: ' . $reservation->num_personas . '</strong></li>
                </ul>
                <p>
                    <a href="' . $confirm_link . '" class="button button-confirm">Aceptar</a>
                    <a href="' . $cancel_link . '" class="button button-cancel">Rechazar</a>
                </p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Javi Cotilla. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>';

    // Usar el tipo de contenido HTML para el correo electrónico
    add_filter('wp_mail_content_type', function() { return 'text/html'; });

    wp_mail($admin_email, 'Nueva reserva recibida', $message);

    // Restaurar el tipo de contenido predeterminado
    remove_filter('wp_mail_content_type', 'set_html_content_type');
}

// Acción para confirmar la reserva
add_action('admin_post_confirm_reservation', 'rk_confirm_reservation');
function rk_confirm_reservation() {
    global $wpdb;

    if (!isset($_GET['reservation_id'])) {
        wp_redirect(admin_url());
        exit;
    }

    $reservation_id = intval($_GET['reservation_id']);
    // Actualizar el estado de la reserva a 'confirmed'
    $wpdb->update(
        "{$wpdb->prefix}reservas",
        array('estado' => 'confirmed'),
        array('id' => $reservation_id),
        array('%s'),
        array('%d')
    );

    // Obtener el correo del cliente y los detalles de la reserva para enviar el correo
    $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reservas WHERE id = %d", $reservation_id));
    if ($reservation) {
        $customer_email = $reservation->email;
        $reservation_details = array(
            'date' => $reservation->fecha_reserva,
            'time' => $reservation->hora_reserva,
            'people' => $reservation->num_personas
        );
        rk_send_confirmation_email($customer_email, $reservation_id, 'confirmed', $reservation_details);
    }

    // Redirigir al administrador a la URL de confirmación con parámetros de éxito y reserva
    wp_redirect(add_query_arg(array('status' => 'confirmed', 'reservation_id' => $reservation_id), 'https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/reserva-aceptada/'));
    exit;
}

// Acción para cancelar la reserva
add_action('admin_post_cancel_reservation', 'rk_cancel_reservation');
function rk_cancel_reservation() {
    global $wpdb;

    if (!isset($_GET['reservation_id'])) {
        wp_redirect(admin_url());
        exit;
    }

    $reservation_id = intval($_GET['reservation_id']);
    // Actualizar el estado de la reserva a 'cancelled'
    $wpdb->update(
        "{$wpdb->prefix}reservas",
        array('estado' => 'cancelled'),
        array('id' => $reservation_id),
        array('%s'),
        array('%d')
    );

    // Obtener el correo del cliente y los detalles de la reserva para enviar el correo
    $reservation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}reservas WHERE id = %d", $reservation_id));
    if ($reservation) {
        $customer_email = $reservation->email;
        $reservation_details = array(
            'name' => $reservation->nombre,
            'date' => $reservation->fecha_reserva,
            'time' => $reservation->hora_reserva,
            'people' => $reservation->num_personas
        );
        rk_send_confirmation_email($customer_email, $reservation_id, 'cancelled', $reservation_details);
    }

    // Redirigir al administrador a la URL de cancelación con parámetros de éxito y reserva
    wp_redirect(add_query_arg(array('status' => 'cancelled', 'reservation_id' => $reservation_id), 'https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/reserva-rechazada/'));
    exit;
}



// Función para enviar correo de confirmación de edición o borrado
function send_email($to, $subject, $message) {
    wp_mail($to, $subject, $message);
}

function send_reserva_updated_email($post_id) {
    $to = get_post_meta($post_id, 'reserva_email', true);
    $subject = 'Tu reserva ha sido actualizada';
    $message = 'Los detalles de tu reserva han sido actualizados. Aquí están los nuevos detalles: ...';
    send_email($to, $subject, $message);
}

function send_reserva_deleted_email($post_id) {
    $to = get_post_meta($post_id, 'reserva_email', true);
    $subject = 'Tu reserva ha sido borrada';
    $message = 'Tu reserva con los siguientes detalles ha sido borrada: ...';
    send_email($to, $subject, $message);
}
