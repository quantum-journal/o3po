<?php

/**
 * Fired during plugin activation
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-rewrite-rules.php';

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Code to activate the plugin.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {

        O3PO_RewriteRules::schedule_refresh();
        // The pending refresh runs on wp_loaded after dynamic post types and endpoints register.

    }

}
