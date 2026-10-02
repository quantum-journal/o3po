<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-people-shortcodes.php');
require_once(dirname( __FILE__ ) . '/O3PO_SettingsTest.php');

class O3PO_PeopleShortcodesTest extends O3PO_TestCase
{
    private $editor_uuid = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    private $former_editor_uuid = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
    private $coordinator_uuid = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
    private $original_settings;
    private $original_query;

    private function configure_people() {
        global $options;
        $this->original_settings = $options['o3po-settings'];
        $this->original_query = get_global_query();
        O3PO_SettingsTest::get_settings();
        $options['o3po-settings'] = array_merge($options['o3po-settings'], array(
            'person_first_names' => array('Ada', 'Grace', 'Katherine'),
            'person_last_names' => array('Lovelace', 'Hopper', 'Johnson'),
            'person_role' => array('editor', 'editor', 'coordinator'),
            'person_since_year' => array('2021', '2015', ''),
            'person_until_year' => array('', '2020', ''),
            'person_url' => array('https://ada.example', 'https://grace.example', 'https://katherine.example'),
            'person_affiliation' => array('Analytical Engines', 'Navy', 'NASA'),
            'person_country' => array('United Kingdom', 'United States', 'United States'),
            'person_extra' => array('Editor in chief', '', ''),
            'person_uuidv4' => array($this->editor_uuid, $this->former_editor_uuid, $this->coordinator_uuid),
        ));
    }

    protected function tearDown(): void {
        if(isset($this->original_settings))
        {
            global $options;
            $options['o3po-settings'] = $this->original_settings;
            set_global_query($this->original_query);
        }
        parent::tearDown();
    }

    public function test_editor_names_link_to_profile_pages_including_former_editors() {
        $this->configure_people();

        $html = O3PO_PeopleShortcodes::persons_ul_shortcode(array('former' => 'True'), null, 'persons-ul');

        $this->assertStringContains('href="https://foo.bar.com/editor/' . $this->editor_uuid . '/"', $html);
        $this->assertStringContains('href="https://foo.bar.com/editor/' . $this->former_editor_uuid . '/"', $html);
        $this->assertStringContains('href="https://katherine.example"', $html);
        $this->assertStringNotContains('href="https://ada.example"', $html);
    }

    public function test_editor_endpoint_sets_up_page_for_matching_editor_uuid() {
        $this->configure_people();

        $wp = (object) array('query_vars' => array('editor' => $this->former_editor_uuid));
        O3PO_PeopleShortcodes::handle_editor_endpoint_request($wp);

        global $wp_query;
        $this->assertSame($this->former_editor_uuid, $wp_query->query_vars['editor_profile_uuid']);
        $this->assertTrue($wp_query->query_vars['editor_profile_add_fake_post']);

        $posts = O3PO_PeopleShortcodes::add_fake_editor_post_to_query(array());
        $this->assertCount(1, $posts);
        $this->assertSame('page', $posts[0]->post_type);
        $this->assertSame('page.php', O3PO_PeopleShortcodes::editor_profile_template('index.php'));
    }

    public function test_editor_endpoint_uses_wordpress_404_for_unknown_or_non_editor_uuid() {
        $this->configure_people();

        $unknown = (object) array('query_vars' => array('editor' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd'));
        O3PO_PeopleShortcodes::handle_editor_endpoint_request($unknown);
        $this->assertSame('404', $unknown->query_vars['error']);

        $coordinator = (object) array('query_vars' => array('editor' => $this->coordinator_uuid));
        O3PO_PeopleShortcodes::handle_editor_endpoint_request($coordinator);
        $this->assertSame('404', $coordinator->query_vars['error']);
    }

    public function test_editor_profile_renders_existing_data_for_former_editor() {
        $this->configure_people();
        $query = new WP_Query(null, array('editor_profile_uuid' => $this->former_editor_uuid));

        ob_start();
        O3PO_PeopleShortcodes::editor_profile_at_loop_start($query);
        $html = ob_get_clean();

        $this->assertValidHTMLFragment($html);
        $this->assertStringContains('Grace Hopper', $html);
        $this->assertStringContains('Navy', $html);
        $this->assertStringContains('2015–2020', $html);
        $this->assertStringContains('https://grace.example', $html);
    }
}
