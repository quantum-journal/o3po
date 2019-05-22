<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-environment.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-journal.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-primary-publication-type.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-secondary-publication-type.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-latex.php');


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

        $this->assertInstanceOf(O3PO_Settings::class, $settings);

        return $settings;
    }

        /**
         * @depends test_initialize_settings
         */
    public function test_setup_primary_journal( $settings )
    {
        $journal = O3PO::setup_primary_journal($settings);
        $this->assertInstanceOf(O3PO_Journal::class, $journal);

        return $journal;
    }


        /**
         * @depends test_initialize_settings
         */
    public function test_setup_secondary_journal( $settings )
    {

        $journal = O3PO::setup_secondary_journal($settings);
        $this->assertInstanceOf(O3PO_Journal::class, $journal);

        return $journal;
    }

        /**
         * @depends test_initialize_settings
         */
    public function test_setup_environment( $settings ) {

        $environment = new O3PO_Environment($settings->get_plugin_option("production_site_url"));
        $this->assertInstanceOf(O3PO_Environment::class, $environment);

        return $environment;
    }

        /**
         * @depends test_setup_primary_journal
         * @depends test_setup_environment
         */
    public function test_create_primary_publication_type( $journal, $environment )
    {

        $primary_publication_type = new O3PO_PrimaryPublicationType($journal, $environment);
        $this->assertInstanceOf(O3PO_PrimaryPublicationType::class, $primary_publication_type);

        return $primary_publication_type;
    }

        /**
         * @depends test_create_primary_publication_type
         * @depends test_setup_secondary_journal
         * @depends test_setup_environment
         */
    public function test_create_secondary_publication_type( $primary_publication_type, $journal, $environment )
    {

        $secondary_publication_type = new O3PO_SecondaryPublicationType($primary_publication_type->get_publication_type_name(), $primary_publication_type->get_publication_type_name_plural(), $journal, $environment);
        $this->assertInstanceOf(O3PO_SecondaryPublicationType::class, $secondary_publication_type);

        return $secondary_publication_type;
    }

    public function single_paper_template_provider() {

        return [
            [1],
            [5],
        ];
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
    public function test_primary_the_admin_components_are_well_formed_html( $function, $primary_publication_type ) {

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

            $result = $dom->loadHTML('<div>' . $output . '<div>');
                //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);
        }
    }

    public function secondary_the_admin_components_provider() {

        return [
            ['the_admin_panel_intro_text'],
            ['the_admin_panel_validation_result'],
            ['the_admin_panel_sub_type'],
            ['the_admin_panel_target_dois'],
            ['the_admin_panel_title'],
            ['the_admin_panel_corresponding_author_email'],
            ['the_admin_panel_buffer_email'],
            ['the_admin_panel_authors'],
            ['the_admin_panel_affiliations'],
            ['the_admin_panel_date_volume_pages'],
            ['the_admin_panel_doi'],
            ['the_admin_panel_reviewers_summary'],
            ['the_admin_panel_reviewers'],
            ['the_admin_panel_reviewer_institutions'],
            ['the_admin_panel_author_commentary'],
                ];
    }

        /**
         * @dataProvider secondary_the_admin_components_provider
         * @depends test_create_secondary_publication_type
         */
    public function test_secondary_the_admin_components_are_well_formed_html( $function, $secondary_publication_type ) {

        global $posts;

        $class = new ReflectionClass('O3PO_SecondaryPublicationType');
        foreach($posts as $post_id => $post_data)
        {
            $post_type = get_post_type($post_id);
            if ( $secondary_publication_type->get_publication_type_name() !== $post_type )
                continue;

            $method = $class->getMethod($function);
            $method->setAccessible(true);

            ob_start();
            $method->invokeArgs($secondary_publication_type, array($post_id));
            $output = ob_get_contents();
            ob_end_clean();

            $dom = new DOMDocument;
            $result = $dom->loadHTML('<div>' . $output . '</div>');
                //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);
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
            $result = $dom->loadHTML('<div>' . $output . '</div>');
//            $this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);
        }
    }

       /**
         * @depends test_create_secondary_publication_type
         */
    public function test_secondary_render_metabox_is_well_formed_html( $secondary_publication_type ) {
        global $posts;

        foreach($posts as $post_id => $post_data)
        {
                // $this->expectOutputRegex() didn't work as expected...
            ob_start();
            $secondary_publication_type->render_metabox(new WP_Post($post_id));
            $output = ob_get_contents();
            ob_end_clean();

            $dom = new DOMDocument;
            $result = $dom->loadHTML('<div>' . $output . '</div>');
//            $this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);
        }
    }

        /**
         * @depends test_create_secondary_publication_type
         * @depends test_initialize_settings
         */
    public function test_secondary_get_the_content( $secondary_publication_type, $settings ) {
        global $posts;
        global $post;

        foreach($posts as $post_id => $post_data)
        {
            $post = new WP_Post($post_id);
            set_global_query(new WP_Query(array('ID' => $post_id)));
            the_post();

            if(isset($posts[$post_id]['post_content']))
                $orig_content = $posts[$post_id]['post_content'];
            else
                $orig_content = '';
            $content = $secondary_publication_type->get_the_content($orig_content);

            if(isset($posts[$post_id]['meta']['view_type']) && $posts[$post_id]['meta']['view_type'] === 'Leap')
            {
                foreach( array(
                         '#popular science#',
                         '#' . $settings->get_plugin_option('license_url')  . '#',
                         '#' . $settings->get_plugin_option('publisher')  . '#',
                           )
                         as $regexp)
                    $this->assertRegexp($regexp, $content);
            }
            else
                $this->assertSame($orig_content, $content);

            $content = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5

            $dom = new DOMDocument;
            $result = $dom->loadHTML('<div>' . $content . '</div>');
//            $this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);
        }
    }


        /**
         * @depends test_create_primary_publication_type
         * @depends test_initialize_settings
         */
    public function test_primary_get_the_content( $primary_publication_type, $settings ) {
        global $posts;
        global $post;

        foreach($posts as $post_id => $post_data)
        {
            $post = new WP_Post($post_id);
            set_global_query(new WP_Query(array('ID' => $post_id)));
            the_post();

            if(isset($posts[$post_id]['post_content']))
                $orig_content = $posts[$post_id]['post_content'];
            else
                $orig_content = '';
            $content = $primary_publication_type->get_the_content($orig_content);

            $post_type = get_post_type($post_id);
            if($post_type == 'paper')
            {
                foreach( array(
                         '#' . $settings->get_plugin_option('license_url')  . '#',
                         '#' . $settings->get_plugin_option('publisher')  . '#',
                           )
                         as $regexp)
                    $this->assertRegexp($regexp, $content);
            }
            else
                $this->assertSame($orig_content, $content);

            $content = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5

            $dom = new DOMDocument;
            $result = $dom->loadHTML('<div>' . $content . '</div>');
//            $this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);
        }
    }


    public function download_to_media_library_provider() {

        return [
            ['https://arxiv.org/pdf/0908.2921v2', 'q-1234-07-11-14', 'pdf', 'application/pdf', '1', false],
        ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
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
         * @depends test_create_secondary_publication_type
         */
    function test_get_active_publication_type_names( $primary_publication_type, $secondary_publication_type ) {

        $this->assertSame($primary_publication_type->get_active_publication_type_names(), array($primary_publication_type->get_publication_type_name(), $secondary_publication_type->get_publication_type_name()));
        $this->assertSame($primary_publication_type->get_active_publication_types(), array($primary_publication_type, $secondary_publication_type));
        $this->assertSame($primary_publication_type->get_active_publication_types($primary_publication_type->get_publication_type_name()), $primary_publication_type);
    }



    function doi_suffix_still_free_provider() {

        return [
            ['unused_prefix', true],
            ['fake_paper_doi_suffix', false],
            ['fake_journal_level_doi_suffix-' . current_time("Y-m-d") . '-3', false],
            ['q-2004-04-25-8', true],
        ];
    }

        /**
         * @dataProvider doi_suffix_still_free_provider
         * @depends test_setup_primary_journal
         * @depends test_setup_secondary_journal
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    function test_doi_suffix_still_free( $prefix, $expected, $primary_journal, $setup_secondary_journal, $primary_publication_type, $secondary_publication_type ) {

        $this->assertSame($expected, $primary_journal->doi_suffix_still_free($prefix, $primary_publication_type->get_active_publication_type_names()));

    }


    function pages_still_free_info_provider() {
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

    public function parse_publication_source_provider() {
        $settings = O3PO_Settings::instance();

        return [
            [dirname(__FILE__) . '/resources/arxiv/1711.04662v3.tar.gz', "application/gzip", array(
                    "affiliations" => array('#Aix-Marseille Univ, Université de Toulon, CNRS, LIS, Marseille, and IXXI, Lyon, France#', '#Aix-Marseille Univ, Université de Toulon, CNRS, LIS, Marseille, France and Departamento de Física Teórica and IFIC, Universidad de Valencia-CSIC, Dr. Moliner 50, 46100-Burjassot, Spain#', '#Aix-Marseille Univ, Université de Toulon, CNRS, LIS, Marseille, France#'),
                    "validation_result" => array("#REVIEW: Found BibTeX or manually formated bibliographic information#", "#REVIEW: Author homepage data updated from arxiv source#"),
                    "bbl" => array('#\\\\begin{thebibliography}#', '#ahlbrecht2012molecular#', '#venegas2012quantum#'),
                    'num_dois' => 28,
                                                                                                   )],
            [dirname(__FILE__) . '/resources/arxiv/0809.2542v4.tar.gz', "application/x-tar", array(
                    "validation_result" => "#REVIEW: Found BibTeX or manually formated bibliographic information#",
                    "author_affiliations" => '#1#',
                    "affiliations" => '#Fakultät für Physik und Astronomie, Universität Würzburg, Am Hubland, 97074 Würzburg, Germany#',
                    "bbl" => array('#\\\\begin{thebibliography}#', '#DeGennes#', '#Ising#'),
                    'num_dois' => 0,
                                                                                                   )],
            [dirname(__FILE__) . '/resources/arxiv/1708.05489v2.tar.gz', "application/gz", array(
                    "validation_result" => array("#REVIEW: Found BibTeX or manually formated bibliographic information#", '#REVIEW: Author and affiliations data updated from arxiv source.#'),
                    "author_affiliations" => array('#1#', '#2#'),
                    "affiliations" => array('#Institute of Theoretical Physics, Faculty of Physics, University of Warsaw, Pasteura 5, 02-093 Warsaw, Poland#', '#Department of Physics, Saint Anselm College, Manchester, NH 03102, USA#'),
                    "bbl" => array('#\\\\begin{thebibliography}#', '#\\[Abdolrahimi\\(2014\\)\\]{Abdolrahimi:2014aa}#', '#\\\\end{thebibliography}#'),
                    'num_dois' => 21,
                                                                                                 )],
            [dirname(__FILE__) . '/resources/arxiv/0908.2921v2.tex', "text/tex", array(
                    "validation_result" => array('#Found BibTeX or manually formated bibliograph#', '#Author and affiliations data updated from arxiv source#'),
                    #"author_latex_macro_definitions" => '#\\\\newcommand{\\\\bra}#',
                        /*"author_orcids" => , */
                    "author_affiliations" => '#1#',
                    "affiliations" => '#Fakultät für Physik und Astronomie, Universität Würzburg, Am Hubland, 97074 Würzburg, Germany#',
                    "bbl" => '#\\\\begin{thebibliography}#',
                    'num_dois' => 0,
                                                                                       )],


            [dirname(__FILE__) . '/resources/arxiv/1704.02130v3.tar.gz', "application/x-tar", array(
                    "validation_result" => array('#REVIEW: Found BibLaTeX formated bibliographic information#', '#Author and affiliations data updated from arxiv source#'),
                    #"author_latex_macro_definitions" => '#\\\\newcommand{\\\\bra}#',
                        /*"author_orcids" => , */
                    "author_affiliations" => '#1#',
                    "affiliations" => '#Laboratoire d\'Information Quantique, CP 224, Université libre de Bruxelles \(ULB\), 1050 Brussels, Belgium#',
                    "bbl" => array( '#biblatex auxiliary file#', '#entry{AM16}{article}{}#', '#verb 10\.1109/ISIT\.2002\.1023345#' ),
                    'num_dois' => 17,
                                                                                                    )],

            [dirname(__FILE__) . '/resources/arxiv/1603.04424v3.tar.gz', "application/x-tar", array(
                    'num_dois' => 21,
                                                                                                    )],

            [dirname(__FILE__) . '/resources/arxiv/1610.00336v2.tar.gz', "application/x-tar", array(
                    'num_dois' => 54,
                                                                                                    )],

            [dirname(__FILE__) . '/resources/arxiv/1610.06169.tar.gz', "application/x-tar", array(
                    'num_dois' => 48,
                                                                                                    )],

            [dirname(__FILE__) . '/resources/arxiv/1801.03508.tar.gz', "application/x-tar", array(
                    'num_dois' => 26,
                                                                                                    )],



            ];


    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider parse_publication_source_provider
         * @depends test_create_primary_publication_type
         */
    public function test_parse_publication_source( $path_source, $mime_type, $expectation, $primary_publication_type ) {

        $class = new ReflectionClass('O3PO_PrimaryPublicationType');
        $method = $class->getMethod('parse_publication_source');
        $method->setAccessible(true);

        $parse_publication_source_result = $method->invokeArgs($primary_publication_type, array($path_source, $mime_type));

        foreach(array("validation_result","author_latex_macro_definitions","author_orcids","author_affiliations","affiliations","bbl") as $key)
        {
            if(isset($expectation[$key]))
            {
                if(is_array($parse_publication_source_result[$key]))
                    $result = implode("###", $parse_publication_source_result[$key]);
                else
                    $result = $parse_publication_source_result[$key];
                if(!is_array($expectation[$key]))
                    $expectations = array($expectation[$key]);
                else
                    $expectations = $expectation[$key];
                foreach($expectations as $expect)
                    $this->assertRegexp($expect, $result);
            }
        }

        # We do some more in-depth parsing of the extracted bbl:
        if($parse_publication_source_result['bbl'])
        {
            $parsed_bbl = O3PO_Latex::parse_bbl($parse_publication_source_result['bbl']);
            $num_dois = 0;
            foreach($parsed_bbl as $n => $entry) {

                $this->assertFalse( O3PO_Latex::strpos_outside_math_mode($entry['text'], '\\'), "The text " . $entry['text'] . " extracted from the bbl in " . $path_source . " still contains one ore more backslashes that were not caught by parse_bbl");

                $dom = new DOMDocument;
                #print("\n\n" . $primary_publication_type->get_formated_bibliography_entry_html($entry) . "\n\n");

                $result = $dom->loadHTML('<div>' . $primary_publication_type->get_formated_bibliography_entry_html($entry) . '</div>');
                    // $this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
                $this->assertNotFalse($result);


                if( !empty($entry['doi']) )
                    $num_dois += 1;
            }
            #print("  ".$path_source.":".$num_dois."  ");

            if(!empty($expectation['num_dois']))
                $this->assertSame($num_dois, $expectation['num_dois']);
        }
    }

    public function posts_for_validate_and_process_data_provider() {
        global $posts;

        return [
            [1, array(
                    '#REVIEW: The pdf was downloaded successfully from the arXiv#',
                    '#REVIEW: The source was downloaded successfully from the arXiv to [^ ]*' . get_post_meta( 1, 'paper_doi_suffix', true) . '[0-9-]*\.tex and is of mime-type text/x-tex#',
                    '#REVIEW: Found BibTeX or manually formated bibliographic information in.*\.tex#',
                    '#REVIEW: Bibliographic information updated.#',
                    '#ERROR: Corresponding author email is malformed#',
                    '#(INFO: Licensing information .* and meta-data of .*' . get_post_meta( 1, 'paper_doi_suffix', true) . '[0-9-]*\.pdf added/updated|ERROR: Adding meta-data to pdfs requires the external programm exiftool but the exiftool binary was not found)#',
                    '#ERROR: Corresponding author email is malformed#',
                                 )],
            [5, array(
                                 )],
            [9, array(
                    '#ERROR: Affiliation 1 is not associated to any authors.#',
                                 )],
            [10, array(

                                 )],
                ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider posts_for_validate_and_process_data_provider
         * @depends test_create_primary_publication_type
         */
    public function test_primary_validate_and_process_data( $post_id, $expections, $primary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

        if(!defined('ABSPATH'))
            define( 'ABSPATH', dirname( __FILE__ ) . '/resources/' );

        $primary_publication_type_class = new ReflectionClass('O3PO_PrimaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() !== $post_type )
        {
            $this->addToAssertionCount(1);
            return;
        }

        $method = $primary_publication_type_class->getMethod('validate_and_process_data');
        $method->setAccessible(true);
        $validation_result = $method->invokeArgs($primary_publication_type, array($post_id));

        foreach($expections as $expection)
        {
            $this->assertRegexp($expection, $validation_result);
        }

        $this->assertFalse(strpos('Exception while downloading the source', $validation_result));
    }


        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider posts_for_validate_and_process_data_provider
         * @depends test_create_secondary_publication_type
         */
    public function test_secondary_validate_and_process_data( $post_id, $expections, $secondary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

        $secondary_publication_type_class = new ReflectionClass('O3PO_SecondaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $secondary_publication_type->get_publication_type_name() !== $post_type )
        {
            $this->addToAssertionCount(1);
            return;
        }

        $method = $secondary_publication_type_class->getMethod('validate_and_process_data');
        $method->setAccessible(true);
        $validation_result = $method->invokeArgs($secondary_publication_type, array($post_id));

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
            [9],
            [10],
        ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider on_post_actually_published_provider
         * @depends test_create_primary_publication_type
         */
    public function test_primary_on_post_actually_published( $post_id, $primary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

        $class = new ReflectionClass('O3PO_PrimaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() !== $post_type )
        {
            $this->addToAssertionCount(1);
            return;
        }

        $method = $class->getMethod('on_post_actually_published');
        $method->setAccessible(true);
        $validation_result = $method->invokeArgs($primary_publication_type, array($post_id));

        $this->assertRegexp('#INFO: This .* was publicly published#', $validation_result);
        $this->assertFalse(strpos('ERROR', $validation_result));
    }


        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider on_post_actually_published_provider
         * @depends test_create_secondary_publication_type
         */
    public function test_secondary_on_post_actually_published( $post_id, $secondary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

        $class = new ReflectionClass('O3PO_SecondaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $secondary_publication_type->get_publication_type_name() !== $post_type )
        {
            $this->addToAssertionCount(1);
            return;
        }

        $method = $class->getMethod('on_post_actually_published');
        $method->setAccessible(true);
        $validation_result = $method->invokeArgs($secondary_publication_type, array($post_id));

        $this->assertRegexp('#INFO: This .* was publicly published#', $validation_result);
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
                 '_date_published' => current_time("Y-m-d"),
                 '_journal' => 'q',
                 '_volume' => '1',
                 '_pages' => 's',
                 '_corresponding_author_email' => 't',
                 '_buffer_email' => 'u',
                 '_buffer_special_text' => 'v',
                 '_bbl' => 'w',
                 '_nonce' => 'fake_nonce',
                   ),
             array(),
             ],
            [5,
             array(
                 '_eprint' => '0809.2542v4',
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                 '_nonce' => 'fake_nonce',
                   ),
             array(
                 '#ERROR: It seems like .* is not published under .* creative commons#',
                   ),
             ],
            [8,
             array(
                 '_eprint' => '1609.09584v4',
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                 '_nonce' => 'fake_nonce',
                   ),
             array(
                 '#SUCCESS: Fetched meta-data from https://arxiv.org/abs/1609\.09584v4#',
                   ),
             ],
            [9,
             array(
                 '_number_authors' => 1,
                 '_number_reviewers' => 2,
                 '_nonce' => 'fake_nonce',
             ),
             array()
             ],
            [10,
             array(
                 '_number_authors' => 1,
                 '_number_reviewers' => 3,
                 '_nonce' => 'fake_nonce',
             ),
             array()
             ],
                ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider save_meta_data_provider
         * @depends test_create_primary_publication_type
         */
    public function test_primary_save_meta_data( $post_id, $POST_args, $expections, $primary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

        $post_type = get_post_type($post_id);

        foreach($POST_args as $key => $value)
        {
            $_POST[ $post_type . $key ] = $value;
        }

        $class = new ReflectionClass('O3PO_PrimaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() !== $post_type )
        {
            $this->addToAssertionCount(1);
            return;
        }

        $method = $class->getMethod('save_meta_data');
        $method->setAccessible(true);
        $method->invokeArgs($primary_publication_type, array($post_id));


        if(!empty($POST_args['_fetch_metadata_from_arxiv']))
        {
            foreach($expections as $expection)
            {
                $this->assertRegexp($expection, get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true));
            }
        }
        else
        {
            foreach($POST_args as $key => $value)
            {
                if( $key === '_nonce')
                    continue;
                $this->assertSame($value, get_post_meta( $post_id, $post_type . $key, true), 'Property ' . $post_type . $key . ' was not set correctly.');
            }
        }
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider save_meta_data_provider
         * @depends test_create_secondary_publication_type
         */
    public function test_secondary_save_meta_data( $post_id, $POST_args, $expections, $secondary_publication_type ) {
        $post_type = get_post_type($post_id);

        foreach($POST_args as $key => $value)
        {
            $_POST[ $post_type . $key ] = $value;
        }

        $class = new ReflectionClass('O3PO_SecondaryPublicationType');

        $post_type = get_post_type($post_id);
        if ( $secondary_publication_type->get_publication_type_name() !== $post_type )
        {
            $this->addToAssertionCount(1);
            return;
        }

        $method = $class->getMethod('save_meta_data');
        $method->setAccessible(true);
        $method->invokeArgs($secondary_publication_type, array($post_id));


        if(!empty($POST_args['_fetch_metadata_from_arxiv']))
        {
            foreach($expections as $expection)
            {
                $this->assertRegexp($expection, get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true));
            }
        }
        else
        {
            foreach($POST_args as $key => $value)
            {
                if( $key === '_nonce')
                    continue;
                $this->assertSame($value, get_post_meta( $post_id, $post_type . $key, true), 'Property ' . $post_type . $key . ' was not set correctly.');
            }
        }
    }


        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         */
    public function save_metabox_provider() {
        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();
        $settings = O3PO_Settings::instance();

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
                 '_date_published' => current_time("Y-m-d"),
                 '_journal' => 'q',
                 '_volume' => '1',
                 '_pages' => '1',
                 '_corresponding_author_email' => 't',
                 '_buffer_email' => 'u',
                 '_buffer_special_text' => 'v',
                 '_bbl' => 'w',
                 '_nonce' => 'fake_nonce',
                   ),
             array(
                 '#ERROR: Eprint is empty\.#',
                 '#ERROR: Abstract is empty\.#',
                 '#ERROR: Cannot add licensing information, no pdf attached to post.*#',
                 '#WARNING: The page number .* of this paper is not exactly one larger#',
                 '#ERROR: ORCID of author .* is malformed\.#',
                 '#ERROR: Corresponding author email is malformed\.#',
                 '#ERROR: Unable to generate XML for Crossref#',
                 '#ERROR: Unable to generate JSON for DOAJ#',
                 '#ERROR: Unable to generate XML for CLOCKSS#',
                   ),
             array(
                 '#ERROR: Eprint is empty\.#',
                 '#ERROR: Abstract is empty\.#',
                 '#ERROR: Cannot add licensing information, no pdf attached to post.*#',
                 '#WARNING: The page number .* of this paper is not exactly one larger#',
                 '#ERROR: ORCID of author .* is malformed\.#',
                 '#ERROR: Corresponding author email is malformed\.#',
                 '#ERROR: Unable to generate XML for Crossref#',
                 '#ERROR: Unable to generate JSON for DOAJ#',
                 '#ERROR: Unable to generate XML for CLOCKSS#',
             ),
             ],
            [5,
             array(
                 '_eprint' => '0809.2542v4',
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                 '_nonce' => 'fake_nonce',
                 '_doi_suffix' => 'fake doi_suffix',
                 '_pages' => '2',
                 '_date_published' => current_time("Y-m-d"),
                 '_volume' => '2',
                 '_corresponding_author_email' => 'foo@bar.com',
                 '_journal' => $settings->get_plugin_option('journal_title'),
                   ),
             array(
                 '#ERROR: It seems like .* is not published under .* creative commons#',
                 '#REVIEW: The pdf was downloaded successfully from the arXiv\.#',
                 '#REVIEW: The source was downloaded successfully from the arXiv .* and is of mime-type application/x-gzip#',
                 '#REVIEW: Found BibTeX or manually formated bibliographic information#',
                 '#REVIEW: Author and affiliations data updated from arxiv source#',
                 '#REVIEW: Bibliographic information updated.#',
                 '#REVIEW: The doi suffix was set#',
                 '#WARNING: Not yet published.#',
                   ),
             array(
                 '#INFO: This paper was publicly published\.#',
                   ),
             ],
            [8,
             array(
                 '_eprint' => '0908.2921v2',
                 '_pages' => '3',
                 '_date_published' => get_the_date('Y-m-d', 8),
                 '_volume' => '2',
                 '_corresponding_author_email' => 'baz@bar.com',
                 '_number_authors' => 2,
                 '_author_given_names' => ['Foo', 'Baz'],
                 '_author_surnames' => ['Bar', 'Boo'],
                 '_author_name_styles' => ["western", "western"],
                 '_author_affiliations' => ['1,2','2'],
                 '_author_orcids' => ['',''],
                 '_author_urls' => ['',''],
                 '_number_affiliations' => 2,
                 '_affiliations' => ['Foo University', 'Bar Institut'],
                 '_journal' => $settings->get_plugin_option('journal_title'),
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                 '_nonce' => 'fake_nonce',
                 '_buffer_email' => 'checked',
                   ),
             array(
                 '#ERROR: It seems like .* not published .* creative commons license#',
                 '#INFO: This paper was publicly published\.#',
                 '#INFO: Email to buffer.com sent correctly\.#',
                   ),
             array(
                 '#INFO: This paper was publicly published\.#',
             ),
             ],
            [11,
             array(
                 '_eprint' => '1806.02820v3',
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                 '_nonce' => 'fake_nonce',
                 '_doi_suffix' => 'fake doi_suffix',
                 '_pages' => '4',
                 '_date_published' => current_time("Y-m-d"),
                 '_volume' => '2',
                 '_corresponding_author_email' => 'foo@bar.com',
                 '_journal' => $settings->get_plugin_option('journal_title'),
                   ),
             array(
                 '#REVIEW: Author and affiliations data updated from arxiv source. Please check\.#',
                 '#SUCCESS: Fetched meta-data from.*#',
                   ),
             array(
                 '#INFO: This paper was publicly published\.#',
                   ),
             ],
                ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider save_metabox_provider
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_save_metabox( $post_id, $POST_args, $expections_first, $expections_second, $primary_publication_type, $secondary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

        if(!defined('ABSPATH'))
            define( 'ABSPATH', dirname( __FILE__ ) . '/resources/' );

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() == $post_type )
            $class = new ReflectionClass('O3PO_PrimaryPublicationType');
        else
            $class = new ReflectionClass('O3PO_SecondaryPublicationType');

        $method = $class->getMethod('save_metabox');
        $method->setAccessible(true);

        foreach($POST_args as $key => $value)
            $_POST[ $post_type . $key ] = $value;
        $method->invokeArgs($primary_publication_type, array($post_id, new WP_Post($post_id) ));

        $validation_result = get_post_meta( $post_id, $post_type . '_validation_result');
        foreach($expections_first as $expection)
        {
            $this->assertRegexp($expection, $validation_result);
        }

            //call it again to potentially trigger a post actually published event
        set_post_status($post_id, 'publish');
        foreach(get_all_post_metas($post_id) as $key => $value)
            $_POST[ $key ] = $value;
        $method->invokeArgs($primary_publication_type, array($post_id, new WP_Post($post_id) ));

        $validation_result = get_post_meta( $post_id, $post_type . '_validation_result');
        foreach($expections_second as $expection)
        {
            $this->assertRegexp($expection, $validation_result);
        }


    }

        /**
         * Tests whether publishing via scheduling has the same final outcome as publishing directly.
         *
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider save_metabox_provider
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_on_transition_post_status( $post_id, $POST_args, $expections_first, $expections_second, $primary_publication_type, $secondary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

        if(!defined('ABSPATH'))
            define( 'ABSPATH', dirname( __FILE__ ) . '/resources/' );

        $post_type = get_post_type($post_id);
        if ( $primary_publication_type->get_publication_type_name() == $post_type )
            $class = new ReflectionClass('O3PO_PrimaryPublicationType');
        else
            $class = new ReflectionClass('O3PO_SecondaryPublicationType');

        $method = $class->getMethod('save_metabox'); //calls save_meta_data() but also does some further things
        $method->setAccessible(true);

        set_post_status($post_id, 'private');
        foreach($POST_args as $key => $value)
            $_POST[ $post_type . $key ] = $value;
        $method->invokeArgs($primary_publication_type, array($post_id, new WP_Post($post_id, $post_type) ));

        set_post_status($post_id, 'future');
        foreach(get_all_post_metas($post_id) as $key => $value)
            $_POST[ $key ] = $value;

        $method->invokeArgs($primary_publication_type, array($post_id, new WP_Post($post_id, $post_type) ));
        $method = $class->getMethod('on_transition_post_status');

        foreach(get_all_post_metas($post_id) as $key => $value)
            $_POST[ $key ] = $value;
        $method->invokeArgs($primary_publication_type, array("future", "private", new WP_Post($post_id, $post_type) ));

            //call it again to potentially trigger a post actually published event
        set_post_status($post_id, 'publish');
        foreach(get_all_post_metas($post_id) as $key => $value)
            $_POST[ $key ] = $value;
        $method->invokeArgs($primary_publication_type, array('publish', 'future', new WP_Post($post_id, $post_type) ));

        $validation_result = get_post_meta( $post_id, $post_type . '_validation_result');
        foreach($expections_second as $expection)
        {
            $this->assertRegexp($expection, $validation_result);
        }


    }

        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         * @depends test_initialize_settings
         */
    public function test_register_as_custom_post_type( $primary_publication_type, $secondary_publication_type, $settings ) {
        $primary_publication_type->register_as_custom_post_type();
        $secondary_publication_type->register_as_custom_post_type();
    }

        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_init_metabox( $primary_publication_type, $secondary_publication_type) {
        $primary_publication_type->init_metabox();
        $secondary_publication_type->init_metabox();
    }

        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_add_metabox( $primary_publication_type, $secondary_publication_type) {
        $primary_publication_type->add_metabox();
        $secondary_publication_type->add_metabox();
    }


        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_add_custom_post_types_to_query( $primary_publication_type, $secondary_publication_type) {
        $primary_publication_type->add_custom_post_types_to_query( new WP_Query() );
        $secondary_publication_type->add_custom_post_types_to_query( new WP_Query() );
    }

        /**
         * @depends test_setup_primary_journal
         * @depends test_setup_environment
         */
    public function test_volumes_endpoint_overview( $journal, $environment )
    {

        $query_vars = array($journal->get_journal_property('volumes_endpoint') => "/");
        $wp_query = new WP_Query(null , $query_vars);

        $journal->handle_volumes_endpoint_request($wp_query);

        $journal->add_fake_post_to_volume_overview_page(array());
        $this->assertSame('page.php', $journal->volume_endpoint_template('original-template.php'));

        ob_start();
        $journal->volume_navigation_at_loop_start(get_global_query());
        $output = ob_get_contents();
        ob_end_clean();
        $output = preg_replace('#(main|header)#', 'div', $output); # this is a brutal hack because $dom->loadHTML cannot cope with html 5

        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
            //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
        $this->assertNotFalse($result);

    }

    function volumes_endpoint_volume_1_provider() {

        return [
            ["1/" ],
            ["1/page/1"],
            ["1/page/2"],
        ];
    }

        /**
         * @dataProvider volumes_endpoint_volume_1_provider
         * @depends test_setup_primary_journal
         * @depends test_setup_environment
         */
    public function test_volumes_endpoint_volume_1( $query_var_extra, $journal, $environment )
    {
        $wp_query = new WP_Query(null , null);
        #first check that nothing is output/changed if query doesn't match
        $this->assertSame('original-template.php', $journal->volume_endpoint_template('original-template.php'));
        $this->assertSame(array(), $journal->add_fake_post_to_volume_overview_page(array()));

        ob_start();
        $journal->handle_volumes_endpoint_request($wp_query);
        $journal->volume_navigation_at_loop_start(get_global_query());
        $journal->compress_entries_in_volume_view(get_global_query());
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEmpty($output);

        $query_vars = array($journal->get_journal_property('volumes_endpoint') => $query_var_extra);
        $wp_query = new WP_Query(null , $query_vars);

        $journal->handle_volumes_endpoint_request($wp_query);
        ob_start();
        $journal->volume_navigation_at_loop_start(get_global_query());
        $journal->compress_entries_in_volume_view(get_global_query());
        $output = ob_get_contents();
        ob_end_clean();
        $output = preg_replace('#(main|header)#', 'div', $output); # this is a brutal hack because $dom->loadHTML cannot cope with html 5

        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
            //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
        $this->assertNotFalse($result);
    }


        /**
         * @depends test_setup_primary_journal
         */
    public function test_execution_of_various_journal_functions( $journal ) {

        define( 'EP_ROOT', 'EP_ROOT' );
        $journal->add_volumes_endpoint();

        $this->expectException(Exception::class);
        $journal->get_journal_property('non-existing-id');

    }

        /**
         * @depends test_setup_primary_journal
         */
    public function test_search_form_additions( $journal ) {

        $neither_main_nor_search_query = new WP_Query('some query');
        ob_start();
        $this->assertSame($neither_main_nor_search_query, $journal->add_notice_to_search_results_at_loop_start($neither_main_nor_search_query));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEmpty($output);

        $form = '<div>fake search form</div>';
        $this->assertSame($form, $journal->add_notice_to_search_form($form));

        set_global_query(new WP_Query("ID=1341351341341349889" , array('s' => 'search term'))); #global search query that will not yields results, so we expect the search form to be modified
        $this->assertNotSame($form, $journal->add_notice_to_search_form($form));
        $dom = new DOMDocument;
        $result = $dom->loadHTML('<div>' . $journal->add_notice_to_search_form($form) . '</div>');
        $this->assertNotFalse($result);

        global $_GET;
        $_GET["reason"]="title-click";

        #first a query with no posts
        $main_search_query = new WP_Query(null, array('s' => 'search term', 'is_main' => true));
        set_global_query($main_search_query);
        ob_start();
        $this->assertSame($main_search_query, $journal->add_notice_to_search_results_at_loop_start($main_search_query));
        $output = ob_get_contents();
        ob_end_clean();

        $dom = new DOMDocument;
        $result = $dom->loadHTML('<div>' . $output . '</div>');
        $this->assertNotFalse($result);

        #and then one that has posts
        $main_search_query = new WP_Query("post_type=paper", array('s' => 'search term', 'is_main' => true));
        set_global_query($main_search_query);
        ob_start();
        $this->assertSame($main_search_query, $journal->add_notice_to_search_results_at_loop_start($main_search_query));
        $output = ob_get_contents();
        ob_end_clean();

        $dom = new DOMDocument;
        $result = $dom->loadHTML('<div>' . $output . '</div>');
        $this->assertNotFalse($result);
    }


        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    function test_get_all_citation_counts( $primary_publication_type, $secondary_publication_type ) {

        $this->assertSame($primary_publication_type->get_all_citation_counts()['citation_count']['10.22331/q-2017-04-25-8'], 43);

        $cited_by_data = $primary_publication_type->get_cited_by_data(12);
        $this->assertSame($cited_by_data['citation_count'], count($cited_by_data['all_bibentries']));

        $dom = new DOMDocument;
        $result = $dom->loadHTML('<div>' . $cited_by_data['html'] . '<div>');
            //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
        $this->assertNotFalse($result);

    }

        /**
         * @doesNotPerformAssertions
         */
    public function test_cleanup_at_the_very_end() {
        exec('git checkout ' . dirname(__File__) . '/resources/arxiv/0809.2542v4.pdf');
        O3PO_Environment::save_recursive_remove_dir(dirname(__File__) . "/resources/tmp/", dirname(__File__));
    }
    #do not add tests after this one!
}
