<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/includes/classes
 * @author     Smarty Studio | Martin Nestorov
 */
class Smarty_Sameday_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function load_plugin_textdomain() {
        load_plugin_textdomain('smarty-sameday-lockers-locator', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/');
    }
}