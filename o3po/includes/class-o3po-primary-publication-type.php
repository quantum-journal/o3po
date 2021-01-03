<?php

/**
 * Class representing the primary publication type.
 *
 * Each publication type is connected to a WordPress custom post type and
 * individual publications are represented by posts of that type.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-latex.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-publication-type.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-email-templates.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-arxiv.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-relevanssi.php';


/**
 * Class representing the primary publication type.
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_PrimaryPublicationType extends O3PO_PublicationType {

        /**
         * Construct this publication type.
         *
         * Constructs and registers this publication type in the array
         * static::$active_publication_types. Throws an error in case a
         * publication type with the same $publication_type_name is alreay
         * registered.
         *
         * @since    0.1.0
         * @access   public
         * @param    object               $journal        The journal this publication type is associated with.
         * @param    O3PO_Environment     $environment    The evironment in which this post type is to be created.
         */
    public function __construct( $journal, $environment ) {

        parent::__construct($journal, 4, $environment);
    }

        /**
         * Render the admin panel meta box.
         *
         * @since     0.1.0
         * @access    public
         * @param     Post    $post    Post for which the meta box is to be rendered.
         */
    public function render_metabox( $post ) {

        $post_id = $post->ID;
        $post_type = get_post_type($post_id);
            // If the post type doesn't fit do nothing
        if ( $this->get_publication_type_name() !== $post_type )
            return;

        parent::render_metabox( $post );

        $this->the_admin_panel_intro_text($post_id);
        $this->the_admin_panel_howto($post_id);
        $this->the_admin_panel_validation_result($post_id);
        echo '<table class="form-table">';
        $this->the_admin_panel_eprint($post_id);
        $this->the_admin_panel_title($post_id);
        $this->the_admin_panel_corresponding_author_email($post_id);
        $this->the_admin_panel_buffer($post_id);
        $this->the_admin_panel_fermats_library($post_id);
        $this->the_admin_panel_authors($post_id);
        $this->the_admin_panel_affiliations($post_id);
        $this->the_admin_panel_date_volume_pages($post_id);
        $this->the_admin_panel_abstract($post_id);
        $this->the_admin_panel_doi($post_id);
        $this->the_admin_panel_feature_image_caption($post_id);
        $this->the_admin_panel_popular_summary($post_id);
        $this->the_admin_panel_bibliography($post_id);
        $this->the_admin_panel_crossref($post_id);
        $this->the_admin_panel_doaj($post_id);
        $this->the_admin_panel_clockss($post_id);
        $this->the_admin_panel_arxiv($post_id);
        echo '</table>';

    }

        /**
         * Callback function for handling the data enterd into the meta-box
         * when a correspnding post is saved.
         *
         * Calls save_meta_data() and validate_and_process_data() as well as
         * a bunch of other methods such as on_post_actually_published() when appropriate
         * to actually do the processing. Also ensures that the post is forced to private
         * as long as there are still validation ERRORs or REVIEW requests.
         *
         * Warning: This is already called when a New Post is created and not
         * only when the "Publish" or "Update" button is pressed!
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post whose meta data is to be saved.
         * */
    protected function save_meta_data( $post_id ) {

        parent::save_meta_data($post_id);

        $post_type = get_post_type($post_id);

		$new_abstract = isset( $_POST[ $post_type . '_abstract' ] ) ? $_POST[ $post_type . '_abstract' ] : '';
		$new_abstract_mathml = isset( $_POST[ $post_type . '_abstract_mathml' ] ) ? $_POST[ $post_type . '_abstract_mathml' ] : '';
		$new_eprint = isset( $_POST[ $post_type . '_eprint' ] ) ? sanitize_text_field( $_POST[ $post_type . '_eprint' ] ) : '';
        $old_eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
        $new_fermats_library = isset($_POST[ $post_type . '_fermats_library' ]) ? $_POST[ $post_type . '_fermats_library' ] : '';
		$new_fermats_library_permalink = isset( $_POST[ $post_type . '_fermats_library_permalink' ] ) ? sanitize_text_field( $_POST[ $post_type . '_fermats_library_permalink' ] ) : '';
		$new_feature_image_caption = isset( $_POST[ $post_type . '_feature_image_caption' ] ) ? $_POST[ $post_type . '_feature_image_caption' ] : '';
		$new_popular_summary = isset( $_POST[ $post_type . '_popular_summary' ] ) ? $_POST[ $post_type . '_popular_summary' ] : '';
        $old_arxiv_fetch_results = get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true );

        if ($old_eprint === $new_eprint)
            update_post_meta( $post_id, $post_type . '_eprint_was_changed_on_last_save', "false" );
		else
			update_post_meta( $post_id, $post_type . '_eprint_was_changed_on_last_save', "true" );

        $arxiv_fetch_results = '';
		if ( ( isset($_POST[$post_type . '_fetch_metadata_from_arxiv'] ) or $old_eprint !== $new_eprint ) and !empty($new_eprint) and preg_match("/^(quant-ph\/[0-9]{6,}|[0-9]{4}\.[0-9]{4,})v[0-9]*$/u", $new_eprint) === 1 ) {

            $settings = O3PO_Settings::instance();
            $arxiv_abs_page_url = $settings->get_field_value('arxiv_url_abs_prefix');
            $fetch_meta_data_from_abstract_page_result = O3PO_Arxiv::fetch_meta_data_from_abstract_page($arxiv_abs_page_url, $new_eprint);
            if(!empty($fetch_meta_data_from_abstract_page_result['arxiv_fetch_results']))
                $arxiv_fetch_results .= $fetch_meta_data_from_abstract_page_result['arxiv_fetch_results'];
            if(!empty($fetch_meta_data_from_abstract_page_result['abstract']))
                $new_abstract = addslashes($fetch_meta_data_from_abstract_page_result['abstract']);
            if(!empty($fetch_meta_data_from_abstract_page_result['title']))
                update_post_meta( $post_id, $post_type . '_title', addslashes($fetch_meta_data_from_abstract_page_result['title']));
            if(!empty($fetch_meta_data_from_abstract_page_result['number_authors']))
                update_post_meta( $post_id, $post_type . '_number_authors', $fetch_meta_data_from_abstract_page_result['number_authors'] );
            if(!empty($fetch_meta_data_from_abstract_page_result['author_first_names']))
                update_post_meta( $post_id, $post_type . '_author_given_names', $fetch_meta_data_from_abstract_page_result['author_first_names'] ); # we take the first name as the given name, which is not always correct
            if(!empty($fetch_meta_data_from_abstract_page_result['author_last_names']))
                update_post_meta( $post_id, $post_type . '_author_surnames', $fetch_meta_data_from_abstract_page_result['author_last_names'] ); # we take the last name as the surname, which is not always correct
            if(isset($fetch_meta_data_from_abstract_page_result['number_authors']) and $fetch_meta_data_from_abstract_page_result['number_authors'] >= 0)
                update_post_meta( $post_id, $post_type . '_author_name_styles', array_fill(0, $fetch_meta_data_from_abstract_page_result['number_authors'], 'western') ); #we cannot guess the name style from the arXiv page but must set this to some legal value
		}
        else if(strpos($old_arxiv_fetch_results, 'ERROR') !== false or strpos($old_arxiv_fetch_results, 'WARNING') !== false)
            $arxiv_fetch_results .= "WARNING: No meta-data was fetched from the arXiv this time, but there were errors or warnings during the last fetch. Please make sure to resolve all of them manually or trigger a new fetch attempt by ticking the corresponding box below.\n";

        update_post_meta( $post_id, $post_type . '_arxiv_fetch_results', $arxiv_fetch_results );
		update_post_meta( $post_id, $post_type . '_abstract', $new_abstract);
		update_post_meta( $post_id, $post_type . '_abstract_mathml', $new_abstract_mathml );
		update_post_meta( $post_id, $post_type . '_eprint', $new_eprint );
		update_post_meta( $post_id, $post_type . '_fermats_library', $new_fermats_library );
		update_post_meta( $post_id, $post_type . '_fermats_library_permalink', $new_fermats_library_permalink );
		update_post_meta( $post_id, $post_type . '_feature_image_caption', $new_feature_image_caption );
		update_post_meta( $post_id, $post_type . '_popular_summary', $new_popular_summary );

    }

        /**
         * Validate and process the meta-data that was saved in save_meta_data().
         *
         * @since    0.1.0
         * @access   protected
         * @param    int          $post_id   The id of the post whose meta-data is to be validated and processed.
         */
    protected function validate_and_process_data( $post_id ) {

        $post_type = get_post_type($post_id);

        $settings = O3PO_Settings::instance();

        $abstract = get_post_meta( $post_id, $post_type . '_abstract', true );
        $abstract_mathml = get_post_meta( $post_id, $post_type . '_abstract_mathml', true );
        $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
        $eprint_was_changed_on_last_save = get_post_meta( $post_id, $post_type . '_eprint_was_changed_on_last_save', true );
        $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        $doi_suffix_was_changed_on_last_save = get_post_meta( $post_id, $post_type . '_doi_suffix_was_changed_on_last_save', true );
        $arxiv_fetch_results = get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true );
        $arxiv_pdf_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_pdf_attach_ids');
        $arxiv_source_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_source_attach_ids');
        $fermats_library = get_post_meta( $post_id, $post_type . '_fermats_library', true );
        $fermats_library_permalink = get_post_meta( $post_id, $post_type . '_fermats_library_permalink', true );
        $fermats_library_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_fermats_library_has_been_notifed_date', true );
		$corresponding_author_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_corresponding_author_has_been_notifed_date', true );
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );

            // Set the category
        $term_id = term_exists( ucfirst($this->get_publication_type_name()), 'category' );
        if($term_id == 0)
        {
            wp_insert_term( ucfirst($this->get_publication_type_name()), 'category');
            $term_id = term_exists( ucfirst($this->get_publication_type_name()), 'category' );
        }
        wp_set_post_terms( $post_id, $term_id, 'category' );

        $validation_result = '';
        $validation_result .= $arxiv_fetch_results;

        $post_date = get_the_date( 'Y-m-d', $post_id );
        $today_date = current_time( 'Y-m-d' );

        if ($post_date !== $date_published)
            $validation_result .= "ERROR: The publication date of this post (" . $post_date . ") set in the Publish box on the right does not match the publication date (" . $date_published . ") of this " . $post_type . " given in the input field below.\n";
        if ($post_date !== $today_date and empty($corresponding_author_has_been_notifed_date) )
            $validation_result .= "WARNING: The publication date of this post (" . $post_date . ") is not set to today's date (" . $today_date . ") but the post of this " . $post_type . " also does not appear to have already been published in the past.\n";
        if ($eprint_was_changed_on_last_save === "true")
            $validation_result .= "REVIEW: The eprint was set to ". $eprint . ".\n";
        if ( empty( $eprint ) )
            $validation_result .= "ERROR: Eprint is empty.\n";
        else if (strpos($eprint, 'v') === false or preg_match("/^(quant-ph\/[0-9]{5,}|[0-9]{4}\.[0-9]{4,})v[0-9]*$/u", $eprint ) !== 1 )
            $validation_result .= "ERROR: Eprint does not contain the specific arXiv version, i.e., ????.????v3.\n";

            // Download PDF form the arXiv
        if( !empty( $doi_suffix ) and !empty( $eprint ) and (isset($_POST[$post_type . '_download_arxiv_pdf']) or empty($arxiv_pdf_attach_ids) or $eprint_was_changed_on_last_save === "true" or $doi_suffix_was_changed_on_last_save === "true" ) )
        {
            $arxiv_url_pdf_prefix = $settings->get_field_value('arxiv_url_pdf_prefix');
            $pdf_download_result= O3PO_Arxiv::download_pdf($this->environment, $arxiv_url_pdf_prefix, $eprint, $doi_suffix, $post_id);
        }
        if ( !empty( $pdf_download_result['error'] ) ) {
            $validation_result .= "ERROR: Exception while downloading the pdf of " . $eprint . " from the arXiv: " . $pdf_download_result['error'] . "\n";
        } else if (!empty($pdf_download_result)) {
            $arxiv_pdf_attach_ids[] = $pdf_download_result['attach_id'];
            update_post_meta( $post_id, $post_type . '_arxiv_pdf_attach_ids', $arxiv_pdf_attach_ids );
            $validation_result .= "REVIEW: The pdf was downloaded successfully from the arXiv.\n";
        }

            // Download SOURCE form the arXiv (This can yield either a .tex or a .tar.gz file!)
        if( !empty( $doi_suffix ) and !empty( $eprint ) and (isset($_POST[$post_type . '_download_arxiv_source']) or empty($arxiv_source_attach_ids) or $eprint_was_changed_on_last_save === "true" or $doi_suffix_was_changed_on_last_save === 'true') )
        {
            $arxiv_url_source_prefix = $settings->get_field_value('arxiv_url_source_prefix');
            $source_download_result = O3PO_Arxiv::download_source($this->environment, $arxiv_url_source_prefix, $eprint, $doi_suffix, $post_id );
        }
        if ( !empty( $source_download_result['error'] ) ) {
            $validation_result .= "ERROR: Exception while downloading the source of " . $eprint ." from the arXiv: " . $source_download_result['error'] . "\n";
        } else if (!empty($source_download_result)) {
            $arxiv_source_attach_ids[] = $source_download_result['attach_id'];
            update_post_meta( $post_id, $post_type . '_arxiv_source_attach_ids', $arxiv_source_attach_ids );

            $path_source = $source_download_result['file'];
            $mime_type = $source_download_result['mime_type'];

            $validation_result .= "REVIEW: The source was downloaded successfully from the arXiv to " . $path_source . " and is of mime-type " . $mime_type . "\n";

            $parse_publication_source_result = $this->parse_publication_source($path_source, $mime_type);

            $validation_result .= $parse_publication_source_result['validation_result'];

            $new_author_latex_macro_definitions = $parse_publication_source_result['author_latex_macro_definitions'];
            if(!empty($new_author_latex_macro_definitions))
            {
                    //add slashes before update_post_meta()
                $new_author_latex_macro_definitions_with_slashes = array();
                for($i=0; $i < count($new_author_latex_macro_definitions); $i++)
                {
                    $new_author_latex_macro_definitions_with_slashes[$i] = array_map('addslashes', $new_author_latex_macro_definitions[$i]);
                }
                update_post_meta( $post_id, $post_type . '_author_latex_macro_definitions', $new_author_latex_macro_definitions_with_slashes);
            }
            $old_author_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_orcids');
            $new_author_orcids = $parse_publication_source_result['author_orcids'];
            foreach($new_author_orcids as $key => $value)
                if(empty($value) and !empty($old_author_orcids[$key]))
                    $new_author_orcids[$key] = $old_author_orcids[$key];
            $new_author_affiliations = $parse_publication_source_result['author_affiliations'];
            $new_affiliations = $parse_publication_source_result['affiliations'];

            if(!empty($new_author_orcids))
                update_post_meta( $post_id, $post_type . '_author_orcids', $new_author_orcids );
            if(!empty($new_author_affiliations))
                update_post_meta( $post_id, $post_type . '_author_affiliations', $new_author_affiliations);
            if(!empty($new_affiliations)) {
                update_post_meta( $post_id, $post_type . '_affiliations',  array_map('addslashes', $new_affiliations));
                update_post_meta( $post_id, $post_type . '_number_affiliations', count($new_affiliations) );
            }

            $new_abstract = $parse_publication_source_result['abstract'];
                // Only update the abstract from source if eprint was changed or abstract was empty
            if(!empty($new_abstract) and (empty($abstract) or $eprint_was_changed_on_last_save === "true")) {
                $abstract = $new_abstract;
                update_post_meta( $post_id, $post_type . '_abstract',  addslashes($new_abstract));
            }

            $bbl = $parse_publication_source_result['bbl'];
            if(!empty($bbl)) {
                $validation_result .= "REVIEW: Bibliographic information updated.\n";

                if(!empty($new_author_latex_macro_definitions))
                {
                    $new_author_latex_macro_definitions_without_specials = O3PO_Latex::remove_special_macros_to_ignore_in_bbl($new_author_latex_macro_definitions);

                    $bbl = O3PO_Latex::expand_latex_macros($new_author_latex_macro_definitions_without_specials, $bbl);
                }
                $bbl = addslashes($bbl);
                update_post_meta( $post_id, $post_type . '_bbl', $bbl );
            }
        }

        if ( empty($abstract) )
            $validation_result .= "ERROR: Abstract is empty.\n" ;
        else if ( preg_match('/[<>]/u', $abstract ) )
            $validation_result .= "WARNING: Abstract contains < or > signs. If they are meant to represent math, the formulas should be enclosed in dollar signs and they should be replaced with \\\\lt and \\\\gt respectively (similarly <= and >= should be replaced by \\\\leq and \\\\geq).\n" ;
        if ( empty($abstract_mathml) && preg_match('/[^\\\\]\$.*[^\\\\]\$/u' , $abstract ) )
            $validation_result .= "ERROR: Special characters in the abstract indicate that it contains formulas, but no MathML variant was saved so far. This is normal if meta-data has only just been fetched. If this error does not disappear, please check that all formulas have appropriate LaTeX math mode delimiters.\n";

        $add_licensing_information_result = static::add_licensing_information_to_last_pdf_from_arxiv($post_id);
        if(!empty($add_licensing_information_result))
            $validation_result .= $add_licensing_information_result . "\n";

        $validation_result .= parent::validate_and_process_data($post_id);

        return $validation_result;
    }

        /**
         * Add licensing information to latest arXiv pdf.
         *
         * @since     0.1.0
         * @acesss    public
         * @param     int     $post_id     Id of the post whose meta data is to be saved.
         */
    public function add_licensing_information_to_last_pdf_from_arxiv( $post_id ) {

        if( ini_get('safe_mode') )
            return "WARNING: Adding meta-data to pdfs only works if PHP is not in safe mode"; // See below for why.
        if(php_uname('s')!=='Linux')
            return "WARNING: Adding meta-data to pdfs is currently only supported on Linux";
        $exiftool_command_name = 'exiftool';
        $exiftool_binary_path = exec('command -v ' . $exiftool_command_name);
        if(empty($exiftool_binary_path))
            return "WARNING: Adding meta-data to pdfs requires the external programm exiftool but the exiftool binary was not found via ´" . 'command -v ' . $exiftool_command_name . "´.";;

        $post_type = get_post_type($post_id);
        $arxiv_pdf_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_pdf_attach_ids');
        if(empty($arxiv_pdf_attach_ids))
            return "ERROR: Cannot add licensing information, no pdf attached to post " . $post_id ;
        $path = get_attached_file(end($arxiv_pdf_attach_ids));
        if(empty($path))
            return "ERROR: Cannot add licensing information, no file found for pdf attachment of post " . $post_id;
        $doi = get_post_meta( $post_id, $post_type . '_doi_prefix', true ) . '/' .  get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        if(empty($doi))
            return "ERROR: Cannot add licensing information, DOI not set" ;
        $url = $this->get_journal_property('doi_url_prefix') . $doi;
        $web_statement_url = get_site_url() . '/' . $this->get_publication_type_name_plural() . '/' . get_post_meta( $post_id, $post_type . '_doi_suffix', true ) . '/web-statement/';

        $command  = $exiftool_binary_path;
            /* For more information on the scheme see https://wiki.creativecommons.org/wiki/XMP */
        $command .= ' -XMP-xmpRights:Marked=' . escapeshellarg('True');
        $command .= ' -XMP-xmpRights:UsageTerms=' .  escapeshellarg('This work is published under the ' . $this->get_journal_property('license_name') . ' license ' . $this->get_journal_property('license_url') . ' verify at ' . $web_statement_url);
        $command .= ' -XMP-xmpRights:WebStatement=' . escapeshellarg($web_statement_url);
        $command .= ' -XMP-dc:Rights=' . escapeshellarg($this->get_journal_property('license_type') . ' ' . $this->get_journal_property('license_version'));
        $command .= ' -XMP-cc:license=' . escapeshellarg($this->get_journal_property('license_url'));
        $command .= ' -XMP-cc:attributionURL=' . escapeshellarg($url);
        $command .= ' -XMP-cc:attributionName=' . escapeshellarg(static::get_formated_authors($post_id) . ", " . get_the_title( $post_id ) . ", " . static::get_formated_citation($post_id));

        $command .= ' ' . escapeshellarg($path);

        try
        {
            exec($command, $output, $exit_code); // We can not use escapeshellcmd() here as it escapes even the content of arguments enclosed in '' and this breaks things. In PHP safe mode escapeshellcmd() is forcefully run inside exec(), which is why we cannot add licencing information in safe mode.
        } catch (Exception $e) {
            return "ERROR: Running exiftool resulted in the exception: " + $e->getText();
        }

        if($exit_code != 0)
            return "ERROR: Exiftool (" . $command . ") finished with exit code=" . $exit_code . " for file " . $path . " the output was: " . implode($output," ");
        else {
            $command  = $exiftool_binary_path;
            $command .= ' -delete_original!';
            $command .= ' ' . escapeshellarg($path);
            try
            {
                exec($command, $output, $exit_code);
            } catch (Exception $e) {
                return "ERROR: Running exiftool to delete temporary files resulted in the exception: " + $e->getText();
            }
        }

        return "INFO: Licensing information (" . $this->get_journal_property('license_type') . ' ' . $this->get_journal_property('license_version') . ") and meta-data of " . $path . " added/updated";
    }

        /**
         * Do things when the post is finally published.
         *
         * Is called from save_metabox().
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post that is actually published publicly.
         */
    protected function on_post_actually_published( $post_id ) {

        $settings = O3PO_Settings::instance();

        $validation_result = parent::on_post_actually_published($post_id);

        $post_type = get_post_type($post_id);

        $corresponding_author_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_corresponding_author_has_been_notifed_date', true );
        $corresponding_author_email = get_post_meta( $post_id, $post_type . '_corresponding_author_email', true );
        $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
        $fermats_library = get_post_meta( $post_id, $post_type . '_fermats_library', true );
        $fermats_library_permalink = get_post_meta( $post_id, $post_type . '_fermats_library_permalink', true );
        $fermats_library_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_fermats_library_has_been_notifed_date', true );
        $doi = static::get_doi($post_id);
        $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        $title = get_post_meta( $post_id, $post_type . '_title', true );
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        $post_url = get_permalink( $post_id );

        $executive_board = $settings->get_field_value('executive_board');
        $editor_in_chief = $settings->get_field_value('editor_in_chief');

            // Send Emails about the submission to us
        $to = ($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $this->get_journal_property('publisher_email') );
        $headers = array( 'From: ' . $this->get_journal_property('publisher_email'));

        $subject  = ($this->environment->is_test_environment() ? 'TEST ' : '') .
            O3PO_EmailTemplates::expand('self_notification_subject',
                                        array('[journal]' => $journal,
                                              '[publication_type_name]' => $this->get_publication_type_name()
                                              ));

        $message  = ($this->environment->is_test_environment() ? 'TEST ' : '') .
            O3PO_EmailTemplates::expand('self_notification_body',
                                        array('[journal]' => $journal,
                                              '[publication_type_name]' => $this->get_publication_type_name(),
                                              '[title]' => $title,
                                              '[authors]' => static::get_formated_authors($post_id),
                                              '[url]' => $post_url,
                                              '[doi_url_prefix]' => $this->get_journal_property('doi_url_prefix'),
                                              '[doi]' => $doi,
                                              ));

        $successfully_sent = wp_mail( $to, $subject, $message, $headers);

        if(!$successfully_sent)
            $validation_result .= 'WARNING: Error sending email notification of publication to publisher.' . "\n";
        else
            $validation_result .= 'INFO: Email notification of publication sent to publisher.' . "\n";

            /* We do not send trackbacks for papers as it is against arXiv's policies.
             * Instead we have a doi feed through wich arXiv can automatically
             * pull and set dois.*/
        /*     // Send a trackback to the arXiv */
        /* if(!empty($eprint) && !$this->environment->is_test_environment()) { */
        /*     $eprint_without_version = preg_replace('#v[0-9]+$#u', '', $eprint); */
        /*     $trackback_result = trackback( $this->get_journal_property('arxiv_url_trackback_prefix') . $eprint_without_version , $title, $this->get_trackback_excerpt($post_id), $post_id ); */
        /*     $validation_result .= "INFO: A trackback was sent to " . $this->get_journal_property('arxiv_url_trackback_prefix') . $eprint_without_version . " and the response was: " . $trackback_result . ".\n" ; */
        /* } */

            // Send email notifying authors of publication
        if( empty($corresponding_author_has_been_notifed_date) ) {

            $to = ($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $corresponding_author_email);
            $headers = array( 'Cc: ' . ($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $this->get_journal_property('publisher_email') ), 'From: ' . $this->get_journal_property('publisher_email'));
            $subject  = ($this->environment->is_test_environment() ? 'TEST ' : '') .
                O3PO_EmailTemplates::expand('author_notification_subject',
                                            array('[journal]' => $journal,
                                                  '[publication_type_name]' => $this->get_publication_type_name()
                                                  ));

            $message  = ($this->environment->is_test_environment() ? 'TEST ' : '') .
                O3PO_EmailTemplates::expand('author_notification_body',
                                            array('[journal]' => $journal,
                                                  '[executive_board]' => $executive_board,
                                                  '[editor_in_chief]' => $editor_in_chief,
                                                  '[publisher_email]' => $this->get_journal_property('publisher_email'),
                                                  '[publication_type_name]' => $this->get_publication_type_name(),
                                                  '[title]' => $title,
                                                  '[authors]' => static::get_formated_authors($post_id),
                                                  '[post_url]' => $post_url,
                                                  '[doi_url_prefix]' => $this->get_journal_property('doi_url_prefix'),
                                                  '[doi]' => $doi,
                                                  '[journal_reference]' => static::get_formated_citation($post_id)
                                                  ));

            $successfully_sent = wp_mail( $to, $subject, $message, $headers);

            if($successfully_sent) {
                update_post_meta( $post_id, $post_type . '_corresponding_author_has_been_notifed_date', date("Y-m-d") );
                $validation_result .= 'INFO: Email to corresponding author sent.' . "\n";
            }
            else
            {
                $validation_result .= 'WARNING: Sending email to corresponding author failed.' . "\n";
            }
        }

            // Send email to Fermat's library
        if(($fermats_library === "checked" && empty($fermats_library_has_been_notifed_date))) {

            $fermats_library_permalink = $this->get_journal_property('fermats_library_url_prefix') . $doi_suffix;

            $to = ($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $this->get_journal_property('fermats_library_email'));
            $headers = array( 'Cc: ' . ($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $this->get_journal_property('publisher_email') ), 'From: ' . $this->get_journal_property('publisher_email'));
            $subject  = ($this->environment->is_test_environment() ? 'TEST ' : '') .
                O3PO_EmailTemplates::expand('fermats_library_notification_subject',
                                            array('[journal]' => $journal,
                                                  '[publication_type_name]' => $this->get_publication_type_name()
                                                  ));
            $message  = ($this->environment->is_test_environment() ? 'TEST ' : '') .
                O3PO_EmailTemplates::expand('fermats_library_notification_body',
                                            array('[journal]' => $journal,
                                                  '[publication_type_name]' => $this->get_publication_type_name(),
                                                  '[title]' => $title,
                                                  '[authors]' => static::get_formated_authors($post_id),
                                                  '[post_url]' => $post_url,
                                                  '[doi_url_prefix]' => $this->get_journal_property('doi_url_prefix'),
                                                  '[doi]' => $doi,
                                                  '[fermats_library_permalink]' => $fermats_library_permalink
                                                  ));

            $successfully_sent = wp_mail( $to, $subject, $message, $headers);

            if($successfully_sent) {
                update_post_meta( $post_id, $post_type . '_fermats_library_permalink', $fermats_library_permalink );
                update_post_meta( $post_id, $post_type . '_fermats_library_has_been_notifed_date', date("Y-m-d") );
                $validation_result .= "INFO: Email to fermat's library sent." . "\n";
            }
            else
                $validation_result .= "WARNING: Error sending email to fermat's library." . "\n";
        }

        return $validation_result;
    }

        /**
         * Get an excerpt for trackbaks.
         */
    /* private function get_trackback_excerpt($post_id) { */
    /*     $post_type = get_post_type($post_id); */

    /*     if ( $post_type === $this->get_publication_type_name() ) { */
    /*         $abstract = get_post_meta( $post_id, $post_type . '_abstract', true ); */
    /*         $doi = static::get_doi( $post_id ); */
    /*         $authors = static::get_formated_authors($post_id); */
    /*         $excerpt = ''; */
    /*         $excerpt .= '<h2>' . esc_html($authors) . '</h2>'; */
    /*         $excerpt .= '<a href="' . $this->get_journal_property('doi_url_prefix') . $doi . '">' . $this->get_journal_property('doi_url_prefix') . $doi . '</a>'; */
    /*         $excerpt .= '<p>' . esc_html($abstract) . '</p>'; */
    /*         $excerpt = str_replace(']]>', ']]&gt;', $excerpt); */
    /*         $excerpt = wp_html_excerpt($excerpt, 252, '&#8230;'); */
    /*         return $excerpt; */
    /*     } */
    /*     else */
    /*         return ''; */
    /* } */

        /**
         * Outptus the html formated volume of this publication
         * type.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public static function get_formated_volume_html( $post_id ) {

        $post_type = get_post_type($post_id);
        $volumen_num = get_post_meta( $post_id, $post_type . '_volume', true );

        return '<a href="/volumes/' . esc_attr($volumen_num) . '">volume ' . esc_html($volumen_num) . '</a>';
    }

        /**
         * Echo a howto for the admin panel.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    protected function the_admin_panel_howto( $post_id ) {

        $post_type = get_post_type($post_id);

        $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
		$arxiv_pdf_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_pdf_attach_ids');
        $popular_summary = get_post_meta( $post_id, $post_type . '_popular_summary', true );
        $feature_image_caption = get_post_meta( $post_id, $post_type . '_feature_image_caption', true );
        $feature_image_path = $this->environment->get_feature_image_path($post_id);
        $fermats_library = get_post_meta( $post_id, $post_type . '_fermats_library', true );

        echo "<h4>How to publish in 10 easy steps</h4>" . "\n";
		echo '<table style="width:100%">' . "\n";
        echo '<tr><td>Step 0</td><td>Check on <a href="'. $this->get_journal_property('scholastica_manuscripts_url').'" target="_blank">Scholastica that the manuscript has actually been accepted</a>!</td></tr>' . "\n";
        echo '<tr><td>Step 1</td><td>Put the eprint number in the box below'.(empty($eprint) ? ' and press "Publish"' : " DONE!").'</td></tr>' . "\n";
        if(!empty($arxiv_pdf_attach_ids))
        {
            echo '<tr><td>Step 2</td><td>Review the validation results below.</td></tr>' . "\n";
            echo '<tr><td>Step 3</td><td>Open the <a href="'.esc_attr(wp_get_attachment_url(end($arxiv_pdf_attach_ids))).'" target="_blank">pdf</a> and cross check the content of the following fields:</td></tr>' . "\n";
            echo '<tr><td></td><td>Title</td></tr>' . "\n";
            echo '<tr><td></td><td>Abstract</td></tr>' . "\n";
            echo '<tr><td></td><td>Authors names</td></tr>' . "\n";
            echo '<tr><td></td><td>Affiliations (number, association, spelling)</td></tr>' . "\n";
            echo '<tr><td></td><td>References (total number, DOIs)</td></tr>' . "\n";
            echo '<tr><td>Step 4</td><td>Only if requested by the authors: Tick the opt-in to fermats library box.'.(empty($fermats_library==="checked") ? "" : " DONE!").'</td></tr>';
            echo '<tr><td>Step 5</td><td>If provided by the authors: Copy over the <a href="#' . $post_type. '_popular_summary">popular summary</a>.'.(empty($popular_summary) ? "" : " DONE!").'</td></tr>';
            echo '<tr><td>Step 6</td><td>If provided by the authors: Copy over the <a href="#' . $post_type . '_feature_image_caption">feature image caption</a>.'.(empty($feature_image_caption) ? "" : " DONE!").'</td></tr>';
            echo '<tr><td>Step 7</td><td>If provided by the authors: Edit the feature image to a suitable format (large enough, aspect ration 2:1) and set it as <a href="#postimagediv">feature image</a>.'.(empty($feature_image_path) ? "" : " DONE!").'</td></tr>';
            echo '<tr><td>Step 8</td><td>Click the Update button and address all remaining warnings and errors in the validation results below.</td></tr>';
            echo '<tr><td>Step 9</td><td>Once all is resolved, click edit next to <a href="#submitdiv">Visibility</a> in the Publish box and select Public. Then press the Publish button. '.(get_post_status( $post_id ) !== 'publish' ? "" : " DONE!").'</td></tr>';
        }
        echo '</table>' . "\n";

    }

        /**
         * Echo the eprint part of the admin panel.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    protected function the_admin_panel_eprint( $post_id ) {

        $post_type = get_post_type($post_id);

        $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );

        if( empty( $eprint ) ) $eprint = '';
		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_eprint" class="' . $post_type . '_eprint_label">' . 'Eprint' . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" id="' . $post_type . '_eprint" name="' . $post_type . '_eprint" class="' . $post_type . '_eprint_field required" placeholder="" value="' . esc_attr($eprint) . '">';
		echo '                  <input type="checkbox" name="' . $post_type . '_fetch_metadata_from_arxiv"' . (empty($eprint) ? 'checked' : '' ) . '>Fetch title, authors, and abstract from the arXiv upon next Save/Update';
		echo '			<p>(The arXiv identifier including the version and, for old eprints, the prefix, e.g., 1701.1234v5 or quant-ph/123456v3.)</p>';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the Ferma's library part of the admin panel.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    protected function the_admin_panel_fermats_library ( $post_id ) {

        $post_type = get_post_type($post_id);

        $fermats_library = get_post_meta( $post_id, $post_type . '_fermats_library', true );
		$fermats_library_permalink = get_post_meta( $post_id, $post_type . '_fermats_library_permalink', true );
		$fermats_library_permalink_worked = get_post_meta( $post_id, $post_type . '_fermats_library_permalink_worked', true );
		$fermats_library_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_fermats_library_has_been_notifed_date', true );
		if( empty( $fermats_library ) ) $fermats_library = '' ;
		if( empty( $fermats_library_permalink ) ) $fermats_library_permalink = '' ;
		if( empty( $fermats_library_permalink_worked ) ) $fermats_library_permalink_worked = 'false' ;

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_fermats_library" class="' . $post_type . '_fermats_library_label">' . 'Fermat&#39;s library' . '</label></th>';
		echo '		<td>';
		echo '                  <input type="checkbox" name="' . $post_type . '_fermats_library" value="checked"' . $fermats_library . '>Opt-in for Fermat&#39;s library.' . ( !empty($fermats_library_has_been_notifed_date) ? " Fermat&#39;s library has been automatically notified on " . $fermats_library_has_been_notifed_date . '.' : ' Fermat&#39;s library has not been notified so far.' ) . '<br />';
		echo '			<input ' . (!empty($fermats_library_has_been_notifed_date) ? 'readonly' : '' ) . ' style="width:100%;" type="text" id="' . $post_type . '_fermats_library_permalink" name="' . $post_type . '_fermats_library_permalink" class="' . $post_type . '_fermats_library_permalink_field" placeholder="' . '' . '" value="' . esc_attr($fermats_library_permalink) . '"><br />(If you leave blank the permalink field it is automatically generated when the email is sent and can then no longer be modified.)';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the abstract part of the admin panel.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    protected function the_admin_panel_abstract( $post_id ) {

        $post_type = get_post_type($post_id);

        $abstract = get_post_meta( $post_id, $post_type . '_abstract', true );
		$abstract_mathml = get_post_meta( $post_id, $post_type . '_abstract_mathml', true );

		if( empty( $abstract ) ) $abstract = '';
		if( empty( $abstract_mathml ) ) $abstract_mathml = '';

        echo '	<tr>';
        echo '		<th><label for="' . $post_type . '_abstract" class="' . $post_type . '_abstract_label">' . 'Abstract' . '</label></th>';
		echo '		<td>';
		echo '			<textarea rows="10" style="width:100%;" name="' . $post_type . '_abstract" id="' . $post_type . '_abstract" class="preview_and_mathml required">' . esc_textarea($abstract) . '</textarea><p>(Just like the title, the abstract may contain special characters typed out as é or ç for example. Do not use LaTeX notation for special characters. In contrary, mathematical formulas must be entered in LaTeX notation surrounded by $ signs. Type \\$ for an actual dollar symbol. Beware that the automatic import sometimes confuses a \\langle or \\rangle with a smaller or larger sign and fix these manually. If a formula is detected a live preview and the corresponding MathML code is shown above this help text.)</p>';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the feature image caption part of the amdin panel.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    protected function the_admin_panel_feature_image_caption( $post_id ) {

        $post_type = get_post_type($post_id);

        $feature_image_caption = get_post_meta( $post_id, $post_type . '_feature_image_caption', true );

        if( empty( $feature_image_caption ) ) $feature_image_caption = '' ;

		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_feature_image_caption" class="' . $post_type . '_feature_image_caption_label">' . 'Feature image caption' . '</label></th>';
		echo '		<td>';
		echo '			<textarea rows="6" style="width:100%;" name="' . $post_type . '_feature_image_caption" id="' . $post_type . '_feature_image_caption">' . esc_textarea($feature_image_caption) . '</textarea><p>(Please upload images sent by the authors as feature image via the button on the right. Please add here a caption in case the ' . $this->get_publication_type_name() . ' has a feature image.)</p>';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the popular summary part of the admin panel.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    protected function the_admin_panel_popular_summary( $post_id ) {

        $post_type = get_post_type($post_id);

        $popular_summary = get_post_meta( $post_id, $post_type . '_popular_summary', true );

		if( empty( $popular_summary ) ) $popular_summary = '' ;

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_popular_summary" class="' . $post_type . '_popular_summary_label">' . 'Popular summary' . '</label></th>';
		echo '		<td>';
		echo '			<textarea rows="6" style="width:100%;" name="' . $post_type . '_popular_summary" id="' . $post_type . '_popular_summary">' . esc_textarea($popular_summary) . '</textarea><p>(Popular summary if provided by the authors.)</p>';
		echo '		</td>';
		echo '	</tr>';
    }

    /**
     * Echo the arXiv part of the admin panel.
     *
     * @since     0.1.0
     * @access    public
     * @param     int     $post_id     Id of the post.
     */
    protected function the_admin_panel_arxiv( $post_id ) {

        $post_type = get_post_type($post_id);

        $arxiv_fetch_results = get_post_meta( $post_id, $post_type . '_arxiv_fetch_results', true );
		$arxiv_pdf_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_pdf_attach_ids');
		$arxiv_source_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_source_attach_ids');

		if ( !empty($arxiv_fetch_results) ) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_arxiv_fetch_results" class="' . $post_type . '_arxiv_fetch_results_label">' . 'ArXiv fetch result' . '</label></th>';
			echo '		<td>';
			echo '			<textarea rows="' . (mb_substr_count( $arxiv_fetch_results, "\n" )+1) . '" cols="65" readonly>' . esc_textarea($arxiv_fetch_results) . '</textarea><p>(The result of fetching metadata from the arXiv.)</p>';
			echo '		</td>';
			echo '	</tr>';
		}

        echo '	<tr>';
        echo '		<th><label for="' . $post_type . '_arxiv_pdf_ids" class="' . $post_type . '_arxiv_pdf_ids_label">' . 'PDFs from arXiv' . '</label></th>';
        echo '		<td>';
        echo '                  <input type="checkbox" id="' . $post_type . '_download_arxiv_pdf" name="' . $post_type . '_download_arxiv_pdf"><label for="' . $post_type . '_download_arxiv_pdf">Download the pdf from the arXiv again upon next Save/Update.</label>';
        if ( !empty($arxiv_pdf_attach_ids) ) {
			foreach ($arxiv_pdf_attach_ids as $arxiv_pdf_attach_id) {
				echo '<p>ID: <a href="post.php?post=' . $arxiv_pdf_attach_id . '&amp;action=edit" target="_blank">' . $arxiv_pdf_attach_id . '</a> Url: <a href="' . wp_get_attachment_url( $arxiv_pdf_attach_id ) . '" target="_blank">' . wp_get_attachment_url( $arxiv_pdf_attach_id ) . "</a></p>\n";
			}
        }
        echo '		</td>';
        echo '	</tr>';

        echo '	<tr>';
        echo '		<th><label for="' . $post_type . '_arxiv_source_ids" class="' . $post_type . '_arxiv_source_ids_label">' . 'Source files from arXiv' . '</label></th>';
        echo '		<td>';
        echo '                  <input type="checkbox" id="' . $post_type . '_download_arxiv_source" name="' . $post_type . '_download_arxiv_source"><label for="' . $post_type . '_download_arxiv_source">Download the source from the arXiv again upon next Save/Update.</label>';
        if ( !empty($arxiv_source_attach_ids) ) {
			foreach ($arxiv_source_attach_ids as $arxiv_source_attach_id) {
				echo '<p>ID: <a href="post.php?post=' . $arxiv_source_attach_id . '&amp;action=edit" target="_blank">' . $arxiv_source_attach_id . '</a> Url: <a href="' . wp_get_attachment_url( $arxiv_source_attach_id ) . '" target="_blank">' . wp_get_attachment_url( $arxiv_source_attach_id ) . "</a></p>\n";
			}
        }
        echo '		</td>';
        echo '	</tr>';

    }

        /**
         * Get the url of the latest arXiv pdf.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public function get_last_arxiv_pdf_url( $post_id ) {

        $post_type = get_post_type($post_id);

        $arxiv_pdf_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_pdf_attach_ids');
        if ( empty($arxiv_pdf_attach_ids) )
            return '';
        $last_url = '';
        foreach ($arxiv_pdf_attach_ids as $arxiv_pdf_attach_id) {
            $last_url = wp_get_attachment_url( $arxiv_pdf_attach_id );
        }

        return $last_url;
    }

        /**
         * Get the path of the last arXiv pdf.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public static function get_last_arxiv_pdf_path( $post_id ) {
        $post_type = get_post_type($post_id);

        $arxiv_pdf_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_pdf_attach_ids');
        if ( empty($arxiv_pdf_attach_ids) )
            return '';
        $last_path = '';
        foreach ($arxiv_pdf_attach_ids as $arxiv_pdf_attach_id) {
            $last_path = get_attached_file( $arxiv_pdf_attach_id );
        }

        return $last_path;
    }

        /**
         * Get the path of the fulltext pdf.
         *
         * Overwerites a method in the base class.
         *
         * @since 0.2.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public function get_fulltext_pdf_path( $post_id ) {

        return static::get_last_arxiv_pdf_path( $post_id );
    }

        /**
         * Get the url of the last arXiv soruce.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    protected static function get_last_arxiv_source_url( $post_id ) {
        $post_type = get_post_type($post_id);

        $arxiv_source_attach_ids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_arxiv_source_attach_ids');
        if ( empty($arxiv_source_attach_ids) )
            return '';
        $last_url = '';
        foreach ($arxiv_source_attach_ids as $arxiv_source_attach_id) {
		$last_url = wp_get_attachment_url( $arxiv_source_attach_id );
        }

        return $last_url;
    }

        /**
         * Decide whether or not the Fermat's library permalink should be shown.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public static function show_fermats_library_permalink( $post_id ) {

        $post_type = get_post_type($post_id);

        $fermats_library = get_post_meta( $post_id, $post_type . '_fermats_library', true );
        $fermats_library_permalink = get_post_meta( $post_id, $post_type . '_fermats_library_permalink', true );
        $fermats_library_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_fermats_library_has_been_notifed_date', true );
        $fermats_library_permalink_worked = get_post_meta( $post_id, $post_type . '_fermats_library_permalink_worked', true );

        if( $fermats_library === 'checked' and $fermats_library_permalink_worked === 'true' and !empty($fermats_library_has_been_notifed_date) and !empty($fermats_library_permalink) )
            return true;
        if ( $fermats_library === 'checked' and !empty($fermats_library_has_been_notifed_date) and !empty($fermats_library_permalink) )
            $response = wp_remote_get( $fermats_library_permalink );

        if ( !empty($response) && wp_remote_retrieve_response_code($response) == 200 ) {
            update_post_meta( $post_id, $post_type . '_fermats_library_permalink_worked', "true" );
            return true;
        }
        else
            return false;
    }

        /**
         * Get the content for for the rss feed.
         *
         * To be added to the 'the_content_feed' and 'the_excerpt_rss' filter.
         *
         * @since     0.1.0
         * @access    public
         * @param     string     $content     Content to be ammended.
         */
    public function get_feed_content( $content ) {

        global $post;
        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if ( $post_type === $this->get_publication_type_name() ) {
            $old_content = $content;
            $abstract = get_post_meta( $post_id, $post_type . '_abstract', true );
            $post_content = get_post_field('post_content', $post_id);
            $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
            $doi = static::get_doi( $post_id );
            $content = '';
            $content .= '<p>' . static::get_formated_citation($post_id) . '</p>';
            $content .= '<a href="' . $this->get_journal_property('doi_url_prefix') . $doi . '">' . $this->get_journal_property('doi_url_prefix') . $doi . '</a>';
            $content .= '<p class="abstract">' . esc_html($abstract) . '</p>';
            if(!empty($post_content))
                $content .= '<p class="further-content">' . $post_content . '</p>';
            #$content .= $old_content;

            $content = O3PO_Latex::expand_cite_to_html($content, $bbl);
            return $content;
        }
        else
            return $content;

    }

        /**
         * Get the text basis for the text part of the excerpt
         *
         * In this case we return the abstract.
         *
         * @since 0.3.1
         * @access public
         * @param int $post_id The ID of the post.
         * @return The basis for the text of the excerpt.
         */
    public function get_basis_for_excerpt( $post_id ) {

        $post_type = get_post_type($post_id);

        return get_post_meta( $post_id, $post_type . '_abstract', true );
    }


        /**
         * Get the pretty permalink of the pdf associated with a post.
         *
         * For Google Scholar the full text must be available in a
         * subdirectory of the abstract page and anyway it is nice to have a
         * consistent api for downloading the fulltext pdf. The following
         * functions are added to the 'init' and 'parse_request' hooks and
         * thereby make any url of the form [post-type-name]/<doi-suffix>/pdf/ return
         * the associated pdf.
         *
         * @since     0.1.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public function get_pdf_pretty_permalink( $post_id ) {

        $post_type = get_post_type($post_id);
        if ( $post_type !== $this->get_publication_type_name() || empty(static::get_last_arxiv_pdf_url( $post_id )) )
            return '';

        return get_permalink( $post_id ) . "pdf/";
    }

        /**
         * Add a /pdf endpoint for serving the full text pdf.
         *
         * To be added to the 'init' action.
         *
         * @since    0.1.0
         * @access   public
         */
    public function add_pdf_endpoint() {

        add_rewrite_endpoint( 'pdf', EP_PERMALINK | EP_PAGES );

    }


        /**
         * Handle request to the /pdf endpoint for serving the full text pdf.
         *
         * For PHPUnit testing this function needs a way to suppress the
         * calls to exit(), which are otherwise necessary to prevent other
         * parts of WordPress from adding additional output to the PDF
         * we want to deliver. This can be done with the optional parameter
         * $do_not_exit which defaults to false.
         *
         * To be added to the 'parse_request' action.
         *
         * @since     0.1.0
         * @access    public
         * @param     WP_Query   $wp_query    The WP_Query to be handled.
         * @param     boolean    $do_not_exit Prevent this function from calling exit() and return instead.
         */
    public function handle_pdf_endpoint_request( $wp_query, $do_not_exit=false ) {

        if ( !isset( $wp_query->query_vars[ 'pdf' ] ) )
            return;
        if ( !isset($wp_query->query_vars[ 'post_type' ]) or $wp_query->query_vars[ 'post_type' ] !== $this->get_publication_type_name() or !isset($wp_query->query_vars[ $this->get_publication_type_name() ]) )
            return;

        $post_id = url_to_postid( '/' . $this->get_publication_type_name_plural() . '/' . $wp_query->query_vars[ $this->get_publication_type_name() ] . '/');
        if(empty($post_id))
        {
            header('Content-Type: text/plain');
            echo "ERROR: post_id is empty";
            if(!$do_not_exit)
                exit(); // @codeCoverageIgnore
            else
                return;
        }
        $post_type = get_post_type($post_id);
        $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        if(empty($doi_suffix))
        {
            header('Content-Type: text/plain');
            echo "ERROR: doi_suffix is empty";
            if(!$do_not_exit)
                exit(); // @codeCoverageIgnore
            else
                return;
        }

        $file_path = static::get_last_arxiv_pdf_path($post_id);
        if(empty($file_path))
        {
            header('Content-Type: text/plain');
            echo "ERROR: file_path is empty";
            if(!$do_not_exit)
                exit(); // @codeCoverageIgnore
            else
                return;
        }

            /* We deliver the pdf file through an output buffer
             * so we can do a lengthy computation afterwards. */
        ob_start();
        ##################
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . esc_html($doi_suffix) . '.pdf"' );//always return the same file name even if local revision number has changed
        readfile($file_path);
        ##################
        header('Content-Length: '.ob_get_length());
        ob_end_flush();
        flush();

            /* We do this because now we can then submit the pdf for
             * indexing to relevanssi. */
        $settings = O3PO_Settings::instance();
        $relevanssi_index_pdfs_asynchronously = $settings->get_field_value('relevanssi_index_pdfs_asynchronously');
        if($relevanssi_index_pdfs_asynchronously === 'checked')
        {
            ignore_user_abort(true);
            set_time_limit(30);
            $arxiv_pdf_attach_ids = static::get_post_meta_field_containing_array($post_id, $post_type . '_arxiv_pdf_attach_ids');
            $last_arxiv_pdf_attach_id = end($arxiv_pdf_attach_ids);
            if(!empty($last_arxiv_pdf_attach_id))
            {
                O3PO_Relevanssi::index_pdf_attachment_if_not_already_done( $last_arxiv_pdf_attach_id, false );
            }
        }

        if(!$do_not_exit)
            exit(); // @codeCoverageIgnore
        else
            return;
    }

       /**
        * Add /web-statement end point for serving a web statement of the licence.
        *
        * To be added to the 'init' action.
        *
        * @since 0.1.0
        */
    public static function add_web_statement_endpoint() {

        add_rewrite_endpoint( 'web-statement', EP_PERMALINK | EP_PAGES );

    }

       /**
        * Handle requests to the /web-statement end point for serving a web statement of the licence.
        * For PHPUnit testing this function needs a way to suppress the
        * calls to exit(), which are otherwise necessary to prevent other
        * parts of WordPress from adding additional output to the web statement
        * we want to deliver. This can be done with the optional parameter
        * $do_not_exit which defaults to false.
        * To be added to the 'parse_request' action.
        *
        * @since     0.1.0
        * @access    public
        * @param     WP_Query   $wp_query    The WP_Query to be handled.
        * @param     boolean    $do_not_exit Prevent this function from calling exit() and return instead.
        * */
    public function handle_web_statement_endpoint_request( $wp_query, $do_not_exit=false ) {

        if ( !isset( $wp_query->query_vars[ 'web-statement' ] ) )
            return;
        if ( !isset($wp_query->query_vars[ 'post_type' ]) or $wp_query->query_vars[ 'post_type' ] !== $this->get_publication_type_name())
            return;

        $post_id = url_to_postid( '/' . $this->get_publication_type_name_plural() . '/' . $wp_query->query_vars[ $this->get_publication_type_name() ] . '/');
        if ( empty($post_id) )
        {
            header('Content-Type: text/plain');
            echo "ERROR: post_id is empty";
            if(!$do_not_exit)
                exit(); // @codeCoverageIgnore
            else
                return;
        }
        $post_type = get_post_type($post_id);
        $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        if(empty($doi_suffix))
        {
            header('Content-Type: text/plain');
            echo "ERROR: doi_suffix is empty";
            if(!$do_not_exit)
                exit(); // @codeCoverageIgnore
            else
                return;
        }

        $file_path = static::get_last_arxiv_pdf_path($post_id);
        if ( empty($file_path) )
        {
            header('Content-Type: text/plain');
            echo "ERROR: file_path is empty";
            if(!$do_not_exit)
                exit(); // @codeCoverageIgnore
            else
                return;
        }

        $sha1 = get_transient($post_id . '_web_statement_sha1');
        if(empty($sha1))
        {
            $sha1 = mb_strtoupper(O3PO_Utility::base_convert_arbitrary_precision(sha1_file($file_path), 16, 32));
            set_transient($post_id . '_web_statement_sha1', $sha1, 10*60);
        }

        header('Content-Type: text/html');
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
        echo '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
        echo '<head></head>' . "\n";
        echo '<body>' . "\n";
        echo '<span about="urn:sha1:' . $sha1 . '">' . "\n";
        echo $doi_suffix . '.pdf' . ' is licensed under' . "\n";
        echo '<a about="urn:sha1:' . $sha1 . '" rel="license" href="' . esc_attr($this->get_journal_property('license_url')) . '">' . esc_html($this->get_journal_property('license_name')) . '</a>' . "\n";
        echo '</span>' . "\n";
        echo '</body>' . "\n";
        echo '</html>' . "\n";

        if(!$do_not_exit)
            exit(); // @codeCoverageIgnore
        else
            return;
    }

        /**
        * Add /arxiv_paper_doi_feed end point for serving a feed of recent papers for the arXiv.
        *
        * To be added to the 'init' action.
        *
        * @since     0.1.0
        * @access    public
        */
    public static function add_axiv_paper_doi_feed_endpoint() {

        $settings = O3PO_Settings::instance();
        $endpoint_suffix = $settings->get_field_value('arxiv_paper_doi_feed_endpoint');

        add_rewrite_endpoint( $endpoint_suffix, EP_ROOT );

    }

        /**
        * Handle requests to the arxiv_paper_doi_feed endpoint for serving a feed of recent papers for the arXiv.
        *
        * To be added to the 'parse_request' action.
        *
        * @since    0.1.0
        * @access   public
        * @param    WP_Query   $wp_query   The WP_Query to be handled.
        * @param     boolean   $do_not_exit Prevent this function from calling exit() and return instead.
        */
    public function handle_arxiv_paper_doi_feed_endpoint_request( $wp_query, $do_not_exit=false ) {

        $settings = O3PO_Settings::instance();
        $endpoint_suffix = $settings->get_field_value('arxiv_paper_doi_feed_endpoint');
        $endpoint_days = $settings->get_field_value('arxiv_paper_doi_feed_days');


        if ( !isset( $wp_query->query_vars[ $endpoint_suffix ] ) )
            return;

        $date=getdate();

        header('Content-Type: text/xml');
        header("Content-Disposition: inline; filename=arxiv_doi_feed.xml" );
        $identifier = $this->get_journal_property('arxiv_doi_feed_identifier');
        echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n";
        echo '<preprint xmlns="http://arxiv.org/doi_feed" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" identifier="' . $identifier . '" version="DOI SnappyFeed v1.0" xsi:schemaLocation="http://arxiv.org/doi_feed http://arxiv.org/schemas/doi_feed.xsd">' . "\n";
        echo '  <date year="' . $date['year'] . '" month="' . $date['mon'] . '" day="' . $date['mday'] . '"/>' . "\n";

        query_posts(array('post_status' => 'publish', 'post_type' => $this->get_publication_type_name(), 'date_query'    => array(
                              'column'  => 'post_date',
                              'after'   => '- ' . $endpoint_days . ' days'                                                                         ) ));
        while(have_posts()) {
            the_post();
            $post_id = get_the_ID();
            $post_type = get_post_type($post_id);
            $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
            $eprint_without_version = preg_replace('#v[0-9]+$#u', '', $eprint);
            $citation = rtrim(static::get_formated_citation($post_id), '.');
            $doi = static::get_doi($post_id);
            echo '  <article doi="' . $doi .'" preprint_id="arXiv:' . $eprint_without_version . '" journal_ref="' . $citation . '"/>' . "\n";
        }
        wp_reset_query();


        echo '</preprint>' . "\n";

        if(!$do_not_exit)
            exit(); // @codeCoverageIgnore
        else
            return;
    }

        /**
         * Output meta tags describing this publication type.
         *
         * Overwrites and calls function of same name in O3PO_Publication_Type.
         *
         * To be added to the 'wp_head' action.
         *
         * @since     0.1.0
         * @access    public
         */
    public function add_dublin_core_and_highwire_press_meta_tags() {

        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        if ( !is_single() || $post_type !== $this->get_publication_type_name())
            return;

        $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );

        parent::add_dublin_core_and_highwire_press_meta_tags();

        $pdf_url = static::get_pdf_pretty_permalink($post_id);

            // Highwire Press tags
        if(!empty($pdf_url)) echo '<meta name="citation_pdf_url" content="' . $pdf_url . '">'."\n";
        if(!empty($eprint)) echo '<meta name="citation_arxiv_id" content="' . $eprint . '">'."\n";

    }

        /**
         * Parse the source files.
         *
         * Depending on the manuscript we either got a single uncompressed .tex file
         * or a tar.gz archive from the arXivm which we have to extract and then analyse.
         *
         * @since   0.3.0
         * @access  private
         * @param   string     $path_source    Path to the source file.
         * @param   string     $mime_type      Mime type of the source file.
         */
    private function parse_publication_source( $path_source, $mime_type )
    {
        $phar_tar = null;
        $path_folder = null;
        $validation_result = '';
        $bbl = '';
        $abstract = '';
        $new_author_orcids = array();
        $new_author_affiliations = array();
        $new_affiliations = array();
        $new_author_latex_macro_definitions = array();

        try {
            if ( preg_match('#text/.*tex#u', $mime_type) && mb_substr($path_source, -4) === '.tex' ) // We got a single file
                $source_files = array(new SplFileInfo($path_source));
            else if ( preg_match('#application/.*(tar|gz|gzip)#u', $mime_type) && mb_substr($path_source, -7) === '.tar.gz' ) { // We got an archive

                    /**
                     * PHP cannot correctly handle file names with dots, see this bug: https://bugs.php.net/bug.php?id=58852
                     * Thus, if the filename contains dots, we need to copy the source file to a new, not already exisiting file without additional dots in the name.
                     * In the following we rely on the fact that we know that the path ends with '.tar.gz'.
                     */
                try
                {
                    $basename = pathinfo($path_source, PATHINFO_BASENAME);
                    $basename_without_tar_gz = mb_substr($basename, 0, -7);
                    $path_source_copy_to_unlik_later = Null;
                    if(strpos($basename_without_tar_gz, '.') !== false)
                    {
                        $orig_path_source = $path_source;
                        $extra = 0;
                        while(file_exists($path_source)) {
                            $path_source = pathinfo($path_source, PATHINFO_DIRNAME) . '/' . str_replace('.', '_', $basename_without_tar_gz) . ($extra === 0 ? '' : '-' . $extra) . '.tar.gz';

                            $extra += 1;
                        }
                        copy($orig_path_source, $path_source);
                        $path_source_copy_to_unlik_later = $path_source;
                    }

                        //Unpack
                    $path_tar = preg_replace('/\.gz$/u', '', $path_source);
                    $path_folder = preg_replace('/\.tar$/u', '', $path_tar) . '_extracted/';

                    $phar_gz = new PharData($path_source);
                    $phar_gz->decompress(); // *.tar.gz -> *.tar
                    $phar_tar = new PharData($path_tar);
                    $phar_tar->extractTo($path_folder);

                } finally {
                    if(!empty($path_source_copy_to_unlik_later))
                        unlink($path_source_copy_to_unlik_later);
                }

                $source_files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator($path_folder, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            } else {
                $validation_result .= "ERROR: Extension of source file " . $path_source . " and mime-type " . $mime_type . " do not match or are neither .tex nor .tar.gz.\n";
            }

                //Loop over the source files
            foreach($source_files as $entry ) {
                if($entry->isFile() && ( mb_substr($entry->getPathname(), -4) === '.bbl' || mb_substr($entry->getPathname(), -4) === '.tex' ) )
                {
                    $filecontents = $this->environment->file_get_contents_utf8($entry->getPathname());
                    $filecontents_without_comments = preg_replace('#(?<!\\\\)%.*#u', '', $filecontents);//remove all comments

                        //Extract all the user defined tex macros and collect them
                    $author_latex_macro_definitions_from_this_file = O3PO_Latex::extract_latex_macros($filecontents_without_comments);
                    if(!empty($author_latex_macro_definitions_from_this_file))
                    {
                        $new_author_latex_macro_definitions = array_merge_recursive($new_author_latex_macro_definitions, $author_latex_macro_definitions_from_this_file);
                    }

                        //Look for bibliographies and extract them
                    $thisbbl = O3PO_Latex::extract_bibliographies($filecontents_without_comments);//we search the file with comments removed to not accidentially pic up a commented out bibliography
                    if(!empty($thisbbl)) {
                        $validation_result .= "REVIEW: Found BibTeX or manually formated bibliographic information in " . $entry->getPathname() . ".\n";
                        if(!empty($bbl))
                            $bbl .= "\n";
                        $bbl .= $thisbbl;
                    } else if( mb_substr($entry->getPathname(), -4) === '.bbl' && mb_strpos( $filecontents, 'biblatex auxiliary file' ) !== false )  {
                        if(!empty($bbl))
                            $bbl .= "\n";
                        $bbl .= $filecontents . "\n";//here comments must be preserved as they contain clues for parsing
                        $validation_result .= "REVIEW: Found BibLaTeX formated bibliographic information in " . $entry . "\n";
                    }
                }
            }

            $new_author_orcids = array();
            $new_author_urls = array();
            $new_author_affiliations = array();
            $new_affiliations = array();
            $author_number = -1;
            $authors_since_last_affiliation = array();

            foreach($source_files as $entry ) {
                if($entry->isFile() && ( mb_substr($entry->getPathname(), -4) === '.tex' ) )
                {
                    $filecontents = $this->environment->file_get_contents_utf8($entry->getPathname());
                    $filecontents_without_comments = preg_replace('#(?<!\\\\)%.*#u', '', $filecontents);//remove all comments

                        // Extract author, affiliation and similar information from the source
                    preg_match_all('#\\\\(author|affiliation|affil|address|orcid|homepage)\s*([^{]*)\s*(?=\{((?:[^{}]++|\{(?3)\})*)\})#u', $filecontents_without_comments, $author_info);//matches balanced parenthesis (Note the use of (?3) here!) to test changes go here https://regex101.com/r/bVHadc/1
                    if(!empty($author_info[0]) && !empty($author_info[1]))
                    {
                        if($author_number !== -1)
                            $validation_result .= "WARNING: Found affiliations, ORCIDs, or author URLs in more than one file. Please check.\n";

                        if(in_array('author', $author_info[1]) or in_array('affiliation', $author_info[1]) or in_array('affil', $author_info[1]) or in_array('address', $author_info[1]))
                            $validation_result .= "REVIEW: Author and affiliations data updated from arxiv source. Please check.\n";
                        if(in_array('orcid', $author_info[1]))
                            $validation_result .= "REVIEW: ORCID data updated from arxiv source. Please check.\n";
                        if(in_array('homepage', $author_info[1]))
                            $validation_result .= "REVIEW: Author homepage data updated from arxiv source. Please check.\n";

                        $was_affiliation_since_last_author = false;
                        for($x = 0; $x < count($author_info[1]) ; $x++) {
                            if( $author_info[1][$x] === 'author')
                            {
                                $author_number += 1;

                                    /* It is difficult to extract the author name from the source
                                     * as the LaTeX \author macro gives no clue about what is the
                                     * given name and what is the surname. We hence ignore
                                     * $author_info[3][$x] for now and rely on the information
                                     * fetched from the abstract page of the arXiv.*/

                                if($was_affiliation_since_last_author)
                                    $authors_since_last_affiliation = array();
                                $authors_since_last_affiliation[] = $author_number;

                                    // we interpret the optional argument of \author[1,2]{Foo Bar} as the list of affiliation numbers for compatibility with autblk
                                if(!empty($author_info[2][$x]) )
                                {
                                    preg_match_all('/\[([0-9,]*)\]/u', $author_info[2][$x], $affiliations_from_optional_argument);
                                    if(!empty($affiliations_from_optional_argument[1][0]))
                                        $new_author_affiliations[$author_number] = $affiliations_from_optional_argument[1][0];
                                }
                            }
                            else if( $author_info[1][$x] === 'orcid' and !empty($author_info[3][$x]))
                                $new_author_orcids[$author_number] = $author_info[3][$x];
                            else if( $author_info[1][$x] === 'homepage' and !empty($author_info[3][$x]))
                                $new_author_urls[$author_number] = $author_info[3][$x];
                            else if( $author_info[1][$x] === 'affiliation' or $author_info[1][$x] === 'affil' or $author_info[1][$x] === 'address')
                            {
                                $current_affiliation = trim($author_info[3][$x], ' {}');

                                $current_affiliation = O3PO_Latex::expand_latex_macros($new_author_latex_macro_definitions, $current_affiliation);
                                $current_affiliation = O3PO_Latex::latex_to_utf8_outside_math_mode($current_affiliation);
                                $current_affiliation = O3PO_Latex::normalize_whitespace_and_linebreak_characters($current_affiliation);

                                if( $author_info[1][$x] === 'affiliation' or $author_info[1][$x] === 'address' )
                                {
                                    if(!in_array($current_affiliation, $new_affiliations))
                                        $new_affiliations[] = $current_affiliation;

                                    if(empty($author_info[2][$x])) # if there is no optional argument add this affiliation to preceding authors
                                    {
                                        foreach($authors_since_last_affiliation as $author_number_since_last_affiliation)
                                        {
                                            if(empty($new_author_affiliations[$author_number_since_last_affiliation]))
                                                $new_author_affiliations[$author_number_since_last_affiliation] = '';
                                            else
                                                $new_author_affiliations[$author_number_since_last_affiliation] .= ',';
                                            $new_author_affiliations[$author_number_since_last_affiliation] .= (array_search($current_affiliation, $new_affiliations , true)+1);
                                        }
                                        $was_affiliation_since_last_author = true;
                                    }
                                }
                                elseif( $author_info[1][$x] === 'affil')
                                {
                                    preg_match('/[0-9]*/u', $author_info[2][$x], $affiliation_symb_from_optional_argument);
                                    if(!empty($affiliation_symb_from_optional_argument[0]) && is_int($affiliation_symb_from_optional_argument[0]))
                                        $current_affiliation_num = intval($affiliation_symb_from_optional_argument[0])-1;
                                    else
                                        $current_affiliation_num = count($new_affiliations);

                                    $new_affiliations[$current_affiliation_num] = $current_affiliation;
                                }
                            }
                        }
                    }

                        //Look for abstracts and extract them
                    $thisabstract = O3PO_Latex::extract_abstracts($filecontents_without_comments);//we search the file with comments removed to not accidentially pic up a commented out abstract
                    $thisabstract = O3PO_Latex::expand_latex_macros($new_author_latex_macro_definitions, $thisabstract);
                    $thisabstract = O3PO_Latex::latex_to_utf8_outside_math_mode($thisabstract, false);
                    $thisabstract = O3PO_Latex::normalize_whitespace_and_linebreak_characters($thisabstract, false, true);
                    $thisabstract = O3PO_Latex::remove_font_changing_commands($thisabstract);
                    if(!empty($thisabstract))
                    {
                        if(!empty($abstract))
                            $abstract .= "\n\n";
                        $abstract .= $thisabstract;
                    }
                }
            }
        } catch (Exception $e) {
            $validation_result .= "ERROR: While processing the source files an exception occurred: '" . $e->getMessage() . "' in " . $e->getFile() . ":" . $e->getLine() . "\n";
        } finally {
            try {
                if(!empty($phar_tar))
                    unlink($path_tar);
                if(!empty($path_folder))
                    $this->environment->save_recursive_remove_dir($path_folder, $path_folder);
            } catch (Exception $e) {
                $validation_result .= "ERROR: While processing the source files an exception occurred: " . $e->getMessage() . "\n";
            }
        }
        return array(
            'validation_result' => $validation_result,
            'author_latex_macro_definitions' => $new_author_latex_macro_definitions,
            'author_orcids' => $new_author_orcids,
            'author_affiliations' => $new_author_affiliations,
            'affiliations' => $new_affiliations,
            'bbl' => $bbl,
            'abstract' => $abstract,
                     );
    }

        /**
         * Construct the content.
         *
         * Here we output dynamic information about the publication
         * alongside the standard content.
         *
         * To be added to the 'the_content' filter.
         *
         * @since     0.1.0
         * @access    public
         * @param     string    $content     Content to be filtered.
         */
    public function get_the_content( $content ) {

        global $post;

        $settings = O3PO_Settings::instance();

        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if ( get_post_type($post_id) === $this->get_publication_type_name() ) {
            $old_content = $content;
            $content = '';

            $content .= '<header class="entry-header">';
            if($settings->get_field_value('page_template_for_publication_posts')==='checked')
                $content .= '<h1 class="entry-title title citation_title"><a href="#">' . esc_html ( get_the_title( $post_id ) ) . '</a></h1>';
            $content .= '<p class="authors citation_author">';
            $content .= $this->get_formated_authors_html( $post_id );
            $content .= '</p>';
            $content .= '<p class="affiliations">';
            $content .= $this->get_formated_affiliations_html( $post_id );
            $content .= '</p>';
            $content .= '<table class="meta-data-table">';
            $content .= '<tr><td>Published:</td><td>' . esc_html($this->get_formated_date_published( $post_id )) .  ', ' . $this->get_formated_volume_html($post_id) . ', page ' . esc_html(get_post_meta( $post_id, $post_type . '_pages', true )) . '</td></tr>';
            $content .= '<tr><td>Eprint:</td><td><a href="' . esc_attr($settings->get_field_value('arxiv_url_abs_prefix') . get_post_meta( $post_id, $post_type . '_eprint', true ) ) . '">arXiv:' . esc_html(get_post_meta( $post_id, $post_type . '_eprint', true )) . '</a></td></tr>';
            $doi = get_post_meta( $post_id, $post_type . '_doi_prefix', true ) . '/' .  get_post_meta( $post_id, $post_type . '_doi_suffix', true );
            $content .= '<tr><td>Doi:</td><td><a href="' . esc_attr($settings->get_field_value('doi_url_prefix') . $doi) . '">' . esc_html($settings->get_field_value('doi_url_prefix') . $doi ) . '</a></td></tr>';
            $content .= '<tr><td>Citation:</td><td>' . esc_html($this->get_formated_citation($post_id)) . '</td></tr>';
            $content .= '</table>';

            $content .= '<div class="publication-action-buttons">';
            $content .= '<a href="' . esc_url($this->get_pdf_pretty_permalink($post_id)) . '" ><button id="fulltext" class="btn-theme-primary pirate-forms-submit-button" type="button">Get full text pdf</button></a>';
            if(!empty($settings->get_field_value('arxiv_vanity_url_prefix')))
            {
                $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
                $eprint_without_version = preg_replace('#v[0-9]+$#u', '', $eprint);
                $arxiv_vanity_url = $settings->get_field_value('arxiv_vanity_url_prefix') . $eprint_without_version;
                $content .= '<a href="' . esc_url($arxiv_vanity_url) . '" ><button id="arxiv-vanity" class="btn-theme-primary pirate-forms-submit-button" type="button">Read on arXiv Vanity</button></a>';
            }
            if($this->show_fermats_library_permalink($post_id))
            {
                $fermats_library_permalink = get_post_meta( $post_id, $post_type . '_fermats_library_permalink', true );
                $content .= '<a href="' . esc_url($fermats_library_permalink) . '" ><button id="fermats-library" class="btn-theme-primary pirate-forms-submit-button" type="button">Comment on Fermat\'s library</button></a>';
            }
            $content .= '</div>';

            if(!empty($settings->get_field_value('scirate_url_abs_prefix')))
            {
                $scirate_url = $settings->get_field_value('scirate_url_abs_prefix') . get_post_meta( $post_id, $post_type . '_eprint', true );
                $content .= '<p>Find this '. $post_type . ' interesting or want to discuss? <a href="' . esc_attr($scirate_url) . '">Scite or leave a comment on SciRate</a>.<p>';
            }

            $content .= '</header>';
            $content .= '<div class="entry-content">';

            $abstract_header = $settings->get_field_value('page_template_abstract_header');
            if(!empty($abstract_header)) {
                $content .= '<h3 class="abstract-header" >' . esc_html($abstract_header) . '</h3>';
            }
            $content .= '<p class="abstract">';
            $content .= nl2br(esc_html( get_post_meta( $post_id, $post_type . '_abstract', true )) );
            $content .= '</p>';

            $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
            $content = O3PO_Latex::expand_cite_to_html($content, $bbl);
            if ( has_post_thumbnail( ) ) {
                $content .= '<div class="featured-image-box">';
                $content .= '<div style="float:left; padding-right: 1rem; padding-bottom: 1rem">';
                $content .= get_the_post_thumbnail($post_id);
                $content .= '</div>';
                $feature_image_caption = get_post_meta( $post_id, $post_type . '_feature_image_caption', true );
                if (!empty($feature_image_caption))
                    $content .= '<p class="feature-image-caption">' . "Featured image: " . nl2br(esc_html($feature_image_caption)) . '</p>';
                $content .= '<div style="clear:both;"></div>';
                $content .= '</div>';
            }

            $content .= $old_content;
            $content .= $this->get_popular_summary($post_id);
            $content .= $this->get_bibtex_html($post_id);
            $content .= $this->get_bibliography_html($post_id);
            $content .= $this->get_cited_by($post_id);
            $content .= $this->get_license_information($post_id);
            $content .= '</div>';
            return $content;
        }
        else
            return $content;
    }


        /**
         * Get the eprint of the post with id $post_id.
         *
         * @since    0.3.0
         * @access   public
         * @param    int    $post_id    The id of the post for which to get the eprint.
         */
    public static function get_eprint( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_eprint', true );
    }


        /**
         * Get the arXiv upload date
         *
         * @since    0.3.0
         * @access   public
         * @param    int    $post_id    The id of the post for which to get the arXiv upload date.
         */
    public function get_arxiv_upload_date( $post_id ) {

        $eprint = static::get_eprint($post_id);
        $arxiv_url_abs_prefix = $this->get_journal_property('arxiv_url_abs_prefix');

        return O3PO_Arxiv::get_arxiv_upload_date($arxiv_url_abs_prefix, $eprint);
    }


}
