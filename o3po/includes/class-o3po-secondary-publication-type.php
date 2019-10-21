<?php

/**
 * Class representing the secondary publication type.
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

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-publication-type.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-latex.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-email-templates.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

/**
 * Class representing the secondary publication type.
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_SecondaryPublicationType extends O3PO_PublicationType {

        /**
         * Name of the publication type on which this publication type
         * can be meta literature.
         *
         * @since    0.1.0
         * @access   private
         */
    private $target_publication_type_name;

        /**
         * Plural name of the publication type on which this publication
         * type can be meta literature.
         *
         * @since    0.1.0
         * @access   private
         */
    private $target_publication_type_name_plural;


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
         * @param    string               $target_publication_type_name           Name of the publication type targeted by publications of this type.
         * @param    string               $target_publication_type_name_plural    Plural of the name of the publication type targeted by publications of this type.
         * @param    O3PO_Journal         $journal                               The journal this publication type is associated with.
         * @param    O3PO_Environment     $environment                           The evironment in which this post type is to be created.
         */
    public function __construct( $target_publication_type_name, $target_publication_type_name_plural, $journal, $environment ) {

        parent::__construct($journal, 1, $environment);

        $this->target_publication_type_name = $target_publication_type_name;
        $this->target_publication_type_name_plural = $target_publication_type_name_plural;
    }

        /**
         * Get the categories associated with this publication type
         *
         * The publication type View comes in different flavors, depending
         * on the content and authors who wrote it.
         *
         * @since    0.1.0
         * @access   public
         */
    public static function get_associated_categories() {

        return array("Perspective", "Editorial", "Leap");
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

        $post_id = $post->ID;

        $this->the_admin_panel_intro_text($post_id);
        $this->the_admin_panel_validation_result($post_id);
        echo '<table class="form-table">';
        $this->the_admin_panel_sub_type($post_id);
        $this->the_admin_panel_target_dois($post_id);
        $this->the_admin_panel_title($post_id);
        $this->the_admin_panel_corresponding_author_email($post_id);
        $this->the_admin_panel_buffer($post_id);
        $this->the_admin_panel_authors($post_id);
        $this->the_admin_panel_affiliations($post_id);
        $this->the_admin_panel_date_volume_pages($post_id);
        $this->the_admin_panel_doi($post_id);
        $post_type = get_post_type($post_id);
        $sub_type = get_post_meta( $post_id, $post_type . '_type', true );
        if($sub_type==="Leap")
        {
            static::the_admin_panel_reviewers_summary($post_id);
            static::the_admin_panel_reviewers($post_id);
            static::the_admin_panel_reviewer_institutions($post_id);
            static::the_admin_panel_author_commentary($post_id);
        }
        $this->the_admin_panel_bibliography($post_id);
        $this->the_admin_panel_crossref($post_id);
        $this->the_admin_panel_doaj($post_id);
        $this->the_admin_panel_clockss($post_id);
        echo '</table>';
    }

        /**
         * Callback function for handling the data enterd into the meta-box
         * when a correspnding post is saved.
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

        $new_type = isset( $_POST[ $post_type . '_type' ] ) ? sanitize_text_field( $_POST[ $post_type . '_type' ] ) : '';
        $new_number_target_dois = isset( $_POST[ $post_type . '_number_target_dois' ] ) ? sanitize_text_field( $_POST[ $post_type . '_number_target_dois' ] ) : '';
        $new_target_dois = array();
		for ($x = 0; $x < $new_number_target_dois; $x++) {
			$new_target_dois[] = isset( $_POST[ $post_type . '_target_dois' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_target_dois' ][$x] ) : '';
		}

        $new_reviewers_summary = isset( $_POST[ $post_type . '_reviewers_summary' ] ) ? $_POST[ $post_type . '_reviewers_summary' ] : '';

		$new_number_reviewers = isset( $_POST[ $post_type . '_number_reviewers' ] ) ? sanitize_text_field( $_POST[ $post_type . '_number_reviewers' ] ) : '';

        $new_reviewer_given_names[] = array();
        $new_reviewer_surnames[] = array();
        $new_reviewer_name_styles[] = array();
        $affiliation_nums = array();
        $affiliation_nums = array();
        $new_reviewer_affiliations[] = array();
        $new_reviewer_orcids[] = array();
        $new_reviewer_urls[] = array();
        $new_reviewer_ages[] = array();
        $new_reviewer_grades[] = array();
		for ($x = 0; $x < $new_number_reviewers; $x++) {
			$new_reviewer_given_names[] = isset( $_POST[ $post_type . '_reviewer_given_names' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_given_names' ][$x] ) : '';
			$new_reviewer_surnames[] = isset( $_POST[ $post_type . '_reviewer_surnames' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_surnames' ][$x] ) : '';
			$new_reviewer_name_styles[] = isset( $_POST[ $post_type . '_reviewer_name_styles' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_name_styles' ][$x] ) : '';
			$affiliation_nums = isset( $_POST[ $post_type . '_reviewer_affiliations' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_affiliations' ][$x] ) : '';
			$affiliation_nums = trim( preg_replace("/[^,0-9]/u", "", $affiliation_nums ), ',');
			$new_reviewer_affiliations[] = $affiliation_nums;
			$new_reviewer_orcids[] = isset( $_POST[ $post_type . '_reviewer_orcids' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_orcids' ][$x] ) : '';
            $new_reviewer_urls[] = isset( $_POST[ $post_type . '_reviewer_urls' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_urls' ][$x] ) : '';

            $new_reviewer_ages[] = isset( $_POST[ $post_type . '_reviewer_ages' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_ages' ][$x] ) : '';
            $new_reviewer_grades[] = isset( $_POST[ $post_type . '_reviewer_grades' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_grades' ][$x] ) : '';
		}
        $new_number_reviewer_institutions = isset( $_POST[ $post_type . '_number_reviewer_institutions' ] ) ? sanitize_text_field( $_POST[ $post_type . '_number_reviewer_institutions' ] ) : '';
        $new_reviewer_institutions = array();
		for ($x = 0; $x < $new_number_reviewer_institutions; $x++) {
			$new_reviewer_institutions[] = isset( $_POST[ $post_type . '_reviewer_institutions' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_reviewer_institutions' ][$x] ) : '';
		}
        $new_author_commentary = isset( $_POST[ $post_type . '_author_commentary' ] ) ? $_POST[ $post_type . '_author_commentary' ] : '';
        $new_about_the_author = isset( $_POST[ $post_type . '_about_the_author' ] ) ? $_POST[ $post_type . '_about_the_author' ] : '';

        update_post_meta( $post_id, $post_type . '_type', $new_type );
        update_post_meta( $post_id, $post_type . '_number_target_dois', $new_number_target_dois );
        update_post_meta( $post_id, $post_type . '_target_dois', $new_target_dois );
        update_post_meta( $post_id, $post_type . '_reviewers_summary', $new_reviewers_summary );

        update_post_meta( $post_id, $post_type . '_number_reviewers', $new_number_reviewers );
		update_post_meta( $post_id, $post_type . '_reviewer_given_names', $new_reviewer_given_names );
		update_post_meta( $post_id, $post_type . '_reviewer_surnames', $new_reviewer_surnames );
		update_post_meta( $post_id, $post_type . '_reviewer_name_styles', $new_reviewer_name_styles );
		update_post_meta( $post_id, $post_type . '_reviewer_affiliations', $new_reviewer_affiliations );
		update_post_meta( $post_id, $post_type . '_reviewer_orcids', $new_reviewer_orcids );
        update_post_meta( $post_id, $post_type . '_reviewer_urls', $new_reviewer_urls );
        update_post_meta( $post_id, $post_type . '_reviewer_ages', $new_reviewer_ages );
        update_post_meta( $post_id, $post_type . '_reviewer_grades', $new_reviewer_grades );
        update_post_meta( $post_id, $post_type . '_number_reviewer_institutions', $new_number_reviewer_institutions );
		update_post_meta( $post_id, $post_type . '_reviewer_institutions', $new_reviewer_institutions );

        update_post_meta( $post_id, $post_type . '_author_commentary', $new_author_commentary );
        update_post_meta( $post_id, $post_type . '_about_the_author', $new_about_the_author );
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

        $type = get_post_meta( $post_id, $post_type . '_type', true );
        $sub_type = get_post_meta( $post_id, $post_type . '_type', true );
        $number_target_dois = get_post_meta( $post_id, $post_type . '_number_target_dois', true );
        $target_dois = static::get_post_meta_field_containing_array( $post_id, $post_type . '_target_dois');
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );
        $corresponding_author_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_corresponding_author_has_been_notifed_date', true );

        if($sub_type==="Leap")
        {
            $number_reviewers = get_post_meta( $post_id, $post_type . '_number_reviewers', true );
            $reviewer_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_given_names');
            $reviewer_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_surnames');
            $reviewer_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_name_styles');
            $reviewer_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_affiliations');
            $reviewer_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_orcids');
            $reviewer_urls = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_urls');
            $reviewer_ages = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_ages');
            $reviewer_grades = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_grades');
            $number_reviewer_institutions = get_post_meta( $post_id, $post_type . '_number_reviewer_institutions', true );
            $reviewer_institutions = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_institutions');
        }

            // Set the category from $type
        $term_id = term_exists( $type, 'category' );
        if($term_id == 0)
        {
            wp_insert_term( $type, 'category');
            $term_id = term_exists( $type, 'category' );
        }
        wp_set_post_terms( $post_id, $term_id, 'category' );

        $validation_result = '';
        $validation_result .= parent::validate_and_process_data($post_id);

        if ( empty( $number_target_dois ) && $number_target_dois !== '0' ) $validation_result .= "ERROR: Number of target DOIs is empty.\n";

        $settings = O3PO_Settings::instance();
        for ($x = 0; $x < $number_target_dois; $x++) {
            $doi_prefix = $this->get_journal_property('doi_prefix');
            if ( empty( $target_dois[$x] ) )
                $validation_result .= "WARNING: Target DOI " . ($x+1) . " is empty.\n" ;
            else if( mb_substr($target_dois[$x], 0, mb_strlen($doi_prefix)) !== $doi_prefix )
                $validation_result .= "WARNING: Target DOI " . ($x+1) . " does not point to a paper of this publisher or it contains a prefix such as https://dx.doi.org/, which it shouldn't. Pleae check the DOI.\n" ;
        }

        $post_date = get_the_date( 'Y-m-d', $post_id );
        $today_date = current_time( 'Y-m-d' );
        if ($post_date !== $date_published)
            $validation_result .= "ERROR: The publication date of this post (" . $post_date . ") set in the Publish box on the right does not match the publication date (" . $date_published . ") of this " . $post_type . " given in the input field below.\n";
        if ($post_date !== $today_date and empty($corresponding_author_has_been_notifed_date) )
            $validation_result .= "WARNING: The publication date of this post (" . $post_date . ") is not set to today's date (" . $today_date . ") but the post of this " . $post_type . " also does not appear to have already been published in the past.\n";

        if($sub_type==="Leap")
        {
            if(empty($number_reviewers))
                $validation_result .= "ERROR: Number of reviewers is empty.\n" ;
            else
            {
                for ($x = 0; $x < $number_reviewers; $x++) {
                    if ( empty( $reviewer_given_names[$x] ) )
                        $validation_result .= "WARNING: Reviewer " . ($x+1) . " Given name is empty.\n" ;
                    if ( empty( $reviewer_surnames[$x] ) )
                        $validation_result .= "ERROR: Reviewer " . ($x+1) . " Surname is empty.\n" ;
                    if ( empty( $reviewer_name_styles[$x] ) )
                        $validation_result .= "WARNING: Reviewer " . ($x+1) . " name style is empty.\n" ;
                    if ( !empty( $reviewer_orcids[$x] ) )
                    {
                        $check_orcid_result = O3PO_Utility::check_orcid( $reviewer_orcids[$x]);
                        if( !($check_orcid_result === true) )
                            $validation_result .= "ERROR: ORCID of reviewer " . ($x+1) . " " . $check_orcid_result . ".\n" ;
                    }
                    if ( empty( $reviewer_affiliations[$x] ) )
                        $validation_result .= "WARNING: Affiliations of reviewer " . ($x+1) . " are empty.\n" ;
                    else {
                        $last_affiliation_num = 0;
                        foreach(preg_split('/\s*,\s*/u', $reviewer_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY) as $affiliation_num) {
                            if ($affiliation_num < 1 or $affiliation_num > $number_reviewer_institutions )
                                $validation_result .= "ERROR: At least one affiliation number of reviewer " . ($x+1) . " does not correspond to an actual affiliation.\n" ;
                            if( $last_affiliation_num >= $number_reviewer_institutions )
                                $validation_result .= "WARNING: Affiliations of reviewer " . ($x+1) . " are not in increasing order.\n" ;
                            $last_affiliation_num = $affiliation_num;
                        }
                    }
                    if ( empty( $reviewer_ages[$x] ) )
                        $validation_result .= "WARNING: Age of reviewer " . ($x+1) . " is empty.\n" ;
                    if ( empty( $reviewer_grades[$x] ) )
                        $validation_result .= "WARNING: Grade of reviewer " . ($x+1) . " is empty.\n" ;
                }
                if ( !empty($reviewer_affiliations))
                    $all_appearing_reviewer_affiliations = join(',', $reviewer_affiliations);
                else
                    $all_appearing_reviewer_affiliations = '';
                for ($x = 0; $x < $number_reviewer_institutions; $x++) {
                    if ( empty( $reviewer_institutions[$x] ) )
                        $validation_result .= "ERROR: Reviewer institution " . ($x+1) . " is empty.\n" ;
                    if ( preg_match('#[\\\\]#u', $reviewer_institutions[$x] ) )
                        $validation_result .= "WARNING: Reviewer institution " . ($x+1) . " contains suspicious looking special characters.\n" ;
                    if ( strpos($all_appearing_reviewer_affiliations, (string)($x+1) ) === false)
                        $validation_result .= "ERROR: Reviewer institution " . ($x+1) . " is not associated to any reviewers.\n" ;
                    if ( strpos($all_appearing_reviewer_affiliations, (string)($x) ) !== false and strpos($all_appearing_reviewer_affiliations, (string)($x+1) ) !== false and strpos($all_appearing_reviewer_affiliations, (string)($x) ) > strpos($all_appearing_reviewer_affiliations, (string)($x+1) ) )
                        $validation_result .= "ERROR: Reviewer institution " . ($x) . " appears after first appearance of " . ($x+1) . "\n" ;
                }
            }
        }

        return $validation_result;
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

        $executive_board = $settings->get_plugin_option('executive_board');
        $editor_in_chief = $settings->get_plugin_option('editor_in_chief');

        $corresponding_author_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_corresponding_author_has_been_notifed_date', true );
        $corresponding_author_email = get_post_meta( $post_id, $post_type . '_corresponding_author_email', true );
        $sub_type = get_post_meta( $post_id, $post_type . '_type', true );
        $doi = static::get_doi($post_id);
        $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        $title = get_post_meta( $post_id, $post_type . '_title', true );
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        $post_url = get_permalink( $post_id );

            // Send Emails about the submission to us
        $to = $this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $this->get_journal_property('publisher_email');
        $headers = array( 'From: ' . $this->get_journal_property('publisher_email'));
        $subject  = $this->environment->is_test_environment() ? 'TEST ' : ''.
                    O3PO_EmailTemplates::self_notification_subject(
                      $settings->get_plugin_option('self_notification_subject_template'),
                    $journal, mb_strtolower($sub_type))['result'];
        $message  = $this->environment->is_test_environment() ? 'TEST ' : '' .
                    O3PO_EmailTemplates::self_notification_body(
                        $settings->get_plugin_option('self_notification_body_template'),
                        $journal,
                        $this->get_publication_type_name(),
                        $title,
                        static::get_formated_authors($post_id),
                        $post_url,
                        $this->get_journal_property('doi_url_prefix'),
                        $doi)['result'];

        $successfully_sent = wp_mail( $to, $subject, $message, $headers);

        if(!$successfully_sent)
            $validation_result .= 'WARNING: Error sending email notification of publication to publisher.' . "\n";
        else
            $validation_result .= 'INFO: Email notification of publication to publisher sent.' . "\n";

            // Send Trackbacks to the arXiv and ourselves
        $number_target_dois = get_post_meta( $post_id, $post_type . '_number_target_dois', true );
        $target_dois = static::get_post_meta_field_containing_array( $post_id, $post_type . '_target_dois');
        $trackback_excerpt = $this->get_trackback_excerpt($post_id);
        $doi_prefix = $this->get_journal_property('doi_prefix');
        for ($x = 0; $x < $number_target_dois; $x++) {
            if( mb_substr($target_dois[$x], 0, mb_strlen($doi_prefix)) === $doi_prefix )
            {
                $suspected_post_url = '/' . $this->target_publication_type_name_plural . '/' . mb_substr($target_dois[$x], mb_strlen($doi_prefix)+1) . '/';
                $target_post_id = url_to_postid($suspected_post_url);
                if($target_post_id === 0)
                    continue;

                $target_post_type = get_post_type($target_post_id);
                $target_eprint = get_post_meta( $target_post_id, $target_post_type . '_eprint', true );
                $eprint_without_version = preg_replace('#v[0-9]*$#u', '', $target_eprint);
                if(!empty($target_eprint) && !$this->environment->is_test_environment()) {
                        //Send Trackback to the arxiv
                    trackback( $this->get_journal_property('arxiv_url_trackback_prefix') . $eprint_without_version , $title, $trackback_excerpt, $post_id );
                    $validation_result .= 'INFO: Trackback to the arXiv for ' . $eprint_without_version . ' sent.' . "\n";
                }

                if($settings->get_plugin_option('trackbacks_from_secondary_directly_into_database') !== 'checked')
                {
                        //Send Trackback to ourselves via trackback()
                    $response = trackback( get_site_url() . $suspected_post_url, $title, $trackback_excerpt, $post_id );
                    if(is_wp_error($response))
                        $validation_result .= 'WARNING: Trackback to ' . get_site_url() . $suspected_post_url . ' could not be sent: ' . $response->get_error_message() . "\n";
                    else
                        $validation_result .= 'INFO: Trackback to ' . get_site_url() . $suspected_post_url . ' sent successfully.' . "\n";

                }
                else
                {
                        //Put Trackback comment directly into database with wp_new_comment()
                    if(empty($corresponding_author_has_been_notifed_date) || $this->environment->is_test_environment()) {
                        global $current_user;
                        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
                        $commentdata = array(
                            'comment_post_ID' => $target_post_id,
                            'comment_author' => $sub_type . ' in ' . $journal . ' by ' . static::get_formated_authors($post_id) . ' "' . $title . '"', //This is the only thing displayed in most themes, so we put more information than just the author
                            'comment_author_email' => '',
                            'comment_author_url' => $post_url,
                            'comment_content' => $trackback_excerpt,
                            'comment_type' => 'trackback',
                            'comment_parent' => 0, //0 because it is not a reply to another comment
                            'user_id' => $current_user->ID,
                                             );
                        $comment_id = wp_new_comment($commentdata, true);
                        if(is_wp_error($comment_id))
                            $validation_result .= 'WARNING: Trackback to ' . get_site_url() . $suspected_post_url . ' could not be put into database: ' . $comment_id->get_error_message() . "\n";
                        elseif($comment_id === false)
                            $validation_result .= 'WARNING: Trackback to ' . get_site_url() . $suspected_post_url . ' could not be put into database for an unknown reason.' . "\n";
                        else
                        {
                            $validation_result .= 'INFO: Trackback to ' . get_site_url() . $suspected_post_url . ' put into database successfully.' . "\n";
                            wp_set_comment_status( $comment_id, 'approve', true ); # always attempt to approve the comment
                        }
                    }
                }
            }
        }

            // Send email notifying authors of publication
        if(empty($corresponding_author_has_been_notifed_date) || $this->environment->is_test_environment()) {
            $to = ($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $corresponding_author_email);
            $headers = array( 'Cc: ' . ($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $this->get_journal_property('publisher_email') ), 'From: ' . $this->get_journal_property('publisher_email'));

            $subject  = $this->environment->is_test_environment() ? 'TEST ' : ''.
                  O3PO_EmailTemplates::author_notification_subject(
                      $settings->get_plugin_option('author_notification_secondary_subject_template'),
                      $journal,
                      $sub_type)['result'];
            $message  = $this->environment->is_test_environment() ? 'TEST ' : '' .
                        O3PO_EmailTemplates::author_notification_body(
                            $settings->get_plugin_option('author_notification_secondary_body_template'),
                            $journal,
                            $executive_board,
                            $editor_in_chief,
                            $this->get_journal_property('publisher_email'),
                            $this->get_publication_type_name(),
                            $title,
                            static::get_formated_authors($post_id),
                            $post_url,
                            $this->get_journal_property('doi_url_prefix'),
                            $doi,
                            static::get_formated_citation($post_id)
                                                                      )['result'];

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

        return $validation_result;
    }

        /**
         * Get the excerpt for Trackbacks.
         *
         * @since    0.1.0
         * @access   private
         * @param    int      $post_id    Id of the post.
         */
    public function get_trackback_excerpt( $post_id ) {

        $post_type = get_post_type($post_id);
        if ( $post_type === $this->get_publication_type_name() ) {
            $doi = static::get_doi( $post_id );
            $authors = static::get_formated_authors($post_id);
            $excerpt = '';
            #$excerpt .= '<h2>' . esc_html($authors) . '</h2>' . "\n";
                //$excerpt .= '<a href="' . $this->get_journal_property('doi_url_prefix') . $doi . '">' . $this->get_journal_property('doi_url_prefix') . $doi . '</a>';
            #$excerpt .= static::lead_in_paragraph($post_id) . "\n";
            $excerpt .= '<p>' . get_post_field('post_content', $post_id) . '</p>' . "\n";
            $excerpt = str_replace(']]>', ']]&gt;', $excerpt);
            $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
            $excerpt = O3PO_Latex::expand_cite_to_html($excerpt, $bbl);
            $excerpt = wp_html_excerpt($excerpt, 552, '&#8230;');
            return $excerpt;
        }
        else
            return '';
    }

        /**
         * Get the excerpt of a this publication type.
         *
         * As we modify the content in get_the_content() we
         * construct the excerpt from scratch,
         *
         * To be added to the 'get_the_excerpt' filter.
         *
         * @since 0.1.0
         * @param string    $content    Content to be filtered.
         */
    public function get_the_excerpt( $content ) {

        global $post;

        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if ( $post_type === $this->get_publication_type_name() ) {
            $content = '';
            $content .= '<p class="authors-in-excerpt">' . static::get_formated_authors( $post_id ) . ',</p>' . "\n";
            $content .= '<p class="citation-in-excerpt"><a href="' . $this->get_journal_property('doi_url_prefix') . static::get_doi($post_id) . '">' . static::get_formated_citation($post_id) . '</a></p>' . "\n";
            $content .= '<p><a href="' . get_permalink($post_id) . '" class="abstract-in-excerpt">';
            $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
            $trimmer_abstract = wp_html_excerpt( do_shortcode(O3PO_Latex::expand_cite_to_html(get_post_field('post_content', $post_id), $bbl)), 190, '&#8230;');
            while( preg_match_all('/(?<!\\\\)\$/u', $trimmer_abstract) % 2 !== 0 )
            {
                empty($i) ? $i = 1 : $i += 1;
                $trimmer_abstract = wp_html_excerpt( get_post_meta( $post_id, $post_type . '_abstract', true ), 190+$i, '&#8230;');
            }
            $content .= esc_html ( $trimmer_abstract );
            $content .= '</a></p>';
        }

        return $content;
    }

        /**
         * Echo the sub type/category part of the admin panel.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    protected function the_admin_panel_sub_type( $post_id ) {

        $post_type = get_post_type($post_id);

        $type = get_post_meta( $post_id, $post_type . '_type', true );
        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_type" class="' . $post_type . '_type_label">' . 'Type' . '</label></th>';
		echo '		<td>';
        echo '			<div style="float:left"><select name="' . $post_type . '_type">';
        foreach(static::get_associated_categories() as $current_type)
            echo '<option value="' . $current_type . '"' . ($current_type === $type ? " selected" : "" ) . '>' . $current_type . '</option>';
        echo '</select><br /><label for="' . $post_type . '_type" class="' . $post_type . '_type_label">Type of ' . $this->get_publication_type_name() . '</label></div>';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the target DOI part of the admin panel.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    protected function the_admin_panel_target_dois( $post_id ) {

        $post_type = get_post_type($post_id);

        $number_target_dois = get_post_meta( $post_id, $post_type . '_number_target_dois', true );
        $target_dois = static::get_post_meta_field_containing_array( $post_id, $post_type . '_target_dois');

        if( empty( $number_target_dois ) && $number_target_dois !== '0' )
            $number_target_dois = 1;

        echo '	<tr>';
        echo '		<th><label for="' . $post_type . '_number_target_dois" class="' . $post_type . '_number_target_dois_label">' . 'Number of target dois' . '</label></th>';
		echo '		<td>';
		echo '			<input style="width:4rem" type="number" id="' . $post_type . '_number_target_dois" name="' . $post_type . '_number_target_dois" class="' . $post_type . '_number_target_dois_field required" placeholder="' . '' . '" value="' . esc_attr($number_target_dois) . '"><p>(Please put here the total number of other DOIs this ' . $this->get_publication_type_name() . ' is on. To update the number of fields below, please save the post.)</p>';
		echo '		</td>';
		echo '	</tr>';
		for ($x = 0; $x < $number_target_dois; $x++) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_target_doi" class="' . $post_type . '_target_doi_label">' . "Target doi " . ($x+1) . '</label></th>';
			echo '		<td>';
			echo '			<input type="text" name="' . $post_type . '_target_dois[]" class="' . $post_type . '_target_dois required" placeholder="' . '' . '" value="' . esc_attr( isset($target_dois[$x]) ? $target_dois[$x] : '' ) . '" />';

			echo '		</td>';
			echo '	</tr>';
		}
    }

        /**
         * Echo the reviewers summary part of the admin panel.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    protected static function the_admin_panel_reviewers_summary( $post_id ) {

        $post_type = get_post_type($post_id);

        $reviewers_summary = get_post_meta( $post_id, $post_type . '_reviewers_summary', true );

		if( empty( $reviewers_summary ) ) $reviewers_summary = '' ;

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_reviewers_summary" class="' . $post_type . '_reviewers_summary_label">' . 'Reviewers summary' . '</label></th>';
		echo '		<td>';
		echo '			<textarea rows="6" style="width:100%;" name="' . $post_type . '_reviewers_summary" id="' . $post_type . '_reviewers_summary">' . esc_textarea($reviewers_summary) . '</textarea><p>(Summary of the reviewers.)</p>';
		echo '		</td>';
		echo '	</tr>';
    }

        /**
         * Echo the reviewers part of the admin panel.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    protected static function the_admin_panel_reviewers( $post_id ) {

        $post_type = get_post_type($post_id);

        $number_reviewers = get_post_meta( $post_id, $post_type . '_number_reviewers', true );
		$reviewer_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_given_names');
		$reviewer_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_surnames');
		$reviewer_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_name_styles');
		$reviewer_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_affiliations');
		$reviewer_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_orcids');
        $reviewer_urls = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_urls');
        $reviewer_ages = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_ages');
        $reviewer_grades = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_grades');
        if( empty( $number_reviewers ) ) $number_reviewers = static::get_default_number_reviewers();
		if( empty( $reviewer_given_names ) ) $reviewer_given_names = array();
		if( empty( $reviewer_surnames ) ) $reviewer_surnames = array();
		if( empty( $reviewer_name_styles ) ) $reviewer_name_styles = array();
		if( empty( $reviewer_affiliations ) ) $reviewer_affiliations = array();
		if( empty( $reviewer_orcids ) ) $reviewer_orcids = array();
        if( empty( $reviewer_urls ) ) $reviewer_urls = array();
        if( empty( $reviewer_ages ) ) $reviewer_ages = array();
        if( empty( $reviewer_grades ) ) $reviewer_grades = array();

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_number_reviewers" class="' . $post_type . '_number_reviewers_label">' . 'Number of reviewers' . '</label></th>';
		echo '		<td>';
		echo '			<input style="width:4rem" type="number" id="' . $post_type . '_number_reviewers" name="' . $post_type . '_number_reviewers" class="' . $post_type . '_number_reviewers_field required" placeholder="' . '' . '" value="' . esc_attr($number_reviewers) . '"><p>(Please put here the actual number of reviewers. To update the number of entries in the list below please save the post. Give affiliations as a comma separated list referring to the affiliations below, e.g., 1,2,5,7. As with the title, special characters are allowed and must be entered as í or é and so on.)</p>';
		echo '		</td>';
		echo '	</tr>';

		for ($x = 0; $x < $number_reviewers; $x++) {
			$y = $x+1;
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_reviewer" class="' . $post_type . '_reviewer_label">' . "Reviewer  $y" . '</label></th>';
			echo '		<td>';
			echo '			<div style="float:left"><input type="text" name="' . $post_type . '_reviewer_given_names[]" class="' . $post_type . '_reviewer_given_names_field" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_given_names[$x]) ? $reviewer_given_names[$x] : '' ) . '" /><br /><label for="' . $post_type . '_reviewer_given_names" class="' . $post_type . '_reviewer_given_names_label">Given name</label></div>';
			echo '			<div style="float:left"><input type="text" name="' . $post_type . '_reviewer_surnames[]" class="' . $post_type . '_reviewer_surnames_field required" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_surnames[$x]) ? $reviewer_surnames[$x] : '' ) . '" /><br /><label for="' . $post_type . '_reviewer_surnames" class="' . $post_type . '_reviewer_surnames_label">Surname</label></div>';
			echo '			<div style="float:left"><select name="' . $post_type . '_reviewer_name_styles[]">';
			foreach(array("western", "eastern", "islensk", "given-only") as $style)
                echo '<option value="' . $style . '"' . ( (isset($reviewer_name_styles[$x]) && $reviewer_name_styles[$x] === $style) ? " selected" : "" ) . '>' . $style . '</option>';
			echo '</select><br /><label for="' . $post_type . '_reviewer_name_styles" class="' . $post_type . '_reviewer_name_styles_label">Name style</label></div>';
			echo '			<div style="float:left"><input style="width:5rem" type="text" name="' . $post_type . '_reviewer_affiliations[]" class="' . $post_type . '_reviewer_affiliations_field" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_affiliations[$x]) ? $reviewer_affiliations[$x] : '' ) . '" /><br /><label for="' . $post_type . '_reviewer_affiliations" class="' . $post_type . '_reviewer_affiliations">Institutions</label></div>';
//			echo '			<div style="float:left"><input style="width:11rem" type="text" name="' . $post_type . '_reviewer_orcids[]" class="' . $post_type . '_reviewer_orcids" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_orcids[$x]) ? $reviewer_orcids[$x] : '' ) . '" /><br /><label for="' . $post_type . '_reviewer_orcids" class="' . $post_type . '_reviewer_orcids_label">ORCID</label></div>';
//            echo '			<div style="float:left"><input style="width:20rem" type="text" name="' . $post_type . '_reviewer_urls[]" class="' . $post_type . '_reviewer_urls" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_urls[$x]) ? $reviewer_urls[$x] : '' ) . '" /><br /><label for="' . $post_type . '_reviewer_urls" class="' . $post_type . '_reviewer_urls_label">URL</label></div>';
            echo '			<div style="float:left"><input style="width:20rem" type="text" name="' . $post_type . '_reviewer_ages[]" class="' . $post_type . '_reviewer_ages" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_ages[$x]) ? $reviewer_ages[$x] : '' ) . '" /><br /><label for="' . $post_type . '_reviewer_ages" class="' . $post_type . '_reviewer_ages_label">Age</label></div>';
            echo '			<div style="float:left"><input style="width:20rem" type="text" name="' . $post_type . '_reviewer_grades[]" class="' . $post_type . '_reviewer_grades" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_grades[$x]) ? $reviewer_grades[$x] : '' ) . '" /><br /><label for="' . $post_type . '_reviewer_grades" class="' . $post_type . '_reviewer_grades_label">Grade</label></div>';
			echo '		</td>';
			echo '	</tr>';
		}

    }

        /**
         * Echo the reviewer institutions part of the admin panel.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    protected static function the_admin_panel_reviewer_institutions( $post_id ) {

        $post_type = get_post_type($post_id);
        $number_reviewer_institutions = get_post_meta( $post_id, $post_type . '_number_reviewer_institutions', true );
		$reviewer_institutions = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_institutions');

        if( empty( $number_reviewer_institutions ) && $number_reviewer_institutions !== '0' ) $number_reviewer_institutions = 1;
		if( empty( $reviewer_institutions ) ) $reviewer_institutions = array();

		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_number_reviewer_institutions" class="' . $post_type . '_number_reviewer_institutions_label">' . 'Number of reviewer institutions' . '</label></th>';
		echo '		<td>';
		echo '			<input style="width:4rem" type="number" id="' . $post_type . '_number_reviewer_institutions" name="' . $post_type . '_number_reviewer_institutions" class="' . $post_type . '_number_reviewer_institutions_field required" placeholder="' . '' . '" value="' . esc_attr($number_reviewer_institutions) . '"><p>(Please put here the total number of reviewer institutions. To update the number of Reviewer instition fields save the post.)</p>';
		echo '		</td>';
		echo '	</tr>';
		for ($x = 0; $x < $number_reviewer_institutions; $x++) {
			$y = $x+1;
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_reviewer_institutions" class="' . $post_type . '_reviewer_institutions_label">' . "Reviewer institution  $y" . '</label></th>';
			echo '		<td>';
			echo '			<input style="width:100%" type="text" name="' . $post_type . '_reviewer_institutions[]" class="' . $post_type . '_reviewer_institutions required" placeholder="' . '' . '" value="' . esc_attr( isset($reviewer_institutions[$x]) ? $reviewer_institutions[$x] : '' ) . '" />';

			echo '		</td>';
			echo '	</tr>';
		}

    }

        /**
         * Echo the author commentary part of the admin panel.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    protected static function the_admin_panel_author_commentary( $post_id ) {

        $post_type = get_post_type($post_id);

        $author_commentary = get_post_meta( $post_id, $post_type . '_author_commentary', true );
        $about_the_author = get_post_meta( $post_id, $post_type . '_about_the_author', true );

		if( empty( $author_commentary ) ) $author_commentary = '' ;
		if( empty( $about_the_author ) ) $about_the_author = '' ;

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_author_commentary" class="' . $post_type . '_author_commentary_label">' . 'Author commentary' . '</label></th>';
		echo '		<td>';
		echo '			<textarea rows="6" style="width:100%;" name="' . $post_type . '_author_commentary" id="' . $post_type . '_author_commentary">' . esc_textarea( $author_commentary ) . '</textarea><p>(Commentary of the author(s).)</p>';

        echo '			<textarea rows="6" style="width:100%;" name="' . $post_type . '_about_the_author" id="' . $post_type . '_about_the_author">' . esc_textarea( $about_the_author ) . '</textarea><p>(Some text about the author(s).)</p>';

		echo '		</td>';
		echo '	</tr>';
    }

        /**
         * Get the reviewers summary as html.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    public static function get_reviewers_summary_html( $post_id ) {

        $post_type = get_post_type($post_id);

        $reviewers_summary = get_post_meta( $post_id, $post_type . '_reviewers_summary', true );

        $reviewers_summary_html = '';
        $reviewers_summary_html .= '<h3>Reviewers summary</h3>';
        $reviewers_summary_html .= '<p class="reviewers-summary">' . $reviewers_summary . '</p>';

        return $reviewers_summary_html;
    }

       /**
         * Get the reviewers as html.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    public static function get_reviewers_html( $post_id ) {

        $post_type = get_post_type($post_id);

        $number_reviewers = get_post_meta( $post_id, $post_type . '_number_reviewers', true );
		$reviewer_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_given_names');
		$reviewer_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_surnames');
		$reviewer_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_name_styles');
		$reviewer_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_affiliations');
		$reviewer_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_orcids');
        $reviewer_urls = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_urls');
        $reviewer_ages = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_ages');
        $reviewer_grades = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_grades');


        $number_institutions = get_post_meta( $post_id, $post_type . '_number_reviewer_institutions', true );
        $reviewer_institutions = static::get_post_meta_field_containing_array( $post_id, $post_type . '_reviewer_institutions');

        $reviewers_html = '';
        $reviewers_html .= '<h3>Reviewed by</h3>';
        $reviewer_names = array();
        for ($x = 0; $x < $number_reviewers; $x++) {
            $reviewer_names[] = $reviewer_given_names[$x] . ' ' . $reviewer_surnames[$x];
        }
        $reviewers_html .= '<p>';
        $reviewers_html .= O3PO_Utility::oxford_comma_implode($reviewer_names) . '<br />';
        if(!empty($reviewer_ages) and !empty($reviewer_grades))
        {
            $reviewer_ages_filtered = array_filter($reviewer_ages,'mb_strlen');
            $reviewer_grades_filtered = array_filter($reviewer_grades,'mb_strlen');
            if(!empty($reviewer_ages_filtered) and !empty($reviewer_grades_filtered))
            {
                $min_age = min($reviewer_ages_filtered);
                $max_age = max($reviewer_ages_filtered);
                $min_grade = min($reviewer_grades_filtered);
                $max_grade = max($reviewer_grades_filtered);
                $reviewers_html .= 'Grade ' . ( ($min_grade === $max_grade)? $max_grade : $min_grade . '&ndash;' . $max_grade ) . ' (' .  ( ($min_age === $max_age) ? 'age ' . $max_age : 'ages ' . $min_age . '&ndash;' . $max_age ) . ')<br />';
            }
        }
        if(!empty($reviewer_institutions))
            $reviewers_html .= O3PO_Utility::oxford_comma_implode($reviewer_institutions) . '<br />';
        $reviewers_html .= "The reviewers consented to publication of their names as stated" . '<br />';
        $reviewers_html .= '</p>';

        return $reviewers_html;
    }

        /**
         * Get the author commentary summary as html.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int     $post_id     Id of the post.
         */
    public static function get_author_commentary_html( $post_id ) {

        $post_type = get_post_type($post_id);

        $author_commentary = get_post_meta( $post_id, $post_type . '_author_commentary', true );
        $about_the_author = get_post_meta( $post_id, $post_type . '_about_the_author', true );

        $author_commentary_html = '';
        $author_commentary_html .= '<h3>Author commentary</h3>';
        $author_commentary_html .= '<p class="author-commentary">' . $author_commentary . '</p>';
        $author_commentary_html .= '<p class="about-the-author">' . $about_the_author . '</p>';

        return $author_commentary_html;
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
            $doi = $this->get_doi($post_id);
            $authors = $this->get_formated_authors($post_id);
            $type = get_post_meta( $post_id, $post_type . '_type', true );
            $number_target_dois = get_post_meta( $post_id, $post_type . '_number_target_dois', true );
            $target_dois = $this->get_post_meta_field_containing_array( $post_id, $post_type . '_target_dois');
            $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
            $author_given_names = $this->get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
            $author_surnames = $this->get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
            $author_urls = $this->get_post_meta_field_containing_array( $post_id, $post_type . '_author_urls');
            $author_affiliations = $this->get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');
            $affiliations = $this->get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');
            $citation = rtrim($this->get_formated_citation($post_id), '.');
            $journal = get_post_meta( $post_id, $post_type . '_journal', true );

            $content = '';
            $content .= '<header class="entry-header">';
            if($settings->get_plugin_option('page_template_for_publication_posts')==='checked')
                $content .= '<h1 class="entry-title title citation_title"><a href="#">' . esc_html ( get_the_title( $post_id ) ) . '</a></h1>';

            if ( has_post_thumbnail( ) ) {
                $content .= '<img src="' . get_the_post_thumbnail_url($post_id) . '" alt="" width="300" height="150" class="alignright size-medium wp-image-1433">';
            }

            $content .= $this->lead_in_paragraph($post_id);

            $all_authors_have_same_affiliation = true;
            if ( !empty($author_affiliations) ) {
                foreach($author_affiliations as $author_affiliation) {
                    if( $author_affiliation !== end($author_affiliations) ) {
                        $all_authors_have_same_affiliation = false;
                    break;
                    }
                }
            }

            $content .= "<p><strong>By";
            for ($x = 0; $x < $number_authors; $x++) {
                if( !empty($author_urls[$x]))
                    $content .= ' <a href="' . $author_urls[$x] . '">' . $author_given_names[$x] . ' ' . $author_surnames[$x] . '</a>';
                else
                    $content .= ' ' . $author_given_names[$x] . ' ' . $author_surnames[$x];
                if( !$all_authors_have_same_affiliation && !empty($author_affiliations) && !empty($author_affiliations[$x]) )
                {
                    $content .= ' (';
                    $this_authors_affiliations = preg_split('/\s*,\s*/u', $author_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY);
                    $this_authors_affiliations_count = count($this_authors_affiliations);
                    foreach($this_authors_affiliations as $y => $affiliation_num)
                    {
                        $content .= $affiliations[$affiliation_num-1];
                        if( $y < $this_authors_affiliations_count-1 and $this_authors_affiliations_count > 2) $content .= ",";
                        if( $y < $this_authors_affiliations_count-1 ) $content .= " ";
                        if( $y === $this_authors_affiliations_count-2 ) $content .= "and ";
                    }
                    $content .= ')';
                }
                if( $x < $number_authors-1 and $number_authors > 2) $content .= ",";
                if( $x < $number_authors-1 ) $content .= " ";
                if( $x === $number_authors-2 ) $content .= "and ";
            }
            if(!empty($affiliations) && !empty(end($affiliations)) && $all_authors_have_same_affiliation && !empty($author_affiliations) ) {
                $this_authors_affiliations = preg_split('/\s*,\s*/u', $author_affiliations[0], -1, PREG_SPLIT_NO_EMPTY);
                $this_authors_affiliations_count = count($this_authors_affiliations);
                if($this_authors_affiliations_count > 0)
                {
                    $content .= ' (';
                    foreach($this_authors_affiliations as $y => $affiliation_num)
                    {
                        $content .= $affiliations[$affiliation_num-1];
                        if( $y < $this_authors_affiliations_count-1 and $this_authors_affiliations_count > 2) $content .= ",";
                        if( $y < $this_authors_affiliations_count-1 ) $content .= " ";
                        if( $y === $this_authors_affiliations_count-2 ) $content .= "and ";
                    }
                    $content .= ')';
                }
            }
            $content .= ".</strong></p>\n";

            $content .= '<table class="meta-data-table">';
            $content .= '<tr><td>Published:</td><td>' . esc_html($this->get_formated_date_published( $post_id )) .  ', ' . $this->get_formated_volume_html($post_id) . ', page ' . get_post_meta( $post_id, $post_type . '_pages', true ) . '</td></tr>';
            $content .= '<tr><td>Doi:</td><td><a href="' . esc_attr($this->get_journal_property('doi_url_prefix') . $doi) . '">' . esc_html($this->get_journal_property('doi_url_prefix') . $doi ) . '</a></td></tr>';
            $content .= '<tr><td>Citation:</td><td>' . esc_html($citation) . '</td></tr>';
            $content .= '</table>';

            $content .= '<div class="publication-action-buttons">';
            /* $content .= '<form action="javascript:if(window.print)window.print()" method="post"><input style="display:none;" id="print-btn" type="submit" value="print page"></form>'; */
            $content .= '<button id="print-btn" style="display:none;" class="btn-theme-primary pirate-forms-submit-button" type="submit" onclick="if(window.print)window.print()">Print page</button>';
            $content .= '</div>';

            $content .= '<script type="text/javascript">document.getElementById("print-btn").style.display = "inline-block";</script>';//show button only if browser supports java script
            $content .= '</header>';

            $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
            $content .= O3PO_Latex::expand_cite_to_html($old_content, $bbl);

            if($type==="Leap")
            {
                $content .= $this->get_reviewers_summary_html($post_id);
                $content .= $this->get_reviewers_html($post_id);
                $content .= $this->get_author_commentary_html($post_id);
            }

            $content .= $this->get_bibtex_html($post_id);
            $content .= $this->get_bibliography_html($post_id);
            $content .= $this->get_cited_by($post_id);
            $content .= $this->get_license_information($post_id);
            return $content;
        }
        else
            return $content;
    }


        /**
         * Get the lead in paragraph for View publications.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id    Id of the post.
         */
    public function lead_in_paragraph( $post_id ) {

        $post_type = get_post_type($post_id);
        $type = get_post_meta( $post_id, $post_type . '_type', true );
        $number_target_dois = get_post_meta( $post_id, $post_type . '_number_target_dois', true );
        $target_dois = static::get_post_meta_field_containing_array( $post_id, $post_type . '_target_dois');
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );

        $content = '';
        $content .= '<p><em>This is ' . ( preg_match('/^[hH]?[aeiouAEIOU]/u' , $type) ? 'an' : 'a') . ' ' . $type;
        if($type==="Leap")
            $content .= ' &mdash; a popular science article on quantum research written by scientists and reviewed by teenagers';

        if($number_target_dois>0)
        {
            if($type==="Leap")
                $content .= ' &mdash;';
            $content .= ' on ';
            for ($x = 0; $x < $number_target_dois; $x++) {
                $doi_prefix = $this->get_journal_property('doi_prefix');
                if( mb_substr($target_dois[$x], 0, mb_strlen($doi_prefix)) === $doi_prefix )
                {
                    $suspected_post_url = '/' . $this->target_publication_type_name_plural . '/' . mb_substr($target_dois[$x], mb_strlen($doi_prefix)+1) . '/';
                    $target_post_id = url_to_postid($suspected_post_url);
                    $target_post_type = get_post_type( $target_post_id );
                    $target_title = get_post_meta( $target_post_id, $target_post_type . '_title', true );
                    $target_authors = static::get_formated_authors($target_post_id);
                    $target_citation = static::get_formated_citation($target_post_id);
                    $content .= '<a href="' . $this->get_journal_property('doi_url_prefix') . $target_dois[$x] . '">&quot;' . $target_title . '&quot;</a> by ' . $target_authors . ', published in ' . rtrim($target_citation, '.');
                    if( $x < $number_target_dois-1 and $number_target_dois > 2) $formated_authors .= ",";
                    if( $x < $number_target_dois-1 ) $formated_authors .= " ";
                    if( $x === $number_target_dois-2 ) $formated_authors .= "and ";
                }
                else
                    $content .= '<a href="' . $this->get_journal_property('doi_url_prefix') . $target_dois[$x] . '">' . $target_dois[$x] . '</a>';
            }
        }
        $content .= ".</em></p>\n";

        return $content;
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

        parent::add_dublin_core_and_highwire_press_meta_tags();
    }

        /**
         * Get the default number of reviewers.
         *
         * @since    0.1.0
         * @access   protected
         */
    protected static function get_default_number_reviewers() {

        return 4;
    }

        /**
         * Get the path of the fulltext pdf.
         *
         * In this class there is nothing to return. So we don't return anything.
         *
         * @since 0.2.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public function get_fulltext_pdf_path( $post_id ) {

        return null;
    }


        /**
         * Get the pretty permalink of the pdf associated with a post.
         *
         * In this class there is nothing to return. So we don't return anything.
         *
         * @since 0.2.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public function get_pdf_pretty_permalink( $post_id ) {

        return null;
    }

}
