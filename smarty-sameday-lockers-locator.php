<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                    https://github.com/mnestorov
 * @since                   1.0.0
 * @package                 Smarty_Sameday_Locator
 *
 * @wordpress-plugin
 * Plugin Name:             SM - Sameday Lockers Locator for WooCommerce
 * Plugin URI:              https://github.com/mnestorov/smarty-sameday-lockers-locator
 * Description:             Integrates WooCommerce with Sameday, a Romanian courier service, to manage and display Sameday lockers on checkout page.
 * Version:                 1.0.0
 * Author:                  Martin Nestorov
 * Author URI:              https://github.com/mnestorov
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             smarty-sameday-lockers-locator
 * Domain Path:             /languages
 * WC requires at least:    3.5.0
 * WC tested up to:         9.0.2
 * Requires Plugins:		woocommerce
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Check if SAMEDAY_LOCATOR_VERSION is not already defined
if (!defined('SAMEDAY_LOCATOR_VERSION')) {
	/**
	 * Current plugin version.
	 * For the versioning of the plugin is used SemVer - https://semver.org
	 */
	define('SAMEDAY_LOCATOR_VERSION', '1.0.1');
}

// Check if SAMEDAY_BASE_DIR is not already defined
if (!defined('SAMEDAY_BASE_DIR')) {
	/**
	 * This constant is used as a base path for including other files or referencing directories within the plugin.
	 */
    define('SAMEDAY_BASE_DIR', dirname(__FILE__));
}

// Check if SAMEDAY_DB_PREFIX is not already defined
if (!defined('SAMEDAY_DB_PREFIX')) {
	/**
	 * This constant is used to store the db table name prefix.
	 */
    define('SAMEDAY_DB_PREFIX', 'smarty_');
}

// Check if SAMEDAY_LOCKER_TABLE is not already defined
if (!defined('SAMEDAY_LOCKER_TABLE')) {
	/**
	 * This constant is used to store the table name where Sameday locker data is kept.
	 */
    define('SAMEDAY_LOCKER_TABLE', 'sameday_lockers');
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/classes/class-smarty-sameday-activator.php
 * 
 * @since    1.0.0
 * @return void
 */
function activate_sameday_locator() {
	require_once plugin_dir_path(__FILE__) . 'includes/classes/class-smarty-sameday-activator.php';
	Smarty_Sameday_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/classes/class-smarty-sameday-deactivator.php
 * 
 * @since    1.0.0
 * @return void
 */
function deactivate_sameday_locator() {
	require_once plugin_dir_path(__FILE__) . 'includes/classes/class-smarty-sameday-deactivator.php';
	Smarty_Sameday_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_sameday_locator');
register_deactivation_hook(__FILE__, 'deactivate_sameday_locator');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/classes/class-smarty-sameday-locator.php';

/**
 * The plugin functions file that is used to define general functions, shortcodes etc.
 */
require plugin_dir_path(__FILE__) . 'includes/functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 * @return   void
 */
function run_sameday_locator() {
	$plugin = new Smarty_Sameday_Locator();
	$plugin->run();
}

run_sameday_locator();
