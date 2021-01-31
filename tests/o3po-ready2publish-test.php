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

        global $wp_query; # content ends up in here

        # fake request uri for add_query_arg() called in do_parse_request()
        $_SERVER['REQUEST_URI'] = parse_url(home_url(), PHP_URL_PATH) . $settings->get_field_value("ready2publish_slug");

        #ob_start();
        $form->do_parse_request(True, Null, True);
        #$output = ob_get_contents();
        #ob_end_clean();

        $content = $wp_query->post->ID->post_content;

        echo "Output:" . json_encode($content);

        $this->assertValidHTMLFragment($content);
    }
}
