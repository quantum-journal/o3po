<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-people-shortcodes.php';

class O3PO_EditorPages {

        /**
         * Add the /editor/ endpoint for editor profile pages.
         *
         * To be added to the 'init' action.
         *
         * @since  0.4.4
         * @access public
         */
    public static function add_editor_endpoint() {

        add_rewrite_endpoint('editor', EP_ROOT);

    }

        /**
         * Handle requests to the /editor/{uuid}/ endpoint.
         *
         * To be added to the 'parse_request' action.
         *
         * @since  0.4.4
         * @access public
         * @param  WP $wp The current WordPress environment.
         */
    public static function handle_editor_endpoint_request($wp) {

        if(!isset($wp->query_vars['editor']))
            return;

        $editor = static::get_editor_by_uuid($wp->query_vars['editor']);
        if(empty($editor))
        {
            $wp->query_vars['error'] = '404';
            return;
        }

        query_posts(array(
            'post_type' => 'page',
            'post__in' => array(0),
            'editor_profile_add_fake_post' => true,
            'editor_profile_uuid' => $editor['uuidv4'],
        ));

    }

        /**
         * Find an editor by UUID.
         *
         * @since  0.4.4
         * @access private
         * @param  string $uuidv4 The editor UUID.
         * @return array|null Editor data or null if the UUID does not belong to an editor.
         */
    private static function get_editor_by_uuid($uuidv4) {

        if(!is_string($uuidv4) or empty($uuidv4))
            return null;

        foreach(O3PO_PeopleShortcodes::get_person_data() as $person)
            if($person['role'] === 'editor' and $person['uuidv4'] === $uuidv4)
                return $person;

        return null;
    }

        /**
         * Get published papers handled by an editor, refreshing the transient periodically.
         *
         * @since  0.4.4
         * @access private
         * @param  string $uuidv4 The editor UUID.
         * @return array List of published paper titles and permalinks.
         */
    private static function get_handled_papers($uuidv4) {

        $transient = 'o3po_editor_handled_papers_' . $uuidv4;
        $papers = get_transient($transient);
        if(false !== $papers)
            return $papers;

        $settings = O3PO_Settings::instance();
        $refresh_seconds = max(1, (int)$settings->get_field_value('cited_by_refresh_seconds'));
        $publication_type = $settings->get_field_value('primary_publication_type_name');
        $papers = array();
        $query = new WP_Query(array(
            'post_type' => $publication_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => $publication_type . '_handling_editor_uuidv4',
            'meta_value' => $uuidv4,
        ));

        foreach($query->posts as $post_id => $post)
        {
            if(is_object($post))
                $post_id = $post->ID;

            $papers[] = array(
                'title' => get_the_title($post_id),
                'url' => get_permalink($post_id),
            );
        }

        set_transient($transient, $papers, $refresh_seconds);

        return $papers;
    }

        /**
         * Add a fake page post so editor profiles use the active theme's page template.
         *
         * To be added to the 'the_posts' filter.
         *
         * @since  0.4.4
         * @access public
         * @param  array $posts The posts returned by the query.
         * @return array Posts, including a fake page post for an editor profile.
         */
    public static function add_fake_editor_post_to_query($posts) {

        global $wp_query;

        if(count($posts) > 0 or !isset($wp_query->query_vars['editor_profile_add_fake_post']))
            return $posts;

        $post = new stdClass;
        $post->post_author = 0;
        $post->post_name = 'editor';
        $post->guid = get_site_url();
        $post->post_title = '';
        $post->post_content = '';
        $post->ID = -1;
        $post->post_status = 'publish';
        $post->post_type = 'page';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);

        return array($post);
    }

        /**
         * Use the active theme's page template for editor profiles.
         *
         * To be added to the 'template_include' filter.
         *
         * @since  0.4.4
         * @access public
         * @param  string $template The template that would otherwise be used.
         * @return string The page template for editor profiles.
         */
    public static function editor_profile_template($template) {

        global $wp_query;

        if(!isset($wp_query->query_vars['editor_profile_add_fake_post']))
            return $template;

        $page_template = locate_template(array('page.php'));
        return !empty($page_template) ? $page_template : $template;
    }

        /**
         * Render the editor profile at the start of the page template loop.
         *
         * To be added to the 'loop_start' action.
         *
         * @since  0.4.4
         * @access public
         * @param  WP_Query $wp_query The current WordPress query.
         */
    public static function editor_profile_at_loop_start($wp_query) {

        if(empty($wp_query->query_vars['editor_profile_uuid']))
            return;

        $editor = static::get_editor_by_uuid($wp_query->query_vars['editor_profile_uuid']);
        if(empty($editor))
            return;

        $editor_name = trim($editor['first_names'] . ' ' . $editor['last_names']);
        $current_year = date('Y');
        $service = '';
        if(!empty($editor['since_year']))
            $service = ($current_year >= $editor['since_year'] ? 'Since ' : 'Starting in ') . $editor['since_year'];
        if(!empty($editor['until_year']))
            $service = (!empty($service) ? $editor['since_year'] . '–' : 'Until ') . $editor['until_year'];

        echo '<div class="entry-header editor-profile">';
        echo '<h1 class="entry-title title citation_title">' . esc_html($editor_name) . '</h1>';
        echo '<p class="authors citation_author">' . esc_html(ucwords($editor['role'])) . '</p>';
        echo '<table class="meta-data-table">';
        if(!empty($editor['affiliation']))
            echo '<tr><td>Affiliation:</td><td>' . esc_html($editor['affiliation']) . '</td></tr>';
        if(!empty($editor['country']))
            echo '<tr><td>Country:</td><td>' . esc_html($editor['country']) . '</td></tr>';
        if(!empty($service))
            echo '<tr><td>Service:</td><td>' . esc_html($service) . '</td></tr>';
        if(!empty($editor['extra']))
            echo '<tr><td>Additional information:</td><td>' . esc_html($editor['extra']) . '</td></tr>';
        if(!empty($editor['url']))
            echo '<tr><td>Website:</td><td><a href="' . esc_url($editor['url']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($editor['url']) . '</a></td></tr>';
        echo '</table>';
        echo '</div>';

        $handled_papers = static::get_handled_papers($editor['uuidv4']);
        echo '<div class="entry-content editor-handled-papers">';
        echo '<h3 class="references additional-info">Published papers handled</h3>';
        if(!empty($handled_papers))
        {
            echo '<ul>';
            foreach($handled_papers as $paper)
                echo '<li><a href="' . esc_url($paper['url']) . '">' . esc_html($paper['title']) . '</a></li>';
            echo '</ul>';
        }
        else
            echo '<p>No published papers are currently listed.</p>';
        echo '</div>';

    }

}
