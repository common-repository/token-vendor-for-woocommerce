<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'NTVWC_Admin' ) ) {
/**
 * Admin Class
 * 
 * 
**/
class NTVWC_Admin extends NTVWC_Unique {

	#
	# Properties
	#
		/**
		 * Admin pages
		 * 
		 * @var NTVWC_Admin_Pages
		**/
		public $admin_pages = null;

		/**
		 * Notices
		 * 
		 * @var NTVWC_Notices
		**/
		public $notices = null;

		/**
		 * Instance of NTVWC_User_Meta
		 * 
		 * @var NTVWC_User_Meta
		**/
		public $user_meta_manager = null;

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
		protected function __construct()
		{

			/**
			 * Instance of NTVWC_Admin_Pages
			 * 
			 * @var NTVWC_Admin_Pages
			**/
			$this->admin_pages = NTVWC_Admin_Pages::get_instance( 'ntvwc' );

			/**
			 * Instance of NTVWC_Product_Metabox
			 * 
			 * @var NTVWC_Product_Metabox
			**/
			$this->product_metabox = NTVWC_Product_Metabox::get_instance();

			/**
			 * Instance of NTVWC_Order_Metabox
			 * 
			 * @var NTVWC_Order_Metabox
			**/
			$this->product_metabox = NTVWC_Order_Metabox::get_instance();

			// Init WP hooks
			$this->init_hooks();

		}

		/**
		 * Init WP hooks
		**/
		protected function init_hooks()
		{

		}

}
}

