<?php

class O3PO_Test extends PHPUnit_Framework_TestCase
{
    public function test_o3po() {
        define( 'WPINC', 'wp-includes' );
        include(dirname( __FILE__ ) . '/../o3po/o3po.php');
    }
}
