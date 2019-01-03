<?php

/**
 * A class to represent authors.
 *
 * @link       http://example.com
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * A class to represent authors.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Author {

    private $given_name;
    private $surname;
    private $name_style;
    private $orcid;
    private $url;
    private $affiliations;

    public function __construct( $given_name, $surname, $name_style='western', $orcid='', $url='', $affiliations=array() ) {
        $this->given_name = $given_name;
        $this->surname = $surname;
        $this->name_style = $name_style;
        $this->orcid = $orcid;
        $this->url = $url;
        if(is_array($affiliations))
            $this->affiliations = $affiliations;
        else
            $this->affiliations = preg_split('/\s*,\s*/', $affiliations);
    }

    public function get( $field ) {
        return $this->$field;
    }

    public function get_name() {
        $out = '';
        if(!empty($this->given_name))
            $out .= $this->given_name;
        if(!empty($this->given_name) and !empty($this->surname))
            $out .= ' ';
        if(!empty($this->surname))
            $out .= $this->surname;

        return $out;
    }

    public function get_affiliations_csv() {
        $out = '';
        foreach($this->affiliations as $affiliation)
        {
            if(!empty($out))
                $out .= ',';
            $out .= $affiliation;
        }
        return $out;
    }
}
