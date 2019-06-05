<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php');

class O3PO_SettingsTest extends PHPUnit_Framework_TestCase
{

    public function fake_get_active_publication_type_names() {

        return array("fake_publication_type_name_1", "fake_publication_type_name_2");
    }

    public function test_initialize_settings()
    {
        $file_data = get_file_data(dirname( __FILE__ ) . '/../o3po/o3po.php', array(
                                       'Version' => 'Version',
                                       'Plugin Name' => 'Plugin Name',
                                       'Text Domain' => 'Text Domain'
                                                   ));

        $settings = O3PO_Settings::instance();
        $settings->configure($file_data['Text Domain'], $file_data['Plugin Name'], $file_data['Version'], array( $this, 'fake_get_active_publication_type_names'));

        $this->assertInstanceOf(O3PO_Settings::class, $settings);

        return $settings;
    }


        /**
         * @depends test_initialize_settings
         */
    public function test_register_and_render_settings_page( $settings ) {

        global $_GET;

        $settings->register_settings();

        $class = new ReflectionClass('O3PO_Settings');
        $property = $class->getProperty('settings_sections');
        $property->setAccessible(true);

        $combined_output = '';
        foreach($property->getValue($settings) as $section_id => $section_options)
        {
            ob_start();
            $_GET['tab'] = $section_id;
            $settings->render_settings_page();
            $output = ob_get_contents();
            ob_end_clean();
            $dom = new DOMDocument;
            $result = $dom->loadHTML($output);
                //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);

            $combined_output .= $output;
        }


        foreach($settings->get_option_defaults() as $id => $default)
        {
            $this->assertContains($id, array_keys($settings->get_all_settings_fields_map()), 'A default was provided for the option ' . $id . ' but it is not in the all_settings_fields_map' );
        }

        foreach($settings->get_all_settings_fields_map() as $id => $callable)
        {
            if(!in_array($id ,
                         array('cited_by_refresh_seconds',
                               'primary_publication_type_name',
                               'primary_publication_type_name_plural',
                               'secondary_publication_type_name',
                               'secondary_publication_type_name_plural',
                               'volumes_endpoint',)))
            {
                if(method_exists($this, 'assertStringContainsString'))
                    $this->assertStringContainsString($id, $combined_output, 'There was a default set for the option ' . $id . ' but it was not found in the settings page html.');
                else
                    $this->assertContains($id, $combined_output, 'There was a default set for the option ' . $id . ' but it was not found in the settings page html.');
            }
            else
            {
                if(method_exists($this, 'assertStringNotContainsString'))
                    $this->assertStringNotContainsString($id, $combined_output, 'Option ' . $id . ' was found in the settings page html, but we thought it should not be configurable?.');
                else
                    $this->assertNotContains($id, $combined_output, 'Option ' . $id . ' was found in the settings page html, but we thought it should not be configurable?.');
            }

            #test the special cases of 'tab' not being set and a transient being present that will trigger a lush rewrite rules
            global $get_transient_returns;
            $get_transient_returns = true;
            ob_start();
            $_GET['tab'] = null;
            $settings->render_settings_page();
            $output = ob_get_contents();
            ob_end_clean();
            $get_transient_returns = false;
            $dom = new DOMDocument;
            $result = $dom->loadHTML($output);
                //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
            $this->assertNotFalse($result);
        }

        preg_match_all('#id="' . $settings->get_plugin_name() . '-settings-(.*?)"#', $combined_output, $matches);
        foreach($matches[1] as $id)
        {
            $this->assertContains($id, array_keys($settings->get_all_settings_fields_map()), 'Option ' . $id . ' was found in the settings page html but not in the all_settings_fields_map. Only settings with an entry in that map are actually saved, when the settings are saved.');
        }
    }


        /**
         * @depends test_initialize_settings
         */
    public function test_render_array_as_comma_separated_list_setting( $settings ) {

        ob_start();
        $settings->render_array_as_comma_separated_list_setting('maintenance_mode');#call this on a non-list setting to make sure it executes on non-initialized settings
        $output = ob_get_contents();
        ob_end_clean();
        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
            //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
        $this->assertNotFalse($result);
    }


    public function validate_doi_prefix_provider() {
        return [
            ['1234567890.-', '1234567890.-'],
            ['@', ''],
            ['asdf', ''],
                ];
    }

