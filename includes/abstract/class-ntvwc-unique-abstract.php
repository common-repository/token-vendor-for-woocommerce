<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'NTVWC_Unique' ) ) {
/**
 * Class which should be initialized only once
**/
class NTVWC_Unique {

	#
	# Statics
	#
		/**
		 * Instance of the Class
		 * 
		 * @var object WCYSS
		**/
		protected static $instance = null;

	#
	# Settings
	#
		/**
		 * Cloning is forbidden.
		 * @since 1.0.0
		 */
		public function __clone()
		{
			ntvwc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Clone.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0.0
		 */
		public function __wakeup() {
			ntvwc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Unserialize', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

}
}

