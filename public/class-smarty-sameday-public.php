<?php

/**
 * The public functionality of the plugin.
 * 
 * Defines the plugin name, version, and two hooks for how to enqueue 
 * the public-facing stylesheet (CSS) and JavaScript code.
 * 
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/public
 * @author     Smarty Studio | Martin Nestorov
 */
class Smarty_Sameday_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name     The ID of this plugin.
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
	 * @param    string    $plugin_name     The name of the plugin.
	 * @param    string    $version         The version of this plugin.
	 * @param    Smarty_Sameday_API    $api_instance    Instance of the API class.
	 * @return   void
	 */
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		// Initialize the API class
        $this->api_instance = new Smarty_Sameday_API();
	}

	/**
     * Registers shortcodes.
	 * 
	 * @since    1.0.0
	 * @return   void
     */
    public function register_shortcodes() {
        add_shortcode('sameday_lockers', array($this, 'get_sameday_lockers'));
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function enqueue_styles() {
		/**
		 * This function enqueues custom CSS for the WooCommerce checkout page.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smarty_Sameday_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smarty_Sameday_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
         if (is_checkout()) {
            wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/smarty-sameday-public.css', array(), $this->version, 'all');
        }
    }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function enqueue_scripts() {
		/**
		 * This function enqueues custom JavaScript for the WooCommerce checkout page.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smarty_Sameday_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smarty_Sameday_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
         if (is_checkout()) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/smarty-sameday-public.js', array('jquery'), $this->version, true);

    		$hide_locker = get_option('smarty_sameday_hide_locker', 'no');

            wp_localize_script($this->plugin_name, 'sameday_params', array(
                'ajax_url' => WC()->ajax_url(),
                'selectSamedayLockerFieldTitle' => __('Sameday Locker', 'smarty-sameday-lockers-locator'),
                'selectSamedayLockerMessage' => __('Please select an Sameday locker.', 'smarty-sameday-lockers-locator'),
				'hide_sameday_locker' => $hide_locker !== 'yes' ? 'no' : 'yes',
            ));
        }
	}

	/**
	 * Adds custom Sameday radio buttons to the WooCommerce checkout page.
	 *
	 * This function is hooked into WooCommerce's 'woocommerce_before_checkout_billing_form'
	 * action, allowing it to inject custom HTML for Sameday shipping options into the checkout form.
	 * It provides two radio button options for the user to select their preferred Sameday shipping method.
	 *
	 * The function:
	 * - Generates a URL to the Sameday logo stored in a specific assets directory.
	 * - Outputs HTML for two radio buttons: 'Sameday Locker'.
	 * - Each radio button is styled with custom CSS classes and includes an image of the Sameday logo.
	 * - The 'Sameday Locker' option is set as the default selected option.
	 * 
	 * @since    1.0.0 
	 * @return   void
	 */
	public function add_sameday_radio_buttons() {
		$logo_url = plugins_url('images/sameday-logo.png', __FILE__);
		$enable_address = get_option('smarty_sameday_enable_address', 'no');
		$session_value = WC()->session->get('carrier_sameday');
		$should_check_address = empty($session_value) || $session_value === 'Sameday Address';
		$should_check_locker  = $session_value === 'Sameday Locker';
		$hide_locker = get_option('smarty_sameday_hide_locker', 'no');
	
		if ($hide_locker === 'yes') { return; }
		?>
		<div class="sameday-radio-buttons">
			<?php if ($enable_address === 'yes') : ?>
				<div class="radio-wrap sameday sameday-to-address <?php echo $should_check_address ? 'selected' : ''; ?>">
					<input type="checkbox" class="input-radio" value="Sameday Address" name="carrier_sameday" id="carrier_sameday_address"
						<?php checked($should_check_address); ?> />
					<label for="carrier_sameday_address" class="radio">
						<?php _e('To Address', 'smarty-sameday-lockers-locator'); ?>
					</label>
				</div>
			<?php endif; ?>
			<div class="radio-wrap sameday sameday-locker <?php echo $should_check_locker ? 'selected' : ''; ?>">
				<input type="checkbox" class="input-radio" value="Sameday Locker" name="carrier_sameday" id="carrier_sameday_locker"
					<?php checked($should_check_locker); ?> />
				<label for="carrier_sameday_locker" class="radio">
					<img src="<?php echo esc_url($logo_url); ?>" alt="Sameday Logo" width="110" />
				</label>
			</div>
		</div>
		<?php
	}		
	
	/**
	 * Function to handle AJAX request and update shipping method in session.
	 * 
	 * @since    1.0.0 
	 * @return   void
	 */
	public function update_shipping_method() {
		if (isset($_POST['shipping_method'])) {
			WC()->session->set('carrier_sameday', sanitize_text_field($_POST['shipping_method']));
			WC()->session->set('chosen_shipping_method', sanitize_text_field($_POST['shipping_method']));
		}
		wp_send_json_success('Shipping method updated');
	}

	/**
	 * Function to conditionally skip validation based on the chosen shipping method.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function conditionally_skip_validation() {
		$chosen_shipping_method = WC()->session->get('chosen_shipping_method');

		if ($chosen_shipping_method === 'Sameday Locker') {
			add_filter('woocommerce_checkout_fields', array($this, 'override_checkout_fields'));
		}
	}

	/**
	 * Function to override checkout fields and remove required attribute where needed.
	 * 
	 * @since    1.0.0
	 * @param    array    $fields    Array of all checkout fields.
	 * @return   array    Modified checkout fields.
	 */
	public function override_checkout_fields($fields) {
		unset($fields['billing']['billing_city']['required']);
		unset($fields['billing']['billing_address_1']['required']);

		// Additionally, conditionally unset the required attribute for country
		$chosen_shipping_method = WC()->session->get('chosen_shipping_method');

		if ($chosen_shipping_method === 'Sameday Locker') {
			unset($fields['billing']['billing_city']['required']);
			unset($fields['billing']['billing_address_1']['required']);
			unset($fields['billing']['billing_country']['required']);
		}

		return $fields;
	}

	/**
	 * Custom validation for the Sameday locker selection during WooCommerce checkout.
	 *
	 * This function checks if the "Sameday Locker" shipping option is selected and validates whether
	 * the Sameday locker field is filled out. If the field is empty, it adds an error notice to the checkout
	 * process, preventing the user from completing the checkout until an Sameday locker is selected.
	 * 
	 * @since    1.0.0
	 * @return   void
	 */
	public function sameday_locker_validation() {
		if (!isset($_POST['carrier_sameday'])) {
			return;
		}
	
		$carrier = sanitize_text_field($_POST['carrier_sameday']);
	
		if ($carrier === 'Sameday Locker' && empty($_POST['sameday_locker'])) {
			wc_add_notice(__('Please select a Sameday locker.', 'smarty-sameday-lockers-locator'), 'error');
		}
	}

	/**
	 * Calls the `query_sameday_lockers` method from the API instance.
	 *
	 * @since 1.0.0
	 * @return array Array of Sameday lockers.
	 */
	public function api_query_sameday_locker() {
		return $this->api_instance->query_sameday_lockers();
	}

	/**
	 * Retrieves Sameday lockers with caching mechanism.
	 * 
	 * @since    1.0.0 
	 * @param array $atts Attributes for the shortcode. Accepts 'country' to filter lockers by country code.
	 * @return string HTML content for the dropdown of lockers.
	 */
	public function get_sameday_lockers($atts) {
		global $wpdb;
		$table = $wpdb->prefix . 'smarty_sameday_lockers';
	
		// Get first available country if none specified
		$countries = smarty_get_available_countries();
		$country_name = $atts['country'] ?? ($countries[0] ?? '');
	
		if (empty($country_name)) {
			return '<p>' . __('No country found for lockers.', 'smarty-sameday-lockers-locator') . '</p>';
		}
	
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM {$table} WHERE country = %s ORDER BY city_name ASC, name ASC", $country_name),
			ARRAY_A
		);
	
		if (empty($results)) {
			return '<p>' . __('No lockers found.', 'smarty-sameday-lockers-locator') . '</p>';
		}
	
		$html = '<select id="sameday_locker" class="sameday-select-field" style="width: 100%; margin-bottom:20px;" name="sameday_locker">';
		$html .= '<option value="">' . esc_html__('Choose Sameday Locker', 'smarty-sameday-lockers-locator') . '</option>';
	
		foreach ($results as $val) {
			$html .= sprintf(
				'<option value="%s" data-city="%s" data-postcode="%s" data-address="%s" data-state="%s">%s</option>',
				esc_attr($val['locker_id']),
				esc_attr($val['city_name']),
				esc_attr($val['post_code']),
				esc_attr($val['full_address']),
				esc_attr($val['county']),
				esc_html($val['full_address']) . ' [' . esc_html($val['name']) . ']'
			);
		}		
	
		$html .= '</select>';
		return $html;
	}	

	/**
	 * Validates the Sameday lockers selection during the WooCommerce checkout process.
	 * 
	 * @since    1.0.0 
	 * @param array $fields Array of all checkout fields.
	 * @param WP_Error $errors WP_Error object for storing validation errors.
	 */
	public function validate_sameday_locker($fields, $errors) {
		// Checks if Sameday locker is selected when Sameday is chosen as the carrier
		if (preg_match('/LOCKER/', $fields['carrier']) && empty( $fields['billing_sameday_lockers'])) {
			$errors->add('validation', 'Please, choose the Sameday locker.');
		}
	}

	/**
	 * Adds an Sameday locker selection field to the WooCommerce checkout page.
	 * 
	 * @since    1.0.0 
	 * @param array $fields Checkout fields array.
	 * @return array Modified checkout fields with the Sameday locker field added.
	 */
	public function add_sameday_locker_field_to_checkout($fields) {
		if (!function_exists('WC') || is_admin() || !class_exists('WC_Session_Handler')) {
			return $fields;
		}
	
		// Ensure WooCommerce session is properly initialized
		if (!WC()->session || !method_exists(WC()->session, 'get')) {
			return $fields;
		}
	
		// Check if WooCommerce session is initialized properly
		if (WC()->session instanceof WC_Session_Handler) {
			$chosen_methods = WC()->session->get('chosen_shipping_methods');
			$chosen_shipping = is_array($chosen_methods) ? array_pop($chosen_methods) : '';

			$sameday_locker_field = array(
				'sameday_locker' => array(
					'type'        => 'select',
					'class'       => array('form-row-wide'),
					'label'       => __('Sameday Locker', 'smarty-sameday-lockers-locator'),
					'options'     => self::get_locker_options(),
					'required'    => false,
					'clear'       => true
				)
			);

			// Merge the new field at the beginning of the billing section
			$fields['billing'] = array_merge($sameday_locker_field, $fields['billing']);
		}

		return $fields;
	}

	/**
	 * Retrieves a list of Sameday lockers for dropdown options.
	 * 
	 * @since    1.0.0 
	 * @return array Associative array of locker options with locker code as key and address as value.
	 */
	public function get_locker_options() {
		global $wpdb;
		$table = $wpdb->prefix . 'smarty_sameday_lockers';
	
		// Get the first country available
		$countries = smarty_get_available_countries();
		$country_name = $countries[0] ?? '';
	
		if (empty($country_name)) {
			return array('' => __('No lockers available.', 'smarty-sameday-lockers-locator'));
		}
	
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM {$table} WHERE country = %s ORDER BY city_name ASC, name ASC", $country_name),
			ARRAY_A
		);
	
		$options = array();
		$options[''] = __('Choose Sameday Locker', 'smarty-sameday-lockers-locator');
	
		foreach ($results as $locker) {
			$options[esc_attr($locker['locker_id'])] = esc_html("{$locker['full_address']} [{$locker['name']}]");
		}
	
		return $options;
	}	

	/**
	 * Display the selected Sameday Locker on the thank you page.
	 *
	 * @since 1.0.0
	 * @param int $order_id
	 */
	public function display_locker_on_thank_you($order_id) {
		if (!$order_id) {
			return;
		}

		$order = wc_get_order($order_id);
		$sameday_selected_option = get_post_meta($order_id, '_sameday_selected_option', true);

		if ($sameday_selected_option === 'Sameday Locker') {
			$sameday_locker_details = get_post_meta($order_id, '_sameday_locker_details', true);
			$locker_details = maybe_unserialize($sameday_locker_details);

			if (!empty($locker_details) && is_array($locker_details)) {
				$locker_info = sprintf(
					'<strong>[%s]</strong>: %s (%s)',
					esc_html($locker_details['name'] ?? __('Unknown Name', 'smarty-sameday-lockers-locator')),
					esc_html($locker_details['full_address'] ?? __('Unknown Address', 'smarty-sameday-lockers-locator')),
					esc_html($locker_details['locker_id'] ?? __('Unknown ID', 'smarty-sameday-lockers-locator'))
				);
				?>
				<section class="woocommerce-order-details sameday-locker-info" style="
					margin-top: 20px;
					background-color: #edffeb; 
					border: 1px solid #bde5b9; 
					border-radius: 3px; 
					color: #333333;
					padding: 10px;
					display: flex; 
					flex-flow: column;
				">
					<span style="padding: 5px;"><?php echo $locker_info; ?></span>
				</section>
				<?php
			}
		}
	}
}