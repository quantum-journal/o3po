<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-relevanssi.php';

class O3PO_RelevanssiTest extends PHPUnit_Framework_TestCase
{

    public function test_exclude_mime_types_by_regexp() {

        $this->assertFalse(O3PO_Relevanssi::exclude_mime_types_by_regexp(false, 6));
        $this->assertTrue(O3PO_Relevanssi::exclude_mime_types_by_regexp(false, 7));
        $this->assertTrue(O3PO_Relevanssi::exclude_mime_types_by_regexp(false, 13));

    }
}
