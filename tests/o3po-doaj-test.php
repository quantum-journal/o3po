<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-doaj.php';

class O3PO_DoajTest extends PHPUnit_Framework_TestCase
{

    public function remote_post_meta_data_to_doaj_provider() {
        return [
            array(
                'doaj_json' => json_encode('foo'),
                'doaj_api_url' => 'https://url.com',
                'doaj_api_key' => 'key',
                'expected' => Null
                  ),
                ];
    }

        /**
         * @dataProvider remote_post_meta_data_to_doaj_provider
         */
    public function test_remote_post_meta_data_to_doaj( $doaj_json, $doaj_api_url, $doaj_api_key, $expected ) {

        $this->assertSame($expected, O3PO_Doaj::remote_post_meta_data_to_doaj($doaj_json, $doaj_api_url, $doaj_api_key));

    }

}
