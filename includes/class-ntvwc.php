<?php

// Define Class Nora_Token_Vendor_For_WooCommerce
if ( ! class_exists( 'Nora_Token_Vendor_For_WooCommerce' ) ) {
/**
 * Token Vendor for WooCommerce
**/
final class Nora_Token_Vendor_For_WooCommerce {

	#
	# Consts
	#
		/**
		 * Unique key to be used for prefixes
		**/
		const PLUGIN_NAME      = 'Token Vendor for WooCommerce';
		const PLUGIN_VERSION   = '0.1.11';
		const UNIQUE_KEY       = 'ntvwc';
		const UPPER_UNIQUE_KEY = 'NTVWC';

		// For update checker
		const TEXTDOMAIN       = 'ntvwc';
		const PLUGIN_DIR_NAME  = 'token-vendor-for-woocommerce';

		// For Files
		const UPLOAD_DIR       = 'wp-content/uploads/ntvwc';

	#
	# Properties
	#
		#
		# Public
		#
			/**
			 * Instance of NTVWC_Translatable_Texts 
			 * 
			 * @var [NTVWC_Translatable_Texts]
			**/
			public $texts;

			/**
			 * Instance of NTVWC_Option_Manager 
			 * 
			 * @var [NTVWC_Option_Manager]
			**/
			public $option_manager;

			/**
			 * Instance of NTVWC_Admin 
			 * 
			 * @var [NTVWC_Admin]
			**/
			public $admin;

			/**
			 * Instance of WCYSS_Notices
			 * 
			 * @var [NTVWC_Notices]
			**/
			public $notices;

			/**
			 * Instance of NTVWC_Order_Manager 
			 * 
			 * @var [NTVWC_Order_Manager]
			**/
			public $order_manager;

			/**
			 * Instance of NTVWC_Token_Manager 
			 * 
			 * @var [NTVWC_Token_Manager]
			**/
			public $token_manager;

			/**
			 * Instance of NTVWC_Notification_Manager 
			 * 
			 * @var [NTVWC_Notification_Manager]
			**/
			public $notification_manager;

			/**
			 * Instance of NTVWC_Client 
			 * 
			 * @var [NTVWC_Client]
			**/
			public $ntvwc_client;

		#
		# Private
		#

	#
	# Statics
	#
		/**
		 * Instance of the Class
		 * 
		 * @var [Nora_Token_Vendor_For_WooCommerce]
		**/
		private static $instance;

	#
	# Settings
	#
		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone()
		{
			ntvwc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Clone.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup()
		{
			ntvwc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Unserialize', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

	#
	# Tools
	#
		/**
		 * Define Constant
		 * 
		 * @param string $name
		 * @param bool|int|string
		 * 
		 * @return bool
		**/
		private function define_const( $name, $value )
		{

			// Check Name
			if( empty( $name ) || ! is_string( $name ) ) {
				return false;
			}

			// Check Value
			if( ! isset( $value ) || is_array( $value ) || is_object( $value ) ) {
				return false;
			}

			// Exec
			if( ! defined( $name ) ) {
				define( $name, $value );
				return true;
			}

			return false;

		}

		/**
		 * Make Directory
		 * @param string $directory : Directory String to Make
		 * @return bool             :
		**/
		private function make_directory( $directory )
		{

			// Case : dir not exist
			if( ! is_dir( $directory ) ) {

				// Dir Check
				if( ! $this->make_directory( dirname( $directory ) ) ) {
					return false;
				}

				// Make Directory
				if( ! mkdir( $directory, 0755 ) ) {
					return false;
				}

			}
			
			// End
			return true;

		}

		/**
		 * Returns the key
		 * @uses  [string] self::UNIQUE_KEY
		 * @return [string]
		**/
		public function get_prefix_key()
		{
			return self::UNIQUE_KEY;
		}

		/**
		 * Public Initializer
		 * @return NTVWC_Option_Manager
		**/
		public function get_option_manager()
		{

			// Init if not yet
			return $this->option_manager;

		}

		/**
		 * Public Initializer
		 * @return NTVWC_Translatable_Texts
		**/
		public function get_tramslatable_texts()
		{

			// Init if not yet
			return $this->texts;

		}

		/**
		 * Public Initializer
		 * @return NTVWC_Order_Manager
		**/
		public function get_order_manager()
		{

			// Init if not yet
			return $this->order_manager;

		}

		/**
		 * Public Initializer
		 * @return NTVWC_Token_Manager
		**/
		public function get_token_manager()
		{

			// Init if not yet
			return $this->token_manager;

		}

		/**
		 * Public Initializer
		 * @return NTVWC_Notification_Manager
		**/
		public function get_notification_manager()
		{

			// Init if not yet
			return $this->notification_manager;

		}

		/**
		 * Public Initializer
		 * @return NTVWC_Token_Manager
		**/
		public function get_ntvwc_client()
		{

			// Init if not yet
			return $this->ntvwc_client;

		}

	#
	# Activation
	#
		/**
		 * Activation
		**/
		public function activate()
		{

			$this->upload_dir = ABSPATH . Nora_Token_Vendor_For_WooCommerce::UPLOAD_DIR;
			$this->logs_dir   = $this->upload_dir . '/logs';

			$this->make_directory( $this->upload_dir );
			$this->make_directory( $this->logs_dir );

			$this->includes();
			$endpoint_purchased_token = NTVWC_Endpoint_Purchased_Tokens::install();
			flush_rewrite_rules();

		}

		public static function install() {
			
		}

	#
	# Deactivation
	#
		/**
		 * Deactivation
		**/
		public function deactivate()
		{
			
		}

	#
	# Init
	#
		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @return Nora_Token_Vendor_For_WooCommerce
		**/
		public static function get_instance()
		{

			// Init if not yet
			if ( null === self::$instance ) {
				self::$instance = new Nora_Token_Vendor_For_WooCommerce;
			}

			// End
			return self::$instance;

		}

		/**
		 * Constructor
		**/
		protected function __construct()
		{

			// Activate
			register_activation_hook( NTVWC_MAIN_FILE, array( $this, 'activate' ) );

			// Deactivate
			register_deactivation_hook( NTVWC_MAIN_FILE, array( $this, 'deactivate' ) );

			// Check if site has WooCommerce
			// Then, Start setup
			// Define Constants and Check if WCYSS is Working
			add_action( 'plugins_loaded', array( $this, 'define_requirements' ), 0 );

		}

		/**
		 * Define variables
		 * 		First, Check if WooCommerce is active
		 * 		Second, Start
		**/
		public function define_requirements()
		{

			// Check if WooCommerce is active
			if ( ! function_exists( 'wc' ) ) {
				return;
			}

			// Text Domain
			load_plugin_textdomain(
				Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN,
				false,
				//'ntvwc/i18n/languages'
				plugin_basename( dirname( NTVWC_MAIN_FILE ) ) . '/i18n/languages'
			);

			// Define Vars
				$this->define_const( 'NTVWC_ASSET_CSS_URI', NTVWC_DIR_URL . 'assets/css/' );
				$this->define_const( 'NTVWC_ASSET_JS_URI', NTVWC_DIR_URL . 'assets/js/' );
				$this->define_const( 'NTVWC_ASSET_IMG_URI', NTVWC_DIR_URL . 'assets/img/' );

			// Include files
			$this->includes();

			// Init classes
			$this->init_classes();

			// Init WP hooks
			$this->init_hooks(); 

		}

		/**
		 * Include required files
		**/
		protected function includes()
		{

			// Include files
			require_once( NTVWC_DIR_PATH . 'includes/exec/include/required-files.php' );

		}

		/**
		 * Init Classes
		 * 		should be after 'plugins_loaded'
		 * 
		 * @usedby $this->define_requirements()
		 * 
		 * @return bool Returns false if this not 
		**/
		protected function init_classes()
		{
			/**
			 * Instance of NTVWC_Translatable_Texts
			 * 
			 * @var NTVWC_Translatable_Texts 
			**/
			$this->texts = NTVWC_Translatable_Texts::get_instance();

			/**
			 * Instance of NTVWC_Option_Manager
			 * 
			 * @var NTVWC_Option_Manager 
			**/
			$this->option_manager = NTVWC_Option_Manager::get_instance();
			$this->option_manager->reset_options();
			$this->option_manager->init_hooks();

			/**
			 * Instance of NTVWC_Notification_Manager
			 * 
			 * @var NTVWC_Notification_Manager 
			**/
			$this->endpoint_purchased_token = NTVWC_Endpoint_Purchased_Tokens::get_instance();

			/**
			 * Instance of NTVWC_Notification_Manager
			 * 
			 * @var NTVWC_Notification_Manager 
			**/
			$this->notification_manager = NTVWC_Notification_Manager::get_instance();

			/**
			 * Instance of NTVWC_Order_Manager
			 * 
			 * @var NTVWC_Order_Manager 
			**/
			$this->order_manager = NTVWC_Order_Manager::get_instance();

			/**
			 * Instance of NTVWC_Token_Manager
			 * 
			 * @var NTVWC_Token_Manager 
			**/
			$this->token_manager = NTVWC_Token_Manager::get_instance();

			/**
			 * Init extensions which is activated
			**/
			//do_action( 'ntvwc_includes_extensions', $this );

			// NTVWC_REST_API
			//$this->rest_api = NTVWC_REST_API_Loader::load( 'basic' );
			//$this->rest_api->run();

			// NTVWC_REST_API
				$this->rest_api = NTVWC_REST_API_Loader::load( 'purchased_token' );
				$this->rest_api->run();

			/**
			 * Instance of NTVWC_Admin
			 * 
			 * @var NTVWC_Admin 
			**/
			$this->admin = NTVWC_Admin::get_instance();

			do_action( 'ntvwc_init_classes', $this );

		}

		/**
		 * Init WP hooks
		 * 		should be after 'plugins_loaded'
		 * 
		 * @usedby $this->define_requirements()
		 * 
		 * @return bool Returns false if this not 
		**/
		private function init_hooks()
		{

			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 9 );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 9 );
			add_action( 'customize_preview_init', array( $this, 'register_scripts' ), 9 );
			add_action( 'customize_controls_print_footer_scripts', array( $this, 'register_scripts' ), 9 );

		}

	#
	# Actions
	#
		/**
		 * Register CSS and JS
		 * @return void
		 */
		public function register_scripts()
		{

			// CSS
				// Admin setting page
				wp_register_style(
					'ntvwc-admin-pages-css',
					NTVWC_ASSET_CSS_URI . 'ntvwc-admin-page.css'
				);

				// Admin setting page
				wp_register_style(
					'ntvwc-product-settings-css',
					NTVWC_ASSET_CSS_URI . 'ntvwc-product-settings.css'
				);

				// Client dashboard page
				wp_register_style(
					'ntvwc-customer-downloads-css',
					NTVWC_ASSET_CSS_URI . 'ntvwc-customer-downloads.css'
				);

			// JS
				// Base: Init var ntvwc
				wp_register_script(
					'ntvwc-base-js',
					NTVWC_ASSET_JS_URI . 'ntvwc.js',
					array( 'jquery', 'jquery-ui-resizable', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'jquery-ui-draggable', 'underscore', 'backbone' ),
					false,
					true
				);

				// Admin setting page
				wp_register_script(
					'ntvwc-admin-setting-page-js',
					NTVWC_ASSET_JS_URI . 'ntvwc-admin-setting-page.js',
					array( 'ntvwc-base-js' ),
					false,
					true
				);

				// Product settings
				wp_register_script(
					'ntvwc-product-settings-js',
					NTVWC_ASSET_JS_URI . 'product-settings.js',
					array( 'ntvwc-base-js' ),
					false,
					true
				);

				// Customer downloads
				wp_register_script(
					'ntvwc-customer-downloads-js',
					NTVWC_ASSET_JS_URI . 'customer-downloads.js',
					array( 'ntvwc-base-js' ),
					false,
					true
				);

		}

	#
	# Filters
	#

} // End Closure of the Class

}






