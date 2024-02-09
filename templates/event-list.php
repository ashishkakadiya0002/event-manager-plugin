<?php
$events_query = new WP_Query(array(
    'post_type' => 'event',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => 'event_start_datetime',
    'orderby' => 'meta_value',
    'order' => 'DESC',
));

if ($events_query->have_posts()) :
?>
    <style>
        .event-list {
            list-style: none;
            padding: 0;
        }

        .event-item {
            border: 1px solid #ccc;
            padding: 20px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .event-item img {
            float: left;
            margin-right: 20px;
            max-width: 100px;
            height: auto;
        }

        .event-details {
            overflow: hidden;
        }

        .event-details h2 {
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .event-details p {
            margin: 5px 0;
        }

        .edit-delete-buttons {
            margin-top: 10px;
        }

        .edit-delete-buttons button {
            background-color: #0073aa;
            color: #fff;
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            margin-right: 10px;
            text-transform: uppercase;
        }

        .edit-delete-buttons button a {
            color: #fff;
            text-decoration: none;
        }
    </style>

    <ul class="event-list">
        <?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
            <li class="event-item">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="event-thumbnail">
                        <?php the_post_thumbnail('thumbnail'); ?>
                    </div>
                <?php endif; ?>
                <div class="event-details">
                    <h2><?php the_title(); ?></h2>
                    <p><?php echo get_the_content(); ?></p>
                    <p><strong>Start Time:</strong> <?php echo get_post_meta(get_the_ID(), 'event_start_datetime', true); ?></p>
                    <p><strong>End Time:</strong> <?php echo get_post_meta(get_the_ID(), 'event_end_datetime', true); ?></p>
                    <p><strong>Venue:</strong> <?php echo get_post_meta(get_the_ID(), 'event_venue', true); ?></p>
                    <p><strong>Created by:</strong> <?php echo get_the_author(); ?></p>
                </div>
                <?php if (is_user_logged_in() && (current_user_can('event_manager') || current_user_can('administrator'))) : ?>
                    <div class="edit-delete-buttons">
                        <button><a href="<?php echo get_edit_post_link(); ?>">Edit</a></button>
                        <button>
                            <a href="<?php echo get_delete_post_link(get_the_ID()); ?>" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                        </button>
                    </div>
                <?php endif; ?>
            </li>
        <?php endwhile; ?>
    </ul>

<?php
    wp_reset_postdata();
else :
    echo 'No events found.';
endif;
?>
