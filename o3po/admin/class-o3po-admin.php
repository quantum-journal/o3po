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
         * Initialize the class and set its properties.
         *
         * @since    0.1.0
         * @param    string    $plugin_name   The name of this plugin.
         * @param    string    $version       The version of this plugin.
         */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
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
         * To be added to the 'plugin_action_links' filter.
         *
         * @since    0.2.0
         * @param    array     $links    Array of links to filter
         */
    public function add_plugin_action_links( $actions )
    {
        static $plugin;

        $settings = array('settings' => '<a href="' . esc_url('options-general.php?page=' . $this->plugin_name . '-settings' ) . '">' . 'Settings</a>');

        $actions = array_merge($actions, $settings);

		return $actions;
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
}
