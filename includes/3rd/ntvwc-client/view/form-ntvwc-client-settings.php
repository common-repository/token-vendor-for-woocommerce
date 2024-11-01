<?php

//echo $this->ntvwc_client->get_prefixed_action_hook( 'admin_page_start' );
//$this->plugin_name;
//$this->unique_prefix;
do_action( $this->ntvwc_client->get_prefixed_action_hook( 'admin_page_start' ), $this->ntvwc_client );

// Token
$validation_token = $this->ntvwc_client->get_saved_validation_token();
$update_token     = $this->ntvwc_client->get_saved_update_token();

?>
<form id="ntvwc-client-settings" method="post" action="">

	<?php
		// Nonce
		wp_nonce_field(
			$this->ntvwc_client->get_prefixed_value( 'ntvwc_client' ),
			$this->ntvwc_client->get_prefixed_value( 'ntvwc_client_nonce' )
		);
	?>

	<input type="hidden" name="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'plugin_name' ) ); ?>" id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'plugin-name', '-' ) ); ?>" class="regular-hidden plugin-name-value" value="<?php echo esc_attr( $this->ntvwc_client->get_plugin_name() ); ?>">

	<input type="hidden" name="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'textdomain' ) ); ?>" id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'textdomain', '-' ) ); ?>" class="regular-hidden textdomain-value" value="<?php echo esc_attr( $this->ntvwc_client->get_textdomain() ); ?>">

	<input type="hidden" name="ntvwc-client" id="ntvwc-client-post" class="regular-hidden unique-prefix-value" value="ntvwc-client">

	<div class="metabox-holder"><div id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'ntvwc-client-settings-wrapper', '-' ) ); ?>" class="settings-wrapper postbox">

		<h3 id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'ntvwc-client-settings-h2', '-' ) ); ?>" class="form-table-title hndle"><?php printf( esc_html__( '%s', $this->ntvwc_client->get_textdomain() ), $this->ntvwc_client->get_plugin_name() ); ?></h3>

		<div class="inside"><div class="main">

			<p><?php printf( 
				esc_html__( 'This will send a following hashed url to the shop: "%1$s"', $this->ntvwc_client->get_textdomain() ),
				$this->ntvwc_client->get_hashed_client_uri()
			); ?></p>

			<table id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'ntvwc-client-settings', '-' ) ); ?>" class="form-table"><tbody>

				<tr>
					<th><label><?php esc_html_e( 'Validation Token', $this->ntvwc_client->get_textdomain() ); ?></label></th>
					<td>

						<input type="text" name="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'validation_token' ) ); ?>" id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'validation_token', '-' ) ); ?>" class="regular-text ntvwc-client-setting-token" value="<?php echo esc_attr( $validation_token ); ?>">


					</td>
					<td>
						<?php

						$token_expiry = intval( $this->ntvwc_client->get_validation_token_expiry() );
						$expiry_date  = date_i18n( 'Y-m-d', $token_expiry, false );
						if ( 0 < $token_expiry ) {
							$format = '<span class="expiry-label">%1$s</span> <span class="expiry-value">%2$s</span>';
							printf( 
								$format, 
								esc_html__( 'Token will be Expired at:', $this->ntvwc_client->get_textdomain() ),
								$expiry_date
							);
						} elseif ( -1 === $token_expiry ) {
							$format = '<span class="expiry-label">%1$s</span>';
							printf( 
								$format, 
								esc_html__( 'Token has No Expiry.', $this->ntvwc_client->get_textdomain() )
							);
						}
						
						?>
					</td>
				</tr>

				<tr>
					<th><label><?php esc_html_e( 'Update Token', $this->ntvwc_client->get_textdomain() ); ?></label></th>
					<td>

						<input type="text" name="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'update_token' ) ); ?>" id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'update_token', '-' ) ); ?>" class="regular-text ntvwc-client-setting-token" value="<?php echo esc_attr( $update_token ); ?>">

					</td>
					<td>
					</td>
				</tr>

			</tbody></table>

			<input type="submit" name="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'save_ntvwc_client_token' ) ); ?>" id="<?php echo esc_attr( $this->ntvwc_client->get_prefixed_value( 'save_ntvwc_client_token' ) ); ?>" class="button button-primary button-save-ntvwc-client-token" value="<?php esc_attr_e( 'Save', $this->ntvwc_client->get_textdomain() ); ?>" >

		</div></div>

	</div></div>
</form>
<?php

do_action( $this->ntvwc_client->get_prefixed_action_hook( 'admin_page_end' ), $this->ntvwc_client );
