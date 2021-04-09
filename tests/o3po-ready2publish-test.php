<?php

require_once(dirname( __FILE__ ) . '/../o3po/public/class-o3po-ready2publish-form.php');
require_once(dirname( __FILE__ ) . '/o3po-settings-test.php');

class O3PO_Ready2PublishTest extends O3PO_TestCase
{

    public function test_initialize_settings() {

        $settings = O3PO_SettingsTest::get_settings();

        return $settings;
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
         * @depends test_initialize_settings
         */
    public function test_initialize_ready2publish_storage( $settings ) {

        $storage = new O3PO_Ready2PublishStorage('o3po', $settings->get_field_value("ready2publish_slug") . '-storage');

        return $storage;
    }

        /**
         * @depends test_initialize_settings
         * @depends test_initialize_ready2publish_storage
         * @depends test_setup_environment
         */
    public function test_initialize_ready2publish_form( $settings, $storage, $environment ) {

        $form = new O3PO_Ready2PublishForm('o3po', $settings->get_field_value("ready2publish_slug"), $environment, $storage);

        return $form;
    }

        /**
         * @depends test_initialize_settings
         * @depends test_initialize_ready2publish_storage
         * @depends test_setup_environment
         */
    public function test_form_html_and_logic( $settings, $environment, $storage ) {

        global $wp_query; # content ends up in here

        $form = new O3PO_Ready2PublishForm('o3po', $settings->get_field_value("ready2publish_slug"), $environment, $storage);

        # fake request uri for add_query_arg() called in do_parse_request()
        $_SERVER['REQUEST_URI'] = parse_url(home_url(), PHP_URL_PATH) . $settings->get_field_value("ready2publish_slug");

        $form->do_parse_request(True, Null, True);
        $content = $wp_query->post->ID->post_content;
        #echo "Output:" . json_encode($content);
        $this->assertValidHTMLFragment($content);

        # try to advance to next page without session id
        $form = new O3PO_Ready2PublishForm('o3po', $settings->get_field_value("ready2publish_slug"), $environment, $storage);
        $_POST['coming_from_page'] = 'basic_manuscript_data';
        $_POST['navigation'] = 'Next';
        $form->do_parse_request(True, Null, True);
        $content = $wp_query->post->ID->post_content;
        #echo "Output:" . json_encode($content);
        $this->assertValidHTMLFragment($content);
        $this->assertContains("Invalid session id", $content);
        $this->assertSame($form->get_page_to_display(), false);

        # now try with session id
        $form = new O3PO_Ready2PublishForm('o3po', $settings->get_field_value("ready2publish_slug"), $environment, $storage);
        $_POST['coming_from_page'] = 'basic_manuscript_data';
        $_POST['navigation'] = 'Next';
        $_POST['o3po-ready2publish'] = array(
                                             );
        $class = new ReflectionClass('O3PO_Ready2PublishForm');
        $method = $class->getMethod('get_session_ids');
        $method->setAccessible(true);
        $_POST['session_id'] = $method->invoke($form)[0];
        $form->do_parse_request(True, Null, True);
        $content = $wp_query->post->ID->post_content;
        #echo "Output:\n" . $content;
        $this->assertValidHTMLFragment($content);
        $this->assertContains("The arXiv identifier in &amp;#039;ArXiv identifier&amp;#039; must not be empty.", $content);
        $this->assertContains("The box &amp;#039;Consent to publish&amp;#039; must be checked", $content);
        $this->assertContains("An acceptance code must be provided", $content);
        $this->assertContains("not a valid email address", $content);
        $this->assertSame($form->get_page_to_display(), 'basic_manuscript_data');

        # now try with session id and some data in $_POST
        $form = new O3PO_Ready2PublishForm('o3po', $settings->get_field_value("ready2publish_slug"), $environment, $storage);
        $_POST['coming_from_page'] = 'basic_manuscript_data';
        $_POST['navigation'] = 'Next';
        $_POST['o3po-ready2publish'] = array(
            'eprint' => '2006.01273v3',
            'acceptance_code' => 'AAA',
            'agree_to_publish' => 'checked',
            'corresponding_author_email' => 'foo@bar.com',
            'payment_method' => 'invoice',
                                             );
        $class = new ReflectionClass('O3PO_Ready2PublishForm');
        $method = $class->getMethod('get_session_ids');
        $method->setAccessible(true);
        $_POST['session_id'] = $method->invoke($form)[0];
        $form->do_parse_request(True, Null, True);
        $content = $wp_query->post->ID->post_content;
        echo "Output:\n" . $content;
        $this->assertValidHTMLFragment($content);
        $this->assertContains("2006.01273v3", $content);
        $this->assertContains("Mills", $content);
        $this->assertContains("Daniel", $content);
        $this->assertNotContains("alert", $content);
        $this->assertSame($form->get_page_to_display(), 'meta_data');

    }

    public function validate_featured_image_upload_provider() {

        return [
            [array(
            'tmp_name' => dirname( __FILE__ ) . '/resources/img/quantum_template_wrong_aspect.png',
            'name' => 'quantum_template_wrong_aspect.png',
            'type' => 'image/png',
                   ), 165314, 'error'],
            [array(
            'tmp_name' => dirname( __FILE__ ) . '/resources/img/quantum_template.png',
            'name' => 'quantum_template.png',
            'type' => 'application/pdf',
                   ), 165314, 'error'],
            [array(
            'tmp_name' => dirname( __FILE__ ) . '/resources/img/quantum_template.png',
            'name' => 'quantum_template.png',
            'type' => 'image/png',
                   ), 165314-1, 'error'], # max file size too small
            [array(
            'tmp_name' => dirname( __FILE__ ) . '/resources/img/quantum_template.png',
            'type' => 'image/png',
                   ), 165314, 'error'], # 'name' is missing
            [array(
            'tmp_name' => dirname( __FILE__ ) . '/resources/img/quantum_template.png',
            'name' => 'quantum_template.png',
            'type' => 'image/png',
                   ), 165314, 'user_name'],
                ];
    }


        /**
         * @dataProvider validate_featured_image_upload_provider
         * @depends test_initialize_settings
         * @depends test_initialize_ready2publish_storage
         * @depends test_setup_environment
         */
    public function test_validate_featured_image_upload( $file_of_this_id, $max_file_size, $expected_key, $settings, $environment, $storage ) {

        $form = new O3PO_Ready2PublishForm('o3po', $settings->get_field_value("ready2publish_slug"), $environment, $storage);
        $form->read_and_validate_field_values(); # mostly to set the session_id

        $orig_file_size = Null;
        if(!empty($_POST['MAX_FILE_SIZE']))
            $orig_file_size = $_POST['MAX_FILE_SIZE'];

        $_POST['MAX_FILE_SIZE'] = $max_file_size;

        $result = $form->validate_featured_image_upload('id', $file_of_this_id);
        if($orig_file_size !== Null)
            $_POST['MAX_FILE_SIZE'] = $orig_file_size;

        $this->assertArrayHasKey($expected_key, $result);


    }


    public function acceptance_code_provider() {

        return [
            ['AAA', 'AAA', True],
            ['8941341j43ffa', '', False],
            ['', '', False],
                ];
    }

        /**
         * @dataProvider acceptance_code_provider
         * @depends test_initialize_settings
         * @depends test_initialize_ready2publish_storage
         * @depends test_setup_environment
         */
    public function test_validate_acceptance_code( $code, $expected, $is_valid, $settings, $environment, $storage) {

        $form = new O3PO_Ready2PublishForm('o3po', $settings->get_field_value("ready2publish_slug"), $environment, $storage);

        $result = $form->validate_acceptance_code("acceptance_code", $code);
        $this->assertSame($result, $expected);
        if($is_valid)
            $this->assertSame(count($form->get_errors()), 0);
        else
            $this->assertSame(count($form->get_errors()), 1);
    }

}
