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
         * @depends test_initialize_ready2publish_form
         */
    public function test_form_html( $settings, $form ) {

        # fake request uri for add_query_arg() called in do_parse_request()
        $_SERVER['REQUEST_URI'] = parse_url(home_url(), PHP_URL_PATH) . $settings->get_field_value("ready2publish_slug");
        # needed to make setup_query() work
        add_filter('the_posts', array($this, 'add_zero_element') );

        ob_start();
        $form->do_parse_request(True, Null);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertValidHTMLFragment($output);
    }

    static function add_zero_element($in) {
        $out = array($in);
        $out[0] = "zero_element";

        return $out;
    }
}
