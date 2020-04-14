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
        echo "<h3>Manuscripts ready for publication</h3>";
        echo '<ul>';
        foreach($this->get_all_unpublished_manuscripts() as $id => $manuscript_info)
        {
            echo '<li><div class="has-row-actionsX">';
            echo '<p>' . esc_html($manuscript_info['eprint']) . '</p>';
            echo '<p class="row-actionsX">';
            echo '<span class="reply"><a href="mailto:' . esc_attr($manuscript_info['corresponding_author_email']) . '">' . esc_html($manuscript_info['corresponding_author_email']) . '</a></span>';
            echo '<span class="approve"><a href="/' . $this->slug . '?action=' . 'publish' . '&id=' . urlencode($id) . '">Publish</a></span>';
            echo '<span class="approve"><a href="/' . $this->slug . '?action=' . 'invoice' . '&id=' . urlencode($id) . '">Invoice</a></span>';

            echo '</p>';
            echo '</div></li>';
        }
        echo '</ul>';

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
            update_post_meta($post_id, $post_type . '_eprint', $manuscript_info['eprint']);
        }

        return $post_id;
    }

    public function insert_and_display_post( $id ) {
        $post_id = $this->insert_post($id);
        if(is_wp_error($post_id))
            echo "ERROR: " . $post_id->get_error_message();
        else
            header('Location: /post.php?post=' . $post_id . '&action=edit');
    }



    public function do_parse_request( $bool, $wp, $extra_query_vars ) {

        $home_path = parse_url(home_url(), PHP_URL_PATH);
        $path = trim(preg_replace("#^/?{$home_path}/#", '/', esc_url(add_query_arg(array()))), '/' );

        if($path !== $this->slug)
            return $bool;

        $action = get_query_var( 'action', null );
        switch($action)
        {
            case 'publish':
                $id = get_query_var( 'id', null );
                if($id !== null)
                    $this->insert_and_display_post($id);
                break;
            default:
                echo "unsupported action " . $action;
        }
        exit();
    }


}
