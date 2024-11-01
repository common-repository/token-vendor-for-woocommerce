<?php
/**
 * Requires to define 
 * 		$option_data
 * 		$options
 * Defined vars :
 * 
 * 
 * 
**/

// Vars
$data_option = ntvwc_get_data_option( 'token_vendor' );
$option_data = $data_option->get_data();
//ntvwc_test_var_dump( $option_data );

$options = ntvwc()->get_option_manager()->get_option_form_inputs( 'token_vendor' );

?>
<div id="token_vendor-settings-wrapper" class="settings-wrapper postbox" style="">
	<h3 id="token_vendor-settings-h2" class="form-table-title hndle"><?php esc_html_e( 'Token Vendor for WooCommerce', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></h3>
	<div class="inside"><div class="main">
		<table id="token_vendor-settings" class="form-table">
			<tbody>
				<?php
				foreach ( $option_data as $option_index => $option_value ) {
				?>
					<tr>
						<th scope="row">
							<?php ntvwc_print_form_label( $options[ $option_index ]['name'], $options[ $option_index ]['label'] ) ?>
						</th>
						<td>
							<?php
							ntvwc_print_form_input( $options[ $option_index ]['type'],
								$options[ $option_index ]['id'],
								'option_token_vendor',
								$options[ $option_index ]['name'],
								$option_value
							);
							?>
						</td>
						<td><?php echo esc_html( $options[ $option_index ]['description'] ); ?></td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php
			/** 
			 * 
			 * @param [string] $option_name 
			 * @param [string] $text 
			 * @param [bool]   $with_nonce 
			**/
			ntvwc_print_form_option_button_for_ajax(
				'token_vendor',
				__( 'Save', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				true
			);
		?>
	</div></div>
</div>
