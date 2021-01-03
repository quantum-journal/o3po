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

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-ready2publish-storage.php';

/**
 * Class for displaying manuscripts ready to publish on the admin panel.
 *
 * @since      0.3.1+
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Ready2PublishDashboard {

        //use O3PO_Ready2PublishStorage;

        /**
         *
         */
    protected $slug;

    protected $plugin_name;

    protected $plugin_pretty_name;

    protected $title;

    private static $meta_fields = [
        'eprint',
        'title',
        'corresponding_author_email',
        'abstract',
        'author_given_names', // is populated from author_first_names
        'author_surnames', // is populated from author_last_names
        'author_name_styles',
        'award_numbers',
        'funder_names',
        'funder_identifiers',
        'popular_summary',
        'featured_image',
        'featured_image_caption',
        'multimedia_comment',
        'fermats_library'
                                   ];

    private $storage;

    public function __construct( $plugin_name, $plugin_pretty_name, $slug, $title, $storage ) {

        $this->plugin_name = $plugin_name;
        $this->slug = $slug;
        $this->title = $title;
        $this->plugin_pretty_name = $plugin_pretty_name;
        $this->storage = $storage;

    }

    public function setup() {

        wp_add_dashboard_widget($this->slug, esc_html($this->plugin_pretty_name . " " . $this->title), array($this, 'render'));
    }

    public function render() {

        $partially_published_manuscripts = $this->storage->get_manuscripts('partial');
        if(!empty($partially_published_manuscripts))
        {
            echo '<h3>Partially published manuscripts</h3>';
            echo '<ul>';
            foreach($partially_published_manuscripts as $id => $manuscript_info)
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
        }

        $unprocessed_manuscripts = $this->storage->get_manuscripts('unprocessed');
        if(!empty($unprocessed_manuscripts))
        {
            echo '<h3>Manuscripts awaiting publication</h3>';
            echo '<ul>';
            foreach($unprocessed_manuscripts as $id => $manuscript_info)
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
        }
    }

    public function insert_post( $id ) {
            // Do nothing if in maintenance mode
        $settings = O3PO_Settings::instance();
        if($settings->get_field_value('maintenance_mode')!=='unchecked')
            return;

            // Check if the user has permissions to save data
		if(!current_user_can('edit_posts'))
			return;

        $manuscript_info = $this->storage->get_manuscript($id);

        $post_type = 'paper';
        $postarr = [
            'post_type' => $post_type,
                    ];
        $post_id = wp_insert_post($postarr, true);
        if(is_wp_error($post_id))
            return $post_id;
        else
        {
            update_post_meta($post_id, $post_type . '_number_authors', count($manuscript_info['author_name_styles']));

                // Translate from first/last to given surname
            $manuscript_info['author_given_names'] = array();
            $manuscript_info['author_surnames'] = array();
            foreach($manuscript_info['author_name_styles'] as $author_num => $name_style)
            {
                if($name_style === 'eastern')
                {
                    $manuscript_info['author_given_names'][$author_num] = $manuscript_info['author_last_names'][$author_num];
                    $manuscript_info['author_surnames'][$author_num] = $manuscript_info['author_first_names'][$author_num];
                }
                else
                {
                    $manuscript_info['author_given_names'][$author_num] = $manuscript_info['author_first_names'][$author_num];
                    $manuscript_info['author_surnames'][$author_num] = $manuscript_info['author_last_names'][$author_num];
                }
            }
            unset($manuscript_info['author_first_names']);
            unset($manuscript_info['author_last_names']);

            foreach( static::meta_fields as $field_id)
                update_post_meta($post_id, $post_type . '_' . $field_id, $manuscript_info[$field_id]);


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
                {
                    $post_eprint = $this->storage->get_manuscript($id)['eprint'];
                    $eprint_without_version = preg_replace('#v[0-9]+$#u', '', $post_eprint);
                    $post_id = $this->post_id_for_eprint($eprint_without_version);
                    $this->display_post($post_id);
                }
                break;
            default:
                echo "unsupported action " . $action;
        }
        exit();
    }


}
