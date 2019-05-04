<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-bibentry.php';

class O3PO_BibentryTest extends PHPUnit_Framework_TestCase
{

    public function match_provider() {
        return [
            [
                [new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zhang')), 'title' => 'Quantum algorithms to simulate many-body physics of correlated fermions', 'year' => 2017 )),
                 new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zhang')), 'title' => 'Quantum Algorithms to Simulate Many-Body Physics of Correlated Fermions', 'year' => 2018 ))
                 ], true],
            [
                [new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jang', 'Zhang')), 'title' => 'Quantum algorithms to simulate many-body physics of correlated fermions', 'year' => 2017 )),
                 new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zang')), 'title' => 'Quantum Algorithms to Simulate Many-Body Physics of Correlated Fermions', 'year' => 2018 ))
                 ], true],
            [
                [new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Quantum algorithms to simulate many-body physics of correlated fermions', 'year' => 2017 )),
                 new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zang')), 'title' => 'Quantum Algorithms to Simulate Many-Body Physics of Correlated Fermions', 'year' => 2018 ))
                 ], false],
            [
                [new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zang')), 'title' => 'Quantum algorithms for many-body physics of correlated fermions', 'year' => 2017 )),
                 new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zang')), 'title' => 'Quantum Algorithms to Simulate Many-Body Physics of Correlated Fermions', 'year' => 2010 ))
                 ], false],
            [
                [new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zang')), 'title' => 'Quantum algorithms for many-body physics', 'year' => 2010 )),
                 new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Jiang', 'Zang')), 'title' => 'Quantum Algorithms to Simulate Many-Body Physics of Correlated Fermions', 'year' => 2010 ))
                 ], false],
            [
                [new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                 new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))
                 ], true],
            [
                [new O3PO_Bibentry(array('eprint' => '0000.0001', 'titel' => "aasdfaefaefaefaefaefaefaef" )),
                 new O3PO_Bibentry(array('eprint' => '0000.0001', 'titel' => "ifnoifpiefpqe" )),
                 ], true],
            [
                [new O3PO_Bibentry(array('doi' => 'opijopn', 'titel' => "aasdfaefaefaefaefaefaefaef" )),
                 new O3PO_Bibentry(array('doi' => 'opijopn', 'titel' => "ifnoifpiefpqe" )),
                 ], true],
            [
                [new O3PO_Bibentry(array('eprint' => '0000.0001', 'titel' => "aasdfaefaefaefaefaefaefaef" )),
                 new O3PO_Bibentry(array('eprint' => '0000.0002', 'titel' => "aasdfaefaefaefaefaefaefaef" )),
                 ], false],
            [
                [new O3PO_Bibentry(array('doi' => 'opijopn', 'titel' => "aasdfaefaefaefaefaefaefaef" )),
                 new O3PO_Bibentry(array('doi' => 'xcvbxbv', 'titel' => "aasdfaefaefaefaefaefaefaef" )),
                 ], false],
                ];
    }


        /**
         * @dataProvider match_provider
         */
    public function test_match( $bibentries, $expected ) {

        $this->assertSame($expected, O3PO_Bibentry::match($bibentries[0], $bibentries[1]));

    }



    public function merge_bibitem_arrays_provider() {
        return [
            [
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 ))),
                true,
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 )))
             ],

            [
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 ))),
                false,
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 )))
             ],

            [
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                false,
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      )
             ],

            [
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                true,
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )))
             ],

            [
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 ))),
                false,
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 )))
             ],

            [
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 ))),
                true,
                array(0 => new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      2 => new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 )))
             ],

            [
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'A unique paper', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'And a second unique paper', 'year' => 2015 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 ))),
                array(new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 )),
                      new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),),
                true,
                array(0 => new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'A unique paper', 'year' => 2015 )),
                      1 => new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'Reasonable tile 1', 'year' => 2015 )),
                      4 => new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Foo', 'Bar')), 'title' => 'And a second unique paper', 'year' => 2015 )),
                      6 => new O3PO_Bibentry(array('authors' => array(new O3PO_Author('Baz', 'Zag')), 'title' => 'Another paper with title', 'year' => 2018 ))),
             ],

            [
                array(),
                array(),
                true,
                array(),
            ],
            [
                array(),
                array(),
                false,
                array(),
            ],

            [
                array(new O3PO_Bibentry()),
                array(),
                true,
                array(new O3PO_Bibentry()),
            ],
            [
                array(),
                array(new O3PO_Bibentry()),
                true,
                array(new O3PO_Bibentry()),
            ],
