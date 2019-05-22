<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-utility.php';

class O3PO_UtilityTest extends PHPUnit_Framework_TestCase
{
        /**
         * @dataProvider orcid_provider
         */
    public function test_check_orcid( $orcid, $expected ) {

        $this->assertSame($expected, O3PO_Utility::check_orcid($orcid));
    }

    public function orcid_provider() {

        return [
            ["0000-0002-1483-5661", true],
            ["0000-0002-2054-9901", true],
            ["0000-0003-0290-4698", true],
            ["000A-0003-0290-4698", "is malformed"],
            ["000A-0003-0290-469", "is malformed"],
            ["000A-0003+0290-4698", "is malformed"],
            ["1234-5678-1234-5678", "did not pass the checksum test with result=3 but last digit=8"],
            ["0000-0102-1483-5661", "did not pass the checksum test with result=0 but last digit=1"],
                ];
    }

        /**
         * @dataProvider base_convert_arbitrary_precision_provider
         */
    public function test_base_convert_arbitrary_precision( $str, $frombase, $tobase , $expected ) {

        $this->assertSame($expected, O3PO_Utility::base_convert_arbitrary_precision($str, $frombase, $tobase));

    }

    public function base_convert_arbitrary_precision_provider() {

        return [
            ["0", 10, 10, "0"],
            ["0", 2, 2, "0"],
            ["00000", 2, 2, "0"],
            ["1", 2, 2, "1"],
            ["10", 2, 10, "2"],
            ["1234", 10, 2, "10011010010"],
            ["10011010010101010101010100101110101010", 2, 16, "2695554baa"],
            ["13452345134512345134132234625123", 7, 9, "4123037525075018448522786044"],
                ];
    }

        /**
         * @dataProvider remove_stopwords_provider
         */
    public function test_remove_stopwords( $str, $expected ) {

        $this->assertSame($expected, O3PO_Utility::remove_stopwords($str));

    }

    public function remove_stopwords_provider() {

         return [
             ["The big ground fox jumps over the lazy cat.", "big ground fox jumps over lazy cat."],
             ["An apple and a pear for an orange.", "apple pear orange."],
             ['a an the on for with at by and', ""],
                 ];
    }

        /**
         * @dataProvider oxford_comma_implode_provider
         */
    public function test_oxford_comma_implode( $array, $expected ) {

        $this->assertSame($expected, O3PO_Utility::oxford_comma_implode($array));

    }

    public function oxford_comma_implode_provider() {

         return [
             [["Foo"], "Foo"],
             [["Foo", "Bar"], "Foo and Bar"],
             [["Foo", "Bar", "Baz"], "Foo, Bar, and Baz"],
             [["Foo", "Bar", "Baz", "Bazz"], "Foo, Bar, Baz, and Bazz"],
             [array(), ""],
                 ];
    }

        /**
         * @dataProvider make_slash_breakable_html_provider
         */
    public function test_make_slash_breakable_html( $str, $expected ) {

        $this->assertSame($expected, O3PO_Utility::make_slash_breakable_html($str));
    }

    public function make_slash_breakable_html_provider() {

         return [
             ["/home/www/test", "/​home/​www/​test"],
             ["https://quantum-journal.org/papers/q-2018-07-05-75/", "https:/​/​quantum-journal.org/​papers/​q-2018-07-05-75/​"],
                 ];
    }



    public function valid_email_provider() {
        return [
            ['info@quantum-journal.org', true],
            ['info@subdomain.quantum-journal.org', true],
            ['a-öadaed@foo.com', true],
            /* ['a-öadaedfoo.com', false], */
            /* ['info@.org', false], */
            /* ['info@.', false], */
            ['info@', false],
            ['@foo.de', false],
            ['.@foo.de', true],
            ['@@foo.de', false],
        ];
    }

        /**
         * @dataProvider valid_email_provider
         */
    public function test_valid_email( $array, $expected ) {

        $this->assertSame($expected, O3PO_Utility::valid_email($array));
    }


    public function array_mean_provider() {
        return [
            [[1,1,1,1], 1],
            [[1,2,3], 2],
            [[0.11243,0.51234,3.1223], 1.2490233333333334],
            [[-0.74352,0.51234,-1.11324], -0.44813999999999998],
            ['foo', DomainException::class]
        ];
    }

        /**
         * @dataProvider array_mean_provider
         */
    public function test_array_mean( $array, $expected ) {

        if(!is_array($array))
            $this->expectException($expected);

        $this->assertSame($expected, O3PO_Utility::array_mean($array));
    }


    public function array_stddev_provider() {
        return [
            [[1,1,1,1], 0.0],
            [[1,2,3], 1.0],
            [[0.11243,0.51234,3.1223], 1.6345813238971421],
            [[-0.74352,0.51234,-1.11324], 0.85209423915433202],
            [[0.1531324], 0.0],
            ['asdfad', DomainException::class]
        ];
    }

        /**
         * @dataProvider array_stddev_provider
         */
    public function test_array_stddev( $array, $expected ) {

        if(!is_array($array))
            $this->expectException($expected);

        $this->assertSame($expected, O3PO_Utility::array_stddev($array));
    }


    public function array_median_provider() {
        return [
            [[1,1,1,1], 1],
            [[1,2,3], 2],
            [[0.11243,0.51234,3.1223, 7.7, 9.2], 3.1223],
            [[-0.74352,0.51234,-1.11324, 0.2], -0.27176],
            [[0.1531324], 0.1531324],
            ['asdfad', DomainException::class],
            [array(), DomainException::class],
        ];
    }

        /**
         * @dataProvider array_median_provider
         */
    public function test_array_median( $array, $expected ) {

        if(!is_array($array) or empty($array))
            $this->expectException($expected);

        $this->assertSame($expected, O3PO_Utility::array_median($array));
    }


}
