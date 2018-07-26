<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-environment.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-journal.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-primary-publication-type.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-secondary-publication-type.php');

class O3PO_JournalAndPublicationTypesTest extends PHPUnit_Framework_TestCase
{

    public function test_initialize_settings()
    {
        $file_data = get_file_data(dirname( __FILE__ ) . '/../o3po/o3po.php', array(
                                       'Version' => 'Version',
                                       'Plugin Name' => 'Plugin Name',
                                       'Text Domain' => 'Text Domain'
                                                   ));

        $settings = O3PO_Settings::instance();
        $settings->configure($file_data['Text Domain'], $file_data['Plugin Name'], $file_data['Version'], 'O3PO_PublicationType::get_active_publication_type_names');

        return $settings;
    }

        /**
         * @depends test_initialize_settings
         */
    public function test_setup_primary_journal( $settings )
    {
        $journal_config_properties = O3PO_Journal::get_journal_config_properties();
        $journal_config = array();
        foreach(array_intersect(array_keys($settings->get_all_settings_fields_map()), $journal_config_properties) as $journal_config_property){
            $journal_config[$journal_config_property] = $settings->get_plugin_option($journal_config_property);
        }
            //add some properties that are named differently (for a reason) in settings
        $journal_config['publication_type_name'] = $settings->get_plugin_option('primary_publication_type_name');
        $journal_config['publication_type_name_plural'] = $settings->get_plugin_option('primary_publication_type_name_plural');

            //create the journal
        return new O3PO_Journal($journal_config);
    }


        /**
         * @depends test_initialize_settings
         */
    public function test_setup_secondary_journal( $settings )
    {
        $journal_config_properties = O3PO_Journal::get_journal_config_properties();
        $journal_config = array();
        foreach(array_intersect(array_keys($settings->get_all_settings_fields_map()), $journal_config_properties) as $journal_config_property){
            $journal_config[$journal_config_property] = $settings->get_plugin_option($journal_config_property);
        }
            //add some properties that are named differently (for a reason) in settings
        $journal_config['publication_type_name'] = $settings->get_plugin_option('primary_publication_type_name');
        $journal_config['publication_type_name_plural'] = $settings->get_plugin_option('primary_publication_type_name_plural');

            //reconfigure for the secondary journal
        $journal_config['journal_title'] = $settings->get_plugin_option('secondary_journal_title');
        $journal_config['journal_level_doi_suffix'] = $settings->get_plugin_option('secondary_journal_level_doi_suffix');
        $journal_config['eissn'] = $settings->get_plugin_option('secondary_journal_eissn');
        $journal_config['volumes_endpoint'] = 'secondary_volumes';
        $journal_config['publication_type_name'] = $settings->get_plugin_option('secondary_publication_type_name');
        $journal_config['publication_type_name_plural'] = $settings->get_plugin_option('secondary_publication_type_name_plural');


            //create the journal
        return new O3PO_Journal($journal_config);
    }

        /**
         * @depends test_initialize_settings
         */
    public function test_setup_environment( $settings ) {

        return new O3PO_Environment($settings->get_plugin_option("production_site_url"));
    }

        /**
         * @depends test_setup_primary_journal
         * @depends test_setup_environment
         */
    public function test_create_primary_publication_type( $journal, $environment )
    {

        return new O3PO_PrimaryPublicationType($journal, $environment);
    }

        /**
         * @depends test_create_primary_publication_type
         * @depends test_setup_secondary_journal
         * @depends test_setup_environment
         */
    public function test_create_secondary_publication_type( $primary_publication_type, $journal, $environment )
    {

        return new O3PO_SecondaryPublicationType($primary_publication_type->get_publication_type_name(), $primary_publication_type->get_publication_type_name_plural(), $journal, $environment);
    }

    public function primary_the_admin_components_provider() {

        return [
            ['the_admin_panel_intro_text'],
            ['the_admin_panel_howto'],
            ['the_admin_panel_validation_result'],
            ['the_admin_panel_eprint'],
            ['the_admin_panel_title'],
            ['the_admin_panel_corresponding_author_email'],
            ['the_admin_panel_buffer_email'],
            ['the_admin_panel_fermats_library'],
            ['the_admin_panel_authors'],
            ['the_admin_panel_affiliations'],
            ['the_admin_panel_date_volume_pages'],
            ['the_admin_panel_abstract'],
            ['the_admin_panel_doi'],
            ['the_admin_panel_feature_image_caption'],
            ['the_admin_panel_popular_summary'],
            ['the_admin_panel_bibliography'],
            ['the_admin_panel_crossref'],
            ['the_admin_panel_doaj'],
            ['the_admin_panel_arxiv'],
                ];
    }

