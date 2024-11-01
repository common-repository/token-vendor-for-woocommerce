<?php

// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Data_Purchased_Token' ) ) {
/**
 * Data
 * 
 * 
**/
class NTVWC_Data_Purchased_Token extends NTVWC_Data_Product_Token {

	/**
	 * Order Object
	 * @var NTVWC_Order
	**/
	protected $ntvwc_order = null;

	/**
	 * Product Object
	 * @var WC_Product
	**/
	protected $wc_product = null;

	/**
	 * Data
	 * @var array
	**/
	protected $defaults = array(
		'type'             => 'validation',
		'expiry'           => '',
		'restrict_access'  => 'no',
		'purchased_number' => 0
	);

	/**
	 * Constructor
	 * @param mixed $order
	 * @param mixed $product
	**/
	public function __construct( $order, $product )
	{

		parent::__construct( $product );

		$wc_order = WC()->order_factory->get_order( $order );
		if ( false === $wc_order ) {
			throw new Exception( 'Wrong Order Data.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
		}
		$this->ntvwc_order = new NTVWC_Order( $wc_order->get_id() );

		$wc_product = WC()->product_factory->get_product( $product );
		if ( false === $wc_product ) {
			throw new Exception( 'Wrong Product Data.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN );
		}
		$this->wc_product = $wc_product;

	}

	/**
	 * Get data
	**/
	public function get_data()
	{

		$data = parent::get_data();
		$data['order_key']        = $this->ntvwc_order->get_order_key();
		$data['purchased_number'] = intval( $this->ntvwc_order->get_token_item_quantity( $this->wc_product->get_id() ) );
		return $data;

	}

	public function get_latest_token( $token )
	{

		// Prepare var $token from ID and some params
		if ( is_string( $token_obj ) ) {
			$token_obj = NTVWC_Token_Methods::parse_from_string( $token_obj );
		}

		if ( is_string( $token_obj ) ) {
			return;
		}

		$this->is_token = true;
		$this->token = $token_obj;

	}


}
}


