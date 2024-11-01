<?php

// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Data_Token' ) ) {
/**
 * Data
 * 
 * 
**/
class NTVWC_Data_Token {

	/**
	 * Properties
	**/
		/**
		 * ID
		**/
		protected $id;

		/**
		 * Token Data
		**/
		protected $data = array();

		/**
		 * 
		 * 
		**/
		protected $defaults = array(
			'token_id'         => '',
			'order_id'         => '',
			'order_key'        => '',
			'product_id'       => '',
			'type'             => 'validation',
			'expiry'           => '',
			'restrict_access'  => 'no',
			'purchased_number' => 0
		);

		/**
		 * Post
		**/
		protected $post = null;

		/**
		 * Post Meta Names
		**/
		protected $post_meta_names = array(
			'values'            => '_ntvwc_used_values',
			'secrets'           => '_ntvwc_used_secrets',
			'tokens'            => '_ntvwc_purchased_tokens',
			'signers'           => '_ntvwc_used_signers',
			'order_id'          => '_ntvwc_order_id',
			'product_id'        => '_ntvwc_product_id',
			'purchased_number'  => '_ntvwc_purchased_number',
		);


	/**
	 * Settings
	**/
		/**
		 * Call
		**/
		public function __call( $method, $args )
		{

			// get_{$prop}();
			if ( preg_match( '/^get\_/i', $method ) ) {
				$prop_name = preg_replace( '/^get\_/i', '', $method );
				if ( empty( $args ) && isset( $this->defaults[ $prop_name ] ) ) {
					return $this->get_prop( $prop_name );
				}
			}

		}

	/**
	 * Init
	**/
		/**
		 * Public Init
		 * @param mixed $token_or_order_id
		 * @return string|NTVWC_Data_Token 
		**/
		public static function get_instance( $token_or_order_id, $product_id = null, $index = 0 )
		{
			try {
				$instance = new Self( $token_or_order_id, $product_id, $index );
			} catch ( Exception $e ) {
				return $e->getMessage();
			}
			return $instance;
		}

