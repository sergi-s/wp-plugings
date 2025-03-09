<?php

// Activation Hook - Create Database Table
register_activation_hook(__FILE__, 'academic_articles_install');

function academic_articles_install()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'academic_articles';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        title TEXT NOT NULL,
        authors TEXT NOT NULL,
        publication_year INT,
        journal TEXT,
        abstract TEXT,
        article_keywords TEXT,
        subject_area TEXT,
        scale_of_research TEXT,
        main_geographical_focus TEXT,
        methodology TEXT,
        data_type TEXT,
        full_citation TEXT,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Deactivation Hook - Cleanup
function deactivate_academic_articles_list()
{
    remove_shortcode('academic_articles_list');
}

register_deactivation_hook(__FILE__, 'deactivate_academic_articles_list');
