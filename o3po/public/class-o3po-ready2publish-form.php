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
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-environment.php';

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
        $settings->specify_section('ready2publish_settings', 'Ready2Publish Form', array('O3PO_Ready2PublishForm', 'render_ready2publish_settings'), 'ready2publish_settings');
        $settings->specify_field('acceptance_codes', 'Acceptance codes currently valid', array('O3PO_Ready2PublishForm', 'render_acceptance_codes_setting' ), 'ready2publish_settings', 'ready2publish_settings', array(), array($settings, 'validate_array_as_comma_separated_list'), array('AAA'));

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

        $this->specify_page('basic_manuscript_data', 'Your accepted manuscript is ready for publication?');

        $this->specify_section('basic_manuscript_data', 'Please enter the basic manuscript information', array($this, 'render_basic_manuscript_data_section'), 'basic_manuscript_data');
        $this->specify_field('eprint', 'ArXiv identifier', array( $this, 'render_eprint_field' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'validate_eprint_fetch_meta_data_check_license_and_store_in_session'), '');
        $this->specify_field('agree_to_publish', 'Consent to publish', array( $this, 'render_agree_to_publish' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'checked'), 'unchecked');
        $this->specify_field('acceptance_code', 'Acceptance code', array( $this, 'render_acceptance_code' ), 'basic_manuscript_data', 'basic_manuscript_data', array(), array($this, 'validate_acceptance_code'), '');

        $this->specify_page('meta_data', 'Manuscript meta-data');
        $this->specify_section('manuscript_data', 'Manuscript data', array($this, 'render_manuscript_data_section'), 'meta_data');
        $this->specify_field('title', 'Title', array( $this, 'render_title' ), 'meta_data', 'manuscript_data', array(), array($this, 'trim'), '');
        $this->specify_field('abstract', 'Abstract', array( $this, 'render_abstract' ), 'meta_data', 'manuscript_data', array(), array($this, 'trim'), '');

        $this->specify_section('author_data', 'Author data', array($this, 'render_author_data'), 'meta_data'); # We render everything here as part of the section and set the render callable of the fields to Null
        $this->specify_field('author_first_names', Null, Null, 'meta_data', 'author_data', array(), array($this, 'validate_array_of_at_most_1000_names'), array());
        $this->specify_field('author_second_names', Null, Null, 'meta_data', 'author_data', array(), array($this, 'validate_array_of_at_most_1000_names'), array());
        $this->specify_field('author_name_styles', Null, Null, 'meta_data', 'author_data', array(), array($this, 'validate_array_of_at_most_1000_name_styles'), array());

        $this->specify_page('dissemination', 'Dissemination options');

        $this->specify_section('dissemination_material', 'Dissemination material', array($this, 'render_dissemination_material_section'), 'dissemination');

        $this->specify_field('popular_summary', 'Popular summary', array( $this, 'render_popular_summary' ), 'dissemination', 'dissemination_material', array(), array($this, 'trim'), '');
        $this->specify_field('featured_image_upload', 'Featured image', array( $this, 'render_featured_image_upload' ), 'dissemination', 'dissemination_material', array(), array($this, 'validate_featured_image_upload'), '');
        $this->specify_field('featured_image_caption', 'Featured image caption', array( $this, 'render_featured_image_caption' ), 'dissemination', 'dissemination_material', array(), array($this, 'trim'), '');

        $this->specify_section('dissemination_fermats_library', 'Fermat\'s library', null, 'dissemination');
        $this->specify_field('fermats_library', 'Opt-in to Fermat\'s library', array( $this, 'render_fermats_library' ), 'dissemination', 'dissemination_fermats_library', array(), array($this, 'checked_or_unchecked'), 'unchecked');

        $this->specify_page('payment', 'Payment');

        $this->specify_section('payment', 'Choose your payment options', null, 'payment');
        $this->specify_field('waiver', 'Waiver', array( $this, 'render_waiver' ), 'payment', 'payment', array(), array($this, 'checked_or_unchecked'), 'unchecked');

        $this->specify_page('summary', 'Summary');

    }

    public function render_eprint_field() {
        $this->render_single_line_field('eprint', 'e.g. 1234.56789v2');
    }


    public function render_agree_to_publish() {
        $this->render_checkbox_field('agree_to_publish', 'I certify that this is the final version. All authors hereby give their consent to publish it and allow Quantum the necessary processing and storage of personal data.');
    }

    public function render_acceptance_code() {
        echo '<div style="float:left;">';
        $this->render_single_line_field('acceptance_code', null, 'off', '', 'Please enter the acceptance code sent to you in the notification of acceptance.', true, 'display:block;');
        echo '</div>';
    }

    public function render_popular_summary() {
        $this->render_multi_line_field('popular_summary', 12, 'width:100%');
    }

    public function render_featured_image_caption() {
        $this->render_multi_line_field('featured_image_caption', 6, 'width:100%');
    }


    public function render_fermats_library() {

        $settings = O3PO_Settings::instance();
        $this->render_checkbox_field('fermats_library', 'All authors want this paper to appear on <a href="'. esc_attr($settings->get_field_value('fermats_library_about_url')) . ' target="_blank">Fermat\'s library</a>.', false);
    }

    public function render_featured_image_upload() {

        $id = 'featured_image_upload';
        $upload_max_filesize = O3PO_Environment::max_file_upload_bytes();

        $this->render_image_upload_field($id, 'Image must be in jpg or png format, have a white background, and an aspect ratio of 2:1. The maximum file size is ' . ($upload_max_filesize > 1024 ? (round($upload_max_filesize/1024, 2)) . 'M' : $upload_max_filesize) . 'B.');

    }


    public function render_waiver() {

        $settings = O3PO_Settings::instance();
        $this->render_checkbox_field('waiver', 'I require a waiver.');
    }


        /**
         *
         * @param array $file_of_this_id Array with fields such as those
         * of a single element of $_FILE
         */
    public function validate_featured_image_upload( $id, $file_of_this_id ) {

        $temp_file = $file_of_this_id['tmp_name'];
        $size = $file_of_this_id['size'];
        $mime_type = $file_of_this_id['type'];

        $filesize = filesize($temp_file);
        $upload_max_filesize = O3PO_Environment::max_file_upload_bytes();
        if($filesize > $upload_max_filesize)
            return array('error' => "The image file must be at most " . $upload_max_filesize . "B large.");

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actual_mime_type = finfo_file($finfo, $temp_file);
        finfo_close($finfo);
        if($mime_type != $actual_mime_type or !in_array($actual_mime_type, array('image/png', 'image/jpeg')))
            return array('error' => "The image must be a png or jpeg file.");

        $size = getimagesize($temp_file);
        $width = $size[0];
        $height = $size[1];
        if($width !== 2*$height )
            return array('error' => "The image must must have an aspect ratio of 2:1. The current image size is " . $width . "x" . $height . ".");

        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        $result = wp_handle_sideload($file_of_this_id, array('test_form' => FALSE));

        if(empty($result['error']) and !empty($result['file']))
        {
            $result['user_name'] = $file_of_this_id['name'];
            $result['size'] = $file_of_this_id['size'];
            $this->append_session_data('sideloaded_files', $result['file']);
        }

        return $result;
    }


    public function validate_acceptance_code( $id, $input ) {

        $input = trim($input);
        $settings = O3PO_Settings::instance();
        $acceptance_codes = $settings->get_field_value('acceptance_codes');

        if(empty(trim($input)))
        {
            $this->add_error( $id, 'empty-acceptance-code', "An acceptance code must be provided in '" . $this->fields[$id]['title'] . "'.", 'error');
            return $this->get_field_default($id);
        }

        if(in_array($input, $acceptance_codes))
            return $input;

        $this->add_error( $id, 'invalid-acceptance-code', "The acceptance code '" . $input ."' given in '" . $this->fields[$id]['title'] . "' is not valid.", 'error');
        return $this->get_field_default($id);
    }

    public function validate_eprint_fetch_meta_data_check_license_and_store_in_session($id, $input) {

        if(empty(trim($input)))
        {
            $this->add_error( $id, 'eprint-empty', "The arXiv identifier asked for in '" . $this->fields[$id]['title'] . "' may not be empty.", 'error');
            return $this->get_field_default($id);
        }

        $eprint = $this->validate_eprint($id, $input);
        if(trim($input) !== $eprint) # validate_eprint() was not happy and has already added an error for us, we simply return the result
            return $eprint;

        $meta_data = $this->get_session_data('arxiv_meta_data_' . $eprint);
        if(empty($meta_data['title'])) #return $eprint; #meta-data has already been fetched, so we don't fetch again
        {
            $settings = O3PO_Settings::instance();
            $arxiv_url_abs_prefix = $settings->get_field_value('arxiv_url_abs_prefix');
            $meta_data = O3PO_Arxiv::fetch_meta_data_from_abstract_page( $arxiv_url_abs_prefix, $eprint);

            if(!empty($meta_data['arxiv_fetch_results']) and (strpos($meta_data['arxiv_fetch_results'], 'ERROR') or strpos($meta_data['arxiv_fetch_results'], 'WARNING')))
            {
                $this->add_error($id, 'arxiv-fetch-error', $meta_data['arxiv_fetch_results'] . "Are you sure the arXiv identifier is correct and the preprint already available?", 'error');
                return $this->get_field_default($id);
            }

            $arxiv_license = $meta_data['arxiv_license'];
            if(!O3PO_Arxiv::is_cc_by_license_url($arxiv_license))
            {
                $this->add_error($id, 'upload-error', "It seems like your " . $eprint . " is not published under one of the three creative commons license (CC BY 4.0, CC BY-SA 4.0, or CC BY-NC-SA 4.0) on the arXiv. Please update the arXiv version of your manuscript and chose the CC BY 4.0 license.", 'error');
                return $this->get_field_default($id);
            }

            $this->put_session_data('arxiv_meta_data_' . $eprint, $meta_data);
        }

        # The way the validation of options works, we can still set fields that appear later in the form here. We just have to do the same sanitation and validation as if the input were coming form the user.
        foreach(['title' => 'title', 'abstract' => 'abstract', 'author_given_names' => 'author_first_names', 'author_surnames' => 'author_second_names'] as $source => $id)
            if(empty($_POST[$this->plugin_name . '-' . $this->slug][$id]))
                $_POST[$this->plugin_name . '-' . $this->slug][$id] = call_user_func($this->fields[$id]['validation_callable'], $id, $this->sanitize_user_input($meta_data[$source]));

        if(empty($_POST[$this->plugin_name . '-' . $this->slug]['author_name_styles']))
        {
            $_POST[$this->plugin_name . '-' . $this->slug]['author_name_styles'] = array();
            foreach($_POST[$this->plugin_name . '-' . $this->slug]['author_first_names'] as $foo)
                $_POST[$this->plugin_name . '-' . $this->slug]['author_name_styles'][] = 'western';
        }

        return $eprint;
    }


    public function render_title() {

        $this->render_single_line_field('title', '', 'on', 'width:100%;');
    }

    public function render_abstract() {

        $this->render_multi_line_field('abstract', 12, 'width:100%;', true);

    }


    public function render_author_data() {

        $author_first_names = $this->get_field_value('author_first_names');
        $author_second_names = $this->get_field_value('author_second_names');

        echo '<p>Please help us identify which part(s) of the authors\' names belong to the first and which to their second name(s) as well as which part is their given name (e.g., in Chinese names the given name is usually after the family name, whereas in western cultures the given name comes first). This format does not do justice to <a href="https://www.w3.org/International/questions/qa-personal-names">all common name styles around the world</a>, but names in this format are needed for the registration of DOIs with Crossref.</p>';
        echo '<div id="' . $this->plugin_name . '-' . $this->slug . '-author-list">';
        foreach($author_second_names as $x => $surname)
        {
            echo '<div class="' . $this->plugin_name . '-' . $this->slug . ' ' . $this->plugin_name . '-' . $this->slug . '-author">';

            echo '<div style="float:left;">';
            $this->render_single_line_field('author_first_names[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'First and middle name(s)', true, 'display:block;');
            echo '</div>';
            echo '<div style="float:left;">';
            $this->render_single_line_field('author_second_names[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Last name(s)', true, 'display:block;');
            echo '</div>';

            $this->render_select_field('author_name_styles[' . $x . ']', [
                                      array('value' => 'western',
                                            'description' => 'First name(s) are given name(s)'),
                                      array('value' => 'eastern',
                                            'description' => 'Last name(s) are given name(s)'),
                                      /* array('value' => 'islensk', */
                                      /*       'description' => 'Last name(s) are given name(s)'), */
                                      /* array('value' => 'given-only', */
                                      /*       'description' => 'Only given name(s)'), */
                                                        ]);
            echo '</div>';
        }
        echo '</div>';
        echo'<script>
        function addAuthor() {
            var item = document.getElementById("' . $this->plugin_name . '-' . $this->slug . '-author-list").lastElementChild;
            var clone = item.cloneNode(true);
            var authorNumber = parseInt(RegExp("\\\\[([0-9]*)\\\\]$").exec(clone.getElementsByTagName("input")[0].name)[1]) + 1;
            var inputs = clone.getElementsByTagName("input");
            for (i = 0; i < inputs.length; i++) {
              inputs[i].value = "";

              inputs[i].name = inputs[i].name.replace(RegExp("\[[0-9]*\]$"), "["+authorNumber+"]");
              inputs[i].id = inputs[i].id.replace(RegExp("\[[0-9]*\]$"), "["+authorNumber+"]");
            }
            var labels = clone.getElementsByTagName("label")
            for (i = 0; i < labels.length; i++) {
              labels[i].setAttribute("for", labels[i].getAttribute("for").replace(RegExp("\[[0-9]*\]$"), "["+authorNumber+"]"));
            }
            var selects = clone.getElementsByTagName("select")
            for (i = 0; i < selects.length; i++) {
              selects[i].name = selects[i].name.replace(RegExp("\[[0-9]*\]$"), "["+authorNumber+"]");
              selects[i].id = selects[i].id.replace(RegExp("\[[0-9]*\]$"), "["+authorNumber+"]");
              selects[i].selectedIndex = 0;
            }
            document.getElementById("' . $this->plugin_name . '-' . $this->slug . '-author-list").appendChild(clone);
        }
        function removeAuthor() {
            var select = document.getElementById("' . $this->plugin_name . '-' . $this->slug . '-author-list");
            if(select.childElementCount > 1) {
                select.removeChild(select.lastElementChild);
            }
        }
        </script>';
        echo '<button type="button" onclick="addAuthor()">Add author</button>';
        echo '<button type="button" onclick="removeAuthor()">Remove author</button>';

    }

    public static function render_ready2publish_settings() {

                echo '<p>Configure the form for submission of accepted manuscripts ready for publication.</p>';

    }

    public static function render_acceptance_codes_setting() {

        $settings = O3PO_Settings::instance();
        $settings->render_array_as_comma_separated_list_field('acceptance_codes');
        echo '<p>(Comma separated list of currently valid acceptance codes the user can enter to make it past the first page of the form.)</p>';
    }

    public function submitted_message() {
        return 'Thank you for providing ';
    }


    public function render_basic_manuscript_data_section() {

        echo 'Data in this form ... if you encounter any problems, ....';
    }

    public function render_manuscript_data_section() {

        echo '<p>The following information was fetched from the arXiv for your convenience. Please check and correct carefully. You may use standard LaTeX formulas in both title and abstract.</p>';
    }

    public function render_dissemination_material_section() {

        echo '<p>Please provide optional dissemination material.</p>';
    }
}
