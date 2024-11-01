<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'NTVWC_Notification_Manager' ) ) {
/**
 * Notices to be printed by WP Hook 'admin_notices'
 * 
 * Email with
 * 
 * 
**/
class NTVWC_Notification_Manager {

	#
	# Properties
	#
		/**
		 * Email Handler
		 * 
		 * @var NTVWC_Mail
		**/
		protected $mail = null;

		/**
		 * NTVWC_Post_Type_Notification Handler
		 * 
		 * @var NTVWC_Post_Type_Notification
		**/
		protected $post_type = null;

	#
	# Static Vars
	#
		/**
		 * Instance
		 * 
		 * @var object $instance
		**/
		private static $instance = null;

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
		 * Initializer Called by "get_instance"
		 * 
		 * @param array $args
		 * 
		 * @uses $this->init_hooks()
		**/
		protected function __construct( $args = array() )
		{

			// NTVWC_Post_Type_Notification
			$this->post_type = NTVWC_Post_Type_Notification::get_instance();

			// Init WP hooks
			$this->init_hooks();

		}

		/**
		 * Initialize Hooks
		**/
		protected function init_hooks()
		{

			// Print notice
			add_action( 'all_admin_notices', array( $this, 'admin_notices' ) );

		}

		/**
		 * Print Notice
		**/
		public function admin_notices()
		{

			// Check the User Cap
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}
		}

}
}
