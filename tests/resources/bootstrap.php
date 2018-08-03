<?php

/**
 * The bootstrap file for the PHPUnit tests.
 *
 * Here we define fake version of the WordPress functions
 * the plugin calls.
 */

if(!class_exists('PHPUnit_Framework_TestCase')){
        /**
         * Make sure the class PHPUnit_Framework_TestCase is always defined
         *
         * Different versions of PHPUnit call the base test class differently.
         */
    class PHPUnit_Framework_TestCase extends PHPUnit\Framework\TestCase
    {

    }
}


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
            'arxiv_url_abs_prefix' => 'https://arxiv.org/abs/',
            'arxiv_url_pdf_prefix' => 'https://arxiv.org/pdf/',
            'arxiv_url_source_prefix' => 'https://arxiv.org/e-print/',
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
            'first_volume_year' => "2009",
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

$post_data = array();

$posts = array(
    1 => array(
        'post_type' => 'paper',
        'post_content' => 'fake_post_content1',
        'paper_nonce' => 'fake_nonce',
        'thumbnail_id' => 2,
        'post_status' => 'private',
        'post_title' => 'Fake title',
        'permalink' => 'Fake permalink',
        'meta' => array(
            'paper_abstract' => 'This is a test abstract that contains not math so far and no special characters.',
            'paper_abstract_mathml' => 'This is a test abstract that contains not math so far and no special characters.',
            'paper_eprint' => '0908.2921v2',
            'paper_eprint_was_changed_on_last_save' => false,
//            'paper_arxiv_pdf_attach_ids' => array(4),
            'paper_arxiv_pdf_attach_ids' => array(),
            'paper_popular_summary' => 'Some random fake summary.',
            'paper_feature_image_caption' => 'Some random fake cation.',
            'paper_fermats_library' => 'checked',
            'paper_validation_result' => 'fake_validation_result',
            'paper_title' => 'Fake title',
            'paper_title_mathml' => 'Fake title',
            'paper_corresponding_author_email' => 'mal_formed_corresponding_author_email',
            'paper_corresponding_author_has_been_notifed_date' => '',
            'paper_buffer_email' => 'fake_paper_buffer_email',
            'paper_buffer_email_was_sent_date' => '',
            'paper_buffer_special_text' => 'fake_paper_buffer_special_text',
            'paper_fermats_library_permalink' => 'fake_paper_fermats_library_permalink',
            'paper_fermats_library_permalink_worked' => 'fake_paper_fermats_library_permalink_worked',
            'paper_fermats_library_has_been_notifed_date' => '',
            'paper_number_authors' => 2,
            'paper_author_given_names' => ['Foo', 'Baz'],
            'paper_author_surnames' => ['Bar', 'Boo'],
            'paper_author_name_styles' => ["western", "western"],
            'paper_author_affiliations' => ['1,2','2'],
            'paper_author_orcids' => ['',''],
            'paper_author_urls' => ['',''],
            'paper_number_affiliations' => 2,
            'paper_affiliations' => ['Foo University', 'Bar Institut'],
            'paper_date_published' => '',
            'paper_journal' => 'fake_paper_journal',
            'paper_volume' => '1',
            'paper_pages' => '1',
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
            'paper_doi_suffix_was_changed_on_last_save' => false,
            'paper_clockss_xml' => 'fake clocks xml',
            'paper_clockss_response' => 'fake clocks response',
                        ),
               ),
    2 => array(
        'post_type' => 'attachment',
        'attachment_image_src' => 'fake_attachment_image_src',
        'thumbnail_id' => 3,
        'attachment_url' => "Fake attachment_url",
        'meta' => array(),
               ),
    3 => array(
        'post_type' => 'attachment',
        'attachment_image_src' => 'fake_attachment_image_src',
        'attachment_url' => "Fake attachment_url",
        'attachment_path' => dirname(__FILE__) . '/arxiv/0809.2542v4.pdf',
               ),
    4 => array(
        'post_type' => 'attachment',
        'attachment_url' => 'fake_attachment_url',
        'attachment_path' => 'fake_attachment_path',
               ),
    5 => array(
        'post_type' => 'paper',
        'post_content' => 'fake_post_content5',
        'paper_nonce' => 'fake_nonce',
        'thumbnail_id' => 2,
        'post_status' => 'private',
        'post_title' => 'Fake title 2',
        'permalink' => 'Fake permalink',
        'meta' => array(
            'paper_abstract' => 'This is a test abstract 2 that contains not math so far and no special characters.',
            'paper_abstract_mathml' => 'This is a test abstract 2 that contains not math so far and no special characters.',
            'paper_eprint' => '0809.2542v4',
            'paper_eprint_was_changed_on_last_save' => false,
            'paper_arxiv_pdf_attach_ids' => array(),
            'paper_popular_summary' => 'Some random fake summary.',
            'paper_feature_image_caption' => 'Some random fake cation.',
            'paper_fermats_library' => '',
            'paper_validation_result' => 'fake_validation_result',
            'paper_title' => 'Fake title 2',
            'paper_title_mathml' => 'Fake title 2',
            'paper_corresponding_author_email' => 'validemail@quantum-journal.org',
            'paper_corresponding_author_has_been_notifed_date' => '',
            'paper_buffer_email' => 'fake_paper_buffer_email',
            'paper_buffer_email_was_sent_date' => '',
            'paper_buffer_special_text' => 'fake_paper_buffer_special_text',
            'paper_fermats_library_permalink' => 'fake_paper_fermats_library_permalink',
            'paper_fermats_library_permalink_worked' => 'fake_paper_fermats_library_permalink_worked',
            'paper_fermats_library_has_been_notifed_date' => '',
            'paper_number_authors' => 1,
            'paper_author_given_names' => ['Foo'],
            'paper_author_surnames' => ['Bar'],
            'paper_author_name_styles' => ["western"],
            'paper_author_affiliations' => ['1'],
            'paper_author_orcids' => ['0000-0003-0290-4698'],
            'paper_author_urls' => [''],
            'paper_number_affiliations' => 1,
            'paper_affiliations' => ['Foo Institute'],
            'paper_date_published' => '',
            'paper_journal' => 'fake_paper_journal',
            'paper_volume' => '1',
            'paper_pages' => '2',
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
            'paper_doi_suffix_was_changed_on_last_save' => false,
            'paper_clockss_xml' => '',
            'paper_clockss_response' => '',
                        )
               ),
    6 => array(
        'post_type' => 'attachment',
        'attachment_url' => 'fake_attachment_url',
        'attachment_path' => dirname(__FILE__) . '/arxiv/0809.2542v4.pdf',
               ),
    7 => array(
        'post_type' => 'attachment',
        'attachment_url' => 'fake_attachment_url',
        'attachment_path' => dirname(__FILE__) . '/arxiv/0809.2542v4.tar.gz',
               ),
    8 => array(
        'post_type' => 'paper',
        'post_content' => 'fake_post_content8',
        'paper_nonce' => 'fake_nonce',
        'thumbnail_id' => 2,
        'post_status' => 'publish',
        'post_title' => 'Fake title',
        'permalink' => 'Fake permalink',
        'meta' => array(
            'paper_abstract' => 'This is a test abstract that contains not math so far and no special characters.',
            'paper_abstract_mathml' => 'This is a test abstract that contains not math so far and no special characters.',
            'paper_eprint' => '0908.2921v2',
            'paper_eprint_was_changed_on_last_save' => false,
            'paper_arxiv_pdf_attach_ids' => array(3),
            'paper_popular_summary' => 'Some random fake summary.',
            'paper_feature_image_caption' => 'Some random fake cation.',
            'paper_fermats_library' => '',
            'paper_validation_result' => 'fake_validation_result',
            'paper_title' => 'Fake title',
            'paper_title_mathml' => 'Fake title',
            'paper_corresponding_author_email' => 'mal_formed_corresponding_author_email',
            'paper_corresponding_author_has_been_notifed_date' => '',
            'paper_buffer_email' => '',
            'paper_buffer_email_was_sent_date' => '',
            'paper_buffer_special_text' => 'fake_paper_buffer_special_text',
            'paper_fermats_library_permalink' => 'fake_paper_fermats_library_permalink',
            'paper_fermats_library_permalink_worked' => 'fake_paper_fermats_library_permalink_worked',
            'paper_fermats_library_has_been_notifed_date' => '',
            'paper_number_authors' => 2,
            'paper_author_given_names' => ['Foo', 'Baz'],
            'paper_author_surnames' => ['Bar', 'Boo'],
            'paper_author_name_styles' => ["western", "western"],
            'paper_author_affiliations' => ['1,2','2'],
            'paper_author_orcids' => ['',''],
            'paper_author_urls' => ['',''],
            'paper_number_affiliations' => 2,
            'paper_affiliations' => ['Foo University', 'Bar Institut'],
            'paper_date_published' => current_time("Y-m-d"),
            'paper_journal' => 'fake_paper_journal',
            'paper_volume' => '2',
            'paper_pages' => '3',
            'paper_doi_prefix' => 'fake_paper_doi_prefix',
            'paper_doi_suffix' => 'fake_journal_level_doi_suffix-' . current_time("Y-m-d") . '-3',
            'paper_bbl' => 'fake_paper_bbl',
            'paper_author_latex_macro_definitions' => array(),
            'paper_crossref_xml' => 'fake_paper_crossref_xml',
            'paper_crossref_response' => 'fake_paper_crossref_response',
            'paper_doaj_json' => 'fake_paper_doaj_json',
            'paper_doaj_response' => 'fake_paper_doaj_response',
            'paper_arxiv_fetch_results' => 'fake_paper_arxiv_fetch_results',
            'paper_arxiv_source_attach_ids' => array(4),
            'paper_doi_suffix_was_changed_on_last_save' => false,
            'paper_clockss_xml' => '',
            'paper_clockss_response' => '',
                        ),
               ),
    9 => array(
        'post_type' => 'view',
        'post_content' => 'fake_post_content9',
        'paper_nonce' => 'fake_nonce',
        'thumbnail_id' => 2,
        'post_status' => 'publish',
        'post_title' => 'Fake title',
        'permalink' => 'Fake permalink',
        'meta' => array(
            'view_type' => 'Leap',
            'view_number_target_dois' => '0',
            'view_title' => 'A leaping title',
            'view_corresponding_author_email' => 'author@leap.me',
            'view_buffer_email' => 'checked',
            'view_number_authors' => '1',
            'view_number_affiliations' => '1',
            'view_date_published' => current_time("Y-m-d"),
            'view_doi_prefix' => 'fake',
            'view_reviewers_summary' => 'A nice summary.',
            'view_number_reviewers' => '2',
            'view_number_reviewer_institutions' => 1,
            'view_author_commentary' => 'A not so nice reply.',
            'view_validation_result' => '',
            'view_target_dois' => array(),
            'view_title_mathml' => '',
            'view_corresponding_author_has_been_notifed_date' => '',
            'view_buffer_email_was_sent_date' => '',
            'view_author_given_names' => array('Foo'),
            'view_affiliations' => array('Affiliation'),
            'view_journal' => '',
            'view_doi_suffix' => '',
            'view_reviewer_given_names' => array(),
            'view_reviewer_institutions' => array(),
            'view_about_the_author' => 'Some text about the author',
            'view_buffer_special_text' => 'Special buffer message',
            'view_author_surnames' => array('Bar'),
            'view_volume' => '1',
            'view_reviewer_surnames' => array(),
            'view_author_name_styles' => array('western', 'western'),
            'view_pages' => '1',
            'view_reviewer_name_styles' => array(),
            'view_author_affiliations' => array(),
            'view_reviewer_affiliations' => array(),
            'view_author_orcids' => array('', ''),
            'view_reviewer_orcids' => array(),
            'view_author_urls' => array('', ''),
            'view_reviewer_urls' => array(),
            'view_reviewer_ages' => array(),
            'view_reviewer_grades' => array(),
            'view_bbl' => '',
            'view_author_latex_macro_definitions' => array(),
            'view_crossref_xml' => '',
            'view_crossref_response' => '',
            'view_doaj_json' => '',
            'view_doaj_response' => '',
            'view_doi_suffix_was_changed_on_last_save' => false,
            'view_abstract' => '',
            'view_clockss_xml' => '',
            'view_clockss_response' => '',
                        )
               ),
    10 => array(
        'post_type' => 'view',
        'post_content' => 'fake_post_content10',
        'paper_nonce' => 'fake_nonce',
        'thumbnail_id' => 2,
        'post_status' => 'publish',
        'post_title' => 'Fake title',
        'permalink' => 'Fake permalink',
        'meta' => array(
            'view_type' => 'Leap',
            'view_number_target_dois' => '1',
            'view_title' => 'A leaping title',
            'view_corresponding_author_email' => 'author@leap.me',
            'view_buffer_email' => 'checked',
            'view_number_authors' => '1',
            'view_number_affiliations' => '1',
            'view_date_published' => current_time("Y-m-d"),
            'view_doi_prefix' => 'fake',
            'view_reviewers_summary' => 'A nice summary.',
            'view_number_reviewers' => '2',
            'view_number_reviewer_institutions' => 1,
            'view_author_commentary' => 'A not so nice reply.',
            'view_validation_result' => '',
            'view_target_dois' => array('a-doi-that-does-not-exist'),
            'view_title_mathml' => '',
            'view_corresponding_author_has_been_notifed_date' => '',
            'view_buffer_email_was_sent_date' => '',
            'view_author_given_names' => array('Baz'),
            'view_affiliations' => array('Foo Instiute'),
            'view_journal' => '',
            'view_doi_suffix' => '',
            'view_reviewer_given_names' => array(),
            'view_reviewer_institutions' => array(),
            'view_about_the_author' => 'Some text about the author',
            'view_buffer_special_text' => 'Special buffer message',
            'view_author_surnames' => array('Bazzz'),
            'view_volume' => '1',
            'view_reviewer_surnames' => array(),
            'view_author_name_styles' => array('western'),
            'view_pages' => '2',
            'view_reviewer_name_styles' => array(),
            'view_author_affiliations' => array('1'),
            'view_reviewer_affiliations' => array(),
            'view_author_orcids' => array(''),
            'view_reviewer_orcids' => array(),
            'view_author_urls' => array('http://www.baz.baz'),
            'view_reviewer_urls' => array(),
            'view_reviewer_ages' => array(),
            'view_reviewer_grades' => array(),
            'view_bbl' => '',
            'view_author_latex_macro_definitions' => array(),
            'view_crossref_xml' => '',
            'view_crossref_response' => '',
            'view_doaj_json' => '',
            'view_doaj_response' => '',
            'view_doi_suffix_was_changed_on_last_save' => false,
            'view_abstract' => '',
            'view_clockss_xml' => '',
            'view_clockss_response' => '',
                        )
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
        throw new Exception("Post with id=" . $post_id . " has no meta-data for key=" . $key . ".");
       /* echo("\nPost with id=" . $post_id . " has no meta-data for key=" . $key . "\n"); */
       /* return "fake!"; */
    }


    return $posts[$post_id]['meta'][$key];
}

