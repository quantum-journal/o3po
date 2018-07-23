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
}
