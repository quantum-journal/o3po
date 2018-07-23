<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-latex.php';

class O3PO_LatexTest extends PHPUnit_Framework_TestCase
{

 
    public function test_get_bbl_file() {

        return file_get_contents(dirname( __FILE__ ) . "/resources/test_bibliography.bbl");
    }

        /**
         * @depends test_get_bbl_file
         */ 
    public function test_extract_latex_macros( $bbl ) {

        return O3PO_Latex::extract_latex_macros($bbl);
    }

        /**
         * @depends test_extract_latex_macros
         */ 
    public function test_remove_special_macros_to_ignore_in_bbl( $bbl ) {

        return O3PO_Latex::remove_special_macros_to_ignore_in_bbl($bbl);
    }

        /**
         * @depends test_remove_special_macros_to_ignore_in_bbl
         */ 
    public function test_all_special_macros_removed( $latex_macro_definitions_without_specials ) {

        $special_macros_to_ignore = O3PO_Latex::get_special_macros_to_ignore_in_bbl();
                
        $this->assertTrue(!empty($latex_macro_definitions_without_specials));
        $this->assertTrue(!empty($special_macros_to_ignore));

        foreach($latex_macro_definitions_without_specials as $latex_macro_definition)
            $this->assertNotContains($latex_macro_definition[2], $special_macros_to_ignore);
        
    }
    
        /**
         * @depends test_get_bbl_file
         */ 
    public function test_parse_bbl( $bbl ) {

        return O3PO_Latex::parse_bbl($bbl);
    }


    public function get_month_string_provider() {
        return [
            [0, null],
            [1 , 'jan'],
            [2 , 'feb'],
            [3 , 'mar'],
            [4 , 'apr'],
            [5 , 'may'],
            [6 , 'jun'],
            [7 , 'jul'],
            [8 , 'aug'],
            [9 , 'sep'],
            [10 , 'oct'],
            [11 , 'nov'],
            [12 , 'dec'],
            [13 , null],
        ];
    }
    

        /**
         * @dataProvider get_month_string_provider
         */
    public function test_get_month_string( $month, $expected ) {
        $exception = null;
        try
        {
            $this->assertSame($expected, O3PO_Latex::get_month_string($month));
        } catch(Exception $e) {
            $exception = $e;
        }

        if($expected!=null and $exception!=null)
            throw($e);
        
        if($expected===null and $exception===null)
            throw(new Exception("get_month_string() should throw an exception for input " . $month));
    }
    
    
    

}
