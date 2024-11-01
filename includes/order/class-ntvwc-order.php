<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Order' ) && class_exists( 'WC_Order' ) ) {
/**
 * Data
 * 
 * 
**/
class NTVWC_Order extends WC_Order {

	/**
	 * Token holder
	**/
	protected $token_holder = array();

	/**
	 * Token holder
	**/
	protected $all_token_params = array();

	/**
	 * 
	**/
	public static function get_instance( $order = 0 )
	{

		try {

			$wc_order = WC()->order_factory->get_order( $order );
			if ( false === $wc_order ) {
				throw new NTVWC_Exception( __( 'Invalid Order.', Nora_Package_Update_Server_For_WooCommerce::TEXTDOMAIN ) );
			}
			$instance = new Self( $wc_order->get_id() );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return $instance;

	}

	/**
	 * Constructor
	 * 
	 * @param mixed $order
	**/
	public function __construct( $order = 0 )
	{

		// Parent WC_Order
		parent::__construct( $order );

	}

	/**
	 * Generate Token
	 * @return bool|int Returns false if error occured, otherwise returns order ID
	**/
	public function generate_tokens()
	{

		$all_token_params = $this->get_all_token_params();
		if ( ! is_array( $all_token_params ) || 0 >= count( $all_token_params ) ) {
			return false;
		}

		// User
		$customer_id  = intval( $this->get_customer_id( 'view' ) );
		if ( 0 >= $customer_id ) {
			do_action( 'ntvwc_action_order_no_customer_id', $this );
		}

		// Current secret
		$data_option = ntvwc_get_data_option( 'token_vendor' );
		$option_data = $data_option->get_data();
		$current_secret = hash( 'sha256', ( 
			is_string( $option_data['jwt_secret_key'] ) 
			? $option_data['jwt_secret_key'] 
			: ''
		) );

		// Each
		$ntvwc_token_id_holder = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_registered_token_ids' );
		if ( ! is_array( $ntvwc_token_id_holder ) || 0 >= count( $ntvwc_token_id_holder ) ) {
			$ntvwc_token_id_holder = array();
		}
		$ntvwc_token_id_holder = array();
		foreach ( $all_token_params as $token_params ) {

			// Product ID
			$product_id = intval( $token_params['product_id'] );

			// Init the token id holder
			if ( ! isset( $ntvwc_token_id_holder[ $product_id ] )
				|| ! is_array( $ntvwc_token_id_holder[ $product_id ] )
			) {
				$ntvwc_token_id_holder[ $product_id ] = array();
			}

			$purchased_number = intval( $this->get_token_item_quantity( $product_id ) );
			if ( 0 >= $purchased_number ) {
				continue;
			}

			for ( $index = 0; $index < $purchased_number; $index++ ) {

				$data_token = NTVWC_Data_Token::get_instance(
					$this->get_id(),
					$product_id,
					$index
				);

				$token_id = $data_token->get_id();
				$ntvwc_token_id_holder[ $product_id ][ $index ] = intval( $token_id );

			}

		}

		$ntvwc_token_ids_in_json = json_encode( $ntvwc_token_id_holder, JSON_UNESCAPED_UNICODE );
		update_post_meta( $this->get_id(), '_ntvwc_registered_token_ids', $ntvwc_token_ids_in_json );

		return $this->get_id();

	}

	/**
	 * Init specified Auth by $type
	 * @return array
	**/
	public function get_all_token_params()
	{

		// Holder
		$token_holder = array();
		$token_items = $this->get_token_items();
		if ( is_array( $token_items ) && 0 < count( $token_items ) ) {
		foreach ( $token_items as $token_item ) {
			$token_params = $this->generate_token_params_by_token_item( $token_item );
			if ( false === $token_params ) {
				continue;
			}

			// Set to the holder
			$token_product_id = intval( $token_item['product_id'] );
			$token_holder[ $token_product_id ] = $token_params;

		}
		}

		// End
		return $token_holder;

	}

		/**
		 * Init specified Auth by $type
		 * @return array
		**/
		public function generate_token_params_by_token_item( $token_item = array() )
		{

			if ( ! is_array( $token_item ) 
				|| ! isset( $token_item['order_key'] )
				|| ! isset( $token_item['product_id'] )
			) {
				return false;
			}

			// Product ID
				$token_product_id = $token_item['product_id'];
				$wc_product = WC()->product_factory->get_product( $token_product_id );

			// Token ID
				$token_id = $this->generate_token_id_by_product( $token_product_id );

			// Date completed
				$product_id = intval( $token_item['product_id'] );
				$date_completed = $this->get_date_completed();
				$date_completed_timestamp = intval( 
					null !== $date_completed
					&& is_subclass_of( $date_completed, 'DateTime' ) 
					? $date_completed->getTimestamp() 
					: 0 
				);

			// Expires
				$exp = -1;
				$_token_expiry_in_day = $token_item['expiry'];
				if ( 0 < intval( $_token_expiry_in_day ) ) {
					$_token_expiry_in_second = intval( DAY_IN_SECONDS ) * intval( $_token_expiry_in_day );
					$exp = $date_completed_timestamp + $_token_expiry_in_second;
				}

			// Params
				$token_params = wp_parse_args( array(
					// Token ID just in case
					//'user_id'         => $this->get_customer_id(),
					// Order ID
					'order_id'        => $this->get_id(),
					// Order key
					'order_key'       => $this->get_order_key(),
					// Product ID
					'product_id'      => $wc_product->get_id(),
					// Product Name
					'product_name'    => $wc_product->get_name(),
					// Token ID just in case
					'token_id'        => $token_id,
					// Token Value
					//'token_value'   => $token_item['token_value'],
					// Date Completed
					'date_completed'  => $date_completed_timestamp,
					// Access Expire : -1 in unlimited case
					'access_expiry'   => $exp,
					// Restrict Access : 
					//'restrict_access' => $token_item['restrict_access'],
				), $token_item );

				return apply_filters( 'ntvwc_filter_order_token_params', $token_params, $this );

		}

		/**
		 * Init specified Auth by $type
		 * @param int $product_id
		 * @return array
		**/
		public function generate_token_params_by_product_id( int $product_id )
		{

			$purchased_token = new NTVWC_Data_Purchased_Token( $this->get_id(), $product_id );
			$token_data = $purchased_token->get_data();
			$token_params = $this->generate_token_params_by_token_item( $token_data );
			return $token_params;

		}

	/**
	 * Delete token id
	 * @param mixed $product
	 * @param int   $index
	 * @return bool|int Retruns false if error occured
	**/
	public function delete_registered_token_id_by_product( $product = false, $index = 0 )
	{

		if ( false === $product ) {
			return esc_html__( 'Wrong target to be set.' );
		}
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return esc_html__( 'Wrong target to be deleted.' );
		}

		$token_ids = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_registered_token_ids' );
		$product_id = $product->get_id();
		if ( isset( $token_ids[ $product_id ] ) 
			&& is_array( $token_ids[ $product_id ] ) 
			&& 0 < count( $token_ids[ $product_id ] )
			&& is_numeric( $token_ids[ $product_id ][ $index ] ) 
			&& 0 < intval( $token_ids[ $product_id ][ $index ] ) 
		) {
			$token_ids[ $product_id ][ $index ] = null;
			$token_ids_json = json_encode( $token_ids, JSON_UNESCAPED_UNICODE );
			$result = update_post_meta( $this->get_id(), '_ntvwc_registered_token_ids', $token_ids_json );
			return $result;
		}

			return esc_html__( 'Target not found.' );

	}

