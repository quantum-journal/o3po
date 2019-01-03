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
        'type',
        'url',
        'venue',
        'volume',
        'year',
    );

    public function __construct( $meta_data ) {
        foreach($meta_data_fields as $field)
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

        $citation_journal_title = $cite->journal_title;
        $citation_article_title = $cite->article_title;
        $citation_title = $cite->title;
        $citation_series_title = $cite->series_title;
        $citation_volume_title = $cite->volume_title;
        $citation_volume = $cite->volume;
        $citation_component_number = $cite->component_number;
        $citation_issue = $cite->issue;
        $citation_first_page = $cite->first_page;
        $citation_item_number = $cite->item_number;
        $citation_page = !empty($citation_first_page) ? $citation_first_page : $citation_item_number;
        $citation_year = $cite->year;
        $citation_doi = $cite->doi;
        $citation_isbn = $cite->isbn;
        $citation_issn = $cite->issn;
        $citation_publication_type = $cite->publication_type;

        if(!empty($citation_journal_title))
            $citation_cite_as .= $citation_journal_title . " ";
        if(!empty($citation_series_title))
            $citation_cite_as .= $citation_series_title . " ";
        if(!empty($citation_volume_title))
            $citation_cite_as .= $citation_volume_title . " ";
        if(!empty($citation_component_number))
            $citation_cite_as .= $citation_component_number . " ";
        if(!empty($citation_volume))
            $citation_cite_as .= $citation_volume;
        if(!empty($citation_volume) && !empty($citation_issue))
            $citation_cite_as .= " ";
        if(!empty($citation_issue))
            $citation_cite_as .= $citation_issue;
        if((!empty($citation_volume) || !empty($citation_issue)) && !empty($citation_page))
            $citation_cite_as .= ', ';
        if((!empty($citation_volume) || !empty($citation_issue)) && empty($citation_page))
            $citation_cite_as .= ' ';
        if(!empty($citation_page))
            $citation_cite_as .= $citation_page . " ";
        if(empty($citation_cite_as))
            $citation_cite_as = $citation_doi . ' ';
        if(!empty($citation_year))
            $citation_cite_as .= '('. $citation_year . ')';
        if(!empty($citation_isbn))
            $citation_cite_as .= ' ISBN:'. $citation_isbn;

        return $citation_cite_as;
    }

}