function wp_nonce_field( $action, $name, $referer=true, $echo=true ) {}

class WP_Error
{
    private $code;
    private $message;
    private $data;

    function __construct( $code = '', $message = '', $data = '' ) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    function get_error_message() {
        return $this->message;
    }
}

class WP_Post
{
    public function __construct( $post_id ) {
        $this->ID = $post_id;
    }

    public $ID;
}

class WP_Query
{
    private $posts;
    private $query;

    function __construct( $input=null ) {
        global $posts;

        $this->query = $input;

        if(is_array($input))
            $array = $input;
        else
            $array = array($input);

        $this->posts = array();
        if(!empty($posts))
        {
            foreach($posts as $id => $post)
            {
                $include_post = true;
                foreach($array as $key => $value)
                {
                    if(!is_array($value))
                        $value = array($value);

                    if($key === 'ID' and in_array($id, $value))
                        break;

                    if(!isset($posts[$id][$key]) or !in_array ($posts[$id][$key], $value))
                    {
                        $include_post = false;
                        break;
                    }
                }
                if($include_post)
                    $this->posts[$id] = $post;
            }
        }
    }

    function get($key) {
        return $this->query[$key];
    }

    function have_posts() {
        return count($this->posts) > 0;
    }

    function the_post() {
        global $post_data;

        if(empty($this->posts))
        {
            throw new Exception("the_post() called with no posts left");
        }

        $keys = array_keys($this->posts);
        $min_key = min($keys);

        $current = $this->posts[$min_key];
//        array_shift($this->posts);
        unset($this->posts[$min_key]);

        $post_data = array('current' => $current, 'ID' => $min_key);
    }
}

