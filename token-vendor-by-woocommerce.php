<?php
/**
 * Token Vendor for WooCommerce
 *
 * @package     Token Vendor for WooCommerce
 * @author      Nora
 * @copyright   2018 Nora https://token-vendor.com
 * @license     GPL-2.0+
 * 
 * @wordpress-plugin
 * Plugin Name: Token Vendor for WooCommerce
 * Plugin URI: https://token-vendor.com
 * Description: Extension for Plugins "WooCommerce"
 * Version: 0.1.11
 * Author: nora0123456789
 * Author URI: https://wp-works.com
 * Text Domain: ntvwc
 * Domain Path: /i18n/languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
**/

// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Degine Plugin Dir Path
if( ! defined( 'NTVWC_MAIN_FILE' ) ) define( 'NTVWC_MAIN_FILE', __FILE__ );
if( ! defined( 'NTVWC_DIR_PATH' ) ) define( 'NTVWC_DIR_PATH', plugin_dir_path( __FILE__ ) );
if( ! defined( 'NTVWC_DIR_URL' ) ) define( 'NTVWC_DIR_URL', plugin_dir_url( __FILE__ ) );

// Define Class Nora_Token_Vendor_For_WooCommerce
require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc.php' );

if ( ! function_exists( 'ntvwc' ) ) {
	/**
	 * Init Nora_Token_Vendor_For_WooCommerce
	**/
	function ntvwc()
	{
		global $ntvwc;
		if ( ! $ntvwc instanceof Nora_Token_Vendor_For_WooCommerce ) {
			$ntvwc = Nora_Token_Vendor_For_WooCommerce::get_instance();
		}
		return $ntvwc;
	}
	global $ntvwc;
	$ntvwc = ntvwc();

} // End Closure for Function "ntvwc"


