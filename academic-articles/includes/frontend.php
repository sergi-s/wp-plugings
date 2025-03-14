<?php

add_shortcode('academic_articles_list', 'display_academic_articles');

function display_academic_articles()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'academic_articles';

    // Get current page number
    $page = isset($_GET['page_num']) ? max(1, intval($_GET['page_num'])) : 1;
    $articles_per_page = 5;
    $offset = ($page - 1) * $articles_per_page;

    // Get selected filters
    $selected_journal = isset($_GET['journal']) ? trim($_GET['journal']) : '';
    $selected_author = isset($_GET['authors']) ? trim($_GET['authors']) : '';
    $selected_year = isset($_GET['publication_year']) ? trim($_GET['publication_year']) : '';
    $selected_subject = isset($_GET['subject_area']) ? trim($_GET['subject_area']) : '';
    $selected_geo_focus = isset($_GET['main_geographical_focus']) ? trim($_GET['main_geographical_focus']) : '';
    $abstract_search = isset($_GET['abstract_search']) ? trim($_GET['abstract_search']) : '';

    $where_clauses = [];
    $params = [];

    if (!empty($selected_journal)) {
        $where_clauses[] = "journal = %s";
        $params[] = $selected_journal;
    }
    if (!empty($selected_author)) {
        $where_clauses[] = "authors LIKE %s";
        $params[] = "%{$selected_author}%";
    }
    if (!empty($selected_year)) {
        $where_clauses[] = "publication_year = %d";
        $params[] = intval($selected_year);
    }
    if (!empty($selected_subject)) {
        $where_clauses[] = "subject_area = %s";
        $params[] = $selected_subject;
    }
    if (!empty($selected_geo_focus)) {
        $where_clauses[] = "main_geographical_focus = %s";
        $params[] = $selected_geo_focus;
    }
    if (!empty($abstract_search)) {
        $where_clauses[] = "abstract LIKE %s";
        $params[] = "%{$abstract_search}%";
    }

    $where_sql = $where_clauses ? "WHERE " . implode(" AND ", $where_clauses) : "";

    // Get total articles count (for pagination)
    $total_articles = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name $where_sql", ...$params));
    $total_pages = ceil($total_articles / $articles_per_page);

    // Fetch filtered and paginated results
    $query = "SELECT * FROM $table_name $where_sql ORDER BY publication_year DESC, id ASC LIMIT %d OFFSET %d";
    $params[] = $articles_per_page;
    $params[] = $offset;
    $articles = $wpdb->get_results($wpdb->prepare($query, ...$params));

    // Fetch unique dropdown values
    $authors = $wpdb->get_col("SELECT DISTINCT authors FROM $table_name");
    $keywords = $wpdb->get_col("SELECT DISTINCT article_keywords FROM $table_name");
    $subjects = $wpdb->get_col("SELECT DISTINCT subject_area FROM $table_name ORDER BY subject_area ASC");
    $geo_focuses = $wpdb->get_col("SELECT DISTINCT main_geographical_focus FROM $table_name ORDER BY main_geographical_focus ASC");

    // Normalize authors and keywords (split by ";" and remove duplicates)
    $authors = array_unique(array_map('trim', preg_split('/\s*;\s*/', implode(';', $authors))));
    $keywords = array_unique(array_map('trim', preg_split('/\s*;\s*/', implode(';', $keywords))));

    ob_start();
?>
    <div id="articles-container">
        <h2>Academic Articles</h2>

        <div class="search-container">
            <form method="GET">
                <input type="hidden" name="page_num" value="1">

                <label>Author:</label>
                <select name="authors" id="author-dropdown">
                    <option value="">All</option>
                    <?php foreach ($authors as $author) : ?>
                        <option value="<?php echo esc_attr($author); ?>" <?php selected($selected_author, $author); ?>><?php echo esc_html($author); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Keywords:</label>
                <select name="article_keywords" id="keyword-dropdown">
                    <option value="">All</option>
                    <?php foreach ($keywords as $keyword) : ?>
                        <option value="<?php echo esc_attr($keyword); ?>"><?php echo esc_html($keyword); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Subject Area:</label>
                <select name="subject_area" id="subject-dropdown">
                    <option value="">All</option>
                    <?php foreach ($subjects as $subject) : ?>
                        <option value="<?php echo esc_attr($subject); ?>" <?php selected($selected_subject, $subject); ?>><?php echo esc_html($subject); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Geographical Focus:</label>
                <select name="main_geographical_focus" id="geo-dropdown">
                    <option value="">All</option>
                    <?php foreach ($geo_focuses as $geo_focus) : ?>
                        <option value="<?php echo esc_attr($geo_focus); ?>" <?php selected($selected_geo_focus, $geo_focus); ?>><?php echo esc_html($geo_focus); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Search Abstract:</label>
                <input type="text" name="abstract_search" placeholder="Search abstracts..." value="<?php echo esc_attr($abstract_search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <!-- Display the actual articles results -->
        <div class="articles-results">
            <?php if (empty($articles)) : ?>
                <p>No articles found.</p>
            <?php else : ?>
                <p>Showing <?php echo count($articles); ?> of <?php echo $total_articles; ?> articles</p>
                <?php foreach ($articles as $article) : ?>
                    <div class="article-item">
                        <h3><?php echo esc_html($article->title); ?></h3>
                        <p class="article-meta">
                            <strong>Authors:</strong> <?php echo esc_html($article->authors); ?><br>
                            <strong>Journal:</strong> <?php echo esc_html($article->journal); ?> (<?php echo esc_html($article->publication_year); ?>)<br>
                            <strong>Subject Area:</strong> <?php echo esc_html($article->subject_area); ?><br>
                            <strong>Geographical Focus:</strong> <?php echo esc_html($article->main_geographical_focus); ?>
                        </p>
                        <div class="article-abstract">
                            <strong>Abstract:</strong>
                            <p><?php echo nl2br(esc_html($article->abstract)); ?></p>
                        </div>
                        <?php if (!empty($article->article_keywords)) : ?>
                            <p><strong>Keywords:</strong> <?php echo esc_html($article->article_keywords); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($article->doi_or_link)) : ?>
                            <p><a href="<?php echo esc_url($article->doi_or_link); ?>" target="_blank">View Article</a></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page_num=<?php echo $page - 1; ?>&authors=<?php echo urlencode($selected_author); ?>&subject_area=<?php echo urlencode($selected_subject); ?>&main_geographical_focus=<?php echo urlencode($selected_geo_focus); ?>&abstract_search=<?php echo urlencode($abstract_search); ?>" class="pagination-button">&laquo; Previous</a>
            <?php endif; ?>

            <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <input type="number" id="page-input" value="<?php echo $page; ?>" min="1" max="<?php echo $total_pages; ?>">
            <button onclick="goToPage(<?php echo $total_pages; ?>)">Go</button>

            <?php if ($page < $total_pages) : ?>
                <a href="?page_num=<?php echo $page + 1; ?>&authors=<?php echo urlencode($selected_author); ?>&subject_area=<?php echo urlencode($selected_subject); ?>&main_geographical_focus=<?php echo urlencode($selected_geo_focus); ?>&abstract_search=<?php echo urlencode($abstract_search); ?>" class="pagination-button">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <script>
        // Apply Select2 to the dropdowns
        jQuery(document).ready(function() {
            jQuery('#author-dropdown, #keyword-dropdown, #subject-dropdown, #geo-dropdown').select2({
                placeholder: "Select an option or type to search",
                allowClear: true
            });
        });
    </script>

    <style>
<?php
    return ob_get_clean();
}
