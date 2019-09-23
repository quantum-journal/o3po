<?php

/**
 * Encapsulates the interface with the external service Crossref.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-bibentry.php';

/**
 * Encapsulates the interface with the external service Crossref.
 *
 * Provides methods to interface with Crossref.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Crossref {

        /**
         * Http post meta data to Crossref.
         *
         * Submits meta-data in Crossref's xml format via http post to their servers.
         *
         * From the command line one could do roughly the same with curl as follows:
         *
         * curl -F 'operation=doMDUpload' -F 'login_id=XXXX' -F 'login_passwd=XXXX' -F 'fname=@/home/cgogolin/tmp/crossref-test.xml' https://test.crossref.org/servlet/deposit -v

         * @since    0.3.0
         * @access   private
         * @param    string   $doi_batch_id    Batch id of this upload.
         * @param    string   $crossref_xml    The xml to upload.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $crossref_url    The url of the crossref server to upload to.
         */
    public static function remote_post_meta_data_to_crossref( $doi_batch_id, $crossref_xml, $crossref_id, $crossref_pw, $crossref_url ) {

            // Construct the HTTP POST call
		$boundary = wp_generate_password(24);
		$headers = array( 'content-type' => 'multipart/form-data; boundary=' . $boundary );
		$post_fields = array(
			'operation' => 'doMDUpload',
 			'login_id' => $crossref_id,
 			'login_passwd' => $crossref_pw,
                             );
        $payload = '';
            // Add the standard POST fields
		foreach ( $post_fields as $name => $value ) {
            $payload .= '--' . $boundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
            $payload .= $value;
            $payload .= "\r\n";
		}
            // Attach the xml data
		if ( !empty($crossref_xml) && !empty($doi_batch_id) ) {
            $payload .= '--' . $boundary;
            $payload .= "\r\n";
            $payload .= 'Content-Disposition: form-data; name="' . 'fname' . '"; filename="' . $doi_batch_id . '.xml' . '"' . "\r\n";
            $payload .= 'Content-Type: text/xml' . "\r\n";
            $payload .= "\r\n";
            $payload .= $crossref_xml;
            $payload .= "\r\n";
		}
		$payload .= '--' . $boundary . '--';

		$response = wp_remote_post( $crossref_url, array(
                                        'headers' => $headers,
                                        'body' => $payload,
                                                         ) );

        return $response;
    }

        /**
         * Retrieve cited-by information for a given doi from Crossref.
         *
         * Uses Crossref's cited-by service.
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $crossref_url    The url of the crossref server from which to fetch cited by information.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $doi             The doi for which cited-by data is to be retrieved.
         * @param    int      $storage_time    The maximal time for which to cache the response in a transient (default 600 seconds).
         * @param    int      $timeout         Maximum time to wait for a response (default 6 seconds).
         * @return   mixed    Response of wp_remote_get() from Crossref or WP_Error.
         */
    private static function remote_get_cited_by( $crossref_url, $crossref_id, $crossref_pw, $doi, $storage_time=60*10, $timeout=6 ) {

        $request_url = $crossref_url . '?usr=' . urlencode($crossref_id).  '&pwd=' . urlencode($crossref_pw) . '&doi=' . urlencode($doi) . '&include_postedcontent=true';

        $response = get_transient('get_crossref_cited_by_' . $request_url);
        if(empty($response)) {
            $response = wp_remote_get($request_url, array('timeout' => $timeout));
            if(is_wp_error($response))
                return $response;
            set_transient('get_crossref_cited_by_' . $request_url, $response, $storage_time);
        }

        return $response;
    }

        /**
         * Retrieve cited-by information in xml format from Crossref.
         *
         * Uses Crossref's cited-by service to retrieve information about works
         * citing the given DOI in xml format.
         *
         * See http://data.crossref.org/reports/help/schema_doc/crossref_query_output2.0/query_output2.0.html
         * for more information.
         *
         * @since    0.3.0
         * @access   public
         * @param    string   $crossref_url    The url of the crossref server to upload to.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $doi             The doi for which cited-by data is to be retrieved.
         */
    public static function get_cited_by_xml_body( $crossref_url, $crossref_id, $crossref_pw, $doi ) {

        try
        {
            $use_errors=libxml_use_internal_errors(true);

            $response = static::remote_get_cited_by($crossref_url, $crossref_id, $crossref_pw, $doi);

            if(is_wp_error($response))
                return $response;
            else if(empty($response['body']) )
                throw new Exception("Could not fetch cited-by data for " . $doi . " from Crossref. No response.");
            else
            {
                $xml = simplexml_load_string($response['body']);
                if ($xml === false) {
                    $error = "Could not fetch cited-by data for " . $doi . " from Crossref. This is normal if the DOI was registered recently.";
                    if(!empty(libxml_get_errors()))
                    {
                        /* foreach(libxml_get_errors() as $e) { */
                        /*     $error .= " " . trim($e->message) . "."; */
                        /* } */
                        libxml_clear_errors();
                    }
                    throw new Exception($error);
                }
                $body = $xml->query_result->body[0];
                return $body;
            }
        } catch (Exception $e) {
            return new WP_Error("exception", $e->getMessage());
        } finally {
            libxml_use_internal_errors($use_errors);
        }
    }


        /**
         * Get cited by information for a given DOI as an array of bibtenries.
         *
         * @since    0.3.0
         * @access   public
         * @param    string   $crossref_url    The url of the crossref server to upload to.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $doi             The doi for which cited-by data is to be retrieved.
         * @return   array Array of bibentries citing the given DOI
         *
         */
    public static function get_cited_by_bibentries( $crossref_url, $crossref_id, $crossref_pw, $doi ) {

        try
        {
            $body = O3PO_Crossref::get_cited_by_xml_body($crossref_url, $crossref_id, $crossref_pw, $doi);

            if(is_wp_error($body))
                return $body;

            $citation_number = 0;
            $bibentries = array();

            foreach ($body->forward_link as $f_link) {
                if(!empty($f_link->journal_cite))
                    $cite = $f_link->journal_cite;
                elseif(!empty($f_link->book_cite))
                    $cite = $f_link->book_cite;
                elseif(!empty($f_link->conf_cite))
                    $cite = $f_link->conf_cite;
                elseif(!empty($f_link->dissertation_cite))
                    $cite = $f_link->dissertation_cite;
                elseif(!empty($f_link->report_cite))
                    $cite = $f_link->report_cite;
                elseif(!empty($f_link->standard_cite))
                    $cite = $f_link->standard_cite;
                else
                    throw new Exception("Encountered an unhandled forward link type.");

                $authors = array();
                if(!empty($cite->contributors->contributor))
                {
                    foreach ($cite->contributors->contributor as $contributor) {
                        $authors[] = new O3PO_Author($contributor->given_name, $contributor->surname);
                    }
                }

                $bibentries[] = new O3PO_Bibentry(
                    array(
                        'authors' => $authors,
                        'venue' => $cite->journal_title,
                        'title' => !empty($cite->title) ? $cite->title : $cite->article_title,
                        'collectiontitle' => !empty($cite->series_title) ? $cite->series_title : $cite->volume_title,
                        'volume' => $cite->volume,
                        'issue' => $cite->issue,
                        'page' => !empty($cite->first_page) ? $cite->first_page : $cite->item_number,
                        'year' => $cite->year,
                        'doi' => $cite->doi,
                        'isbn' => $cite->isbn,
                        'issn' => $cite->issn,
                        'type' => $cite->publication_type,
                          ));
            }
        }
        catch (Exception $e) {
            return new WP_Error("exception", $e->getMessage());
        }

        return $bibentries;
    }

}
