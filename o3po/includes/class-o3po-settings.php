<?php

/**
 * Manage the settings of the plugin.
 *
 * This class must be static or a singleton because we need to
 * access setting from template files (search.php, single.php)
 * into which we cannot inject a dependency to the settings by
 * passing a instance.
 * This class however also cannot be static if we want the settings
 * group name used in settings_fields() and register_setting() to
 * depend on the plugin slug.
 * Therefore we implement O3PO_Settings as a singleton. It doesn't seem
 * to be such a evil thing to do given that the options are anyway
 * global.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-singleton.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-email-templates.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-buffer.php';


/**
 * Manage the settings of the plugin.
 *
 * Provide methods to set and get plugin options and to create
 * the respetive admin page and menu entry. *
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Settings extends O3PO_Singleton {

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
         * The callback from which to get the active post type names.
         *
         * @since    0.1.0
         * @access   protected
         * @var      mixed     $active_post_type_names_callback    The callback from which to get the active post type names.
         */
	protected $active_post_type_names_callback;

        /**
         * Array of the IDs of all settings sections.
         *
         * @since    0.3.0
         * @access   protected
         * @var      array     $settings_sections   Dictionary of all setting sections and their properties.
         */
	protected $settings_sections = array();

        /**
         * Array of the IDs of all settings field.
         *
         * @since    0.3.0
         * @access   protected
         * @var      array     $settings_fields    Dictionary of all setting fields and their properties.
         */
	protected $settings_fields = array();

        /**
         * The dafaults for various options
         *
         * @since    0.1.0
         * @access   private
         * @var      array    $option_defaults    Array of the defaults for various options.
         */
    private $option_defaults = array(
        'license_name' => 'Creative Commons Attribution 4.0 International (CC BY 4.0)',
        'license_type' => 'CC BY',
        'license_version' => '4.0',
        'license_url' => 'https://creativecommons.org/licenses/by/4.0/',
        'license_explanation' => 'Copyright remains with the original copyright holders such as the authors or their institutions.',
        'mathjax_url' => 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js',
        'crossref_get_forward_links_url' => 'https://doi.crossref.org/servlet/getForwardLinks',
        'crossref_deposite_url' => 'https://doi.crossref.org/servlet/deposit',
        'crossref_test_deposite_url' => 'https://test.crossref.org/servlet/deposit',
        'clockss_ftp_url' => 'ftp.clockss.org',
        'arxiv_doi_feed_identifier' => '',
        'arxiv_paper_doi_feed_endpoint' => 'arxiv_paper_doi_feed',
        'arxiv_paper_doi_feed_days' => '365',
        'arxiv_url_abs_prefix' => 'https://arxiv.org/abs/',
        'arxiv_url_pdf_prefix' => 'https://arxiv.org/pdf/',
        'arxiv_url_source_prefix' => 'https://arxiv.org/e-print/',
        'arxiv_url_trackback_prefix' => 'http://arxiv.org/trackback/',
        'doi_url_prefix' => 'https://doi.org/',
        'scirate_url_abs_prefix' => 'https://scirate.com/arxiv/',
        'arxiv_vanity_url_prefix' => 'https://www.arxiv-vanity.com/papers/',
        'orcid_url_prefix' => 'https://orcid.org/',
        'fermats_library_url_prefix' => 'https://fermatslibrary.com/s/',
        'doaj_api_url' => "https://doaj.org/api/v1/articles",
        'doaj_language_code' => "EN",
        'buffer_api_url' => 'https://api.bufferapp.com/1',
        'extended_search_and_navigation' => "checked",
        'search_form_on_search_page' => "checked",
        'custom_search_page' => "checked",
        'page_template_for_publication_posts' => "unchecked",
        'page_template_abstract_header' => '',
        'trackbacks_from_secondary_directly_into_database' => "unchecked",
        'maintenance_mode' => 'unchecked',
        'doi_suffix_template' => '[journal_level_doi_suffix]-[date]-[page]',

        'self_notification_subject_template' =>
        "A [publication_type_name] has been published/updated by [journal]",
        'self_notification_body_template' =>
        "[journal] has published/updated the following [publication_type_name]\n".
        "Title:   [title] \n".
        "Authors: [authors] \n".
        "URL:     [url]\n".
        "DOI:     [doi_url_prefix][doi]\n",
        'author_notification_subject_template' =>
        "[journal] has published your [publication_type_name]",
        'author_notification_body_template' =>
        "Dear [authors],\n\n".
        "Congratulations! Your [publication_type_name] '[title]' has been published by [journal] and is now available under:\n\n".
        "[post_url]\n\n".
        "Your work has been assigned the following journal reference and DOI\n\n".
        "Journal reference: [journal_reference]\n".
        "DOI:               [doi_url_prefix][doi]\n\n".
        "We kindly ask you to log in on the arXiv under https://arxiv.org/user/login and add this information to the page of your work there. Thank you very much!\n\n".
        "In case you have an ORCID you can go to http://search.crossref.org/?q=[doi] to conveniently add your new publication to your profile.\n\n".
        "Please be patient, it can take several hours until the DOI has been activated by Crossref.\n\n".
        "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under [publisher_email]\n\n".
        "Best regards,\n\n".
        "[executive_board]\n".
        "Executive Board\n",
        'author_notification_secondary_subject_template' =>
        "[journal] has published your [publication_type_name]",
        'author_notification_secondary_body_template' =>
        "Dear [authors],\n\n".
        "Congratulations! Your [publication_type_name] '[title]' has been published by [journal] and is now available under:\n\n".
        "[post_url]\n\n".
        "Your [publication_type_name] has been assigned the following journal reference and DOI\n\n".
        "Journal reference: [journal_reference]\n".
        "DOI:               [doi_url_prefix][doi]\n\n".
        "In case you have an ORCID you can go to http://search.crossref.org/?q=[doi] to conveniently add your new publication to your profile.\n\n".
        "Please be patient, it can take several hours before the above link works.\n\n".
        "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under [publisher_email]\n\n".
        "Thank you for writing this [publication_type_name] for [journal]!\n\n".
        "Best regards,\n\n".
        "[executive_board]\n".
        "Executive Board\n",
        'fermats_library_notification_subject_template' =>
        "[journal] has a new [publication_type_name] for Fermat's library",
        'fermats_library_notification_body_template' =>
        "Dear team at Fermat's library,\n\n".
        "[journal] has published the following [publication_type_name]:\n\n".
        "Title:     [title]\n".
        "Author(s): [authors]\n".
        "URL:       [post_url]\n".
        "DOI:       [doi_url_prefix][doi]\n".
        "\n".
        "Please post it on Fermat's library under the permalink: [fermats_library_permalink]\n".
        "Thank you very much!\n\n".
        "Kind regards,\n\n".
        "The Executive Board\n",

        'executive_board' => "",
        'editor_in_chief' => "",
        'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
        'ads_api_token' => '',
        'relevanssi_mime_types_to_exclude' => '#(application/.*(tar|gz|gzip)|text/.*tex)#u',
        'relevanssi_index_pdfs_asynchronously' => "checked",

            /* The options below are currently not customizable.
             *
             * Warning: The name of the paper-single.php templare must match
             * the primary_publication_type_name!
             */
        'cited_by_refresh_seconds' => '43200',#=60*60*12
        'primary_publication_type_name' => 'paper',
        'primary_publication_type_name_plural' => 'papers',
        'secondary_publication_type_name' => 'view',
        'secondary_publication_type_name_plural' => 'views',
        'volumes_endpoint' => 'volumes',
                                     );

        /**
         * Configure the settings singleton.
         *
         * @since    0.1.0
         * @param    string    $plugin_name                      Simple name of this plugin.
         * @param    string    $plugin_pretty_name               Pretty name of this plugin.
         * @param    string    $version                          Version of this plugin.
         * @param    callback  $active_post_type_names_callback  The callback from which to get the active post type names.
         */
	public function configure( $plugin_name, $plugin_pretty_name, $version, $active_post_type_names_callback ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_pretty_name = $plugin_pretty_name;
        $this->active_post_type_names_callback = $active_post_type_names_callback;

	}

        /**
         * Add the settings page to the admin menu.
         *
         * To be added to the 'admin_menu' action.
         *
         * @since    0.1.0
         * @access   public
         */
    public function add_settings_page_to_menu() {

        add_options_page($this->plugin_pretty_name . ' settings page', $this->plugin_pretty_name, 'manage_options', $this->plugin_name . '-settings', array( $this, 'render_settings_page' ));

    }

        /**
         * Render the settings page.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_settings_page() {

            /*
             * Flush the rewrite rules here because only during the rendering
             * of the settings page can we be sure that all post types and
             * endpoints have been registered.
             */
        if(get_transient($this->plugin_name . '-settings-rewrite-rules-affected'))
        {
            flush_rewrite_rules(true);
            delete_transient($this->plugin_name . '-settings-rewrite-rules-affected');
        }

        echo '<div>';
        echo '<h2>' . $this->plugin_pretty_name . ' settings (version ' . $this->version . ')</h2>';

        if(isset( $_GET['tab'] ))
            $active_setting_section = $_GET['tab'];
        else
        {
            reset($this->settings_sections);
            $active_setting_section = key($this->settings_sections);
        }

        echo '<h2 class="nav-tab-wrapper">' . "\n";
        foreach($this->settings_sections as $section_id => $section_options)
            echo '<a href="' . esc_url('?page=' . $this->plugin_name . '-settings' . '&amp;tab=' . $section_id) . '" class="nav-tab' . ($active_setting_section == $section_id ? ' nav-tab-active' : '') . '">' . esc_html($section_options['title']) . '</a>' . "\n";
        echo '</h2>' . "\n";

        echo '<form action="options.php" method="post">';

        settings_fields($this->plugin_name . '-settings'); # Output nonce, action, and option_page fields for a settings page.

        do_settings_sections($this->plugin_name . '-settings:' . $active_setting_section);

        echo '<input name="Submit" type="submit" value="Save Settings" />';
        echo '</form></div>';

    }

        /**
         * Register all the settings.
         *
         * To be added to the 'admin_init' action.
         *
         * @since    0.1.0
         * @access   public
         */
    public function register_settings() {

        register_setting( $this->plugin_name . '-settings', $this->plugin_name . '-settings', array( $this, 'validate_settings' ) );

        $this->add_settings_section('plugin_settings', 'Plugin', array( $this, 'render_plugin_settings' ), $this->plugin_name . '-settings:plugin_settings');
        $this->add_settings_field('production_site_url', 'Production site url', array( $this, 'render_production_site_url_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('extended_search_and_navigation', 'Add search and navigation to home page', array( $this, 'render_extended_search_and_navigation_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('search_form_on_search_page', 'Add a search form to the search page', array( $this, 'render_search_form_on_search_page_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('custom_search_page', 'Display search page notice', array( $this, 'render_custom_search_page_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('page_template_for_publication_posts', 'Force page template', array( $this, 'render_page_template_for_publication_posts_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('page_template_abstract_header', 'Show a heading for the abstract', array( $this, 'render_page_template_abstract_header_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('trackbacks_from_secondary_directly_into_database', 'Write Trackbacks directly', array( $this, 'render_trackbacks_from_secondary_directly_into_database_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('cited_by_refresh_seconds', 'Refresh cited-by time', array( $this, 'render_cited_by_refresh_seconds_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');
        $this->add_settings_field('maintenance_mode', 'Maintenance mode', array( $this, 'render_maintenance_mode_setting' ), $this->plugin_name . '-settings:plugin_settings', 'plugin_settings');

        $this->add_settings_section('journal_settings', 'Journal', array( $this, 'render_journal_settings' ), $this->plugin_name . '-settings:journal_settings');
        $this->add_settings_field('doi_prefix', 'DOI prefix', array( $this, 'render_doi_prefix_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('journal_title', 'Journal title', array( $this, 'render_journal_title_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('journal_subtitle', 'Journal subtitle', array( $this, 'render_journal_subtitle_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('journal_description', 'Journal description', array( $this, 'render_journal_description_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('journal_level_doi_suffix', 'Journal level DOI suffix', array( $this, 'render_journal_level_doi_suffix_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('journal_doi_template', 'DOI suffix template', array( $this, 'render_doi_suffix_template_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('eissn', 'eISSN', array( $this, 'render_eissn_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('publisher', 'Publisher', array( $this, 'render_publisher_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('secondary_journal_title', 'Secondary journal title', array( $this, 'render_secondary_journal_title_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('secondary_journal_level_doi_suffix', 'Secondary journal level DOI suffix', array( $this, 'render_secondary_journal_level_doi_suffix_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('secondary_journal_eissn', 'Secondary journal eISSN', array( $this, 'render_secondary_journal_eissn_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('developer_email', 'Email of developer', array( $this, 'render_developer_email_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('publisher_email', 'Email of publisher', array( $this, 'render_publisher_email_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('first_volume_year', 'Year of first volume', array( $this, 'render_first_volume_year_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('publisher_country', 'Country of publisher', array( $this, 'render_publisher_country_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('license_name', 'License name', array( $this, 'render_license_name_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('license_type', 'License type', array( $this, 'render_license_type_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('license_version', 'License version', array( $this, 'render_license_version_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('license_url', 'License url', array( $this, 'render_license_url_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');
        $this->add_settings_field('license_explanation', 'License explanation string', array( $this, 'render_license_explanation_setting' ), $this->plugin_name . '-settings:journal_settings', 'journal_settings');

        $this->add_settings_section('email_settings', 'Email', array($this , 'render_email_settings'), $this->plugin_name . '-settings:email_settings');
        $this->add_settings_field('executive_board' , 'The names of the executive board members' , array($this, 'render_executive_board') , $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('editor_in_chief' , 'The name of the editor in chief' , array($this, 'render_editor_in_chief') , $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('self_notification_subject_template', 'Self notification subject template', array($this, 'render_self_notification_subject_template_settings'),$this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('self_notification_body_template', 'Self notification body template', array($this, 'render_self_notification_body_template_settings'), $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('author_notification_subject_template', 'Author notification subject template', array($this, 'render_author_notification_subject_template_settings'), $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('author_notification_body_template' , 'Author notification body template' , array($this, 'render_author_notification_body_template_settings') , $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('author_notification_secondary_subject_template' , 'Author notification subject template for the secondary journal' , array($this, 'render_author_notification_secondary_subject_template_settings') , $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('author_notification_secondary_body_template' , 'Author notification body template for the secondary journal' , array($this, 'render_author_notification_secondary_body_template_settings') , $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('fermats_library_subject_template' , 'Fermats library subject template' , array($this, 'render_fermats_library_subject_template_settings') , $this->plugin_name . '-settings:email_settings', 'email_settings');
        $this->add_settings_field('fermats_library_body_template' , 'Fermats library body template' , array($this, 'render_fermats_library_body_template_settings') , $this->plugin_name . '-settings:email_settings', 'email_settings');

        $this->add_settings_section('crossref_settings', 'Crossref', array( $this, 'render_crossref_settings' ), $this->plugin_name . '-settings:crossref_settings');
        $this->add_settings_field('crossref_id', 'Crossref ID', array( $this, 'render_crossref_id_setting' ), $this->plugin_name . '-settings:crossref_settings', 'crossref_settings');
        $this->add_settings_field('crossref_pw', 'Crossref password', array( $this, 'render_crossref_pw_setting' ), $this->plugin_name . '-settings:crossref_settings', 'crossref_settings');
        $this->add_settings_field('crossref_get_forward_links_url', 'Crossref get forward links url', array( $this, 'render_crossref_get_forward_links_url_setting' ), $this->plugin_name . '-settings:crossref_settings', 'crossref_settings');
        $this->add_settings_field('crossref_deposite_url', 'Crossref deposite url', array( $this, 'render_crossref_deposite_url_setting' ), $this->plugin_name . '-settings:crossref_settings', 'crossref_settings');
        $this->add_settings_field('crossref_test_deposite_url', 'Crossref deposite url for testing', array( $this, 'render_crossref_test_deposite_url_setting' ), $this->plugin_name . '-settings:crossref_settings', 'crossref_settings');
        $this->add_settings_field('crossref_email', 'Email for communication with Crossref', array( $this, 'render_crossref_email_setting' ), $this->plugin_name . '-settings:crossref_settings', 'crossref_settings');
        $this->add_settings_field('crossref_archive_locations', 'Archive locations', array( $this, 'render_crossref_archive_locations_setting' ), $this->plugin_name . '-settings:crossref_settings', 'crossref_settings');

        $this->add_settings_section('ads_settings', 'ADS', array( $this, 'render_ads_settings' ), $this->plugin_name . '-settings:ads_settings');
        $this->add_settings_field('ads_api_search_url', 'ADS API URL', array( $this, 'render_ads_api_search_url_setting' ), $this->plugin_name . '-settings:ads_settings', 'ads_settings');
        $this->add_settings_field('ads_api_token', 'ADS API token', array( $this, 'render_ads_api_token_setting' ), $this->plugin_name . '-settings:ads_settings', 'ads_settings');

        $this->add_settings_section('clockss_settings', 'Clockss', array( $this, 'render_clockss_settings' ), $this->plugin_name . '-settings:clockss_settings');
        $this->add_settings_field('clockss_ftp_url', 'Clockss FTP URL', array( $this, 'render_clockss_ftp_url_setting' ), $this->plugin_name . '-settings:clockss_settings', 'clockss_settings');
        $this->add_settings_field('clockss_username', 'Clockss Username', array( $this, 'render_clockss_username_setting' ), $this->plugin_name . '-settings:clockss_settings', 'clockss_settings');
        $this->add_settings_field('clockss_password', 'Clockss Password', array( $this, 'render_clockss_password_setting' ), $this->plugin_name . '-settings:clockss_settings', 'clockss_settings');

        $this->add_settings_section('doaj_settings', 'DOAJ', array( $this, 'render_doaj_settings' ), $this->plugin_name . '-settings:doaj_settings');
        $this->add_settings_field('doaj_api_url', 'DOAJ API url', array( $this, 'render_doaj_api_url_setting' ), $this->plugin_name . '-settings:doaj_settings', 'doaj_settings');
        $this->add_settings_field('doaj_api_key', 'DOAJ API key', array( $this, 'render_doaj_api_key_setting' ), $this->plugin_name . '-settings:doaj_settings', 'doaj_settings');
        $this->add_settings_field('doaj_language_code', 'DOAJ langugage code (two upper case letters)', array( $this, 'render_doaj_language_code_setting' ), $this->plugin_name . '-settings:doaj_settings', 'doaj_settings');

        $this->add_settings_section('arxiv_settings', 'ArXiv', array( $this, 'render_arxiv_settings' ), $this->plugin_name . '-settings:arxiv_settings');
        $this->add_settings_field('arxiv_url_abs_prefix', 'Url prefix for abstract pages', array( $this, 'render_arxiv_url_abs_prefix_setting' ), $this->plugin_name . '-settings:arxiv_settings', 'arxiv_settings');
        $this->add_settings_field('arxiv_url_pdf_prefix', 'Url prefix for pdfs', array( $this, 'render_arxiv_url_pdf_prefix_setting' ), $this->plugin_name . '-settings:arxiv_settings', 'arxiv_settings');
        $this->add_settings_field('arxiv_url_source_prefix', 'Url prefix for eprint source', array( $this, 'render_arxiv_url_source_prefix_setting' ), $this->plugin_name . '-settings:arxiv_settings', 'arxiv_settings');
        $this->add_settings_field('arxiv_url_trackback_prefix', 'Url prefix for Trackbacks', array( $this, 'render_arxiv_url_trackback_prefix_setting' ), $this->plugin_name . '-settings:arxiv_settings', 'arxiv_settings');
        $this->add_settings_field('arxiv_doi_feed_identifier', 'Indentifier for the DOI feed', array( $this, 'render_arxiv_doi_feed_identifier_setting' ), $this->plugin_name . '-settings:arxiv_settings', 'arxiv_settings');
        $this->add_settings_field('arxiv_paper_doi_feed_endpoint', 'Endpoint for the arXiv DOI feed', array( $this, 'render_arxiv_paper_doi_feed_endpoint_setting' ), $this->plugin_name . '-settings:arxiv_settings', 'arxiv_settings');
        $this->add_settings_field('arxiv_paper_doi_feed_days', 'Number of days in arXiv DOI feed', array( $this, 'render_arxiv_paper_doi_feed_days_setting' ), $this->plugin_name . '-settings:arxiv_settings', 'arxiv_settings');

        $this->add_settings_section('buffer_settings', 'Buffer.com', array( $this, 'render_buffer_settings' ), $this->plugin_name . '-settings:buffer_settings');
        $this->add_settings_field('buffer_api_url', 'Url of the buffer.com api', array( $this, 'render_buffer_api_url_setting' ), $this->plugin_name . '-settings:buffer_settings', 'buffer_settings');
        $this->add_settings_field('buffer_access_token', 'Access token from buffer.com', array( $this, 'render_buffer_access_token_setting' ), $this->plugin_name . '-settings:buffer_settings', 'buffer_settings');
        $this->add_settings_field('buffer_profile_ids', 'Profile IDs on buffer.com', array( $this, 'render_buffer_profile_ids_setting' ), $this->plugin_name . '-settings:buffer_settings', 'buffer_settings');

        $this->add_settings_section('other_service_settings', 'Other services', array( $this, 'render_other_service_settings' ), $this->plugin_name . '-settings:other_service_settings');
        $this->add_settings_field('doi_url_prefix', 'Url prefix for DOI resolution', array( $this, 'render_doi_url_prefix_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('scirate_url_abs_prefix', 'Url prefix for scirate pages', array( $this, 'render_scirate_url_abs_prefix_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('arxiv_vanity_url_prefix', 'Url prefix for arXiv Vanity pages', array( $this, 'render_arxiv_vanity_url_prefix_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('scholastica_manuscripts_url', 'Url of Scholastica manuscripts page', array( $this, 'render_scholastica_manuscripts_url_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('orcid_url_prefix', 'Orcid url prefix', array( $this, 'render_orcid_url_prefix_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('fermats_library_url_prefix', 'Url prefix for Fermats Library', array( $this, 'render_fermats_library_url_prefix_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('fermats_library_email', 'Email for Fermats Library', array( $this, 'render_fermats_library_email_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('mathjax_url', 'MathJax url', array( $this, 'render_mathjax_url_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('social_media_thumbnail_url', 'Url of default thumbnail for social media', array( $this, 'render_social_media_thumbnail_url_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');
        $this->add_settings_field('facebook_app_id', 'Facebook app_id', array( $this, 'render_facebook_app_id_setting' ), $this->plugin_name . '-settings:other_service_settings', 'other_service_settings');

        $this->add_settings_section('other_plugins_settings', 'Other plugins', array( $this, 'render_other_plugins_settings' ), $this->plugin_name . '-settings:other_plugins_settings');
        $this->add_settings_field('relevanssi_mime_types_to_exclude', 'Relevanssi mime types to exclude', array( $this, 'render_relevanssi_mime_types_to_exclude_setting' ), $this->plugin_name . '-settings:other_plugins_settings', 'other_plugins_settings');
        $this->add_settings_field('relevanssi_index_pdfs_asynchronously', 'Index PDFs asynchronously', array( $this, 'render_relevanssi_index_pdfs_asynchronously_setting' ), $this->plugin_name . '-settings:other_plugins_settings', 'other_plugins_settings');

    }

        /**
         * Render the head of the plugin settings page.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_plugin_settings() {

        echo '<p>Configure the general behavior of ' . $this->plugin_name . '.</p>';

    }

        /**
         * Render the head of the plugin settings page.
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_email_settings() {

        echo '<p>Configure the templates used for sending emails.</p>';

    }

        /**
         * Render the head of the journal settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_settings() {

        echo '<p>Configure your journal(s).</p>';

    }

        /**
         * Render the head of the crossref settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with <a href="https://www.crossref.org/">Crossref</a>.</p>';

    }

        /**
         * Render the head of the ads settings part.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_ads_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with <a href="https://github.com/adsabs/adsabs-dev-api">ADS</a>.</p>';

    }

        /**
         * Render the head of the clockss settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with <a href="https://clockss.org/">CLOCKSS</a>.</p>';

    }

        /**
         * Render the head of the DOAJ settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with the <a href="https://doaj.org/">DOAJ</a>.</p>';

    }

        /**
         * Render the head of the arXiv settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with the <a href="https://arxiv.org/">arXiv</a>.</p>';

    }

        /**
         * Render the head of the Buffer.com settings part.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_buffer_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with <a href="https://arxiv.org/">Buffer.com</a>.</p>';

    }

        /**
         * Render the head of the other services settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_other_service_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with other external services.</p>';

    }

        /**
         * Render the head of the other plugins settings part.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_other_plugins_settings() {

        echo '<p>Configure how ' . $this->plugin_name . ' interacts with other plugins.</p>';

    }

        /**
         * Render the setting for the production site url.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_production_site_url_setting() {

        $this->render_setting('production_site_url');
        echo '<p>(Unless this field is filled and matches the string ' . esc_html(get_site_url())  . ', this instance will be considered a test system and the interfaces with various critical services will remain disabled. This ensures that even a full backup of your journal website, when hosted under a different domain and used as, e.g., a staging system, will not accidentally register DOIs or interact with external services in an unintended way.)</p>';

    }

        /**
         * Render the setting for whether to show the extended search-based navigation on the home page.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_extended_search_and_navigation_setting() {

        $this->render_checkbox_setting('extended_search_and_navigation', 'Add  a search-base navigation with statistics about the number of publications and volumes above the posts on the home page. Has no effect if the home page is set to a static page without a post list.');

    }

        /**
         * Render the setting for whether to show a search form above the results on the search page.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_search_form_on_search_page_setting() {

        $this->render_checkbox_setting('search_form_on_search_page', 'Add a search form above the results on the search page.');

    }

        /**
         * Render the setting for whether to show the custom search page.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_custom_search_page_setting() {

        $this->render_checkbox_setting('custom_search_page', 'Uncheck to disable the display of a notice on the search page informing users what it can mean if they are unable to find a paper on this website, but whose version on the arXiv claims that it was published in this journal. You can preview the two versions of this message <a href="/?s=thissearchstringyieldsnoresults">here</a> and <a href="/?s=thissearchstringyieldsnoresults&amp;reason=title-click">here</a>. Notice how a search that includes the reason=title-click query variable can be used to implement a way for readers to check the validity of claims of publication in, e.g., the LaTeX template of your journal.');

    }

        /**
         * Render the setting for the abstract heading
         *
         * @since    0.3.1
         * @access   public
         */
    public function render_page_template_abstract_header_setting() {

        $this->render_setting('page_template_abstract_header');
        echo '<p>An optional header that is displayed before the abstract on the pages of individual publications.</p>';

    }

        /**
         * Render the setting for whether to write trackbacks directly.
         *
         * @since    0.3.1
         * @access   public
         */
    public function render_trackbacks_from_secondary_directly_into_database_setting() {

        $this->render_checkbox_setting('trackbacks_from_secondary_directly_into_database', 'Publication posts in the secondary journal targeting posts published in the primary journal send <a href="https://en.support.wordpress.com/comments/trackbacks/">Trackbacks</a> to the targeted post(s). Unfortunately, sending such Trackbacks does not work reliably on many WordPress instances. You can check this box to instead have ' . $this->plugin_pretty_name . ' write the such Trackbacks directly into the local database via wp_new_comment() instead of using trackback().' );

    }

        /**
         * Render the setting for whether to use the page template for publications.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_page_template_for_publication_posts_setting() {

        $this->render_checkbox_setting('page_template_for_publication_posts', 'Show publication posts with the page template instead of the default post template of your theme. Some themes include information such as "Posted on ... by ..." on the post template, which may be inappropriate for publication posts.');

    }

        /**
         * Render the cited by refresh seconds field
         *
         * @sinde 0.3.0
         * @access public
         */
    public function render_cited_by_refresh_seconds_setting() {

        $this->render_setting('cited_by_refresh_seconds');
        echo '<p>(Refresh the cited by data on publication pages at most every this many seconds. This affects how cited-by data is retrieved from Crossref and ADS.)</p>';

    }


        /**
         * Render the setting to enabled maintenance mode.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_maintenance_mode_setting() {

        $this->render_checkbox_setting('maintenance_mode', 'Enable maintenance mode.');
        $post_types = O3PO_Utility::oxford_comma_implode(call_user_func($this->active_post_type_names_callback));
        echo('<p>(In maintenance mode, modifying any of the meta-data of ' . $post_types . ' posts and publishing new such posts is inhibited and a warning message is shown on the edit post screen in the admin area. All public facing aspects of the website will continue to operate normally.)</p>');

    }

        /**
         * Render the setting for the DOI prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doi_prefix_setting() {

        $this->render_setting('doi_prefix');
        echo('<p>(The DOI prefix assigned to your publisher by your DOI registry.)</p>');

    }

        /**
         * Render the setting for the journal title of the primary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_title_setting() {

        $this->render_setting('journal_title');
        echo('<p>(The title of your journal.)</p>');

    }

        /**
         * Render the setting for the subtitle of the primary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_subtitle_setting() {

        $this->render_setting('journal_subtitle');
        echo('<p>(The subtitle of your journal. Currently not used.)</p>');

    }

        /**
         * Render the setting for the journal description.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_description_setting() {

        $this->render_setting('journal_description');
        echo('<p>(A short description of your journal for use in open graph meta-tags.)</p>');

    }

        /**
         * Render the setting for the journal level DOI suffix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_level_doi_suffix_setting() {

        $this->render_setting('journal_level_doi_suffix');
        echo('<p>(This is used as both the journal level DOI suffix and to generate the DOIs of your publications via the scheme [doi_prefix]/doi_suffix_template. See journal settings DOI suffix template for more information on how the template is substituted.');

    }

        /**
         * Render the setting for the DOI suffix template.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doi_suffix_template_setting() {

        $this->render_setting('doi_suffix_template');
        echo('<p>The DOI suffix template is used to specify the DOI suffix. The following shortcodes are available: <ul>'
                  .'<li><emph>[journal_level_doi_suffix]</emph>: The journal level DOI suffix</li>'
                  .'<li><emph>[volume]</emph>: The volume in which the article appears</li>'
                  .'<li><emph>[page]</emph>: An article number that counts up starting at 1 </li>'
                  .'<li><emph>[date]</emph>: The <a href="https://en.wikipedia.org/wiki/ISO_8601">ISO_8601</a> formated publication date </emph></li></ul><br>'
              .'See the <a href="https://support.crossref.org/hc/en-us/articles/214569903-Journal-level-DOIs">Crossref website</a> for more background.'
              .'</p>');
    }

        /**
         * Render the setting for the EISSN.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_eissn_setting() {

        $this->render_setting('eissn');
        echo('<p>(The eISSN of your journal.)</p>');

    }

        /**
         * Render the setting for the publisher name.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_setting() {

        $this->render_setting('publisher');
        echo('<p>(The name of your publisher.)</p>');

    }

        /**
         * Render the setting for the title of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_title_setting() {

        $this->render_setting('secondary_journal_title');
        echo('<p>(' . $this->get_plugin_pretty_name() . ' allows you to run a secondary journal for editorials and other secondary literature. Set its title here.)</p>');

    }

        /**
         * Render the setting for the journal level DOI suffix of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_level_doi_suffix_setting() {

        $this->render_setting('secondary_journal_level_doi_suffix');
        echo('<p>(This is used as both the journal level DOI suffix of the secondary journal and to generate the DOIs of the publications in the secondary journal via the scheme [doi_prefix]/[secondary_journal_level_doi_suffix]-[date]-[page], where [date] is the <a href="https://en.wikipedia.org/wiki/ISO_8601">ISO_8601</a> formated publication date and [page] is an article number that counts up starting at 1.  See the <a href="https://support.crossref.org/hc/en-us/articles/214569903-Journal-level-DOIs">Crossref website</a> for more background.)</p>');

    }

        /**
         * Render the setting for the EISSN of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_eissn_setting() {

        $this->render_setting('secondary_journal_eissn');
        echo '<p>(The eISSN of your secondary journal. Do not set this equal to the eISSN of your primary journal. If you do, they may be treated as a single journal for the purpose of biometrics. It is OK to leave this blank, but DOAJ, for example will not accept meta-date on articles in the secondary journal if this is not set.)</p>';

    }

        /**
         * Render the setting for the email of the developer.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_developer_email_setting() {

        $this->render_setting('developer_email');
        echo('<p>(Debug and other notification emails are sent to this address. Is used as the primary email address on test systems.)</p>');

    }

        /**
         * Render the setting for the email of the publisher.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_email_setting() {

        $this->render_setting('publisher_email');
        echo('<p>(Email address of the publisher. This is used as the from address for emails sent by ' . $this->get_plugin_pretty_name() . '. Must be an address that is valid on the SMTP server used by this Wordpress instance.)</p>');

    }

        /**
         * Render the setting for the year of the first volume.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_first_volume_year_setting() {

        $this->render_setting('first_volume_year');
        echo('<p>(Four digit year in which the first volume was published. Is used to automatically set the volume number of newly published publications and, e.g., when generating the <a href="/volume/">volume overview page</a>.)</p>');

    }

        /**
         * Render the executive board of the email settings
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_executive_board() {

        $this->render_setting('executive_board');
        echo('<p>(Names of the executive board of your journal. Set this if you want to use the [executive_board] shortcode in the email templates below.)</p>');

    }

        /**
         * Render the editor in chief of the email settings
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_editor_in_chief() {

        $this->render_setting('editor_in_chief');
        echo('<p>(Name of the editor in chief. Set this if you want to use the [editor_in_chief] shortcode in the email templates below.)</p>');

    }


        /**
         * Render the email template for the self notification subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_self_notification_subject_template_settings() {

        $this->render_setting('self_notification_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('self_notification_subject');

    }

        /**
         * Render the email template for the self notification body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_self_notification_body_template_settings() {

        $this->render_multi_line_setting('self_notification_body_template');
        echo O3PO_EmailTemplates::render_short_codes('self_notification_body');

    }

        /**
         * Render the email template for the author notification subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_subject_template_settings() {

        $this->render_setting('author_notification_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_subject');

    }

        /**
         * Render the email template for the author notification body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_body_template_settings() {

        $this->render_multi_line_setting('author_notification_body_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_body');

    }

        /**
         * Render the email template for the author notification secondary subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_secondary_subject_template_settings() {

        $this->render_multi_line_setting('author_notification_secondary_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_subject');

    }

        /**
         * Render the email template for the author notification secondary body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_secondary_body_template_settings() {

        $this->render_multi_line_setting('author_notification_secondary_body_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_body');

    }

        /**
         * Render the email template for the fermats library notification subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_fermats_library_subject_template_settings() {

        $this->render_setting('fermats_library_notification_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('fermats_library_notification_subject');

    }

        /**
         * Render the email template for the fermats library notification body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_fermats_library_body_template_settings() {

        $this->render_multi_line_setting('fermats_library_notification_body_template');
        echo O3PO_EmailTemplates::render_short_codes('fermats_library_notification_body');

    }

        /**
         * Render the setting for the licence name.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_name_setting() {

        $this->render_setting('license_name');
        echo '<p>(The human readable name of the license under which you publish. For example: Creative Commons Attribution 4.0 International (CC BY 4.0))</p>';

    }

        /**
         * Render the setting for the license type.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_type_setting() {

        $this->render_setting('license_type');
        echo '<p>(The type of license under which you publish. For example: CC BY)</p>';

    }

        /**
         * Render the setting for the license version.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_version_setting() {

        $this->render_setting('license_version');
        echo '<p>(The version of the license under which you publish. For example: 4.0)</p>';

    }

        /**
         * Render the setting for the license URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_url_setting() {

        $this->render_setting('license_url');
        echo '<p>(The url under which the license can be found. For example : https://creativecommons.org/licenses/by/4.0/)</p>';

    }

        /**
         * Render the setting for the text appearing in the license statement.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_explanation_setting() {

        $this->render_setting('license_explanation');
        echo '<p>(This will be displayed at the end of the license statement on the individual pages of your publications.)</p>';

    }

        /**
         * Render the setting for the country of the publisher.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_country_setting() {

        $this->render_setting('publisher_country');
        echo '<p>(The country in which your publisher is registered.)</p>';

    }

        /**
         * Render the setting for the CorssRef id.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_id_setting() {

        $this->render_setting('crossref_id');
        echo '<p>(Your Crossref ID.)</p>';

    }

        /**
         * Render the setting for the CorssRef password.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_pw_setting() {

        $this->render_password_setting('crossref_pw');
        echo '<p>(Your Crossref password.)</p>';

    }

        /**
         * Render the setting for the url to query to retrieve citing articles.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_get_forward_links_url_setting() {

        $this->render_setting('crossref_get_forward_links_url');
        echo '<p>(The url from which <a href="https://www.crossref.org/services/cited-by/">Crossref cited-by data</a> can be obtained.)</p>';

    }

        /**
         * Render the setting for the CrossRef deposit URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_deposite_url_setting() {

        $this->render_setting('crossref_deposite_url');
        echo '<p>(The url to use when depositing meta-data with Crossref.)</p>';

    }

        /**
         * Render the setting for the URl of the CrossRef deposit test system.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_test_deposite_url_setting() {

        $this->render_setting('crossref_test_deposite_url');
        echo '<p>(This url is used in place of the real Crossref deposit url when registering dois if ' . $this->get_plugin_pretty_name() . ' is in test system mode. It should be the deposit url of Crossref\'s test system.)</p>';

    }

        /**
         * Render the setting for the email to submit to crossref.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_email_setting() {

        $this->render_setting('crossref_email');
        echo '<p>(The email address to use as the depositor\'s email when registering DOIs.)</p>';

    }

        /**
         * Render the setting for the archives the primary journal is listed in for submitting meta-data to CrossRef.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_archive_locations_setting() {

        $this->render_setting('crossref_archive_locations');
        echo '<p>(Comma separated list containing a subset of CLOCKSS, LOCKSS Portico, KB, DWT, Internet Archive, depending on which archives the primary journal\'s content is archived in.)</p>';

    }

        /**
         * Render the setting for the ads api search URL.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_ads_api_search_url_setting() {

        $this->render_setting('ads_api_search_url');
        echo '<p>(The url of the ADS API from which cited-by information can be retrieved.)</p>';

    }

        /**
         * Render the setting for the ads api token.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_ads_api_token_setting() {

        $this->render_password_setting('ads_api_token');
        echo '<p>(Your ADS API token.)</p>';

    }

        /**
         * Render the setting for the CLOCKSS ftp url.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_ftp_url_setting() {

        $this->render_setting('clockss_ftp_url');
        echo '<p>(The CLOCKSS FTP server to use. Please enter the raw url without the leading ftp:// protocol specification.)</p>';

    }

        /**
         * Render the setting for the CLOCKSS username.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_username_setting() {

        $this->render_setting('clockss_username');
        echo '<p>(Your CLOCKSS user name.)</p>';

    }

        /**
         * Render the setting for the CLOCKSS password.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_password_setting() {

        $this->render_password_setting('clockss_password');
        echo '<p>(Your CLOCKSS password.)</p>';

    }


        /**
         * Render the setting for the URL of the DOAJ API.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_api_url_setting() {

        $this->render_setting('doaj_api_url');
        echo '<p>(The url of the DOAJ API.)</p>';

    }

        /**
         * Render the setting for the DOAJ api key.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_api_key_setting() {

        $this->render_password_setting('doaj_api_key');
        echo '<p>(Your DOAJ API password.)</p>';

    }

        /**
         * Render the setting for the language code for DOAJ.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_language_code_setting() {

        $this->render_setting('doaj_language_code');
        echo '<p>(The language code corresponding to the language in which you publish.)</p>';

    }

        /**
         * Render the setting for the arXiv abstract URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_abs_prefix_setting() {

        $this->render_setting('arxiv_url_abs_prefix');
        echo '<p>(The url prefix of arXiv abstract pages.)</p>';

    }

        /**
         * Render the setting for the arXiv pdf URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_pdf_prefix_setting() {

        $this->render_setting('arxiv_url_pdf_prefix');
        echo '<p>(The url prefix for arXiv pdf downloads.)</p>';

    }

        /**
         * Render the setting for the arXiv source URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_source_prefix_setting() {

        $this->render_setting('arxiv_url_source_prefix');
        echo '<p>(The url prefix for arXiv source downloads.)</p>';

    }

        /**
         * Render the setting for the arXiv trackback prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_trackback_prefix_setting() {

        $this->render_setting('arxiv_url_trackback_prefix');
        echo '<p>(The url prefix for sending Trackbacks to the arXiv.)</p>';

    }

        /**
         * Render the setting for the DOI feed identifier for the arXiv.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_doi_feed_identifier_setting() {

        $this->render_setting('arxiv_doi_feed_identifier');
        echo '<p>(The arXiv can automatically update the journal reference of preprints based on information provided by publishers in the form of an xml feed as described <a href="https://arxiv.org/help/bib_feed">here</a>. For this to work, you must pick an identifier (specified via this setting) and inform the arXiv about the url under which the feed is available (can be configured in the following setting).)</p>';

    }

        /**
         * Render the setting for the DOI feed endpoint for the arXiv.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_arxiv_paper_doi_feed_endpoint_setting() {

        $this->render_setting('arxiv_paper_doi_feed_endpoint');
        $settings = O3PO_Settings::instance();
        $endpoint_suffix = $settings->get_plugin_option('arxiv_paper_doi_feed_endpoint');
        $arxiv_paper_doi_feed_endpoint_url = get_site_url() . '/'. $endpoint_suffix;

        echo '<p>(With the current setting the feed is available under <a href="' . esc_attr($arxiv_paper_doi_feed_endpoint_url) . '">' . esc_html($arxiv_paper_doi_feed_endpoint_url) . '</a>.)</p>';

    }

        /**
         * Render the setting for the arXiv DOI feed number of days.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_arxiv_paper_doi_feed_days_setting() {

        $this->render_setting('arxiv_paper_doi_feed_days');
        echo '<p>(Show publications up to this many days in the past in the doi feed.)</p>';

    }

        /**
         * Render the setting for the CrossRef DOI resolution url prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doi_url_prefix_setting() {

        $this->render_setting('doi_url_prefix');
        echo '<p>(The url prefix under which publications can be linked via their DOI.)</p>';

    }

        /**
         * Render the setting for the Scholastica manuscript page setting.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_scholastica_manuscripts_url_setting() {

        $this->render_setting('scholastica_manuscripts_url');
        echo '<p>(If you are using Scholastica as a peer-review platform, you may put here the url of the page on which Scholastica shows all your accepted manuscripts. This link will be displayed in the step-by-step publication guide on the admin panel for easy verification of the acceptance status of manuscripts before publication.)</p>';

    }

        /**
         * Render the setting for the Scirate abstract URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_scirate_url_abs_prefix_setting() {

        $this->render_setting('scirate_url_abs_prefix');
        echo '<p>(The url prefix of scirate abstract pages. If left blank no link to scirate is put on the publication pages.)</p>';

    }


        /**
         * Render the setting for the arXiv vanity URL prefix.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_arxiv_vanity_url_prefix_setting() {

        $this->render_setting('arxiv_vanity_url_prefix');
        echo '<p>(The url prefix of arXiv vanity pages. If left blank no link to arXiv Vanity is put on the publication pages.)</p>';

    }

        /**
         * Render the setting for the ORCID URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_orcid_url_prefix_setting() {

        $this->render_setting('orcid_url_prefix');
        echo '<p>(The url prefix of the pages of individual ORCIDs.)</p>';

    }

        /**
         * Render the setting for the URL prefix of Fermat's library.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_fermats_library_url_prefix_setting() {

        $this->render_setting('fermats_library_url_prefix');
        echo '<p>(The url prefix under which papers are published on Fermat\'s library.)</p>';

    }

        /**
         * Render the setting for the email of Fermt's library.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_fermats_library_email_setting() {

        $this->render_setting('fermats_library_email');
        echo '<p>(The address to which emails to fermat\'s library should be sent when notifying them of a newly published paper.)</p>';

    }

        /**
         * Render the setting for the MathJax URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_mathjax_url_setting() {

        $this->render_setting('mathjax_url');
        echo '<p>(The url of the version of MathJax.js to use.)</p>';

    }

        /**
         * Render the setting for the URL of the default thubnail for social media.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_social_media_thumbnail_url_setting() {

        $this->render_setting('social_media_thumbnail_url');
        echo '<p>(Full url of a suitable image file. For posts and pages that do not have a dedicated feature image, this image is offered via meta-tags to social media platforms when a link to that post/page is shared.)</p>';

    }

        /**
         * Render the setting for the Facebook App Id.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_facebook_app_id_setting() {

        $this->render_setting('facebook_app_id');
        echo '<p>(Your facebook_app_id, in case you have and want to use one.)</p>';

    }

        /**
         * Render the setting for the Buffer.com api url.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_buffer_api_url_setting() {

        $this->render_setting('buffer_api_url');
        echo '<p>(Url of the buffer.com api.)</p>';

    }

        /**
         * Render the setting for the Buffer.com access token.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_buffer_access_token_setting() {

        $this->render_password_setting('buffer_access_token');
        $post_types = O3PO_Utility::oxford_comma_implode(call_user_func($this->active_post_type_names_callback));
        echo '<p>(Create an access token <a href="https://buffer.com/developers/apps/create">here</a>.)</p>';

    }

        /**
         * Render the setting for the Buffer.com prfile ids.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_buffer_profile_ids_setting() {

        $post_types = O3PO_Utility::oxford_comma_implode(call_user_func($this->active_post_type_names_callback));

        if(empty($this->get_plugin_option('buffer_access_token')))
            $profile_id_help = 'Save the settings after entering a valid access token above, to get a list of available profile ids under your account.';
        else{
            $buffer_profile_information = O3PO_Buffer::get_profile_information($this->get_plugin_option('buffer_api_url'), $this->get_plugin_option('buffer_access_token'));

            if(is_wp_error($buffer_profile_information))
                $profile_id_help = 'There was an error when trying to obtain the available profile ids for the provided access token: ' . $buffer_profile_information->get_error_message();
            else{
                $profile_id_help = 'The available services and profile ids are:';
                foreach($buffer_profile_information as $info)
                    $profile_id_help .= ' ' . $info['service'] . ":" . $info['id'];
            }
        }

        $this->render_array_as_comma_separated_list_setting('buffer_profile_ids');
        echo '<p>(Comma separated list of buffer.com profile IDs under which to share updates of new ' . $post_types . ' posts. If empty, no attempt to share posts is made. ' . esc_html($profile_id_help) . ')</p>';
    }

        /**
         * Render the setting for the Relevanssi mime types to exclude.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_relevanssi_mime_types_to_exclude_setting() {

        if(!function_exists('relevanssi_index_pdf'))
            echo "<p>Please install relevanssi premium</p>";
        else
            echo "<p>Relevanssi premium installed</p>";

        echo("<p>TODO: Verify that indexing actually works!</p>");

        $this->render_setting('relevanssi_mime_types_to_exclude');
        echo '<p>(Relevanssi Premium has the ability to index the content of attachments and thereby, e.g., enabled full text search in PDFs attached to publications. It however, by default, will index all attachment types and this is usually not desirable for the arXiv source files in .tex or .tar.gz format. Through this setting, mime types can be excluded from indexing by providing a php regular expression. All attachment posts whose mime type matches that regular expression are excluded from indexing via the <a href="https://www.relevanssi.com/knowledge-base/controlling-attachment-types-index/">relevanssi_do_not_index</a> filter. If left empty all post attachments are indexed if that feature is enable in Relevanssi Premium.)</p>';

    }

        /**
         * Render the setting for whether to index pdf asynchronously.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_relevanssi_index_pdfs_asynchronously_setting() {

        $this->render_checkbox_setting('relevanssi_index_pdfs_asynchronously', 'Index pdfs after full text first requested via pdf endpoint');
        echo '<p>(Relevanssi Premium has the ability to index the content of attachments and thereby, e.g., enabled full text search in PDFs attached to publications. The indexing however happens on another server and is thus slow when done during the publishing of a publication post. Checking this box allows to do the indexing instead in the background after the full text pdf has first been requested via the pdf endpoint.)</p>';

    }

        /**
         * Render a standard text box type setting.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id   Id of the setting.
         */
    public function render_setting( $id ) {

        $option = $this->get_plugin_option($id);

        echo '<input class="regular-text ltr o3po-setting o3po-setting-text" type="text" id="' . $this->plugin_name . '-settings-' . $id . '" name="' . $this->plugin_name . '-settings[' . $id . ']" value="' . esc_attr($option) . '" />';

    }

        /**
         * Render a multi line text box type setting.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id   Id of the setting.
         */
    public function render_multi_line_setting( $id ) {

        $option = $this->get_plugin_option($id);

        echo '<textarea class="regular-text ltr o3po-setting o3po-setting-text-multi-line" id="' . $this->plugin_name . '-settings-' . $id . '" name="' . $this->plugin_name . '-settings[' . $id . ']" rows="' . (mb_substr_count( $option, "\n" )+1) . '">' . esc_html($option) . '</textarea>';

    }

        /**
         * Render a password setting.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id   Id of the setting.
         */
    public function render_password_setting( $id ) {

        $option = $this->get_plugin_option($id);

        echo '<input class="regular-text ltr o3po-setting o3po-setting-password" type="password" id="' . $this->plugin_name . '-settings-' . $id . '" name="' . $this->plugin_name . '-settings[' . $id . ']" value="' . esc_attr($option) . '" />';
        echo '<input type="checkbox" onclick="(function myFunction() {
    var x = document.getElementById(\'' . $this->plugin_name . '-settings-' . $id . '\');
    if (x.type === \'password\') {
        x.type = \'text\';
    } else {
        x.type = \'password\';
    }
})();">Show Password';
    }

        /**
         * Render a checkbox type setting.
         *
         * @since    0.1.0
         * @access   public
         * @param    string   $id    Id of the setting.
         * @param    string   $label Label of the setting.
         */
    public function render_checkbox_setting( $id, $label='') {

        $option = $this->get_plugin_option($id);

        echo '<input type="hidden" name="' . $this->plugin_name . '-settings[' . $id . ']" value="unchecked">'; //To have a 0 in POST when the checkbox is unticked
        echo '<input class="o3po-setting o3po-setting-checkbox" type="checkbox" id="' . $this->plugin_name . '-settings-' . $id . '" name="' . $this->plugin_name . '-settings[' . $id . ']" value="checked"' . checked( 'checked', $option, false ) . '/>';
        echo '<label for="' . $this->plugin_name . '-settings-' . $id . '">' . $label . '</label>';

    }

        /**
         * Render an array as comma separated list type setting.
         *
         * Does not escape or otherwise handle individual fields that contain commas.
         *
         * @since    0.3.0
         * @access   public
         * @param    string    $id   Id of the setting.
         */
    public function render_array_as_comma_separated_list_setting( $id ) {

        $option = $this->get_plugin_option($id);
        if(!is_array($option))
            $option = array();

        echo '<input class="regular-text ltr o3po-setting o3po-setting-text" type="text" id="' . $this->plugin_name . '-settings-' . $id . '" name="' . $this->plugin_name . '-settings[' . $id . ']" value="' . esc_attr(implode($option, ',')) . '" />';

    }


        /**
         * An array of all option names to the respective functions used when cleaning user input for these options.
         *
         * @since    0.1.0
         * @access   private
         * @var      array    $all_settings_fields_map    Aarray of all option names to the respective functions used when cleaning user input for these options.
         */
    private static $all_settings_fields_map = Null;

        /**
         * Get array of all option names to the respective functions used when cleaning user input for these options.
         *
         * @since    0.1.0
         * @access   public
         */
    public function get_all_settings_fields_map() {

        if(empty(self::$all_settings_fields_map))
            self::$all_settings_fields_map = array(
                'production_site_url' => 'validate_url',
                'journal_title' => 'trim_settings_field',
                'journal_subtitle' => 'trim_settings_field',
                'journal_description' => 'trim_settings_field',
                'journal_level_doi_suffix' => 'validate_doi_suffix',
                'doi_suffix_template' => 'trim_settings_field',
                'eissn' => 'validate_issn',
                'publisher' => 'trim_settings_field',
                'secondary_journal_title' => 'trim_settings_field',
                'secondary_journal_level_doi_suffix' => 'validate_doi_suffix',
                'secondary_journal_eissn' => 'validate_issn',
                'developer_email' => 'trim_settings_field',
                'publisher_email' => 'trim_settings_field',
                'publisher_country' => 'trim_settings_field',
                'license_name' => 'trim_settings_field',
                'license_type' => 'trim_settings_field',
                'license_version' => 'trim_settings_field',
                'license_url' => 'validate_url',
                'license_explanation' => 'trim_settings_field',
                'crossref_id' => 'trim_settings_field',
                'crossref_pw' => 'trim_settings_field',
                'crossref_get_forward_links_url' => 'validate_url',
                'crossref_deposite_url' => 'validate_url',
                'crossref_test_deposite_url' => 'validate_url',
                'crossref_email' => 'trim_settings_field',
                'crossref_archive_locations' => 'trim_settings_field',
                'ads_api_search_url' => 'validate_url',
                'ads_api_token' => 'trim_settings_field',
                'clockss_ftp_url' => 'trim_settings_field', #cannot use validate_url here because it prepends https:// or ftp:// and we want to save the raw url
                'clockss_username' => 'trim_settings_field',
                'clockss_password' => 'trim_settings_field',
                'arxiv_url_abs_prefix' => 'validate_url',
                'arxiv_url_pdf_prefix' => 'validate_url',
                'arxiv_url_source_prefix' => 'validate_url',
                'arxiv_url_trackback_prefix' => 'validate_url',
                'arxiv_doi_feed_identifier' => 'trim_settings_field',
                'arxiv_paper_doi_feed_endpoint' => 'trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed',
                'arxiv_paper_doi_feed_days' => 'validate_positive_integer',
                'doi_url_prefix' => 'validate_url',
                'scholastica_manuscripts_url' => 'validate_url',
                'scirate_url_abs_prefix' => 'validate_url',
                'arxiv_vanity_url_prefix' => 'validate_url',
                'orcid_url_prefix' => 'validate_url',
                'fermats_library_url_prefix' => 'validate_url',
                'fermats_library_email' => 'trim_settings_field',
                'mathjax_url' => 'validate_url',
                'social_media_thumbnail_url' => 'trim_settings_field',
                'buffer_api_url' => 'validate_url',
                'buffer_access_token' => 'trim_settings_field',
                'buffer_profile_ids' => 'validate_array_as_comma_separated_list',
                'facebook_app_id' => 'trim_settings_field',
                'doaj_api_url' => 'trim_settings_field',
                'doaj_api_key' => 'trim_settings_field',
                'doaj_language_code' => 'validate_two_letter_country_code',
                'custom_search_page' => 'checked_or_unchecked',
                'extended_search_and_navigation' => 'checked_or_unchecked',
                'search_form_on_search_page' => 'checked_or_unchecked',
                'page_template_for_publication_posts' => 'checked_or_unchecked',
                'page_template_abstract_header' => 'trim_settings_field',
                'trackbacks_from_secondary_directly_into_database' => 'checked_or_unchecked',
                'maintenance_mode' => 'checked_or_unchecked',
                'volumes_endpoint' => 'trim_settings_field',
                'doi_prefix' => 'validate_doi_prefix',
                'first_volume_year' => 'validate_first_volume_year',
                'executive_board' => 'trim_settings_field',
                'editor_in_chief' => 'trim_settings_field',
                'self_notification_subject_template' => 'trim_settings_field',
                'self_notification_body_template' => 'leave_unchaged',
                'author_notification_subject_template' => 'trim_settings_field',
                'author_notification_body_template' => 'leave_unchaged',
                'author_notification_secondary_subject_template' => 'trim_settings_field',
                'author_notification_secondary_body_template' => 'leave_unchaged',
                'fermats_library_notification_subject_template' => 'trim_settings_field',
                'fermats_library_notification_body_template' => 'leave_unchaged',
                'relevanssi_mime_types_to_exclude' => 'trim_settings_field',
                'relevanssi_index_pdfs_asynchronously' => 'checked_or_unchecked',
                'cited_by_refresh_seconds' => 'validate_positive_integer',

                    /* The following settings cannot be customized by the user.
                     * validation method null ensures that these settings are never
                     * updated when user settings are saved and validated. */
                'primary_publication_type_name' => null,
                'primary_publication_type_name_plural' => null,
                'secondary_publication_type_name' => null,
                'secondary_publication_type_name_plural' => null,
                                                   );

        return self::$all_settings_fields_map;
    }

        /**
         * Clean user input to the doi_prefix setting
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $doi_prefix    User input.
         */
    public function validate_doi_prefix( $field, $doi_prefix ) {

        $doi_prefix = trim($doi_prefix);
        if(preg_match('/^[0-9.-]*$/u', $doi_prefix))
            return $doi_prefix;

        add_settings_error( $field, 'illegal-doi-prefix', "The DOI prefix in '" . $this->settings_fields[$field]['title'] . "' may consist only of numbers 0-9, dot . and the dash - character. Field cleared.", 'error');
        return "";
    }

        /**
         * Clean user input to the doi_prefix setting
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $doi_suffix    User input.
         */
    public function validate_doi_suffix( $field, $doi_suffix ) {

        $doi_suffix = trim($doi_suffix);
        if(preg_match('/^[a-zA-Z0-9.-]*$/u', $doi_suffix))
            return $doi_suffix;

        add_settings_error( $field, 'illegal-doi-suffix', "The DOI suffix in '" . $this->settings_fields[$field]['title'] . "' may consist only of lower and upper case English alphabet letters a-z and A-Z, numbers 0-9, dot . and the dash - character. Field cleared.", 'error');
        return "";
    }

        /**
         * Clean user input to the first_volume_year setting
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $first_volume_year    User input.
         */
    public function validate_first_volume_year( $field, $first_volume_year ) {

        $first_volume_year = trim($first_volume_year);
        if(preg_match('/^[0-9]{4}$/u', $first_volume_year)) //this will cause a year 10000 bug
            return $first_volume_year;

        add_settings_error( $field, 'illegal-first-volume-year', "The year in '" . $this->settings_fields[$field]['title'] . "' must consist of exactly four digits in the range 0-9. Field cleared.", 'error');
        return "";
    }

        /**
         * Clean user input to issn type settings
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_issn( $field, $input ) {

        $input = trim($input);
        if(empty($input))
            return '';

        if(!O3PO_Utility::valid_issn($input))
            add_settings_error( $field, 'invalid-issn', "The ISSN in '" . $this->settings_fields[$field]['title'] . "' is invalid", 'error');

        return $input;
    }


        /**
         * Clean user input to url type settings
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_url( $field, $input ) {

        $input_trimmed = trim($input);
        $url = esc_url_raw(strip_tags(stripslashes($input_trimmed)));

        $parsed = parse_url($url);
        if(empty($parsed['scheme']) or empty($parsed['host']))
            add_settings_error( $field, 'url-validated', "The URL in '" . $this->settings_fields[$field]['title'] . "' was malformed. Please check.", 'error');
        elseif($url !== $input)
            add_settings_error( $field, 'url-validated', "The URL in '" . $this->settings_fields[$field]['title'] . "' was malformed or contained special or illegal characters, which were removed or escaped. Please check.", 'updated');
        return $url;
    }

        /**
         * Break a comma separated list into an array of fields
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_array_as_comma_separated_list( $field, $input ) {

        try
        {
            $input = trim($input);
            $array = preg_split('#,#u', $input, Null, PREG_SPLIT_NO_EMPTY);
            foreach($array as $key => $field)
                $array[$key] = trim($field);

            return $array;
        }
        catch (Exception $e) {
            add_settings_error( $field, 'not-comma-separated-list', "The input to '" . $this->settings_fields[$field]['title'] . "' could not be interpreted as a comma separated list.", 'error');
            return array();
        }
    }



        /**
         * Validate two letter country code
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_two_letter_country_code( $field, $input ) {

        $input = trim($input);
        if(preg_match('/^[A-Z]{2}$/u', $input))
            return $input;

        add_settings_error( $field, 'url-validated', "The two letter country code in '" . $this->settings_fields[$field]['title'] . "' was malformed. Field cleared.", 'error');
        return "";
    }

        /**
         * Validate positive integer
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_positive_integer( $field, $input ) {

        $input = trim($input);
        if(preg_match('/^[1-9][0-9]*$/u', $input))
            return $input;

        add_settings_error( $field, 'not-a-positive-integer', "The input to the field '" . $this->settings_fields[$field]['title'] . "' was not a positive integer without leading zeros.", 'error');

        return "1";
    }

        /**
         * Restrict input to checked or unchecked
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function checked_or_unchecked( $field, $input ) {

        if($input === "checked" or $input === "unchecked")
            return $input;

        add_settings_error( $field, 'not-checked-or-unchecked', "The field '" . $this->settings_fields[$field]['title'] . "' must be either checked or unchecked. Set to unchecked.", 'error');
        return 'unchecked';
    }

        /**
         * Trim user input to settings
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed( $field, $input ) {

        $input = trim($input);
        if(empty($input))
        {
            add_settings_error( $field, 'must-not-be-empty', "The field '" . $this->settings_fields[$field]['title'] . "' must not be empty. Reset to default value.", 'error');
            $input = $this->option_defaults[$field];
        }
        if(empty($input))
            $input = 'this-field-must-not-be-empty';

        if($input !== $this->get_plugin_option($field))
        {
                /*
                 * In render_settings_page() we check for transient and
                 * flush rewrite rules there if it is set and True.
                 */
            set_transient($this->plugin_name . '-settings-rewrite-rules-affected', True);
            add_settings_error( $field, 'rewrite-rules-affected', "The rewrite rules have been updated because the field '" . $this->settings_fields[$field]['title'] . "' was changed.", 'updated');
        }

        return $input;
    }

        /**
         * Trim user input to settings
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function trim_settings_field( $field, $input ) {

        return trim($input);
    }

        /**
         * Leave user input to settings unchanged.
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function leave_unchaged( $field, $input ) {

        return $input;
    }

        /**
         * Validate settings.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $input    Value of the setting to validate.
         */
    public function validate_settings( $input ) {

        $newinput = array();
        foreach($this->get_all_settings_fields_map() as $field => $validation_method)
        {
            if(isset($input[$field]) and $validation_method !== null)
                $newinput[$field] = $this->$validation_method($field, $input[$field]);
            else
                $newinput[$field] = $this->get_plugin_option($field);
        }

        return $newinput;
    }

        /**
         * Get the value of a plugin option by id.
         *
         * @since    0.1.0
         * @acceess  prublic
         * @param    int    $id     Id of the setting.
         */
    public function get_plugin_option( $id ) {

        $options = get_option($this->plugin_name . '-settings', array());
        if(array_key_exists($id, $options))
            return $options[$id];

        if(isset($this->option_defaults[$id]))
            return $this->option_defaults[$id];


        foreach($this->get_all_settings_fields_map() as $field => $callable)
        {
            if($field === $id)
                return "";
        }

        throw new Exception('The non existing plugin option '. $id . ' was requested.');
    }

        /**
         * Get the plugin_name.
         *
         * @since 0.3.0
         * @access public
         */
    public function get_plugin_name() {

        return $this->plugin_name;
    }

        /**
         * Get the plugin_pretty_name.
         *
         * @since 0.3.0
         * @access public
         */
    public function get_plugin_pretty_name() {

        return $this->plugin_pretty_name;
    }


        /**
         * Wrapper around Wordpress' add_settings_section()
         *
         * Keeps a record of all settings sections in $this->settings_sections.
         *
         * @since  0.3.0
         * @access private
         * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the section. Shown as the heading for the section.
         * @param callable $callback Function that echos out any content at the top of the section (between heading and fields).
         * @param string   $page     The slug-name of the settings page on which to show the section. Built-in pages include
         *                           'general', 'reading', 'writing', 'discussion', 'media', etc. Create your own using
         *                           add_options_page();
         */
    public function add_settings_section( $id, $title, $callback, $page ) {

        add_settings_section($id, $title, $callback, $page);
        $this->settings_sections[$id] = array('title' => $title, 'callback' => $callback, 'page' => $page);

    }

        /**
         * Wrapper around Wordpress' add_settings_field()
         *
         * Keeps a record of all settings sections in $this->settings_sections.
         *
         * @since  0.3.0
         * @access private
         * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the section. Shown as the heading for the section.
         * @param callable $callback Function that echos out any content at the top of the section (between heading and fields).
         * @param string   $page     The slug-name of the settings page on which to show the section. Built-in pages include
         *                           'general', 'reading', 'writing', 'discussion', 'media', etc. Create your own using
         *                           add_options_page();
         * @param string   $section  Optional. The slug-name of the section of the settings page
         *                           in which to show the box. Default 'default'.
         * @param array    $args {
         *     Optional. Extra arguments used when outputting the field.
         *
         *     @type string $label_for When supplied, the setting title will be wrapped
         *                             in a `<label>` element, its `for` attribute populated
         *                             with this value.
         *     @type string $class     CSS Class to be added to the `<tr>` element when the
         *                             field is output.
         * }
         */
    public function add_settings_field($id, $title, $callback, $page, $section='default', $args=array() ) {

        add_settings_field($id, $title, $callback, $page, $section, $args);
        $this->settings_fields[$id] = array('title' => $title, 'callback' => $callback, 'page' => $page, 'section' => $section, 'args' => $args);
    }


        /**
         * Return the defaults of all options.
         *
         * @sinde 0.3.0
         * @access public
         * @return array  Array of all default options.
         */
    public function get_option_defaults() {
        return $this->option_defaults;
    }
}
