<?php

require_once(dirname( __FILE__ ) . '/../o3po/admin/class-o3po-admin.php');
#require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-publication-type.php');


class O3PO_AdminTest extends PHPUnit_Framework_TestCase
{

    private $admin;

    # As phpunit 8 requires a specification of a void return type for setUp(), as explained here https://thephp.cc/news/2019/02/help-my-tests-stopped-working, but PHP <7 does not support such declarations setUp() can no longer be used if the tests are to run across PHP versions.
    public function test_construct() {
        return new O3PO_Admin( 'o3po', '0.3.0', 'O-3PO' );
    }

        /**
         * @depends test_construct
         */
    public function test_get_plugin_name( $admin ) {

        $this->assertEquals($admin->get_plugin_name(), 'o3po');
    }

        /**
         * @depends test_construct
         */
    public function test_get_plugin_pretty_name( $admin ) {

        $this->assertEquals($admin->get_plugin_pretty_name(), 'O-3PO');
    }

        /**
         * @depends test_construct
         * @doesNotPerformAssertions
         */
    public function test_enqueue_styles( $admin ) {

        $admin->enqueue_styles();
    }

       /**
         * @depends test_construct
         * @doesNotPerformAssertions
         */
    public function test_enqueue_scripts( $admin ) {

        $admin->enqueue_scripts();
    }

        /**
         * @depends test_construct
         */
    public function test_add_plugin_action_links( $admin ) {

        ob_start();
        echo "<div>";
        foreach($admin->add_plugin_action_links(array('<a href="foo">foo</a>')) as $link_html)
            echo($link_html);
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
        $this->assertNotFalse($result);
    }

        /**
         * @depends test_construct
         */
    public function test_enable_mathjax( $admin ) {

        $settings = O3PO_Settings::instance();
        $settings->configure('o3po', '0.3.0', 'O-3PO', 'O3PO_PublicationType::get_active_publication_type_names');

        ob_start();
        echo "<div>";
        $admin->enable_mathjax();
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
        $this->assertNotFalse($result);
    }

        /**
         * @depends test_construct
         */
    public function test_add_meta_data_explorer_page_to_menu( $admin ) {

        $admin->add_meta_data_explorer_page_to_menu();
    }


    #render_meta_data_explorer() needs fully set up journal and post types to work properly so we test in in o3po-journal-and-post-types-test.php
    /* public function test_render_meta_data_explorer() { */

    /*     $this->admin->render_meta_data_explorer(); */
    /* } */
}
