<?php

/**
 * Encapsulates the interface with the external service ads.
 *
 * @link       http://example.com
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
         *
         * If running out of queries storage time is automatically increased.
         */
    public static function get_cited_by_json( $ads_api_search_url, $api_token, $eprint, $storage_time=10*60, $timeout=20 ) {

        $eprint_without_version = preg_replace('#v[0-9]+$#', '', $eprint);
        $headers = array( 'Authorization' => 'Bearer:' . $api_token );

        $url = $ads_api_search_url . '?q=' . 'arxiv:' . $eprint_without_version . '&fl=' . 'citation';
        $response = get_transient('get_ads_cited_by_json_' . $url);
        if(empty($response)) {
            $response = wp_remote_get($url, array('headers' => $headers, 'timeout' => $timeout));
            if(is_wp_error($response))
                return $response;

            if(!empty($response['headers']['x-ratelimit-remaining']))
            {
                $remaining_queries = $response['headers']['x-ratelimit-remaining'];
                if($remaining_queries <= 100)
                    $storage_time = $storage_time*10;
                if($remaining_queries == 0)
                    return new WP_Error("Cannot retrieve data from ADS due to rate limitations.");
            }
            set_transient('get_ads_cited_by_json_' . $url, $response, $storage_time);
        }

        return json_decode($response['body']);
    }



        /**
         *
         * If running out of queries storage time is automatically increased.
         */
    public static function get_cited_by_bibentries( $ads_api_search_url, $api_token, $eprint, $storage_time=10*60, $max_number_of_citations=1000, $timeout=20 ) {

        $json = static::get_cited_by_json($ads_api_search_url, $api_token, $eprint);

        if(is_wp_error($json))
            return $json;

        if(isset($json->response->docs[0]->citation))
            $bibcodes = $json->response->docs[0]->citation;
        else
            return array();
        $citing_bibcodes_querey = 'bibcode:' . implode($bibcodes, '+OR+bibcode:');

        $url = $ads_api_search_url . '?q=' . $citing_bibcodes_querey . '&fl=' . 'doi,title,author,page,issue,volume,year,pub,pubdate' . '&rows=' . $max_number_of_citations;
        $response = get_transient('get_ads_cited_by_json_' . $url);
        if(empty($response)) {
            $headers = array( 'Authorization' => 'Bearer:' . $api_token );
            $response = wp_remote_get($url, array('headers' => $headers, 'timeout' => $timeout));
            if(is_wp_error($response))
                return $response;
            if(!empty($response['headers']['x-ratelimit-remaining']))
            {
                $remaining_queries = $response['headers']['x-ratelimit-remaining'];
                if($remaining_queries <= 100)
                    $storage_time = $storage_time*10;
                if($remaining_queries == 0)
                    return new WP_Error("Cannot retrieve data from ADS due to rate limitations.");
            }
            set_transient('get_ads_cited_by_json_' . $url, $response, $storage_time);
        }
        $json = json_decode($response['body']);

        $bibentries = array();
        foreach($json->response->docs as $doc)
        {
            $authors = array();
            foreach($doc->author as $author)
            {
                $names = preg_split('#\s*,\s*#', $author);
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
            if(substr($bibentry_data['page'], 0, 6 ) === 'arXiv:')
            {
                $bibentry_data['eprint'] = substr($bibentry_data['page'], 6);
                $bibentry_data['page'] = '';
                if(substr($bibentry_data['venue'], 0, 5 ) === 'arXiv')
                    $bibentry_data['venue'] = '';
            }

            $bibentries[] = new O3PO_Bibentry($bibentry_data);
        }

        return $bibentries;
    }
}
