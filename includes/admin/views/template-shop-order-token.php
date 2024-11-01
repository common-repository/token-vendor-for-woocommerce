<?php

$order_id = intval( $order->ID );
$ntvwc_order = NTVWC_Order::get_instance( $order_id );

$registered_token_id_holder = ntvwc_get_post_meta( $order_id, '_ntvwc_registered_token_ids' );

if ( is_array( $registered_token_id_holder ) && 0 < count( $registered_token_id_holder ) ) {
?>
<table id="token_vendor-settings" class="form-table" style="border: solid #eee 1px;">
	<thead>
		<tr>
			<th style="padding: 10px;"><?php esc_html_e( 'Product', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></th>
			<th style="padding: 10px;"><?php esc_html_e( 'Token', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></th>
			<th style="padding: 10px;"><?php esc_html_e( 'Number of the purchased product', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></th>
			<th style="padding: 10px;"><?php esc_html_e( 'Registered hashed URLs', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></th>
		</tr>
	</thead>

	<tbody>
<?php
	foreach ( $registered_token_id_holder as $token_product_id => $registered_token_ids ) {

		if ( ! is_array( $registered_token_ids ) || 0 >= count( $registered_token_ids ) ) {
			continue;
		}

		foreach ( $registered_token_ids as $registered_token_index => $registered_token_id ) {

			$data_token = NTVWC_Data_Token::get_instance( $registered_token_id );
			$token_text = $data_token->get_the_latest_purchased_token();

			$token_text = $ntvwc_order->get_token_by_product( $token_product_id, $registered_token_index );

			$token_obj = NTVWC_Token_Methods::parse_from_string( $token_text );
			if ( is_string( $token_obj ) ) {
				continue;
			}

			$purchased_number = $ntvwc_order->get_token_item_quantity( $token_product_id );
			if ( false === $purchased_number ) {
				continue;
			}

			$hashed_urls = get_post_meta( $registered_token_id, '_ntvwc_hashed_user_site_urls', true );
			$hashed_urls = is_string( $hashed_urls ) && '' !== $hashed_urls ? $hashed_urls : '{}';
			$hashed_urls = json_decode( $hashed_urls, true );
			$hashed_urls = (
				is_array( $hashed_urls )
				? $hashed_urls
				: array()
			);

			echo '<tr>';
				echo '<th style="padding: 10px;">' . esc_html( $data_token->get_prop( 'product_name' ) ) . '</th>';
				echo '<td style="padding: 10px;"><input type="text" value="' . esc_attr( $token_text ) . '" class="regular-text" style="width: 200px; overflow: scrolled;" disabled></td>';
				echo '<td style="padding: 10px;">' . esc_html( $purchased_number ) . '</td>';

				echo '<td style="padding: 10px;">';
					if ( isset( $hashed_urls )
						&& is_array( $hashed_urls )
						&& 0 < count( $hashed_urls )
					) {
					foreach ( $hashed_urls as $hashed_url ) {
						echo '<input type="text" value="' . $hashed_url . '" disabled><br>';
					}
					}

				echo '</td>';
			echo '</tr>';

		}
	}
?>
	</tbody>
</table>

<?php

}







?>
