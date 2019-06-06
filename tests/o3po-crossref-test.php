<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-crossref.php';

class O3PO_CrossrefTest extends PHPUnit_Framework_TestCase
{

    public function get_cited_by_xml_body_provider() {
        return [
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => '10.22331/q-2017-04-25-8',
                'expected' => simplexml_load_string(file_get_contents(dirname(__FILE__) . '/resources' . '/crossref/q-2017-04-25-8.xml'))->query_result->body[0],
                  ),
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => '10.22331/q-2018-08-06-79',
                'expected' => simplexml_load_string(file_get_contents(dirname(__FILE__) . '/resources' . '/crossref/q-2018-08-06-79.xml'))->query_result->body[0],
                  ),
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => 'empty_response',
                'expected' => new WP_Error('exception', 'Could not fetch cited-by data for empty_response from Crossref. No response.'),
                  ),
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => 'invalid_xml',
                'expected' => new WP_Error('exception', 'Could not fetch cited-by data for invalid_xml from Crossref. This is normal if the DOI was registered recently.'),
                  ),
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => 'unhandled_doi',
                'expected' => new WP_Error('unhandled_url', 'Fake wp_remote_get() does not know how to handle https://fake_crossref_get_forward_links_url?usr=fake_crossref_id&pwd=fake_crossref_pw&doi=unhandled_doi&include_postedcontent=true'),
                  ),
                ];
    }

        /**
         * @dataProvider get_cited_by_xml_body_provider
         */
    public function test_get_cited_by_xml_body( $crossref_url, $crossref_id, $crossref_pw, $doi, $expected ) {

        $this->assertEquals($expected, O3PO_Crossref::get_cited_by_xml_body($crossref_url, $crossref_id, $crossref_pw, $doi));

    }



    public function get_cited_by_bibentries_provider() {
        return [
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => '10.22331/q-2017-04-25-8',
                'expected' => array(
                    0 => new O3PO_Bibentry(array(
                                               'authors' => array(
                                                   0 => new O3PO_Author('Jonathan', 'Olson'),
                                               ),
                                               'doi' => '10.1088/2040-8986/aae74a',
                                               'issn' => '2040-8978',
                                               'issue' => '12',
                                               'page' => '123501',
                                               'title' => 'The role of complexity theory in quantum optics—a tutorial for BosonSampling',
                                               'type' => 'full_text',
                                               'venue' => 'Journal of Optics',
                                               'volume' => '20',
                                               'year' => '2018',
                                                 )
                                           ),
                1 => new O3PO_Bibentry(array(
                                           'authors' => array(
                                               0 => new O3PO_Author('Alexandra E.', 'Moylett'),
                                               1 => new O3PO_Author('Peter S.', 'Turner'),
                                                              ),
                                           'doi' => '10.1103/PhysRevA.97.062329',
                                           'issn' => '2469-9926',
                                           'issue' => '6',
                                           'page' => '062329',
                                           'title' => 'Quantum simulation of partially distinguishable boson sampling',
                                           'type' => 'full_text',
                                           'venue' => 'Physical Review A',
                                           'volume' => '97',
                                           'year' => '2018',
                                             )
                                       ),
            2 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('Adam', 'Bouland'),
                                           1 => new O3PO_Author('Bill', 'Fefferman'),
                                           2 => new O3PO_Author('Chinmay', 'Nirkhe'),
                                           3 => new O3PO_Author('Umesh', 'Vazirani'),
                                                          ),
                                       'doi' => '10.1038/s41567-018-0318-2',
                                       'issn' => '1745-2473',
                                       'issue' => '2',
                                       'page' => '159',
                                       'title' => 'On the complexity and verification of quantum random circuit sampling',
                                       'type' => 'full_text',
                                       'venue' => 'Nature Physics',
                                       'volume' => '15',
                                       'year' => '2019',
                                         )
                                   ),
            3 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('Juan Miguel', 'Arrazola'),
                                           1 => new O3PO_Author('Eleni', 'Diamanti'),
                                           2 => new O3PO_Author('Iordanis', 'Kerenidis'),
                                                          ),
                                       'doi' => '10.1038/s41534-018-0103-1',
                                       'issn' => '2056-6387',
                                       'issue' => '1',
                                       'page' => '56',
                                       'title' => 'Quantum superiority for verifying NP-complete problems with linear optics',
                                       'type' => 'full_text',
                                       'venue' => 'npj Quantum Information',
                                       'volume' => '4',
                                       'year' => '2018',
                                         )
                                   ),
            4 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('Tameem', 'Albash'),
                                           1 => new O3PO_Author('Victor', 'Martin-Mayor'),
                                           2 => new O3PO_Author('Itay', 'Hen'),
                                                          ),
                                       'doi' => '10.1103/PhysRevLett.119.110502',
                                       'issn' => '0031-9007',
                                       'issue' => '11',
                                       'page' => '110502',
                                       'title' => 'Temperature Scaling Law for Quantum Annealing Optimizers',
                                       'type' => 'full_text',
                                       'venue' => 'Physical Review Letters',
                                       'volume' => '119',
                                       'year' => '2017',
                                         )
                                   ),
            5 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('Laszlo', 'Gyongyosi'),
                                           1 => new O3PO_Author('Sandor', 'Imre'),
                                                          ),
                                       'doi' => '10.1016/j.cosrev.2018.11.002',
                                       'issn' => '15740137',
                                       'page' => '51',
                                       'title' => 'A Survey on quantum computing technology',
                                       'type' => 'full_text',
                                       'venue' => 'Computer Science Review',
                                       'volume' => '31',
                                       'year' => '2019',
                                         )
                                   ),
            6 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('A.', 'Elben'),
                                           1 => new O3PO_Author('B.', 'Vermersch'),
                                           2 => new O3PO_Author('M.', 'Dalmonte'),
                                           3 => new O3PO_Author('J. I.', 'Cirac'),
                                           4 => new O3PO_Author('P.', 'Zoller'),
                                                          ),
                                       'doi' => '10.1103/PhysRevLett.120.050406',
                                       'issn' => '0031-9007',
                                       'issue' => '5',
                                       'page' => '050406',
                                       'title' => 'Rényi Entropies from Random Quenches in Atomic Hubbard and Spin Models',
                                       'type' => 'full_text',
                                       'venue' => 'Physical Review Letters',
                                       'volume' => '120',
                                       'year' => '2018',
                                         )
                                   ),
            7 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('Juan', 'Bermejo-Vega'),
                                           1 => new O3PO_Author('Dominik', 'Hangleiter'),
                                           2 => new O3PO_Author('Martin', 'Schwarz'),
                                           3 => new O3PO_Author('Robert', 'Raussendorf'),
                                           4 => new O3PO_Author('Jens', 'Eisert'),
                                                          ),
                                       'doi' => '10.1103/PhysRevX.8.021010',
                                       'issn' => '2160-3308',
                                       'issue' => '2',
                                       'page' => '021010',
                                       'title' => 'Architectures for Quantum Simulation Showing a Quantum Speedup',
                                       'type' => 'full_text',
                                       'venue' => 'Physical Review X',
                                       'volume' => '8',
                                       'year' => '2018',
                                         )
                                   ),
            8 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('Stuart', 'Hadfield'),
                                           1 => new O3PO_Author('Zhihui', 'Wang'),
                                           2 => new O3PO_Author('Bryan', 'O\'Gorman'),
                                           3 => new O3PO_Author('Eleanor', 'Rieffel'),
                                           4 => new O3PO_Author('Davide', 'Venturelli'),
                                           5 => new O3PO_Author('Rupak', 'Biswas'),
                                       ),
                                       'doi' => '10.3390/a12020034',
                                       'issn' => '1999-4893',
                                       'issue' => '2',
                                       'page' => '34',
                                       'title' => 'From the Quantum Approximate Optimization Algorithm to a Quantum Alternating Operator Ansatz',
                                       'type' => 'full_text',
                                       'venue' => 'Algorithms',
                                       'volume' => '12',
                                       'year' => '2019',
                                         )
                                   ),
            9 => new O3PO_Bibentry(array(
                                       'authors' => array(
                                           0 => new O3PO_Author('Jacob', 'Miller'),
                                           1 => new O3PO_Author('Stephen', 'Sanders'),
                                           2 => new O3PO_Author('Akimasa', 'Miyake'),
                                                          ),
                                       'doi' => '10.1103/PhysRevA.96.062320',
                                       'issn' => '2469-9926',
                                       'issue' => '6',
                                       'page' => '062320',
                                       'title' => 'Quantum supremacy in constant-time measurement-based computation: A unified architecture for sampling and verification',
                                       'type' => 'full_text',
                                       'venue' => 'Physical Review A',
                                       'volume' => '96',
                                       'year' => '2017',
                                         )
                                   ),
                        /* There is no point comparing the whole set.*/
            /* 10 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1103/PhysRevX.9.011013', */
            /*                             'issn' => '2160-3308', */
            /*                             'issue' => '1', */
            /*                             'page' => '011013', */
            /*                             'title' => 'Pattern Recognition Techniques for Boson Sampling Validation', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Physical Review X', */
            /*                             'volume' => '9', */
            /*                             'year' => '2019', */
            /*                               ) */
            /*                         ), */
            /* 11 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1103/PhysRevA.98.012322', */
            /*                             'issn' => '2469-9926', */
            /*                             'issue' => '1', */
            /*                             'page' => '012322', */
            /*                             'title' => 'Quantum approximate optimization with Gaussian boson sampling', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Physical Review A', */
            /*                             'volume' => '98', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 12 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1098/rspa.2018.0427', */
            /*                             'issn' => '1364-5021', */
            /*                             'issue' => '2225', */
            /*                             'page' => '20180427', */
            /*                             'title' => 'Quantum advantage of unitary Clifford circuits with magic state inputs', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Proceedings of the Royal Society A: Mathematical, Physical and Engineering Sciences', */
            /*                             'volume' => '475', */
            /*                             'year' => '2019', */
            /*                               ) */
            /*                         ), */
            /* 13 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.4204/EPTCS.266.14', */
            /*                             'issn' => '2075-2180', */
            /*                             'page' => '209', */
            /*                             'title' => 'Information Theoretically Secure Hypothesis Test for Temporally Unstructured Quantum Computation (Extended Abstract)', */
            /*                             'venue' => 'Electronic Proceedings in Theoretical Computer Science', */
            /*                             'volume' => '266', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 14 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1126/science.aar3106', */
            /*                             'issn' => '0036-8075', */
            /*                             'issue' => '6412', */
            /*                             'page' => '308', */
            /*                             'title' => 'Quantum advantage with shallow circuits', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Science', */
            /*                             'volume' => '362', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 15 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1038/nature23458', */
            /*                             'issn' => '0028-0836', */
            /*                             'issue' => '7671', */
            /*                             'page' => '203', */
            /*                             'title' => 'Quantum computational supremacy', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Nature', */
            /*                             'volume' => '549', */
            /*                             'year' => '2017', */
            /*                               ) */
            /*                         ), */
            /* 16 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1103/PhysRevLett.120.200502', */
            /*                             'issn' => '0031-9007', */
            /*                             'issue' => '20', */
            /*                             'page' => '200502', */
            /*                             'title' => 'Impossibility of Classically Simulating One-Clean-Qubit Model with Multiplicative Error', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Physical Review Letters', */
            /*                             'volume' => '120', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 17 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.22331/q-2019-03-11-129', */
            /*                             'issn' => '2521-327X', */
            /*                             'page' => '129', */
            /*                             'title' => 'Strawberry Fields: A Software Platform for Photonic Quantum Computing', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Quantum', */
            /*                             'volume' => '3', */
            /*                             'year' => '2019', */
            /*                               ) */
            /*                         ), */
            /* 18 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1038/s41467-017-01637-7', */
            /*                             'issn' => '2041-1723', */
            /*                             'issue' => '1', */
            /*                             'page' => '1572', */
            /*                             'title' => 'Fast-forwarding of Hamiltonians and exponentially precise measurements', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Nature Communications', */
            /*                             'volume' => '8', */
            /*                             'year' => '2017', */
            /*                               ) */
            /*                         ), */
            /* 19 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1038/nphys4270', */
            /*                             'issn' => '1745-2473', */
            /*                             'issue' => '12', */
            /*                             'page' => '1153', */
            /*                             'title' => 'Classical boson sampling algorithms with superior performance to near-term experiments', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Nature Physics', */
            /*                             'volume' => '13', */
            /*                             'year' => '2017', */
            /*                               ) */
            /*                         ), */
            /* 20 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1038/s41598-018-35264-z', */
            /*                             'issn' => '2045-2322', */
            /*                             'issue' => '1', */
            /*                             'page' => '17191', */
            /*                             'title' => 'Quantum fluctuation theorem for error diagnostics in quantum annealers', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Scientific Reports', */
            /*                             'volume' => '8', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 21 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1038/s41586-019-0980-2', */
            /*                             'issn' => '0028-0836', */
            /*                             'issue' => '7747', */
            /*                             'page' => '209', */
            /*                             'title' => 'Supervised learning with quantum-enhanced feature spaces', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Nature', */
            /*                             'volume' => '567', */
            /*                             'year' => '2019', */
            /*                               ) */
            /*                         ), */
            /* 22 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1088/1361-6633/aab406', */
            /*                             'issn' => '0034-4885', */
            /*                             'issue' => '7', */
            /*                             'page' => '074001', */
            /*                             'title' => 'Machine learning & artificial intelligence in the quantum domain: a review of recent progress', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Reports on Progress in Physics', */
            /*                             'volume' => '81', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 23 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1103/PhysRevA.99.052304', */
            /*                             'issn' => '2469-9926', */
            /*                             'issue' => '5', */
            /*                             'page' => '052304', */
            /*                             'title' => 'Changing the circuit-depth complexity of measurement-based quantum computation with hypergraph states', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Physical Review A', */
            /*                             'volume' => '99', */
            /*                             'year' => '2019', */
            /*                               ) */
            /*                         ), */
            /* 24 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1126/science.aao4309', */
            /*                             'issn' => '0036-8075', */
            /*                             'issue' => '6385', */
            /*                             'page' => '195', */
            /*                             'title' => 'A blueprint for demonstrating quantum supremacy with superconducting qubits', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Science', */
            /*                             'volume' => '360', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 25 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1038/s41567-018-0124-x', */
            /*                             'issn' => '1745-2473', */
            /*                             'issue' => '6', */
            /*                             'page' => '595', */
            /*                             'title' => 'Characterizing quantum supremacy in near-term devices', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Nature Physics', */
            /*                             'volume' => '14', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
            /* 26 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.1093/nsr/nwy072', */
            /*                             'issn' => '2095-5138', */
            /*                             'issue' => '1', */
            /*                             'page' => '22', */
            /*                             'title' => 'Quantum supremacy: some fundamental concepts', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'National Science Review', */
            /*                             'volume' => '6', */
            /*                             'year' => '2019', */
            /*                               ) */
            /*                         ), */
            /* 27 => new O3PO_Bibentry(array( */
            /*                             'authors' => array(), */
            /*                             'doi' => '10.22331/q-2018-05-22-65', */
            /*                             'issn' => '2521-327X', */
            /*                             'page' => '65', */
            /*                             'title' => 'Anticoncentration theorems for schemes showing a quantum speedup', */
            /*                             'type' => 'full_text', */
            /*                             'venue' => 'Quantum', */
            /*                             'volume' => '2', */
            /*                             'year' => '2018', */
            /*                               ) */
            /*                         ), */
                                    ),
                  ),
            array(
                'crossref_url' => 'bad_url',
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => 'bad_doi',
                'expected' => new WP_Error('unhandled_url', 'Fake wp_remote_get() does not know how to handle bad_url?usr=fake_crossref_id&pwd=fake_crossref_pw&doi=bad_doi&include_postedcontent=true'),
                  ),
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => 'empty_response',
                'expected' => new WP_Error('exception', 'Could not fetch cited-by data for empty_response from Crossref. No response.'),
                  ),
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => 'varied_cites',
                'expected' => array(
                    new O3PO_Bibentry(array(
                                          'authors' => array(
                                              new O3PO_Author('Yosep', 'Kim'),
                                              new O3PO_Author('Kang-Hee', 'Hong'),
                                              new O3PO_Author('Joonsuk', 'Huh'),
                                              new O3PO_Author('Yoon-Ho', 'Kim'),
                                          ),
                                          'doi' => '10.1103/PhysRevA.99.052308',
                                          'issn' => '2469-9926',
                                          'issue' => '5',
                                          'page' => '052308',
                                          'title' => 'Experimental linear optical computing of the matrix permanent',
                                          'type' => 'full_text',
                                          'venue' => 'Physical Review A',
                                          'volume' => '99',
                                          'year' => '2019',

                                            )),
                    new O3PO_Bibentry(array(
                                          'authors' => array(
                                              new O3PO_Author('Frank', 'Leymann'),
                                          ),
                                          'collectiontitle' => 'Lecture Notes in Computer Science',
                                          'doi' => '10.1007/978-3-030-14082-3_19',
                                          'isbn' => '978-3-030-14081-6',
                                          'issn' => '0302-9743',
                                          'page' => '218',
                                          'type' => 'full_text',
                                          'volume' => '11413',
                                          'year' => '2019',
                                            )),
                                    ),
                  ),
            array(
                'crossref_url' => get_option('o3po-settings')['crossref_get_forward_links_url'],
                'crossref_id' => get_option('o3po-settings')['crossref_id'],
                'crossref_pw' => get_option('o3po-settings')['crossref_pw'],
                'doi' => 'unhandled_forward_link_type',
                'expected' => new WP_Error('exception', 'Encountered an unhandled forward link type.'),
                  ),
                ];
    }

        /**
         * @dataProvider get_cited_by_bibentries_provider
         */
    public function test_get_cited_by_bibentries( $crossref_url, $crossref_id, $crossref_pw, $doi, $expected ) {

        $all = O3PO_Crossref::get_cited_by_bibentries($crossref_url, $crossref_id, $crossref_pw, $doi);
        if(is_array($all))
        {
            $subset = array();
            for($i=0; $i < count($expected); $i++)
                $subset[$i] = $all[$i];
            $this->assertEquals($expected, $subset);
        }
        else
            $this->assertEquals($expected, $all);



    }


}
