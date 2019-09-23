<?php

/**
 * A class to represent bibliography entries.
 *
 * @link       https://quantum-journal.org/o3po/
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

        /**
         * Array holding the meta-data of this bibtentry
         *
         * @sinde 0.3.0
         * @access private
         */
    private $meta_data;


        /**
         * Array of all supported meta-data fields
         *
         * @sinde 0.3.0
         * @access private
         */
    private static $meta_data_fields = array(
        'authors',
        'chapter',
        'collectiontitle',
        'day',
        'doi',
        'editors',
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

        /**
         * Construct a bibtentry.
         *
         * Only the entries in $meta_data whose keys are listed in
         * $meta_data_fields are taken into account.
         * All fields that are not arrays are converted to string.
         * Fields that are in $meta_data_fields but for which no data
         * is provided are initialized to an empty string.
         *
         * @sinde 0.3.0
         * @access public
         * @param array $meta_data The meta-data to store in this bibentry
         */
    public function __construct( $meta_data=array() ) {

        $this->meta_data = array();
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

        /**
         * Get the value of a given field
         *
         * @since 0.3.0
         * @access public
         * @param string $field The key of the field to get.
         * @return mixed The string or array stored in the field.
         */
    public function get( $field ) {
        if(isset($this->meta_data[$field]))
            return $this->meta_data[$field];
        else
            return '';
    }

        /**
         * Oxford comma separated list of all author and editor surnames.
         *
         * @since 0.3.0
         * @access public
         * @return string Oxford comma separated list of all author surnames.
         */
    public function get_surnames() {

        $surnames = array();
        if(!empty($this->meta_data['authors']) and is_array($this->meta_data['authors']))
            foreach($this->meta_data['authors'] as $author) {
                $surnames[] = $author->get_surname();
            }
        if(!empty($this->meta_data['editors']) and is_array($this->meta_data['editors']))
            foreach($this->meta_data['editors'] as $author) {
                $surnames[] = $author->get_surname();
            }

        return O3PO_Utility::oxford_comma_implode($surnames);
    }


        /**
         * Formated author and editor list
         *
         * Editors are indicated with the string Editor: or Editors: before their names
         *
         * @since 0.3.0
         * @access public
         * @return string Oxford comma separated list of all authors and editors.
         */
    public function get_formated_authors() {

        $result = '';
        $author_names = array();
        if(!empty($this->meta_data['authors']) and is_array($this->meta_data['authors']))
        {
            $author_names = array();
            foreach ($this->meta_data['authors'] as $author) {
                $author_names[] = $author->get_name();
            }
            $result .= O3PO_Utility::oxford_comma_implode($author_names);
        }
        $editor_names = array();
        if(!empty($this->meta_data['editors']) and is_array($this->meta_data['editors']))
        {
            foreach ($this->meta_data['editors'] as $editor) {
                $editor_names[] = $editor->get_name();
            }
            if(count($author_names) > 0)
                $result .= ', ';
            if(count($editor_names) == 1)
                $result .= 'Editor: ';
            elseif(count($editor_names) > 1)
                $result .= 'Editors: ';
            $result .= O3PO_Utility::oxford_comma_implode($editor_names);
        }

        return trim($result);
    }


        /**
         * HTML representation of the bibentry.
         *
         * @since 0.3.0
         * @access public
         * @param $doi_url_prefix        Prefix to use for DOI links.
         * @param $arxiv_url_abs_prefix  Prefix to use for arXiv links.
         * @return string                HTHL representation of the bibentry.
         */
    public function get_formated_html( $doi_url_prefix, $arxiv_url_abs_prefix ) {

        $bibitem_html = '';

        $bibitem_html .= esc_html($this->get_formated_authors());
        if(!empty($bibitem_html))
            $bibitem_html .= ', ';

        if(!empty($this->meta_data['title']))
            $bibitem_html .= '"' . esc_html($this->meta_data['title']) . '", ';

        if(!empty($this->meta_data['eprint']))
            $bibitem_html .= '<a href="' . esc_attr($arxiv_url_abs_prefix . $this->meta_data['eprint']) . '">' . esc_html("arXiv:" . $this->meta_data['eprint']) . '</a>';
        if(!empty($this->meta_data['doi']) and !empty($this->meta_data['eprint']))
            $bibitem_html .= ", ";
        if(!empty($this->meta_data['doi']))
            $bibitem_html .= '<a href="' . esc_attr($doi_url_prefix . $this->meta_data['doi']) . '">' . esc_html($this->get_cite_as_text()) . '</a>';

        if(empty($this->meta_data['doi']) and empty($this->meta_data['eprint']))
            $bibitem_html .= esc_html($this->get_cite_as_text());

        $bibitem_html = trim($bibitem_html, ' ,') . '.';

        return $bibitem_html;
    }


        /**
         * How to cite this bibentry.
         *
         * @since 0.3.0
         * @access public
         * @return string  Text describing how to cite this bibentry.
         */
    public function get_cite_as_text() {

        $citation_cite_as = '';

        if(!empty($this->meta_data['type']) and !in_array(mb_strtolower($this->meta_data['type']), array('book', 'full_text')))
            $citation_cite_as .= ucfirst($this->meta_data['type']) . " ";
        if(!empty($this->meta_data['venue']))
            $citation_cite_as .= $this->meta_data['venue'] . " ";
        if(!empty($this->meta_data['collectiontitle']))
            $citation_cite_as .= $this->meta_data['collectiontitle'] . " ";
        if(!empty($this->meta_data['publisher']) and $this->meta_data['type'] == "book")
            $citation_cite_as .= $this->meta_data['publisher'] . " ";
        if(!empty($this->meta_data['institution']))
            $citation_cite_as .= $this->meta_data['institution'] . " ";
        if(!empty($this->meta_data['howpublished']))
            $citation_cite_as .= "(" . $this->meta_data['howpublished'] . ") ";
        if(!empty($this->meta_data['volume']))
            $citation_cite_as .= $this->meta_data['volume'];
        if(!empty($this->meta_data['volume']) and !empty($this->meta_data['issue']))
            $citation_cite_as .= " ";
        if(!empty($this->meta_data['issue']))
            $citation_cite_as .= $this->meta_data['issue'];
        if((!empty($this->meta_data['volume']) or !empty($this->meta_data['issue'])) and !empty($this->meta_data['page']))
            $citation_cite_as .= ", ";
        if(!empty($this->meta_data['page']))
            $citation_cite_as .= $this->meta_data['page'] . " ";
        /* if(!empty($this->meta_data['eprint'])) */
        /*     $citation_cite_as .= 'arXiv:'. $this->meta_data['eprint'] . " "; */
        if(!empty($this->meta_data['year']))
            $citation_cite_as .= '(' . $this->meta_data['year'] . ")";
        /* if(!empty($this->meta_data['doi'])) */
        /*     $citation_cite_as .= ' doi:'. $this->meta_data['doi']; */
        if(!empty($this->meta_data['isbn']))
            $citation_cite_as .= ' ISBN:'. $this->meta_data['isbn'];
        /* if(!empty($this->meta_data['url'])) */
        /*     $citation_cite_as .= $this->meta_data['url'] . ' '; */

        return trim($citation_cite_as, ' ');
    }


        /**
         * Merge two bibentries.
         *
         * Bibentries are merged field wise, in case of collisions
         * $bibitem1 takes preference over $bibitem2.
         *
         * @since 0.3.0
         * @access public
         * @param O3PO_Bibentry $bibitem1 First bibentry to merge.
         * @param O3PO_Bibentry $bibitem2 First bibentry to merge.
         * @return O3PO_Bibentry          Merged bibentry.
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

        /**
         * Compare two bibentries.
         *
         * @since 0.3.0
         * @access public
         * @param O3PO_Bibentry $bibitem1 First bibentry to match.
         * @param O3PO_Bibentry $bibitem2 First bibentry to match.
         * @return bool     True if bibentries are considered similar enough to probably represent the same bibliographic item, false otherwise.
         */
    public static function match($bibitem1, $bibitem2) {

        if(!empty($bibitem1->get('eprint')) and !empty($bibitem2->get('eprint')))
        {
            if($bibitem1->get('eprint') === $bibitem2->get('eprint'))
                return true;
            else
                return false;
        }
        elseif(!empty($bibitem1->get('doi')) and !empty($bibitem2->get('doi')))
        {
            if($bibitem1->get('doi') === $bibitem2->get('doi'))
                return true;
            else
                return false;
        }
        else #now we do some heuristics to catch the remaining duplicates:
        {
            $years_similar = false;
            if(!empty($bibitem1->get('year')) and !empty($bibitem2->get('year')) and abs($bibitem1->get('year') - $bibitem2->get('year'))<=5 )
                $years_similar = true;

            if($years_similar)
            {
                $titles_similar = false;
                $titles_very_similar = false;
                if(!empty($bibitem1->get('title')) and !empty($bibitem2->get('title')))
                {
                    # using byte based string functions here because levenshtein() is also byte based
                    $t1 = substr(mb_strtolower($bibitem1->get('title')), 0, 255);
                    $t2 = substr(mb_strtolower($bibitem2->get('title')), 0, 255);
                    $l1 = strlen($t1);
                    $l2 = strlen($t2);
                    $lmin = min($l1, $l2);
                    $lev = levenshtein($t1, $t2);
                    if($lev <= 0.2*$lmin or $lev <= 5)
                        $titles_similar = true;
                    if($lev <= 0.1*$lmin)
                        $titles_very_similar = true;
                }
                if($titles_similar)
                {
                    $authors_similar = false;
                    $authors_very_similar = false;
                    if(!empty($bibitem1->get_surnames()) and !empty($bibitem2->get_surnames()))
                    {
                        # using byte based string functions here because levenshtein() is also byte based
                        $a1 = substr(mb_strtolower($bibitem1->get_surnames()), 0, 255);
                        $a2 = substr(mb_strtolower($bibitem2->get_surnames()), 0, 255);
                        $l1 = strlen($a1);
                        $l2 = strlen($a2);
                        $lmin = min($l1, $l2);
                        $lev = levenshtein($a1, $a2);
                        if($lev <= 0.2*$lmin or $lev <= 2)
                            $authors_similar = true;
                        if($lev <= 0.1*$lmin)
                            $authors_very_similar = true;
                    }
                    if(($titles_similar and $authors_similar and ( $titles_very_similar or $authors_very_similar )))
                        return true;
                }
            }
        }

        return false;
    }


        /**
         * Merge two arrays of bibentries.
         *
         * Merges $array2 into $array1. The content of the entries in $array1
         * takes preference over those in $array2, entries in $array2 that
         * were not merged are appended at the end. Array keys in $array1
         * are preserved.
         *
         * If $remove_dulicates is true all duplicates are removed from the
         * final array by merging them into the first matching entry.
         * Non-removed array keys are preserved.
         *
         * @param array $array1           First array of bibtentries.
         * @param array $array2           Second array of bibtentries.
         * @param bool $remove_dulicates  Whether to remove duplicates.
         * @return array                  Merged array of bibentries.
         */
    public static function merge_bibitem_arrays($array1, $array2, $remove_dulicates=true ) {

        if(empty($array1) and empty($array2))
            return array();
        if(empty($array1))
            if($remove_dulicates)
                return static::remove_duplicates($array2, true);
            else
                return $array2;
        if(empty($array2))
            if($remove_dulicates)
                return static::remove_duplicates($array1, true);
            else
                return $array1;

        $merged = $array1;
        foreach($array2 as $key2 => $bibitem2){
            $merged_at_least_once = false;
            foreach($array1 as $key1 => $bibitem1){
                if(static::match($bibitem1, $bibitem2))
                {
                    $merged[$key1] = static::merge($bibitem1, $bibitem2);
                    $merged_at_least_once = true;
                }
            }
            if(!$merged_at_least_once)
                $merged[] = $bibitem2;
        }
        if($remove_dulicates)
            $merged = static::remove_duplicates($merged, true);

        return $merged;
    }


        /**
         * Remove duplicates from an array of bibentries.
         *
         * Duplicates are identified by comparing pairs with the help of match().
         *
         * @since 0.3.0
         * @access public
         * @param array $array  Array of bibentries from which duplicates are to be removed.
         * @param bool $merge   Whether to merge duplicates or simply discard them.
         */
    public static function remove_duplicates($array, $merge=false) {

        $keys_to_unset = array();
        $num_elem1=0;
        foreach($array as $key1 => $bibitem1){
            $num_elem2=0;
            foreach($array as $key2 => $bibitem2){
                if($num_elem2 > $num_elem1)
                {
                    if(static::match($bibitem1, $bibitem2))
                    {
                        if($merge)
                            $array[$key1] = static::merge($bibitem1, $bibitem2);
                        $keys_to_unset[$key2] = true;
                    }
                }
                $num_elem2 += 1;
            }
            $num_elem1 += 1;
        }
        foreach($keys_to_unset as $key => $val)
        {
            unset($array[$key]);
        }

        return $array;
    }

}
