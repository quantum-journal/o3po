<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-ads.php';

class O3PO_AdsTest extends PHPUnit_Framework_TestCase
{

    public function ads_provider() {
        return [
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '',
                'expected' => array(),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0809.2542',
                'expected' => array(
                    0 => new O3PO_Bibentry(
                        array (
                            'authors' => array(new O3PO_Author('Shiqi', 'Zhou')),
                            'doi' => '10.1088/1742-5468/2011/05/P05023',
                            'issue' => '5',
                            'page' => '05023',
                            'title' => 'Acute effect of trace component on capillary phase transition of n-alkanes',
                            'venue' => 'Journal of Statistical Mechanics: Theory and Experiment',
                            'volume' => '2011',
                            'year' => '2011',
                               )
                                           ),
                    1 => new O3PO_Bibentry(
                        array(
                            'meta_data' => array(),
                            'authors' => array(new O3PO_Author('Shi-Qi', 'Zhou')),
                            'doi' => '10.1088/0253-6102/54/6/14',
                            'issue' => '6',
                            'month' => '',
                            'page' => '1023',
                            'title' => 'GENERAL Augmented Kierlik—Rosinberg Fundamental Measure Functional and Extension of Fundamental Measure Functional to Inhomogeneous Non-hard Sphere Fluids',
                            'venue' => 'Communications in Theoretical Physics',
                            'volume' => '54',
                            'year' => '2010',
                              )
                                           ),
                    2 => new O3PO_Bibentry(
                        array(
                            'authors' => array(new O3PO_Author('Anna I.', 'Posazhennikova'), new O3PO_Author('Joseph O.', 'Indekeu')),
                            'doi' => '10.1016/j.physa.2014.07.027',
                            'page' => '240',
                            'title' => 'Magnetic hierarchical deposition',
                            'venue' => 'Physica A Statistical Mechanics and its Applications',
                            'volume' => '414',
                            'year' => '2014',
                              )
                                           ),
                    3 => new O3PO_Bibentry(
                        array(
                            'authors' => array(new O3PO_Author('J.', 'Hooyberghs'), new O3PO_Author('J. O.', 'Indekeu')),
                            'doi' => '10.1140/epjb/e2011-20085-2',
                            'issue' => '2',
                            'page' => '155',
                            'title' => 'Nonequilibrium wetting transition in a nonthermal 2D Ising model',
                            'venue' => 'European Physical Journal B',
                            'volume' => '81',
                            'year' => '2011',
                              )
                                           ),
                                    ),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0000', #will produce a WP_Error in wp_remote_get()
                'expected' => new WP_Error('unhandled_url', 'Fake wp_remote_get() does not know how to handle https://api.adsabs.harvard.edu/v1/search/query?q=arxiv:0000.0000&fl=citation'),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0001',
                'expected' => new WP_Error("json_decode_failed", "No response from ADS or unable to decode the received json data when getting the list of citing works."),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0002',
                'expected' => array(),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0003',
                'expected' => new WP_Error("rate_limitation", "Cannot retrieve data from ADS due to rate limitations."),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0004',
                'expected' => new WP_Error("json_decode_failed", "No response from ADS or unable to decode the received json data when querying for bibliographic information of citing works."),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0005',
                'expected' => new WP_Error('unhandled_url', 'Fake wp_remote_get() does not know how to handle https://api.adsabs.harvard.edu/v1/search/query?q=bibcode:2014Abcde..13.5578E&fl=doi,title,author,page,issue,volume,year,pub,pubdate&rows=1000'),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0006',
                'expected' => new WP_Error('rate_limitation', 'Cannot retrieve fresh data from ADS due to rate limitations.'),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0007',
                'expected' => array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'eprint' => '1704.01998', 'title' => 'Information Theoretically Secure Hypothesis Test for Temporally Unstructured Quantum Computation', 'year' => '2017'))),
                  ),
            array(
                'ads_api_search_url' => 'https://api.adsabs.harvard.edu/v1/search/query',
                'api_token' => '',
                'eprint' => '0000.0008',
                'expected' => new WP_Error('exception', 'There was an error parsing the data received from ADS: Invalid argument supplied for foreach()'),
                  ),
                ];
    }

        /**
         * @dataProvider ads_provider
         */
    public function test_get_cited_by_bibentries( $ads_api_search_url, $api_token, $eprint, $expected ) {

        $this->assertEquals($expected, O3PO_Ads::get_cited_by_bibentries( $ads_api_search_url, $api_token, $eprint ));

    }

}
