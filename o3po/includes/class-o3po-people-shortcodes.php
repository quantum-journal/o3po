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

    public static $shortcode_descriptions = array(
        'persons-ul' => "An itemized (i.e. not numbered but unordered) list of people.",
        'persons-count' => "The number of people matching the given criteria.",
                                                  );

    public static $shortcode_atts = array(
        'persons-ul' => array(
            'sort' => array(
                'default' => 'last_names',
                'allowed' => ['last_names', 'first_names', 'False'],
                'description' => 'Whether to sort and according to what.',
                            ),
            'role' => array(
                'default' => '',
                'allowed' => ['', 'editor', 'coordinator', 'steering board', 'admin', 'executive board'],
                'description' => 'If provided only persons with the given role are included. Multiple roles can be given as a comma separated list.',
                            ),
            'link' => array(
                'default' => 'True',
                'allowed' => ['True', 'False'],
                'description' => 'Whether make the name a link to the persons URL.',
                            ),
            'affiliation' => array(
                'default' => 'True',
                'allowed' => ['True', 'False'],
                'description' => 'Whether include the persons affiliation.',
                                   ),
            'country' => array(
                'default' => 'True',
                'allowed' => ['True', 'False'],
                'description' => 'Whether include the persons country.',
                               ),
            'date' => array(
                'default' => 'True',
                'allowed' => ['True', 'False'],
                'description' => 'Whether include the date(s) the persons joined or and/or left their position.',
                            ),
            'former' => array(
                'default' => 'False',
                'allowed' => ['False', 'True', 'Only'],
                'description' => 'Whether to include (or only include) people who have left their role before the current year.',
                            ),
            'extra' => array(
                'default' => 'True',
                'allowed' => ['False', 'True'],
                'description' => 'Whether to print the persons extra in brackets.',
                            ),
            'li-style' => array(
                'default' => '',
                'allowed' => ['', 'css'],
                'description' => 'Style to apply to every li element.',
                            ),
                              ),
        'persons-count' => array(
            'role' => array(
                'default' => '',
                'allowed' => ['', 'editor', 'coordinator', 'steering board', 'admin', 'executive board'],
                'description' => 'If provided only persons with the given role are included. Multiple roles can be given as a comma separated list.',
                            ),
            'former' => array(
                'default' => 'False',
                'allowed' => ['False', 'True', 'Only'],
                'description' => 'Whether to include (or only include) people who have left their role before the current year.',
                              ),
                                 ),
                                          );

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
        $settings->specify_field('person_role', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_roles'), array('editor'));
        $settings->specify_field('person_since_year', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_years_or_empty'), array(''));
        $settings->specify_field('person_until_year', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_years_or_empty'), array(''));
        $settings->specify_field('person_url', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_urls_or_empty'), array(''));
        $settings->specify_field('person_affiliation', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('person_country', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_names'), array(''));
        $settings->specify_field('person_extra', Null, Null, 'people_shortcode_settings', 'people_shortcode_settings', array(), array('O3PO_Settings', 'validate_array_of_at_most_1000_names'), array(''));


    }

        /**
         * Render the person data field(s)
         *
         * @since    0.4.1+
         * @access   public
         */
    public static function render_people_shortcode_settings() {

        $settings = O3PO_Settings::instance();
        $person_first_names = $settings->get_field_value('person_first_names');
        $slug = 'people-shortcodes';

        echo '<p>You can use the following shortcodes to output various information about the people listed below anywhere in WordPress:</p>';
        echo '<dl>';
        foreach(static::$shortcode_atts as $shortcode => $atts)
        {
            echo '<dt>[' . esc_html($shortcode) . '] ' . esc_html(static::$shortcode_descriptions[$shortcode]) . '</dt>';
            echo '<dd>With optional attributes:<dl>';
            foreach($atts as $att => $att_property)
                echo "<dt>" . esc_html($att) . "='" . esc_html(implode('|', $att_property['allowed'])) . "'</dt><dd>" . esc_html($att_property['description']) . " Default is '" . esc_html($att_property['default']) . "'</dd>";
            echo '</dl></dd>';
        }
        echo '</dl>';
        echo '<h3>' . esc_html(count($person_first_names)) . ' persons</h3>';
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
            $person_role_values = array();
            foreach(static::$shortcode_atts['persons-ul']['role']['allowed'] as $value)
                $person_role_values[] = array('value' => $value, 'description' => ucwords($value));
            $settings->render_select_field('person_role[' . $x . ']', $person_role_values, false, 'Role');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_since_year[' . $x . ']', '', 'on', 'width:5em;max-width:100%;', 'Since year', true, 'display:block;', '');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_until_year[' . $x . ']', '', 'on', 'width:5em;max-width:100%;', 'Until year', true, 'display:block;', '');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_url[' . $x . ']', '', 'on', 'width:35em;max-width:100%;', 'URL', true, 'display:block;', '');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_affiliation[' . $x . ']', '', 'on', 'width:40em;max-width:100%;', 'Affiliation', true, 'display:block;', '');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_country[' . $x . ']', '', 'on', 'width:15em;max-width:100%;', 'Country', true, 'display:block;', '');
            echo '</div>';

            echo '<div style="float:left;">';
            $settings->render_single_line_field('person_extra[' . $x . ']', '', 'on', 'width:15em;max-width:100%;', 'Extra', true, 'display:block;', '');
            echo '</div>';

            echo '<button style="float:right;" type="button" onclick="removePerson(this)">Remove person</button>';

            echo '<div style="clear:both;margin-bottom:1em;"></div>';
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

        /**
         * Compare names
         *
         * Tries to takes into account name prefixes.
         *
         * @since    0.4.1+
         * @access   public
         */
    public static function compare_names($name_a, $name_b) {
        $name_a = trim($name_a);
        $name_b = trim($name_b);

        if($name_a === $name_b)
            return 0;

        $prefix_regex = '#^([aA][pflb]|[dD]el|[dD][ea]|[dD]i|[dD]os|[dD]u|[lL]a|[lL]e|[vV]an ([dD]e|[dD]en|[dD]er|[hH]et|)|[vV]on|[zZ]u) #u';

        $name_a_without_prefix = preg_replace($prefix_regex, '', $name_a);
        $name_b_without_prefix = preg_replace($prefix_regex, '', $name_b);

        return strnatcmp($name_a_without_prefix, $name_b_without_prefix);
    }

        /**
         * Sort by last names
         *
         * @since    0.4.1+
         * @access   public
         */
    public static function sort_by_last_names($person_a, $person_b) {

        return static::compare_names($person_a['last_names'], $person_b['last_names']);
    }

        /**
         * Sort by first names
         *
         * @since    0.4.1+
         * @access   public
         */
    public static function sort_by_first_names($person_a, $person_b) {

        return static::compare_names($person_a['first_names'], $person_b['first_names']);
    }


        /**
         * Get person data from settings storage in a convenient array structure
         *
         * @since  0.4.1+
         * @access public
         * @return array Array with of arrays, one per person, containing that
         *               persons data.
         */
    public static function get_person_data() {

        $settings = O3PO_Settings::instance();
        $person_first_names = $settings->get_field_value('person_first_names');
        $person_last_names = $settings->get_field_value('person_last_names');
        $person_role = $settings->get_field_value('person_role');
        $person_since_year = $settings->get_field_value('person_since_year');
        $person_until_year = $settings->get_field_value('person_until_year');
        $person_url = $settings->get_field_value('person_url');
        $person_affiliation = $settings->get_field_value('person_affiliation');
        $person_country = $settings->get_field_value('person_country');
        $person_extra = $settings->get_field_value('person_extra');

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
                'extra' => $person_extra[$x],
                                   );

        return $person_data;
    }


        /**
         * Function generating the person-ul shotcode
         *
         * To be added as a shortcode via add_shortcode()
         *
         * @since  0.4.1+
         * @access public
         * @param  array  $atts Array of attributes
         * @param  string $content Content of the shortcode (is ignored)
         * @param  string $tag Ignored
         * @return string The html formated list
         */
    public static function persons_ul_shortcode( $atts, $content, $tag ) {

        if(empty($atts))
            $atts = array();

        foreach(static::$shortcode_atts['persons-ul'] as $key => $value)
            if(empty($atts[$key])) $atts[$key] = $value['default'];

        $person_data = static::get_person_data();

        if($atts['sort'] === 'last_names')
            uasort($person_data, array('self', 'sort_by_last_names'));
        elseif($atts['sort'] === 'first_names')
            uasort($person_data, array('self', 'sort_by_first_names'));

        $current_year = date('Y');
        $result = '<ul>';
        foreach($person_data as $x => $person)
        {
            if($atts['former'] === 'False')
                if(!empty($person['until_year']) and $current_year > $person['until_year'])
                    continue;
            if($atts['former'] === 'Only')
                if(empty($person['until_year']) or $current_year < $person['until_year'])
                    continue;

            if(empty($atts['role']) or $atts['role'] === $person['role'] or in_array($person['role'], preg_split('/\s*,\s*/u', $atts['role'])))
            {
                if(!empty($atts['li-style']))
                    $result .= '<li style="' . esc_attr($atts['li-style']) . '">';
                else
                    $result .= '<li>';
                $person_name = $person['first_names'] . ' ' . $person['last_names'];
                if($atts['link'] !== 'False' and !empty($person['url']))
                    $result .= '<a href="' . esc_attr($person['url']) . '" target="_blank">' . esc_html($person_name) . '</a>';
                else
                    $result .= esc_html($person_name);
                if($atts['affiliation'] !== 'False' and !empty($person['affiliation']))
                    $result .= ', ' . esc_html($person['affiliation']);
                if($atts['country'] !== 'False' and !empty($person['country']))
                    $result .= ', ' . esc_html($person['country']);
                if($atts['date'] !== 'False')
                {
                    if(empty($person['until_year']))
                    {
                        if(!empty($person['since_year']))
                            $result .= ' (' . ( $current_year >= $person['since_year'] ? 'since' : 'starting in') . ' ' . esc_html($person['since_year']) . ')';
                    }
                    else
                    {
                        if(!empty($person['since_year']))
                            $result .= ' (' . esc_html($person['since_year']) . ' - ' . esc_html($person['until_year']) . ')';
                        else
                            $result .= ' (until ' . esc_html($person['until_year']) . ')';
                    }
                }
                if($atts['extra'] !== 'False' and !empty($person['extra']))
                {
                    $result .= ' (' . esc_html($person['extra']) . ')';
                }
                $result .= '</li>';
            }
        }
        $result .= '</ul>';

        return $result;
    }


        /**
         * Function generating the person-count shotcode
         *
         * To be added as a shortcode via add_shortcode()
         *
         * @since  0.4.1+
         * @access public
         * @param  array  $atts Array of attributes
         * @param  string $content Content of the shortcode (is ignored)
         * @param  string $tag Ignored
         * @return int The count
         */
    public static function persons_count_shortcode( $atts, $content, $tag ) {

        if(empty($atts))
            $atts = array();

        foreach(static::$shortcode_atts['persons-count'] as $key => $value)
            if(empty($atts[$key])) $atts[$key] = $value['default'];

        $person_data = static::get_person_data();

        $current_year = date('Y');
        $count = 0;
        foreach($person_data as $x => $person)
        {
            if($atts['former'] === 'False')
                if(!empty($person['until_year']) and $current_year > $person['until_year'])
                    continue;
            if($atts['former'] === 'Only')
                if(empty($person['until_year']) or $current_year < $person['until_year'])
                    continue;

            if(empty($atts['role']) or $atts['role'] === $person['role'] or in_array($person['role'], preg_split('/\s*,\s*/u', $atts['role'])))
                $count += 1;
        }

        return $count;
    }


        /**
         * Add shortcodes of this class
         *
         * To be added to the init hook.
         *
         * @since  0.4.1+
         * @access public
         */
    public static function add_shortcodes() {

        add_shortcode('persons-ul', array('O3PO_PeopleShortcodes', 'persons_ul_shortcode'));
        add_shortcode('persons-count', array('O3PO_PeopleShortcodes', 'persons_count_shortcode'));

    }
}
