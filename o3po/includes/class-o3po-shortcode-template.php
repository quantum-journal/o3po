<?php

/**
 * A class to represent shortcode templates.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0+
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * A class to represent shortcode templates.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_ShortcodeTemplate {

        /**
         * Array holding the shortcodes of this template
         *
         * @since 0.3.0+
         * @access private
         * @var array The array keys are the shortcodes, the values are arrays
         *            with two fields: 'description' holding the description
         *            of the shortcode and 'example' holding an exemplary value
         *            which is representative for the values with which the
         *            shortcode is ususally replaced. Shortcodes must be of the
         *            format [shortcode].
         */
    private $shortcodes;


        /**
         * The template
         *
         * @since 0.3.0+
         * @access private
         * @avr string The string specifying the template.
         */
    private static $template;


        /**
         * Construct a shortcode template.
         *
         * @since 0.3.0+
         * @access public
         * @param array $meta_data The meta-data to store in this bibentry
         */
    public function __construct( $template, $shortcodes ) {

        foreach($shortcodes as $shortcode => $specification)
        {
            if(!is_string($shortcode) or mb_substr($shortcode, 0, 1) !== '[' or mb_substr($shortcode, mb_strlen($shortcode)-1, 1) !== ']')
                throw new Exception('shortcode ' . str($shortcode) . ' is malformed');
            if(!is_array($specification) or !isset($specification['description']) or !isset($specification['example']))
                throw new Exception('specification of shortcode ' . $shortcode . ' is malformed');
        }

        $this->template = $template;
        $this->shortcodes = $shortcodes;

    }

        /**
         * Get all the shortcodes
         *
         * @since 0.3.0+
         * @access public
         * @return array The array of all shortcodes.
         */
    public function get_shortcodes() {

        return array_keys($this->shortcodes);
    }

        /**
         * Return the expanded version of the template
         *
         * @since 0.3.0+
         * @access public
         * @param array $replacements Array of strings with which to replace the
         *                            shortcodes. Must be either an array of the
         *                            same length as $shortcodes or have a subset
         *                            of shortcodes as array keys and their
         *                            replacements as values.
         * @param boolean $error_if_not_all_specified Throw an exception if
         *                                            template contains
         *                                            shortcodes for which no
         *                                            value was provided
         *                                            (optional).
         * @return string The expanded template.
         */
    public function expanded( $replacements, $error_if_not_all_specified=false ) {

        if(!is_array($replacements))
            throw new Exception('Argument $replacements must be an array.');

        if(!is_assoc($replacements))
        {
            if(count($replacements) !== count($this->shortcodes))
                throw new Exception('Argument $replacements must be an associative array or have the same number of entries as there are shortcodes,');
            $result = str_replace(array_keys($this->shortcodes), $replacements, $this->template);
        }
        else
        {
            $result = $this->template;
            foreach($this->shortcodes as $shortcode => $specification)
                if($error_if_not_all_specified and !array_key_exists($shortcode, $replacements) and strpos($this->template, $shortcode) !== false))
                    throw new Exception('No value was provided for the shortcodee ' . $shortcode . ' but it appears in the template.');

                $result = str_replace($shortcode, $replacements[$shortcode], $result);

        }

        return $result;
    }


        /**
         * Return the template with short codes replaced by their example values
         *
         * @since 0.3.0+
         * @access public
         * @return string The expanded template based on the examples.
         */
    public function example_expanded() {

        $result = $this->template;
        foreach($this->shortcodes as $shortcode => $specification)
            $result = str_replace($shortcode, $specification['example']], $result);

        return $result;
    }




}