        /**
         * @dataProvider validate_doi_prefix_provider
         * @depends test_initialize_settings
         */
    public function test_validate_doi_prefix( $doi_prefix, $expected, $setting ) {

        $this->assertSame($setting->validate_doi_prefix('doi_prefix', $doi_prefix), $expected);
    }



    public function validate_doi_suffix_provider() {
        return [
            ['abcdkrwrfdxyzABCDEZUHAZ0123456789.-', 'abcdkrwrfdxyzABCDEZUHAZ0123456789.-'],
            ['@', ''],
            ['asdf', 'asdf'],
                ];
    }

        /**
         * @dataProvider validate_doi_suffix_provider
         * @depends test_initialize_settings
         */
    public function test_validate_doi_suffix( $doi_suffix, $expected, $setting ) {

        $this->assertSame($setting->validate_doi_suffix('journal_level_doi_suffix', $doi_suffix), $expected);
    }



    public function validate_eissn_provider() {
        return [
            ['1234-5678', '1234-5678'],
            ['1234-5678 ', '1234-5678'],
            ['1234-567X', '1234-567X'],
            ['@', ''],
            ['asdf', ''],
            ['123-456', ''],
                ];
    }

        /**
         * @dataProvider validate_eissn_provider
         * @depends test_initialize_settings
         */
    public function test_validate_eissn( $eissn, $expected, $setting ) {

        $this->assertSame($setting->validate_eissn('eissn', $eissn), $expected);
    }



    public function validate_first_volume_year_provider() {
        return [
            ['2017', '2017'],
            ['  2018 ', '2018'],
            ['  1962 ', '1962'],
            ['@', ''],
            ['asdf', ''],
                ];
    }

        /**
         * @dataProvider validate_first_volume_year_provider
         * @depends test_initialize_settings
         */
    public function test_validate_first_volume_year( $first_volume_year, $expected, $setting ) {

        $this->assertSame($setting->validate_first_volume_year('first_volume_year', $first_volume_year), $expected);
    }




    public function validate_url_provider() {
        return [
            ['https://doaj.org/api/v1/docs#!/CRUD_Articles/post_api_v1_articles', 'https://doaj.org/api/v1/docs#!/CRUD_Articles/post_api_v1_articles'],
            ['https://codex.wordpress.org/Function_Reference/get_post_thumbnail_id', 'https://codex.wordpress.org/Function_Reference/get_post_thumbnail_id'],
            ['ftp://fo.bar', 'ftp://fo.bar'], #in the fake wordpress environment of the test system esc_url and esc_url_raw currently have no effect, so this validation doesn't really work during unit tests
            ['  https://foo.de  ', 'https://foo.de']
                ];
    }

        /**
         * @dataProvider validate_url_provider
         * @depends test_initialize_settings
         */
    public function test_validate_url( $url, $expected, $setting ) {

        $this->assertSame($setting->validate_url('license_url', $url), $expected);
    }



public function validate_array_as_comma_separated_list_provider() {
        return [
            ['a, b, c,d', array('a', 'b', 'c', 'd')],
            ['   a, b, ', array('a', 'b')],
            [',a,b,', array('a', 'b')],
            [',a,', array('a')],
            ['', array()],
            [',', array()],
            [',,', array()],
            [', ,', array('')],
            [42, array('42')],
            [new WP_Error('', 'just some object that cannot be interpreted as a comma separated list'), array()],
                ];
    }

        /**
         * @dataProvider validate_array_as_comma_separated_list_provider
         * @depends test_initialize_settings
         */
    public function test_validate_array_as_comma_separated_list( $array_as_comma_separated_list, $expected, $setting ) {

        $this->assertSame($setting->validate_array_as_comma_separated_list('buffer_profile_ids', $array_as_comma_separated_list), $expected);
    }


    public function validate_two_letter_country_code_provider() {
        return [
            ['DE', 'DE'],
            ['ES', 'ES'],
            ['USA', ''],
            ['2', ''],
                ];
    }

        /**
         * @dataProvider validate_two_letter_country_code_provider
         * @depends test_initialize_settings
         */
    public function test_validate_two_letter_country_code( $two_letter_country_code, $expected, $setting ) {

        $this->assertSame($setting->validate_two_letter_country_code('doaj_language_code', $two_letter_country_code), $expected);
    }



