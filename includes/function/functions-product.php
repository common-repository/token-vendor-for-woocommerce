<?php

if ( ! function_exists( 'ntvwc_get_product' ) ) {
	/**
	 * Get product by ID
	 * @param  mixed $product_id 
	 * @return WC_Product
	 */
	function ntvwc_get_product( $product_id )
	{
		if ( ! did_action( 'woocommerce_init' ) ) {
			return false;
		}
		return WC()->product_factory->get_product( $product_id );
	}
}

if ( ! function_exists( 'ntvwc_get_token_products' ) ) {
	/**
	 * Get all token products
	 * @return array : WC_Product
	 */
	function ntvwc_get_token_products()
	{
		if ( ! did_action( 'woocommerce_init' ) ) {
			return false;
		}

		$posts = get_posts( array(
			'post_type' => 'product',
			'post_status' => array( 'publish', 'pending', 'future', 'private', 'trash' ),
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_ntvwc_type_token',
					'value' => 'yes',
				),
			)
		) );

		$products = array();
		if ( is_array( $posts ) && 0 < count( $posts ) ) {
		foreach ( $posts as $post ) {
			$product = WC()->product_factory->get_product( $post->ID );
			if ( false !== $product ) {
				array_push( $products, $product );
			}
		}
		}

		return $products;

	}
}

if ( ! function_exists( 'ntvwc_get_validation_token_products' ) ) {
	/**
	 * Get all validation token products
	 * @return array : WC_Product
	 */
	function ntvwc_get_validation_token_products( string $token_value )
	{
		if ( ! did_action( 'woocommerce_init' ) ) {
			return false;
		}

		$query = array(
			'post_type' => array( 'product', 'product_variation' ),
			'post_status' => array( 'publish', 'pending', 'future', 'private', 'trash' ),
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => '_ntvwc_type_token',
					'value' => 'yes',
				),
				array(
					'key'   => '_ntvwc_token_type',
					'value' => 'validation',
				), 
			)
		);

		if ( '' !== $token_value ) {
			$query['meta_query'][] = array(
				'key'   => '_ntvwc_token_value',
				'value' => $token_value
			);
		}

		$posts = get_posts( $query );

		$products = array();
		if ( is_array( $posts ) && 0 < count( $posts ) ) {
		foreach ( $posts as $post ) {
			$product = WC()->product_factory->get_product( $post->ID );
			if ( false !== $product ) {
				array_push( $products, $product );
			}
		}
		}
		return $products;

	}
}

if ( ! function_exists( 'ntvwc_get_update_token_products' ) ) {
	/**
	 * Get all update token products
	 * @return array : WC_Product
	 */
	function ntvwc_get_update_token_products( string $token_value )
	{
		if ( ! did_action( 'woocommerce_init' ) ) {
			return false;
		}

		$query = array(
			'post_type' => 'product',
			'post_status' => array( 'publish', 'pending', 'future', 'private', 'trash' ),
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => '_ntvwc_type_token',
					'value' => 'yes',
				),
				array(
					'key'   => '_ntvwc_token_type',
					'value' => 'update',
				), 
			)
		);

		if ( '' !== $token_value ) {
			$query['meta_query'][] = array(
				'key'   => '_ntvwc_token_value',
				'value' => $token_value
			);
		}

		$posts = get_posts( $query );

		$products = array();
		if ( is_array( $posts ) && 0 < count( $posts ) ) {
		foreach ( $posts as $post ) {
			$product = WC()->product_factory->get_product( $post->ID );
			if ( false !== $product ) {
				array_push( $products, $product );
			}
		}
		}
		return $products;

	}
}

if ( ! function_exists( 'ntvwc_get_validation_token_product_names' ) ) {
	/**
	 * Get all validation token product names
	 * @return array : WC_Product
	 */
	function ntvwc_get_validation_token_product_names( string $token_value )
	{

		$result = array();
		$products = ntvwc_get_validation_token_products( $token_value );
		if ( 0 < count( $products ) ) {
		foreach ( $products as $product ) {
			$product_id = intval( $product->get_id() );
			$result[ $product_id ] = $product->get_name();
		}
		}
		return $result;

	}
}

if ( ! function_exists( 'ntvwc_get_udpate_token_product_names' ) ) {
	/**
	 * Get all update token product names
	 * @return array : WC_Product
	 */
	function ntvwc_get_udpate_token_product_names( string $token_value )
	{

		$result = array();
		$products = ntvwc_get_update_token_products( $token_value );
		if ( 0 < count( $products ) ) {
		foreach ( $products as $product ) {
			$product_id = intval( $product->get_id() );
			$result[ $product_id ] = $product->get_name();
		}
		}
		return $result;

	}
}

if ( ! function_exists( 'ntvwc_get_validation_token_product_values' ) ) {
	/**
	 * Get all validation token product names
	 * @return array
	 */
	function ntvwc_get_validation_token_product_values()
	{

		$result = array();
		$products = ntvwc_get_validation_token_products( '' );
		if ( 0 < count( $products ) ) {
		foreach ( $products as $product ) {
			$token_value = get_post_meta( $product->get_id(), '_ntvwc_token_value', true );
			if ( is_string( $token_value ) && '' !== $token_value ) {
				$result[ $token_value ] = $token_value;
			}
		}
		}
		return $result;

	}
}













