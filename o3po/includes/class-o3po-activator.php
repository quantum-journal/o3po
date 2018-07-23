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
            //Flush the rewrite rules to make custom endpoints work
        flush_rewrite_rules(true);
            /* Note: As this plugin contains dynamically generated post types
             * which cannot be registered via a static function call,
             * flushing rewrite rules here is not sufficient to make these
             * post types accessible. We thus flush again after registering
             * the post types, but only if there are not yet any rewrite
             * rules associated with the slugs of these post types, to avoid
             * unnecessary flusing. See class-o3po-publication-type.php for more
             * info.*/

    }

}
