jQuery(document).ready(function ($) {
  function generateCalendar(month, year) {
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const firstDay = new Date(year, month).getDay();

      let table = '<tr>';
      const daysOfWeek = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

      for (let i = 0; i < daysOfWeek.length; i++) {
          table += '<th>' + daysOfWeek[i] + '</th>';
      }
      table += '</tr><tr>';

      for (let i = 0; i < firstDay; i++) {
          table += '<td></td>';
      }

      for (let day = 1; day <= daysInMonth; day++) {
          if ((firstDay + day - 1) % 7 === 0) {
              table += '</tr><tr>';
          }
          table += '<td>' + day + '</td>';
      }

      table += '</tr>'; // Cerrar la última fila de la tabla

      $('#calendar-table').html(table);
  }

  let date = new Date();
  let currentMonth = date.getMonth();
  let currentYear = date.getFullYear();

  generateCalendar(currentMonth, currentYear);

  $('#prev-month').click(function () {
      currentMonth--;
      if (currentMonth < 0) {
          currentMonth = 11;
          currentYear--;
      }
      generateCalendar(currentMonth, currentYear);
  });

  $('#next-month').click(function () {
      currentMonth++;
      if (currentMonth > 11) {
          currentMonth = 0;
          currentYear++;
      }
      generateCalendar(currentMonth, currentYear);
  });
});
