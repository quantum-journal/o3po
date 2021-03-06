<?php

/**
 * Trait for the storage of data on manuscripts ready to publish.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.4.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Trait for the storage of data on manuscripts ready to publish.
 *
 * @since      0.4.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Ready2PublishStorage {

        /**
         * Manuscript fields to store
         *
         * array of fields to store. All other fields are ignored.
         *
         * @since    0.4.0
         * @access   private
         * @var      array   Array of fields to store.
         */
    private static $manuscript_info_fields_to_store = array(
        'eprint',
        'agree_to_publish',
        'acceptance_code',
        'title',
        'corresponding_author_email',
        'abstract',
        'author_given_names', // is populated from author_last_names
        'author_surnames', // is populated from author_last_names
        'author_name_styles',
        'number_award_numbers',
        'award_numbers',
        'funder_names',
        'funder_identifiers',
        'popular_summary',
        'feature_image_attachment_id', // due to compatibility with the publication type class we call these fields feature_image_... and not featured_image_... as in the form
        'feature_image_caption',
        'dissemination_multimedia',
        'fermats_library',
        'copyright_confirmation',
        'payment_method',
        'payment_amount',
        'invoice_recipient',
        'invoice_address',
        'invoice_vat_number',
        'ready2publish_comments',
        'time_submitted',
    );

        /**
         * Name of the plugin under which this storage operates
         *
         * @since    0.4.0
         * @access   private
         * @var      string   PLugin name.
         */
    private $plugin_name;

        /**
         * Slug of the storage
         *
         * @since    0.4.0
         * @access   private
         * @var      string   Slug.
         */
    private $slug;


        /**
         * Construct the storage
         *
         * @since    0.4.0
         * @access   public
         * @param    string $plugin_name The name of the plugin under which this storage runs.
         * @param    string $slug        Slug of this storage.
         * */
    public function __construct( $plugin_name, $slug ) {

        $this->plugin_name = $plugin_name;
        $this->slug = $slug;

    }

        /**
         * Store a manuscript
         *
         * @since    0.4.0
         * @access   public
         * @param    array $manuscript_info Information about the manuscript to store.
         * */
    public function store_manuscript( $manuscript_info ) {

        $settings = O3PO_Settings::instance();

            // Translate a few manuscript info fields
        $manuscript_info['feature_image_attachment_id'] = $manuscript_info['featured_image_attachment_id'];
        $manuscript_info['feature_image_caption'] = $manuscript_info['featured_image_caption'];
        $manuscript_info['ready2publish_comments'] = $manuscript_info['comments'];
        if(empty($manuscript_info['author_given_names']))
        {
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
        }

            //Clean award
        $clean_award_numbers = array();
        $clean_funder_identifiers = array();
        $clean_funder_names = array();
        foreach($manuscript_info['award_numbers'] as $key => $award_number)
            if(!empty($award_number))
            {
                $clean_award_numbers[] = $manuscript_info['award_numbers'][$key];
                $clean_funder_identifiers[] = $manuscript_info['funder_identifiers'][$key];
                $clean_funder_names[] = $manuscript_info['funder_names'][$key];
            }
        $manuscript_info['number_award_numbers'] = count($clean_award_numbers);
        $manuscript_info['award_numbers'] = $clean_award_numbers;
        $manuscript_info['funder_identifiers'] = $clean_funder_identifiers;
        $manuscript_info['funder_names'] = $clean_funder_names;

        $clean_manuscript_info = array();
        foreach(static::$manuscript_info_fields_to_store as $field)
            $clean_manuscript_info[$field] = $manuscript_info[$field];

        $manuscripts = get_option($this->plugin_name . '-' . $this->slug, array());
        $manuscripts[] = $clean_manuscript_info;
        update_option($this->plugin_name . '-' . $this->slug, $manuscripts);

    }


        /**
         * Get a manuscript by ID
         *
         * @since    0.4.0
         * @access   public
         * @param    int    $id ID of the manuscript to get.
         * */
    public function get_manuscript( $id ) {

        return $this->get_all_manuscripts()[$id];
    }

        /**
         * Get all manuscripts
         *
         * @since    0.4.0
         * @access   public
         * */
    public function get_all_manuscripts() {

        return get_option($this->plugin_name . '-' . $this->slug, array());
    }

        /**
         * Find the id of the post belonging to this eprint
         *
         * @since  0.4.0
         * @access public
         * @param  string   $eprint_without_version The eprint without version.
         * @return int|null                         The post id or null in case no post is found
         *                                          for the eprint
         * */
    public static function post_id_for_eprint( $eprint_without_version ) {

        $query = array(
            'post_type' => O3PO_PublicationType::get_active_publication_type_names(),
            'post_status' => 'any',
            'posts_per_page' => -1
                       );
        $my_query = new WP_Query( $query );
        while($my_query->have_posts()) {
            $my_query->the_post();
            $post_id = get_the_ID();
            $post_type = get_post_type($post_id);
            $post_eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
            $post_eprint_without_version = preg_replace('#v[0-9]+$#u', '', $post_eprint);
            if($post_eprint_without_version === $eprint_without_version)
                return $post_id;
        }
        return null;
    }

        /**
         * Get manuscripts by post status
         *
         * @since  0.4.0
         * @access public
         * @param  string $post_status One of 'unprocessed', 'published', or 'partial'
         * @return array  Array mapping manuscript ids to manuscript information
         *                and the post_id if any
         * */
    public function get_manuscripts( $post_status ) {

        $query = array(
            'posts_per_page' => -1,
            'post_status' => 'any',
            'post_type' => O3PO_PublicationType::get_active_publication_type_names()
                       );

        $eprints_already_having_post = array();
        $my_query = new WP_Query($query);
        while($my_query->have_posts()) {
            $my_query->the_post();
            $post_id = get_the_ID();
            $post_type = get_post_type($post_id);
            $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
            if(!empty($eprint))
                $eprints_already_having_post[preg_replace('#v[0-9]+$#u', '', $eprint)] = $post_id;
        }

        $result = array();
        foreach($this->get_all_manuscripts() as $id => $manuscript_info)
        {
            if(empty($manuscript_info['eprint']))
                continue;
            $eprint = $manuscript_info['eprint'];
            $eprint_without_version = preg_replace('#v[0-9]+$#u', '', $eprint);
            if($post_status === 'unprocessed' and !isset($eprints_already_having_post[$eprint_without_version]) or $post_status === 'partial' and isset($eprints_already_having_post[$eprint_without_version]) and get_post_status($eprints_already_having_post[$eprint_without_version]) !== 'publish')
            {
                $result[$id] = $manuscript_info;
                if(isset($eprints_already_having_post[$eprint_without_version]))
                    $result[$id]['post_id'] = $eprints_already_having_post[$eprint_without_version];
            }
        }

        return $result;
    }

}
