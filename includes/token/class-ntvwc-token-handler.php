<?php

// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Token_Handler' ) ) {
/**
 * Data
 * 
 * 
**/
class NTVWC_Token_Handler {

	/**
	 * Token initialized
	**/
	protected $is_token_init = false;

	/**
	 * Token initialized
	**/
	protected $is_the_latest = true;

	/**
	 * Token Object
	**/
	protected $token;

	/**
	 * Token Type
	**/
	protected $type = 'validation';

	/**
	 * NTVWC_Token_Validator
	**/
	protected $validator = null;

	/**
	 * NTVWC_Order
	**/
	public $ntvwc_order = null;

	/**
	 * WC_Product
	**/
	public $wc_product = null;

	/**
	 * NTVWC_Data_Token
	**/
	public $data_token = null;




	/**
	 * Flag if validating token is done or not
	**/
	protected $validating_token_done = false;

	/**
	 * Flag if validating expiry is done or not
	**/
	protected $validating_expiry_done = false;

	/**
	 * Getters
	**/
		/**
		 * General
		**/
			/**
			 * Get Token Object
			 * @return Token
			**/
			public function get_token()
			{

				$this->token;

				$token = $this->data_token->get_the_latest_purchased_token();
				try {
					$token_obj = NTVWC_Token_Methods::parse_from_string( $token );
				} catch ( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				return $token_obj;
			}

			/**
			 * Get Token Type
			 * @return string
			**/
			public function get_type()
			{
				return $this->type;
			}

		/**
		 * Validation Token
		**/
			/**
			 * Has Expiry
			 * @return bool
			**/
			public function has_expiry()
			{
				if ( ! $this->token->hasClaim( 'access_expiry' ) 
					|| -1 === intval( $this->token->getClaim( 'access_expiry' ) )
				) {
					return false;
				} 
				return true;
			}

			/**
			 * Access Expiry
			 * @return int
			**/
			public function get_access_expiry()
			{
				if ( 'validation' !== $this->type 
					|| ! $this->token->hasClaim( 'access_expiry' ) 
					|| -1 === intval( $this->token->getClaim( 'access_expiry' ) )
				) {
					return false;
				}
				$access_expiry = $this->token->getClaim( 'access_expiry' );
				return $access_expiry;
			}

			/**
			 * Token Value
			 * @return string
			**/
			public function get_token_value()
			{
				if ( 'validation' !== $this->type ) {
					return false;
				}
				$token_index = $this->data_token->get_purchased_token_index( $this->get_token()->__toString() );
				$token_value = $this->data_token->get_used_value( $token_index );
				return $token_value;
			}

		/**
		 * Update Token
		**/
			/**
			 * Extending Expiry
			 * @return int
			**/
			public function get_extending_expiry()
			{
				if ( 'update' !== $this->type
					|| ! $this->token->hasClaim( 'access_expiry' ) 
				) {
					return false;
				}
				$expiry_in_day = $this->token->getClaim( 'expiry' );
				return $expiry_in_day;
			}

	/**
	 * Init
	**/
		/**
		 * Constructor
		 * @param string|Token $token_obj
		**/
		public function __construct( $token_obj )
		{

			if ( '' === $token_obj ) {
				throw new NTVWC_Exception( 'param token object is empty string.' );
			}

			// Parent WC_Order
			$this->init_vars( $token_obj );
			$this->maybe_update();
			$this->init_objs( $token_obj );

		}

		/**
		 * Init Vars
		 * @param string|Token $toke_obj
		**/
		protected function init_vars( $token_obj )
		{

			// Prepare var $token from ID and some params
			if ( is_string( $token_obj ) ) {
				$token_obj = NTVWC_Token_Methods::parse_from_string( $token_obj );
			}
			// Validate
			$this->validator = new NTVWC_Token_Validator( $token_obj );
			$token_obj = $this->validator->validate();
			if ( is_string( $token_obj ) ) {
				throw new NTVWC_Exception( $token_obj );
			}

			$this->is_token_init = true;
			$this->token = $token_obj;
			$this->type = ( $this->token->hasClaim( 'type' ) ? $this->token->getClaim( 'type' ) : 'validation' );

			if ( ! $this->token->hasClaim( 'order_id' )
				|| ! is_numeric( $this->token->getClaim( 'order_id' ) )
				|| 0 >= intval( $this->token->getClaim( 'order_id' ) )
				|| ! $this->token->hasClaim( 'product_id' )
				|| ! is_numeric( $this->token->getClaim( 'product_id' ) )
				|| 0 >= intval( $this->token->getClaim( 'product_id' ) )
				|| ! $this->token->hasClaim( 'registered_index' )
				|| ! is_numeric( $this->token->getClaim( 'registered_index' ) )
				|| 0 > intval( $this->token->getClaim( 'registered_index' ) )
			) {
				throw new NTVWC_Exception( esc_html__( 'Token does not have requierd params.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
			}

			$order_id   = intval( $this->token->getClaim( 'order_id' ) );
			$this->ntvwc_order = NTVWC_Order::get_instance( $order_id );
			if ( false === $this->ntvwc_order 
				|| is_string( $this->ntvwc_order )
			) {
				throw new NTVWC_Exception( esc_html__( 'Wrong Order.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
			}

			$product_id = intval( $this->token->getClaim( 'product_id' ) );
			$this->wc_product = WC()->product_factory->get_product( $product_id );
			if ( false === $this->wc_product 
				|| is_string( $this->wc_product )
			) {
				throw new NTVWC_Exception( esc_html__( 'Wrong Product.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
			}

			$registered_id_index = $token_obj->hasClaim( 'registered_index' ) ? $token_obj->getClaim( 'registered_index' ) : 0;
			$registered_token_id = $this->ntvwc_order->get_registered_token_id_by_product( $this->wc_product, $registered_id_index );
			$this->data_token = NTVWC_Data_Token::get_instance( $registered_token_id );
			if ( false === $this->data_token 
				|| is_string( $this->data_token )
			) {
				throw new NTVWC_Exception( esc_html__( 'Wrong Token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
			}

		}

		/**
		 * Update Token if the later version exists
		**/
		protected function maybe_update()
		{

			if ( 'validation' !== $this->type ) {
				return false;
			}

			$new_token = $this->get_the_latest_token();
			if ( null === $new_token || is_string( $new_token ) ) {
				return;
			}

			$new_token_obj = $this->validate( $new_token );
			if ( is_string( $new_token_obj ) ) {
				return;
			}

			$this->token = $new_token_obj;
			$this->is_the_latest = true;

		}

		/**
		 * Init Vars
		 * @param string|Token $token_obj
		**/
		protected function init_objs( $token_obj )
		{
		}

	/**
	 * Validate
	**/
		/**
		 * Validate the token
		 * @param string|Token $token : Default ''
		 * @return bool|Token
		**/
		public function validate( $token = '' )
		{

			try {
				$another_validator = new NTVWC_Token_Validator( $token );
				$token_obj = $another_validator->validate();
			} catch ( NTVWC_Exception $e ) {
				$token_obj = $this->validate( $this->token );
			}

			return $token_obj;
		}



	/**
	 * Extend Expiry
	**/
		/**
		 * Extends the expiry
		 * @param NTVWC_Token_Handler $update_token_handler
		 * @return string|Token
		**/
		public function extend_expiry( $update_token_handler )
		{

			// Token Type
				if ( 'validation' !== $this->type ) {
					return esc_html__( 'Wrong Token type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				}

			// Token Version
				if ( ! $this->is_latest_token( $this ) ) {
					//$this->maybe_update();
					return esc_html__( 'Token is not the latest.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				}

			// Need Expiry
				$update_token_handler = $this->need_extend_expiry( $update_token_handler );
				if ( ! $update_token_handler ) {
					return esc_html__( 'No need to extend the expiry.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				}

			// Check the Token Value
				$update_token_used_token_value     = $update_token_handler->get_the_used_token_value();
				$validation_token_used_token_value = $this->get_the_used_token_value();
				if ( $update_token_used_token_value !== $validation_token_used_token_value ) {
					return esc_html__( 'Update Token Handler cannot be initialized.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				}

			// Access Expiry
				$purchased_token_data = new NTVWC_Data_Purchased_Token( $this->ntvwc_order->get_id(), $this->wc_product );
				$token_params = $this->data_token->get_data();
				$access_expiry  = intval( $this->get_access_expiry() );
				if ( -1 < $access_expiry && current_time( 'timestamp', true ) > $access_expiry ) {
					$access_expiry = current_time( 'timestamp', true );
				}
				$extending_days = intval( $update_token_handler->get_extending_expiry() );
				if ( -1 === $extending_days ) {
					$access_expiry = -1;
				} else {
					$access_expiry = $access_expiry + ( $extending_days * DAY_IN_SECONDS );
				}

			// Params
				$new_params = wp_parse_args( array(
					'access_expiry' => $access_expiry
				), $token_params );

				try {
					$new_token = $this->update_the_validation_expiry( $new_params );
				} catch ( NTVWC_Exception $e ) {
					return $e->getMessage();
				}

				$this->maybe_update();

			// End
				return $new_token;
	
		}

		/**
		 * Update the Expiry
		 * @param array $new_params
		 * @return string|Token
		**/
		protected function update_the_validation_expiry( $new_params = array() )
		{

			// Token Type
				if ( 'validation' !== $this->type ) {
					throw new NTVWC_Exception( esc_html__( 'Wrong Token type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

				$result = $this->data_token->update_expiry( $new_token );
				if ( is_string( $result ) ) {
					throw new NTVWC_Exception( $result );
				}

				return $this->data_token->get_the_latest_purchased_token();

		}

		/**
		 * Check if the token expiry need to be extended by the update token
		 * @return bool|NTVWC_Token_Handler : of the update token.
		**/
		public function need_extend_expiry( $update_token_handler )
		{

			if ( 'validation' !== $this->type ) {
				return false;
			}

			// Update Token
			$validation_value = $update_token_handler->get_the_used_token_value();

			// Validation Token
			$token_value = $this->get_the_used_token_value();

			if ( null === $token_value
				|| $validation_value !== $token_value
			) {
				return false;
			}

			return $update_token_handler;

		}

	/**
	 * Invalidate the Update Token
	**/
		/**
		 * Invalidate the Update Token
		 * @param NTVWC_Token_Handler $validation_token_handler description
		 * @return bool
		**/
		public function invalidate_token( $validation_token_handler )
		{

			if ( 'update' !== $this->type ) {
				return esc_html__( 'Wrong Token Type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
			}

			try {

				$decreased = $this->data_token->decrease_purchased_number( 1 );
				if ( false === $decreased ) {
					throw new NTVWC_Exception( esc_html__( 'Failed to decrease the number of updated token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

				if ( 0 === $decreased ) {

					$result = $this->delete_the_update_token();
					if ( is_string( $result ) ) {
						throw new NTVWC_Exception( esc_html__( 'Failed to delete the updated token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
					}

				}

			} catch ( NTVWC_Exception $e ) {

				$this->cancel_updated_new_token( $validation_token_handler );
				return false;

			}

			return true;

		}

		/**
		 * Invalidate the Update Token
		 * @return bool|string Returns string for errors.
		**/
		protected function delete_the_update_token()
		{

			if ( 'update' !== $this->type ) {
				return esc_html__( 'Wrong Token Type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
			}

			try {

				// Vars
					$order_id   = $this->ntvwc_order->get_id();
					$product_id = $this->wc_product->get_id();


					$result = $this->ntvwc_order->delete_registered_token_id_by_product( $product_id, $this->data_token->get_registered_index() );
					if ( is_string( $result ) ) {
						throw new NTVWC_Exception( $result );
					}

					$result = $this->data_token->delete_the_update_token();
					if ( is_string( $result ) ) {
						throw new NTVWC_Exception( $result );
					}

			} catch ( NTVWC_Exception $e ) {
				return $e->getMessage();
			}

			// End
				return true;

		}

		/**
		 * Invalidate the Update Token
		 * @param NTVWC_Token_Handler $validation_token_handler
		 * @return bool
		**/
		protected function cancel_updated_new_token( $validation_token_handler )
		{

			if ( 'update' !== $this->type ) {
				return esc_html__( 'Wrong Token Type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
			}

			if ( ! $validation_token_handler->is_latest_token() ) {
				return false;
			}

			try {

				$result = $validation_token_handler->data_token->cancel_updated_new_token();
				if ( is_string( $result ) ) {
					throw new NTVWC_Exception( esc_html__( 'Failed to cancel the updated validation token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

			} catch ( NTVWC_Exception $e ) {
				return false;
			}

			// End
				return true;

		}

		/**
		 * Call
		 * @param callable $method
		 * @param array    $args
		 * @return mixed
		**/
		function __call( $method, $args )
		{

			if ( 'NTVWC_Order' === get_class( $this->ntvwc_order )
				&& method_exists( $this->ntvwc_order, $method )
				&& is_callable( array( $this->ntvwc_order, $method ) ) 
			) {
				return call_user_func_array(
					array( $this->ntvwc_order, $method ),
					$args
				);
			}

			return null;

		}

		/**
		 * Get the token index
		**/
		protected function get_the_token_index()
		{

			// Check if order items exist
			return $this->data_token->get_purchased_token_index( $this->token->__toString() );

		}

		/**
		 * Get the latest purchased token
		 * @return string
		**/
		protected function get_the_latest_token()
		{

			// Check if order items exist
			return $this->data_token->get_the_latest_purchased_token();

		}

		/**
		 * Get the token value
		 * @return string
		**/
		public function get_the_used_token_value()
		{

			$token_index = $this->data_token->get_purchased_token_index( $this->get_token()->__toString() );
			$token_value = $this->data_token->get_used_value( $token_index );

			return $token_value;

		}

		/**
		 * Is latest token
		 * @return bool
		**/
		public function is_latest_token()
		{

			return $this->data_token->is_the_latest_purchased_token( $this->get_token()->__toString() );

		}


}
}























































