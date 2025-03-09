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

    // Handle search query
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $search_by = isset($_GET['search_by']) ? $_GET['search_by'] : 'title';

    $where_clause = "";
    $params = [];

    if (!empty($search)) {
        if ($search_by === "universal") {
            $where_clause = "WHERE title LIKE %s OR authors LIKE %s OR journal LIKE %s OR article_keywords LIKE %s OR subject_area LIKE %s OR main_geographical_focus LIKE %s";
            $params = array_fill(0, 6, "%{$search}%");
        } else {
            $where_clause = "WHERE $search_by LIKE %s";
            $params[] = "%{$search}%";
        }
    }

    // Get total articles count (for pagination)
    $total_articles = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name $where_clause", ...$params));
    $total_pages = ceil($total_articles / $articles_per_page);

    // Fetch filtered and paginated results
    $query = "SELECT * FROM $table_name $where_clause ORDER BY publication_year DESC, id ASC LIMIT %d OFFSET %d";
    $params[] = $articles_per_page;
    $params[] = $offset;
    $articles = $wpdb->get_results($wpdb->prepare($query, ...$params));

    ob_start();
?>
    <div id="articles-container">
        <h2>Academic Articles</h2>

        <div class="search-container">
            <form method="GET">
                <input type="hidden" name="page_num" value="1">
                <input type="text" name="search" id="search" placeholder="Search..." value="<?php echo esc_attr($search); ?>">
                <select name="search_by" id="search_by">
                    <option value="title" <?php selected($search_by, 'title'); ?>>Title</option>
                    <option value="authors" <?php selected($search_by, 'authors'); ?>>Authors</option>
                    <option value="publication_year" <?php selected($search_by, 'publication_year'); ?>>Publication Year</option>
                    <option value="journal" <?php selected($search_by, 'journal'); ?>>Journal</option>
                    <option value="article_keywords" <?php selected($search_by, 'article_keywords'); ?>>Keywords</option>
                    <option value="subject_area" <?php selected($search_by, 'subject_area'); ?>>Subject Area</option>
                    <option value="main_geographical_focus" <?php selected($search_by, 'main_geographical_focus'); ?>>Geographical Focus</option>
                    <option value="universal" <?php selected($search_by, 'universal'); ?>>Universal Search</option>
                </select>
                <button type="submit">Search</button>
            </form>
        </div>

        <div id="articles-list">
            <?php if ($articles) : ?>
                <?php foreach ($articles as $article) : ?>
                    <div class="article-card">
                        <h3><?php echo esc_html($article->title); ?></h3>
                        <p data-field="authors"><strong>Authors:</strong> <?php echo esc_html($article->authors); ?></p>
                        <p data-field="publication_year"><strong>Year:</strong> <?php echo esc_html($article->publication_year); ?></p>
                        <p data-field="journal"><strong>Journal:</strong> <?php echo esc_html($article->journal); ?></p>

                        <!-- Abstract with "Read More" feature -->
                        <p data-field="abstract"><strong>Abstract:</strong>
                            <?php
                            $abstract_text = esc_html($article->abstract);
                            $excerpt = wp_trim_words($abstract_text, 30, '...'); // Trim to 30 words
                            if (strlen($abstract_text) > strlen($excerpt)) : ?>
                                <span class="short-abstract"><?php echo $excerpt; ?></span>
                                <span class="full-abstract" style="display: none;"><?php echo $abstract_text; ?></span>
                                <a href="javascript:void(0);" class="read-more" onclick="toggleAbstract(this)">Read More</a>
                            <?php else : ?>
                                <?php echo $abstract_text; ?>
                            <?php endif; ?>
                        </p>

                        <p data-field="article_keywords"><strong>Keywords:</strong> <?php echo esc_html($article->article_keywords); ?></p>
                        <p data-field="subject_area"><strong>Subject Area:</strong> <?php echo esc_html($article->subject_area); ?></p>
                        <p data-field="main_geographical_focus"><strong>Geographical Focus:</strong> <?php echo esc_html($article->main_geographical_focus); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No articles found.</p>
            <?php endif; ?>
        </div>

        <div id="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page_num=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo urlencode($search_by); ?>" class="pagination-button">&laquo; Previous</a>
            <?php endif; ?>

            <input type="number" id="page-input" value="<?php echo $page; ?>" min="1" max="<?php echo $total_pages; ?>">
            <button onclick="goToPage(<?php echo $total_pages; ?>)">Go</button>

            <?php if ($page < $total_pages) : ?>
                <a href="?page_num=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&search_by=<?php echo urlencode($search_by); ?>" class="pagination-button">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
