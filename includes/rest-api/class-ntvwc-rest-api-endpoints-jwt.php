<?php

use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;


if ( ! class_exists( 'NTVWC_REST_API_Endpoints_JWT' ) ) {
/**
 * JWT Auth in Public
 * 
**/
class NTVWC_REST_API_Endpoints_JWT extends NTVWC_REST_API_Endpoints {

	/**
	 * Consts
	**/
		const CLIENT_VERSION_KEY = 'client_version';
		const VALUE_KEY          = 'value';
		const TOKEN_KEY          = 'token';
		const UPDATE_TOKEN_KEY   = 'update_token';
		const CLIENT_URI_KEY     = 'client_uri';
	
		/**
		 * The auth type.
		**/
		protected $type = 'token';

		/**
		 * Validation Token
		 * @var Token
		**/
		protected $validation_token = null;

		/**
		 * Update Token
		 * @var Token
		**/
		protected $update_token = null;

	/**
	 * Init
	**/
		/**
		 * Constructor
		 * 
		 * @param string $plugin_name
		 * @param string $version
		 * @param string $type        : 
		**/
		function __construct( $plugin_name, $version )
		{

			// Parent
			parent::__construct( $plugin_name, $version );

		}

		/**
		 * Register REST routes
		 * 
		 * @param WP_REST_Server $wp_rest_server
		**/
		public function register_rest_routes( $wp_rest_server = '' )
		{

			parent::register_rest_routes( $wp_rest_server );

			// Validate token
			register_rest_route( $this->namespace, '/token/validate', array(
				'methods' => 'POST',
				'callback' => array( $this, 'validate' )
			) );

		}

		/**
		 * Add CORs suppot to the request.
		 * Required define const "NTVWC_CORS_ENABLE_JWT_AUTH" to be true
		 * 
		**/
		public function add_cors_support()
		{
			parent::add_cors_support();
		}

	/**
	 * Validations
	**/
		/**
		 * Validate
		 * 
		 * @param WP_REST_Request $request
		 * @param string          $post_fields_key
		 * 
		 * @return string|Token : Returns string for error.
		**/
		public function validate( $request, $post_fields_key = 'ntvwc' )
		{

			try {

				// Params
					$params = $this->parse_request_into_params( $request, $post_fields_key );

				// Token
					$token = ntvwc_get_bearer_token();
					if ( null === $token ) {
						if ( ! isset( $params[ self::TOKEN_KEY ] ) || '' === $params[ self::TOKEN_KEY ] ) {
							throw new NTVWC_Exception( esc_html__( 'Token is not set.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
						}
						$token = $params[ self::TOKEN_KEY ];
					}

				// Validate for update checker in the certain version
					$result = $this->validate_for_each_client( $params );
					// Update checker version
					if ( is_string( $result ) ) {
						throw new NTVWC_Exception( $result );
					}

				// Validate the JWT
					$token_obj = $this->validate_token( $params );
					if ( is_string( $token_obj ) ) {
						throw new NTVWC_Exception( $token_obj );
					}

				// Validate the hashed URL
				do_action( 'ntvwc_action_rest_api_endpoint_validate', $request, $params, $token_obj );

			} catch ( NTVWC_Exception $e ) {

				//$this->send_header( 400 );
				echo json_encode( array(
					'error_message' => $e->getMessage(),
				), JSON_UNESCAPED_UNICODE );
				die();

			} catch ( Exception $e ) {

				//$this->send_header( 400 );
				echo json_encode( array(
					'error_message' => $e->getMessage(),
				), JSON_UNESCAPED_UNICODE );
				die();

			}

			// Maybe Update Token
				$returned_data = array(
					'expiry'           => $token_obj->hasClaim( 'access_expiry' ) ? $token_obj->getClaim( 'access_expiry' ) : -1,
					'token'            => $token_obj->__toString(),
					'is_token_updated' => $params[ self::TOKEN_KEY ] !== $token_obj->__toString()
				);

			// End
				echo json_encode( $returned_data, JSON_UNESCAPED_UNICODE );
				die();

		}

		/**
		 * Protected
		**/
			/**
			 * Validate JWT Token
			 * 
			 * 1. Validate the token
			 * 2. Check the version and the expiry
			 * 3. Update the JWT and return it before the expiry if the version is updated
			 * 4. 
			 * 
			 * @param array $params
			 * 
			 * @return string|Token : Returns string for error
			**/
			protected function validate_token( $params )
			{

				if ( isset( $params[ self::TOKEN_KEY ] ) && '' !== $params[ self::TOKEN_KEY ] ) {
					$token_obj = ntvwc()->get_token_manager()->validate_token( $params[ self::TOKEN_KEY ] );
				}
				if ( is_string( $token_obj ) ) {
					return $token_obj;
				}

				// Maybe update token
				if ( isset( $params[ self::UPDATE_TOKEN_KEY ] ) && '' !== $params[ self::UPDATE_TOKEN_KEY ] ) {
					$update_token_obj = ntvwc()->get_token_manager()->validate_token( $params[ self::UPDATE_TOKEN_KEY ], 'update' );
					if ( is_string( $update_token_obj ) ) {
						$messages = array(
							esc_html__( 'Update Token is invalid.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
							$update_token_obj
						);
						return implode( '<br>', $messages );
					}

					if ( ! $update_token_obj->hasClaim( 'type' )
						|| ! in_array( $update_token_obj->getClaim( 'type' ), array( 'validation', 'update' ) )
					) {
						$messages = array(
							esc_html__( 'Token type is not set or is not valid.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
							$update_token_obj
						);
						return implode( '<br>', $messages );
					}
					if ( 'update' === $update_token_obj->getClaim( 'type' ) ) {
						$token_obj = apply_filters(
							'ntvwc_filter_rest_api_maybe_update_token',
							$token_obj,
							$update_token_obj,
							$params
						);
					}
				}

				// Expire
				$token_obj = ntvwc()->get_token_manager()->validate_expiry( $token_obj );
				if ( is_string( $token_obj ) ) {
					return $token_obj;
				}

				// Value
				$token_obj = ntvwc()->get_token_manager()->validate_value( $token_obj, $params[ self::VALUE_KEY ] );

				// End
					return $token_obj;

			}

			/**
			 * Validate JWT Token v1.0.0
			 * 
			 * 1. Validate the token
			 * 2. Check the version and the expiry
			 * 3. Update the JWT and return it before the expiry if the version is updated
			 * 4. 
			 * 
			 * @param array $params
			 * 
			 * @return [bool|string] : Returns string for error
			**/
			protected function validate_for_each_client( $params )
			{

				// Vars
				$client_version = $params[ self::CLIENT_VERSION_KEY ];

				// Check by the update checker version
				$client_version_in_underscore = str_replace( array( '.' ), '_', $client_version );
				$method = 'validate_for_update_checker_' . $client_version_in_underscore;
				if ( method_exists( $this, $method ) ) {
					$result = call_user_func_array(
						array( $this, $method ),
						array( $params )
					);
					if ( ! $result ) {
						$message = esc_html__( 'Error for the update checker version %1$s', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
						return sprintf( $message, $client_version );
					}
					unset( $result );
				}

				return true;

			}

				/**
				 * Validate JWT Token v1.0.0
				 * 
				 * 1. Validate the token
				 * 2. Check the version and the expiry
				 * 3. Update the JWT and return it before the expiry if the version is updated
				 * 4. 
				 * 
				 * @param array $params
				 * 
				 * @return [bool|string] : Returns string for error
				**/
				protected function validate_for_update_checker_1_0_0( $params )
				{

					return true;

				}

		/**
		 * Parse into params
		 * @param  WP_REST_Request $request
		 * @throws NTVWC_Exception
		 * @return array
		**/
		protected function parse_request_into_params( $request, $post_fields_key = 'ntvwc' )
		{

			// Has Required Data
				$ntvwc = $request->get_param( $post_fields_key );
				if ( null === $ntvwc ) {
					throw new NTVWC_Exception( esc_html__( 'Required param doesn\'t exist.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}
			// Data
				$params = json_decode( str_replace( array( '\\"' ), '"', urldecode( $ntvwc ) ), true );
				if ( ! is_array( $params ) ) {
					throw new NTVWC_Exception( esc_html__( 'Data could not be decoded.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				}

			// End
				return $params;

		}

	/**
	 * Headers
	**/


}
}