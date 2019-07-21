<?php

/**
 * Base class for types of publications.
 *
 * This is an abstract base class that provides infrastructure common to various
 * publication types that can be used for both the primary and secondary journal.
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

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-clockss.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-crossref.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-ads.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-buffer.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-doaj.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-latex.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';

/**
 * Base class for types of publications.
 *
 * Abstract base class for representing different publication types as custom WordPress post types.
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
abstract class O3PO_PublicationType {

        /**
         * Array of all activated publication types derived from this class.
         *
         * @since    0.1.0
         * @access   private
         * @var      array    $active_publication_types    Array of all activated publication types derived from this class.
         */
    private static $active_publication_types;

        /**
         * The array holding the properties of the journal this post type is associated with.
         *
         * @since    0.1.0
         * @access   private
         * @var      O3PO_Journal    $journal    A the journal this publication type is published in.
         */
    private $journal;

        /**
         * Name of this publication type.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    Name of this publication type.
         */
    private $publication_type_name;

        /**
         * Plural of the name of this publication type.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    Plural of the name of this publication type.
         */
    private $publication_type_name_plural;

        /**
         * Default number of authors for this publication type.
         *
         * @since    0.1.0
         * @access   private
         * @var      int    Default number of authors for this publication type.
         */
    private $default_number_authors;

        /**
         * The envrironment in which this publication type exists.
         *
         * @since    0.1.0
         * @access   protected
         * @var      O3PO_Environment    The envrironment in which this publication type exists.
         */
    protected $environment;

        /**
         * Get a property of the associated journal.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id    If of the property.
         */
    public function get_journal_property( $id ) {

        return $this->journal->get_journal_property($id);
    }

        /**
         * Get the name of this publication type.
         *
         * @since    0.1.0
         * @access   public
         */
    public function get_publication_type_name() {

        return $this->publication_type_name;
    }

        /**
         * Get the plural name of this publication type.
         *
         * @since    0.1.0
         * @access   public
         */
    public function get_publication_type_name_plural() {

        return $this->publication_type_name_plural;
    }

        /**
         * Get the default number of authors for this publication type.
         *
         * @since    0.1.0
         * @access   public
         */
    public function get_default_number_authors() {

        return $this->default_number_authors;
    }

        /**
         * Construct this publication type.
         *
         * Constructs and registers this publication type in the array
         * $active_publication_types. Throws an error in case a
         * publication type with the same $publication_type_name is alreay
         * registered.
         *
         * @since    0.1.0
         * @access   public
         * @param    object               $journal                         The journal this publication type is associated with.
         * @param    int                  $default_number_authors          The default number of authors.
         * @param    O3PO_Environment     $environment                     The evironment in which this post type is to be created.
         */
    public function __construct( $journal, $default_number_authors, $environment ) {

        if(!isset(self::$active_publication_types))
            self::$active_publication_types = array();

        $this->journal = $journal;

        $this->publication_type_name = $this->get_journal_property('publication_type_name');
        $this->publication_type_name_plural = $this->get_journal_property('publication_type_name_plural');

        $this->default_number_authors = $default_number_authors;
        $this->environment = $environment;

        foreach(self::$active_publication_types as $active_publication_type)
            if($active_publication_type->get_publication_type_name() == $this->get_publication_type_name() )
                throw new Exception('Cannot create a publication type with an already taken publication name.');

        array_push(self::$active_publication_types, $this);

    }

        /**
         * Get all registered publication types.
         *
         * @since    0.1.0
         * @access   public
         * @param    string     $name    Name of a publication type. If provided returns only that publication type (if it is acitve).
         */
    public static function get_active_publication_types( $name=Null ) {

        if(!isset(self::$active_publication_types))
            return Null;

        if(empty($name))
            return self::$active_publication_types;

        foreach(self::$active_publication_types as $active_publication_type)
            if($active_publication_type->get_publication_type_name() === $name)
                return $active_publication_type;

        return Null;
    }

        /**
         * Get the names of all registered publication types.
         *
         * @since    0.1.0
         * @access   public
         * @return   array   Array of names of all active publication types.
         */
    public static function get_active_publication_type_names() {

        if(empty(self::$active_publication_types))
            return array();

        $active_publication_type_names = array();
        foreach(self::$active_publication_types as $active_publication_type)
            $active_publication_type_names[] = $active_publication_type->get_publication_type_name();

        return $active_publication_type_names;
    }

        /**
         * Registers this publication type as a custom post type with WordPress.
         *
         * To be added to the 'init' action.
         *
         * @since    0.1.0
         * @access   public
         */
    public final function register_as_custom_post_type() {

        $labels = array(
            'name'                => ucfirst($this->get_publication_type_name()),
            'singular_name'       => ucfirst($this->get_publication_type_name()),
            'menu_name'           => ucfirst($this->get_publication_type_name_plural()),
            'parent_item_colon'   => 'Parent ' . ucfirst($this->get_publication_type_name()),
            'all_items'           => 'All ' . ucfirst($this->get_publication_type_name_plural()),
            'view_item'           => 'View ' . ucfirst($this->get_publication_type_name()),
            'add_new_item'        => 'Add New ' . ucfirst($this->get_publication_type_name()),
            'add_new'             => 'Add New',
            'edit_item'           => 'Edit ' . ucfirst($this->get_publication_type_name()),
            'update_item'         => 'Update ' . ucfirst($this->get_publication_type_name()),
            'search_items'        => 'Search ' . ucfirst($this->get_publication_type_name()),
            'not_found'           => 'Not Found',
            'not_found_in_trash'  => 'Not found in Trash',
                        );

        $args = array(
            'label'               => $this->get_publication_type_name(),
            'description'         => ucfirst($this->get_publication_type_name()) . ' published by ' . $this->get_journal_property('journal_title'),
            'labels'              => $labels,
                // Features this CPT supports in Post Editor.
            'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'post-formats', 'trackbacks' ),
                // You can associate this CPT with a taxonomy or custom taxonomy.
            'taxonomies'  => array( 'category', 'post_format', 'post_author' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_icon' => 'dashicons-media-document',
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'rewrite' => array('slug' => $this->get_publication_type_name_plural()), //THIS MUST NEVER BE CHANGED
                      );

        register_post_type($this->get_publication_type_name(), $args );

            /* As this post type is dnynamically created, we cannot register
             * it via a static function call during the activation hook.
             * Flushing rewrite rules there is hence not sufficient to make
             * the post pages it creates accessible. At the same time,
             * flush_rewrite_rules() must not be called on every page load.
             * We thus resort to checking whether get_option('rewrite_rules')
             * already contains rules associated with this slug and only
             * flush if this is not the case. */
        $rewrite_rules_must_be_fluhed = true;
        foreach( get_option( 'rewrite_rules' ) as $key => $rule)
            if(strpos($key, $args['rewrite']['slug'] ) === 0)
            {
                $rewrite_rules_must_be_fluhed = false;
                break;
            }
        if($rewrite_rules_must_be_fluhed)
            flush_rewrite_rules(true);
    }

        /**
         * Init the meta box for all publication types and add hooks for its save action.
         *
         * Normally all hooks should be added in class-o3po.php. This method is special
         * in that it adds two extra hooks. We do this, because we need to unhook one
         * of them during save_metabox() (see below), so that it is better to keep this
         * all contained in the same file
         *
         * @since      0.1.0
         * @access     public
         * */
	public static final function init_metabox() {

        foreach(self::$active_publication_types as $active_publication_type)
        {
            add_action( 'add_meta_boxes', array($active_publication_type, 'add_metabox') );
            add_action( 'save_post',      array($active_publication_type, 'save_metabox' ), 10, 2 ); //This is critical because we remove and re-add this hook in save_metabox()!!!
        }
	}

        /**
         * Adds the meta box for the meta-data of posts of this publication
         * type to the create new/update post page in the admin area.
         *
         * @since    0.1.0
         * @access   public
         */
    public final function add_metabox() {

        add_meta_box(
            'metadata_metabox',
            'Metadata',
            array($this, 'render_metabox'),
            $this->get_publication_type_name(),
            'advanced',
            'default'
                     );

	}

        /**
         * Render the meta box.
         *
         * This function should be overwritten in any child class and but
         * the parent implementation should be called through.
         *
         * @since    0.1.0
         * @access   public
         * @param    WP_Post     $post   The post for which to render the metabox.
         * */
    public function render_metabox( $post ) {

            // Add nonce for security and authentication.
		$nonce_name   = $this->get_publication_type_name() . '_nonce';
		$nonce_action = $this->get_publication_type_name() . '_nonce_action';

        $this->render_maintenance_mode_warning($post);

		wp_nonce_field( $nonce_action, $nonce_name );
    }

        /**
         * Render the maintenance_mode warning if appropriate.
         *
         * @since    0.3.0
         * @access   public
         * @param    WP_Post     $post   The post for which to render the metabox.
         * */
    public static final function render_maintenance_mode_warning( $post ) {

        $settings = O3PO_Settings::instance();
        if($settings->get_plugin_option('maintenance_mode')!=='unchecked')
            echo '<script>alert("' . esc_html($settings->get_plugin_pretty_name()) . ' has been put in maintenance mode. Modification of publication-meta data is inhibited. Maintenance mode can be disabled in the plugin settings. Please contact your site administrator(s).");</script>' . "\n";

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
         * @since    0.1.0
         * @access   public
         * @param    int         $post_id  The id of the post for which to save the metabox.
         * @param    WP_Post     $post     The post for which to save the metabox.
         * */
    public final function save_metabox( $post_id, $post ) {

        $post_type = get_post_type($post_id);

            // If the post type doesn't fit do nothing
        if ( $this->get_publication_type_name() !== $post_type )
            return;

            // Check nonce for security and authentication.
		$nonce_name   = isset($_POST[$this->get_publication_type_name() . '_nonce']) ? $_POST[$this->get_publication_type_name() . '_nonce'] : '';
		$nonce_action = $this->get_publication_type_name() . '_nonce_action';

            // Check if a nonce is set and valid.
		if ( empty($nonce_name) || ! wp_verify_nonce( $nonce_name, $nonce_action ) )
			return;

            // Check if the user has permissions to save data
		if ( !current_user_can( 'edit_post', $post_id ) )
			return;

            // Check if it's not an autosave
		if ( wp_is_post_autosave( $post_id ) )
			return;

            // Check if it's not a revision
		if ( wp_is_post_revision( $post_id ) )
			return;

            // We do not validate and process newly created posts
        if ( get_post_status( $post_id ) === 'auto-draft' )
            return;

            // Do nothing if in maintenance mode
        $settings = O3PO_Settings::instance();
        if($settings->get_plugin_option('maintenance_mode')!=='unchecked')
            return;

            //Save the entered meta data
        $this->save_meta_data($post_id);

            // Unhook this function to prevent infinite looping because in the remainder of this function (especially in validate_and_process_data()) we will be calling update_post() which triggers 'save_post'
        $validation_result = '';
        try
        {
            remove_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );

            $validation_result .= $this->validate_and_process_data($post_id);
        }
        catch(Exception $e) {
            $validation_result .= "ERROR: There was an exception while saving and processing the entered meta-data: " . $e->getMessage() . "\n";
        }
        finally {
            if ( get_post_status( $post_id ) !== 'publish' and  get_post_status( $post_id ) !== 'future' )
                $validation_result .= "WARNING: Not yet published.\n";
            update_post_meta( $post_id, $post_type . '_validation_result', $validation_result );

                // Rehock this function
            add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
        }
    }


        /**
         * Handle post status transitions
         *
         * To be added to the 'transition_post_status' hook.
         *
         * @since    0.3.0
         * @access   public
         * @param    string      $new      The new post status after this transition.
         * @param    string      $old      The old post status before this transition.
         * @param    int         $post     The post that is undergoing the transitions.
         */
    public final function on_transition_post_status( $new, $old, $post ) {

        $post_id = $post->ID;
        $post_type = $post->post_type;

            // If the post type doesn't fit do nothing
        if ( $this->get_publication_type_name() !== $post_type )
            return;

        if ( $old == 'future' && $new == 'publish')
        {
            $validation_result = get_post_meta( $post_id, $post_type . '_validation_result', true);
            $validation_result .= "INFO: Publication of this scheduled " . $post_type . " was triggered on " . date('Y-m-d H:i:s') . ".\n";
            $validation_result .= $this->validate_and_process_data($post_id);

            update_post_meta( $post_id, $post_type . '_validation_result', $validation_result );
        }

    }

        /**
         * Obtain the data entered into the meta-data metabox from the
         * $_POST, sanitize it, set defauls, and save it.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int          $post_id   The id of the post.
         */
    protected function save_meta_data( $post_id ) {

        $post_type = get_post_type($post_id);

        $new_title = isset( $_POST[ $post_type . '_title' ] ) ? $_POST[ $post_type . '_title' ] : '';
        $new_title_mathml = isset( $_POST[ $post_type . '_title_mathml' ] ) ? $_POST[ $post_type . '_title_mathml' ] : '';
		$new_number_authors = isset( $_POST[ $post_type . '_number_authors' ] ) ? sanitize_text_field( $_POST[ $post_type . '_number_authors' ] ) : '';
		for ($x = 0; $x < $new_number_authors; $x++) {
			$new_author_given_names[] = isset( $_POST[ $post_type . '_author_given_names' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_author_given_names' ][$x] ) : '';
			$new_author_surnames[] = isset( $_POST[ $post_type . '_author_surnames' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_author_surnames' ][$x] ) : '';
			$new_author_name_styles[] = isset( $_POST[ $post_type . '_author_name_styles' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_author_name_styles' ][$x] ) : 'western';
			$affiliation_nums = isset( $_POST[ $post_type . '_author_affiliations' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_author_affiliations' ][$x] ) : '';
			$affiliation_nums = trim( preg_replace("/[^,0-9]/u", "", $affiliation_nums ), ',');
			$new_author_affiliations[] = $affiliation_nums;
			$new_author_orcids[] = isset( $_POST[ $post_type . '_author_orcids' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_author_orcids' ][$x] ) : '';
            $new_author_urls[] = isset( $_POST[ $post_type . '_author_urls' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_author_urls' ][$x] ) : '';
		}
        $new_number_affiliations = isset( $_POST[ $post_type . '_number_affiliations' ] ) ? sanitize_text_field( $_POST[ $post_type . '_number_affiliations' ] ) : '';
        $new_affiliations = array();
		for ($x = 0; $x < $new_number_affiliations; $x++) {
			$new_affiliations[] = isset( $_POST[ $post_type . '_affiliations' ][$x] ) ? sanitize_text_field( $_POST[ $post_type . '_affiliations' ][$x] ) : '';
		}
        $new_date_published = isset( $_POST[ $post_type . '_date_published' ] ) ? sanitize_text_field( $_POST[ $post_type . '_date_published' ] ) : '';
		$new_journal = isset( $_POST[ $post_type . '_journal' ] ) ? sanitize_text_field( $_POST[ $post_type . '_journal' ] ) : '';
		$new_volume = isset( $_POST[ $post_type . '_volume' ] ) ? sanitize_text_field( $_POST[ $post_type . '_volume' ] ) : '';
		$new_pages = isset( $_POST[ $post_type . '_pages' ] ) ? sanitize_text_field( $_POST[ $post_type . '_pages' ] ) : '';
		$new_doi_prefix = $this->get_journal_property('doi_prefix');
        $old_doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
		if ( !empty($new_date_published) and !empty($new_pages) ) {
			$new_doi_suffix = $this->get_journal_property('journal_level_doi_suffix') . "-" . $new_date_published . "-" . $new_pages;
		}
		else {
			$new_doi_suffix = '';
		}
		if ($old_doi_suffix === $new_doi_suffix)
			update_post_meta( $post_id, $post_type . '_doi_suffix_was_changed_on_last_save', "false" );
		else {
			if ( !$this->journal->doi_suffix_still_free($new_doi_suffix, $this->get_active_publication_type_names()) )
				$new_doi_suffix = '';
			update_post_meta( $post_id, $post_type . '_doi_suffix_was_changed_on_last_save', "true" );
		}
        $new_corresponding_author_email = isset( $_POST[ $post_type . '_corresponding_author_email' ] ) ? sanitize_text_field( $_POST[ $post_type . '_corresponding_author_email' ] ) : '';
        $new_buffer_email = isset($_POST[ $post_type . '_buffer_email' ]) ? sanitize_text_field( $_POST[ $post_type . '_buffer_email' ]) : ''; #we keep using the buffer_email and buffer_email_xxx fields for compatibility, even though the new buffer.com interface does no longer send emails but uses the buffer.com api
        $new_buffer_special_text = isset($_POST[ $post_type . '_buffer_special_text' ]) ? sanitize_text_field( $_POST[ $post_type . '_buffer_special_text' ]) : '';

        $new_bbl = isset( $_POST[ $post_type . '_bbl' ] ) ? $_POST[ $post_type . '_bbl' ] : '';
        delete_transient($post_id . '_bibliography_html'); //Delete cached version of the bibliography html

        update_post_meta( $post_id, $post_type . '_title', $new_title );
		update_post_meta( $post_id, $post_type . '_title_mathml', $new_title_mathml );
		update_post_meta( $post_id, $post_type . '_number_authors', $new_number_authors );
		update_post_meta( $post_id, $post_type . '_number_affiliations', $new_number_affiliations );
		update_post_meta( $post_id, $post_type . '_author_given_names', $new_author_given_names );
		update_post_meta( $post_id, $post_type . '_author_surnames', $new_author_surnames );
		update_post_meta( $post_id, $post_type . '_author_name_styles', $new_author_name_styles );
		update_post_meta( $post_id, $post_type . '_author_affiliations', $new_author_affiliations );
		update_post_meta( $post_id, $post_type . '_author_orcids', $new_author_orcids );
        update_post_meta( $post_id, $post_type . '_author_urls', $new_author_urls );
		update_post_meta( $post_id, $post_type . '_affiliations', $new_affiliations );
		update_post_meta( $post_id, $post_type . '_date_published', $new_date_published );
		update_post_meta( $post_id, $post_type . '_journal', $new_journal );
		update_post_meta( $post_id, $post_type . '_volume', $new_volume );
		update_post_meta( $post_id, $post_type . '_pages', $new_pages );
        update_post_meta( $post_id, $post_type . '_doi_prefix', $new_doi_prefix );
		update_post_meta( $post_id, $post_type . '_doi_suffix', $new_doi_suffix );
		update_post_meta( $post_id, $post_type . '_corresponding_author_email', $new_corresponding_author_email );
        update_post_meta( $post_id, $post_type . '_bbl', $new_bbl );
        update_post_meta( $post_id, $post_type . '_buffer_email', $new_buffer_email ); #we keep using the buffer_email and buffer_email_xxx fields for compatibility, even though the new buffer.com interface does no longer send emails but uses the buffer.com api
        update_post_meta( $post_id, $post_type . '_buffer_special_text', $new_buffer_special_text );

    }

        /**
         * Validate and process the meta-data that was saved in save_meta_data().
         *
         * We also trigger interactions with external services (Crossref, DOAJ,
         * CLOCKSS) here.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int          $post_id   The id of the post.
         */
    protected function validate_and_process_data( $post_id ) {

        $post_type = get_post_type($post_id);

        $title = get_post_meta( $post_id, $post_type . '_title', true );
        $title_mathml = get_post_meta( $post_id, $post_type . '_title_mathml', true);
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        $pages = get_post_meta( $post_id, $post_type . '_pages', true );
        $doi_prefix = get_post_meta( $post_id, $post_type . '_doi_prefix', true );
        $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        $number_affiliations = get_post_meta( $post_id, $post_type . '_number_affiliations', true );
        $affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        $author_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_name_styles');
        $author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');
        $author_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_orcids');
        $author_urls = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_urls');
        $corresponding_author_email = get_post_meta( $post_id, $post_type . '_corresponding_author_email', true );
        $corresponding_author_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_corresponding_author_has_been_notifed_date', true );
        $doi_suffix_was_changed_on_last_save = get_post_meta( $post_id, $post_type . '_doi_suffix_was_changed_on_last_save', true );
        $abstract = get_post_meta( $post_id, $post_type . '_abstract', true );
        $publisher = $this->get_journal_property('publisher');

            // Set the permalink
        if( !empty( $doi_suffix ) ) {
            wp_update_post( array('ID' => $post_id, 'post_name' => addslashes($doi_suffix) ));
        }
            // Set the post title
        if( !empty( $title ) )
            wp_update_post( array('ID' => $post_id, 'post_title' => addslashes($title) ));

        $validation_result = '';

        if( empty($journal) or empty($doi_prefix) or empty($publisher) )
            $validation_result .= "WARNING: The journal title (" . $journal . "), doi prefix (" . $doi_prefix . "), or publisher (" . $publisher . ") seem to be empty. Probably some some essential settings were not set. Please go to the settings page and configure them.\n";

        if( O3PO_Latex::preg_match_outside_math_mode('#\\\\(?!cite)#u', $abstract) !== 0)
            $validation_result .= "WARNING: The abstract contains one or more backslashes that are not part of a \\\\cite command or inside a formula.\n" ;
        if( O3PO_Latex::strpos_outside_math_mode($abstract, '=') !== false )
            $validation_result .= "WARNING: The abstract contains an = sign that should probably be part of a mathematical formulat, please put dollar signs around the formula.\n" ;
        if( O3PO_Latex::strpos_outside_math_mode($abstract, '<') !== false )
            $validation_result .= "WARNING: The abstract contains an < sign that should probably be part of a mathematical formulat, please put dollar signs around the formula.\n" ;
        if( O3PO_Latex::strpos_outside_math_mode($abstract, '>') !== false )
            $validation_result .= "WARNING: The abstract contains an > sign that should probably be part of a mathematical formulat, please put dollar signs around the formula.\n" ;

        $highest_pages_info = $this->journal->get_post_type_highest_pages_info( $post_id, array($post_type) );
        if ($highest_pages_info['future_post_exists'])
            $validation_result .= "WARNING: There is at least one " . $post_type . " scheduled for publication in the future. Please be super extra careful with the page number!\n";
        $highest_pages = $highest_pages_info['pages'];
        if (intval($pages) !== intval($highest_pages+1))
            $validation_result .= "WARNING: The page number " . $pages . " of this " . $post_type . " is not exactly one larger than the so-far highest page number " . $highest_pages . " of all posts of type " . $post_type . ". This can be correct if you are modifying an already published post, or if there are posts scheduled for publication in the future. Please double check.\n";
        $pages_still_free_info = $this->journal->pages_still_free_info( $post_id, $pages, array($post_type) );
        if(!$pages_still_free_info['still_free'])
            $validation_result .= "ERROR: The page number " . $pages . " is already taken by the " . $post_type . " " . $pages_still_free_info['title'] . ". This prevents assignment of a DOI as well as the downloading and parsing of the source files and pdf from the arXiv.\n";

        if ($doi_suffix_was_changed_on_last_save === "true")
            $validation_result .= "REVIEW: The doi suffix was set to ". $doi_suffix . ".\n";

        $post_thumbnail_id = get_post_thumbnail_id( $post_id );
        if(!empty($post_thumbnail_id)) {
            $image_data = wp_get_attachment_image_src($post_thumbnail_id, "Full");
            if(is_array($image_data))
            {
                $width = (int)($image_data[1]);
                $height = (int)($image_data[2]);
                if($width !== 2*$height)
                    $validation_result .= "WARNING: The featured image aspect ration of " . $width . ":". $height . " is not 2:1.\n";
                if($width < 400 or $height < 200)
                    $validation_result .= "WARNING: The featured image is too small.\n";
            }
            else
                $validation_result .= "WARNING: A featured image is set but the size could not be checked.\n";
        }
        else
            $validation_result .= "WARNING: No featured image.\n";

        if ( empty($title) )
            $validation_result .= "ERROR: Title is empty.\n";
        else if ( preg_match('/[<>]/u', $title ) )
            $validation_result .= "WARNING: Title contains < or > signs. If they are meant to represent math, the formulas should be enclosed in dollar signs and they should be replaced with \\\\lt and \\\\gt respectively (similarly <= and >= should be replaced by \\\\leq and \\\\geq).\n" ;
        if ( empty($title_mathml) && preg_match('/[^\\\\]\$.*[^\\\\]\$/u' , $title ) )
            $validation_result .= "ERROR: Title contains math but no MathML variant was saved so far.\n";
        if ( empty( $pages ) or $pages < 0 )
            $validation_result .= "ERROR: Pages is invalid. Maybe you are trying to publish something that would break lexicographic ordering of DOIs?\n";
        if ( empty( $doi_prefix ) )
            $validation_result .= "ERROR: DOI prefix is empty.\n";
        if ( empty( $doi_suffix ) )
            $validation_result .= "ERROR: DOI suffix is empty. Probably Publication date and/or Pages are missing or the suffix resulting from them is already taken.\n";
        if ( empty( $number_authors ) )
            $validation_result .= "ERROR: Number of authors is empty.\n";
        if ( empty( $number_affiliations ) && $number_affiliations !== '0' )
            $validation_result .= "ERROR: Number of affiliations is empty.\n";
        for ($x = 0; $x < $number_authors; $x++) {
            if ( empty( $author_given_names[$x] ) )
                $validation_result .= "WARNING: Author " . ($x+1) . " Given name is empty.\n" ;
            elseif ( strpos($author_given_names[$x], ' ') !== false )
                $validation_result .= "WARNING: Author " . ($x+1) . " Given name contains a space. If this is because of a middle name, make sure it is in the right box, depending on whether it is a second given name or surname.\n" ;
            if ( empty( $author_surnames[$x] ) )
                $validation_result .= "ERROR: Author " . ($x+1) . " Surname is empty.\n" ;
            elseif ( strpos($author_surnames[$x], ' ') !== false )
                $validation_result .= "WARNING: Author " . ($x+1) . " Surname contains a space. If this is because of a middle name, make sure it is in the right box, depending on whether it is a second given name or surname.\n" ;
            if ( empty( $author_name_styles[$x] ) )
                $validation_result .= "WARNING: Author " . ($x+1) . " name style is empty.\n" ;
            if ( !empty( $author_orcids[$x] ) )
            {
                $check_orcid_result = O3PO_Utility::check_orcid( $author_orcids[$x]);
                if( !($check_orcid_result === true) )
                    $validation_result .= "ERROR: ORCID of author " . ($x+1) . " " . $check_orcid_result . ".\n" ;
            }
            if ( empty( $author_affiliations[$x] ) )
                $validation_result .= "WARNING: Affiliations of author " . ($x+1) . " are empty.\n" ;
            else {
                $last_affiliation_num = 0;
                foreach(preg_split('/\s*,\s*/u', $author_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY) as $affiliation_num) {
                    if ($affiliation_num < 1 or $affiliation_num > $number_affiliations )
                        $validation_result .= "ERROR: At least one affiliation number of author " . ($x+1) . " does not correspond to an actual affiliation.\n" ;
                    if( $last_affiliation_num >= $affiliation_num )
                        $validation_result .= "WARNING: Affiliations of author " . ($x+1) . " are not in increasing order.\n" ;
                    $last_affiliation_num = $affiliation_num;
                }
            }
        }
        if ( !empty($author_affiliations))
            $all_appearing_affiliations = join(',', $author_affiliations);
        else
            $all_appearing_affiliations = '';
        for ($x = 0; $x < $number_affiliations; $x++) {
            if ( empty( $affiliations[$x] ) )
                $validation_result .= "ERROR: Affiliation " . ($x+1) . " is empty.\n" ;
            if ( !empty($affiliations[$x]) and preg_match('#[\\\\]#u', $affiliations[$x] ) )
                $validation_result .= "WARNING: Affiliation " . ($x+1) . " contains suspicious looking special characters.\n" ;
            if ( strpos($all_appearing_affiliations, (string)($x+1) ) === false)
                $validation_result .= "ERROR: Affiliation " . ($x+1) . " is not associated to any authors.\n" ;
            if ( strpos($all_appearing_affiliations, (string)($x) ) !== false and strpos($all_appearing_affiliations, (string)($x+1) ) !== false and strpos($all_appearing_affiliations, (string)($x) ) > strpos($all_appearing_affiliations, (string)($x+1) ) )
                $validation_result .= "ERROR: Affiliation " . ($x) . " appears after first appearance of " . ($x+1) . "\n" ;
        }

        if ( empty($corresponding_author_email) )
            $validation_result .= "ERROR: Corresponding author email is empty.\n" ;
        else if(!O3PO_Utility::valid_email($corresponding_author_email))
            $validation_result .= "ERROR: Corresponding author email is malformed.\n" ;

            // Generate Crossref xml
        $timestamp = time();
        $crossref_xml_doi_batch_id = $this->generate_crossref_xml_doi_batch_id($post_id, $timestamp);
        $crossref_xml = $this->generate_crossref_xml($post_id, $crossref_xml_doi_batch_id, $timestamp);
        update_post_meta( $post_id, $post_type . '_crossref_xml', wp_slash($crossref_xml) ); // see https://codex.wordpress.org/Function_Reference/update_post_meta for why we have to used wp_slash() here and not addslashes()
        update_post_meta( $post_id, $post_type . '_crossref_xml_doi_batch_id', wp_slash($crossref_xml_doi_batch_id) ); // see https://codex.wordpress.org/Function_Reference/update_post_meta for why we have to used wp_slash() here and not addslashes()
        if(strpos($crossref_xml, 'ERROR') !== false)
            $validation_result .= $crossref_xml . "\n";

            // Generate DOAJ json
        $doaj_json = $this->generate_doaj_json($post_id);
        update_post_meta( $post_id, $post_type . '_doaj_json', wp_slash($doaj_json) ); // see https://codex.wordpress.org/Function_Reference/update_post_meta for why we have to used wp_slash() here and not addslashes()
        if(strpos($doaj_json, 'ERROR') !== false)
            $validation_result .= $doaj_json . "\n";

            // Generate CLOCKSS xml
        $clockss_xml = $this->generate_clockss_xml($post_id);
        update_post_meta( $post_id, $post_type . '_clockss_xml', wp_slash($clockss_xml) ); // see https://codex.wordpress.org/Function_Reference/update_post_meta for why we have to used wp_slash() here and not addslashes()
        if(strpos($clockss_xml, 'ERROR') !== false)
            $validation_result .= $clockss_xml . "\n";

            /*
             * Now we start interfacing with external services.
             *
             * We only do this if no ERRORs have occurred and there are
             * no outstanding REVIEWs.
             *
             * Crossref is the most critical and we hence we do it first.
             * We want the published status of the post to track the
             * registration of the DOI as closely as possible. Hence, if
             * there are any ERRORs or REVIEWs from before or during the
             * Crossref phase, we force the post private.
             */

        try
        {
                /* If there were no errors and there are no outstadning requests
                 * to review we attept to submit meta-data to Crossref. */
            if( get_post_status($post_id) === 'publish' and strpos($validation_result, 'ERROR') === false and strpos($validation_result, 'REVIEW') === false) {
                    /* On the test system we submit to the Crossref test system and not to the real system.*/
                $crossref_url = $this->environment->is_test_environment() ? $this->get_journal_property('crossref_test_deposite_url') : $this->get_journal_property('crossref_deposite_url');
                    //Upload meta-data to Crossref
                $crossref_response = $this->upload_meta_data_to_crossref($crossref_xml_doi_batch_id, $crossref_xml,
                                                                         $this->get_journal_property('crossref_id'),
                                                                         $this->get_journal_property('crossref_pw'),
                                                                         $crossref_url
                                                                         );
                if(!empty($crossref_response) && strpos($crossref_response, 'ERROR') !== false )
                    $validation_result .= $crossref_response;
                else
                    $validation_result .= "INFO: DOI successfully registered/updated at " . $crossref_url . "\n";
            }
            else
                $crossref_response = NULL;
            update_post_meta( $post_id, $post_type . '_crossref_response', $crossref_response );
        } catch(Exception $e) {
            $validation_result .= "ERROR: There was an exception while registering the DOI with Crossref: " . $e->getMessage() . "\n";
        } finally {
                /* Force the post private until everything validates without errors,
                 * there are no outstadning requests to review, and the Crossref
                 * registation was successful. */
            if( strpos($validation_result, 'ERROR') !== false or strpos($validation_result, 'REVIEW') !== false) {
                if ( get_post_status( $post_id ) === 'publish' or get_post_status( $post_id ) === 'future' )
                    wp_update_post(array('ID' => $post_id, 'post_status' => 'private'));
            }
        }

            //If all is still well, we do all the other external services
        if( strpos($validation_result, 'ERROR') === false or strpos($validation_result, 'REVIEW') === false)
        {
                //Upload meta-data to DOAJ
            $eissn = $this->get_journal_property('eissn');
            if(!empty($eissn) && get_post_status($post_id) === 'publish' && !$this->environment->is_test_environment())
            {
                $doaj_api_url = $this->get_journal_property('doaj_api_url');
                $doaj_api_key = $this->get_journal_property('doaj_api_key');
                $doaj_response = $this->upload_meta_data_to_doaj($doaj_json, $doaj_api_url, $doaj_api_key);

                if(strpos($doaj_response, 'ERROR') !== false || strpos($doaj_response, 'WARNING') !== false)
                    $validation_result .= $doaj_response;
                else
                    $validation_result .= "INFO: Meta-data successfully uploaded to DOAJ.\n";
            }
            else
                $doaj_response = NULL;
            update_post_meta( $post_id, $post_type . '_doaj_response', $doaj_response);

                //Upload meta-data and fulltext to CLOCKSS (only if a fulltext exists)
            $fulltext_pdf_path = $this->get_fulltext_pdf_path($post_id);
            if(!empty($fulltext_pdf_path) && get_post_status($post_id) === 'publish' && !$this->environment->is_test_environment())
            {
                $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
                $remote_filename_without_extension = $doi_suffix;
                $clockss_ftp_url = $this->get_journal_property('clockss_ftp_url');
                $clockss_username = $this->get_journal_property('clockss_username');
                $clockss_password = $this->get_journal_property('clockss_password');
                $clockss_response = $this->upload_meta_data_and_pdf_to_clockss($clockss_xml, $fulltext_pdf_path, $remote_filename_without_extension,
                                                                               $clockss_ftp_url, $clockss_username, $clockss_password);

                if(!empty($clockss_response) && ( strpos($clockss_response, 'ERROR') !== false || strpos($clockss_response, 'WARNING') !== false))
                    $validation_result .= $clockss_response;
                else
                    $validation_result .= "INFO: Meta-data and fulltext successfully uploaded to CLOCKSS.\n";
            }
            else
                $clockss_response = NULL;
            update_post_meta( $post_id, $post_type . '_clockss_response', $clockss_response );

            if(get_post_status($post_id) === 'publish')
                $validation_result .= $this->on_post_actually_published($post_id);
            elseif(get_post_status( $post_id ) === 'future')
                $validation_result .= "WARNING: This " . $post_type . " was scheduled for publication.\n";
        }

        return $validation_result;
    }


        /**
         * Put an update about the given publication post in the queue of buffer.com.
         *
         * @since     0.3.0
         * @access    private
         * @param     int    $post_id   Id of the post.
         * @return    string            The validation_result of the upload.
         */
    private function post_update_on_buffer_com_if_not_already_done( $post_id ) {

        $validation_result = '';
        $post_type = get_post_type($post_id);

        $buffer_api_url = $this->get_journal_property('buffer_api_url');
        $buffer_access_token = $this->get_journal_property('buffer_access_token');
        $buffer_profile_ids = $this->get_journal_property('buffer_profile_ids');

        if(!empty($buffer_api_url) and !empty($buffer_access_token) and !empty($buffer_profile_ids) and is_array($buffer_profile_ids))
        {
            $buffer_email = get_post_meta( $post_id, $post_type . '_buffer_email', true ); #we keep using the buffer_email and buffer_email_xxx fields for compatibility, even though the new buffer.com interface does no longer send emails but uses the buffer.com api
            $buffer_email_was_sent_date = get_post_meta( $post_id, $post_type . '_buffer_email_was_sent_date', true );
            $buffer_special_text = get_post_meta( $post_id, $post_type . '_buffer_special_text', true );

            if( $buffer_email === "checked" && empty($buffer_email_was_sent_date) ) {

                $title = get_post_meta( $post_id, $post_type . '_title', true );
                $authors = static::get_formated_authors($post_id);
        		$post_url = get_permalink( $post_id );
                $journal = get_post_meta( $post_id, $post_type . '_journal', true );
                $lead_ins = array(
                    "Published in " . $journal,
                    "Recently published in " . $journal,
                    "Fresh in " . $journal,
                    "New in " . $journal,
                    "Newly published in " . $journal,
                    "Now in " . $journal,
                    "Freshly published in " . $journal,
                    "A new publication in " . $journal,
                    "Accepted and published in " . $journal,
                    $journal . " has recently published",
                                  );
                $image_url = static::get_social_media_thumbnail_src($post_id);
                $subject = '';
                if(!empty($buffer_special_text))
                {
                    $subject .= $buffer_special_text;
                }
                else
                {
                    $subject .= $lead_ins[array_rand($lead_ins)];
                    $subject .= ': ' . $title . " by " . $authors;
                }
                if(mb_strlen($subject)+1+mb_strlen($post_url) > 280)
                    $subject = mb_substr($subject, 0, 280-(1+mb_strlen($post_url)-3)) . '...';
                $subject .= ' ' . $post_url;
                $media = array(
                    'photo' => $image_url,
                    'link' => $post_url,
                               );

                $response = O3PO_Buffer::create_update( $buffer_api_url, $buffer_access_token, $buffer_profile_ids, $subject, $media, false);

                if(is_wp_error($response))
                    $validation_result .= 'ERROR: Posting update on buffer.com about this publication failed: ' . $response->get_error_message() . "\n";
                else
                {
                    update_post_meta( $post_id, $post_type . '_buffer_email_was_sent_date', date("Y-m-d"));
                    $validation_result .= 'INFO: Update about this publication posted to buffer.com queue.' . "\n";
                }
            }
        }

        return $validation_result;
    }


        /**
         * Do things when the post is finally published.
         *
         * Is called from validate_and_process_data().
         *
         * @since     0.1.0
         * @access    protected
         * @param     int    $post_id   Id of the post that is actually beeing finally published publicly.
         */
    protected function on_post_actually_published( $post_id ) {

        $post_type = get_post_type($post_id);
        $validation_result = 'INFO: This ' . $post_type . " was publicly published.\n";

        if(!$this->environment->is_test_environment())
            $validation_result .= $this->post_update_on_buffer_com_if_not_already_done($post_id);

        return $validation_result;
    }

        /**
         * Add the custom post type of this publication type to the main query.
         *
         * To be added to the 'pre_get_posts' action.
         *
         * @since    0.1.0
         * @access   public
         * @param    WP_query    $query    The current query to which the post type is potentially to be added.
         */
    public final function add_custom_post_types_to_query( $query ) {

            //$query->get('post_type') can either an array or a string
        if(is_array($query->get('post_type')))
            $get_post_type_as_array = $query->get('post_type');
        else
            $get_post_type_as_array = array($query->get('post_type'));

            /* Wordpress shows all posts of type 'post' when $query->get('post_type') is empty
             * and also when it contains only the empty string ''.
             * We want to add the custom post type and not loose regular posts, so we have to
             * add 'post' explicitly.
             * See also: https://wordpress.stackexchange.com/questions/311446/adding-custom-post-type-to-queries-doing-it-the-right-way */
        if ( empty($get_post_type_as_array))
            $get_post_type_as_array = array('post');
        if(in_array('', $get_post_type_as_array) and !in_array('post', $get_post_type_as_array))
            $get_post_type_as_array = array_merge( $get_post_type_as_array, array('post'));

            //only add custom post type to queries that contain regular 'post's
        if(!in_array('post', $get_post_type_as_array))
            return;

        $new_post_type = array_merge( $get_post_type_as_array, array($this->get_publication_type_name()) );

        if ( (is_home() && $query->is_main_query()) or is_category() )
            $query->set( 'post_type', $new_post_type );

    }

        /**
         * Add the custom post type of this publication to the rss feed.
         *
         * To be added to the 'request' filter.
         *
         * @since    0.1.0
         * @access   public
         * @param    WP_query    $query    The query to which this post type is maybe to be added.
         */
    public final function add_custom_post_types_to_rss_feed( $query ) {

        if (isset($query['feed']) && !isset($query['post_type']))
            $query['post_type'] = array_merge( array( 'post' ), array($this->get_publication_type_name()) );
        return $query;
    }
        /*     /\* We want custom posts to appear in the rss feed. We do this by */
        /*      * adding this function to the 'request' filter.*\/ */
        /* public static final function add_custom_post_types_to_rss_feed($qv) { */
        /*     if (isset($qv['feed']) && !isset($qv['post_type'])) */
        /*         $qv['post_type'] = array_merge( array( 'post' ) , static::get_active_publication_type_names() ); */
        /*     return $qv; */
        /* } */


        /**
         * Modify the author reported on the feed for publication posts.
         *
         * To be added to the 'the_author' filter.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $display_name    Original author name.
         */
    public function the_author_feed( $display_name ) {
        global $post;
        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if ( is_feed() && $post_type === $this->get_publication_type_name() ) {
            return esc_html($this->get_formated_authors($post_id));
        }
        else
        {
            return $display_name;
        }
    }
        /* Should we make this non static and then modify this post type? */
        /*     /\* We return a fake author name for publication type posts */
        /*      * on the rss feed.*\/ */
        /* public static function the_author_feed( $display_name ) { */
        /*     global $post; */
        /*     $post_id = $post->ID; */
        /*     $post_type = get_post_type($post_id); */

        /*     if ( is_feed() && in_array( $post_type, static::get_active_publication_type_names() ) ) { */
        /*         return esc_html(static::get_formated_authors($post_id)); */
        /*     } */
        /*     else */
        /*     { */
        /*         return $display_name; */
        /*     } */
        /* } */

        /**
         * Add Dublin Core and Highwire Press meta tags.
         *
         * Add meta information for Google Scholar (for more info on the
         * available tags see http://webmasters.stackexchange.com/questions/11613/indexing-for-google-scholar-which-tags-to-use)
         * and similar services. This is called from the single template.
         *
         * To be added to the 'wp_head' action.
         *
         * @since    0.1.0
         * @access   public
         * */
    public function add_dublin_core_and_highwire_press_meta_tags() {
        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        if ( !is_single() )
            return;

        if($post_type !== $this->get_publication_type_name())
            return;

        $title = get_post_meta( $post_id, $post_type . '_title', true );
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );
        $volume = get_post_meta( $post_id, $post_type . '_volume', true );
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        $number_affiliations = get_post_meta( $post_id, $post_type . '_number_affiliations', true );
        $affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        $author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        $volume = get_post_meta( $post_id, $post_type . '_volume', true );
        $pages = get_post_meta( $post_id, $post_type . '_pages', true );
		$bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
        $doi = static::get_doi($post_id);
        $publisher = $this->get_journal_property('publisher');

            // Highwire Press tags (More info on commonly used tags can be found here https://gist.github.com/hubgit/5985963)
        if(!empty($title)) echo '<meta name="citation_title" content="' . esc_attr( $title ) . '">'."\n";
        if(!empty($number_authors))
        {
            for ($x = 0; $x < $number_authors; $x++) {
                if(!empty($author_surnames[$x])) echo '<meta name="citation_author" content="' . esc_attr($author_given_names[$x] . " " . $author_surnames[$x]) . '">'."\n";
                if ( !empty($author_affiliations) && !empty($author_affiliations[$x]) ) {
                    foreach(preg_split('/\s*,\s*/u', $author_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY) as $affiliation_num) {
                        if ( !empty($affiliations[$affiliation_num-1]) )
                            echo '<meta name="citation_author_institution" content="' . esc_attr($affiliations[$affiliation_num-1]) . '">' . "\n";
                    }
                }
            }
        }
        if(!empty($date_published)) echo '<meta name="citation_publication_date" content="' . preg_replace('/-/u', '/', $date_published ) . '">'."\n";
        if(!empty($journal)) echo '<meta name="citation_journal_title" content="' . $journal . '">'."\n";
        if(!empty($volume)) echo '<meta name="citation_volume" content="' . $volume . '">'."\n";
        if(!empty($pages)) echo '<meta name="citation_firstpage" content="' . $pages . '">'."\n";
        if(!empty($doi)) {
            echo '<meta name="citation_doi" content="' . esc_attr($doi) . '">'."\n";
            echo '<meta name="doi" content="' . esc_attr($doi) . '">'."\n";
        }

		if ( !empty($bbl) ) {
			$parsed_bbl = O3PO_Latex::parse_bbl($bbl);
			if( !empty($parsed_bbl) ) {
				foreach($parsed_bbl as $n => $entry) {
                    if(!empty($entry['text']))
                    {
                        $citation_reference_meta_tag  = '<meta name="citation_reference" content="';
                        $citation_reference_meta_tag .= esc_attr($entry['text']);
                        $citation_reference_meta_tag .= '">'."\n";
                        echo $citation_reference_meta_tag;
                    }
                }
            }
        }

            // Dublin Core
        if(!empty($title)) echo '<meta name="dc.title" content="' . esc_attr($title) . '" />' . "\n";
        if(!empty($publisher) )echo '<meta name="dc.publisher" content="' . esc_attr($publisher) . '" />' . "\n";
        if(!empty($date_published)) echo '<meta name="dc.date" scheme="W3CDTF" content="' . $date_published . '" />' . "\n";
        if(!empty($doi)) {
            echo '<meta name="dc.identifyer" content="' . esc_attr($doi) . '" />' . "\n";
            echo '<meta name="dc.doi" content="' . esc_attr($doi) . '" />' . "\n";
            echo '<meta name="dc.identifyer.doi" content="' . esc_attr($doi) . '" />' . "\n";
        }

    }

        /**
         * Indiviual publications of a given type are represented
         * by posts of the corresponding custom post type. This class
         * also defines various getter methods to get the properties
         * of such a concrete publication from the $post_id of the
         * post. These methods are statless and hence can be
         * declared static by retreiving the post type via
         * get_post_type($post_id).
         * */

        /**
         * Get the DOI of the post with id $post_id.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id    The id of the post for which to get the doi.
         */
    public static function get_doi( $post_id ) {

        $post_type = get_post_type($post_id);
        $doi_prefix = get_post_meta( $post_id, $post_type . '_doi_prefix', true );
        $doi_suffix = get_post_meta( $post_id, $post_type . '_doi_suffix', true );
        if( !empty($doi_prefix) && !empty($doi_suffix) )
            return $doi_prefix . '/' . $doi_suffix;
        else
            return '';
    }

        /**
         * Get the volume of the post with id $post_id.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id    The id of the post for which to get the volume.
         */
    public static function get_volume( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_volume', true );
    }

        /**
         * Get the page of the post with id $post_id.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id    The id of the post for which to get the page.
         */
    public static function get_page( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_page', true );
    }

        /**
         * Get how this $post_id should be cited.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id   Id of the post for which the formated citation is to be returned
         */
    public static function get_formated_citation( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_journal', true ) . ' ' . get_post_meta( $post_id, $post_type . '_volume', true ) . ', ' . get_post_meta( $post_id, $post_type . '_pages', true ) . ' (' . mb_substr( get_post_meta( $post_id, $post_type . '_date_published', true ), 0, 4 ) . ').';

    }

        /**
         * Generate a doi batch id for coressref
         *
         * Cossref requires us to generate a unique id for each doi batch
         * submission. Here we do this by adding a timestamp to the doi
         * of the post we want to register.
         *
         * @since    0.1.0
         * @access   public
         * @param    int        $post_id      Id of the post for which to generate the doi batch id.
         * @param    string     $timestamp    Timestamp of the submission for which the requested batch id is to be used.
         */
    public static function generate_crossref_xml_doi_batch_id( $post_id, $timestamp ) {

        $post_type = get_post_type($post_id);

        return get_post_meta( $post_id, $post_type . '_doi_prefix', true ) . '-' . get_post_meta( $post_id, $post_type . '_doi_suffix', true ) . '-' . $timestamp;
    }

        /**
         * Generate an xml representation of the meta-data of that post
         * suitable for crossref.
         *
         * This function return a string containing xml formated meta-data about the
         * given $post_id that can be uploaded to and then processed by Crossref.
         *
         * @since    0.1.0
         * @access   public
         * @param    int       $post_id         Id of the post for which the crossref xml is to be generated.
         * @param    string    $doi_batch_id    Batch id of the submission for which the crossref xml is to be generated.
         * @param    string    %timestamp       Timestamp of the submission for which the crossref xml is to be generated.
         */
    public function generate_crossref_xml( $post_id, $doi_batch_id, $timestamp ) {

        $post_type = get_post_type($post_id);

        if(empty($post_type)) return 'ERROR: Unable to generate XML for Crossref, post_type is empty';
        $title = get_post_meta( $post_id, $post_type . '_title', true );
        if(empty($title)) return 'ERROR: Unable to generate XML for Crossref, title is empty';
        $title_mathml = get_post_meta( $post_id, $post_type . '_title_mathml', true );
        $abstract = get_post_meta( $post_id, $post_type . '_abstract', true );
        $abstract_mathml = get_post_meta( $post_id, $post_type . '_abstract_mathml', true );
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        if(empty($number_authors)) return 'ERROR: Unable to generate XML for Crossref, number_authors is empty';
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        if(empty($author_given_names)) return 'ERROR: Unable to generate XML for Crossref, author_given_names is empty';
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        if(empty($author_surnames)) return 'ERROR: Unable to generate XML for Crossref, author_surnames is empty';
        $author_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_name_styles');
        if(empty($author_name_styles)) return 'ERROR: Unable to generate XML for Crossref, author_name_styles is empty';
        $author_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_orcids');
        $author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );
        if(empty($date_published)) return 'ERROR: Unable to generate XML for Crossref, date_published is empty';
        $pages = get_post_meta( $post_id, $post_type . '_pages', true );
        if(empty($pages)) return 'ERROR: Unable to generate XML for Crossref, pages is empty';
        $doi = static::get_doi($post_id);
        if(empty($doi)) return 'ERROR: Unable to generate XML for Crossref, doi is empty';
        $affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        if(empty($journal)) return 'ERROR: Unable to generate XML for Crossref, journal is empty';
        if($journal !== $this->get_journal_property('journal_title')) return 'ERROR: Unable to generate XML for Crossref, journal of the post and publication type do not match';
        $volume = get_post_meta( $post_id, $post_type . '_volume', true );
        if(empty($volume)) return 'ERROR: Unable to generate XML for Crossref, volume is empty';
        $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
        $post_url = get_permalink( $post_id );
        if(empty($post_url)) return 'ERROR: Unable to generate XML for Crossref, url is empty';
        $pdf_pretty_permalink = $this->get_pdf_pretty_permalink($post_id);

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<doi_batch version="4.4.0" xmlns="http://www.crossref.org/schema/4.4.0" xmlns:mml="http://www.w3.org/1998/Math/MathML" xmlns:jats="http://www.ncbi.nlm.nih.gov/JATS1" xmlns:ai="http://www.crossref.org/AccessIndicators.xsd">' . "\n";
        $xml .= '  <head>' . "\n";
            //a unique id for each batch
        $xml .= '    <doi_batch_id>' . $doi_batch_id . '</doi_batch_id>' . "\n";
            /* timestamp for batch integer representation of date and time that serves as a
             * version number for the record that is being deposited. Because CrossRef uses it as a
             * version number, the format need not follow any public standard and therefore the
             * publisher can determine the internal format. The schema format is a double of at
             * least 64 bits */
        $xml .= '    <timestamp>' . $timestamp . '</timestamp>' . "\n";
        $xml .= '    <depositor>' . "\n";

        #Test for test environment should not be done here but futher up the class hierarchy!

        $xml .= '      <depositor_name>' . esc_html($this->get_journal_property('publisher')) . '</depositor_name>' . "\n";
        $xml .= '      <email_address>' . esc_html($this->environment->is_test_environment() ? $this->get_journal_property('developer_email') : $this->get_journal_property('crossref_email') ) . '</email_address>' . "\n";
        $xml .= '    </depositor>' . "\n";
        $xml .= '    <registrant>' . esc_html($this->get_journal_property('publisher')) . '</registrant>' . "\n";
        $xml .= '  </head>' . "\n";
        $xml .= '  <body>' . "\n";
        $xml .= '    <journal>' . "\n";
        $xml .= '      <journal_metadata language="en" metadata_distribution_opts="any" reference_distribution_opts="any">' . "\n";
        $xml .= '	<full_title>' . esc_html($journal) . '</full_title>' . "\n";
        $xml .= '	<abbrev_title>' . esc_html($journal) . '</abbrev_title>' . "\n";
        if(!empty($this->get_journal_property('eissn')))
            $xml .= '	<issn media_type="electronic">' . $this->get_journal_property('eissn') . '</issn>' . "\n";
            // we don't have a coden
            // $xml .= '	<coden></coden>' . "\n";
            // Options for archive names are: CLOCKSS, LOCKSS Portico, KB, DWT, Internet Archive.
        if(!empty($this->get_journal_property('crossref_archive_locations')) && $journal === $this->get_journal_property('journal_title'))
        {
            $xml .= '	<archive_locations>' . "\n";
            foreach(preg_split('/\s*,\s*/u', $this->get_journal_property('crossref_archive_locations'))  as $archive_name)
                $xml .= '	  <archive name="' . esc_attr(trim($archive_name)) . '"></archive>' . "\n";
            $xml .= '	</archive_locations>' . "\n";
        }
        $xml .= '	<doi_data>' . "\n";
            // Add the journal level DOI of the journal
        if( !empty($this->get_journal_property('doi_prefix')) && !empty($this->get_journal_property('journal_level_doi_suffix')) )
            $xml .= '	  <doi>' . $this->get_journal_property('doi_prefix') .'/' . $this->get_journal_property('journal_level_doi_suffix') . '</doi>' . "\n";
            // timestamp for journal level doi data, not mandatory if already given in head
            // $xml .= '	  <timestamp></timestamp>' . "\n";
        $xml .= '	  <resource mime_type="text/html">' . get_site_url() . '</resource>' . "\n";
        $xml .= '	</doi_data>' . "\n";
        $xml .= '      </journal_metadata>' . "\n";
            // We don't have issues but volumes
        $xml .= '      <journal_issue>' . "\n";
        $xml .= '	     <publication_date media_type="online">' . "\n";
        $xml .= '	       <month>' . mb_substr($date_published, 5, 2) . '</month>' . "\n";
        $xml .= '	       <day>' . mb_substr($date_published, 8, 2) .'</day>' . "\n";
        $xml .= '	       <year>' . mb_substr($date_published, 0, 4) . '</year>' . "\n";
        $xml .= '	     </publication_date>' . "\n";
        $xml .= '	     <journal_volume>' . "\n";
        $xml .= '	       <volume>' . $volume . '</volume>' . "\n";
            //$xml .= '	         <publisher_item>{0,1}</publisher_item>' . "\n";
            //$xml .= '	         <archive_locations>{0,1}</archive_locations>' . "\n";
            //$xml .= '	         <doi_data>{0,1}</doi_data>' . "\n";
        $xml .= '	     </journal_volume>' . "\n";
        $xml .= '      </journal_issue>' . "\n";
        $xml .= '      <journal_article language="en" metadata_distribution_opts="any" publication_type="full_text" reference_distribution_opts="any">' . "\n";
        $xml .= '	<titles>' . "\n";
            // Minimal face markup and MathML are supported in the title
        $xml .= '	  <title>' . (!empty($title_mathml) ? $title_mathml : esc_html($title)) . '</title>' . "\n";
            // $xml .= '	  <subtitle>{0,1}</subtitle>' . "\n";
            // $xml .= '	  <original_language_title language="">{1,1}</original_language_title>' . "\n";
            // $xml .= '	  <subtitle>{0,1}</subtitle>' . "\n";
        $xml .= '	</titles>' . "\n";
        $xml .= '	<contributors>' . "\n";
            // we only have authors
            // $xml .= '	  <organization contributor_role="" language="" name-style="" sequence="">{1,1}</organization>' . "\n";
        for ($x = 0; $x < $number_authors; $x++) {
            $xml .= '	  <person_name contributor_role="author" sequence="' . ($x === 0 ? "first" : "additional") . '"';
            if ( !empty($author_name_styles[$x]) )
                $xml .= ' name-style="' . $author_name_styles[$x] . '"';
            $xml .= '>' . "\n";
            if ( !empty($author_given_names[$x]) )
                $xml .= '	    <given_name>' . esc_html($author_given_names[$x]) . '</given_name>' . "\n";
            $xml .= '	    <surname>' . esc_html($author_surnames[$x]) . '</surname>' . "\n";
                // $xml .= '	    <suffix>{0,1}</suffix>' . "\n";
            if ( !empty($author_affiliations) && !empty($author_affiliations[$x]) ) {
                foreach(preg_split('/\s*,\s*/u', $author_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY) as $affiliation_num) {
                    if ( !empty($affiliations[$affiliation_num-1]) )
				     	$xml .= '	    <affiliation>' . esc_html($affiliations[$affiliation_num-1]) . '</affiliation>' . "\n";
                }
            }
            if ( !empty($author_orcids) && !empty($author_orcids[$x]) )
                $xml .= '	    <ORCID authenticated="false">' . $this->get_journal_property('orcid_url_prefix') . $author_orcids[$x] . '</ORCID>' . "\n";
                // $xml .= '	    <alt-name>{0,1}</alt-name>' . "\n";
            $xml .= '	  </person_name>' . "\n";
        }
        $xml .= '	</contributors>' . "\n";
        if( !empty($abstract) || !empty($abstract_mathml) )
        {
            $xml .= '	<jats:abstract xml:lang="en">' . "\n";
            $xml .= '	  <jats:p>' . (!empty($abstract_mathml) ? $abstract_mathml : esc_html($abstract)) . '</jats:p>' . "\n";
            $xml .= '	</jats:abstract>' . "\n";
        }
        $xml .= '	<publication_date media_type="online">' . "\n";
        $xml .= '	    <month>' . mb_substr($date_published, 5, 2) . '</month>' . "\n";
        $xml .= '	    <day>' . mb_substr($date_published, 8, 2) .'</day>' . "\n";
        $xml .= '	    <year>' . mb_substr($date_published, 0, 4) . '</year>' . "\n";
        $xml .= '	</publication_date>' . "\n";
            // we only have article numbers which should go into the publisher_item  below, but despite what Crossref says in their documentation they don't handle this propperly so we have to add it also here
        $xml .= '	<pages>' . "\n";
        $xml .= '	  <first_page>' . $pages . '</first_page>' . "\n";
            // $xml .= '	  <last_page>...</last_page>' . "\n";
        $xml .= '	</pages>' . "\n";
        $xml .= '	<publisher_item>' . "\n";
        $xml .= '	  <item_number item_number_type="article-number">' . $pages . '</item_number>' . "\n";
        $xml .= '	</publisher_item>' . "\n";
            // something to consider for the future
            // $xml .= '	<crossmark></crossmark>' . "\n";
            // for funding information, we don't do this currently
            // $xml .= '	<fr:program name="fundref">{0,1}</fr:program>' . "\n";
        $xml .= '	<ai:program name="AccessIndicators">' . "\n";
        $xml .= '	  <ai:free_to_read></ai:free_to_read>' . "\n";
        $xml .= '	  <ai:license_ref start_date="' . $date_published . '">' . esc_html($this->get_journal_property('license_url')) . '</ai:license_ref>' . "\n";
        $xml .= '	</ai:program>' . "\n";
            // for clinical trials, we don't have that
            // $xml .= '	<ct:program>{0,1}</ct:program>' . "\n";
            // for relations between programs
            // $xml .= '	<rel:program name="relations">{0,1}</rel:program>' . "\n";
            // we archive on the arXiv and not here
            // $xml .= '	<archive_locations><archive></archive></archive_locations>' . "\n";
        $xml .= '	<doi_data>' . "\n";
        $xml .= '	  <doi>' . esc_html($doi) . '</doi>' . "\n";
            // not mandatory if already given in head
            // $xml .= '	  <timestamp>...</timestamp>' . "\n";
            // URL to landing page, content_version can be vor (version of record) or am (advance manuscript).
        $xml .= '	  <resource content_version="am" mime_type="text/html">' . esc_url($post_url) . '</resource>' . "\n";
            // think we don't need this
            // $xml .= '	  <collection multi-resolution="" property="">{0,unbounded}</collection>' . "\n";
            // add full text link for text-mining
        if(!empty($pdf_pretty_permalink))
        {
            $xml .= '<collection property="text-mining">' . "\n";
            $xml .= '<item>' . "\n";
            $xml .= '<resource>' . "\n";
            $xml .= esc_url($pdf_pretty_permalink) . "\n";
            $xml .= '</resource>' . "\n";
            $xml .= '</item>' . "\n";
            $xml .= '</collection>' . "\n";
        }
        $xml .= '	</doi_data>' . "\n";
            // the references
        if( !empty($bbl) ) {
            $parsed_bbl = O3PO_Latex::parse_bbl($bbl);
            if( !empty($parsed_bbl) ) {

                $xml .= '	<citation_list>' . "\n";
                foreach($parsed_bbl as $n => $entry) {
                    $xml .= '	  <citation key="' . $n . '">' . "\n";
                    if( !empty($entry['doi']) )
                        $xml .= '	    <doi>' . esc_html($entry['doi']) . '</doi>' . "\n";
                    $xml .= '	    <unstructured_citation>' . esc_html($entry['text']) . '</unstructured_citation>' . "\n";
                    $xml .= '	  </citation>' . "\n";
                }
                $xml .= '	</citation_list>' . "\n";
            }
        }
            // we don't usually have components, just single articles
            // $xml .= '	<component_list>{0,1}</component_list>' . "\n";
        $xml .= '      </journal_article>' . "\n";
        $xml .= '    </journal>' . "\n";
        $xml .= '  </body>' . "\n";
        $xml .= '</doi_batch>' . "\n";

        return $xml;
    }

        /**
         * Submit meta-data to Crossref.
         *
         * This function must be private since we do no longer check internally whether we are running on the test system.
         *
         * @since    0.1.0
         * @access   private
         * @param    string   $doi_batch_id    Batch id of this upload.
         * @param    string   $crossref_xml    The xml to upload.
         * @param    string   $crossref_id     The id for which to submit this upload.
         * @param    string   $crossref_pw     The password corresponding to the crossref_id.
         * @param    string   $crossref_url    The url of the crossref server to upload to.
         * */
    private static function upload_meta_data_to_crossref( $doi_batch_id, $crossref_xml, $crossref_id, $crossref_pw, $crossref_url ) {

        if(empty($crossref_id) || empty($crossref_pw))
            return "WARNING: Crossref credential incomplete. Meta-data was not uploaded to Corssref.\n";

        $response = O3PO_Crossref::remote_post_meta_data_to_crossref($doi_batch_id, $crossref_xml, $crossref_id, $crossref_pw, $crossref_url);

		$crossref_response = $crossref_url . " responded at " . date('Y-m-d H:i:s') . " with:\n";
		if ( is_wp_error( $response ) ) {
			$crossref_response = 'ERROR: ' . $response->get_error_message();
		} else {
			$crossref_response .= trim($response['body']);
		}

        return $crossref_response;
    }

        /**
         * Submit meta-data to DOAJ.
         *
         * From the command line you could do rouhgly the smame via:
         *
         * curl -X POST --header "Content-Type: application/json" --header "Accept: application/json" -d "[json goes here]" "https://doaj.org/api/v1/articles?api_key=XXX"
         * see https://doaj.org/api/v1/docs#/ for more infomation.
         *
         * DOAJ has no test system, so that we can only get a response from the real system once we publish the final and actual record.
         *
         * This function must be private since we do no longer check internally whether we are running on the test system.
         *
         * @since    0.1.0
         * @access   private
         * @param    string     $doaj_json      The JSON encoded meta-data to upload.
         * @param    string     $doaj_api_url   The url of the DOAJ api to upload to.
         * @param    string     $doaj_api_key   The API key with DOAJ
         */
    private static function upload_meta_data_to_doaj( $doaj_json, $doaj_api_url, $doaj_api_key ) {

        if(empty($doaj_api_url) || empty($doaj_api_key))
            return "WARNING: DOAJ credentials incomplete. No attempt was made to uploaded meta-data to the DOAJ.\n";

        $response = O3PO_Doaj::remote_post_meta_data_to_doaj( $doaj_json, $doaj_api_url, $doaj_api_key );

        $doaj_response = $doaj_api_url . " responded at " . date('Y-m-d H:i:s') . " with:\n";
        if(is_wp_error($response))
            $doaj_response = 'ERROR: While submitting meta-data to DOAJ the following error occurred: ' . $response->get_error_message() . "\n";
        elseif(!isset($response['body']) or strpos($doaj_response, 'created') !== false)
        {
            $doaj_response = 'ERROR: Did not get the expected response from DOAJ. This may indicate a problem with the meta-data upload, please check manually:' . "\n" . trim($response['body']) . "\n";

        }
        else
            $doaj_response .= trim($response['body']);

        return $doaj_response;
    }


        /**
         * Submit meta-data and full text to CLOCKSS.
         *
         * Upload an xml file with the meta-data and the full text pdf to CLOCKSS.
         *
         * CLOCKSS has no test system, so that we can only get a response from the real system once we publish the final and actual record.
         *
         * This function must be private since we do no longer check internally whether we are running on the test system.
         *
         * @since    0.1.0
         * @access   private
         * @param    string     $clockss_xml      The xml encoded meta-data to upload.
         * @param    string     $pdf_path         Path to the local fulltext pdf
         * @param    string     $remote_filename_without_extension Filename without extension under which the files are to be deposited on the remore server
         * @param    string     $clockss_ftp_url  The url of the CLOCKSS ftp server.
         * @param    string     $clockss_username The CLOCKSS username
         * @param    string     $clockss_password The CLOCKSS password
         */
    private static function upload_meta_data_and_pdf_to_clockss( $clockss_xml, $pdf_path, $remote_filename_without_extension, $clockss_ftp_url, $clockss_username, $clockss_password ) {

        if(empty($clockss_username) || empty($clockss_password))
            return "WARNING: CLOCKKS credentials incomplete. No attempt was made to upload meta-data and full text to CLOCKSS.\n";

        $clockss_response = O3PO_Clockss::ftp_upload_meta_data_and_pdf_to_clockss($clockss_xml, $pdf_path, $remote_filename_without_extension, $clockss_ftp_url, $clockss_username, $clockss_password);

        return $clockss_response;
    }


        /**
         * Generate xml suitable for the submission to CLOCKSS
         *
         * This function returns a string containing xml formated according to the JATS
         * scheme suitable for CLOCKSS.
         *
         * @since    0.1.0
         * @access   public
         * @param    int     $post_id    Id of the post for which to generate the clockss xml.
         * */
    public function generate_clockss_xml( $post_id ) {

        $post_type = get_post_type($post_id);

        if(empty($post_type)) return 'ERROR: Unable to generate XML for CLOCKSS, post_type is empty';
        $title = get_post_meta( $post_id, $post_type . '_title', true );
        if(empty($title)) return 'ERROR: Unable to generate XML for CLOCKSS, title is empty';
        $title_mathml = get_post_meta( $post_id, $post_type . '_title_mathml', true );
        $abstract = get_post_meta( $post_id, $post_type . '_abstract', true );
        $abstract_mathml = get_post_meta( $post_id, $post_type . '_abstract_mathml', true );
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        if(empty($number_authors)) return 'ERROR: Unable to generate XML for CLOCKSS, number_authors is empty';
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        if(empty($author_given_names)) return 'ERROR: Unable to generate XML for CLOCKSS, author_given_names is empty';
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        if(empty($author_surnames)) return 'ERROR: Unable to generate XML for CLOCKSS, author_surnames is empty';
        $author_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_name_styles');
        if(empty($author_name_styles)) return 'ERROR: Unable to generate XML for CLOCKSS, author_name_styles is empty';
        $author_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_orcids');
        $author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );
        if(empty($date_published)) return 'ERROR: Unable to generate XML for CLOCKSS, date_published is empty';
        $pages = get_post_meta( $post_id, $post_type . '_pages', true );
        if(empty($pages)) return 'ERROR: Unable to generate XML for CLOCKSS, pages is empty';
        $doi = static::get_doi($post_id);
        if(empty($doi)) return 'ERROR: Unable to generate XML for CLOCKSS, doi is empty';
        $affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        if(empty($journal)) return 'ERROR: Unable to generate XML for CLOCKSS, journal is empty';
        if($journal !== $this->get_journal_property('journal_title')) return 'ERROR: Unable to generate XML for CLOCKSS, journal of the post and publication type do not match';
        $volume = get_post_meta( $post_id, $post_type . '_volume', true );
        if(empty($volume)) return 'ERROR: Unable to generate XML for CLOCKSS, volume is empty';
        $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
        $post_url = get_permalink( $post_id );
        if(empty($post_url)) return 'ERROR: Unable to generate XML for CLOCKSS, url is empty';

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<article xmlns:mml="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" article-type="research-article" dtd-version="1.1" xml:lang="en">' . "\n";
        $xml .= '  <front>' . "\n";

        $xml .= '    <journal-meta>' . "\n";
        $xml .= '      <journal-id journal-id-type="publisher">' . esc_html($journal) . '</journal-id>' . "\n";
        if(!empty($this->get_journal_property('eissn')) && $journal === $this->get_journal_property('journal_title'))
            $xml .= '      <issn>' . $this->get_journal_property('eissn') . '</issn>' . "\n";
        /* elseif(!empty($this->get_journal_property('secondary_journal_eissn')) && $journal === $this->get_journal_property('secondary_journal_title')) */
        /*     $xml .= '      <issn>' . $this->get_journal_property('secondary_journal_eissn') . '</issn>' . "\n"; */

        $xml .= '      <publisher>' . "\n";
        $xml .= '        <publisher-name>' . esc_html( $this->get_journal_property('publisher')) . '</publisher-name>' . "\n";
        $xml .= '      </publisher>' . "\n";
        $xml .= '    </journal-meta>' . "\n";

        $xml .= '    <article-meta>' . "\n";
        $xml .= '      <article-id pub-id-type="doi">' . esc_html($doi) . '</article-id>' . "\n";
        $xml .= '      <title-group>' . "\n";
        $xml .= '        <article-title>' . "\n";
        $xml .= '          ' . (!empty($title_mathml) ? $title_mathml : esc_html($title)) . "\n";
        $xml .= '        </article-title>' . "\n";
        $xml .= '      </title-group>' . "\n";

        $xml .= '      <contrib-group>' . "\n";
        for ($x = 0; $x < $number_authors; $x++) {
            $xml .= '        <contrib contrib-type="author">' . "\n";
            $xml .= '          <name>' . "\n";
            $xml .= '            <surname>' . esc_html($author_surnames[$x]) . '</surname>' . "\n";
            $xml .= '            <given-names>' . esc_html($author_given_names[$x]) . '</given-names>' . "\n";
            $xml .= '          </name>' . "\n";
            if ( !empty($author_affiliations) && !empty($author_affiliations[$x]) ) {
                foreach(preg_split('/\s*,\s*/u', $author_affiliations[$x], -1, PREG_SPLIT_NO_EMPTY) as $affiliation_num) {
                    $xml .= '          <xref ref-type="aff" rid="aff-' . $affiliation_num . '"/>' . "\n";
                }
            }
            $xml .= '        </contrib>' . "\n";
        }
        $xml .= '      </contrib-group>' . "\n";
        foreach($affiliations as $n => $affiliation)
            $xml .= '      <aff id="aff-' . ($n+1) . '">' . esc_html($affiliation) . '</aff>' . "\n";

        $xml .= '      <pub-date date-type="pub" publication-format="electronic" iso-8601-date="' . $date_published . '">' . "\n";
        $xml .= '        <day>' . mb_substr($date_published, 8, 2) . '</day>' . "\n";
        $xml .= '        <month>' . mb_substr($date_published, 5, 2) . '</month>' . "\n";
        $xml .= '        <year>' . mb_substr($date_published, 0, 4) . '</year>' . "\n";
        $xml .= '      </pub-date>' . "\n";
        $xml .= '      <volume>' . $volume . '</volume>' . "\n";
//        $xml .= '  <issue>18</issue>' . "\n";
        $xml .= '      <fpage>' . $pages . '</fpage>' . "\n";
//        $xml .= '  <lpage>10219</lpage>' . "\n";
        $xml .= '      <permissions>' . "\n";
        $xml .= '        <copyright-statement>' . 'This work is published under the ' . esc_html($this->get_journal_property('license_name')) . ' license ' . esc_html($this->get_journal_property('license_url')) . '.' . '</copyright-statement>' . "\n";
        $xml .= '        <copyright-year>' . mb_substr($date_published, 0, 4) .'</copyright-year>' . "\n";
        $xml .= '      </permissions>' . "\n";
        if( !empty($abstract) || !empty($abstract_mathml) )
        {
            $xml .= '      <abstract>' . "\n";
            $xml .= '        <p>' . "\n";
            $xml .= '          ' . (!empty($abstract_mathml) ? $abstract_mathml : esc_html($abstract)) . "\n";
            $xml .= '        </p>' . "\n";
            $xml .= '      </abstract>' . "\n";
        }
        $xml .= '    </article-meta>' . "\n";

        $xml .= '  </front>' . "\n";

        $xml .= '  <body></body>' . "\n";
        $xml .= '  <back></back>' . "\n";
        $xml .= '</article>' . "\n";

        return $xml;
    }


        /**
         * Generate json suitable for the submission to DOAJ
         *
         * This function returns a json encoded string containing the meta-data in a format
         * suitable for DOAJ.
         *
         * TODO: Clean this up once we got some feedback from DOAJ
         *
         * @since    0.1.0
         * @acccess  public
         * @param    int     $post_id    Id of the post for which to generate the the json encoded meta-data.
         */
    public function generate_doaj_json( $post_id ) {

        $post_type = get_post_type($post_id);

        if(empty($post_type)) return 'ERROR: Unable to generate JSON for DOAJ, post_type is empty';
        $title = get_post_meta( $post_id, $post_type . '_title', true );
        if(empty($title)) return 'ERROR: Unable to generate JSON for DOAJ, title is empty';
        $title_mathml = get_post_meta( $post_id, $post_type . '_title_mathml', true );
        $abstract = get_post_meta( $post_id, $post_type . '_abstract', true );
        $abstract_mathml = get_post_meta( $post_id, $post_type . '_abstract_mathml', true );
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        if(empty($number_authors)) return 'ERROR: Unable to generate JSON for DOAJ, number_authors is empty';
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        if(empty($author_given_names)) return 'ERROR: Unable to generate JSON for DOAJ, author_given_names is empty';
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        if(empty($author_surnames)) return 'ERROR: Unable to generate JSON for DOAJ, author_surnames is empty';
        $author_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_name_styles');
        if(empty($author_name_styles)) return 'ERROR: Unable to generate JSON for DOAJ, author_name_styles is empty';
        $author_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_orcids');
        $author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );
        if(empty($date_published)) return 'ERROR: Unable to generate JSON for DOAJ, date_published is empty';
        $pages = get_post_meta( $post_id, $post_type . '_pages', true );
        if(empty($pages)) return 'ERROR: Unable to generate JSON for DOAJ, pages is empty';
        $doi = static::get_doi($post_id);
        if(empty($doi)) return 'ERROR: Unable to generate JSON for DOAJ, doi is empty';
        $affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        if(empty($journal)) return 'ERROR: Unable to generate JSON for DOAJ, journal is empty';
        if($journal !== $this->get_journal_property('journal_title')) return 'ERROR: Unable to generate JSON for DOAJ, journal of the post and publication type do not match';
        $volume = get_post_meta( $post_id, $post_type . '_volume', true );
        if(empty($volume)) return 'ERROR: Unable to generate JSON for DOAJ, volume is empty';
        $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
        $post_url = get_permalink( $post_id );
        if(empty($post_url)) return 'ERROR: Unable to generate JSON for DOAJ, url is empty';

        $json_array = array();

        $json_array["admin"] = array(
            "in_doaj" => true,
            "publisher_record_id" => $doi
                                     );
        $json_array["bibjson"] = array();
        $json_array["bibjson"]["author"] = array();

        $json_array["bibjson"]["title"] = $title;
        $json_array["bibjson"]["abstract"] = $abstract;
        $json_array["bibjson"]["year"] = mb_substr($date_published, 0, 4);
        $json_array["bibjson"]["month"] = mb_substr($date_published, 5, 2);
        $json_array["bibjson"]["day"] = mb_substr($date_published, 8, 2);
        for ($x = 0; $x < $number_authors; $x++) {
            $author_array = array(
                "name" => $author_given_names[$x] . ' ' . $author_surnames[$x]
                    /* "affiliation" => "string", */
                    /* "email" => "string", */
                                  );
            $json_array["bibjson"]["author"][] = $author_array;
        }
        $json_array["bibjson"]["link"] = [
            array(
                "url" => $post_url . 'pdf/',
                "type" => "fulltext",
                "content_type" => "pdf"
                  ),
                /* array( */
                /*     "url" => $post_url, */
                /*     "type" => "abstract", */
                /*     "content_type" => "html" */
                /*       ) */
                                          ];
            //Put the eISSN
        if(!empty($this->get_journal_property('eissn')))
            $json_array["bibjson"]["identifier"][] =
                array(
                    "type" => "eissn",
                    "id" => $this->get_journal_property('eissn')
                      );
            //Put the DOI
        if(!empty($doi))
            $json_array["bibjson"]["identifier"][] =
                array(
                    "type" => "doi",
                    "id" => $doi
                      );

        $json_array["bibjson"]["journal"] = array();
        $json_array["bibjson"]["journal"]["title"] = $journal;
        $json_array["bibjson"]["journal"]["volume"] = $volume;
//        $json_array["bibjson"]["journal"]["number"] = ?;
        $json_array["bibjson"]["journal"]["start_page"] = $pages;
//        $json_array["bibjson"]["journal"]["end_page"] = ?;
        if(!empty($this->get_journal_property('publisher')))
            $json_array["bibjson"]["journal"]["publisher"] = $this->get_journal_property('publisher');
        if(!empty($this->get_journal_property("doaj_language_code")))
            $json_array["bibjson"]["journal"]["language"] = [ $this->get_journal_property("doaj_language_code") ];
        if(!empty($this->get_journal_property("publisher_country")))
            $json_array["bibjson"]["journal"]["country"] = $this->get_journal_property("publisher_country");
        $json_array["bibjson"]["journal"]["license"] = [
            array(
                "url" => $this->get_journal_property('license_url'),
                "open_access" => true,
                "version" => $this->get_journal_property('license_version'),
                "type" => $this->get_journal_property('license_type'),
                "title" => $this->get_journal_property('license_type')
                  )                                                        ];

        return json_encode($json_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

        /**
         * Echo an intro text for the admin panel.
         *
         * @since     0.1.0
         * @access    protected
         * @param     int          $post_id    Id of the post.
         */
    static protected function the_admin_panel_intro_text( $post_id ) {

        $post_type = get_post_type($post_id);

        echo '<p>Use the large editor field above only for additional content on the page of this ' . $post_type . ' that does not fit into the fields below.</p>';
        echo '<p>Hint: You can drag and drop this box to appear closer to the top of the admin panel page.</p>';

    }

        /**
         * Echo the validation results for the admin panel.
         *
         * @since    0.1.0
         * @access   protected
         * @param    int    $post_id    Id of the post.
         */
    protected static function the_admin_panel_validation_result( $post_id ) {

        $post_type = get_post_type($post_id);
        $validation_result = get_post_meta( $post_id, $post_type . '_validation_result', true );
        if( empty( $validation_result ) )
            $validation_result = '';
        else if( get_post_status( $post_id ) !== 'auto-draft' )
        {

            echo '<h4>Validation results</h4>';
            echo '<div style="width:100%; background-color: #fff; border: 1px solid #eee"><div style="margin:6pt 6pt 6pt 6pt">';
            foreach(preg_split("/\n/u", $validation_result, -1, PREG_SPLIT_NO_EMPTY) as $line){
                $color = "green";
                if(strpos($line, 'WARNING') !== false)
                    $color = "orange";
                if(strpos($line, 'ERROR') !== false)
                    $color = "red";
                echo '<div style="color:' . $color . ';">' . esc_html($line) . '</div>';
            }
            echo "</div></div>";

            if( get_post_status( $post_id ) === 'publish' && strpos($validation_result, 'ERROR') !== false )
            {
                echo '<script>alert("There were ERRORs during the final stage of the publication of this post. Please check.");</script>';
            }
        }

    }

        /**
         * Echo the corresponding author emial for the admin panel.
         *
         * @since     0.1.0
         * @access    protected
         * @param     int           $post_id    Id of the post.
         */
    protected static function the_admin_panel_corresponding_author_email( $post_id ) {

        $post_type = get_post_type($post_id);
        $corresponding_author_email = get_post_meta( $post_id, $post_type . '_corresponding_author_email', true );
		$corresponding_author_has_been_notifed_date = get_post_meta( $post_id, $post_type . '_corresponding_author_has_been_notifed_date', true );
		if( empty( $corresponding_author_email ) ) $corresponding_author_email = '' ;
		if( empty( $corresponding_author_has_been_notifed_date ) ) $corresponding_author_has_been_notifed_date = '' ;

		echo '	<tr>';
		echo '          <th><label for="' . $post_type . '_corresponding_author_email" class="' . $post_type .'_corresponding_author_email_label">' . 'Email' . '</label></th>';
		echo '          <td>';
		echo '                  <input' . ($corresponding_author_has_been_notifed_date === '' ? " " : " readonly ") . 'type="text" id="' . $post_type . '_corresponding_author_email" name="' . $post_type . '_corresponding_author_email" class="' . $post_type . '_corresponding_author_email_field required" placeholder="' . '' . '" value="' . esc_attr($corresponding_author_email) . '">' . ( !empty($corresponding_author_has_been_notifed_date) ? " The authors have been automatically notified on " . $corresponding_author_has_been_notifed_date . '.' : '' );
		echo '                  <p>(The email of the corresponding author. They get sent an automatic notification of publication email when the post is first published.)</p>';
		echo '          </td>';
		echo '  </tr>';

    }

        /**
         * Echo an intro text for buffer functionality on the admin panel.
         *
         * @since    0.3.0
         * @access   proteted
         * @param    int         $post_id    Id of the post.
         */
    protected function the_admin_panel_buffer( $post_id ) {

        $buffer_api_url = $this->get_journal_property('buffer_api_url');
        $buffer_access_token = $this->get_journal_property('buffer_access_token');
        $buffer_profile_ids = $this->get_journal_property('buffer_profile_ids');
        if(!empty($buffer_api_url) and !empty($buffer_access_token) and !empty($buffer_profile_ids) and is_array($buffer_profile_ids))
            $buffer_configured = true;
        else
            $buffer_configured = false;

        $post_type = get_post_type($post_id);
        $buffer_email = get_post_meta( $post_id, $post_type . '_buffer_email', true ); #we keep using the buffer_email and buffer_email_xxx fields for compatibility, even though the new buffer.com interface does no longer send emails but uses the buffer.com api
		$buffer_email_was_sent_date = get_post_meta( $post_id, $post_type . '_buffer_email_was_sent_date', true );
        $buffer_special_text = get_post_meta( $post_id, $post_type . '_buffer_special_text', true );
        if(empty($buffer_email_was_sent_date))
            $buffer_email_was_sent_date = '';

        if(empty($buffer_email))
            if(get_post_status( $post_id ) === 'auto-draft')
                $buffer_email = 'checked'; //Check this by default for new posts
            else
                $buffer_email = '';
        if(empty($buffer_special_text))
            $buffer_special_text = '';

		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_buffer_email" class="' . $post_type .'_buffer_email_label">' . 'Buffer' . '</label></th>';
        echo '		<td>';

        if(empty($buffer_email_was_sent_date))
        {
            if( get_post_status( $post_id ) === 'publish' )
                echo '		  This post is already published. Only new posts can be put into the buffer.com queue.';
            else
            {
                echo '		  <input type="checkbox"' . (($buffer_email_was_sent_date === '' and $buffer_configured) ? " " : " disabled ") . ' name="' . $post_type . '_buffer_email" value="checked"' . ($buffer_configured ? $buffer_email: '') . '>Put a post with a link to this ' . $post_type . ' into the queue on buffer.com during publication';

                echo '		  <input style="width:100%;" type="text"' . (($buffer_email_was_sent_date === '' and $buffer_configured) ? " " : " disabled ") . ' id="' . $post_type . '_buffer_special_text" name="' . $post_type . '_buffer_special_text" class="' . $post_type . '_buffer_special_text_field" placeholder="' . '' . '" value="' . esc_attr($buffer_special_text) . '"><p>(You can specify a custom message for buffer.com in this text field. The url to this post, separated by a space, is automatically appended. If left blank, the message is generated from a set of pre-defined texts, the title, and the author names.' . (!$buffer_configured ? ' To enabled this features configure the buffer.com functionality via the settings.' : '') . ')</p>';
            }
        }
        else
            echo '		  An update with a link to this ' . $post_type . ' was put into the buffer.com queue on ' . $buffer_email_was_sent_date . '.';
        echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the title for the admin panel.
         *
         * @since     0.1.0
         * @access    protected
         * @param     int    $post_id    Id of the post.
         */
    protected static function the_admin_panel_title( $post_id ) {

        $post_type = get_post_type($post_id);
        $title = get_post_meta( $post_id, $post_type . '_title', true );
		$title_mathml = get_post_meta( $post_id, $post_type . '_title_mathml', true );
		if( empty( $title ) ) $title = '';
		if( empty( $title_mathml ) ) $title_mathml = '';

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_title" class="' . $post_type . '_title_label">' . 'Title' . '</label></th>';
		echo '		<td>';
		echo '			<input style="width:100%;" type="text" id="' . $post_type . '_title" name="' . $post_type . '_title" class="' . $post_type . '_title_field preview_and_mathml required" placeholder="' . '' . '" value="' . esc_attr($title) . '"><p>(The title may contain special characters. Type Č instead of \\v{C} for example. On the contrary, mathematical formulas must be entered in LaTeX notation surrounded by $ signs. Write \\$ for an actual dollar sign. If a mathematical formula is detected, a live preview of how it will display on the website and the MathML representation appears above this help text.)</p>';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the authors for the admin panel.
         *
         * @since     0.1.0
         * @access    protected
         * @param     int          $post_id    Id of the post.
         */
    protected function the_admin_panel_authors( $post_id ) {

        $post_type = get_post_type($post_id);
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
		$author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
		$author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
		$author_name_styles = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_name_styles');
		$author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');
		$author_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_orcids');
        $author_urls = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_urls');
        if( empty( $number_authors ) ) $number_authors = $this->default_number_authors;
		if( empty( $author_given_names ) ) $author_given_names = array();
		if( empty( $author_surnames ) ) $author_surnames = array();
		if( empty( $author_name_styles ) ) $author_name_styles = array();
		if( empty( $author_affiliations ) ) $author_affiliations = array();
		if( empty( $author_orcids ) ) $author_orcids = array();
        if( empty( $author_urls ) ) $author_urls = array();

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_number_authors" class="' . $post_type . '_number_authors_label">' . 'Number of authors' . '</label></th>';
		echo '		<td>';
		echo '			<input style="width:4rem" type="number" id="' . $post_type . '_number_authors" name="' . $post_type . '_number_authors" class="' . $post_type . '_number_authors_field required" placeholder="' . '' . '" value="' . esc_attr($number_authors) . '"><p>(Please put here the actual number of authors. To update the number of entries in the list below please save the post. Give affiliations as a comma separated list referring to the affiliations below, e.g., 1,2,5,7. As with the title, special characters are allowed and must be entered as í or é and so on.)</p>';
		echo '		</td>';
		echo '	</tr>';

		for ($x = 0; $x < $number_authors; $x++) {
			$y = $x+1;
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_author" class="' . $post_type . '_author_label">' . "Author  $y" . '</label></th>';
			echo '		<td>';
			echo '			<div style="float:left"><input type="text" name="' . $post_type . '_author_given_names[]" class="' . $post_type . '_author_given_names_field" placeholder="' . '' . '" value="' . esc_attr( isset($author_given_names[$x]) ? $author_given_names[$x] : '' ) . '" /><br /><label for="' . $post_type . '_author_given_names" class="' . $post_type . '_author_given_names_label">Given name</label></div>';
			echo '			<div style="float:left"><input type="text" name="' . $post_type . '_author_surnames[]" class="' . $post_type . '_author_surnames_field required" placeholder="' . '' . '" value="' . esc_attr( isset($author_surnames[$x]) ? $author_surnames[$x] : '' ) . '" /><br /><label for="' . $post_type . '_author_surnames" class="' . $post_type . '_author_surnames_label">Surname</label></div>';
			echo '			<div style="float:left"><select name="' . $post_type . '_author_name_styles[]">';
			foreach(array("western", "eastern", "islensk", "given-only") as $style)
                echo '<option value="' . $style . '"' . ( (isset($author_name_styles[$x]) && $author_name_styles[$x] === $style) ? " selected" : "" ) . '>' . $style . '</option>';
			echo '</select><br /><label for="' . $post_type . '_author_name_styles" class="' . $post_type . '_author_name_styles_label">Name style</label></div>';
			echo '			<div style="float:left"><input style="width:5rem" type="text" name="' . $post_type . '_author_affiliations[]" class="' . $post_type . '_author_affiliations_field" placeholder="' . '' . '" value="' . esc_attr( isset($author_affiliations[$x]) ? $author_affiliations[$x] : '' ) . '" /><br /><label for="' . $post_type . '_author_affiliations" class="' . $post_type . '_author_affiliations">Affiliations</label></div>';
			echo '			<div style="float:left"><input style="width:11rem" type="text" name="' . $post_type . '_author_orcids[]" class="' . $post_type . '_author_orcids" placeholder="' . '' . '" value="' . esc_attr( isset($author_orcids[$x]) ? $author_orcids[$x] : '' ) . '" /><br /><label for="' . $post_type . '_author_orcids" class="' . $post_type . '_author_orcids_label">ORCID</label></div>';
            echo '			<div style="float:left"><input style="width:20rem" type="text" name="' . $post_type . '_author_urls[]" class="' . $post_type . '_author_urls" placeholder="' . '' . '" value="' . esc_attr( isset($author_urls[$x]) ? $author_urls[$x] : '' ) . '" /><br /><label for="' . $post_type . '_author_urls" class="' . $post_type . '_author_urls_label">URL</label></div>';
			echo '		</td>';
			echo '	</tr>';
		}

    }

        /**
         * Echo the affiliations for the admin panel.
         *
         * @since      0.1.0
         * @accesss    protected
         * @param      int          $post_id    Id of the post.
         */
    protected static function the_admin_panel_affiliations( $post_id ) {

        $post_type = get_post_type($post_id);
        $number_affiliations = get_post_meta( $post_id, $post_type . '_number_affiliations', true );
		$affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');

        if( empty( $number_affiliations ) && $number_affiliations !== '0' ) $number_affiliations = 4;
		if( empty( $affiliations ) ) $affiliations = array();

		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_number_affiliations" class="' . $post_type . '_number_affiliations_label">' . 'Number of affiliations' . '</label></th>';
		echo '		<td>';
		echo '			<input style="width:4rem" type="number" id="' . $post_type . '_number_affiliations" name="' . $post_type . '_number_affiliations" class="' . $post_type . '_number_affiliations_field required" placeholder="' . '' . '" value="' . esc_attr( $number_affiliations ) . '"><p>(Please put here the total number of affiliations. To update the number of Affiliation fields save the post.)</p>';
		echo '		</td>';
		echo '	</tr>';
		for ($x = 0; $x < $number_affiliations; $x++) {
			$y = $x+1;
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_affiliation" class="' . $post_type . '_affiliation_label">' . "Affiliation  $y" . '</label></th>';
			echo '		<td>';
			echo '			<input style="width:100%" type="text" name="' . $post_type . '_affiliations[]" class="' . $post_type . '_affiliations required" placeholder="' . '' . '" value="' . esc_attr( isset($affiliations[$x]) ? $affiliations[$x] : '' ) . '" />';

			echo '		</td>';
			echo '	</tr>';
		}

    }

        /**
         * Echo the data, volume and pages information for the admin panel.
         *
         * @since     0.1.0
         * @access    protected
         * @param     int          $post_id    Id of the post.
         */
    protected function the_admin_panel_date_volume_pages( $post_id ) {

        $post_type = get_post_type($post_id);
        $post_status = get_post_status($post_id);

        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );
        $journal = get_post_meta( $post_id, $post_type . '_journal', true );
        $volume = get_post_meta( $post_id, $post_type . '_volume', true );
        $pages = get_post_meta( $post_id, $post_type . '_pages', true );


        if( empty( $date_published ) ) $date_published = date("Y-m-d");
        if( empty( $journal ) ) $journal = $this->get_journal_property('journal_title');
        if( empty( $volume ) ) $volume = getdate()["year"] - ($this->get_journal_property('first_volume_year')-1);
        if( empty( $pages ) ) {
			$highest_pages_info = $this->journal->get_post_type_highest_pages_info( $post_id, array($this->get_publication_type_name()) );
			$highest_pages = $highest_pages_info['pages'];
			$highest_pages_date_published = $highest_pages_info['date_published'];
			if ( $highest_pages_date_published === $date_published and mb_strlen((string)$highest_pages) !== mb_strlen((string)($highest_pages+1)))
				$pages = -1; //Throws an error during validation to ensure lexicographic ordering of DOIs
			else
                $pages = $highest_pages+1;
		}

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_date_published" class="' . $post_type . '_date_published_label">' . 'Publication date' . '</label></th>';
		echo '		<td>';
		echo '			<input' . ($post_status !== 'publish' ? " " : " readonly ") .  'type="date" id="' . $post_type . '_date_published" name="' . $post_type . '_date_published" class="' . $post_type . '_date_published_field required" placeholder="' . '' . '" value="' . esc_attr($date_published) . '">';
		echo '		</td>';
		echo '	</tr>';

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_journal" class="' . $post_type . '_journal_label">' . 'Journal' . '</label></th>';
		echo '		<td>';
		echo '			<input readonly type="text" id="' . $post_type . '_journal" name="' . $post_type . '_journal" class="' . $post_type . '_journal_field required" placeholder="' . '' . '" value="' . esc_attr( $journal ) . '">';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_volume" class="' . $post_type . '_volume_label">' . 'Volume' . '</label></th>';
		echo '		<td>';
		echo '			<input' . ($post_status !== 'publish' ? " " : " readonly ") .  'type="number" id="' . $post_type . '_volume" name="' . $post_type . '_volume" class="' . $post_type . '_volume_field required" placeholder="' . '' . '" value="' . esc_attr($volume) . '">';
		echo '			<p>(The volume number is the year of publication minus ' . ($this->get_journal_property('first_volume_year')-1) . '.)</p>';
		echo '		</td>';
		echo '	</tr>';

		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_pages" class="' . $post_type . '_pages_label">' . 'Pages' . '</label></th>';
		echo '		<td>';
		echo '			<input' . ($post_status !== 'publish' ? " " : " readonly ") .  'type="number" id="' . $post_type . '_pages" name="' . $post_type . '_pages" class="' . $post_type . '_pages_field required" placeholder="' . '' . '" value="' . esc_attr($pages) . '">';
		echo '			<p>(This is our (fake) page number. Must be one larger than that of the last published work. Please double check the default value this is automatically set to. If it is -1, then publishing this post at the given date would break lexicographic ordering or DOIs.)</p>';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the DOI for the admin panel.
         *
         * @since      0.1.0
         * @access     protecte
         * @param      int        $post_id    Id of the post.
         */
    protected function the_admin_panel_doi( $post_id ) {

        $post_type = get_post_type($post_id);
        $doi = static::get_doi($post_id);

		echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_doi" class="' . $post_type . '_doi_label">' . 'Doi' . '</label></th>';
		echo '		<td>';
		echo '			<input type="text" readonly value="' . esc_attr($doi) . '" ><br /><p>(The doi is automatically calculated from the above meta data and is of the form ' . $this->get_journal_property('journal_level_doi_suffix') . '-YYYY-MM-DD-pages. If not enough information is available, it is not set and the post is forced to private)</p>';
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo the bibliography for the admin panel.
         *
         * @since 0.1.0
         * @param int    $post_id    Id of the post.
         */
    protected function the_admin_panel_bibliography($post_id) {

        $post_type = get_post_type($post_id);
		$bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
        $author_latex_macro_definitions = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_latex_macro_definitions');

        echo '	<tr>';
		echo '		<th><label for="' . $post_type . '_bbl" class="' . $post_type . '_bbl_label">' . 'Bibliography' . '</label></th>';
		echo '		<td><p>(The bibliography as extracted from the bbl code below.)</p>';
		if ( !empty($bbl) ) {

			$parsed_bbl = O3PO_Latex::parse_bbl($bbl);
			if( !empty($parsed_bbl) ) {
				foreach($parsed_bbl as $n => $entry) {
					static::the_formated_bibliography_entry_html($entry);
					if( O3PO_Latex::strpos_outside_math_mode($entry['text'], '\\') !== false ) echo '<p style="color:red;">WARNING: This entry still contains one or more backslashes. Probably this means we have not recognized some LaTeX commmand, but it can also be ok if the entry contains a mathematical formula.</p>';
                    if( empty($entry['doi']) ) echo '<p style="color:orange;">WARNING: No DOI found for this entry. Does it really not have one?</p>';
				}
			} else {
				echo '<p>No entries found.</p>' . "\n";
			}
		} else {
			echo '<p>No entries found.</p>' . "\n";
		}
        echo '			<p>(The above was generated from the following bbl data that was extracted from the source files with some subsequent macro expansion (see below for which macros were expanded). If something with the above is not right you can edit the extracted bbl by modifying the text below, the references are then recalculated upon the next save/update. Please also report any problems to ' . $this->get_journal_property('developer_email') . ' so that they can be fixed and the automatic extraction improved. Keep in mind that your changes are overwritten if source files are downloaded again from the arXiv!</p><textarea name="' . $post_type . '_bbl" id="' . $post_type . '_bbl" rows="' . (min(mb_substr_count( $bbl, "\n" )+1, 30)) . '" style="width: 100%; overflow: scroll;">' . esc_textarea($bbl) . '</textarea>';

        echo '         <p>If you need to hand craft a bibliography you can start from the following template:</p>
<textarea rows="10" style="width: 100%; overflow: scroll;">
\begin{thebibliography}{99}
\bibitem{Author2000}
  Name Surname, Name Surname, and Name Surname,
  Journal Name, 13 123-125 (2000),
  \doi{10.22331/idonotexist}.}

\bibitem{Author2018}
  Name Surname and Name Surname,
  \href{http://arxiv.org/abs/1804.00000}{arXiv:1804.00000}.}
\end{thebibliography}</textarea>';

        if(!empty($author_latex_macro_definitions))
        {
            foreach($author_latex_macro_definitions as $author_latex_macro_definition)
            {
                if(!isset($author_latex_macro_definition_summary))
                    $author_latex_macro_definition_summary = "";
                $author_latex_macro_definition_summary .= '\\' . $author_latex_macro_definition[1] . '{' . $author_latex_macro_definition[2] . '}' . $author_latex_macro_definition[3] . $author_latex_macro_definition[4] . '{' . $author_latex_macro_definition[5] . '}' . "\n";
            }
            echo '			<p>(In the source files the following latex commands were identified and expanded when generating the bbl above from the source.)</p><textarea name="' . $post_type . '_author_latex_macro_definitions" id="' . $post_type . '_author_latex_macro_definitions" rows="' . (min(mb_substr_count( $author_latex_macro_definition_summary, "\n" )+1, 30)) . '" style="width: 100%; overflow: scroll;" readonly>' . esc_textarea($author_latex_macro_definition_summary) . '</textarea>';
        }
		echo '		</td>';
		echo '	</tr>';

    }

        /**
         * Echo crossref information for the admin panel.
         *
         * @since 0.1.0
         * @param int    $post_id    Id of the post.
         */
    protected static function the_admin_panel_crossref($post_id) {

        $post_type = get_post_type($post_id);
		$crossref_xml = get_post_meta( $post_id, $post_type . '_crossref_xml', true );
		$crossref_response = get_post_meta( $post_id, $post_type . '_crossref_response', true );

		if ( !empty($crossref_xml) ) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_crossref_xml" class="' . $post_type . '_crossref_xml_label">' . 'Crossref xml' . '</label></th>';
			echo '		<td>';
			echo '			<textarea rows="16" style="width:100%;" readonly>' . esc_textarea($crossref_xml) . '</textarea><p>(The Crossref xml is automatically calculated from the above meta data.)</p>';
			echo '		</td>';
			echo '	</tr>';
		}

		if ( !empty($crossref_response) ) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_crossref_response" class="' . $post_type . '_crossref_response_label">' . 'Crossref response' . '</label></th>';
			echo '		<td>';
			echo '			<textarea rows="' . (mb_substr_count( $crossref_response, "\n" )+1) . '" style="width:100%;" readonly>' . esc_textarea($crossref_response) . '</textarea><p>(The response we got from Crossref when uploading the metadata.)</p>';
			echo '		</td>';
			echo '	</tr>';
		}

    }


        /**
         * Echo the DOAJ information for the admin panel.
         *
         * @since 0.1.0
         * @param int    $post_id    Id of the post.
         */
    protected static function the_admin_panel_doaj($post_id) {

        $post_type = get_post_type($post_id);
		$doaj_json = get_post_meta( $post_id, $post_type . '_doaj_json', true );
		$doaj_response = get_post_meta( $post_id, $post_type . '_doaj_response', true );

		if ( !empty($doaj_json) ) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_doaj_json" class="' . $post_type . '_doaj_json_label">' . 'DOAJ json' . '</label></th>';
			echo '		<td>';
			echo '			<textarea rows="16" style="width:100%;" readonly>' . esc_textarea($doaj_json) . '</textarea><p>(The DOAJ json is automatically calculated from the above meta data.)</p>';
			echo '		</td>';
			echo '	</tr>';
		}

		if ( !empty($doaj_response) ) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_doaj_response" class="' . $post_type . '_doaj_response_label">' . 'DOAJ response' . '</label></th>';
			echo '		<td>';
			echo '			<textarea rows="' . (mb_substr_count( $doaj_response, "\n" )+2) . '" style="width:100%;" readonly>' . esc_textarea($doaj_response) . '</textarea><p>(The response we got from DOAJ when uploading the metadata.)</p>';
			echo '		</td>';
			echo '	</tr>';
		}

    }

        /**
         * Echo the CLOCKSS information for the admin panel.
         *
         * @since 0.1.0
         * @param int    $post_id    Id of the post.
         */
    protected static function the_admin_panel_clockss($post_id) {

        $post_type = get_post_type($post_id);
		$clockss_xml = get_post_meta( $post_id, $post_type . '_clockss_xml', true );
		$clockss_response = get_post_meta( $post_id, $post_type . '_clockss_response', true );

		if ( !empty($clockss_xml) ) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_clockss_xml" class="' . $post_type . '_clockss_xml_label">' . 'CLOCKSS xml' . '</label></th>';
			echo '		<td>';
			echo '			<textarea rows="16" style="width:100%;" readonly>' . esc_textarea($clockss_xml) . '</textarea><p>(The CLOCKSS xml is automatically calculated from the above meta data.)</p>';
			echo '		</td>';
			echo '	</tr>';
		}

		if ( !empty($clockss_response) ) {
			echo '	<tr>';
			echo '		<th><label for="' . $post_type . '_clockss_response" class="' . $post_type . '_clockss_response_label">' . 'CLOCKSS response' . '</label></th>';
			echo '		<td>';
			echo '			<textarea rows="' . (mb_substr_count( $clockss_response, "\n" )+2) . '" style="width:100%;" readonly>' . esc_textarea($clockss_response) . '</textarea><p>(The response we got from CLOCKSS when uploading the metadata and full text pdf, if available.)</p>';
			echo '		</td>';
			echo '	</tr>';
		}

    }

        /**
         * Outputs some java script for the single page of this publication
         * type.
         *
         * To be added to the 'wp_head' action.
         *
         * @since   0.1.0
         * @access  public
         */
    public function the_java_script_single_page() {

        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        if ( !is_single() || $post_type !== $this->get_publication_type_name())
            return;
?>
        <script type='text/javascript'>//<![CDATA[
        window.onload = function() {
            var anchors = document.getElementsByClassName("initially-display-none-if-js");
            for(var i = 0; i < anchors.length; i++) {
                anchors[i].style.display = 'none';
            }
        }
        window.onbeforeprint = function() {
            var anchors = document.getElementsByClassName("initially-display-none-if-js");
            for(var i = 0; i < anchors.length; i++) {
                anchors[i].style.display = 'block';
            }
        }
        function toggleFollowing(element){
            following = element.parentElement.nextSibling.style.display;
            if(element.parentElement.nextSibling.style.display == 'none')
                element.parentElement.nextSibling.style.display = 'block';
            else
                element.parentElement.nextSibling.style.display = 'none';
            return false;
        }//]]>
        </script>
<?php
    }


        /**
         * Outptus the html formated volume of this publication
         * type.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public static function get_formated_volume_html( $post_id ) {

        $post_type = get_post_type($post_id);
        $volumen_num = get_post_meta( $post_id, $post_type . '_volume', true );

        return 'volume ' . esc_html($volumen_num);
    }

        /**
         * Echo the html formated bibliography.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function the_formated_bibliography_html( $post_id ) {

        echo $this->get_formated_bibliography_html($post_id);

    }

        /**
         * Echo the html formated bibliography.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function get_formated_bibliography_html( $post_id ) {

        $post_type = get_post_type($post_id);
        $bbl = get_post_meta( $post_id, $post_type . '_bbl', true );
        $bibliography = '';
        if ( !empty($bbl) ) {
            $parsed_bbl = O3PO_Latex::parse_bbl($bbl);
            if( !empty($parsed_bbl) )
            {
                foreach($parsed_bbl as $n => $entry) {
                    $bibliography .= $this->get_formated_bibliography_entry_html($entry);
                }
            }
        }

        return $bibliography;
    }

        /**
         * Echo a formated bibliography entry.
         *
         * @since    0.1.0
         * @access   public
         * @param    array  $entry       Array describing the bibliography entry.
         */
    public function the_formated_bibliography_entry_html( $entry ) {

        echo $this->get_formated_bibliography_entry_html($entry);

    }


        /**
         * Get a formated bibliography entry.
         *
         * Expects a entry of a bibliography such as those that can be
         * obtained via O3PO_Latex::parse_bbl() from bbl code.
         *
         * @since    0.1.0
         * @access   public
         * @param    array  $entry       Array describing the bibliography entry.
         */
    public function get_formated_bibliography_entry_html( $entry ) {

        $doi_url_prefix = $this->get_journal_property('doi_url_prefix');

        return '			 <p class="break"><a id="' . esc_attr($entry['key']) . '">[' . esc_html($entry['ref']) . ']</a> ' . O3PO_Utility::make_slash_breakable_html(esc_html(htmlspecialchars($entry['text']))) . (!empty($entry['doi']) ? ' <br /><a href="' . esc_url(htmlspecialchars($doi_url_prefix . $entry['doi'])) . '">' . O3PO_Utility::make_slash_breakable_html(esc_url(htmlspecialchars($doi_url_prefix . $entry['doi']))) . '</a>' : '' ) . ( !empty($entry['eprint']) ? ' <br /><a href="' . esc_url($this->get_journal_property('arxiv_url_abs_prefix') . $entry['eprint']) . '">arXiv:' . esc_html($entry['eprint']) . '</a>' : '' ) . ( !empty($entry['url']) ? ' <br /><a href="' . esc_url(htmlspecialchars($entry['url'])) . '">' . O3PO_Utility::make_slash_breakable_html(esc_url(htmlspecialchars($entry['url']))) . '</a>' : '' ) . '</p>';
    }


        /**
         * Echo the thml formated bibliography.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function the_bibliography( $post_id ) {

        echo $this->get_bibliography_html($post_id);

    }

        /**
         * Get the thml formated bibliography.
         *
         * Includes a heading for use in the single templates and the like.
         *
         * @since   0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function get_bibliography_html( $post_id ) {

        $bibliography = get_transient($post_id . '_bibliography_html');

        if( false === $bibliography ) {
                // Transient expired, regenerate
            $bibliography = '';
            $post_type = get_post_type($post_id);
            if( !empty(get_post_meta( $post_id, $post_type . '_bbl', true )) ) {
                $bibliography .= '<h3 class="references toggle-following additional-info"><a href="javascript:void(0);" onclick="toggleFollowing(this);">&#9658; References</a></h3>';
                $bibliography .= '<div class="initially-display-none-if-js" id="references">';
                $bibliography .= $this->get_formated_bibliography_html($post_id);
                $bibliography .= '</div>';
            }
            set_transient($post_id . '_bibliography_html', $bibliography, 60);
        }

        return $bibliography;
    }


        /**
         * Get the html formated cited information.
         *
         * @since   0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function get_formated_cited_by_html( $post_id ) {
        return $this->get_cited_by_data($post_id)['html'];
    }

        /**
         *
         */
    public function get_cited_by_data( $post_id, $fetch_if_outdated=true ) {

        $post_type = get_post_type($post_id);
        $doi = $this->get_doi($post_id);

        $login_id = $this->get_journal_property('crossref_id');
        $login_passwd = $this->get_journal_property('crossref_pw');
        $crossref_url = $this->get_journal_property('crossref_get_forward_links_url');
        $doi_url_prefix = $this->get_journal_property('doi_url_prefix');
        $ads_api_search_url = $this->get_journal_property('ads_api_search_url');
        $ads_api_token = $this->get_journal_property('ads_api_token');
        $arxiv_url_abs_prefix = $this->get_journal_property('arxiv_url_abs_prefix');
        $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );

        $settings = O3PO_Settings::instance();
        $cited_by_refresh_seconds = $settings->get_plugin_option('cited_by_refresh_seconds');

        $crossref_bibentries = get_post_meta( $post_id, $post_type . '_crossref_cited_by_bibentries', true );
        $crossref_bibentries_timestamp = get_post_meta( $post_id, $post_type . '_crossref_cited_by_bibentries_timestamp', true );
        $crossref_bibentries_last_fetch_attempt_timestamp = get_post_meta( $post_id, $post_type . '_crossref_cited_by_bibentries_last_fetch_attempt_timestamp', true );
        if(empty($crossref_bibentries_last_fetch_attempt_timestamp) or ($fetch_if_outdated and time() - $crossref_bibentries_last_fetch_attempt_timestamp > $cited_by_refresh_seconds))
        {
            $crossref_bibentries_last_fetch_attempt_timestamp = time();
            update_post_meta( $post_id, $post_type . '_crossref_cited_by_bibentries_last_fetch_attempt_timestamp', $crossref_bibentries_last_fetch_attempt_timestamp);

            $new_crossref_bibentries = O3PO_Crossref::get_cited_by_bibentries($crossref_url, $login_id, $login_passwd, $doi);
            if(!empty($new_crossref_bibentries) or !is_wp_error($new_crossref_bibentries) or empty($crossref_bibentries) or is_wp_error($crossref_bibentries))
            {
                $crossref_bibentries = $new_crossref_bibentries;
                update_post_meta( $post_id, $post_type . '_crossref_cited_by_bibentries', $crossref_bibentries );
                $crossref_bibentries_timestamp = time();
                update_post_meta( $post_id, $post_type . '_crossref_cited_by_bibentries_timestamp', $crossref_bibentries_timestamp);
            }
        }

        $ads_bibentries = get_post_meta( $post_id, $post_type . '_ads_cited_by_bibentries', true );
        $ads_bibentries_timestamp = get_post_meta( $post_id, $post_type . '_ads_cited_by_bibentries_timestamp', true );
        $ads_bibentries_last_fetch_attempt_timestamp = get_post_meta( $post_id, $post_type . '_ads_cited_by_bibentries_last_fetch_attempt_timestamp', true );
        if(empty($ads_bibentries_last_fetch_attempt_timestamp) or ($fetch_if_outdated and time() - $ads_bibentries_last_fetch_attempt_timestamp > $cited_by_refresh_seconds))
        {
            $ads_bibentries_last_fetch_attempt_timestamp = time();
            update_post_meta( $post_id, $post_type . '_ads_cited_by_bibentries_last_fetch_attempt_timestamp', $ads_bibentries_last_fetch_attempt_timestamp);

            $new_ads_bibentries = O3PO_Ads::get_cited_by_bibentries($ads_api_search_url, $ads_api_token, $eprint);

            if(!empty($new_ads_bibentries) or !is_wp_error($new_ads_bibentries) or empty($ads_bibentries) or is_wp_error($ads_bibentries))
            {
                $ads_bibentries = $new_ads_bibentries;
                update_post_meta( $post_id, $post_type . '_ads_cited_by_bibentries', $ads_bibentries );
                $ads_bibentries_timestamp = time();
                update_post_meta( $post_id, $post_type . '_ads_cited_by_bibentries_timestamp', $ads_bibentries_timestamp);
            }
        }

        $cited_by_html = '';

        $errors = array();
        $error_explanations = array();
        if(is_wp_error($crossref_bibentries))
        {
            $errors[] = $crossref_bibentries;
            $error_explanations[] = 'Could not fetch <a href="https://www.crossref.org/services/cited-by/">Crossref cited-by data</a> (last attempt ' . date("Y-m-d H:i:s", $crossref_bibentries_last_fetch_attempt_timestamp) . '): ' . esc_html($crossref_bibentries->get_error_message());
            $crossref_bibentries = array();
        }
        elseif(empty($crossref_bibentries))
            $error_explanations[] = 'On <a href="https://www.crossref.org/services/cited-by/">Crossref\'s cited-by service</a> no data on citing works was found (last attempt ' . date("Y-m-d H:i:s", $crossref_bibentries_last_fetch_attempt_timestamp) . ').';


        if (is_wp_error($ads_bibentries))
        {
            $errors[] = $ads_bibentries;
            $error_explanations[] = 'Could not fetch <a href="https://ui.adsabs.harvard.edu/">ADS cited-by data</a> (last attempt ' . date("Y-m-d H:i:s", $ads_bibentries_last_fetch_attempt_timestamp) . '): ' . esc_html($ads_bibentries->get_error_message());
            $ads_bibentries = array();
        }
        elseif(empty($ads_bibentries))
            $error_explanations[] = 'On <a href="https://ui.adsabs.harvard.edu/">SAO/NASA ADS</a> no data on citing works was found (last attempt ' . date("Y-m-d H:i:s", $ads_bibentries_last_fetch_attempt_timestamp) . ').';

        if(!empty($crossref_bibentries) and !empty($ads_bibentries))
        {
            $all_bibentries = O3PO_Bibentry::merge_bibitem_arrays($crossref_bibentries, $ads_bibentries, true);
        }
        elseif(!empty($crossref_bibentries))
        {
            $all_bibentries = O3PO_Bibentry::remove_duplicates($crossref_bibentries);
        }
        elseif(!empty($ads_bibentries))
        {
            $all_bibentries = O3PO_Bibentry::remove_duplicates($ads_bibentries);
        }
        else
            $all_bibentries = array();

        $sources = array();
        $timestamps = array();
        if(!empty($crossref_bibentries))
        {
            $sources[] = '<a href="https://www.crossref.org/services/cited-by/">Crossref\'s cited-by service</a> (last updated ' . date("Y-m-d H:i:s", $crossref_bibentries_timestamp) . ')';
            $timestamps[] = $crossref_bibentries_timestamp;
        }
        if(!empty($ads_bibentries))
        {
            $sources[] = '<a href="https://ui.adsabs.harvard.edu/">SAO/NASA ADS</a>  (last updated ' . date("Y-m-d H:i:s", $ads_bibentries_timestamp) . ')';
            $timestamps[] = $ads_bibentries_timestamp;
        }

        $citation_number = 0;
        foreach($all_bibentries as $bibentry)
        {
            $citation_number += 1;
            $cited_by_html .= '<p class="break">' . '[' . esc_html($citation_number) . '] ';
            $cited_by_html .= $bibentry->get_formated_html($doi_url_prefix, $arxiv_url_abs_prefix);
            $cited_by_html .= '</p>' . "\n";
        }

        if(!empty($sources))
            $cited_by_html .= '<p>The above citations are from ' . implode($sources, ' and ') . '. The list may be incomplete as not all publishers provide suitable and complete citation data.</p>';

        if(!empty($error_explanations))
            $cited_by_html .= '<p>' . implode($error_explanations, ' ') . '</p>';

        return array(
            'html' => $cited_by_html,
            'citation_count' => $citation_number,
            'all_bibentries' => $all_bibentries,
            'crossref_bibentries' => $crossref_bibentries,
            'ads_bibentries' => $ads_bibentries,
            'errors' => $errors,
            'sources' => $sources,
            'timestamps' => $timestamps,
                     );
    }

        /**
         * Echo the html formated cited by information.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function the_cited_by( $post_id ) {

        echo $this->get_cited_by($post_id);

    }

        /**
         * Get the html formated cited by information.
         *
         * Echos the cited-by date including a heading for use in the single
         * templates and the like.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function get_cited_by( $post_id ) {

        $cited_by = get_transient($post_id . '_cited_by_html');
        if( false === $cited_by ) {
            $cited_by = '';
            $post_type = get_post_type($post_id);
            $cited_by .= '<h3 class="references additional-info">Cited by</h3>';
            $cited_by .= '<div class="">';
            $cited_by .= $this->get_formated_cited_by_html($post_id);
            $cited_by .= '</div>';
            set_transient($post_id . '_cited_by_html', $cited_by, 60); //keep for 1 minute
        }

        return $cited_by;
    }


        /*
         * Retrieve and return BibTeX data from Crossref.
         *
         * In the long run
         * this function should cache the response it got from Crossref to
         * speed up page load and generate less traffic for Crossref, this
         * however has to be done in a smart way, as Crossref has several
         * servers, often producing inconsistent results and taking
         * different ammounts of time to update and respond.
         *
         * @since 0.1.0
         * */
        //Deactivated because Crossref does not return nice BibTeX and we therefore rather generate it ourselves
    /* public function get_crossref_bibtex($post_id) { */

    /*     $post_type = get_post_type($post_id); */
    /*     $doi = static::get_doi($post_id); */

    /*     $response = wp_remote_get( $this->get_journal_property('doi_url_prefix') . $doi, array( 'headers' => array( 'Accept' => 'application/x-bibtex' )) ); */
    /*     if ( is_wp_error( $response ) or $response['body'][0] !== '@' ) { */
    /*         return 'BibTeX data is currently not available.'; */
    /*     } else { */
    /*         return $response['body']; */
    /*     } */

    /* } */


    /**
     * Generate a BibTeX representation of the meta-data of $post_id
     *
     * @since    0.1.0
     * @access   public
     * @param    int    $post_id     Id of the post.
     */
    public function generate_bibtex( $post_id ) {

        $post_type = get_post_type($post_id);
        $doi = static::get_doi($post_id);

        $pages = get_post_meta( $post_id, $post_type . '_pages', true );
        $title = O3PO_Latex::utf8_to_bibtex(get_post_meta( $post_id, $post_type . '_title', true ));
        $publisher = '{' . O3PO_Latex::utf8_to_latex($this->get_journal_property('publisher')) . '}';
        $journal = '{' . O3PO_Latex::utf8_to_latex(get_post_meta( $post_id, $post_type . '_journal', true )) . '}';
        $volume = get_post_meta( $post_id, $post_type . '_volume', true );
        $authors = $this->get_formated_authors_bibtex($post_id);
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );
        $month = mb_substr( $date_published, 5, 2 );
        if( !empty($month) and 1 <= $month and $month <= 13 )
            $month = O3PO_Latex::get_month_string($month);
        else
            $month = '';

        $year = mb_substr( $date_published, 0, 4 );
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        $doi = $this->get_doi($post_id);
        $doi_url_prefix = $this->get_journal_property('doi_url_prefix');

        $key = (isset($author_surnames[0]) ? O3PO_Latex::utf8_to_closest_latin_letter_string($author_surnames[0]) : 'surname') . $year . O3PO_Latex::title_to_key_suffix(get_post_meta( $post_id, $post_type . '_title', true ));

        $bibtex = '';
        $bibtex .= '@article{' . $key . ',' . "\n";
        $bibtex .= '  doi = {' . $doi . '},' . "\n";
        $bibtex .= '  url = {' . $doi_url_prefix . $doi . '},' . "\n";
        $bibtex .= '  title = {' . $title . '},' . "\n";
        $bibtex .= '  author = {' . $authors . '},' . "\n";
        $bibtex .= '  journal = {' . $journal . '},' . "\n";
        if(!empty($this->get_journal_property('eissn')) && get_post_meta( $post_id, $post_type . '_journal', true ) === $this->get_journal_property('journal_title') )
            $bibtex .= '  issn = {' . $this->get_journal_property('eissn') . '},' . "\n";
        /* elseif(!empty($this->get_journal_property('secondary_journal_eissn')) && get_post_meta( $post_id, $post_type . '_journal', true ) === $this->get_journal_property('secondary_journal_title')) */
        /*     $bibtex .= '  issn = {' . $this->get_journal_property('secondary_journal_eissn') . '},' . "\n"; */

        if(!empty($this->get_journal_property('publisher')))
            $bibtex .= '  publisher = {' . $publisher . '},' . "\n";
        $bibtex .= '  volume = {' . $volume . '},' . "\n";
        $bibtex .= '  pages = {' . $pages . '},' . "\n";
        if(!empty($month))
            $bibtex .= '  month = ' . $month . ',' . "\n";
        $bibtex .= '  year = {' . $year . '}' . "\n";
        $bibtex .= '}';

        return $bibtex;
    }


        /**
         * Echo the html formated bibtex data.
         *
         * @since    0.1.0
         * @access   public
         * @param    int     $post_id     Id of the post.
         */
    public function the_bibtex_data( $post_id ) {

        echo $this->get_bibtex_html($post_id);

    }


        /**
         * Get the html formated bibtex data.
         *
         * Gets the bibtex data and a suitable caption. To be used in the
         * single templates.
         *
         * @since 0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function get_bibtex_html( $post_id ) {

        $post_type = get_post_type($post_id);
        $bibtex_html = '';
        $bibtex_html .= '<h3 class="toggle-following additional-info"><a href="javascript:void(0);" onclick="toggleFollowing(this);">&#9658; BibTeX data</a></h3>';
        $bibtex = $this->generate_bibtex($post_id);

        $bibtex_html .= '<textarea class="bibtex initially-display-none-if-js" rows="' . (mb_substr_count( $bibtex, "\n" )+1) . '" readonly>';
        $bibtex_html .= esc_textarea($bibtex);
        $bibtex_html .= '</textarea>';

        return $bibtex_html;
    }


        /**
         * Echo the polupar summary.
         *
         * Echo the popilar summary if available. To be used in the single
         * templates.
         *
         * @since  0.1.0
         * @access public
         * @param  int    $post_id     Id of the post.
         */
    public static function the_popular_summary( $post_id ) {
        echo static::get_popular_summary( $post_id );
    }

        /**
         * Get the polupar summary.
         *
         * Get the popilar summary if available. To be used in the single
         * templates.
         *
         * @since  0.3.0
         * @access public
         * @param  int    $post_id     Id of the post.
         */
    public static function get_popular_summary( $post_id ) {
        $output = '';
        $post_type = get_post_type($post_id);
        $popular_summary = get_post_meta( $post_id, $post_type . '_popular_summary', true );
        if( !empty($popular_summary) ) {
            $output .= '<h3 class="popular-summary additional-info"><a href="">Popular summary</a></h3>';
            $output .= '<div>';
            $output .= nl2br(esc_html($popular_summary));
            $output .= '</div>';
        }
        return $output;
    }

        /**
         * Get the formatted authors.
         *
         * Return the list of authors in first name last name format, seperated by
         * commas and the word 'and' including an oxford comma.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public static function get_formated_authors( $post_id ) {
        $post_type = get_post_type($post_id);
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        $author_names = array();
        for ($x = 0; $x < $number_authors; $x++) {
            $author_names[] = $author_given_names[$x] . " " . $author_surnames[$x];
        }

        return O3PO_Utility::oxford_comma_implode($author_names);
    }

        /**
         * Get the authors BibTeX formatted.
         *
         * @since    0.1.0
         * @access   public
         * @param    int       $post_id     Id of the post.
         */
    public static function get_formated_authors_bibtex( $post_id ) {

        $post_type = get_post_type($post_id);
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        $formated_authors = "";
        for ($x = 0; $x < $number_authors; $x++) {
            $formated_authors .= $author_surnames[$x] . ', ' . $author_given_names[$x] ;
            if( $x < $number_authors-1) $formated_authors .= " and ";
        }

        return O3PO_Latex::utf8_to_latex($formated_authors);
    }

        /**
         * Echo the license_information.
         *
         * @since     0.1.0
         * @access    public
         * @param     int      $post_id   Id of the post.
         */
    public function the_license_information( $post_id ) {

        echo $this->get_license_information($post_id);

    }

        /**
         * Get the license_information.
         *
         * @since     0.1.0
         * @access    public
         * @param     int      $post_id   Id of the post.
         */
    public function get_license_information( $post_id ) {

        $post_type = get_post_type($post_id);

        return '<p class="copyright">This ' . ucfirst($this->get_publication_type_name()) . ' is published in ' . get_post_meta( $post_id, $post_type . '_journal', true ) . ' under the <a rel="license" href="' . esc_attr($this->get_journal_property('license_url')) . '">' . esc_html($this->get_journal_property('license_name')) . '</a> license.' . (empty($this->get_journal_property('license_explanation')) ? '' : ' ' . $this->get_journal_property('license_explanation')) . '</p>';

    }


        /**
         * Get the authors html formated.
         *
         * Returns html formated authors with affiliations indicated as
         * superscripts.
         *
         * @since    0.1.0
         * @access   public
         * @param    int    $post_id     Id of the post.
         */
    public function get_formated_authors_html( $post_id ) {

        $post_type = get_post_type($post_id);
        $number_authors = get_post_meta( $post_id, $post_type . '_number_authors', true );
        $author_given_names = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_given_names');
        $author_surnames = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_surnames');
        $author_orcids = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_orcids');
        $number_affiliations = get_post_meta( $post_id, $post_type . '_number_affiliations', true );
        $author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');

        $all_authors_have_same_affiliation = true;
        if ( !empty($author_affiliations) ) {
            foreach($author_affiliations as $author_affiliation) {
                if( $author_affiliation !== end($author_affiliations) ) {
                    $all_authors_have_same_affiliation = false;
                    break;
                }
            }
        }

        $formated_authors = "";
        for ($x = 0; $x < $number_authors; $x++) {
	    	if ( !empty($author_orcids[$x]) )
                $formated_authors .= '<a href="' . $this->get_journal_property('orcid_url_prefix') . $author_orcids[$x] . '">' . $author_given_names[$x] . " " . $author_surnames[$x] . '</a>';
            else
                $formated_authors .= $author_given_names[$x] . " " . $author_surnames[$x];
            if ( !$all_authors_have_same_affiliation and !empty($author_affiliations[$x]) )
		    	$formated_authors .= '<sup>' . $author_affiliations[$x] . '</sup>';
            if( $x < $number_authors-1 and $number_authors > 2) $formated_authors .= ",";
            if( $x < $number_authors-1 ) $formated_authors .= " ";
            if( $x == $number_authors-2 ) $formated_authors .= "and ";
        }

        return $formated_authors;
    }


        /**
         * Echo the afiliations html formated.
         *
         * Html formated list of affiliations with superscript number
         * consistent with those of get_formated_authors_html().
         *
         * @since     0.1.0
         * @access    public
         * @param     int       $post_id      Id of the post.
         */
    public static function get_formated_affiliations_html( $post_id ) {

        $post_type = get_post_type($post_id);
        $affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_affiliations');
        $number_affiliations = get_post_meta( $post_id, $post_type . '_number_affiliations', true );
        $author_affiliations = static::get_post_meta_field_containing_array( $post_id, $post_type . '_author_affiliations');

        $all_authors_have_same_affiliation = true;
        if ( !empty($author_affiliations) ) {
            foreach($author_affiliations as $author_affiliation) {
                if( $author_affiliation !== end($author_affiliations) ) {
                    $all_authors_have_same_affiliation = false;
                    break;
                }
            }
        }

        if ( empty($affiliations) ) return '';
        $formated_affiliations = "";
        foreach ($affiliations as $x => $affiliation) {
            if (!$all_authors_have_same_affiliation)
                $formated_affiliations .= '<sup>' . (string)($x+1) . '</sup>';
            $formated_affiliations .= esc_html($affiliation) . "<br />";
        }

        return $formated_affiliations;
    }


        /**
         * Get publication date formated.
         *
         * Returns a nicely formted version of the publication date.
         * Currently this is set to the initernational format YYYY-MM-DD.
         *
         * @since    0.1.0
         * @access   public
         * @param    int      $post_id    Id of the post.
         */
    public static function get_formated_date_published( $post_id ) {

        $post_type = get_post_type($post_id);
        $date_published = get_post_meta( $post_id, $post_type . '_date_published', true );

        return $date_published;
    }



        /**
         * Adds css to the amin area to hide the WordPress title field for
         * the associated post type to avoid confusion.
         *
         * To be added to the 'admin_head' action.
         *
         * @since    0.1.0
         * @access   public
         */
    public function admin_page_extra_css() {

        global $post;
        if(empty($post))
            return;
        $post_id = $post->ID;
        if(empty($post_id))
            return;
        $post_type = get_post_type($post_id);
        if(empty($post_type) || $post_type !== $this->get_publication_type_name())
            return;

        echo '  <style type="text/css">' . "\n";
        echo '        input#title,' . "\n";
        echo '                   #title-prompt-text' . "\n";
        echo '        {' . "\n";
        echo '          display: none;' . "\n";
        echo '        }' . "\n";
        echo '        textarea#content' . "\n";
        echo '        {' . "\n";
        echo '          height: 10em;' . "\n";
        echo '        }' . "\n";
        echo '        #postimagediv > div.inside:after' . "\n";
        echo '        {' . "\n";
        echo '      content: \'Feature images must have an aspect ratio of 2:1 and the minimum size is 400:200 pixel, better are 600:300 or 800:400. The backgroud must be white or transparent. Please leave some margin around the actual content of the image\';' . "\n";
        echo '      color: red;' . "\n";
        echo '    }' . "\n";
        echo '    #major-publishing-actions:after {' . "\n";
        echo '  content: \'Posts are forced to private as long as the validation finds ERRORs. Please also carefully take into account all WARNINGs! Publicly publishing a post is an IRREVERSIBLE PROCESS, it sends emails to the authors and registers the DOI thereby fixing the publication date, volume and page number FOR EVER!!! In addition please manully check the references and affiliations.\';' . "\n";
        echo '  color: red;' . "\n";
        echo '}' . "\n";
        echo '#wp-content-editor-container > textarea.wp-editor-area {' . "\n";
        echo 'height: 100px !important;' . "\n";
        echo '}' . "\n";
        echo '</style>' . "\n";
    }


        /**
         * Get a post meta field that is expected to contain an array.
         *
         * Contrary to get_post_meta($post_id, $key, true ) this returns an
         * emty array() if the key is unset. This allows to directly add to
         * the array with the [] notation even on php >= 7.1, wich no longer
         * allows this notation on strings.
         *
         * @since 0.2.1
         * @param int     $post_id    The Id of the post whose meta field is to be retreived.
         * @param string  $key        The key that is to be retreived.
         */
    public static function get_post_meta_field_containing_array( $post_id, $key ) {

        $result = get_post_meta($post_id, $key, true );
        if($result === '')
            return array();
        else
            return $result;
    }

        /**
         * Get the path of the fulltext pdf.
         *
         * To be overwerites in subclasses. May return null if the subclass
         * has no fulltext pdf.
         *
         * @since 0.2.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    abstract public function get_fulltext_pdf_path( $post_id );

        /**
         * Get the pretty permalink of the pdf associated with a post.
         *
         * To be overwerites in subclasses. May return null if the subclass
         * has no fulltext pdf.
         *
         * @since     0.2.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    abstract public function get_pdf_pretty_permalink( $post_id );


        /**
         * Fake the author.
         *
         * To be added to the 'the_author' filter.
         *
         * @since    0.1.0
         * @access   pulic
         * @param    string    $display_name   Display name to be filtered.
         */
    public function get_the_author( $display_name ) {

        global $post;

        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if ( $post_type === $this->get_publication_type_name() ) {
            $journal = get_post_meta( $post_id, $post_type . '_journal', true );
            return $journal;
        }
        else
        {
            return $display_name;
        }
    }

        /**
         * Fake the author post link.
         *
         * To be added to the 'the_author_posts_link' filter.
         *
         * @since    0.3.0
         * @access   pulic
         * @param    string    $link   Link to be filtered.
         */
    public function get_the_author_posts_link( $link ) {

        global $post;

        if(!is_object($post))
            return $link;

        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if ( $post_type === $this->get_publication_type_name() ) {
            $slug = $this->get_publication_type_name_plural();
            return '/' . $slug;
        }
        else
        {
            return $link;
        }
    }

        /**
         * Force the usage of the page template for publication posts.
         *
         * To be added to the 'template_include' action.
         *
         * @since  0.3.0
         * @access public
         * @param  string   $template   The template that would be used.
         * @return string   Template that should be used.
         */
    public function use_page_template( $template ) {

        global $post;
        if(!is_object($post))
            return $template;

        $post_id = $post->ID;
        $post_type = get_post_type($post_id);

        if ( !is_single() or $this->get_publication_type_name() !== $post_type )
            return $template;

        return locate_template( array( 'page.php' ) );
    }

        /**
         * Get the email of the corresponding author.
         *
         * @since 0.3.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public static function get_corresponding_author_email( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_corresponding_author_email', true );
    }

        /**
         * Get the number of authors.
         *
         * @since 0.3.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public static function get_number_authors( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_number_authors', true );
    }

        /**
         * Get the title.
         *
         * @since 0.3.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public static function get_title( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_title', true );
    }

        /**
         * Get a meta data field.
         *
         * @since 0.3.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         */
    public static function get_post_meta( $post_id, $field ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_' . $field, true );
    }


        /**
         * Get date published.
         *
         * @since 0.3.0
         * @access    public
         * @param     int     $post_id     Id of the post.
         * @retur     string               YYYY-mm-dd representation of the date of publication.
         */
    public static function get_date_published( $post_id ) {

        $post_type = get_post_type($post_id);
        return get_post_meta( $post_id, $post_type . '_date_published', true );
    }


        /**
         * Get the citation counts for all publications of the given type.
         *
         * @since 0.3.0
         */
    public function get_all_citation_counts($fetch_if_outdated = false) {

        $post_type = $this->get_publication_type_name();

        $login_id = $this->get_journal_property('crossref_id');
        $login_passwd = $this->get_journal_property('crossref_pw');
        $crossref_url = $this->get_journal_property('crossref_get_forward_links_url');
        $doi_url_prefix = $this->get_journal_property('doi_url_prefix');
        $crossref_url = $this->get_journal_property('crossref_get_forward_links_url');
        $doi_prefix = $this->get_journal_property('doi_prefix');

        $query = array(
            'post_type' => $post_type,
            'post_status' => array('publish'),
            'posts_per_page' => -1,
                       );

        $errors = array();
        $timestamps = array();
        $citations_this_type = array();
        $my_query = new WP_Query( $query );

        if ( $my_query->have_posts() ) {
            $num = 0;
            while ( $my_query->have_posts() ) {
                $num++;
                $my_query->the_post();

                $post_id = get_the_ID();
                $cited_by_data = $this->get_cited_by_data($post_id, $fetch_if_outdated);
                if(!empty($cited_by_data['errors']))
                    $errors = array_merge($errors, $cited_by_data['errors']);

                if(!empty($cited_by_data['timestamps']))
                    foreach($cited_by_data['timestamps'] as $timestamp)
                        $timestamps[] = $timestamp;

                $doi = $this->get_doi($post_id);
                $citations_this_type[$doi] = $cited_by_data['citation_count'];
            }
        }

        $out = array(
            'citation_count' => $citations_this_type,
            'errors' => $errors,
                     );
        if(!empty($timestamps))
        {
            $out['min_timestamp'] = min($timestamps);
            $out['max_timestamp'] = max($timestamps);
        }

        return $out;
    }



        /**
         * Get the src of the feature image.
         *
         * @since  0.3.0
         * @access public
         * @param  int|WP_Post $post_id  Id of the post for which to get the social media thumbnail src.
         * @return string                Src of the social media thumbnail.
         */
    public static function get_social_media_thumbnail_src( $post_id ) {

        if(has_post_thumbnail($post_id))
        {
            $specific_image_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), "Full")[0];
            if(!empty($specific_image_url))
                return $specific_image_url;
        }

        $settings = O3PO_Settings::instance();
        $default_image_url = $settings->get_plugin_option('social_media_thumbnail_url');
        return $default_image_url;
    }


}
