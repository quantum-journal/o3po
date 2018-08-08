<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php');

class O3PO_SettingsTest extends PHPUnit_Framework_TestCase
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
         * @doesNotPerformAssertions
         * @depends test_initialize_settings
         */
    public function test_register_settings( $settings ) {

        $settings->register_settings();

        return true;
    }


        /**
         * @depends test_initialize_settings
         * @depends test_register_settings
         */
    public function test_render_settings_page( $settings, $settings_registered ) {
        $settings->register_settings(); //Without process isolation changes recorded in global variables by the fake WP environment for testing should be persisted from when when this was called arelady in test_register_settings(). For some reason I do not understand this is not the case, so we call it again...

        ob_start();
        $settings->render_settings_page();
        $output = ob_get_contents();
        ob_end_clean();

            //print($output);

        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
            //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
        $this->assertNotFalse($result);

        foreach($settings->get_all_settings_fields_map() as $id => $callable)
        {
            if(!in_array($id ,
                         array('primary_publication_type_name',
                               'primary_publication_type_name_plural',
                               'secondary_publication_type_name',
                               'secondary_publication_type_name_plural',
                               'volumes_endpoint',)))
                $this->assertContains($id, $output, 'Option ' . $id . ' was not found in the settings page html.');
            else
                $this->assertNotContains($id, $output, 'Option ' . $id . ' was found in the settings page html, but we thought it should not be configurable?.');
        }
    }

}
