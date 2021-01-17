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


    private $plugin_name;
    private $slug;


    public function __construct( $plugin_name, $slug ) {

        $this->plugin_name = $plugin_name;
        $this->slug = $slug;

    }


    public function store_manuscript($manuscript_info) {

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

    public function get_manuscript( $id ) {

        return $this->get_all_manuscripts()[$id];
    }


    public function get_all_manuscripts() {

        $test_manuscripts = ['12345' => [
                'eprint' => '1234.00099v2',
                'title' => 'On Foo Bar',
                'corresponding_author_email' => "foo@bar.com",
                'abstract' => "Some boring abstract",
                'author_given_names' => ['Christian', 'Marcus', 'Ning'],
                'author_surnames' => ['Gogolin', 'Huber', 'Wang'],
                'author_name_styles' => ['western', 'western', 'eastern'],
                'number_award_numbers' => 2,
                'award_numbers' => array('563452431', 'ADUOIPIS'),
                'funder_names' => array('Foo agency', 'Bar agency'),
                'funder_identifiers' => array('563452431', ''),
                'popular_summary' => 'A very popular summary',
                'feature_image_attachment_id' => 3273,
                'feature_image_caption' => 'A great featured image!',
                'dissemination_multimedia' => 'Maybe you can embed this video?',
                'fermats_library' => 'checked',
                'payment_method' => 'paypal',
                'payment_amount' => '450€',
                'agree_to_publish' => 'checked',
                'acceptance_code' => 'AAA',
                'copyright_confirmation' => 'checked',
                'invoice_recipient' => 'Foo institute',
                'invoice_address' => 'Bar street in Baz Town 3245143',
                'invoice_vat_number' => 'AVT324123',
                'ready2publish_comments' => 'No comment',
                'time_submitted' => 1610393598,
                            ],
                '12346' => [
                'eprint' => '1234.1349v2',
                'title' => 'A longer title that usual papers have it',
                'corresponding_author_email' => "baz@gmail.com",
                'abstract' => "This abstract is much better",
                'author_given_names' => ['Adam', 'Eva'],
                'author_surnames' => ['Riese', 'Zwerg'],
                'author_name_styles' => ['western', 'western'],
                'number_award_numbers' => 2,
                'award_numbers' => array('563452431', 'ADUOIPIS'),
                'funder_names' => array('Foo agency', 'Bar agency'),
                'funder_identifiers' => array('563452431', ''),
                'popular_summary' => 'An even more popular summary',
                'feature_image_attachment_id' => 3273,
                'feature_image_caption' => '',
                'dissemination_multimedia' => '',
                'fermats_library' => '',
                'payment_method' => 'invoice',
                'payment_amount' => '450€',
                'agree_to_publish' => 'checked',
                'acceptance_code' => 'AAA',
                'copyright_confirmation' => 'checked',
                'invoice_recipient' => '',
                'invoice_address' => '',
                'invoice_vat_number' => '',
                'ready2publish_comments' => 'I did not get the form!!!',
                'time_submitted' => 1610393599,
                            ]];

        $manuscripts = get_option($this->plugin_name . '-' . $this->slug, array());

        #return array_merge($manuscripts, $test_manuscripts);
        return array_merge($manuscripts);
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
