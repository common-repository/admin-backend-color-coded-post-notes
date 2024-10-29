<?php
/*
Plugin Name: Admin Backend Color Coded Post Notes
Description: Allows administrators to leave color-coded notes on posts and pages within the editor.
Version: 0.2
Author: The 215 Guys
Author URI: https://www.the215guys.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access to this file.
if (!defined('ABSPATH')) {
    exit;
}

function abccpn_add_custom_meta_box() {
    add_meta_box(
        'abccpn_meta_box',          // Unique ID
        'Color Coded Notes',       // Box title
        'abccpn_meta_box_html',     // Content callback, must be of type callable
        'post',                    // Post type
        'side'                     // Context
    );
}
add_action('add_meta_boxes', 'abccpn_add_custom_meta_box');

function abccpn_meta_box_html($post) {
    $value = get_post_meta($post->ID, '_abccpn_color_note', true);
    $color = get_post_meta($post->ID, '_abccpn_color', true);
    wp_nonce_field('abccpn_save_meta_box_data', 'abccpn_meta_box_nonce');
    ?>
    <label for="abccpn_field">Color Note:</label>
    <textarea id="abccpn_field" name="abccpn_field" rows="4" style="width:100%"><?php echo esc_textarea($value); ?></textarea>
    <p>Select Note Color: <input type="color" name="abccpn_color_picker" value="<?php echo esc_attr($color) ?: '#ff0000'; ?>"></p>
    <?php
}

function abccpn_save_postdata($post_id) {
    // Check if nonce is set.
    if (!isset($_POST['abccpn_meta_box_nonce'])) {
        return;
    }
    
    // Verify that the nonce is valid.
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['abccpn_meta_box_nonce'])), 'abccpn_save_meta_box_data')) {
        return;
    }
    
    // Check if the user has permission to save the data.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if the data is set and save it.
    if (array_key_exists('abccpn_field', $_POST)) {
        update_post_meta(
            $post_id,
            '_abccpn_color_note',
            sanitize_textarea_field($_POST['abccpn_field'])
        );
    }

    if (array_key_exists('abccpn_color_picker', $_POST)) {
        update_post_meta(
            $post_id,
            '_abccpn_color',
            sanitize_hex_color($_POST['abccpn_color_picker'])
        );
    }
}
add_action('save_post', 'abccpn_save_postdata');
