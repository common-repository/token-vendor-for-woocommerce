<?php
if ( ! class_exists( 'NTVWC_Client_Page' ) ) {

/**
 * @todo cron exec
 * 1. Update validation_token
 * 2. Update json file
 * 3. Check the update with saved json
 * 
 * @uses option "{$this->unique_prefix}_ntvwc_client_token" for Token
 * @uses option "{$this->unique_prefix}_ntvwc_client_json_data" for last exec time of cron
 * @uses option "{$this->unique_prefix}_json_url_for_updates" for json_url update
 * @uses option "{$this->unique_prefix}_flag_to_require_update" for json_url update
 * @uses option "{$this->unique_prefix}_last_exec_time" for last exec time of cron
**/
final class NTVWC_Client_Page {

	/**
	 * Properties
	**/
	/**
	 * Vars
	**/
		/**
		 * Public
		**/
			/**
			 * Update checker
			 * 
			 * @var NTVWC_Client
			**/
			protected $ntvwc_client = null;

			/**
			 * Flag if it is multisite
			 * @var bool
			**/
			public $is_multisite = false;

		/**
		 * Protected
		**/
			/**
			 * Action Prefix
			 * 
			 * @var string
			**/
			protected $action_prefix = 'ntvwc_client_action_';

			/**
			 * Filter Prefix
			 * 
			 * @var string
			**/
			protected $filter_prefix = 'ntvwc_client_filter_';

	/**
	 * Init
	**/
		/**
		 * Public Initializer
		 * 
		 * @uses self::$instance
		 * 
		 * @param string $client_uri
		 * @param string $textdomain
		 * 
		 * @return Self
		**/
		public static function get_instance(
			$client_uri,
			$textdomain
		)
		{

			// Init if not yet
			$instance = new Self(
				$client_uri,
				$textdomain
			);

			// End
			return $instance;

		}

		/**
		 * Constructor
		 * @param string $client_uri
		 * @param string $textdomain
		**/
		public function __construct(
			$client_uri,
			$textdomain
		)
		{
			// Property
			$this->client_uri       = trailingslashit( $client_uri );
			$this->textdomain       = $textdomain;

			if ( defined( 'MULTISITE' ) && MULTISITE ) {
				$this->is_multisite = true;
			} else {
				$this->is_multisite = false;
			}

			// Init WP hooks
			$this->init_hooks();

		}

		/**
		 * Init WP hooks
		**/
		protected function init_hooks()
		{

			#
			# Actions
			#
				// Update the option
				if ( $this->is_multisite ) {
					add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
				}

				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'all_admin_notices', array( $this, 'admin_notice' ) );

				// Notices
				//add_action( 'all_admin_notices', array( $this, 'admin_notice' ) );
				//add_action( 'all_admin_notices', array( $this, 'admin_notice' ) );

		}

	/**
	 * Actions
	**/
		public function admin_notice()
		{

			$token_expiry = intval( $this->ntvwc_client->get_validation_token_expiry() );

			if ( 0 < $token_expiry ) {

				$current_time = intval( current_time( 'timestamp', false ) );
				$difference = $token_expiry - $current_time;

				if ( WEEK_IN_SECONDS > $difference ) {

					$expiry_date = date_i18n( 'Y-m-d', $token_expiry, true );
					$format = '<span class="plugin-label">%1$s</span>: <span class="expiry-label">%2$s</span> <span class="expiry-value">%3$s</span>';
					$message = sprintf( 
						$format, 
						$this->ntvwc_client->get_plugin_name(),
						esc_html__( 'Validation Token will be expired at', $this->ntvwc_client->get_textdomain() ),
						$expiry_date
					);
					$this->ntvwc_client->add_notice_message( $message, 'notice' );

				}

			}

		}

		public function admin_menu()
		{
			//do_action( $this->ntvwc_client->get_prefixed_action_hook( 'admin_menu' ), $this );
			$this->update_the_token();
			$this->add_admin_page();

		}

		public function add_admin_page()
		{

			// Admin Page
				// Submenu Page
				add_submenu_page(
					'index.php',
					sprintf( esc_html__( 'Token Validator: %s', $this->ntvwc_client->get_textdomain() ), $this->ntvwc_client->get_plugin_name() ), 
					sprintf( esc_html__( 'Token Validator: %s', $this->ntvwc_client->get_textdomain() ), $this->ntvwc_client->get_plugin_name() ), 
					'manage_options', 
					$this->ntvwc_client->get_prefixed_value( 'custom_ntvwc_client' ), 
					array( $this, 'render_admin_page' )
				);

		}

		/**
		 * Render Admin Page
		**/
		public function render_admin_page()
		{

			// Load Template
			ob_start();
				require( trailingslashit( dirname( dirname( __FILE__ ) ) ) . 'view/form-ntvwc-client-settings.php' );
			$custom_ntvwc_client_settings = ob_get_clean();

			/**
			 * Print through filter
			 * 
			 * @param string $custom_ntvwc_client_settings : HTML
			 * @param string $this->ntvwc_client->get_unique_prefix()
			**/
			echo apply_filters( $this->ntvwc_client->get_prefixed_filter_hook( 'setting_page' ), $custom_ntvwc_client_settings, $this->ntvwc_client->get_unique_prefix() );

		}

		/**
		 * Admin enqueue scripts
		 * 
		 * @param string $hook
		**/
		public function admin_enqueue_scripts( $hook )
		{

			if ( ! is_admin()
				|| ! isset( $_GET['page'] )
				|| $_GET['page'] !== $this->ntvwc_client->get_prefixed_value( 'custom_ntvwc_client' )
			) {
				return;
			}

		}


	/**
	 * Option
	**/
		/**
		 * Save ntvwc client token
		 * 
		 * @param [NTVWC_Client] $paramname description
		**/
		public function update_the_token()
		{

			// Check if this request is by ntvwc client
			if ( ! isset( $_POST['ntvwc-client'] )
				|| ! is_string( $_POST['ntvwc-client'] )
				|| '' === $_POST['ntvwc-client']
			) {
				return;
			}

			// Vars
			$validation_token_name  = $this->ntvwc_client->get_prefixed_value( 'validation_token' );
			$update_token_name      = $this->ntvwc_client->get_prefixed_value( 'update_token' );
			$button_name            = $this->ntvwc_client->get_prefixed_value( 'save_ntvwc_client_token' );
			$nonce_name             = $this->ntvwc_client->get_prefixed_value( 'ntvwc_client_nonce' );

			// Check
			if ( ! isset( $_POST[ $button_name ] )
				|| ! isset( $_POST[ $nonce_name ] )
			) {
				$this->ntvwc_client->add_notice_message( __( 'Button is not set.', $this->ntvwc_client->get_textdomain() ), 'updated' );
				return;
			}

			// Admin Referer
			check_admin_referer( $this->ntvwc_client->get_prefixed_value( 'ntvwc_client' ), $nonce_name );

			// Check the required params
			if ( ! isset( $_REQUEST[ $validation_token_name ] ) ) {
				$this->ntvwc_client->add_notice_message( __( 'Token name is not set.', $this->ntvwc_client->get_textdomain() ), 'updated' );
				return;
			}

			// Validation Token
				$validation_token = sanitize_text_field( $_REQUEST[ $validation_token_name ] );
				$old_validation_token = get_option( $this->ntvwc_client->get_prefixed_value( 'ntvwc_client_validation_token' ), '' );
				if ( $old_validation_token !== $validation_token ) {
					$this->ntvwc_client->add_notice_message( __( 'Token was successfully updated.', $this->ntvwc_client->get_textdomain() ), 'updated' );
				}

				// Save
				$option_result = $this->ntvwc_client->update_ntvwc_client_validation_token( $validation_token );

			// update Token
				$update_token = sanitize_text_field( $_REQUEST[ $update_token_name ] );
				$old_update_token = get_option( $this->ntvwc_client->get_prefixed_value( 'ntvwc_client_update_token' ), '' );
				if ( $old_update_token !== $update_token ) {
					$this->ntvwc_client->add_notice_message( __( 'Token was successfully updated.', $this->ntvwc_client->get_textdomain() ), 'updated' );
				}

				// Save
				$option_result = $this->ntvwc_client->update_ntvwc_client_update_token( $update_token );
				if ( '' !== $update_token ) {
					$expiry = $this->ntvwc_client->get_validation_token_expiry();

				}

		}

	/**
	 * Update checker
	**/
		/**
		 * Set ntvwc client to the property
		 * 
		 * @param [NTVWC_Client] $ntvwc_client description
		**/
		public function set_ntvwc_client( &$ntvwc_client )
		{

			$this->ntvwc_client = $ntvwc_client;

		}


}
}


