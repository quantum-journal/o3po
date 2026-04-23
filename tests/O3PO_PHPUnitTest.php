<?php

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\DataProvider;


class O3PO_PHPUnitTest extends O3PO_TestCase
{
    public static function date_provider() {
        return array([date("Y-m-d")]);
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider date_provider
         */
    #[RunInSeparateProcess]
    #[PreserveGlobalState('disabled')]
    #[DataProvider('date_provider')]
    public function test_date2( $date ) {
        $this->assertSame($date, date("Y-m-d"), "You are encountering a bug in PhPUnit that makes date() return different values inside tests that are runInSeparateProcess on systems whose time zone is not UTC and the date in your time zone is different from that in UTC.");
    }

}
