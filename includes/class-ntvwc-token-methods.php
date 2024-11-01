<?php

// JWT
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\ValidationData;

// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Token_Methods' ) ) {
/**
 * Option Manager
 * Should be initialized early
**/
class NTVWC_Token_Methods {

		/**
		 * Parse JWT from String
		 * @param string $string
		 * @return string|Token : Returns if error occured
		**/
		public static function parse_from_string( $string )
		{

			try {
				$token = ( new \Lcobucci\JWT\Parser() )->parse( ( string ) $string ); // Parses from a string
			} catch ( InvalidArgumentException $e ) {
				return $e->getMessage();
			}

			return $token;

		}

		/**
		 * Parse JWT from String
		 * @param string|Token $token
		 * @return string|Token : Returns if error occured
		**/
		public static function is_token( $token )
		{

			try {
				$token = NTVWC_Token_Methods::parse_from_string( $token );
			} catch ( InvalidArgumentException $e ) {
				return $e->getMessage();
			}

			if ( 'Lcobucci\JWT\Token' === get_class( $token ) ) {
				return true;
			}

			return false;

		}

		/**
		 * Get JWT signer
		 * @param  [array]  $args
		 * @return [signer|bool] 
		 */
		public static function get_jwt_signer( $args = array() )
		{
			$signer = apply_filters( 'ntvwc_filter_jwt_signer', new Sha256(), $args );
			return $signer;
		}

		/**
		 * Get JWT signer type
		 * @param  [array]  $args
		 * @return [string]
		 */
		public static function get_jwt_signer_type( $args = array() )
		{
			$signer_type = apply_filters( 'ntvwc_filter_jwt_signer', 'Sha256', $args );
			return $signer_type;
		}

		/**
		 * Get JWT signer type
		 * @param  [array]  $args
		 * @param  [Signer]  $args
		 * @return [string]
		 */
		public static function get_the_jwt_signer_type( Signer $signer, $args = array() )
		{
			$signer_type = apply_filters( 'ntvwc_filter_jwt_signer', 'Sha256', $args );
			return $signer_type;
		}

		/**
		 * Get JWT signer type
		 * @param  [array]  $args
		 * @param  [Signer]  $args
		 * @return [string]
		 */
		public static function get_builder()
		{
			return new Builder();
		}

		/**
		 * Set the data to the builder
		 * 		'user_id', 'order_id', 'order_key', 'product_id', 'product_name', 'access_expiry', 'restrict_access', '', '', 
		 * @param Builder &$builder
		 * @param array   $data
		 * @return bool|Builder description
		**/
		public static function set_data_to_the_builder( Builder $builder, $data = array() )
		{
			if ( ! is_array( $data ) || 0 >= count( $data ) ) {
				return false;
			}

			foreach ( $data as $data_index => $data_value ) {
				$builder = $builder->set( $data_index, $data_value );
			}

			return $builder;

		}

		/**
		 * Generate token by download params from "ntvwc_get_download_params_by_order_id( $order_id )"
		 * 
		 * @param array  $token_params
		 * @param string $hashed_secret
		 * @param int    $token_index
		 * @param int    $timestamp
		 * @return JWT description
		**/
		public static function generate_token(
			$token_params,
			$hashed_secret = '',
			$timestamp = null
		)
		{

			// Home URL
				$home_url = get_site_url();

			// Current time
			if ( ! is_int( $timestamp )
				|| null === $timestamp
			) {
				$current_time = intval( current_time( 'timestamp', true ) );
			} else {
				$current_time = intval( $timestamp );
			}

			// Builder
				$builder = ( NTVWC_Token_Methods::get_builder() )->setIssuer( $home_url ) // Configures the issuer (iss claim)
					->setId( $token_params['token_id'], true ) // Configures the id (jti claim), replicating as a header item
					->setIssuedAt( $current_time ) // Configures the time that the token was issue (iat claim)
					->set( 'ntvwc_version', Nora_Token_Vendor_For_WooCommerce::PLUGIN_VERSION )
					->set( 'hashed_secret', $hashed_secret ); // Configures a new claim, called "wp_content_type"

				$builder = NTVWC_Token_Methods::set_data_to_the_builder( $builder, $token_params );

			// Sign
				// Signer : Sha256 as default
				$signer   = NTVWC_Token_Methods::get_jwt_signer();
				$sign_str = NTVWC_Token_Methods::generate_sign_str_from_token_params( $token_params, $hashed_secret );

			// Token
				$token = $builder
					->sign( $signer, $sign_str ) // creates a signature using "testing" as key
					->getToken(); // Retrieves the generated token

			// End
				return $token;

		}

		/**
		 * Generate Sign by download params from "ntvwc_get_download_params_by_order_id( $order_id )"
		 * 
		 * @param array  $token_params
		 * @param string $hashed_secret : Default ""
		 * @return JWT description
		**/
		public static function generate_sign_str_from_token_params( $token_params, $hashed_secret = '' )
		{

			if ( ! isset( $token_params['order_key'] ) 
				|| ! isset( $token_params['product_id'] ) 
			) {
				return false;
			}

			$sign_str = $token_params['token_id'];
			if ( isset( $token_params['user_id'] )
				&& is_string( $token_params['user_id'] )
				&& '' !== $token_params['user_id']
			) {
				$sign_str = $token_params['user_id'] . '_' . $sign_str;
			}

			// Secret
			if ( is_string( $hashed_secret ) && '' !== $hashed_secret ) {
				$sign_str = $hashed_secret . '-' . $sign_str;
			}

			return $sign_str;

		}




}
}
