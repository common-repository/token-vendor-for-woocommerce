<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_page    = empty( $current_page ) ? 1 : absint( $current_page );
$customer_orders = wc_get_orders( apply_filters( 'woocommerce_my_account_my_orders_query', array(
	'customer' => get_current_user_id(),
	'page'     => $current_page,
	'paginate' => true,
) ) );

$token_holder = array();
if ( is_array( $customer_orders->orders ) && 0 < count( $customer_orders->orders ) ) {
foreach ( $customer_orders->orders as $customer_order ) {

	$ntvwc_order = new NTVWC_Order( $customer_order->get_id() );
	$all_token_params = $ntvwc_order->get_all_token_params();
	if ( is_array( $all_token_params ) && 0 < count( $all_token_params ) ) {
	foreach ( $all_token_params as $token_product_id => $token_params ) {

		$purchased_number = intval( $ntvwc_order->get_token_item_quantity( $token_product_id ) );
		if ( 0 >= $purchased_number ) {
			continue;
		}

		for ( $index = 0; $index < $purchased_number; $index++ ) {

			// Product Token
			$token_text = $ntvwc_order->get_token_by_product( $token_product_id, $index );
			if ( ! is_string( $token_text ) 
				|| '' === $token_text
			) {
				continue;
			}

			// Token Object
			$token_obj = NTVWC_Token_Methods::parse_from_string( $token_text );

			$result = ntvwc()->get_token_manager()->validate_expiry( $token_obj );
			if ( is_string( $result ) ) {
				continue;
			}

			if ( ! $token_obj->hasClaim( 'token_id' ) ) {
				continue;
			}
			$token_id = $token_obj->getClaim( 'token_id' );
			$token_holder[ $token_id ] = $token_obj;

		}


	}
	}
}
}

if ( is_array( $token_holder ) && 0 < count( $token_holder ) ) {
	echo '<section class="woocommerce-order-ntvwc-tokens">';
	echo '<table class="woocommerce-table woocommerce-table--order-ntvwc-tokens shop_table shop_table_responsive order_details"">';
		echo '<thead>';
			echo '<tr>';
				$format_th = '<td class="token-%1$s"><span class="nobr">%2$s</span></td>';

				$product_name_label       = esc_html__( 'Product Name', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				$product_token_type_label = esc_html__( 'Type', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				$product_expire_label     = esc_html__( 'Expiry', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				//$purchased_quantity_label = esc_html__( 'Quantity', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				$product_token_label      = esc_html__( 'Token', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );

				printf( $format_th, 'product', $product_name_label );
				printf( $format_th, 'type', $product_token_type_label );
				printf( $format_th, 'expires', $product_expire_label );
				//printf( $format_th, 'quantity', $purchased_quantity_label );
				printf( $format_th, 'text', $product_token_label );

			echo '</tr>';
		echo '</thead>';

		echo '<tbody>';
			$format = '<td class="token-%1$s" data-title="%2$s">%3$s</td>';
			foreach ( $token_holder as $token_id => $token_obj ) {

				if ( ! $this->token->hasClaim( 'order_id' )
					|| ! is_numeric( $this->token->getClaim( 'order_id' ) )
					|| 0 >= intval( $this->token->getClaim( 'order_id' ) )
					|| ! $this->token->hasClaim( 'product_id' )
					|| ! is_numeric( $this->token->getClaim( 'product_id' ) )
					|| 0 >= intval( $this->token->getClaim( 'product_id' ) )
					|| ! $this->token->hasClaim( 'registered_index' )
					|| ! is_numeric( $this->token->getClaim( 'registered_index' ) )
					|| 0 > intval( $this->token->getClaim( 'registered_index' ) )
				) {

				}

				// Order
				$order_id = intval( $token_obj->getClaim( 'order_id' ) );
				$ntvwc_order = new NTVWC_Order( $order_id );

				// Token Type
				$token_type = $token_obj->getClaim( 'type' );
				if ( ! is_string( $token_type )
					|| ! in_array( $token_type, array( 'validation', 'update' ) )
				) {
					continue;
				}

				if ( 'validation' === $token_type ) {

					$token_type_label = esc_html__( 'Validation', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );

					$expire_timestamp = $token_obj->getClaim( 'access_expiry' );
					if ( -1 === $expire_timestamp ) {
						$expire_with_format = '&#8734;';
					} elseif ( 0 < $expire_timestamp ) {
						$expire_with_format = date_i18n( 'Y-m-d', $expire_timestamp );
					}

				} elseif ( 'update' === $token_type ) {

					$token_type_label = esc_html__( 'Update', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );

					$expire_timestamp = $token_obj->getClaim( 'expiry' );
					if ( -1 === $expire_timestamp ) {
						$expire_with_format = '&#8734;';
					} elseif ( 0 < $expire_timestamp ) {
						$expire_with_format = intval( $expire_timestamp );
					}

				}

				$token_product_id = intval( $token_obj->getClaim( 'product_id' ) );
				$token_text = $token_obj->__toString();

				// Product Name
				$wc_product = WC()->product_factory->get_product( intval( $token_product_id ) );
				//$product_name = $token_obj->get_();
				$product_name = $token_obj->getClaim( 'product_name' );

				// Expiry
				//$expire_timestamp = $token_params['access_expiry'];
				//$expire_timestamp = $token_obj->getClaim( 'access_expiry' );
				//if ( -1 === $expire_timestamp ) {
					//$expire_with_format = '&#8734;';
				//} elseif ( 0 < $expire_timestamp ) {
					//$expire_with_format = date_i18n( 'Y-m-d', $expire_timestamp );
				//}

				// Purchased Quantity
				//$purchased_number = $ntvwc_order->get_token_item_quantity( $token_product_id );
				//$purchased_number = $token_obj->getClaim( 'purchased_number' );
				//$token_id = $token_obj->getClaim( 'token_id' );


				// Popup button
				$popup_button_format = '<a id="%1$s" class="%2$s" data-order="%3$s" data-product-id="%4$s" href="javascript: void( 0 );">%5$s</a>';
				$popup_button = sprintf( $popup_button_format,
					'ntvwc-customer-token-' . $token_product_id,
					'ntvwc-customer-token button alt',
					$customer_order->get_id(),
					$token_product_id,
					esc_html__( 'Get the Token', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
				);

				// Input
				$input_form_with_token = '<input type="hidden" id="ntvwc-customer-purchased-token-%1$s" class="ntvwc-token" value="%2$s" disabled style="padding: 2px; width: 100px; border-radius: 3px; overflow: scroll;">';
				$input_with_token = sprintf( 
					$input_form_with_token,
					$token_id,
					$token_text
				);

				echo '<tr>';

					// Print
					printf( $format, 'product', $product_name_label, $product_name );
					printf( $format, 'type', $product_token_type_label, $token_type_label );
					printf( $format, 'expires', $product_expire_label, $expire_with_format );
					//printf( $format, 'quantity', $purchased_quantity_label, $purchased_number );
					$this->popup_get_the_token( $token_id, $token_text );
					//printf( $format, 'text', $product_token_label, $popup_button . $input_with_token );


				echo '</tr>';

			}

		echo '</tbody>';

	echo '</table>';
	echo '</section>';
} else {
	echo '<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">';
		printf(
			'<a class="woocommerce-Button button" href="%1$s">%2$s</a>',
			wc_get_page_permalink( 'shop' ),
			esc_html__( 'To Shop', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
		);
		esc_html_e( 'You don\'t have any available purchased token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
	echo '</div>';
}










