<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-rewrite-rules.php';

/**
 * Fired during plugin deactivation
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Code to deactivate the plugin.
	 *
	 * @since    0.1.0
	 */
	public static function deactivate() {

        O3PO_RewriteRules::flush_on_deactivation();

	}

}
