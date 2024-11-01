<?php

if ( ! class_exists( 'NTVWC_Post_Type_Notification' ) ) {

class NTVWC_Post_Type_Notification {

	/*
	 * Statics
	**/
		/**
		 * Instance of the Class
		 * 
		 * @var [Nora_Token_Vendor_For_WooCommerce]
		**/
		private static $instance;

	/**
	 * Settings
	**/
		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone()
		{
			ntvwc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Clone.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup()
		{
			ntvwc_doing_it_wrong( __FUNCTION__, esc_html__( 'DO NOT Unserialize', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ), '1.0.0' );
		}

	/**
	 * Init
	**/
		/**
		 * Public Initializer
		 * @return Self
		**/
		public static function get_instance()
		{
			if ( null === self::$instance ) {
				self::$instance = new Self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		**/
		private function __construct()
		{

			$this->init_hooks();

		}

		/**
		 * Init Hooks
		**/
		private function init_hooks()
		{

			add_action( 'init', array( $this, 'register_post_types' ) );

			//add_filter( 'map_meta_cap', array( $this, 'add_notification_caps' ), 10, 4 );

		}

		/**
		 * Register Post Type "token"
		**/
		public function register_post_types()
		{

			$labels = array(
				'name'               => _x( 'Notifications', 'post type general name', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'singular_name'      => _x( 'Notification', 'post type singular name', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'menu_name'          => _x( 'Notifications', 'admin menu', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'name_admin_bar'     => _x( 'Notification', 'add new on admin bar', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'add_new'            => _x( 'Add New', 'token', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'add_new_item'       => __( 'Add New Notification', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'new_item'           => __( 'New Notification', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'edit_item'          => __( 'Edit Notification', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'view_item'          => __( 'View Notification', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'all_items'          => __( 'All Notifications', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'search_items'       => __( 'Search Notifications', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'parent_item_colon'  => __( 'Parent Notifications:', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'not_found'          => __( 'No notifications found.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'not_found_in_trash' => __( 'No notifications found in Trash.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
			);

			$args = apply_filters( 'ntvwc_filter_post_type_args', array(
				'labels'              => $labels,
				'description'         => __( 'Description.', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN ),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'query_var'           => false,
				'rewrite'             => array( 'slug' => 'ntvwc-notification' ),
				'capability_type'     => 'ntvwc_notification',
				'map_meta_cap'        => false,
				'has_archive'         => false,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array( 'title', 'editor', 'author' )
			), 'ntvwc-notification' );

			register_post_type( 'ntvwc-notification', $args );

			// Caps
				$shop_manager_caps = array(
					'edit_ntvwc_notifications',
					'edit_others_ntvwc_notifications',
					//'publish_ntvwc_notifications',
					'read_private_ntvwc_notifications',
					'read_ntvwc_notifications',
					'delete_ntvwc_notifications',
					'delete_private_ntvwc_notifications',
					'delete_published_ntvwc_notifications',
					'delete_others_ntvwc_notifications',
					//'edit_private_ntvwc_notifications',
					//'edit_published_ntvwc_notifications',
					//'create_ntvwc_notifications',
				);

				$other_caps = array(
					'read_private_ntvwc_notifications',
					'read_ntvwc_notifications',
				);

			$caps_map = array(
				'administrator' => $shop_manager_caps,
				'shop_manager'  => $shop_manager_caps,
				'editor'        => $other_caps,
				'author'        => $other_caps,
				'contributor'   => $other_caps
			);

			foreach ( $caps_map as $role_name => $caps ) {
				$role = get_role( $role_name );

				if ( empty( $role ) ) {
					continue;
				}

				foreach ( $caps as $cap ) {
					if ( ! $role->has_cap( $cap ) ) {
						$role->add_cap( $cap );
					}
				}
			}

		}

}
}