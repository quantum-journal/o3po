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
class O3PO_Ready2PublishForm {

    use O3PO_Form;
    use O3PO_Ready2PublishStorage;


    public function __construct() {

        $this->slug = O3PO_Ready2PublishStorage::$slug;
        $this->specify_sections_and_fields();

    }

        /**
         * Specifies form sections and fields.
         *
         * @since 0.3.1+
         * @access private
         */
    private function specify_sections_and_fields() {

        $this->specify_section('basic_manuscript_data', 'Which manuscript do you want to submit?', array( $this, 'render_basic_manuscript_data' ), 'basic_manuscript_data');
        $this->specify_field('eprint', 'ArXiv identifyer', array( $this, 'render_eprint_field' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'validate_eprint'), '');

    }

    public function render() {

    }

       /**
         * Adds a rewrite endpoint for the form.
         *
         * To be added to the 'init' action.
         *
         * @since    0.1.0
         * @access   public
         * */
    public function add_endpoint() {

        add_rewrite_endpoint($this->slug, EP_ROOT);
            //flush_rewrite_rules( true );  //// <---------- ONLY COMMENT IN WHILE TESTING
    }


}
