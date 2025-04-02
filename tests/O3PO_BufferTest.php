<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-buffer.php';

class O3PO_BufferTest extends O3PO_TestCase
{

    public function create_update_provider() {

        return [
            array(
                'buffer_url' => 'https://api.bufferapp.com/1',
                'access_token' => '1%2F345792aa62c_1',
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
                'access_token' => '1%2F345792aa62c_2',
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
                'access_token' => '1%2F345792aa62c_3',
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
                'access_token' => '1%2F345792aa62c_4',
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
                'access_token' => '1%2F345792aa62c_5',
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
            array(
                'buffer_url' => 'https://api.bufferapp.com/1',
                'access_token' => '1%2F345792aa62c_6', #this produces a WP_Error in wp_remote_post()
                'profile_ids' => '3514513134134',
                'text' => 'test text',
                'media' => array(
                    'link' => 'https://quantum-journal.org/two-years-of-publications/',
                    'photo' => 'https://quantum-journal.org/wp-content/uploads/2019/04/2years_publications_carnations.jpg',
                                 ),
                'attachment' => true,
                'shorten' => false,
                'now' => false,
                'top' => false,
                'expected' => new WP_Error('error', 'this url produces an error'),
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



    function get_profile_information_provider() {
        return [
            array(
                'https://api.bufferapp.com/1',
                '1/345792aa62c_7',
                array(
                    array(
                        'id' => '4352461346424513',
                        'service' => 'twitter'
                          ),
                    array(
                        'id' => '578c44fb3d66da874d311e98',
                        'service' => 'facebook'
                          ),
                ),
            ),
            array(
                'https://api.bufferapp.com/1',
                '1/345792aa62c_8_unhandled',
                new WP_Error('unhandled_url','Fake wp_remote_get() does not know how to handle https://api.bufferapp.com/1/profiles.json?access_token=1%2F345792aa62c_8_unhandled'),
                  ),

            array(
                'https://api.bufferapp.com/1',
                '1/345792aa62c_9',
                new WP_Error('error','Some error'),
                  ),


            array(
                'https://api.bufferapp.com/1',
                '1/345792aa62c_10',
                new WP_Error('exception', ''),
                  ),

                ];
    }

        /**
         * @dataProvider get_profile_information_provider
         */
    public function test_get_profile_information( $buffer_api_url, $access_token, $expected ){

        if(is_wp_error($expected))
        {
            $error = O3PO_Buffer::get_profile_information($buffer_api_url, $access_token);
            $this->assertTrue(is_wp_error($error));
            if(!empty($expected->get_error_message()))
                $this->assertStringContains($expected->get_error_message(), $error->get_error_message());
        }
        else
            $this->assertEquals(O3PO_Buffer::get_profile_information($buffer_api_url, $access_token), $expected);
    }
}
