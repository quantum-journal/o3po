<?php

/**
 * Class representing the email templates
 *
 * @link       http://example.com
 * @since      0.2.3
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Class representing the email templates
 *
 * @since      0.2.3
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Johannes Drever <drever@lrz.uni-muenchen.de>
 */
class O3PO_EmailTemplates {

   public static function self_notification_subject($template
                                   , $journal, $publication_type_name){
       $short_codes = array("[journal]" => "The journal name"
                          , "[publication_type_name]" => "The type of the publication.");
       return array('short_codes' => $short_codes
                  , 'result' => str_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name)
                         , $template));
   }

   public static function self_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi){
       $short_codes = array("[journal]" => "The journal name"
                         , "[publication_type_name]" => "The type of the publication"
                         , "[title]" => "The title of the publication"
                         , "[authors]" => "The list of authors"
                         , "[url]" => "The publication URL"
                         , "[doi]" => "The DOI");
       return array('short_codes' => $short_codes
                  , 'result' => str_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name, $title, $authors, $url, $doi)
                         , $email_template));
   }

   public static function author_notification_subject($template
                                   , $journal, $publication_type_name){
       $short_codes = array("[journal]" => "The journal name"
                          , "[publication_type_name]" => "The type of the publication");
       return array('short_codes' => $short_codes
                  , 'result' => str_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name)
                         , $template));
   }

   public static function author_notification_body($email_template
                                    , $journal, $executive_board, $publisher_email
                                    , $publication_type_name, $title, $authors, $url, $doi_url_prefix, $doi, $journal_reference){
       $short_codes = array("[journal]" => "The journal name"
                          , "[executive_board]" => "Names of the executive board members"
                          , "[publisher_email]" => "Email address of the publisher"
                          , "[publication_type_name]" => "The type of the publication"
                          , "[title]" => "The title of the article"
                          , "[authors]" => "The names of the authors"
                          , "[post_url]" => "The url where the publication can be found"
                          , "[doi_url_prefix]" => "The DOI url prefix"
                          , "[doi]" => "The DOI"
                          , "[journal_reference]" => "The journal reference"
                        );
       return array('short_codes' => $short_codes
                  , 'result' => str_replace(array_keys($short_codes)
                         , array($journal, $executive_board, $publisher_email
                               , $publication_type_name, $title, $authors
                               , $url, $doi_url_prefix, $doi, $journal_reference)
                         , $email_template));
   }

   public static function fermats_library_notification_subject($template
                                   , $journal, $publication_type_name){
       $short_codes = array("[journal]" => "The journal name"
                          , "[publication_type_name]" => "The type of the publication");
       return array('short_codes' => $short_codes
                  , 'result' => str_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name)
                         , $template));
   }

   public static function fermats_library_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi, $fermats_library_permalink){
       $short_codes = array("[journal]" => "The type of the publication"
                          , "[publication_type_name]" => "The type of the publication"
                          , "[title]" => "The title of the article"
                          , "[authors]" => "The names of the authors"
                          , "[post_url]" => "The url where the publication can be found"
                          , "[doi]" => "The DOI"
                          , "[fermats_library_permalink]" => "The permalink in fermats library");
       return array('short_codes' => $short_codes
                  , 'result' => str_replace(array_keys($short_codes)
                         , array($journal
                               , $publication_type_name, $title, $authors
                               , $url, $doi, $fermats_library_permalink)
                         , $email_template));
   }

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

         $result = '<ul>';
         foreach($short_codes as $short_code => $description) {
               $result .= "<li><i>$short_code</i>:$description</li>";
         }
         return $result . '</ul>';
   }
}

?>
