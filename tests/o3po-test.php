<?php

class O3PO_Test extends PHPUnit_Framework_TestCase
{
    public function test_o3po() {
        define( 'WPINC', 'wp-includes' );
        include(dirname( __FILE__ ) . '/../o3po/o3po.php');

        activate_o3po();
        deactivate_o3po();
    }

    public function test_activate_o3po() {
        activate_o3po();
    }

    public function test_deactivate_o3po() {
        deactivate_o3po();
    }
}
