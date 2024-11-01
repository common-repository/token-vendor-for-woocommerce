<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Token_Validator' ) ) {
/**
 * Data
 * 
 * 
**/
class NTVWC_Token_Validator {

	/**
	 * Token holder
	**/
	protected $is_token = false;

	/**
	 * Token holder
	**/
	protected $token;

	/**
	 * Token holder
	**/
	protected $type = 'validation';

	/**
	 * NTVWC_Token_Handler
	**/
	protected $token_handler = null;

	/**
	 * NTVWC_Order
	**/
	protected $ntvwc_order = null;

	/**
	 * WC_Product
	**/
	protected $wc_product = null;


	/**
	 * Flag if validating token is done or not
	**/
	protected $validating_token_done = false;

	/**
	 * Flag if validating expiry is done or not
	**/
	protected $validating_expiry_done = false;

	/**
	 * Constructor
	 * @param string|Token $token_obj
	**/
	public function __construct( $token_obj )
	{

		// Parent WC_Order
		$this->init_vars( $token_obj );

	}

	/**
	 * Init Vars
	 * @param string|Token $token_obj
	**/
	//protected function init_vars( $token_obj )
	protected function init_vars( $token_obj )
	{

		// Prepare var $token from ID and some params
		$result = $this->set_token( $token_obj );
		if( is_string( $result ) ) {
			throw new NTVWC_Exception( 
				esc_html__( 'Failed to parse string into Token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				400,
				null,
				$token_obj
			);
		}

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
			throw new NTVWC_Exception( 
				esc_html__( 'Token does not have required params.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				400,
				null,
				$token_obj
			);
		}

		$order_id   = intval( $this->token->getClaim( 'order_id' ) );
		$this->ntvwc_order = new NTVWC_Order( $order_id );

		$product_id = intval( $this->token->getClaim( 'product_id' ) );
		$this->wc_product = WC()->product_factory->get_product( $product_id );

		$registered_id_index = $token_obj->hasClaim( 'registered_index' ) ? $token_obj->getClaim( 'registered_index' ) : 0;
		$registered_token_id = $this->ntvwc_order->get_registered_token_id_by_product( $this->wc_product, $registered_id_index );
		$this->data_token = NTVWC_Data_Token::get_instance( $registered_token_id );

		return true;

	}

	/**
	 * Init Vars
	 * @param string|Token $token_obj
	 * @return string|Token Returns string for error.
	**/
	protected function set_token( $token_obj )
	{

		// Prepare var $token from ID and some params
		if ( is_string( $token_obj ) ) {
			$token_obj = NTVWC_Token_Methods::parse_from_string( $token_obj );
		}

		if ( is_string( $token_obj ) ) {
			return $token_obj;
		}

		if ( null === $token_obj ) {
			throw new NTVWC_Exception( esc_html__( 'Validator : wrong param input "null".', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
		}

		$this->is_token = true;
		$this->token    = $token_obj;
		$this->type     = ( $this->token->hasClaim( 'type' ) ? $this->token->getClaim( 'type' ) : 'validation' );

		return $token_obj;

	}

	/**
	 * Init Vars
	 * @return Token Returns string for error.
	**/
	public function get_token()
	{

		return $this->token;

	}

	/**
	 * Validate token :
	 * 		token itself
	 *   	expiry
	 * @return string|Token
	**/
	public function validate()
	{

		try {

			$token_obj = $this->validate_token();
			if ( is_string( $token_obj ) ) {
				throw new NTVWC_Exception( sprintf( 
					esc_html__( 'Validating Token: Result False. %s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
					$token_obj
				) );
			}

			if ( 'validation' === $this->type ) {
				$token_obj = $this->validate_expiry();
				if ( is_string( $token_obj ) ) {
					throw new NTVWC_Exception( sprintf( 
						esc_html__( 'Validating Expiry: Result False. %s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						$token_obj
					) );
				}
			} elseif( 'update' === $this->type ) {
				$token_obj = $this->validate_purchased_number();
				if ( is_string( $token_obj ) ) {
					throw new NTVWC_Exception( sprintf( 
						esc_html__( 'Validating Expiry: Result False. %s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						$token_obj
					) );
				}
			}

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return $token_obj;

	}

	/**
	 * Validate token except the expires
	 * @return string|Token
	**/
	public function validate_token()
	{

		try {

			// Prepare var $token from ID and some params
				if ( ! $this->token ) {
					throw new NTVWC_Exception( esc_html__( 'This is even not token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

			// Check if the order exists and has purchased items.
				$this->validate_order();

			// Check if the token signer is valid
				$this->validate_signer();

				if ( 'validation' === $this->type ) {

				}

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return $this->token;

	}

		/**
		 * Check if the order exists
		 * @throws Exception
		**/
		protected function validate_order()
		{
			
			// Check if order items exist
				$order_id    = intval( $this->ntvwc_order->get_id() );
				$product_id  = intval( $this->wc_product->get_id() );

				if ( 0 >= count( $this->ntvwc_order->get_items() ) ) {
					throw new NTVWC_Exception( esc_html__( 'The order doesn\'t exist.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

			// Check if the token item exists.
				if ( ! $this->ntvwc_order->has_token_items() ) {
					throw new NTVWC_Exception( esc_html__( 'The order doesn\'t have any token items.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}
				$all_token_params = $this->ntvwc_order->get_all_token_params();
				foreach ( $all_token_params as $token_params ) {
					$product_ids[] = $token_params['product_id'];
				}
				$product_id = $product_id;
				if ( ! in_array( $product_id, $product_ids ) ) {
					throw new NTVWC_Exception( esc_html__( 'The order doesn\'t have such a token item.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

			// Check if the order saved the same token as this
				$is_token_id_registered_in_order = $this->is_token_id_registered_in_order();
				if ( ! $is_token_id_registered_in_order ) {
					throw new NTVWC_Exception( sprintf( esc_html__( 'Such a %s token is not registered.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), $this->type ) );
				}

		}

			/**
			 * Check if the order exists
			 * @return bool
			**/
			protected function is_token_id_registered_in_order()
			{

				$product_id       = intval( $this->wc_product->get_id() );
				$registered_index = intval( $this->token->hasClaim( 'registered_index' ) ? $this->token->getClaim( 'registered_index' ) : 0 );
				$token_id         = intval( $this->data_token->get_id() );

				$token_id_holder = ntvwc_get_post_meta( $this->ntvwc_order->get_id(), '_ntvwc_registered_token_ids' );
				if ( is_array( $token_id_holder )
					&& isset( $token_id_holder[ $product_id ] )
					&& is_array( $token_id_holder[ $product_id ] )
					&& 0 < count( $token_id_holder[ $product_id ] )
					&& isset( $token_id_holder[ $product_id ][ $registered_index ] )
					&& is_numeric( $token_id_holder[ $product_id ][ $registered_index ] )
					&& 0 <= intval( $token_id_holder[ $product_id ][ $registered_index ] )
					&& $token_id === intval( $token_id_holder[ $product_id ][ $registered_index ] )
				) {
					return true;
				}

				return false;

			}

		/**
		 * Check if the order exists
		 * @return bool
		**/
		public function is_token_latest()
		{

			//$target_token_histroy = $this->ntvwc_order->get_the_purchased_tokens( $this->wc_product->get_id() );
			$latest_token_index = intval( $this->token_handler->get_the_latest_token_index() );

			// Compare the token index
			if ( intval( $this->get_the_token_index() ) < $latest_token_index ) {
				return true;
			}
			$this->is_the_latest = false;
			return false;


		}

		/**
		 * Check if the order exists
		 * @return bool
		**/
		protected function validate_signer()
		{

			// Signer index
				$token_index = $this->data_token->get_purchased_token_index( $this->token->__toString() );
				if ( null === $token_index ) {
					throw new NTVWC_Exception( sprintf(
						esc_html__( 'Token index does not exist in Token. %1$s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						$token_index
					) );
				}

			// Indexed signer
				$indexed_signer = $this->data_token->get_used_signer_by_index( $token_index );
				if (  ! ( isset( $indexed_signer['signer'] ) && is_string( $indexed_signer['signer'] ) )
					|| ! ( isset( $indexed_signer['string'] ) && is_string( $indexed_signer['string'] ) )
				) {
					throw new NTVWC_Exception( sprintf(
						esc_html__( 'Indexed Signer does not exist. %1$s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						$token_index
					) );
				}

				$signer_type = $indexed_signer['signer'];
				$signer_str  = $indexed_signer['string'];

				$signer = NTVWC_Token_Methods::get_jwt_signer( array(
					'type' => $signer_type
				) );

			// No signer
				if ( false === $signer ) {
				}
				elseif ( 'Sha256' === $signer_type ) {
					if( ! $this->token->verify( $signer, $signer_str ) ) {
						ob_start();
						ntvwc_test_var_dump( $this->token );
						$html = ob_get_clean();
						throw new NTVWC_Exception( esc_html__( 'Sign is not valid.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
					}
				}

		}

	/**
	 * Validate token except the expires
	 * @return string|Token
	**/
	public function validate_expiry()
	{

		// Prepare var $token from ID and some params
			if ( is_string( $this->token ) && '' !== $this->token ) {
				$this->token = NTVWC_Token_Methods::parse_from_string( $this->token );
			} elseif ( is_string( $this->token ) && '' === $this->token ) {
				return esc_html__( 'Token is invalid string.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
			}

		// Expiry
			$access_expiry = intval( $this->token->hasClaim( 'access_expiry' ) ? $this->token->getClaim( 'access_expiry' ) : -1 );
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
			return $this->token;

	}

	/**
	 * Validate token except the expires
	 * @return string|Token
	**/
	public function validate_purchased_number()
	{

		$purchased_number = intval( $this->data_token->get_purchased_number() );
		if ( 0 >= $purchased_number ) {
			return esc_html__( 'Update token is all used.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
		}
		return $this->token;

	}

	/**
	 * Get the token index
	 * @return null|int|string
	**/
	protected function get_the_token_index()
	{

		// Check if order items exist
			return $this->ntvwc_order->get_the_token_index( $this );

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


}
}



