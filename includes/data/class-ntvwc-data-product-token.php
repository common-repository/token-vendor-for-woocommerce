<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Data_Product_Token' ) ) {
/**
 * Data
 * 
 * 
**/
class NTVWC_Data_Product_Token {

	/**
	 * Should be Order ID
	 * @var null|int
	**/
	protected $id;

	/**
	 * Data
	 * @var array
	**/
	protected $data = array();

	/**
	 * Data
	 * @var array
	**/
	protected $defaults = array(
		'type'             => 'validations',
		'expiry'           => '',
		'restrict_access'  => 'no',
	);

	/**
	 * Product Object
	 * @var WC_Product
	**/
	protected $wc_product = null;

	/**
	 * Constructor
	 * @param mixed $order
	 * @param mixed $product
	**/
	public function __construct( $product )
	{

		$wc_product = WC()->product_factory->get_product( $product );
		if ( false === $wc_product ) {
			return false;
		}

		$this->wc_product = $wc_product;
		$this->id = $this->wc_product->get_id();

	}

	/**
	 * Init Vars
	 * @param WC_Product $product_id
	**/
	public function get_data()
	{

		// Check if the product is token
		$is_ntvwc_token = get_post_meta( intval( $this->id ), '_ntvwc_type_token', true );
		if ( ! is_string( $is_ntvwc_token ) || 'yes' !== $is_ntvwc_token ) {
			return false;
		}

		// Token Type
		$token_type = get_post_meta( $this->id, '_ntvwc_token_type', true );
		$token_type = ( is_string( $token_type ) && '' !== $token_type ? $token_type : 'validation' );
		if ( ! is_string( $token_type ) 
			|| ! in_array( $token_type, array( 'validation', 'update' ) )
		) {
			return false;
		}

		// Expire in day
		$token_expiry     = get_post_meta( $this->id, '_ntvwc_token_expiry_in_day', true );
		$token_expiry     = intval( 
			is_numeric( $token_expiry ) 
				&& 0 < intval( $token_expiry )
			? $token_expiry 
			: -1 
		);

		if ( in_array( $token_type, array( 'validation', 'update' ) ) ) {

			$data = array(
				'product_id'   => $this->wc_product->get_id(),
				'product_name' => $this->wc_product->get_name(),
				'type'         => $token_type,
				'expiry'       => $token_expiry
			);

			if ( 'validation' === $token_type ) {

				// Restrict Access
				$restrict_access  = get_post_meta( $this->id, '_ntvwc_restrict_url_access', true );
				$restrict_access  = ( is_string( $restrict_access ) && 'yes' !== $restrict_access ? 'yes' : 'no' );
				$data['restrict_access'] = $restrict_access;

			}

			return $data;

		}


		return false;

	}

}
}


