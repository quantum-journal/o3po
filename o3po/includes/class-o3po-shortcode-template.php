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

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';

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
    private $template;


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
         * Get all the descriptions
         *
         * @since 0.3.0+
         * @access public
         * @return array Associative array of shortcode descriptions.
         */
    public function get_shortcode_descriptions() {

        $descriptions = array();
        foreach($this->shortcodes as $shortcode => $specification)
            $descriptions[$shortcode] = $specification['description'];

        return $descriptions;
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
         * @param boolean $error_if_not_all_appearing_specified Throw an exception if
         *                                            template contains
         *                                            shortcodes for which no
         *                                            value was provided
         *                                            (default true).
         * @return string The expanded template.
         */
    public function expand( $replacements, $error_if_not_all_appearing_specified=true ) {

        if(!is_array($replacements))
            throw new Exception('Argument $replacements must be an array.');

        if(!O3PO_Utility::is_assoc($replacements))
        {
            if(count($replacements) !== count($this->shortcodes))
                throw new Exception('Argument $replacements must be an associative array or have the same number of entries as there are shortcodes,');
            $result = str_replace(array_keys($this->shortcodes), $replacements, $this->template);
        }
        else
        {
            $result = $this->template;
            foreach($this->shortcodes as $shortcode => $specification)
            {
                if($error_if_not_all_appearing_specified and !array_key_exists($shortcode, $replacements) and strpos($this->template, $shortcode) !== false)
                    throw new Exception('No value was provided for the shortcode ' . $shortcode . ' but it appears in the template.');

                $result = str_replace($shortcode, $replacements[$shortcode], $result);
            }
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
    public function example_expand() {

        $result = $this->template;
        foreach($this->shortcodes as $shortcode => $specification)
            $result = str_replace($shortcode, $specification['example'], $result);

        return $result;
    }


        /**
         *
         *
         */
    public function render_short_codes() {

        $result = '<p>You may use the following shortcodes:</p>';
        $result .= '<ul>';
        foreach($this->get_shortcode_descriptions() as $short_code => $description) {
            $result .= '<li>' . esc_html($short_code) . ': ' . esc_html($description) . '</li>';
        }
        $result .= '</ul>';

        return $result;
    }


}
