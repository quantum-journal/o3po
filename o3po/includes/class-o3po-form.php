<?php

/**
 * Class for forms.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.4.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Form for forms.
 *
 * @since      0.4.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Form {


        /**
         * Add a section to this form.
         *
         * Insipred by Wordpress' add_settings_section()
         *
         * @since  0.4.0
         * @access protected
         * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the section. Shown as the heading for the section.
         * @param callable $callback Function that echos out any content at the top of the section (between heading and fields).
         */
    protected function add_section( $id, $title, $callback ) {

    }


        /**
         * Add a field to this form.
         *
         * Insired by Wordpress' add_settings_field()
         *
         * @since  0.4.0
         * @access protected
         * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the section. Shown as the heading for the section.
         * @param callable $callback Function that echos out any content at the top of the section (between heading and fields).
         * @param string   $section  The slug-name of the section in which to show the field.
         * }
         */
    protected function add_field($id, $title, $callback, $page, $section ) {

    }

        /**
         * Validate field.
         *
         * @since    0.4.0
         * @access   protected
         * @param    array    $input    Array of values of the fields to validate.
         */
    protected function validate_input( $input ) {

        $newinput = array();
        foreach($this->get_all_fields_map() as $field => $validation_method)
        {
            if(isset($input[$field]) and $validation_method !== null)
                $newinput[$field] = $this->$validation_method($field, $input[$field]);
            else
                $newinput[$field] = ...;
        }

        return $newinput;
    }


        /**
         * Leave user input unchanged.
         *
         * @since    0.4.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function accept_as_is( $field, $input ) {

        return $input;
    }


        /**
         * Trim user input
         *
         * @since    0.4.0
         * @access   private
         * @param    string   $field    The field this was input to.
         * @param    string   $input    User input.
         */
    public function trim( $field, $input ) {

        return trim($input);
    }

}
