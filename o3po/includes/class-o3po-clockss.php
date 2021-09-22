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
            if($ftp_connection === False)
                $clockss_response .= "ERROR: Establishing an ftp connection to CLOCKSS under " . $clockss_ftp_url . " failed. Maybe try again in a few minutes.";
            else
            {
                $login_result = ftp_login($ftp_connection, $clockss_username, $clockss_password);
                if($login_result === False)
                    $clockss_response .= "ERROR: Could not log in to CLOCKSS under " . $clockss_ftp_url . " with user name " . $clockss_username . ".";
                else
                {
                    ftp_pasv($ftp_connection, true);

                    if(ftp_put($ftp_connection, $remote_filename_without_extension . '.xml', $tmpfile_clockss_xml, FTP_BINARY))
                        $clockss_response .= "INFO: successfully uploaded the meta-data xml to CLOCKSS.\n";
                    else
                        $clockss_response .= "ERROR: There was an error uploading the meta-data xml to CLOCKSS: " . $php_errormsg . "\n";

                    if(ftp_put($ftp_connection, $remote_filename_without_extension . '.pdf', $pdf_path, FTP_BINARY))
                        $clockss_response .= "INFO: successfully uploaded the fulltext pdf to CLOCKSS.\n";
                    else
                        $clockss_response .= "ERROR: There was an error uploading the fulltext pdf to CLOCKSS: " . $php_errormsg . "\n";
                }
            }
        } catch(Throwable $e) {
            $clockss_response .= "ERROR: There was an exception during the ftp transfer to CLOCKSS. " . $e->getMessage() . "\n";
        } finally {
            ini_set('track_errors', $trackErrors);
            restore_error_handler();
            if($ftp_connection !== False)
            {
                try
                {
                    set_error_handler(
                        function ($severity, $message, $file, $line) {
                            throw new ErrorException($message, $severity, $severity, $file, $line);
                        }
                                      );
                    ftp_close($ftp_connection);
                    restore_error_handler();
                } catch(Throwable $e) {
                    $clockss_response .= "ERROR: There was an exception while closing the ftp connection to CLOCKSS. " . $e->getMessage() . "\n";
                }
            }
        }

        return $clockss_response;
    }

    public static function generate_clockss_xml( $eissn, $journal, $journal_title, $publisher, $title, $title_mathml, $doi, $number_authors, $author_surnames, $author_given_names, $author_affiliations, $affiliations, $date_published, $volume, $pages, $license_name, $license_url, $abstract_mathml, $abstract ) {

                $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<article xmlns:mml="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" article-type="research-article" dtd-version="1.1" xml:lang="en">' . "\n";
        $xml .= '  <front>' . "\n";

        $xml .= '    <journal-meta>' . "\n";
        $xml .= '      <journal-id journal-id-type="publisher">' . esc_xml($journal) . '</journal-id>' . "\n";
        if(!empty($eissn) && $journal === $journal_title)
            $xml .= '      <issn>' . $eissn . '</issn>' . "\n";

        $xml .= '      <publisher>' . "\n";
        $xml .= '        <publisher-name>' . esc_xml($publisher) . '</publisher-name>' . "\n";
        $xml .= '      </publisher>' . "\n";
        $xml .= '    </journal-meta>' . "\n";

        $xml .= '    <article-meta>' . "\n";
        $xml .= '      <article-id pub-id-type="doi">' . esc_xml($doi) . '</article-id>' . "\n";
        $xml .= '      <title-group>' . "\n";
        $xml .= '        <article-title>' . "\n";
        $xml .= '          ' . (!empty($title_mathml) ? $title_mathml : esc_xml($title)) . "\n";
        $xml .= '        </article-title>' . "\n";
        $xml .= '      </title-group>' . "\n";

        $xml .= '      <contrib-group>' . "\n";
        for ($x = 0; $x < $number_authors; $x++) {
            $xml .= '        <contrib contrib-type="author">' . "\n";
            $xml .= '          <name>' . "\n";
            $xml .= '            <surname>' . esc_xml($author_surnames[$x]) . '</surname>' . "\n";
            $xml .= '            <given-names>' . esc_xml($author_given_names[$x]) . '</given-names>' . "\n";
            $xml .= '          </name>' . "\n";
            if ( !empty($author_affiliations) && !empty($author_affiliations[$x]) ) {
                foreach(preg_split('/\s*,\s*/u', $author_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY) as $affiliation_num) {
                    $xml .= '          <xref ref-type="aff" rid="aff-' . $affiliation_num . '"/>' . "\n";
                }
            }
            $xml .= '        </contrib>' . "\n";
        }
        $xml .= '      </contrib-group>' . "\n";
        foreach($affiliations as $n => $affiliation)
            $xml .= '      <aff id="aff-' . ($n+1) . '">' . esc_xml($affiliation) . '</aff>' . "\n";

        $xml .= '      <pub-date date-type="pub" publication-format="electronic" iso-8601-date="' . $date_published . '">' . "\n";
        $xml .= '        <day>' . mb_substr($date_published, 8, 2) . '</day>' . "\n";
        $xml .= '        <month>' . mb_substr($date_published, 5, 2) . '</month>' . "\n";
        $xml .= '        <year>' . mb_substr($date_published, 0, 4) . '</year>' . "\n";
        $xml .= '      </pub-date>' . "\n";
        $xml .= '      <volume>' . $volume . '</volume>' . "\n";
        $xml .= '      <fpage>' . $pages . '</fpage>' . "\n";
        $xml .= '      <permissions>' . "\n";
        $xml .= '        <copyright-statement>' . 'This work is published under the ' . esc_xml($license_name) . ' license ' . esc_xml($license_url) . '.' . '</copyright-statement>' . "\n";
        $xml .= '        <copyright-year>' . mb_substr($date_published, 0, 4) .'</copyright-year>' . "\n";
        $xml .= '      </permissions>' . "\n";
        if( !empty($abstract) || !empty($abstract_mathml) )
        {
            $xml .= '      <abstract>' . "\n";
            $xml .= '        <p>' . "\n";
            $xml .= '          ' . esc_xml(!empty($abstract_mathml) ? $abstract_mathml : $abstract) . "\n";
            $xml .= '        </p>' . "\n";
            $xml .= '      </abstract>' . "\n";
        }
        $xml .= '    </article-meta>' . "\n";

        $xml .= '  </front>' . "\n";

        $xml .= '  <body></body>' . "\n";
        $xml .= '  <back></back>' . "\n";
        $xml .= '</article>' . "\n";

        # re-encode escape sequences that are valid escape sequences in html but
        # not in xml to prevent such sequences from leaking into the xml
        $xml = preg_replace_callback('#&[A-Z0-9]+;#i', function ($matches) {
                return htmlentities(html_entity_decode($matches[0]), ENT_XML1);
            }, $xml);

        return $xml;
    }

}