$global_query = new WP_Query();
function set_global_query( $wp_query ) {
    global $global_query;

    $global_query = $wp_query;
}

function have_posts() {
    global $global_query;

    if(!($global_query instanceof WP_Query))
        throw(new Exception('You must fist set the $global_query before you can use have_posts()'));

    return $global_query->have_posts();
}

function the_post() {
    global $global_query;

    if(!($global_query instanceof WP_Query))
        throw(new Exception('You must fist set the $global_query before you can use have_posts()'));

    return $global_query->the_post();
}


function has_post_thumbnail() {
        $post_id = get_the_ID();

    return !empty($posts[$post_id]['thumbnail_id']);
}

function the_post_thumbnail() {
    global $posts;

    $post_id = get_the_ID();

    return '<img src="' . esc_url($posts[$posts[$post_id]['thumbnail_id']]['attachment_url']) . '" >';
}

function get_the_ID() {
    global $post_data;

    return $post_data['ID'];
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

    return 'esc_html' . $text;
}

function esc_attr__( $text ) {

    return 'esc_attr__' . $text;
}

function esc_attr( $text ) {

    return 'esc_attr' . $text;
}

function esc_url( $text ) {

    return 'esc_url' . $text;
}

function wp_get_attachment_url($id) {
    global $posts;

    $post_id = $id;

    if(!isset($posts[$post_id]['attachment_url']))
        throw new Exception("Post with id=" . $post_id . " has no attachment_url." . json_encode($posts[$post_id]));

    return $posts[$post_id]['attachment_url'];
}


