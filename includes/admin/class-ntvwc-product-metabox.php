<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NTVWC_Product_Metabox' ) ) {
/**
 * 
**/
class NTVWC_Product_Metabox extends NTVWC_Unique {

	#
	# Vars
	#
		/**
		 * Instance of Self
		 * 
		 * @var Self
		**/
		protected static $instance = null;

	/**
	 * Initializer
	**/
		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @return Self
		**/
		public static function get_instance()
		{

			// Init if not yet
			if ( null === self::$instance ) {
				self::$instance = new Self();
			}

			// End
			return self::$instance;

		}

		/**
		 * Constructor
		**/
		protected function __construct()
		{

			$this->init_hooks();

		}

		/**
		 * Init WP Hooks
		**/
		protected function init_hooks()
		{

			#
			# Enqueue Scripts
			#
				/**
				 * Admin enqueue scripts
				 * 
				 * @param int $post_id
				**/
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

				/**
				 * Admin enqueue scripts
				 * 
				 * @param int $post_id
				**/
				add_action( 'save_post', array( $this, 'update_notice' ), 10, 3 );

			#
			# Save
			#
				/**
				 * Save Action for variation
				 * 
				 * @param int $post_id
				**/
				add_action( 'woocommerce_process_product_meta_simple', array( $this, 'save_product_simple' ), 10 );

			#
			# Render
			#
				/**
				 * Tab Filter
				 * 
				 * @param array : array(
				 * 		'general' => array(
				 * 			'label'    => __( 'General', 'woocommerce' ),
				 * 			'target'   => 'general_product_data',
				 * 			'class'    => array( 'hide_if_grouped' ),
				 * 			'priority' => 10,
				 * 		),
				 * 	)
				**/
				add_filter( 'woocommerce_product_data_tabs', array( $this, 'filter_product_form_tab' ), 100, 1 );

				add_filter( 'product_type_options', array( $this, 'product_type_options' ) );

				/**
				 * Triggered at the end of tab form of single product
				 * You should print the form view 
				**/
				add_action( 'woocommerce_product_options_general_product_data', array( $this, 'render_product_form' ), 10 );

				add_action( 'ntvwc_action_render_product_form_start', array( $this, 'ntvwc_action_render_product_form_start' ), 10, 1 );

		}

