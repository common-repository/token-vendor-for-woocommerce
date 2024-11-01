<?php
use Lcobucci\JWT\Signer\Hmac\Sha256;

if ( ! function_exists( 'ntvwc_get_rest_api' ) ) {
	/**
	 * Init specified Auth by $type
	 * 
	 * @param string $type : Auth Type
	 * 
	 * @return NTVWC_REST_API|bool
	**/
	function ntvwc_get_rest_api( $type = 'basic' )
	{

		if ( class_exists( 'NTVWC_REST_API_Loader' ) ) {
			return NTVWC_REST_API_Loader::load( $type );
		}

		return false;

	}
}

if ( ! function_exists( 'ntvwc_run_rest_api' ) ) {
	/**
	 * Run the Auth by $type
	 * 
	 * @param string $type : Auth Type
	 * 
	 * @return NTVWC_REST_API|NTVWC_REST_API_{$type}
	**/
	function ntvwc_run_rest_api( $type )
	{

		// Init Auth
		$auth = ntvwc_get_auth( $type );
		
		// Run
		$auth->run();

		// End
		return $auth;

	}
}


