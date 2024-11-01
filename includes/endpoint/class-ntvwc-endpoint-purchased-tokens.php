<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class NTVWC_Endpoint_Purchased_Tokens extends NTVWC_Endpoint_Abstract {

	public static $instance = null;
	public static $index_endpoint = 'downloads';
	public $scripts_text_object = 'scriptsObject';

	public static function get_instance()
	{
		if ( null === self::$instance ) {
			self::$instance = new Self();
		}
		return self::$instance;
	}

	protected function __construct()
	{
		$this->description = __( 'Endpoint for the "Tokens" page.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
		$this->init(
			'ntvwc-tokens',
			__( 'Purchased Tokens', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
		);
	}

	public function init( string $endpoint, string $title_endpoint )
	{
		parent::init( $endpoint, $title_endpoint );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'wp_footer', array( $this, 'print_templates_for_js' ), 10 );
	}

	/**
	 * Enqueue scripts
	**/
	function enqueue_scripts( $hook )
	{

		if ( is_account_page() || is_checkout() ) {

			wp_enqueue_style( 'ntvwc-customer-downloads-css' );

			wp_localize_script( 'ntvwc-customer-downloads-js', $this->scripts_text_object, ntvwc()->texts->get_admin_texts() );
			wp_enqueue_script( 'ntvwc-customer-downloads-js' );

		}

	}

	/**
	 * Render NTVWC Tokens Tab
	 * @param string $value
	**/
	public function woocommerce_account_endpoint( $current_page )
	{
		include( 'views/template-purchased-tokens.php' );
	}

	/**
	 * Print a JWT Column
	 *
	 * @param string $column_id
	 * @param array  $download
	**/
	public function popup_get_the_token( string $token_id, string $token_text = '' )
	{

		if ( '' === $token_id
			|| '' === $token_text
		) {
			return false;
		}

		echo '<td>';
			echo '<a id="ntvwc-customer-purchased-token-' . $token_id . '" class="ntvwc-customer-purchased-token button alt" href="javascript: void( 0 );" data-token-id="' . $token_id . '">' . esc_html__( 'Get the Token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ) . '</a>';
			echo '<input id="ntvwc-hidden-customer-purchased-token-' . $token_id . '" class="ntvwc-purchased-token-value" type="hidden" value="' . $token_text . '" data-token-id="' . $token_id . '">';
		echo '</td>';

	}

	/**
	 * Load template HTMLs
	 *
	 * @param string $column_id
	**/
	public function print_templates_for_js()
	{

		require_once( 'views/template-popup-customer-downloads.php' );

	}

	public static function install() {
		$endpoint = new Self();
		$endpoint->add_endpoints();
		parent::install();
	}

}



