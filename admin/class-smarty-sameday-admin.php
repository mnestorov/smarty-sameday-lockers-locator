<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for how to enqueue 
 * the admin-specific stylesheet (CSS) and JavaScript code.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/admin
 * @author     Smarty Studio | Martin Nestorov
 */
class Smarty_Sameday_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version         The current version of this plugin.
	 */
	private $version;

	/**
     * The instance of the API class.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Smarty_Sameday_API    $api_instance    Instance of the API class.
     */
    protected $api_instance;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name     The name of this plugin.
	 * @param    string    $version         The version of this plugin.
	 * @param    Smarty_Sameday_API    $api_instance    Instance of the API class.
	 * @return   void
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Initialize the API class
        $this->api_instance = new Smarty_Sameday_API();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 * @return    void
	 */
	public function enqueue_styles() {
		/**
		 * This function enqueues custom CSS for the plugin settings in WordPress admin.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smarty_Sameday_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smarty_Sameday_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/smarty-sameday-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 * @return    void
	 */
	public function enqueue_scripts() {
		/**
		 * This function enqueues custom JavaScript for the plugin settings in WordPress admin.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smarty_Sameday_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smarty_Sameday_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/smarty-sameday-admin.js', array('jquery'), $this->version, false);

        wp_localize_script($this->plugin_name, 'sameday_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
			'update_nonce' => wp_create_nonce('smarty_sameday_update_nonce'),
            'updateSamedayLockersFailedMessage' => __('Update failed.', 'smarty-sameday-lockers-locator')
        ));
	}

	/**
	 * Adds an options page for the plugin in the WordPress admin menu.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function add_admin_menu() {
		add_options_page(
			__('Sameday Lockers | Settings', 'smarty-sameday-lockers-locator'),   
			__('Sameday Lockers', 'smarty-sameday-lockers-locator'),   
			'manage_options', 
			'smarty-sameday-settings', 
			array($this, 'display_settings_page')
		);
	}

	/**
	 * Outputs the HTML for the settings page.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function display_settings_page() {
		if (!current_user_can('manage_options')) {
			return;
		}
	
		// Check if settings have been submitted
		if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
			// Redirect to settings page with custom query variable to avoid the default notice
			wp_redirect(add_query_arg('smarty-settings-updated', 'true', menu_page_url('smarty-sameday-settings', false)));
			exit;
		}
		
		// Define the path to the external file
		$partial_file = plugin_dir_path(__FILE__) . 'partials/smarty-sameday-admin-display.php';

		if (file_exists($partial_file) && is_readable($partial_file)) {
			include_once $partial_file;
		} else {
			_sll_write_logs("Unable to include: '$partial_file'");
		}
	}

	/**
	 * Initializes the plugin settings by registering the settings, sections, and fields.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function settings_init() {
		// Check if the settings were saved and set a transient
		if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
			set_transient('smarty_sameday_settings_updated', 'yes', 5);
		}
	
		// Registers a setting and its data]
		register_setting('smarty-sameday-settings-options', 'smarty_sameday_settings');
		register_setting('smarty-sameday-settings-options', 'smarty_sameday_country_code');
		register_setting('smarty-sameday-settings-options', 'smarty_sameday_default_locker');
		register_setting('smarty-sameday-settings-options', 'smarty_sameday_hide_locker');
	
		// Adds a new section to a settings page
		add_settings_section(
			'smarty_sameday_section_general',    	                    		// ID of the section
			__('General', 'smarty-sameday-lockers-locator'),              		// Title of the section
			array($this, 'section_general_cb'), 	                    		// Callback function that fills the section with the desired content
			'smarty-sameday-settings'              	                        	// Page on which to add the section
		);
	
		// Adds a new fields to a settings page
		add_settings_field(
			'smarty_sameday_field_country_code', 								// ID of the field
			__('Country Code', 'smarty-sameday-lockers-locator'), 				// Title of the field
			array($this, 'field_country_code_cb'), 								// Callback function to render the field
			'smarty-sameday-settings', 											// Page on which to add the field
			'smarty_sameday_section_general',									// Section to which the field belongs
			array(                                                     		 	// Extra arguments passed to the callback function
				'label_for' 				 => 'smarty_sameday_field_country_code',
				'class' 					 => 'smarty_sameday_row',
				'smarty_sameday_custom_data' => 'custom',
			)
		);
		
		// New section for Sameday credentials
		add_settings_section(
			'smarty_sameday_section_updater',	 	                			// ID of the section
			__('Locker Updater', 'smarty-sameday-lockers-locator'),	    		// Title of the section
			array($this, 'section_updater_cb'),	                	    		// Callback function that fills the section with the desired content
			'smarty-sameday-settings',					                   		// Page on which to add the section
		);
		
		// Adds a new field for setting the automatic update schedule
		add_settings_field(
			'smarty_sameday_field_update_schedule',                  		    // ID of the field
			__('Automatic Update Schedule', 'smarty-sameday-lockers-locator'),  // Title of the section
			array($this, 'field_update_schedule_cb'),                           // Callback function that fills the section with the desired content
			'smarty-sameday-settings',                                          // Page on which to add the section
			'smarty_sameday_section_updater'                       		        // Section to which the field belongs
		);
		
		// Adds a new field for manual update trigger
		add_settings_field(
			'smarty_sameday_field_manual_update',                               // ID of the field
			__('Manual Update', 'smarty-sameday-lockers-locator'),              // Title of the section
			array($this, 'field_manual_update_cb'),                             // Callback function that fills the section with the desired content
			'smarty-sameday-settings',                                          // Page on which to add the section
			'smarty_sameday_section_updater'                       		        // Section to which the field belongs
		);

		// New section for Sameday credentials
		add_settings_section(
			'smarty_sameday_section_visualization',	 	                        // ID of the section
			__('Visualization', 'smarty-sameday-lockers-locator'),		        // Title of the section
			array($this, 'section_visualization_cb'),	                        // Callback function that fills the section with the desired content
			'smarty-sameday-settings'					                        // Page on which to add the section
		);
	
		add_settings_field(
			'smarty_sameday_default_locker', 
			__('Default to Sameday Locker', 'smarty-sameday-lockers-locator'), 
			array($this, 'checkbox_default_locker_cb'), 
			'smarty-sameday-settings', 
			'smarty_sameday_section_visualization'
		);
	
		add_settings_field(
			'smarty_sameday_hide_locker', 
			__('Hide Sameday Locker', 'smarty-sameday-lockers-locator'), 
			array($this, 'checkbox_hide_locker_cb'), 
			'smarty-sameday-settings', 
			'smarty_sameday_section_visualization'
		);
		
		// New section for Sameday credentials
		add_settings_section(
			'smarty_sameday_section_credentials',	 	                        // ID of the section
			__('Credentials', 'smarty-sameday-lockers-locator'),		        // Title of the section
			array($this, 'section_credentials_cb'),	                            // Callback function that fills the section with the desired content
			'smarty-sameday-settings'					                        // Page on which to add the section
		);
	
		// Adds a new fields to a settings page
		add_settings_field(
			'smarty_sameday_field_username',            	                    // ID of the field
			__('Username', 'smarty-sameday-lockers-locator'),                   // Title of the field
			array($this, 'field_username_cb'),   	                            // Callback function to render the field
			'smarty-sameday-settings',            	                            // Page on which to add the field
			'smarty_sameday_section_credentials'   	                            // Section to which the field belongs
		);
	
		// Adds a new fields to a settings page
		add_settings_field(
			'smarty_sameday_field_password',            	                    // ID of the field
			__('Password', 'smarty-sameday-lockers-locator'),                   // Title of the field
			array($this, 'field_password_cb'),   	                            // Callback function to render the field
			'smarty-sameday-settings',            	                            // Page on which to add the field
			'smarty_sameday_section_credentials'   	                            // Section to which the field belongs
		);
	}

	/**
	 * Callback function for the section.
	 *
	 * @since    1.0.0
	 * @param array  $args	Additional arguments passed by add_settings_section.
	 * @return void
	 */
	public function section_general_cb($args) {
		?>
    	<p id="<?= esc_attr($args['id']); ?>"><?= esc_html__('Enter the Country ID (ISO code) to filter the Sameday lockers on the checkout page field.', 'smarty-sameday-lockers-locator'); ?></p>
    	<?php
	}

	/**
	 * Callback function for the section.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public function section_updater_cb() {
		?>
		<p><?= esc_html__('Settings to manually trigger lockers locations update and/or set a schedule for automatic lockers locations update.', 'smarty-sameday-lockers-locator'); ?></p>
		<?php
	}

	/**
	 * Callback function for the section.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public function section_credentials_cb() {
		?>
		<p><?= esc_html__('Enter your Sameday service credentials.' , 'smarty-sameday-lockers-locator'); ?></p>
		<?php
	}

	/**
	 * Callback function for the section.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public function section_visualization_cb() {
		?>
		<p><?= esc_html__('Show/Hide delivery methods.' , 'smarty-sameday-lockers-locator'); ?></p>
		<?php
	}

	/**
	 * Callback function for the country id field.
	 *
	 * @since    1.0.0
	 * @param array $args Additional arguments passed by add_settings_field.
	 * @return void
	 */
	public function field_country_code_cb($args) {
		$options = get_option('smarty_sameday_settings');
		$country_code = isset($options['smarty_sameday_field_country_code']) ? $options['smarty_sameday_field_country_code'] : '';
		?>
		<input type="text" id="<?= esc_attr($args['label_for']); ?>" name="smarty_sameday_settings[smarty_sameday_field_country_code]" value="<?= esc_attr($country_code); ?>" data-custom="<?= esc_attr($args['smarty_sameday_custom_data']); ?>">
		<p class="description"><?= __('A list of country codes can be found here: <a href="https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes" target="_blank">ISO 3166 country codes</a>', 'smarty-sameday-lockers-locator'); ?></p>
		<?php
	}

	/**
	 * Callback function for the automatic update schedule field.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public function field_update_schedule_cb() {
		$options = get_option('smarty_sameday_settings');
		$schedule = isset($options['auto_update_schedule']) ? $options['auto_update_schedule'] : '';
		?>
		<input type="text" id="smarty_sameday_field_auto_update_schedule" 
			name="smarty_sameday_settings[auto_update_schedule]" 
			value="<?= esc_attr($schedule); ?>">
		<p class="description"><?= esc_html__('Enter the day and time for automatic update. Format: "Day HH:MM", e.g., "Monday 03:00".', 'smarty-sameday-lockers-locator'); ?></p>
		<?php
	}

	/**
	 * Callback function for the manual update field.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public function field_manual_update_cb() {
		$nonce = wp_create_nonce('smarty_sameday_update_nonce');
		echo '<button type="button" id="smarty_sameday_manual_update" class="button button-secondary">' . esc_html__('Update Now', 'smarty-sameday-lockers-locator') . '</button>';
		echo '<div id="smarty_sameday_update_message"></div>'; // Placeholder for the message
	}
	
	/**
	 * Callback function for the locker checkbox.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function checkbox_default_locker_cb() {
		$option = get_option('smarty_sameday_default_locker', 'no');
		_sll_write_logs('Current checkbox_default_locker option: ' . $option);  // Debug log
		$checked = $option === 'yes' ? 'checked' : '';
		echo '<input type="checkbox" id="smarty_sameday_default_locker" name="smarty_sameday_default_locker" ' . $checked . ' value="yes">';
	}
	
	/**
	 * Callback function to hide locker radio.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function checkbox_hide_locker_cb() {
		$option = get_option('smarty_sameday_hide_locker', 'no');
		_sll_write_logs('Current checkbox_hide_locker option: ' . $option);  // Debug log
		$checked = $option === 'yes' ? 'checked' : '';
		echo '<input type="checkbox" id="smarty_sameday_hide_locker" name="smarty_sameday_hide_locker" ' . $checked . ' value="yes">';
	}

	/**
	 * Callback function for the username field.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public function field_username_cb() {
		$options = get_option('smarty_sameday_settings');
		//_sll_write_logs('Username Option: ' . print_r($options, true)); // Debugging
		$username = isset($options['smarty_sameday_field_username']) ? $options['smarty_sameday_field_username'] : '';
		?>
		<input type="text" id="smarty_sameday_field_username" 
			name="smarty_sameday_settings[smarty_sameday_field_username]" 
			value="<?= esc_attr($username); ?>">
		<?php
	}

	/**
	 * Callback function for the password field.
	 * 
	 * @since    1.0.0
	 * @return void
	 */
	public function field_password_cb() {
		$options = get_option('smarty_sameday_settings');
		//_sll_write_logs('Password Option: ' . print_r( $options, true)); // Debugging
		$password = isset($options['smarty_sameday_field_password']) ? $options['smarty_sameday_field_password'] : '';
		?>
		<input type="text" id="smarty_sameday_field_password" 
			name="smarty_sameday_settings[smarty_sameday_field_password]" 
			value="<?= esc_attr($password); ?>">
		<?php
	}

	/**
	 * Function to check the custom query variable.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function sameday_success_notice() {
		// Check for the custom query variable instead of the transient
		if (isset($_GET['smarty-settings-updated']) && $_GET['smarty-settings-updated'] == 'true') {
			echo '<div class="notice notice-success smarty-auto-hide-notice"><p>' . esc_html__('Settings saved.', 'smarty-sameday-lockers-locator') . '</p></div>';
		}
	}

	/**
	 * Calls the `insert_sameday_lockers` method from the API instance.
	 *
	 * @since 	 1.0.0
	 * @return   array|null The response from the API.
	 */
    public function api_insert_sameday_lockers() {
        return $this->api_instance->insert_sameday_lockers();
    }

	/**
	 * Calls the `query_sameday_lockers` method from the API instance.
	 *
	 * @since 	 1.0.0
	 * @return   array|null The response from the API.
	 */
	public function api_query_sameday_lockers() {
		$options = get_option('smarty_sameday_settings');
		$country_code = $options['smarty_sameday_field_country_code'] ?? 'RO';
	
		return $this->api_instance->query_sameday_lockers($country_code);
	}	

	/**
	 * Schedules a weekly cron job to update Sameday lockers.
	 *
	 * This function checks if a cron job is already scheduled with the same timing.
	 * If not, it schedules a new cron job. 
	 * The timing is set based on the user-configured schedule in the plugin settings.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function schedule_updates() {
		if (!is_admin()) {
			return;
		}

		$options = get_option('smarty_sameday_settings');
		$schedule = isset($options['auto_update_schedule']) ? $options['auto_update_schedule'] : '';

		// Assuming format "Day HH:MM"
		$schedule_parts = explode(' ', $schedule);
		if (count($schedule_parts) === 2) {
			$scheduled_day = $schedule_parts[0];
			$scheduled_time = $schedule_parts[1];
			
			// Convert to WordPress timezone
			$timezone = get_option('timezone_string');
			if ($timezone) {
				date_default_timezone_set($timezone);
			}

			// Calculate the timestamp for the next occurrence
			$timestamp = strtotime("next " . $scheduled_day . " " . $scheduled_time);

			// Clear existing schedule and set a new one if it's different
			$current_timestamp = wp_next_scheduled('smarty_sameday_auto_updater');
			if ($current_timestamp !== $timestamp) {
				if ($current_timestamp) {
					wp_unschedule_event($current_timestamp, 'smarty_sameday_auto_updater');
				}
				wp_schedule_event($timestamp, 'weekly', 'smarty_sameday_auto_updater');
			}
		}
	}

	/**
	 * The function executed by the cron job to update Sameday lockers.
	 *
	 * It logs the start and end times of the execution for debugging purposes
	 * and calls the function to perform the actual update.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function update_sameday_lockers_event() {
		_sll_write_logs('update_sameday_lockers_event started on: ' . current_time('mysql'));
		self::api_insert_sameday_lockers(); // insert/update method
		_sll_write_logs('update_sameday_lockers_event ended on: ' . current_time('mysql'));
	}

	/**
	 * Handles the manual update request for Sameday lockers via AJAX.
	 *
	 * It checks the AJAX nonce for security, updates the Sameday lockers,
	 * and then sends a JSON response indicating success.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function handle_manual_update() {
		check_ajax_referer('smarty_sameday_update_nonce', 'security');
		self::api_insert_sameday_lockers(); // insert/update method
		wp_send_json_success(array('message' => __('Sameday lockers updated successfully.', 'smarty-sameday-lockers-locator')));
	}

	/**
	 * Retrieves the full details of an Sameday locker based on its locker number.
	 * 
	 * @since    1.0.0
	 * @param int $locker_number The unique number identifying an Sameday locker.
	 * @return array|null The details of the locker or null if not found.
	 */
	public function get_locker_details_by_number($locker_id) {
		$options = get_option('smarty_sameday_settings');
		$country_code = $options['smarty_sameday_field_country_code'] ?? 'RO';
		$lockers = $this->api_instance->query_sameday_lockers($country_code);
		_sll_write_logs('Fetching lockers for country: ' . $country_code);
		_sll_write_logs('All Lockers: ' . print_r($lockers, true));
	
		foreach ($lockers as $locker) {
			if ((int) $locker['sameday_id'] === (int) $locker_id) {
				_sll_write_logs('Found locker details for ID ' . $locker_id . ': ' . print_r($locker, true));
				return $locker;
			}
		}
	
		_sll_write_logs('No locker found for ID: ' . $locker_id);
		return null;
	}	

	/**
	 * Retrieves the full details of a Sameday locker based on its name.
	 * 
	 * This function is used to find the locker details based on the user's selection.
	 * 
	 * @since    1.0.0
	 * @param string $locker_name The name of the Sameday locker.
	 * @return array|null The details of the locker or null if not found.
	 */
	public function get_locker_details_by_name($locker_name) {
		$options = get_option('smarty_sameday_settings');
		$country_code = $options['smarty_sameday_field_country_code'] ?? 'RO';
		$lockers = $this->api_instance->query_sameday_lockers($country_code);
	
		foreach ($lockers as $locker) {
			if (stripos($locker['name'], $locker_name) !== false) {
				return $locker;
			}
		}
	
		return null;
	}

	/**
	 * Saves the user's selected Sameday option (locker) during WooCommerce checkout.
	 * 
	 * @since    1.0.0
	 * @param int $order_id The ID of the current WooCommerce order.
	 * @return void
	 */
	public function save_sameday_selection($order_id) {
		_sll_write_logs('POST Data: ' . print_r($_POST, true));
	
		if (isset($_POST['carrier_sameday'])) {
			$selected_option = sanitize_text_field($_POST['carrier_sameday']);
			$simplified_carrier = strtok($selected_option, ' ');
			update_post_meta($order_id, 'carrier', $simplified_carrier);
			update_post_meta($order_id, '_sameday_selected_option', $selected_option);
	
			if ($selected_option === 'Sameday Locker' && isset($_POST['sameday_locker']) && !empty($_POST['sameday_locker'])) {
				$locker_id = absint($_POST['sameday_locker']);
				$locker_details = $this->get_locker_details_by_number($locker_id);
			
				if ($locker_details && is_array($locker_details)) {
					update_post_meta($order_id, '_sameday_locker_details', maybe_serialize($locker_details));
			
					// Build the "nice" locker address
					$locker_address = sprintf(
						'Sameday: [%s] %s, %s (%s)',
						esc_html($locker_details['name'] ?? __('Unknown Name', 'smarty-sameday-lockers-locator')),
						esc_html($locker_details['full_address'] ?? __('Unknown Address', 'smarty-sameday-lockers-locator')),
						esc_html($locker_details['city_name'] ?? __('Unknown City', 'smarty-sameday-lockers-locator')),
						esc_html($locker_details['sameday_id'] ?? __('Unknown ID', 'smarty-sameday-lockers-locator')),
					);
			
					// Save to order meta fields
					update_post_meta($order_id, 'billing_sameday_lockers', $locker_address);
					update_post_meta($order_id, '_shipping_address_1', $locker_address);
			
					// Overwrite "Test 123" or any other user-typed billing address fields
					$order = wc_get_order($order_id);
			
					// Shipping address
					$order->set_shipping_address_1($locker_address);
			
					// Billing address
					$order->set_billing_address_1($locker_address);
					$order->set_billing_city($locker_details['city_name']); 
			
					// Postcode for both
					if (!empty($locker_details['post_code'])) {
						update_post_meta($order_id, '_billing_postcode', $locker_details['post_code']);
						update_post_meta($order_id, '_shipping_postcode', $locker_details['post_code']);
			
						$order->set_shipping_postcode($locker_details['post_code']);
						$order->set_billing_postcode($locker_details['post_code']);
					}
			
					$order->save();
				}
			}
		}
	}

	/**
	 * Displays the selected Sameday option and its details in the WooCommerce order admin area.
	 * 
	 * @since    1.0.0
	 * @param object $order The WooCommerce order object.
	 * @return void
	 */
	public function display_sameday_selection_in_admin($order) {
		$sameday_selected_option = get_post_meta($order->get_id(), '_sameday_selected_option', true);
	
		if (!empty($sameday_selected_option)) {
			echo '<p class="form-field form-field-wide" style="margin-top: 20px;"><label><b>' . __('Selection', 'smarty-sameday-lockers-locator') . ': </b><span style="color: #2271b1;">' . esc_html($sameday_selected_option) . '</span></label></p>';
		}
	
		if ($sameday_selected_option === 'Sameday Locker') {
			?>
			<style type="text/css">
				.order_data_column > div.address > p.none_set { 
					display: none; 
				}
			</style>
			<?php
	
			$sameday_locker_details = get_post_meta($order->get_id(), '_sameday_locker_details', true);
			if (!empty($sameday_locker_details)) {
				$locker_details = maybe_unserialize($sameday_locker_details);
				if (is_array($locker_details)) {
					$locker_info = sprintf(
						'<strong>[%s]</strong>: %s (%s)',
						esc_html($locker_details['name'] ?? __('Unknown Name', 'smarty-sameday-lockers-locator')),
						esc_html($locker_details['full_address'] ?? __('Unknown Address', 'smarty-sameday-lockers-locator')),
						esc_html($locker_details['sameday_id'] ?? __('Unknown ID', 'smarty-sameday-lockers-locator'))
					);
					?>
					<p style="
						background-color: #edffeb; 
						border: 1px solid #bde5b9; 
						border-radius: 3px; 
						color: #333333; 
						display: flex; 
						flex-flow: column;
					">
						<span style="padding: 5px;"><?php echo $locker_info; ?></span>
					</p>
					<?php
				} else {
					echo '<p style="color: red;">' . __('Invalid locker details format.', 'smarty-sameday-lockers-locator') . '</p>';
				}
			} else {
				echo '<p style="color: red;">' . __('No locker details found.', 'smarty-sameday-lockers-locator') . '</p>';
			}
		}
	}

	/**
	 * Saves the details of the selected Sameday_locker to the order meta during WooCommerce checkout.
	 * 
	 * @since    1.0.0
	 * @param int $order_id The ID of the current WooCommerce order.
	 * @return void
	 */
	public function save_sameday_locker($order_id) {
		//_sll_write_logs(print_r($_POST, true)); // For debugging
		if (isset($_POST['sameday_locker']) && !empty($_POST['sameday_locker'])) {
			$locker_number = sanitize_text_field($_POST['sameday_locker']);
			$locker_details = self::get_locker_details_by_name($locker_number);

			if ($locker_details) {
				// Convert the locker details to a string or a serialized array
				$locker_details_str = maybe_serialize($locker_details);
				update_post_meta($order_id, '_sameday_locker_details', $locker_details_str);
			}
		}
	}

	/**
	 * Filter formatted billing/shipping address display in admin panel.
	 *
	 * @since 1.0.0
	 * @param array $address The formatted address.
	 * @return array The modified address.
	 */
	public function filter_formatted_address_output($address, $order) {
		$sameday_selected_option = get_post_meta($order->get_id(), '_sameday_selected_option', true);

		if ($sameday_selected_option === 'Sameday Locker') {
			$address['address_1'] = '';
		}
		return $address;
	}

	/**
	 * Hide the shipping address block in admin for Sameday Locker orders.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function hide_shipping_address_block() {
		global $post;
		if (!$post || get_post_type($post) !== 'shop_order') {
			return;
		}

		$order = wc_get_order($post->ID);
		$sameday_selected_option = get_post_meta($order->get_id(), '_sameday_selected_option', true);

		if ($sameday_selected_option === 'Sameday Locker') {
			echo '<style>
				#woocommerce-order-data .order_data_column:last-child { display: none !important; }
			</style>';
		}
	}
}