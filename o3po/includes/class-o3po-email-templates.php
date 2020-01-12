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

/**
 * Class representing the email templates
 *
 * @since      0.3.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Johannes Drever <drever@lrz.uni-muenchen.de>
 */
class O3PO_EmailTemplates {


    private static $self_notification_subject_template;
    private static $self_notification_body_template;

    private static $author_notification_subject_template;
    private static $author_notification_body_template;

    private static $fermats_library_notification_subject_template;
    private static $fermats_library_notification_body_template;

    private static $templates = array(
        'self_notification_subject' => new O3PO_ShortcodeTemplate($this->get_journal_property('self_notification_subject_template'), this is now a trait, and we can thus define instead functions such as self_notification_subject() that pull from settings, instanciate the template, and expand it!
                                                                  array("[journal]" => array(
                                                                            'description' => "The journal name",
                                                                            'example' => 'New Journal',
                                                                                             ),
                                                                        "[publication_type_name]" => array(
                                                                            'description' => "The type of the publication",
                                                                            'example' => 'paper',
                                                                                                           ),
                                                                        ));
                                                                  ),
        'self_notification_body'
        'author_notification_subject'
        'author_notification_body'
        'fermats_library_notification_subject'
        'fermats_library_notification_body'
    );



    public static function get_template( $template ) {

    }



        /**
         * Replace the short codes in the self notification subject template.
         *
         * @since    0.3.0
         * @access   public
         * @param    string $template The template with the self notification subject.
         * @param    string $journal The journal name.
         * @param    string $publication_type_name The type of the publication.
         * @return   Mixed The function returns a map with two keys.
         *                result: The template in which the short codes were replaced.
         *                short_codes: A map where the keys are the short codes and the values are the descriptions of the short codes.
         */
   public static function self_notification_subject($template
                                   , $journal, $publication_type_name){
       $short_codes = array("[journal]" => "The journal name",
                          "[publication_type_name]" => "The type of the publication.");
       return array('short_codes' => $short_codes,
                  'result' => str_replace(array_keys($short_codes),
                         array($journal, $publication_type_name),
                         $template));
   }

        /**
         * Replace the short codes in the self notification body template.
         *
         * @since    0.3.0
         * @access   public
         * @param    string $email_template The template with the self notification body.
         * @param    string $journal The journal name.
         * @param    string $publication_type_name The type of the publication.
         * @param    string $title The title of the publication.
         * @param    string $authors The list of authors.
         * @param    string $url The publication URL.
         * @param    string $doi_url_prefix The DOI URL prefix.
         * @param    string $doi The DOI.
         * @return   Mixed The function returns a map with two keys.
         *                result: The template in which the short codes were replaced.
         *                short_codes: A map where the keys are the short codes and the values are the descriptions of the short codes.
         */
   public static function self_notification_body( $email_template,
                                                  $journal,
                                                  $publication_type_name,
                                                  $title,
                                                  $authors,
                                                  $url,
                                                  $doi_url_prefix,
                                                  $doi ){

       $doi_hex_encoded = rawurlencode($doi);
       $short_codes = array("[journal]" => "The journal name",
                         "[publication_type_name]" => "The type of the publication",
                         "[title]" => "The title of the publication",
                         "[authors]" => "The list of authors",
                         "[url]" => "The publication URL",
                         "[doi_url_prefix]" => "The DOI url prefix",
                         "[doi]" => "The DOI",
                         "[doi_hex_encoded]" => "The DOI encoded in hex (use this when escaping problems occur)");
       return array('short_codes' => $short_codes,
                    'result' => str_replace(array_keys($short_codes),
                         array($journal, $publication_type_name, $title, $authors, $url, $doi_url_prefix, $doi, $doi_hex_encoded),
                         $email_template));
   }

        /**
         * Replace the short codes in the author notification subject template.
         *
         * @since    0.3.0
         * @access   public
         * @param    string $template The template with the author notification subject.
         * @param    string $journal The journal name.
         * @param    string $publication_type_name The type of the publication.
         * @return   Mixed The function returns a map with two keys.
         *                result: The template in which the short codes were replaced.
         *                short_codes: A map where the keys are the short codes and the values are the descriptions of the short codes.
         */
   public static function author_notification_subject($template,
                                   $journal, $publication_type_name){
       $short_codes = array("[journal]" => "The journal name",
                          "[publication_type_name]" => "The type of the publication");
       return array('short_codes' => $short_codes,
                  'result' => str_replace(array_keys($short_codes),
                         array($journal, $publication_type_name),
                         $template));
   }
        /**
         * Replace the short codes in the author notification body template.
         *
         * @since    0.3.0
         * @access   public
         * @param    string $email_template The template with the author notification body.
         * @param    string $journal The journal name.
         * @param    string $executive_board Names of the executive board members.
         * @param    string $editor_in_chief Names of the editor in chief.
         * @param    string $publisher_email Email address of the publisher.
         * @param    string $publication_type_name The type of the publication.
         * @param    string $title The title of the article.
         * @param    string $authors The names of the authors.
         * @param    string $url The url where the publication can be found.
         * @param    string $doi_url_prefix The DOI url prefix.
         * @param    string $doi The DOI.
         * @param    string $journal_reference The journal reference.
         * @return   Mixed The function returns a map with two keys.
         *                result: The template in which the short codes were replaced.
         *                short_codes: A map where the keys are the short codes and the values are the descriptions of the short codes.
         */

   public static function author_notification_body($email_template,
                                    $journal, $executive_board, $editor_in_chief, $publisher_email,
                                    $publication_type_name, $title, $authors, $url, $doi_url_prefix, $doi, $journal_reference){
       $doi_hex_encoded = rawurlencode($doi);
       $short_codes = array("[journal]" => "The journal name",
                          "[executive_board]" => "Names of the executive board members",
                          "[editor_in_chief]" => "Names of the editor in chief",
                          "[publisher_email]" => "Email address of the publisher",
                          "[publication_type_name]" => "The type of the publication",
                          "[title]" => "The title of the article",
                          "[authors]" => "The names of the authors",
                          "[post_url]" => "The url where the publication can be found",
                          "[doi_url_prefix]" => "The DOI url prefix",
                          "[doi]" => "The DOI",
                          "[doi_hex_encoded]" => "The DOI encoded in hex (use this when escaping problems uccur)",
                          "[journal_reference]" => "The journal reference"
                        );
       return array('short_codes' => $short_codes,
                  'result' => str_replace(array_keys($short_codes),
                         array($journal, $executive_board, $editor_in_chief, $publisher_email,
                               $publication_type_name, $title, $authors,
                               $url, $doi_url_prefix, $doi, $doi_hex_encoded, $journal_reference),
                         $email_template));
   }
        /**
         * Replace the short codes in the fermats library notification subject template.
         *
         * @since    0.3.0
         * @access   public
         * @param    string $template The template with the fermats library notification subject.
         * @param    string $journal The journal name.
         * @param    string $publication_type_name The type of the publication"
         * @return   Mixed The function returns a map with two keys.
         *                result: The template in which the short codes were replaced.
         *                short_codes: A map where the keys are the short codes and the values are the descriptions of the short codes.
         */

   public static function fermats_library_notification_subject($template,
                                   $journal, $publication_type_name){
       $short_codes = array("[journal]" => "The journal name",
                          "[publication_type_name]" => "The type of the publication");
       return array('short_codes' => $short_codes,
                  'result' => str_replace(array_keys($short_codes),
                         array($journal, $publication_type_name),
                         $template));
   }
        /**
         * Replace the short codes in the fermats library notification body template.
         *
         * @since    0.3.0
         * @access   public
         * @param    string $email_template The template with the fermats library notification body.
         * @param    string $journal The type of the publication.
         * @param    string $publication_type_name The type of the publication.
         * @param    string $title The title of the article.
         * @param    string $authors The names of the authors.
         * @param    string $url The url where the publication can be found.
         * @param    string $doi_url_prefix The DOI URL prefix.
         * @param    string $doi The DOI.
         * @param    string $fermats_library_permalink The permalink in fermats library.
         * @return   Mixed The function returns a map with two keys.
         *                result: The template in which the short codes were replaced.
         *                short_codes: A map where the keys are the short codes and the values are the descriptions of the short codes.
         */

   public static function fermats_library_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi_url_prefix, $doi, $fermats_library_permalink){
       $doi_hex_encoded = rawurlencode($doi);
       $short_codes = array("[journal]" => "The type of the publication",
                          "[publication_type_name]" => "The type of the publication",
                          "[title]" => "The title of the article",
                          "[authors]" => "The names of the authors",
                          "[post_url]" => "The url where the publication can be found",
                          "[doi_url_prefix]" => "The DOI url prefix",
                          "[doi]" => "The DOI",
                          "[doi_hex_encoded]" => "The DOI encoded in hex (use this when escaping problems uccur)",
                          "[fermats_library_permalink]" => "The permalink in fermats library");
       return array('short_codes' => $short_codes,
                  'result' => str_replace(array_keys($short_codes),
                         array($journal,
                               $publication_type_name, $title, $authors,
                               $url, $doi_url_prefix, $doi, $doi_hex_encoded, $fermats_library_permalink),
                         $email_template));
   }

       /**
        * Render the short codes for a specific template to an HTML list.
        *
        * @since  0.3.0
        * @access public
        * @param  string $template The name of the O3PO_EmailTemplates class method.
        * @return string An HTML list of the short codes.
        */
   public static function render_short_codes($template){
         $rfc = new ReflectionClass("O3PO_EmailTemplates");
         $rf = $rfc->getMethod($template);
         $render_function = "return O3PO_EmailTemplates::$template(";
         for($i = 1; $i < $rf->getNumberOfParameters(); $i++) {
           $render_function .= "'',";
         }
         $render_function .= "'');";

         $template_result = eval($render_function); The render short code functions should be reworked
         $short_codes = $template_result['short_codes'];

         $result = '<p>The following shortcodes are available:</p>';
         $result .= '<ul>';
         foreach($short_codes as $short_code => $description) {
               $result .= '<li>' . esc_html($short_code) . ': ' . esc_html($description) . '</li>';
         }
         return $result . '</ul>';
   }



        /**
         * Render the executive board of the email settings
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_executive_board_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_setting('executive_board');
        echo('<p>(Names of the executive board of your journal. Set this if you want to use the [executive_board] shortcode in the email templates below.)</p>');

    }

        /**
         * Render the editor in chief of the email settings
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_editor_in_chief_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_setting('editor_in_chief');
        echo('<p>(Name of the editor in chief. Set this if you want to use the [editor_in_chief] shortcode in the email templates below.)</p>');

    }


        /**
         * Render the email template for the self notification subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_self_notification_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_setting('self_notification_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('self_notification_subject');

    }

        /**
         * Render the email template for the self notification body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_self_notification_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_setting('self_notification_body_template');
        echo O3PO_EmailTemplates::render_short_codes('self_notification_body');

    }

        /**
         * Render the email template for the author notification subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_setting('author_notification_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_subject');

    }

        /**
         * Render the email template for the author notification body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_setting('author_notification_body_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_body');

    }

        /**
         * Render the email template for the author notification secondary subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_secondary_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_setting('author_notification_secondary_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_subject');

    }

        /**
         * Render the email template for the author notification secondary body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_author_notification_secondary_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_setting('author_notification_secondary_body_template');
        echo O3PO_EmailTemplates::render_short_codes('author_notification_body');

    }

        /**
         * Render the email template for the fermats library notification subject
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_fermats_library_subject_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_setting('fermats_library_notification_subject_template');
        echo O3PO_EmailTemplates::render_short_codes('fermats_library_notification_subject');

    }

        /**
         * Render the email template for the fermats library notification body
         *
         * @since    0.2.2
         * @access   public
         */
    public function render_fermats_library_body_template_settings() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_setting('fermats_library_notification_body_template');
        echo O3PO_EmailTemplates::render_short_codes('fermats_library_notification_body');

    }





        /**
         * Specifies class specific settings sections and fields.
         *
         * @since    0.3.1+
         * @access   public
         */
    public static function specify_settings() {

        $settings = O3PO_Settings::instance();

        $settings->specify_settings_section('email_settings', 'Email', array($this , 'render_email_settings'), 'email_settings');
        $settings->specify_settings_field('executive_board' , 'The names of the executive board members' , array($this, 'render_executive_board_setting') , 'email_settings', 'email_settings', array(), array($settings, 'trim_settings_field'), '');
        $this->specify_settings_field('editor_in_chief' , 'The name of the editor in chief' , array($this, 'render_editor_in_chief_setting') , 'email_settings', 'email_settings', array(), array($settings, 'trim_settings_field'), '');


                $this->specify_settings_field('self_notification_subject_template', 'Self notification subject template', array($this, 'render_self_notification_subject_template_settings'),'email_settings', 'email_settings', array(), array($settings, 'trim_settings_field'), "A [publication_type_name] has been published/updated by [journal]");
        $this->specify_settings_field('self_notification_body_template', 'Self notification body template', array($this, 'render_self_notification_body_template_settings'), 'email_settings', 'email_settings', array(), array($settings, 'leave_unchaged'),
                                  "[journal] has published/updated the following [publication_type_name]\n".
                                  "Title:   [title] \n".
                                  "Authors: [authors] \n".
                                  "URL:     [url]\n".
                                  "DOI:     [doi_url_prefix][doi]\n"
                                  );
        $this->specify_settings_field('author_notification_subject_template', 'Author notification subject template', array($this, 'render_author_notification_subject_template_settings'), 'email_settings', 'email_settings', array(), array($settings, 'trim_settings_field'), "[journal] has published your [publication_type_name]");
        $this->specify_settings_field('author_notification_body_template' , 'Author notification body template' , array($this, 'render_author_notification_body_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'leave_unchaged'),
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
        $this->specify_settings_field('author_notification_secondary_subject_template' , 'Author notification subject template for the secondary journal' , array($this, 'render_author_notification_secondary_subject_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'trim_settings_field'), "[journal] has published your [publication_type_name]");
        $this->specify_settings_field('author_notification_secondary_body_template' , 'Author notification body template for the secondary journal' , array($this, 'render_author_notification_secondary_body_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'leave_unchaged'),
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
        $this->specify_settings_field('fermats_library_subject_template' , 'Fermats library subject template' , array($this, 'render_fermats_library_subject_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'trim_settings_field'), "[journal] has a new [publication_type_name] for Fermat's library");
        $this->specify_settings_field('fermats_library_body_template' , 'Fermats library body template' , array($this, 'render_fermats_library_body_template_settings') , 'email_settings', 'email_settings', array(), array($settings, 'leave_unchaged'),
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
