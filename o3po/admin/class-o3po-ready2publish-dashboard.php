<?php

/**
 * Class for displaying manuscripts ready to publish on the admin panel.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.1+
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/trait-o3po-ready2publish-storage.php';

/**
 * Class for displaying manuscripts ready to publish on the admin panel.
 *
 * @since      0.3.1+
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Ready2PublishDashboard {

    use O3PO_Ready2PublishStorage;

        /**
         *
         */
    protected $slug;

    protected $plugin_name;

    protected $plugin_pretty_name;

    protected $title;

    private $meta_fields = ['eprint',
                            'title',
                            'corresponding_author_email',
                            'abstract',
                            'author_given_names',
                            'author_surnames',
                            'author_name_styles',
                            'popular_summary',
                            'featured_image',
                            'featured_image_caption',
                            'multimedia_comment',
                            'fermats_library'];

    public function __construct( $plugin_name, $plugin_pretty_name, $slug, $title ) {

        $this->plugin_name = $plugin_name;
        $this->slug = $slug;
        $this->title = $title;
        $this->plugin_pretty_name = $plugin_pretty_name;
    }

    public function setup() {

        wp_add_dashboard_widget($this->slug, esc_html($this->plugin_pretty_name . " " . $this->title), array($this, 'render'));
    }

    public function render() {
        echo '<h3>Manuscripts submitted for publication</h3>';
        echo '<ul>';
        foreach($this->get_manuscripts('unprocessed') as $id => $manuscript_info)
        {
            echo '<li><div class="manuscript-ready2publish">';
            echo '<span>' . esc_html($manuscript_info['eprint']) . '</span>: ';
            echo '<span>' . esc_html($manuscript_info['title']) . '</span>';
            echo '<div style="margin-left:">';
            echo '<span><a href="mailto:' . esc_attr($manuscript_info['corresponding_author_email']) . '">Email ' . esc_html($manuscript_info['corresponding_author_email']) . '</a></span>';
            echo ' | ';
            echo '<span class=""><a href="/' . $this->slug . '?action=' . 'publish' . '&id=' . urlencode($id) . '">Start publishing</a></span>';
            /* echo ' | '; */
            /* echo '<span class=""><a href="/' . $this->slug . '?action=' . 'ignore' . '&id=' . urlencode($id) . '">Ignore</a></span>'; */
            echo '</div>';
            echo '</div></li>';
        }
        echo '</ul>';
        echo '<h3>Partially published manuscripts</h3>';
        echo '<ul>';
        foreach($this->get_manuscripts('partial') as $id => $manuscript_info)
        {
            echo '<li><div class="manuscript-ready2publish">';
            echo '<span>' . esc_html($manuscript_info['eprint']) . '</span>: ';
            echo '<span>' . esc_html($manuscript_info['title']) . '</span>';
            echo '<div style="margin-left:">';
            echo '<span><a href="mailto:' . esc_attr($manuscript_info['corresponding_author_email']) . '">Email ' . esc_html($manuscript_info['corresponding_author_email']) . '</a></span>';
            echo ' | ';
            echo '<span class=""><a href="/' . $this->slug . '?action=' . 'continue' . '&id=' . urlencode($id) . '">Continue</a></span>';
            /* echo ' | '; */
            /* echo '<span class=""><a href="/' . $this->slug . '?action=' . 'ignore' . '&id=' . urlencode($id) . '">Ignore</a></span>'; */
            echo '</div>';
            echo '</div></li>';
        }
        echo '</ul>';
        echo '<h3>Ignored submissions</h3>';
        echo '<span>Coming soon...</span>';
    }

    public function insert_post( $id ) {
        $manuscript_info = $this->get_manuscript($id);

        $post_type = $manuscript_info['post_type'];
        $postarr = [
            'post_type' => $post_type,
                    ];
        $post_id = wp_insert_post($postarr, true);
        if(is_wp_error($post_id))
            return $post_id;
        else
        {
            foreach( $this->meta_fields as $field_id)
                update_post_meta($post_id, $post_type . '_' . $field_id, $manuscript_info[$field_id]);
            update_post_meta($post_id, $post_type . '_number_authors', count($manuscript_info['author_name_styles']));

        }

        return $post_id;
    }

    public function insert_and_display_post( $id ) {
        $post_id = $this->insert_post($id);
        $this->display_post( $post_id );
    }

    public function display_post( $post_id ) {
        if(is_wp_error($post_id))
            echo "ERROR: " . $post_id->get_error_message();
        else
            header('Location: /wp-admin/post.php?post=' . $post_id . '&action=edit');
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

    public function do_parse_request( $bool, $wp, $extra_query_vars ) {

        $home_path = parse_url(home_url(), PHP_URL_PATH);
        $path = trim(preg_replace("#\?.*#", '', preg_replace("#^/?{$home_path}/#", '/', esc_url(add_query_arg(array())))), '/' );

        if($path !== $this->slug)
            return $bool;

        $action = isset($_GET["action"]) ? $_GET["action"] : null;
        $id = isset($_GET["id"]) ? $_GET["id"] : null;

        switch($action)
        {
            case 'publish':
                if($id !== null)
                    $this->insert_and_display_post($id);
                break;
            case 'continue':
                if($id !== null)
                    $post_id = $this->post_id_for_eprint($id);
                    $this->display_post($post_id);
                break;
            default:
                echo "unsupported action " . $action;
        }
        exit();
    }


}
