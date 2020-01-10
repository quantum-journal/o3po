<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://quantum-journal.org/o3po/
 * @since             0.1.0
 * @package           O3PO
 *
 * @wordpress-plugin
 * Plugin Name:       O-3PO
 * Plugin URI:        https://quantum-journal.org/o3po/
 * Description:       Open-source open-access overlay publishing option
 * Version:           0.3.1
 * Author:            Christian Gogolin, Quantum - the open journal for quantum science
 * Author URI:        http://cgogolin.de/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       o3po
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die; // @codeCoverageIgnore
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-o3po-activator.php
 */
function activate_o3po() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-o3po-activator.php';
	O3PO_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-o3po-deactivator.php
 */
function deactivate_o3po() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-o3po-deactivator.php';
	O3PO_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_o3po' );
register_deactivation_hook( __FILE__, 'deactivate_o3po' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-o3po.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_o3po() {

    $file_data = get_file_data(__FILE__, array(
                                   'Version' => 'Version',
                                   'Plugin Name' => 'Plugin Name',
                                   'Text Domain' => 'Text Domain'
                                               ));
    $plugin_name = $file_data['Text Domain']; //make the plugin 'slug' match the text domain
    $plugin_pretty_name = $file_data['Plugin Name'];
    $version = $file_data['Version'];

	$plugin = new O3PO($plugin_name, $plugin_pretty_name, $version);
	$plugin->run();

}
run_o3po();
