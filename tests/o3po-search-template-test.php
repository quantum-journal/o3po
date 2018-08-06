<?php

class O3PO_SearchTemplateTest extends PHPUnit_Framework_TestCase
{

    public function search_template_provider() {
        global $posts;

        return [
            ['foobar', new WP_Query(), '', '#free to use the open source.*usage does not imply endorsement#'],
            ['foobar', new WP_Query(), "title-click", '#The manuscript.*is not published in #'],
            [$posts[1]['post_title'] , new WP_Query(array('ID' => 1)), "", '#' . $posts[1]['post_title'] . '#'],
            [$posts[1]['post_title'] , new WP_Query(array('ID' => 1)), "title-click", '#' . $posts[1]['post_title'] . '#'],
        ];
    }

        /**
         * @dataProvider search_template_provider
         */
    public function test_search_template( $search_query, $query, $reason, $expection ) {
        global $_GET;

        $_GET["reason"] = $reason;
        set_global_search_query($search_query);
        set_global_query($query);

        ob_start();
        include( dirname(__File__) . '/../o3po/public/templates/search.php');
        $output = ob_get_contents();
        ob_end_clean();
        $output = preg_replace('#(main|section)#', 'div', $output); # this is a brutal hack because $dom->loadHTML cannot cope with html 5
            //print($output);

        $this->assertRegexp($expection, $output);
        $this->assertRegexp('#' . $search_query . '#', $output);

        $dom = new DOMDocument;
        $dom->loadHTML($output);
            //$this->assertTrue($dom->validate()); //we cannot easily validate: https://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
    }
}
