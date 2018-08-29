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

   public static $default_self_notification_subject_template =
                 "A #PUBLICATION_TYPE_NAME# has been published/updated by #JOURNAL#";

   public static $default_self_notification_body_template =
                 "#JOURNAL# has published/updated the following #PUBLICATION_TYPE_NAME#\n"
               . "Title:   #TITLE# \n"
               . "Authors: #AUTHORS# \n"
               . "URL:     #URL#\n"
               . "DOI:     #DOI#\n";

   public static $default_author_notification_subject_template =
                 "#JOURNAL# has published your #PUBLICATION_TYPE_NAME#";

   public static $default_author_notification_body_template =
                 "Dear #AUTHORS#\n\n"
               . "Congratulations! Your #PUBLICATION_TYPE_NAME# '#TITLE#' has been published by #JOURNAL# and is now available under:\n\n"
               . "#POST_URL#\n\n"
               . "Your work has been assigned the following journal reference and DOI\n\n"
               . "Journal reference: #JOURNAL_REFERENCE#\n"
               . "DOI:               #DOI#\n\n"
               . "We kindly ask you to log in on the arXiv under https://arxiv.org/user/login and add this information to the page of your work there. Thank you very much!\n\n"
               . "In case you have an ORCID you can go to http://search.crossref.org/?q=#ORCID# to conveniently add your new publication to your profile.\n\n"
               . "Please be patient, it can take several hours until the DOI has been activated by Crossref.\n\n"
               . "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under #PUBLISHER_EMAIL#\n\n"
               . "Best regards,\n\n"
               . "#EXECUTIVE_BOARD#\n"
               . "Executive Board\n";

   public static $default_author_notification_secondary_body_template =
                 "Dear #AUTHORS#\n\n"
               . "Congratulations! Your #PUBLICATION_TYPE_NAME# '#TITLE#' has been published by #JOURNAL# and is now available under:\n\n"
               . "#POST_URL#\n\n"
               . "Your #PUBLICATION_TYPE_NAME# has been assigned the following journal reference and DOI\n\n"
               . "Journal reference: #JOURNAL_REFERENCE#\n"
               . "DOI:               #DOI#\n\n"
               . "In case you have an ORCID you can go to http://search.crossref.org/?q=#ORCID# to conveniently add your new publication to your profile.\n\n"
               . "Please be patient, it can take several hours before the above link works.\n\n"
               . "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under #PUBLISHER_EMAIL#\n\n"
               . "Thank you for writing this #PUBLICATION_TYPE_NAME# for #JOUNRAL#!\n\n"
               . "Best regards,\n\n"
               . "#EXECUTIVE_BOARD#\n"
               . "Executive Board\n";

   public static $default_fermats_library_subject =
                 "#JOURNAL# has a new #PUBLICATION_TYPE_NAME# for Fermat's library";

   public static $default_fermats_library_body =
                 "Dear team at Fermat's library,\n\n"
               . "#JOURNAL$ has published the following #PUBLICATION_TYPE_NAME#:\n\n"
               . "Title:     #TITLE#\n"
               . "Author(s): #AUTHORS#\n"
               . "URL:       #POST_URL$\n"
               . "DOI:       #DOI\n"
               . "\n"
               . "Please post it on Fermat's library under the permalink: #FERMATS_LIBRARY_PERMALINK#\n"
               . "Thank you very much!\n\n"
               . "Kind regards,\n\n"
               . "The Executive Board\n";

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
