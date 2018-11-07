<?php

/**
 * The functionality related to individual journals
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

/**
 * The functionality related to individual journals.*
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Journal {


        /**
         * Array holding the configuration of the journal.
         *
         * @since    0.1.0
         * @access   private
         * @var      array      $journal_config    Array of properties definig the journal.
         */
    private $journal_config = array();

        /**
         * Initialize the journal.
         *
         * @since    0.1.0
         * @access   public
         * @param    array    $journal_config   Array with values for the keys retuned by get_journal_config_properties().
         */
	public function __construct( $journal_config ) {

		$this->journal_config = $journal_config;

            //check whether at least all properties specified in get_journal_config_properties have been provided
        foreach($this->get_journal_config_properties() as $journal_config_property)
            $this->get_journal_property($journal_config_property);

	}


        /**
         * Required journal config properties
         *
         * @since   0.1.0
         * @access  private
         * @var     array    $required_journal_config_properties   Array of all required journal config properties.
         */
    static private $required_journal_config_properties = array(
            'arxiv_doi_feed_identifier', //from primary
            'arxiv_url_abs_prefix',
            'arxiv_url_pdf_prefix', //from primary
            'arxiv_url_source_prefix', //from primary
            'arxiv_url_trackback_prefix', //from secondary
            'buffer_secret_email',
            'crossref_archive_locations',
            'crossref_deposite_url',
            'crossref_email',
            'crossref_get_forward_links_url',
            'crossref_id',
            'crossref_pw',
            'crossref_test_deposite_url',
            'developer_email',
            'doaj_api_key',
            'doaj_api_url',
            'doaj_language_code',
            'doi_prefix',
            'journal_level_doi_suffix',
            'doi_url_prefix',
            'eissn',
            'fermats_library_email', //from primary
            'fermats_library_url_prefix', //from primary
            'first_volume_year',
            'journal_title',
            'license_explanation',
            'license_name', //from primary
            'license_type',
            'license_url',
            'license_version',
            'orcid_url_prefix',
            'publisher',
            'publisher_country',
            'publisher_email',
            'scholastica_manuscripts_url', //from primary
            'clockss_ftp_url',
            'clockss_username',
            'clockss_password',

            'publication_type_name', //not in in settings
            'publication_type_name_plural', //not in in settings
            'volumes_endpoint', //in settings
                       );

        /**
         * Get an array of all options the journal expects in its journal config.
         *
         * @since    0.1.0
         * @access   public
         */
    public static function get_journal_config_properties() {

        return static::$required_journal_config_properties;
    }

        /**
         * Get a journal property.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id    Id of the property.
         */
    public function get_journal_property( $id ) {

        if(!isset($this->journal_config[$id]))
            throw new Exception('Journal property "' . $id . '" is requested but was not provided.' . ' journal_config contains ' . count($this->journal_config) . ' entries.');

        return $this->journal_config[$id];
    }

        /**
         * Adds a rewrite endpoint for the /volumes/, /volumes/1, /volumes/2, ... pages.
         *
         * To be added to the 'init' action.
         *
         * @since    0.1.0
         * @access   public
         * */
    public function add_volumes_endpoint() {

        add_rewrite_endpoint( $this->get_journal_property('volumes_endpoint'), EP_ROOT );
            // flush_rewrite_rules( true );  //// <---------- ONLY COMMENT IN WHILE TESTING
    }


        /**
         * Handle the requests to the /volume/ endpoint.
         *
         * We want to display an overview of the available volumes and then sub-pages
         * with lists for each volume. Here we set up appropriate queries to do that.
         *
         * We then want that Wordpress and the theme take care of turning these queries
         * into actual pages without the need for further intervention from ours side
         * to the extent possible in order to (i) make this plugin as theme independent
         * as possible and (ii) have the volume page rendered with the global theme the
         * users have chosen for their website.
         *
         * Unfortunately we run into several complications:
         *
         * - For the lists of publications in a given volume it is straight forward to
         * generate the query and a navigation can be nicely added just above the list
         * by hooking into 'loop_start' (see below).
         *
         * - For the overview page of all volumes we can add the navigation in the same
         * way, but we don't want to display any entries.
         * We thus set up an empty query and force the usage of the 'page' template (see
         * the function volume_endpoint_template() below). Without any posts to show,
         * however, even the 'page' template does not execute 'loop_start' and (depending
         * on the theme) can fall back to the 'content-none' template. To prevent this we
         * have to add a fake "empty" post. We do this in during the 'the_posts' action in
         * add_fake_empty_post_to_volume_overview_page() (see below).
         *
         * - Finally, we want the entries in the list to appear more "compressed" than in
         * other archive pages. This again could be solved with a custom theme or template,
         * but we want to stay largely theme independent. We this insert some java script
         * into the page to hide pars of the generated html and reduce the spacing between
         * elements. This will not work with every theme, but at least is unlikely to cause
         * serious unintended side-effects.
         *
         * The true long-term solution to the problem we are solving here could be taxonomies.
         *
         * To be added to the 'parse_request' action.
         *
         * @since    0.1.0
         * @access   public
         * @param    WP_Query     $wp_query   Query to act on.
         * @return   Modified WP_Query object.
         * */
    public function handle_volumes_endpoint_request( $wp_query ) {

        $settings = O3PO_Settings::instance();

        if ( !isset( $wp_query->query_vars[ $this->get_journal_property('volumes_endpoint') ] ) )
            return;

        $extras = preg_split('#/#' , $wp_query->query_vars[ $this->get_journal_property('volumes_endpoint') ]);

        if(isset($extras[0]))
            $vol_num = (int)$extras[0];

        if(isset($extras[1]) and $extras[1] === 'page' and isset($extras[2]) and ctype_digit($extras[2]))
            $page = (int)$extras[2];
        if(empty($page))
            $page = 1;

        if(isset($vol_num) and $vol_num>=1)
            query_posts(array('post_status' => 'publish', 'post_type' => $this->get_journal_property('publication_type_name'), 'meta_key' => $this->get_journal_property('publication_type_name') . '_volume', 'meta_value' => $vol_num, 'paged' => $page, 'posts_per_page' => 9999 ));
        else
        {
            query_posts(array('post_type' => 'page', 'post__in' => array(0), $this->get_journal_property('volumes_endpoint') . '_add_fake_post' => true)); //empty query but with $this->get_journal_property('volumes_endpoint') => true so that we know that we should inject a fake post
        }

        set_query_var($this->get_journal_property('volumes_endpoint'), true);
        set_query_var('page', $page);
        if(isset($vol_num)) set_query_var('vol_num', $vol_num);

        return $wp_query;
    }

        /**
         * Add navigation before the loop to the pages of the volume endpoint.
         *
         * To be added to 'loop_start'.
         *
         * @since  0.2.2+
         * @access public
         * @param  WP_Query  $wp_query   The current Wordpress query.
         */
    public function volume_navigation_at_loop_start( $wp_query ) {

        $settings = O3PO_Settings::instance();

        if ( !isset( $wp_query->query_vars[ $this->get_journal_property('volumes_endpoint') ] ) )
            return;

        $page = get_query_var('page');
        $vol_num = get_query_var('vol_num');

        $content = "";
        if ( empty($vol_num) or $vol_num < 1 ) {
            $last_volume = getdate()["year"] - ($settings->get_plugin_option('first_volume_year')-1);
            $content .= '<h1>Volumes published by ' . $settings->get_plugin_option('journal_title') . '</h1>';
            $content .= '<p>&larr; <a href="' . get_site_url() . '">back to main page</a><p>';
            $content .= '<ul>';
            for ($volume = 1; $volume <= $last_volume; $volume++) {
                $content .= '  <li><a href="' . get_site_url() . '/volumes/' . $volume . '/">Volume ' . $volume . ' (' . ($volume+($settings->get_plugin_option('first_volume_year')-1)) . ') ' . $this->get_count_of_volume($volume, $this->get_journal_property('publication_type_name')) . ' ' . $this->get_journal_property('publication_type_name_plural') . '</a></li>';
            }
            $content .= '</ul>';
        }
        else {
            $content .= '<h1>' . $this->get_count_of_volume($vol_num, $this->get_journal_property('publication_type_name')) . ' ' . $this->get_journal_property('publication_type_name_plural') . ' in Volume ' . $vol_num . ' (' . ($vol_num+($settings->get_plugin_option('first_volume_year')-1)) . ')</h1>';
            $content .= '<p>&larr; <a href="' . get_site_url() . '/volumes/">back to all volumes</a><p>';
        }

        echo $content;

    }

        /**
         * Force the usage of the page template on the volume overview page
         *
         * Checks for the presence of a query var ending in '_add_fake_post'
         * to determine when to change the template.
         *
         * @since  0.2.2+
         * @access public
         * @param  string   $template   The template that would be used.
         * @return string   Template that should be used.
         */
    public function volume_endpoint_template( $template ) {

        global $wp_query;

        if ( !isset( $wp_query->query_vars[ $this->get_journal_property('volumes_endpoint') . '_add_fake_post' ] ) )
            return $template;

        return locate_template( array( 'page.php' ) );
    }

        /**
         * Add a fake "empty" post to make 'loop_start' fire on the volume overview page
         *
         * For queries that do not return any posts, the loop is never run.
         * As we initiate an empty query for the top level volume endpoint
         * that is supposed to serve the volume overview page, we must add
         * at least a fake "empty" post to the $posts in order to allow us
         * to inject content into the standard page template of the current
         * theme.
         *
         * @since  0.2.2+
         * @access public
         * @param  array   $posts   Array of posts that would be returned if we do not intervene.
         * @return array   Array of posts that are returned if we do intervene.
         */
    public function add_fake_empty_post_to_volume_overview_page( $posts ) {

        global $wp_query;

        if ( count($posts) > 0 or !isset( $wp_query->query_vars[ $this->get_journal_property('volumes_endpoint') . '_add_fake_post' ] ) )
            return $posts;

            //create a fake post
        $page_slug = $this->get_journal_property('volumes_endpoint');
        $post = new stdClass;
        $post->post_author = -1;
        $post->post_name = $page_slug;
        $post->guid = get_bloginfo( get_site_url() . $page_slug);
        $post->post_title = '';
        $post->post_content = '';
        $post->ID = -1;
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql',1);
        $post = (object) array_merge((array) $post, array());
        $posts = NULL;
        $posts[] = $post;

        return $posts;
    }

        /**
         * Inject java script to compress the entries in the volume listings
         *
         * Inject some java script to change how the entries of
         * individual publications are displayed in the list of a volume.
         *
         * @since  0.2.2+
         * @access public
         * @para   WP_Query  $wp_query   The current Wordpress query.
         */
    public function compress_enteies_in_volume_view( $wp_query ) {

        if ( !isset( $wp_query->query_vars[ $this->get_journal_property('volumes_endpoint') ] ) )
            return;

        echo '<script type="text/javascript">
var i;
var elemets_to_not_display = document.querySelectorAll(".list-article-meta,.abstract-in-excerpt,.list-article-thumb");
for (i = 0; i < elemets_to_not_display.length; i++) {
    elemets_to_not_display[i].style.display = "none";
}
var elemets_to_condense = document.querySelectorAll(".list-article,.entry-title");
for (i = 0; i < elemets_to_condense.length; i++) {
    elemets_to_condense[i].style.padding = "0.2em 0";
    elemets_to_condense[i].style.margin = "0";
}
</script>';
    }

        /**
         * Returns information about the post with the highest page number.
         *
         * We have an aricle/fake page number that counts up 1,2,3,... This
         * function determines the highes such number among all posts of the
         * given publication types as well as the publication date of that
         * post. Only published and future posts (i.e. such scheduled for
         * publication) apart from the excluded post are taken into account.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id_to_exclude    Id of a post to exclude in the calculation
         * @param    array  $post_types            Array of post types to take into account.
         * */
    public static function get_post_type_highest_pages_info( $post_id_to_exclude, $post_types ) {

        $pages_highest = 0;
        $pages_highest_date_published = '';
        $pages_highest_id = 0;
        $future_post_exists = false;
        foreach($post_types as $post_type)
        {
            $query = array(
                'post_type' => $post_type,
                'post_status' => array('publish', 'future')
                           );

            $my_query = new WP_Query( $query );
            if ( $my_query->have_posts() ) {
                while ( $my_query->have_posts() ) {
                    $my_query->the_post();

                    if( get_post_status ( get_the_ID() ) === 'future') {
                        $future_post_exists = true;
                    }

                    if(get_the_ID() === $post_id_to_exclude)
                        continue;

                    $curr_pages = get_post_meta( get_the_ID(), $post_type . '_pages', true );
                    if( $pages_highest <= $curr_pages) {
                        $pages_highest = $curr_pages;
                        $pages_highest_date_published = get_post_meta( get_the_ID(), $post_type . '_date_published', true );
                        $pages_highest_id = get_the_ID();
                    }
                }
            }
        }
        wp_reset_postdata();

        return array( 'pages' => $pages_highest,
                      'date_published' => $pages_highest_date_published,
                      'future_post_exists' => $future_post_exists);
    }

        /**
         * Determines whether a given page number is still free.
         *
         * We have an aricle/fake page number that counts up 1,2,3,... This
         * function determines whether $pages is still free, in the sense
         * that no post with a type in $post_types other than that with
         * id $post_id already has page number $pages.
         *
         * @since    0.1.0
         * @access   public
         * @param    int      $post_id_to_exclude    Id of a post to exclude.
         * @param    int      $pages                 Page number to be checked for whether it is still free or not.
         * @param    array    $post_types            Post types to take into accoun.
         * */
    public static function pages_still_free_info( $post_id_to_exclude, $pages, $post_types ) {

        $still_free = true;
        $title = '';
        foreach($post_types as $post_type)
        {
            $query = array(
                'post_type' => $post_type
                           );

            $my_query = new WP_Query( $query );
            if ( $my_query->have_posts() ) {
                while ( $my_query->have_posts() ) {
                    $my_query->the_post();

                    if(get_the_ID() === $post_id_to_exclude)
                        continue;

                    $curr_pages = get_post_meta( get_the_ID(), $post_type . '_pages', true );
                    if( intval($pages) === intval($curr_pages)) {
                        $still_free = false;
                        $title = get_post_meta( get_the_ID(), $post_type . '_title', true );
                        break;
                    }
                }
            }
            if(!$still_free)
                break;
        }
        wp_reset_postdata();

        return array( 'still_free' => $still_free,
                      'title' => $title);
    }


        /**
         * Determines whether a given page number is still free.
         *
         * This function can be used to check whether a given $doi_suffix is
         * sitll free or already taken by a registered post type.
         *
         * @since    0.1.0
         * @access   public
         * @param    string   $doi_suffix   Doi suffix to be checked.
         * @param    array    $post_types   Post types to take into accoun.
         * */
    public static function doi_suffix_stil_free( $doi_suffix, $post_types ) {

        $still_free = true;
        foreach($post_types as $post_type)
        {
            $my_query = new WP_Query( 'post_type=' . $post_type );
            if ( $my_query->have_posts() ) {
                while ( $my_query->have_posts() ) {
                    $my_query->the_post();
                    $curr_doi_suffix = get_post_meta( get_the_ID(), $post_type . '_doi_suffix', true );
                    if( $curr_doi_suffix === $doi_suffix) {
                        $still_free = false;
                        break;
                    }
                }
            }
            wp_reset_postdata();
            if( !$still_free)
                break;
        }

        return $still_free;
    }


        /**
         * Get number of posts of the given type in the given volume.
         *
         * If $vol_num is empty, the total number over all volumes is returned.
         *
         * @since 0.1.0
         * @access public
         * @param   int     $vol_num            Volume number to count or all volumes if empty.
         * @param   string  $publication_type   Publication type to count.
         */
    public static function get_count_of_volume( $vol_num, $publication_type ) {

        $query = array(
            'post_type' => $publication_type,
            'post_status' => array('publish'),
            'posts_per_page' => -1,
                       );
        if(!empty($vol_num))
        {
            $query['meta_key'] = $publication_type . '_volume';
            $query['meta_value'] = $vol_num;
        }
        $my_query = new WP_Query( $query );

        return $my_query->post_count;
    }

}
