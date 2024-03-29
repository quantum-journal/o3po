<?php

/**
 * Class for the ready to publish form.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.4.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/trait-o3po-form.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-environment.php';

/**
 * Class for the ready to publish form.
 *
 * @since      0.4.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
abstract class O3PO_PublicForm {

    use O3PO_Form;

        /**
         * Errors identified in the form
         *
         * @since    0.4.0
         * @access   private
         * @var      array   $errors Array of errors.
         */
    private $errors = array();

        /**
         * Field values of the form.
         *
         * @since    0.4.0
         * @access   private
         * @var      array   $field_values Array of field values.
         */
    private $field_values = array();

        /**
         * Title
         *
         * @since    0.4.0
         * @access   private
         * @var      string   $title Title of the form.
         */
    private $title;

        /**
         * The page id of the page the user is coming from
         *
         * @since    0.4.0
         * @access   private
         * @var      int     $coming_from_page Page id.
         */
    protected $coming_from_page = false;

        /**
         * Page to display
         *
         * @since    0.4.0
         * @access   private
         * @var      int     $page_to_display Id of page to display.
         */
    private $page_to_display = false;

        /**
         * Value of the navigation
         *
         * @since    0.4.0
         * @access   private
         * @var      string   $navigation One of 'Next', 'Back', 'Submit', or 'Upload'
         */
    private $navigation = false;

        /**
         * Whether the form was submitted successfully
         *
         * @since    0.4.0
         * @access   private
         * @var      boolean   $submitted_successfully Whether the form was submitted successfully.
         */
    private $submitted_successfully = false;

        /**
         * Read all form values from $_POST and process.
         *
         * @since  0.4.0
         * @access public
         * @param  string $plugin_name The name of the plugin.
         * @param  string $slug        The slug of the form.
         * @param  string $title       The title of the form.
         */
    public function __construct( $plugin_name, $slug, $title ) {

        $this->plugin_name = $plugin_name;
        $this->slug = $slug;
        $this->title = $title;
        $this->specify_pages_sections_and_fields();

    }

        /**
         * Specifies form sections and fields.
         *
         * @since  0.4.0
         * @access private
         */
    abstract protected function specify_pages_sections_and_fields();

        /**
         * Read all form values from $_POST and process.
         *
         * @since  0.4.0
         * @access public
         */
    public function read_and_validate_field_values() {

        if(isset($_POST['coming_from_page']) and isset($this->pages[$_POST['coming_from_page']]))
        {
            $this->coming_from_page = wp_unslash($_POST['coming_from_page']);
            if(!empty($_POST['session_id']))
            {
                $this->session_id = $this->validate_session_id(wp_unslash($_POST['session_id']));
                if($this->session_id !== false)
                    $this->renew_session_id($this->session_id);
            }
            else
                $this->session_id = false;
        }
        else
            $this->session_id = $this->generate_session_id();

        if($this->session_id == false)
            return;

        if(isset($_POST['navigation']) and in_array(wp_unslash($_POST['navigation']), ['Next', 'Back', 'Submit', 'Upload']))
            $this->navigation = wp_unslash($_POST['navigation']);
        else
            $this->navigation = false;

        $this->field_values = array();

        foreach($this->fields as $id => $field_options)
        {
            if(isset($_POST[$this->plugin_name . '-' . $this->slug][$id]))
            {
                $sanitized_value = $this->sanitize_user_input(wp_unslash($_POST[$this->plugin_name . '-' . $this->slug][$id]));
            }
            else
                $sanitized_value = $this->get_field_default($id);

            if($field_options['max_length'] !== false)
                $sanitized_value = substr($sanitized_value, 0, $field_options['max_length']);
            $this->field_values[$id] = call_user_func($this->fields[$id]['validation_callable'], $id, $sanitized_value);
        }
        if($this->navigation === 'Upload' and count($this->errors) === 0)
        {
            # Note: All files not handled will be automatically deleted by PHP
            foreach($this->fields as $id => $field_options)
            {
                if(isset($_FILES[$this->plugin_name . '-' . $this->slug . '-' . $id]))
                {
                    $file_of_this_id = $_FILES[$this->plugin_name . '-' . $this->slug . '-' . $id];

                    switch($file_of_this_id['error']) {
                        case UPLOAD_ERR_OK:
                            $this->put_session_data('_FILES_' . $id, $file_of_this_id);
                            $result = call_user_func($this->fields[$id]['validation_callable'], $id, $file_of_this_id);
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $upload_max_filesize = O3PO_Environment::max_file_upload_bytes();
                            $result = array('error' => "The file was larger than the maximum file size of " . $upload_max_filesize . " Bytes.");
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $result = array('error' => "The file was only partially uploaded");
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $result = array('error' => "No file was uploaded");
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $result = array('error' => "The server is missing a temporary upload folder");
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $result = array('error' => "The server failed to write the file to disk");
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $result = array('error' => "The upload was stopped by a PHP extension");
                            break;
                        default:
                            $result = array('error' => "An unknown upload error occurred");
                            break;
                    }
                    if(!empty($result['error']))
                        $this->add_error($id, 'upload-error', $result['error'], 'error');

                    $this->put_session_data('file_upload_result_' . $id, $result);
                }
            }
        }
        elseif($this->navigation === 'Submit' and count($this->errors) === 0)
        {
            $attach_ids = $this->add_sideloaded_files_to_media_library();
            $this->submitted_successfully = $this->on_submit($attach_ids);
        }

        if(count($this->errors) > 0)
            $this->page_to_display = $this->coming_from_page;
        else
        {
            if(isset($this->navigation) and $this->coming_from_page !== false)
            {
                reset($this->pages);
                while(key($this->pages) !== $this->coming_from_page and key($this->pages) !== null)
                    next($this->pages);
                if($this->navigation === 'Next')
                    next($this->pages);
                elseif($this->navigation === 'Back')
                    prev($this->pages);
                $this->page_to_display = key($this->pages);
            }
        }
        if($this->page_to_display === false)
            $this->page_to_display = array_key_first($this->pages);
    }


        /**
         * Handle the form data and render the form
         *
         * @since  0.4.0
         * @access public
         */
    public function handle_form_data_and_produce_content() {

        if($this->session_id == false)
            return 'Invalid session id or session expired. Access denied. We are sorry, but you will have to <a href="/' . $this->slug . '">start from scratch</a>.';


        if(count($this->errors) === 0)
        {
            if($this->navigation === 'Submit' and $this->coming_from_page !== false)
            {
                $ret = '<script>window.history.replaceState(null, "", "/' . $this->slug . '")</script>';
                $ret .= $this->submitted_message($this->submitted_successfully);
                return $ret;
            }
        }

        ob_start();
        if($this->navigation !== false and count($this->errors) > 0)
        {
            foreach($this->errors as $error_num => $error)
            {
                echo('<div class="' . esc_attr($error['code']) . ' alert ' . ($error['type'] === 'error' ? 'alert-danger' : 'alert-warning') . '"><a href="#' . esc_html($error['setting']) . '">' . esc_html($error['message']) . '</a></div>' . "\n");
            }
        }
        echo '<form method="post" enctype="multipart/form-data" action="' . home_url($this->slug) . '">';

        echo '<input type="hidden" name="session_id" value="' . $this->session_id . '">';

        $num = 0;
        $previous_page_id = false;
        reset($this->pages);
        while($page_options = current($this->pages))
        {
            $num += 1;
            $page_id = key($this->pages);
            $next_page_id = next($this->pages);

            echo '<div style="display:' . ($page_id === $this->page_to_display ? 'initial' : 'none' ) . '">';
            echo '<h2>' . esc_html($page_options['title']) . ' (step ' . $num . ' of ' . count($this->pages) . ')</h2>';
            foreach($this->sections as $section_id => $section_options)
            {
                if($section_options['page'] !== $this->plugin_name . '-' . $this->slug . ':' . $page_id)
                    continue;
                echo '<h3 id="' . esc_attr($section_id) . '">' . esc_html($section_options['title']) . '</h3>';
                if(is_callable($section_options['callback']))
                    call_user_func($section_options['callback']);
                foreach($this->fields as $field_id => $field_options)
                {
                    if($section_options['page'] !== $this->plugin_name . '-' . $this->slug . ':' . $page_id or $field_options['section'] !== $section_id)
                        continue;
                    if(!empty($field_options['title']))
                        echo '<h4 id=' . esc_attr($field_id) . '>' . esc_html($field_options['title']) . '</h4>';
                    if(is_callable($field_options['callback']))
                        call_user_func($field_options['callback']);
                }
            }
            echo '</div>';
            if($page_id === $this->page_to_display)
                $this->render_navigation($previous_page_id, $page_id, $next_page_id);
            $previous_page_id = $page_id;
        }
        echo '</form>';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }


        /**
         * The message to show after submission
         *
         * @since  0.4.0
         * @access public
         * @return string Message to display.
         * @param  boolean $submitted_successfully Whether the form was submitted successfully.
         */
    public function submitted_message( $submitted_successfully ) {

        return 'Thank you! Your request has been submitted.';
    }


        /**
         * Render the form navigation
         *
         * @since  0.4.0
         * @access public
         * @param  int $previous_page_id Id of the previous page
         * @param  int $page_id          Id of this page
         * @param  int $next_page_id     Id of the next page
         */
    public function render_navigation( $previous_page_id, $page_id, $next_page_id ) {
        echo('<div display="none">');
        echo('<input type="hidden" name="coming_from_page" value="' . $page_id . '">');
        echo('</div>');
        echo('<div>');
        if($previous_page_id)
            echo '<input type="submit" name="navigation" value="Back" />';
        if($next_page_id)
            echo '<input type="submit" name="navigation" style="float:right;" value="Next" />';
        else
            echo '<input type="submit" name="navigation" style="float:right;" value="Submit" />';
        echo '</div>';
    }


       /**
         * Adds a rewrite endpoint for the form.
         *
         * To be added to the 'init' action.
         *
         * @since    0.1.0
         * @access   public
         * */
    public function init() {

        add_rewrite_endpoint($this->slug, EP_ROOT);
            //flush_rewrite_rules( true );  //// <---------- ONLY COMMENT IN WHILE TESTING
    }

        /**
         * Parse form requests
         *
         * Processes form input and displays the form.
         *
         * @since   0.4.0
         * @access  public
         * @param   bool   $bool        Whether or not to parse the request.
         * @param   WP     $wp          Current WordPress environment instance.
         * @param   bool   $do_not_exit Whether to exit after parsing the request. Can be set to false for testing only.
         * */
    public function do_parse_request( $bool, $wp, $do_not_exit=False ) {

        $home_path = parse_url(home_url(), PHP_URL_PATH);
        $path = trim(preg_replace("#^/?{$home_path}/#", '/', add_query_arg(array())), '/' );

        if($path !== $this->slug)
            return $bool;

        $this->read_and_validate_field_values();

        do_action( 'parse_request', $wp );
        $this->setup_query($this->make_post($this->title, $this->handle_form_data_and_produce_content()));
        remove_filter( 'the_content', 'wpautop' ); // wpautop converts newline to <br /> in the post content and we don't want to that that to happen for thr virtual post we are displaying here!
        do_action( 'wp', $wp );

        do_action( 'template_redirect' );
        $template = locate_template('page.php');
        $filtered = apply_filters( 'template_include',
            apply_filters( 'virtual_page_template', $template )
        );
        if ( empty( $filtered ) || file_exists( $filtered ) ) {
            $template = $filtered;
        }
        if ( ! empty( $template ) && file_exists( $template ) ) {
            require_once $template;
        }

        if(!$do_not_exit)
            exit(); // @codeCoverageIgnore
    }


        /**
         * Make a fake post to show the form
         *
         * @since  0.4.0
         * @access public
         * @param  string $title   Title of the post
         * @param  string $content Content of the post
         */
    private function make_post( $title, $content ) {
        $post = new WP_Post((object) array(
            'ID'             => 0,
            'post_title'     => $title,
            'post_name'      => sanitize_title($title),
            'post_content'   => $content,
            'post_excerpt'   => '',
            'post_parent'    => 0,
            'menu_order'     => 0,
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'comment_count'  => 0,
            'post_password'  => '',
            'to_ping'        => '',
            'pinged'         => '',
            'guid'           => home_url($this->slug),
            'post_date'      => current_time( 'mysql' ),
            'post_date_gmt'  => current_time( 'mysql', 1 ),
            'post_author'    => is_user_logged_in() ? get_current_user_id() : 0,
            'is_virtual'     => TRUE,
            'filter'         => 'raw'
                                  ));
        return $post;
    }


        /**
         * Setup the query to show a post
         *
         * @since  0.4.0
         * @access public
         * @param  WP_Post $wp_post Post to show.
         */
    private function setup_query( $wp_post ) {

        global $wp_query;

        $wp_query->init();
        $wp_query->is_page       = TRUE;
        $wp_query->is_singular   = TRUE;
        $wp_query->is_home       = FALSE;
        $wp_query->found_posts   = 1;
        $wp_query->post_count    = 1;
        $wp_query->max_num_pages = 1;
        $posts = (array) apply_filters(
            'the_posts', array( $wp_post ), $wp_query
        );
        $post = $posts[0];
        $wp_query->posts          = $posts;
        $wp_query->post           = $post;
        $wp_query->queried_object = $post;
        $GLOBALS['post']          = $post;
    }

        /**
         * Add an error or warning to the list of errors
         *
         * @since  0.4.0
         * @access public
         * @param  string   $setting Setting that produced the error.
         * @param  string   $code    Error code.
         * @param  string   $message Error message
         * @param  string   $type    Type of error, one of 'error' or 'warning'.
         */
    protected function add_error( $setting, $code, $message, $type='error' ) {
        $this->errors[] = array('setting' => $setting,
                                'id' => $setting,
                                'code' => $code,
                                'message' => $message,
                                'type' => $type
                                );
    }

        /**
         * Return page to display
         *
         * @since  0.4.0
         * @access public
         * @return string Page id to be displayed
         */
    public function get_page_to_display() {

        return $this->page_to_display;
    }

        /**
         * Return all errors
         *
         * @since  0.4.0
         * @access public
         * @return array All errors recorded so far.
         */
    public function get_errors() {

        return $this->errors;
    }

        /**
         * Get the value of a field by id.
         *
         * @since   0.4.0
         * @acceess prublic
         * @param   int     $id Id of the field.
         */
    public function get_field_value( $id ) {

        if(preg_match('#(.*)\[(.*)\]#u', $id, $matches) === 1)
        {
            $array = $matches[1];
            $key = $matches[2];
            return $this->field_values[$array][$key];
        }
        else
            return $this->field_values[$id];
    }


        /**
         * Sanitize user input
         *
         * @since  0.4.0
         * @access public
         * @param  string $input The input to sanitize.
         * @return string Sanitized input
         */
    public function sanitize_user_input( $input ) {

        if(is_array($input))
        {
            $result = array();
            foreach($input as $key => $elem)
                $result[$key] = $this->sanitize_user_input($elem);
            return $result;
        }
        else
        {
            $string = $input;
            $string = iconv("UTF-8","UTF-8//IGNORE", $string);
            $string = preg_replace('/[^[:print:]\r\n]/u', '', $string);
            return $string;
        }
    }

        /**
         * Generate a unique session id
         *
         * @since  0.4.0
         * @access public
         * @return string Random hex string
         */
    public function generate_session_id() {

        $id = bin2hex(random_bytes(32));

        $class_options = $this->get_class_option();
        if(empty($class_options['session_data']))
            $class_options['session_data'] = array();

        $class_options['session_data'][$id] = array(
            'time' => time(),
            'data' => array(),
                                                   );
        $this->update_class_option($class_options);

        return $id;
    }


        /**
         * Check whether an id is a valid session id
         *
         * @since  0.4.0
         * @param  int   $id Id to validate
         * @return mixed Session id or False if invalid.
         */
    public function validate_session_id( $id ) {

        $session_ids = $this->get_session_ids();
        if(in_array($id, $session_ids))
            return $id;
        else
            return false;
    }

        /**
         * Discard a session and all its data
         *
         * @since  0.4.0
         * @param  int   $session_id Id to discard
         */
    private function discard_session( $session_id ) {

        $class_options = $this->get_class_option();
        $this->delete_sideloaded_files($session_id);
        unset($class_options['session_data'][$session_id]);
        $this->update_class_option($class_options);
    }

        /**
         * Renew a session and all its data
         *
         * @since  0.4.0
         * @param  int   $session_id Id to renew
         */
    private function renew_session_id( $session_id ) {

        $class_options = $this->get_class_option();
        $class_options['session_data'][$session_id]['time'] = time();
        $this->update_class_option($class_options);
    }

        /**
         * Get all recent session ids
         *
         * @since  0.4.0
         * @param  int   $dicard_older_than Discard all session ids older than this many seconds
         * @return array Recent session ids
         */
    private function get_session_ids( $dicard_older_than=24*60*60 ) {

        $class_options = $this->get_class_option();

        if($dicard_older_than > 0 and !empty($class_options['session_data']))
        {
            foreach($class_options['session_data'] as $session_id => $data)
            {
                if(abs(time() - $data['time']) > $dicard_older_than)
                    $this->discard_session($session_id);
            }
            $class_options = $this->get_class_option();#reload after discarding
        }

        if(isset($class_options['session_data']))
            return array_keys($class_options['session_data']);
        else
            return array();
    }

        /**
         * Get the option element used by this class
         *
         * @since  0.4.0
         * @return array Class option
         */
    private function get_class_option() {
        return get_option($this->plugin_name . '-' . $this->slug, array());
    }

        /**
         * Set the option element used by this class
         *
         * @since  0.4.0
         * @param  array Class option
         */
    private function update_class_option( $class_options ) {
        update_option($this->plugin_name . '-' . $this->slug, $class_options);
    }

        /**
         * Set field in the data of a session
         *
         * @since  0.4.0
         * @param  string $field      The field to set.
         * @param  mixed  $value      The value to set.
         * @param  string $session_id The session id for which to set the field.
         */
    protected function put_session_data( $field, $value, $session_id=Null )
    {
        if($session_id === Null)
            $session_id = $this->session_id;

        $class_options = $this->get_class_option();

        if(!$this->session_id or !isset($class_options['session_data'][$session_id]['data']) or !is_array($class_options['session_data'][$session_id]['data']))
            return false;

        $class_options['session_data'][$session_id]['data'][$field] = $value;

        $this->update_class_option($class_options);

    }

        /**
         * Get field from the data of a session
         *
         * @since  0.4.0
         * @param  string $field      The field to get.
         * @param  mixed  $default    The default value in case the field is not set.
         * @param   string $session_id The session id for which to get the field.
         */
    protected function get_session_data( $field, $default=Null, $session_id=Null ) {

        if($session_id === Null)
            $session_id = $this->session_id;

            $class_options = $this->get_class_option();
        if(isset($class_options['session_data'][$session_id]['data'][$field]))
            return $class_options['session_data'][$session_id]['data'][$field];
        else
            return $default;
    }


        /**
         * Append a field value pair to the current session data
         *
         * @since  0.4.0
         * @param  string $field      The field to set.
         * @param  mixed  $value      The value to set.
         */
    protected function append_session_data( $field, $value ) {

        $session_data = $this->get_session_data($field, array());
        $session_data[] = $value;
        $this->put_session_data($field, $session_data);

    }

        /**
         * Render an image upload field
         *
         * @since  0.0.4
         * @access public
         * @param  string $id        Id of the field
         * @param  string $label     Label of the field. May contain html and is not escaped!
         * @param  bool   $esc_label Whether to escape the label
         */
    public function render_image_upload_field( $id, $label='', $esc_label=true ) {
        $file_upload_result = $this->get_session_data('file_upload_result_' . $id);

        if(!empty($file_upload_result['file']))
        {
            echo('<p>Image file ' . esc_html($file_upload_result['user_name']) . ' was uploaded and saved successfully:</p>');
            echo('<img style="display:block;max-width:100%;max-height:10em;width: auto;height: auto;" src="' . esc_attr($file_upload_result['url']) . '" >');
            echo('<p>Changed your mind? You can choose to upload a different file below.</p>');
        }

        echo('<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />');
        # $_FILES looks funny if an array is used as name of the upload
        echo('<input type="file" id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '">');
        if(!empty($label))
            echo '<label for="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '">' . ($esc_label ? esc_html($label) : $label) . '</label>';

        echo '<p><input type="submit" name="navigation" value="Upload" /></p>';
    }


        /**
         * Add the files uploaded by the user to the media library.
         *
         * @since  0.4.0
         * @access protected
         */
    protected function add_sideloaded_files_to_media_library() {

        include_once( ABSPATH . 'wp-admin/includes/image.php' );

        $sideloaded_files = $this->get_session_data('sideloaded_files', array());
        $attach_ids = array();
        foreach($sideloaded_files as $key => $sideloaded_file)
        {
            $parent_post_id = 0;
            $filename = basename($sideloaded_file['file']);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $actual_mime_type = finfo_file($finfo, $sideloaded_file['file']);

            $attachment = array(
                'guid'           => $filename,
                'post_mime_type' => $actual_mime_type,
                'post_title'     => $filename,
                'post_content'   => '',
                'post_status'    => 'inherit'
                                );
            $attach_id = wp_insert_attachment($attachment, $sideloaded_file['file'], $parent_post_id);
            if($attach_id === 0)
                $attach_id = new WP_Error("sideload-error", "wp_insert_attachment() returned 0");
            if(!is_wp_error($attach_id))
            {
                $attach_data = wp_generate_attachment_metadata($attach_id, $sideloaded_file['file']);
                $update_attachment_result = wp_update_attachment_metadata($attach_id, $attach_data);
                /* if($update_attachment_result === False) */
                /*     $attach_id = new WP_Error("sideload-error", "Unable to update attachment meta-data of attachment " . $attach_id . "."); */
            }
            $attach_ids[$sideloaded_file['id']] = $attach_id;
            unset($sideloaded_files[$key]);
        }
        $this->put_session_data('sideloaded_files', $sideloaded_files);

        return $attach_ids;
    }


        /**
         * Delete the sideloaded files of a session
         *
         * @since  0.4.0
         * @access private
         * @param  string  $session_id Session id.
         */
    private function delete_sideloaded_files( $session_id )
    {
        $sideloaded_files = $this->get_session_data('sideloaded_files', array(), $session_id);
        foreach($sideloaded_files as $sideloaded_file)
        {
            if(file_exists($sideloaded_file))
                unlink($sideloaded_file);
        }

    }

        /**
         * Validate that an array of at most 1000 name styles
         *
         * @since  0.4.0
         * @access private
         * @param  string  $id    The id of the field whose input is validated.
         * @param  array   $input The input.
         */
    public function validate_array_of_at_most_1000_name_styles( $id, $input ) {

        if(!is_array($input))
        {
            $this->add_error( $id, 'not-array', "The input to field " . $id . " must be an array but was of type " . gettype($input) . ".", 'error');
            return array();
        }

        $input = array_slice($input, 0, 1000);
        $result = array();
        foreach($input as $key => $name)
        {
            if($name !== 'western' and $name !== 'eastern')
                $name = 'western';
            $result[$key] = $name;
        }

        return $result;
    }


        /**
         * Render the form summary
         *
         * @since  0.4.0
         * @access public
         */
    public function render_summary() {
        echo "<p>Please verify that the following information is correct before submitting.</p>";
        foreach($this->sections as $section_id => $section_options)
        {
            echo '<h3 id="' . esc_attr($section_id) . '_summary">' . esc_html($section_options['title']) . '</h3>';
            if($section_options['summary_callback'] !== null)
            {
                echo call_user_func($section_options['summary_callback']);
            }
            else
            {
                foreach($this->fields as $id => $field_options) {
                    if($field_options['section'] !== $section_id)
                        continue;
                    echo '<h4>' . esc_html($field_options['title']) . '</h4>';
                    $value = $this->field_values[$id];
                    if(is_array($value))
                    {
                        foreach($value as $val)
                            echo '<p style="white-space:pre-wrap;">' . (!empty($val) ? esc_html($val) : 'Not provided') . '</p>';
                    }
                    else
                    {
                        $result = $this->get_session_data('file_upload_result_' . $id);
                        if(empty($result['error']) and !empty($result['user_name']))
                            echo '<p style="white-space:pre-wrap;">' . esc_html($result['user_name']) . '</p>';
                        else
                            echo '<p style="white-space:pre-wrap;">' . (!empty($value) ? esc_html($value) : 'Not provided') . '</p>';
                    }
                }
            }
        }
    }


        /**
         * Ensure that a check box field is checked
         *
         * @sinde  0.4.0
         * @access public
         * @param  string $id    The field this was input to.
         * @param  string $input User input.
         */
    public function checked_if_on_or_past_containing_page_unless_back_or_upload( $id, $input ) {

        if($input === "checked")
            return $input;

        $page = $this->fields[$id]['page'];
        $page_id_of_field = mb_substr($page, mb_strlen($this->plugin_name . '-' . $this->slug . ':'));
        foreach($this->pages as $page_id => $page_info)
        {
            if($page_id === $page_id_of_field)
            {
                if(!in_array($this->navigation, ['Back', 'Upload']))
                    $this->add_error( $id, 'not-checked', "The box '" . $this->fields[$id]['title'] . "' must be checked.", 'error');
                break;
            }
            elseif($page_id === $this->coming_from_page)
            {
                if($input !== "unchecked")
                    $this->add_error( $id, 'neither-checked-nor-unchecked', "The box '" . $this->fields[$id]['title'] . "' must be checked or unchecked.", 'error');
                break;
            }
        }

        return $this->get_field_default($id);
    }


        /**
         * Called on form submission
         *
         * Must be implemented by sub classes.
         *
         * @since  0.4.0
         * @access private
         * @param  array   $attach_ids The ids of attachments created during form submission.
         */
    abstract public function on_submit( $attach_ids );

}