[
                array(new O3PO_Bibentry()),
                array(),
                false,
                array(new O3PO_Bibentry()),
            ],
            [
                array(),
                array(new O3PO_Bibentry()),
                false,
                array(new O3PO_Bibentry()),
            ],

                ];
    }

        /**
         * @dataProvider merge_bibitem_arrays_provider
         */
    public function test_merge_bibitem_arrays( $array1, $array2, $remove_dulicates, $expected ) {

        $this->assertEquals($expected, O3PO_Bibentry::merge_bibitem_arrays($array1, $array2, $remove_dulicates));

    }


    public function bibentry_provider() {
        return [
            [new O3PO_Bibentry(
                    array(
                        'authors' => array(new O3PO_Author('A', 'Foo'), new O3PO_Author('B', 'Bar'), new O3PO_Author('C', 'Baz')),
                        'day' => '4',
                        'doi' => '123112/132123.142142',
                        'eprint' => '1414.1234v3',
                        'issn' => '1234-151X',
                        'issue' => '5',
                        'month' => 'May',
                        'page' => '102',
                        'publisher' => 'Nice publisher',
                        'title' => 'Example paper',
                        'venue' => 'Nice journal',
                        'volume' => '4',
                        'year' => '2072',
                          )
                               ),
             "Nice journal 4 5, 102 (2072)",
             'A Foo, B Bar, and C Baz, "Example paper", <a href="fake_arxiv_abs_prefix/1414.1234v3">arXiv:1414.1234v3</a>, <a href="fake_doi_url_prefix/123112/132123.142142">Nice journal 4 5, 102 (2072)</a>.',
             'Foo, Bar, and Baz'],
            [new O3PO_Bibentry(
                    array(
                        'authors' => array(new O3PO_Author('Doris', 'Doe')),
                        'chapter' => '3',
                        'collectiontitle' => 'Interesting Series',
                        'day' => '4',
                        'editors' => array(new O3PO_Author('Egon', 'Ewald')),
                        'isbn' => '12341-12342143-1231243',
                        'month' => 'May',
                        'publisher' => 'Nice publisher',
                        'title' => 'Title',
                        'type' => 'book',
                        'url' => 'https://nice-publisher.com/isbn/12341-12342143-1231243/',
                        'year' => '1852',
                          )
                               ),
             "Interesting Series Nice publisher (1852) ISBN:12341-12342143-1231243",
             'Doris Doe, Editor: Egon Ewald, "Title", Interesting Series Nice publisher (1852) ISBN:12341-12342143-1231243.',
             'Doe and Ewald'],
            [new O3PO_Bibentry(
                    array(
                        'day' => '4',
                        'editors' => array(new O3PO_Author('Egon', 'Ewald'), new O3PO_Author('Gustav', 'Gans')),
                        'isbn' => '132-47633-34673573',
                        'publisher' => 'Nice publisher',
                        'title' => 'Title',
                        'type' => 'book',
                        'year' => '1972',
                          )
                               ),
             "Nice publisher (1972) ISBN:132-47633-34673573",
             'Editors: Egon Ewald and Gustav Gans, "Title", Nice publisher (1972) ISBN:132-47633-34673573.',
             'Ewald and Gans'],
            [new O3PO_Bibentry(
                    array(
                        'authors' => array(new O3PO_Author('Jon', 'Doe')),
                        'day' => '4',
                        'doi' => '126512/145674576asdf421',
                        'eprint' => '1412.1123v3',
                        'howpublished' => 'print',
                        'institution' => 'Foo University',
                        'month' => 'May',
                        'title' => 'Awesome thesis',
                        'type' => 'thesis',
                        'year' => '2055',
                          )
                               ),
             "Thesis Foo University (print) (2055)",
             'Jon Doe, "Awesome thesis", <a href="fake_arxiv_abs_prefix/1412.1123v3">arXiv:1412.1123v3</a>, <a href="fake_doi_url_prefix/126512/145674576asdf421">Thesis Foo University (print) (2055)</a>.',
             'Doe'],
            [new O3PO_Bibentry(
                    array(
                        'authors' => array(new O3PO_Author('Jon', 'Doe')),
                          )),
             '',
             'Jon Doe.',
             'Doe'],
            [new O3PO_Bibentry(
                    array(
                        'authors' => array(),
                          )),
             '',
             '.',
             '']
                ];

    }


        /**
         * @dataProvider bibentry_provider
         */
    public function test_get_cite_as_text( $bibentry, $expected_text, $expected_html, $expected_surnames ) {

        $this->assertEquals($expected_text, $bibentry->get_cite_as_text());

    }

        /**
         * @dataProvider bibentry_provider
         */
    public function test_get_formated_html( $bibentry, $expected_text, $expected_html, $expected_surnames ) {

        $this->assertEquals($expected_html, $bibentry->get_formated_html('fake_doi_url_prefix/', 'fake_arxiv_abs_prefix/'));

    }


        /**
         * @dataProvider bibentry_provider
         */
    public function test_get_surnames( $bibentry, $expected_text, $expected_html, $expected_surnames ) {

        $this->assertEquals($expected_surnames, $bibentry->get_surnames());

    }



}
