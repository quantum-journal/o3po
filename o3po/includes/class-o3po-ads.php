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

    public static function get_cited_by_json( $ads_api_search_url, $api_token, $eprint ) {

        $eprint_without_version = preg_replace('#v[0-9]+$#', '', $eprint);
        $headers = array( 'Authorization' => 'Bearer:' . $api_token );

        $url = $ads_api_search_url . '?q=' . 'arxiv:' . $eprint_without_version . '&fl=' . 'citation';
        $response = get_transient('get_cited_by_json_' . $url);
        if(empty($response)) {
            $response = wp_remote_get($url, array('headers' => $headers));
            if(is_wp_error($response))
                return '';
        }
        set_transient('get_cited_by_json_' . $url, $response, 10*60);
        $json = json_decode($response['body']);

        $bibcodes = $json->response->docs[0]->citation;
        $citing_bibcodes_querey = 'bibcode:' . implode($bibcodes, '+OR+bibcode:');
        echo($citing_bibcodes_querey);

        $url = $ads_api_search_url . '?q=' . $citing_bibcodes_querey . '&fl=' . 'doi,title,author,page,issue,volume,year,pub,pubdate';
        $response = get_transient('get_cited_by_json_' . $url);
        if(empty($response)) {
            $response = wp_remote_get($url, array('headers' => $headers));
            if(is_wp_error($response))
                return '';
        }
        set_transient('get_cited_by_json_' . $url, $response, 10*60);
        $json = json_decode($response['body']);

        $citation_list = array();
        foreach($json->response->docs as $doc)
        {
            $citation_list[] = array( #todo introduce a class to handle this!
                'doi' => $doc->doi,
                'title' => $doc->title,
                'authors' => $doc->author, #todo: split this up ["Wu, Anqi","Aoi, Mikio C.","Pillow, Jonathan W."]
                'page' => $doc->page,
                'issue' => $doc->issue,
                'volume' => $doc->volume,
                'year' => $doc->year,
                'venue' => $doc->pub,
                                     );
        }

        XXXX

        return $citation_list; #treat the return value reasonbly in publication-type.php and unify how crossref and ads citations are returned, i.e., implement a function in crossref.php to return the same format as here and split this function up!
    }
}
