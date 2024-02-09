<?php
/*
Plugin Name: Event Manager Plugin
Description: Custom plugin for event management.
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define('EVENT_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EVENT_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once(EVENT_MANAGER_PLUGIN_DIR . 'includes/event-functions.php');
require_once(EVENT_MANAGER_PLUGIN_DIR . 'includes/event-shortcodes.php');
require_once(EVENT_MANAGER_PLUGIN_DIR . 'includes/event-scripts.php');

add_action('init', 'event_manager_init');

function event_manager_init() {
    add_action('wp_enqueue_scripts', 'enqueue_datetimepicker_scripts');
    add_action('admin_enqueue_scripts', 'enqueue_datetimepicker_scripts');

    add_action('add_meta_boxes', 'add_movie_custom_fields');

    add_action('save_post', 'save_event_custom_fields');

    add_action('admin_menu', 'event_menu_page');
}

function enqueue_datetimepicker_scripts() {
    // Enqueue jQuery from the WordPress core
    wp_enqueue_script('jquery');

    // Enqueue jQuery UI
    wp_enqueue_script('jquery-ui-core', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js', array('jquery'), '1.13.2', true);

    // Enqueue jQuery UI Timepicker addon
    wp_enqueue_script('jquery-ui-timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), '1.6.3', true);

    // Optionally, enqueue jQuery UI CSS if needed
    wp_enqueue_style('jquery-ui-css', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css');
}

function add_movie_custom_fields() {
    add_meta_box(
        'event_details',
        'Event Details',
        'display_event_details_meta_box',
        'event',
        'normal',
        'default'
    );
}

function display_event_details_meta_box($post) {
    $event_start_datetime = get_post_meta($post->ID, 'event_start_datetime', true);
    $event_end_datetime = get_post_meta($post->ID, 'event_end_datetime', true);
    $event_venue = get_post_meta($post->ID, 'event_venue', true);

    ?>
    <label for="event_start_datetime">Event Start Date & Time:</label>
    <input type="text" name="event_start_datetime" id="event_start_datetime" value="<?php echo esc_attr($event_start_datetime); ?>" class="datetime-picker">
    <br>
    <label for="event_end_datetime">Event End Date & Time:</label>
    <input type="text" name="event_end_datetime" id="event_end_datetime" value="<?php echo esc_attr($event_end_datetime); ?>" class="datetime-picker">
    <br>
    <label for="event_venue">event_venue:</label>
    <input type="text" name="event_venue" value="<?php echo esc_attr($event_venue); ?>">
    <br>
    <script>
    jQuery(document).ready(function ($) {
        $('.datetime-picker').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss',
            controlType: 'select',
            oneLine: true
        });
    });
    </script>

    <?php
}

function save_event_custom_fields($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $fields = array('event_start_datetime', 'event_end_datetime', 'event_venue');

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Event_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct(array(
            'singular' => 'Event',
            'plural' => 'Events',
            'ajax' => false
        ));
    }

    function get_columns() {
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'start_datetime' => __('Start Date & Time'),
            'end_datetime' => __('End Date & Time'),
            'venue' => __('Venue'),
            'author' => __('Author'),
            'date' => __('Date'),
            'actions' => __('Actions')
        );
    }

    function prepare_items() {
        global $wpdb;

        $columns = $this->get_columns();
        $query = "SELECT * FROM $wpdb->posts WHERE post_type = 'event' AND post_status = 'publish'";
        $data = $wpdb->get_results($query);
        $this->_column_headers = array($columns);
        $this->items = $data;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'title':
                return $item->post_title;
            case 'start_datetime':
                return get_post_meta($item->ID, 'event_start_datetime', true);
            case 'end_datetime':
                return get_post_meta($item->ID, 'event_end_datetime', true);
            case 'venue':
                return get_post_meta($item->ID, 'event_venue', true);
            case 'author':
                return get_the_author_meta('display_name', $item->post_author);
            case 'date':
                return $item->post_date;
            case 'actions':
                $edit_url = admin_url('post.php?post=' . $item->ID . '&action=edit');
                $delete_url = get_delete_post_link($item->ID);
                return '<a href="' . $edit_url . '">Edit</a> | <a href="' . $delete_url . '" onclick="return confirm(\'Are you sure you want to delete this event?\')">Delete</a>';
            default:
                return print_r($item, true);
        }
    }


    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="event_id[]" value="%d" />',
            $item->ID
        );
    }

    function get_bulk_actions() {
        return array(
            'delete' => 'Delete'
        );
    }

    function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['event_id']) ? $_REQUEST['event_id'] : array();
            foreach ($ids as $id) {
                wp_delete_post($id);
            }
        }
    }

    function extra_tablenav($which) {
        if ($which == 'top') {
            echo '<input type="submit" name="bulk_delete" value="Delete" class="button">';
        }
    }
}

function event_menu_page() {
    add_menu_page('Events', 'Events', 'manage_options', 'events', 'render_events_page');
}

function render_events_page() {
    $event_table = new Event_List_Table();
    $event_table->prepare_items();

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">Events</h1>';
    echo '<a href="' . admin_url('post-new.php?post_type=event') . '" class="page-title-action">Add New Event</a>';
    echo '<form method="post">';
    $event_table->search_box('Search', 'event_search');
    $event_table->display();
    echo '</form>';
    echo '</div>';
}



?>
