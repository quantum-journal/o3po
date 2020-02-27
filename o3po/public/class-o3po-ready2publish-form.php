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

        parent::__construct($plugin_name, $slug);
        $this->specify_pages_sections_and_fields();

    }


        /**
         * Specifies form sections and fields.
         *
         * @since 0.3.1+
         * @access private
         */
    protected function specify_pages_sections_and_fields() {

        $this->specify_page('basic_manuscript_data', array( $this, 'render_basic_manuscript_data_navigation' ));

        $this->specify_section('basic_manuscript_data', 'Which manuscript do you want to submit?', array( $this, 'render_basic_manuscript_data_section' ), 'basic_manuscript_data');
        $this->specify_field('eprint', 'ArXiv identifyer', array( $this, 'render_eprint_field' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'validate_eprint'), '0000.0000v2');

    }

    public function render_basic_manuscript_data_navigation( $previous_page_id, $next_page_id ) {
        echo '<input type="submit" value="Submit" />';
    }

    public function render_basic_manuscript_data_section() {
        echo '<h2>Enter the basic manuscript data</h2>';
    }

    public function render_eprint_field() {
        $this->render_single_line_field('eprint');
        echo '<p>The arXiv number of your manuscript</p>';
    }
}
