<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-email-templates.php';

class O3PO_EmailTemplatesTest extends PHPUnit_Framework_TestCase
{
  public function test_self_notification_subject(){
      $message = O3PO_EmailTemplates::self_notification_subject(
                   O3PO_EmailTemplates::$default_self_notification_subject_template
                 , "test-journal", "test-publication-type-name");
      $this->assertEquals($message
                        , "A test-publication-type-name has been published/updated by test-journal");
  }

  public function test_self_notification_body(){
      $message = O3PO_EmailTemplates::self_notification_body(
                   O3PO_EmailTemplates::$default_self_notification_body_template
                 , "test-journal", "test-publication-name", "test-title", "test-authors", "test-url", "test-doi");
      $this->assertEquals($message
                        , "test-journal has published/updated the following test-publication-name\n"
                        . "Title:   test-title \n"
                        . "Authors: test-authors \n"
                        . "URL:     test-url\n"
                        . "DOI:     test-doi\n");
  }

  public function test_author_notification_subject() {
      $message = O3PO_EmailTemplates::author_notification_subject(
                   O3PO_EmailTemplates::$default_author_notification_subject_template
                 , "test-journal", "test-publication-type-name"
                 );

      $this->assertEquals($message
                         , "test-journal has published your test-publication-type-name");
  }
}
?>