	/**
	 * Get registered token id
	 * @param mixed $product
	 * @param int   $index
	 * @return bool|int Retruns false if error occured
	**/
	public function get_registered_token_id_by_product( $product = false, $index = 0 )
	{

		if ( false === $product ) {
			return false;
		}
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}

		$token_ids = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_registered_token_ids' );
		$product_id = $product->get_id();
		if ( isset( $token_ids[ $product_id ] ) 
			&& is_array( $token_ids[ $product_id ] ) 
			&& 0 < count( $token_ids[ $product_id ] )
			&& is_numeric( $token_ids[ $product_id ][ $index ] ) 
			&& 0 < intval( $token_ids[ $product_id ][ $index ] ) 
		) {
			return intval( $token_ids[ $product_id ][ $index ] );
		}

		return false;

	}

	/**
	 * Get the token
	 * @param mixed $product
	 * @return bool|string Retruns false if error occured
	**/
	public function get_token_by_product( $product = false, $index = 0 )
	{

		if ( false === $product ) {
			return false;
		}
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}

		$token_id = $this->get_registered_token_id_by_product( $product->get_id(), $index );
		if ( false === $token_id ) {
			return false;
		}

		try {
			$data_token = NTVWC_Data_Token::get_instance( $token_id );
		} catch ( Exception $e ) {
			return false;
		}

		return $data_token->get_the_latest_purchased_token();

	}

	/**
	 * Has token item
	 * @return [bool] Returns false if the target doesn't exist.
	**/
	public function has_token_items()
	{

		foreach ( $this->get_items() as $item ) {
			if ( $item->is_type( 'line_item' ) ) {
				$product = $item->get_product();
				$is_ntvwc_token = get_post_meta( $product->get_id(), '_ntvwc_type_token', true );
				if ( is_string( $is_ntvwc_token ) && 'yes' === $is_ntvwc_token ) {
					return true;
				}
			}
		}

		return false;

	}

	/**
	 * Has item data
	 * @return [bool] Returns false if the target doesn't exist.
	**/
	public function has_token_item( $product_id )
	{

		foreach( $this->get_token_items() as $token_product_id => $token_item ) {
			if ( $product_id === $token_product_id ) {
				return true;
			}
		}
		return false;

	}

	/**
	 * Get token item data
	 * @return [array]
	**/
	public function get_token_items()
	{

		$tokens = array();
		foreach ( $this->get_items() as $item ) {

			if ( ! $item->is_type( 'line_item' ) ) {
				continue;
			}

			$product = $item->get_product();
			if ( false === $product ) {
				continue;
			}

			$product_id = intval( $product->get_id() );

			$is_ntvwc_token = get_post_meta( $product->get_id(), '_ntvwc_type_token', true );
			if ( ! is_string( $is_ntvwc_token ) || 'yes' !== $is_ntvwc_token ) {
				continue;
			}

			// Check if the product is token
			$is_ntvwc_token = get_post_meta( $product_id, '_ntvwc_type_token', true );
			if ( ! is_string( $is_ntvwc_token ) || 'yes' !== $is_ntvwc_token ) {
				continue;
			}

			$purchased_token = new NTVWC_Data_Purchased_Token( $this->get_id(), $product_id );
			$tokens[ $product_id ] = $purchased_token->get_data();

		}

		return $tokens;

	}

		/**
		 * Get download item data
		 * 
		 * @param [int] $product_id
		 * 
		 * @return [bool|array] Returns false if the target doesn't exist.
		**/
		protected function get_token_item_by_product_id( $product )
		{

			// Check if the product is token
			$is_ntvwc_token = get_post_meta( $product->get_id(), '_ntvwc_type_token', true );
			if ( ! is_string( $is_ntvwc_token ) || 'yes' !== $is_ntvwc_token ) {
				return false;
			}

			$product_token = new NTVWC_Data_Purchased_Token( $product->get_id() );

			return $product_token->get_data();

		}

	/**
	 * Is the product token type
	 * @param [mixed] $product
	 * @return [bool]
	**/
	public function is_the_product_token_type( $product )
	{

		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}

		$is_ntvwc_token = get_post_meta( $product->get_id(), '_ntvwc_type_token', true );
		if ( ! is_string( $is_ntvwc_token ) || 'yes' !== $is_ntvwc_token ) {
			return false;
		}

		if ( is_string( $is_ntvwc_token ) && 'yes' === $is_ntvwc_token ) {
			return true;
		}

		return false;

	}

	/**
	 * Get token item data
	 * @param int $target_product_id
	 * @return [bool|int]
	**/
	public function get_token_item_quantity( $target_product_id )
	{

		foreach ( $this->get_items() as $item ) {

			if ( $item->is_type( 'line_item' ) ) {

				$product = $item->get_product();
				if ( false !== $product 
					&& $target_product_id === $product->get_id()
				) {
					return $item->get_quantity();
				}

			}

		}

		return false;

	}

	/**
	 * Get token index
	 * 
	 * @param [int|WC_Product] $product
	 * 
	 * @return [bool|string] Returns false if the target doesn't exist.
	**/
	public function generate_token_id_by_product( $product )
	{
		$product = WC()->product_factory->get_product( $product );
		if ( false === $product ) {
			return false;
		}
		return $this->get_order_key() . '_' . $product->get_id();
	}

	/**
	 * Get token signer data
	 * @param string $product_id
	 * @return array
	**/
	public function get_token_signer_by_token_id( $product_id )
	{

		$token_signers = json_decode( get_post_meta( $this->get_id(), '_ntvwc_purchased_token_signers', true ), true );
		if ( isset( $jwt_signers[ $product_id ] )
			&& is_array( $jwt_signers[ $product_id ] )
			&& 0 < count( $jwt_signers[ $product_id ] )
		) {
			$token_signer = $jwt_signers[ $product_id ];
			return $token_signer;
		}

		return false;

	}

	/**
	 * Getters
	**/
		/**
		 * Values
		**/
			/**
			 * Get all used token values
			 * @return array
			**/
			public function get_all_used_token_values()
			{
				$token_values = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_used_values' );
				return $token_values;
			}

			/**
			 * Get used values
			 * @param int $product_id
			 * @return string[]
			**/
			public function get_the_used_token_values( int $product_id )
			{

				$values = $this->get_all_used_token_values();
				if ( isset( $values[ $product_id ] ) 
					&& is_array( $values[ $product_id ] )
					&& 0 < count( $values[ $product_id ] )
				) {
					return $values[ $product_id ];
				}

				return array();

			}

			/**
			 * Get the used value
			 * @param int $product_id
			 * @return string
			**/
			public function get_the_used_token_value( int $product_id, $index )
			{

				$values = $this->get_the_used_token_values( $product_id );
				if ( is_array( $values )
					&& 0 < count( $values )
					&& isset( $values[ $index ] )
				) {
					return $values[ $index ];
				}

				return '';

			}

			public function is_the_same_used_token_value(  )
			{

			}

		/**
		 * Secret
		**/
			/**
			 * Secrets
			 * @return string[]
			**/
			public function get_all_used_secrets()
			{

				$secrets = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_used_secrets' );
				return $tokens;

			}

			/**
			 * Target Secret History
			 * @param int $product_id
			 * @return string[]
			**/
			public function get_the_used_secrets( int $product_id )
			{

				$secrets = $this->get_all_used_secrets();
				if ( isset( $secrets[ $product_id ] ) 
					&& is_array( $secrets[ $product_id ] )
					&& 0 < count( $secrets[ $product_id ] )
				) {
					return $secrets[ $product_id ];
				}

				return array();

			}

			/**
			 * The Latest Secret
			 * @param int $product_id
			 * @return string
			**/
			public function get_the_latest_used_secret( int $product_id )
			{

				$secrets = $this->get_the_used_secrets( $product_id );
				$latest_version_index = count( $secrets ) - 1;
				if ( is_string( $secrets[ $latest_version_index ] ) 
					&& '' !== $secrets[ $latest_version_index ]
				) {
					return $secrets[ $latest_version_index ];
				}

				return '';

			}

		/**
		 * Tokens
		**/
			/**
			 * Tokens
			 * @return array[]
			**/
			public function get_all_purchased_tokens()
			{

				$tokens = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_purchased_tokens' );
				return $tokens;

			}

			/**
			 * Target Token History
			 * @param int $product_id
			 * @return string[]
			**/
			public function get_the_purchased_tokens( int $product_id )
			{

				$the_tokens = $this->get_all_purchased_tokens();
				if ( isset( $the_tokens[ $product_id ] ) 
					&& is_array( $the_tokens[ $product_id ] )
					&& 0 < count( $the_tokens[ $product_id ] )
				) {
					return $the_tokens[ $product_id ];
				}

				return array();

			}

			/**
			 * The Latest Token
			 * @param int $product_id
			 * @return string
			**/
			public function get_the_latest_purchased_token( int $product_id )
			{

				$the_tokens = $this->get_the_purchased_tokens( $product_id );
				$latest_version_index = count( $the_tokens ) - 1;
				if ( is_string( $the_tokens[ $latest_version_index ] ) 
					&& '' !== $the_tokens[ $latest_version_index ]
				) {
					return $the_tokens[ $latest_version_index ];
				}

				return '';

			}



			/**
			 * Contexted Handler
			**/
				/**
				 * Get the token history related to the product_id.
				 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
				 * @return string[] : Returns empty array if there is no tokens registered related to the product id.
				**/
				public function get_the_purchased_tokens_by_handler( $token_handler )
				{

					// Vars
						$product_id = intval( $token_handler->wc_product->get_id() );

						return $this->get_the_purchased_tokens( $product_id );
				}

				/**
				 * Get the latest version of the token.
				 * 	This is for the client who puchased 2 or more tokens
				 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
				 * @return string|Token : Returns string for some errors.
				**/
				public function get_the_latest_token( $token_handler )
				{

					// Token history
						$target_purchased_tokens = $this->get_the_purchased_tokens_by_handler( $token_handler );
						$the_latest_index = $this->get_the_latest_token_index( $token_handler );

					// Post Meta
						if ( ! isset( $target_purchased_tokens[ $the_latest_index ] )
							|| ! is_string( $target_purchased_tokens[ $the_latest_index ] )
							|| '' === $target_purchased_tokens[ $the_latest_index ]
						) {
							return esc_html__( 'The Latest Token is not found.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
						}
						$the_latest_token = $target_purchased_tokens[ $the_latest_index ];
						$validator = new NTVWC_Token_Validator( $the_latest_token );
						$token_obj = $validator->validate();

						return $token_obj;
				}

			/**
			 * Index
			**/
				/**
				 * Get the latest version of the token.
				 * 	This is for the client who puchased 2 or more tokens
				 * @param string $token
				 * @return null|int|string : Returns null for some errors.
				**/
				protected function get_the_token_index_by_token( $token )
				{

					// Check the param
					if ( is_string( $token ) && '' !== $token ) {
					} elseif ( ! is_string( $token ) 
						&& is_object( $token ) 
						&& method_exists( $token, '__toString' )
					) {
						$token = $token->__toString();
					} else {
						return null;
					}

					// Tokens
						$all_purchased_tokens = $this->get_all_purchased_tokens();
						if ( ! is_array( $all_purchased_tokens )
							|| 0 >= count( $all_purchased_tokens )
						) {
							return null;
						}

						foreach ( $all_purchased_tokens as $product_id => $each_purchased_tokens ) {
							if ( in_array( $token, $each_purchased_tokens ) ) {
								break;
							}
						}

					// Search
						$token_index = array_search( $token, $each_purchased_tokens );

					// End
						return $token_index;

				}

				/**
				 * Get the token index
				 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
				 * @return null|int|string
				**/
				public function get_the_token_index( $token_handler )
				{

					$token = $token_handler->get_token()->__toString();

					// Tokens
						$token_index = $this->get_the_token_index_by_token( $token );

					// End
						return $token_index;

				}

				/**
				 * Check if the token has updated version
				 *  This is for the client who purchased 2 or more tokens
				 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
				 * @return bool
				**/
				protected function has_later_version( $token_handler )
				{

					$target_token_histroy = $this->get_the_purchased_tokens( $token_handler->wc_product->get_id() );
					$latest_token_index = $this->get_the_latest_token_index( $token_handler );

					// Compare the token index
					if ( $this->get_the_token_index( $token_handler ) < $latest_token_index ) {
						return true;
					}
					$this->is_the_latest = false;
					return false;

				}


				/**
				 * Get the latest version of the token.
				 * 	This is for the client who puchased 2 or more tokens
				 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
				 * @return bool|int|string : Returns false for some errors.
				**/
				protected function get_the_latest_token_index( $token_handler )
				{

					// Tokens
						$target_purchased_tokens = $this->get_the_purchased_tokens_by_handler( $token_handler );
						if ( ! is_array( $target_purchased_tokens )
							|| 0 >= count( $target_purchased_tokens )
						) {
							return false;
						}

					// Last key
						$the_latest_index = ntvwc_array_key_last( $target_purchased_tokens );

					// End
						return $the_latest_index;

				}

				/**
				 * Check if the token exists, registered for the order
				 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
				 * @return bool
				**/
				public function token_exists( $token_handler )
				{

					// Get the history
						$target_purchased_tokens = $this->get_the_purchased_tokens_by_handler( $token_handler );

					// Post Meta
						if ( in_array( $this->token->__toString(), $target_purchased_tokens ) ) {
							return true;
						}

						return false;

				}

				/**
				 * Check if the token is the latest
				 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
				 * @return bool
				**/
				public function is_latest_token( $token_handler )
				{

					// Post Meta
						if ( $token_handler->get_token()->__toString() === $this->get_the_latest_token( $token_handler )->__toString() ) {
							return true;
						}

					// End with no found
						return false;

				}

		/**
		 * Signers
		**/
			/**
			 * Signers
			 * @param NTVWC_Token_Handler|NTVWC_Token_Validator $token_handler
			 * @return array[]
			**/
			public function get_purchased_token_signers( $token_handler )
			{

				$signers = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_purchased_token_signers' );
				return $signers;

			}

			/**
			 * Target Token History
			 * @param int $product_id
			 * @return string[]
			**/
			public function get_the_purchased_token_signers( int $product_id )
			{

				$the_token_signers = $this->get_purchased_token_signers();
				if ( isset( $the_token_signers[ $product_id ] ) 
					&& is_array( $the_token_signers[ $product_id ] )
					&& 0 < count( $the_token_signers[ $product_id ] )
				) {
					return $the_token_signers[ $product_id ];
				}

				return array();

			}

			/**
			 * The Latest Token
			 * @param int $product_id
			 * @return string
			**/
			public function get_the_latest_purchased_token_signer( int $product_id )
			{

				$the_token_signers = $this->get_the_purchased_token_signers( $product_id );
				$latest_version_index = count( $the_token_signers ) - 1;
				if ( array( $the_token_signers[ $latest_version_index ] ) 
					&& 0 < count( $the_token_signers[ $latest_version_index ] )
				) {
					return $the_token_signers[ $latest_version_index ];
				}

				return array();

			}



	/**
	 * Version
	**/

}
}

