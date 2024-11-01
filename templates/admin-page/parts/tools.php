<?php
do_action( 'ntvwc_action_tool_page_start' );
?>
<div class="metabox-holder">
	<div id="general-settings-wrapper" class="settings-wrapper postbox" style="">
		<h3 id="general-settings-h2" class="form-table-title hndle"><?php esc_html_e( 'NTVWC Client', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></h3>
		<div class="inside"><div class="main">
			<table id="general-settings" class="form-table">
				<tbody>

					<tr>
						<th scope="row">
						<p><?php esc_html_e( "NTVWC Client", Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></p>
						</th>
						<td>
							<form id="ntvwc-download-ntvwc" method="post" action="<?php ?>">
								<?php wp_nonce_field( 'ntvwc-download-ntvwc-client', 'ntvwc-download-ntvwc-client-nonce' ); ?>
								<input type="submit" name="button-download-ntvwc-client" id="button-download-ntvwc-client" class="button button-primary" value="<?php esc_html_e( 'Download', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?>" >
							</form>
						</td>
						<td>
						</td>
					</tr>

				</tbody>
			</table>

			<p><?php esc_html_e( 'Download "NTVWC Client( file name : ntvwc-client )" above and include it in the product package.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></p>

			<p><?php esc_html_e( 'Example.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); ?></p>

			<pre style="margin: 0; padding: 10px; border: solid 1px #ccc;">
/**
 * NTVWC Client for WordPress
 * 
 * @param string $file
 * @param string $textdomain
 * @param string $plugin_dir_name
 * @param string $api_url_base
 */
if ( ! class_exists( 'NTVWC_Client' ) ) {
	require_once( 'path/to/ntvwc-client/class/class-ntvwc-client.php' );
}
$ntvwc_client = NTVWC_Client::get_instance(
	<?php
	echo 'MAIN_FILE, // ';
	esc_html_e( 'Like "path/to/functions.php" of the theme or "path/to/plugin-file.php" of the plugin.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
	?>

	<?php
	echo 'TEXTDOMAIN, // ';
	esc_html_e( 'Textdomain', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
	?>

	<?php
	echo 'PACKAGE_DIR_NAME, // ';
	esc_html_e( 'Folder name of the package', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
	?>

	<?php
	echo '"' . esc_url( get_site_url() ) . '" // ';
	esc_html_e( 'URL of WooCommerce site which installed this', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
	?>

);

// <?php printf( esc_html__( 'You can get the "%s" under the setting field of token value in the product setting page.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), "HASHED_TOKEN_VALUE" ); ?>

$ntvwc_client->validate_token( HASHED_TOKEN_VALUE ); 
			</pre>
		</div></div>
	</div>
</div>
<?php
