<?php

/**
 * Class representing the shortcodes generating various lists of people
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.4.1
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

/**
 * Class representing the shortcodes generating various lists of people
 *
 * @since      0.4.1
 * @package    O3PO
 * @subpackage O3PO/includes
 */
class O3PO_PeopleShortcodes implements O3PO_SettingsSpecifyer {

        /**
         * Specifies class specific settings sections and fields.
         *
         * To be called from O3PO_Settings::configure().
         *
         * @since  0.4.0
         * @access public
         * @param  O3PO_Settings $settings Settings object.
         */
    public static function specify_settings( $settings ) {

        $settings->specify_section('people_shortcode_settings', 'People', array('O3PO_PeopleShortcodes', 'render_people_shortcode_settings'), 'people_shortcode_settings'); # We render everything here as part of the section and set the render callable of the fields to Null
        $settings->specify_field('editor_first_names', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('editor_last_names', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('editor_since_year', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_years'), array(date('Y')));
        $settings->specify_field('editor_url', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_urls'), array(''));
        $settings->specify_field('editor_affiliation', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('editor_country', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('editor_coordinator', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_checked_or_unchecked'), array('unchecked'));
        $settings->specify_field('editor_retired', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_PeopleShortcodes', 'validate_array_of_at_most_1000_checked_or_unchecked'), array('unchecked'));

    }

        /**
         * Render the editor data field(s)
         *
         * @since    0.4.1
         * @access   public
         */
    public static function render_people_shortcode_settings() {

        $settings = O3PO_Settings::instance();
        $editor_first_names = $settings->get_field_value('editor_first_names');
        $slug = 'people-shortcodes';

        echo '<p>You can use the following shortcodes to generate various lists of editors from the data below anywhere in WordPress:</p>';
        echo '<ul>';
        echo '<li>[editors-ul] unordered list of all editors</li>';
        echo '<li>...</li>';
        echo '</ul>';
        echo '<div id="' . $slug . '-editor-list">';
        foreach($editor_first_names as $x => $foo)
        {
            echo '<div class="' . $slug . ' ' . $slug . '-editor">';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('editor_first_names[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'First and middle name(s)', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('editor_last_names[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Last name(s)', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('editor_since_year[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Since year', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('editor_url[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Last name(s)', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('editor_affiliation[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Affiliation', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('editor_country[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Country', true, 'display:block;');
            echo '</div>';


            echo '<div style="float:left;">';
            $settings->render_checkbox_field('editor_coordinator[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Coordinator', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_checkbox_field('editor_retired[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Retired', true, 'display:block;');
            echo '</div>';

            echo '</div>';
        }
        echo '</div>';
        echo '<script>
        function addEditor() {
            var item = document.getElementById("' . $slug . '-editor-list").lastElementChild;
            var clone = item.cloneNode(true);
            var editorNumber = parseInt(RegExp("\\\\[([0-9]*)\\\\]$").exec(clone.getElementsByTagName("input")[0].name)[1]) + 1;
            var inputs = clone.getElementsByTagName("input");
            for (i = 0; i < inputs.length; i++) {
              inputs[i].value = "";

              inputs[i].name = inputs[i].name.replace(RegExp("\[[0-9]*\]$"), "["+editorNumber+"]");
              inputs[i].id = inputs[i].id.replace(RegExp("\[[0-9]*\]$"), "["+editorNumber+"]");
            }
            var labels = clone.getElementsByTagName("label")
            for (i = 0; i < labels.length; i++) {
              labels[i].setAttribute("for", labels[i].getAttribute("for").replace(RegExp("\[[0-9]*\]$"), "["+editorNumber+"]"));
            }
            var selects = clone.getElementsByTagName("select")
            for (i = 0; i < selects.length; i++) {
              selects[i].name = selects[i].name.replace(RegExp("\[[0-9]*\]$"), "["+editorNumber+"]");
              selects[i].id = selects[i].id.replace(RegExp("\[[0-9]*\]$"), "["+editorNumber+"]");
              selects[i].selectedIndex = 0;
            }
            document.getElementById("' . $slug . '-editor-list").appendChild(clone);
        }
        function removeEditor() {
            var select = document.getElementById("' . $slug . '-editor-list");
            if(select.childElementCount > 1) {
                select.removeChild(select.lastElementChild);
            }
        }
        </script>';
        echo '<p><button type="button" onclick="addEditor()">Add editor</button>';
        echo '<button type="button" onclick="removeEditor()">Remove last editor</button></p>';

    }

        /**
         *
         * To be added as a shortcode via add_shortcode()
         *
         */
    public static function editors_ul_shortcode( $atts, $content, $tag ) {

        return 'foo';
    }

    public static function add_shortcodes() {

        add_shortcode('editors-ul', array('O3PO_PeopleShortcodes', 'editors_ul_shortcode'));

    }
}
