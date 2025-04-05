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
    $selected_author = isset($_GET['authors']) ? $_GET['authors'] : [];
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
        $authors_array = array_map('trim', (array)$selected_author);
        $author_clauses = array_fill(0, count($authors_array), "authors LIKE %s");
        $where_clauses[] = "(" . implode(" OR ", $author_clauses) . ")";
        foreach ($authors_array as $author) {
            $params[] = "%{$author}%";
        }
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
        $where_clauses[] = "(abstract LIKE %s OR title LIKE %s OR authors LIKE %s OR journal LIKE %s)";
        $params[] = "%{$abstract_search}%";
        $params[] = "%{$abstract_search}%";
        $params[] = "%{$abstract_search}%";
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
    sort($authors);

    $keywords = array_unique(array_map('trim', preg_split('/\s*;\s*/', implode(';', $keywords))));
    sort($keywords);

    $subjects = $wpdb->get_col("SELECT DISTINCT subject_area FROM $table_name ORDER BY subject_area ASC");
    sort($subjects);

    $geo_focuses = $wpdb->get_col("SELECT DISTINCT main_geographical_focus FROM $table_name ORDER BY main_geographical_focus ASC");
    sort($geo_focuses);

    $years = $wpdb->get_col("SELECT DISTINCT publication_year FROM $table_name ORDER BY publication_year DESC");
    sort($years);

    ob_start();
