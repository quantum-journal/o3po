<?php

/**
 * Class representing the email templates
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-shortcode-template.php';

/**
 * Class representing the email templates
 *
 * @since      0.3.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Johannes Drever <drever@lrz.uni-muenchen.de>
 */
class O3PO_EmailTemplates implements O3PO_SettingsSpecifyer {


        /**
         * Array of templates.
         *
         * Used to cache templates after they were first requested.
         *
         * @since  0.4.0
         * @access private
         * @var    array   $templates Array of templates used for caching.
         */
    private static $templates = array();

        /**
         * Get a template by name.
         *
         * @since  0.4.0
         * @access public
         * @param  string                 $template_name The name of the template.
         * @return O3PO_ShortcodeTemplate The requested template.
         */
    public static function get_template( $template_name ) {

        if(isset(static::$templates[$template_name]))
            return static::$templates[$template_name];

        $settings = O3PO_Settings::instance();

        switch($template_name) {
            case 'self_notification_subject':
                $template = new O3PO_ShortcodeTemplate(
                    $settings->get_field_value($template_name . '_template'),
                    array("[journal]" => array(
                              'description' => "The journal name",
                              'example' => 'Some Journal',
                                               ),
                          "[publication_type_name]" => array(
                              'description' => "The type of the publication",
                              'example' => 'paper',
                                                             ),
                          )
                                                       );
                break;
            case 'self_notification_body':
                $template = new O3PO_ShortcodeTemplate(
                    $settings->get_field_value($template_name . '_template'),
                    array(
                        "[journal]" => array(
                            'description' => "The journal name",
                            'example' => 'Some Journal'),
                        "[publication_type_name]" => array(
                            'description' => "The type of the publication",
                            'example' => 'paper'),
                        "[title]" => array(
                            'description' => "The title of the publication",
                            'example' => 'A title'),
                        "[authors]" => array(
                            'description' => "The list of authors",
                            'example' => 'A. Foo, B. Bar, and C. Baz'),
                        "[url]" => array(
                            'description' => "The url where the publication can be found",
                            'example' => 'https://some.url/12345/'),
                        "[doi_url_prefix]" => array(
                            'description' => "The DOI url prefix",
                            'example' => 'https://doi.org/'),
                        "[doi]" => array(
                            'description' => "The DOI",
                            'example' => 'q-2017-06-01-152'),
                          ));
                break;
            case 'author_notification_subject':
            case 'author_notification_secondary_subject':
                $template = new O3PO_ShortcodeTemplate(
                    $settings->get_field_value($template_name . '_template'),
                    array(
                        "[journal]" => array(
                            'description' => "The journal name",
                            'example' => 'Some Journal'),
                        "[publication_type_name]" => array(
                            'description' => "The type of the publication",
                            'example' => 'paper'),
                          ));
                break;
            case 'author_notification_body':
            case 'author_notification_secondary_body':
                $template = new O3PO_ShortcodeTemplate(
                    $settings->get_field_value($template_name . '_template'),
                    array("[journal]" => array(
                              'description' => "The journal name",
                              'example' => 'Some Journal'),
                          "[executive_board]" => array(
                              'description' => "Names of the executive board members",
                              'example' => 'Some Name, Another Name, and Diffeerent Name'),
                          "[editor_in_chief]" => array(
                              'description' => "Names of the editor in chief",
                              'example' => 'Some Name, Another Name, and Diffeerent Name'),
                          "[publisher_email]" => array(
                              'description' => "Email address of the publisher",
                              'example' => 'mail@publisher.tld'),
                          "[publication_type_name]" => array(
                              'description' => "The type of the publication",
                              'example' => 'paper'),
                          "[title]" => array(
                              'description' => "The title of the article",
                              'example' => 'A Title'),
                          "[authors]" => array(
                              'description' => "The names of the authors",
                              'example' => 'A. Foo, B. Bar, and C. Baz'),
                          "[post_url]" => array(
                              'description' => "The url where the publication can be found",
                              'example' => 'https://some.url/12345/'),
                          "[doi_url_prefix]" => array(
                              'description' => "The DOI url prefix",
                              'example' => 'https://doi.org/'),
                          "[doi]" => array(
                              'description' => "The DOI",
                              'example' => 'q-2017-06-01-152'),
                          "[journal_reference]" => array(
                              'description' => "The journal reference",
                              'example' => 'Some Journal, 74, 12351451 (2001)'),
                          ));
                break;
            case 'fermats_library_notification_subject':
                $template = new O3PO_ShortcodeTemplate(
                    $settings->get_field_value($template_name . '_template'),
                    array("[journal]" => array(
                              'description' => "The journal name",
                              'example' => 'Some Journal'),
                          "[publication_type_name]" => array(
                              'description' => "The type of the publication",
                              'example' => 'paper'),
                          ));
                break;
            case 'fermats_library_notification_body':
                $template = new O3PO_ShortcodeTemplate(
                    $settings->get_field_value($template_name . '_template'),
                    array(
                        "[journal]" => array(
                            'description' => "The journal name",
                            'example' => 'Some Journal'),
                        "[publication_type_name]" => array(
                            'description' => "The type of the publication",
                            'example' => 'paper'),
                        "[title]" => array(
                            'description' => "The title of the article",
                            'example' => 'A Title'),
                        "[authors]" => array(
                            'description' => "The list of the authors",
                            'example' => 'A. Foo, B. Bar, and C. Baz'),
                        "[post_url]" => array(
                            'description' => "The url where the publication can be found",
                            'example' => 'https://some.url/12345/'),
                        "[doi_url_prefix]" => array(
                            'description' => "The DOI url prefix",
                            'example' => 'https://doi.org/'),
                        "[doi]" => array(
                            'description' => "The DOI",
                            'example' => 'q-2017-06-01-152'),
                        "[fermats_library_permalink]" => array(
                            'description' => "The permalink in fermats library",
                            'example' => 'https://fermatslibrary.com/s/q-2017-06-01-152'),
                          ));
                break;
            default:
                throw new InvalidArgumentException('Template ' . $template_name . ' is not known.');
        }
        static::$templates[$template_name] = $template;

        return $template;
    }


