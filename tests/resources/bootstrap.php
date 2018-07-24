<?php

/**
 * The bootstrap file for the PHPUnit tests.
 *
 * Here we define fake version of the WordPress functions
 * the plugin calls.
 */ 

function plugin_dir_path($path) {

    return dirname($path) . '/';
}

function wp_upload_dir( $time = null, $create_dir = true, $refresh_cache = false) {
    $basedir = dirname(__FILE__) . '/tmp/uploads';
        
    return array(
        'basedir' => $basedir,
        'baseurl' => 'http://foo/' . $basedir,
                 );
}

$hooks = array();
function add_action( $hook, $callable ) {
    global $hooks;
    
    if(!isset($hooks[$hook]))
        $hooks[$hook] = array();
    $hooks[$hook][] = $callable;
}

function register_activation_hook( $file, $callable ) {
    add_action( 'activation_hook', $callable );
}

function register_deactivation_hook( $file, $callable ) {
    add_action( 'deactivation_hook', $callable );
}

function trigger_hook( $hook ) {
    foreach($hooks[$hook] as $callable)
        call_user_func($callable);
}

$filteres = array();
function add_filter( $hook, $callable ) {
    global $filters;
    
    if(!isset($filters[$hook]))
        $filters[$hook] = array();
    $filters[$hook][] = $callable;
}

function is_admin() {}

function get_site_url() {

    return 'https://foo.bar.com/';
}

