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
        $settings->specify_field('person_first_names', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('person_last_names', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('person_role', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_editor_coordinator_or_steering_board'), array('editor'));
        $settings->specify_field('person_since_year', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_years'), array(''));
        $settings->specify_field('person_until_year', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_years'), array(''));
        $settings->specify_field('person_url', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_urls'), array(''));
        $settings->specify_field('person_affiliation', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('person_country', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_names'), array(''));


    }

        /**
         * Render the person data field(s)
         *
         * @since    0.4.1
         * @access   public
         */
    public static function render_people_shortcode_settings() {

        $settings = O3PO_Settings::instance();
        $person_first_names = $settings->get_field_value('person_first_names');
        $slug = 'people-shortcodes';

        echo '<p>You can use the following shortcodes to generate various lists of persons from the data below anywhere in WordPress:</p>';
        echo '<ul>';
        echo '<li>[persons-ul] unordered list of all persons</li>';
        echo '<li>...</li>';
        echo '</ul>';
        echo '<h3>Persons</h3>';
        echo '<div id="' . $slug . '-person-list">';
        foreach($person_first_names as $x => $foo)
        {
            echo '<div class="' . $slug . ' ' . $slug . '-person">';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_first_names[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'First and middle name(s)', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_last_names[' . $x . ']', '', 'on', 'width:20em;max-width:100%;', 'Last name(s)', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_select_field('person_role[' . $x . ']', array(
                                               array('value' => 'editor', 'description' => 'Editor'),
                                               array('value' => 'coordinator', 'description' => 'Coordinator'),
                                               array('value' => 'steering board', 'description' => 'Steering Board')
                                                                            ),
                                           false,
                                           'Role'
                                           );
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_since_year[' . $x . ']', '', 'on', 'width:5em;max-width:100%;', 'Since year', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_until_year[' . $x . ']', '', 'on', 'width:5em;max-width:100%;', 'Until year', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_url[' . $x . ']', '', 'on', 'width:35em;max-width:100%;', 'URL', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_affiliation[' . $x . ']', '', 'on', 'width:40em;max-width:100%;', 'Affiliation', true, 'display:block;');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_country[' . $x . ']', '', 'on', 'width:15em;max-width:100%;', 'Country', true, 'display:block;');
            echo '</div>';

            echo '<button style="float:left;" type="button" onclick="removePerson(this)">Remove person</button>';

            echo '<div style="clear:both"></div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<script>
        function addPerson() {
            var item = document.getElementById("' . $slug . '-person-list").lastElementChild;
            var clone = item.cloneNode(true);
            var personNumber = parseInt(RegExp("\\\\[([0-9]*)\\\\]$").exec(clone.getElementsByTagName("input")[0].name)[1]) + 1;
            var inputs = clone.getElementsByTagName("input");
            for (i = 0; i < inputs.length; i++) {
              inputs[i].value = "";

              inputs[i].name = inputs[i].name.replace(RegExp("\[[0-9]*\]$"), "["+personNumber+"]");
              inputs[i].id = inputs[i].id.replace(RegExp("\[[0-9]*\]$"), "["+personNumber+"]");
            }
            var labels = clone.getElementsByTagName("label")
            for (i = 0; i < labels.length; i++) {
              labels[i].setAttribute("for", labels[i].getAttribute("for").replace(RegExp("\[[0-9]*\]$"), "["+personNumber+"]"));
            }
            var selects = clone.getElementsByTagName("select")
            for (i = 0; i < selects.length; i++) {
              selects[i].name = selects[i].name.replace(RegExp("\[[0-9]*\]$"), "["+personNumber+"]");
              selects[i].id = selects[i].id.replace(RegExp("\[[0-9]*\]$"), "["+personNumber+"]");
              selects[i].selectedIndex = 0;
            }
            document.getElementById("' . $slug . '-person-list").appendChild(clone);
        }
        function removePerson(elem) {
            var select = document.getElementById("' . $slug . '-person-list");
            if(select.childElementCount > 1) {
                elem.parentElement.remove();
            }
        }
        function removeLastPerson() {
            var select = document.getElementById("' . $slug . '-person-list");
            if(select.childElementCount > 1) {
                select.removeChild(select.lastElementChild);
            }
        }
        </script>';
        echo '<div>';
        echo '<p><button type="button" onclick="addPerson()">Add person</button>';
        echo '<button type="button" onclick="removeLastPerson()">Remove last person</button></p>';
        echo '</div>';
    }


    public static function sort_by_last_names($person_a, $person_b) {

        return strnatcmp($person_a['last_names'], $person_b['last_names']);
    }

        /**
         *
         * To be added as a shortcode via add_shortcode()
         *
         */
    public static function persons_ul_shortcode( $atts, $content, $tag ) {

        $settings = O3PO_Settings::instance();
        $person_first_names = $settings->get_field_value('person_first_names');
        $person_last_names = $settings->get_field_value('person_last_names');
        $person_role = $settings->get_field_value('person_role');
        $person_since_year = $settings->get_field_value('person_since_year');
        $person_until_year = $settings->get_field_value('person_until_year');
        $person_url = $settings->get_field_value('person_url');
        $person_affiliation = $settings->get_field_value('person_affiliation');
        $person_country = $settings->get_field_value('person_country');

        $person_data = array();
        foreach($person_first_names as $x => $foo)
            $person_data[] = array(
                'first_names' => $person_first_names[$x],
                'last_names' => $person_last_names[$x],
                'role' => $person_role[$x],
                'since_year' => $person_since_year[$x],
                'until_year' => $person_until_year[$x],
                'url' => $person_url[$x],
                'affiliation' => $person_affiliation[$x],
                'country' => $person_country[$x],
                                   );

        uasort($person_data, array('self', 'sort_by_last_names'));

        $result = '<ul>';
        foreach($person_data as $x => $person)
        {
            if(empty($atts['role']) or $atts['role'] === $person['role'] or in_array($person['role'], preg_split('/\s*,\s*/', $atts['role'])))
            {
                $result .= '<li>';
                $person_name = $person['first_names'] . ' ' . $person['last_names'];
                if(!empty($atts['link']) and !empty($person['url']))
                    $result .= '<a href="' . esc_attr($person['url']) . '" target="_blank">' . esc_html($person_name) . '</a>';
                else
                    $result .= esc_html($person_name);
                if(!empty($atts['affiliation']) and !empty($person['affiliation']))
                    $result .= ', ' . esc_html($person['affiliation']);
                if(!empty($atts['country']) and !empty($person['country']))
                    $result .= ', ' . esc_html($person['country']);
                if(!empty($atts['date']))
                {
                    $current_year = date('Y');
                    $result .= ' (' . ( $current_year >= $person['since_year'] ? 'since' : 'starting in') . ' ' . esc_html($person['since_year']) . ')';
                }
                $result .= '</li>';
            }
        }
        $result .= '</ul>';

        return $result;
    }

    public static function add_shortcodes() {

        add_shortcode('persons-ul', array('O3PO_PeopleShortcodes', 'persons_ul_shortcode'));

    }
}
