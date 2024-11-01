<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'ntvwc_is_wp_ajax' ) ) {
	/**
	 * WP AJAX or not
	**/
	function ntvwc_is_wp_ajax() {

		// Case : WP AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return true;
		}

		// End
		return false;

	}
}

if ( ! function_exists( 'ntvwc_is_wp_cron' ) ) {
	/**
	 * WP AJAX or not
	**/
	function ntvwc_is_wp_cron() {

		// Case : WP Cron
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return true;
		}

		// End
		return false;

	}
}

if ( ! function_exists( 'ntvwc_is_working' ) ) {
	/**
	 * WCYSS is Working
	**/
	function ntvwc_is_working() {

		// WCYSS DOING WORK
		return boolval( is_admin() || ntvwc_is_wp_ajax() || ntvwc_is_wp_cron() );

	}
}

if ( ! function_exists( 'ntvwc_is_rest_url' ) ) {
	/**
	 * WCYSS is Working
	**/
	function ntvwc_is_rest_url() {
		$bIsRest = false;
		if ( function_exists( 'rest_url' ) && ! empty( $_SERVER[ 'REQUEST_URI' ] ) ) {
			$sRestUrlBase = get_rest_url( get_current_blog_id(), '/' );
			$sRestPath    = trim( parse_url( $sRestUrlBase, PHP_URL_PATH ), '/' );
			$sRequestPath = trim( $_SERVER[ 'REQUEST_URI' ], '/' );
			
			$bIsRest      = ( strpos( $sRequestPath, $sRestPath ) === 0 );
		}
		return $bIsRest;
	}
}




