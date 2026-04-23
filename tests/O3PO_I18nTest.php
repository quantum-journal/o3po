<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-i18n.php';

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

class O3PO_I18nTest extends O3PO_TestCase
{

        /**
         * @doesNotPerformAssertions
         */
    #[DoesNotPerformAssertions]
    public function test_execute_load_plugin_textdomain() {

        $i18n = new O3PO_i18n();
        $i18n->load_plugin_textdomain();

    }

}
