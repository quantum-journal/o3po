<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-email-templates.php';
require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php';

class O3PO_EmailTemplatesTest extends PHPUnit_Framework_TestCase
{
  public function test_self_notification_subject(){

      $message = O3PO_EmailTemplates::self_notification_subject(
                   O3PO_EmailTemplatesTest::getTemplate('self_notification_subject_template')
                 , "test-journal", "test-publication-type-name");
      $this->assertEquals($message['result']
                        , "A test-publication-type-name has been published/updated by test-journal");
  }

  public function test_self_notification_body(){
      $message = O3PO_EmailTemplates::self_notification_body(
                   O3PO_EmailTemplatesTest::getTemplate('self_notification_body_template')
                 , "test-journal", "test-publication-name", "test-title", "test-authors", "test-url", "test-doi");
      $this->assertEquals($message['result']
                        , "test-journal has published/updated the following test-publication-name\n"
                        . "Title:   test-title \n"
                        . "Authors: test-authors \n"
                        . "URL:     test-url\n"
                        . "DOI:     test-doi\n");
  }

  public function test_author_notification_subject() {
      $message = O3PO_EmailTemplates::author_notification_subject(
                   O3PO_EmailTemplatesTest::getTemplate('author_notification_subject_template')
                 , "test-journal", "test-publication-type-name"
                 );

      $this->assertEquals($message['result']
                         , "test-journal has published your test-publication-type-name");
  }

  public function test_author_notification_body(){
    $message = O3PO_EmailTemplates::author_notification_body(
                   O3PO_EmailTemplatesTest::getTemplate('author_notification_body_template')
                 , "test-journal", "test-executive-board", "test-publisher-email", "test-publication-type-name", "test-title", "test-authors", "test-post-url", "test-doi", "test-journal-reference", "test-orcid"
                 );
    $this->assertEquals($message['result']
                      , "Dear test-authors\n\n"
                      . "Congratulations! Your test-publication-type-name 'test-title' has been published by test-journal and is now available under:\n\n"
                      . "test-post-url\n\n"
                      . "Your work has been assigned the following journal reference and DOI\n\n"
                      . "Journal reference: test-journal-reference\n"
                      . "DOI:               test-doi\n\n"
                      . "We kindly ask you to log in on the arXiv under https://arxiv.org/user/login and add this information to the page of your work there. Thank you very much!\n\n"
                      . "In case you have an ORCID you can go to http://search.crossref.org/?q=test-orcid to conveniently add your new publication to your profile.\n\n"
                      . "Please be patient, it can take several hours until the DOI has been activated by Crossref.\n\n"
                      . "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under test-publisher-email\n\n"
                      . "Best regards,\n\n"
                      . "test-executive-board\n"
                      . "Executive Board\n"
                      );
  }

  public function test_author_notification_secondary_body(){
    $message = O3PO_EmailTemplates::author_notification_body(
                   O3PO_EmailTemplatesTest::getTemplate('author_notification_secondary_body_template')
                 , "test-journal", "test-executive-board", "test-publisher-email", "test-publication-type-name", "test-title", "test-authors", "test-post-url", "test-doi", "test-journal-reference", "test-orcid"
                 );
    $this->assertEquals($message['result']
                      , "Dear test-authors\n\n"
                      . "Congratulations! Your test-publication-type-name 'test-title' has been published by test-journal and is now available under:\n\n"
                      . "test-post-url\n\n"
                      . "Your test-publication-type-name has been assigned the following journal reference and DOI\n\n"
                      . "Journal reference: test-journal-reference\n"
                      . "DOI:               test-doi\n\n"
                      . "In case you have an ORCID you can go to http://search.crossref.org/?q=test-orcid to conveniently add your new publication to your profile.\n\n"
                      . "Please be patient, it can take several hours before the above link works.\n\n"
                      . "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under test-publisher-email\n\n"
                      . "Thank you for writing this test-publication-type-name for test-journal!\n\n"
                      . "Best regards,\n\n"
                      . "test-executive-board\n"
                      . "Executive Board\n"
                      );
  }
   public function test_fermats_library_notification_subject(){
       $message = O3PO_EmailTemplates::fermats_library_notification_subject(
                     O3PO_EmailTemplatesTest::getTemplate('fermats_library_notification_subject_template')
                  , "test-journal", "test-publication-type-name"
                  );
       $this->assertEquals($message['result']
                 , "test-journal has a new test-publication-type-name for Fermat's library");
   }

   public function test_fermats_library_notification_body(){
       $message = O3PO_EmailTemplates::fermats_library_notification_body(
                     O3PO_EmailTemplatesTest::getTemplate('fermats_library_notification_body_template')
                  , "test-journal"
                  , "test-publication-type-name", "test-title", "test-authors"
                  , "test-post-url", "test-doi", "test-fermats-library-permalink"
                  );
       $this->assertEquals($message['result']
               , "Dear team at Fermat's library,\n\n"
               . "test-journal has published the following test-publication-type-name:\n\n"
               . "Title:     test-title\n"
               . "Author(s): test-authors\n"
               . "URL:       test-post-url\n"
               . "DOI:       test-doi\n"
               . "\n"
               . "Please post it on Fermat's library under the permalink: test-fermats-library-permalink\n"
               . "Thank you very much!\n\n"
               . "Kind regards,\n\n"
               . "The Executive Board\n");
   }

   public function test_render_short_codes(){
     $expectedDom = new DomDocument();
     $expectedDom->loadHTML(O3PO_EmailTemplates::render_short_codes('self_notification_subject'));
     $expectedDom->preserveWhiteSpace = false;

     $actualDom = new DomDocument();
     $actualDom->loadHTML("<ul><li><i>#JOURNAL#</i>:The journal name</li><li><i>#PUBLICATION_TYPE_NAME#</i>:The type of the publication.</li></ul>");
     $actualDom->preserveWhiteSpace = false;

     $this->assertEquals($expectedDom->saveHTML(), $actualDom->saveHTML());
   }

   private static function getTemplate($template_name){
       $settings = O3PO_Settings::instance();
       return $settings->get_plugin_option($template_name);
   }
}
?>

