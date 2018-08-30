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
       $shortCodes = array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/");
       return array('shortCodes' => $shortCodes
                  , 'result' => preg_replace($shortCodes
                         , array($journal, $publication_type_name)
                         , $template));
   }

   public static function self_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi){
       $shortCodes = array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/", "/#TITLE#/", "/#AUTHORS#/", "/#URL#/", "/#DOI#/");
       return preg_replace($shortCodes
                         , array($journal, $publication_type_name, $title, $authors, $url, $doi)
                         , $email_template);
   }

   public static function author_notification_subject($template
                                   , $journal, $publication_type_name){
       $shortCodes = array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/");
       return preg_replace($shortCodes
                         , array($journal, $publication_type_name)
                         , $template);
   }

   public static function author_notification_body($email_template
                                    , $journal, $executive_board, $publisher_email
                                    , $publication_type_name, $title, $authors, $url, $doi, $journal_reference, $orcid){
       $shortCodes = array("/#JOURNAL#/", "/#EXECUTIVE_BOARD#/", "/#PUBLISHER_EMAIL#/"
                               , "/#PUBLICATION_TYPE_NAME#/", "/#TITLE#/", "/#AUTHORS#/"
                               , "/#POST_URL#/", "/#DOI#/", "/#JOURNAL_REFERENCE#/", "/#ORCID#/");
       return preg_replace($shortCodes
                         , array($journal, $executive_board, $publisher_email
                               , $publication_type_name, $title, $authors
                               , $url, $doi, $journal_reference, $orcid)
                         , $email_template);
   }

   public static function fermats_library_notification_subject($template
                                   , $journal, $publication_type_name){
       $shortCodes = array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/");
       return preg_replace($shortCodes
                         , array($journal, $publication_type_name)
                         , $template);
   }

   public static function fermats_library_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi, $fermats_library_permalink){
       $shortCodes = array("/#JOURNAL#/"
                               , "/#PUBLICATION_TYPE_NAME#/", "/#TITLE#/", "/#AUTHORS#/"
                               , "/#POST_URL#/", "/#DOI#/", "/#FERMATS_LIBRARY_PERMALINK#/");
       return preg_replace($shortCodes
                         , array($journal
                               , $publication_type_name, $title, $authors
                               , $url, $doi, $fermats_library_permalink)
                         , $email_template);
   }
}

?>
