<?php

// Función para recuperar horarios permitidos
function rk_get_allowed_times() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservas';

    // Valores predeterminados para almuerzo y cena
    $default_lunch_start = '13:00';
    $default_lunch_end = '16:00';
    $default_dinner_start = '20:00';
    $default_dinner_end = '23:00';

    // Recuperar configuraciones de horarios desde la base de datos
    $schedule_settings = get_option('koalum_schedule_settings', array());

    // Usar valores predeterminados o configurados para almuerzo
    $lunch_start = isset($schedule_settings['lunch_start']) ? $schedule_settings['lunch_start'] : $default_lunch_start;
    $lunch_end = isset($schedule_settings['lunch_end']) ? $schedule_settings['lunch_end'] : $default_lunch_end;

    // Usar valores predeterminados o configurados para cena
    $dinner_start = isset($schedule_settings['dinner_start']) ? $schedule_settings['dinner_start'] : $default_dinner_start;
    $dinner_end = isset($schedule_settings['dinner_end']) ? $schedule_settings['dinner_end'] : $default_dinner_end;

    // Array para almacenar los tiempos permitidos
    $allowed_times = array();

    // Recuperar reservas existentes
    $reservas = $wpdb->get_results("SELECT hora_reserva FROM $table_name", ARRAY_A);

    // Convertir las reservas a un array de horas
    $reservas_horas = array_map(function($reserva) {
        return $reserva['hora_reserva'];
    }, $reservas);

    // Generar los tiempos permitidos para el almuerzo y la cena
    $allowed_times = array_merge(
        generate_times($lunch_start, $lunch_end, $reservas_horas),
        generate_times($dinner_start, $dinner_end, $reservas_horas)
    );

    // Eliminar duplicados y ordenar los tiempos permitidos
    $allowed_times = array_unique($allowed_times);
    sort($allowed_times);

    return $allowed_times;
}

function generate_times($start, $end, $excluded_times) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $times = array();

    while ($start_time <= $end_time) { // Incluir el tiempo final
        $time_str = date('H:i', $start_time);
        if (!in_array($time_str, $excluded_times)) {
            $times[] = $time_str;
        }
        $start_time = strtotime('+30 minutes', $start_time);
    }

    return $times;
}






// Definir un espacio global para almacenar temporalmente los detalles de la reserva
global $rk_reservation_details;
$rk_reservation_details = [];

// Función para establecer los detalles de la reserva en la variable global
function rk_set_reservation_details($details) {
    global $rk_reservation_details;
    $rk_reservation_details = $details;
}

// Función para obtener un detalle específico de la reserva desde la variable global
function rk_get_reservation_detail($key) {
    global $rk_reservation_details;
    return isset($rk_reservation_details[$key]) ? $rk_reservation_details[$key] : '';
}

// Shortcode para mostrar la fecha de la reserva
function rk_shortcode_date($atts) {
    return rk_get_reservation_detail('date');
}

// Shortcode para mostrar la hora de la reserva
function rk_shortcode_time($atts) {
    return rk_get_reservation_detail('time');
}

// Shortcode para mostrar el número de personas de la reserva
function rk_shortcode_people($atts) {
    return rk_get_reservation_detail('people');
}

// Registrar los shortcodes en WordPress
add_shortcode('rk_date', 'rk_shortcode_date');
add_shortcode('rk_time', 'rk_shortcode_time');
add_shortcode('rk_people', 'rk_shortcode_people');
