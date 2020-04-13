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
            echo '<span class="approve"><a href="/' XXX . '?id=' . $id . '">Publish</a></span>';
            echo '</p>';
            echo '</div></li>';
        }
        echo '</ul>';

    }

    public function insert_post() {

        $postarr = [
            XXX
        ];
        return wp_insert_post($postarr, true);
    }

    public function insert_and_display_post() {
        $post = $this->insert_post();
        if(is_wp_error($post))
            XXX
        else
            header('Location: /post.php?post=' . $post . '&action=edit');
    }



}
