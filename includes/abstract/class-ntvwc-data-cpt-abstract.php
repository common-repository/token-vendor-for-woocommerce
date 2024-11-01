<?php
// Check if WP is Loaded
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Data_CPT' ) ) {
/**
 * Data formats
**/
abstract class NTVWC_Data_CPT extends NTVWC_Data {

		/**
		 * Attributes for this object.
		 *
		 * @since 1.0.0
		 * @var [array]
		 */
		protected $attributes = array(
			'id'          => 0, // can be string
			'object_read' => false,
			'data_type'   => 'data', // like 'data' 'option' 'post' 'meta'
			'object_type' => '', // like 'single' 'downloadable'
		);

		/**
		 * This is the name of this object type.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $object_type = 'post';

		/**
		 * Set to _data on construct so we can track and reset data if needed.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $default_data = array(
		);

	/**
	 * Init
	**/
		/**
		 * Default constructor.
		 *
		 * @param [int|object|array] $read ID to load from the DB (optional) or already queried data.
		 */
		public function __construct( $read = 0 )
		{

			parent::__construct( $read );

		}



}
}
