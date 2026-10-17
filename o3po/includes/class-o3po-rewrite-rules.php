<?php

/**
 * Schedule and recover O-3PO rewrite rules.
 *
 * @since 0.5.0
 */
class O3PO_RewriteRules {

    const REFRESH_PENDING_OPTION = 'o3po_rewrite_rules_refresh_pending';
    const LAST_404_REFRESH_TRIGGER_OPTION = 'o3po_rewrite_rules_last_404_trigger';
    const REFRESH_404_LOCK_OPTION = 'o3po_rewrite_rules_404_lock';
    const REFRESH_404_LOCK_TIMEOUT_SECONDS = 60;
    // Avoid delimiter collisions with user-configured rewrite patterns.
    const REWRITE_RULE_REGEX_DELIMITER = "\x01";
    // Limit 404-triggered rewrite refreshes to one every ten minutes.
    const REWRITE_404_RATE_LIMIT_SECONDS = 600;

        /**
         * Schedule a rewrite refresh for the next request.
         *
         * @since 0.5.0
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
         * @since 0.5.0
         * @access public
         */
    public static function schedule_refresh_on_endpoint_404() {

        global $wp_query;

        if(!is_404() or !isset($wp_query->query_vars) or empty(get_option('permalink_structure')) or !static::is_o3po_endpoint_request($wp_query->query_vars))
            return;

        $lock_time = time();
        $lock_acquired = false;
        try
        {
            if(add_option(static::REFRESH_404_LOCK_OPTION, $lock_time, '', false))
                $lock_acquired = true;
            else
            {
                static::clear_option_cache(static::REFRESH_404_LOCK_OPTION);
                $existing_lock_time = (int)get_option(static::REFRESH_404_LOCK_OPTION, 0);
                if($existing_lock_time > $lock_time - static::REFRESH_404_LOCK_TIMEOUT_SECONDS)
                    return;

                if(!static::replace_stale_refresh_lock($existing_lock_time, $lock_time))
                    return;

                $lock_acquired = true;
            }

            $now = time();
            static::clear_option_cache(static::LAST_404_REFRESH_TRIGGER_OPTION);
            $last_trigger = (int)get_option(static::LAST_404_REFRESH_TRIGGER_OPTION, 0);
            if($last_trigger > $now - static::REWRITE_404_RATE_LIMIT_SECONDS)
                return;

            update_option(static::LAST_404_REFRESH_TRIGGER_OPTION, $now, false);
            static::schedule_refresh();
        }
        finally
        {
            if($lock_acquired)
                static::release_refresh_lock($lock_time);
        }

    }

        /**
         * Refresh rewrite rules once all endpoints and post types have registered.
         *
         * To be added to the 'wp_loaded' action.
         * A refresh queued on 'template_redirect' runs on the next request; it
         * does not change the 404 response that caused it.
         *
         * @since 0.5.0
         * @access public
         */
    public static function refresh_if_needed() {

        $refresh_pending = get_option(static::REFRESH_PENDING_OPTION, false);
        if(!$refresh_pending)
            return;

        if(empty(get_option('permalink_structure')))
            // Preserve queued refreshes so they can run after pretty permalinks are enabled.
            return;

        flush_rewrite_rules(false);
        delete_option(static::REFRESH_PENDING_OPTION);

    }

        /**
         * Flush all rewrite rules after plugin deactivation.
         *
         * @since 0.5.0
         * @access public
         */
    public static function flush_on_deactivation() {

        flush_rewrite_rules(true);

    }

