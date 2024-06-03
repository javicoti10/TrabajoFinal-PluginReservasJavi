<?php
// Función para manejar el envío de formularios de reserva
function rk_handle_reservation_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'reservas';

        // Validaciones en el servidor
        $nombre = sanitize_text_field($_POST['nombre']);
        $email = sanitize_email($_POST['email']);
        $telefono = sanitize_text_field($_POST['telefono']);
        $fecha_reserva = sanitize_text_field($_POST['fecha_reserva']);
        $hora_reserva = sanitize_text_field($_POST['hora_reserva']);
        $num_personas = intval($_POST['num_personas']);
        $comentarios = sanitize_textarea_field($_POST['comentarios']);

        // Verificar que el nombre no exceda los 40 caracteres
        if (strlen($nombre) > 40) {
            wp_die('El nombre no debe exceder los 40 caracteres.');
        }

        // Verificar que el número de teléfono tenga exactamente 9 dígitos
        if (!preg_match('/^\d{9}$/', $telefono)) {
            wp_die('El número de teléfono debe tener exactamente 9 dígitos.');
        }

        // Verificar que el email tenga el formato correcto
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            wp_die('Formato de email inválido.');
        }

        // Verificar restricciones de reservas por horario
        $validacion_restricciones = koalum_procesar_reserva($fecha_reserva, $hora_reserva);
        if (is_wp_error($validacion_restricciones)) {
            wp_die('El número máximo de reservas para esta hora ha sido alcanzado.Prueba a reservar para otra hora.
            <br><a href="https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/prueba-plugin/" style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #0073aa; border: none; border-radius: 3px; text-decoration: none; margin-top: 10px;">VOLVER A RESERVAR</a>');
        }
      
         // Verificar el número máximo de personas
         $max_personas = get_option('koalum_restricciones_max_personas', 10);
         if ($num_personas < 1 || $num_personas > $max_personas) {
             wp_die('El número de personas debe estar entre 1 y ' . $max_personas . '.');
         }

         // Verificar los días cerrados
        $closed_days = get_option('koalum_schedule_settings')['closed_days'] ?? array();
        $day_of_week = strtolower(date('l', strtotime($fecha_reserva)));

        if (in_array($day_of_week, $closed_days)) {
            wp_die('Lo siento, el día seleccionado está cerrado.<br> <a href="https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/prueba-plugin/" style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #0073aa; border: none; border-radius: 3px; text-decoration: none; margin-top: 10px;">VOLVER A RESERVAR</a>');
        }

        // Verificar el número máximo de palabras en comentarios
        if (str_word_count($comentarios) > 100) {
            wp_die('Los comentarios no deben exceder las 100 palabras.');
        }

      // Verificar el número de reservas del cliente en la fecha específica
      $reservas_cliente = $wpdb->get_var($wpdb->prepare("
      SELECT COUNT(*) FROM {$table_name}
      WHERE email = %s AND fecha_reserva = %s
  ", $email, $fecha_reserva));

  if ($reservas_cliente >= 2) {
    wp_die('Lo sentimos pero no se pueden hacer más de 2 reservas por día.
    <br> <a href="https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/prueba-plugin/" style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #0073aa; border: none; border-radius: 3px; text-decoration: none; margin-top: 10px;">VOLVER A RESERVAR</a>');
}


        // Insertar los datos de la reserva en la base de datos con estado 'pendiente'
        $wpdb->insert($table_name, [
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'fecha_reserva' => $fecha_reserva,
            'hora_reserva' => $hora_reserva,
            'num_personas' => $num_personas,
            'comentarios' => $comentarios,
            'estado' => 'pendiente',  // Asegurar que el estado inicial es 'pendiente'
        ], [
            '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'
        ]);

        $reservation_id = $wpdb->insert_id;
        $reservation_details = [
            'name' => $nombre,
            'date' => $fecha_reserva,
            'time' => $hora_reserva,
            'people' => $num_personas
        ];

        // Establecer los detalles de la reserva para usar en los shortcodes
        rk_set_reservation_details($reservation_details);

        // Enviar correo de confirmación de reserva pendiente
        rk_send_confirmation_email($email, $reservation_id, 'pending', $reservation_details);

        // Enviar correo de notificación al administrador
        rk_send_admin_notification_email($email, $reservation_id);

        // Insertar la reserva como un nuevo post en el CPT
        $cpt_result = rk_insert_reservation_as_cpt($nombre, $email, $telefono, $fecha_reserva, $hora_reserva, $num_personas, $comentarios);
        if ($cpt_result) {
            error_log('Reserva añadida correctamente al CPT con ID: ' . $cpt_result);
        } else {
            error_log('Error al añadir la reserva al CPT.');
        }
    }
}
add_action('init', 'rk_handle_reservation_submission');

// Función para procesar la reserva y validar restricciones
function koalum_procesar_reserva($fecha_reserva, $hora_reserva) {
    global $wpdb;
    $max_reservas = get_option('koalum_restricciones_max_reservas', 5);

    // Obtener el número actual de reservas para el horario dado
    $reservas_actuales = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*) FROM {$wpdb->prefix}reservas
        WHERE fecha_reserva = %s AND hora_reserva = %s
    ", $fecha_reserva, $hora_reserva));

    if ($reservas_actuales >= $max_reservas) {
        // Si se ha alcanzado el máximo de reservas, devolver un error o manejar la situación adecuadamente
        return new WP_Error('max_reservas_excedido', 'El número máximo de reservas para este horario ha sido alcanzado.');
    }

    return true;
}


// Función para insertar una reserva en el CPT
function rk_insert_reservation_as_cpt($nombre, $email, $telefono, $fecha_reserva, $hora_reserva, $num_personas, $comentarios) {
    // Crear el post en el CPT
    $post_data = array(
        'post_title'    => wp_strip_all_tags($nombre),
        'post_content'  => $comentarios,
        'post_status'   => 'publish',
        'post_type'     => 'reserva', // Asegúrate de que este es el nombre de tu CPT
        'meta_input'    => array(
            '_koalum_nombre' => $nombre,
            '_koalum_email' => $email,
            '_koalum_telefono' => $telefono,
            '_koalum_fecha' => $fecha_reserva,
            '_koalum_hora' => $hora_reserva,
            '_koalum_num_personas' => $num_personas,
            '_koalum_comentarios' => $comentarios,
            '_koalum_estado' => 'pendiente', // Estado inicial de la reserva
        ),
    );

    // Insertar el post en la base de datos
    $post_id = wp_insert_post($post_data);

    // Verificar si el post fue insertado correctamente
    if ($post_id) {
        return $post_id;
    } else {
        return false;
    }
}

// Función para actualizar el estado de la reserva y redirigir con el ID de la reserva
function update_reservation_status($reserva_id, $nuevo_estado) {
    global $wpdb;
    $wpdb->update(
        "{$wpdb->prefix}reservas",
        array('estado' => $nuevo_estado),
        array('id' => $reserva_id),
        array('%s'),
        array('%d')
    );

    // Verificación para asegurarse de que el ID de reserva es correcto
    if ($reserva_id > 0) {
        // Generar la URL de redirección basada en el estado de la reserva
        if ($nuevo_estado == 'aceptada') {
            $redirect_url = add_query_arg('reservation_id', $reserva_id, home_url('/pruebaplugin/reserva-aceptada/'));
        } elseif ($nuevo_estado == 'rechazada') {
            $redirect_url = add_query_arg('reservation_id', $reserva_id, home_url('/pruebaplugin/reserva-rechazada/'));
        } else {
            $redirect_url = home_url();
        }
        
        // Imprimir la URL de redirección para depuración
        error_log('Redirect URL: ' . $redirect_url);
        
        // Redireccionar después de actualizar el estado
        wp_redirect($redirect_url);
        exit;
    } else {
        // Manejar el error de ID de reserva no válido
        error_log('ID de reserva no válido en la actualización: ' . $reserva_id);
    }
}

// Guardar datos de metaboxes y validaciones
function save_reserva_meta_box_data($post_id) {
    if (!isset($_POST['reserva_meta_box_nonce']) || !wp_verify_nonce($_POST['reserva_meta_box_nonce'], 'reserva_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $estado = sanitize_text_field($_POST['reserva_estado']);
    $hora = sanitize_text_field($_POST['reserva_hora']);
    $telefono = sanitize_text_field($_POST['reserva_telefono']);

    update_post_meta($post_id, 'reserva_estado', $estado);
    update_post_meta($post_id, 'reserva_hora', $hora);
    update_post_meta($post_id, 'reserva_telefono', $telefono);

    // Enviar correo de confirmación de edición
    $to = get_post_meta($post_id, 'reserva_email', true);
    $subject = 'Tu reserva ha sido actualizada';
    $message = 'Los detalles de tu reserva han sido actualizados. Aquí están los nuevos detalles: ...';
    wp_mail($to, $subject, $message);
}
add_action('save_post', 'save_reserva_meta_box_data');

// Manejar el borrado de reservas
function delete_reserva($post_id) {
    if (get_post_type($post_id) != 'reserva') {
        return;
    }

    // Enviar correo de confirmación de borrado
    $to = get_post_meta($post_id, 'reserva_email', true);
    $subject = 'Tu reserva ha sido borrada';
    $message = 'Tu reserva con los siguientes detalles ha sido borrada: ...';
    wp_mail($to, $subject, $message);
}
add_action('before_delete_post', 'delete_reserva');





