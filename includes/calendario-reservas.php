<?php
if (!defined('ABSPATH')) exit; // Protección contra acceso directo no autorizado

function koalum_calendar_page_content() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'reservas';

    $meses = [
        1 => 'ENERO',
        2 => 'FEBRERO',
        3 => 'MARZO',
        4 => 'ABRIL',
        5 => 'MAYO',
        6 => 'JUNIO',
        7 => 'JULIO',
        8 => 'AGOSTO',
        9 => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE'
    ];

    // Contenedor de navegación y contenido del calendario
    echo '<div class="wrap">
        <h2 class="calendar-title">CALENDARIO DE RESERVAS</h2>
        <p class="calendar-description">Aquí podrás visualizar todas las reservas en un calendario.</p>
        <div class="calendar-navigation">
            <button id="prev-month">Mes Anterior</button>
            <span id="current-month-name"></span>
            <button id="next-month">Mes Siguiente</button>
        </div>
        <div id="calendar-content">';

    // Generar tablas para cada mes
    foreach ($meses as $mes_num => $mes_nombre) {
        $reservas = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_name 
            WHERE MONTH(fecha_reserva) = %d AND YEAR(fecha_reserva) = 2024 AND estado = 'confirmed'
        ", $mes_num));

        $reservas_por_dia = [];
        foreach ($reservas as $reserva) {
            $dia = (int) date('j', strtotime($reserva->fecha_reserva));
            if (!isset($reservas_por_dia[$dia])) {
                $reservas_por_dia[$dia] = [];
            }
            $reservas_por_dia[$dia][] = $reserva;
        }

        echo "<div class='month-table' id='month-$mes_num' style='display:none;'>";
        echo "<table class='wp-calendar-table'>
                <thead>
                    <tr>
                        <th>LUNES</th>
                        <th>MARTES</th>
                        <th>MIÉRCOLES</th>
                        <th>JUEVES</th>
                        <th>VIERNES</th>
                        <th>SÁBADO</th>
                        <th>DOMINGO</th>
                    </tr>
                </thead>
                <tbody>";

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mes_num, 2024);
        $firstDayOfMonth = date('N', strtotime("2024-$mes_num-01"));
        $weeksInMonth = ceil(($daysInMonth + $firstDayOfMonth - 1) / 7);

        $currentDay = 1;
        for ($week = 0; $week < $weeksInMonth; $week++) {
            echo "<tr>";
            for ($day = 1; $day <= 7; $day++) {
                if (($week === 0 && $day < $firstDayOfMonth) || $currentDay > $daysInMonth) {
                    echo "<td></td>";
                } else {
                    echo "<td><div class='day-container'><div class='day-number'>{$currentDay}</div>";
                    if (isset($reservas_por_dia[$currentDay])) {
                        foreach ($reservas_por_dia[$currentDay] as $reserva) {
                            echo "<div class='reserva'>";
                            echo "<strong>RESERVA DE:</strong> {$reserva->email}<br>";
                            echo "<strong>Nombre:</strong> {$reserva->nombre}<br>";
                            echo "<strong>Hora:</strong> {$reserva->hora_reserva}<br>";
                            echo "<strong>Nº personas:</strong> {$reserva->num_personas}<br>";
                            echo "<strong>Comentarios:</strong> {$reserva->comentarios}<br>";
                            $cancel_url = wp_nonce_url(admin_url('admin-post.php?action=cancel_reservation&reservation_id=' . $reserva->id), 'cancel_reservation_' . $reserva->id);
                            echo '<a href="' . $cancel_url . '" class="reserva-boton reserva-cancelar" style="margin-top: 10px;" onclick="return confirm(\'¿Estás seguro de que quieres cancelar esta reserva?\')">Cancelar</a>';
                            echo "</div>";
                        }
                    }
                    echo "</div></td>";
                    $currentDay++;
                }
            }
            echo "</tr>";
        }
        echo "</tbody></table></div>";
    }

    // Cierre del contenedor del calendario y el contenedor de navegación
    echo '</div></div>';

    // Estilos
    echo '<style>
        .calendar-navigation {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .calendar-navigation button {
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            background-color: #0073aa;
            color: #fff;
            border: none;
            border-radius: 3px;
            margin: 0 10px;
        }
        .calendar-navigation span {
            font-size: 40px;
            font-weight: bold;
            color: #0073aa;
            margin: 0 20px;
        }
        .calendar-title {
            font-size: 42px;
            font-weight: bold;
            color: #0073aa;
            margin-bottom: 10px;
        }
        .calendar-description {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .wp-calendar-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
            text-align: left;
            background-color: #f9f9f9;
            color: #444;
        }
        .wp-calendar-table th, .wp-calendar-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            vertical-align: top;
            height: 200px; /* Incrementamos la altura de la celda */
        }
        .wp-calendar-table th {
            background-color: #0073aa; /* Color de encabezado de WordPress */
            color: #fff;
            text-align: center;
            text-transform: uppercase;
            vertical-align: middle;
            line-height: 200px; /* Ajustamos la línea para centrar verticalmente */
        }
        .wp-calendar-table tr:nth-of-type(even) {
            background-color: #f3f3f3;
        }
        .wp-calendar-table tr:hover {
            background-color: #f1f1f1;
        }
        .day-container {
            position: relative;
            width: 100%;
            height: 100%;
            padding: 5px; /* Añadimos padding para espaciado */
        }
        .day-number {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 14px;
            color: #666;
        }
        .reserva {
            margin-top: 10px;
            background-color: #fff;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 10px; /* Añadimos margen inferior para espaciado */
        }
        .reserva strong {
            display: block;
            color: #0073aa; /* Ajustamos el color para mejor legibilidad */
            margin-bottom: 5px; /* Añadimos margen inferior para espaciado */
        }
    </style>';

    // JavaScript
    echo '<script>
        jQuery(document).ready(function($) {
            var currentMonth = new Date().getMonth() + 1; // Obtener el mes actual (1-12)
            var monthNames = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];

            function showMonth(month) {
                $(".month-table").hide();
                $("#month-" + month).show();
                $("#current-month-name").text(monthNames[month - 1] + " 2024");
            }

            showMonth(currentMonth); // Mostrar el mes actual al cargar la página

            $("#prev-month").on("click", function() {
                if (currentMonth === 1) {
                    currentMonth = 12;
                } else {
                    currentMonth--;
                }
                showMonth(currentMonth);
            });

            $("#next-month").on("click", function() {
                if (currentMonth === 12) {
                    currentMonth = 1;
                } else {
                    currentMonth++;
                }
                showMonth(currentMonth);
            });
        });
    </script>';
}

koalum_calendar_page_content();














