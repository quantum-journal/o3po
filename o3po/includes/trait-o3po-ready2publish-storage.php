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
trait O3PO_Ready2PublishStorage {

        /**
         * Slug of this storage.
         *
         * @since 0.3.1+
         * @access protected
         * @var string $slug Slug of the storage.
         */
    protected $slug;

    public static function store_manuscript($manuscript_info) {

    }

    public static function get_manuscript( $id ) {

        return $this->get_all_manuscripts()[$id];
    }


    public static function get_all_manuscripts() {
        return ['12345' => [
                'post_type' => 'paper',
                'eprint' => '1234.56789v4',
                'title' => 'On Foo Bar',
                'corresponding_author_email' => "foo@bar.com",
                'abstract' => "Some boring abstract",
                'author_first_names' => ['Christian', 'Marcus'],
                'author_second_names' => ['Gogolin', 'Huber'],
                'author_name_styles' => ['western', 'eastern'],
                'popular_summary' => '',
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
    }

    public static function get_all_unpublished_manuscripts() {

        $eprints_of_all_published_without_version = [];
        $query = array(
            #'post_type' => $post_type,
            'post_status' => array('publish'),
            'posts_per_page' => -1,
                       );
        $my_query = new WP_Query( $query );
        while($my_query->have_posts()) {
            $my_query->the_post();
            $post_id = get_the_ID();
            $post_type = get_post_type($post_id);
            $eprint = get_post_meta( $post_id, $post_type . '_eprint', true );
            $eprints_of_all_published_without_version[] = preg_replace('#v[0-9]+$#u', '', $eprint);
        }
        $unpublished = [];
        foreach(static::get_all_manuscripts() as $id => $manuscript_info)
        {
            $eprint = $manuscript_info['eprint'];
            $eprint_without_version = preg_replace('#v[0-9]+$#u', '', $eprint);
            if(!in_array($eprint_without_version, $eprints_of_all_published_without_version))
                $unpublished[$id] = $manuscript_info;
        }
        return $unpublished;
    }

}
