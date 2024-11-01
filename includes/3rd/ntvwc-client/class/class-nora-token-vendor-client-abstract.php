<?php

if ( ! class_exists( 'Nora_Token_Vendor_Client_Abstract' ) ) {
/**
 * 
**/
abstract class Nora_Token_Vendor_Client_Abstract {

	/**
	 * Consts
	**/
		const REQUEST_URI_VALIDATE = 'wp-json/ntvwc/v1/token/validate';
		const HASH_SALT = '$6$rounds=5000$ntvwc_hash$';
		const HASH_COST = 10;

	/**
	 * Properties
	**/
		/**
		 * Data which have int or string
		**/
		protected $data = array();

		/**
		 * Data
		**/
		protected $_defaults = array(
			'client_uri'   => '',
			'host_uri'     => 'https://token-vendor.com',
		);

		/**
		 * Rersult of the Request
		**/
		protected $result = array();


	/**
	 * Init
	**/
		/**
		 * Init Vars
		 * @param array $data
		 * @return void 
		**/
		public function init_vars( $data = array() )
		{

			// Data
			if ( is_array( $data ) && 0 < count( $data ) ) {
			foreach ( $data as $data_index => $data_value ) {
				if ( isset( $this->_defaults[ $data_index ] )
					&& $this->_defaults[ $data_index ] === $data_value
				) {
					$this->data[ $data_index ] = $this->_defaults[ $data_index ];
					continue;
				}

				if (
					( is_numeric( $data_value )
						|| is_string( $data_value )
					)
					&& '' !== ( string ) $data_value
				) {
					$this->set_prop( $data_index, $data_value );
				}

			}
			}

		}

	/**
	 * Request
	**/
		/**
		 * Get Post Fields by data
		 * @param string $url
		 * @param array  $post_fields
		 * @return mixed
		**/
		protected function http_request( $url, $value, $options = array() )
		{

			if ( $this->isset_result() && $this->is_result_valid() ) {
				return $this->result;
			}

			// Init
			$curl_handle = curl_init( $url );
			// Options
			if( is_array( $options ) && 0 < count( $options ) ) {
				curl_setopt_array( $curl_handle, $options );
			}

			// Result
			try {
				//$buffer = '';
				//ob_start();
				$result = curl_exec( $curl_handle );
				//$buffer = ob_get_clean();
				if ( ! $result ) {
					throw new Exception( 'Exec Result false' );
				}
				// Encoding
				$supported_encodings = implode( ',', mb_list_encodings() );
				$mb_convert_variables = mb_convert_variables(
					mb_internal_encoding(),
					$supported_encodings,
					$result
				);

				// Returned Value
				$this->result['result'] = $result;

				$curl_info = curl_getinfo( $curl_handle );
				if ( false === $curl_info || 400 <= $curl_info['http_code'] ) {
					throw new Exception( 'Info Error.' );
				}

				$this->result['info'] = $curl_info;

			} catch ( Exception $e ) {
				curl_close( $curl_handle );
				trigger_error( $e->getMessage() );
				return false;
			}

			// File
			if( isset( $options[ CURLOPT_RETURNTRANSFER ] ) 
				&& $options[ CURLOPT_RETURNTRANSFER ]
			) {
				$header_size = curl_getinfo( $curl_handle, CURLINFO_HEADER_SIZE );
				$this->result['header'] = substr( $result, 0, $header_size );
				$this->result['body']   = substr( $result, $header_size );
			}

			// Close
			curl_close( $curl_handle );

		}

		/**
		 * Get Post Fields by data
		 * @param array $curl_info
		 * @return bool
		**/
		protected function is_curl_valid_by_info( $curl_info )
		{
			if ( isset( $curl_info['http_code'] ) 
				&& is_numeric( $curl_info['http_code'] )
				&& 400 > intval( $curl_info['http_code'] )
				&& 200 <= intval( $curl_info['http_code'] )
			) {
				return true;
			}
			return false;
		}

		/**
		 * Get HTTP Request header result
		 * @param string $url
		 * @param array  $post_fields
		 * @return mixed
		**/
		protected function http_request_header( $url, $value, $options = array() )
		{
			try {
				$result = $this->http_request( $url, $value, $options );
				if ( ! isset( $result['header'] ) ) {
					throw new Exception( 'HTTP Result doesn\'t have its header.' );
				}
			} catch ( Exception $e ) {
				trigger_error( $e->getMessage() );
				return false;
			}
			return $result['header'];
		}

		/**
		 * Get HTTP Request body result
		 * @param string $url
		 * @param array  $post_fields
		 * @return mixed
		**/
		protected function http_request_body( $url, $value, $options = array() )
		{
			try {
				$result = $this->http_request( $url, $value, $options );
				if ( ! isset( $result['body'] ) ) {
					throw new Exception( 'HTTP Result doesn\'t have its body.' );
				}
			} catch ( Exception $e ) {
				trigger_error( $e->getMessage() );
				return false;
			}
			return $result['body'];
		}


	/**
	 * Helpers
	**/
		/**
		 * Get Post Fields by data
		 * @return mixed
		**/
		public function get_hashed_client_uri()
		{

			try {
				$client_uri = $this->get_client_uri();
				if ( ! is_string( $client_uri ) || '' === $client_uri ) {
					throw new Exception( '$client_uri is not set or is invalid' );
				}
			} catch( Exception $e ) {
				trigger_error( $e->getMessage() );
				return false;
			}

			return @password_hash( 
				$client_uri,
				PASSWORD_BCRYPT,
				array(
					'salt' => self::HASH_SALT,
					'cost' => self::HASH_COST
				)
			);

		}

		/**
		 * Get Post Fields by data
		 * @return mixed
		**/
		protected function get_client_uri()
		{

			try {
				$client_uri = $this->sanitize_client_uri( $this->get_prop( 'client_uri' ) );
				if ( ! is_string( $client_uri ) || '' === $client_uri ) {
					throw new Exception( '$client_uri is not set or is invalid' );
				}
			} catch( Exception $e ) {
					trigger_error( $e->getMessage() );
				return false;
			}

			return $client_uri;

		}

		/**
		 * Get Post Fields by data
		 * @return mixed
		**/
		protected function get_host_uri()
		{

			try {
				$host_uri = $this->sanitize_host_uri( $this->get_prop( 'host_uri' ) );
				if ( ! is_string( $host_uri ) || '' === $host_uri ) {
					throw new Exception( '$host_uri is not set or is invalid' );
				}
			} catch( Exception $e ) {
				trigger_error( $e->getMessage() );
				return false;
			}

			return $host_uri;

		}

		/**
		 * Get Post Fields by data
		 * @param array $options : Default "array()"
		 * @return mixed
		**/
		protected function get_request_post_fields( $options = array() )
		{

			try {

				$token = $this->sanitize_token( $this->get_prop( 'token' ) );
				if ( ! is_string( $token ) || '' === $token ) {
					throw new Exception( '$token is not set or is invalid' );
				}

				$hashed_client_uri = $this->get_hashed_client_uri();
				if ( ! is_string( $hashed_client_uri ) || '' === $hashed_client_uri ) {
					throw new Exception( '$hashed_client_uri is not set or is invalid' );
				}

			} catch( Exception $e ) {
				trigger_error( $e->getMessage() );
				return false;
			}

			$request_post_field = array( 
				'client_version' => self::VERSION,
				'token'          => $token,
				'client_uri'     => $hashed_client_uri,
			);

			if ( is_array( $options ) && 0 < count( $options ) ) {
			foreach ( $options as $option_key => $option_value ) {
				if ( isset( $options[ $option_key ] ) ) {
					$request_post_field[ $option_key ] = $option_value;
				}
			}
			}

			return array( 
				'ntvwc' => json_encode( $request_post_field, JSON_UNESCAPED_UNICODE )
			);

		}

		/**
		 * Get Post Fields by data
		 * @param array $options
		 * @return array
		**/
		protected function get_http_request_options( $options = array() )
		{

			$defaults = array(
				CURLOPT_AUTOREFERER    => true,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_HEADER         => true,
				CURLOPT_TIMEOUT        => 300,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_FAILONERROR    => true,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_RETURNTRANSFER => true
			);

			foreach ( $defaults as $default_index => $default_value ) {
				if ( ! isset( $options[ $default_index ] ) ) {
					$options[ $default_index ] = $default_value;
				}
			}

			return $options;

		}

	/**
	 * Flags
	**/
		/**
		 * Check if the validate is already done
		 * @param string $value
		 * @return bool
		**/
		protected function isset_result( $value )
		{
			if ( isset( $this->result[ $value ] ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Check if prev result is valid
		 * @param string $value
		 * @return bool
		**/
		protected function is_result_valid( $value )
		{
			if ( $this->is_result_invalid( $value ) ) {
				return false;
			}
			return true;
		}

		/**
		 * Check if prev result is invalid
		 * @param string $value
		 * @return bool
		**/
		protected function is_result_invalid( $value )
		{
			if ( $this->need_validate_again( $value ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Check if the validate is already done and still need validate for some reason
		 * @param string $value
		 * @return bool
		**/
		abstract protected function need_validate_again( $value );

	/**
	 * Setters
	**/
		/**
		 * Set Prop
		 * @param string $key
		 * @param mixed  $valule
		 * @return bool
		**/
		protected function set_prop( string $key, $value, $force = false )
		{

			$sanitized_value = $this->sanitize_prop( $key, $value );
			if ( null === $sanitized_value && ! $force ) {
				return false;
			}
			$this->data[ $key ] = $sanitized_value;
			return true;
		}

	/**
	 * Getters
	**/
		/**
		 * Get target prop
		 * @param string $key
		 * @return mixed
		**/
		protected function get_prop( string $key )
		{
			if ( isset( $this->data[ $key ] ) ) {
				return $this->data[ $key ];
			}
			return null;
		}

	/**
	 * Sanitizers
	**/
		/**
		 * Set Prop
		 * @param string $key
		 * @param mixed  $valule
		 * @return mixed 
		**/
		protected function sanitize_prop( string $key, $value )
		{

			if ( method_exists( $this, 'sanitize_' . $key ) ) {
				$sanitized_value = call_user_func_array(
					array( $this, 'sanitize_' . $key ),
					array( $value )
				);

				return $sanitized_value;

			} else {

				if (
					( is_numeric( $value )
						|| is_string( $value )
					)
					&& '' !== ( string ) $value
				) {
					return $value;
				}

			}

		}

		/**
		 * Sanitize client uri
		 * @param mixed $valule
		 * @return null|string 
		**/
		protected function sanitize_client_uri( $value, $default = null )
		{
			$uri = filter_var( $value, FILTER_VALIDATE_URL );
			if ( '' !== $uri && false !== $uri ) {
				return rtrim( $uri, '/\\' ) . '/';
			}
			return $default;
		}

		/**
		 * Sanitize host uri
		 * @param mixed $valule
		 * @return null|string 
		**/
		protected function sanitize_host_uri( $value, $default = null )
		{
			$uri = filter_var( $value, FILTER_VALIDATE_URL );
			if ( '' !== $uri && false !== $uri ) {
				return rtrim( $uri, '/\\' ) . '/';
			}
			return $default;
		}

		/**
		 * Sanitize token value
		 * @param mixed  $valule
		 * @return null|string 
		**/
		protected function sanitize_token( $value, $default = null )
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
		 * Sanitize update token value
		 * @param mixed  $valule
		 * @return null|string 
		**/
		protected function sanitize_update_token( $value, $default = null )
		{
			if ( ! is_string( $value ) || '' === $value ) {
				return $default;
			}
			if ( preg_match( "/^[a-zA-Z0-9\-_]+?\.[a-zA-Z0-9\-_]+?\.([a-zA-Z0-9\-_]+)?$/", $value ) ) {
				return $value;
			}
			return $default;
		}


}	
}