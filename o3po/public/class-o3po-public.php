<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/public
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-publication-type.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-journal.php';

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-secondary-publication-type.php';


/**
 * The public-facing functionality of the plugin.
 *
 * @package    O3PO
 * @subpackage O3PO/public
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Public {

        /**
         * The ID of this plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
	private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
	private $version;

        /**
         * Initialize the class and set its properties.
         *
         * @since    0.1.0
         * @param    string    $plugin_name    The name of the plugin.
         * @param    string    $version        The version of this plugin.
         */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

        /**
         * Register the stylesheets for the public-facing side of the site.
         *
         * @since    0.1.0
         */
	public function enqueue_styles() {

            /**
             * An instance of this class should be passed to the run() function
             * defined in O3PO_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The O3PO_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */

		wp_enqueue_style( $this->plugin_name . '-public.css', plugin_dir_url( __FILE__ ) . 'css/' . $this->plugin_name . '-public.css', array(), $this->version, 'all' );

        $theme = wp_get_theme();
        if ( $theme->get('Name') === 'OnePress' )
            wp_enqueue_style( $this->plugin_name . '-onepress-extra.css', plugin_dir_url( __FILE__ ) . 'css/' . $this->plugin_name . '-onepress-extra.css', array(), $this->version, 'all' );

	}

        /**
         * Register the JavaScript for the public-facing side of the site.
         *
         * @since    0.1.0
         */
	public function enqueue_scripts() {

            /**
             * An instance of this class should be passed to the run() function
             * defined in O3PO_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The O3PO_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */

//		wp_enqueue_script( $this->plugin_name . '-public.js', plugin_dir_url( __FILE__ ) . 'js/' . $this->plugin_name . '-public.js', array( 'jquery' ), $this->version, false );

	}

        /**
         * Add opne graph (OG) meta tags describing the respective page. This
         * data is used by social networks to generate excerpts for sharing.
         *
         * @since    0.1.0
         * @access   public
         */
    public function add_open_graph_meta_tags_for_social_media() {
        $settings = O3PO_Settings::instance();

        $image_url = $settings->get_plugin_option('social_media_thumbnail_url');
        $title = $settings->get_plugin_option('journal_title');
        $url = get_site_url();
        $description = $settings->get_plugin_option('journal_description');
        $facebook_app_id = $settings->get_plugin_option('facebook_app_id');
        if(is_single())
        {

            $image_url = O3PO_PublicationType::get_social_media_thumbnail_src(get_the_ID());

            $specific_title = get_the_title();
            if(!empty($specific_title))
                $title = $specific_title;

            $specific_url = get_permalink();
            if(!empty($specific_url))
                $url = $specific_url;

            $specific_description = strip_tags(get_the_excerpt());
            if(empty($specific_description))
                $specific_description = strip_tags(wp_trim_words(get_post_field('post_content', get_the_ID())));
            if(!empty($specific_description))
                $description = $specific_description;
        }

        $journal_title = $settings->get_plugin_option('journal_title');

        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:url" content="' . esc_attr($url) . '" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr($journal_title) .'" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";

        if(!empty($image_url))
            echo '<meta property="og:image" content="' .$image_url . '" />' . "\n";

        if(!empty($facebook_app_id))
            echo '<meta property="fb:app_id" content="' .$facebook_app_id . '" />' . "\n";

    }

        /**
         * Enable MathJax on all public pages.
         *
         * @since    0.1.0
         * @access   public
         */
    public function enable_mathjax() {

        $settings = O3PO_Settings::instance();
?>
        <script type="text/x-mathjax-config">
        MathJax.Hub.Config({
              tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']], processEscapes: true}
            });
        </script>
        <script type="text/javascript" async src="<?php echo $settings->get_plugin_option('mathjax_url') ?>?config=TeX-AMS_CHTML"></script>
<?php

    }


        /**
         * Fix some invalid html generated by WordPress as part of the logo.
         *
         * To be added to the 'get_custom_logo' filter.
         *
         * @since    0.1.0
         * @access   public
         */
    public function fix_custom_logo_html() {

        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $html = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home">%2$s</a>',
                         esc_url( home_url( '/' ) ),
                         wp_get_attachment_image( $custom_logo_id, 'full', false,
                                                  array(
                                                      'class'    => 'custom-logo',
                                                        ) )
                         );
        return $html;
    }

        /**
         * Add search based extended navigation to the main page.
         *
         * Add a search interface, some statistics about the number of publications and volumes, and links to the respective volume pand publication type pages to the main page just before the loop starts.
         *
         * To be added to the 'loop_start' action.
         *
         * @since      0.1.0
         * @access     public
         * @param      string    $query      Query that lead to the current loop.
         */
    public function extended_search_and_navigation_at_loop_start( $query ){

        $settings = O3PO_Settings::instance();

        if(is_home() and $query->is_main_query() and !is_admin()) {
                /* To get all post types from the primary and secondary journals we could do the following,
                 * but in the text we are outputting, we are only mentioning the primary and secondary post types
                 * and so it is more honest to take precisely these counts. As of version 0.1.0 these
                 * two counts are identical, but this may change in the future. */
                /* $primary_journal_post_count = 0; */
                /* $secondary_journal_post_count = 0; */
                /* foreach(O3PO_PublicationType::$active_post_type_classes as $class)  */
                /* { */
                /*     if($class::get_journal_title() === $settings->get_plugin_option('journal_title')) */
                /*         $primary_journal_post_count += $class::get_count_of_volume(null); */
                /*     elseif($class::get_journal_title() === $settings->get_plugin_option('secondary_journal_title')) */
                /*         $secondary_journal_post_count += $class::get_count_of_volume(null); */
                /* } */
            $primary_journal_post_count = O3PO_Journal::get_count_of_volume(null, $settings->get_plugin_option('primary_publication_type_name'));
            $secondary_journal_post_count = O3PO_Journal::get_count_of_volume(null, $settings->get_plugin_option('secondary_publication_type_name'));

            echo '<div class="search-results">';
            echo '<div class="hentry">';
            get_search_form();
            echo '<script type="text/javascript">
var search_field = document.getElementsByClassName("search-field");
    var i;
    for (i = 0; i < search_field.length; i++) {
        search_field[i].placeholder=\'Doi, title, author, arXiv id, ...\';
    }
</script>';
            echo $settings->get_plugin_option('journal_title') . ' has published <a href="/' . $settings->get_plugin_option('primary_publication_type_name_plural') . '/">' . $primary_journal_post_count . ' ' . ucfirst($primary_journal_post_count > 1 ? $settings->get_plugin_option('primary_publication_type_name_plural') : $settings->get_plugin_option('primary_publication_type_name')) . '</a> in <a href="/' . $settings->get_plugin_option('volumes_endpoint') . '">' . (getdate()["year"] - ($settings->get_plugin_option('first_volume_year')-1)) . ' Volumes</a>, as well as <a href="/'. $settings->get_plugin_option('secondary_publication_type_name_plural') . '/">' . $secondary_journal_post_count . ' ' . ucfirst($secondary_journal_post_count > 1 ? $settings->get_plugin_option('secondary_publication_type_name_plural') : $settings->get_plugin_option('secondary_publication_type_name') ) . '</a> in ' . $settings->get_plugin_option('secondary_journal_title') . '.';
            echo '</div>';
            echo '</div>';
        }
    }


        /**
         * Add a search form above the search results just before the loop starts.
         *
         * To be added to the 'loop_start' action.
         *
         * @since      0.1.0
         * @access     public
         * @param      string    $query      Query that lead to the current loop.
         */
    public function search_form_at_loop_start_on_search_page( $query ){

        if(is_search() and !is_admin()) {
            echo '<div class="search-results">';
            echo '<div class="hentry">';
            get_search_form();
            echo '</div>';
            echo '</div>';
        }

    }



        /**
         * Add a help text for listing pages showing posts from the secondary journal just before the loop starts.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $query      Query that lead to the current loop.
         */
    public function secondary_journal_help_text( $query )
    {
        $settings = O3PO_Settings::instance();

        $show_help_on_this_page = false;

        if(is_post_type_archive($settings->get_plugin_option('secondary_publication_type_name')))
            $show_help_on_this_page = true;
        else
            foreach(O3PO_SecondaryPublicationType::get_associated_categories() as $category)
            {
                if(is_category($category))
                {
                    $show_help_on_this_page = true;
                    break;
                }
            }
        if($show_help_on_this_page)
        {
            $settings = O3PO_Settings::instance();

            $categories = O3PO_SecondaryPublicationType::get_associated_categories();
            $categorylinks = array();
            foreach($categories as $category){
                $categorylinks[] = '<a href="/category/' . $category . '">' . $category . 's</a>';
            }

            $categories_string = O3PO_Utility::oxford_comma_implode($categorylinks);
            echo '<p>' . $settings->get_plugin_option('secondary_journal_title') . ' publishes ' . $categories_string . ' on research papers and other issues of relevance for <a href="/">' . $settings->get_plugin_option('journal_title') . '</a>.</p>';
        }
    }

}
