<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-shortcode-template.php';

class O3PO_ShortcodeTemplateTest extends O3PO_TestCase
{

    public function test_construct() {

        return new O3PO_ShortcodeTemplate('template with [shortcode]', array('[shortcode]' => array('description' => "A nice shortcode", 'example' => 'example value')));
    }


    public function test_fail_to_construct() {

        $this->expectException(InvalidArgumentException::class);
        new O3PO_ShortcodeTemplate('template with malformed [shortcode] specification', array('shortcode' => array('description' => "A malformed", 'example' => 'foo')));

    }

        /**
         * @depends test_construct
         */
    public function test_get_shortcodes($shortcode_template) {

        $this->assertSame($shortcode_template->get_shortcodes(), array('[shortcode]'));
    }


        /**
         * @depends test_construct
         */
    public function test_get_shortcode_descriptions($shortcode_template) {

        $this->assertSame($shortcode_template->get_shortcode_descriptions(), array('[shortcode]' => "A nice shortcode"));
    }


        /**
         * @depends test_construct
         */
    public function test_expand($shortcode_template) {

        $this->assertSame($shortcode_template->expand(array('[shortcode]' => "bar")), 'template with bar');

        $this->assertSame($shortcode_template->expand(array("bar")), 'template with bar');
    }


        /**
         * @depends test_construct
         */
    public function test_expand_fail_not_array($shortcode_template) {

        $this->expectException(InvalidArgumentException::class);
        $shortcode_template->expand(5);
    }


        /**
         * @depends test_construct
         */
    public function test_expand_fail_malformed_array($shortcode_template) {

        $this->expectException(InvalidArgumentException::class);
        $shortcode_template->expand(array('too', 'many', 'elements'));
    }

        /**
         * @depends test_construct
         */
    public function test_expand_no_value_provided($shortcode_template) {

        $this->expectException(InvalidArgumentException::class);
        $shortcode_template->expand(array('[other shortcode]' => 'baz'), true);
    }


        /**
         * @depends test_construct
         */
    public function test_example_expand($shortcode_template) {

        $this->assertSame($shortcode_template->example_expand(array('[shortcode]' => 'baz')), 'template with example value');
    }

        /**
         * @depends test_construct
         */
    public function test_render_short_codes($shortcode_template) {

        $this->assertValidHTMLFragment($shortcode_template->render_short_codes(array('[shortcode]' => 'baz')));
    }


}
