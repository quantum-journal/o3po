<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-buffer.php';

class O3PO_BufferTest extends PHPUnit_Framework_TestCase
{

    public function create_update_provider() {

        return [
            array(
                'buffer_url' => 'https://api.bufferapp.com/1',
                'access_token' => '1/345792aa62c2435f2345ff1845365234',
                'profile_ids' => array('573423fb35252a874d311e98', '57828568375464d044868456'),
                'text' => 'test text',
                'media' => array(
                    'link' => 'https://quantum-journal.org/two-years-of-publications/',
                    'photo' => 'https://quantum-journal.org/wp-content/uploads/2019/04/2years_publications_carnations.jpg',
                                 ),
                'attachment' => true,
                'shorten' => false,
                'now' => false,
                'top' => false,
                'expected' => true,
                  ),
            array(
                'buffer_url' => 'https://api.bufferapp.com/1',
                'access_token' => 'invalid_token',
                'profile_ids' => array('573423fb35252a874d311e98', '57828568375464d044868456'),
                'text' => 'test text',
                'media' => array(
                    'link' => 'https://quantum-journal.org/two-years-of-publications/',
                    'photo' => 'https://quantum-journal.org/wp-content/uploads/2019/04/2years_publications_carnations.jpg',
                                 ),
                'attachment' => true,
                'shorten' => false,
                'now' => false,
                'top' => false,
                'expected' => new WP_Error('buffer_error', 'The provided access token is invalid'),
                  ),
            array(
                'buffer_url' => 'https://api.bufferapp.com/1',
                'access_token' => '1/345792aa62c2435f2345ff1845365234',
                'profile_ids' => array(), #this will cause a select_account error from buffer.com
                'text' => 'test text',
                'media' => array(
                    'link' => 'https://quantum-journal.org/two-years-of-publications/',
                    'photo' => 'https://quantum-journal.org/wp-content/uploads/2019/04/2years_publications_carnations.jpg',
                                 ),
                'attachment' => true,
                'shorten' => false,
                'now' => false,
                'top' => false,
                'expected' => new WP_Error('buffer_error', 'Please select at least one account to post from.'),
                  ),
            array(
                'buffer_url' => 'https://api.bufferapp.com/1',
                'access_token' => '1/345792aa62c2435f2345ff1845365234',
                'profile_ids' => 'profile_to_which_we_have_no_permission', #this will cause a no_permission error from buffer.com
                'text' => 'test text',
                'media' => array(
                    'link' => 'https://quantum-journal.org/two-years-of-publications/',
                    'photo' => 'https://quantum-journal.org/wp-content/uploads/2019/04/2years_publications_carnations.jpg',
                                 ),
                'attachment' => true,
                'shorten' => false,
                'now' => false,
                'top' => false,
                'expected' => new WP_Error('buffer_error', 'You do not have permission to post to any of the profile_id\'s provided.'),
                  ),
            array(
                'buffer_url' => new WP_Error('exception', 'When provided as URL I will raise an exception in create_update'),
                'access_token' => '1/f798bb6527f3de77832ab3a562ab59a9',
                'profile_ids' => array('578c44fb3d66da874d311e98', '578c441e0c5464d043cccfb6'),
                'text' => 'test text',
                'media' => array(
                    'link' => 'https://quantum-journal.org/two-years-of-publications/',
                    'photo' => 'https://quantum-journal.org/wp-content/uploads/2019/04/2years_publications_carnations.jpg',
                                 ),
                'attachment' => true,
                'shorten' => false,
                'now' => false,
                'top' => false,
                'expected' => new WP_Error('exception', 'Object of class WP_Error could not be converted to string'),
                  ),
            array(
                'buffer_url' => 'https://invalid.api.bufferapp.com/1',
                'access_token' => '1/345792aa62c2435f2345ff1845365234',
                'profile_ids' => array('573423fb35252a874d311e98', '57828568375464d044868456'),
                'text' => 'test text',
                'media' => array(
                    'link' => 'https://quantum-journal.org/two-years-of-publications/',
                    'photo' => 'https://quantum-journal.org/wp-content/uploads/2019/04/2years_publications_carnations.jpg',
                                 ),
                'attachment' => true,
                'shorten' => false,
                'now' => false,
                'top' => false,
                'expected' => new WP_Error('buffer_error', 'The response from buffer.com could not be interpreted.'),
                  ),
                ];
    }

        /**
         * @dataProvider create_update_provider
         */
    public function test_create_update( $buffer_url, $access_token, $profile_ids, $text, $media, $attachment, $shorten, $now, $top, $expected ) {

        $response = O3PO_Buffer::create_update($buffer_url, $access_token, $profile_ids, $text, $media, $attachment, $shorten, $now, $top);

        $this->assertEquals($expected, $response);
    }

}
