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

        $coming_from_page = false;
        $page_to_display = false;
        if(isset($_POST['coming_from_page']) and isset($this->pages[$_POST['coming_from_page']]))
            $coming_from_page = $_POST['coming_from_page'];

        ob_start();
        if(count($this->errors) > 0)
        {
            foreach($this->errors as $error_num => $error)
            {
                echo('<div id="' . esc_attr($error['code']) . '" class="alert ' . ($error['type'] === 'error' ? 'alert-danger' : 'alert-warning') . '">' . esc_html(esc_attr($error['message'])) . '</div>');
            }
            $page_to_display = $coming_from_page;
        }
        else
        {
            if(isset($_POST['navigation']) and $coming_from_page !== false)
            {
                if($_POST['navigation'] === 'Submit')
                {
                    return 'Thank you! Your request has been submitted.';
                }

                reset($this->pages);
                while(key($this->pages) !== $coming_from_page and key($this->pages) !== null)
                    next($this->pages);
                if($_POST['navigation'] === 'Next')
                    next($this->pages);
                elseif($_POST['navigation'] === 'Back')
                    prev($this->pages);
                else
                    throw new Exception('<div>Error, don\'t know which page to display.</div>');
                $page_to_display = key($this->pages);
            }
        }
        if($page_to_display === false)
            $page_to_display = array_key_first($this->pages);

        echo '<form method="post">';
        $previous_page_id = false;
        reset($this->pages);
        while($page_options = current($this->pages))
        {
            $page_id = key($this->pages);
            $next_page_id = next($this->pages);

            echo '<div style="display:' . ($page_id === $page_to_display ? 'initial' : 'none' ) . '">';
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
            if($page_id === $page_to_display)
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
            echo '<input type="submit" name="navigation" value="Next" />';
        else
            echo '<input type="submit" name="navigation" value="Submit" />';
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

        $this->field_values = array();
        foreach($this->fields as $id => $field_options)
        {
            if(isset($_POST[$this->plugin_name . '-' . $this->slug][$id]))
                $this->field_values[$id] = call_user_func($this->fields[$id]['validation_callable'], $id, $_POST[$this->plugin_name . '-' . $this->slug][$id]);
            else
                $this->field_values[$id] = $this->get_field_default($id);
        }
    }
}