function term_exists() {
    return 0;
}

function wp_insert_term() {}

function wp_set_post_terms() {}

function current_time( $format ) {
    return date($format);
}

function get_the_date( $format, $post_id ) {
    return current_time( $format );
}

function download_url( $url, $timeout_seconds ) {
    $tmppath = dirname(__FILE__) . '/tmp/';
    if(!file_exists($tmppath))
        mkdir($tmppath);
    $tmpfile = tempnam($tmppath , 'download_url_' );

        //we fake some downloads by copying local files
    $special_urls = array(
        'https://arxiv.org/pdf/0908.2921v2' => dirname(__FILE__) . '/arxiv/0908.2921v2.pdf',
        'https://arxiv.org/e-print/0908.2921v2' => dirname(__FILE__) . '/arxiv/0908.2921v2.tex',
        'https://arxiv.org/pdf/0809.2542v4' => dirname(__FILE__) . '/arxiv/0809.2542v4.pdf',
        'https://arxiv.org/e-print/0809.2542v4' => dirname(__FILE__) . '/arxiv/0809.2542v4.tar.gz',
    );
    if(!empty($special_urls[$url]))
        copy($special_urls[$url], $tmpfile);
    else
    {
        try {
            file_put_contents($tmpfile, fopen($url, 'r'));
        } catch(Exception $e) {
            return new WP_Error($e->getCode(), $e->getMessage());
        }
    }

    return $tmpfile;
}

