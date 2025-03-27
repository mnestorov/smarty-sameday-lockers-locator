<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/includes/classes
 * @author     Smarty Studio | Martin Nestorov
 */
class Smarty_Sameday_Activator {

	/**
	 * This function will be executed when the plugin is activated.
	 *
	 * @since    1.0.0
     * @return   void
	 */
	public static function activate() {
        self::create_sameday_table();

        // Clear any stored transients related to the plugin as they might be outdated
        delete_transient('sameday_lockers_bulgaria');
        delete_transient('all_sameday_lockers_global');

        // Schedule the automatic update event
        if (!wp_next_scheduled('update_sameday_lockers_event')) {
            wp_schedule_event(time(), 'weekly', 'update_sameday_lockers_event');
        }
    }

    /**
	 * Create Sameday Lockers table on plugin activation.
     *
	 * Initializes the database table for Sameday lockers.
     * 
	 * @since    1.0.0
     * @return   void
	 */
    private static function create_sameday_table() {
        global $wpdb;
        $sameday_lockers_table_name = $wpdb->prefix . SAMEDAY_DB_PREFIX . SAMEDAY_LOCKER_TABLE;

        $sql = "CREATE TABLE IF NOT EXISTS `{$sameday_lockers_table_name}` (
            `locker_id` INT(11) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `country` VARCHAR(100) NOT NULL,
            `city_name` VARCHAR(255) NOT NULL,
            `post_code` VARCHAR(20) NOT NULL,
            `address` VARCHAR(255) NOT NULL,
            `full_address` VARCHAR(255) NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`locker_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='All Sameday Lockers'";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Check for errors during table creation
        if($wpdb->last_error !== '') {
            _sll_write_logs('Database Error: ' . $wpdb->last_error);
        }

        // Initial data population
        $api_instance = new Smarty_Sameday_API();
        $api_instance->insert_sameday_lockers();
    }
}