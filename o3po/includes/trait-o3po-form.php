<?php

/**
 * Trait for forms.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.1+
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Trait for forms such as the settings form.
 *
 * @since      0.3.1+
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
trait O3PO_Form {

        /**
         * The unique identifier of this plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $plugin_name    The string used to uniquely identify this plugin.
         */
	protected $plugin_name;

        /**
         * Slug of this form.
         *
         * Used for css classes when rendering the sections and fields.
         * Used then storing/retreiving data from the database.
         *
         * @since 0.3.1+
         * @access protected
         * @var string $slug Slug of the form.
         */
    protected $slug;

        /**
         * Array of all pages of the form.
         *
         * @since    0.3.1+
         * @access   protected
         * @var      array     $sections   Dictionary of all pages and their properties.
         */
	protected $pages = array();

        /**
         * Array of all sections of the form.
         *
         * @since    0.3.1+
         * @access   protected
         * @var      array     $sections   Dictionary of all sections and their properties.
         */
	protected $sections = array();

        /**
         * Array of all field of the form.
         *
         * @since    0.3.1+
         * @access   protected
         * @var      array     $fields    Dictionary of all fields and their properties.
         */
	protected $fields = array();

        /**
         * Specify a section of the form.
         *
         * Keeps a record of all sections in $this->sections.
         *
         * @since  0.3.1+
         * @access private
         * @param string   $id       Slug-name to identify the page.
         * @param string   $title    Formatted title of the section. Shown as the heading for the section.
         * @param callable $callback Function that echos out the any content at the top of the page (before the first section).
         */
    public function specify_page( $id, $title, $callback=null ) {

        $this->pages[$id] = array(
            'title' => $title,
            'callback' => $callback,
                                  );

    }

        /**
         * Specify a section of the form.
         *
         * Keeps a record of all sections in $this->sections.
         *
         * @since  0.3.1+
         * @access private
         * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the section. Shown as the heading for the section.
         * @param callable $callback Function that echos out any content at the top of the section (between heading and fields).
         * @param string   $page     The slug-name of the page on which to show the section.
         */
    public function specify_section( $id, $title, $callback, $page, $summary_callback=null ) {

        $this->sections[$id] = array('title' => $title, 'callback' => $callback, 'page' => $this->plugin_name . '-' . $this->slug . ':' . $page, 'summary_callback' => $summary_callback);

    }


       /**
         * Specify a field of the form.
         *
         * Keeps a record of all fields in $this->fields.
         *
         * @since  0.3.1+
         * @access private
         * @param string   $id       Slug-name to identify the field. Used in the 'id' attribute of tags.
         * @param string   $title    Formatted title of the field. Shown as the heading for the field.
         * @param callable $callback Function that echos out any content at the top of the field (between heading and fields).
         * @param string   $page     The slug-name of the page on which to show the field.
         * @param string   $section     The slug-name of the section in which to show the field.
         * @param string   $field  The slug-name of the field of the page
         *                           in which to show the box.
         * @param array    $args {
         *     Extra arguments used when outputting the field. May be an empty array().
         *
         *     @type string $label_for When supplied, the label will be wrapped
         *                             in a `<label>` element, its `for` attribute populated
         *                             with this value.
         *     @type string $class     CSS Class to be added to the `<tr>` element when the
         *                             field is output.
         * }
         * @param callable $validation_callable Callable to use during validation of inputs.
         *                                      Must take a field ID and input as parameters
         *                                      and return a valid value for the field. Should
         *                                      call $this->add_error() to indicate problems.
         * @param string   $default  Default value for the field.
         */
    public function specify_field($id, $title, $callback, $page, $section, $args, $validation_callable, $default, $max_length=false ) {

        if(!isset($this->sections[$section]))
            throw new Exception('Cannot add field ' . $id . ' to non-existing section ' . $section . '.');

        $this->fields[$id] = array(
            'title' => $title,
            'callback' => $callback,
            'page' => $this->plugin_name . '-' . $this->slug . ':' . $page,
            'section' => $section,
            'args' => $args,
            'validation_callable' => $validation_callable,
            'default' => $default,
            'max_length' => $max_length);
    }

        /**
         * Record errors during input verification.
         *
         * The O3PO_Settings class for example implements this as just a
         * wrappter around add_settings_error().
         *
         * Implementations must adhere to the following parameter specification:
         *
         * @param string $setting Slug title of the setting to which this error applies.
         * @param string $code Slug-name to identify the error. Used as part of 'id' attribute in HTML output.
         * @param string $message The formatted message text to display to the user (will be shown inside styled <div> and <p> tags).
         * @param $type Message type, controls HTML class. Possible values include 'error', 'success', 'warning', 'info'. Default value: 'error'
         */
    abstract protected function add_error( $setting, $code, $message, $type='error' );

        /**
         * Render a standard text box type field.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id   Id of the field.
         */
    public function render_single_line_field( $id , $placeholder=null, $autocomplete='on', $style=false, $label=false, $esc_label=true, $label_style=false) {

        $value = $this->get_field_value($id);

        if(preg_match('#(.*)\[(.*)\]#u', $id, $matches) === 1)
            $name_end = '[' . $matches[1] . '][' . $matches[2] . ']';
        else
            $name_end = '[' . $id . ']';
        echo '<input class="regular-text ltr ' . $this->plugin_name . '-' . $this->slug . ' ' . $this->plugin_name . '-' . $this->slug . '-text" type="text" id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . $name_end . '" value="' . esc_attr($value) . '"' . ($placeholder ? ' placeholder="' . esc_attr($placeholder) . '"' : '' ) . ($style ? ' style="' . esc_attr($style). '" ': '') . ($autocomplete === 'off' ? ' autocomplete=off': '') .' />';
        if(!empty($label))
            echo '<label for="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '"' . ($label_style ? ' style="' . esc_attr($label_style). '" ': '') . '>' . ($esc_label ? esc_html($label) : $label) . '</label>';

    }

        /**
         * Render a multi line text box type field.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id   Id of the field.
         */
    public function render_multi_line_field( $id, $rows=false, $style=false, $preview=false, $placeholder=false ) {

        $value = $this->get_field_value($id);
        echo '<textarea class="regular-text ltr o3po-' . $this->slug . ' o3po-' . $this->slug . '-text-multi-line" ' . ($style ? 'style="' . esc_attr($style). '" ': '') . ($preview ? ' oninput=UpdatePreview(this.id)': '') . ($rows ? ' rows="' . esc_attr($rows). '" ': ' ') . 'id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . '[' . $id . ']" rows="' . (mb_substr_count( $value, "\n" )+1) . '"' . ($placeholder !== false ? ' placeholder="' . esc_attr($placeholder) . '"' : '') . '>' . esc_textarea($value) . '</textarea>';
        if($preview === true)
        {
            echo'<script>
function UpdatePreview(id) {
var source = document.getElementById(id);
var target = document.getElementById(id + "-preview");
//var label = document.getElementById(id + "-preview-lable");
target.textContent = source.value;
MathJax.Hub.Queue(["Typeset", MathJax.Hub, target]);
}
</script>';
            echo '<p><span id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '-preview-lable" stype="">LaTeX preview:</span><br/>';
            echo '<span id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '-preview" style="white-space:pre-wrap;">' . (!empty($value) ? esc_html($value) : 'Nothing to show') . '</span></p>';
        }
    }

        /**
         * Render a password field.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $id   Id of the field.
         */
    public function render_password_field( $id ) {

        $value = $this->get_field_value($id);

        echo '<input class="regular-text ltr o3po-' . $this->slug . ' o3po-' . $this->slug . '-password" type="password" id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . '[' . $id . ']" value="' . esc_attr($value) . '" />';
        echo '<input type="checkbox" onclick="(function myFunction() {
    var x = document.getElementById(\'' . $this->plugin_name . '-' . $this->slug . '-' . $id . '\');
    if (x.type === \'password\') {
        x.type = \'text\';
    } else {
        x.type = \'password\';
    }
})();">Show Password';
    }

        /**
         * Render a checkbox type field.
         *
         * @since    0.1.0
         * @access   public
         * @param    string   $id    Id of the field.
         * @param    string   $label Label of the field.
         * @param    boolean  $esc_label Whether to escape the content of $label, disable in case you want $label to contains htmls
         */
    public function render_checkbox_field( $id, $label='', $esc_label=true) {

        $value = $this->get_field_value($id);

        echo '<input type="hidden" name="' . $this->plugin_name . '-' . $this->slug . '[' . $id . ']" value="unchecked">'; //To have a 0 in POST when the checkbox is unticked
        echo '<input class="o3po-' . $this->slug . ' o3po-' . $this->slug . '-checkbox" type="checkbox" id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . '[' . $id . ']" value="checked"' . checked( 'checked', $value, false ) . '/>';
        if(!empty($label))
            echo '<label for="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '">' . ($esc_label ? esc_html($label) : $label) . '</label>';

    }


    public function render_select_field( $id, $options, $onchange=false) {

        $value = $this->get_field_value($id);

        if(preg_match('#(.*)\[(.*)\]#u', $id, $matches) === 1)
            $name_end = '[' . $matches[1] . '][' . $matches[2] . ']';
        else
            $name_end = '[' . $id . ']';

        echo '<select class="o3po-' . $this->slug . ' o3po-' . $this->slug . '-select" id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . $name_end . '"' . ($onchange !== false ? ' onchange="' . $onchange . '"' : '') . '>';
        foreach($options as $option)
            echo '<option value="' . $option['value'] . '"' . ($value == $option['value'] ? 'selected': '' ) . '>' . $option['description'] . '</option>';
        echo '</select>';
    }

        /**
         * Render an array as comma separated list type field.
         *
         * Does not escape or otherwise handle individual fields that contain commas.
         *
         * @since    0.3.0
         * @access   public
         * @param    string    $id   Id of the field.
         */
    public function render_array_as_comma_separated_list_field( $id, $placeholder=null ) {

        $value = $this->get_field_value($id);
        if(!is_array($value))
            $value = array();

        echo '<input class="regular-text ltr o3po-' . $this->slug . ' o3po-' . $this->slug . '-text" type="text" id="' . $this->plugin_name . '-' . $this->slug . '-' . $id . '" name="' . $this->plugin_name . '-' . $this->slug . '[' . $id . ']" value="' . esc_attr(implode($value, ',')) . '"' . ($placeholder ? ' placeholder="' . esc_attr($placeholder) . '"' : '' ) . ' />';

    }

        /**
         * Get the default value of a field by id.
         *
         * @since    0.3.1+
         * @acceess  public
         * @param    int    $id     Id of the field.
         */
    public function get_field_default( $id ) {

        if(isset($this->fields[$id]) and isset($this->fields[$id]['default']))
            return $this->fields[$id]['default'];

        throw new Exception('Field '. $id . ' is not known or has no default value.');
    }

        /**
         * Get the default value of all fields.
         *
         *
         * @since    0.3.1+
         * @acceess  public
         * @param boolean $include_fake_fields Whether to also include fake fields in the list.
         */
    public function get_field_defaults( $include_fake_fields=false ) {

        $return = array();
        foreach($this->fields as $id => $specification)
            if($include_fake_fields or isset($specification['title'])) # fake fields do not have titles
                $return[$id] = $specification['default'];

        return $return;
    }

       /**
         * Get the title of a field by id.
         *
         * @since    0.3.1+
         * @acceess  prublic
         * @param    int    $id     Id of the field.
         */
    public function get_field_title( $id ) {

        if(isset($this->fields[$id]) and isset($this->fields[$id]['title']))
            return $this->fields[$id]['title'];

        throw new Exception('Field '. $id . ' has no title.');
    }


        /**
         * Add a fake field
         *
         * Fake fields cannot be modified, but have default values that
         * can be used to avoid hard coding values.
         *
         * @since  0.3.1+
         * @access private
         * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
         * @param string   $default  Default value for the field.
         */
    public function specify_fake_field( $id, $default ) {

        $this->fields[$id] = array(
            'default' => $default,
            'max_length' => false,
                                   );

    }

        /**
         * Clean user input to doi_prefix fields
         *
         * @since    0.3.1+
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input User input.
         */
    public function validate_doi_prefix( $id, $input ) {

        $doi_prefix = trim($input);
        if(preg_match('/^[0-9.-]*$/u', $doi_prefix))
            return $doi_prefix;

        $this->add_error($id, 'illegal-doi-prefix', "The DOI prefix '" . $input ."' given in '" . $this->fields[$id]['title'] . "' may consist only of numbers 0-9, dot . and the dash - character. Field reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Clean user input to doi_suffix fields
         *
         * @since    0.3.1+
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input User input.
         */
    public function validate_doi_suffix( $id, $input ) {

        $doi_suffix = trim($input);
        if(preg_match('/^[a-zA-Z0-9.-]*$/u', $doi_suffix))
            return $doi_suffix;

        $this->add_error($id, 'illegal-doi-suffix', "The DOI suffix '" . $input ."' given in '" . $this->fields[$id]['title'] . "' may consist only of lower and upper case English alphabet letters a-z and A-Z, numbers 0-9, dot . and the dash - character. Field reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Clean user input to fields expecting a year.
         *
         * @since    0.3.1
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input User input.
         */
    public function validate_four_digit_year( $id, $input ) {

        $first_volume_year = trim($input);
        if(preg_match('/^[0-9]{4}$/u', $first_volume_year)) //this will cause a year 10000 bug and rejects years pre 1000
            return $first_volume_year;

        $this->add_error($id, 'illegal-year', "The year '" . $input ."' given in '" . $this->fields[$id]['title'] . "' must consist of exactly four digits in the range 0-9. Field reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Clean user input to eprint fields
         *
         * @since    0.3.1+
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         * @return string Valid issn or empty string.
         */
    public function validate_eprint( $id, $input ) {

        $eprint = trim($input);
        if(preg_match('#^([-.a-zA-Z]+/[0-9]{7}|[0-9]{4}.[0-9]{4,5})v[0-9]+$#u', $eprint))
            return $eprint;

        $this->add_error($id, 'malformed-eprint', "The arXiv identifier '" . $input ."' given in '" . $this->fields[$id]['title'] . "' is not valid.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Clean user input to issn fields
         *
         * @since    0.3.1+
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         * @return string Valid issn or empty string.
         */
    public function validate_issn_or_empty( $id, $input ) {

        if(empty(trim($input)))
            return '';
        else
            return $this->validate_issn($id, $input);
    }

       /**
         * Clean user input to issn fields
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_issn( $id, $input ) {

        $trimmed_input = trim($input);

        if(O3PO_Utility::valid_issn($trimmed_input))
            return $trimmed_input;

        $this->add_error($id, 'invalid-issn', "The ISSN '" . $input ."' given in '" . $this->fields[$id]['title'] . "' is invalid. Field reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Clean user input to email fields
         *
         * @since    0.3.1+
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_email( $id, $input ) {

        $input_trimmed = trim($input);
        if(O3PO_Utility::valid_email($input_trimmed))
            return $input_trimmed;

        $this->add_error($id, 'invalid-email', "The input '" . $input . "' to '" . $this->fields[$id]['title'] . "' was not a valid email address. Field reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Clean user input to url fields
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_url( $id, $input ) {

        $input_trimmed = trim($input);
        $url = esc_url_raw(strip_tags(stripslashes($input_trimmed)));

        $parsed = parse_url($url);
        if(empty($parsed['scheme']) or empty($parsed['host']))
        {
            $this->add_error($id, 'url-validated', "The URL '" . $input . "' given in '" . $this->fields[$id]['title'] . "' was malformed. Field reset.", 'error');
            return $this->get_field_default($id);
        }
        elseif($url !== $input)
        {
            $this->add_error($id, 'url-validated', "The URL '" . $input . "' given in '" . $this->fields[$id]['title'] . "' was malformed or contained special or illegal characters, which were removed or escaped. Please check.", 'updated');
            return $url;
        }

        return $url;
    }

        /**
         * Validate input to comma separated list fields.
         *
         * Break a comma separated list into an array of fields.
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_array_as_comma_separated_list( $id, $input ) {

        try
        {
            $input = trim($input);
            $array = preg_split('#,#u', $input, Null, PREG_SPLIT_NO_EMPTY);
            foreach($array as $key => $id)
                $array[$key] = trim($id);

            return $array;
        }
        catch (Exception $e) {
            $this->add_error($id, 'not-comma-separated-list', "The input to '" . $this->fields[$id]['title'] . "' could not be interpreted as a comma separated list. Field reset.", 'error');
            return $this->get_field_default($id);
        }
    }

        /**
         * Validate two letter country code fields
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_two_letter_country_code( $id, $input ) {

        $input = trim($input);
        if(preg_match('/^[A-Z]{2}$/u', $input))
            return $input;

        $this->add_error($id, 'url-validated', "The two letter country code '" . $input . "' given in '" . $this->fields[$id]['title'] . "' was malformed. Field reset.", 'error');
        return $this->get_field_default($id);
    }


        /**
         * Validate positive integer fields
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_positive_integer( $id, $input ) {

        $input = trim($input);
        if(preg_match('/^[1-9][0-9]*$/u', $input))
            return $input;

        $this->add_error($id, 'not-a-positive-integer', "The input '" . $input . "' given in '" . $this->fields[$id]['title'] . "' was not a positive integer without leading zeros. Field reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Validate non-negative euro fields
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function validate_non_negative_euros( $id, $input ) {

        $input = trim($input);
        if(preg_match('/^[0-9][0-9]*(\.[0-9]*|)€$/u', $input))
            return $input;

        $this->add_error($id, 'not-a-non-negative-euro', "The input '" . $input . "' given in '" . $this->fields[$id]['title'] . "' was not a non-negative amount of euros. Field reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Restrict input to checked or unchecked fields
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function checked_or_unchecked( $id, $input ) {

        if($input === "checked" or $input === "unchecked")
            return $input;

        $this->add_error($id, 'not-checked-or-unchecked', "The box '" . $this->fields[$id]['title'] . "' must be either checked or unchecked. Box reset.", 'error');
        return $this->get_field_default($id);
    }

        /**
         * Ensure that a check box field is checked
         *
         * @sinde    0.3.1+
         * @access   public
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function checked( $id, $input ) {

        if($input === "checked")
            return $input;

        $this->add_error($id, 'not-checked', "The box '" . $this->fields[$id]['title'] . "' must be checked.", 'error');

        return $this->get_field_default($id);
    }

        /**
         * Trim user input to field
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function trim( $id, $input ) {

        return trim($input);
    }

        /**
         * Trim user input to field
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function trim_strip_tags( $id, $input ) {

        $input = trim($input);
        $trimmed_striped_input = strip_tags($input);
        if($trimmed_striped_input !== $input)
            $this->add_error($id, 'tags-stripped', "The field '" . $this->fields[$id]['title'] . "' cannot contain html tags. Tags were removed.", 'error');

        return $trimmed_striped_input;
    }

        /**
         * Leave user input to field unchanged.
         *
         * @since    0.3.0
         * @access   private
         * @param    string   $id    The field this was input to.
         * @param    string   $input    User input.
         */
    public function leave_unchanged( $id, $input ) {

        return $input;
    }

        /**
         * Get the value of a field by id.
         *
         * @since    0.3.1+
         * @acceess  prublic
         * @param    int    $id     Id of the field.
         */
    abstract public function get_field_value( $id );

}
