<?php

/**
 * Encapsulates the interface with the external service arXiv.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-latex.php';

/**
 * Encapsulates the interface with the external service arXiv.
 *
 * Provides methods to interface with arXiv.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Arxiv {

        /**
         * Fetch meta-data from the abstract page of an eprint on the arXiv.
         *
         * extracts the abstract, number_authors, author_given_names,
         * author_surnames and title
         *
         * @since  0.3.0
         * @access public
         * @param  string  $arxiv_url_abs_prefix The url prefix under which arXiv abstracts can be found.
         * @param  string  $eprint The eprint for which to fetch the meta-data.
         * @param  int     $timeout An optional timeout.
         * @return array   An array containing the extracted meta-data.
         */
    public static function fetch_meta_data_from_abstract_page( $arxiv_url_abs_prefix, $eprint, $timeout=10 ) {

        try
        {
            $arxiv_abs_page_url = $arxiv_url_abs_prefix . $eprint;
            $arxiv_fetch_results = '';
            $response = wp_remote_get( $arxiv_abs_page_url, array('timeout'=> $timeout) );
            if(!is_wp_error($response)) {
                $html = $response['body'];
                $dom = new DOMDocument;
                @$dom->loadHTML($html);
                $x_path = new DOMXPath($dom);

                $number_authors = 0;
                $author_given_names = array();
                $author_surnames = array();
                $title = '';
                $arxiv_author_links = $x_path->query("/html/body//div[@class='authors']/a");
                if(isset($arxiv_author_links[0]))
                {
                    foreach($arxiv_author_links as $x => $arxiv_author_link) {
                        $arxiv_author_names = preg_split('/\s+(?=\S+$)/u', $arxiv_author_link->nodeValue, -1, PREG_SPLIT_NO_EMPTY);
                        $author_given_names[$x] = empty($arxiv_author_names[0]) ? '' : $arxiv_author_names[0];
                        $author_surnames[$x] = empty($arxiv_author_names[1]) ? '' : $arxiv_author_names[1];
                        $number_authors = $x+1;
                    }
                }
                else
                    $arxiv_fetch_results .= "WARNING: Failed to fetch author information from " . $arxiv_abs_page_url . ".\n";

                $arxiv_titles = $x_path->query("/html/body//h1[contains(@class, 'title')]/text()[last()]");
                if(!empty($arxiv_titles->item(0)->nodeValue))
                    $arxiv_title_text = preg_replace("/[\r\n\s]+/u", " ", trim( $arxiv_titles->item(0)->nodeValue ) );
                if(!empty($arxiv_title_text) ) {
                    $title = addslashes( O3PO_Latex::latex_to_utf8_outside_math_mode($arxiv_title_text) );
                }
                else
                    $arxiv_fetch_results .= "WARNING: Failed to fetch title from " . $arxiv_abs_page_url . ".\n";

                $arxiv_abstracts = $x_path->query("/html/body//blockquote[contains(@class, 'abstract')]/text()[position()>0]");
                $arxiv_abstract_text = "";
                foreach($arxiv_abstracts as $arxiv_abstract_par)
                    $arxiv_abstract_text .= preg_replace('#\s+#u', ' ', trim($arxiv_abstract_par->nodeValue)) . "\n";
                $arxiv_abstract_text = trim($arxiv_abstract_text);

                $abstract = '';
                if (!empty($arxiv_abstract_text))
                    $abstract = addslashes( O3PO_Latex::latex_to_utf8_outside_math_mode($arxiv_abstract_text) );
                else
                    $arxiv_fetch_results .= "WARNING: Failed to fetch abstract from " . $arxiv_abs_page_url . ".\n";

                $arxiv_license_urls = $x_path->query("/html/body//div[contains(@class, 'abs-license')]/a/@href");
                if(isset($arxiv_license_urls[0]))
                    foreach ($arxiv_license_urls as $x => $arxiv_license_url) {
                        if( preg_match('#creativecommons.org/licenses/(by-nc-sa|by-sa|by)/4.0/#u', $arxiv_license_url->nodeValue) !== 1)
                            $arxiv_fetch_results .= "ERROR: It seems like " . $arxiv_abs_page_url . " is not published under one of the three creative commons license (CC BY 4.0, CC BY-SA 4.0, or CC BY-NC-SA 4.0). Please inform the authors that this is mandatory and remind them that we will publish under CC BY 4.0 and that, by our terms and conditions, they grant us the right to do so.\n";
                    }
                else
                    $arxiv_fetch_results .= "ERROR: No license informatin found on " . $arxiv_abs_page_url . ".\n";

                if (empty($arxiv_fetch_results))
                    $arxiv_fetch_results .= "SUCCESS: Fetched meta-data from " . $arxiv_abs_page_url . "\n";

                return array(
                    'arxiv_fetch_results' => $arxiv_fetch_results,
                    'abstract' => $abstract,
                    'number_authors' => $number_authors,
                    'author_given_names' => $author_given_names,
                    'author_surnames' => $author_surnames,
                    'title' => $title,
                             );
            }
            else
                throw new Exception($response->get_error_message());
        }
        catch(Exception $e) {
            return array(
                'arxiv_fetch_results' => "ERROR: Failed to fetch or parse arXiv abstract html for " . $eprint . " " . $e->getMessage() . "\n",
                         );
        }

    }

        /**
         * Download the source of an eprint from the arXiv.
         *
         * Uses the provided $evironment to download the
         * pdf of an eprint from the arXiv.
         *
         * @since  0.3.0
         * @access public
         * @param  O3PO_Environment  $environment                  The environment to use for downloading
         * @param  string            $arxiv_url_pdf_prefix         The url prefix under which arXiv pdfs can be found.
         * @param  string            $eprint                       The eprint whose source is to be downloaded
         * @param  string            $file_name_without_extension  The desired filename without extension of the local file after the download.
         * @param  int               $post_id                      Id of the post to which to attach the download.
         * @return mixed             Returns a map with information on the downloaded file. See O3PO_Environment for more details.
         */
    public static function download_pdf( $environment, $arxiv_url_pdf_prefix, $eprint, $file_name_without_extension, $post_id ) {

        $pdf_download_url = $arxiv_url_pdf_prefix . $eprint;
        return $environment->download_to_media_library($pdf_download_url, $file_name_without_extension, 'pdf', 'application/pdf', $post_id );
    }

        /**
         * Download the source of an eprint from the arXiv.
         *
         * The arXiv returns either a tar.gz file in case the authors'
         * submission consisted of multiple files, or a single
         * uncompressed tex file. The returned mime type is
         * accessible via the 'mime_type' key in the returned results.
         *
         * @since  0.3.0
         * @access public
         * @param  O3PO_Environment  $environment                  The environment to use for downloading
         * @param string $arxiv_url_source_prefix  The url prefix under which arXiv source can be found.
         * @param  string            $eprint                       The eprint whose source is to be downloaded
         * @param  string            $file_name_without_extension  The desired filename without extension of the local file after the download.
         * @param  int               $post_id                      Id of the post to which to attach the download.
         * @return mixed             Returns a map with information on the downloaded file. See O3PO_Environment for more details.
         */
    public static function download_source( $environment, $arxiv_url_source_prefix, $eprint, $file_name_without_extension, $post_id ) {

        $source_download_url = $arxiv_url_source_prefix . $eprint;
        return $environment->download_to_media_library($source_download_url, $file_name_without_extension, '', '', $post_id );
    }


        /**
         * Get the date at which a eprint was uploaded to the arXiv.
         *
         * @since 0.3.0
         * @access public
         * @param string $arxiv_url_abs_prefix  The url prefix under which arXiv abstracts can be found.
         * @param string $eprint                The eprint for which to get the upload date.
         * @param int    $timeout               An optional timeout.
         * @return int|WP_Error   The upload date
         */
    public static function get_arxiv_upload_date( $arxiv_url_abs_prefix, $eprint, $timeout=10 ) {

        try
        {
            $response = wp_remote_get( $arxiv_url_abs_prefix . $eprint, array('timeout'=> $timeout) );
            if(is_wp_error($response))
                return $response;

            $html = $response['body'];
            $dom = new DOMDocument;
            @$dom->loadHTML($html);
            $x_path = new DOMXPath($dom);
            $date = -1;
            $arxiv_submission_history = $x_path->query("/html/body//div[@class='submission-history']/b[last()]/following-sibling::text()");
            foreach($arxiv_submission_history as $entry){
                $date_info = $entry->nodeValue;
                preg_match('#[0-9]+ [A-Z][a-z]{2} [0-9]{4} [:0-9]+ [A-Z]+ #u', $date_info, $date);
                $date = strtotime(trim($date[0]));
            }

            if($date === -1)
                throw new Exception('Date could not be determined');
            else
                return $date;
        }
        catch(Exception $e) {
            return new WP_Error('exception', $e->getMessage());
        }
    }
}
