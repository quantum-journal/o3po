<?php

/**
 * The bootstrap file for the PHPUnit tests.
 *
 * Here we define fake version of the WordPress functions
 * the plugin calls.
 */

include(dirname( __FILE__ ) . '/posts.php');
include(dirname( __FILE__ ) . '/formatting.php');
include(dirname( __FILE__ ) . '/kses.php');

require_once dirname( __FILE__ ) . '/../../o3po/includes/class-o3po-settings.php';

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

function esc_html_filter( $text ) {

    $replacements = array(
        '#&(?!amp;)#' => '&amp;',
        '#"#' => '&quot;',
        "#'#" => '&#039;',
        '#<#' => '&lt;',
        '#>#' => '&gt;',
    );
    foreach($replacements as $expression => $replacement)
        $text = preg_replace($expression, $replacement, $text);

    return $text;
}
add_filter( 'esc_html', 'esc_html_filter' );


function is_admin() {}

function get_site_url() {

    return 'https://foo.bar.com';
}

function get_option( $option, $default = false ) {

    if($option === 'o3po-settings')
        return array(
            'production_site_url' => get_site_url(),#we test as if this were the production system
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
            'license_url' => 'https://fake_license_url',
            'license_explanation' => 'fake_license_explanation',
            'crossref_id' => 'fake_crossref_id',
            'crossref_pw' => 'fake_crossref_pw',
            'crossref_get_forward_links_url' => 'https://fake_crossref_get_forward_links_url',
            'crossref_deposite_url' => 'https://fake_crossref_deposite_url',
            'crossref_test_deposite_url' => 'https://fake_crossref_test_deposite_url',
            'crossref_email' => 'fake_crossref_email',
            'crossref_archive_locations' => 'fake_crossref_archive_locations',
            'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
            'ads_api_token' => '',
            'arxiv_url_abs_prefix' => 'https://arxiv.org/abs/',
            'arxiv_url_pdf_prefix' => 'https://arxiv.org/pdf/',
            'arxiv_url_source_prefix' => 'https://arxiv.org/e-print/',
            'arxiv_url_trackback_prefix' => 'fake_arxiv_url_trackback_prefix',
            'arxiv_doi_feed_identifier' => 'fake_arxiv_doi_feed_identifier',
            'doi_url_prefix' => 'fake_doi_url_prefix',
            'scholastica_manuscripts_url' => 'https://fake_scholastica_manuscripts_url',
            'scirate_url_abs_prefix' => 'https://fake_scirate_url_abs_prefix',
            'orcid_url_prefix' => 'https://fake_orcid_url_prefix',
            'fermats_library_url_prefix' => 'https://fake_fermats_library_url_prefix',
            'fermats_library_email' => 'fake_fermats_library_email',
            'mathjax_url' => 'https://fake_mathjax_url',
            'social_media_thumbnail_url' => 'https://fake_social_media_thumbnail_url',
            'buffer_api_url' => 'https://fake_buffer_api_url',
            'buffer_access_token' => '081fa8123a892134ba93241',
            'buffer_profile_ids' => array('1513412357695652', '785663451345245'),
            'facebook_app_id' => 'https://fake_facebook_app_id',
            'doaj_api_url' => 'https://fake_doaj_api_url',
            'doaj_api_key' => 'fake_doaj_api_key',
            'doaj_language_code' => 'fake_doaj_language_code',
            'custom_search_page' => 'fake_custom_search_page',
            'volumes_endpoint' => 'fake_volumes_endpoint',
            'doi_prefix' => 'fake_doi_prefix',
            'eissn' => 'fake_eissn',
            'secondary_journal_eissn' => "fake_secondary_journal_eissn",
            'first_volume_year' => "2009",
            'custom_search_page' => 'checked',
            'page_template_for_publication_posts' => 'checked',
                     );
    elseif($option === 'blog_charset')
        return 'UTF-8';
    elseif($option === 'rewrite_rules')
        return array();
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

function add_rewrite_endpoint( $a, $b=Null ) {}

$post_data = array();

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
        #throw new Exception("Post with id=" . $post_id . " has no meta-data for key=" . $key . ".");
        if($single)
            return '';
        else
            return array();
    }


    return $posts[$post_id]['meta'][$key];
}

function get_all_post_metas( $post_id ) {
    global $posts;

    return $posts[$post_id]['meta'];
}

function set_post_status($post_id, $status) {
    global $posts;

    return $posts[$post_id]['post_status'] = $status;
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

    function get_error_code() {
        return $this->code;
    }
}

