<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-environment.php');

class O3PO_Environment_Test extends O3PO_TestCase
{

    # As phpunit 8 requires a specification of a void return type for setUp(), as explained here https://thephp.cc/news/2019/02/help-my-tests-stopped-working, but PHP <7 does not support such declarations setUp() can no longer be used if the tests are to run across PHP versions.
    public function test_construct_production_environment() {
        $environment = new O3PO_Environment(get_site_url());
        $this->assertFalse($environment->is_test_environment());
        return $environment;
    }

    public function test_construct_test_environment() {
        $environment = new O3PO_Environment('https://not.production.com/');
        $this->assertTrue($environment->is_test_environment());

        $environment2 = new O3PO_Environment('');
        $this->assertTrue($environment2->is_test_environment());

        return $environment;
    }

        /**
         * @depends test_construct_production_environment
         * @depends test_construct_test_environment
         */
    public function test_get_plugin_pretty_name( $production_environment, $test_environment ) {
        ob_start();
        $production_environment->modify_css_if_in_test_environment();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEmpty($output);

        ob_start();
        $test_environment->modify_css_if_in_test_environment();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

    }


        /**
         * @depends test_construct_production_environment
         */
    public function test_unique_filename_callback( $environment ) {

        $this->assertSame('o3po-environment-test-1.php', $environment->unique_filename_callback( dirname( __FILE__ ), 'o3po-environment-test.php', '.php' ));
        $this->assertSame('file-that-does-not-yet-exist.txt', $environment->unique_filename_callback( dirname( __FILE__ ), 'file-that-does-not-yet-exist.txt', '.txt' ));
        $this->assertSame('0809.2542v4-1.tar.gz', $environment->unique_filename_callback( dirname( __FILE__ ) . '/resources/arxiv', '0809.2542v4.tar.gz', '.tar.gz' ));
        $this->assertSame('file-with-number-3.txt', $environment->unique_filename_callback( dirname( __FILE__ ) . '/resources', 'file-with-number.txt', '.txt' ));
        $this->assertSame('file-with-number-3.txt', $environment->unique_filename_callback( dirname( __FILE__ ) . '/resources', 'file-with-number-3.txt', '.txt' ));
        $this->assertSame('file-with-number-42.txt', $environment->unique_filename_callback( dirname( __FILE__ ) . '/resources', 'file-with-number-42.txt', '.txt' ));

        $this->assertSame('file-without-extension-1', $environment->unique_filename_callback( dirname( __FILE__ ) . '/resources', 'file-without-extension', '' ));

    }


        /**
         * @depends test_construct_production_environment
         */
    public function test_custom_upload_mimes( $environment ) {

        $mimes = $environment->custom_upload_mimes();
        $this->assertArrayHasKey('pdf', $mimes);
        $this->assertArrayHasKey('tex', $mimes);
        $this->assertArrayHasKey('gz', $mimes);
        $this->assertArrayHasKey('tar.gz', $mimes);

    }


    public function mime_check_data_provider() {

        return [
            array(
                'data' => array('ext'=> 'pdf',
                                'type'=> 'application/pdf',
                                'proper_filename' => 'should_be_called_like_this'),
                'file' => '/path/to/the/real/file/file.pdf',
                'filename' => 'should_be_called_like_this.pdf',
                'mimes' => array('pdf' => 'application/pdf'),
                'expected' => array(
                    'ext' => 'pdf',
                    'type' => 'application/pdf',
                    'proper_filename' => 'should_be_called_like_this',
                ),
                  ),

            array(
                'data' => array('ext'=> 'tar.gz',
                                'type'=> 'application/gz',
                                'proper_filename' => 'should_be_called_like_this'),
                'file' => '/path/to/the/real/file/file.tar.gz',
                'filename' => 'should_be_called_like_this.tar.gz',
                'mimes' => array('tar.gz' => 'application/gz'),
                'expected' => array(
                    'ext' => 'tar.gz',
                    'type' => 'application/gz',
                    'proper_filename' => 'should_be_called_like_this',
                ),
                  )
                ];
    }


        /**
         * @dataProvider mime_check_data_provider
         * @depends test_construct_production_environment
         */
    public function test_disable_real_mime_check_for_selected_extensions( $data, $file, $filename, $mimes, $expected, $environment ) {

        $out = $environment->disable_real_mime_check_for_selected_extensions($data, $file, $filename, $mimes );
        $this->assertSame($expected, $out);

    }


    /* public function download_to_media_library_provider() { */

    /*     return [ */
    /*         array(), */
    /*     ]; */
    /* } */

    /*     /\** */
    /*      * @dataProvider download_to_media_library_provider */
    /*      * @depends test_construct_production_environment */
    /*      *\/ */
    /* public function test_download_to_media_library( $url, $filename, $extension, $mime_type, $parent_post_id, $environment) {} */


    /* public function folder_to_delete_provider() { */

    /*     return [ */
    /*         array( */
    /*             'path' => dirname( __FILE__ ). '/tmp/foo/', */
    /*             'root' => dirname( __FILE__ ). '/tmp/foo/' */
    /*               ), */
    /*     ];    */
    /* } */

        /**
         * @depends test_construct_production_environment
         */
    public function test_save_recursive_remove_dir( $environment) {

        $tmp_dir = dirname( __FILE__ ). '/tmp';

        $dirs = array(
            $tmp_dir,
            $tmp_dir . '/foo',
            $tmp_dir . '/foo/bar',
            $tmp_dir . '/foo/baz',
            $tmp_dir . '/foo/bar/zonk',
        );

            // create dirs
        foreach($dirs as $dir)
            if(!is_dir($dir))
                mkdir($dir);

            // check
        foreach($dirs as $dir)
            $this->assertTrue(is_dir($dir));

        # test starts here

        $environment->save_recursive_remove_dir($tmp_dir . '/foo/bar', $tmp_dir . '/foo/baz');
        $this->assertTrue(is_dir($tmp_dir . '/foo/bar'));
        $environment->save_recursive_remove_dir($tmp_dir . '/foo/bar', $tmp_dir . '/foo');
        $this->assertFalse(is_dir($tmp_dir . '/foo/bar'));

        # test ends here

            // clean up
        foreach(array_reverse($dirs) as $dir)
        {
            if(is_dir($dir))
               rmdir($dir);
        }

            // check
        foreach($dirs as $dir)
            $this->assertFalse(is_dir($dir));

    }


        /**
         * @depends test_construct_production_environment
         */
    public function test_file_get_contents_utf8( $environment ) {

        $content = $environment->file_get_contents_utf8(dirname( __FILE__ ) . '/resources/file-with-uft8-chars.tex');
        $this->assertSame("just some test with special characters ¢ é ä @ § ³ Σ\n", $content);

    }


/*
 * get_feature_image_path() cannot be tested here
 */

}
