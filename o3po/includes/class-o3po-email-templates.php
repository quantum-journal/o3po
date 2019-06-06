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

       $doi_hex_encoded = static::hex_encode($doi);
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
       $doi_hex_encoded = static::hex_encode($doi);
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
       $doi_hex_encoded = static::hex_encode($doi);
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

         $template_result = eval($render_function);
         $short_codes = $template_result['short_codes'];

         $result = '<p>The following shortcodes are available:</p>';
         $result .= '<ul>';
         foreach($short_codes as $short_code => $description) {
               $result .= '<li>' . esc_html($short_code) . ': ' . esc_html($description) . '</li>';
         }
         return $result . '</ul>';
   }

       /**
        * Encode the DOI into hex to escape URL parameters.
        *
        * @since  0.3.0
        * @access public
        * @param  string $doi The DOI that should be encoded.
        * @return string The hex encoded DOI.
        */
   private static function hex_encode($doi) {

       return rawurlencode($doi);
   }

}
