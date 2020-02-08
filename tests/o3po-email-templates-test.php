<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-email-templates.php';
require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php';

class O3PO_EmailTemplatesTest extends PHPUnit_Framework_TestCase
{
  public function test_self_notification_subject(){

      $message = O3PO_EmailTemplates::expand('self_notification_subject',
                                             array(
                                                 "[journal]" => "test-journal",
                                                 "[publication_type_name]" => "test-publication-type-name"
                                                   )
                                             );
      $this->assertEquals("A test-publication-type-name has been published/updated by test-journal"
                          , $message);
  }

  public function test_self_notification_body(){
      $message = O3PO_EmailTemplates::expand('self_notification_body',
                                             array(
                                                 "[journal]" => "test-journal",
                                                 "[publication_type_name]" => "test-publication-name",
                                                 "[title]" => "test-title",
                                                 "[authors]" => "test-authors",
                                                 "[url]" => "test-url",
                                                 "[doi_url_prefix]" => "test-doi-prefix",
                                                 "[doi]" => "test-doi/with-special-char"
                                                   )
                                             );
      $this->assertEquals("test-journal has published/updated the following test-publication-name\n".
                          "Title:   test-title \n".
                          "Authors: test-authors \n".
                          "URL:     test-url\n".
                          "DOI:     test-doi-prefixtest-doi/with-special-char\n",
                          $message);
  }

  public function test_author_notification_subject() {
      $message = O3PO_EmailTemplates::expand('author_notification_subject',
                                             array(
                                                 "[journal]" => "test-journal",
                                                 "[publication_type_name]" => "test-publication-type-name",
                                                   )
                                             );
      $this->assertEquals("test-journal has published your test-publication-type-name",
                         $message);
  }

  public function test_author_notification_body(){
      $message = O3PO_EmailTemplates::expand('author_notification_body',
                                             array(
                                                 "[journal]" => "test-journal",
                                                 "[executive_board]" => "test-executive-board",
                                                 "[editor_in_chief]" => "test-editor-in-chief",
                                                 "[publisher_email]" => "test-publisher-email",
                                                 "[publication_type_name]" => "test-publication-type-name",
                                                 "[title]" => "test-title",
                                                 "[authors]" => "test-authors",
                                                 "[post_url]" => "test-post-url",
                                                 "[doi_url_prefix]" => "https://doi.org/",
                                                 "[doi]" => "10.22331/q-2018-08-27-87",
                                                 "[journal_reference]" => "test-journal-reference",
                                                   )
                                             );
      $this->assertEquals("Dear test-authors,\n\n".
                          "Congratulations! Your test-publication-type-name 'test-title' has been published by test-journal and is now available under:\n\n".
                          "test-post-url\n\n".
                          "Your work has been assigned the following journal reference and DOI\n\n".
                          "Journal reference: test-journal-reference\n".
                          "DOI:               https://doi.org/10.22331/q-2018-08-27-87\n\n".
                          "We kindly ask you to log in on the arXiv under https://arxiv.org/user/login and add this information to the page of your work there. Thank you very much!\n\n".
                          "In case you have an ORCID you can go to http://search.crossref.org/?q=10.22331/q-2018-08-27-87 to conveniently add your new publication to your profile.\n\n".
                          "Please be patient, it can take several hours until the DOI has been activated by Crossref.\n\n".
                          "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under test-publisher-email\n\n".
                          "Best regards,\n\n".
                          "test-executive-board\n".
                          "Executive Board\n",
                          $message
                          );
  }

  public function test_author_notification_secondary_body(){
    $message = O3PO_EmailTemplates::expand('author_notification_secondary_body',
                                           array(
                                               "[journal]" => "test-journal",
                                               "[executive_board]" => "test-executive-board",
                                               "[editor_in_chief]" => "test-editor-in-chief",
                                               "[publisher_email]" => "test-publisher-email",
                                               "[publication_type_name]" => "test-publication-type-name",
                                               "[title]" => "test-title",
                                               "[authors]" => "test-authors",
                                               "[post_url]" => "test-post-url",
                                               "[doi_url_prefix]" => "test-doi-prefix",
                                               "[doi]" => "test-doi",
                                               "[journal_reference]" => "test-journal-reference",
                                                 )
                                           );
    $this->assertEquals("Dear test-authors,\n\n".
                        "Congratulations! Your test-publication-type-name 'test-title' has been published by test-journal and is now available under:\n\n".
                        "test-post-url\n\n".
                        "Your test-publication-type-name has been assigned the following journal reference and DOI\n\n".
                        "Journal reference: test-journal-reference\n".
                        "DOI:               test-doi-prefixtest-doi\n\n".
                        "In case you have an ORCID you can go to http://search.crossref.org/?q=test-doi to conveniently add your new publication to your profile.\n\n".
                        "Please be patient, it can take several hours before the above link works.\n\n".
                        "If you have any feedback or ideas for how to improve the peer-review and publishing process, or any other question, please let us know under test-publisher-email\n\n".
                        "Thank you for writing this test-publication-type-name for test-journal!\n\n".
                        "Best regards,\n\n".
                        "test-executive-board\n".
                        "Executive Board\n",
                        $message
                      );
  }
   public function test_fermats_library_notification_subject(){
       $message = O3PO_EmailTemplates::expand('fermats_library_notification_subject',
                                              array(
                                                  "[journal]" => "test-journal",
                                                  "[publication_type_name]" => "test-publication-type-name",
                                                    )
                                              );
       $this->assertEquals("test-journal has a new test-publication-type-name for Fermat's library",
                 $message);
   }

   public function test_fermats_library_notification_body(){
       $message = O3PO_EmailTemplates::expand('fermats_library_notification_body',
                                              array(
                                                  "[journal]" => "test-journal",
                                                  "[publication_type_name]" => "test-publication-type-name",
                                                  "[title]" => "test-title",
                                                  "[authors]" => "test-authors",
                                                  "[post_url]" => "test-post-url",
                                                  "[doi_url_prefix]" => "test-doi-url-prefix",
                                                  "[doi]" => "test-doi",
                                                  "[fermats_library_permalink]" => "test-fermats-library-permalink",
                                                    )
                                              );
       $this->assertEquals("Dear team at Fermat's library,\n\n".
                           "test-journal has published the following test-publication-type-name:\n\n".
                           "Title:     test-title\n".
                           "Author(s): test-authors\n".
                           "URL:       test-post-url\n".
                           "DOI:       test-doi-url-prefixtest-doi\n".
                           "\n".
                           "Please post it on Fermat's library under the permalink: test-fermats-library-permalink\n".
                           "Thank you very much!\n\n".
                           "Kind regards,\n\n".
                           "The Executive Board\n",
                           $message);
   }

   public function test_render_short_codes(){
     $actualDom = new DomDocument();
     $actualDom->loadHTML(O3PO_EmailTemplates::render_short_codes('self_notification_subject'));
     $actualDom->preserveWhiteSpace = false;

     $expectedDom = new DomDocument();
     $expectedDom->loadHTML("<p>You may use the following shortcodes:</p><ul><li>[journal]: The journal name</li><li>[publication_type_name]: The type of the publication</li></ul>");
     $expectedDom->preserveWhiteSpace = false;

     $this->assertEquals($expectedDom->saveHTML(), $actualDom->saveHTML());
   }

   private static function getTemplate($template_name){
       $settings = O3PO_Settings::instance();
       $settings->configure('o3po', 'O-3PO', '1.2.3', 'O3PO_PublicationType::get_active_publication_type_names');
       return $settings->get_plugin_option($template_name);
   }
}
?>
