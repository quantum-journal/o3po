<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-latex.php';

class O3PO_LatexTest extends PHPUnit_Framework_TestCase
{


    public function test_get_bbl_file() {

        $content = file_get_contents(dirname( __FILE__ ) . "/resources/test_bibliography.bbl");
        $this->assertNotEmpty($content);

        return $content;
    }

    public function test_get_biblatex_bbl_file() {

        $content = file_get_contents(dirname( __FILE__ ) . "/resources/test_bibliography_biblatex.bbl");

        $this->assertNotEmpty($content);

        return $content;
    }

    public function test_get_latex_file() {

        $content = file_get_contents(dirname( __FILE__ ) . "/resources/latex.tex");
        $this->assertNotEmpty($content);

        return $content;
    }

        /**
         * @depends test_get_bbl_file
         */
    public function test_extract_latex_macros_bbl( $bbl ) {

        $macros = O3PO_Latex::extract_latex_macros($bbl);
        $this->assertEquals(count($macros), 31);

        return $macros;
    }

        /**
         * @depends test_get_biblatex_bbl_file
         */
    public function test_extract_latex_macros_bbl_biblatex( $bbl ) {

        $macros = O3PO_Latex::extract_latex_macros($bbl);
        $this->assertEquals(count($macros), 0);
        return $macros;
    }

        /**
         * @depends test_extract_latex_macros_bbl
         */
    public function test_remove_special_macros_to_ignore_in_bbl( $latex_macro_definitions ) {

        $macros = O3PO_Latex::remove_special_macros_to_ignore_in_bbl($latex_macro_definitions);
        $this->assertEquals(count($macros), 29);
        return $macros;
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

        $parsed_bbl = O3PO_Latex::parse_bbl($bbl);
        $this->assertEquals(count($parsed_bbl), 64);

        return $parsed_bbl;
    }

        /**
         * @depends test_get_biblatex_bbl_file
         */
    public function test_parse_biblatex_bbl( $bbl ) {

        $parsed_bbl = O3PO_Latex::parse_bbl($bbl);
        $this->assertEquals(count($parsed_bbl), 294);

        return $parsed_bbl;
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

        if($expected!=null)
            $this->assertNull($exception);
        if($expected===null)
            $this->assertNotNull($exception);

    }

        /**
         * @depends test_get_latex_file
         */
    public function test_extract_latex_macros_latex( $latex ) {

        $macros = O3PO_Latex::extract_latex_macros($latex);;
        $this->assertEquals(count($macros), 68);

        return $macros;
    }

        /**
         * @depends test_extract_latex_macros_latex
         * @depends test_get_latex_file
         */
    public function test_expand_latex_macros_latex( $macro_definitions, $text ) {

        $text_expanded = O3PO_Latex::expand_latex_macros( $macro_definitions, $text );

        $text_doubl_expanded = O3PO_Latex::expand_latex_macros($macro_definitions, $text_expanded);

        $this->assertNotSame($text, $text_expanded);
        $this->assertSame($text_doubl_expanded, $text_expanded);
    }



    public function latex_to_utf8_outside_math_mode_test_case_provider() {
        return [
            ["\\'  \n a" , "á"],
            ["\\'  \n ab" , "áb"],
            ["\\'{   a}" , "á"],
            ["\\'{   a  }" , "á"],
            ["\\'{a}" , "á"],
            ["\\'\a" , "á"],
            ["\\'    a   }" , "á   }"],
            ["\\'a}" , "á}"],
            ["\\'a    " , "á    "],
            ["\\'{a}" , "á"],
            ["\\'{\\a}" , "á"],
            ["\\'xax " , "\\'xax "],
            ["\\'{a" , "\\'{a"],
            ["\\'{\\a" , "\\'{\\a"],
            ["\\`  \n a" , "à"],
            ["\\`  \n ab" , "àb"],
            ["\\`{   a}" , "à"],
            ["\\`{   a  }" , "à"],
            ["\\`{a}" , "à"],
            ["\\`\a" , "à"],
            ["\\`    a   }" , "à   }"],
            ["\\`a}" , "à}"],
            ["\\`a    " , "à    "],
            ["\\`{a}" , "à"],
            ["\\`{\\a}" , "à"],
            ["\\`xax " , "\\`xax "],
            ["\\`{a" , "\\`{a"],
            ["\\`{\\a" , "\\`{\\a"],
            ["\\`a}" , "à}"],
            ['\\"{a}' , 'ä'],
            ['\\"
a' , 'ä'],
            ['\\"
ab' , 'äb'],
            ['\\"{   a}' , 'ä'],
            ['\\"{   a  }' , 'ä'],
            ['\\"{a}' , 'ä'],
            ['\\"\a' , 'ä'],
            ['\\"    a   }' , 'ä   }'],
            ['\\"a}' , 'ä}'],
            ['\\"a    ' , 'ä    '],
            ['\\"{a}' , 'ä'],
            ['\\"{\\a}' , 'ä'],
            ['\\"xax ' , '\\"xax '],
            ['\\"{a' , '\\"{a'],
            ['\\"{\\a' , '\\"{\\a'],
            ['\\"a}' , 'ä}'],
            ['a\\ss b' , 'aßb'],
            ['a\\ss {}b' , 'aßb'],
            ['a\\ss {} b' , 'aß b'],
            ['a\\ssb' , 'a\\ssb'],
            ['a\\ss{b' , 'aß{b'],
            ['\\vs' , '\\vs'],
            ['\\v{s}' , 'š'],
            ['\\v\s' , 'š'],
            ['\\v{s    }' , 'š'],
            ['\\v s' , 'š'],
            ['\\vemph ' , '\\vemph '],
            ['\\vca' , '\\vca'],
            ['\\vc,' , '\\vc,'],
            ['\\v\\c' , 'č'],
                ];
    }

        /**
         * @dataProvider latex_to_utf8_outside_math_mode_test_case_provider
         */
    public function test_latex_to_utf8_outside_math_mode( $input, $expected ) {
        $this->assertSame($expected, O3PO_Latex::latex_to_utf8_outside_math_mode($input, false));
    }


}