    public function validate_positive_integer_provider() {
        return [
            ['1234567890', '1234567890'],
            ['-2', '1'],
            ['0', '1'],
            ['000', '1'],
            ['0.2', '1'],
            ['-0.3', '1'],
            ['a', '1'],
                ];
    }

        /**
         * @dataProvider validate_positive_integer_provider
         * @depends test_initialize_settings
         */
    public function test_validate_positive_integer( $positive_integer, $expected, $setting ) {

        $this->assertSame($setting->validate_positive_integer('arxiv_paper_doi_feed_days', $positive_integer), $expected);
    }


    public function checked_or_unchecked_provider() {
        return [
            ['checked', 'checked'],
            ['unchecked', 'unchecked'],
            ['foo', 'unchecked'],
                ];
    }

        /**
         * @dataProvider checked_or_unchecked_provider
         * @depends test_initialize_settings
         */
    public function test_checked_or_unchecked( $checked_or_unchecked, $expected, $setting ) {

        $this->assertSame($setting->checked_or_unchecked('custom_search_page', $checked_or_unchecked), $expected);
    }




    public function trim_settings_field_provider() {
        return [
            ['a nice text', 'a nice text'],
            ['a nice text ', 'a nice text'],
            [' a nice text ', 'a nice text'],
            ["\n a nice text ", 'a nice text'],
                ];
    }

        /**
         * @dataProvider trim_settings_field_provider
         * @depends test_initialize_settings
         */
    public function test_trim_settings_field( $trim_settings_field, $expected, $setting ) {

        $this->assertSame($setting->trim_settings_field('trim_settings_field', $trim_settings_field), $expected);
    }


    public function trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed_provider( $settings ) {

        $this->test_initialize_settings();
        $settings = O3PO_Settings::instance();
        $previous_value = $settings->get_plugin_option('arxiv_paper_doi_feed_endpoint');

        return [
            ['abc', 'abc'],
            ['abc ', 'abc'],
            [' abc', 'abc'],
            ['', $previous_value],
            [null, $previous_value],
            [' ', $previous_value],
                ];
    }

        /**
         * @dataProvider trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed_provider
         * @depends test_initialize_settings
         */
    public function test_trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed( $trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed, $expected, $setting ) {

        $this->assertSame($setting->trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed('arxiv_paper_doi_feed_endpoint', $trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed), $expected);
    }


        /**
         * @depends test_initialize_settings
         */
    public function test_trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed_on_empty_setting( $settings ) {

        $this->assertEmpty($settings->get_option_defaults()['arxiv_doi_feed_identifier'], "If the field arxiv_doi_feed_identifier is no longer empty, another field must be used in this test");

        $this->assertSame($settings->trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed('arxiv_doi_feed_identifier', ''), 'this-field-must-not-be-empty');
    }


        /**
         * @depends test_initialize_settings
         * @doesNotPerformAssertions
         */
    public function test_execute_add_settings_page_to_menu( $settings ) {

        $settings->add_settings_page_to_menu();
    }


    public function validate_settings_provider() {
        $this->test_initialize_settings();
        $settings = O3PO_Settings::instance();

        return [
            [array(), array('journal_title' => 'fake_journal_title')], #check that defaults survive even if input is empty
            [$settings->get_option_defaults(), $settings->get_option_defaults()], #check that all default options validate
            [array('journal_title' => 'new title'), array('journal_title' => 'new title')], #check that settings actually change

                ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider validate_settings_provider
         */
    public function test_validate_settings( $input, $expected ) {

        $this->test_initialize_settings();
        $settings = O3PO_Settings::instance();
        $output = $settings->validate_settings($input);

        foreach($expected as $key => $val)
            $this->assertSame($output[$key], $val);
    }


        /**
         * @depends test_initialize_settings
         */
    public function test_get_plugin_option_that_does_not_exis( $settings ) {
        try{
            $settings->get_plugin_option('i-do-not-exist');
            $this->assertFalse(true, 'Command above should throw exception.');
        } catch (Exception $e) {
            $this->assertEquals($e, new Exception('The non existing plugin option i-do-not-exist was requested.'));
        }
    }


}
