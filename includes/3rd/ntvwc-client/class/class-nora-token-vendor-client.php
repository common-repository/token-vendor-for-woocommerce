<?php

if ( ! class_exists( 'Nora_Token_Vendor_Client' ) ) {
	if ( ! class_exists( 'Nora_Token_Vendor_Client_Abstract' )) {
		require_once( 'class-nora-token-vendor-client-abstract.php' );
	}
/**
 * @version 1.0.0
**/
class Nora_Token_Vendor_Client extends Nora_Token_Vendor_Client_Abstract {

	/**
	 * Consts
	**/
		const VERSION = '1.0.0';

	/**
	 * Properties
	**/
		/**
		 * Data which have int or string
		**/
		protected $data = array(
			'client_uri'   => '',
			'host_uri'     => '',
		);

		/**
		 * Data
		**/
		protected $_defaults = array(
			'client_uri'   => '',
			'host_uri'     => 'https://token-vendor.com',
			'token'        => '',
			'update_token' => ''
		);

		/**
		 * Result
		**/
		protected $result;

	/**
	 * Init
	**/
		/**
		 * Constructor
		**/
		public function __construct(
			string $client_site,
			string $host_uri
		)
		{

			$data = array(
				'client_uri' => $client_site,
				'host_uri'   => $host_uri,
			);

			if ( 'Nora_Token_Vendor_Client' === get_class( $this ) ) {
				$this->init_vars( $data );
			}

		}

	/**
	 * Request
	**/
		/**
		 * Validate
		**/
			/**
			 * Validate
			 * @param string $token
			 * @param string $value
			 * @param string $update_token
			 * @return bool|array
			**/
			public function validate( string $token, string $value, string $update_token = '' )
			{

				if ( is_string( $update_token ) && '' !== $update_token ) {
					$this->update_validation_token( $token, $value, $update_token );
				}

				try {

					$result = $this->prepare( 'validate', $token );
					if ( false === $result ) {
						throw new Exception( 'Prepare failed. Type: Validate' );
					}

					$this->http_request(
						$this->get_host_uri() . Nora_Token_Vendor_Client::REQUEST_URI_VALIDATE,
						$value,
						$this->get_http_request_options( array(
							CURLOPT_POSTFIELDS => $this->get_request_post_fields()
						) )
					);

					if ( ! $this->isset_result() 
						|| $this->is_result_invalid()
					) {
						throw new Exception( 'HTTP request FAILED.' );
					}

				} catch( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				return $this->result;

			}

			/**
			 * Update
			 * @param string $token
			 * @param string $update_token
			 * @return bool|array
			**/
			public function update_validation_token( string $token, string $value, string $update_token )
			{

				try {

					$result = $this->prepare( 'update', $token, $update_token );
					if ( false === $result ) {
						throw new Exception( 'Prepare failed. Type: Update' );
					}

					$this->http_request(
						$this->get_host_uri() . Nora_Token_Vendor_Client::REQUEST_URI_VALIDATE,
						$value,
						$this->get_http_request_options( array( 
							CURLOPT_POSTFIELDS => $this->get_request_post_fields( 
								array( 'update_token' => $this->get_prop( 'update_token' ) )
							)
						) )
					);
					if ( ! $this->isset_result() 
						|| $this->is_result_invalid()
					) {
						throw new Exception( 'HTTP request FAILED.' );
					}

				} catch( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				$this->set_prop( 'update_token', '' );

				return $this->result;

			}

		/**
		 * Prepare
		**/
			/**
			 * Prepare
			 * @param string $type : 'validate' or 'update'
			 * @param string $token
			 * @param string $update_token
			 * @return bool|array
			**/
			public function prepare( $type, $token = '', $update_token = '' )
			{

				$result_prepare = $this->prepare_token( $token );
				if ( ! $result_prepare ) {
					return false;
				}

				$post_field_options = array();
				if ( is_string( $type ) && 'update' === $type ) {
					if ( is_string( $update_token ) && '' !== $update_token ) {
						$result_prepare = $this->prepare_update_token( $update_token );
						if ( ! $result_prepare ) {
							return false;
						}
						$post_field_options = array( 'update_token' => $this->get_prop( 'update_token' ) );
					}
				}

				if ( $this->isset_result() && $this->is_result_valid() ) {
					return $this->result;
				}

				$result_prepare = $this->prepare_post_fields();
				if ( ! $result_prepare ) {
					return false;
				}



				try {
					$this->http_request(
						$this->get_host_uri() . Nora_Token_Vendor_Client::REQUEST_URI_VALIDATE,
						$value,
						$this->get_http_request_options( array( 
							CURLOPT_POSTFIELDS => $this->get_request_post_fields( $post_field_options )
						) )
					);
					if ( ! $this->isset_result() 
						|| $this->is_result_invalid()
					) {
						throw new Exception( 'HTTP request FAILED.' );
					}
				} catch( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				return $this->result;

			}

			/**
			 * Prepare token
			 * @param string $token : Default ''
			 * @return bool
			**/
			protected function prepare_token( $token = '' )
			{

				try {
					if ( ! is_string( $token ) || '' === $token ) {
						$token = $this->sanitize_token( $this->get_prop( 'token' ) );
						if ( ! is_string( $token ) || '' === $token ) {
							throw new Exception( 'Token is empty.' );
						}
					}
					elseif ( is_string( $token ) && '' !== $token ) {
						$this->set_prop( 'token', $token );
					}
					else {
						throw new Exception( 'Token data is invlaid.' );
					}
				} catch ( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				return true;

			}

			/**
			 * Prepare token
			 * @param string $token : Default ''
			 * @return bool
			**/
			protected function prepare_update_token( $update_token = '' )
			{

				try {
					if ( ! is_string( $update_token ) || '' === $update_token ) {
						$update_token = $this->sanitize_update_token( $this->get_prop( 'update_token' ) );
						if ( ! is_string( $update_token ) || '' === $update_token ) {
							throw new Exception( 'Token is empty.' );
						}
					}
					elseif ( is_string( $update_token ) && '' !== $update_token ) {
						$this->set_prop( 'update_token', $update_token );
					}
					else {
						throw new Exception( 'Update token data is invlaid.' );
					}
				} catch ( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				return true;

			}

			/**
			 * Prepare post fields
			 * @return bool
			**/
			protected function prepare_post_fields( $options = array() )
			{

				try {

					$post_fields = $this->get_request_post_fields( $options );
					if ( false === $post_fields ) {
						throw new Exception( '$post_fields is not set or is invalid.' );
					}

					$host_uri = $this->get_host_uri();
					if ( false === $host_uri ) {
						throw new Exception( '$post_fields is not set or is invalid.' );
					}

				} catch( Exception $e ) {
					trigger_error( $e->getMessage() );
					return false;
				}

				return true;

			}


	/**
	 * Flags
	**/
		/**
		 * Check if the validate is already done and still need validate for some reason
		 * @return bool
		**/
		protected function need_validate_again( $value )
		{
			if ( isset( $this->result[ $value ] )
				&& isset( $this->result[ $value ]['info'] ) 
				&& isset( $this->result[ $value ]['info']['http_code'] )
				&& is_numeric( $this->result[ $value ]['info']['http_code'] )
				&& 400 > intval( $this->result[ $value ]['info']['http_code'] )
				&& 200 <= intval( $this->result[ $value ]['info']['http_code'] )
			) {
				return false;
			}
			return true;
		}


}
}

