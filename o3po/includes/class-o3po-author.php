<?php

/**
 * A class to represent authors.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';

/**
 * A class to represent authors.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Author {

        /**
         * Given name.
         *
         * @since 0.3.0
         * @access private
         */
    private $given_name;

        /**
         * Surname.
         *
         * @since 0.3.0
         * @access private
         */
    private $surname;

        /**
         * Name style.
         *
         * Can be one of "western", "eastern", "islensk", or "given-only".
         *
         * @since 0.3.0
         * @access private
         */
    private $name_style;

        /**
         * ORCID.
         *
         * @since 0.3.0
         * @access private
         */
    private $orcid;

        /**
         * Url.
         *
         * @since 0.3.0
         * @access private
         */
    private $url;

        /**
         * Array of affiliations.
         *
         * An array of strings of the affiliations of the author.
         *
         * @since 0.3.0
         * @access private
         */
    private $affiliations;

        /**
         * Array of valid name styles
         *
         * @since 0.3.0
         * @access private
         */
    private static $name_styles = array("western", "eastern", "islensk", "given-only");

        /**
         * Construct an author
         *
         * Construct an author object from given_name, surname, and further
         * details. The $orcid is verified with a checksum test, and
         * $affiliations can be given as both an array of strings or
         * a single string of comma separated affiliations. Affiliations that
         * contain a comma must be passed as an array containing a single
         * string.
         *
         * @since 0.3.0
         * @access public
         * @param string $given_name                     The given name.
         * @param string $surname                        The surname.
         * @param string $name_style (optional)          One of "western", "eastern", "islensk", ir "given-only".
         * @param string $orcid (optional)               The ORCID
         * @param string $url (optional)                 The url, for example of the authors homepage.
         * @param array|string $affiliations (optional)  The affiliations of the author, either as an array of strings or a single string of comma separated affiliations.
         */
    public function __construct( $given_name, $surname, $name_style='western', $orcid='', $url='', $affiliations=array() ) {

        $this->given_name = (string)$given_name;
        $this->surname = (string)$surname;
        if(empty($name_style))
            $name_style = 'western';
        if(in_array((string)$name_style, static::$name_styles))
            $this->name_style = (string)$name_style;
        else
            throw new InvalidArgumentException('The value of $name_style (' . $name_style . ') is not one of the allowed values ' . implode(static::$name_styles, ', ') . '.');
        if(empty($orcid) or O3PO_Utility::check_orcid($orcid) === true)
            $this->orcid = (string)$orcid;
        else
            throw new InvalidArgumentException('The value of $orcid (' . $orcid . ') is not a valid ORCID.');
        $this->url = (string)$url;
        if(is_array($affiliations))
            $this->affiliations = $affiliations;
        elseif(is_string($affiliations) == True)
            $this->affiliations = preg_split('/\s*,\s*/u', (string)$affiliations, -1, PREG_SPLIT_NO_EMPTY);
        else
            throw new InvalidArgumentException('The value given for $affiliations is neither an array not a string.');

    }

        /**
         * Get the content of any field of the the author.
         *
         * @since 0.3.0
         * @access public
         * @param string $field The field whose value to get.
         * @return string Value of the $field.
         */
    public function get( $field ) {
        return $this->$field;
    }

        /**
         * Get the full name of the author.
         *
         * TODO: Name styles are not currently honored.
         *
         * @sice 0.3.0
         * @access public
         * @return string The name of the author.
         */
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

        /**
         * Get the surname of the author.
         *
         * @sice 0.3.0
         * @access public
         * @return string The surname of the author.
         */
    public function get_surname() {
        return $this->surname;
    }

        /**
         * Get a comma separated string of the author's affiliations.
         *
         * TODO: Commas in affiliations are not escaped.
         *
         * @sice 0.3.0
         * @access public
         * @return string Comma separated string of affiliations.
         */
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
