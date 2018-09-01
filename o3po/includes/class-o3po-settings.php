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
 * @link       http://example.com
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-singleton.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-email-templates.php';


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
        'arxiv_doi_feed_identifier' => 'arxiv_paper_doi_feed',
        'arxiv_url_abs_prefix' => 'https://arxiv.org/abs/',
        'arxiv_url_pdf_prefix' => 'https://arxiv.org/pdf/',
        'arxiv_url_source_prefix' => 'https://arxiv.org/e-print/',
        'arxiv_url_trackback_prefix' => 'http://arxiv.org/trackback/',
        'doi_url_prefix' => 'https://doi.org/',
        'scirate_url_abs_prefix' => 'https://scirate.com/arxiv/',
        'orcid_url_prefix' => 'https://orcid.org/',
        'fermats_library_url_prefix' => 'https://fermatslibrary.com/s/',
        'doaj_api_url' => "https://doaj.org/api/v1/articles",
        'doaj_language_code' => "EN",
        'custom_search_page' => "checked",

        'self_notification_subject_template' => 
                 "A [publication_type_name] has been published/updated by [journal]",
        'self_notification_body_template' => 
                 "[journal] has published/updated the following [publication_type_name]\n".
                 "Title:   [title] \n".
                 "Authors: [authors] \n".
                 "URL:     [url]\n".
                 "DOI:     [doi]\n",
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
                 "DOI:       [doi]\n".
                 "\n".
                 "Please post it on Fermat's library under the permalink: [fermats_library_permalink]\n".
                 "Thank you very much!\n\n".
                 "Kind regards,\n\n".
                 "The Executive Board\n",

            /* The options below are currently not customizable.
             *
             * Warning: The name of the paper-single.php templare must match
             * the primary_publication_type_name!
             */
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

        echo '<div>';
        echo '<h1>' . $this->plugin_pretty_name . ' settings (version ' . $this->version . ')</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields($this->plugin_name . '-setttings');
        do_settings_sections('plugin_settings');
        do_settings_sections('journal_settings');
        do_settings_sections('email_settings');
        do_settings_sections('crossref_settings');
        do_settings_sections('clockss_settings');
        do_settings_sections('doaj_settings');
        do_settings_sections('arxiv_settings');
        do_settings_sections('other_service_settings');
        echo '<input name="Submit" type="submit" value="Save Settings" />';
        echo '</form></div>';

    }

        /**
         * Register all the all the settings.
         *
         * To be added to the 'admin_init' action.
         *
         * @since    0.1.0
         * @access   public
         */
    public function register_settings() {

        register_setting( $this->plugin_name . '-setttings', $this->plugin_name . '-setttings', array( $this, 'validate_settings' ) );

        add_settings_section('plugin_settings', 'Plugin settings', array( $this, 'render_plugin_settings' ), 'plugin_settings');
        add_settings_field('production_site_url', 'Production site url', array( $this, 'render_production_site_url_setting' ), 'plugin_settings', 'plugin_settings');
        add_settings_field('custom_search_page', 'Use custom search page', array( $this, 'render_custom_search_page_setting' ), 'plugin_settings', 'plugin_settings');

        add_settings_section('journal_settings', 'Journal settings', array( $this, 'render_journal_settings' ), 'journal_settings');
        add_settings_field('doi_prefix', 'Doi prefix', array( $this, 'render_doi_prefix_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('journal_title', 'Journal title', array( $this, 'render_journal_title_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('journal_subtitle', 'Journal subtitle', array( $this, 'render_journal_subtitle_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('journal_description', 'Journal description', array( $this, 'render_journal_description_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('journal_level_doi_suffix', 'Journal level doi suffix', array( $this, 'render_journal_level_doi_suffix_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('eissn', 'eISSN', array( $this, 'render_eissn_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('publisher', 'Publisher', array( $this, 'render_publisher_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('secondary_journal_title', 'Secondary journal title', array( $this, 'render_secondary_journal_title_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('secondary_journal_level_doi_suffix', 'Secondary journal level doi suffix', array( $this, 'render_secondary_journal_level_doi_suffix_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('secondary_journal_eissn', 'Secondary journal eISSN', array( $this, 'render_secondary_journal_eissn_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('developer_email', 'Email of developer', array( $this, 'render_developer_email_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('publisher_email', 'Email of publisher', array( $this, 'render_publisher_email_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('first_volume_year', 'Year of first volume', array( $this, 'render_first_volume_year_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('publisher_country', 'Country of publisher', array( $this, 'render_publisher_country_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('license_name', 'License name', array( $this, 'render_license_name_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('license_type', 'License type', array( $this, 'render_license_type_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('license_version', 'License version', array( $this, 'render_license_version_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('license_url', 'License url', array( $this, 'render_license_url_setting' ), 'journal_settings', 'journal_settings');
        add_settings_field('license_explanation', 'License explanation string', array( $this, 'render_license_explanation_setting' ), 'journal_settings', 'journal_settings');
        
        add_settings_section('email_settings', 'Email settings', array($this , 'render_email_settings'), 'email_settings');
        add_settings_field('self_notification_subject_template', 'Self notification subject template', array($this, 'render_self_notification_subject_template_settings'),'email_settings', 'email_settings');
        add_settings_field('self_notification_body_template', 'Self notification body template', array($this, 'render_self_notification_body_template_settings'), 'email_settings', 'email_settings');
        add_settings_field('author_notification_subject_template', 'Self author notification subject template', array($this, 'render_author_notification_subject_template_settings'), 'email_settings', 'email_settings');
        add_settings_field('author_notification_body_template' , 'Author notification body template' , array($this, 'render_author_notification_body_template_settings') , 'email_settings', 'email_settings');
        add_settings_field('author_notification_secondary_body_template' , 'Author notification secondary body template' , array($this, 'render_author_notification_secondary_body_template_settings') , 'email_settings', 'email_settings');
        add_settings_field('fermats_library_subject_template' , 'Fermats library subject template' , array($this, 'render_fermats_library_subject_template_settings') , 'email_settings', 'email_settings');
        add_settings_field('fermats_library_body_template' , 'Fermats library body template' , array($this, 'render_fermats_library_body_template_settings') , 'email_settings', 'email_settings');

        add_settings_section('crossref_settings', 'Crossref settings', array( $this, 'render_crossref_settings' ), 'crossref_settings');
        add_settings_field('crossref_id', 'Crossref ID', array( $this, 'render_crossref_id_setting' ), 'crossref_settings', 'crossref_settings');
        add_settings_field('crossref_pw', 'Crossref password', array( $this, 'render_crossref_pw_setting' ), 'crossref_settings', 'crossref_settings');
        add_settings_field('crossref_get_forward_links_url', 'Crossref get forward links url', array( $this, 'render_crossref_get_forward_links_url_setting' ), 'crossref_settings', 'crossref_settings');
        add_settings_field('crossref_deposite_url', 'Crossref deposite url', array( $this, 'render_crossref_deposite_url_setting' ), 'crossref_settings', 'crossref_settings');
        add_settings_field('crossref_test_deposite_url', 'Crossref deposite url for testing', array( $this, 'render_crossref_test_deposite_url_setting' ), 'crossref_settings', 'crossref_settings');
        add_settings_field('crossref_email', 'Email for communication with Crossref', array( $this, 'render_crossref_email_setting' ), 'crossref_settings', 'crossref_settings');
        add_settings_field('crossref_archive_locations', 'Archive locations', array( $this, 'render_crossref_archive_locations_setting' ), 'crossref_settings', 'crossref_settings');

        add_settings_section('clockss_settings', 'Clockss settings', array( $this, 'render_clockss_settings' ), 'clockss_settings');
        add_settings_field('clockss_ftp_url', 'Clockss FTP URL', array( $this, 'render_clockss_ftp_url_setting' ), 'clockss_settings', 'clockss_settings');
        add_settings_field('clockss_username', 'Clockss Username', array( $this, 'render_clockss_username_setting' ), 'clockss_settings', 'clockss_settings');
        add_settings_field('clockss_password', 'Clockss Password', array( $this, 'render_clockss_password_setting' ), 'clockss_settings', 'clockss_settings');

        add_settings_section('doaj_settings', 'DOAJ settings', array( $this, 'render_doaj_settings' ), 'doaj_settings');
        add_settings_field('doaj_api_url', 'DOAJ API url', array( $this, 'render_doaj_api_url_setting' ), 'doaj_settings', 'doaj_settings');
        add_settings_field('doaj_api_key', 'DOAJ API key', array( $this, 'render_doaj_api_key_setting' ), 'doaj_settings', 'doaj_settings');
        add_settings_field('doaj_language_code', 'DOAJ langugage code (two upper case letters)', array( $this, 'render_doaj_language_code_setting' ), 'doaj_settings', 'doaj_settings');

        add_settings_section('arxiv_settings', 'ArXiv settings', array( $this, 'render_arxiv_settings' ), 'arxiv_settings');
        add_settings_field('arxiv_url_abs_prefix', 'Url prefix for abstract pages', array( $this, 'render_arxiv_url_abs_prefix_setting' ), 'arxiv_settings', 'arxiv_settings');
        add_settings_field('arxiv_url_pdf_prefix', 'Url prefix for pdfs', array( $this, 'render_arxiv_url_pdf_prefix_setting' ), 'arxiv_settings', 'arxiv_settings');
        add_settings_field('arxiv_url_source_prefix', 'Url prefix for eprint source', array( $this, 'render_arxiv_url_source_prefix_setting' ), 'arxiv_settings', 'arxiv_settings');
        add_settings_field('arxiv_url_trackback_prefix', 'Url prefix for trackbacks', array( $this, 'render_arxiv_url_trackback_prefix_setting' ), 'arxiv_settings', 'arxiv_settings');
        add_settings_field('arxiv_doi_feed_identifier', 'Indentifier for the doi feed', array( $this, 'render_arxiv_doi_feed_identifier_setting' ), 'arxiv_settings', 'arxiv_settings');

        add_settings_section('other_service_settings', 'Settings for other services', array( $this, 'render_other_service_settings' ), 'other_service_settings');
        add_settings_field('doi_url_prefix', 'Url prefix for doi resolution', array( $this, 'render_doi_url_prefix_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('scirate_url_abs_prefix', 'Url prefix for scirate pages', array( $this, 'render_scirate_url_abs_prefix_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('scholastica_manuscripts_url', 'Url of Scholastica manuscripts page', array( $this, 'render_scholastica_manuscripts_url_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('orcid_url_prefix', 'Orcid url prefix', array( $this, 'render_orcid_url_prefix_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('fermats_library_url_prefix', 'Url prefix for Fermats Library', array( $this, 'render_fermats_library_url_prefix_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('fermats_library_email', 'Email for Fermats Library', array( $this, 'render_fermats_library_email_setting' ), 'other_service_settings', 'other_service_settings');

        add_settings_field('mathjax_url', 'MathJax url', array( $this, 'render_mathjax_url_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('social_media_thumbnail_url', 'Url of default thumbnail for social media', array( $this, 'render_social_media_thumbnail_url_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('facebook_app_id', 'Facebook app_id', array( $this, 'render_facebook_app_id_setting' ), 'other_service_settings', 'other_service_settings');
        add_settings_field('buffer_secret_email', 'Secret email for adding posts to buffer.com', array( $this, 'render_buffer_secret_email_setting' ), 'other_service_settings', 'other_service_settings');
    }

        /**
         * Render the head of the plugin settings page.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_plugin_settings() {
        echo '<p>Settings of the plugin.</p>';
    }

    /**
     * Render the head of the plugin settings page.
     *
     * @since    0.2.2
     * @access   public
     */
    public function render_email_settings() {
        echo '<p> Settings of email templates.</p>';
    }

        /**
         * Render the head of the journal settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_settings() {
        echo '<p>Settings defining the journal.</p>';
    }

        /**
         * Render the head of the crossref settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_settings() {
        echo '<p>Settings for interaction with Crossref.</p>';
    }

        /**
         * Render the head of the clockss settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_settings() {
        echo '<p>Settings for interaction with CLOCKSS.</p>';
    }

        /**
         * Render the head of the DOAJ settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_settings() {
        echo '<p>Settings for interaction with the DOAJ.</p>';
    }

        /**
         * Render the head of the arXiv settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_settings() {
        echo '<p>Settings for arXiv.org.</p>';
    }

        /**
         * Render the head of the other services settings part.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_other_service_settings() {
        echo '<p>Settings for other services.</p>';
    }

        /**
         * Render the setting for the production site url.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_production_site_url_setting() {

        $this->render_setting('production_site_url');
        echo '<p>(Unless this field is filled and matches the string ' . esc_html(get_site_url())  . ' this instance will be considered a test system and the interfaces with various critical services will remain disabled.)</p>';

    }

        /**
         * Render the setting for whether to show the custom search page.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_custom_search_page_setting() {

        $this->render_checkbox_setting('custom_search_page', 'Uncheck to disable the custom search page with extra information for users lookig for a published paper that is provided by this plugin.');

    }

        /**
         * Render the setting for the DOI prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doi_prefix_setting() {
        $this->render_setting('doi_prefix');
    }

        /**
         * Render the setting for the journal title of the primary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_title_setting() {
        $this->render_setting('journal_title');
    }

        /**
         * Render the setting for the subtitle of the primary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_subtitle_setting() {
        $this->render_setting('journal_subtitle');
    }

        /**
         * Render the setting for the journal description.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_description_setting() {
        $this->render_setting('journal_description');
    }

        /**
         * Render the setting for the journal level DOI suffix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_journal_level_doi_suffix_setting() {
        $this->render_setting('journal_level_doi_suffix');
    }

        /**
         * Render the setting for the EISSN.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_eissn_setting() {
        $this->render_setting('eissn');
    }

        /**
         * Render the setting for the publisher name.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_setting() {
        $this->render_setting('publisher');
    }

        /**
         * Render the setting for the title of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_title_setting() {
        $this->render_setting('secondary_journal_title');
    }

        /**
         * Render the setting for the journal level DOI suffix of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_level_doi_suffix_setting() {
        $this->render_setting('secondary_journal_level_doi_suffix');
    }

        /**
         * Render the setting for the EISSN of the secondary journal.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_secondary_journal_eissn_setting() {
        $this->render_setting('secondary_journal_eissn');
        echo '<p>(It is OK to leave this blank, but DOAJ, for example will not accept meta-date on articles in the secondary journal if this is not set. Do not set it equal to the primary eISSN, as works in both journals will be treated on an equal footing when it comes to citation counting.)</p>';
    }

        /**
         * Render the setting for the email of the developer.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_developer_email_setting() {
        $this->render_setting('developer_email');
    }

        /**
         * Render the setting for the email of the publisher.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_email_setting() {
        $this->render_setting('publisher_email');
    }

        /**
         * Render the setting for the year of the first volume.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_first_volume_year_setting() {
        $this->render_setting('first_volume_year');
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
        $this->render_setting('self_notification_body_template');
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
        $this->render_setting('author_notification_body_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_body');
    }

    /**
     * Render the email template for the author notification secondary body
     *
     * @since    0.2.2
     * @access   public
     */
    public function render_author_notification_secondary_body_template_settings() {
        $this->render_setting('author_notification_secondary_body_template');
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
        $this->render_setting('fermats_library_notification_body_template');
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
        echo '<p>(For example: Creative Commons Attribution 4.0 International (CC BY 4.0))</p>';
    }

        /**
         * Render the setting for the license type.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_type_setting() {
        $this->render_setting('license_type');
        echo '<p>(For example: CC BY)</p>';
    }

        /**
         * Render the setting for the license version.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_version_setting() {
        $this->render_setting('license_version');
        echo '<p>(For example: 4.0)</p>';
    }

        /**
         * Render the setting for the license URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_url_setting() {
        $this->render_setting('license_url');
    }

        /**
         * Render the setting for the text appearing in the license statement.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_license_explanation_setting() {
        $this->render_setting('license_explanation');
        echo '<p>(This will be displayed at the end of the license statement.)</p>';
    }

        /**
         * Render the setting for the country of the publisher.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_publisher_country_setting() {
        $this->render_setting('publisher_country');
    }

        /**
         * Render the setting for the CorssRef id.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_id_setting() {
        $this->render_setting('crossref_id');
    }

        /**
         * Render the setting for the CorssRef password.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_pw_setting() {
        $this->render_password_setting('crossref_pw');
    }

        /**
         * Render the setting for the url to query to retrieve citing articles.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_get_forward_links_url_setting() {
        $this->render_setting('crossref_get_forward_links_url');
    }

        /**
         * Render the setting for the CrossRef deposit URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_deposite_url_setting() {
        $this->render_setting('crossref_deposite_url');
    }

        /**
         * Render the setting for the URl of the CrossRef deposit test system.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_test_deposite_url_setting() {
        $this->render_setting('crossref_test_deposite_url');
    }

        /**
         * Render the setting for the email to submit to crossref.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_email_setting() {
        $this->render_setting('crossref_email');
    }

        /**
         * Render the setting for the archives the primary journal is listed in for submitting meta-data to CrossRef.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_crossref_archive_locations_setting() {
        $this->render_setting('crossref_archive_locations');
        echo '<p>(Please put a comma seperated list containing a subset of CLOCKSS, LOCKSS Portico, KB, DWT, Internet Archive, depending on the kind of archive the primary journal\'s content is archived in.)</p>';
    }

        /**
         * Render the setting for the CLOCKSS ftp url.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_ftp_url_setting() {
        $this->render_setting('clockss_ftp_url');
    }

        /**
         * Render the setting for the CLOCKSS username.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_username_setting() {
        $this->render_setting('clockss_username');
    }

        /**
         * Render the setting for the CLOCKSS password.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_clockss_password_setting() {
        $this->render_password_setting('clockss_password');
    }


        /**
         * Render the setting for the URL of the DOAJ API.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_api_url_setting() {
        $this->render_setting('doaj_api_url');
    }

        /**
         * Render the setting for the DOAJ api key.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_api_key_setting() {
        $this->render_password_setting('doaj_api_key');
    }

        /**
         * Render the setting for the language code for DOAJ.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doaj_language_code_setting() {
        $this->render_setting('doaj_language_code');
    }

        /**
         * Render the setting for the arXiv abstract URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_abs_prefix_setting() {
        $this->render_setting('arxiv_url_abs_prefix');
    }

        /**
         * Render the setting for the arXiv pdf URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_pdf_prefix_setting() {
        $this->render_setting('arxiv_url_pdf_prefix');
    }

        /**
         * Render the setting for the arXiv source URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_source_prefix_setting() {
        $this->render_setting('arxiv_url_source_prefix');
    }

        /**
         * Render the setting for the arXiv trackback prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_url_trackback_prefix_setting() {
        $this->render_setting('arxiv_url_trackback_prefix');
    }

        /**
         * Render the setting for the DOI feed identifier for the arXiv.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_arxiv_doi_feed_identifier_setting() {
        $this->render_setting('arxiv_doi_feed_identifier');
    }

        /**
         * Render the setting for the CrossRef DOI resolution url prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_doi_url_prefix_setting() {
        $this->render_setting('doi_url_prefix');
    }

        /**
         * Render the setting for the Scholastica manuscript page setting.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_scholastica_manuscripts_url_setting() {
        $this->render_setting('scholastica_manuscripts_url');
    }

        /**
         * Render the setting for the Scirate abstract URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_scirate_url_abs_prefix_setting() {
        $this->render_setting('scirate_url_abs_prefix');
    }

        /**
         * Render the setting for the ORCID URL prefix.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_orcid_url_prefix_setting() {
        $this->render_setting('orcid_url_prefix');
    }

        /**
         * Render the setting for the URL prefix of Fermat's library.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_fermats_library_url_prefix_setting() {
        $this->render_setting('fermats_library_url_prefix');
    }

        /**
         * Render the setting for the email of Fermt's library.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_fermats_library_email_setting() {
        $this->render_setting('fermats_library_email');
    }

        /**
         * Render the setting for the MathJax URL.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_mathjax_url_setting() {
        $this->render_setting('mathjax_url');
    }

        /**
         * Render the setting for the URL of the default thubnail for social media.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_social_media_thumbnail_url_setting() {
        $this->render_setting('social_media_thumbnail_url');
    }

        /**
         * Render the setting for the Facebook App Id.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_facebook_app_id_setting() {
        $this->render_setting('facebook_app_id');
    }

        /**
         * Render the setting for the Buffer.com secret email.
         *
         * @since    0.1.0
         * @access   public
         */
    public function render_buffer_secret_email_setting() {

        $this->render_password_setting('buffer_secret_email');
        $post_types = O3PO_Utility::oxford_comma_implode(call_user_func($this->active_post_type_names_callback));
        echo '<p>(If this is set, new ' . $post_types . ' posts are <a target="_blank" href="https://faq.buffer.com/article/272-is-it-possible-to-add-a-post-to-buffer-through-email">automatically submitted</a> to the buffer.com queue associated with the secret email)</p>';

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

        echo '<input type="text" id="' . $this->plugin_name . '-setttings-' . $id . '" name="' . $this->plugin_name . '-setttings[' . $id . ']" style="width: 80%" value="' . $option . '" />';

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

        echo '<input type="password" id="' . $this->plugin_name . '-setttings-' . $id . '" name="' . $this->plugin_name . '-setttings[' . $id . ']" style="width: 80%" value="' . $option . '" />';
        echo '<input type="checkbox" onclick="(function myFunction() {
    var x = document.getElementById(\'' . $this->plugin_name . '-setttings-' . $id . '\');
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

        echo '<input type="hidden" name="' . $this->plugin_name . '-setttings[' . $id . ']" value="unchecked">'; //To have a 0 in POST when the checkbox is unticked
        echo '<input type="checkbox" id="' . $this->plugin_name . '-setttings-' . $id . '" name="' . $this->plugin_name . '-setttings[' . $id . ']" value="checked"' . checked( 'checked', $option, false ) . '/>';
        echo '<label for="' . $this->plugin_name . '-setttings-' . $id . '">' . $label . '</label>';

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
                'clockss_ftp_url' => 'trim',
                'clockss_username' => 'trim',
                'clockss_password' => 'trim',
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
                'doi_prefix' => array($this, 'clean_doi_prefix'),
                'eissn' => array($this, 'clean_eissn'),
                'secondary_journal_eissn' => array($this, 'clean_secondary_journal_eissn'),
                'first_volume_year' => array($this, 'clean_first_volume_year'),
                                                   );

        return self::$all_settings_fields_map;
    }

        /**
         * Cleam user input to the doi_prefix setting
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $doi_prefix    User input.
         */
    private function clean_doi_prefix( $doi_prefix ) {

        $doi_prefix = trim($doi_prefix);
        if(preg_match('/^[0-9.]*$/', $doi_prefix))
            return $doi_prefix;
        else
            "";
    }

        /**
         * Cleam user input to the eissn setting
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $eissn    User input.
         */
    private function clean_eissn( $eissn ) {

        $eissn = trim($eissn);
        if(preg_match('/^[0-9]{4}-[0-9]{3}[0-9xX]$/', $eissn))
            return $eissn;
        else
            return "";
    }

        /**
         * Cleam user input to the secondary_journal_eissn setting
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $secondary_journal_eissn    User input.
         */
    private function clean_secondary_journal_eissn( $secondary_journal_eissn ) {
        $secondary_journal_eissn = trim($secondary_journal_eissn);
        if(preg_match('/^[0-9]{4}-[0-9]{3}[0-9xX]$/', $secondary_journal_eissn))
            return $secondary_journal_eissn;
        else
            return "";
    }

        /**
         * Cleam user input to the first_volume_year setting
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $first_volume_year    User input.
         */
    private function clean_first_volume_year( $first_volume_year ) {

        $first_volume_year = trim($first_volume_year);
        if(preg_match('/^[0-9]{4}$/', $first_volume_year)) //this will cause a year 10000 bug
            return $first_volume_year;
        else
            return "";
    }

        /**
         * Validate settings.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $input    Value of the setting to validate.
         */
    public function validate_settings( $input ) {

        foreach($this->get_all_settings_fields_map() as $field => $callable)
        {
            if(isset($input[$field]))
                $newinput[$field] = call_user_func($callable, $input[$field]);
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

        $options = get_option($this->plugin_name . '-setttings');
        if(!empty($options[$id]))
            return $options[$id];

        $options = get_option('quantum-journal-plugin-setttings');
        if(!empty($options[$id]))
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

}
