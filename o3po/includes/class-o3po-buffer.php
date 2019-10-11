<?php

/**
 * Encapsulates the interface with the external service Buffer.com.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-bibentry.php';

/**
 * Encapsulates the interface with the external service Buffer.com.
 *
 * Provides methods to interface with Buffer.com.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Buffer {

        /**
         * Http post meta data to Buffer.
         *
         * Schedule a social media update via Buffer.com's api.
         * For more information see here
         * https://buffer.com/developers/api/updates
         *
         * @since  0.3.0
         * @access public
         * @param   string $buffer_url        Url of the Buffer.com api.
         * @param  string $access_token      Access token.
         * @param   array|string $profile_ids An array of Buffer profile id’s of an individual id that the status update should be sent to.
         * @param   string $text       The status update text.
         * @param   array $media       An associative array of media to be attached to the update containing some of the following parameters: link, description, title, picture, photo, thumbnail.
         * @param   boolean $attachment Whether a link in the text should automatically populate the media parameter if media is empty.
         * @param   boolean $shorten   If shorten is false links within the text will not be automatically shortened, otherwise they will.
         * @param   boolean $now       If now is set, this update will be sent immediately to all profiles instead of being added to the buffer.
         * @param   boolean $top       If top is set, this update will be added to the top of the buffer and will become the next update sent.
         * @param   int     $timeout   Maximum number of seconds to wait for a response (default 15 seconds).
         * @return boolean|WP_Error    Returns true on success or a WP_Error in case an error occurred.
         */
    public static function create_update( $buffer_url, $access_token, $profile_ids, $text='', $media=array(), $attachment=true, $shorten=false, $now=false, $top=false, $timeout=15 ) {

        try
        {

            if(!is_array($profile_ids))
                $profile_ids = array($profile_ids);

            $headers = array( 'content-type' => 'application/x-www-form-urlencoded');
            $buffer_api_url = $buffer_url . '/updates/create.json';
            $buffer_api_url_with_token = $buffer_api_url . '?access_token=' . urlencode($access_token);

            $media_parts = array(
                'link',
                'description',
                'title',
                'picture',
                'photo',
                'thumbnail',
                                 );

            $body = array();

            if(!empty($profile_ids))
            {
                $body['profile_ids'] = array();
                foreach($profile_ids as $profile_id)
                    $body['profile_ids'][] = $profile_id;
            }
            if(!empty($text))
                $body['text'] = $text;

            if(!empty($media))
            {
                $body['media'] = array();
                foreach($media_parts as $media_part)
                    if(isset($media[$media_part]))
                        $body['media'][$media_part] = $media[$media_part];
            }
            $body['attachment'] = ( $attachment ? 'true' : 'false' );
            $body['shorten'] = ( $shorten ? 'true' : 'false' );
            $body['now'] = ( $now ? 'true' : 'false' );
            $body['top'] = ( $top ? 'true' : 'false' );

            $response = wp_remote_post( $buffer_api_url_with_token, array(
                                            'headers' => $headers,
                                            'body' => $body,
                                            'timeout' => $timeout,
                                            'method'    => 'POST'
                                                                          ) );

            if(is_wp_error($response))
                return $response;
            elseif(empty($response['body']))
                return new WP_Error("buffer_error", 'The response from buffer.com could not be interpreted.'); // @codeCoverageIgnore
            $json = json_decode($response['body']);
            if(isset($json->success) and $json->success === true)
                return true;
            elseif(isset($json->success) and $json->success !== true and !empty($json->message))
                return new WP_Error("buffer_error", $json->message);
            elseif(!empty($json->error))
                return new WP_Error("buffer_error", $json->error);
            else
                return new WP_Error("buffer_error", 'The response from buffer.com could not be interpreted.');
        } catch(Exception $e) {
            return new WP_Error("exception", $e->getMessage());
        }
    }


        /**
         * Get a list of services and profiles that are accessible via a given acces token.
         *
         * @since 0.3.0
         * @access public
         * @param  string $buffer_api_url        Url of the Buffer.com api.
         * @param  string $access_token      Access token.
         * @param  int    $timeout          Timeout of the request in seconds (default 2 seconds)
         * @return array|WP_Error     An array of associative array with fields 'srevice' and 'id' containing the service names and corresponding ids.
         */
    public static function get_profile_information( $buffer_api_url, $access_token, $timeout=2 ) {

        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            });

        try
        {
            $buffer_profile_id_request_url = $buffer_api_url . '/profiles.json?access_token=' . urlencode($access_token);
            $response = wp_remote_get($buffer_profile_id_request_url, array('timeout' => $timeout));
            if(is_wp_error($response))
                return $response;

            $profiles = json_decode($response['body']);

            if(!empty($profiles->error))
                return new WP_Error("error", $profiles->error);

            $profile_information = array();
            foreach($profiles as $profile)
                $profile_information[] = array(
                    'id' => $profile->id,
                    'service' => $profile->service,
                                               );

        } catch(Exception $e) {
            return new WP_Error("exception", $e->getMessage());
        } finally {
            restore_error_handler();
        }

        return $profile_information;
    }


}
