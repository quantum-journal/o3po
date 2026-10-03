<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

class O3PO_People {

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
         * Get a person's formatted name from their UUID.
         *
         * @since  0.4.1+
         * @access public
         */
    public static function get_formated_name_from_uuidv4($uuidv4) {

        $settings = O3PO_Settings::instance();
        $person_first_names = $settings->get_field_value('person_first_names');
        $person_last_names = $settings->get_field_value('person_last_names');
        $person_uuidv4 = $settings->get_field_value('person_uuidv4');

        $key = array_search($uuidv4, $person_uuidv4, true);

        if($key === false)
            return "";

        return  $person_first_names[$key] . " " . $person_last_names[$key];
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
        $person_uuidv4 = $settings->get_field_value('person_uuidv4');

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
                'uuidv4' => $person_uuidv4[$x],
                                   );

        return $person_data;
    }
}
