<?php
function add_event_manager_role() {
    add_role(
        'event_manager',
        'Event Manager',
        array(
            'read' => true,
            'edit_posts' => true, 
            'delete_posts' => true, 
            'publish_posts' => true, 
            'upload_files' => true, 
            'edit_published_posts' => true, 
            'delete_published_posts' => true, 
            'edit_others_posts' => true, 
            'delete_others_posts' => true, 
            'edit_private_posts' => true, 
            'read_private_posts' => true, 
            'edit_post' => true, 
            'delete_post' => true, 
            'edit_event_list' => true, 
            'event_delete' => true, 
            'event_edit' => true, 
            'event_add' => true, 
        )
    );
}
add_action('init', 'add_event_manager_role');

add_action('init', 'create_event_post_type');
function create_event_post_type() {
    register_post_type('event', array(
        'labels' => array(
            'name' => 'Events',
            'singular_name' => 'Event',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'author', 'comments'),
    ));
}