        /**
         * Expand a email template and return the resulting text.
         *
         * @since  0.4.0
         * @access public
         * @param  string  $template_name                        The name of the template.
         * @param  array   $replacements                         Array of replacements for all or a subset
         *                                                       of the shotcodes in the template.
         * @param  boolean $error_if_not_all_appearing_specified Whether to throw an exception in case not for
         *                                                       all shortcodes appearing in the template
         *                                                       replacements are specified.
         * @return string  Expanded template with shortcodes replaced by replacements.
         */
    public static function expand( $template_name, $replacements, $error_if_not_all_appearing_specified=true ) {

        return static::get_template($template_name)->expand($replacements, $error_if_not_all_appearing_specified);
    }


        /**
         * Render the short codes for a specific template to an HTML list.
         *
         * @since  0.4.0
         * @access public
         * @param  string $template_name The name of the O3PO_EmailTemplates class method.
         * @return string An HTML list of the short codes.
         */
    public static function render_short_codes( $template_name ){

        $template = static::get_template($template_name);
        return $template->render_short_codes();
    }

        /**
         * Render the head of the email settings.
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_email_settings() {

        echo '<p>Configure the templates used for sending emails.</p>';

    }


        /**
         * Render the executive board of the email settings
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_executive_board_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_single_line_field('executive_board');
        echo('<p>(Names of the executive board of your journal. Set this if you want to use the [executive_board] shortcode in the email templates below.)</p>');

    }

        /**
         * Render the editor in chief of the email settings
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_editor_in_chief_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_single_line_field('editor_in_chief');
        echo('<p>(Name of the editor in chief. Set this if you want to use the [editor_in_chief] shortcode in the email templates below.)</p>');

    }


        /**
         * Render the email template for the self notification subject
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_self_notification_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_single_line_field('self_notification_subject_template');
        echo static::render_short_codes('self_notification_subject');

    }

        /**
         * Render the email template for the self notification body
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_self_notification_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_field('self_notification_body_template');
        echo static::render_short_codes('self_notification_body');

    }

        /**
         * Render the email template for the author notification subject
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_author_notification_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_single_line_field('author_notification_subject_template');
        echo static::render_short_codes('author_notification_subject');

    }

        /**
         * Render the email template for the author notification body
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_author_notification_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_field('author_notification_body_template');
        echo static::render_short_codes('author_notification_body');

    }

        /**
         * Render the email template for the author notification secondary subject
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_author_notification_secondary_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_field('author_notification_secondary_subject_template');
        echo static::render_short_codes('author_notification_subject');

    }

        /**
         * Render the email template for the author notification secondary body
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_author_notification_secondary_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_field('author_notification_secondary_body_template');
        echo static::render_short_codes('author_notification_body');

    }

        /**
         * Render the email template for the fermats library notification subject
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_fermats_library_notification_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_single_line_field('fermats_library_notification_subject_template');
        echo static::render_short_codes('fermats_library_notification_subject');

    }

        /**
         * Render the email template for the fermats library notification body
         *
         * @since  0.4.0
         * @access public
         */
    public static function render_fermats_library_notification_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_field('fermats_library_notification_body_template');
        echo static::render_short_codes('fermats_library_notification_body');

    }

        /**
         * Specifies class specific settings sections and fields.
         *
         * To be called from O3PO_Settings::configure().
         *
         * @since  0.4.0
         * @access public
         * @param  O3PO_Settings $settings Settings object.
         */
    public static function specify_settings( $settings ) {

        $settings->specify_section('email_settings', 'Email', array('O3PO_EmailTemplates', 'render_email_settings'), 'email_settings');
        $settings->specify_field('executive_board' , 'The names of the executive board members' , array('O3PO_EmailTemplates', 'render_executive_board_setting') , 'email_settings', 'email_settings', array(), array($settings, 'trim'), '');
        $settings->specify_field('editor_in_chief' , 'The name of the editor in chief' , array('O3PO_EmailTemplates', 'render_editor_in_chief_setting') , 'email_settings', 'email_settings', array(), array($settings, 'trim'), '');

        $settings->specify_field('self_notification_subject_template', 'Self notification subject template', array('O3PO_EmailTemplates', 'render_self_notification_subject_template_settings'),'email_settings', 'email_settings', array(), array($settings, 'trim'), "A [publication_type_name] has been published/updated by [journal]");
        $settings->specify_field('self_notification_body_template', 'Self notification body template', array('O3PO_EmailTemplates', 'render_self_notification_body_template_settings'), 'email_settings', 'email_settings', array(), array($settings, 'leave_unchanged'),
                                          "[journal] has published/updated the following [publication_type_name]\n".
                                          "Title:   [title] \n".
                                          "Authors: [authors] \n".
                                          "URL:     [url]\n".
                                          "DOI:     [doi_url_prefix][doi]\n"
                                          );
        $settings->specify_field('author_notification_subject_template', 'Author notification subject template', array('O3PO_EmailTemplates', 'render_author_notification_subject_template_settings'), 'email_settings', 'email_settings', array(), array($settings, 'trim'), "[journal] has published your [publication_type_name]");
        $settings->specify_field('author_notification_body_template' , 'Author notification body template' , array('O3PO_EmailTemplates', 'render_author_notification_body_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'leave_unchanged'),
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
                                          "Executive Board\n"
                                          );
        $settings->specify_field('author_notification_secondary_subject_template' , 'Author notification subject template for the secondary journal' , array('O3PO_EmailTemplates', 'render_author_notification_secondary_subject_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'trim'), "[journal] has published your [publication_type_name]");
        $settings->specify_field('author_notification_secondary_body_template' , 'Author notification body template for the secondary journal' , array('O3PO_EmailTemplates', 'render_author_notification_secondary_body_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'leave_unchanged'),
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
                                          "Executive Board\n"
                                          );
        $settings->specify_field('fermats_library_notification_subject_template' , 'Fermats library subject template' , array('O3PO_EmailTemplates', 'render_fermats_library_notification_subject_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'trim'), "[journal] has a new [publication_type_name] for Fermat's library");
        $settings->specify_field('fermats_library_notification_body_template' , 'Fermats library body template' , array('O3PO_EmailTemplates', 'render_fermats_library_notification_body_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'leave_unchanged'),
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
                                          "The Executive Board\n"
                                          );

    }

}
