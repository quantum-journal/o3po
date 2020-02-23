<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-author.php';

class O3PO_AuthorTest extends O3PO_TestCase
{

    public function author_provider() {
        return [
            array(
                'given_name' => 'Foo',
                'surname' => 'Bar',
                'name_style' => '',
                'orcid' => '',
                'url' => '',
                'affiliations' => array('A University', 'B Institute'),
                'expected_exception' => Null,
                  ),
            array(
                'given_name' => 'Foo',
                'surname' => 'Baz',
                'name_style' => 'western',
                'orcid' => '0000-0003-0290-4698',
                'url' => '',
                'affiliations' => 'A University',
                'expected_exception' => Null,
                  ),
            array(
                'given_name' => 'Foo',
                'surname' => 'Bar',
                'name_style' => 'eastern',
                'orcid' => '',
                'url' => '',
                'affiliations' => array('A University', 'B Institute'),
                'expected_exception' => Null,
                  ),
            array(
                'given_name' => 'Foo',
                'surname' => 'Bar',
                'name_style' => 'islensk',
                'orcid' => 'invalidorcid',
                'url' => '',
                'affiliations' => array('A University', 'B Institute'),
                'expected_exception' => InvalidArgumentException::class,
                  ),
            array(
                'given_name' => 'Fooloolooo',
                'surname' => '',
                'name_style' => 'given-only',
                'orcid' => '',
                'url' => '',
                'affiliations' => 'B Institute',
                'expected_exception' => Null,
                  ),
            array(
                'given_name' => 'Foo',
                'surname' => 'Bar',
                'name_style' => 'invalid_namestyle',
                'orcid' => '',
                'url' => '',
                'affiliations' => 'A University,B Institute',
                'expected_exception' => InvalidArgumentException::class,
                  ),
            array(
                'given_name' => 'Foo',
                'surname' => 'Bar',
                'name_style' => 'western',
                'orcid' => '',
                'url' => '',
                'affiliations' => 42,
                'expected_exception' => InvalidArgumentException::class,
                  ),
                ];
    }

        /**
         * @dataProvider author_provider
         */
    public function test_match( $given_name, $surname, $name_style, $orcid, $url, $affiliations, $expected_exception ) {

        if(!empty($expected_exception))
            $this->expectException($expected_exception);

        $author = new O3PO_Author($given_name, $surname, $name_style, $orcid, $url, $affiliations);

        $this->assertSame($author->get_surname(), $surname);
        $this->assertSame($author->get_surname(), $author->get('surname'));
        $this->assertSame($author->get_affiliations_csv(), is_array($affiliations) ? implode($affiliations, ',') : $affiliations);
        $this->assertSame($author->get_name(), trim($given_name . ' ' . $surname));

    }

}
