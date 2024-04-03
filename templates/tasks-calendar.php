<h1>Tasks</h1>
<?php //print_r($output_arrays); 
wp_enqueue_script("full-calendar", WAMS_URL . 'assets/fullcalendar/index.global.min.js', array(), WAMS_VERSION, false);

// print_r($tasks);
$today = date('Y-m-d');

?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            initialDate: '<?php echo $today; ?>',
            navLinks: true, // can click day/week names to navigate views
            nowIndicator: true,
            slotDuration: '01:00:00',
            views: {
                month: {
                    eventDisplay: 'block'
                }
            },

            editable: false,
            selectable: true,
            dayMaxEvents: true, // allow "more" link when too many events
            showNonCurrentDates: true,
            // events: [{}]
            events: <?php echo json_encode($tasks); ?>,

        });

        calendar.setOption('locale', '<?php echo get_locale() == 'ar' ? 'ar' : 'en'; ?>');
        calendar.render();
    });
</script>

<div id='calendar'></div>