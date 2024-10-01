<?php
/*
Plugin Name: Thumbnail or Not
Description: A column showing featured images of images is added in the media library.
Version: 1.0
Author: devreyakan.com
License: GPL-3.0
*/

// Medya kütüphanesine yeni bir sütun ekle
function add_thumbnail_status_column($columns) {
    $columns['thumbnail_status'] = 'Thumbnail Status';
    return $columns;
}
add_filter('manage_upload_columns', 'add_thumbnail_status_column');

// Yeni sütuna veri ekle
function show_thumbnail_status_column($column_name, $post_id) {
    if ($column_name === 'thumbnail_status') {
        if (is_image_used_as_thumbnail($post_id)) {
            echo '<strong style="color: green;">Yes</strong>';
        } else {
            echo '<strong style="color: red;">No</strong>';
        }
    }
}
add_action('manage_media_custom_column', 'show_thumbnail_status_column', 10, 2);

// Thumbnail olup olmadığını kontrol eden fonksiyon
function is_image_used_as_thumbnail($image_id) {
    $posts = get_posts(array(
        'meta_key' => '_thumbnail_id',
        'meta_value' => $image_id,
        'post_type' => 'any',
        'posts_per_page' => -1
    ));
    return !empty($posts);
}

// Medya kütüphanesinde Thumbnail sütununu sıralanabilir yap
function sortable_thumbnail_status_column($columns) {
    $columns['thumbnail_status'] = 'thumbnail_status';
    return $columns;
}
add_filter('manage_upload_sortable_columns', 'sortable_thumbnail_status_column');

// Sıralama işlevi
function sort_by_thumbnail_status($query) {
    if ( ! is_admin() || ! $query->is_main_query() || $query->get('post_type') !== 'attachment' ) {
        return;
    }

    // Sıralama için meta verisi oluştur
    $meta_query = array(
        'relation' => 'OR',
        array(
            'key' => '_thumbnail_id',
            'compare' => 'EXISTS',
        ),
        array(
            'key' => '_thumbnail_id',
            'compare' => 'NOT EXISTS',
        )
    );

    $query->set('meta_query', $meta_query);
    $query->set('orderby', 'thumbnail_status');
}
add_action('pre_get_posts', 'sort_by_thumbnail_status');
