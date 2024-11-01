<?php

if ( ! function_exists( 'ntvwc_get_data_option' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name : option name
	 * 
	 * @return NTVWC_Data_Option
	**/
	function ntvwc_get_data_option( $option_name )
	{

		return ntvwc()->option_manager->get_data_option( $option_name );

	}
}

if ( ! function_exists( 'ntvwc_get_option' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name : option name
	 * 
	 * @return array
	**/
	function ntvwc_get_option( $option_name )
	{

		return ntvwc_get_data_option( $option_name )->get_data();

	}
}

if ( ! function_exists( 'ntvwc_get_options' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name : option name
	 * 
	 * @return NTVWC_Data_Option
	**/
	function ntvwc_get_options()
	{

		return ntvwc()->option_manager->get_options();

	}
}

if ( ! function_exists( 'ntvwc_get_options_default_values' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name
	 * 
	 * @return [array]
	**/
	function ntvwc_get_options_default_values()
	{

		return ntvwc()->get_option_manager()->get_option_default_values();

	}
}

if ( ! function_exists( 'ntvwc_get_option_default_values' ) ) {
	/**
	 * Get option data
	 * 
	 * @param string $option_name
	 * 
	 * @return [array]
	**/
	function ntvwc_get_option_default_values( $option_name )
	{


		$default_data = ntvwc()->get_option_manager()->get_option_default_values( $option_name );

		return $default_data[ $option_name ];

	}
}

