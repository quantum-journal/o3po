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

    private static $meta_data_fields = array(
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
        foreach(static::$meta_data_fields as $field)
            if(isset($meta_data[$field]))
            {
                if(is_array($meta_data[$field]))
                    $this->meta_data[$field] = $meta_data[$field];
                else
                    $this->meta_data[$field] = (string)$meta_data[$field];
            }
            else
                $this->meta_data[$field] = '';
    }

    public function get( $field ) {

        return $this->meta_data[$field];
    }

    public function get_formated_authors() {

        if(empty($this->get('authors')) or !is_array($this->get('authors')))
            return '';

        $author_names = array();
        foreach ($this->get('authors') as $author) {
            $author_names[] = $author->get_name();
        }

        return O3PO_Utility::oxford_comma_implode($author_names);
    }


    public function get_formated_html( $doi_url_prefix, $arxiv_url_abs_prefix ) {

        $bibitem_html = '';

        $bibitem_html .= esc_html($this->get_formated_authors());
        if(!empty($bibitem_html))
            $bibitem_html .= ', ';

        if(!empty($this->get('title')))
            $bibitem_html .= '"' . esc_html($this->get('title')) . '", ';

        $citation_cite_as_html = esc_html($this->get_cite_as_text());
        if(!empty($this->get('doi')))
            $bibitem_html .= '<a href="' . esc_attr($doi_url_prefix . $this->get('doi')) . '">' . $citation_cite_as_html . '</a>.';
        elseif(!empty($this->get('eprint')))
            $bibitem_html .= '<a href="' . esc_attr($arxiv_url_abs_prefix . $this->get('eprint')) . '">' . $citation_cite_as_html . '</a>.';
        else
            $bibitem_html .= $citation_cite_as_html;

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


        /**
         *
         *  $bibitem1 takes preference over $bibitem2
         *
         */
    public static function merge($bibitem1, $bibitem2) {

        $merged_meta_data = array();
        foreach(static::$meta_data_fields as $field)
        {
            $merged_meta_data[$field] = $bibitem1->get($field);
            if(empty($merged_meta_data[$field]))
                $merged_meta_data[$field] = $bibitem2->get($field);
        }

        return new O3PO_Bibentry($merged_meta_data);
    }

    public static function match($bibitem1, $bibitem2) {

        if($bibitem1 == $bibitem2)
            return true;
        elseif(!empty($bibitem1->get('eprint')) and $bibitem1->get('eprint') === $bibitem2->get('eprint'))
            return true;
        elseif(!empty($bibitem1->get('doi')) and $bibitem1->get('doi') === $bibitem2->get('doi'))
            return true;
        else #now we do some heuristics to catch the remaining duplicates:
        {
            $years_identical = false;
            if(!empty($bibitem1->get('year')) and !empty($bibitem2->get('year')) and $bibitem1->get('year') == $bibitem2->get('year') )
                $years_identical = true;

            if($years_identical)
            {
                $titles_similar = false;
                $authors_similar = false;
                if(!empty($bibitem1->get('title')) and !empty($bibitem2->get('title')))
                {
                    $t1 = substr(strtolower($bibitem1->get('title')), 0, 255);
                    $t2 = substr(strtolower($bibitem2->get('title')), 0, 255);
                    $l1 = strlen($t1); #length in bytes
                    $l2 = strlen($t2);
                    $lmin = min($l1, $l2);
                    $lev = levenshtein($t1, $t2);
                    if($lev <= 0.2*$lmin)
                        $titles_similar = true;
                }
                if(!empty($bibitem1->get('title')) and !empty($bibitem2->get('title')))
                {
                    $a1 = substr(strtolower($bibitem1->get_formated_authors()), 0, 255);
                    $a2 = substr(strtolower($bibitem2->get_formated_authors()), 0, 255);
                    $l1 = strlen($t1); #length in bytes
                    $l2 = strlen($t2);
                    $lmin = min($l1, $l2);
                    $lev = levenshtein($a1, $a2);
                    if($lev <= 0.2*$lmin)
                        $authors_similar = true;
                }
                if(($titles_similar and $authors_similar)
                       /* or ($authors_similar and empty($bibitem1->get('title')) and empty($bibitem2->get('title'))) */
                       /* or ($title_similar and empty($bibitem1->get('authors')) and empty($bibitem2->get('authors'))) */
                   )
                    return true;
            }
        }

        return false;
    }


        /**
         *
         * Merges $array1 into $array2,
         */
    public static function merge_bibitem_arrays($array1, $array2 ) {

        $merged = $array2;
        foreach($array1 as $key1 => $bibitem1){
            $merged_at_least_once = false;
            foreach($array2 as $key2 => $bibitem2){
                if(static::match($bibitem2, $bibitem1))
                {
                    $merged[$key2] = static::merge($bibitem2, $bibitem1);
                    $merged_at_least_once = true;
                }
            }
            if(!$merged_at_least_once)
                $merged[] = $bibitem1;
        }
        return $merged;
    }


}
