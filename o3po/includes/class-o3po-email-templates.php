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
       return preg_replace(array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/")
                         , array($journal, $publication_type_name)
                         , $template);
   }

   public static function self_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi){
       return preg_replace(array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/", "/#TITLE#/", "/#AUTHORS#/", "/#URL#/", "/#DOI#/")
                         , array($journal, $publication_type_name, $title, $authors, $url, $doi)
                         , $email_template);
   }

   public static function author_notification_subject($template
                                   , $journal, $publication_type_name){
       return preg_replace(array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/")
                         , array($journal, $publication_type_name)
                         , $template);
   }

   public static function author_notification_body($email_template
                                    , $journal, $executive_board, $publisher_email
                                    , $publication_type_name, $title, $authors, $url, $doi, $journal_reference, $orcid){
       return preg_replace(array("/#JOURNAL#/", "/#EXECUTIVE_BOARD#/", "/#PUBLISHER_EMAIL#/"
                               , "/#PUBLICATION_TYPE_NAME#/", "/#TITLE#/", "/#AUTHORS#/"
                               , "/#POST_URL#/", "/#DOI#/", "/#JOURNAL_REFERENCE#/", "/#ORCID#/")
                         , array($journal, $executive_board, $publisher_email
                               , $publication_type_name, $title, $authors
                               , $url, $doi, $journal_reference, $orcid)
                         , $email_template);
   }

   public static function fermats_library_notification_subject($template
                                   , $journal, $publication_type_name){
       return preg_replace(array("/#JOURNAL#/", "/#PUBLICATION_TYPE_NAME#/")
                         , array($journal, $publication_type_name)
                         , $template);
   }

   public static function fermats_library_notification_body($email_template
                                    , $journal
                                    , $publication_type_name, $title, $authors, $url, $doi, $fermats_library_permalink){
       return preg_replace(array("/#JOURNAL#/"
                               , "/#PUBLICATION_TYPE_NAME#/", "/#TITLE#/", "/#AUTHORS#/"
                               , "/#POST_URL#/", "/#DOI#/", "/#FERMATS_LIBRARY_PERMALINK#/")
                         , array($journal
                               , $publication_type_name, $title, $authors
                               , $url, $doi, $fermats_library_permalink)
                         , $email_template);
   }
}

?>
