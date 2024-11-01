<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




if ( ! class_exists( 'NTVWC_Token_Manager' ) ) {
/**
 * 
 * 
**/
class NTVWC_Token_Manager extends NTVWC_Unique {

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

			/**
			 * JWT Holder
			 * 
			 * @var array
			**/
			protected $post_type_token = null;

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

			$this->post_type_token = NTVWC_Post_Type_Token::get_instance();

			// Init hooks
			$this->init_hooks();

		}

		/**
		 * Init WP hooks
		**/
		protected function init_hooks()
		{

			// Actions
				/**
				 * WC action hook "woocommerce_grant_product_download_permissions"
				 * at the end of the function "wc_downloadable_product_permissions"
				 * 
				 * @param int $order_id
				**/
				add_action( 'woocommerce_order_status_completed', array( $this, 'generate_tokens_by_order_id' ), 10, 1 );

				/**
				 * Complete
				 * 
				 * @param Token  $token_obj
				 * @param array  $params
				 * @param string $update_token
				 * 
				 * @return Token
				**/
				add_filter( 'ntvwc_filter_rest_api_maybe_update_token', array( $this, 'maybe_update_token' ), 10, 3 ); 

		}

	#
	# Actions
	#
		/**
		 * Hooked in "woocommerce_order_status_completed"
		 * @param int|bool $order_id
		**/
		public function generate_tokens_by_order_id( $order_id )
		{

			if ( 'woocommerce_order_status_completed' !== current_filter() ) {
				return false;
			}

			// NTVWC_Order will check if the order has token items
			$ntvwc_order = new NTVWC_Order( intval( $order_id ) );
			$result = $ntvwc_order->generate_tokens();
			if ( false !== $result ) {
				$customer_id = $ntvwc_order->get_customer_id();
				if ( 0 >= intval( $customer_id ) ) {
					$to = $ntvwc_order->get_billing_email();
					$subject = sprintf( 
						esc_html__( '%1$s - Your Purchased Tokens', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						get_bloginfo( 'name' )
					);

					$messages = array();
					$token_id_holder = ntvwc_get_post_meta( $order_id, '_ntvwc_registered_token_ids' );

					if ( is_array( $token_id_holder ) && 0 < count( $token_id_holder ) ) {
					foreach ( $token_id_holder as $product_id => $token_ids ) {

						if ( ! is_array( $token_ids ) || 0 >= count( $token_ids ) ) {
							continue;
						}

						$wc_product= WC()->product_factory->get_product( $product_id );
						$token_product_name = $wc_product->get_name();
						$index = 1;
						foreach( $token_ids as $token_id ) {
							$data_token = NTVWC_Data_Token::get_instance( $token_id );
							$message = sprintf( 
								'%1$s - %2$d: "%3$s"',
								$token_product_name,
								$index,
								$data_token->get_the_latest_purchased_token()
							);
							array_push( $messages, $message );
							$index++;
						}
					}
					}

					$result = wp_mail(
						$to,
						$subject,
						implode( PHP_EOL . PHP_EOL, $messages )
					);
				}
			}

			return $result;

		}

		/**
		 * 
		 * Maybe update token if 
		 * 
		 * @param Token  $validation_token_obj
		 * @param array  $params
		 * @param string $update_token
		 * 
		 * @return string|Token : Returns string for error
		**/
		public function maybe_update_token( $validation_token_obj, $update_token_obj, $params = array() )
		{

			if ( 'ntvwc_filter_rest_api_maybe_update_token' !== current_filter() ) {
				return $validation_token_obj;
			}

			// Init Validation Token Handler
			try {

				// Validate
					$validation_token_handler = new NTVWC_Token_Handler( $validation_token_obj );
				// Check if the token has expiry
					if ( ! $validation_token_handler->has_expiry() ) {
						throw new NTVWC_Exception( esc_html__( 'Validation Token has no expiry.' ) );
					}

				// Check if the request recieved update token
					if ( ! is_string( $update_token_obj->__toString() ) 
						|| '' === $update_token_obj->__toString()
					) {
						throw new NTVWC_Exception( esc_html__( 'Update Token is empty.' ) );
					}

				// Init Update Token Handler
					$update_token_handler = new NTVWC_Token_Handler( $update_token_obj );
					$update_token_obj     = $update_token_handler->validate();

				// Extend the Expiry
					$new_validation_token = $validation_token_handler->extend_expiry( $update_token_handler );
					if ( is_string( $new_validation_token ) ) {
						throw new NTVWC_Exception( $new_validation_token );
					}

					$result = $update_token_handler->invalidate_token( $validation_token_handler );
					if ( ! $result ) {
						throw new NTVWC_Exception( esc_html__( 'Invalidating failed.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
					}

			} catch( NTVWC_Exception $e ) {
				return $validation_token_handler->get_token();
			} catch( Exception $e ) {
				return $validation_token_handler->get_token();
			}

			return $new_validation_token;

		}

		/**
		 * Validate token except the expires.
		 * 		Order ID
		 *   	Product ID
		 *    	Signers
		 *     	Token Index
		 *      Indexed Token
		 * @param string|Token $token_obj
		 * @param string       $type : 'variation' 'update'
		 * @return string|Token
		**/
		public function validate_token( $token_obj, $type = 'validation' )
		{

			try {
				//
				$token_handler = new NTVWC_Token_Handler( $token_obj );
				$token_obj = $token_handler->validate( $token_handler->get_token() );

				// Prepare var $token from ID and some params
				if ( is_string( $token_obj ) ) {
					throw new NTVWC_Exception( 'Invalid Token.' );
				}

			} catch ( NTVWC_Exception $e ) {
				return $e->getMessage();
			} catch ( Exception $e ) {
				return $e->getMessage();
			}

			// End
				return $token_obj;

		}

		/**
		 * Validate token expiry
		 * @param string|Token $token_obj
		 * @return string|Token
		**/
		public function validate_expiry( $token_obj )
		{

			// Prepare var $token from ID and some params
				if ( is_string( $token_obj ) && '' !== $token_obj ) {
					$token_obj = NTVWC_Token_Methods::parse_from_string( $token_obj );
				} elseif ( is_string( $token_obj ) && '' === $token_obj ) {
					return esc_html__( 'Token is invalid string.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				}

			// Expiry
				$access_expiry = intval( $token_obj->hasClaim( 'access_expiry' ) ? $token_obj->getClaim( 'access_expiry' ) : -1 );
				if ( -1 !== $access_expiry
					&& $access_expiry < current_time( 'timestamp', true ) + 60
				) {

					// Messages
						$token_expired_message = esc_html__( 'Token is expired.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
						$error_messages = array(
							$token_expired_message
						);

					// End
						return implode( '<br>', $error_messages );

				}
			// End
				return $token_obj;

		}

		/**
		 * Validate token value
		 * @param string|Token $token_obj
		 * @return string|Token
		**/
		public function validate_value( $token_obj, $hashed_value )
		{

			try {

				// Prepare var $token from ID and some params
					if ( is_string( $token_obj ) && '' !== $token_obj ) {
						$token_obj = NTVWC_Token_Methods::parse_from_string( $token_obj );
					} elseif ( is_string( $token_obj ) && '' === $token_obj ) {
						return esc_html__( 'Token is invalid string.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
					}

				// Vars
					$token_handler = new NTVWC_Token_Handler( $token_obj );
					$order_id      = intval( $token_handler->get_token()->getClaim( 'order_id' ) );
					$product_id    = intval( $token_handler->get_token()->getClaim( 'product_id' ) );

				// Data
					$token_index = $token_handler->get_the_token_index( $token_handler );
					$used_value  = $token_handler->get_the_used_token_value( $product_id, $token_index );

			} catch ( NTVWC_Exception $e ) {
				return sprintf( 
					esc_html__( 'Error: %s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
					$e->getMessage()
				);
			} catch ( Exception $e ) {
				return sprintf( 
					esc_html__( 'Error: %s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
					$e->getMessage()
				);
			}

			// Check
				if ( password_verify( $used_value, $hashed_value ) ) {
					return $token_obj;
				}

				return esc_html__( 'Value does not match.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );


		}
}
}
