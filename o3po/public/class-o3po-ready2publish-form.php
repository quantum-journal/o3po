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

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-o3po-public-form.php';

/**
 * Class for the ready to publish form.
 *
 * @since      0.3.1+
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Ready2PublishForm extends O3PO_PublicForm {

    public static function specify_settings( $settings ) {

    }

    public function __construct( $plugin_name, $slug ) {

        parent::__construct($plugin_name, $slug, 'Submit your manuscript for publication');
        $this->specify_pages_sections_and_fields();

    }


        /**
         * Specifies form sections and fields.
         *
         * @since 0.3.1+
         * @access private
         */
    protected function specify_pages_sections_and_fields() {

        $this->specify_page('basic_manuscript_data', 'Enter the basic manuscript data');

        $this->specify_section('basic_manuscript_data', 'Which manuscript do you want to submit?', null, 'basic_manuscript_data');
        $this->specify_field('eprint', 'ArXiv identifier', array( $this, 'render_eprint_field' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'validate_eprint'), '');
        $this->specify_field('agree_to_publish', 'Consent to publish', array( $this, 'render_agree_to_publish' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'checked'), 'unchecked');
        $this->specify_field('acceptance_code', 'Acceptance code', array( $this, 'render_acceptance_code' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'trim'), '');

        $this->specify_page('dissemination', 'Dissemination options');

        $this->specify_section('dissemination_material', 'Dissemination material', null, 'dissemination');
        $this->specify_field('popular_summary', 'Popular summary', array( $this, 'render_popular_summary' ), 'dissemination', 'dissemination_material', array(), array($this, 'trim'), '');
        $this->specify_field('featured_image_upload', 'Featured image', array( $this, 'render_featured_image_upload' ), 'dissemination', 'dissemination_material', array(), array($this, 'leave_unchanged'), '');

        $this->specify_section('dissemination_fermats_library', 'Fermat\'s library', null, 'dissemination');
        $this->specify_field('fermats_library', 'Opt-in to Fermat\'s library', array( $this, 'render_fermats_library' ), 'dissemination', 'dissemination_fermats_library', array(), array($this, 'checked_or_unchecked'), 'unchecked');

        $this->specify_page('payment', 'Payment');

        $this->specify_section('payment', 'Choose your payment options', null, 'payment');
        $this->specify_field('waiver', 'Waiver', array( $this, 'render_waiver' ), 'payment', 'payment', array(), array($this, 'checked_or_unchecked'), 'unchecked');

    }

    public function render_eprint_field() {
        $this->render_single_line_field('eprint', 'e.g. 1234.56789v2');
    }


    public function render_agree_to_publish() {
        $this->render_checkbox_field('agree_to_publish', 'I certify that this is the final version and all authors have given their consent to publish it.');
    }

    public function render_acceptance_code() {
        $this->render_single_line_field('acceptance_code');
    }

    public function render_popular_summary() {
        $this->render_multi_line_field('popular_summary');
    }

    public function render_fermats_library() {

        $settings = O3PO_Settings::instance();
        $this->render_checkbox_field('fermats_library', 'I want this paper to appear on <a href="'. esc_attr($settings->get_field_value('fermats_library_about_url')) . ' target="_blank">Fermat\'s library</a>.', false);
    }

    public function render_featured_image_upload() {

        $id = 'featured_image_upload';

        $this->render_image_upload_field($id, 'Image must have a white background and have an aspect ratio of 2:1.');

    }


    public function render_waiver() {

        $settings = O3PO_Settings::instance();
        $this->render_checkbox_field('waiver', 'I require a waiver.');
    }

}
