<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/admin
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks
 *
 * @package    O3PO
 * @subpackage O3PO/admin
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Admin {

        /**
         * The ID of this plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
	private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
	private $version;

        /**
         * The pretty name of this plugin.
         *
         * @since    0.1.0
         * @access   private
         * @var      string    $plugin_pretty_name    The pretty name of this plugin.
         */
	private $plugin__pretty_name;

        /**
         * Initialize the class and set its properties.
         *
         * @since    0.1.0
         * @param    string    $plugin_name   The name of this plugin.
         * @param    string    $version       The version of this plugin.
         */
	public function __construct( $plugin_name, $version, $plugin_pretty_name ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->plugin_pretty_name = $plugin_pretty_name;

	}

        /**
         * Register the stylesheets for the admin area.
         *
         * To be added to the 'admin_enqueue_scripts' action.
         *
         * @since    0.1.0
         */
	public function enqueue_styles() {

            /**
             * This function is provided for demonstration purposes only.
             *
             * An instance of this class should be passed to the run() function
             * defined in O3PO_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The O3PO_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */

		wp_enqueue_style( $this->plugin_name . '-admin.css', plugin_dir_url( __FILE__ ) . 'css/' . $this->plugin_name . '-admin.css', array(), $this->version, 'all' );

	}

        /**
         * Register the JavaScript for the admin area.
         *
         * To be added to the 'admin_enqueue_scripts' action.
         *
         * @since    0.1.0
         */
	public function enqueue_scripts() {

            /**
             * This function is provided for demonstration purposes only.
             *
             * An instance of this class should be passed to the run() function
             * defined in O3PO_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The O3PO_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */

            //wp_enqueue_script( $this->plugin_name . '-admin.js', plugin_dir_url( __FILE__ ) . 'js/' . $this->plugin_name . '-admin.js', array( 'jquery' ), $this->version, false );

	}

        /**
         * Add links
         *
         * To be added to the 'plugin_action_links_[plugin-name]/[plugin-name].php' filter.
         *
         * @since    0.2.0
         * @param    array     $actions    Array of links to filter
         */
    public function add_plugin_action_links( $actions ) {

        $settings = array('settings' => '<a href="' . esc_url('options-general.php?page=' . $this->plugin_name . '-settings' ) . '">' . 'Settings</a>');
        $actions = array_merge($actions, $settings);

		return $actions;
    }


        /**
         * Top-level item to the administration menu
         *
         * @access public
         * @since 0.3.0
         */
    public function add_meta_data_explorer_page_to_menu() {
        add_menu_page(
            $this->get_plugin_pretty_name() . ' meta-data explorer',
            $this->get_plugin_pretty_name(),
            'administrator',
            $this->get_plugin_name() . '-meta-data-explorer',
            array($this, 'render_meta_data_explorer'),
            'dashicons-chart-pie'
                      );
    }

        /**
         * Renders the O-3PO meta-data explorer
         *
         * @access public
         * @sinde 0.3.0
         */
    public function render_meta_data_explorer() {
        $html = '<div class="wrap">';
        $html .= '<h2>' . $this->get_plugin_pretty_name() .' meta-data explorer</h2>';
        $html .= '</div>';

        if(isset( $_GET['tab'] ))
            $active_tab = $_GET['tab'];
        else
        {
            reset($this->meta_data_explorer_tabs);
            $active_tab = key($this->meta_data_explorer_tabs);
        }

        $html .= '<h2 class="nav-tab-wrapper">' . "\n";
        foreach($this->meta_data_explorer_tabs as $tab_slug => $tab_name)
            $html .= '<a href="' . esc_url('?page=' . $this->get_plugin_name() . '-meta-data-explorer' . '&amp;tab=' . $tab_slug) . '" class="nav-tab' . ($active_tab == $tab_slug ? ' nav-tab-active' : '') . '">' . esc_html($tab_name) . '</a>' . "\n";
        $html .= '</h2>' . "\n";


        if($active_tab === 'meta-data')
        {
            $out = "";
            foreach(O3PO_PublicationType::get_active_publication_type_names() as $post_type)
            {
                $query = array(
                    'post_type' => $post_type,
                    'post_status' => array('publish'),
                    'posts_per_page' => -1,
                               );
                $my_query = new WP_Query( $query );
                if ( $my_query->have_posts() ) {
                    $num = 0;
                    while ( $my_query->have_posts() ) {
                        $num++;
                        $my_query->the_post();

                        $post_id = get_the_ID();
                        $post_type = get_post_type($post_id);

                        $out .= "[" . '"' . O3PO_PublicationType::get_formated_authors( $post_id ) . '", ' . O3PO_PublicationType::get_number_authors( $post_id ) . ', "' . O3PO_PublicationType::get_title() . ', "' . O3PO_PublicationType::get_corresponding_author_email($post_id) .  '"' . "],\n";
                    }
                }
            }
            wp_reset_postdata();

            $html .= '<textarea rows="16" style="width:80%;" readonly>' . esc_textarea($out) . '</textarea>';
        }
        elseif($active_tab === 'citation-metrics')
        {
            $html .= '<h3>Crossref cited-by citation statistics</h3>';

            $settings = O3PO_Settings::instance();

            $login_id = $settings->get_plugin_option('crossref_id');
            $login_passwd = $settings->get_plugin_option('crossref_pw');
            $crossref_url = $settings->get_plugin_option('crossref_get_forward_links_url');
            $doi_prefix = $settings->get_plugin_option('doi_prefix');
            $doi_url_prefix = $settings->get_plugin_option('doi_url_prefix');
            $first_volume_year = $settings->get_plugin_option('first_volume_year');
            $start_date = $first_volume_year . '-01-01';

            $citations = O3PO_Crossref::get_all_citation_counts($crossref_url, $login_id, $login_passwd, $doi_prefix, $start_date);

            $html .= '<p>The following data is based on cited-by data by Crossref for publications published under the DOI prefix ' . $doi_prefix . ' since ' . $start_date . '. Remember that not all publishers participate in this service, and therefore citations may be missing.</p>';

            foreach(O3PO_PublicationType::get_active_publication_type_names() as $post_type)
            {
                $query = array(
                    'post_type' => $post_type,
                    'post_status' => array('publish'),
                    'posts_per_page' => -1,
                               );

                $my_query = new WP_Query( $query );
                if ( $my_query->have_posts() ) {
                    $num = 0;
                    $citations_this_type = array();
                    while ( $my_query->have_posts() ) {
                        $num++;
                        $my_query->the_post();

                        $post_id = get_the_ID();
                        $post_type = get_post_type($post_id);
                        $doi = O3PO_PublicationType::get_doi($post_id);
                        $citations_this_type[$doi] = (!empty($citations[$doi]) ? $citations[$doi] : '0');
                    }
                }

                $max_citations = max($citations_this_type);
                $total_publications = count($citations_this_type);

                if($max_citations == 0)
                    continue;

                $html .= '<h4>Publications of type ' . $post_type . '</h4>';
                $html .= '<h5>Ten most cited</h5>';
                arsort($citations_this_type);
                $num = 10;
                $html .= '<table><tr><th style="text-align: center;" >Citations</th><th style="text-align: center;">DOI</th></tr>';
                foreach($citations_this_type as $doi => $citations)
                {
                    $html .= '<tr><td style="text-align: right;">' . esc_html($citations) . '</td><td style="text-align: left;"><a href="' . esc_attr($doi_url_prefix . $doi) . '">' . esc_html($doi) . '</a></td></tr>' . "\n";
                    $num -= 1;
                    if($num <= 0)
                        break;
                }
                $html .= '</table>' ;

                $delta_x = 1;
                while($max_citations/$delta_x > 25)
                    $delta_x += 1;

                $html .= '<h5>Citation statistics</h5>';
                $html .= static::histogram($citations_this_type, $delta_x, "citations", "number of publications", "Citation histogram.", "#53257F");

                $html .= '<table>
<tr><td style="text-align: right;">Total number of publications:</td><td style="text-align: left;">' . count($citations_this_type) . '</td></tr>
<tr><td style="text-align: right;">Mean number of citations:</td><td style="text-align: left;">' . O3PO_Utility::array_mean($citations_this_type) . '</td></tr>
<tr><td style="text-align: right;">Median number of citations:</td><td style="text-align: left;">' . O3PO_Utility::array_median($citations_this_type) . '</td></tr></table>';
            }
            wp_reset_postdata();

        }

        echo $html;
    }


    private $meta_data_explorer_tabs = [
        'meta-data' => 'Meta-data',
        'citation-metrics' => 'Citation metrics'
                                             ];


    public static function caption($type,$text,$ref=null)
    {
        global $caption_refs, $last_num;

        $output = "";
        if(!isset($caption_refs[$type]))
        {
            $caption_refs[$type] = array();
        }

        if(!isset($last_num[$type]))
            $last_num[$type] = 0;

        $id = $last_num[$type]+1;

        if(!empty($ref))
            $caption_refs[$type][$ref] = $id;

            /* if($type==="table") */
            /*     $output .= "<caption style=\"caption-side: bottom;margin-bottom:1em;margin-top:0.5em;text-align:left\"><span id=\"" . $type . $id . "\" style=\"transform:translateY(-50vh);\"></span><strong>".ucfirst($type)." ".($last_num[$type]+1).":</strong> ".$text."</caption>"; */
            /* else */
        $output .= "<div style=\"display: flex;\"><span id=\"" . $type . $id . "\" style=\"transform:translateY(-50vh);\"></span><div style=\"flex-grow: 1;width: 0;margin-bottom:1em;margin-top:0.5em;text-align:left\"><strong>".ucfirst($type)." ".($last_num[$type]+1).":</strong> ".$text."</div></div>";

        $output .= "\n";
        $last_num[$type] +=1;
        return $output;
    }


    public static function histogram($data, $x_delta, $x_label, $y_label, $caption, $color, $ref=null, $extra=null)
{
    $frame_color = "gray";

    $max_val_in_data = 0;
    foreach($data as $title => $t)
    {
        if($t > $max_val_in_data)
            $max_val_in_data = $t;
    }
    $x_max = floor($max_val_in_data/$x_delta+1)*$x_delta;

    $hist_data = array_fill(0,floor($x_max/$x_delta)+1,0);
    foreach($data as $title => $t)
    {
        $hist_data[floor($t/$x_delta)] += 1;
    }
    $y_max = max($hist_data);

    $y_delta = 1;
    $y_delta_step = 0;
    while($y_max/$y_delta > 10) {
        $y_delta = (($y_delta_step % 3)**2 + 1) * 10**floor($y_delta_step/3); #taken from http://oeis.org/A051109
        $y_delta_step += 1;
    }

    $output = "";
    $output .= '<div style="position:relative;display:inline-block;vertical-align:top;width:100%;max-width:45em;margin-right:1em;"><div style="display:block;margin:1em;margin-bottom:3em;margin-left:1em;"><div style="position:relative;height:14em;outline:0.2ch solid '.$frame_color.';margin-left:6ch;margin-bottom:2em;margin-right:2ch;margin-top:0.5em">';
    $output .=  '<span style="display:inline-block;width:0;height:100%;"></span>';//make following bars start at the bottom;
    /* foreach($data as $title => $t) */
    /* { */
    /*     $output .=  '<span title="'.round($t/60/60/24,0).' days" style="display:inline-block;width:'.(100/$x_max).'%;height:'.($t/$y_max*100).'%;box-shadow:0px 0px 0px 1px '.$color.' inset;background-color:'.$color.';"   onMouseOver="this.style.opacity=\'0.7\'" onMouseOut="this.style.opacity=\'1\'"></span>'; */
    /* } */

    foreach($hist_data as $x => $y)
    {
        $output .= '<span title="'.$y.' times ' . $x . '" style="display:inline-block;width:'.(100*$x_delta/$x_max).'%;height:'.($y/$y_max*100).'%;box-shadow:0px 0px 0px 1px '.$color.' inset;background-color:'.$color.';"   onMouseOver="this.style.opacity=\'0.7\'" onMouseOut="this.style.opacity=\'1\'"></span>';
    }

//Axes
    for($y=0; $y <= $y_max; $y+=$y_delta)
    {
        $output .=  '<div style="position:absolute;left:-1ch;width:1ch;height:0.2ch;top:'.(100-$y/$y_max*100).'%;background-color:'.$frame_color.';box-shadow:0px 0px 0px 1px '.$frame_color.' inset;"><div style="color:'.$frame_color.';position:absolute;left:-6ch;width:5ch;bottom:-0.5em;text-align:right">'.round($y,2).'</div></div>';
    }
    for($x=0; $x <= $x_max; $x+=$x_delta)
    {
        $output .=  '<div style="position:absolute;bottom:-1ch;width:0.2ch;height:1ch;right:'.(100-$x/$x_max*100).'%;background-color:'.$frame_color.';box-shadow:0px 0px 0px 1px '.$frame_color.' inset;"><div style="color:'.$frame_color.';position:absolute;bottom:-1.5em;width:6ch;left:-3ch;text-align:center">'.round($x,2).'</div></div>';
    }
        //Axes labels
    $output .=  '<div style="position:absolute;right:50%;bottom:-2em;"><div style="position:absolute;left:-13em;width:26em;text-align:center;">'.$x_label.'</div></div>';
    $output .=  '<div style="position:absolute;top:50%;left:-7ch;"><div style="position:absolute;left:-8em;text-align:center;width:16em;-moz-transform: rotate(-90deg);-o-transform: rotate(-90deg);-webkit-transform: rotate(-90deg);transform: rotate(-90deg);">'.$y_label.'</div></div>';
    $output .=  '</div></div>' . "\n";
    static::caption("figure", $caption, $ref);
    $output .=  '<div class="bar-chart-data" style="display:none;">' . "\n";
    $output .=  "time in seconds\n";
    foreach($data as $data_point)
        $output .=  $data_point . ", ";
    $output .= '</div>'. "\n";
    $output .= '</div>';

    return $output;
}



        /**
         * Enable MathJax and some extra functionality on admin pages.
         *
         * This allows us to show a live preview of titles and abstracts containing
         * mathematical formulas and save their MathML representation when a post is saved.
         * This would be very hard (impossible?) to do with just php, so we have to resort
         * to running some code in the browser of the person adding the manuscript to the
         * website. Concretely, the following adds a live preview and MathML output
         * to all text fields of with css class "preview_and_mathml". The MathML
         * output is itself a textfield and gets and id that is derived from that of
         * the input field. When the post is saved its content hence ends up in POST
         * and can be captured by our PHP code in the custom post types.
         *
         * To be added to the 'admin_head' action.
         *
         * @since    0.1.0
         */
    public function enable_mathjax() {

        $settings = O3PO_Settings::instance();

?>
        <script type="text/x-mathjax-config">
        MathJax.Hub.Config({
              tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']], processEscapes: true},
                    extensions: ["toMathML.js"]
                    });
        </script>
        <script type="text/javascript" async src="<?php echo $settings->get_plugin_option('mathjax_url') ?>?config=TeX-AMS_CHTML"></script>
        <script type="text/javascript">
        function toMathML(jax,callback) {
            var mml;
            try {
                mml = jax.root.toMathML("");
            } catch(err) {
                if (!err.restart) {throw err} // an actual error
                return MathJax.Callback.After([toMathML,jax,callback],err.restart);
            }
            MathJax.Callback(callback)(mml);
        }
	    var PreviewAndMathML = {
          update: function (tex, id) {
                var element = document.getElementById(id);
                var formula = document.getElementById(id + "_preview");
                var mathml = document.getElementById(id + "_mathml");
                if(element && tex && tex.match(/[^\\]\$.*[^\\]\$|^\$.*[^\\]\$/g)) { //processEscapes: true should be set in the tex2jax config
                    if(!formula || !mathml) {
                        var p = document.createElement("p");
                        p.setAttribute("id", id + "_preview");
                        element.parentNode.insertBefore(p, element.nextSibling);
                        var textarea = document.createElement("textarea");
                        textarea.setAttribute("id", id + "_mathml");
                        textarea.setAttribute("name", id + "_mathml");
                        textarea.setAttribute("style", "width:100%");
                        textarea.setAttribute("rows", "10");
                        textarea.setAttribute("readonly", "");
                        p.parentNode.insertBefore(textarea, p.nextSibling);
                        var formula = document.getElementById(id + "_preview")
                            var mathml = document.getElementById(id + "_mathml")
                            }
                    formula.textContent = tex;
                    MathJax.Hub.Queue(["Typeset", MathJax.Hub, formula], [ function (formula, mathml) {
                                var output = "";
                                if(formula) {
                                    for(var child=formula.firstChild; child!==null; child=child.nextSibling) {
                                        var jax = MathJax.Hub.getJaxFor(child);
                                        if(jax) {
                                            if(child.tagName.toLowerCase() == 'span')
                                                toMathML(jax, function (mml) {output += mml});
                                        } else {
                                            output += child.textContent;
                                        }
                                    }
                                    output = output.replace(/\r?\n|\r/g, '');//remove newlines
                                    output = output.replace(/>\s*</g, '><');//remove superflous whitespace
                                    output = output.replace(/<!--.*?-->/g, '');//remove commets
                                    output = output.replace(/<([\/]?)/g, '<$1mml:');//Add the mml: namespace because crossref wants it like that
                                    output = output.replace(/xmlns=/g, 'xmlns:mml=');
                                    mathml.value = output;
                                }
                            }, formula, mathml ]);
                } else if(element) {
                    if(formula)
                        element.parentNode.removeChild(formula);
                    if(mathml)
                        element.parentNode.removeChild(mathml);
                }
            }
	    };
	    window.onload = function() {
	        var anchors = document.getElementsByClassName("preview_and_mathml");
	        for(var i = 0; i < anchors.length; i++) {
	            var anchor = anchors[i];
	            anchor.oninput = function() {
                    PreviewAndMathML.update(this.value, this.id);
                }
                PreviewAndMathML.update(anchor.value, anchor.id);
	        }
	    }
        </script>
<?php

    }

        /**
         * Get the plugin_name.
         *
         * @since 0.3.0
         * @access public
         */
    public function get_plugin_name() {

        return $this->plugin_name;
    }

        /**
         * Get the plugin_pretty_name.
         *
         * @since 0.3.0
         * @access public
         */
    public function get_plugin_pretty_name() {

        return $this->plugin_pretty_name;
    }

}