        /**
         * @dataProvider primary_the_admin_components_provider
         * @depends test_create_primary_publication_type
         */
    public function test_primary_the_admin_components_is_well_formed_html( $function, $primary_publication_type ) {

        global $posts;

        $class = new ReflectionClass('O3PO_PrimaryPublicationType');
        foreach($posts as $post_id => $post_data)
        {
            $post_type = get_post_type($post_id);
            if ( $primary_publication_type->get_publication_type_name() !== $post_type )
                continue;

            $method = $class->getMethod($function);
            $method->setAccessible(true);

            ob_start();
            $method->invokeArgs($primary_publication_type, array($post_id));
            $output = ob_get_contents();
            ob_end_clean();

            $dom = new DOMDocument;
            $dom->loadHTML('<div>' . $output . '</div>');
                //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
        }
    }

        /**
         * @depends test_create_primary_publication_type
         */
    public function test_primary_render_metabox_is_well_formed_html( $primary_publication_type ) {
        global $posts;

        foreach($posts as $post_id => $post_data)
        {
                // $this->expectOutputRegex() didn't work as expected...
            ob_start();
            $primary_publication_type->render_metabox(new WP_Post($post_id));
            $output = ob_get_contents();
            ob_end_clean();

            $dom = new DOMDocument;
            $dom->loadHTML('<div>' . $output . '</div>');
//            $this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
        }
    }

    public function download_to_media_library_provider() {

        return [
            ['https://arxiv.org/pdf/0908.2921v2', 'q-1234-07-11-14', 'pdf', 'application/pdf', '1', false],
        ];
    }

        /**
         * @dataProvider download_to_media_library_provider
         * @depends test_setup_environment
         */
    public function test_download_to_media_library( $url, $filename, $extension, $mime_type, $parent_post_id, $expected_error, $environment ) {

        if(!defined('ABSPATH'))
            define( 'ABSPATH', dirname( __FILE__ ) . '/resources/' );
        $results = $environment->download_to_media_library($url, $filename, $extension, $mime_type, $parent_post_id);

        if(empty($results['error']))
        {
            $downloaded_file = $results['file'];
            if(strpos($downloaded_file, ABSPATH) === false)
                throw new Exception('File ' . $downloaded_file . ' is ouside of ABSPATH ' . ABSPATH . '. Aborting for security reasons.');

            $this->assertStringEndsWith($filename . '.' . $extension, $downloaded_file);
            $this->assertFileExists($downloaded_file);
            $this->assertFalse($expected_error);

            unlink($downloaded_file);
        }
        else
        {
            $this->assertSame($expected_error, $results['error']);
        }
    }

        /**
         * @depends test_create_primary_publication_type
         */
    function pages_still_free_info_provider( $primary_publication_type ) {
        return [
            [null, 1234, array('still_free' => true, 'title' => '')],
            [null, 1, array('still_free' => false, 'title' => 'Fake title')],
        ];
    }

        /**
         * @dataProvider pages_still_free_info_provider
         * @depends test_create_primary_publication_type
         * @depends test_setup_primary_journal
         */
    public function test_pages_still_free_info( $post_id_to_exclude, $pages, $expected, $primary_publication_type, $journal ) {
        $this->assertSame($expected, $journal->pages_still_free_info( $post_id_to_exclude, $pages, array($primary_publication_type->get_publication_type_name()) ));
    }

    public function posts_for_validate_and_process_data_provider() {
        global $posts;

        return [
            [1, $posts[1], array(
                    '#REVIEW: The pdf was downloaded successfully from the arXiv#',
                    '#REVIEW: The source was downloaded successfully from the arXiv to [^ ]*' . get_post_meta( 1, 'paper_doi_suffix', true) . '\.tex and is of mime-type text/x-tex#',
                    '#REVIEW: Found bibliographic information#',
                    '#REVIEW: Bibliographic information updated.#',
                    '#ERROR: Corresponding author email is malformed#',
                    '#(INFO: Licensing information .* and meta-data of .*' . get_post_meta( 1, 'paper_doi_suffix', true) . '\.pdf added/updated|ERROR: Adding meta-data to pdfs requires the external programm exiftool but the exiftool binary was not found)#',
                    '#ERROR: Corresponding author email is malformed#',
                                 )],
            [5, $posts[5], array(
                    '#INFO: URL of author 1 is empty\.#',
                                 )],
                ];
    }

