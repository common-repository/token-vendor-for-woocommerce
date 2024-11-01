<?php
// Check if WP is Loaded
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 3rds
	// JWT
	if ( ! class_exists( '\Lcobucci\JWT\Token' ) ) {
		require_once( NTVWC_DIR_PATH . 'includes/3rd/jwt/autoload.php' );
	}

// Functions
	// General
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-general.php' );
	// Detect
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-detect.php' );
	// Option
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-option.php' );
	// Sanitizer
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-sanitizer.php' );
	// Notice
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-notice.php' );
	// Product
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-product.php' );
	// Order
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-order.php' );
	// Post Meta
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-post-meta.php' );
	// Rest API
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-rest-api.php' );
	// HTML
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-html.php' );
	// Template
	require_once( NTVWC_DIR_PATH . 'includes/function/functions-template.php' );

// Interfaces
	// Data
	require_once( NTVWC_DIR_PATH . 'includes/interface/class-ntvwc-data-interface.php' );
	// Data Store
	require_once( NTVWC_DIR_PATH . 'includes/interface/class-ntvwc-data-store-interface.php' );
	// Data Store Option
	require_once( NTVWC_DIR_PATH . 'includes/interface/class-ntvwc-data-store-option-interface.php' );

// Abstract
	// Endpoint
	require_once( NTVWC_DIR_PATH . 'includes/abstract/class-ntvwc-endpoint-abstract.php' );
	// Data
	require_once( NTVWC_DIR_PATH . 'includes/abstract/class-ntvwc-data-abstract.php' );
	// Data CPT
	require_once( NTVWC_DIR_PATH . 'includes/abstract/class-ntvwc-data-cpt-abstract.php' );
	// Data Store
	require_once( NTVWC_DIR_PATH . 'includes/abstract/class-ntvwc-data-store-abstract.php' );
	// Mail
	require_once( NTVWC_DIR_PATH . 'includes/abstract/class-ntvwc-mail-abstract.php' );
	// Unique
	require_once( NTVWC_DIR_PATH . 'includes/abstract/class-ntvwc-unique-abstract.php' );

// Global
	// Endpoint
	require_once( NTVWC_DIR_PATH . 'includes/endpoint/class-ntvwc-endpoint-purchased-tokens.php' );
	// Token Values
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-token-values.php' );
	// Translatable Texts
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-translatable-texts.php' );
	// Sanitize Methods
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-sanitize-methods.php' );
	// Order Manager
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-order-manager.php' );
	// Option Manager
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-option-manager.php' );
	// JWT Methods
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-token-methods.php' );
	// Data Store Loader
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-data-store-loader.php' );
	// NTVWC_REST_API_Loader
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-rest-api-loader.php' );
	// NTVWC_Token_Manager
	require_once( NTVWC_DIR_PATH . 'includes/class-ntvwc-token-manager.php' );

// Data
	// Option
	require_once( NTVWC_DIR_PATH . 'includes/data/class-ntvwc-data-option.php' );
	// NTVWC_Data_Product_Token
	require_once( NTVWC_DIR_PATH . 'includes/data/class-ntvwc-data-product-token.php' );
	// NTVWC_Data_Purchased_Token
	require_once( NTVWC_DIR_PATH . 'includes/data/class-ntvwc-data-purchased-token.php' );

// Data Store
	// Option
	require_once( NTVWC_DIR_PATH . 'includes/data-store/class-ntvwc-data-store-option.php' );

// Notification
	// NTVWC_Notification_Manager
	require_once( NTVWC_DIR_PATH . 'includes/notification/class-ntvwc-notification-manager.php' );
	// NTVWC_Post_Type_Notification
	require_once( NTVWC_DIR_PATH . 'includes/notification/class-ntvwc-post-type-notification.php' );
	// NTVWC_Exception
	require_once( NTVWC_DIR_PATH . 'includes/notification/class-ntvwc-exception.php' );
	// NTVWC_Mail
	require_once( NTVWC_DIR_PATH . 'includes/notification/class-ntvwc-mail.php' );

// Order
	// NTVWC_Order
	require_once( NTVWC_DIR_PATH . 'includes/order/class-ntvwc-order.php' );

// Token
	// NTVWC_Data_Token
	require_once( NTVWC_DIR_PATH . 'includes/token/class-ntvwc-data-token.php' );
	// NTVWC_Post_Type_Token
	require_once( NTVWC_DIR_PATH . 'includes/token/class-ntvwc-post-type-token.php' );
	// NTVWC_Purchased_Token
	require_once( NTVWC_DIR_PATH . 'includes/token/class-ntvwc-token-handler.php' );
	// NTVWC_Purchased_Token
	require_once( NTVWC_DIR_PATH . 'includes/token/class-ntvwc-token-validator.php' );

// Managers

// Admin
	// NTVWC_Admin
	require_once( NTVWC_DIR_PATH . 'includes/admin/class-ntvwc-admin.php' );
	// NTVWC_Admin_Pages
	require_once( NTVWC_DIR_PATH . 'includes/admin/class-ntvwc-admin-pages.php' );
	// NTVWC_Product_Metabox
	require_once( NTVWC_DIR_PATH . 'includes/admin/class-ntvwc-product-metabox.php' );
	// NTVWC_Order_Metabox
	require_once( NTVWC_DIR_PATH . 'includes/admin/class-ntvwc-order-metabox.php' );

	// File
		// NTVWC_Filesystem_Methods
		require_once( NTVWC_DIR_PATH . 'includes/admin/file/class-ntvwc-filesystem-methods.php' );

// Auth
	// NTVWC_REST_API
	require_once( NTVWC_DIR_PATH . 'includes/rest-api/class-ntvwc-rest-api.php' );
	// NTVWC_REST_API_JWT
	require_once( NTVWC_DIR_PATH . 'includes/rest-api/class-ntvwc-rest-api-jwt.php' );
	// NTVWC_REST_API_Endpoints
	require_once( NTVWC_DIR_PATH . 'includes/rest-api/class-ntvwc-rest-api-endpoints.php' );
	// NTVWC_REST_API_Endpoints_JWT
	require_once( NTVWC_DIR_PATH . 'includes/rest-api/class-ntvwc-rest-api-endpoints-jwt.php' );

