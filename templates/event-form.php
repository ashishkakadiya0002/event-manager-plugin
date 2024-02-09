<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_event'])) {
    // Include necessary WordPress files
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $event_title = sanitize_text_field($_POST['event_title']);
    $event_description = sanitize_textarea_field($_POST['event_description']);
    $event_start_datetime = sanitize_text_field($_POST['event_start_datetime']);
    $event_end_datetime = sanitize_text_field($_POST['event_end_datetime']);
    $event_venue = sanitize_text_field($_POST['event_venue']);

    $event_data = array(
        'post_title' => $event_title,
        'post_content' => $event_description,
        'post_type' => 'event',
        'post_status' => 'publish',
    );
    $event_id = wp_insert_post($event_data);

    if (!empty($_FILES['event_image__']['name'])) {
        $uploaded_image = $_FILES['event_image__'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploaded_image, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $file_name = $movefile['file'];
            $file_type = wp_check_filetype(basename($file_name), null);
            $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
            $wp_upload_dir = wp_upload_dir();

            // Set up the attachment data
            $attachment = array(
                'guid' => $wp_upload_dir['url'] . '/' . basename($file_name),
                'post_mime_type' => $file_type['type'],
                'post_title' => $attachment_title,
                'post_content' => '',
                'post_status' => 'inherit'
            );

            // Insert the attachment
            $attachment_id = wp_insert_attachment($attachment, $file_name);
            if (!is_wp_error($attachment_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_name);
                wp_update_attachment_metadata($attachment_id, $attachment_data);

                // Set the uploaded image as the post thumbnail
                set_post_thumbnail($event_id, $attachment_id);
            } else {
                echo '<p>Error inserting attachment: ' . $attachment_id->get_error_message() . '</p>';
            }
        } else {
            echo '<p>Error uploading event image: ' . $movefile['error'] . '</p>';
            return;
        }
    }

    if (!is_wp_error($event_id)) {

        update_post_meta($event_id, 'event_start_datetime', $event_start_datetime);
        update_post_meta($event_id, 'event_end_datetime', $event_end_datetime);
        update_post_meta($event_id, 'event_venue', $event_venue);

        // echo '<p>Event added successfully!</p>';
    } else {
        echo '<p>Error: Failed to add event.</p>';
    }
}


?>

<form id="event-form" method="post" action="" enctype="multipart/form-data">
    <label for="event_title">Event Title:</label>
    <input type="text" name="event_title" id="event_title" required>

    <label for="event_description">Event Description:</label>
    <textarea name="event_description" id="event_description" required></textarea>

    <label for="event_image__">Feature Image:</label>
    <input type="file" name="event_image__" id="event_image__">
    <br>
    
    <label for="event_start_datetime">Event Start Date & Time:</label>
    <input type="text" name="event_start_datetime" id="event_start_datetime" class="datetime-picker" required>

    <label for="event_end_datetime">Event End Date & Time:</label>
    <input type="text" name="event_end_datetime" id="event_end_datetime" class="datetime-picker" required>

    <label for="event_venue">Venue:</label>
    <input type="text" name="event_venue" id="event_venue" required>

    <input type="submit" name="submit_event" value="Add Event" onclick="return validateForm()">
</form>
<script>
    jQuery(document).ready(function ($) {
        $('.datetime-picker').datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm:ss',
            controlType: 'select',
            oneLine: true
        });
    });

    function validateForm() {
        var eventTitle = document.getElementById("event_title").value;
        var eventDescription = document.getElementById("event_description").value;
        var eventStartDatetime = document.getElementById("event_start_datetime").value;
        var eventEndDatetime = document.getElementById("event_end_datetime").value;
        var eventVenue = document.getElementById("event_venue").value;

        if (eventTitle.trim() == "" || eventDescription.trim() == "" || eventStartDatetime.trim() == "" || eventEndDatetime.trim() == "" || eventVenue.trim() == "") {
            alert("All fields are required");
            return false;
        }

        return true;
    }
</script>