		/**
		 * Constructor
		 * @param int $token_or_order_id : Token type id. but with product_id, can be order_id
		 * @param int $product_id
		 * @throws Exception description
		**/
		protected function __construct( $token_or_order_id, $product_id = null, $index = 0 )
		{

			if ( is_numeric( $product_id ) ) {

				try {
					$this->read_from_order_and_product( $token_or_order_id, $product_id, $index );
				} catch ( Exception $e ) {
					throw new Exception( 'Wrong Input.', 0, $e );
				}

				try {
					$this->register( $index );
				} catch ( Exception $e ) {
					throw new Exception( 'Something wrong.', 0, $e );
				}

			} elseif ( ! is_numeric( $product_id ) ) {
				$wp_post = WP_Post::get_instance( intval( $token_or_order_id ) );
				if ( in_array( $wp_post, array( null, false ) ) ) {
					throw new Exception( esc_html__( 'Wrong ID.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				} elseif ( 
					'WP_Post' !== get_class( $wp_post )
					|| 'ntvwc-token' !== $wp_post->post_type 
				) {
					throw new Exception( sprintf( 
						esc_html__( 'Wrong Post Type: %1$s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						$wp_post->post_type
					) );
				}
				$this->id   = intval( $wp_post->ID );
				$this->data = $this->get_token_params();
				$this->read_order();
				$this->read_product();

			}

		}

			/**
			 * Init
			 * @param int $order_id
			 * @param int $product_id
			 * @throws Exception
			**/
			protected function read_from_order_and_product( $order_id, $product_id, $index = 0 )
			{

				if ( ! isset( $order_id ) 
					|| 0 >= intval( $order_id )
					|| ! isset( $product_id )
					|| 0 >= intval( $product_id )
				) {
					return false;					
				}

				$wc_order = WC()->order_factory->get_order( $order_id );
				if ( false === $wc_order ) {
					throw new Exception( esc_html__( 'Wrong Order ID.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}
				$ntvwc_order = new NTVWC_Order( $wc_order->get_id() );

				$wc_product = WC()->product_factory->get_product( $product_id );
				if ( false === $wc_product ) {
					throw new Exception( esc_html__( 'Wrong Product ID.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}
				$is_type_token = get_post_meta( $product_id, '_ntvwc_type_token', true );
				if ( ! is_string( $is_type_token ) || ! in_array( $is_type_token, array( 'yes', 'no' ) ) ) {
					throw new Exception( esc_html__( 'This is not token product.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

				if ( ! $ntvwc_order->has_token_item( $product_id ) ) {
					throw new Exception( esc_html__( 'Order does not have such a token product.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

				$this->set_prop( 'order_id', intval( $wc_order->get_id() ) );
				$this->set_prop( 'product_id', intval( $wc_product->get_id() ) );

				//$purchased_token_data = new NTVWC_Data_Purchased_Token( $ntvwc_order->get_id(), $wc_product );
				//$token_data = $purchased_token_data->get_data();
				$this->data = $ntvwc_order->generate_token_params_by_product_id( $wc_product->get_id() );
				$this->read_order();
				$this->read_product();

			}

		/**
		 * Register as Post type token
		**/
		protected function register( $index = 0 )
		{


			$this->read_order();
			$this->read_product();

			if ( 'NTVWC_Order' !== get_class( $this->ntvwc_order )
				|| ! in_array( get_class( $this->wc_product ), array( 'WC_Product_Simple', 'WC_Product_Variation' ) )
			) {
				return false;
			}

			$order_id = intval( $this->ntvwc_order->get_id() );
			$product_id = intval( $this->wc_product->get_id() );

			$purchased_token_data = new NTVWC_Data_Purchased_Token( $this->ntvwc_order->get_id(), $this->wc_product );
			$token_params = $this->ntvwc_order->generate_token_params_by_token_item( $purchased_token_data->get_data() );

			// Data 
				// Used Value 
					$used_value = '';
					if ( 'validation' === $token_params['type'] ) {
						$used_value = get_post_meta( $product_id, '_ntvwc_token_value', true );
						$used_value = ( is_string( $used_value ) && '' !== $used_value ? $used_value : '' );
					} elseif ( 'update' === $token_params['type'] ) {
						$used_value = get_post_meta( $product_id, '_ntvwc_token_validation_value', true );
						$used_value = ( is_string( $used_value ) && '' !== $used_value ? $used_value : '' );
					}

				// Current secret
					$data_option = ntvwc_get_data_option( 'token_vendor' );
					$option_data = $data_option->get_data();
					$current_secret = hash( 'sha256', ( 
						is_string( $option_data['jwt_secret_key'] ) 
						? $option_data['jwt_secret_key'] 
						: ''
					) );

				// Token ID
					$token_params['token_id'] = $token_params['token_id'] . '_' . $index;
					$token_params['registered_index'] = $index;

			// Generate
				// Token
					$new_token = NTVWC_Token_Methods::generate_token( $token_params, $current_secret );
				// Sign String Used for the token
					$sign_str = NTVWC_Token_Methods::generate_sign_str_from_token_params( $token_params, $current_secret );

			$post_author = $this->ntvwc_order->get_customer_id();
			$post_title_format = '%1$s-%2$s-%3$d';
			$post_title = sprintf(
				$post_title_format,
				$this->ntvwc_order->get_order_key(),
				$this->wc_product->get_id(),
				$index
			);

			$post_arr = array(
				'post_author'    => $post_author,
				'post_content'   => '',
				'post_title'     => $post_title,
				'post_excerpt'   => '',
				'post_status'    => 'publish',
				'post_type'      => 'ntvwc-token',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'post_parent'    => 0,
			);

			$post_id = wp_insert_post( $post_arr );
			if ( 0 === $post_id 
				|| is_wp_error( $post_id )
			) {
				return false;
			}
			$this->id = $post_id;

			$this->update_token_params( $token_params );

			// Used Tokens
			$result = $this->append_purchased_tokens( $new_token->__toString() );
			if ( ! $result ) {
				return false;
			}
			// Used Value
			$this->append_used_values( $used_value );
			// Used Secret
			$this->append_used_secrets( $current_secret );
			// Used Signer
			$this->append_used_signers( array(
				'signer' => NTVWC_Token_Methods::get_jwt_signer_type(),
				'string' => $sign_str
			) );

		}

		/**
		 * Update Token
		 * @param array $new_params
		 * @return Token
		**/
		public function update_expiry( $new_params = array() )
		{

			$result = $this->new_params_is_valid( $new_params );
			if ( ! $result ) {
				return esc_html__( 'Wrong Params.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
			}

			$this->read_order();
			$this->read_product();

			// Vars
				$order_id   = $this->ntvwc_order->get_id();
				$product_id = $this->wc_product->get_id();

			// Data 
				// Used Value 
					$used_value = '';
					if ( 'validation' === $new_params['type'] ) {
						$used_value = get_post_meta( $product_id, '_ntvwc_token_value', true );
						$used_value = ( is_string( $used_value ) && '' !== $used_value ? $used_value : '' );
					} elseif ( 'update' === $new_params['type'] ) {
						$used_value = get_post_meta( $product_id, '_ntvwc_token_validation_value', true );
						$used_value = ( is_string( $used_value ) && '' !== $used_value ? $used_value : '' );
					}

				// Current secret
					$data_option = ntvwc_get_data_option( 'token_vendor' );
					$option_data = $data_option->get_data();
					$current_secret = hash( 'sha256', ( 
						is_string( $option_data['jwt_secret_key'] ) 
						? $option_data['jwt_secret_key'] 
						: ''
					) );

				// Token Index
					$token_index = intval( $this->get_the_token_index( $this ) );
					$new_token_index = intval( $this->get_the_latest_token_index() ) + 1;

			// Generate
				// Token
					$new_token = NTVWC_Token_Methods::generate_token( $new_params, $current_secret );

				// Sign String Used for the token
					$sign_str = NTVWC_Token_Methods::generate_sign_str_from_token_params( $new_params, $current_secret );

			// Append
				$result = $this->append_purchased_tokens( $new_token->__toString() );
				if ( ! $result ) {
					return $new_token;
				}
				$result = $this->append_used_values( $used_value );
				$result = $this->append_used_secrets( $current_secret );
				$result = $this->append_used_signers( array(
					'signer' => NTVWC_Token_Methods::get_jwt_signer_type(),
					'string' => $sign_str
				) );

				$this->update_token_params( $new_params );

				return $new_token;

		}

			/**
			 * Check the new params
			 * @param array $new_params
			 * @return bool
			**/
			protected function new_params_is_valid( $new_params )
			{

				if ( is_array( $new_params ) 
					&& 0 < count( $new_params )
					&& isset( $new_params['order_id'] ) && ! empty( $new_params['order_id'] )
					&& isset( $new_params['product_id'] ) && ! empty( $new_params['product_id'] )
					&& isset( $new_params['order_key'] ) && ! empty( $new_params['order_key'] )
					&& isset( $new_params['type'] ) && ! empty( $new_params['type'] )
					&& isset( $new_params['expiry'] ) && ! empty( $new_params['expiry'] )
					&& isset( $new_params['restrict_access'] ) && ! empty( $new_params['restrict_access'] )
					&& isset( $new_params['purchased_number'] ) && ! empty( $new_params['purchased_number'] )
					&& isset( $new_params['date_completed'] ) && ! empty( $new_params['date_completed'] )
					&& isset( $new_params['access_expiry'] ) && ! empty( $new_params['access_expiry'] )
					&& isset( $new_params['product_name'] ) && ! empty( $new_params['product_name'] )
				) {
					return true;
				}

				return false;

			}

	/**
	 * Readers
	**/
		/**
		 * Order
		**/
		public function read_order()
		{

			$order_id = $this->get_order_id();
			if ( is_numeric( $order_id ) && 0 < intval( $order_id ) ) {
				$this->ntvwc_order = new NTVWC_Order( intval( $order_id ) );
			}

		}

		/**
		 * Product
		**/
		public function read_product()
		{

			$product_id = $this->get_product_id();
			if ( is_numeric( $product_id ) && 0 < intval( $product_id ) ) {
				$this->wc_product = WC()->product_factory->get_product( intval( $product_id ) );
			}

		}

		/**
		 * Product
		**/
		public function read_data()
		{
			if ( ! is_numeric( $this->get_id() )
				|| 0 >= intval( $this->get_id() )
			) {
				throw new Exception( esc_html__( 'Read Data Error: Invalid ID.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
			}
			$this->data = ntvwc_get_post_meta( $this->get_id(), '_ntvwc_token_params' );
		}

	/**
	 * Getters
	**/
		/**
		 * Get $this->id
		 * @return int
		**/
		public function get_id()
		{
			return $this->id;
		}

		/**
		 * Get $this->id
		 * @return array
		**/
		public function get_data()
		{
			$this->read_data();
			return $this->data;
		}

		/**
		 * Prop of $this->data
		 * @param string $key
		 * @return mixed
		**/
		public function get_prop( $key )
		{
			if ( isset( $this->data[ $key ] ) && ! empty( $this->data[ $key ] ) ) {
				return $this->data[ $key ];
			}
			return false;
		}

		/**
		 * Order Object
		 * @return bool|NTVWC_Order
		**/
		public function get_order()
		{

			$this->read_order();
			if ( isset( $this->ntvwc_order ) && null !== $this->ntvwc_order ) {
				return $this->ntvwc_order;
			}
			return false;

		}

		/**
		 * Product Object
		 * @return bool|WC_Product
		**/
		public function get_product()
		{

			$this->read_product();
			if ( isset( $this->wc_product ) && null !== $this->wc_product ) {
				return $this->wc_product;
			}
			return false;

		}

	/**
	 * Setters
	**/
		/**
		 * Set $this->id
		 * @return int
		**/
		public function set_id( int $id )
		{
			if ( is_numeric( $id ) && 0 < intval( $id ) ) {
				$this->id = $id;
				return true;
			}
			return false;
		}

		public function set_props()
		{

			if ( ! isset( $this->id ) 
				|| is_int( $this->id ) 
				|| 0 < intval( $this->id )
			) {
				return false;
			}

			$this->set_prop();

			return true;

		}

		/**
		 * Set Prop of $this->data
		 * @param strig $key
		 * @param mixed $value
		 * @return bool
		**/
		public function set_prop( $key, $value )
		{

			if ( ! is_string( $key ) 
				|| '' === $key
			) {
				return false;
			}

			if ( isset( $defaults[ $key ] ) && ! empty( $value ) ) {
				$this->data[ $key ] = $value;
				return true;
			}

			return false;

		}

	/**
	 * Options
	**/
		/**
		 * Values
		**/
			/**
			 * Get
			 * @return string[]
			**/
			public function get_used_values()
			{
				$used_values = ntvwc_get_post_meta( $this->id, '_ntvwc_used_values' );
				if ( is_array( $used_values ) && 0 < count( $used_values ) ) {
					return $used_values;
				}
				return array();
			}

			/**
			 * Update
			 * @param string[] $new_value
			 * @return bool
			**/
			protected function update_used_values( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_ntvwc_used_values', $new_value );
				return $update_result;
			}

			/**
			 * Append
			 * @param string $new_value
			 * @return bool
			**/
			public function append_used_values( $new_value )
			{
				$used_values = $this->get_used_values();
				$latest_token_index = ntvwc_array_key_last( $used_values );
				$new_token_index = $latest_token_index + 1;
				$used_values[ $new_token_index ] = $new_value;
				$used_values_json = json_encode( $used_values, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_values( $used_values_json );
				return $update_result;
			}

			/**
			 * Cancel
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_used_values( $index = -1 )
			{
				$used_values = $this->get_used_values();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $used_values );
				}
				unset( $used_values[ $index ] );
				$used_values_json = json_encode( $used_values, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_values( $used_values_json );
				return $update_result;
			}

		/**
		 * Secrets
		**/
			/**
			 * Get
			 * @return string[]
			**/
			public function get_used_secrets()
			{
				$used_secrets = ntvwc_get_post_meta( $this->id, '_ntvwc_used_secrets' );
				if ( is_array( $used_secrets ) && 0 < count( $used_secrets ) ) {
					return $used_secrets;
				}
				return array();
			}

			/**
			 * Update
			 * @param string[] $new_value
			 * @return bool
			**/
			protected function update_used_secrets( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_ntvwc_used_secrets', $new_value );
				return $update_result;
			}

			/**
			 * Append
			 * @param string $new_value
			 * @return bool
			**/
			public function append_used_secrets( $new_value )
			{
				$used_secrets = $this->get_used_secrets();
				$latest_token_index = ntvwc_array_key_last( $used_secrets );
				$new_token_index = $latest_token_index + 1;
				$used_secrets[ $new_token_index ] = $new_value;
				$used_secrets_json = json_encode( $used_secrets, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_secrets( $used_secrets_json );
				return $update_result;
			}

			/**
			 * Cancel
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_used_secrets( $index = -1 )
			{

				$used_secrets = $this->get_used_secrets();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $used_secrets );
				}
				unset( $used_secrets[ $index ] );
				$used_secrets_json = json_encode( $used_secrets, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_secrets( $used_secrets_json );
				return $update_result;
			}

		/**
		 * Tokens
		**/
			/**
			 * Get
			 * @return string[]
			**/
			public function get_purchased_tokens()
			{
				$purchased_token = ntvwc_get_post_meta( $this->id, '_ntvwc_purchased_tokens' );
				if ( is_array( $purchased_token ) && 0 < count( $purchased_token ) ) {
					return $purchased_token;
				}
				return array();
			}

			/**
			 * Update
			 * @param string[] $new_value
			 * @return bool
			**/
			protected function update_purchased_tokens( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_ntvwc_purchased_tokens', $new_value );
				return $update_result;
			}

			/**
			 * Append
			 * @param string $new_value
			 * @return bool
			**/
			public function append_purchased_tokens( $new_value )
			{
				$purchased_token = $this->get_purchased_tokens();
				$latest_token_index = ntvwc_array_key_last( $purchased_token );
				$new_token_index = $latest_token_index + 1;
				$new_token = $this->sanitize_token( $new_value );
				if ( null === $new_token ) {
					return false;
				}
				$purchased_token[ $new_token_index ] = $new_token;
				$purchased_token_json = json_encode( $purchased_token, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_purchased_tokens( $purchased_token_json );
				return $update_result;
			}

			/**
			 * Cancel
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_purchased_tokens( $index = -1 )
			{
				$purchased_token = $this->get_purchased_tokens();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $purchased_token );
				}
				unset( $purchased_token[ $index ] );
				$purchased_token_json = json_encode( $purchased_token, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_purchased_tokens( $purchased_token_json );
				return $update_result;
			}

		/**
		 * Signers
		**/
			/**
			 * Get
			 * @return array
			**/
			public function get_used_signers()
			{
				$used_signers = ntvwc_get_post_meta( $this->id, '_ntvwc_used_signers' );
				if ( is_array( $used_signers ) && 0 < count( $used_signers ) ) {
					return $used_signers;
				}
				return array();
			}

			/**
			 * Update
			 * @param array[] $new_value
			 * @return bool
			**/
			protected function update_used_signers( $new_value )
			{
				$update_result = update_post_meta( $this->id, '_ntvwc_used_signers', $new_value );
				return $update_result;
			}

			/**
			 * Update
			 * @param array $new_value
			 * @return bool
			**/
			public function append_used_signers( $new_value )
			{
				$used_signers = $this->get_used_signers();
				$latest_token_index = ntvwc_array_key_last( $used_signers );
				$new_token_index = $latest_token_index + 1;
				$used_signers[ $new_token_index ] = $new_value;
				$used_signers_json = json_encode( $used_signers, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_signers( $used_signers_json );
				return $update_result;
			}

			/**
			 * Update
			 * @param int   $index
			 * @return bool
			**/
			public function cancel_used_signers( $index = -1 )
			{
				$used_signers = $this->get_used_signers();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $used_signers );
				}
				unset( $used_signers[ $index ] );
				$used_signers_json = json_encode( $used_signers, JSON_UNESCAPED_UNICODE );

				$update_result = $this->update_used_signers( $used_signers_json );
				return $update_result;
			}

		/**
		 * Properties
		**/
			/**
			 * Update
			 * @return null|array
			**/
			public function get_token_params()
			{

				$token_params_json  = get_post_meta( $this->get_id(), '_ntvwc_token_params', true );
				$token_params = json_decode( $token_params_json, true );
				if ( null === $token_params ) {
					return false;
				}
				return $token_params;

			}

			/**
			 * Update
			 * @param array $new_value
			 * @return bool
			**/
			public function update_token_params( $new_value )
			{

				if ( ! is_array( $new_value ) 
					|| 0 >= count( $new_value )
				) {
					return false;
				}

				$new_value_json = json_encode( $new_value, JSON_UNESCAPED_UNICODE );
				$update_result  = update_post_meta( $this->get_id(), '_ntvwc_token_params', $new_value_json );
				return $update_result;

			}

		/**
		 * Sanitizers
		**/
			/**
			 * Token
			 * @param string $value
			 * @param mixed $default : Default null
			 * @return string
			**/
			public function sanitize_token( $value, $default = null )
			{
				if ( ! is_string( $value ) || '' === $value ) {
					return $default;
				}
				if ( preg_match( "/^[a-zA-Z0-9\-_]+?\.[a-zA-Z0-9\-_]+?\.([a-zA-Z0-9\-_]+)?$/", $value ) ) {
					return $value;
				}
				return $default;
			}

	/**
	 * Tools
	**/
		/**
		 * Value
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|string
			**/
			public function get_used_value( int $index = -1 )
			{

				$used_values = $this->get_used_values();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $used_values );
				}
				if ( is_array( $used_values ) 
					&& 0 < count( $used_values ) 
					&& isset( $used_values[ $index ] )
					&& is_string( $used_values[ $index ] )
					&& '' !== $used_values[ $index ]
				) {
					return $used_values[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|string
			**/
			public function get_the_latest_used_value()
			{

				return $this->get_used_value( -1 );

			}

		/**
		 * Secret
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|string
			**/
			public function get_used_secret( int $index = -1 )
			{

				$used_secrets = $this->get_used_secrets();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $used_secrets );
				}
				if ( is_array( $used_secrets ) 
					&& 0 < count( $used_secrets ) 
					&& isset( $used_secrets[ $index ] )
					&& is_string( $used_secrets[ $index ] )
					&& '' !== $used_secrets[ $index ]
				) {
					return $used_secrets[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|string
			**/
			public function get_the_latest_used_secret()
			{

				return $this->get_used_secret( -1 );

			}

		/**
		 * Token
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|string
			**/
			public function get_purchased_token( int $index = -1 )
			{

				$purchased_tokens = $this->get_purchased_tokens();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $purchased_tokens );
				}
				
				if ( is_array( $purchased_tokens ) 
					&& 0 < count( $purchased_tokens ) 
					&& isset( $purchased_tokens[ $index ] )
					&& is_string( $purchased_tokens[ $index ] )
					&& '' !== $purchased_tokens[ $index ]
				) {
					return $purchased_tokens[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|string
			**/
			public function get_the_latest_purchased_token()
			{

				return $this->get_purchased_token( -1 );

			}

			/**
			 * Get the latest purchased token
			 * @param string $token
			 * @return bool
			**/
			public function is_the_latest_purchased_token( string $token = '' )
			{

				return $this->get_purchased_token( -1 ) === $token;

			}

		/**
		 * Signers
		**/
			/**
			 * Get the target purchased token
			 * @param int $index : Default -1
			 * @return null|array
			**/
			public function get_used_signer_by_index( int $index = -1 )
			{

				$used_signers = $this->get_used_signers();
				if ( -1 === $index ) {
					$index = ntvwc_array_key_last( $used_signers );
				}
				if ( is_array( $used_signers ) 
					&& 0 < count( $used_signers ) 
					&& isset( $used_signers[ $index ] )
					&& is_array( $used_signers[ $index ] )
					&& isset( $used_signers[ $index ]['signer'] )
					&& isset( $used_signers[ $index ]['string'] )
				) {
					return $used_signers[ $index ];
				}

				return null;

			}

			/**
			 * Get the latest purchased token
			 * @return null|array
			**/
			public function get_the_latest_used_signer()
			{

				return $this->get_used_signer_by_index( -1 );

			}

		/**
		 * Index
		**/
			/**
			 * Get the token index
			 * @param string $token
			 * @return null|int
			**/
			public function get_purchased_token_index( $token = '' )
			{
				if ( ! is_string( $token ) 
					|| '' === $token
				) {
					return null;
				}

				$purchased_tokens = $this->get_purchased_tokens();
				if ( is_array( $purchased_tokens ) 
					&& 0 < count( $purchased_tokens ) 
					&& in_array( $token, $purchased_tokens )
				) {
					$key = array_search( $token, $purchased_tokens );
					if ( false === $key ) {
						return null;
					}
					return $key;
				}

				return null;

			}

			/**
			 * Get the latest token index
			 * @return null|int
			**/
			public function get_the_latest_purchased_token_index()
			{

				$purchased_tokens = $this->get_purchased_tokens();
				if ( is_array( $purchased_tokens ) && 0 < count( $purchased_tokens ) ) {
					$key = ntvwc_array_key_last( $purchased_tokens );
					return $key;
				}

				return null;

			}

	/**
	 * Handling Data
	**/
		/**
		 * Decrease the number of the purchased
		 * @param int $number
		 * @return bool|int 
		**/
		public function decrease_purchased_number( int $number = 1 )
		{

			$params = $this->get_token_params();


			$current_number = intval( $params['purchased_number'] );
			if ( 0 >= $current_number ) {
				return false;
			}

			if ( 0 >= $number ) {
				return false;
			}

			$decreased = $current_number - intval( $number );
			if ( 0 > $decreased ) {
				return false;
			}

			$params['purchased_number'] = intval( $decreased );
			$result = $this->update_token_params( $params );
			if ( ! $result ) {
				return false;
			}

			return $decreased;

		}

		/**
		 * Decrease the number of the purchased
		 * @param mixed $product
		 * @param int   $index
		 * @return bool|string
		**/
		public function delete_the_update_token( $product_id, $index = 0 )
		{

			if ( 'update' !== $this->get_type() ) {
				return esc_html__( 'Wrong Type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
			}

			$this->read_order();
			$this->read_product();

			// Vars
				$order_id   = $this->ntvwc_order->get_id();
				$product_id = $this->wc_product->get_id();

				$token_id = $this->ntvwc_order->get_registered_token_id_by_product( $product_id, $this->get_registered_index() );
				if ( null === $token_id
					|| $this->get_id() !== $token_id
				) {
				return esc_html__( 'Token ID not found.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				}

				$result = wp_delete_post( $this->get_id() );
				if ( in_array( $result, array( false, null ) ) ) {
					return esc_html__( 'Something wrong to delete the update token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				}

				do_action( $this->get_prefixed_action_hook( 'ntvwc_action_deleted_update_token', $result, $this ) );

				return true;

		}

		/**
		 * Delete the updated validation token
		 * @return bool|string
		**/
		public function cancel_updated_new_token()
		{

			if ( 'validation' !== $this->get_type() ) {
				return esc_html__( 'Wrong Type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
			}

			$result = $this->cancel_used_values();
			$result = $this->cancel_used_secrets();
			$result = $this->cancel_purchased_tokens();
			$result = $this->cancel_used_signers();

			return true;

		}

}
}












