<?php

/**
 * Encapsulates the interface with the external service ads.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-bibentry.php';

/**
 * Encapsulates the interface with the external service ads.
 *
 * Provides methods to interface with ads.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Ads {

        /**
         * Get json encoded cited-by information.
         *
         * Retrieves cited-by information in json format from ads.
         *
         * @since 0.3.0
         * @access private
         * @param string $ads_api_search_url      ADS api url.
         * @param string $api_token               ADS API token.
         * @param string $eprint                  Eprint for which cited-by information is to be retrieved.
         * @param string (optional) $storage_time Time for which to store the response in a transient.
         * @param string (optional) $timeout      Maximal time to wait for a response from ADS.
         * @return mixed Json encoded cited-by information or a WP_Error in case of an error.
         */
    private static function get_cited_by_json( $ads_api_search_url, $api_token, $eprint, $storage_time=60*60*12, $timeout=6 ) {

        if(empty($eprint))
            return array();

        $eprint_without_version = preg_replace('#v[0-9]+$#u', '', $eprint);
        $headers = array( 'Authorization' => 'Bearer:' . $api_token );

        $url = $ads_api_search_url . '?q=' . 'arxiv:' . urlencode($eprint_without_version) . '&fl=' . 'citation';
        $response = get_transient('get_ads_cited_by_json_' . $url);
        if(empty($response)) {
            $response = wp_remote_get($url, array('headers' => $headers, 'timeout' => $timeout));
            if(is_wp_error($response))
                return $response;
            if(isset($response['headers']['x-ratelimit-remaining']))
            {
                $remaining_queries = $response['headers']['x-ratelimit-remaining'];
                if($remaining_queries == 0)
                    return new WP_Error("rate_limitation", "Cannot retrieve data from ADS due to rate limitations.");
            }
            set_transient('get_ads_cited_by_json_' . $url, $response, $storage_time);
        }
        $json = json_decode($response['body']);
        if($json === Null)
            return new WP_Error("json_decode_failed", "No response from ADS or unable to decode the received json data when getting the list of citing works.");

        return $json;
    }

        /**
         * Query url for detailed bibliographic data
         *
         * Construct the full query for retrieving detailed
         * bibliographic data url from an array of bibcodes
         *
         * @sinde 0.3.0
         * @access private
         * @param string $ads_api_search_url      ADS api url.
         * @param array $bibcodes                 Bibcodes for which to construct the query url.
         * @param int $max_number_of_citations    Maximal number of citations to return.
         */
    private static function bibcodes_to_query_url( $ads_api_search_url, $bibcodes, $max_number_of_citations ) {

        $citing_bibcodes_querey = '';
        foreach($bibcodes as $bibcode)
        {
            if(empty($citing_bibcodes_querey))
                $citing_bibcodes_querey .= 'bibcode:';
            else
                $citing_bibcodes_querey .= '+OR+bibcode:';
            $citing_bibcodes_querey .= urlencode($bibcode);
        }
        $url = $ads_api_search_url . '?q=' . $citing_bibcodes_querey . '&fl=' . 'doi,title,author,page,issue,volume,year,pub,pubdate' . '&rows=' . $max_number_of_citations;
        return $url;
    }

        /**
         * Get cited-by information as an array of O3PO_Bibentries.
         *
         * @since 0.3.0
         * @access public
         * @param string $ads_api_search_url      ADS api url.
         * @param string $api_token               ADS API token.
         * @param string $eprint                  Eprint for which cited-by information is to be retrieved.
         * @param string (optional) $storage_time Time for which to store the response from ADS in a transient.
         * @param int $max_number_of_citations    Maximal number of citations to return.
         * @param string (optional) $timeout      Maximal time to wait for a response from ADS.
         * @return mixed Cited-by information as an array of O3PO_Bibentries or WP_Error
         */
    public static function get_cited_by_bibentries( $ads_api_search_url, $api_token, $eprint, $storage_time=60*60*12, $max_number_of_citations=1000, $timeout=10 ) {

        try
        {
            $json = static::get_cited_by_json($ads_api_search_url, $api_token, $eprint, $storage_time, $timeout);

            if(is_wp_error($json))
                return $json;

            if(isset($json->response->docs[0]->citation))
                $bibcodes = $json->response->docs[0]->citation;
            else
                return array();

                /**
                 * We break down $bibcodes into an array of chunks of
                 * bibcodes because the ADS api can only handle request
                 * urls up to 3094 characters
                 */
            $bibcode_chunks = array();
            foreach($bibcodes as $n => $bibcode){
                if(count($bibcode_chunks) === 0) {
                    $bibcode_chunks[] = [$bibcode];
                    continue;
                }
                else {
                    end($bibcode_chunks);
                    $last_key = key($bibcode_chunks);
                    $bibcode_chunks[$last_key][] = $bibcode;
                }

                $url = static::bibcodes_to_query_url($ads_api_search_url, $bibcode_chunks[$last_key], $max_number_of_citations);
                if(mb_strlen($url) > 4000) #4094 seems to be the max for this api
                {
                    array_pop($bibcode_chunks[$last_key]);
                    $bibcode_chunks[] = [$bibcode];
                }
            }

            $all_bibentries = array();
            foreach($bibcode_chunks as $bibcodes) {
                $url = static::bibcodes_to_query_url($ads_api_search_url, $bibcodes, $max_number_of_citations);
                $response = get_transient('get_ads_cited_by_json_' . $url);
                if(empty($response)) {
                    $headers = array( 'Authorization' => 'Bearer:' . $api_token );
                    $response = wp_remote_get($url, array('headers' => $headers, 'timeout' => $timeout));
                    if(is_wp_error($response))
                        return $response;
                    set_transient('get_ads_cited_by_json_' . $url, $response, $storage_time);
                }

                if(isset($response['headers']['x-ratelimit-remaining']))
                {
                    $remaining_queries = $response['headers']['x-ratelimit-remaining'];
                    if($remaining_queries == 0)
                        return new WP_Error("rate_limitation", "Cannot retrieve fresh data from ADS due to rate limitations.");
                }

                $json = json_decode($response['body']);
                if($json === Null)
                    return new WP_Error("json_decode_failed", "No response from ADS or unable to decode the received json data when querying for bibliographic information of citing works.");

                $bibentries = array();
                foreach($json->response->docs as $doc)
                {
                    $authors = array();
                    foreach($doc->author as $author)
                    {
                        $names = preg_split('#\s*,\s*#u', $author, -1, PREG_SPLIT_NO_EMPTY);
                        $authors[] = new O3PO_Author(!empty($names[1]) ? $names[1] : '', !empty($names[0]) ? $names[0] : '');
                    }

                    $bibentry_data = array(
                        'doi' => !empty($doc->doi) ? $doc->doi[0] : '',
                        'title' => !empty($doc->title) ? implode($doc->title, ' - ') : '',
                        'authors' => !empty($authors) ? $authors : '',
                        'page' => !empty($doc->page) ? $doc->page[0] : '',
                        'issue' => !empty($doc->issue) ? $doc->issue : '',
                        'volume' => !empty($doc->volume) ? $doc->volume : '',
                        'year' => !empty($doc->year) ? $doc->year : '',
                        'venue' => !empty($doc->pub) ? $doc->pub : '',
                                           );

                    #post process the page and venue in case of arXiv citations
                    if(mb_substr($bibentry_data['page'], 0, 6 ) === 'arXiv:')
                    {
                        $bibentry_data['eprint'] = mb_substr($bibentry_data['page'], 6);
                        $bibentry_data['page'] = '';
                        if(mb_substr($bibentry_data['venue'], 0, 5 ) === 'arXiv')
                            $bibentry_data['venue'] = '';
                    }

                    $bibentries[] = new O3PO_Bibentry($bibentry_data);
                }
                $all_bibentries = array_merge($all_bibentries, $bibentries);
            }
        }
        catch(Exception $e) {
            return new WP_Error('exception', 'There was an error parsing the data received from ADS: ' . $e->getMessage());
        }

        return $all_bibentries;
    }
}