function is_wp_error( $object ) {

    return is_object($object) ? get_class($object) == 'WP_Error' : false;
}

function wp_handle_sideload( $file, $overrides ) {
    $oldname = $file['tmp_name'];

    if(!file_exists($oldname))
        return array('error' => 'The file ' . $oldname . 'does not exist.');

    $newname = dirname($file['tmp_name']) . '/' . $file['name'];
    if(strpos($newname, ABSPATH) === false)
        throw new Exception('Target file ' . $newname . ' path would be outside of ABSPATH ' . ABSPATH . '. Aborting for security reasons.');
    rename($oldname, $newname);

    return array('file' => $newname );
}

function wp_insert_attachment( $attachment, $filepath, $parent_post_id ) {

    if(!file_exists(dirname($filepath)))
        throw new Exception('The file ' . $filepath . 'does not exist.');

    global $posts;

    $post_status = $attachment['post_status'];

    if($post_status === 'inherit')
        $post_status = $posts[$parent_post_id]['post_status'];

    $posts[] = array (
        'post_type' => 'attachment',
        'guid' => $attachment['guid'],
        'post_mime_type' => $attachment['post_mime_type'],
        'post_title' => $attachment['post_title'],
        'post_content' => $attachment['post_content'],
        'post_status' => $post_status,
        'attachment_path' => $filepath,
        'attachment_url' => 'fake_attachment_url',
    );

//    $posts[$parent_post_id][] = max(array_keys($posts));

    return max(array_keys($posts));
}

