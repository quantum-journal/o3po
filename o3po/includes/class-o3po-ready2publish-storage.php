<?php

/**
 * Trait for the storage of data on manuscripts ready to publish.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.1+
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Trait for the storage of data on manuscripts ready to publish.
 *
 * @since      0.3.1+
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Ready2PublishStorage {

    private static $manuscript_info_fields = array(
        'eprint',
        'title',
        'corresponding_author_email',
        'abstract',
        'author_first_names',
        'author_last_names',
        'author_name_styles',
        'award_numbers',
        'funder_names',
        'funder_identifiers',
        'popular_summary',
        'featured_image',
        'featured_image_caption',
        'multimedia_comment',
        'fermats_library',
        'payment_method',
        'invoice_recipient',
        'invoice_address',
        'invoice_vat_number',
        'author_comments',
    );


    private $plugin_name;
    private $slug;

    public function __construct( $plugin_name, $slug ) {

        $this->plugin_name = $plugin_name;
        $this->slug = $slug;

    }


    public function store_manuscript($manuscript_info) {

        $settings = O3PO_Settings::instance();

        $clean_manuscript_info = array();
        foreach(static::$manuscript_info_fields as $field)
            if(isset($manuscript_info[$field]))
                $clean_manuscript_info[$field] = $manuscript_info[$field];

        $manuscripts = get_option($this->plugin_name . '-' . $this->slug, array());

        $manuscripts[] = $clean_manuscript_info;

        update_option($this->plugin_name . '-' . $this->slug, $manuscripts);

    }

    public function get_manuscript( $id ) {

        return $this->get_all_manuscripts()[$id];
    }


    public function get_all_manuscripts() {

        $test_manuscripts = ['12345' => [
                'eprint' => '1234.00099v2',
                'title' => 'On Foo Bar',
                'corresponding_author_email' => "foo@bar.com",
                'abstract' => "Some boring abstract",
                'author_first_names' => ['Christian', 'Marcus', 'Wang'],
                'author_last_names' => ['Gogolin', 'Huber', 'Ning'],
                'author_name_styles' => ['western', 'western', 'eastern'],
                'award_numbers' => array('563452431', 'ADUOIPIS'),
                'funder_names' => array('Foo agency', 'Bar agency'),
                'funder_identifiers' => array('563452431', ''),
                'popular_summary' => 'A very popular summary',
                'featured_image' => '',
                'featured_image_caption' => '',
                'multimedia_comment' => '',
                'fermats_library' => 'checked',
                'payment_method' => '',
                'invoice_recipient' => '',
                'invoice_address' => '',
                'invoice_vat_number' => '',
                'author_comments' => '',
                            ],
                '12346' => [
                'eprint' => '1234.1349v2',
                'title' => 'A longer title that usual papers have it',
                'corresponding_author_email' => "baz@gmail.com",
                'abstract' => "This abstract is much better",
                'author_first_names' => ['Adam', 'Eva'],
                'author_last_names' => ['Riese', 'Zwerg'],
                'author_name_styles' => ['western', 'western'],
                'popular_summary' => 'An even more popular summary',
                'featured_image' => '',
                'featured_image_caption' => '',
                'multimedia_comment' => '',
                'fermats_library' => '',
                'payment_method' => '',
                'invoice_recipient' => '',
                'invoice_address' => '',
                'invoice_vat_number' => '',
                'author_comments' => '',
                            ]];

        $manuscripts = get_option($this->plugin_name . '-' . $this->slug, array());

        return array_merge($manuscripts, $test_manuscripts);
    }

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
