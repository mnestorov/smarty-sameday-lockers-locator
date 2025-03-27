<?php

/**
 * The core plugin class.
 *
 * This is used to define attributes, functions, internationalization used across
 * both the admin-specific hooks, and public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/includes/classes
 * @author     Smarty Studio | Martin Nestorov
 */
class Smarty_Sameday_Locator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks
	 * that power the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Smarty_Sameday_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function __construct() {
		if (defined('SAMEDAY_LOCATOR_VERSION')) {
			$this->version = SAMEDAY_LOCATOR_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'smarty_sameday_lockers_locator';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smarty_Sameday_Loader. Orchestrates the hooks of the plugin.
	 * - Smarty_Sameday_i18n. Defines internationalization functionality.
	 * - Smarty_Sameday_Admin. Defines all hooks for the admin area.
	 * - Smarty_Sameday_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   void
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-smarty-sameday-loader.php';

		/**
		 * The class responsible for defining internationalization functionality of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-smarty-sameday-i18n.php';

		/**
		 * The class responsible for interacting with the Sameday API.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'classes/class-smarty-sameday-api.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . '../admin/class-smarty-sameday-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . '../public/class-smarty-sameday-public.php';

		// Run the loader
		$this->loader = new Smarty_Sameday_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smarty_Sameday_I18n class in order to set the domain and to
	 * register the hook with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   void
	 */
	private function set_locale() {
		$plugin_i18n = new Smarty_Sameday_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   void
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Smarty_Sameday_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
		$this->loader->add_action('admin_init', $plugin_admin, 'settings_init');
		$this->loader->add_action('admin_init', $plugin_admin, 'schedule_updates');
		$this->loader->add_action('admin_notices', $plugin_admin, 'sameday_success_notice');
		$this->loader->add_action('smarty_sameday_auto_updater', $plugin_admin, 'update_sameday_lockers_event');
		$this->loader->add_action('wp_ajax_smarty_trigger_sameday_update', $plugin_admin, 'handle_manual_update');
		$this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_admin, 'save_sameday_selection');
		$this->loader->add_action('woocommerce_admin_order_data_after_order_details', $plugin_admin, 'display_sameday_selection_in_admin', 10, 1);
		$this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_admin, 'save_sameday_locker');
		$this->loader->add_filter('woocommerce_order_formatted_billing_address', $plugin_admin, 'filter_formatted_address_output', 10, 2);
		$this->loader->add_filter('woocommerce_order_formatted_shipping_address', $plugin_admin, 'filter_formatted_address_output', 10, 2);
		$this->loader->add_action('admin_head', $plugin_admin, 'hide_shipping_address_block');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   void
	 */
	private function define_public_hooks() {
		$plugin_public = new Smarty_Sameday_Public($this->get_plugin_name(), $this->get_version());
		
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('woocommerce_before_checkout_billing_form', $plugin_public, 'add_sameday_radio_buttons');
		$this->loader->add_action('wp_ajax_update_shipping_method', $plugin_public, 'update_shipping_method');
		$this->loader->add_action('woocommerce_checkout_process', $plugin_public, 'custom_sameday_locker_validation');
		$this->loader->add_action('wp_ajax_nopriv_update_shipping_method', $plugin_public, 'update_shipping_method');
		$this->loader->add_action('woocommerce_checkout_process', $plugin_public, 'conditionally_skip_validation');
		$this->loader->add_action('init', $plugin_public, 'register_shortcodes');
		$this->loader->add_action('woocommerce_after_checkout_validation', $plugin_public, 'validate_sameday_locker', 10, 2);
		$this->loader->add_filter('woocommerce_checkout_fields', $plugin_public, 'add_sameday_locker_field_to_checkout');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    Smarty_Sameday_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
