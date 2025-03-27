<?php

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/includes/classes
 * @author     Smarty Studio | Martin Nestorov
 */
class Smarty_Sameday_Deactivator {

	/**
	 * This function will be executed when the plugin is deactivated.
	 *
	 * @since    1.0.0
     * @return   void
	 */
	public static function deactivate() {
		global $wpdb;
        $sameday_lockers_table_name = $wpdb->prefix . SAMEDAY_DB_PREFIX . SAMEDAY_LOCKER_TABLE;

        // SQL to drop the Sameday lockers records table
        $sql = "DROP TABLE IF EXISTS $sameday_lockers_table_name;";
        $wpdb->query($sql);

        $timestamp = wp_next_scheduled('update_sameday_lockers_event');
		
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'update_sameday_lockers_event');
        }
    }
}