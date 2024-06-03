<?php
// Horarios permitidos
$horarios = rk_get_allowed_times(); // Esta función debe estar definida en 'utilities.php'
// Obtener los días cerrados desde la configuración
$closed_days = get_option('koalum_schedule_settings')['closed_days'] ?? array();

?>
<form action="" method="post" id="reservasForm">
    <input type="text" name="nombre" placeholder="Nombre completo" required maxlength="40">
    <input type="email" name="email" placeholder="Email" required pattern="[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]+$">
    <input type="tel" name="telefono" placeholder="Teléfono" required pattern="\d{9}">
    <input type="date" name="fecha_reserva" id="fecha_reserva" placeholder="Fecha de reserva" required>
    <select name="hora_reserva" required>
        <?php foreach ($horarios as $hora) : ?>
            <option value="<?php echo $hora; ?>"><?php echo $hora; ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="num_personas" placeholder="Número de personas" required min="1" max="<?php echo esc_attr(get_option('koalum_restricciones_max_personas', 10)); ?>">
    <textarea name="comentarios" placeholder="Comentarios (alergias, intolerancias, etc.)" oninput="limitWords(this);" maxlength="500"></textarea>
    <input type="submit" value="RESERVAR">
</form>

<script>
function limitWords(textarea) {
    var maxWords = 100;
    var words = textarea.value.split(/\s+/);
    if (words.length > maxWords) {
        textarea.value = words.slice(0, maxWords).join(" ");
        alert('Solo puedes ingresar hasta 100 palabras en los comentarios.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const closedDays = <?php echo json_encode($closed_days); ?>;
    const fechaReservaInput = document.getElementById('fecha_reserva');

    fechaReservaInput.addEventListener('input', function() {
        const selectedDate = new Date(this.value);
        const dayOfWeek = selectedDate.toLocaleString('en-US', { weekday: 'long' }).toLowerCase();

        if (closedDays.includes(dayOfWeek)) {
            alert('Lo siento, el día seleccionado está cerrado.');
            this.value = '';
        }
    });
});
</script>