class WP_Post
{
    public $ID;
    public $post_type;

    public function __construct( $post_id, $post_type='post' ) {
        $this->ID = $post_id;
        $this->post_type = $post_type;
    }
}

class WP_Query
{
    private $posts;
    private $query;
    public $query_vars;
    public $post_count;
    public $found_posts;

    function __construct( $input=null, $query_vars=array() ) {
        global $posts;

        $this->query = $input;
        $this->query_vars = $query_vars;

        if(is_array($input))
            $array = $input;
        else
            $array = array($input);

        # turn 'key=value' type queries into $key => $value type ones
        foreach($array as $key => $value){
            if(is_numeric($key) and is_string($value) and strpos($value, '=') !== false)
            {
                $split = preg_split('/\s*=\s*/', $value);
                if(!empty($split[0]) and isset($split[1]))
                {
                    $array[$split[0]] = $split[1];
                    unset($array[$key]);
                }
            }
        }

        if(isset($array['posts_per_page']))
        {
            $max_posts_to_return = $array['posts_per_page'];
            unset($array['posts_per_page']);
        }

        $this->posts = array();
        if(!empty($posts) and $input !== null)
        {
            foreach($posts as $id => $post)
            {
                $include_post = true;
                foreach($array as $key => $value)
                {
                    if(!is_array($value))
                        $value = array($value);

                    if($key === 'ID')
                    {
                        $include_post = in_array($id, $value);
                        break;
                    }

                    if(!isset($posts[$id][$key]) or !in_array($posts[$id][$key], $value))
                    {
                        $include_post = false;
                        break;
                    }
                }
                if($include_post)
                    $this->posts[$id] = $post;
            }
        }

        $this->post_count = count($this->posts);
        $this->found_posts = $this->post_count;
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

    function is_search() {
        return isset($this->query_vars['s']) ? true : false;
    }

    function is_main_query() {
        return isset($this->query_vars['is_main']) ? $this->query_vars['is_main'] : false;
    }
}

$wp_query = new WP_Query();
function set_global_query( $query ) {
    global $wp_query;

    $wp_query = $query;
}

function get_global_query() {
    global $wp_query;

    return $wp_query;
}


function query_posts( $args ) {
    set_global_query(new WP_Query($args, $args)); #this is a hack! I don't actually understand how the real query_posts() sets query vars.
}

function is_search() {
    global $wp_query;

    return $wp_query->is_search();
}


$global_search_query = '';
function set_global_search_query( $string ) {
    global $global_search_query;

    $global_search_query = $string;
}

function get_search_query() {
    global $global_search_query;

    return $global_search_query;
}

function have_posts() {
    global $wp_query;

    if(!($wp_query instanceof WP_Query))
        throw(new Exception('You must first set the $wp_query before you can use have_posts()'));

    return $wp_query->have_posts();
}

function the_post() {
    global $wp_query;

    if(!($wp_query instanceof WP_Query))
        throw(new Exception('You must fist set the $wp_query before you can use have_posts()'));

    return $wp_query->the_post();
}


function set_query_var( $var, $val ) {
    global $wp_query;

    if(!($wp_query instanceof WP_Query))
        throw(new Exception('You must first set the $wp_query before you can use get_query_var()'));

    $wp_query->query_vars[$var] = $val;
}

function get_query_var( $var ) {
    global $wp_query;

    if(!($wp_query instanceof WP_Query))
        throw(new Exception('You must first set the $wp_query before you can use get_query_var()'));

    return $wp_query->query_vars[$var];
}


function has_post_thumbnail( $post_id=Null ) {
    global $posts;

    if($post_id === NUll)
        $post_id = get_the_ID();

    return !empty($posts[$post_id]['thumbnail_id']);
}

function the_post_thumbnail() {
    global $posts;

    $post_id = get_the_ID();

    return '<img src="' . esc_url($posts[$posts[$post_id]['thumbnail_id']]['attachment_url']) . '" >';
}

function get_the_post_thumbnail($post_id, $size = 'post-thumbnail') {
    global $posts;

    return '<img src="' . esc_url($posts[$posts[$post_id]['thumbnail_id']]['attachment_url']) . '" >';
}

function get_the_post_thumbnail_url( $post_id = null, $size = 'post-thumbnail') {
    global $posts;

    return $posts[$posts[$post_id]['thumbnail_id']]['attachment_url'];
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

function get_post_mime_type( $post_id ) {
    global $posts;

    return $posts[$post_id]['mime_type'];
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
    global $posts;

    if($format !== 'Y-m-d')
        throw Exception("Date format not implemented");

    return $posts[$post_id]['date'];
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
        'https://arxiv.org/e-print/1708.05489v2' => dirname(__FILE__) . '/arxiv/1708.05489v2.tar.gz',
        'https://arxiv.org/e-print/1711.04662v3' => dirname(__FILE__) . '/arxiv/1711.04662v3.tar.gz',
        'https://arxiv.org/pdf/1806.02820v3' => dirname(__FILE__) . '/arxiv/1806.02820v3.pdf',
        'https://arxiv.org/e-print/1806.02820v3' => dirname(__FILE__) . '/arxiv/1806.02820v3.tar.gz',
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

    $newfilename = $file['name'];
    $newdir = dirname($file['tmp_name']) . '/';
    $newext = '.' . pathinfo($newfilename, PATHINFO_EXTENSION);

    if(!empty($overrides['unique_filename_callback']))
        $newfilename = call_user_func($overrides['unique_filename_callback'], $newdir, $newfilename, $newext);

    $newname = $newdir . $newfilename;
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

    serialize($value); #test that value is serializable. We save the plain value for simplicity.

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

$get_transient_returns = false;
function get_transient( $transient ) {
    global $get_transient_returns;

    return $get_transient_returns;
}

function set_transient( $transient, $value, $expiration=0 ) {}

function wp_remote_get( $url, $args=array() ) {
        //return http_get( $url, $args );

    $local_file_urls = array(
        'https://arxiv.org/abs/0809.2542v4' => dirname(__FILE__) . '/arxiv/0809.2542v4.html',
        'https://arxiv.org/abs/0809.2542v5' => dirname(__FILE__) . '/arxiv/0809.2542v5.html',
        'https://arxiv.org/abs/1609.09584v4' => dirname(__FILE__) . '/arxiv/1609.09584v4.html',
        'https://arxiv.org/abs/0908.2921v2' => dirname(__FILE__) . '/arxiv/0908.2921v2.html',
        'https://arxiv.org/abs/1806.02820v3' => dirname(__FILE__) . '/arxiv/1806.02820v3.html',
         'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0908.2921&fl=citation' => dirname(__FILE__) . '/ads/0908.2921.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0809.2542&fl=citation' => dirname(__FILE__) . '/ads/0809.2542.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2010CoTPh..54.1023Z+OR+bibcode:2011EPJB...81..155H+OR+bibcode:2011JSMTE..05..023Z+OR+bibcode:2014PhyA..414..240P&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000' => dirname(__FILE__) . '/ads/0809.2542_citations.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:1806.02820&fl=citation' => dirname(__FILE__) . '/ads/1806.02820.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2018arXiv180601279B+OR+bibcode:2018arXiv181009469B+OR+bibcode:2018arXiv181205117B&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000' => dirname(__FILE__) . '/ads/1806.02820_citation.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:1610.01808&fl=citation' => dirname(__FILE__) . '/ads/1610.01808.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2015arXiv151207892E+OR+bibcode:2016arXiv160800263B+OR+bibcode:2016arXiv161003632F+OR+bibcode:2016arXiv161201652L+OR+bibcode:2016arXiv161205903A+OR+bibcode:2017arXiv170309568K+OR+bibcode:2017arXiv170401998M+OR+bibcode:2017arXiv170500686N+OR+bibcode:2017arXiv170608913Y+OR+bibcode:2017arXiv170801875B+OR+bibcode:2017arXiv170903489H+OR+bibcode:2017arXiv171205384B+OR+bibcode:2017NatCo...8.1572A+OR+bibcode:2017npjQI...3...15L+OR+bibcode:2017PhRvA..95d2336M+OR+bibcode:2017PhRvL.118d0502G+OR+bibcode:2018arXiv180306775B+OR+bibcode:2018arXiv180603200Y+OR+bibcode:2018arXiv180906957H+OR+bibcode:2018QS&T....3b5004V+OR+bibcode:2018Sci...360..195N+OR+bibcode:2018Sci...362..308B&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000' => dirname(__FILE__) . '/ads/1610.01808_citations.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0001&fl=citation' => dirname(__FILE__) . '/ads/0000.0001.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0002&fl=citation' => dirname(__FILE__) . '/ads/0000.0002.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0003&fl=citation' => array('headers' => array('x-ratelimit-remaining' => 0), 'body' => dirname(__FILE__) . '/ads/0000.0003.json'),
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0004&fl=citation' => dirname(__FILE__) . '/ads/0000.0004.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2010Abcde..12.3456A&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000' => dirname(__FILE__) . '/ads/0000.0004_citations.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0005&fl=citation' => dirname(__FILE__) . '/ads/0000.0005.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0006&fl=citation' => dirname(__FILE__) . '/ads/0000.0006.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2015Phys...93.1143F&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000' => array('headers' => array('x-ratelimit-remaining' => 0), 'body' => dirname(__FILE__) . '/ads/0000.0006_citations.json'),
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0007&fl=citation' => dirname(__FILE__) . '/ads/0000.0007.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2016Phys...12.4444F&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000' => array('headers' => array(), 'body' => dirname(__FILE__) . '/ads/0000.0007_citations.json'),
        'https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0008&fl=citation' => dirname(__FILE__) . '/ads/0000.0008.json',
        'https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2017XYZ....00001111&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000' => dirname(__FILE__) . '/ads/0000.0008_citations.json',
        get_option('o3po-settings')['crossref_get_forward_links_url'] . '?usr=' . get_option('o3po-settings')['crossref_id'] . '&pwd=' . get_option('o3po-settings')['crossref_pw'] .'&doi=10.22331%2Fq-2017-04-25-8&include_postedcontent=true' => dirname(__FILE__) . '/crossref/q-2017-04-25-8.xml',
        get_option('o3po-settings')['crossref_get_forward_links_url'] . '?usr=' . get_option('o3po-settings')['crossref_id'] . '&pwd=' . get_option('o3po-settings')['crossref_pw'] .'&doi=10.22331%2Fq-2018-08-06-79&include_postedcontent=true' => dirname(__FILE__) . '/crossref/q-2018-08-06-79.xml',
        get_option('o3po-settings')['crossref_get_forward_links_url'] . '?usr=' . get_option('o3po-settings')['crossref_id'] . '&pwd=' . get_option('o3po-settings')['crossref_pw'] .'&doi=empty_response&include_postedcontent=true' => dirname(__FILE__) . '/crossref/empty_response.xml',
        get_option('o3po-settings')['crossref_get_forward_links_url'] . '?usr=' . get_option('o3po-settings')['crossref_id'] . '&pwd=' . get_option('o3po-settings')['crossref_pw'] .'&doi=invalid_xml&include_postedcontent=true' => dirname(__FILE__) . '/crossref/invalid_xml.xml',
        get_option('o3po-settings')['crossref_get_forward_links_url'] . '?usr=' . get_option('o3po-settings')['crossref_id'] . '&pwd=' . get_option('o3po-settings')['crossref_pw'] .'&doi=varied_cites&include_postedcontent=true' => dirname(__FILE__) . '/crossref/varied_cites.xml',
        get_option('o3po-settings')['crossref_get_forward_links_url'] . '?usr=' . get_option('o3po-settings')['crossref_id'] . '&pwd=' . get_option('o3po-settings')['crossref_pw'] .'&doi=unhandled_forward_link_type&include_postedcontent=true' => dirname(__FILE__) . '/crossref/unhandled_forward_link_type.xml',
        'https://api.bufferapp.com/1/profiles.json?access_token=1%2F345792aa62c_7' => dirname(__FILE__) . '/buffer/profile_ids.json',
        'https://api.bufferapp.com/1/profiles.json?access_token=1%2F345792aa62c_9' => dirname(__FILE__) . '/buffer/profile_ids_error.json',
        'https://api.bufferapp.com/1/profiles.json?access_token=1%2F345792aa62c_10' => array('Just some object that cannot tbe json_decoded and therefor leads to an error'),
                          );

    if(!empty($local_file_urls[$url]))
        if(is_array($local_file_urls[$url]))
            return array('headers'=>$local_file_urls[$url]['headers'] ,'body'=> file_get_contents($local_file_urls[$url]['body']) );
        else
            return array('headers'=>array() ,'body'=> file_get_contents($local_file_urls[$url]) );
    /* elseif(strpos($url, get_option('o3po-settings')['crossref_get_forward_links_url']) === 0) */
    /*     return array('body' => 'fake respose form crossref forward links url'); */
    else
        return new WP_Error('unhandled_url', 'Fake wp_remote_get() does not know how to handle ' . $url);

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

function wp_generate_password( $length ) {
    $string = '';
    for($i=0; $i<$length; $i++)
        $string .= rand(0, 9); //This is just a fake environemnt, so no security concerns
    return $string;
}

function wp_remote_post( $url, $args = array() ) {
    $response_array = array(
        'https://api.bufferapp.com/1/updates/create.json?access_token=1%252F345792aa62c_1' => dirname(__FILE__) . '/buffer/successful.json',
        'https://api.bufferapp.com/1/updates/create.json?access_token=invalid_token' => dirname(__FILE__) . '/buffer/token_invalid.json',
        'https://api.bufferapp.com/1/updates/create.json?access_token=1%252F345792aa62c_2' => dirname(__FILE__) . '/buffer/select_account.json',
        'https://api.bufferapp.com/1/updates/create.json?access_token=1%252F345792aa62c_3' => dirname(__FILE__) . '/buffer/no_permission.json',
        'https://invalid.api.bufferapp.com/1/updates/create.json?access_token=1%252F345792aa62c_5' => null,#dirname(__FILE__) . '/buffer/invalid.json',
        'https://api.bufferapp.com/1/updates/create.json?access_token=1%252F345792aa62c_6' => new WP_Error('error', 'this url produces an error'),
        'https://fake_buffer_api_url/updates/create.json?access_token=081fa8123a892134ba93241' => dirname(__FILE__) . '/buffer/successful.json',
        'Einselection+without+pointer+states+by+Christian+Gogolin&media[photo]=f&attachment=true&shorten=false&now=false&top=false' => dirname(__FILE__) . '/buffer/successful.json',
        'Dynamic+wetting+with+two+competing+adsorbates+by+Christian+Gogolin%2C+Christian+Meltzer%2C+Marvin+Willers%2C+and+Haye+Hinrichsen&media[photo]=f&attachment=true&shorten=false&now=false&top=false' => dirname(__FILE__) . '/buffer/invalid.json',
        'The+boundaries+and+twist+defects+of+the+color+code+and+their+applications+to+topological+quantum+computation+by+Markus+S.+Kesselring%2C+Fernando+Pastawski%2C+Jens+Eisert%2C+and+Benjamin+J.+Brown&media[photo]=f&attachment=true&shorten=false&now=false&top=false' => dirname(__FILE__) . '/buffer/successful.json',
        'https://fake_doaj_api_url.com?api_key=key' => dirname(__FILE__) . '/doaj/success.json',
        'https://fake_doaj_api_url?api_key=fake_doaj_api_key' => dirname(__FILE__) . '/doaj/success.json',
        'https://fake_crossref_test_deposite_url' => dirname(__FILE__) . '/crossref/deposit_success.xml',
        'https://fake_crossref_deposite_url' => dirname(__FILE__) . '/crossref/deposit_success.xml',
                             );

    foreach(array_keys($response_array) as $key)
    {
        if(strpos($url, $key) !== false)
        {
            if(!empty($response_array[$key]) and is_string($response_array[$key]))
                return array('body' => file_get_contents($response_array[$key]));
            elseif(!empty($response_array[$key]) and (is_array($response_array[$key]) or is_wp_error($response_array[$key])))
                return $response_array[$key];
            else
                return array();
        }
    }


    /* if(in_array($url, array_keys($response_array))) */
    /* { */
    /*     if(!empty($response_array[$url]) and is_string($response_array[$url])) */
    /*         return array('body' => file_get_contents($response_array[$url])); */
    /*     elseif(!empty($response_array[$url]) and is_array($response_array[$url])) */
    /*         return $response_array[$url]; */
    /*     else */
    /*         return array(); */
    /* } */

    echo("\nunhandled url in wp_remote_post() in bootstrap.php:" . $url . "\n");
}

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
    echo '<!DOCTYPE html>
<html lang="en-GB">
<head><title>fake title</title></head><body>';
}

function get_theme_mod() {
    return "";
}

function onepress_breadcrumb() {
    return "";
}

function get_search_form() {

echo '<form role="search" method="get" class="search-form" action="' . 'http://some.url.org' . '">
				<label>
					<span class="screen-reader-text">Search for:</span>
					<input type="search" class="search-field" placeholder="Search &hellip;" value="' . get_search_query() . '" name="s" />
				</label>
				<input type="submit" class="search-submit" value="Search" />
			</form>';
}

function get_template_part() {

    echo '';
}

function locate_template( $array ) {

    return $array[0];
}

function the_posts_navigation() {

    echo '';
}

$global_settings = array();
$wp_settings_fields = array();
$wp_settings_sections = array();

function register_setting( $option_group, $option_name, $args = array() ) {
    global $global_settings;

    if(!isset($global_settings[$option_group]))
        $global_settings[$option_group] = array();

    if(!isset($global_settings[$option_group][$option_name]))
        $global_settings[$option_group][$option_name] = $args;
}

function add_settings_section( $id, $title, $callback, $page ) {
    global $global_settings, $wp_settings_sections;

    if(empty($global_settings['sections']))
        $global_settings['sections'] = array();

    $global_settings['sections'][$id] = array(
        'title' => $title,
        'callback' => $callback,
        'page' => $page,
        'fields' => array(),
                                               );

    if(!isset($wp_settings_sections[$page]))
        $wp_settings_sections[$page] = array();

    $wp_settings_sections[$page][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback);
}

function add_settings_field( $id, $title, $callback, $page, $section = 'default', $args = array() ) {
    global $global_settings, $wp_settings_fields;

    $keys = array_keys($global_settings['sections']);
    $last_settings_section_id = end($keys);
        //$current_section = end($global_settings['sections']);

    $global_settings['sections'][$last_settings_section_id]['fields'][$id] = array(
        'title' => $title,
        'callback' => $callback,
        'page' => $page,
                                                                                   );

    $wp_settings_fields[$page][$section][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'args' => $args);
}

$global_setting_errors = array();

function settings_errors( $setting = '', $sanitize = false, $hide_on_update = false ) {
    global $global_setting_errors;

    $output = '';
    foreach ( $global_setting_errors as $key => $details ) {
        $css_id = 'setting-error-' . $details['code'];
        $css_class = $details['type'] . ' settings-error notice is-dismissible';
        $output .= "<div id='$css_id' class='$css_class'> \n";
        $output .= "<p><strong>{$details['message']}</strong></p>";
        $output .= "</div> \n";
    }
    echo $output;
}

function add_settings_error( $setting, $code, $message, $type = 'error' ) {
    global $global_setting_errors;

    $global_setting_errors[] = array(
        'setting' => $setting,
        'code'    => $code,
        'message' => $message,
        'type'    => $type
                                     );
}


function settings_fields( $option_group ) {

}

function do_settings_sections( $id ) {
    global $global_settings;

    $id = substr($id, strpos($id, ":") + 1);

    call_user_func($global_settings['sections'][$id]['callback']);
    foreach($global_settings['sections'][$id]['fields'] as $id => $properties)
    {
        call_user_func($properties['callback']);
    }
}


function checked( $helper, $current=true, $echo=true, $type='checked' ) {
    if ( (string) $helper === (string) $current )
        $result = " $type='$type'";
    else
        $result = '';

    if ( $echo )
        echo $result;

    return $result;
}

function apply_filters( $hook, $orig_text, $text )
{
    global $filters;

    if(!empty($filters[$hook]))
    {
        foreach($filters[$hook] as $callable)
            $text = call_user_func($callable, $text);
    }

    return $text;
}

/**
 * Stripped down version of the original wp_allowed_protocols()
 */
function wp_allowed_protocols() {
    static $protocols = array();

    if ( empty( $protocols ) ) {
        $protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' );
    }

    /* if ( ! did_action( 'wp_loaded' ) ) { */
    /*     /\** */
    /*      * Filters the list of protocols allowed in HTML attributes. */
    /*      * */
    /*      * @since 3.0.0 */
    /*      * */
    /*      * @param array $protocols Array of allowed protocols e.g. 'http', 'ftp', 'tel', and more. */
    /*      *\/ */
    /*     $protocols = array_unique( (array) apply_filters( 'kses_allowed_protocols', $protocols ) ); */
    /* } */

    return $protocols;
}

function wp_load_alloptions() {
    return array();
}

function the_archive_title( $before = '', $after = '' ) {
    $title = get_the_archive_title();

    if ( ! empty( $title ) ) {
        echo $before . $title . $after;
    }
}

function get_the_archive_title() {
    return "foo";
}

function the_archive_description( $before = '', $after = '' ) {
    $title = get_the_archive_description();

    if ( ! empty( $title ) ) {
        echo $before . $title . $after;
    }
}

function get_the_archive_description() {
    return "bar";
}

function load_plugin_textdomain( $slug, $b, $dir ) {

}

function plugin_basename( $file  ) {

    return 'fake_basename';
}

function get_bloginfo( $url ) {

    return 134134;
}

function add_options_page($a, $b, $c, $d) {

}