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

// Add custom meta box for book details
add_action('add_meta_boxes', 'wp_book_add_meta_box');
function wp_book_add_meta_box() {
    add_meta_box(
        'wp_book_meta_box', // Meta box ID
        __('Book Details', 'wp-book'), // Title
        'wp_book_meta_box_callback', // Callback function
        'book', // Post type
        'normal', // Context
        'high' // Priority
    );
}

function wp_book_meta_box_callback($post) {
    wp_nonce_field(basename(__FILE__), 'wp_book_nonce');
    
    $meta = get_post_meta($post->ID, '_wp_book_meta', true);

    ?>
    <p>
        <label for="wp_book_author_name"><?php _e('Author Name', 'wp-book'); ?></label>
        <input type="text" id="wp_book_author_name" name="wp_book_author_name" value="<?php echo esc_attr($meta['author_name'] ?? ''); ?>" size="25" />
    </p>
    <p>
        <label for="wp_book_price"><?php _e('Price', 'wp-book'); ?></label>
        <input type="text" id="wp_book_price" name="wp_book_price" value="<?php echo esc_attr($meta['price'] ?? ''); ?>" size="25" />
    </p>
    <p>
        <label for="wp_book_publisher"><?php _e('Publisher', 'wp-book'); ?></label>
        <input type="text" id="wp_book_publisher" name="wp_book_publisher" value="<?php echo esc_attr($meta['publisher'] ?? ''); ?>" size="25" />
    </p>
    <p>
        <label for="wp_book_year"><?php _e('Year', 'wp-book'); ?></label>
        <input type="number" id="wp_book_year" name="wp_book_year" value="<?php echo esc_attr($meta['year'] ?? ''); ?>" size="25" />
    </p>
    <p>
        <label for="wp_book_edition"><?php _e('Edition', 'wp-book'); ?></label>
        <input type="text" id="wp_book_edition" name="wp_book_edition" value="<?php echo esc_attr($meta['edition'] ?? ''); ?>" size="25" />
    </p>
    <p>
        <label for="wp_book_url"><?php _e('URL', 'wp-book'); ?></label>
        <input type="url" id="wp_book_url" name="wp_book_url" value="<?php echo esc_attr($meta['url'] ?? ''); ?>" size="25" />
    </p>
    <?php
}

// Save the meta box data
add_action('save_post', 'wp_book_save_meta_box');
function wp_book_save_meta_box($post_id) {
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST['wp_book_nonce']) && wp_verify_nonce($_POST['wp_book_nonce'], basename(__FILE__))) ? true : false;

    if ($is_autosave || $is_revision || !$is_valid_nonce) {
        return;
    }

    $meta = array(
        'author_name' => sanitize_text_field($_POST['wp_book_author_name']),
        'price'       => floatval($_POST['wp_book_price']),
        'publisher'   => sanitize_text_field($_POST['wp_book_publisher']),
        'year'        => intval($_POST['wp_book_year']),
        'edition'     => sanitize_text_field($_POST['wp_book_edition']),
        'url'         => esc_url_raw($_POST['wp_book_url']),
    );

    update_post_meta($post_id, '_wp_book_meta', $meta);

    // Save meta to custom table
    wp_book_save_meta_to_custom_table($post_id, $meta);
}

function wp_book_save_meta_to_custom_table($post_id, $meta) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'book_meta';

    $exists = $wpdb->get_var($wpdb->prepare("SELECT meta_id FROM $table_name WHERE post_id = %d", $post_id));

    if ($exists) {
        $wpdb->update(
            $table_name,
            array(
                'author_name' => $meta['author_name'],
                'price'       => $meta['price'],
                'publisher'   => $meta['publisher'],
                'year'        => $meta['year'],
                'edition'     => $meta['edition'],
                'url'         => $meta['url'],
            ),
            array('post_id' => $post_id),
            array('%s', '%f', '%s', '%d', '%s', '%s'),
            array('%d')
        );
    } else {
        $wpdb->insert(
            $table_name,
            array(
                'post_id'     => $post_id,
                'author_name' => $meta['author_name'],
                'price'       => $meta['price'],
                'publisher'   => $meta['publisher'],
                'year'        => $meta['year'],
                'edition'     => $meta['edition'],
                'url'         => $meta['url'],
            ),
            array('%d', '%s', '%f', '%s', '%d', '%s', '%s')
        );
    }
}

// Deactivation Hook: Cleanup table
register_deactivation_hook(__FILE__, 'wp_book_delete_table');
function wp_book_delete_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'book_meta';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}

// Add custom settings page under the "Books" menu
add_action('admin_menu', 'wp_book_add_admin_menu');
function wp_book_add_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=book',     // Parent menu slug
        __('Book Settings', 'wp-book'), // Page title
        __('Settings', 'wp-book'),      // Menu title
        'manage_options',               // Capability
        'wp-book-settings',             // Menu slug
        'wp_book_settings_page'         // Callback function
    );
}

// Register settings
add_action('admin_init', 'wp_book_register_settings');
function wp_book_register_settings() {
    // Register a new setting for currency
    register_setting('wp_book_settings_group', 'wp_book_currency', array(
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'INR'
    ));

    // Register a new setting for number of books per page
    register_setting('wp_book_settings_group', 'wp_book_books_per_page', array(
        'type' => 'integer',
        'sanitize_callback' => 'intval',
        'default' => 10
    ));
}