	/**
	 * Notices
	**/
		/**
		 * Trigger save post
		 * 
		**/
		function update_notice( $post_id, $post, $update )
		{

			$_ntvwc_token_value = get_post_meta( $post_id, '_ntvwc_token_value', true );
			if ( isset( $_ntvwc_token_value ) 
				&& is_string( $_ntvwc_token_value )
				&& '' !== $_ntvwc_token_value
			) {

				// Token Value
				$token_hashed_value = password_hash(
					$_ntvwc_token_value,
					PASSWORD_BCRYPT,
					array(
						'cost' => 10
					)
				);
				//wc_add_notice( $token_hashed_value );
				// Notice
				echo ntvwc_wrap_as_notices(
					sprintf(
						esc_html__( 'Validation Token Value was successfully Updated. Please Use this hashed value "%1$s" for the validation.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
						$token_hashed_value
					),
					'notice'
				);

			}

		}

		/**
		 * Get the message to notify hashed token
		 * @param int $product_id
		 * @return string
		**/
		protected function get_token_value_message( $product_id )
		{

			$_ntvwc_token_value = get_post_meta( $product_id, '_ntvwc_token_value', true );

			if ( isset( $_ntvwc_token_value ) 
				&& is_string( $_ntvwc_token_value )
				&& '' !== $_ntvwc_token_value
			) {

				// Token Value
				$token_hashed_value = password_hash(
					$_ntvwc_token_value,
					PASSWORD_BCRYPT,
					array(
						'cost' => 10
					)
				);

				// Notice
				return sprintf(
					esc_html__( 'Validation Token Value was successfully Updated. Please Use this hashed value "%1$s" for the validation.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
					$token_hashed_value
				);

			}

			return '';

		}

	#
	# Admin enqueue scripts
	#
		/**
		 * 
		**/
		public function admin_enqueue_scripts( $hook )
		{

			if ( ! isset( $hook )
				|| ! in_array( $hook, array( 'post-new.php', 'post.php' ) )
			) {
				return;
			}

			if ( 'post-new.php' === $hook ) {
				if ( ! isset( $_GET['post_type'] )
					|| 'product' !== $_GET['post_type']
				) {
					return;
				}
			}

			elseif ( 'post.php' === $hook ) {
				global $post;
				if ( 'product' !== $post->post_type ) {
					return;
				}
			}

			wp_enqueue_style( 'ntvwc-product-settings-css' );
			wp_enqueue_script( 'ntvwc-product-settings-js' );

		}

	#
	# Forms
	#
		/**
		 * Filter product tab of the form
		 * 
		 * @param array : array(
		 * 		'general' => array(
		 * 			'label'    => __( 'General', 'woocommerce' ),
		 * 			'target'   => 'general_product_data',
		 * 			'class'    => array( 'hide_if_grouped' ),
		 * 			'priority' => 10,
		 * 		),
		 * 	)
		**/
		public function filter_product_form_tab( $tab_data )
		{

			// Append the tab "ntvwc_content_type"
			$tab_data = apply_filters( 'ntvwc_filter_product_data_tabs', $tab_data );

			return $tab_data;

		}

		/**
		 * Filter product type options
		 * 
		 * @param array : array(
		 * 		'virtual' => array(
		 * 			'id'            => __( 'General', 'woocommerce' ),
		 * 			'wrapper_class' => 'show_if_simple',
		 * 			'label'         => __( 'Virtual', 'woocommerce' ),
		 * 			'description'   => __( 'Virtual products are intangible and are not shipped.', 'woocommerce' ),
		 * 			'description'   => 'no',
		 * 		),
		 * 	)
		 * @return array
		**/
		public function product_type_options( $product_type_options )
		{

			$product_type_options['ntvwc_type_token'] = array(
				'id'            => '_ntvwc_type_token',
				'wrapper_class' => implode( ' ', array( 
					'show_if_simple',
					'ntvwc_product_type_option_token',
				) ),
				'label'         => __( 'Token', 'woocommerce' ),
				'description'   => __( 'When purchase is completed, token is going to be generated, which client can get in token tab in their account page.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'default'       => 'no'
			);

			return $product_type_options;

		}

		/**
		 * Load template of form WP content type
		**/
		public function render_product_form()
		{

			global $post;
			$product_id = $post->ID;

			echo '<div class="options_group ntvwc_token_options show_if_simple">';

			do_action( 'ntvwc_action_render_product_form_start', $product_id );

			// Token Type
			woocommerce_wp_select( array(
				'id'            => "_ntvwc_token_type",
				'class'         => 'select',
				'wrapper_class' => implode( ' ', array( 
					'show_if_token',
					'show_if_validation_token',
					'show_if_update_token',
				) ),
				'name'          => "_ntvwc_token_type",
				'label'         => __( 'Token Type', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'desc_tip'      => true,
				'description'   => __( 'Please select "Validation" or "Update".', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'options'       => array(
					'validation' => __( 'Validation', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
					'update'     => __( 'Update', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				),
			) );

			// Token Value
			woocommerce_wp_text_input( array(
				'id'            => "_ntvwc_token_value",
				'class'         => 'short',
				'wrapper_class' => implode( ' ', array( 
					'show_if_token',
					'show_if_validation_token',
				) ),
				'name'          => "_ntvwc_token_value",
				'placeholder'   => __( 'Token value to be evaluated. Please enter unique or setting value that is expected by client.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'label'         => __( 'Token Value', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'desc_tip'      => true,
				'description'   => __( 'Token value to be evaluated. Please enter unique or setting value that is expected by client. Use this hashed value to be validated with the purchased token.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
			) );
			$_ntvwc_token_value = get_post_meta( $product_id, '_ntvwc_token_value', true );
			if ( isset( $_ntvwc_token_value ) 
				&& is_string( $_ntvwc_token_value )
				&& '' !== $_ntvwc_token_value
			) {

				// Token Value
				$token_hashed_value = password_hash(
					$_ntvwc_token_value,
					PASSWORD_BCRYPT,
					array(
						'cost' => 10
					)
				);

				// Message
				$format = '<p class="form-field _ntvwc_token_value_field show_if_token show_if_validation_token">
					%1$s
					<input class="hashed-token-value" value="%2$s" disabled > <a href="javascript: void(0);" class="button get-the-hashed-value">%3$s</a>
				</p>';
				printf( 
					$format,
					esc_html__( 'Please use the hashed value following for the validation.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
					$token_hashed_value,
					esc_html__( 'Copy', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
				);

			}

			// Validation Token Value
			woocommerce_wp_select( array(
				'id'            => "_ntvwc_token_validation_value",
				'class'         => 'select',
				'wrapper_class' => implode( ' ', array( 
					'show_if_token',
					'show_if_update_token',
				) ),
				'name'          => "_ntvwc_token_validation_value",
				'label'         => __( 'Validation Token Value', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'desc_tip'      => true,
				'description'   => __( 'Please select validation token value.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'options'       => ntvwc_get_validation_token_product_values(),
			) );

			// Token Expiry in Day
			woocommerce_wp_text_input( array(
				'id'            => "_ntvwc_token_expiry_in_day",
				'class'         => 'short',
				'wrapper_class' => implode( ' ', array( 
					'show_if_token',
					'show_if_validation_token',
					'show_if_update_token',
				) ),
				'name'          => "_ntvwc_token_expiry_in_day",
				'placeholder'   => "Token expiry in day.",
				'maxlength'     => "10",
				'pattern'       => "[0-9]+?",
				'label'         => __( 'Token Expiry', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'desc_tip'      => true,
				'description'   => __( 'Token expiry in day.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
			) );

			do_action( 'ntvwc_action_render_product_form_end', $product_id );

			echo '</div>';

		}

			public function ntvwc_action_render_product_form_start( $product_id )
			{

				echo '<p class="ntvwc-token-settings-label show_if_token show_if_validation_token show_if_update_token">'; echo esc_html__( 'Settings of Type Token', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ); echo '</p>';

			}


	#
	# Save
	#
		/**
		 * Save Action for variation
		 * 
		 * @param int $product_id
		**/
		public function save_product_simple( $product_id )
		{

			// Is Token
				$_ntvwc_type_token = 'no';
				if ( ! isset( $_POST['_ntvwc_type_token'] ) ) {
					delete_post_meta( $product_id, '_ntvwc_type_token' );
				} else {
					$_ntvwc_type_token = $this->sanitize_ntvwc_type_token( $_POST['_ntvwc_type_token'] );
					update_post_meta( $product_id, '_ntvwc_type_token', $_ntvwc_type_token );
				}

				if ( 'no' === $_ntvwc_type_token ) {
					delete_post_meta( $product_id, '_ntvwc_token_type' );
					delete_post_meta( $product_id, '_ntvwc_token_value' );
					delete_post_meta( $product_id, '_ntvwc_token_validation_value' );
					delete_post_meta( $product_id, '_ntvwc_token_expiry_in_day' );
					delete_post_meta( $product_id, '_ntvwc_restrict_url_access' );
					return;
				}

			// Token Type
				if ( ! isset( $_POST['_ntvwc_token_type'] ) || '' ===  $_POST['_ntvwc_token_type'] ) {
					update_post_meta( $product_id, '_ntvwc_token_type', 'validation' );
				} else {
					$_ntvwc_token_type = $this->sanitize_ntvwc_token_type( $_POST['_ntvwc_token_type'] );
					update_post_meta( $product_id, '_ntvwc_token_type', $_ntvwc_token_type );
				}

			// Validation Token
			if ( 'validation' === $_ntvwc_token_type ) {

				// Token Value
					if ( ! isset( $_POST['_ntvwc_token_value'] ) || '' ===  $_POST['_ntvwc_token_value'] ) {
						update_post_meta( $product_id, '_ntvwc_token_value', '' );
					} else {
						$_ntvwc_token_value = $this->sanitize_ntvwc_token_value( $_POST['_ntvwc_token_value'] );
						update_post_meta( $product_id, '_ntvwc_token_value', $_ntvwc_token_value );
					}

			}
			// Update Token
			elseif ( 'update' === $_ntvwc_token_type ) {

				// Token Value
					if ( ! isset( $_POST['_ntvwc_token_validation_value'] ) || '' ===  $_POST['_ntvwc_token_validation_value'] ) {
						update_post_meta( $product_id, '_ntvwc_token_validation_value', '' );
					} else {
						$_ntvwc_token_validation_value = $this->sanitize_ntvwc_token_validation_value( $_POST['_ntvwc_token_validation_value'] );
						update_post_meta( $product_id, '_ntvwc_token_validation_value', $_ntvwc_token_validation_value );
					}

				// Delete Validation Token Value
					delete_post_meta( $product_id, '_ntvwc_token_value' );
					delete_post_meta( $product_id, '_ntvwc_restrict_url_access' );

			}

			// Token Expiry in day
				if ( ! isset( $_POST['_ntvwc_token_expiry_in_day'] ) || '' ===  $_POST['_ntvwc_token_expiry_in_day'] ) {
					delete_post_meta( $product_id, '_ntvwc_token_expiry_in_day' );
				} else {
					$_ntvwc_token_expiry_in_day = $this->sanitize_ntvwc_token_expiry_in_day( $_POST['_ntvwc_token_expiry_in_day'] );
					update_post_meta( $product_id, '_ntvwc_token_expiry_in_day', $_ntvwc_token_expiry_in_day );
				}

			// Save Action
				do_action( 'ntvwc_action_save_product_simple', $product_id, $_ntvwc_type_token, $_ntvwc_token_type );

		}

	#
	# Sanitize methods
	#
		/**
		 * Sanitize data for the post meta "_ntvwc_product_package_type"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_ntvwc_type_token( $value )
		{

			// Check the required param
			if ( is_string( $value )
				&& in_array( $value, array( 'on', 'yes' ) )
			) {
				return 'yes';
			}

			// End
			return 'no';

		}

		/**
		 * Sanitize data for the post meta "_ntvwc_token_type"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_ntvwc_token_type( $value )
		{

			// Check the required param
			if ( ! is_string( $value ) 
				|| ! in_array( $value, array( 'validation', 'update' ) )
			) {
				return '';
			}

			// End
			return $value;

		}

		/**
		 * Sanitize data for the post meta "_ntvwc_token_value"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_ntvwc_token_value( $value )
		{

			// Check the required param
			if ( ! is_string( $value ) || '' === $value ) {
				return '';
			}

			// End
			return $value;

		}

		/**
		 * Sanitize data for the post meta "_ntvwc_token_validation_value"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_ntvwc_token_validation_value( $value )
		{

			$all_token_product_values = ntvwc_get_validation_token_product_values();
			if ( ! is_array( $all_token_product_values ) || 0 >= count( $all_token_product_values ) ) {
				return '';
			}

			// Check the required param
			if ( ! is_string( $value ) || ! in_array( $value, $all_token_product_values ) ) {
				return '';
			}

			// End
			return $value;

		}

		/**
		 * Sanitize data for the post meta "_ntvwc_token_expiry_in_day"
		 * 
		 * @param string $value
		 * 
		 * @return string
		**/
		protected function sanitize_ntvwc_token_expiry_in_day( $value )
		{

			// Check the required param
			if ( is_numeric( $value ) ) {
				return intval( $value );
			}

			// End
			return '';

		}

}
}
