<?php
/**
 * Plugin Name: Academic Articles Manager
 * Description: A plugin to store, manage, and display academic articles with search functionality.
 * Version:     1.0.0
 * Author:      Sergi Rizkallah
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/db-setup.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin.php';
include_once plugin_dir_path(__FILE__) . 'includes/frontend.php';

// Enqueue styles and scripts
function academic_articles_enqueue_scripts() {
    wp_enqueue_style('academic-articles-styles', plugin_dir_url(__FILE__) . 'includes/styles.css');
    wp_enqueue_script('academic-articles-scripts', plugin_dir_url(__FILE__) . 'includes/scripts.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'academic_articles_enqueue_scripts');
add_action('admin_enqueue_scripts', 'academic_articles_enqueue_scripts');
