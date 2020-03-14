<?php

/**
 * Class for the ready to publish form.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.1+
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/trait-o3po-form.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/trait-o3po-ready2publish-storage.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-environment.php';

/**
 * Class for the ready to publish form.
 *
 * @since      0.3.1+
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
abstract class O3PO_PublicForm {

    use O3PO_Form;
    use O3PO_Ready2PublishStorage;

    private $errors = array();

    private $field_values = array();

    private $title;

    private $coming_from_page = false;
    private $page_to_display = false;
    private $navigation = false;
    #private $upload_results = array();

    public function __construct( $plugin_name, $slug, $title ) {

        $this->plugin_name = $plugin_name;
        $this->slug = $slug;
        $this->title = $title;
        $this->specify_pages_sections_and_fields();

    }


    public static function specify_settings( $settings ) {

    }

        /**
         * Specifies form sections and fields.
         *
         * @since 0.3.1+
         * @access private
         */
    abstract protected function specify_pages_sections_and_fields();


    public function handle_form_data_and_produce_content() {

        if($this->session_id == false)
            return 'Invalid session id or session expired. Access denied.';

        ob_start();
        if(count($this->errors) > 0)
        {
            foreach($this->errors as $error_num => $error)
            {
                echo('<div id="' . esc_attr($error['code']) . '" class="alert ' . ($error['type'] === 'error' ? 'alert-danger' : 'alert-warning') . '">' . esc_html(esc_attr($error['message'])) . '</div>');
            }
        }
        else
        {
            if($this->navigation === 'Submit' and $this->coming_from_page !== false)
                return 'Thank you! Your request has been submitted.';
        }

        echo '<form method="post" enctype="multipart/form-data">';

        echo '<input type="hidden" name="session_id" value="' . $this->session_id . '">';

        $previous_page_id = false;
        reset($this->pages);
        while($page_options = current($this->pages))
        {
            $page_id = key($this->pages);
            $next_page_id = next($this->pages);

            echo '<div style="display:' . ($page_id === $this->page_to_display ? 'initial' : 'none' ) . '">';
            echo '<h2>' . esc_html($page_options['title']) . '</h2>';
            foreach($this->sections as $section_id => $section_options)
            {
                if($section_options['page'] !== $this->plugin_name . '-' . $this->slug . ':' . $page_id)
                    continue;
                echo '<h3>' . esc_html($section_options['title']) . '</h3>';
                if(is_callable($section_options['callback']))
                    call_user_func($section_options['callback']);
                foreach($this->fields as $field_id => $field_options)
                {
                    if($section_options['page'] !== $this->plugin_name . '-' . $this->slug . ':' . $page_id or $field_options['section'] !== $section_id)
                        continue;
                    echo '<h4>' . esc_html($field_options['title']) . '</h4>';
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


    public function do_parse_request( $bool, $wp ) {

        $home_path = parse_url(home_url(), PHP_URL_PATH);
        $path = trim(preg_replace("#^/?{$home_path}/#", '/', esc_url(add_query_arg(array()))), '/' );

        if($path !== $this->slug)
            return $bool;

        $this->read_and_validate_field_values();

        do_action( 'parse_request', $wp );
        $this->setup_query($this->make_post($this->title, $this->handle_form_data_and_produce_content()));
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

        exit();
    }


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

    protected function add_error( $setting, $code, $message, $type='error' ) {
        $this->errors[] = array('setting' => $setting,
                                'code' => $code,
                                'message' => $message,
                                'type' => $type
                                );
    }

        /**
         * Get the value of a field by id.
         *
         * @since    0.3.1+
         * @acceess  prublic
         * @param    int    $id     Id of the field.
         */
    public function get_field_value( $id ) {

        return $this->field_values[$id];
    }

    public function read_and_validate_field_values() {

        if(isset($_POST['coming_from_page']) and isset($this->pages[$_POST['coming_from_page']]))
        {
            $this->coming_from_page = $_POST['coming_from_page'];
            if(!empty($_POST['session_id']))
                $this->session_id = $this->validate_session_id($_POST['session_id']);
            else
                $this->session_id = false;
        }
        else
            $this->session_id = $this->generate_session_id();

        if($this->session_id == false)
            return;

        if(isset($_POST['navigation']) and in_array($_POST['navigation'], ['Next', 'Back', 'Submit', 'Upload' ]))
            $this->navigation = $_POST['navigation'];
        else
            $this->navigation = false;

        $this->field_values = array();

        foreach($this->fields as $id => $field_options)
        {
            if(isset($_POST[$this->plugin_name . '-' . $this->slug][$id]))
            {
                $sanitized_value = $this->sanitize_user_input($_POST[$this->plugin_name . '-' . $this->slug][$id]);

                if($field_options['max_length'] !== false)
                    $sanitized_value = substr($sanitized_value, 0, $field_options['max_length']);

                $this->field_values[$id] = call_user_func($this->fields[$id]['validation_callable'], $id, $sanitized_value);
            }
            else
                $this->field_values[$id] = $this->get_field_default($id);
        }
        if($this->navigation === 'Upload')
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

    public function sanitize_user_input( $input ) {

        return strip_tags($input);
    }


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
         * @return mixed Session id or False if invalid.
         */
    public function validate_session_id( $id ) {

        $session_ids = $this->get_session_ids();
        if(in_array($id, $session_ids))
            return $id;
        else
            return false;
    }


    private function get_session_ids( $dicard_older_than=24*60*60 ) {

        $class_options = $this->get_class_option();

        if($dicard_older_than > 0 and !empty($class_options['session_data']))
        {
            foreach($class_options['session_data'] as $session_id => $data)
            {
                if(abs(time() - $data['time']) > $dicard_older_than)
                    unset($class_options['session_data'][$session_id]);
            }
            $this->update_class_option($class_options);
        }

        if(isset($class_options['session_data']))
            return array_keys($class_options['session_data']);
        else
            return array();
    }

    private function get_class_option() {
        return get_option($this->plugin_name . '-' . $this->slug, array());
    }

    private function update_class_option( $class_options ) {
        update_option($this->plugin_name . '-' . $this->slug, $class_options);
    }


    private function put_session_data( $field, $value)
    {

        $class_options = $this->get_class_option();

        if(!$this->session_id or !isset($class_options['session_data'][$this->session_id]['data']) or !is_array($class_options['session_data'][$this->session_id]['data']))
            return false;

        $class_options['session_data'][$this->session_id]['data'][$field] = $value;

        $this->update_class_option($class_options);

    }

    protected function get_session_data( $field ) {

     $class_options = $this->get_class_option();
     if(isset($class_options['session_data'][$this->session_id]['data'][$field]))
         return $class_options['session_data'][$this->session_id]['data'][$field];
     else
         return Null;
    }


        /**
         *
         *
         * @param    string   $label Label of the field. May contain html and is not escaped!
         */
    public function render_image_upload_field( $id, $label='', $esc_label=true ) {
        $file_upload_result = $this->get_session_data('file_upload_result_' . $id);
        #$_file = $this->get_session_data('_FILES_' . $id);
        #echo('file_upload_result_' . $id . '=' . json_encode($file_upload_result));
        #echo('_FILES_' . $id . '=' . json_encode($_file));

        /* if(!empty($file_upload_result['error'])) */
        /*     echo '<p>An error occurred during the upload: ' . $file_upload_result['error'] . '</p>'; */

        if(!empty($file_upload_result['file']))
        {
            echo('<img style="display:block;max-width:100%;max-height:10em;width: auto;height: auto;" src="' . esc_attr($file_upload_result['url']) . '" >');
            echo('<p>Image file ' . esc_html($file_upload_result['user_name']) . ' was uploaded and saved successfully. Want to upload a different file?</p>');
        }

        echo('<input type="hidden" name="MAX_FILE_SIZE" value="30720" />');
        # $_FILES looks funny if an array is used as name of the upload
        echo('<input type="file" id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '">');
        if(!empty($label))
            echo '<label for="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '">' . ($esc_label ? esc_html($label) : $label) . '</label>';

        echo '<input type="submit" name="navigation" value="Upload" />';
    }

}
