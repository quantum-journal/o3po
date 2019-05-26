<?php

class O3PO_Test extends PHPUnit_Framework_TestCase
{
        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         */
    public function test_o3po() {

        define( 'WPINC', 'wp-includes' );
        include(dirname( __FILE__ ) . '/../o3po/o3po.php');

        global $hooks;
        global $filters;

            //Check that at least some standard hooks have been added
        foreach(["activation_hook","deactivation_hook","admin_menu","admin_init","init"] as $hook)
            $this->assertArrayHasKey($hook, $hooks);

            //and that some filters, which we want now and almost certainly also in the future, have been added
        foreach(["the_author","get_the_excerpt","the_content_feed","the_excerpt_rss"] as $filter)
            $this->assertArrayHasKey($filter, $filters);

            /* print(json_encode(array_keys($hooks))."\n"); */
            /* foreach($hooks as $key => $hook) */
            /*     print($key . ": " . json_encode($hook)."\n"); */
            /* print(json_encode(array_keys($filters))."\n"); */
            /* foreach($filters as $key => $filter) */
            /*     print($key . ": " . json_encode($filter)."\n"); */

            //Note that the following cannot be done in separate tests, as this test must be run in a separate process!

            //Test activation and deactivation
        activate_o3po();
        deactivate_o3po();

    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @doesNotPerformAssertions
         */
    public function test_uninstall_o3po() {
        define( 'WP_UNINSTALL_PLUGIN', 'true' );
        include(dirname( __FILE__ ) . '/../o3po/uninstall.php');
    }
}
