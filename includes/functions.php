<?php

/**
 * The plugin functions file.
 *
 * This is used to define general functions, shortcodes etc.
 * 
 * Important: Always use the `smarty_` prefix for function names.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/includes
 * @author     Smarty Studio | Martin Nestorov
 */

/**
 * Get available countries from the locker DB table.
 *
 * @since 1.0.0
 * @return array List of country names
 */
function smarty_get_available_countries() {
	global $wpdb;
	$table = $wpdb->prefix . 'smarty_sameday_lockers';

	$countries = $wpdb->get_col("SELECT DISTINCT country FROM {$table} ORDER BY country ASC");

	return $countries;
}

if (!function_exists('_sll_write_logs')) {
	/**
     * Writes logs for the plugin.
     * 
     * @since 1.0.0
     * @param string $message Message to be logged.
     * @param mixed $data Additional data to log, optional.
     */
    function _sll_write_logs($message, $data = null) {
        $log_entry = '[' . current_time('mysql') . '] ' . $message;
    
        if (!is_null($data)) {
            $log_entry .= ' - ' . print_r($data, true);
        }

        $logs_file = fopen(SAMEDAY_BASE_DIR . DIRECTORY_SEPARATOR . "logs.txt", "a+");
        fwrite($logs_file, $log_entry . "\n");
        fclose($logs_file);
    }
}