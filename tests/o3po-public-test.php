<?php

require_once(dirname( __FILE__ ) . '/../o3po/public/class-o3po-public.php');


class O3PO_PublicTest extends O3PO_TestCase
{

    private $public;

        # As phpunit 8 requires a specification of a void return type for setUp(), as explained here https://thephp.cc/news/2019/02/help-my-tests-stopped-working, but PHP <7 does not support such declarations setUp() can no longer be used if the tests are to run across PHP versions.
        /**
         * @doesNotPerformAssertions
         */
    public function test_construct() {
        $public = new O3PO_Public( 'o3po', '0.3.0' );

        $file_data = get_file_data(dirname( __FILE__ ) . '/../o3po/o3po.php', array(
                                       'Version' => 'Version',
                                       'Plugin Name' => 'Plugin Name',
                                       'Text Domain' => 'Text Domain'
                                                                                    ));

        $settings = O3PO_Settings::instance();
        $settings->configure($file_data['Text Domain'], $file_data['Plugin Name'], $file_data['Version'], array( $this, 'fake_get_active_publication_type_names'));

        return $public;
    }

        /**
         * @doesNotPerformAssertions
         * @depends test_construct
         */
    public function test_enqueue_styles( $public ) {

        $public->enqueue_styles();
    }

       /**
         * @doesNotPerformAssertions
         * @depends test_construct
         */
    public function test_enqueue_scripts( $public ) {

        $public->enqueue_scripts();
    }


    public function id_provider() {

        return [[1],[5]];
    }

        /**
         * @dataProvider id_provider
         * @depends test_construct
         */
    public function test_add_open_graph_meta_tags_for_social_media_and_enable_mathjax_and_fix_custom_logo_html( $post_id, $public ) {
        global $is_single;

        $orig_is_single = $is_single;
        $is_single = true;

        $post = new WP_Post($post_id);
        set_global_query(new WP_Query(array('ID' => $post_id)));
        the_post();

        ob_start();
        echo "<div>";

        $public->add_open_graph_meta_tags_for_social_media();
        $public->enable_mathjax();
        echo($public->fix_custom_logo_html());

        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

        $is_single = $orig_is_single;
    }

        /**
         * @depends test_construct
         */
    public function test_extended_search_and_navigation_at_loop_start( $public ) {

        global $is_home;
        global $is_single;

        $orig_is_single = $is_single;
        $orig_is_home = $is_home;
        $is_single = true;
        $is_home = true;

        $query = new WP_Query(null, array('is_main' => true));

        ob_start();
        echo "<div>";
        $public->extended_search_and_navigation_at_loop_start($query);
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

        $is_single = $orig_is_single;
        $is_home = $orig_is_home;
    }


        /**
         * @depends test_construct
         */
    public function test_search_form_at_loop_start_on_search_page( $public ) {

        global $is_home;
        global $is_single;

        $orig_is_single = $is_single;
        $orig_is_home = $is_home;
        $is_single = true;
        $is_home = true;

        $query = new WP_Query(null, array('s' => "foo"));
        set_global_query($query);

        ob_start();
        echo "<div>";
        $public->search_form_at_loop_start_on_search_page($query);
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

        $is_single = $orig_is_single;
        $is_home = $orig_is_home;
    }


    public function secondary_journal_help_text_query_provider() {
        $settings = O3PO_Settings::instance();

        return [
            [new WP_Query(null, array('post_type' => $settings->get_plugin_option('secondary_publication_type_name')))],
            [new WP_Query(null, array('category' => O3PO_SecondaryPublicationType::get_associated_categories()[0]))],
        ];
    }

       /**
         * @dataProvider secondary_journal_help_text_query_provider
         * @depends test_construct
         */
    public function test_secondary_journal_help_text( $query, $public ) {

        global $is_home;
        global $is_single;

        $orig_is_single = $is_single;
        $orig_is_home = $is_home;
        $is_single = true;
        $is_home = true;

        set_global_query($query);

        ob_start();
        echo "<div>";
        $public->secondary_journal_help_text($query);
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertValidHTMLFragment($output);

        $is_single = $orig_is_single;
        $is_home = $orig_is_home;
    }
}
