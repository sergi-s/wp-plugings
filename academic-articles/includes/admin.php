<?php

// Add menu items in admin
add_action('admin_menu', 'academic_articles_menu');

function academic_articles_menu()
{
    // Add "Add New Article" page
    add_menu_page(
        'Academic Articles',
        'Academic Articles',
        'manage_options',
        'academic-articles-create',
        'academic_articles_create_page',
        'dashicons-welcome-learn-more',
        20
    );

    // Add "List Articles" page
    add_submenu_page(
        'academic-articles-create',
        'List Articles',
        'List Articles',
        'manage_options',
        'academic-articles-list',
        'academic_articles_list_page'
    );

    // Add "Update/Delete Article" page
    add_submenu_page(
        'academic-articles-create',
        'Update/Delete Article',
        'Update/Delete Article',
        'manage_options',
        'academic-articles-update',
        'academic_articles_update_page'
    );
}

// Admin Page to Add New Article
function academic_articles_create_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'academic_articles';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_article'])) {
        $wpdb->insert($table_name, [
            'title'                  => sanitize_text_field($_POST['title']),
            'authors'                => sanitize_text_field($_POST['authors']),
            'publication_year'       => intval($_POST['publication_year']),
            'journal'                => sanitize_text_field($_POST['journal']),
            'abstract'               => sanitize_textarea_field($_POST['abstract']),
            'article_keywords'       => sanitize_text_field($_POST['article_keywords']),
            'subject_area'           => sanitize_text_field($_POST['subject_area']),
            'scale_of_research'      => sanitize_text_field($_POST['scale_of_research']),
            'main_geographical_focus' => sanitize_text_field($_POST['main_geographical_focus']),
            'methodology'            => sanitize_text_field($_POST['methodology']),
            'data_type'              => sanitize_text_field($_POST['data_type']),
            'full_citation'          => sanitize_textarea_field($_POST['full_citation']),
        ]);
        echo '<div class="updated"><p>Article Added!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Add New Academic Article</h1>
        <form method="post">
            <table class="form-table">
                <tr><th>Title</th><td><input type="text" name="title" required></td></tr>
                <tr><th>Authors</th><td><input type="text" name="authors" required></td></tr>
                <tr><th>Publication Year</th><td><input type="number" name="publication_year"></td></tr>
                <tr><th>Journal</th><td><input type="text" name="journal"></td></tr>
                <tr><th>Abstract</th><td><textarea name="abstract"></textarea></td></tr>
                <tr><th>Keywords</th><td><input type="text" name="article_keywords"></td></tr>
                <tr><th>Subject Area</th><td><input type="text" name="subject_area"></td></tr>
                <tr><th>Scale of Research</th><td><input type="text" name="scale_of_research"></td></tr>
                <tr><th>Main Geographical Focus</th><td><input type="text" name="main_geographical_focus"></td></tr>
                <tr><th>Methodology</th><td><input type="text" name="methodology"></td></tr>
                <tr><th>Data Type</th><td><input type="text" name="data_type"></td></tr>
                <tr><th>Full Citation</th><td><textarea name="full_citation"></textarea></td></tr>
            </table>
            <input type="submit" name="add_article" class="button button-primary" value="Add Article">
        </form>
    </div>
    <?php
}

// Admin Page to List Articles
function academic_articles_list_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'academic_articles';

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY publication_year DESC");

    ?>
    <div class="wrap">
        <h1>List of Academic Articles</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Authors</th>
                    <th>Publication Year</th>
                    <th>Journal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $article) { ?>
                <tr>
                    <td><?php echo esc_html($article->title); ?></td>
                    <td><?php echo esc_html($article->authors); ?></td>
                    <td><?php echo esc_html($article->publication_year); ?></td>
                    <td><?php echo esc_html($article->journal); ?></td>
                    <td>
                        <a href="?page=academic-articles-update&article_id=<?php echo $article->id; ?>">Edit</a> | 
                        <a href="?page=academic-articles-list&delete_article=<?php echo $article->id; ?>" onclick="return confirm('Are you sure you want to delete this article?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php

    // Handle Deletion
    if (isset($_GET['delete_article'])) {
        $article_id = intval($_GET['delete_article']);
        $wpdb->delete($table_name, ['id' => $article_id]);
        echo '<div class="updated"><p>Article Deleted!</p></div>';
        wp_redirect(admin_url('admin.php?page=academic-articles-list'));
        exit;
    }
}