?>
    <style>
        .filter-form {
            float: left;
            width: 100%;
            margin-bottom: 20px;
        }

        .filter-form section {
            float: left;
            width: 24%;
            margin-right: 1%;
            margin-bottom: 10px;
        }

        .filter-form select {
            width: 100%;
            height: 35px;
            border: 1px solid #ccc;
            padding: 5px 10px;
            font-size: 13px;
            border-radius: 5px;
        }

        .filter-form .search-bar {
            width: 100%;
        }

        .filter-form .search-bar input {
            width: calc(100% - 100px);
            height: 35px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 0px 10px;
        }

        .filter-form .search-bar button {
            width: 90px;
            height: 35px;
            background: #5f9cd2;
            color: white;
            border-radius: 5px;
            border: none;
        }

        .result-showing {
            color: #999;
            font-size: 13px;
            margin-bottom: 0;
        }

        .results article {
            float: left;
            box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, .2);
            box-sizing: border-box;
            padding: 12px 15px;
            margin-bottom: 5px;
        }

        .results article h2 {
            color: #5f9cd2;
            font-size: 17px;
            text-align: left;
            line-height: 20px;
            margin-bottom: 5px;
        }

        .results article h3 {
            font-weight: normal;
            margin-top: 0;
            line-height: 15px;
        }

        .results article .authors {
            color: #006621;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .results article .authors a:after {
            content: ';';
        }

        .results article .journal:before {
            content: '\2014';
            margin-right: 5px;
        }

        .results article .journal {
            color: #666;
            font-size: 13px;
        }

        .results .chips {
            display: inline-block;
            border-radius: 10px;
            border: 1px solid #e3e3e3;
            padding: 3px 10px;
        }

        .results .chips .fa {
            font-size: 13px;
            margin-right: 5px;
        }

        .results .chips span {
            font-size: 11px;
        }

        .results .abstracts {
            margin-top: 10px;
        }

        .results .abstracts p {
            color: black;
            font-size: 13px;
            line-height: 20px;
        }

        .results .keywords {
            margin-top: 0;
        }

        .results .keywords span {
            float: left;
            font-size: 11px;
            color: gray;
            border: 1px solid #e3e3e3;
            border-radius: 10px;
            padding: 4px 8px;
            margin-right: 5px;
        }

        #pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination-button {
            background: #2980b9;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .pagination-button:hover {
            background: #21618c;
        }
    </style>
    <div class="container">
        <form class="filter-form" method="GET">
            <input type="hidden" name="page_num" value="1">

            <section>
                <select name="authors[]" id="author-dropdown" multiple>
                    <option value="">Author</option>
                    <?php foreach ($authors as $author) : ?>
                        <option
                            value="<?php echo esc_attr($author); ?>"
                            <?php echo in_array($author, (array)$selected_author) ? 'selected' : ''; ?>><?php echo esc_html($author); ?></option>
                    <?php endforeach; ?>
                </select>
            </section>

            <!-- Client wanted to remove keyword search  -->
            <!-- <section>
                <select name="article_keywords" id="keyword-dropdown">
                    <option value="">Keywords</option>
                    <?php foreach ($keywords as $keyword) : ?>
                        <option
                            value="<?php echo esc_attr($keyword); ?>"
                        ><?php echo esc_html($keyword); ?></option>
                    <?php endforeach; ?>
                </select>
            </section> -->

            <section>
                <select name="subject_area" id="subject-dropdown">
                    <option value="">Topics</option>
                    <?php foreach ($subjects as $subject) : ?>
                        <option
                            value="<?php echo esc_attr($subject); ?>"
                            <?php selected($selected_subject, $subject); ?>><?php echo esc_html($subject); ?></option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section>
                <select name="main_geographical_focus" id="geo-dropdown">
                    <option value="">Geographical Focus</option>
                    <?php foreach ($geo_focuses as $geo_focus) : ?>
                        <option
                            value="<?php echo esc_attr($geo_focus); ?>"
                            <?php selected($selected_geo_focus, $geo_focus); ?>><?php echo esc_html($geo_focus); ?></option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section>
                <select name="publication_year" id="year-dropdown">
                    <option value="">Year</option>
                    <?php foreach ($years as $year) : ?>
                        <option
                            value="<?php echo esc_attr($year); ?>"
                            <?php selected($selected_year, $year); ?>><?php echo esc_html($year); ?></option>
                    <?php endforeach; ?>
                </select>
            </section>

            <div class="search-bar">
                <input type="text" name="abstract_search" placeholder="Search" value="<?php echo esc_attr($abstract_search); ?>">
                <button type="submit">Search</button>
            </div>
        </form>

        <div class="results">
            <?php if (empty($articles)) : ?>
                <p>No articles found.</p>
            <?php else : ?>
                <h2 class="result-showing">Showing <?php echo count($articles); ?> of <?php echo $total_articles; ?> Results</h2>
                <?php foreach ($articles as $article) : ?>
                    <article>
                        <h2>
                            <a href="<?php echo esc_url($article->doi_or_link); ?>" target="_blank">
                                <?php echo esc_html($article->title); ?>
                            </a>
                        </h2>

                        <h3>
                            <span class="authors"><?php echo esc_html($article->authors); ?></span>
                            <span class="journal">
                                <?php echo esc_html($article->journal); ?> (<?php echo esc_html($article->publication_year); ?>)
                            </span>
                        </h3>

                        <div class="chips-container">
                            <div class="chips">
                                <i class="fa fa-map-o" aria-hidden="true"></i>
                                <span><?php echo esc_html($article->subject_area); ?></span>

                            <div class="chips">
                                <i class="fa fa-globe" aria-hidden="true"></i>
                                <span><?php echo esc_html($article->main_geographical_focus); ?></span>
                            </div>
                        </div> <!-- /chips -->

                        <div class="abstracts">
                            <p><?php echo nl2br(substr($article->abstract, 0, 400)); ?></p>
                        </div> <!-- /abstracts -->

                        <div class="keywords">

                            <span>Marine finfish aquaculture</span>
                            <span>Life-history stages</span>
                            <span>Environmental impacts</span>
                            <span>Management tools</span>
                        </div> <!-- /abstracts -->
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> <!-- /results -->
        <div id="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page_num=<?php echo $page - 1; ?>&authors=<?php echo urlencode(implode(',', (array)$selected_author)); ?>&subject_area=<?php echo urlencode($selected_subject); ?>&main_geographical_focus=<?php echo urlencode($selected_geo_focus); ?>&abstract_search=<?php echo urlencode($abstract_search); ?>" class="pagination-button">&laquo; Previous</a>
            <?php endif; ?>

            <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <input type="number" id="page-input" value="<?php echo $page; ?>" min="1" max="<?php echo $total_pages; ?>">
            <button onclick="goToPage(<?php echo $total_pages; ?>)">Go</button>

            <?php if ($page < $total_pages) : ?>
                <a href="?page_num=<?php echo $page + 1; ?>&authors=<?php echo urlencode(implode(',', (array)$selected_author)); ?>&subject_area=<?php echo urlencode($selected_subject); ?>&main_geographical_focus=<?php echo urlencode($selected_geo_focus); ?>&abstract_search=<?php echo urlencode($abstract_search); ?>" class="pagination-button">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
?>