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