function get_option( $option, $default = false ) {

    if($option === 'o3po-setttings' or 'quantum-journal-plugin-setttings')
        return array(
            'production_site_url' => 'fake_production_site_url',
            'journal_title' => 'fake_journal_title',
            'journal_subtitle' => 'fake_journal_subtitle',
            'journal_description' => 'fake_journal_description',
            'journal_level_doi_suffix' => 'fake_journal_level_doi_suffix',
            'eissn' => 'fake_eissn',
            'publisher' => 'fake_publisher',
            'secondary_journal_title' => 'fake_secondary_journal_title',
            'secondary_journal_level_doi_suffix' => 'fake_secondary_journal_level_doi_suffix',
            'secondary_journal_eissn' => 'fake_secondary_journal_eissn',
            'developer_email' => 'fake_developer_email',
            'publisher_email' => 'fake_publisher_email',
            'publisher_country' => 'fake_publisher_country',
            'license_name' => 'fake_license_name',
            'license_type' => 'fake_license_type',
            'license_version' => 'fake_license_version',
            'license_url' => 'fake_license_url',
            'license_explanation' => 'fake_license_explanation',
            'crossref_id' => 'fake_crossref_id',
            'crossref_pw' => 'fake_crossref_pw',
            'crossref_get_forward_links_url' => 'fake_crossref_get_forward_links_url',
            'crossref_deposite_url' => 'fake_crossref_deposite_url',
            'crossref_test_deposite_url' => 'fake_crossref_test_deposite_url',
            'crossref_email' => 'fake_crossref_email',
            'crossref_archive_locations' => 'fake_crossref_archive_locations',
            'arxiv_url_abs_prefix' => 'fake_arxiv_url_abs_prefix',
            'arxiv_url_pdf_prefix' => 'fake_arxiv_url_pdf_prefix',
            'arxiv_url_source_prefix' => 'fake_arxiv_url_source_prefix',
            'arxiv_url_trackback_prefix' => 'fake_arxiv_url_trackback_prefix',
            'arxiv_doi_feed_identifier' => 'fake_arxiv_doi_feed_identifier',
            'doi_url_prefix' => 'fake_doi_url_prefix',
            'scholastica_manuscripts_url' => 'fake_scholastica_manuscripts_url',
            'scirate_url_abs_prefix' => 'fake_scirate_url_abs_prefix',
            'orcid_url_prefix' => 'fake_orcid_url_prefix',
            'fermats_library_url_prefix' => 'fake_fermats_library_url_prefix',
            'fermats_library_email' => 'fake_fermats_library_email',
            'mathjax_url' => 'fake_mathjax_url',
            'social_media_thumbnail_url' => 'fake_social_media_thumbnail_url',
            'buffer_secret_email' => 'fake_buffer_secret_email',
            'facebook_app_id' => 'fake_facebook_app_id',
            'doaj_api_url' => 'fake_doaj_api_url',
            'doaj_api_key' => 'fake_doaj_api_key',
            'doaj_language_code' => 'fake_doaj_language_code',
            'custom_search_page' => 'fake_custom_search_page',
            'volumes_endpoint' => 'fake_volumes_endpoint',
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

function flush_rewrite_rules( $hard=false ) {}

$posts = array(
    1 => array(
        'post_type' => 'paper',
        'thumbnail_id' => 2,
        'post_status' => 'private',
        'meta' => array(
            'paper_abstract' => 'This is a test abstract that contains not math so far and no special characters.',
            'paper_abstract_mathml' => 'This is a test abstract that contains not math so far and no special characters.',
            'paper_eprint' => '0908.2921v2',
            'paper_eprint_was_changed_on_last_save' => false,
            'paper_arxiv_pdf_attach_ids' => array(4),
            'paper_popular_summary' => 'Some random fake summary.',
            'paper_feature_image_caption' => 'Some random fake cation.',
            'paper_fermats_library' => '',
            'paper_validation_result' => 'fake_validation_result',
            'paper_title' => 'Fake title',
            'paper_title_mathml' => 'Fake title',
            'paper_corresponding_author_email' => 'fake_paper_corresponding_author_email',
            'paper_corresponding_author_has_been_notifed_date' => 'fake_paper_corresponding_author_has_been_notifed_date',
            'paper_buffer_email' => 'fake_paper_buffer_email',
            'paper_buffer_email_was_sent_date' => 'fake_paper_buffer_email_was_sent_date',
            'paper_buffer_special_text' => 'fake_paper_buffer_special_text',
            'paper_fermats_library_permalink' => 'fake_paper_fermats_library_permalink',
            'paper_fermats_library_permalink_worked' => 'fake_paper_fermats_library_permalink_worked',
            'paper_fermats_library_has_been_notifed_date' => 'fake_paper_fermats_library_has_been_notifed_date',
            'paper_number_authors' => 'fake_paper_number_authors',
            'paper_author_given_names' => 'fake_paper_author_given_names',
            'paper_author_surnames' => 'fake_paper_author_surnames',
            'paper_author_name_styles' => 'fake_paper_author_name_styles',
            'paper_author_affiliations' => 'fake_paper_author_affiliations',
            'paper_author_orcids' => 'fake_paper_author_orcids',
            'paper_author_urls' => 'fake_paper_author_urls',
            'paper_number_affiliations' => 'fake_paper_number_affiliations',
            'paper_affiliations' => 'fake_paper_affiliations',
            'paper_date_published' => 'fake_paper_date_published',
            'paper_journal' => 'fake_paper_journal',
            'paper_volume' => 'fake_paper_volume',
            'paper_pages' => 'fake_paper_pages',
            'paper_doi_prefix' => 'fake_paper_doi_prefix',
            'paper_doi_suffix' => 'fake_paper_doi_suffix',
            'paper_bbl' => 'fake_paper_bbl',
            'paper_author_latex_macro_definitions' => array(),
            'paper_crossref_xml' => 'fake_paper_crossref_xml',
            'paper_crossref_response' => 'fake_paper_crossref_response',
            'paper_doaj_json' => 'fake_paper_doaj_json',
            'paper_doaj_response' => 'fake_paper_doaj_response',
            'paper_arxiv_fetch_results' => 'fake_paper_arxiv_fetch_results',
            'paper_arxiv_source_attach_ids' => array(),
                        ),
               ),
    2 => array(
        'post_type' => 'attachment',
        'attachment_image_src' => 'fake_attachment_image_src',
        'thumbnail_id' => 3,
        'meta' => array(
            
                        ),
               ),
    3 => array(
        'post_type' => 'attachment',
        'attachment_image_src' => 'fake_attachment_image_src',
               ),
    4 => array(
        'post_type' => 'attachment',
        'attachment_url' => 'fake_attachment_url',
               ),
               );

function get_post_type( $post_id ) {
    global $posts;

    if(!isset($posts[$post_id]['post_type']))
        throw new Exception("Post with id=" . $post_id . " has no post_type.");
    
    return $posts[$post_id]['post_type'];
}
        
function get_post_meta( $post_id, $key, $single = false ) {
    global $posts;
    
    if(!isset($posts[$post_id]))
        throw new Exception("No post with id=" . $post_id);
    if(!isset($posts[$post_id]['meta']))
        throw new Exception("Post with id=" . $post_id . " has no meta-data.");
    if(!isset($posts[$post_id]['meta'][$key]))
    {
//        throw new Exception("Post with id=" . $post_id . " has no meta-data for key=" . $key . ".");
       echo("\nPost with id=" . $post_id . " has no meta-data for key=" . $key . "\n");
       return "fake!";
    }
    
    
    return $posts[$post_id]['meta'][$key];
}

function wp_nonce_field( $action, $name, $referer=true, $echo=true ) {}

class WP_Post 
{
    public function __construct( $post_id ) {
        $this->ID = $post_id;
    }
    
    public $ID;
}

function get_post_thumbnail_id( $post_id ) {
    global $posts;

    if(!isset($posts[$post_id]['thumbnail_id']))
        throw new Exception("Post with id=" . $post_id . " has no thumbnail_id.");
    
    return $posts[$post_id]['thumbnail_id'];    
}

function wp_get_attachment_image_src( $post_id ) {
    global $posts;

    if(!isset($posts[$post_id]['attachment_image_src']))
        throw new Exception("Post with id=" . $post_id . " has no attachment_image_src.");
    
    return $posts[$post_id]['attachment_image_src'];
}

function get_post_status( $ID = '' ) {
    global $posts;

    $post_id = $ID;
    
    if(!isset($posts[$post_id]['post_status']))
        throw new Exception("Post with id=" . $post_id . " has no post_status.");
    
    return $posts[$post_id]['post_status'];
}

function esc_html( $text ) {
    
    return '(esc_html does nothing useful in the bootstaped fake WordPress)' . $text;
}

function esc_attr__( $text ) {
    
    return '(esc_attr__ does nothing useful in the bootstaped fake WordPress)' . $text;
}

function esc_attr( $text ) {
    
    return '(esc_attr does nothing useful in the bootstaped fake WordPress)' . $text;
}

function wp_get_attachment_url($id) {
    global $posts;

    $post_id = $id;
    
    if(!isset($posts[$post_id]['attachment_url']))
        throw new Exception("Post with id=" . $post_id . " has no attachment_url.");
    
    return $posts[$post_id]['attachment_url'];
}
