<?php

if ( ! function_exists( 'ntvwc_get_data_option' ) ) {
	/**
	 * Sanitize checkbox input
	 * 
	 * @param [string] $input 
	 * @param [string] $needle 
	 * 
	 * @return [string]
	**/
	function ntvwc_sanitize_checkbox_input( $input, $needle = 'yes' )
	{

		return $needle === $input ? 'yes' : 'no';

	}
}

