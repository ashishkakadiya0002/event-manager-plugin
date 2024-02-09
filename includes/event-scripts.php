<?php
function event_form_script() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var eventForm = document.getElementById('event-form');

            if (eventForm) {
                eventForm.addEventListener('submit', function (event) {
                    var startDatetime = eventForm.querySelector('input[name="event_start_datetime"]').value;
                    var endDatetime = eventForm.querySelector('input[name="event_end_datetime"]').value;

                    var startDate = new Date(startDatetime);
                    var endDate = new Date(endDatetime);

                    if (endDate <= startDate) {
                        alert('End date must be after start date.');
                        event.preventDefault();
                    }
                });
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'event_form_script');
?>
