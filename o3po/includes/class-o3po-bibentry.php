<?php

/**
 * A class to represent bibliography entries.
 *
 * @link       http://example.com
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-author.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';

/**
 * A class to represent bibliography entries.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Bibentry {

    private $meta_data;

    private $meta_data_fields = array(
        'authors',
        'chapter',
        'collectiontitle',
        'day',
        'doi',
        'editor',
        'eprint',
        'howpublished',
        'institution',
        'isbn',
        'issn',
        'issue',
        'month',
        'page',
        'publisher',
        'ref',
        'title',
        'type',
        'url',
        'venue',
        'volume',
        'year',
    );

    public function __construct( $meta_data ) {
        foreach($this->meta_data_fields as $field)
            if(isset($meta_data[$field]))
                $this->meta_data[$field] = $meta_data[$field];
            else
                $this->meta_data[$field] = '';
    }

    public function get( $field ) {

        return $this->meta_data[$field];
    }

    public function get_formated_html( $doi_url_prefix, $arxiv_url_abs_prefix ) {

        $bibitem_html = '';

        if(!empty($this->get('authors')))
        {
            $author_names = array();
            foreach ($this->get('authors') as $author) {
                $author_names[] = $author->get_name();
            }
            $bibitem_html .= O3PO_Utility::oxford_comma_implode($author_names) . ', ';
        }

        if(!empty($this->get('title')))
            $bibitem_html .= '"' . esc_html($this->get('title')) . '", ';

        $citation_cite_as = $this->get_cite_as_text();
        if(!empty($this->get('doi')))
            $bibitem_html .= '<a href="' . esc_attr($doi_url_prefix . $this->get('doi')) . '">' . esc_html($citation_cite_as) . '</a>.';
        elseif(!empty($this->get('eprint')))
            $bibitem_html .= '<a href="' . esc_attr($arxiv_url_abs_prefix . $this->get('eprint')) . '">' . esc_html($citation_cite_as) . '</a>.';
        else
            $bibitem_html .= esc_html($citation_cite_as);

        return $bibitem_html;
    }


    public function get_cite_as_text() {

        $citation_cite_as = '';

        if(!empty($this->get('howpublished')))
            $citation_cite_as .= $this->get('howpublished') . " ";
        if(!empty($this->get('venue')))
            $citation_cite_as .= $this->get('venue') . " ";
        if(!empty($this->get('collectiontitle')))
            $citation_cite_as .= $this->get('collectiontitle') . " ";
        if(!empty($this->get('institution')))
            $citation_cite_as .= $this->get('institution') . " ";
        if(!empty($this->get('volume')))
            $citation_cite_as .= $this->get('volume');
        if(!empty($this->get('volume')) and !empty($this->get('issue')))
            $citation_cite_as .= " ";
        if(!empty($this->get('issue')))
            $citation_cite_as .= $this->get('issue');
        if((!empty($this->get('volume')) or !empty($this->get('issue'))) and !empty($this->get('page')))
            $citation_cite_as .= ", ";
        if(!empty($this->get('page')))
            $citation_cite_as .= $this->get('page') . " ";
        if(!empty($this->get('eprint')))
            $citation_cite_as .= 'arXiv:'. $this->get('eprint') . " ";
        if(!empty($this->get('year')))
            $citation_cite_as .= '(' . $this->get('year') . ")";
        /* if(!empty($this->get('doi'))) */
        /*     $citation_cite_as .= ' doi:'. $this->get('doi'); */
        if(!empty($this->get('isbn')))
            $citation_cite_as .= ' ISBN:'. $this->get('isbn');

        return trim($citation_cite_as, ' ');
    }

}
