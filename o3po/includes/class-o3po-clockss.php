<?php

/**
 * Encapsulates the interface with the external service CLOCKSS.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Encapsulates the interface with the external service CLOCKSS.
 *
 * Provides methods to interface with CLOCKSS.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Clockss {

        /**
         * Upload xml meta-data and pdf to CLOCKSS.
         *
         * Uploads xml meta-data and pdf to CLOCKSS for permanent archiving.
         *
         * @since    0.3.0
         * @access   public
         * @param    string     $clockss_xml      The xml encoded meta-data to upload.
         * @param    string     $pdf_path         Path to the local fulltext pdf
         * @param    string     $remote_filename_without_extension Filename without extension under which the files are to be deposited on the remore server
         * @param    string     $clockss_ftp_url  The url of the CLOCKSS ftp server.
         * @param    string     $clockss_username The CLOCKSS username
         * @param    string     $clockss_password The CLOCKSS password
         * @return   string     Description of what happened during the upload.
         */
    public static function ftp_upload_meta_data_and_pdf_to_clockss( $clockss_xml, $pdf_path, $remote_filename_without_extension, $clockss_ftp_url, $clockss_username, $clockss_password ) {

        $trackErrors = ini_get('track_errors');
        $ftp_connection = null;
        $clockss_response = '';
        try
        {
            set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
                });

            $tmpfile_clockss_xml = tempnam(sys_get_temp_dir(), $remote_filename_without_extension );
            file_put_contents($tmpfile_clockss_xml, $clockss_xml);

            if($clockss_ftp_url === 'invalid_url_used_in_unit_tests')
                throw new Exception('Got invalid_url_used_in_unit_tests as url, aborting because we take this as an indication that we were called in a unit test.');

            $ftp_connection = ftp_connect($clockss_ftp_url, 21, 10);
            $login_result = ftp_login($ftp_connection, $clockss_username, $clockss_password);

            if(ftp_put($ftp_connection, $remote_filename_without_extension . '.xml', $tmpfile_clockss_xml, FTP_BINARY))
                $clockss_response .= "INFO: successfully uploaded the meta-data xml to CLOCKSS.\n";
            else
                $clockss_response .= "ERROR: There was an error uploading the meta-data xml to CLOCKSS: " . $php_errormsg . "\n";

            if(ftp_put($ftp_connection, $remote_filename_without_extension . '.pdf', $pdf_path, FTP_BINARY))
                $clockss_response .= "INFO: successfully uploaded the fulltext pdf to CLOCKSS.\n";
            else
                $clockss_response .= "ERROR: There was an error uploading the fulltext pdf to CLOCKSS: " . $php_errormsg . "\n";


        } catch(Exception $e) {
            $clockss_response .= "ERROR: There was an exception during the ftp transfer to CLOCKSS. " . $e->getMessage() . "\n";
        } finally {
            ini_set('track_errors', $trackErrors);
            restore_error_handler();
            if($ftp_connection !== null)
            {
                try
                {
                    ftp_close($ftp_connection);
                } catch(Exception $e) {
                    $clockss_response .= "ERROR: There was an exception while closing the ftp connection to CLOCKSS. " . $e->getMessage() . "\n";
                }
            }
        }

        return $clockss_response;
    }
}
