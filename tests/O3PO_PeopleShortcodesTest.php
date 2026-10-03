<?php

require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-people-shortcodes.php');
require_once(dirname( __FILE__ ) . '/../o3po/includes/editor-pages.php');
require_once(dirname( __FILE__ ) . '/O3PO_SettingsTest.php');

class O3PO_PeopleShortcodesTest extends O3PO_TestCase
{
    private $editor_uuid = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    private $former_editor_uuid = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
    private $coordinator_uuid = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
    private $original_settings;
    private $original_query;
    private $original_papers;
    private $original_transient_result;
    private $paper_ids = array(990001, 990002, 990003);

    private function configure_people() {
        global $options;
        $this->original_settings = $options['o3po-settings'];
        $this->original_query = get_global_query();
        global $get_transient_returns;
        $this->original_transient_result = $get_transient_returns;
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
            global $get_transient_returns;
            $options['o3po-settings'] = $this->original_settings;
            $get_transient_returns = $this->original_transient_result;
            set_global_query($this->original_query);
        }
        if(isset($this->original_papers))
        {
            global $posts;
            foreach($this->original_papers as $paper_id => $post)
                if(null === $post)
                    unset($posts[$paper_id]);
                else
                    $posts[$paper_id] = $post;
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

    public function test_people_shortcode_sanitizes_external_urls_and_adds_safe_link_rel() {
        $this->configure_people();
        global $options;
        $options['o3po-settings']['person_url'][2] = 'javascript:alert(1)';

        $html = O3PO_PeopleShortcodes::persons_ul_shortcode(array('role' => 'coordinator'), null, 'persons-ul');

        $this->assertStringNotContains('javascript:', $html);
        $this->assertStringContains('rel="noopener noreferrer"', $html);
    }

    public function test_editor_endpoint_sets_up_page_for_matching_editor_uuid() {
        $this->configure_people();

        $wp = (object) array('query_vars' => array('editor' => $this->former_editor_uuid));
        O3PO_EditorPages::handle_editor_endpoint_request($wp);

        global $wp_query;
        $this->assertSame($this->former_editor_uuid, $wp_query->query_vars['editor_profile_uuid']);
        $this->assertTrue($wp_query->query_vars['editor_profile_add_fake_post']);

        $posts = O3PO_EditorPages::add_fake_editor_post_to_query(array());
        $this->assertCount(1, $posts);
        $this->assertSame('page', $posts[0]->post_type);
        $this->assertSame('page.php', O3PO_EditorPages::editor_profile_template('index.php'));
    }

    public function test_editor_endpoint_uses_wordpress_404_for_unknown_or_non_editor_uuid() {
        $this->configure_people();

        $unknown = (object) array('query_vars' => array('editor' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd'));
        O3PO_EditorPages::handle_editor_endpoint_request($unknown);
        $this->assertSame('404', $unknown->query_vars['error']);

        $coordinator = (object) array('query_vars' => array('editor' => $this->coordinator_uuid));
        O3PO_EditorPages::handle_editor_endpoint_request($coordinator);
        $this->assertSame('404', $coordinator->query_vars['error']);
    }

    public function test_editor_profile_renders_existing_data_for_former_editor() {
        $this->configure_people();
        $query = new WP_Query(null, array('editor_profile_uuid' => $this->former_editor_uuid));

        ob_start();
        O3PO_EditorPages::editor_profile_at_loop_start($query);
        $html = ob_get_clean();

        $this->assertValidHTMLFragment($html);
        $this->assertStringContains('Grace Hopper', $html);
        $this->assertStringContains('Navy', $html);
        $this->assertStringContains('2015–2020', $html);
        $this->assertStringContains('https://grace.example', $html);
    }

    public function test_editor_profile_lists_only_published_papers_handled_by_that_editor() {
        $this->configure_people();
        global $posts;
        foreach($this->paper_ids as $paper_id)
            $this->original_papers[$paper_id] = isset($posts[$paper_id]) ? $posts[$paper_id] : null;

        $posts[$this->paper_ids[0]] = array(
            'post_type' => 'paper',
            'post_status' => 'publish',
            'post_title' => 'Handled paper',
            'permalink' => 'https://foo.bar.com/papers/handled-paper/',
            'meta' => array('paper_handling_editor_uuidv4' => $this->former_editor_uuid),
        );
        $posts[$this->paper_ids[1]] = array(
            'post_type' => 'paper',
            'post_status' => 'draft',
            'post_title' => 'Draft paper',
            'permalink' => 'https://foo.bar.com/papers/draft-paper/',
            'meta' => array('paper_handling_editor_uuidv4' => $this->former_editor_uuid),
        );
        $posts[$this->paper_ids[2]] = array(
            'post_type' => 'paper',
            'post_status' => 'publish',
            'post_title' => 'Another editor paper',
            'permalink' => 'https://foo.bar.com/papers/another-editor-paper/',
            'meta' => array('paper_handling_editor_uuidv4' => $this->editor_uuid),
        );

        $query = new WP_Query(null, array('editor_profile_uuid' => $this->former_editor_uuid));
        ob_start();
        O3PO_EditorPages::editor_profile_at_loop_start($query);
        $html = ob_get_clean();

        $this->assertStringContains('Handled paper', $html);
        $this->assertStringContains('https://foo.bar.com/papers/handled-paper/', $html);
        $this->assertStringNotContains('Draft paper', $html);
        $this->assertStringNotContains('Another editor paper', $html);
        global $set_transient_calls;
        $last_transient = end($set_transient_calls);
        $this->assertSame('o3po_editor_handled_papers_' . $this->former_editor_uuid, $last_transient[0]);
        $this->assertSame((int)O3PO_Settings::instance()->get_field_value('cited_by_refresh_seconds'), $last_transient[2]);
    }

    public function test_cached_editor_paper_list_is_rendered_without_querying_again() {
        $this->configure_people();
        global $get_transient_returns, $wp_query_constructor_count;
        $get_transient_returns = array(array(
            'title' => 'Cached handled paper',
            'url' => 'https://foo.bar.com/papers/cached-paper/',
        ));
        $query = new WP_Query(null, array('editor_profile_uuid' => $this->former_editor_uuid));
        $query_count = $wp_query_constructor_count;

        ob_start();
        O3PO_EditorPages::editor_profile_at_loop_start($query);
        $html = ob_get_clean();

        $this->assertStringContains('Cached handled paper', $html);
        $this->assertSame($query_count, $wp_query_constructor_count);
    }

    public function test_invalid_editor_paper_cache_is_rebuilt() {
        $this->configure_people();
        global $get_transient_returns, $wp_query_constructor_count;
        $invalid_caches = array(
            'invalid cached value',
            array(array('title' => 'Missing URL')),
            array('not a paper record'),
            array(array('title' => 1, 'url' => 'https://example.org')),
            array(array('title' => 'Paper', 'url' => 1)),
        );
        foreach($invalid_caches as $invalid_cache)
        {
            $get_transient_returns = $invalid_cache;
            $query = new WP_Query(null, array('editor_profile_uuid' => $this->former_editor_uuid));
            $query_count = $wp_query_constructor_count;

            ob_start();
            O3PO_EditorPages::editor_profile_at_loop_start($query);
            $html = ob_get_clean();

            $this->assertStringContains('No published papers are currently listed.', $html);
            $this->assertSame($query_count + 1, $wp_query_constructor_count);
        }
    }

    public function test_saving_a_primary_paper_invalidates_only_its_editor_cache() {
        $this->configure_people();
        global $posts, $deleted_transients;
        $paper_id = 990004;
        $this->original_papers[$paper_id] = isset($posts[$paper_id]) ? $posts[$paper_id] : null;
        $posts[$paper_id] = array(
            'post_type' => 'paper',
            'meta' => array('paper_handling_editor_uuidv4' => $this->former_editor_uuid),
        );
        $deleted_transients = array();

        O3PO_EditorPages::invalidate_handled_papers_cache($paper_id);

        $this->assertContains('o3po_editor_handled_papers_' . $this->former_editor_uuid, $deleted_transients);
        $this->assertCount(1, $deleted_transients);
    }

    public function test_changing_publication_status_invalidates_editor_cache() {
        $this->configure_people();
        global $posts, $deleted_transients;
        $paper_id = 990006;
        $this->original_papers[$paper_id] = isset($posts[$paper_id]) ? $posts[$paper_id] : null;
        $posts[$paper_id] = array(
            'post_type' => 'paper',
            'meta' => array('paper_handling_editor_uuidv4' => $this->former_editor_uuid),
        );
        $deleted_transients = array();

        O3PO_EditorPages::invalidate_handled_papers_on_status_transition('trash', 'publish', new WP_Post($paper_id, 'paper'));

        $this->assertSame(array('o3po_editor_handled_papers_' . $this->former_editor_uuid), $deleted_transients);
        O3PO_EditorPages::invalidate_handled_papers_on_status_transition('publish', 'publish', new WP_Post($paper_id, 'paper'));
        $this->assertCount(1, $deleted_transients);
    }

    public function test_changing_handling_editor_invalidates_old_and_new_editor_caches() {
        $this->configure_people();
        global $posts, $deleted_transients;
        $paper_id = 990007;
        $this->original_papers[$paper_id] = isset($posts[$paper_id]) ? $posts[$paper_id] : null;
        $posts[$paper_id] = array(
            'post_type' => 'paper',
            'meta' => array('paper_handling_editor_uuidv4' => $this->editor_uuid),
        );
        $deleted_transients = array();

        O3PO_EditorPages::remember_editor_uuid_before_update(null, $paper_id, 'paper_handling_editor_uuidv4', $this->former_editor_uuid);
        $previous_assignments = new ReflectionProperty('O3PO_EditorPages', 'handling_editor_uuid_before_meta_change');
        $previous_assignments->setAccessible(true);
        $this->assertSame($this->editor_uuid, $previous_assignments->getValue()[$paper_id]);
        $posts[$paper_id]['meta']['paper_handling_editor_uuidv4'] = $this->former_editor_uuid;
        O3PO_EditorPages::invalidate_handled_papers_cache_on_meta_change(1, $paper_id, 'paper_handling_editor_uuidv4', $this->former_editor_uuid);

        $this->assertContains('o3po_editor_handled_papers_' . $this->editor_uuid, $deleted_transients);
        $this->assertContains('o3po_editor_handled_papers_' . $this->former_editor_uuid, $deleted_transients);
        $this->assertCount(2, $deleted_transients);
        $this->assertArrayNotHasKey($paper_id, $previous_assignments->getValue());

        $deleted_transients = array();
        O3PO_EditorPages::remember_editor_uuid_before_update(null, $paper_id, 'paper_handling_editor_uuidv4', $this->former_editor_uuid);
        O3PO_EditorPages::invalidate_handled_papers_cache_on_meta_change(1, $paper_id, 'paper_handling_editor_uuidv4', $this->former_editor_uuid);
        $this->assertArrayNotHasKey($paper_id, $previous_assignments->getValue());
        $this->assertCount(0, $deleted_transients);
    }

    public function test_deleting_handling_editor_meta_invalidates_the_deleted_editors_cache() {
        $this->configure_people();
        global $posts, $deleted_transients;
        $paper_id = 990008;
        $this->original_papers[$paper_id] = isset($posts[$paper_id]) ? $posts[$paper_id] : null;
        $posts[$paper_id] = array(
            'post_type' => 'paper',
            'meta' => array('paper_handling_editor_uuidv4' => $this->former_editor_uuid),
        );
        $deleted_transients = array();

        O3PO_EditorPages::remember_editor_uuid_before_meta_change(null, $paper_id, 'paper_handling_editor_uuidv4', '');
        unset($posts[$paper_id]['meta']['paper_handling_editor_uuidv4']);
        O3PO_EditorPages::invalidate_handled_papers_cache_on_meta_change(array(1), $paper_id, 'paper_handling_editor_uuidv4', '');

        $this->assertSame(array('o3po_editor_handled_papers_' . $this->former_editor_uuid), $deleted_transients);
    }

    public function test_secondary_publication_editor_meta_does_not_invalidate_primary_paper_caches() {
        $this->configure_people();
        global $posts, $deleted_transients;
        $post_id = 990009;
        $this->original_papers[$post_id] = isset($posts[$post_id]) ? $posts[$post_id] : null;
        $posts[$post_id] = array(
            'post_type' => 'view',
            'meta' => array('view_handling_editor_uuidv4' => $this->editor_uuid),
        );
        $deleted_transients = array();

        O3PO_EditorPages::remember_editor_uuid_before_meta_change(null, $post_id, 'view_handling_editor_uuidv4', $this->former_editor_uuid);
        $posts[$post_id]['meta']['view_handling_editor_uuidv4'] = $this->former_editor_uuid;
        O3PO_EditorPages::invalidate_handled_papers_cache_on_meta_change(1, $post_id, 'view_handling_editor_uuidv4', $this->former_editor_uuid);

        $this->assertCount(0, $deleted_transients);
    }

    public function test_saving_a_primary_paper_revision_or_autosave_does_not_invalidate_editor_cache() {
        $this->configure_people();
        global $posts, $deleted_transients, $revision_post_id, $autosave_post_id;
        $paper_id = 990005;
        $this->original_papers[$paper_id] = isset($posts[$paper_id]) ? $posts[$paper_id] : null;
        $posts[$paper_id] = array(
            'post_type' => 'paper',
            'meta' => array('paper_handling_editor_uuidv4' => $this->former_editor_uuid),
        );
        $deleted_transients = array();
        $revision_post_id = $paper_id;

        O3PO_EditorPages::invalidate_handled_papers_cache($paper_id);

        $this->assertCount(0, $deleted_transients);
        $revision_post_id = null;

        $autosave_post_id = $paper_id;
        O3PO_EditorPages::invalidate_handled_papers_cache($paper_id);
        $this->assertCount(0, $deleted_transients);
        $autosave_post_id = null;
    }
}