        /**
         * Determine whether a 404 request targets one of O3PO's routes.
         *
         * Query vars catch recognized routes. The URI fallback also catches
         * endpoint URLs whose rewrite rule is missing and therefore was not parsed.
         *
         * @since 0.5.0
         * @access private
         * @param  array $query_vars Parsed WordPress query variables.
         * @return bool Whether the request targets an O3PO endpoint or publication route.
         */
    private static function is_o3po_endpoint_request($query_vars) {

        $settings = O3PO_Settings::instance();
        foreach(static::get_endpoint_query_vars($settings) as $query_var)
            if(is_string($query_var) and $query_var !== '' and array_key_exists($query_var, $query_vars))
                return true;

        $request_path = static::get_normalized_request_path();
        if($request_path === false)
            return false;

        $segments = array_values(array_filter(explode('/', trim($request_path, '/')), 'strlen'));
        if(empty($segments))
            return false;

        if(static::contains_case_insensitive($segments[0], static::get_root_endpoint_slugs($settings)))
            return true;

        $publication_slugs = array(
            $settings->get_field_value('primary_publication_type_name_plural'),
            $settings->get_field_value('secondary_publication_type_name_plural'),
        );
        $publication_query_vars = array(
            $settings->get_field_value('primary_publication_type_name'),
            $settings->get_field_value('secondary_publication_type_name'),
        );
        $publication_slug_index = false;
        foreach($publication_slugs as $index => $publication_slug)
            if(is_string($publication_slug) and strcasecmp($segments[0], $publication_slug) === 0)
            {
                $publication_slug_index = $index;
                break;
            }

        if($publication_slug_index !== false)
        {
            $publication_query_var = $publication_query_vars[$publication_slug_index];
            if(!static::has_rewrite_rule_for_publication_path($publication_query_var, $request_path))
                return true;
        }

        return false;

    }

        /**
         * Check whether a rewrite query targets a custom publication type.
         *
         * @since 0.5.0
         * @access private
         * @param  string $query The stored rewrite query.
         * @param  string $publication_query_var The publication type's query var.
         * @return bool Whether the query targets the publication type.
         */
    private static function rewrite_rule_targets_publication_type($query, $publication_query_var) {

        if(!is_string($query) or !is_string($publication_query_var) or $publication_query_var === '')
            return false;

        $query_string = wp_parse_url($query, PHP_URL_QUERY);
        if(!is_string($query_string))
            return false;

        parse_str($query_string, $query_vars);
        if(array_key_exists($publication_query_var, $query_vars))
            return true;

        return isset($query_vars['post_type']) and is_string($query_vars['post_type']) and strcasecmp($query_vars['post_type'], $publication_query_var) === 0;

    }

        /**
         * Check a route slug against configured values without case sensitivity.
         *
         * @since 0.5.0
         * @access private
         * @param  string $value The route slug from the request.
         * @param  array $values Configured route slugs.
         * @return bool Whether the route slug matches.
         */
    private static function contains_case_insensitive($value, $values) {

        foreach($values as $candidate)
            if(is_string($candidate) and strcasecmp($value, $candidate) === 0)
                return true;

        return false;

    }

        /**
         * Get the root-level routes configured by O3PO.
         *
         * @since 0.5.0
         * @access private
         * @param  O3PO_Settings $settings The plugin settings.
         * @return array The root-level endpoint slugs.
         */
    private static function get_root_endpoint_slugs($settings) {

        return array(
            $settings->get_field_value('arxiv_paper_doi_feed_endpoint'),
            $settings->get_field_value('volumes_endpoint'),
            $settings->get_field_value('ready2publish_slug'),
            $settings->get_field_value('ready2publish_slug') . '-dashboard',
            O3PO_EditorPages::ENDPOINT_SLUG,
        );

    }

        /**
         * Get the query vars used to identify O3PO endpoint requests.
         *
         * @since 0.5.0
         * @access private
         * @param  O3PO_Settings $settings The plugin settings.
         * @return array The O3PO endpoint query vars.
         */
    private static function get_endpoint_query_vars($settings) {

        return array_merge(
            static::get_root_endpoint_slugs($settings),
            array(
                'pdf',
                'web-statement',
                $settings->get_field_value('primary_publication_type_name'),
                $settings->get_field_value('secondary_publication_type_name'),
            )
        );

    }