        /**
         * @dataProvider posts_for_validate_and_process_data_provider
         * @depends test_create_primary_publication_type
         */
    public function test_validate_and_process_data( $post_id, $post_data, $expections, $primary_publication_type ) {

        $class = new ReflectionClass('O3PO_PrimaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() !== $post_type )
            return;

        $method = $class->getMethod('validate_and_process_data');
        $method->setAccessible(true);
        $validation_result = $method->invokeArgs($primary_publication_type, array($post_id));

            //print("\n\n" . $validation_result . "\n\n");

        foreach($expections as $expection)
        {
            $this->assertRegexp($expection, $validation_result);
        }

        $this->assertFalse(strpos('Exception while downloading the source', $validation_result));
    }


    public function on_post_actually_published_provider() {

        return [
            [1],
            [5],
        ];
    }

        /**
         * @dataProvider on_post_actually_published_provider
         * @depends test_create_primary_publication_type
         */
    public function test_on_post_actually_published( $post_id, $primary_publication_type ) {
        $class = new ReflectionClass('O3PO_PrimaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() !== $post_type )
            return;

        $method = $class->getMethod('on_post_actually_published');
        $method->setAccessible(true);
        $validation_result = $method->invokeArgs($primary_publication_type, array($post_id));

        $this->assertRegexp('#INFO: This paper was publicly published#', $validation_result);
        $this->assertFalse(strpos('ERROR', $validation_result));
    }


    public function save_meta_data_provider() {

        return [
            [1,
             array(
                 '_title' => 'a',
                 '_title_mathml' => 'b',
                 '_number_authors' => 2,
                 '_author_given_names' => array('c', 'd'),
                 '_author_surnames' => array('e', 'f'),
                 '_author_name_styles' => array('g', 'h'),
                 '_author_affiliations' => array('1', '1'),
                 '_author_orcids' => array('k', 'l'),
                 '_author_urls' => array('m', 'n'),
                 '_number_affiliations' => 1,
                 '_affiliations' => array('o'),
                 '_date_published' => 'p',
                 '_journal' => 'q',
                 '_volume' => 'r',
                 '_pages' => 's',
                 '_corresponding_author_email' => 't',
                 '_buffer_email' => 'u',
                 '_buffer_special_text' => 'v',
                 '_bbl' => 'w',
                   ),
             array(),
             ],
            [5,
             array(
                 '_eprint' => '0809.2542v4',
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                   ),
             array('#WARNING: It seems like 0809.2542v4 is not published under a creative commons license on the arXiv\.#'),
             ],
            [8,
             array(
                 '_eprint' => '1609.09584v4',
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                   ),
             array('#SUCCESS: Fetched metadata from https://arxiv.org/abs/1609\.09584v4#'),
             ],
                ];
    }

        /**
         * @runInSeparateProcess
         * @dataProvider save_meta_data_provider
         * @depends test_create_primary_publication_type
         */
    public function test_save_meta_data( $post_id, $POST_args, $expections, $primary_publication_type ) {
        $post_type = get_post_type($post_id);


        foreach($POST_args as $key => $value)
        {
            $_POST[ $post_type . $key ] = $value;
        }

        $class = new ReflectionClass('O3PO_PrimaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() !== $post_type )
            return;

        $method = $class->getMethod('save_meta_data');
        $method->setAccessible(true);
        $method->invokeArgs($primary_publication_type, array($post_id));


        if(!empty($POST_args['_fetch_metadata_from_arxiv']))
        {
                //print( "\n fetch_results: " . get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true) . "\n" );

            foreach($expections as $expection)
            {
                $this->assertRegexp($expection, get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true));
            }
        }
        else
        {
            foreach($POST_args as $key => $value)
                $this->assertSame($value, get_post_meta( $post_id, $post_type . $key, true), 'Property ' . $post_type . $key . ' was not set correctly.');
        }
    }
}
