<?php
add_shortcode('event_page', 'event_page_shortcode');
function event_page_shortcode() {
    ob_start();
    ?>
    <div id="event-manager-container">
        <?php
        if (is_user_logged_in() && (current_user_can('event_manager') || current_user_can('administrator')) ) {
            include(EVENT_MANAGER_PLUGIN_DIR . 'templates/event-form.php');
            include(EVENT_MANAGER_PLUGIN_DIR . 'templates/event-list.php');
        } else {
            include(EVENT_MANAGER_PLUGIN_DIR . 'templates/event-list.php');
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
?>
