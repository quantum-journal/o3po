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
       $short_codes = array("/#JOURNAL#/" => "The journal name"
                          , "/#PUBLICATION_TYPE_NAME#/" => "The type of the publication.");
       return array('short_codes' => $short_codes
                  , 'result' => preg_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name)
                         , $template));
   }

   public static function self_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi){
       $short_codes = array("/#JOURNAL#/" => "The journal name"
                         , "/#PUBLICATION_TYPE_NAME#/" => "The type of the publication"
                         , "/#TITLE#/" => "The title of the publication"
                         , "/#AUTHORS#/" => "The list of authors"
                         , "/#URL#/" => "The publication URL"
                         , "/#DOI#/" => "The DOI");
       return array('short_codes' => $short_codes
                  , 'result' => preg_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name, $title, $authors, $url, $doi)
                         , $email_template));
   }

   public static function author_notification_subject($template
                                   , $journal, $publication_type_name){
       $short_codes = array("/#JOURNAL#/" => "The journal name"
                          , "/#PUBLICATION_TYPE_NAME#/" => "The type of the publication");
       return array('short_codes' => $short_codes
                  , 'result' => preg_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name)
                         , $template));
   }

   public static function author_notification_body($email_template
                                    , $journal, $executive_board, $publisher_email
                                    , $publication_type_name, $title, $authors, $url, $doi, $journal_reference, $orcid){
       $short_codes = array("/#JOURNAL#/" => "The journal name"
                          , "/#EXECUTIVE_BOARD#/" => "Names of the executive board members"
                          , "/#PUBLISHER_EMAIL#/" => "Email address of the publisher"
                          , "/#PUBLICATION_TYPE_NAME#/" => "The type of the publication"
                          , "/#TITLE#/" => "The title of the article"
                          , "/#AUTHORS#/" => "The names of the authors"
                          , "/#POST_URL#/" => "The url where the publication can be found"
                          , "/#DOI#/" => "The DOI"
                          , "/#JOURNAL_REFERENCE#/" => "The journal reference"
                          , "/#ORCID#/" => "The ORCID");
       return array('short_codes' => $short_codes
                  , 'result' => preg_replace(array_keys($short_codes)
                         , array($journal, $executive_board, $publisher_email
                               , $publication_type_name, $title, $authors
                               , $url, $doi, $journal_reference, $orcid)
                         , $email_template));
   }

   public static function fermats_library_notification_subject($template
                                   , $journal, $publication_type_name){
       $short_codes = array("/#JOURNAL#/" => "The journal name"
                          , "/#PUBLICATION_TYPE_NAME#/" => "The type of the publication");
       return array('short_codes' => $short_codes
                  , 'result' => preg_replace(array_keys($short_codes)
                         , array($journal, $publication_type_name)
                         , $template));
   }

   public static function fermats_library_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi, $fermats_library_permalink){
       $short_codes = array("/#JOURNAL#/" => "The type of the publication"
                          , "/#PUBLICATION_TYPE_NAME#/" => "The type of the publication"
                          , "/#TITLE#/" => "The title of the article"
                          , "/#AUTHORS#/" => "The names of the authors"
                          , "/#POST_URL#/" => "The url where the publication can be found"
                          , "/#DOI#/" => "The DOI"
                          , "/#FERMATS_LIBRARY_PERMALINK#/" => "The permalink in fermats library");
       return array('short_codes' => $short_codes
                  , 'result' => preg_replace(array_keys($short_codes)
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
               $cleaned_short_code = substr($short_code, 1, strlen($short_code) - 2);
               $result .= "<li><i>$cleaned_short_code</i>:$description</li>";
         }
         return $result . '</ul>';
   }
}

?>
