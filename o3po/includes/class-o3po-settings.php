<?php

/**
 * Manage the settings of the plugin.
 *
 * This class must be static or a singleton because we need to
 * access setting from template files (search.php, single.php)
 * into which we cannot inject a dependency to the settings by
 * passing a instance.
 * This class however also cannot be static if we want the settings
 * group names and field ids to depend on the plugin slug.
 * Therefore we implement O3PO_Settings as a singleton. It doesn't seem
 * to be such a evil thing to do given that the settings are anyway
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
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-journal.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/trait-o3po-form.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-o3po-ready2publish-form.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-o3po-ready2publish-dashboard.php';

/**
 * Manage the settings of the plugin.
 *
 * Provide methods to set and get plugin settings fields and to create
 * the respective admin page and menu entry. *
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Settings extends O3PO_Singleton {

    use O3PO_Form;

        /**
         * The human readable name of this plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $plugin_pretty_name    The human readable name of this plugin.
         */
	private $plugin_pretty_name;

        /**
         * The current version of the plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $version    The current version of the plugin.
         */
	private $version;

        /**
         * Whether this singleton had already been configured.
         *
         * @since  0.4.0
         * @access private
         * @var    string  $version Whether this singleton had already been configured.
         */
	private static $configured = false;

        /**
         * The callback from which to get the active post type names.
         *
         * @since    0.1.0
         * @access   private
         * @var      mixed     $active_post_type_names_callback    The callback from which to get the active post type names.
         */
	private $active_post_type_names_callback;

        /**
         * Returns the settings singleton.
         *
         * Also checks whether it has been configured.
         *
         * @since  0.4.0
         * @param  string   $plugin_name                     Simple name of this plugin.
         * @param  string   $plugin_pretty_name              Pretty name of this plugin.
         * @param  string   $version                         Version of this plugin.
         * @param  callback $active_post_type_names_callback The callback from which to get the active post type names.
         * @return O3PO_Settings The settings singleton.
         */
    public static function instance($plugin_name=null, $plugin_pretty_name=null, $version=null, $active_post_type_names_callback=null)
    {

        $settings = parent::instance();
        $settings->slug = 'settings';
        if(!static::configured())
            if($plugin_name!==null and $plugin_pretty_name!==null and $version!==null and $active_post_type_names_callback!==null)
                $settings->configure($plugin_name, $plugin_pretty_name, $version, $active_post_type_names_callback);
            else
                throw new Exception("Settings object must be configured on first initialization. No configuration given.");
        else
            if($plugin_name!==null or $plugin_pretty_name!==null or $version!==null or $active_post_type_names_callback!==null)
                throw new Exception("Settings object must be configured on first initialization. Already configured.");

        return $settings;
    }

        /**
         * Configure the settings singleton.
         *
         * @since    0.1.0
         * @since    0.3.1 Access set to private.
         * @access   private
         * @param    string    $plugin_name                      Simple name of this plugin.
         * @param    string    $plugin_pretty_name               Pretty name of this plugin.
         * @param    string    $version                          Version of this plugin.
         * @param    callback  $active_post_type_names_callback  The callback from which to get the active post type names.
         */
	private function configure( $plugin_name, $plugin_pretty_name, $version, $active_post_type_names_callback ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->plugin_pretty_name = $plugin_pretty_name;
        $this->active_post_type_names_callback = $active_post_type_names_callback;

        $this->specify_sections_and_fields();
        O3PO_Journal::specify_settings($this);
        O3PO_EmailTemplates::specify_settings($this);
        O3PO_Ready2PublishForm::specify_settings($this);
        O3PO_Ready2PublishDashboard::specify_settings($this);

        static::$configured = true;
	}

        /**
         * Check whether this settings singleton has already been configured or not.
         *
         * @since  0.4.0
         * @access public
         * @return boolean Whether this settings singleton has already been configured or not.
         */
    public static function configured() {

        return static::$configured;
    }

        /**
         * Get the value of a field by id.
         *
         * @since   0.4.0
         * @acceess prublic
         * @param   int     $id Id of the field.
         */
    public function get_field_value( $id ) {

        if(!array_key_exists($id, $this->fields))
            throw new Exception('The non existing ' . $this->slug . ' field ' . $id . ' was requested. Known ' . $this->slug . ' fields are: ' . json_encode($this->fields));

        $fields = get_option($this->plugin_name . '-' . $this->slug, array());
        if(array_key_exists($id, $fields))
            if($this->fields[$id]['max_length'] === false)
                return $fields[$id];
            else
                return substr($fields[$id], 0, $this->fields[$id]['max_length']);
        else
            return $this->get_field_default($id);
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

        add_options_page($this->plugin_pretty_name . ' settings page', $this->plugin_pretty_name, 'manage_options', $this->plugin_name . '-' . $this->slug, array( $this, 'render_settings_page' ));

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
        if(get_transient($this->plugin_name . '-' . $this->slug . '-rewrite-rules-affected'))
        {
            flush_rewrite_rules(true);
            delete_transient($this->plugin_name . '-' . $this->slug . '-rewrite-rules-affected');
        }

        echo '<div>';
        echo '<h2>' . $this->plugin_pretty_name . ' settings (version ' . $this->version . ')</h2>';

        if(isset( $_GET['tab'] ))
            $active_setting_section = $_GET['tab'];
        else
        {
            reset($this->sections);
            $active_setting_section = key($this->sections);
        }

        echo '<h2 class="nav-tab-wrapper">' . "\n";
        foreach($this->sections as $section_id => $section_options)
            echo '<a href="' . esc_url('?page=' . $this->plugin_name . '-' . $this->slug . '&amp;tab=' . $section_id) . '" class="nav-tab' . ($active_setting_section == $section_id ? ' nav-tab-active' : '') . '">' . esc_html($section_options['title']) . '</a>' . "\n";
        echo '</h2>' . "\n";

        echo '<form action="options.php" method="post">';

        settings_fields($this->plugin_name . '-' . $this->slug); # Output nonce, action, and option_page fields for a settings page.

        do_settings_sections($this->plugin_name . '-' . $this->slug . ':' . $active_setting_section);

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

        register_setting( $this->plugin_name . '-' . $this->slug, $this->plugin_name . '-' . $this->slug, array( $this, 'validate_input' ) );

        foreach($this->sections as $section => $specification)
            add_settings_section($section, $specification['title'], $specification['callback'], $specification['page']);

        foreach($this->fields as $id => $specification)
            if(isset($specification['title'])) # fake fields do not have titles
                add_settings_field($id, $specification['title'], $specification['callback'], $specification['page'], $specification['section'], $specification['args']);
    }

        /**
         * Specifies plugin wide settings sections and fields.
         *
         * @since  0.3.1+
         * @access private
         */
    private function specify_sections_and_fields() {

        $this->specify_fake_field('primary_publication_type_name', 'paper');
        $this->specify_fake_field('primary_publication_type_name_plural', 'papers');
        $this->specify_fake_field('secondary_publication_type_name', 'view');
        $this->specify_fake_field('secondary_publication_type_name_plural', 'views');
        $this->specify_fake_field('volumes_endpoint', 'volumes');
        $this->specify_fake_field('ready2publish_slug', 'ready2publish');

        $this->specify_section('plugin_settings', 'Plugin', array( $this, 'render_plugin_settings' ), 'plugin_settings');
        $this->specify_field('production_site_url', 'Production site url', array( $this, 'render_production_site_url_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'validate_url'), '');
        $this->specify_field('extended_search_and_navigation', 'Add search and navigation to home page', array( $this, 'render_extended_search_and_navigation_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'checked_or_unchecked'), 'checked');
        $this->specify_field('search_form_on_search_page', 'Add a search form to the search page', array( $this, 'render_search_form_on_search_page_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'checked_or_unchecked'), 'checked');
        $this->specify_field('custom_search_page', 'Display search page notice', array( $this, 'render_custom_search_page_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'checked_or_unchecked'), 'checked');
        $this->specify_field('page_template_for_publication_posts', 'Force page template', array( $this, 'render_page_template_for_publication_posts_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'checked_or_unchecked'), 'unchecked');
        $this->specify_field('page_template_abstract_header', 'Show a heading for the abstract', array( $this, 'render_page_template_abstract_header_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'trim'), '');
        $this->specify_field('trackbacks_from_secondary_directly_into_database', 'Write Trackbacks directly', array( $this, 'render_trackbacks_from_secondary_directly_into_database_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'checked_or_unchecked'), 'unchecked');
        $this->specify_field('cited_by_refresh_seconds', 'Refresh cited-by time', array( $this, 'render_cited_by_refresh_seconds_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'validate_positive_integer'), '43200');#=60*60*12
        $this->specify_field('maintenance_mode', 'Maintenance mode', array( $this, 'render_maintenance_mode_setting' ), 'plugin_settings', 'plugin_settings', array(), array($this, 'checked_or_unchecked'), 'unchecked');

        $this->specify_section('journal_settings', 'Journal', array( $this, 'render_journal_settings' ), 'journal_settings');
        $this->specify_field('doi_prefix', 'DOI prefix', array( $this, 'render_doi_prefix_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_doi_prefix'), '');
        $this->specify_field('journal_title', 'Journal title', array( $this, 'render_journal_title_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('journal_subtitle', 'Journal subtitle', array( $this, 'render_journal_subtitle_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('journal_description', 'Journal description', array( $this, 'render_journal_description_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('journal_level_doi_suffix', 'Journal level DOI suffix', array( $this, 'render_journal_level_doi_suffix_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_doi_suffix'), '');
        $this->specify_field('eissn', 'eISSN', array( $this, 'render_eissn_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_issn_or_empty'), '');
        $this->specify_field('publisher', 'Publisher', array( $this, 'render_publisher_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('publisher_street_and_number', 'Publisher street and number', array( $this, 'render_publisher_street_and_number_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('publisher_zip_code_and_city', 'Publisher zip code and city', array( $this, 'render_publisher_zip_code_and_city_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('publisher_phone', 'Publisher phone', array( $this, 'render_publisher_phone_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('secondary_journal_title', 'Secondary journal title', array( $this, 'render_secondary_journal_title_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('secondary_journal_level_doi_suffix', 'Secondary journal level DOI suffix', array( $this, 'render_secondary_journal_level_doi_suffix_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_doi_suffix'), '');
        $this->specify_field('secondary_journal_eissn', 'Secondary journal eISSN', array( $this, 'render_secondary_journal_eissn_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_issn_or_empty'), '');
        $this->specify_field('developer_email', 'Email of developer', array( $this, 'render_developer_email_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_email'), '');
        $this->specify_field('publisher_email', 'Email of publisher', array( $this, 'render_publisher_email_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_email'), '');
        $this->specify_field('first_volume_year', 'Year of first volume', array( $this, 'render_first_volume_year_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_four_digit_year'), '2017');
        $this->specify_field('publisher_country', 'Country of publisher', array( $this, 'render_publisher_country_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '');
        $this->specify_field('license_name', 'License name', array( $this, 'render_license_name_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), 'Creative Commons Attribution 4.0 International (CC BY 4.0)');
        $this->specify_field('license_type', 'License type', array( $this, 'render_license_type_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), 'CC BY');
        $this->specify_field('license_version', 'License version', array( $this, 'render_license_version_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), '4.0');
        $this->specify_field('license_url', 'License url', array( $this, 'render_license_url_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'validate_url'), 'https://creativecommons.org/licenses/by/4.0/');
        $this->specify_field('license_explanation', 'License explanation string', array( $this, 'render_license_explanation_setting' ), 'journal_settings', 'journal_settings', array(), array($this, 'trim'), 'Copyright remains with the original copyright holders such as the authors or their institutions.');

        $this->specify_section('crossref_settings', 'Crossref', array( $this, 'render_crossref_settings' ), 'crossref_settings');
        $this->specify_field('crossref_id', 'Crossref ID', array( $this, 'render_crossref_id_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'trim'), '');
        $this->specify_field('crossref_pw', 'Crossref password', array( $this, 'render_crossref_pw_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'trim'), '');
        $this->specify_field('crossref_get_forward_links_url', 'Crossref get forward links url', array( $this, 'render_crossref_get_forward_links_url_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'validate_url'), 'https://doi.crossref.org/servlet/getForwardLinks');
        $this->specify_field('crossref_deposite_url', 'Crossref deposite url', array( $this, 'render_crossref_deposite_url_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'validate_url'), 'https://doi.crossref.org/servlet/deposit');
        $this->specify_field('crossref_test_deposite_url', 'Crossref deposite url for testing', array( $this, 'render_crossref_test_deposite_url_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'validate_url'), 'https://test.crossref.org/servlet/deposit');
        $this->specify_field('crossref_email', 'Email for communication with Crossref', array( $this, 'render_crossref_email_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'validate_email'), '');
        $this->specify_field('crossref_archive_locations', 'Archive locations', array( $this, 'render_crossref_archive_locations_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'trim'), '');

        $this->specify_field('crossref_crossmark_policy_page_doi', 'Crossmark policy page doi', array( $this, 'render_crossref_crossmark_policy_page_doi_setting' ), 'crossref_settings', 'crossref_settings', array(), array($this, 'trim'), '');

        $this->specify_section('ads_settings', 'ADS', array( $this, 'render_ads_settings' ), 'ads_settings');
        $this->specify_field('ads_api_search_url', 'ADS API URL', array( $this, 'render_ads_api_search_url_setting' ), 'ads_settings', 'ads_settings', array(), array($this, 'validate_url'), 'https://api.adsabs.harvard.edu/v1/search/query');
        $this->specify_field('ads_api_token', 'ADS API token', array( $this, 'render_ads_api_token_setting' ), 'ads_settings', 'ads_settings', array(), array($this, 'trim'), '');

        $this->specify_section('clockss_settings', 'Clockss', array( $this, 'render_clockss_settings' ), 'clockss_settings');
        $this->specify_field('clockss_ftp_url', 'Clockss FTP URL', array( $this, 'render_clockss_ftp_url_setting' ), 'clockss_settings', 'clockss_settings', array(), array($this, 'trim'), 'ftp.clockss.org'); #cannot use validate_url here because it prepends https:// or ftp:// and we want to save the raw url
        $this->specify_field('clockss_username', 'Clockss Username', array( $this, 'render_clockss_username_setting' ), 'clockss_settings', 'clockss_settings', array(), array($this, 'trim'), '');
        $this->specify_field('clockss_password', 'Clockss Password', array( $this, 'render_clockss_password_setting' ), 'clockss_settings', 'clockss_settings', array(), array($this, 'trim'), '');

        $this->specify_section('doaj_settings', 'DOAJ', array( $this, 'render_doaj_settings' ), 'doaj_settings');
        $this->specify_field('doaj_api_url', 'DOAJ API url', array( $this, 'render_doaj_api_url_setting' ), 'doaj_settings', 'doaj_settings', array(), array($this, 'validate_url'), 'https://doaj.org/api/v1/articles');
        $this->specify_field('doaj_api_key', 'DOAJ API key', array( $this, 'render_doaj_api_key_setting' ), 'doaj_settings', 'doaj_settings', array(), array($this, 'trim'), '');
        $this->specify_field('doaj_language_code', 'DOAJ langugage code (two upper case letters)', array( $this, 'render_doaj_language_code_setting' ), 'doaj_settings', 'doaj_settings', array(), array($this, 'validate_two_letter_country_code'), 'EN');

        $this->specify_section('arxiv_settings', 'ArXiv', array( $this, 'render_arxiv_settings' ), 'arxiv_settings');
        $this->specify_field('arxiv_url_abs_prefix', 'Url prefix for abstract pages', array( $this, 'render_arxiv_url_abs_prefix_setting' ), 'arxiv_settings', 'arxiv_settings', array(), array($this, 'validate_url'), 'https://arxiv.org/abs/');
        $this->specify_field('arxiv_url_pdf_prefix', 'Url prefix for pdfs', array( $this, 'render_arxiv_url_pdf_prefix_setting' ), 'arxiv_settings', 'arxiv_settings', array(), array($this, 'validate_url'), 'https://arxiv.org/pdf/');
        $this->specify_field('arxiv_url_source_prefix', 'Url prefix for eprint source', array( $this, 'render_arxiv_url_source_prefix_setting' ), 'arxiv_settings', 'arxiv_settings', array(), array($this, 'validate_url'), 'https://arxiv.org/e-print/');
        $this->specify_field('arxiv_url_trackback_prefix', 'Url prefix for Trackbacks', array( $this, 'render_arxiv_url_trackback_prefix_setting' ), 'arxiv_settings', 'arxiv_settings', array(), array($this, 'validate_url'), 'http://arxiv.org/trackback/');
        $this->specify_field('arxiv_doi_feed_identifier', 'Indentifier for the DOI feed', array( $this, 'render_arxiv_doi_feed_identifier_setting' ), 'arxiv_settings', 'arxiv_settings', array(), array($this, 'trim'), '');
        $this->specify_field('arxiv_paper_doi_feed_endpoint', 'Endpoint for the arXiv DOI feed', array( $this, 'render_arxiv_paper_doi_feed_endpoint_setting' ), 'arxiv_settings', 'arxiv_settings', array(), array($this, 'trim_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed'), 'arxiv_paper_doi_feed');
        $this->specify_field('arxiv_paper_doi_feed_days', 'Number of days in arXiv DOI feed', array( $this, 'render_arxiv_paper_doi_feed_days_setting' ), 'arxiv_settings', 'arxiv_settings', array(), array($this, 'validate_positive_integer'), '365');

        $this->specify_section('buffer_settings', 'Buffer.com', array( $this, 'render_buffer_settings' ), 'buffer_settings');
        $this->specify_field('buffer_api_url', 'Url of the buffer.com api', array( $this, 'render_buffer_api_url_setting' ), 'buffer_settings', 'buffer_settings', array(), array($this, 'validate_url'), 'https://api.bufferapp.com/1');
        $this->specify_field('buffer_access_token', 'Access token from buffer.com', array( $this, 'render_buffer_access_token_setting' ), 'buffer_settings', 'buffer_settings', array(), array($this, 'trim'), '');
        $this->specify_field('buffer_profile_ids', 'Profile IDs on buffer.com', array( $this, 'render_buffer_profile_ids_setting' ), 'buffer_settings', 'buffer_settings', array(), array($this, 'validate_array_as_comma_separated_list'), array());

        $this->specify_section('other_service_settings', 'Other services', array( $this, 'render_other_service_settings' ), 'other_service_settings');
        $this->specify_field('doi_url_prefix', 'Url prefix for DOI resolution', array( $this, 'render_doi_url_prefix_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), 'https://doi.org/');
        $this->specify_field('scirate_url_abs_prefix', 'Url prefix for scirate pages', array( $this, 'render_scirate_url_abs_prefix_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), 'https://scirate.com/arxiv/');
        $this->specify_field('arxiv_vanity_url_prefix', 'Url prefix for arXiv Vanity pages', array( $this, 'render_arxiv_vanity_url_prefix_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), 'https://www.arxiv-vanity.com/papers/');
        $this->specify_field('scholastica_manuscripts_url', 'Url of Scholastica manuscripts page', array( $this, 'render_scholastica_manuscripts_url_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), '');
        $this->specify_field('orcid_url_prefix', 'Orcid url prefix', array( $this, 'render_orcid_url_prefix_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), 'https://orcid.org/');
        $this->specify_field('fermats_library_about_url', 'Url of Fermat\' Library about page', array( $this, 'render_fermats_library_about_url_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), 'https://fermatslibrary.com/about/');
        $this->specify_field('fermats_library_url_prefix', 'Url prefix for Fermat\'s Library', array( $this, 'render_fermats_library_url_prefix_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), 'https://fermatslibrary.com/s/');
        $this->specify_field('fermats_library_email', 'Email for Fermat\'s Library', array( $this, 'render_fermats_library_email_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_email'), '');
        $this->specify_field('mathjax_url', 'MathJax url', array( $this, 'render_mathjax_url_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js');
        $this->specify_field('social_media_thumbnail_url', 'Url of default thumbnail for social media', array( $this, 'render_social_media_thumbnail_url_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'validate_url'), '');
        $this->specify_field('facebook_app_id', 'Facebook app_id', array( $this, 'render_facebook_app_id_setting' ), 'other_service_settings', 'other_service_settings', array(), array($this, 'trim'), '');

        $this->specify_section('other_plugins_settings', 'Other plugins', array( $this, 'render_other_plugins_settings' ), 'other_plugins_settings');
        $this->specify_field('relevanssi_mime_types_to_exclude', 'Relevanssi mime types to exclude', array( $this, 'render_relevanssi_mime_types_to_exclude_setting' ), 'other_plugins_settings', 'other_plugins_settings', array(), array($this, 'trim'), '#(application/.*(tar|gz|gzip)|text/.*tex)#u');
        $this->specify_field('relevanssi_index_pdfs_asynchronously', 'Index PDFs asynchronously', array( $this, 'render_relevanssi_index_pdfs_asynchronously_setting' ), 'other_plugins_settings', 'other_plugins_settings', array(), array($this, 'checked_or_unchecked'), 'checked');

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

        $this->render_single_line_field('production_site_url');
        echo '<p>(Unless this field is filled and matches the string ' . esc_html(get_site_url())  . ', this instance will be considered a test system and the interfaces with various critical services will remain disabled. This ensures that even a full backup of your journal website, when hosted under a different domain and used as, e.g., a staging system, will not accidentally register DOIs or interact with external services in an unintended way.)</p>';

    }

        /**
         * Render the setting for whether to show the extended search-based navigation on the home page.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_extended_search_and_navigation_setting() {

        $this->render_checkbox_field('extended_search_and_navigation', 'Add  a search-base navigation with statistics about the number of publications and volumes above the posts on the home page. Has no effect if the home page is set to a static page without a post list.');

    }

        /**
         * Render the setting for whether to show a search form above the results on the search page.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_search_form_on_search_page_setting() {

        $this->render_checkbox_field('search_form_on_search_page', 'Add a search form above the results on the search page.');

    }

        /**
         * Render the setting for whether to show the custom search page.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_custom_search_page_setting() {

        $this->render_checkbox_field('custom_search_page', 'Uncheck to disable the display of a notice on the search page informing users what it can mean if they are unable to find a paper on this website, but whose version on the arXiv claims that it was published in this journal. You can preview the two versions of this message <a href="/?s=thissearchstringyieldsnoresults">here</a> and <a href="/?s=thissearchstringyieldsnoresults&amp;reason=title-click">here</a>. Notice how a search that includes the reason=title-click query variable can be used to implement a way for readers to check the validity of claims of publication in, e.g., the LaTeX template of your journal.');

    }

        /**
         * Render the setting for the abstract heading
         *
         * @since    0.3.1
         * @access   public
         */
    public function render_page_template_abstract_header_setting() {

        $this->render_single_line_field('page_template_abstract_header');
        echo '<p>An optional header that is displayed before the abstract on the pages of individual publications.</p>';

    }

        /**
         * Render the setting for whether to write trackbacks directly.
         *
         * @since    0.3.1
         * @access   public
         */
    public function render_trackbacks_from_secondary_directly_into_database_setting() {

        $this->render_checkbox_field('trackbacks_from_secondary_directly_into_database', 'Publication posts in the secondary journal targeting posts published in the primary journal send <a href="https://en.support.wordpress.com/comments/trackbacks/">Trackbacks</a> to the targeted post(s). Unfortunately, sending such Trackbacks does not work reliably on many WordPress instances. You can check this box to instead have ' . $this->get_plugin_pretty_name() . ' write the such Trackbacks directly into the local database via wp_new_comment() instead of using trackback().' );

    }

        /**
         * Render the setting for whether to use the page template for publications.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_page_template_for_publication_posts_setting() {

        $this->render_checkbox_field('page_template_for_publication_posts', 'Show publication posts with the page template instead of the default post template of your theme. Some themes include information such as "Posted on ... by ..." on the post template, which may be inappropriate for publication posts.');

    }

        /**
         * Render the cited by refresh seconds field
         *
         * @sinde 0.3.0
         * @access public
         */
    public function render_cited_by_refresh_seconds_setting() {

        $this->render_single_line_field('cited_by_refresh_seconds');
        echo '<p>(Refresh the cited by data on publication pages at most every this many seconds. This affects how cited-by data is retrieved from Crossref and ADS.)</p>';

    }

        /**
         * Render the setting to enabled maintenance mode.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_maintenance_mode_setting() {

        $this->render_checkbox_field('maintenance_mode', 'Enable maintenance mode.');
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

        $this->render_single_line_field('doi_prefix');
        echo('<p>(The DOI prefix assigned to your publisher by your DOI registry.)</p>');

    }

        /**
         * Render the setting for the journal title of the primary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_title_setting() {

        $this->render_single_line_field('journal_title');
        echo('<p>(The title of your journal.)</p>');

    }

        /**
         * Render the setting for the subtitle of the primary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_subtitle_setting() {

        $this->render_single_line_field('journal_subtitle');
        echo('<p>(The subtitle of your journal. Currently not used.)</p>');

    }

        /**
         * Render the setting for the journal description.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_description_setting() {

        $this->render_single_line_field('journal_description');
        echo('<p>(A short description of your journal for use in open graph meta-tags.)</p>');

    }

        /**
         * Render the setting for the journal level DOI suffix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_level_doi_suffix_setting() {

        $this->render_single_line_field('journal_level_doi_suffix');
        echo('<p>(This is used as both the journal level DOI suffix and to generate the DOIs of your publications via the scheme [doi_prefix]/doi_suffix_template. See journal settings DOI suffix template for more information on how the template is substituted.');

    }

        /**
         * Render the setting for the EISSN.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_eissn_setting() {

        $this->render_single_line_field('eissn');
        echo('<p>(The eISSN of your journal.)</p>');

    }

        /**
         * Render the setting for the publisher name.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_setting() {

        $this->render_single_line_field('publisher');
        echo('<p>(The name of the publisher.)</p>');

    }

        /**
         * Render the setting for the publisher street and number.
         *
         * @since  0.3.1+
         * @access public
         */
    public function render_publisher_street_and_number_setting() {

        $this->render_single_line_field('publisher_street_and_number');
        echo('<p>(The street and house number number of the publisher.)</p>');

    }


        /**
         * Render the setting for the publisher zip code and city.
         *
         * @since  0.3.1+
         * @access public
         */
    public function render_publisher_zip_code_and_city_setting() {

        $this->render_single_line_field('publisher_zip_code_and_city');
        echo('<p>(The zip code and city of the publisher.)</p>');

    }


        /**
         * Render the setting for the publisher phone.
         *
         * @since  0.4.0
         * @access public
         */
    public function render_publisher_phone_setting() {

        $this->render_single_line_field('publisher_phone');
        echo('<p>(The phone number of the publisher. Should include the country code.)</p>');

    }

        /**
         * Render the setting for the title of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_title_setting() {

        $this->render_single_line_field('secondary_journal_title');
        echo('<p>(' . $this->get_plugin_pretty_name() . ' allows you to run a secondary journal for editorials and other secondary literature. Set its title here.)</p>');

    }

        /**
         * Render the setting for the journal level DOI suffix of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_level_doi_suffix_setting() {

        $this->render_single_line_field('secondary_journal_level_doi_suffix');
        echo('<p>(This is used as both the journal level DOI suffix of the secondary journal and to generate the DOIs of the publications in the secondary journal via the scheme [doi_prefix]/[secondary_journal_level_doi_suffix]-[date]-[page], where [date] is the <a href="https://en.wikipedia.org/wiki/ISO_8601">ISO_8601</a> formated publication date and [page] is an article number that counts up starting at 1.  See the <a href="https://support.crossref.org/hc/en-us/articles/214569903-Journal-level-DOIs">Crossref website</a> for more background.)</p>');

    }

        /**
         * Render the setting for the EISSN of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_eissn_setting() {

        $this->render_single_line_field('secondary_journal_eissn');
        echo '<p>(The eISSN of your secondary journal. Do not set this equal to the eISSN of your primary journal. If you do, they may be treated as a single journal for the purpose of biometrics. It is OK to leave this blank, but DOAJ, for example will not accept meta-date on articles in the secondary journal if this is not set.)</p>';

    }

        /**
         * Render the setting for the email of the developer.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_developer_email_setting() {

        $this->render_single_line_field('developer_email');
        echo('<p>(Debug and other notification emails are sent to this address. Is used as the primary email address on test systems.)</p>');

    }

        /**
         * Render the setting for the email of the publisher.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_email_setting() {

        $this->render_single_line_field('publisher_email');
        echo('<p>(Email address of the publisher. This is used as the from address for emails sent by ' . $this->get_plugin_pretty_name() . '. Must be an address that is valid on the SMTP server used by this Wordpress instance.)</p>');

    }

        /**
         * Render the setting for the year of the first volume.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_first_volume_year_setting() {

        $this->render_single_line_field('first_volume_year');
        echo('<p>(Four digit year in which the first volume was published. Is used to automatically set the volume number of newly published publications and, e.g., when generating the <a href="/volume/">volume overview page</a>.)</p>');

    }

        /**
         * Render the setting for the licence name.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_name_setting() {

        $this->render_single_line_field('license_name');
        echo '<p>(The human readable name of the license under which you publish. For example: Creative Commons Attribution 4.0 International (CC BY 4.0))</p>';

    }

        /**
         * Render the setting for the license type.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_type_setting() {

        $this->render_single_line_field('license_type');
        echo '<p>(The type of license under which you publish. For example: CC BY)</p>';

    }

        /**
         * Render the setting for the license version.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_version_setting() {

        $this->render_single_line_field('license_version');
        echo '<p>(The version of the license under which you publish. For example: 4.0)</p>';

    }

        /**
         * Render the setting for the license URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_url_setting() {

        $this->render_single_line_field('license_url');
        echo '<p>(The url under which the license can be found. For example : https://creativecommons.org/licenses/by/4.0/)</p>';

    }

        /**
         * Render the setting for the text appearing in the license statement.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_explanation_setting() {

        $this->render_single_line_field('license_explanation');
        echo '<p>(This will be displayed at the end of the license statement on the individual pages of your publications.)</p>';

    }

        /**
         * Render the setting for the country of the publisher.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_country_setting() {

        $this->render_single_line_field('publisher_country');
        echo '<p>(The country in which your publisher is registered.)</p>';

    }

        /**
         * Render the setting for the CorssRef id.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_id_setting() {

        $this->render_single_line_field('crossref_id');
        echo '<p>(Your Crossref ID.)</p>';

    }

        /**
         * Render the setting for the CorssRef password.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_pw_setting() {

        $this->render_password_field('crossref_pw');
        echo '<p>(Your Crossref password.)</p>';

    }

        /**
         * Render the setting for the url to query to retrieve citing articles.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_get_forward_links_url_setting() {

        $this->render_single_line_field('crossref_get_forward_links_url');
        echo '<p>(The url from which <a href="https://www.crossref.org/services/cited-by/">Crossref cited-by data</a> can be obtained.)</p>';

    }

        /**
         * Render the setting for the CrossRef deposit URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_deposite_url_setting() {

        $this->render_single_line_field('crossref_deposite_url');
        echo '<p>(The url to use when depositing meta-data with Crossref.)</p>';

    }

        /**
         * Render the setting for the URl of the CrossRef deposit test system.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_test_deposite_url_setting() {

        $this->render_single_line_field('crossref_test_deposite_url');
        echo '<p>(This url is used in place of the real Crossref deposit url when registering dois if ' . $this->get_plugin_pretty_name() . ' is in test system mode. It should be the deposit url of Crossref\'s test system.)</p>';

    }

        /**
         * Render the setting for the email to submit to crossref.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_email_setting() {

        $this->render_single_line_field('crossref_email');
        echo '<p>(The email address to use as the depositor\'s email when registering DOIs.)</p>';

    }

        /**
         * Render the setting for the archives the primary journal is listed in for submitting meta-data to CrossRef.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_archive_locations_setting() {

        $this->render_single_line_field('crossref_archive_locations');
        echo '<p>(Comma separated list containing a subset of CLOCKSS, LOCKSS Portico, KB, DWT, Internet Archive, depending on which archives the primary journal\'s content is archived in.)</p>';

    }

        /**
         * Render the setting for the crossmark policy page.
         *
         * @since  0.4.0
         * @access public
         */
    public function render_crossref_crossmark_policy_page_doi_setting() {

        $this->render_single_line_field('crossref_crossmark_policy_page_doi');
        echo '<p>(DOI of the <a href="https://www.crossref.org/education/crossmark/crossmark-policy-page/">crossmark policy page</a>. You can register this special DOI <a href="https://apps.crossref.org/webDeposit/">here</a>.)</p>';

    }

        /**
         * Render the setting for the ads api search URL.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_ads_api_search_url_setting() {

        $this->render_single_line_field('ads_api_search_url');
        echo '<p>(The url of the ADS API from which cited-by information can be retrieved.)</p>';

    }

        /**
         * Render the setting for the ads api token.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_ads_api_token_setting() {

        $this->render_password_field('ads_api_token');
        echo '<p>(Your ADS API token.)</p>';

    }

        /**
         * Render the setting for the CLOCKSS ftp url.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_ftp_url_setting() {

        $this->render_single_line_field('clockss_ftp_url');
        echo '<p>(The CLOCKSS FTP server to use. Please enter the raw url without the leading ftp:// protocol specification.)</p>';

    }

        /**
         * Render the setting for the CLOCKSS username.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_username_setting() {

        $this->render_single_line_field('clockss_username');
        echo '<p>(Your CLOCKSS user name.)</p>';

    }

        /**
         * Render the setting for the CLOCKSS password.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_password_setting() {

        $this->render_password_field('clockss_password');
        echo '<p>(Your CLOCKSS password.)</p>';

    }


        /**
         * Render the setting for the URL of the DOAJ API.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_api_url_setting() {

        $this->render_single_line_field('doaj_api_url');
        echo '<p>(The url of the DOAJ API.)</p>';

    }

        /**
         * Render the setting for the DOAJ api key.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_api_key_setting() {

        $this->render_password_field('doaj_api_key');
        echo '<p>(Your DOAJ API password.)</p>';

    }

        /**
         * Render the setting for the language code for DOAJ.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_language_code_setting() {

        $this->render_single_line_field('doaj_language_code');
        echo '<p>(The language code corresponding to the language in which you publish.)</p>';

    }

        /**
         * Render the setting for the arXiv abstract URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_abs_prefix_setting() {

        $this->render_single_line_field('arxiv_url_abs_prefix');
        echo '<p>(The url prefix of arXiv abstract pages.)</p>';

    }

        /**
         * Render the setting for the arXiv pdf URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_pdf_prefix_setting() {

        $this->render_single_line_field('arxiv_url_pdf_prefix');
        echo '<p>(The url prefix for arXiv pdf downloads.)</p>';

    }

        /**
         * Render the setting for the arXiv source URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_source_prefix_setting() {

        $this->render_single_line_field('arxiv_url_source_prefix');
        echo '<p>(The url prefix for arXiv source downloads.)</p>';

    }

        /**
         * Render the setting for the arXiv trackback prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_trackback_prefix_setting() {

        $this->render_single_line_field('arxiv_url_trackback_prefix');
        echo '<p>(The url prefix for sending Trackbacks to the arXiv.)</p>';

    }

        /**
         * Render the setting for the DOI feed identifier for the arXiv.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_doi_feed_identifier_setting() {

        $this->render_single_line_field('arxiv_doi_feed_identifier');
        echo '<p>(The arXiv can automatically update the journal reference of preprints based on information provided by publishers in the form of an xml feed as described <a href="https://arxiv.org/help/bib_feed">here</a>. For this to work, you must pick an identifier (specified via this setting) and inform the arXiv about the url under which the feed is available (can be configured in the following setting).)</p>';

    }

        /**
         * Render the setting for the DOI feed endpoint for the arXiv.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_arxiv_paper_doi_feed_endpoint_setting() {

        $this->render_single_line_field('arxiv_paper_doi_feed_endpoint');
        $settings = O3PO_Settings::instance();
        $endpoint_suffix = $settings->get_field_value('arxiv_paper_doi_feed_endpoint');
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

        $this->render_single_line_field('arxiv_paper_doi_feed_days');
        echo '<p>(Show publications up to this many days in the past in the doi feed.)</p>';

    }

        /**
         * Render the setting for the CrossRef DOI resolution url prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doi_url_prefix_setting() {

        $this->render_single_line_field('doi_url_prefix');
        echo '<p>(The url prefix under which publications can be linked via their DOI.)</p>';

    }

        /**
         * Render the setting for the Scholastica manuscript page setting.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_scholastica_manuscripts_url_setting() {

        $this->render_single_line_field('scholastica_manuscripts_url');
        echo '<p>(If you are using Scholastica as a peer-review platform, you may put here the url of the page on which Scholastica shows all your accepted manuscripts. This link will be displayed in the step-by-step publication guide on the admin panel for easy verification of the acceptance status of manuscripts before publication.)</p>';

    }

        /**
         * Render the setting for the Scirate abstract URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_scirate_url_abs_prefix_setting() {

        $this->render_single_line_field('scirate_url_abs_prefix');
        echo '<p>(The url prefix of scirate abstract pages. If left blank no link to scirate is put on the publication pages.)</p>';

    }


        /**
         * Render the setting for the arXiv vanity URL prefix.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_arxiv_vanity_url_prefix_setting() {

        $this->render_single_line_field('arxiv_vanity_url_prefix');
        echo '<p>(The url prefix of arXiv vanity pages. If left blank no link to arXiv Vanity is put on the publication pages.)</p>';

    }

        /**
         * Render the setting for the ORCID URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_orcid_url_prefix_setting() {

        $this->render_single_line_field('orcid_url_prefix');
        echo '<p>(The url prefix of the pages of individual ORCIDs.)</p>';

    }

        /**
         * Render the setting for the URL prefix of Fermat's library.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_fermats_library_url_prefix_setting() {

        $this->render_single_line_field('fermats_library_url_prefix');
        echo '<p>(The url prefix under which papers are published on Fermat\'s library.)</p>';

    }


    public function render_fermats_library_about_url_setting() {

        $this->render_single_line_field('fermats_library_about_url');
        echo '<p>(The url of Fermat\'s library about page.)</p>';

    }


        /**
         * Render the setting for the email of Fermt's library.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_fermats_library_email_setting() {

        $this->render_single_line_field('fermats_library_email');
        echo '<p>(The address to which emails to fermat\'s library should be sent when notifying them of a newly published paper.)</p>';

    }

        /**
         * Render the setting for the MathJax URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_mathjax_url_setting() {

        $this->render_single_line_field('mathjax_url');
        echo '<p>(The url of the version of MathJax.js to use.)</p>';

    }

        /**
         * Render the setting for the URL of the default thubnail for social media.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_social_media_thumbnail_url_setting() {

        $this->render_single_line_field('social_media_thumbnail_url');
        echo '<p>(Full url of a suitable image file. For posts and pages that do not have a dedicated feature image, this image is offered via meta-tags to social media platforms when a link to that post/page is shared.)</p>';

    }

        /**
         * Render the setting for the Facebook App Id.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_facebook_app_id_setting() {

        $this->render_single_line_field('facebook_app_id');
        echo '<p>(Your facebook_app_id, in case you have and want to use one.)</p>';

    }

        /**
         * Render the setting for the Buffer.com api url.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_buffer_api_url_setting() {

        $this->render_single_line_field('buffer_api_url');
        echo '<p>(Url of the buffer.com api.)</p>';

    }

        /**
         * Render the setting for the Buffer.com access token.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_buffer_access_token_setting() {

        $this->render_password_field('buffer_access_token');
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

        if(empty($this->get_field_value('buffer_access_token')))
            $profile_id_help = 'Save the settings after entering a valid access token above, to get a list of available profile ids under your account.';
        else{
            $buffer_profile_information = O3PO_Buffer::get_profile_information($this->get_field_value('buffer_api_url'), $this->get_field_value('buffer_access_token'));

            if(is_wp_error($buffer_profile_information))
                $profile_id_help = 'There was an error when trying to obtain the available profile ids for the provided access token: ' . $buffer_profile_information->get_error_message();
            else{
                $profile_id_help = 'The available services and profile ids are:';
                foreach($buffer_profile_information as $info)
                    $profile_id_help .= ' ' . $info['service'] . ":" . $info['id'];
            }
        }

        $this->render_array_as_comma_separated_list_field('buffer_profile_ids');
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

        $this->render_single_line_field('relevanssi_mime_types_to_exclude');
        echo '<p>(Relevanssi Premium has the ability to index the content of attachments and thereby, e.g., enabled full text search in PDFs attached to publications. It however, by default, will index all attachment types and this is usually not desirable for the arXiv source files in .tex or .tar.gz format. Through this setting, mime types can be excluded from indexing by providing a php regular expression. All attachment posts whose mime type matches that regular expression are excluded from indexing via the <a href="https://www.relevanssi.com/knowledge-base/controlling-attachment-types-index/">relevanssi_do_not_index</a> filter. If left empty all post attachments are indexed if that feature is enable in Relevanssi Premium.)</p>';

    }

        /**
         * Render the setting for whether to index pdf asynchronously.
         *
         * @since    0.3.0
         * @access   public
         */
    public function render_relevanssi_index_pdfs_asynchronously_setting() {

        $this->render_checkbox_field('relevanssi_index_pdfs_asynchronously', 'Index pdfs after full text first requested via pdf endpoint');
        echo '<p>(Relevanssi Premium has the ability to index the content of attachments and thereby, e.g., enabled full text search in PDFs attached to publications. The indexing however happens on another server and is thus slow when done during the publishing of a publication post. Checking this box allows to do the indexing instead in the background after the full text pdf has first been requested via the pdf endpoint.)</p>';

    }

        /**
         * Trim user input to special fields connected to rewrite rules.
         *
         * Ensure the rewrite rules are flushed when such setting is changed.
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function trim_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed( $id, $input ) {

        $input = trim($input);
        if(empty($input))
        {
            $this->add_error( $id, 'must-not-be-empty', "The field '" . $this->fields[$id]['title'] . "' must not be empty. Field reset.", 'error');
            $input = $this->get_field_default($id);
        }
        if(empty($input))
            $input = $this->get_field_default($id);

        if($input !== $this->get_field_value($id))
        {
                /*
                 * In render_settings_page() we check for transient and
                 * flush rewrite rules there if it is set and True.
                 */
            set_transient($this->plugin_name . '-' . $this->slug . '-rewrite-rules-affected', True);
            $this->add_error( $id, 'rewrite-rules-affected', "The rewrite rules have been updated because the field '" . $this->fields[$id]['title'] . "' was changed.", 'updated');
        }

        return $input;
    }

        /**
         * Validate settings.
         *
         * @since  0.4.0
         * @access public
         * @param  array  $input Array of all given input values to validate with ids as keys.
         * @return array  Array of validated inputs.
         */
    public function validate_input( $input ) {

        $validated_input = array();
        foreach($this->fields as $id => $specification)
        {

            if(isset($input[$id]) and isset($specification['validation_callable']))
                $validated_input[$id] = call_user_func($specification['validation_callable'], $id, $input[$id]);
            else
                $validated_input[$id] = $this->get_field_value($id);
        }

        return $validated_input;
    }

        /**
         * Get the plugin_name.
         *
         * @since 0.3.0
         * @access public
         * @return string The name of the plugin.
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
         * Record errors during input verification.
         *
         * Calls through to WP's add_settings_error().
         *
         * @since 0.3.1
         * @access protected
         * @param string $setting Slug title of the setting to which this error applies.
         * @param string $code Slug-name to identify the error. Used as part of 'id' attribute in HTML output.
         * @param string $message The formatted message text to display to the user (will be shown inside styled <div> and <p> tags).
         * @param string $type Message type, controls HTML class. Possible values include 'error', 'success', 'warning', 'info'. Default value: 'error'
         */
    protected function add_error( $setting, $code, $message, $type='error' ) {

        add_settings_error($setting, $code, $message, $type);

    }

}


/**
 * Interface for classes specifying settings.
 *
 * @since      0.4.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
interface O3PO_SettingsSpecifyer  {

    static function specify_settings( $settings );

}
