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

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-ready2publish-storage.php';

/**
 * Class for displaying manuscripts ready to publish on the admin panel.
 *
 * @since      0.3.1+
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Ready2PublishDashboard implements O3PO_SettingsSpecifyer {

        //use O3PO_Ready2PublishStorage;

        /**
         *
         */
    protected $slug;

    protected $plugin_name;

    protected $plugin_pretty_name;

    protected $title;

    private static $meta_fields_to_set_when_inserting_post = [
        'eprint',
        'title',
        'corresponding_author_email',
        'abstract',
        'author_given_names',
        'author_surnames',
        'author_name_styles',
        'number_award_numbers',
        'award_numbers',
        'funder_names',
        'funder_identifiers',
        'popular_summary',
        'feature_image_caption',
        'dissemination_multimedia',
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

    public static function specify_settings( $settings ) {

        $settings->specify_field('invoice_header_img', 'Invoice header image', array('O3PO_Ready2PublishDashboard', 'render_invoice_header_img_setting'), 'ready2publish_settings', 'ready2publish_settings', array(), array($settings, 'trim_strip_tags'), '');

        $settings->specify_field('invoice_email', 'Invoice email', array('O3PO_Ready2PublishDashboard', 'render_invoice_email_setting'), 'ready2publish_settings', 'ready2publish_settings', array(), array($settings, 'validate_email'), '');

        $settings->specify_field('invoice_footer', 'Invoice footer', array('O3PO_Ready2PublishDashboard', 'render_invoice_footer_setting'), 'ready2publish_settings', 'ready2publish_settings', array(), array($settings, 'trim'), '');


    }


    public function setup() {

        wp_add_dashboard_widget($this->slug, esc_html($this->plugin_pretty_name . " " . $this->title), array($this, 'render'));
    }

    public function render_manuscript_entry( $id, $manuscript_info, $action ) {

        $settings = O3PO_Settings::instance();
        $out = "";
        $out .= '<li><div class="manuscript-ready2publish">';
        $out .= '<a style="display:inline-block;margin-right:5px" target="_blank" href="' . esc_attr($settings->get_field_value('arxiv_url_abs_prefix') . $manuscript_info['eprint']) . '">' . esc_html($manuscript_info['eprint']) . '</a>';
        $out .= '<div style="margin-left:5px">';
        $out .= '<div>Title: ' . esc_html($manuscript_info['title']) . '</div>';
        if(!empty($manuscript_info['ready2publish_comments']))
            $out .= '<div>Author comment: ' . esc_html($manuscript_info['ready2publish_comments']) . '</div>';
        if(!empty($manuscript_info['time_submitted']))
           $out .= '<div>Submitted: ' . gmdate("Y-m-d H:i:s", $manuscript_info['time_submitted']) . " GMT" . '</div>';
        if($manuscript_info['payment_method'] == 'invoice')
            $out .= '<div>Invoice: ' . "An invoice was requested!" . '</div>';
        $out .= '<div style="float:right">';
        $out .= '<span><a href="mailto:' . esc_attr($manuscript_info['corresponding_author_email']) . '">Email ' . esc_html($manuscript_info['corresponding_author_email']) . '</a></span>';
        $out .= ' | ';
        $out .= '<span class=""><a target="_blank" href="/' . $this->slug . '?action=' . 'show_invoice' . '&id=' . urlencode($id) . '">' . "Create invoice" .  '</a></span>';
        $out .= ' | ';
        $out .= '<span class=""><a href="/' . $this->slug . '?action=' . $action . '&id=' . urlencode($id) . '">' . ($action === 'continue' ? "Go to post" : "Begin publishing") .  '</a></span>';
        $out .= '</div>';
        $out .= '<div style="clear:both"></div>';
        $out .= '</div>';
        $out .= '</div></li>';

        return $out;
    }


    public function render() {

        $partially_published_manuscripts = $this->storage->get_manuscripts('partial');
        if(!empty($partially_published_manuscripts))
        {
            echo '<h3>Partially published manuscripts</h3>';
            echo '<ul>';
            #foreach($partially_published_manuscripts as $id => $manuscript_info)
            reset($partially_published_manuscripts);
            for(end($partially_published_manuscripts); ($id=key($partially_published_manuscripts))!==null; prev($partially_published_manuscripts))
            {
                echo $this->render_manuscript_entry($id, $partially_published_manuscripts[$id], 'continue');
            }
            echo '</ul>';
        }

        $unprocessed_manuscripts = $this->storage->get_manuscripts('unprocessed');
        if(!empty($unprocessed_manuscripts))
        {
            echo '<h3>Manuscripts awaiting publication</h3>';
            echo '<ul>';
            #foreach($unprocessed_manuscripts as $id => $manuscript_info)
            reset($unprocessed_manuscripts);
            for(end($unprocessed_manuscripts); ($id=key($unprocessed_manuscripts))!==null; prev($unprocessed_manuscripts))
            {
                echo $this->render_manuscript_entry($id, $unprocessed_manuscripts[$id], 'publish');
            }
            echo '</ul>';
        }

        if(empty($partially_published_manuscripts) and empty($unprocessed_manuscripts))
            echo '<p>No manuscripts awaiting publication.</p>';
    }

    public function insert_post( $id ) {

            // Do nothing if in maintenance mode
        $settings = O3PO_Settings::instance();
        if($settings->get_field_value('maintenance_mode') !== 'unchecked')
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
            foreach(static::$meta_fields_to_set_when_inserting_post as $field_id)
                update_post_meta($post_id, $post_type . '_' . $field_id, addslashes($manuscript_info[$field_id]));
            update_post_meta($post_id, $post_type . '_ready2publish_storage_id', $id);
                // We also do a few more things that are normally done by the publication type class
            if(!empty($manuscript_info['feature_image_attachment_id']))
                set_post_thumbnail($post_id, $manuscript_info['feature_image_attachment_id']);
            wp_update_post( array('ID' => $post_id, 'post_title' => addslashes($manuscript_info['title']) ));
            update_post_meta( $post_id, $post_type . '_buffer_email', 'checked');

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
                    $post_id = $this->storage->post_id_for_eprint($eprint_without_version);
                    $this->display_post($post_id);
                }
                break;
            case 'show_invoice':
                if($id !== null)
                    $this->show_invoice($id);
                break;
            default:
                echo "unsupported action " . $action;
        }
        exit();
    }

        /**
         * Render the setting for the invoice header image.
         *
         * @since    0.3.1+
         * @access   public
         */
    public static function render_invoice_header_img_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_single_line_field('invoice_header_img');
        echo('<p>Path to the image to show in the head of invoices.</p>');
    }

        /**
         * Render the setting for the invoice email.
         *
         * @since    0.3.1+
         * @access   public
         */
    public static function render_invoice_email_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_single_line_field('invoice_email');
        echo('<p>The email address of the publisher displayed on invoices.</p>');
    }

        /**
         * Render the setting for the invoice header image.
         *
         * @since    0.3.1+
         * @access   public
         */
    public static function render_invoice_footer_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_multi_line_field('invoice_footer');
        echo('<p>Text to put in the footer of invoices. May contain arbitrary html tags.</p>');
    }

        /**
         * Render the setting for the invoice header image.
         *
         * @since    0.3.1+
         * @access   public
         */
    public function show_invoice($id)
    {
		if(!current_user_can('edit_posts'))
			return;

        $settings = O3PO_Settings::instance();
        $manuscript = $this->storage->get_manuscript($id);

        $invoice_html = '';
        $invoice_html .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
        $invoice_html .= '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
        $invoice_html .= '<header><style type="text/css">';
        $invoice_html .= '@media print {@page {size: A4;} body {margin:25mm;} input, textarea {border: none !important;box-shadow: none !important;outline: none !important;font-family: inherit;
   font-size: inherit;} a, a:visited {color: blue;}} input:required:invalid {border: 3pt solid red;}';
        $invoice_html .= '</style>';
        $invoice_html .= '<script type="text/x-mathjax-config">
        MathJax.Hub.Config({
              tex2jax: {inlineMath: [[\'$\',\'$\'], [\'\\\\(\',\'\\\\)\']], processEscapes: true},
              TeX: {equationNumbers: {autoNumber: "AMS"}}
            });
        </script>
        <script type="text/javascript" async src="' . esc_attr($settings->get_field_value('mathjax_url')) . '?config=TeX-AMS_CHTML"></script>';
        $invoice_html .= '</header>';
        $invoice_html .= '<body style="font-family:Sans-Serif;font-size:11pt;">';
        $invoice_html .= '<div>';
        $invoice_html .= '<div style="height:45mm;float:left">';
        $invoice_html .= '<img src="' . esc_attr($settings->get_field_value("invoice_header_img")) . '" style="width:6cm;">';
        $invoice_html .= '</div>';
        $invoice_html .= '<div style="width:6cm;float:right;text-align:right">' . "\n";
        $invoice_html .= '<strong>' . esc_html($settings->get_field_value('publisher')) . '</strong><br /><br />';

        foreach(["publisher_street_and_number", "publisher_zip_code_and_city", "publisher_country", "publisher_phone"] as $field)
        {
            if(!empty($settings->get_field_value($field)))
                $invoice_html .= esc_html($settings->get_field_value($field)) . '<br />';
        }

        if(!empty($settings->get_field_value("invoice_email")))
            $invoice_html .= '<a href="mailto:' . esc_attr($settings->get_field_value("invoice_email")) . '">' . esc_html($settings->get_field_value("invoice_email")) . '</a><br />';
        $invoice_html .= '<a href="' . esc_attr(get_site_url()) . '">' . esc_html(get_site_url()) . '</a><br />';
        $invoice_html .= '</div>';
        $invoice_html .= '<div style="clear:left"></div>';
        $invoice_html .= '<div>';
        $invoice_html .= '<textarea style="width:85mm;height:40mm;resize: none;">' . esc_html($manuscript['invoice_recipient'] . "\n" . $manuscript['invoice_address'] . (!empty($manuscript['invoice_vat_number']) ? "\nVat-Nr: " . $manuscript['invoice_vat_number'] : '')) . '</textarea>';
        $invoice_html .= '</div>';
        $invoice_html .= '<div style="margin-bottom:2em">';
        $invoice_html .= '<div style="float:left;font-size:16pt;">Invoice Nr. <input required style="font-size:16pt;"></input></div>';
        $invoice_html .= '<div style="float:right">Invoice date: <strong>' . esc_html(date('Y-m-d')) . '</strong></div>';
        $invoice_html .= '<div style="clear:both"></div>';
        $invoice_html .= '</div>';
        $invoice_html .= '<table style="width:100%;border-collapse: collapse;">
  <colgroup>
    <col span="1" style="width: 15%;">
    <col span="1" style="width: 50%;">
    <col span="1" style="width: 20%;">
    <col span="1" style="width: 15%;">
  </colgroup>
  <tr style="text-align:left;border-bottom: 1pt solid black;">
    <th >Quantity</th>
    <th>Description</th>
    <th style="text-align:right;">Price per item</th>
    <th style="text-align:right;">Total price</th>
  </tr>
  <tr>
    <td style="vertical-align: top;padding-top:1em;padding-bottom:1em">' . '<input style="width:6em;text-align:left" value="' . "1" . '"></input>' . '</td>
    <!--<td style="vertical-align: top;padding-top:1em;padding-bottom:1em">Publication fee for article:<br /><textarea style="font-weight: bold;width:100%;resize: none;min-height: 5em;">' . esc_html($manuscript['title']) . '</textarea></td>-->
    <td style="vertical-align: top;padding-top:1em;padding-bottom:1em">Publication fee for article:<br /><strong>' . esc_html($manuscript['title']) . '</strong></td>
    <td style="vertical-align: bottom;text-align:right;padding-top:1em;padding-bottom:1em"><strong>' . '<input style="font-weight: bold;width:6em;text-align:right" value="' . esc_attr($manuscript['payment_amount']) . '"></input>' . '</strong></td>
    <td style="vertical-align: bottom;text-align:right;padding-top:1em;padding-bottom:1em"><strong>' . '<input style="font-weight: bold;width:6em;text-align:right" value="' . esc_attr($manuscript['payment_amount']) . '"></input>' . '</strong></td>
  </tr>
  <tr style="border-top: 1pt solid black;">
    <td></td>
    <td></td>
    <td style="text-align:right;padding-top:1em;padding-bottom:1em"><strong>Total</strong></td>
    <td style="text-align:right;padding-top:1em;padding-bottom:1em">' . '<input style="font-weight: bold;width:6em;text-align:right" value="' . esc_attr($manuscript['payment_amount']) . '"></input>' . '</td>
  </tr>
</table>';
        $invoice_html .= '<div style="margin-top:2em">' . $settings->get_field_value('invoice_footer') . '</div>';
        $invoice_html .= '</div>';
        $invoice_html .= '</body>';
        $invoice_html .= '</html>';

        echo $invoice_html;

    }
}
