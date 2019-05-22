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
         * @parm   string $buffer_url        Url of the Buffer.com api.
         * @param  string $access_token      Access token.
         * @parm   array|string $profile_ids An array of Buffer profile id’s of an individual id that the status update should be sent to.
         * @parm   string $text       The status update text.
         * @parm   boolean $shorten   If shorten is false links within the text will not be automatically shortened, otherwise they will.
         * @parm   boolean $now       If now is set, this update will be sent immediately to all profiles instead of being added to the buffer.
         * @parm   boolean $top       If top is set, this update will be added to the top of the buffer and will become the next update sent.
         * @parm   array $media       An associative array of media to be attached to the update containing some of the following parameters: link, description, title, picture, photo, thumbnail.
         * @return boolean|WP_Error   Returns true on success or a WP_Error in case an error occurred.
         */
    public static function create_update( $buffer_url, $access_token, $profile_ids, $text='', $media=array(), $attachment=true, $shorten=false, $now=false, $top=false ) {

        try
        {

            if(!is_array($profile_ids))
                $profile_ids = array($profile_ids);

            $headers = array( 'content-type' => 'application/x-www-form-urlencoded');
            $buffer_api_url = $buffer_url . '/updates/create.json';
            $buffer_api_url_with_token = $buffer_api_url . '?access_token=' . $access_token;

            $request = $buffer_api_url_with_token;
            foreach($profile_ids as $profile_id)
                $request .= '&profile_ids[]=' . $profile_id;
            $request .= '&text=' . urlencode($text);
            $media_parts = array(
                'link',
                'description',
                'title',
                'picture',
                'photo',
                'thumbnail',
                                 );
            foreach($media_parts as $media_part)
                if(isset($media[$media_part]))
                    $request .= '&media[' . $media_part . ']=' . urlencode($media[$media_part]);
            $request .= '&attachment=' . ( $attachment ? 'true' : 'false' );
            $request .= '&shorten=' . ( $shorten ? 'true' : 'false' );
            $request .= '&now=' . ( $now ? 'true' : 'false' );
            $request .= '&top=' . ( $top ? 'true' : 'false' );

            $response = wp_remote_post( $request, array(
                                            'headers' => $headers,
                                            'method'    => 'POST'
                                                        ));
            if(is_wp_error($response))
                return $response;
            elseif(!isset($response['body']))
                return new WP_Error("buffer_error", 'The response from buffer.com could not be interpreted.');

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

}