        /**
         * Get the request path without the site's home path prefix.
         *
         * @since 0.5.0
         * @access private
         * @return string|false The normalized request path, or false when unavailable.
         */
    private static function get_normalized_request_path() {

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

        return $request_path;

    }

        /**
         * Check whether a matching stored rewrite rule targets a publication type.
         *
         * @since 0.5.0
         * @access private
         * @param  string $publication_query_var The publication type's query var.
         * @param  string $request_path The request path.
         * @return bool Whether a stored rewrite rule matches the request path.
         */
    private static function has_rewrite_rule_for_publication_path($publication_query_var, $request_path) {

        $rewrite_rules = get_option('rewrite_rules', array());
        if(!is_array($rewrite_rules))
            return false;

        $request = ltrim($request_path, '/');
        foreach($rewrite_rules as $rule => $query)
        {
            if(!is_string($rule))
                continue;

            $rule_pattern = $rule;
            if(isset($rule_pattern[0]) and $rule_pattern[0] === '^')
                $rule_pattern = substr($rule_pattern, 1);

            if(!static::rewrite_rule_targets_publication_type($query, $publication_query_var))
                continue;

            $delimiter = static::REWRITE_RULE_REGEX_DELIMITER;
            $match_result = preg_match($delimiter . '^(?:' . $rule_pattern . ')' . $delimiter . 'i', $request);
            if($match_result === false)
                continue;
            if($match_result === 1)
                return true;
        }

        return false;

    }

        /**
         * Atomically replace a stale 404 refresh lock.
         *
         * @since 0.5.0
         * @access private
         * @param  int $stale_lock_time The timestamp currently stored in the lock.
         * @param  int $new_lock_time The timestamp to store for this request.
         * @return bool Whether the stale lock was replaced.
         */
    private static function replace_stale_refresh_lock($stale_lock_time, $new_lock_time) {

        global $wpdb;
        if(!isset($wpdb) or !isset($wpdb->options))
            return false;

        $updated = $wpdb->update(
            $wpdb->options,
            array('option_value' => (string)$new_lock_time),
            array(
                'option_name' => static::REFRESH_404_LOCK_OPTION,
                'option_value' => (string)$stale_lock_time,
            ),
            array('%s'),
            array('%s', '%s')
        );
        if($updated !== 1)
            return false;

        static::clear_option_cache(static::REFRESH_404_LOCK_OPTION);

        return true;

    }

        /**
         * Clear cached option values before a fresh database-backed read.
         *
         * @since 0.5.0
         * @access private
         * @param  string $option_name The option to invalidate.
         */
    private static function clear_option_cache($option_name) {

        if(!function_exists('wp_cache_delete'))
            return;

        wp_cache_delete($option_name, 'options');
        wp_cache_delete('alloptions', 'options');

    }

        /**
         * Release the refresh lock only if it is still owned by this request.
         *
         * @since 0.5.0
         * @access private
         * @param  int $lock_time The timestamp stored by this request.
         */
    private static function release_refresh_lock($lock_time) {

        global $wpdb;
        if(isset($wpdb) and isset($wpdb->options) and method_exists($wpdb, 'delete'))
        {
            $deleted = $wpdb->delete(
                $wpdb->options,
                array(
                    'option_name' => static::REFRESH_404_LOCK_OPTION,
                    'option_value' => (string)$lock_time,
                ),
                array('%s', '%s')
            );
            if($deleted === 1)
                static::clear_option_cache(static::REFRESH_404_LOCK_OPTION);
            elseif($deleted === false and (int)get_option(static::REFRESH_404_LOCK_OPTION, 0) === (int)$lock_time)
                delete_option(static::REFRESH_404_LOCK_OPTION);
            return;
        }

        if((int)get_option(static::REFRESH_404_LOCK_OPTION, 0) === (int)$lock_time)
            delete_option(static::REFRESH_404_LOCK_OPTION);

    }
}
