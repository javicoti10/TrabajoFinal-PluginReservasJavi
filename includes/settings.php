<?php
if (!defined('ABSPATH')) exit; // Protección contra acceso directo no autorizado

// Función para agregar menús y submenús al panel de administración
function koalum_add_admin_menu() {
    add_menu_page('Configuración de Plugin Reservas Javi', 'Plugin Reservas Javi', 'manage_options', 'koalum', 'koalum_options_page');
    
    add_submenu_page('koalum', 'Historial de Reservas', 'Historial de Reservas', 'manage_options', 'koalum_historial', 'koalum_history_page');
    add_submenu_page('koalum', 'Personalización de Emails', 'Emails', 'manage_options', 'koalum_emails', 'koalum_emails_page');
    add_submenu_page('koalum', 'Configuración de Horarios', 'Horarios', 'manage_options', 'koalum_horarios', 'koalum_schedules_page');
    add_submenu_page('koalum', 'Calendario de Reservas', 'Calendario', 'manage_options', 'koalum_calendario', 'koalum_calendar_page');
}
add_action('admin_menu', 'koalum_add_admin_menu');

// Función para mostrar la página de opciones del plugin
function koalum_options_page() {
    ?>
    <div class="wrap">
        <h1 style="font-size: 2em; margin-bottom: 1em;">Bienvenido a la configuración del Plugin.</h1>
        <p style="font-size: 1.2em;">Accede a los submenús para configurar y usar el plugin a tu gusto:</p>
        <ul style="font-size: 1.2em; list-style-type: disc; margin-left: 20px;">
            <li style="margin-bottom: 0.5em;">
                <a href="<?php echo admin_url('admin.php?page=koalum_historial'); ?>" style="text-decoration: none; color: #0073aa;">Historial de Reservas</a>
                <p>Consulta y gestiona todas las reservas realizadas.</p>
            </li>
            <li style="margin-bottom: 0.5em;">
                <a href="<?php echo admin_url('admin.php?page=koalum_emails'); ?>" style="text-decoration: none; color: #0073aa;">Personalización de Emails</a>
                <p>Configura y personaliza los emails que se envían a los clientes.</p>
            </li>
            <li style="margin-bottom: 0.5em;">
                <a href="<?php echo admin_url('admin.php?page=koalum_horarios'); ?>" style="text-decoration: none; color: #0073aa;">Configuración de Horarios</a>
                <p>Define y ajusta los horarios disponibles para las reservas.</p>
            </li>
            <li style="margin-bottom: 0.5em;">
                <a href="<?php echo admin_url('admin.php?page=koalum_calendario'); ?>" style="text-decoration: none; color: #0073aa;">Calendario de Reservas</a>
                <p>Visualiza el calendario con todas las reservas.</p>
            </li>
        </ul>
        <hr />
        <h2 style="font-size: 1.5em; margin-top: 1em;">Restricciones de Reservas</h2>
        <p>Configura estos parámetros a tu gusto.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('koalum_restricciones_options_group');
            do_settings_sections('koalum_restricciones_section');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Nº Máximo de Reservas por Hora</th><br>
                    <td>
                        <?php
                        $max_reservas = get_option('koalum_restricciones_max_reservas', 5);
                        echo '<input type="number" name="koalum_restricciones_max_reservas" value="' . esc_attr($max_reservas) . '" min="1" />';
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Nº Máximo de Personas por Reserva</th><br>
                    <td>
                        <?php
                        $max_personas = get_option('koalum_restricciones_max_personas', 10);
                        echo '<input type="number" name="koalum_restricciones_max_personas" value="' . esc_attr($max_personas) . '" min="1" />';
                        ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registrar las configuraciones de restricciones
function koalum_register_restricciones_settings() {
    register_setting('koalum_restricciones_options_group', 'koalum_restricciones_max_reservas');
    register_setting('koalum_restricciones_options_group', 'koalum_restricciones_max_personas');
    add_settings_section('koalum_restricciones_section', '', null, 'koalum_restricciones_section');
    add_settings_field('koalum_restricciones_max_reservas', 'Máximo de Reservas por Horario', 'koalum_restricciones_max_reservas_callback', 'koalum_restricciones_section', 'default');
    add_settings_field('koalum_restricciones_max_personas', 'Máximo de Personas por Reserva', 'koalum_restricciones_max_personas_callback', 'koalum_restricciones_section', 'default');
}

// Callback para mostrar el campo de configuración del número máximo de reservas
function koalum_restricciones_max_reservas_callback() {
    $max_reservas = get_option('koalum_restricciones_max_reservas', 5);
    echo '<input type="number" name="koalum_restricciones_max_reservas" value="' . esc_attr($max_reservas) . '" min="1" />';
}

// Callback para mostrar el campo de configuración del número máximo de personas
function koalum_restricciones_max_personas_callback() {
    $max_personas = get_option('koalum_restricciones_max_personas', 10);
    echo '<input type="number" name="koalum_restricciones_max_personas" value="' . esc_attr($max_personas) . '" min="1" />';
}
add_action('admin_init', 'koalum_register_restricciones_settings');

// Función para redirigir al calendario
function koalum_calendar_page() {
    include plugin_dir_path(__FILE__) . 'calendario-reservas.php';
}

// Función para mostrar el historial de reservas
function koalum_history_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservas';

    // Obtener el número total de reservas
    $total_reservas = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Añadimos estilos CSS
    echo '
    <style>
        .koalum-container {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .koalum-title {
            font-weight: bold;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .koalum-subtitle {
            margin-bottom: 20px;
            font-size: 18px;
            color: #555;
        }
        .koalum-form {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .koalum-form-title {
            margin-bottom: 20px;
        }
        .koalum-form label {
            font-weight: bold;
        }
        .koalum-form input, .koalum-form select {
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
        .koalum-form input[type="text"], .koalum-form input[type="email"], .koalum-form input[type="number"], .koalum-form input[type="date"], .koalum-form input[type="time"], .koalum-form select {
            width: 100%;
        }
        .koalum-form textarea {
            width: 100%;
            resize: vertical;
        }
        .koalum-form input[type="submit"] {
            padding: 10px 20px;
            background-color: #0073aa;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .koalum-form input[type="submit"]:hover {
            background-color: #005177;
        }
        .koalum-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            text-align: left;
        }
        .koalum-table th, .koalum-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }
        .koalum-table th {
            background-color: #0073aa;
            color: #fff;
        }
        .koalum-table tr:nth-of-type(even) {
            background-color: #f9f9f9;
        }
        .koalum-table tr:hover {
            background-color: #f1f1f1;
        }
        .koalum-back-link {
            display: inline-block;
            margin: 20px 0;
            padding: 10px 20px;
            background-color: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .koalum-back-link:hover {
            background-color: #005177;
        }
    </style>
    ';

    // Título del formulario
    echo '<div class="koalum-container">';
    echo '<div class="koalum-title">Buscar Por:</div>';


    // Formulario de búsqueda y filtros
    echo '
    <form class="koalum-form" method="GET" action="">
        <input type="hidden" name="page" value="koalum_historial" />
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="' . (isset($_GET['email']) ? esc_attr($_GET['email']) : '') . '" />
        </div>
        <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="' . (isset($_GET['nombre']) ? esc_attr($_GET['nombre']) : '') . '" />
        </div>
        <div>
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="' . (isset($_GET['telefono']) ? esc_attr($_GET['telefono']) : '') . '" />
        </div>
        <div>
            <label for="fecha">Fecha Reserva:</label>
            <input type="date" id="fecha" name="fecha" value="' . (isset($_GET['fecha']) ? esc_attr($_GET['fecha']) : '') . '" />
        </div>
        <div>
            <label for="hora">Hora Reserva:</label>
            <input type="time" id="hora" name="hora" value="' . (isset($_GET['hora']) ? esc_attr($_GET['hora']) : '') . '" />
        </div>
        <div>
            <label for="num_personas">Número de Personas:</label>
            <input type="number" id="num_personas" name="num_personas" value="' . (isset($_GET['num_personas']) ? esc_attr($_GET['num_personas']) : '') . '" />
        </div>
        <div>
            <label for="comentarios">Comentarios:</label>
            <textarea id="comentarios" name="comentarios">' . (isset($_GET['comentarios']) ? esc_attr($_GET['comentarios']) : '') . '</textarea>
        </div>
        <div>
            <label for="estado">Estado:</label>
            <select id="estado" name="estado">
                <option value="">Todos</option>
                <option value="pendiente"' . (isset($_GET['estado']) && $_GET['estado'] == 'pendiente' ? ' selected' : '') . '>Pendientes</option>
                <option value="confirmed"' . (isset($_GET['estado']) && $_GET['estado'] == 'confirmed' ? ' selected' : '') . '>Aceptadas</option>
                <option value="cancelled"' . (isset($_GET['estado']) && $_GET['estado'] == 'cancelled' ? ' selected' : '') . '>Rechazadas</option>
            </select>
        </div>
        <div>
            <input type="submit" value="Buscar" />
        </div>
    </form>
    </div>
    ';

    // Recoger datos del formulario
    $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
    $nombre = isset($_GET['nombre']) ? sanitize_text_field($_GET['nombre']) : '';
    $telefono = isset($_GET['telefono']) ? sanitize_text_field($_GET['telefono']) : '';
    $fecha = isset($_GET['fecha']) ? sanitize_text_field($_GET['fecha']) : '';
    $hora = isset($_GET['hora']) ? sanitize_text_field($_GET['hora']) : '';
    $num_personas = isset($_GET['num_personas']) ? intval($_GET['num_personas']) : '';
    $comentarios = isset($_GET['comentarios']) ? sanitize_text_field($_GET['comentarios']) : '';
    $estado = isset($_GET['estado']) ? sanitize_text_field($_GET['estado']) : '';

    // Construir la consulta SQL con condiciones
    $query = "SELECT * FROM $table_name WHERE 1=1";
    $params = array();

    if (!empty($email)) {
        $query .= " AND email LIKE %s";
        $params[] = '%' . $wpdb->esc_like($email) . '%';
    }
    if (!empty($nombre)) {
        $query .= " AND nombre LIKE %s";
        $params[] = '%' . $wpdb->esc_like($nombre) . '%';
    }
    if (!empty($telefono)) {
        $query .= " AND telefono LIKE %s";
        $params[] = '%' . $wpdb->esc_like($telefono) . '%';
    }
    if (!empty($fecha)) {
        $query .= " AND fecha_reserva = %s";
        $params[] = $fecha;
    }
    if (!empty($hora)) {
        $query .= " AND hora_reserva = %s";
        $params[] = $hora;
    }
    if (!empty($num_personas)) {
        $query .= " AND num_personas = %d";
        $params[] = $num_personas;
    }
    if (!empty($comentarios)) {
        $query .= " AND comentarios LIKE %s";
        $params[] = '%' . $wpdb->esc_like($comentarios) . '%';
    }
    if (!empty($estado)) {
        $query .= " AND estado = %s";
        $params[] = $estado;
    }

    // Ejecutar la consulta con parámetros
    if (!empty($params)) {
        $reservas = $wpdb->get_results($wpdb->prepare($query, ...$params));
    } else {
        $reservas = $wpdb->get_results($query);
    }

    // Mapeo de estados
    $estado_map = array(
        'pendiente' => 'Pendientes',
        'confirmed' => 'Aceptadas',
        'cancelled' => 'Rechazadas'
    );

    if ($reservas) {
        if (!empty($email)) {
            $total_reservas_email = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $email));
            echo '<h2>Historial de Reservas de ' . esc_html($email) . '</h2>';
            echo '<div class="koalum-subtitle">' . esc_html($email) . ' ha hecho ' . esc_html($total_reservas_email) . ' reservas.</div>';
        } else {
            echo '<h2>Historial de Reservas</h2>';
            // Subtítulo con el número total de reservas
    echo '<div class="koalum-subtitle">Bienvenido al Historial de Reservas. Actualmente hay ' . esc_html($total_reservas) . ' reservas.</div>';
        }
        echo '<table class="koalum-table">';
        echo '<thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Fecha Reserva</th><th>Hora Reserva</th><th>Num. Personas</th><th>Comentarios</th><th>Estado</th></tr></thead>';
        echo '<tbody>';
        foreach ($reservas as $reserva) {
            $reserva_id = isset($reserva->id) ? esc_html($reserva->id) : '';  
            $nombre = isset($reserva->nombre) ? esc_html($reserva->nombre) : '';
            $email = isset($reserva->email) ? esc_html($reserva->email) : '';
            $telefono = isset($reserva->telefono) ? esc_html($reserva->telefono) : '';
            $fecha_reserva = isset($reserva->fecha_reserva) ? esc_html($reserva->fecha_reserva) : '';
            $hora_reserva = isset($reserva->hora_reserva) ? esc_html($reserva->hora_reserva) : '';
            $num_personas = isset($reserva->num_personas) ? esc_html($reserva->num_personas) : '';
            $comentarios = isset($reserva->comentarios) ? esc_html($reserva->comentarios) : '';
            $estado = isset($reserva->estado) ? esc_html($estado_map[$reserva->estado] ?? 'Pendientes') : '';

            echo '<tr>';
            echo '<td>' . $reserva_id . '</td>';
            echo '<td>' . $nombre . '</td>';
            echo '<td><a href="' . admin_url('admin.php?page=koalum_historial&email=' . esc_attr($email)) . '">' . $email . '</a></td>';
            echo '<td>' . $telefono . '</td>';
            echo '<td>' . $fecha_reserva . '</td>';
            echo '<td>' . $hora_reserva . '</td>';
            echo '<td>' . $num_personas . '</td>';
            echo '<td>' . $comentarios . '</td>';
            echo '<td>' . $estado . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<a class="koalum-back-link" href="' . admin_url('admin.php?page=koalum_historial') . '">Volver al listado</a>';
    } else {
        if (!empty($email)) {
            echo '<h2>Historial de Reservas de ' . esc_html($email) . '</h2>';
        }
        echo '<p>No se encontraron reservas con los criterios especificados.</p>';
        echo '<a class="koalum-back-link" href="' . admin_url('admin.php?page=koalum_historial') . '">Volver al listado</a>';
    }
}


// Definir las funciones de callback para los campos de los emails
function koalum_email_subject_field_callback() {
    $value = get_option('koalum_email_subject', 'Estado de su Reserva: Aceptada');
    echo '<input type="text" name="koalum_email_subject" value="' . esc_attr($value) . '">';
}

function koalum_email_content_field_callback() {
    $value = get_option('koalum_email_content', "
        <p><strong>Hola {name},</strong></p>
        <p><strong>Su reserva ha sido aceptada para el día {date} a las {time} para {people} personas.</strong></p>
        <p><strong>Gracias por reservar con nosotros.</strong></p>
        <p><strong>Si desea cancelar su reserva póngase en contacto con nosotros.</strong></p>
        <p>
        <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/contacto/' class='button button-contact'>Contactar</a>
        <a href='tel:654456794' class='button button-call'>Llamar</a>
        <a href='https://wa.me/654456794' class='button button-whatsapp'>WhatsApp</a>
    </p>
    ");
    echo '<textarea name="koalum_email_content">' . esc_textarea($value) . '</textarea>';
}

function koalum_pending_email_subject_field_callback() {
    $value = get_option('koalum_pending_email_subject', 'Estado de su Reserva: Pendiente');
    echo '<input type="text" name="koalum_pending_email_subject" value="' . esc_attr($value) . '">';
}

function koalum_pending_email_content_field_callback() {
    $value = get_option('koalum_pending_email_content', "
        <p><strong>Hola {name},</strong></p>
        <p><strong>Su reserva está pendiente para el día {date} a las {time} para {people} personas.</strong></p>
        <p><strong>En breve recibirá un email indicando si su reserva ha sido aceptada o rechazada.</strong></p>
    ");
    echo '<textarea name="koalum_pending_email_content">' . esc_textarea($value) . '</textarea>';
}

function koalum_rejection_email_subject_field_callback() {
    $value = get_option('koalum_rejection_email_subject', 'Estado de su Reserva: Rechazada');
    echo '<input type="text" name="koalum_rejection_email_subject" value="' . esc_attr($value) . '">';
}

function koalum_rejection_email_content_field_callback() {
    $value = get_option('koalum_rejection_email_content', "
        <p><strong>Hola {name},</strong></p>
        <p><strong>Lamentamos informarle que su reserva para el día {date} a las {time} ha sido rechazada.</strong></p>
        <p><strong>Pruebe a reservar en otro momento o póngase en contacto con nosotros.</strong></p>
        <p>
            <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/contacto/' class='button button-contact'>Contactar</a>
            <a href='tel:654456794' class='button button-call'>Llamar</a>
            <a href='https://wa.me/654456794' class='button button-whatsapp'>WhatsApp</a>
            <a href='https://asesoran-cp23.wordpresstemporal.com/pruebaplugin/prueba-plugin/' class='button button-reserve'>Reservar</a>
        </p>
    ");
    echo '<textarea name="koalum_rejection_email_content">' . esc_textarea($value) . '</textarea>';
}

// Registrar las configuraciones de los emails
function koalum_register_email_settings() {
    register_setting('koalum_email_settings_group', 'koalum_email_subject');
    register_setting('koalum_email_settings_group', 'koalum_email_content');
    register_setting('koalum_email_settings_group', 'koalum_pending_email_subject');
    register_setting('koalum_email_settings_group', 'koalum_pending_email_content');
    register_setting('koalum_email_settings_group', 'koalum_rejection_email_subject');
    register_setting('koalum_email_settings_group', 'koalum_rejection_email_content');
    
    add_settings_section('koalum_email_section', 'Configuración de Emails', null, 'koalum_emails');
    
    add_settings_field('koalum_email_subject', 'Asunto del Email de Confirmación', 'koalum_email_subject_field_callback', 'koalum_emails', 'koalum_email_section');
    add_settings_field('koalum_email_content', 'Contenido del Email de Confirmación', 'koalum_email_content_field_callback', 'koalum_emails', 'koalum_email_section');
    add_settings_field('koalum_pending_email_subject', 'Asunto del Email Pendiente', 'koalum_pending_email_subject_field_callback', 'koalum_emails', 'koalum_email_section');
    add_settings_field('koalum_pending_email_content', 'Contenido del Email Pendiente', 'koalum_pending_email_content_field_callback', 'koalum_emails', 'koalum_email_section');
    add_settings_field('koalum_rejection_email_subject', 'Asunto del Email de Rechazo', 'koalum_rejection_email_subject_field_callback', 'koalum_emails', 'koalum_email_section');
    add_settings_field('koalum_rejection_email_content', 'Contenido del Email de Rechazo', 'koalum_rejection_email_content_field_callback', 'koalum_emails', 'koalum_email_section');
}
add_action('admin_init', 'koalum_register_email_settings');

// Función para mostrar la página de configuración de emails
function koalum_emails_page() {
    ?>
    <div class="wrap">
        <h2>Personalización de Emails</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('koalum_email_settings_group');
            do_settings_sections('koalum_emails');
            submit_button('Guardar Cambios');
            ?>
        </form>
    </div>
    <?php
}



// Función para registrar configuraciones de horarios
function koalum_register_schedule_settings() {
    register_setting('koalum_schedule_settings_group', 'koalum_schedule_settings', 'koalum_settings_validate');

    add_settings_section('koalum_schedule_settings', 'CONFIGURACIÓN DE HORARIOS', 'koalum_settings_section_callback', 'koalum_horarios');

    add_settings_field('schedule', '', 'koalum_schedule_field_callback', 'koalum_horarios', 'koalum_schedule_settings');
}
add_action('admin_init', 'koalum_register_schedule_settings');

function koalum_settings_section_callback() {
    echo '<p>Configura los detalles aquí según tu horario.</p>';
}

function koalum_schedule_field_callback() {
    $options = get_option('koalum_schedule_settings', array());
    $lunch_start = isset($options['lunch_start']) ? esc_attr($options['lunch_start']) : '';
    $lunch_end = isset($options['lunch_end']) ? esc_attr($options['lunch_end']) : '';
    $dinner_start = isset($options['dinner_start']) ? esc_attr($options['dinner_start']) : '';
    $dinner_end = isset($options['dinner_end']) ? esc_attr($options['dinner_end']) : '';
    $closed_days = isset($options['closed_days']) ? $options['closed_days'] : array();

    $days_of_week = array(
        'monday' => 'Lunes',
        'tuesday' => 'Martes',
        'wednesday' => 'Miércoles',
        'thursday' => 'Jueves',
        'friday' => 'Viernes',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    );

    echo "<div class='koalum-schedule-section'>";
    echo "<div class='koalum-schedule-field'>";
    echo "<div class='schedule-label'>Horario:</div>";
    echo "<div class='field-group'>";
    echo "<label for='lunch_start'>Almuerzo inicio:</label> ";
    echo "<input type='time' id='lunch_start' name='koalum_schedule_settings[lunch_start]' value='$lunch_start'>";
    echo "</div>";
    echo "<div class='field-group'>";
    echo "<label for='lunch_end'>Almuerzo fin:</label> ";
    echo "<input type='time' id='lunch_end' name='koalum_schedule_settings[lunch_end]' value='$lunch_end'>";
    echo "</div>";
    echo "<div class='field-group'>";
    echo "<label for='dinner_start'>Cena inicio:</label> ";
    echo "<input type='time' id='dinner_start' name='koalum_schedule_settings[dinner_start]' value='$dinner_start'>";
    echo "</div>";
    echo "<div class='field-group'>";
    echo "<label for='dinner_end'>Cena fin:</label> ";
    echo "<input type='time' id='dinner_end' name='koalum_schedule_settings[dinner_end]' value='$dinner_end'>";
    echo "</div>";
    echo "</div>"; // Cierre de koalum-schedule-field

    echo "<div class='koalum-schedule-field'>";
    echo "<div class='schedule-label'>Días Cerrados:</div>";
    foreach ($days_of_week as $day_key => $day_name) {
        $checked = in_array($day_key, $closed_days) ? 'checked' : '';
        echo "<div class='field-group'>";
        echo "<label for='$day_key'>$day_name</label> ";
        echo "<input type='checkbox' id='$day_key' name='koalum_schedule_settings[closed_days][]' value='$day_key' $checked>";
        echo "</div>";
    }
    echo "</div>"; // Cierre de koalum-schedule-field
    echo "</div>"; // Cierre de koalum-schedule-section
}

// Función para mostrar la página de configuración de horarios
function koalum_schedules_page() {
    ?>
    <div class="wrap">
        <form action="options.php" method="post">
            <?php
            settings_fields('koalum_schedule_settings_group');
            do_settings_sections('koalum_horarios');
            submit_button('Guardar Cambios');
            ?>
        </form>
    </div>
    <style>
        .koalum-schedule-section {
            margin-bottom: 40px;
        }
        .koalum-schedule-field {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start; /* Asegura que los elementos se alineen al principio */
        }
        .schedule-label {
            width: 100%;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: left; /* Alinea el texto a la izquierda */
        }
        .field-group {
            display: flex;
            flex-direction: column;
            width: 45%;
            text-align: left; /* Alinea el texto a la izquierda */
        }
        .field-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .field-group input[type="time"],
        .field-group input[type="checkbox"] {
            margin-bottom: 10px;
            padding: 5px;
            box-sizing: border-box;
        }
        .wrap {
            padding: 20px;
            border: none;
            box-shadow: none;
        }
        h1 {
            margin-bottom: 20px;
            text-align: left; /* Alinea el texto a la izquierda */
        }
        form {
            max-width: 600px;
            text-align: left; /* Alinea el texto a la izquierda */
        }
    </style>
    <?php
}

function koalum_settings_validate($input) {
    // Validación de los campos
    $validated = array();
    foreach ($input as $key => $value) {
        if (is_array($value)) {
            $validated[$key] = array_map('sanitize_text_field', $value);
        } else {
            $validated[$key] = sanitize_text_field($value);
        }
    }
    return $validated;
}






