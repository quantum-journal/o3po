<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-arxiv.php';

class O3PO_ArxivTest extends O3PO_TestCase
{

    public function eprint_provider() {
        return [
            array(
                'eprint' => '1609.09584v4',
                'expected' => array(
                    'arxiv_fetch_results' => 'SUCCESS: Fetched meta-data from https://arxiv.org/abs/1609.09584v4
',
                    'abstract' => 'Self-testing allows classical referees to verify the quantum behaviour of some untrusted devices. Recently we developed a framework for building large self-tests by repeating a smaller self-test many times in parallel. However, the framework did not apply to the CHSH test, which tests a maximally entangled pair of qubits. CHSH is the most well known and widely used test of this type. Here we extend the parallel self-testing framework to build parallel CHSH self-tests for any number of pairs of maximally entangled qubits. Our construction achieves an error bound which is polynomial in the number of tested qubit pairs.',
                    'number_authors' => 1,
                    'author_first_names' => array(
                        0 => 'Matthew'
                                                  ),
                    'author_last_names' => array(
                        0 => 'McKague'
                                               ),
                    'title' => 'Self-testing in parallel with CHSH',
                    'arxiv_license' => 'http://creativecommons.org/licenses/by-sa/4.0/'
                                    ),
                  ),
            array(
                'eprint' => '0809.2542v4',
                'expected' => array(
                    'arxiv_fetch_results' => 'ERROR: It seems like https://arxiv.org/abs/0809.2542v4 is not published under one of the creative commons licenses (CC BY 4.0, CC BY-SA 4.0, CC BY-NC-SA 4.0, or CC BY-NC-ND 4.0). Please inform the authors that they must put the paper on the arXiv under CC BY 4.0 and remind them that we will publish under CC BY 4.0 and that, by our terms and conditions, they grant us the right to do so.
',
                                        'abstract' => 'We study the dynamic properties of a model for wetting with two competing adsorbates on a planar substrate. The two species of particles have identical properties and repel each other. Starting with a flat interface one observes the formation of homogeneous droplets of the respective type separated by nonwet regions where the interface remains pinned. The wet phase is characterized by slow coarsening of competing droplets. Moreover, in 2+1 dimensions an additional line of continuous phase transition emerges in the bound phase, which separates an unordered phase from an ordered one. The symmetry under interchange of the particle types is spontaneously broken in this region and finite systems exhibit two metastable states, each dominated by one of the species. The critical properties of this transition are analyzed by numeric simulations.',
                                        'number_authors' => 4,
                                        'author_first_names' => array(
                                            0 => 'Christian',
                                            1 => 'Christian',
                                            2 => 'Marvin',
                                            3 => 'Haye',
                                                                      ),
                                        'author_last_names' => array(
                                            0 => 'Gogolin',
                                            1 => 'Meltzer',
                                            2 => 'Willers',
                                            3 => 'Hinrichsen',
                                                                   ),
                                        'title' => 'Dynamic wetting with two competing adsorbates',
                                        'arxiv_license' => 'http://arxiv.org/licenses/nonexclusive-distrib/1.0/'
                                    )),
            array(
                'eprint' => '0809.2542v5',
                'expected' => array(
                    'arxiv_fetch_results' => 'WARNING: Failed to fetch author information from https://arxiv.org/abs/0809.2542v5.
WARNING: Failed to fetch title from https://arxiv.org/abs/0809.2542v5.
WARNING: Failed to fetch abstract from https://arxiv.org/abs/0809.2542v5.
ERROR: No license informatin found on https://arxiv.org/abs/0809.2542v5.
',
                    'abstract' => '',
                    'number_authors' => 0,
                    'author_first_names' => array(),
                    'author_last_names' => array(),
                    'title' => '',
                    'arxiv_license' => ''
                                    ),
                  ),
            array(
                'eprint' => '14513.14351v5',
                'expected' => array('arxiv_fetch_results' => 'ERROR: Failed to fetch or parse arXiv abstract html for 14513.14351v5 Fake wp_remote_get() does not know how to handle https://arxiv.org/abs/14513.14351v5
'),
                  ),
                ];
    }

        /**
         * @dataProvider eprint_provider
         */
    public function test_fetch_meta_data_from_abstract_page( $eprint, $expected ) {

        $this->assertSame($expected, O3PO_Arxiv::fetch_meta_data_from_abstract_page('https://arxiv.org/abs/', $eprint));

    }



    public function eprint_submission_history_provider() {
        return [
            array(
                'eprint' => '1609.09584',
                'expected' => array(
                    'v1' => array(
                        "date" => 1475207010,
                        'size' => '12 KB',
                        'comment' => '',
                                  ),
                    'v2' => array(
                        "date" => 1476921373,
                        'size' => '12 KB',
                        'comment' => '',
                                  ),
                    'v3' => array(
                        "date" => 1480549524,
                        'size' => '23 KB',
                        'comment' => '',
                    ),
                    'v4' => array(
                        "date" => 1486681146,
                        'size' => '24 KB',
                        'comment' => 'Changes from anonymous referee comments',
                                  ),
                                    ),
                  ),
            array(
                'eprint' => '2006.01273',
                'expected' => array(
                    'v1' => array(
                        "date" => 1591046493,
                        'size' => '1,047 KB',
                        'comment' => '',
                                  ),
                    'v2' => array(
                        "date" => 1593627487,
                        'size' => '1,046 KB',
                        'comment' => '',
                                  ),
                    'v3' => array(
                        "date" => 1615899981,
                        'size' => '386 KB',
                        'comment' => '33 pages, 17 figures; v2 - typos corrected; v3 - rearrangements and compression for clarity, reformatted for publication',
                    ),
                                    ),
                  ),
            array(
                'eprint' => '2004.04173v4',
                'expected' => array(
                    'v1' => array(
                        "date" => 1586368805,
                        'size' => '878 KB',
                        'comment' => '',
                                  ),
                    'v2' => array(
                        "date" => 1607105669,
                        'size' => '1,621 KB',
                        'comment' => '',
                                  ),
                    'v3' => array(
                        "date" => 1628010357,
                        'size' => '2,466 KB',
                        'comment' => '',
                                  ),
                    'v4' => array(
                        "date" => 1643700249,
                        'size' => '2,244 KB',
                        'comment' => '12 pages, 8 figures (journal version)',
                                  ),
                                    ),
                  ),
            array(
                'eprint' => 'not-a-valid-eprint',
                'expected' => new WP_Error('exception', 'ERROR: Failed to fetch arXiv abstract page html or could not extract submission history for not-a-valid-eprint Fake wp_remote_get() does not know how to handle https://arxiv.org/abs/not-a-valid-eprint
'), #check that this function fails silently and returns a WP_Error in case the history could not be retrieved, we don't want users to see this error and rather disply it on the admin panel
                  ),
                ];
    }

        /**
         * @dataProvider eprint_submission_history_provider
         */
    public function test_get_submission_history_from_abstract_page( $eprint, $expected ) {

        $submission_history = O3PO_Arxiv::get_submission_history_from_abstract_page('https://arxiv.org/abs/', $eprint);

        if(is_wp_error($expected))
            $this->assertEquals($expected, $submission_history);
        else
        {
            if(is_wp_error($submission_history))
                $this->assertTrue(False, $submission_history->get_error_message());
            $this->assertEquals(
                array_keys($expected),
                array_keys($submission_history)
                                );

            $this->assertEquals($expected, $submission_history);
        }
    }


    public function eprint_upload_date_provider() {
        return [
            array(
                'eprint' => '1609.09584v4',
                'expected' => 1486681146,
                  ),
            array(
                'eprint' => '1609.09584v18',
                'expected' => new WP_Error('unhandled_url', 'Fake wp_remote_get() does not know how to handle https://arxiv.org/abs/1609.09584v18'),
                  ),
            array(
                'eprint' => '0809.2542v5',
                'expected' => new WP_Error('exception', 'Date could not be determined'),
                  )
                ];
    }


        /**
         * @dataProvider eprint_upload_date_provider
         */
    public function test_get_arxiv_upload_date( $eprint, $expected ) {

        $this->assertEquals($expected, O3PO_Arxiv::get_arxiv_upload_date('https://arxiv.org/abs/', $eprint));

    }

}