// Admin Page to Update/Delete Article
function academic_articles_update_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'academic_articles';

    if (isset($_GET['article_id'])) {
        $article_id = intval($_GET['article_id']);
        $article = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $article_id");

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_article'])) {
            $wpdb->update($table_name, [
                'title'                  => sanitize_text_field($_POST['title']),
                'authors'                => sanitize_text_field($_POST['authors']),
                'publication_year'       => intval($_POST['publication_year']),
                'journal'                => sanitize_text_field($_POST['journal']),
                'abstract'               => sanitize_textarea_field($_POST['abstract']),
                'article_keywords'       => sanitize_text_field($_POST['article_keywords']),
                'subject_area'           => sanitize_text_field($_POST['subject_area']),
                'scale_of_research'      => sanitize_text_field($_POST['scale_of_research']),
                'main_geographical_focus' => sanitize_text_field($_POST['main_geographical_focus']),
                'methodology'            => sanitize_text_field($_POST['methodology']),
                'data_type'              => sanitize_text_field($_POST['data_type']),
                'full_citation'          => sanitize_textarea_field($_POST['full_citation']),
            ], ['id' => $article_id]);
            echo '<div class="updated"><p>Article Updated!</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1>Update Article</h1>
        <?php if (isset($article)) { ?>
            <form method="post">
                <table class="form-table">
                    <tr><th>Title</th><td><input type="text" name="title" value="<?php echo esc_attr($article->title); ?>" required></td></tr>
                    <tr><th>Authors</th><td><input type="text" name="authors" value="<?php echo esc_attr($article->authors); ?>" required></td></tr>
                    <tr><th>Publication Year</th><td><input type="number" name="publication_year" value="<?php echo esc_attr($article->publication_year); ?>"></td></tr>
                    <tr><th>Journal</th><td><input type="text" name="journal" value="<?php echo esc_attr($article->journal); ?>"></td></tr>
                    <tr><th>Abstract</th><td><textarea name="abstract"><?php echo esc_textarea($article->abstract); ?></textarea></td></tr>
                    <tr><th>Keywords</th><td><input type="text" name="article_keywords" value="<?php echo esc_attr($article->article_keywords); ?>"></td></tr>
                    <tr><th>Subject Area</th><td><input type="text" name="subject_area" value="<?php echo esc_attr($article->subject_area); ?>"></td></tr>
                    <tr><th>Scale of Research</th><td><input type="text" name="scale_of_research" value="<?php echo esc_attr($article->scale_of_research); ?>"></td></tr>
                    <tr><th>Main Geographical Focus</th><td><input type="text" name="main_geographical_focus" value="<?php echo esc_attr($article->main_geographical_focus); ?>"></td></tr>
                    <tr><th>Methodology</th><td><input type="text" name="methodology" value="<?php echo esc_attr($article->methodology); ?>"></td></tr>
                    <tr><th>Data Type</th><td><input type="text" name="data_type" value="<?php echo esc_attr($article->data_type); ?>"></td></tr>
                    <tr><th>Full Citation</th><td><textarea name="full_citation"><?php echo esc_textarea($article->full_citation); ?></textarea></td></tr>
                </table>
                <input type="submit" name="update_article" class="button button-primary" value="Update Article">
            </form>
        <?php } else { ?>
            <p>No article selected for editing.</p>
        <?php } ?>
    </div>
    <?php
}
?>