function wp_generate_attachment_metadata($attach_id, $filename) {
//    global $posts;

//    $posts[$attach_id]['filename'] = $filename;
}
function wp_update_attachment_metadata( $attach_id, $attach_data ) {

}

function get_attached_file( $post_id ) {
    global $posts;

    if(!isset($posts[$post_id]['attachment_path']))
        throw new Exception("Post with id=" . $post_id . " has no attachment_path.");

    return $posts[$post_id]["attachment_path"];
}

function wp_update_post( $array ) {
    global $posts;

    foreach($array as $key => $value)
    {
        if($key === 'ID')
            continue;

        $posts[$array['ID']][$key] = $value;
    }

}

function update_post_meta( $post_id, $key, $value ) {
    global $posts;

    $posts[$post_id]['meta'][$key] = $value;
}

function wp_reset_postdata() {
    global $post_data;

    $post_data = array();
}

function get_the_title( $post_id ) {
    global $posts;

    return $posts[$post_id]['post_title'];
}

function the_content() {
    echo get_the_content();
}

function the_category() {
    echo get_the_category();
}
function get_the_category() {
    return "fake category";
}

function comments_open( $post_id=null ) {
    return false;
}

function get_comments_number( $post_id=null ) {
    return 0;
}


function get_the_content() {
    global $posts;

    $post_id = get_the_ID();

    return $posts[$post_id]['post_content'];
}

function get_permalink( $post_id ) {
    global $posts;

    return $posts[$post_id]['permalink'];
}


function get_sidebar() {
    echo '<div>Sidebar</div>';
}
function get_footer() {
    echo '</body>
</html>';
}


function wp_mail( $to, $subject, $body, $headers, $attach=null) {
    return true;
}

function delete_transient() {}

function get_transient() {
    return false;
}

function set_transient( $transient, $value, $expiration ) {}


function sanitize_text_field( $string ) {

    return $string;
}

function wp_remote_get( $url, $args=array() ) {
        //return http_get( $url, $args );

    $local_file_urls = array(
        'https://arxiv.org/abs/0809.2542v4' => dirname(__FILE__) . '/arxiv/0809.2542v4.html',
        'https://arxiv.org/abs/1609.09584v4' => dirname(__FILE__) . '/arxiv/1609.09584v4.html',
        'https://arxiv.org/abs/0908.2921v2' => dirname(__FILE__) . '/arxiv/0908.2921v2.html',
                          );
    if(!empty($local_file_urls[$url]))
        return array('headers'=>'' ,'body'=> file_get_contents($local_file_urls[$url]) );
    elseif(strpos($url, get_option('o3po-setttings')['crossref_get_forward_links_url']) === 0)
        return array('body' => 'fake respose form crossref forward links url');
    else
        throw new Exception('Fake wp_remote_get() does not know how to handle ' . $url);
}

function wp_verify_nonce() {
    return true;
}

function current_user_can() {
    return true;
}

function wp_is_post_autosave() {
    return false;
}

function wp_is_post_revision() {
    return false;
}

function remove_action() {}

function wp_slash( $input ) {
    return $input;
}

function wp_generate_password( $length ) {
    $string = '';
    for($i=0; $i<$length; $i++)
        $string .= rand(0, 9); //This is just a face environemnt, so no security concerns
    return $string;
}

function wp_remote_post( $url, $args = array() ) {}

function register_post_type( $post_type, $args ) {}

function add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {}


$is_home = false;
function is_home() {
    global $is_home;
    return $is_home;
}

$is_category = false;
function is_category() {
    global $is_category;
    return $is_category;
}


function get_header() {
    echo '<!DOCTYPE html5>
<html lang="en-GB">
<head><title>fake title</title></head><body>';
}

function get_theme_mod() {
    return "";
}

function onepress_breadcrumb() {
    return "";
}
