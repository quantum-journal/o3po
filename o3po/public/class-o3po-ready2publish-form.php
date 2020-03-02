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

        $this->specify_field('acceptance_code', 'Acceptance code', array( $this, 'render_acceptance_code' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'trim'), '');

        $this->specify_page('payment', 'Payment');

    }

    public function render_eprint_field() {
        $this->render_single_line_field('eprint', 'e.g. 1234.56789v2');
    }

    public function render_acceptance_code() {
        $this->render_single_line_field('acceptance_code');
    }

}
