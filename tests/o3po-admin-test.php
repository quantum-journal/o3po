<?php

require_once(dirname( __FILE__ ) . '/../o3po/admin/class-o3po-admin.php');
#require_once(dirname( __FILE__ ) . '/../o3po/includes/class-o3po-publication-type.php');


class O3PO_AdminTest extends PHPUnit_Framework_TestCase
{

    private $admin;

    protected function setUp() {
        $this->admin = new O3PO_Admin( 'o3po', '0.3.0', 'O-3PO' );
    }


    public function test_get_plugin_name() {

        $this->assertEquals($this->admin->get_plugin_name(), 'o3po');
    }

    public function test_get_plugin_pretty_name() {

        $this->assertEquals($this->admin->get_plugin_pretty_name(), 'O-3PO');
    }

        /**
         * @doesNotPerformAssertions
         */
    public function test_enqueue_styles() {

        $this->admin->enqueue_styles();
    }

       /**
         * @doesNotPerformAssertions
         */
    public function test_enqueue_scripts() {

        $this->admin->enqueue_scripts();
    }


    public function test_add_plugin_action_links() {

        ob_start();
        echo "<div>";
        foreach($this->admin->add_plugin_action_links(array('<a href="foo">foo</a>')) as $link_html)
            echo($link_html);
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
        $this->assertNotFalse($result);
    }

    public function test_enable_mathjax() {

        $settings = O3PO_Settings::instance();
        $settings->configure('o3po', '0.3.0', 'O-3PO', 'O3PO_PublicationType::get_active_publication_type_names');

        ob_start();
        echo "<div>";
        $this->admin->enable_mathjax();
        echo "</div>";
        $output = ob_get_contents();
        ob_end_clean();
        $dom = new DOMDocument;
        $result = $dom->loadHTML($output);
        $this->assertNotFalse($result);
    }

    public function test_add_meta_data_explorer_page_to_menu() {

        $this->admin->add_meta_data_explorer_page_to_menu();
    }


    #render_meta_data_explorer() needs fully set up journal and post types to work properly so we test in in o3po-journal-and-post-types-test.php
    /* public function test_render_meta_data_explorer() { */

    /*     $this->admin->render_meta_data_explorer(); */
    /* } */
}