// Create the settings page content
function wp_book_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Book Settings', 'wp-book'); ?></h1>
        <form method="post" action="options.php">
            <?php
            // Output settings fields for the registered settings
            settings_fields('wp_book_settings_group');
            do_settings_sections('wp_book_settings_group');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Currency', 'wp-book'); ?></th>
                    <td>
                        <input type="text" name="wp_book_currency" value="<?php echo esc_attr(get_option('wp_book_currency', 'INR')); ?>" />
                        <p class="description"><?php _e('Enter the currency code (e.g., USD, EUR, GBP)', 'wp-book'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Books per Page', 'wp-book'); ?></th>
                    <td>
                        <input type="number" name="wp_book_books_per_page" value="<?php echo esc_attr(get_option('wp_book_books_per_page', 10)); ?>" />
                        <p class="description"><?php _e('Number of books to display per page in book listings.', 'wp-book'); ?></p>
                    </td>
                </tr>
            </table>

            <?php
            // Output save settings button
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Use the settings on the front end or in the plugin
function wp_book_get_currency() {
    return get_option('wp_book_currency', 'INR');
}

function wp_book_get_books_per_page() {
    return get_option('wp_book_books_per_page', 10);
}

// Register the [book] shortcode
add_shortcode('book', 'wp_book_shortcode');
function wp_book_shortcode($atts) {
    // Set default attributes
    $atts = shortcode_atts(array(
        'id'           => '',
        'author_name'  => '',
        'year'         => '',
        'category'     => '',
        'tag'          => '',
        'publisher'     => ''
    ), $atts, 'book');

    // Query arguments
    $args = array('post_type' => 'book', 'posts_per_page' => -1, 'post_status' => 'publish');

    // Add conditions based on shortcode attributes
    if (!empty($atts['id'])) {
        $args['p'] = intval($atts['id']);
    }

    if (!empty($atts['author_name'])) {
        $args['meta_query'][] = array(
            'key'     => '_wp_book_meta',
            'value'   => esc_attr($atts['author_name']),
            'compare' => 'LIKE',
        );
    }

    if (!empty($atts['year'])) {
        $args['meta_query'][] = array(
            'key'     => '_wp_book_meta',
            'value'   => intval($atts['year']),
            'compare' => 'LIKE',
        );
    }

    if (!empty($atts['category'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'book_category',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($atts['category']),
        );
    }

    if (!empty($atts['tag'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'book_tag',
            'field'    => 'slug',
            'terms'    => sanitize_text_field($atts['tag']),
        );
    }

    if (!empty($atts['publisher'])) {
        $args['meta_query'][] = array(
            'key'     => '_wp_book_meta',
            'value'   => esc_attr($atts['publisher']),
            'compare' => 'LIKE',
        );
    }

    // Fetch the books
    $query = new WP_Query($args);
    $output = '';

    // Check if any books were found
    if ($query->have_posts()) {
        $output .= '<div class="book-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $meta = get_post_meta(get_the_ID(), '_wp_book_meta', true);

            $output .= '<div class="book">';
            $output .= '<h2>' . get_the_title() . '</h2>';
            $output .= '<p><strong>Author:</strong> ' . esc_html($meta['author_name'] ?? 'N/A') . '</p>';
            $output .= '<p><strong>Year:</strong> ' . esc_html($meta['year'] ?? 'N/A') . '</p>';
            $output .= '<p><strong>Publisher:</strong> ' . esc_html($meta['publisher'] ?? 'N/A') . '</p>';
            $output .= '<p><strong>Price:</strong> ' . esc_html($meta['price'] ?? 'N/A') . ' ' . wp_book_get_currency() . '</p>';
            $output .= '</div>';
        }
        $output .= '</div>';
        wp_reset_postdata();
    } else {
        $output .= '<p>' . __('No books found.', 'wp-book') . '</p>';
    }

    return $output;
}

// Enqueue block editor assets
add_action('enqueue_block_editor_assets', 'wp_book_enqueue_block_editor_assets');
function wp_book_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'wp-book-block',
        plugins_url('/js/book-block.js', __FILE__), 
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'), // Dependencies
        null,
        true // Load in footer
    );
}

// Fetch books by category
add_action('rest_api_init', function () {
    register_rest_route('wp-book/v1', '/books', array(
        'methods' => 'GET',
        'callback' => 'wp_book_get_books_by_category',
        'permission_callback' => '__return_true',
    ));
});

function wp_book_get_books_by_category(WP_REST_Request $request) {
    $category_id = $request->get_param('category_id');

    $args = array(
        'post_type' => 'book',
        'tax_query' => array(
            array(
                'taxonomy' => 'book_category',
                'field' => 'term_id',
                'terms' => $category_id,
            ),
        ),
    );

    $query = new WP_Query($args);
    $books = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $books[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'author' => get_post_meta(get_the_ID(), '_wp_book_meta', true)['author_name'] ?? '',
                'price' => get_post_meta(get_the_ID(), '_wp_book_meta', true)['price'] ?? '',
                'url' => get_post_meta(get_the_ID(), '_wp_book_meta', true)['url'] ?? '',
            );
        }
        wp_reset_postdata();
    }

    return new WP_REST_Response($books, 200);
}

// Add a custom dashboard widget
add_action('wp_dashboard_setup', 'wp_book_add_dashboard_widget');
function wp_book_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'wp_book_top_categories', // Widget slug
        __('Top 5 Book Categories', 'wp-book'), // Title
        'wp_book_display_top_categories' // Display function
    );
}

// Display the top categories in the dashboard widget
function wp_book_display_top_categories() {
    // Get the top 5 book categories based on count
    $args = array(
        'taxonomy'   => 'book_category',
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 5,
    );

    $terms = get_terms($args);

    if (!empty($terms) && !is_wp_error($terms)) {
        echo '<ul>';
        foreach ($terms as $term) {
            echo '<li>' . esc_html($term->name) . ' (' . intval($term->count) . ')</li>';
        }
        echo '</ul>';
    } else {
        echo __('No categories found.', 'wp-book');
    }
}
