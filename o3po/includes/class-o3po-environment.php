<?php

/**
 * Provide information about and functions to interact with the environment in which this plugin is running.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Provide information about and functions to interact with the environment in which this plugin is running.
 *
 * Provide methods to obtain information about and functions to interact with the environment in which this plugin is running.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Environment {

        /**
         * The url of the production system.
         *
         * @since    0.1.0
         * @access   protected
         * @var      string    $production_site_url    Site URL of the production site.
         */
	protected $production_site_url;

        /**
         * Instantiate the envrionment object.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $production_site_url    Site URL of the production site.
         */
	public function __construct( $production_site_url ) {

		$this->production_site_url = $production_site_url;

        if($this->is_test_environment()) error_reporting(E_ALL);
	}

        /**
         * Returns whether this plugin is running on a test system or the production system.
         *
         * @since    0.1.0
         * @access   public
         * */
    public function is_test_environment() {

        if(empty($this->production_site_url))
            return true;
        else
            return get_site_url() === $this->production_site_url ? false : true;
    }

        /**
         * Modify the css if we are in a test environment to make the website
         * look ugly to avoid confusion.
         *
         * To be added to the 'wp_head' and 'admin_head' action.
         *
         * @since    0.1.0
         * @access   public
         * */
    public function modify_css_if_in_test_environment() {

        if($this->is_test_environment())
            echo '<style>div#wpadminbar {background-color: #49cf44;}</style>';
    }

        /**
         * Returns a filename that is unique in the given directory.
         *
         * This fuction is based on wp_unique_filename() from
         * https://core.trac.wordpress.org/browser/tags/4.7.3/src/wp-includes/functions.php#L0
         * but it treats .tar.gz as a single extension.
         *
         * @since    0.1.0
         * @access   public
         * @param    string     $dir        Directory.
         * @param    string     $filename   Base Filename including the extension.
         * @param    string     $ext        Desired extension, e.g., '.txt', '.pdf', or '.tar.gz'.
         * */
    public function unique_filename_callback( $dir, $filename, $ext ) {

        $ext = mb_strtolower($ext);
        if($ext === '.gz' && mb_substr($filename, -7) === '.tar.gz' ) $ext = '.tar.gz';
        $number = '';
        while ( file_exists( $dir . "/$filename" ) ) {
            $new_number = (int) $number + 1;
            if ( '' === "$number$ext" ) {
                $filename = "$filename-" . $new_number;
            } else {
                $filename = str_replace( array( "-$number$ext", "$number$ext" ), "-" . $new_number . $ext, $filename );
            }
            $number = $new_number;
        }
        return $filename;
    }

        /**
         * Adds additional mime types to the given array.
         *
         * Enables the upload of such file types to the media library.
         *
         * @since    0.1.0
         * @access   public
         * @param    array    $mime_types    Array of mime types.
         * */
    public function custom_upload_mimes( $mime_types=array() ) {

        if(!array_key_exists('pdf', $mime_types)) $mime_types['pdf'] = 'application/pdf';
        if(!array_key_exists('tex', $mime_types)) $mime_types['tex'] = 'text/x-tex';
        if(!array_key_exists('gz', $mime_types)) $mime_types['gz'] = 'application/gzip';
        if(!array_key_exists('tar.gz', $mime_types)) $mime_types['tar.gz'] = 'application/gzip'; #just to be sure we also add this

        return $mime_types;
    }

        /**
         * Works around a bug in WP that disallows upload of files to the media library even if they are marked as allowed via the 'upload_mimes' filter.
         *
         * To be added to the 'wp_check_filetype_and_ext' filter.
         *
         * @since 0.3.0
         * @access public
         * @param array  $data                      File data array containing 'ext', 'type', and
         *                                          'proper_filename' keys.
         * @param string $file                      Full path to the file.
         * @param string $filename                  The name of the file (may differ from $file due to
         *                                          $file being in a tmp directory).
         * @param array  $mimes                     Key is the file extension with value as the mime type.
         * @return array Values for the extension, MIME, and filename.
         */
    public function disable_real_mime_check_for_selected_extensions( $data, $file, $filename, $mimes ) {
        $wp_filetype = wp_check_filetype($filename, $mimes);

        $ext = $wp_filetype['ext'];
        $type = $wp_filetype['type'];
        $proper_filename = $data['proper_filename'];

        # If the filename is gz (or tar.gz, although WP 5.0.1 treats tar.gz as gz)
        # we return the original file data, thereby reversing the change of $ext
        # and $type to false, that is done by WP during wp_check_filetype_and_ext()
        # for file types that WP "doesn't like" even though we explicitly allowed
        # them via custom_upload_mimes().
        if($ext === "gz" or $ext === "tar.gz")
            return compact( 'ext', 'type', 'proper_filename' );
        else
            return $data;
    }


        /**
         * Download a file to the WordPress media library.
         *
         * $mime_type and $extension are optional, if set to something empty()
         * they are guessed form the download file, where the extension is
         * guessed based on the mime type. If the mime type is provided and
         * the downloaded file does not actually have that mime type, an
         * error is returned.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $url               Url to download from.
         * @param    string    $filename          Fielname under which to save the download.
         * @param    string    $extension         Extension of the file under which to save the download.
         * @param    string    $mime_type         Expected mime type of the download.
         * @param    int       $parent_post_id    Id of post to which to link the download.
         * */
    public function download_to_media_library( $url, $filename, $extension, $mime_type, $parent_post_id ) {

        $extension = ltrim($extension, '.');

            // Gives us access to the download_url() and wp_handle_sideload() functions
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $timeout_seconds = 20;

            // Download file to temp dir
        $temp_file = download_url( $url, $timeout_seconds );

        if ( !is_wp_error( $temp_file ) ) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $actual_mime_type = finfo_file($finfo, $temp_file);
            finfo_close($finfo);

            if( empty($mime_type) ) {
                $mime_type = $actual_mime_type;
            }
            elseif($mime_type !== $actual_mime_type)
                return array( 'error' => "Instead of the mime type " . $mime_type . " we were expecting, the remote server provided a file of mime type " . $actual_mime_type );

            if (empty($extension)) {
                if( preg_match('#text/.*tex#u', $mime_type) )
                    $extension = 'tex';
                else if( preg_match('#application/.*(tar|gz|gzip)#u', $mime_type) )
                    $extension = 'tar.gz';
                else if( preg_match('#application/.*pdf#u', $mime_type) )
                    $extension = 'pdf';
                else
                    $extension = 'unknown';
            }
            $filename = $filename . '.' . $extension;

                // Array based on $_FILE as seen in PHP file uploads
            $file = array(
                'name'     => $filename,
                'type'     => $mime_type,
                'tmp_name' => $temp_file,
                'error'    => 0,
                'size'     => filesize($temp_file),
                          );

            $overrides = array(
                    // Tells WordPress to not look for the POST form
                    // fields that would normally be present as
                    // we downloaded the file from a remote server, so there
                    // will be no form fields
                    // Default is true
                'test_form' => false,

                    // Setting this to false lets WordPress allow empty files, not recommended
                    // Default is true
                'test_size' => true,

                    // for .tar.gz files normally only .gz is treated as the extension, so that
                    // when file.tar.gz already exists the file is uploaded as file.tar-1.gz but
                    // we want the propper file-1.tar.gz
                'unique_filename_callback' => array($this, 'unique_filename_callback'),
                               );

                // Move the temporary file into the uploads directory
            $results = wp_handle_sideload( $file, $overrides );

            if ( !empty( $results['error'] ) ) {
                return array( 'error' => "Failed to put file " . $file['name'] . " of mime type " . $file['type'] . " into uploads directory because Wordpress said: " . $results['error'] );
            } else {
                $filepath  = $results['file']; // Full path to the file

                    // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $filepath,
                    'post_mime_type' => $mime_type,
                    'post_title'     => $filename,
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                                    );

                    // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $filepath, $parent_post_id );

                    // Generate the metadata for the attachment, and update the database record.
                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                $results['mime_type'] = $mime_type;
                $results['attach_id'] = $attach_id;
                return $results;
            }
        }
        else
            return array( 'error' => "Failed to download file of mime type " . $mime_type . ": " . $temp_file->get_error_message());
    }

        /**
         * Recursively remove a directory.
         *
         * PHP itself does not offer a reasonable way to recursively remove
         * directories. This is a subtle issue as links and nasty file names can be
         * inlolved. We don't want to be hackble by uploading a malicious manuscript
         * source to the arXiv, don't we? This is the best I could come up with after
         * looking at many different implementations that can be found on the
         * internet.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $path    Path to file/folder to delete.
         * @param    string    $root    Root folder to which to restric the deleting.
         * */
    public static function save_recursive_remove_dir( $path, $root ) {

        $path = rtrim($path, '/').'/';
        $root = rtrim($root, '/').'/';

        if ($root === '/' || $path === '/' || $root !== mb_substr($path, 0, mb_strlen($root)) )
            return false;

        foreach(new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), 	RecursiveIteratorIterator::CHILD_FIRST) as $entry ) {
        	if($entry->isDir() && !$entry->isLink())
                rmdir($entry->getPathname());
            else
                unlink($entry->getPathname());
        }

        rmdir($path);
    }

        /**
         * PHP seems to have problems correctly detecting utf-8 encoding of
         * some .tex files when they are read with the standard
         * file_get_contents() functinon (is this expected?). The following
         * has turned out to work in all cases I have tested.
         *
         * @since    0.1.0
         * @access   public
         * @param    string     $path    Path to the file to get.
         * */
    public function file_get_contents_utf8( $path ) {

        $content = file_get_contents($path);
        $encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true);
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);

        return $content;
    }

        /**
         * Get the path of a feature image of a post
         *
         * Returns the path of the feature image, insired by
         * https://stackoverflow.com/questions/27306882/actual-file-location-of-a-wordpress-featured-image
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id   Id of the post.
         * */
    public function get_feature_image_path( $post_id ) {

        $thumb_id = get_post_thumbnail_id($post_id);
        if(empty($thumb_id))
            return '';
        $image= wp_get_attachment_image_src($thumb_id, 'full');
        if(empty($image))
            return '';
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        $base_url = $upload_dir['baseurl'];
        $imagepath= str_replace($base_url, $base_dir, $image[0]);

        if (file_exists( $imagepath))
            return $imagepath;
        else
            return '';
    }


}
