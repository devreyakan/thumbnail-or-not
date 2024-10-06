<?php
// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
Plugin Name: Thumbnail or Not
Description: Adds a "Thumbnail Status" column to the media library to check if an image is used as a thumbnail.
Version: 1.0
Author: devreyakan.com
License: GPL-3.0
*/

// Add a column to the Media Library to show thumbnail status
function ton_add_thumbnail_status_column($columns) {
    $columns['thumbnail_status'] = 'Thumbnail Status';
    return $columns;
}
add_filter('manage_upload_columns', 'ton_add_thumbnail_status_column');

// Display whether the image is used as a thumbnail in the new column
function ton_show_thumbnail_status_column($column_name, $post_id) {
    if ($column_name === 'thumbnail_status') {
        if (ton_is_image_used_as_thumbnail($post_id)) {
            echo '<strong style="color: green;">Yes</strong>';
        } else {
            echo '<strong style="color: red;">No</strong>';
        }
    }
}
add_action('manage_media_custom_column', 'ton_show_thumbnail_status_column', 10, 2);

// Check if an image is used as a thumbnail
function ton_is_image_used_as_thumbnail($image_id) {
    $posts = get_posts(array(
        'meta_key' => '_thumbnail_id',
        'meta_value' => $image_id,
        'post_type' => 'any',
        'posts_per_page' => -1
    ));
    return !empty($posts);
}

// Make the Thumbnail Status column sortable
function ton_sortable_thumbnail_status_column($columns) {
    $columns['thumbnail_status'] = 'thumbnail_status';
    return $columns;
}
add_filter('manage_upload_sortable_columns', 'ton_sortable_thumbnail_status_column');

// Custom sorting logic for Thumbnail Status column
function ton_sort_by_thumbnail_status($query) {
    if (!is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('thumbnail_status' === $orderby) {
        global $wpdb;

        // Custom sorting based on thumbnail usage
        $query->set('meta_query', array(
            'relation' => 'OR',
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'EXISTS',
            ),
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'NOT EXISTS',
            )
        ));

        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'ton_sort_by_thumbnail_status');
