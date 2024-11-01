<?php

if ( ! function_exists( 'ntvwc_get_order' ) ) {
	/**
	 * Get WC_Order
	 * 
	 * @param mixed $the_order
	 * 
	 * @return WC_Order|bool
	**/
	function ntvwc_get_order( $the_order = false )
	{

		if ( ! did_action( 'woocommerce_after_register_post_type' ) ) {
			return false;
		}

		return WC()->order_factory->get_order( $the_order );

	}
}

if ( ! function_exists( 'ntvwc_get_purchased_downloadable_number' ) ) {
	/**
	 * Get downloadable number 
	 * 
	 * @param int    $order_id
	 * @param string $download_id
	 * 
	 * @return int : Returns false if failed.
	**/
	function ntvwc_get_purchased_downloadable_number( $order_id, $download_id = '' )
	{

		// Order
		$wc_order = WC()->order_factory->get_order( $order_id );

		// Each Item
		foreach ( $wc_order->get_items() as $item ) {

			if ( ! is_object( $item ) ) {
				continue;
			}

			if ( $item->is_type( 'line_item' ) ) {

				$item_downloads = $item->get_item_downloads();

				foreach ( $item_downloads as $file ) {

					if ( $download_id === $file['id'] ) {
						return intval( $item->get_quantity() );
					}

				}

			}

		}

		return 0;

	}
}

if ( ! function_exists( 'ntvwc_get_downloadable_product_file_url' ) ) {
	/**
	 * Get downloadable file url 
	 * 
	 * @param int    $downloadable_product_id
	 * @param string $searched_file_name
	 * 
	 * @return bool|string : Returns false if failed.
	**/
	function ntvwc_get_downloadable_product_file_url( $downloadable_product_id, $seached_file_name )
	{

		// Downloadable files
		$_downloadable_files = get_post_meta( $downloadable_product_id, '_downloadable_files', true );
		if ( is_array( $_downloadable_files )
			&& 0 < count( $_downloadable_files )
		) {
			foreach ( $_downloadable_files as $_downloadable_file_index => $_downloadable_file ) {

				if ( $seached_file_name === $_downloadable_file['name'] ) {

					return esc_url( $_downloadable_file['file'] );

				}

			}
		}

		return false;

	}
}

if ( ! function_exists( 'ntvwc_order_has_only_wp_downloadable_items' ) ) {
	/**
	 * Check if order has only wp downloadable items
	 * 
	 * @param WC_Order $wc_order
	 * 
	 * @return bool|string : Returns false if failed.
	**/
	function ntvwc_order_has_only_wp_downloadable_items( &$wc_order )
	{

		// Vars
			$wc_datetime = $wc_order->get_date_paid( 'view' );

		if ( null === $wc_datetime ) {
			return false;
		}

		$download_params = ntvwc_get_download_params_by_order_id( $order_id );
		$items = $wc_order->get_items();
		foreach ( $items as $item_index => $item ) {

			$product = $item->get_product();
			$product_id = $product->get_id();
			$wp_content_type = get_post_meta( $product_id, '_ntvwc_product_package_type', true );


			if ( 'none' === $wp_content_type || null === $wp_content_type ) {
				return false;
			}

		}

		return true;

	}
}


