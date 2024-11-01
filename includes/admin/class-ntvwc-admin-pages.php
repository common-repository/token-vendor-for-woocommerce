<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Admin_Pages' ) ) {
/**
 * NTVWC Guide Page
 * 
**/
class NTVWC_Admin_Pages extends NTVWC_Unique {

	#
	# Vars
	#
		/**
		 * Instance of Self
		 * 
		 * @var Self
		**/
		protected static $instance = null;

	#
	# Init
	#
		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @return Self
		**/
		public static function get_instance()
		{

			// Init if not yet
			if ( null === self::$instance ) {
				self::$instance = new Self();
			}

			// End
			return self::$instance;

		}

		/**
		 * Constructor
		**/
		function __construct()
		{

			// Setup
			$this->init_hooks();

		}

		/**
		 * Init Hooks
		**/
		protected function init_hooks()
		{

			#
			# Actions
			#
				// Submenu Page
				add_action( 'admin_menu', array( $this, 'add_admin_page' ), 100 );

				// Enqueue Scripts
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

				// Tools
				add_action( 'admin_menu', array( $this, 'tool_download_ntvwc_client' ), 10 );

		}

	#
	# Actions
	#
		/**
		 * Add Admin Page
		**/
		public function add_admin_page()
		{

			if ( current_user_can( 'manage_woocommerce' ) ) {

				// Admin Page
				add_submenu_page(
					'woocommerce', // WooCommerce shop_order
					esc_html__( 'NTVWC', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), 
					esc_html__( 'NTVWC', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), 
					'manage_options', 
					'ntvwc_admin_page', 
					array( $this, 'render_admin_page' )
				);

			}

		}

		/**
		 * Render Admin Page
		**/
		public function render_admin_page()
		{

			// Load Template
				ob_start();
					require_once( NTVWC_DIR_PATH . 'templates/admin-page/template-admin-page.php' );
				$guide_page = ob_get_clean();
				echo apply_filters( 'ntvwc_filter_html_guide_page', $guide_page );

		}

		/**
		 * Render Admin Page
		**/
		public function render_guide_page()
		{

			// Load Template
				ob_start();
					require_once( NTVWC_DIR_PATH . 'templates/admin-page/template-admin-guide-page.php' );
				$guide_page = ob_get_clean();
				echo apply_filters( 'ntvwc_filter_html_guide_page', $guide_page );

		}

		/**
		 * Render Admin Page
		**/
		public function render_setting_page()
		{

			// Load Template
				ob_start();
					require_once( NTVWC_DIR_PATH . 'templates/admin-page/template-admin-setting-page.php' );
				$setting_page = ob_get_clean();
				echo apply_filters( 'ntvwc_filter_html_setting_page', $setting_page );

		}

		/**
		 * Render Admin Page
		**/
		public function render_tool_page()
		{

			// Load Template
				ob_start();
					require_once( NTVWC_DIR_PATH . 'templates/admin-page/template-admin-tool-page.php' );
				$tool_page = ob_get_clean();
				echo apply_filters( 'ntvwc_filter_html_tool_page', $tool_page );

		}

		/**
		 * Enqueue Scripts
		**/
		public function admin_enqueue_scripts( $hook )
		{

			// Check the URL Request Params
			if ( ! isset( $_GET['page'] ) 
				|| ! in_array( $_GET['page'], array(
					'ntvwc_admin_page',
				) )
			) {
				return false;
			}

			wp_enqueue_style( 'ntvwc-admin-menu-pages-css' );

			// Setting
			if ( isset( $_GET['page'] ) 
				&& in_array( $_GET['page'], array( 'ntvwc_admin_page' ) )
			) {
				//wp_enqueue_style( 'ntvwc-admin-setting-page-style' );
				wp_enqueue_script( 'ntvwc-admin-setting-page-js' );
			}

			// Tool
			elseif ( isset( $_GET['page'] ) 
				&& ! in_array( $_GET['page'], array( 'ntvwc_admin_page' ) )
			) {
				//wp_enqueue_style( 'ntvwc-admin-setting-page-style' );
				wp_enqueue_script( 'ntvwc-admin-setting-page-js' );
			}

		}

		/**
		 * Download update checker in tool tab
		 * @uses string $_POST['ntvwc-download-ntvwc-client-nonce'] description
		 * @uses string $_POST['button-download-ntvwc-client'] description
		**/
		public function tool_download_ntvwc_client()
		{

			if ( ! isset( $_POST['button-download-ntvwc-client'] )
				|| ! isset( $_POST['ntvwc-download-ntvwc-client-nonce'] )
			) {
				return;
			}

			check_admin_referer( 'ntvwc-download-ntvwc-client', 'ntvwc-download-ntvwc-client-nonce' );

			$directory = apply_filters( 'ntvwc_filter_ntvwc_client_folder', NTVWC_DIR_PATH . 'includes/3rd/ntvwc-client' );
			$file_name = apply_filters( 'ntvwc_filter_ntvwc_client_file_name', 'ntvwc-client.zip' );
			NTVWC_File_System_Methods::download_zip_from_directory( $directory, $file_name, null );

			wp_die();

		}

}

}

