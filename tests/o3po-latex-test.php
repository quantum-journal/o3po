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
            ["\\'bax " , "\\'bax "],
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
            ["\\`bax " , "\\`bax "],
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


    public function utf8_to_closest_latin_letter_string_provider() {
        return [
            ['foo' , 'foo'],
            ['ä' , 'a'],
            ['á' , 'a'],
            ['ç' , 'c'],
            ['ü562457189(&(L' , 'uL'],
                ];
    }

        /**
         * @dataProvider utf8_to_closest_latin_letter_string_provider
         */
    public function test_utf8_to_closest_latin_letter_string( $input, $expected ) {
        $this->assertSame($expected, O3PO_Latex::utf8_to_closest_latin_letter_string($input));
    }



    public function preg_split_at_latex_math_mode_delimters_provider() {
        return [
            ['foo' , ['foo']],
            ['foo \\$ bar' , ['foo \\$ bar']],
            ['foo $a$ bar' , ['foo ', 'a', ' bar']],
            ['foo $$a$$ bar' , ['foo ', 'a', ' bar']],
            ['foo \\[a\\] bar' , ['foo ', 'a', ' bar']],
            ['foo \\(a\\) bar' , ['foo ', 'a', ' bar']],
            ['foo \\begin{equation}a\\end{equation} bar' , ['foo ', 'a', ' bar']],
            ['foo \\begin{align*}a\\end{align*} bar' , ['foo ', 'a', ' bar']],
            ['$a$ bar' , ['', 'a', ' bar']],
            ['foo $a$' , ['foo ', 'a', '']],
            ['$a$' , ['', 'a', '']],
            ['\\begin{abstract} foo \\(a\\) bar' , ['\\begin{abstract} foo ', 'a', ' bar']],

                ];
    }

        /**
         * @dataProvider preg_split_at_latex_math_mode_delimters_provider
         */
    public function test_preg_split_at_latex_math_mode_delimters( $input, $expected ) {
        $this->assertSame($expected, O3PO_Latex::preg_split_at_latex_math_mode_delimters($input));
    }

    public function strpos_outside_math_mode_provider() {
        return [
            [['foo', 'o'] , 1],
            [['foo', 'x'] , false],
            [['foo $x$', 'x'] , false],
            [['foo $x$ bar', 'a'] , 6],
            [['foo $x$ \\bar', '\\'] , 5],
            [[' \\begin{abstract} foo $x$', '\\'] , 1],
            [['\\begin{abstract} foo $x$', '\\'] , 0],
            [['\\begin{equation} x + \alpha = 4 \\end{equation}', '\\'] , false],
            [['\\begin{equation} x + \alpha = 4 \\end{equation} x', 'x'] , 1],
                ];
    }

        /**
         * @dataProvider strpos_outside_math_mode_provider
         */
    public function test_strpos_outside_math_mode( $input, $expected ) {
        $this->assertSame($expected, O3PO_Latex::strpos_outside_math_mode($input[0], $input[1]));
    }


    public function preg_match_outside_math_mode_provider() {
        return [
            [['#o#', 'foo'] , 1],
            [['#x#', 'foo'] , 0],
            [['#x#', 'foo $x$'] , 0],
            [['#a#', 'foo $x$ bar'] , 1],
            [['#\\\\#', 'foo $x$ \\bar'] , 1],
            [['#\\\\#', '\\begin{abstract} foo $x$'] , 1],
            [['#\\\\#', '\\begin{abstract} foo $x$ \\bar'] , 2],
            [['#\\\\#', 'abc \\begin{equation} x + \alpha = 4 \\end{equation}'] , 0],
            [['#x#', '\\begin{equation} x + \alpha = 4 \\end{equation} x'] , 1],
            [['#\\\\#', 'foo \\cite{a} $x$ bar \\cite{b}'] , 2],
            [['#\\\\(?!cite)#', 'foo \\cite{a} $x$ bar \\cite{b}'] , 0],
            [['##', 'foo $x$'] , 2],
                ];
    }

        /**
         * @dataProvider preg_match_outside_math_mode_provider
         */
    public function test_preg_match_outside_math_mode( $input, $expected ) {
        $this->assertSame($expected, O3PO_Latex::preg_match_outside_math_mode($input[0], $input[1]));
    }


    public function normalize_whitespace_and_linebreak_characters_provider() {
        return [
            [["abc", True, False] , "abc"],
            [["a \t c", True, False] , "a c"],
            [["a\smallskip c", True, False] , "a c"],
            [["a\medskip c", True, False] , "a c"],
            [["a\bigskip c", False, False] , "a c"],
            [["ab  c  ", True, False] , "ab c"],
            [["  a  bc", True, False] , "a bc"],
            [["ab\nc", False, False] , "ab\nc"],
            [["ab\nc", True, False] , "ab c"],
            [["ab\\newline c", True, False] , "ab c"],
            [["ab\\newline c", False, False] , "ab\nc"],
            [["ab\\linebreak c", True, False] , "ab c"],
            [["ab\\linebreak c", False, False] , "ab\nc"],
            [["ab\\\\ c", True, False] , "ab c"],
            [["ab\\\\ c", False, False] , "ab\nc"],
            [["ab\\newlinec", false, False] , "ab\\newlinec"],
            [["ab\\newlinec", True, False] , "ab\\newlinec"],
            [["ab\\newline c", False, False] , "ab\nc"],
            [["a\\newline ab", False, False] , "a\nab"],
            [["a\\\\b", False, False] , "a\nb"],
            [["a\\\\\\newline b", False, False] , "a\n\nb"],
            [["a\\hspace{2cm}b", False, False] , "a b"],
            [["a\nb", False, True] , "a b"],
            [["a\n\nb", False, True] , "a\n\nb"],
            [["a\\newline\n ab", False, True] , "a\nab"],
            [["a\\newline\n\n ab", False, True] , "a\n\nab"],
            [["a\\newline\n\n\n ab", False, True] , "a\n\nab"],
            [["a\n\n\n\n\nb", False, True] , "a\n\nb"],
            [["a\n", False, True] , "a"],
            [["\nb", False, True] , "b"],
                ];
    }

        /**
         * @dataProvider normalize_whitespace_and_linebreak_characters_provider
         */
    public function test_normalize_whitespace_and_linebreak_characters( $input, $expected ) {
        $this->assertSame($expected, O3PO_Latex::normalize_whitespace_and_linebreak_characters($input[0], $input[1], $input[2]));
    }

}
