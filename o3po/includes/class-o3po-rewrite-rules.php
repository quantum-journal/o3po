<?php

class O3PO_RewriteRules {

    const VERSION_OPTION = 'o3po_rewrite_rules_version';
    const VERSION = '1';
    const REFRESH_PENDING_OPTION = 'o3po_rewrite_rules_refresh_pending';
    const LAST_404_REFRESH_TRIGGER_OPTION = 'o3po_rewrite_rules_last_404_trigger';
    // Limit 404-triggered rewrite refreshes to one every ten minutes.
    const REWRITE_404_RATE_LIMIT_SECONDS = 600;

        /**
         * Schedule a rewrite refresh for the next request.
         *
         * @access public
         */
    public static function schedule_refresh() {

        update_option(static::REFRESH_PENDING_OPTION, true, false);

    }

        /**
         * Schedule a rate-limited refresh after a 404 for one of this plugin's endpoints.
         *
         * To be added to the 'template_redirect' action.
         *
         * @access public
         */
    public static function schedule_refresh_on_endpoint_404() {

        global $wp_query;

        if(!is_404() or !isset($wp_query->query_vars) or !static::is_o3po_endpoint_request($wp_query->query_vars))
            return;

        $now = time();
        $last_trigger = (int)get_option(static::LAST_404_REFRESH_TRIGGER_OPTION, 0);
        if($last_trigger > $now - static::REWRITE_404_RATE_LIMIT_SECONDS)
            return;

        update_option(static::LAST_404_REFRESH_TRIGGER_OPTION, $now, false);
        static::schedule_refresh();

    }

        /**
         * Refresh rewrite rules once all endpoints and post types have registered.
         *
         * To be added to the 'wp_loaded' action.
         * A refresh queued on 'template_redirect' runs on the next request; it
         * does not change the 404 response that caused it.
         *
         * @access public
         */
    public static function refresh_if_needed() {

        $refresh_pending = get_option(static::REFRESH_PENDING_OPTION, false);
        if(!$refresh_pending and get_option(static::VERSION_OPTION) === static::VERSION)
            return;

        if(empty(get_option('permalink_structure')))
        {
            if(!$refresh_pending)
                update_option(static::VERSION_OPTION, static::VERSION, false);
            return;
        }

        flush_rewrite_rules(false);
        update_option(static::VERSION_OPTION, static::VERSION, false);
        delete_option(static::REFRESH_PENDING_OPTION);

    }

        /**
         * Determine whether a 404 request targets one of O3PO's routes.
         *
         * Query vars catch recognized routes. The URI fallback also catches
         * endpoint URLs whose rewrite rule is missing and therefore was not parsed.
         *
         * @access private
         * @param  array $query_vars Parsed WordPress query variables.
         * @return bool Whether the request targets an O3PO endpoint or publication route.
         */
    private static function is_o3po_endpoint_request($query_vars) {

        $settings = O3PO_Settings::instance();
        $endpoint_query_vars = array(
            'editor',
            'pdf',
            'web-statement',
            $settings->get_field_value('arxiv_paper_doi_feed_endpoint'),
            $settings->get_field_value('volumes_endpoint'),
            $settings->get_field_value('ready2publish_slug'),
            $settings->get_field_value('ready2publish_slug') . '-dashboard',
            $settings->get_field_value('primary_publication_type_name'),
            $settings->get_field_value('secondary_publication_type_name'),
        );

        foreach($endpoint_query_vars as $query_var)
            if(is_string($query_var) and $query_var !== '' and array_key_exists($query_var, $query_vars))
                return true;

        if(empty($_SERVER['REQUEST_URI']))
            return false;

        $request_uri = wp_unslash($_SERVER['REQUEST_URI']);
        $request_path = wp_parse_url($request_uri, PHP_URL_PATH);
        $home_path = wp_parse_url(home_url(), PHP_URL_PATH);
        if(!is_string($request_path))
            return false;
        $home_path = is_string($home_path) ? rtrim($home_path, '/') : '';
        if($home_path !== '' and $request_path === $home_path)
            $request_path = '/';
        elseif($home_path !== '' and strpos($request_path, $home_path . '/') === 0)
            $request_path = substr($request_path, strlen($home_path));

        $segments = array_values(array_filter(explode('/', trim($request_path, '/')), 'strlen'));
        if(empty($segments))
            return false;

        $root_endpoints = array(
            $settings->get_field_value('arxiv_paper_doi_feed_endpoint'),
            $settings->get_field_value('volumes_endpoint'),
            $settings->get_field_value('ready2publish_slug'),
            $settings->get_field_value('ready2publish_slug') . '-dashboard',
            'editor',
        );
        if(in_array($segments[0], $root_endpoints, true))
            return true;

        $publication_slugs = array(
            $settings->get_field_value('primary_publication_type_name_plural'),
            $settings->get_field_value('secondary_publication_type_name_plural'),
        );
        if(in_array($segments[0], $publication_slugs, true))
        {
            if(!static::has_rewrite_rule_for_request_path($request_path))
                return true;
        }

        return false;

    }

        /**
         * Check whether WordPress has a stored rewrite rule matching the request path.
         *
         * @access private
         * @param  string $request_path The request path.
         * @return bool Whether a stored rewrite rule matches the request path.
         */
    private static function has_rewrite_rule_for_request_path($request_path) {

        $rewrite_rules = get_option('rewrite_rules', array());
        if(!is_array($rewrite_rules))
            return false;

        $request = ltrim($request_path, '/');
        foreach($rewrite_rules as $rule => $query)
            if(is_string($rule) and preg_match('~^' . str_replace('~', '\\~', $rule) . '~', $request))
                return true;

        return false;

    }
}
