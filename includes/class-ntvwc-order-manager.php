<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// JWT
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha512;



if ( ! class_exists( 'NTVWC_Order_Manager' ) ) {
/**
 * 
 * 
**/
class NTVWC_Order_Manager extends NTVWC_Unique {

	#
	# Properties
	#
		#
		# Public
		#

		#
		# Protected
		#
			/**
			 * JWT Holder
			 * 
			 * @var array
			**/
			protected $jwt_holder = array();

	#
	# Vars
	#
		#
		# Public
		#

		#
		# Protected
		#
			/**
			 * Instance of Self
			 * 
			 * @var Self
			**/
			protected static $instance = null;

	#
	# Settings
	#


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

			// Init hooks
			$this->init_hooks();

		}

		/**
		 * Init WP hooks
		**/
		protected function init_hooks()
		{

			// Actions

			// Filters
				/**
				 * WC action hook "woocommerce_grant_product_download_permissions"
				 * at the end of the function "wc_downloadable_product_permissions"
				 * 
				 * @param int $order_id
				**/
				add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'woocommerce_order_item_needs_processing' ), 10, 3 );

		}

		/**
		 * Hooked in the WC action "woocommerce_grant_product_download_permissions"
		 * at the end of the function "wc_downloadable_product_permissions"
		 * 
		 * @param bool       $not_virtual_downloadable_item
		 * @param WC_Product $product
		 * @param int        $order_id
		**/
		public function woocommerce_order_item_needs_processing( $not_virtual_downloadable_item, $product, $order_id )
		{

			if ( false === $not_virtual_downloadable_item ) {
				return $not_virtual_downloadable_item;
			}

			$_ntvwc_type_token = get_post_meta( $product->get_id(), '_ntvwc_type_token', true );
			if ( 'yes' === $_ntvwc_type_token && $product->is_virtual() ) {
				return false;
			}

			try {
				$ntvwc_order = new NTVWC_Order();
			} catch ( Exception $e ) {
				
			}

			// User
			$user_id  = intval( $ntvwc_order->get_user_id( $context = 'view' ) );
			if ( 0 >= $user_id ) {
				ntvwc_notice_message( ntvwc_current_file_and_line( 1, false ) );
				ntvwc_notice_message( 'by the hook "woocommerce_grant_product_download_permissions" in the function "wc_downloadable_product_permissions".' . PHP_EOL );
				return true;
			}

			return $not_virtual_downloadable_item;

		}


}
}
