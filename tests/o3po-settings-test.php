<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-settings.php');

class O3PO_SettingsTest extends O3PO_TestCase
{

    public function fake_get_active_publication_type_names() {

        return array("fake_publication_type_name_1", "fake_publication_type_name_2");
    }

    public static function get_settings()
    {

        $settings_test = new O3PO_SettingsTest();
        return $settings_test->test_initialize_settings();
    }

    public function test_initialize_settings()
    {
        $file_data = get_file_data(dirname( __FILE__ ) . '/../o3po/o3po.php', array(
                                       'Version' => 'Version',
                                       'Plugin Name' => 'Plugin Name',
                                       'Text Domain' => 'Text Domain'
                                                   ));

        if(!O3PO_Settings::configured()) {
            try
            {
                O3PO_Settings::instance();
                $this->assertTrue(false, 'An exception should have been thrown on first initialization without parameters');
            } catch (Exception $e) {
                $this->assertEquals($e->getMessage(), "Settings object must be configured on first initialization. No configuration given.");
            }
            $settings = O3PO_Settings::instance($file_data['Text Domain'], $file_data['Plugin Name'], $file_data['Version'], array( $this, 'fake_get_active_publication_type_names'));

            try
            {
                $settings = O3PO_Settings::instance('bogus', 'input', 'to', 'initialization');
                $this->assertTrue(false, 'An exception should have been thrown when initializing again with different parameters');
            } catch (Exception $e) {
                $this->assertEquals($e->getMessage(), "Settings object must be configured on first initialization. Already configured.");
            }

        }
        else
            $settings = O3PO_Settings::instance();

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
            $this->assertValidHTMLFragment($output);

            $combined_output .= $output;
        }

        $class = new ReflectionClass('O3PO_Settings');
        $property = $class->getProperty('settings_fields');
        $property->setAccessible(true);

        foreach($property->getValue($settings) as $id => $specification)
        {

            if(!in_array($id ,
                         array('primary_publication_type_name',
                               'primary_publication_type_name_plural',
                               'secondary_publication_type_name',
                               'secondary_publication_type_name_plural',
                               'volumes_endpoint',)))
            {
                $callable = $specification['validation_callable'];

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

            #test the special cases of 'tab' not being set and a transient being present that will trigger a flush rewrite rules
            global $get_transient_returns;
            $get_transient_returns = true;
            ob_start();
            $_GET['tab'] = null;
            $settings->render_settings_page();
            $output = ob_get_contents();
            ob_end_clean();
            $get_transient_returns = false;

            $this->assertValidHTMLFragment($output);
        }

        preg_match_all('#id="' . $settings->get_plugin_name() . '-settings-(.*?)"#', $combined_output, $matches);
        foreach($matches[1] as $id)
        {
            $this->assertContains($id, array_keys($settings->get_option_defaults()), 'Option ' . $id . ' was found in the settings page html but not in the all_settings_fields_map. Only settings with an entry in that map are actually saved, when the settings are saved.');
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
        $this->assertValidHTMLFragment($output);
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



    public function validate_issn_provider() {
        return [
            ['0378-5955', '0378-5955', true],
            ['2521-327X', '2521-327X', true],
            ['  2521-327X ', '2521-327X', true],
            ['1234-5678', '', false],
            ['1234-5678 ', '', false],
            ['1234-567X', '', false],
            [' 1234-567X  ', '', false],
            ['@', '', false],
            ['asdf', '', false],
            ['123- 456', '', false],
            ['', '', true],
            ['   ', '', true],
                ];
    }

        /**
         * @dataProvider validate_issn_provider
         * @depends test_initialize_settings
         */
    public function test_validate_issn_or_empty( $issn, $expected, $valid, $setting ) {
        global $global_setting_errors;

        $global_setting_errors = array();
        $this->assertSame($setting->validate_issn_or_empty('eissn', $issn), $expected);

        if(!$valid)
            $this->assertNotEmpty($global_setting_errors);
        else
            $this->assertEmpty($global_setting_errors, "There should not have been any errors, but we recorded: " . json_encode($global_setting_errors));
    }



    public function validate_first_volume_year_provider() {

        $settings = $this->test_initialize_settings();

        return [
            ['2017', '2017'],
            ['  2018 ', '2018'],
            ['  1962 ', '1962'],
            ['  1963', '1963'],
            ['1965 ', '1965'],
            ['@', $settings->get_plugin_option_default('first_volume_year')],
            ['asdf', $settings->get_plugin_option_default('first_volume_year')]
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

        $this->test_initialize_settings();
        $settings = O3PO_Settings::instance();

        return [
            ['https://doaj.org/api/v1/docs#!/CRUD_Articles/post_api_v1_articles', 'https://doaj.org/api/v1/docs#!/CRUD_Articles/post_api_v1_articles'],
            ['https://codex.wordpress.org/Function_Reference/get_post_thumbnail_id', 'https://codex.wordpress.org/Function_Reference/get_post_thumbnail_id'],
            ['ftp://fo.bar', 'ftp://fo.bar'], #in the fake wordpress environment of the test system esc_url and esc_url_raw currently have no effect, so this validation doesn't really work during unit tests
            ['  https://foo.de  ', 'https://foo.de'],
            ['foo.de', $settings->get_plugin_option_default('license_url')],
            ['/path/', $settings->get_plugin_option_default('license_url')],
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
            ['USA', 'EN'],
            ['2', 'EN'],
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

        $this->test_initialize_settings();
        $settings = O3PO_Settings::instance();

        return [
            ['1234567890', '1234567890'],
            ['-2', $settings->get_plugin_option_default('arxiv_paper_doi_feed_days')],
            ['0', $settings->get_plugin_option_default('arxiv_paper_doi_feed_days')],
            ['000', $settings->get_plugin_option_default('arxiv_paper_doi_feed_days')],
            ['0.2', $settings->get_plugin_option_default('arxiv_paper_doi_feed_days')],
            ['-0.3', $settings->get_plugin_option_default('arxiv_paper_doi_feed_days')],
            ['a', $settings->get_plugin_option_default('arxiv_paper_doi_feed_days')],
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

        $this->test_initialize_settings();
        $settings = O3PO_Settings::instance();

        return [
            ['checked', 'checked'],
            ['unchecked', 'unchecked'],
            ['foo', $settings->get_plugin_option_default('custom_search_page')],
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

        $this->assertSame($settings->trim_settings_field_ensure_not_empty_and_schedule_flush_rewrite_rules_if_changed('arxiv_paper_doi_feed_endpoint', ''), 'arxiv_paper_doi_feed');
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
         * @dataProvider validate_settings_provider
         * @depends test_initialize_settings
         */
    public function test_validate_settings( $input, $expected, $settings ) {

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

            if(method_exists($this, 'assertStringContainsString'))
                $this->assertStringContainsString('The non existing plugin option i-do-not-exist was requested.', $e->getMessage());
            else
                $this->assertContains('The non existing plugin option i-do-not-exist was requested.', $e->getMessage());
        }
    }


}
