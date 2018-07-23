<?php

require_once 'PHPUnit/Autoload.php';

/**
 * The bootstrap file for the PHPUnit tests.
 *
 * Here we define fake version of the WordPress functions
 * the plugin calls.
 */ 

function plugin_dir_path($path) {

    return dirname($path) . '/';
}

function register_activation_hook( $file, $function ) {}

function register_deactivation_hook( $file, $function ) {}

function add_action( $hook, $function ) {}

function add_filter( $hook, $function ) {}

function is_admin() {}

function get_site_url() {

    return 'https://foo.bar.com/';
}

function get_option( $option, $default = false ) {

    if($option === 'o3po-setttings' or 'quantum-journal-plugin-setttings')
        return array(
            'production_site_url' => 'trim',
            'journal_title' => 'trim',
            'journal_subtitle' => 'trim',
            'journal_description' => 'trim',
            'journal_level_doi_suffix' => 'trim',
            'eissn' => 'trim',
            'publisher' => 'trim',
            'secondary_journal_title' => 'trim',
            'secondary_journal_level_doi_suffix' => 'trim',
            'secondary_journal_eissn' => 'trim',
            'developer_email' => 'trim',
            'publisher_email' => 'trim',
            'publisher_country' => 'trim',
            'license_name' => 'trim',
            'license_type' => 'trim',
            'license_version' => 'trim',
            'license_url' => 'trim',
            'license_explanation' => 'trim',
            'crossref_id' => 'trim',
            'crossref_pw' => 'trim',
            'crossref_get_forward_links_url' => 'trim',
            'crossref_deposite_url' => 'trim',
            'crossref_test_deposite_url' => 'trim',
            'crossref_email' => 'trim',
            'crossref_archive_locations' => 'trim',
            'arxiv_url_abs_prefix' => 'trim',
            'arxiv_url_pdf_prefix' => 'trim',
            'arxiv_url_source_prefix' => 'trim',
            'arxiv_url_trackback_prefix' => 'trim',
            'arxiv_doi_feed_identifier' => 'trim',
            'doi_url_prefix' => 'trim',
            'scholastica_manuscripts_url' => 'trim',
            'scirate_url_abs_prefix' => 'trim',
            'orcid_url_prefix' => 'trim',
            'fermats_library_url_prefix' => 'trim',
            'fermats_library_email' => 'trim',
            'mathjax_url' => 'trim',
            'social_media_thumbnail_url' => 'trim',
            'buffer_secret_email' => 'trim',
            'facebook_app_id' => 'trim',
            'doaj_api_url' => 'trim',
            'doaj_api_key' => 'trim',
            'doaj_language_code' => 'trim',
            'custom_search_page' => 'trim',
            'volumes_endpoint' => 'trim',
            'doi_prefix' => 'fake_doi_prefix',
            'eissn' => 'fake_eissn',
            'secondary_journal_eissn' => "fake_secondary_journal_eissn",
            'first_volume_year' => "fake_first_volume_year",
                     );
    else
        throw(new Exception("We don't know how to fake the option " . $option . "."));
    
}

function get_file_data( $file, $options ) {
    
    $file_contents = file_get_contents($file);

    $matches = array();
    foreach($options as $option)
    {
        preg_match('#\s*\\*\s*' . $option . ':\s*(.*)#', $file_contents, $match);
        $matches[$option] = $match[1];
    }
    
    return $matches;
}
