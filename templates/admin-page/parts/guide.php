<?php
/**
 * 
 * Defined vars :
 * $options
 * 
 * 
 * 
**/
?>
<div class="metabox-holder ntvwc-metabox-holder" style="padding-right: 10px;">
	<div id="general-settings-wrapper" class="settings-wrapper postbox" style="">
		<h3 id="general-settings-h2" class="form-table-title hndle"><?php esc_html_e( 'Introduction', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></h3>
		<div class="inside"><div class="main">

			<p><?php esc_html_e( 'This plugin enables you to sell token products sold by WooCommerce.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?> <?php esc_html_e( 'By including "NTVWC Client" into your package including WP Themes and WP Plugins, the package validate the token easily.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?> <?php esc_html_e( 'Please read the how-to below.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></p>

		</div></div>
	</div>
</div>

<div class="metabox-holder" style="padding-right: 10px;">
	<div id="general-settings-wrapper" class="settings-wrapper postbox" style="">
		<h3 id="general-settings-h2" class="form-table-title hndle">
			<?php esc_html_e( 'How to Use', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?>
		</h3>
		<div class="inside"><div class="main">

			<p><?php
				$guide_page_url = __( 'https://token-vendor.com/how-to-use-our-plugins/', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
				printf( __( 'Please read how-to in the page below.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
				$format = ' <a href="%1$s" class="%2$s" target="_blank">%3$s</a> ( Preparing )';
				printf( $format, esc_url( $guide_page_url ), 'guide-page-link', __( 'Token Vendor', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) );
			?></p>

			<?php
			ntvwc_print_list( array(
				array(
					'html' => '<p>' . sprintf( 
						__( 'If your %1$s is not setup yet, go to %2$s and press the button "Save changes".', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						sprintf(
							'<a href="%1$s">%2$s</a>',
							wc_get_account_endpoint_url( 'ntvwc-tokens' ),
							__( 'Purchased Tokens Page', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
						),
						sprintf(
							'<a href="%1$s">%2$s</a>',
							esc_url( add_query_arg( array( 'page' => 'wc-settings', 'tab' => 'advanced' ), admin_url( 'admin.php' ) ) ),
							__( 'WooCommerce advance settings page', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
						)
					) . '</p>'
					. '<p>' . __( 'Just press the button to add the endpoint for purchased tokens to client\'s account page.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
				),
				array(
					'html' => '<p>' . __( 'Create products in token type.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
					. '<p>' . __( 'Validation token requires to be set token value. ( Once you save the value, password-hashed value will appeaer below the setting field. Use this hashed value for the validation. )', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
					. '<p>' . __( 'If you want to make it with no expiry, just let the field "Token Expiry" empty.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
					. '<p>' . __( 'Funcitonality "Restrict Access" is enabled while purchased token is valid.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
					. '<p>' . __( 'Update token needs to select validation token value and to set the expiry in day which is going to extend.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
				),
				array(
					'html' => '<p>' . __( 'Include the Rest API Request methods in your package in proper way.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>' .
					ntvwc_print_list( array(
						array(
							'html' => '<p>' . __( 'If your package is WP Theme or Plugin, download the "NTVWC Client" in tool page and include it.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
						),
						array(
							'html' => '<p>' . __( 'For other tools, some request classes are going to be prepared in updates. Please wait.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
						),
					), 'ul', false )
				),
				array(
					'html' => '<p>' . __( '* When you sell token products, please notify or indicate your clients to get the generated token to copy it to the setting page.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</p>'
				),
			), 'ol' );
			?>

		</div></div>
	</div>
</div>

