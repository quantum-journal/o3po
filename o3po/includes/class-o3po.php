<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO {

        /**
         * The unique identifier of this plugin.
         *
         * @since    0.1.0
         * @access   protected
         * @var      string    $plugin_name    The string used to uniquely identify this plugin.
         */
	protected $plugin_name;

        /**
         * The human readable name of this plugin.
         *
         * @since    0.1.0
         * @access   protected
         * @var      string    $plugin_pretty_name    The human readable name of this plugin.
         */
	protected $plugin_pretty_name;

        /**
         * The current version of the plugin.
         *
         * @since    0.1.0
         * @access   protected
         * @var      string    $version    The current version of the plugin.
         */
	protected $version;

        /**
         * The loader that's responsible for maintaining and registering all hooks that power
         * the plugin.
         *
         * @since    0.1.0
         * @access   protected
         * @var      O3PO_Loader    $loader    Maintains and registers all hooks for the plugin.
         */
	protected $loader;

        /**
         * The environment object from which information about the environment this plugin is running in can be obtained.
         *
         * @since    0.1.0
         * @access   protected
         * @var      O3PO_Environment    $environment    Provides information about the environment the plugin is running in.
         */
	protected $environment;

        /**
         * The primary journal.
         *
         * @since    0.1.0
         * @access   protected
         * @var      O3PO_Journal    $journal    The primary journal.
         */
	protected $journal;

        /**
         * The secondary journal.
         *
         * @since    0.1.0
         * @access   protected
         * @var      O3PO_Journal    $journal_secondary    The secondary journal.
         */
	protected $journal_secondary;

        /**
         * The primary publication type.
         *
         * @since    0.1.0
         * @access   protected
         * @var      O3PO_PrimaryPublicationType    $primary_publication_type    The primary publication type.
         */
	protected $primary_publication_type;

        /**
         * The secondary publication type.
         *
         * @since    0.1.0
         * @access   protected
         * @var      O3PO_SecondaryPublicationType    $secondary_publication_type    The secondary publiction type.
         */
	protected $secondary_publication_type;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    0.1.0
         * @access   public
         * @param    string     $plugin_name          Simplified name of the plugin
         * @param    string     $plugin_pretty_name   Pretty name of the plugin
         * @param    string     $version              Version of the plugin
         */
	public function __construct( $plugin_name, $plugin_pretty_name, $version ) {

		$this->plugin_name = $plugin_name;
        $this->plugin_pretty_name = $plugin_pretty_name;
        $this->version = $version;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

        /**
         * Load the required dependencies for this plugin.
         *
         * Include the following files that make up the plugin:
         *
         * - O3PO_Loader. Orchestrates the hooks of the plugin.
         * - O3PO_i18n. Defines internationalization functionality.
         * - O3PO_Admin. Defines all hooks for the admin area.
         * - O3PO_Public. Defines all hooks for the public side of the site.
         * - O3PO_Settings. Manages the settings of the plugin
         * - O3PO_Environment. Provides information about the environment this plugin is running in
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @since    0.1.0
         * @access   private
         */
	private function load_dependencies() {

            /**
             * The class responsible for orchestrating the actions and filters of the
             * core plugin.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-loader.php';

            /**
             * The class responsible for defining internationalization functionality
             * of the plugin.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-i18n.php';

            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-o3po-admin.php';

            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-o3po-public.php';

            /**
             * The class responsible for managing the settings of the plugin.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

            /**
             * The class providing information about and managing interactions
             * with the environment the plugin is running in.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-environment.php';

            /**
             * The class representing journals; also provids the volume pages.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-journal.php';

            /**
             * The class providing the primary publication type.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-primary-publication-type.php';

            /**
             * The class providing the secondary publication type.
             */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-secondary-publication-type.php';

        $this->loader = new O3PO_Loader();

        $settings = O3PO_Settings::instance();
        $settings->configure($this->plugin_name, $this->plugin_pretty_name, $this->version, 'O3PO_PublicationType::get_active_publication_type_names');

        $this->environment = new O3PO_Environment($settings->get_plugin_option("production_site_url"));

            //construct the journal config from settings
        $journal_config_properties = O3PO_Journal::get_journal_config_properties();
        $journal_config = array();
        foreach(array_intersect(array_keys($settings->get_all_settings_fields_map()), $journal_config_properties) as $journal_config_property){
            $journal_config[$journal_config_property] = $settings->get_plugin_option($journal_config_property);
        }
            //add some properties that are named differently (for a reason) in settings
            /* $journal_config['volumes_endpoint'] = 'volumes'; */
        $journal_config['publication_type_name'] = $settings->get_plugin_option('primary_publication_type_name');
        $journal_config['publication_type_name_plural'] = $settings->get_plugin_option('primary_publication_type_name_plural');

            //create the primary journal
        $this->journal = new O3PO_Journal($journal_config);

            //reconfigure for the secondary journal
        $journal_config['journal_title'] = $settings->get_plugin_option('secondary_journal_title');
        $journal_config['journal_level_doi_suffix'] = $settings->get_plugin_option('secondary_journal_level_doi_suffix');
        $journal_config['eissn'] = $settings->get_plugin_option('secondary_journal_eissn');
        $journal_config['volumes_endpoint'] = 'secondary_volumes';
        $journal_config['publication_type_name'] = $settings->get_plugin_option('secondary_publication_type_name');
        $journal_config['publication_type_name_plural'] = $settings->get_plugin_option('secondary_publication_type_name_plural');

            //create the secondary journal
        $this->journal_secondary = new O3PO_Journal($journal_config);

            //create the publication types for each journal
        $this->primary_publication_type = new O3PO_PrimaryPublicationType($this->journal, $this->environment);
        $this->secondary_publication_type = new O3PO_SecondaryPublicationType($this->primary_publication_type->get_publication_type_name(), $this->primary_publication_type->get_publication_type_name_plural(), $this->journal_secondary, $this->environment);

	}

        /**
         * Define the locale for this plugin for internationalization.
         *
         * Uses the O3PO_i18n class in order to set the domain and to register the hook
         * with WordPress.
         *
         * @since    0.1.0
         * @access   private
         */
	private function set_locale() {

		$plugin_i18n = new O3PO_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

        /**
         * Register all of the hooks related to the admin area functionality
         * of the plugin.
         *
         * @since    0.1.0
         * @access   private
         */
	private function define_admin_hooks() {

		$plugin_admin = new O3PO_Admin( $this->get_plugin_name(), $this->get_version() );
        $settings = O3PO_Settings::instance();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'plugin_action_links_' . $this->get_plugin_name() . '/' . $this->get_plugin_name() . '.php', $plugin_admin, 'add_plugin_action_links' );
        $this->loader->add_action( 'admin_head', $plugin_admin, 'enable_mathjax' );

        $this->loader->add_action( 'admin_menu', $settings, 'add_settings_page_to_menu' );
        $this->loader->add_action( 'admin_init', $settings, 'register_settings' );

        $this->loader->add_action( 'admin_head', $this->environment, 'modify_css_if_in_test_environment' );
        $this->loader->add_action( 'upload_mimes', $this->environment, 'custom_upload_mimes' );

        $this->loader->add_action( 'load-post.php', Null, 'O3PO_PublicationType::init_metabox' );
        $this->loader->add_action( 'load-post-new.php', Null, 'O3PO_PublicationType::init_metabox' );
	}

        /**
         * Register all of the hooks related to the public-facing functionality
         * of the plugin.
         *
         * @since    0.1.0
         * @access   private
         */
	private function define_public_hooks() {

        $settings = O3PO_Settings::instance();
		$plugin_public = new O3PO_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'add_open_graph_meta_tags_for_social_media' );
        $this->loader->add_action( 'wp_head', $plugin_public, 'enable_mathjax' );
        $this->loader->add_action( 'get_custom_logo', $plugin_public, 'fix_custom_logo_html' );
        if($settings->get_plugin_option('custom_search_page')==='checked')
            $this->loader->add_action( 'template_include', $plugin_public, 'install_custom_search_page_template' );
        $this->loader->add_action( 'single_template', $plugin_public, 'primary_publication_type_template' );
        $this->loader->add_action( 'loop_start', $plugin_public, 'extended_search_and_navigation_at_loop_start' );
        $this->loader->add_action( 'loop_start', $plugin_public, 'secondary_journal_help_text' );

        $this->loader->add_action( 'wp_head', $this->environment, 'modify_css_if_in_test_environment' );

        $this->loader->add_action( 'init', $this->journal, 'add_volumes_endpoint' );
        $this->loader->add_action( 'parse_request', $this->journal, 'handle_volumes_endpoint_request' );
        $this->loader->add_filter( 'loop_start', $this->journal, 'volume_navigation_at_loop_start' );
        $this->loader->add_filter( 'loop_end', $this->journal, 'compress_enteies_in_volume_view' );
        $this->loader->add_action('template_include', $this->journal, 'volume_endpoint_template');
        $this->loader->add_action('the_posts', $this->journal, 'add_fake_empty_post_to_volume_overview_page');

        #$this->loader->add_action('get_template_part_template-parts/content', $this->journal, 'foo', 99, 2);

            //add hooks for the primary publication type...
        $this->loader->add_action('pre_get_posts', $this->primary_publication_type, 'add_custom_post_types_to_query' );
        $this->loader->add_action('wp_head', $this->primary_publication_type, 'add_dublin_core_and_highwire_press_meta_tags');
        $this->loader->add_action('wp_head', $this->primary_publication_type, 'the_java_script_single_page');
        $this->loader->add_action('admin_head', $this->primary_publication_type, 'admin_page_extra_css');
        $this->loader->add_filter('request', $this->primary_publication_type, 'add_custom_post_types_to_rss_feed' );
        $this->loader->add_filter('the_author', $this->primary_publication_type, 'the_author_feed', PHP_INT_MAX, 1 );
            //...and those inherited from publicationtype
        $this->loader->add_action('init', $this->primary_publication_type, 'register_as_custom_post_type' );
        $this->loader->add_action( 'init', $this->primary_publication_type, 'add_pdf_endpoint' , 0 );
        $this->loader->add_action( 'parse_request', $this->primary_publication_type, 'handle_pdf_endpoint_request' , 1 );
        $this->loader->add_action( 'init', $this->primary_publication_type, 'add_web_statement_endpoint' , 0 );
        $this->loader->add_action( 'parse_request', $this->primary_publication_type, 'handle_web_statement_endpoint_request' , 1 );
        $this->loader->add_action( 'init', $this->primary_publication_type, 'add_axiv_paper_doi_feed_endpoint' , 0 );
        $this->loader->add_action( 'parse_request', $this->primary_publication_type, 'handle_arxiv_paper_doi_feed_endpoint_request' , 1 );
        $this->loader->add_filter( 'get_the_excerpt', $this->primary_publication_type, 'get_the_excerpt') ;//Use this filter instead of 'the_excerpt' to also affect get_the_excerpt()
        $this->loader->add_filter( 'the_content_feed', $this->primary_publication_type, 'get_feed_content' );
        $this->loader->add_filter( 'the_excerpt_rss', $this->primary_publication_type, 'get_feed_content' );
//        $this->loader->add_filter( 'single_template', $this->primary_publication_type, 'get_custom_post_type_single_template' );
        $this->loader->add_filter( 'transition_post_status', $this->primary_publication_type, 'on_transition_post_status', 10, 3 );

            //add hooks for the secondary publication type...
        $this->loader->add_filter( 'the_author', $this->secondary_publication_type, 'get_the_author', PHP_INT_MAX, 1 );
        $this->loader->add_filter( 'author_link', $this->secondary_publication_type, 'get_the_author_posts_link', PHP_INT_MAX, 1 );
        $this->loader->add_filter( 'the_content', $this->secondary_publication_type, 'get_the_content' );
        $this->loader->add_filter( 'get_the_excerpt', $this->secondary_publication_type, 'get_the_excerpt' );//Use this filter instead of 'the_excerpt' to also affect get_the_excerpt()
            //...and those inherited from publicationtype
        $this->loader->add_action('init', $this->secondary_publication_type, 'register_as_custom_post_type' );
        $this->loader->add_action('pre_get_posts', $this->secondary_publication_type, 'add_custom_post_types_to_query' );
        $this->loader->add_action('wp_head', $this->secondary_publication_type, 'add_dublin_core_and_highwire_press_meta_tags');
        $this->loader->add_action('wp_head', $this->secondary_publication_type, 'the_java_script_single_page');
        $this->loader->add_action('admin_head', $this->secondary_publication_type, 'admin_page_extra_css');
        $this->loader->add_filter('request', $this->secondary_publication_type, 'add_custom_post_types_to_rss_feed' );
        $this->loader->add_filter('the_author', $this->secondary_publication_type, 'the_author_feed', PHP_INT_MAX, 1 );
        $this->loader->add_filter( 'transition_post_status', $this->secondary_publication_type, 'on_transition_post_status', 10, 3 );

	}

        /**
         * Run the loader to execute all of the hooks with WordPress.
         *
         * @since    0.1.0
         */
	public function run() {

		$this->loader->run();
	}

        /**
         * The name of the plugin used to uniquely identify it within the context of
         * WordPress and to define internationalization functionality.
         *
         * @since     0.1.0
         * @return    string    The name of the plugin.
         */
	public function get_plugin_name() {

		return $this->plugin_name;
	}

        /**
         * The reference to the class that orchestrates the hooks with the plugin.
         *
         * @since     0.1.0
         * @return    O3PO_Loader    Orchestrates the hooks of the plugin.
         */
	public function get_loader() {

		return $this->loader;
	}

        /**
         * Retrieve the version number of the plugin.
         *
         * @since     0.1.0
         * @return    string    The version number of the plugin.
         */
	public function get_version() {

		return $this->version;
	}

}
