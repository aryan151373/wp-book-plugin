<?php
/*
Plugin Name: WP Book Plugin
Plugin URI:  https://example.com
Description: A plugin to manage books with custom post types and taxonomies.
Version:     1.0
Author:      Aryan Deswal
Author URI:  https://example.com
License:     GPL2
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Register custom post type 'Book'
add_action('init', 'wp_book_register_post_type');
function wp_book_register_post_type()
{
    $labels = array(
        'name' => _x('Books', 'Post type general name', 'wp-book'),
        'singular_name' => _x('Book', 'Post type singular name', 'wp-book'),
        'menu_name' => _x('Books', 'Admin Menu text', 'wp-book'),
        'name_admin_bar' => _x('Book', 'Add New on Toolbar', 'wp-book'),
        'add_new' => __('Add New', 'wp-book'),
        'add_new_item' => __('Add New Book', 'wp-book'),
        'new_item' => __('New Book', 'wp-book'),
        'edit_item' => __('Edit Book', 'wp-book'),
        'view_item' => __('View Book', 'wp-book'),
        'all_items' => __('All Books', 'wp-book'),
        'search_items' => __('Search Books', 'wp-book'),
        'not_found' => __('No books found.', 'wp-book'),
        'not_found_in_trash' => __('No books found in Trash.', 'wp-book'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'book'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
    );

    register_post_type('book', $args);
}

// Register hierarchical taxonomy 'Book Category'
add_action('init', 'wp_book_register_taxonomy_category');
function wp_book_register_taxonomy_category()
{
    $labels = array(
        'name' => _x('Book Categories', 'taxonomy general name', 'wp-book'),
        'singular_name' => _x('Book Category', 'taxonomy singular name', 'wp-book'),
        'search_items' => __('Search Book Categories', 'wp-book'),
        'all_items' => __('All Book Categories', 'wp-book'),
        'parent_item' => __('Parent Book Category', 'wp-book'),
        'parent_item_colon' => __('Parent Book Category:', 'wp-book'),
        'edit_item' => __('Edit Book Category', 'wp-book'),
        'update_item' => __('Update Book Category', 'wp-book'),
        'add_new_item' => __('Add New Book Category', 'wp-book'),
        'new_item_name' => __('New Book Category Name', 'wp-book'),
        'menu_name' => __('Book Categories', 'wp-book'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'book-category'),
    );

    register_taxonomy('book_category', array('book'), $args);
}

// Register non-hierarchical taxonomy 'Book Tag'
add_action('init', 'wp_book_register_taxonomy_tag');
function wp_book_register_taxonomy_tag()
{
    $labels = array(
        'name' => _x('Book Tags', 'taxonomy general name', 'wp-book'),
        'singular_name' => _x('Book Tag', 'taxonomy singular name', 'wp-book'),
        'search_items' => __('Search Book Tags', 'wp-book'),
        'popular_items' => __('Popular Book Tags', 'wp-book'),
        'all_items' => __('All Book Tags', 'wp-book'),
        'edit_item' => __('Edit Book Tag', 'wp-book'),
        'update_item' => __('Update Book Tag', 'wp-book'),
        'add_new_item' => __('Add New Book Tag', 'wp-book'),
        'new_item_name' => __('New Book Tag Name', 'wp-book'),
        'separate_items_with_commas' => __('Separate book tags with commas', 'wp-book'),
        'add_or_remove_items' => __('Add or remove book tags', 'wp-book'),
        'choose_from_most_used' => __('Choose from the most used book tags', 'wp-book'),
        'menu_name' => __('Book Tags', 'wp-book'),
    );

    $args = array(
        'hierarchical' => false,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var' => true,
        'rewrite' => array('slug' => 'book-tag'),
    );

    register_taxonomy('book_tag', 'book', $args);
}

// Activation Hook: Create custom database table for book metadata
register_activation_hook(__FILE__, 'wp_book_create_table');
function wp_book_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'book_meta';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) UNSIGNED NOT NULL,
        author_name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        publisher VARCHAR(255) NOT NULL,
        year INT(4) NOT NULL,
        edition VARCHAR(100) NOT NULL,
        url VARCHAR(255) NOT NULL,
        PRIMARY KEY (meta_id),
        FOREIGN KEY (post_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
    ) $charset_collate;";

    $wpdb->query($sql);
}



// Deactivation Hook: Cleanup table
register_deactivation_hook(__FILE__, 'wp_book_delete_table');
function wp_book_delete_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'book_meta';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}