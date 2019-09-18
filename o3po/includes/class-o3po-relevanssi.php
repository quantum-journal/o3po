<?php

/**
 * Encapsulates the interface with the Relevanssi plugin.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

/**
 * Encapsulates the interface with the Relevanssi plugin.
 *
 * Provides a filter to exclude latex source files (or other attachments
 * specified via their mime types from indexing and search.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Relevanssi {

        /**
         * Filter posts to index by mime type.
         *
         * To be added to the 'relevanssi_do_not_index' filter.
         *
         * @since  0.3.0
         * @access public
         * @param  bool    $block     Whether to block this post.
         * @param  int     $post_id   The ID of the post.
         * @return bool
         */
    public static function exclude_mime_types_by_regexp( $block, $post_id ) {

        $mime = get_post_mime_type( $post_id );
        if(!empty($mime) and !$block) {
            $settings = O3PO_Settings::instance();
            $pattern = $settings->get_plugin_option('relevanssi_mime_types_to_exclude');
            if(!empty($pattern))
                $block = preg_match($pattern, $mime) === 1;
        }

        return $block;
    }


        /**
         * Index an attached pdf if this has not already been done.
         *
         * @since  0.3.0
         * @access public
         * @param  int     $attachment_post_id   The ID of the attachment to index.
         * @param  boolean $send_file            Whether to send the file or just the URL (default: false)
         * @return boolean                       Whether the file has been uploaded and indexed.
         */
    public static function index_pdf_attachment_if_not_already_done( $attachment_post_id, $send_file = null ) {

        if(!function_exists('relevanssi_index_pdf'))
            return false;

        $relevanssi_pdf_content = get_post_meta($attachment_post_id, '_relevanssi_pdf_content');
        if(empty($relevanssi_pdf_content) or $relevanssi_pdf_content === 'NOT EXISTS' )
        {
            try
            {
                $result = relevanssi_index_pdf($attachment_post_id, false, $send_file);
                return is_array($result) and isset($result['success']) and $result['success'] === true;
            }
            catch (Exception $e) {
                return false;
            }
        }
        return false;
    }
}
