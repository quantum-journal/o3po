<?php

/* require_once(dirname( __FILE__ ) . '/../o3po/admin/class-o3po-admin.php'); */
/* require_once(dirname( __FILE__ ) . '/O3PO_SettingsTest.php'); */

class DebugTest extends PHPUnit\Framework\TestCase # extends O3PO_TestCase
{

    private $admin;

    # As phpunit 8 requires a specification of a void return type for setUp(), as explained here https://thephp.cc/news/2019/02/help-my-tests-stopped-working, but PHP <7 does not support such declarations setUp() can no longer be used if the tests are to run across PHP versions.
        /**
         * @doesNotPerformAssertions
         */
    #[DoesNotPerformAssertions]
    public function test_construct() {
        return "foo";
    }

        /**
         * @depends test_construct
         */
    #[Depends('test_construct')]
    public function test_get_plugin_name( $admin ) {

        $this->assertEquals($admin, 'foo');
    }
}
