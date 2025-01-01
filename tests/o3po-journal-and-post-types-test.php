<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-environment.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-journal.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-primary-publication-type.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-secondary-publication-type.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-latex.php');
require_once(dirname( __FILE__ ) . '/../o3po/admin/class-o3po-admin.php');
require_once(dirname( __FILE__ ) . '/o3po-settings-test.php');

class O3PO_JournalAndPublicationTypesTest extends O3PO_TestCase
{

    public function test_initialize_settings() {

        $settings = O3PO_SettingsTest::get_settings();

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

        $environment = new O3PO_Environment($settings->get_field_value("production_site_url"));
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
            ['the_admin_panel_buffer'],
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
            ['admin_page_extra_css'],
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
            $this->assertValidHTMLFragment($output);

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
            ['the_admin_panel_buffer'],
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
            $this->assertValidHTMLFragment($output);

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

            $this->assertValidHTMLFragment($output);

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

            $this->assertValidHTMLFragment($output);

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
                         '#' . $settings->get_field_value('license_url')  . '#',
                         '#' . $settings->get_field_value('publisher')  . '#',
                           )
                         as $regexp)
                    $this->assertRegexpCompat($regexp, $content);
            }
            else
                $this->assertSame($orig_content, $content);

            $output = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5

            $this->assertValidHTMLFragment($output);

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
            if($post_type == $primary_publication_type->get_publication_type_name())
            {
                foreach( array(
                         '#' . $settings->get_field_value('license_url')  . '#',
                         '#' . $settings->get_field_value('publisher')  . '#',
                           )
                         as $regexp)
                    $this->assertRegexpCompat($regexp, $content);
            }
            else
                $this->assertSame($orig_content, $content);

            $content = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5
            $this->assertValidHTMLFragment($content);

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
            ['q-test-1742-04-01', false],
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

        $settings = O3PO_SettingsTest::get_settings();

        return [
            [dirname(__FILE__) . '/resources/arxiv/2006.12475v4.tar.gz', "application/x-tar", array(
                    "affiliations" => array("#Department of Physics, University of Illinois at Urbana-Champaign, Urbana, IL 61801, USA#", "#Department of Electrical and Computer Engineering, University of Illinois at Urbana-Champaign, Urbana, IL 61801, USA#"),
                    "author_affiliations" => '/1###2###2/u',
                    "validation_result" => array("#REVIEW: Found BibTeX or manually formated bibliographic information#"),
                    "bbl" => array('#Cover-2006a#', '#Giovannetti-2011#'),
                    'num_dois' => 53,
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/2107.12944v2.tar.gz', "application/x-tar", array(
                    "affiliations" => array("#Department of Physics, University of Basel, Klingelbergstrasse 82, 4056 Basel, Switzerland#", "#Laboratoire Kastler Brossel, ENS-Université PSL, CNRS, Sorbonne Université, Collège de France, 24 Rue Lhomond, 75005, Paris, France#", "#Department of Physics, ETH Zürich, 8093 Zürich, Switzerland#", "#ICFO-Institut de Ciències Fotòniques, The Barcelona Institute of Science and Technology, Av. Carl Friedrich Gauss 3, 08860, Castelldefels \(Barcelona\), Spain#"),
                    "author_affiliations" => '/1,3###2,4/u', # actually "correct" would be /1,2###3,4/u but there are inconsistent affiliations in multiple files in this submission and I have no idea how to handle this so that the "expected" result is always returned
                    "validation_result" => array("#REVIEW: Found BibTeX or manually formated bibliographic information#"),
                    "bbl" => array('#NielsenChuang#', '#StreltsovRMP2017#'),
                    'num_dois' => 59,
                                                                                                    )],






            [dirname(__FILE__) . '/resources/arxiv/2202.11338v4.tar.gz', "application/x-tar", array(
                    "affiliations" => array("#Hon Hai Quantum Computing Research Center, Taipei, Taiwan#"),
                    "author_affiliations" => '/1###2,3###1/u',
                    "validation_result" => array("#REVIEW: Found BibTeX or manually formated bibliographic information#"),
                    "bbl" => array('#PhysRevA\.85\.042311#'),
                    'num_dois' => 70,
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1711.04662v3.tar.gz', "application/gzip", array(
                    "affiliations" => array('#Aix-Marseille Univ, Université de Toulon, CNRS, LIS, Marseille, and IXXI, Lyon, France#', '#Aix-Marseille Univ, Université de Toulon, CNRS, LIS, Marseille, France and Departamento de Física Teórica and IFIC, Universidad de Valencia-CSIC, Dr. Moliner 50, 46100-Burjassot, Spain#', '#Aix-Marseille Univ, Université de Toulon, CNRS, LIS, Marseille, France#'),
                    "author_affiliations" => '/1###2###3/u',
                    "validation_result" => array("#REVIEW: Found BibTeX or manually formated bibliographic information#", "#REVIEW: Author homepage data updated from arxiv source#"),
                    "bbl" => array('#\\\\begin{thebibliography}#', '#ahlbrecht2012molecular#', '#venegas2012quantum#'),
                    'num_dois' => 28,
                                                                                                   )],
            [dirname(__FILE__) . '/resources/arxiv/0809.2542v4.tar.gz', "application/x-tar", array(
                    "validation_result" => "#REVIEW: Found BibTeX or manually formated bibliographic information#",
                    "author_affiliations" => '/1/u',
                    "affiliations" => '#Fakultät für Physik und Astronomie, Universität Würzburg, Am Hubland, 97074 Würzburg, Germany#',
                    "author_affiliations" => '/1/u',
                    "bbl" => array('#\\\\begin{thebibliography}#', '#DeGennes#', '#Ising#'),
                    'num_dois' => 0,
                                                                                                   )],
            [dirname(__FILE__) . '/resources/arxiv/1708.05489v2.tar.gz', "application/gz", array(
                    "validation_result" => array("#REVIEW: Found BibTeX or manually formated bibliographic information#", '#REVIEW: Author and affiliations data updated from arxiv source.#'),
                    "author_affiliations" => array('/1###2###1/u'),
                    "affiliations" => array('#Institute of Theoretical Physics, Faculty of Physics, University of Warsaw, Pasteura 5, 02-093 Warsaw, Poland#', '#Department of Physics, Saint Anselm College, Manchester, NH 03102, USA#'),
                    "bbl" => array('#\\\\begin{thebibliography}#', '#\\[Abdolrahimi\\(2014\\)\\]{Abdolrahimi:2014aa}#', '#\\\\end{thebibliography}#'),
                    'num_dois' => 21,
                                                                                                 )],
            [dirname(__FILE__) . '/resources/arxiv/0908.2921v2.tex', "text/tex", array(
                    "validation_result" => array('#Found BibTeX or manually formated bibliograph#', '#Author and affiliations data updated from arxiv source#'),
                    "author_affiliations" => '/1/u',
                    "affiliations" => '#Fakultät für Physik und Astronomie, Universität Würzburg, Am Hubland, 97074 Würzburg, Germany#',
                    "bbl" => '#\\\\begin{thebibliography}#',
                    'num_dois' => 0,
                                                                                       )],
            [dirname(__FILE__) . '/resources/arxiv/1704.02130v3.tar.gz', "application/x-tar", array(
                    "validation_result" => array('#REVIEW: Found BibLaTeX formated bibliographic information#', '#Author and affiliations data updated from arxiv source#'),
                    "affiliations" => '#Laboratoire d\'Information Quantique, CP 224, Université libre de Bruxelles \(ULB\), 1050 Brussels, Belgium#',
                    "author_affiliations" => '/1/u',
                    "bbl" => array( '#biblatex auxiliary file#', '#entry{AM16}{article}{}#', '#verb 10\.1109/ISIT\.2002\.1023345#' ),
                    'num_dois' => 17,
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1603.04424v3.tar.gz', "application/x-tar", array(
                    "author_affiliations" => array("/1###1###2###1,3/u"),
                    'num_dois' => 21,
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1610.00336v2.tar.gz', "application/x-tar", array(
                    #"author_affiliations" => array("/1,2###3###4,5######5,6###7###5,6###5,6,8/u"), # affiliations are not auto recognizable
                    'num_dois' => 54,
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1610.06169.tar.gz', "application/x-tar", array(
                    'num_dois' => 48,
                    "author_affiliations" => array("/1,2###3,2###4###5,6,7/u"),
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1801.03508.tar.gz', "application/x-tar", array(
                    'num_dois' => 25,
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1812.11437v3.tar.gz', "application/x-tar", array(
                    "validation_result" => array('#Author and affiliations data updated from arxiv source#'),
                    "affiliations" => array('#Instituto de Física, Universidad Nacional Autónoma de México, México, D.F., México#', '#Institute of Physics, Slovak Academy of Sciences, Dúbravská cesta 9, Bratislava 84511, Slovakia#', '#Faculty of Informatics, Masaryk University, Botanická 68a, 60200 Brno, Czech Republic#', '#Faculty of Physics, University of Vienna, 1090 Vienna, Austria#'),
                    'num_dois' => 24,
                    "author_affiliations" => array("/1###2,3###1,4/u"),
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1902.02359v2.tar.gz', "application/x-tar", array(
                    "validation_result" => array('#Author and affiliations data updated from arxiv source#'),
                    "author_affiliations" => array("/1,3###2,3###1,2,3/u"),
                                                                                                    )],

            [dirname(__FILE__) . '/resources/arxiv/2006.01273v3.tar.gz', "application/x-tar", array(
                    "validation_result" => array('#Author and affiliations data updated from arxiv source#'),
                    #"author_affiliations" => array("/1,3###2,3###1,2,3/u"),
                    'num_dois' => 82,
                                                                                                    )],
            [dirname(__FILE__) . '/resources/arxiv/1902.02110v6.tar.gz', "application/x-tar", array(
                    "validation_result" => array('#Author and affiliations data updated from arxiv source#'),
                    "affiliations" => array("#Department of Statistical Methods, Faculty of Economics and Sociology University of Lodz, 41/43 Rewolucji 1905 St., 90-214 Lodz, Poland#", "#Department of Computer Science, Faculty of Physics and Applied Informatics University of Lodz, 149/153 Pomorska St., 90-236 Lodz, Poland#"),
                    "author_affiliations" => array("/1###2###2/u"),
                    'num_dois' => 58,
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
                    $this->assertRegexpCompat($expect, $result);
            }
        }

        # We do some more in-depth parsing of the extracted bbl:
        $bbl = $parse_publication_source_result['bbl'];
        if(!empty($bbl))
        {
            $new_author_latex_macro_definitions = $parse_publication_source_result['author_latex_macro_definitions'];
            $new_author_latex_macro_definitions_without_specials = O3PO_Latex::remove_special_macros_to_ignore_in_bbl($new_author_latex_macro_definitions);
            $bbl = O3PO_Latex::expand_latex_macros($new_author_latex_macro_definitions_without_specials, $bbl);

            $parsed_bbl = O3PO_Latex::parse_bbl($bbl);
            $num_dois = 0;
            foreach($parsed_bbl as $n => $entry) {

                $output = $primary_publication_type->get_formated_bibliography_entry_html($entry);
                $this->assertFalse( O3PO_Latex::strpos_outside_math_mode($output, '\\'), "The text " . $entry['text'] . " extracted from the bbl in " . $path_source . " still contains one ore more backslashes that were not caught by parse_bbl. Full entry:" . json_encode($entry));
                $this->assertValidHTMLFragment($output);

                if( !empty($entry['doi']) )
                    $num_dois += 1;
            }

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
            $this->assertRegexpCompat($expection, $validation_result);
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
            $this->assertRegexpCompat($expection, $validation_result);
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

        $this->assertRegexpCompat('#INFO: This .* was publicly published#', $validation_result);
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

        $this->assertRegexpCompat('#INFO: This .* was publicly published#', $validation_result);
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
                $this->assertRegexpCompat($expection, get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true));
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

        #$settings is not used but save_meta_data() needs the settings to be initialized
        $this->test_initialize_settings();

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
                $this->assertRegexpCompat($expection, get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true));
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
        $settings = O3PO_SettingsTest::get_settings();

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
                 '_journal' => $settings->get_field_value('journal_title'),
                 '_buffer_email' => 'checked',
                   ),
             array(
                 '#ERROR: It seems like .* is not published under .* creative commons#',
                 '#REVIEW: The pdf was downloaded successfully from the arXiv\.#',
                 '#REVIEW: The source was downloaded successfully from the arXiv .* and is of mime-type application/.*gzip#',
                 '#REVIEW: Found BibTeX or manually formated bibliographic information#',
                 '#REVIEW: Author and affiliations data updated from arxiv source#',
                 '#REVIEW: Bibliographic information updated.#',
                 '#REVIEW: The doi suffix was set#',
                 '#WARNING: Not yet published.#',
                   ),
             array(
                 '#INFO: Update about this publication posted to buffer\.com queue\.#',#this is expected to fail because the corresponding buffer url returns invalid json
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
                 '_journal' => $settings->get_field_value('journal_title'),
                 '_number_authors' => 4,
                 '_fetch_metadata_from_arxiv' => 'checked',
                 '_nonce' => 'fake_nonce',
                 '_buffer_email' => 'checked',
                   ),
             array(
                 '#ERROR: It seems like .* not published .* creative commons license#', #upon the second publish attempt this ERROR is downgraded to a WARNING and then publishing is possible
                 '#INFO: This paper was publicly published\.#',
                 '#INFO: Update about this publication posted to buffer\.com queue\.#',
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
                 '_journal' => $settings->get_field_value('journal_title'),
                 '_buffer_email' => 'checked',
                   ),
             array(
                 '#REVIEW: Author and affiliations data updated from arxiv source. Please check\.#',
                 '#SUCCESS: Fetched meta-data from.*#',
                   ),
             array(
                 '#INFO: Update about this publication posted to buffer\.com queue\.#',
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
         * @depends test_setup_environment
         */
    public function test_save_metabox( $post_id, $POST_args, $expections_first, $expections_second, $primary_publication_type, $secondary_publication_type ) {

        #init settings here instead of depending on test_initialize_settings because O3PO_Settings is a singleton
        $this->test_initialize_settings();

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
            $this->assertRegexpCompat($expection, $validation_result);
        }

            //call it again to potentially trigger a post actually published event on the second try in case there was something to REVIEW in the first run
        set_post_status($post_id, 'publish');
        foreach(get_all_post_metas($post_id) as $key => $value)
            $_POST[ $key ] = $value;
        $method->invokeArgs($primary_publication_type, array($post_id, new WP_Post($post_id) ));

        $validation_result = get_post_meta( $post_id, $post_type . '_validation_result');
        foreach($expections_second as $expection)
        {
            $this->assertRegexpCompat($expection, $validation_result);
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
            $this->assertRegexpCompat($expection, $validation_result);
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
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_add_custom_post_types_to_query( $primary_publication_type, $secondary_publication_type) {

        global $is_home;
        $is_home = true;

        $query = new WP_Query(null, array('is_main' => true));
        $primary_publication_type->add_custom_post_types_to_query($query);
        $this->assertEquals(array(null, 'post', $primary_publication_type->get_publication_type_name()), $query->get('post_type'));

        $query = new WP_Query(null, array('is_main' => true));
        $secondary_publication_type->add_custom_post_types_to_query($query);
        $this->assertEquals(array(null, 'post', $secondary_publication_type->get_publication_type_name()), $query->get('post_type'));

    }


        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_add_custom_post_types_to_rss_feed( $primary_publication_type, $secondary_publication_type) {

        $request = array('feed' => true);
        $request = $primary_publication_type->add_custom_post_types_to_rss_feed($request);
        $this->assertEquals(array('feed' => true, 'post_type' => array('post', $primary_publication_type->get_publication_type_name())), $request);

        $request = array('feed' => true, 'post_type' => 'some_post_type');
        $request = $primary_publication_type->add_custom_post_types_to_rss_feed($request);
        $this->assertEquals(array('feed' => true, 'post_type' => 'some_post_type'), $request);



        $request = array('feed' => true);
        $request = $secondary_publication_type->add_custom_post_types_to_rss_feed($request);
        $this->assertEquals(array('feed' => true, 'post_type' => array('post', $secondary_publication_type->get_publication_type_name())), $request);

        $request = array('feed' => true, 'post_type' => 'some_post_type');
        $request = $secondary_publication_type->add_custom_post_types_to_rss_feed($request);
        $this->assertEquals(array('feed' => true, 'post_type' => 'some_post_type'), $request);


    }


        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_the_author_feed( $primary_publication_type, $secondary_publication_type) {

        global $posts;
        global $post;
        global $is_feed;

        $is_feed_orig = $is_feed;

        foreach($posts as $post_id => $post_data)
        {
            $post = new WP_Post($post_id);
            set_global_query(new WP_Query(array('ID' => $post_id)));
            the_post();

            $post_type = get_post_type($post_id);
            $orgi_name = 'Foo Bar';

            $is_feed = false;
            $this->assertSame( $orgi_name, $primary_publication_type->the_author_feed($orgi_name));
            $this->assertSame( $orgi_name, $secondary_publication_type->the_author_feed($orgi_name));

            $is_feed = true;

            if($post_type == $primary_publication_type->get_publication_type_name())
                $this->assertSame( $primary_publication_type->get_formated_authors($post_id), $primary_publication_type->the_author_feed($orgi_name));
            elseif($post_type == $secondary_publication_type->get_publication_type_name())
                $this->assertSame( $secondary_publication_type->get_formated_authors($post_id), $secondary_publication_type->the_author_feed($orgi_name));
            else
            {
                $this->assertSame( $orgi_name, $primary_publication_type->the_author_feed($orgi_name));
                $this->assertSame( $orgi_name, $secondary_publication_type->the_author_feed($orgi_name));
            }
        }

        $is_feed = $is_feed_orig;
    }






        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_get_the_author_and_get_the_author_posts_link( $primary_publication_type, $secondary_publication_type) {

        global $posts;
        global $post;

        foreach($posts as $post_id => $post_data)
        {
            $post = new WP_Post($post_id);
            set_global_query(new WP_Query(array('ID' => $post_id)));
            the_post();

            $post_type = get_post_type($post_id);
            $orig_name = 'Foo Bar';
            $orig_link = '/foo/bar/';

            if($post_type == $primary_publication_type->get_publication_type_name())
            {
                $journal = $primary_publication_type->get_post_meta( $post_id, 'journal' );
                $this->assertSame( $journal, $primary_publication_type->get_the_author($orig_name));
                $link = '/' . $primary_publication_type->get_publication_type_name_plural();
                $this->assertSame($link, $primary_publication_type->get_the_author_posts_link($orig_link));

            }
            elseif($post_type == $secondary_publication_type->get_publication_type_name())
            {
                $journal = $secondary_publication_type->get_post_meta( $post_id, 'journal' );
                $this->assertSame( $journal, $secondary_publication_type->get_the_author($orig_name));
                $link = '/' . $secondary_publication_type->get_publication_type_name_plural();
                $this->assertSame($link, $secondary_publication_type->get_the_author_posts_link($orig_link));
            }
            else
            {
                $this->assertSame( $orig_name, $primary_publication_type->get_the_author($orig_name));
                $this->assertSame( $orig_name, $secondary_publication_type->get_the_author($orig_name));


                $this->assertSame($orig_link, $primary_publication_type->get_the_author_posts_link($orig_link));
                $this->assertSame($orig_link, $secondary_publication_type->get_the_author_posts_link($orig_link));
            }
        }

    }





        /**
         * @depends test_create_primary_publication_type
         */
    public function test_get_last_arxiv_source_url( $primary_publication_type ) {
        global $posts;

        $class = new ReflectionClass('O3PO_PrimaryPublicationType');

        $method = $class->getMethod('get_last_arxiv_source_url');
        $method->setAccessible(true);

        foreach($posts as $post_id => $post_data)
        {
            $post_type = get_post_type($post_id);
            if ( $primary_publication_type->get_publication_type_name() !== $post_type )
                continue;

            $last_source_url = $method->invokeArgs($primary_publication_type, array($post_id));

            try
            {
                $source_attach_ids = $post_data['meta']['paper_arxiv_source_attach_ids'];
                if(!empty($source_attach_ids))
                    $this->assertSame($posts[end($source_attach_ids)]['attachment_url'], $last_source_url);
                else
                    $this->assertEmpty($last_source_url);
            }
            catch(Exception $e)
            {
                $this->assertEmpty($last_source_url);
            }
        }

    }

        /**
         * @depends test_create_primary_publication_type
         * @depends test_initialize_settings
         */
    public function test_primary_get_feed_content( $primary_publication_type, $settings ) {
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
            $content = $primary_publication_type->get_feed_content($orig_content);

            $post_type = get_post_type($post_id);
            if($post_type == $primary_publication_type->get_publication_type_name())
            {
                foreach( array(
                             '#' . $settings->get_field_value('doi_url_prefix')  . '#',
                           )
                         as $regexp)
                    $this->assertRegexpCompat($regexp, $content);
            }
            else
                $this->assertSame($orig_content, $content);

            $output = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5

            $this->assertValidHTMLFragment($output);

        }
    }


        /**
         * @depends test_create_primary_publication_type
         * @depends test_initialize_settings
         */
    public function test_primary_get_the_excerpt( $primary_publication_type, $settings ) {
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
            $content = $primary_publication_type->get_the_excerpt($orig_content);

            $post_type = get_post_type($post_id);
            if($post_type == $primary_publication_type->get_publication_type_name())
            {
                foreach( array(
                             '#' . $settings->get_field_value('doi_url_prefix')  . '#',
                           )
                         as $regexp)
                    $this->assertRegexpCompat($regexp, $content);
            }
            else
                $this->assertSame($orig_content, $content);

            $output = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5
            $this->assertValidHTMLFragment($output);

        }
    }




        /**
         * @depends test_create_secondary_publication_type
         * @depends test_initialize_settings
         */
    public function test_secondary_get_the_excerpt( $secondary_publication_type, $settings ) {
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
            $content = $secondary_publication_type->get_the_excerpt($orig_content);

            $post_type = get_post_type($post_id);
            if($post_type == $secondary_publication_type->get_publication_type_name())
            {
                foreach( array(
                             '#' . $settings->get_field_value('doi_url_prefix')  . '#',
                           )
                         as $regexp)
                    $this->assertRegexpCompat($regexp, $content);
            }
            else
                $this->assertSame($orig_content, $content);

            $output = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5
            $this->assertValidHTMLFragment($output);

        }
    }



        /**
         * @depends test_create_secondary_publication_type
         * @depends test_initialize_settings
         */
    public function test_get_trackback_excerpt( $secondary_publication_type, $settings ) {
        global $posts;
        global $post;

        foreach($posts as $post_id => $post_data)
        {
            $post = new WP_Post($post_id);
            set_global_query(new WP_Query(array('ID' => $post_id)));
            the_post();

            $class = new ReflectionClass('O3PO_SecondaryPublicationType');
            $method = $class->getMethod('get_trackback_excerpt');
            $method->setAccessible(true);

            $content = $method->invokeArgs($secondary_publication_type, array($post_id));

            $post_type = get_post_type($post_id);
            if($post_type == $secondary_publication_type->get_publication_type_name())
            {
                foreach( array(
                             '#' . substr($post_data['post_content'], 0, 5)  . '#',
                           )
                         as $regexp)
                {
                    $this->assertRegexpCompat($regexp, $content);
                }
            }
            else
                $this->assertEmpty($content);

            $output = preg_replace('#(main|header)#', 'div', $content); # this is a brutal hack because $dom->loadHTML cannot cope with html 5
            $this->assertValidHTMLFragment($output);

        }
    }


        /**
         * @doesNotPerformAssertions
         */
    public function test_get_default_number_reviews() {

        $class = new ReflectionClass('O3PO_SecondaryPublicationType');
        $method = $class->getMethod('get_default_number_reviewers');
        $method->setAccessible(true);
        $method->invoke(null); #this is a static method, so passing null

    }

        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_get_default_number_authors( $primary_publication_type, $secondary_publication_type) {

        $primary_publication_type->get_default_number_authors();
        $secondary_publication_type->get_default_number_authors();

    }

        /**
         * @depends test_create_secondary_publication_type
         */
    public function test_get_pdf_pretty_permalink( $secondary_publication_type ) {

        $this->assertEmpty($secondary_publication_type->get_pdf_pretty_permalink(1));

    }


        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         */
    public function test_add_pdf_endpoint( $primary_publication_type ) {

        $primary_publication_type->add_pdf_endpoint();

    }


    public function pdf_endpoint_request_query_provider() {

        $paper = 'paper'; # 'paper' should ideally be $primary_publication_type->get_publication_type_name()

        return [
            array(new WP_Query(), ''),
            array(new WP_Query(null, array('pdf' => 'pdf')), ''),
            array(new WP_Query(null, array('pdf' => 'pdf', 'post_type' => $paper, $paper => 'doi-that-does-not-exist')), 'ERROR'),
            array(new WP_Query(null, array('pdf' => 'pdf', 'post_type' => $paper, $paper => 'fake_journal_level_doi_suffix-' . current_time("Y-m-d") . '-3')), '%PDF-1.4'), #doi of paper post with id 8
        ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider pdf_endpoint_request_query_provider
         * @depends test_create_primary_publication_type
         */
    public function test_handle_pdf_endpoint_request( $wp_query, $expected, $primary_publication_type ) {

            /* We must initialize a settings object for handle_pdf_endpoint_request() to work, but we also must runInSeparateProcess with preserveGlobalState disabled because we modify the headers in handle_pdf_endpoint_request(). Because O3PO_Settings is a singleton, we therefore cannot depend on test_initialize_settings(), but must run it here.
             */
        $settings = $this->test_initialize_settings();

        ob_start();
        $primary_publication_type->handle_pdf_endpoint_request( $wp_query , true);
        $output = ob_get_contents();
        ob_end_clean();

        if(!empty($expected))
            $this->assertRegExpCompat('#'.$expected.'#', $output);
    }


        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         */
    public function test_add_web_statement_endpoint( $primary_publication_type ) {

        $primary_publication_type->add_web_statement_endpoint();

    }


    public function web_statement_endpoint_request_query_provider() {

        $paper = 'paper'; # 'paper' should ideally be $primary_publication_type->get_publication_type_name()

        return [
            array(new WP_Query(), ''),
            array(new WP_Query(null, array('web-statement' => 'web-statement')), ''),
            array(new WP_Query(null, array('web-statement' => 'web-statement', 'post_type' => $paper, $paper => 'doi-that-does-not-exist')), 'ERROR'),
            array(new WP_Query(null, array('web-statement' => 'web-statement', 'post_type' => $paper, $paper => 'fake_journal_level_doi_suffix-' . current_time("Y-m-d") . '-3')), 'is licensed under'),
            array(new WP_Query(null, array('web-statement' => 'web-statement', 'post_type' => $paper, $paper => 'q-test-1742-04-01')), 'ERROR: file_path is empty'),
        ];

    }


        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider web_statement_endpoint_request_query_provider
         * @depends test_create_primary_publication_type
         */
    public function test_handle_web_statement_endpoint_request( $wp_query, $expected, $primary_publication_type ) {

            /* We must initialize a settings object for handle_web_statement_endpoint_request() to work, but we also must runInSeparateProcess with preserveGlobalState disabled because we modify the headers in handle_pdf_endpoint_request(). Because O3PO_Settings is a singleton, we therefore cannot depend on test_initialize_settings(), but must run it here.
             */
        $settings = $this->test_initialize_settings();

        ob_start();
        $primary_publication_type->handle_web_statement_endpoint_request( $wp_query , true);
        $output = ob_get_contents();
        ob_end_clean();

        if(!empty($expected))
            $this->assertRegExpCompat('#'.$expected.'#', $output);
    }



        /**
         * @doesNotPerformAssertions
         * @depends test_create_primary_publication_type
         */
    public function test_add_axiv_paper_doi_feed_endpoint( $primary_publication_type ) {

        $primary_publication_type->add_axiv_paper_doi_feed_endpoint();

    }











    public function axiv_paper_doi_feed_endpoint_request_query_provider() {

        $paper = 'paper'; # 'paper' should ideally be $primary_publication_type->get_publication_type_name()
        $settings = $this->test_initialize_settings();
        $endpoint_suffix = $settings->get_field_value('arxiv_paper_doi_feed_endpoint');


        return [
            array(new WP_Query(), ''),
            array(new WP_Query(null, array($endpoint_suffix => $endpoint_suffix)), ''),
            array(new WP_Query(null, array($endpoint_suffix => $endpoint_suffix, 'post_type' => $paper)), 'identifier="fake_arxiv_doi_feed_identifier"'),
        ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider axiv_paper_doi_feed_endpoint_request_query_provider
         * @depends test_create_primary_publication_type
         */
    public function test_handle_arxiv_paper_doi_feed_endpoint_request( $wp_query, $expected, $primary_publication_type ) {

            /* We must initialize a settings object for handle_arxiv_paper_doi_feed_endpoint_request() to work, but we also must runInSeparateProcess with preserveGlobalState disabled because we modify the headers in handle_arxiv_paper_doi_feed_endpoint_request(). Because O3PO_Settings is a singleton, we therefore cannot depend on test_initialize_settings(), but must run it here.
             */
        $settings = $this->test_initialize_settings();

        ob_start();
        $primary_publication_type->handle_arxiv_paper_doi_feed_endpoint_request( $wp_query , true);
        $output = ob_get_contents();
        ob_end_clean();

        if(!empty($expected))
            $this->assertRegExpCompat('#'.$expected.'#', $output);
    }






        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_add_dublin_core_and_highwire_press_meta_tags( $primary_publication_type, $secondary_publication_type ) {
        global $posts;
        global $post;
        global $is_single;

        $is_single = true;

        foreach($posts as $post_id => $post_data)
        {
            $post = new WP_Post($post_id);
            set_global_query(new WP_Query(array('ID' => $post_id)));
            the_post();

            $post_type = get_post_type($post_id);

            ob_start();
            if($primary_publication_type->get_publication_type_name() == $post_type)
                $primary_publication_type->add_dublin_core_and_highwire_press_meta_tags();
            elseif($secondary_publication_type->get_publication_type_name() == $post_type)
                $secondary_publication_type->add_dublin_core_and_highwire_press_meta_tags();

            $output = ob_get_contents();
            ob_end_clean();

            if($primary_publication_type->get_publication_type_name() == $post_type or $secondary_publication_type->get_publication_type_name() == $post_type)
            {
                $this->assertValidHTMLFragment($output);
            }
        }
    }







        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    public function test_the_java_script_single_page( $primary_publication_type, $secondary_publication_type ) {
        global $posts;
        global $post;
        global $is_single;

        $is_single = true;

        foreach($posts as $post_id => $post_data)
        {
            $post = new WP_Post($post_id);
            set_global_query(new WP_Query(array('ID' => $post_id)));
            the_post();

            $post_type = get_post_type($post_id);

            ob_start();
            if($primary_publication_type->get_publication_type_name() == $post_type)
                $primary_publication_type->the_java_script_single_page();
            elseif($secondary_publication_type->get_publication_type_name() == $post_type)
                $secondary_publication_type->the_java_script_single_page();

            $output = ob_get_contents();
            ob_end_clean();

            if($primary_publication_type->get_publication_type_name() == $post_type or $secondary_publication_type->get_publication_type_name() == $post_type)
            {
                $this->assertValidHTMLFragment($output);
            }
            else
                $this->assertEmpty($output);
        }
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

        $this->assertValidHTMLFragment($output);

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
        set_global_query(new WP_Query(null , null));

        #first check that nothing is output/changed if query doesn't match
        $this->assertSame('original-template.php', $journal->volume_endpoint_template('original-template.php'));
        $this->assertSame(array(), $journal->add_fake_post_to_volume_overview_page(array()));

        ob_start();
        $journal->handle_volumes_endpoint_request(get_global_query());
        $journal->volume_navigation_at_loop_start(get_global_query());
        $journal->compress_entries_in_volume_view(get_global_query());
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEmpty($output);

        $query_vars = array($journal->get_journal_property('volumes_endpoint') => $query_var_extra);
        set_global_query(new WP_Query(null , $query_vars));

        $journal->handle_volumes_endpoint_request(get_global_query());
        ob_start();
        $journal->volume_navigation_at_loop_start(get_global_query());
        $journal->compress_entries_in_volume_view(get_global_query());
        $output = ob_get_contents();
        ob_end_clean();
        $output = preg_replace('#(main|header)#', 'div', $output); # this is a brutal hack because $dom->loadHTML cannot cope with html 5
        $this->assertValidHTMLFragment($output);

    }


        /**
         * @depends test_setup_primary_journal
         */
    public function test_execution_of_various_journal_functions( $journal ) {

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
        $output = $journal->add_notice_to_search_form($form);
        $this->assertValidHTMLFragment($output);

        global $_GET;
        $_GET["reason"]="title-click";

        #first a query with no posts
        $main_search_query = new WP_Query(null, array('s' => 'search term', 'is_main' => true));
        set_global_query($main_search_query);
        ob_start();
        $this->assertSame($main_search_query, $journal->add_notice_to_search_results_at_loop_start($main_search_query));
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertValidHTMLFragment($output);

        #and then one that has posts
        $main_search_query = new WP_Query("post_type=paper", array('s' => 'search term', 'is_main' => true));
        set_global_query($main_search_query);
        ob_start();
        $this->assertSame($main_search_query, $journal->add_notice_to_search_results_at_loop_start($main_search_query));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

    }


        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    function test_get_all_citation_counts( $primary_publication_type, $secondary_publication_type ) {

        $this->assertSame($primary_publication_type->get_all_citation_counts()['citation_count']['10.22331/q-2017-04-25-8'], 43);

        $cited_by_data = $primary_publication_type->get_cited_by_data(12);
        $this->assertSame($cited_by_data['citation_count'], count($cited_by_data['all_bibentries']));
        $this->assertValidHTMLFragment($cited_by_data['html']);

    }

        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         */
    function test_admin_render_meta_data_explorer( $primary_publication_type, $secondary_publication_type ) {
        $admin = new O3PO_Admin( 'o3po', '0.3.0', 'O-3PO' );

        #test without giving parameters
        ob_start();
        echo "<div>";
        $admin->render_meta_data_explorer();
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

        #test the meta-data tab for various combinations of parameters
        global $_GET;
        global $_POST;

        $_GET['tab'] = 'meta-data';
        $_GET['max_entries'] = 1735;
        $post_type_names = O3PO_PublicationType::get_active_publication_type_names();
        $this->assertNotEmpty($post_type_names);

        $output_formats = $admin->get_output_formats();
        $this->assertNotEmpty($output_formats);

        foreach($post_type_names as $post_type_name) {
            foreach(array_merge($output_formats, ['a non-existing output format']) as $output_format) {
                foreach(array_merge($admin->get_meta_data_fields(), ['title,number_authors,volume,doi']) as $meta_data_field_list) {

                    $_GET['post_type'] = $post_type_name;
                    $_GET['output_format'] = $output_format;
                    $_GET['meta_data_field_list'] = $meta_data_field_list;

                    ob_start();
                    echo "<div>";
                    $admin->render_meta_data_explorer();
                    echo "</div>";
                    $output = ob_get_contents();
                    ob_end_clean();
                    $this->assertValidHTMLFragment($output);
                }
            }
        }

        #test the citation-metrics tab
        $_GET['tab'] = 'citation-metrics';
        $_POST['refresh'] = 'checked';
        ob_start();
        $admin->render_meta_data_explorer();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

    }



        /**
         * @depends test_create_primary_publication_type
         * @depends test_create_secondary_publication_type
         * @depends test_initialize_settings
         */
    function test_get_social_media_thumbnail_src( $primary_publication_type, $secondary_publication_type, $settings ) {

        # a post with feature image
        $this->assertSame(wp_get_attachment_image_src(get_post_thumbnail_id(1), "Full")[0], $primary_publication_type->get_social_media_thumbnail_src(1));
        $this->assertSame(wp_get_attachment_image_src(get_post_thumbnail_id(1), "Full")[0], $secondary_publication_type->get_social_media_thumbnail_src(1));

        # a post without feature image
        $this->assertSame($settings->get_field_value('social_media_thumbnail_url'), $primary_publication_type->get_social_media_thumbnail_src(5));
        $this->assertSame($settings->get_field_value('social_media_thumbnail_url'), $secondary_publication_type->get_social_media_thumbnail_src(5));

    }




    function html_latex_excerpt_provider() {

        return [
            ["Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.", 190, "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris"],
            ['abcdef', 4, 'abcd'],
            ['abcdef', 40, 'abcdef'],
            ['', 40, ''],
            ['', 0, ''],
            ['ab $\sin(x)$ cdef $x$ aaa', 4, 'ab'],
            ['ab $\sin(x)$ cdef $x$ aaa', 5, 'ab'],
            ['ab $\sin(x)$ cdef $x$ aaa', 15, 'ab $\sin(x)$ cdef'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 16, 'ab $$\sin(x)$$ cdef'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 15, 'ab $$\sin(x)$$ cdef'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 14, 'ab $$\sin(x)$$ cde'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 12, 'ab $$\sin(x)$$ c'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 11, 'ab $$\sin(x)$$'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 10, 'ab $$\sin(x)$$'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 9, 'ab'],
            ['ab $$\sin(x)$$ cdef $x$ aaa', 50, 'ab $$\sin(x)$$ cdef $x$ aaa'],
            ['$\cos(1)$', 7, '$\cos(1)$'],
            ['$\cos(1)$', 2, ''],
        ];
    }

        /**
         * @dataProvider html_latex_excerpt_provider
         * @depends test_create_primary_publication_type
         */
    public function test_html_latex_excerpt( $text, $len, $expected, $primary_publication_type ) {
        $this->assertSame($expected, $primary_publication_type->html_latex_excerpt($text, $len));

    }



    public function validate_doi_suffix_template_provider() {

        $settings = O3PO_SettingsTest::get_settings();

        return [
            ['a-[year]-[page]-foo', 'a-[year]-[page]-foo'],
            ['template-without-page', $settings->get_field_default('doi_suffix_template')],
            ['asdf ', $settings->get_field_default('doi_suffix_template')],
                ];
    }

        /**
         * @dataProvider validate_doi_suffix_template_provider
         */
    public function test_validate_doi_suffix_template( $doi_suffix_template, $expected ) {

        $this->assertSame(O3PO_Journal::validate_doi_suffix_template('doi_suffix_template', $doi_suffix_template), $expected);
    }



        /**
         * @doesNotPerformAssertions
         */
    public function test_cleanup_at_the_very_end() {
        exec('git checkout ' . dirname(__File__) . '/resources/arxiv/0809.2542v4.pdf > /dev/null 2>&1');
        O3PO_Environment::save_recursive_remove_dir(dirname(__File__) . "/resources/tmp/", dirname(__File__));
    }
    #do not add tests after this one!
}
