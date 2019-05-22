<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-clockss.php';

class O3PO_ClockssTest extends PHPUnit_Framework_TestCase
{

    public function ftp_upload_meta_data_and_pdf_to_clockss_provider() {
        return [
            array(
                'clockss_xml' => 'doesn\'t matter',
                'pdf_path'  => 'no pdf',
                'remote_filename_without_extension'  => 'doesn\'t matter',
                'clockss_ftp_url'  => 'invalid_url',
                'clockss_username'  => 'none',
                'clockss_password'  => 'none',
                'expected' => '#ERROR: There was an exception during the ftp transfer to CLOCKSS.*#',
                  ),
                ];
    }

        /**
         * @runInSeparateProcess
         * @preserveGlobalState disabled
         * @dataProvider ftp_upload_meta_data_and_pdf_to_clockss_provider
         */
    public function test_ftp_upload_meta_data_and_pdf_to_clockss( $clockss_xml, $pdf_path, $remote_filename_without_extension, $clockss_ftp_url, $clockss_username, $clockss_password, $expected ) {

        $this->assertRegExp($expected, O3PO_Clockss::ftp_upload_meta_data_and_pdf_to_clockss($clockss_xml, $pdf_path, $remote_filename_without_extension, $clockss_ftp_url, $clockss_username, $clockss_password));

    }

}
