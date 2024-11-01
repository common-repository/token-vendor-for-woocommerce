<?php
if ( ! class_exists( 'NTVWC_Client' ) ) {

	if ( ! class_exists( 'Nora_Token_Vendor_Client_Abstract' ) ) require_once( 'class-nora-token-vendor-client-abstract.php' );
	if ( ! class_exists( 'Nora_Token_Vendor_Client' ) ) require_once( 'class-nora-token-vendor-client.php' );
	if ( ! class_exists( 'NTVWC_Client_Page' ) ) require_once( 'class-ntvwc-client-page.php' );

/**
 * 1. Update token
 * 2. Update json file
 * 3. Check the update with saved json
 * 
 * @version 1.0.0
 * 
 * @uses option "{$this->unique_prefix}_ntvwc_client_token" for Token
 * @uses option "{$this->unique_prefix}_json_url_for_updates" for json_url update
 * @uses option "{$this->unique_prefix}_flag_to_require_update" for json_url update
 * @uses option "{$this->unique_prefix}_last_exec_time" for last exec time of cron
**/
class NTVWC_Client extends Nora_Token_Vendor_Client_Abstract {

	/**
	 * Consts
	**/
		const VERSION = '1.0.0';
		const REQUEST_URI_VALIDATE = 'wp-json/ntvwc/v1/token/validate';

	/**
	 * Properties
	**/
		/**
		 * Protected
		**/
			/**
			 * Update checker version
			 * 
			 * @var string
			**/
			protected $version = '1.0.0';

			/**
			 * Hash salt
			 * 
			 * @var string
			**/
			protected $hash_salt = '$6$rounds=5000$ntvwc_hash$';

			/**
			 * Hash cost
			 * 
			 * @var int
			**/
			protected $hash_cost = 10;

			/**
			 * Plugin name
			 * 
			 * @var string
			**/
			protected $plugin_name = '';

			/**
			 * Plugin version
			 * 
			 * @var string
			**/
			protected $plugin_version = '';

			/**
			 * Route base
			 * 
			 * @var string
			**/
			protected $route_name_base = 'ntvwc';

			/**
			 * Textdomain
			 * 
			 * @var string
			**/
			protected $textdomain = '';

			/**
			 * Unique prefix
			 * 
			 * @var string
			**/
			protected $unique_prefix = '';

			/**
			 * Plugin directory name
			 * 
			 * @var string
			**/
			protected $plugin_dir_name = '';

			/**
			 * Plugin directory path
			 * 
			 * @var string
			**/
			protected $plugin_dir_path = '';

			/**
			 * Token which has purchase data
			 * @uses option "{$this->unique_prefix}_ntvwc_client_validation_token"
			 * 
			 * @var string
			**/
			protected $ntvwc_client_validation_token = '';

			/**
			 * Token which has purchase data
			 * @uses option "{$this->unique_prefix}_ntvwc_client_update_token"
			 * 
			 * @var string
			**/
			protected $ntvwc_client_update_token = '';

			/**
			 * URL to request the update 
			 * 
			 * @var string
			**/
			protected $api_url_base = '';

			/**
			 * Whether current version is latest 
			 * Saved in option 'is_latest_version'
			 * 
			 * @var bool
			**/
			protected $is_latest_version;

			/**
			 * Whether the token is expired. 
			 * Saved in option 'is_token_expired'
			 * 
			 * @var bool
			**/
			protected $is_token_expired = false;

			/**
			 * API URL holder
			 * 
			 * @var array
			**/
			protected $api_urls = array();

			/**
			 * Notices
			 * 
			 * @var array
			**/
			protected $notice_messages = array();

			/**
			 * NTVWC_Client_Page
			**/
			protected $ntvwc_client_page = null;

			/**
			 * 
			 * 
			**/
			protected $result = array();

			/**
			 * 
			 * 
			**/
			protected $result_data = array();

			/**
			 * Flag if it is multisite
			 * @var bool
			**/
			public $is_multisite = false;

	/**
	 * Vars
	**/
		/**
		 * Public
		**/

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
		 * @param string $plugin_file_path
		 * @param string $textdomain
		 * @param string $plugin_dir_name
		 * @param string $api_url_base
		 * 
		 * @return Self
		**/
		public static function get_instance(
			$plugin_file_path,
			$textdomain,
			$plugin_dir_name,
			$api_url_base
		)
		{

			// Init if not yet
			$instance = new Self(
				$plugin_file_path,
				$textdomain,
				$plugin_dir_name,
				$api_url_base
			);

			// End
			return $instance;

		}

		/**
		 * Constructor
		 * 
		 * @param string $plugin_file_path
		 * @param string $textdomain
		 * @param string $plugin_dir_name
		 * @param string $api_url_base
		**/
		protected function __construct(
			$plugin_file_path,
			$textdomain,
			$plugin_dir_name,
			$api_url_base
		)
		{

			// Flag
			if ( false !== strpos( $plugin_file_path, 'wp-content/plugins' ) ) {
				$this->is_theme = false;
				$this->is_plugin = true;
			} elseif ( false !== strpos( $plugin_file_path, 'wp-content/themes' ) ) {
				$this->is_theme = true;
				$this->is_plugin = false;
			}

			$data = array(
				'client_uri' => trailingslashit( get_site_url() ),
				'host_uri'   => trailingslashit( $this->api_url_base ),
			);

			parent::init_vars( $data );

			// Property
			$this->plugin_file_path = $plugin_file_path;
 			$this->textdomain       = $textdomain;
			$this->plugin_dir_name  = $plugin_dir_name;
			$this->unique_prefix    = strtolower( preg_replace( '/[^a-zA-Z0-9]+/i', '_', $textdomain ) );
			$this->api_url_base     = trailingslashit( esc_url( $api_url_base ) );

			$this->set_action_prefix();
			$this->set_filter_prefix();

			if ( defined( 'MULTISITE' ) && MULTISITE ) {
				$this->is_multisite = true;
			} else {
				$this->is_multisite = false;
			}

			// Init ntvwc client
			$this->ntvwc_client_page = new NTVWC_Client_Page(
				trailingslashit( get_site_url() ),
				$this->textdomain
			);

			// Directory
			$this->set_plugin_dir_path();

			// API
				// Add API URLs
					// Get content data
					$this->set_api_url(
						'validate',
						$this->api_url_base . NTVWC_Client::REQUEST_URI_VALIDATE
					);

			// Init WP hooks
			$this->init_hooks();

			// Include files
			$this->includes();

			// Init classes
			$this->init_classes();

		}

		/**
		 * Init WP hooks
		**/
		protected function init_hooks()
		{

			#
			# Actions
			#
				// Init vars 
				if ( $this->is_multisite ) {
					add_action( 'network_admin_menu', array( $this, 'init_vars' ), 5 );
				}

				add_action( 'admin_menu', array( $this, 'init_vars' ), 5 );

				// Init vars 
				add_action( 'all_admin_notices', array( $this, 'all_admin_notices' ), 5 );

			#
			# Filters
			#
				add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );

			#
			# Option
			# 
				add_filter( $this->get_prefixed_filter_hook( 'update_ntvwc_client_validation_token' ), array( $this, 'reset_cron_for_update_token' ), 10, 2 );

			#
			#  Cron
			#  
				add_action( $this->get_prefixed_action_hook( 'cron_validation' ), array( $this, 'cron_validate_token' ), 10 );
				register_activation_hook( $this->plugin_file_path, array( $this, 'activate' ) );
				register_deactivation_hook( $this->plugin_file_path, array( $this, 'deactivate' ) );

		}

			/**
			 * Activate
			**/
			public function activate()
			{
				$this->reset_cron();
			}

			/**
			 * Deactivate
			**/
			public function deactivate()
			{
				$this->delete_cron();
			}

			/**
			 * Init vars
			 * 
			 * Need to be called after action 'admin_menu' because get_plugin_data is not defined before that hook.
			**/
			public function init_vars( $data = array() )
			{

				// Set ntvwc client to the property of the page
				$this->ntvwc_client_page->set_ntvwc_client( $this );

				// File data
				if ( $this->is_theme ) {

					$this->plugin_file_data = wp_get_theme();
					$this->set_plugin_name( $this->plugin_file_data->get( 'Name' ) );
					$this->set_plugin_version( $this->plugin_file_data->get( 'Version' ) );

				}
				elseif ( $this->is_plugin ) {

					$this->plugin_file_data = get_plugin_data( $this->plugin_file_path );
					$this->set_plugin_name( $this->plugin_file_data['Name'] );
					$this->set_plugin_version( $this->plugin_file_data['Version'] );

				}

				do_action( $this->get_prefixed_action_hook( 'init_vars_end' ), $this );

			}

		/**
		 * Includes files
		**/
		protected function includes()
		{

		}

		/**
		 * Init classes
		**/
		protected function init_classes()
		{

		}

	/**
	 * API
	**/
		/**
		 * Validate Token
		 * @param string $value : 
		 * @return bool
		**/
		public function validate_token( $value = '' )
		{

			if ( ! is_admin() 
				|| ! is_string( $value )
				|| '' === $value
			) {
				return false;
			}

			if ( ! is_string( $this->get_saved_validation_token() ) 
				|| '' === $this->get_saved_validation_token()
			) {
				return false;
			}

			// Set Current Result
				// Current Result
					$result_data = $this->get_current_result_data( $value );
					if ( isset( $this->result_data[ $value ] )
						&& is_array( $this->result_data[ $value ] )
						&& 0 < count( $this->result_data[ $value ] )
					) {
						return $this->validate_token_from_saved_data( $value );
					}

				// Saved Result
					$need_update_saved_result = $this->need_update_saved_result( $value );
					if ( ! $need_update_saved_result ) {

						// Saved Result
						$data = $this->get_saved_result_data( $value );
						if ( false !== $data ) {
							$this->set_current_result_data( $value, $data );
							return $this->validate_token_from_saved_data( $value );
						}

					}

			// Request and save the result
				if ( ! isset( $this->result_data[ $value ] ) ) {
					$result = $this->request_validate_token( $value );
				}

			// Save the tokens
			if ( isset( $this->result[ $value ] ) 
				&& is_array( $this->result[ $value ] ) 
				&& isset( $this->result[ $value ]['body'] )
			) {

				$args = $this->get_request_post_fields( array( 'value' => $value ) );
				$decoded_args = json_decode( $args['ntvwc'], true );
				$this->update_token_by_validation_result( $value, $decoded_args );

			}

			return $this->validate_token_from_saved_data( $value );

		}

			/**
			 * Validate token from saved data
			 * @uses $this->result_data[ $value ]
			 * @param string $value
			 * @return bool
			**/
			protected function validate_token_from_saved_data( $value )
			{

				if ( ! isset( $this->result_data[ $value ] )
					|| ! is_array( $this->result_data[ $value ] )
					|| 0 >= count( $this->result_data[ $value ] )
				) {
					return false;
				}

				if ( isset( $this->result_data[ $value ]['token'] )
					&& isset( $this->result_data[ $value ]['expiry'] )
				) {
					return true;
				} elseif ( isset( $this->result_data[ $value ]['error_message'] ) ) {
					$this->add_notice_message( $this->result_data[ $value ]['error_message'] );
					return false;
				}

				return false;

			}

			/**
			 * Update Token
			 * @param string $value
			 * @return array
			 *     'token'
			 *     'code'
			 *     'update_token'
			**/
			protected function request_validate_token( $value )
			{

				$returned = array();

				try {

					$args = $this->get_request_post_fields( array( 'value' => $value ) );
					if ( $this->need_validate_again( $value ) ) {

						$this->result[ $value ] = $this->result_data[ $value ] = array();

						if ( false === $args ) {
							throw new Exception( esc_html__( 'Wrong Post Fields', $this->get_textdomain() ) );
						}

						$this->result[ $value ] = apply_filters( 
							$this->get_prefixed_filter_hook( 'wp_remote_post' ),
							wp_remote_post(
								$this->get_api_url( 'validate' ),
								array(
									'method' => 'POST',
									'body'   => $args
								)
							)
						);

						if ( is_wp_error( $this->result[ $value ] ) ) {
							throw new Exception( implode( '<br>', $this->result[ $value ]->get_error_messages() ) );
						}

						$validate_state = $this->parse_current_result_into_validate_state( $value );
						if ( ! $validate_state['will_save'] ) {
							throw new Exception( esc_html__( 'Not Valid data is set.' ) );
						}

						if ( isset( $validate_state['result_body_data'] ) ) {
							$result_body_data = $validate_state['result_body_data'];
							if ( isset( $this->result[ $value ]['body'] ) ) {
								$result_data = json_decode( $this->result[ $value ]['body'], true );
								if ( null !== $result_data ) {
									$this->set_current_result_data( $value, $result_data );
									$this->update_the_timer( $value );
									$this->update_saved_result_data( $value );
								}
								do_action( $this->get_prefixed_action_hook( 'validate_token_has_body' ), $this, $result_data, $value );
							}
						}

						if ( $this->need_validate_again( $value ) ) {
							throw new Exception( esc_html__( 'Invalid Request.', $this->get_textdomain() ) );
						}

					}

				} catch( Exception $e ) {
					do_action( $this->get_prefixed_action_hook( 'validate_token_error' ), $this, $e, $value );
					$validation_result = false;
					return array(
						'error_message' => $e->getMessage()
					);
				}

				return $result_data;

			}

		/**
		 * Get Post Fields by data
		 * @param array $options : Default "array()"
		 * @return mixed
		**/
		protected function get_request_post_fields( $options = array() )
		{

			try {

				$validation_token = $this->get_saved_validation_token();
				$update_token     = $this->get_saved_update_token();

				$hashed_client_uri = $this->get_hashed_client_uri();
				if ( ! is_string( $hashed_client_uri ) || '' === $hashed_client_uri ) {
					throw new Exception( '$hashed_client_uri is not set or is invalid' );
				}

				$request_post_field = array( 
					'client_version' => self::VERSION,
					'token'          => $validation_token,
					'client_uri'     => $hashed_client_uri,
				);

				if ( is_string( $update_token ) && '' !== $update_token ) {
					$request_post_field['update_token'] = $update_token;
				}

				if ( is_array( $options ) && 0 < count( $options ) ) {
				foreach ( $options as $option_key => $option_value ) {
					if ( isset( $options[ $option_key ] ) ) {
						$request_post_field[ $option_key ] = $option_value;
					}
				}
				}

			} catch( Exception $e ) {
				trigger_error( $e->getMessage() );
				return false;
			}

			return array( 
				'ntvwc' => json_encode( $request_post_field, JSON_UNESCAPED_UNICODE )
			);

		}

	/**
	 * Result
	**/
		/**
		 * Generate the state by the result
		 * @uses array $this->result
		 * @param string $value
		 * @return array
		**/
		protected function parse_current_result_into_validate_state( $value )
		{

			$return = array(
				'will_save' => false,
				'short_interval' => false,
			);

			if ( ! isset( $this->result[ $value ] ) ) {
				$return['error_message'] = esc_html__( 'The result is not set.', $this->get_textdomain() );
				return $return;
			}
			if ( is_wp_error( $this->result[ $value ] ) ) {
				$return['error_message'] = implode( '<br>', $this->result[ $value ]->get_error_messages() );
				return $return;
			}

			$target_result = $this->result[ $value ];

			if ( ntvwc_is_numeric_between( $target_result['response']['code'], 400, 499 ) ) {
				$return['will_save'] = true;
				$return['error_message'] = esc_html__( 'Invalid Request.', $this->get_textdomain() );
				return $return;
			}

			if ( ntvwc_is_numeric_between( $target_result['response']['code'], 500 ) ) {
				$return['will_save'] = true;
				$return['short_interval'] = true;
				$return['error_message'] = esc_html__( 'Server Error.', $this->get_textdomain() );
				return $return;
			}

			if ( ntvwc_is_numeric_between( $target_result['response']['code'], null, 399 ) ) {
				$return['will_save'] = true;
				if ( isset( $target_result['body'] ) && is_string( $target_result['body'] ) && '' !== $target_result['body'] ) {
					$result_body = $target_result['body'];
					$result_body_data = json_decode( $result_body, true );
					$return['result_body_data'] = $result_body_data;
				}
				return $return;

			}

		}

			/**
			 * Generate the state by the result
			 * @param string $value
			 * @param mixed $result_data
			 * @return bool
			**/
			protected function set_current_result_data( string $value, $result_data )
			{

				if ( ! is_string( $value ) || '' === $value ) {
					return false;
				}

				if ( empty( $result_data ) ) {
					return false;
				}

				$this->result_data[ $value ] = $result_data;

				return true;


			}

			/**
			 * Update Token
			**/
			protected function update_token_by_validation_result( $value, $args )
			{

				if ( isset( $this->result_data[ $value ] )
					|| ! is_array( $this->result_data[ $value ] ) 
					|| 0 >= count( $this->result_data[ $value ] )
				) {
					return false;
				}

				$validation_token = '';
				if ( isset( $this->result_data[ $value ]['token'] ) ) {
					$validation_token = $this->result_data[ $value ]['token'];
				}

				if ( is_string( $validation_token ) && '' !== $validation_token ) {
					$result = $this->update_ntvwc_client_validation_token( $validation_token );
				}

				if ( $result && isset( $args['update_token'] ) ) {
					ntvwc_notice_message( esc_html__( 'Token Expiry was successfully updated.', $this->get_textdomain() ) );
					$this->update_ntvwc_client_update_token( '' );
				}

			}

		/**
		 * Current
		**/
			/**
			 * Get Result Data
			 * @param string $key
			 * @return mixed
			**/
			protected function get_current_result_data( string $value, string $key = '' )
			{

				if ( ! is_string( $key ) || '' === $key ) {
					if ( isset( $this->result_data[ $value ] ) ) {
						return $this->result_data[ $value ];
					}
					return null;
				}

				if ( isset( $this->result_data[ $value ] )
					&& isset( $this->result_data[ $value ][ $key ] ) 
					&& ! empty( $this->result_data[ $value ][ $key ] )
				) {
					return $this->result_data[ $value ][ $key ];
				}

				return null;

			}

			/**
			 * Get token expiry
			 * @return null|int : Timestamp of the expiry
			**/
			public function get_validation_token_expiry()
			{

				if ( ! is_array( $this->result_data ) 
					|| 0 >= count( $this->result_data )
				) {
					return null;
				}

				// Each
				foreach ( $this->result_data as $each_value => $each_result_data ) {
					$need_validate_again = $this->need_validate_again( $each_value );
					if ( $need_validate_again ) {
						$result = $this->validate_token( $each_value );
						if ( $result ) {
							$need_validate_again = false;
							break;
						}
					}
				}

				$need_validate_again = $this->need_validate_again( $each_value );
				if ( $need_validate_again ) {
					$result = $this->validate_token( $each_value );
					if ( $result ) {
						$need_validate_again = false;
					}
				}

				if ( ! $need_validate_again ) {
					return intval( $this->get_current_result_data( $each_value, 'expiry' ) );
				}

				return null;

			}

	/**
	 * Transient
	**/
		/**
		 * Timer
		**/
			/**
			 * Get the timers
			 * @return int[]
			**/
			protected function get_the_timers()
			{
				$transient_name_timer = $this->get_prefixed_value( 'ntvwc_client_rest_result_timer' );
				$timer_data = get_transient( $transient_name_timer );
				if ( false === '{}' ) {
					return array();
				}
				$timer_data = json_decode( $timer_data, true );
				if ( null === $timer_data ) {
					return array();
				}
				return $timer_data;
			}

			/**
			 * Get the timestamp
			 * @param string $value
			 * @return int
			**/
			protected function get_the_timer( $value )
			{

				$timer_data = $this->get_the_timers();
				if ( ! isset( $timer_data[ $value ] )
					|| ! is_numeric( $timer_data[ $value ] )
					|| 0 >= intval( $timer_data[ $value ] )
				) {
					return false;
				}

				$saved_timer = intval( $timer_data[ $value ] );

				return $saved_timer;

			}

			/**
			 * Update the Timer with current timestamp
			 * @param string $value
			 * @return bool
			**/
			protected function update_the_timer( $value )
			{

				$transient_name_timer = $this->get_prefixed_value( 'ntvwc_client_rest_result_timer' );
				$timer_data = get_transient( $transient_name_timer );
				if ( false === $timer_data ) {
					$timer_data = '{}';
				}
				$timer_data = json_decode( $timer_data, true );
				if ( null === $timer_data ) {
					$timer_data = array();
				}

				$timer_data[ $value ] = current_time( 'timestamp', true ) + DAY_IN_SECONDS;
				$timer_data_json = json_encode( $timer_data, JSON_UNESCAPED_UNICODE );
				$result = set_transient( $transient_name_timer, $timer_data_json, DAY_IN_SECONDS );

				return $result;

			}

			/**
			 * Check if needs to update saved result
			 * @param string $value
			 * @return bool
			**/
			protected function delete_the_timer( string $value = '' )
			{

				$transient_name_timer = $this->get_prefixed_value( 'ntvwc_client_rest_result_timer' );
				$timer_data = $this->get_the_timers();

				if ( is_string( $value ) && '' !== $value ) {

					unset( $timer_data[ $value ] );
					$timer_data_json = json_encode( $timer_data, JSON_UNESCAPED_UNICODE );
					$result = set_transient( $transient_name_timer, $timer_data_json, DAY_IN_SECONDS );

				} else {
					$result = delete_transient( $transient_name_timer );
				}

				return $result;

			}

			/**
			 * Check if needs to update saved result
			 * @uses $this->delete_the_timer
			 * @param string $value
			 * @return bool
			**/
			protected function is_timer_valid( $value )
			{

				$transient_name_timer = $this->get_prefixed_value( 'ntvwc_client_rest_result_timer' );
				$timestamp = $this->get_the_timer( $value );

				if ( ! is_numeric( $timestamp )
					|| 0 >= intval( $timestamp )
				) {
					return false;
				}

				$saved_timer = intval( $timestamp );

				if ( current_time( 'timestamp', true ) >= $saved_timer ) {
					$this->delete_the_timer( $value );
					return false;
				}

				return true;

			}

		/**
		 * Result
		**/
			/**
			 * Saved
			**/
				/**
				 * Check if needs to update saved result
				 * @param string $value
				 * @return bool
				**/
				protected function need_update_saved_result( $value )
				{

					$is_timer_valid = $this->is_timer_valid( $value );
					if ( ! $is_timer_valid ) {
						return true;
					}

					// Result data
					$transient_name_data = $this->get_prefixed_value( 'ntvwc_client_rest_result_data' );
					$saved_result = get_transient( $transient_name_data );
					if ( false === $saved_result ) {
						return true;
					}
					$saved_result = json_decode( $saved_result, true );
					if ( null === $saved_result ) {
						return true;
					}

					if ( isset( $saved_result[ $value ] ) ) {
						return false;
					}

					return true;

				}

				/**
				 * Get saved result data
				**/
				protected function get_saved_result_data( string $value, string $key = '' )
				{

					if ( $this->need_update_saved_result( $value ) ) {
						return false;
					}

					// Result data
					$transient_name_data = $this->get_prefixed_value( 'ntvwc_client_rest_result_data' );
					$saved_result = json_decode( get_transient( $transient_name_data ), true );

					// Timer
					if ( ! $this->is_timer_valid( $value ) ) {
						return false;
					}

					if ( ! isset( $saved_result[ $value ] )
						|| ! is_array( $saved_result[ $value ] )
						|| 0 >= count( $saved_result[ $value ] )
					) {
						return false;
					}

					$target_result = $saved_result[ $value ];
					if ( ! is_string( $key ) 
						|| '' === $key
					) {
						return $target_result;
					}

					if ( isset( $target_result[ $key ] ) 
						&& ! empty( $target_result[ $key ] )
					) {
						return $target_result[ $key ];
					}

					return false;

				}

				/**
				 * Update Token
				 * @uses $this->result_data
				 * @return bool
				**/
				protected function update_saved_result_data( string $value, $short = false )
				{

					if ( ! is_string( $value ) 
						|| '' === $value
					) {
						return false;
					}

					// Result data
					$transient_name_data = $this->get_prefixed_value( 'ntvwc_client_rest_result_data' );
					$saved_result = json_decode( get_transient( $transient_name_data ), true );
					if ( null === $saved_result ) {
						$saved_result = array();
					}

					if ( isset( $this->result_data )
						&& is_array( $this->result_data )
						&& 0 < count( $this->result_data )
						&& isset( $this->result_data[ $value ] )
						&& is_array( $this->result_data[ $value ] )
						&& 0 < count( $this->result_data[ $value ] )
					) {

						foreach ( $saved_result as $each_value => $each_result_data ) {
							$is_timer_valid = $this->is_timer_valid( $each_value );
							if ( ! $is_timer_valid ) {
								if ( isset( $saved_result[ $each_value ] ) ) {
									unset( $saved_result[ $each_value ] );
								}
							}
						}

						if ( $short ) {
							$time = 30 * MINUTE_IN_SECONDS;
						} else {
							$time = DAY_IN_SECONDS;
						}

						$saved_result[ $value ] = $this->get_current_result_data( $value );
						$saved_result_json = json_encode( $saved_result, JSON_UNESCAPED_UNICODE );
						$result = set_transient( $transient_name_data, $saved_result_json, $time );

						return $result;

					}

					return false;

				}

				/**
				 * Update Token
				 * @param string $value
				 * @return bool
				**/
				protected function delete_saved_result_data( string $value = '' )
				{

					$transient_name_data = $this->get_prefixed_value( 'ntvwc_client_rest_result_data' );

					if ( '' !== $value ) {
						$transient = get_transient( $transient_name_data );
						if ( isset( $transient[ $value ] ) ) {
							unset( $transient[ $value ] );
							$result = set_transient( $transient_name_data, $transient, DAY_IN_SECONDS );
							return $result;
						}

					} else {
						return delete_transient( $transient_name_data );
					}

				}

	/**
	 * Flags
	**/
		/**
		 * Check if the validate is already done
		 * @return bool
		**/
		protected function isset_result( $value )
		{
			if ( isset( $this->result[ $value ] ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Check if prev result is valid
		 * @return bool
		**/
		protected function is_result_valid( $value )
		{
			if ( $this->is_result_invalid() ) {
				return false;
			}
			return true;
		}

		/**
		 * Check if prev result is invalid
		 * @return bool
		**/
		protected function is_result_invalid( $value )
		{
			if ( $this->need_validate_again( $value ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Check if the validate is already done and still need validate for some reason
		 * @return bool
		**/
		protected function need_validate_again( $value )
		{
			if ( isset( $this->result[ $value ] )
				&& ! is_wp_error( $this->result[ $value ] ) 
				&& isset( $this->result[ $value ]['response']['code'] )
				&& is_numeric( $this->result[ $value ]['response']['code'] )
				&& ntvwc_is_numeric_between( intval( $this->result[ $value ]['response']['code'] ), 200, 399 )
				&& ! empty( $this->result[ $value ]['body'] )
			) {
				$data = json_decode( $this->result[ $value ]['body'], true );
				if ( null !== $data
					&& is_array( $data ) 
					&& isset( $data['token'] ) 
					&& is_string( $data['token'] ) 
					&& '' !== $data['token']
				) {
					return false;
				}
			}
			return true;
		}

	/**
	 * Tools
	**/
		/**
		 * Get dir of the plugin root
		 * 
		 * @uses $this->get_plugin_dir_name()
		 * 
		 * @param  [string] $dir
		 * 
		 * @return [string] 
		**/
		protected function get_plugin_dir_path_from( $dir )
		{

			// Check the required param
			if ( ! isset( $dir ) || ! is_string( $dir ) || '' === $dir ) {
				return '';
			}

			// Directory name
			$directory_name = basename( $dir );

			// Case : Parent is plugin
			if ( $directory_name === $this->get_plugin_dir_name() ) {
				return $dir;
			}
			
			// Parent
			$parent_dir_path = dirname( $dir );

			// Directory name
			$directory_name = basename( $parent_dir_path );

			// Case : Parent is plugin
			if ( $directory_name === $this->get_plugin_dir_name() ) {
				return $parent_dir_path;
			}
			
			// Plugin dir
			return $this->get_plugin_dir_path_from( $parent_dir_path );

		}

		/**
		 * File System Init
		 * 
		 * @param string $url
		 * @param string $nonce
		 * @param string $current_user_can : Default "manage_options"
		 * 
		 * @return bool
		**/
		public function init_wp_filesystem( $current_user_can = 'manage_options' )
		{

			// Init Filesystem
				if ( ! current_user_can( $current_user_can ) ) {
					return false;
				}

				$access_type = get_filesystem_method();
				if( $access_type === 'direct' )
				{

					$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array() );

					if ( ! WP_Filesystem( $creds ) ) {
						$this->is_wp_filesystem_on = false;
						return false;
					}

					$this->is_wp_filesystem_on = true;
					return true;

				}	
				else
				{

					$this->is_wp_filesystem_on = false;
					return false;

				}

			return true;

		}

	/**
	 * Manipulate Properties
	 * 
	 * Setter
	 *   $this->plugin_name          : $this->set_plugin_name( string $plugin_name )
	 *   $this->plugin_version       : $this->set_plugin_version( string $plugin_version )
	 *   $this->textdomain           : $this->set_textdomain( string $textdomain )
	 *   $this->unique_prefix        : $this->set_unique_prefix( string $unique_prefix )
	 *   $this->api_urls[$key]       : $this->set_api_url( $key, $api_url = '' )
	 *   $this->action_prefix        : $this->set_action_prefix()
	 *   $this->filter_prefix        : $this->set_filter_prefix()
	 * 
	 * Getter
	 *   $this->api_urls[ $key ]          : $this->get_api_url( $key )
	 *   $this->prefixed{$value}          : $this->get_prefixed_value( $name, $sep = '_' )
	 *   $this->prefixed{$action_hook}    : $this->get_prefixed_action_hook( $name )
	 *   $this->prefixed{$filter_hook}    : $this->get_prefixed_filter_hook( $name )
	 *   $this->plugin_dir_name           : $this->get_plugin_dir_name()
	**/
		/**
		 * Setter
		**/
			/**
			 * Get plugin name
			 * 
			 * @uses $this->plugin_name
			 * 
			 * @param [string] $plugin_name
			 * 
			 * @return [bool] 
			**/
			protected function set_plugin_name( $plugin_name )
			{

				// Check the property
				if ( isset( $this->plugin_name )
					&& is_string( $this->plugin_name ) 
					&& ! empty( $this->plugin_name )
				) {
					return false;
				}

				// Check the param
				if ( ! isset( $plugin_name )
					|| ! is_string( $plugin_name ) 
					|| empty( $plugin_name )
				) {
					return false;
				}

				// Set
				$this->plugin_name = $plugin_name;

				// End
				return true;

			}

			/**
			 * Get plugin version
			 * 
			 * @uses $this->plugin_version
			 * 
			 * @param [string] $plugin_version
			 * 
			 * @return [bool] 
			**/
			protected function set_plugin_version( $plugin_version )
			{

				// Check the property
				if ( isset( $this->plugin_version )
					&& is_string( $this->plugin_version ) 
					&& ! empty( $this->plugin_version )
				) {
					return false;
				}

				// Check the param
				if ( ! isset( $plugin_version )
					|| ! is_string( $plugin_version ) 
					|| empty( $plugin_version )
				) {
					return false;
				}

				// Set
				$this->plugin_version = $plugin_version;

				// End
				return true;

			}

			/**
			 * Set textdomain
			 * 
			 * @uses $this->textdomain
			 * 
			 * @param [string] $textdomain
			 * 
			 * @return [bool] 
			**/
			protected function set_textdomain( $textdomain )
			{

				// Check the property
				if ( isset( $this->textdomain )
					&& is_string( $this->textdomain ) 
					&& ! empty( $this->textdomain )
				) {
					return false;
				}

				// Check the param
				if ( ! isset( $textdomain )
					|| ! is_string( $textdomain ) 
					|| empty( $textdomain )
				) {
					return false;
				}

				// Set
				$this->textdomain = $textdomain;

				// End
				return true;

			}

			/**
			 * Set unique prefix
			 * 
			 * @uses $this->unique_prefix
			 * 
			 * @return [string] 
			**/
			public function set_unique_prefix( $unique_prefix )
			{

				return $this->unique_prefix;

				// Check the property
				if ( isset( $this->unique_prefix )
					&& is_string( $this->unique_prefix ) 
					&& ! empty( $this->unique_prefix )
				) {
					return false;
				}

				// Check the textdomain
				if ( null !== $this->get_textdomain()
					&& is_string( $this->get_textdomain() ) 
					&& ! empty( $this->get_textdomain() )
				) {
					return false;
				}

				// Check the param
				if ( ! isset( $unique_prefix )
					|| ! is_string( $unique_prefix ) 
					|| empty( $unique_prefix )
				) {
					return false;
				}

				// Set
				$this->unique_prefix = strtolower( preg_replace( '/[^a-zA-Z0-9]+/i', '_', $this->get_textdomain() ) );

				// End
				return true;


			}

			/**
			 * Set plugin dir path
			 * 
			 * @uses $this->plugin_file_path
			 * @uses $this->plugin_dir_path
			 * 
			 * @return [string] 
			**/
			protected function set_plugin_dir_path()
			{

				// Check
				if ( ! isset( $this->plugin_file_path )
					|| ! is_string( $this->plugin_file_path ) 
					|| empty( $this->plugin_file_path )
				) {
					return false;
				}

				// Set
				$this->plugin_dir_path = plugin_dir_path( $this->plugin_file_path );

				// End
				return true;

			}

			/**
			 * Set the content file path
			 *
			 * @param  [string] $key
			 * @param  [string] $api_url Default ""
			 * 
			 * @return [string]
			**/
			protected function set_api_url( $key, $api_url = '' )
			{

				// Set
				if ( ! isset( $this->api_urls[ $key ] ) 
					|| $this->api_urls[ $key ] !== $api_url
				) {
					$this->api_urls[ $key ] = esc_url( $api_url );
					return true;
				}

				return false;

			}

			/**
			 * Set the action prefix
			 * 
			 * @return [string]
			**/
			protected function set_action_prefix()
			{

				$this->action_prefix = 'ntvwc_client_' . $this->unique_prefix . '_action_';

			}

			/**
			 * Set the filter prefix
			 * 
			 * @return [string]
			**/
			protected function set_filter_prefix()
			{

				$this->filter_prefix = 'ntvwc_client_' . $this->unique_prefix . '_filter_';

			}

		/**
		 * Getter
		**/
			/**
			 * Get ntvwc client version
			 * 
			 * @uses $this->ntvwc_client_version
			 * 
			 * @return [string] 
			**/
			public function get_ntvwc_client_version()
			{

				return $this->ntvwc_client_version;

			}

			/**
			 * Get plugin name
			 * 
			 * @uses $this->plugin_name
			 * 
			 * @return [string] 
			**/
			public function get_plugin_name()
			{

				return $this->plugin_name;

			}

			/**
			 * Get plugin version
			 * 
			 * @uses $this->plugin_version
			 * 
			 * @return [string] 
			**/
			public function get_plugin_version()
			{

				return $this->plugin_version;

			}

			/**
			 * Get textdomain
			 * 
			 * @uses $this->textdomain
			 * 
			 * @return [string] 
			**/
			public function get_textdomain()
			{

				return $this->textdomain;

			}

			/**
			 * Get unique prefix
			 * 
			 * @uses $this->unique_prefix
			 * 
			 * @return [string] 
			**/
			public function get_unique_prefix()
			{

				return $this->unique_prefix;

			}

			/**
			 * Get plugin dir path
			 * 
			 * @uses $this->plugin_dir_path
			 * 
			 * @return [string] 
			**/
			public function get_plugin_dir_path()
			{

				return $this->plugin_dir_path;

			}

			/**
			 * Get API URL
			 * 
			 * @param  [string] $key
			 * 
			 * @return [string]
			**/
			public function get_api_url( $key )
			{

				return esc_url( isset( $this->api_urls[ $key ] ) ? $this->api_urls[ $key ] : '' );

			}

			/**
			 * Get prefixed name for input attribute
			 * 
			 * @param  [string] $name
			 * @param  [string] $sep  : Default "_"
			 * 
			 * @return [string]
			**/
			public function get_prefixed_value( $name, $sep = '_' )
			{

				return esc_attr( $this->unique_prefix . $sep . $name );

			}

			/**
			 * Get prefixed name for action hook
			 * 
			 * @param  [string] $name
			 * 
			 * @return [string]
			**/
			public function get_prefixed_action_hook( $name )
			{

				return esc_attr( $this->action_prefix . $name );

			}

			/**
			 * Get prefixed name for filter hook
			 * 
			 * @param  [string] $name
			 * 
			 * @return [string]
			**/
			public function get_prefixed_filter_hook( $name )
			{

				return esc_attr( $this->filter_prefix . $name );

			}

			/**
			 * Get plugin dir name
			 * 
			 * @return [string]
			**/
			public function get_plugin_dir_name()
			{

				return $this->plugin_dir_name;

			}

	/**
	 * Options
	 * "ntvwc_client_validation_token"
	 * "ntvwc_client_update_token"
	 * "flag_to_require_update"
	 * "is_the_latest_version"
	 * "is_token_expired"
	**/
		/**
		 * Setter
		**/
			/**
			 * Set ntvwc client token
			 * @uses option "{$this->unique_prefix}_ntvwc_client_validation_token"
			 * 
			 * @return [bool]
			**/
			protected function set_saved_validation_token()
			{

				/**
				 * Filtered by hook $this->get_prefixed_filter_hook( 'ntvwc_client_validation_token' )
				 * @param string Option prefixed "ntvwc_client_validation_token"
				 * @param string $this->unique_prefix
				**/
				$this->ntvwc_client_validation_token = apply_filters(
					$this->get_prefixed_filter_hook( 'ntvwc_client_validation_token' ),
					$this->get_saved_validation_token(),
					$this,
					'set'
				);

				// End
				return $this->ntvwc_client_validation_token;

			}

			/**
			 * Set ntvwc client token
			 * Flag to require update
			 * @uses option "{$this->unique_prefix}_ntvwc_client_update_token"
			 * @return [bool]
			**/
			protected function set_saved_update_token()
			{

				/**
				 * Filtered by hook $this->get_prefixed_filter_hook( 'ntvwc_client_update_token' )
				 * @param string Option prefixed "ntvwc_client_update_token"
				 * @param string $this->unique_prefix
				**/
				$this->ntvwc_client_update_token = apply_filters(
					$this->get_prefixed_filter_hook( 'ntvwc_client_update_token' ),
					$this->get_saved_update_token(),
					$this,
					'set'
				);

				// End
				return $this->ntvwc_client_update_token;

			}

			/**
			 * Set is the latest version
			 * 
			 * @uses option "{$this->unique_prefix}_is_the_latest_version"
			 * 
			 * @return [array]
			**/
			protected function set_is_the_latest_version()
			{

				return apply_filters(
					$this->get_prefixed_filter_hook( 'is_the_latest_version' ),
					$this->get_is_the_latest_version(),
					$this,
					'set'
				);

			}

			/**
			 * Get bool if this require update the json
			 * Flag to require update
			 * 
			 * @uses option "{$this->unique_prefix}_flag_to_require_update"
			 * 
			 * @return [bool]
			**/
			protected function set_is_token_expired()
			{

				return apply_filters(
					$this->get_prefixed_filter_hook( 'is_token_expired' ),
					$this->get_is_token_expired(),
					$this,
					'set'
				);

			}

		/**
		 * Getter
		**/
			/**
			 * Get ntvwc client validation token
			 * @uses option "{$this->unique_prefix}_ntvwc_client_validation_token"
			 * @return [bool]
			**/
			public function get_saved_validation_token()
			{

				return apply_filters(
					$this->get_prefixed_filter_hook( 'ntvwc_client_validation_token' ),
					get_option( $this->get_prefixed_value( 'ntvwc_client_validation_token' ), '' ),
					$this,
					'get'
				);

			}

			/**
			 * Get ntvwc client update token
			 * Flag to require update
			 * @uses option "{$this->unique_prefix}_ntvwc_client_update_token"
			 * @return [string]
			**/
			public function get_saved_update_token()
			{

				return apply_filters(
					$this->get_prefixed_filter_hook( 'ntvwc_client_update_token' ),
					get_option( $this->get_prefixed_value( 'ntvwc_client_update_token' ), '' ),
					$this,
					'get'
				);

			}

			/**
			 * Get is the latest version
			 * 
			 * @uses option "{$this->unique_prefix}_is_the_latest_version"
			 * 
			 * @return [array]
			**/
			public function get_is_the_latest_version()
			{

				return apply_filters(
					$this->get_prefixed_filter_hook( 'is_the_latest_version' ),
					json_decode( get_option( $this->get_prefixed_value( 'is_the_latest_version' ), false ), true ),
					$this,
					'get'
				);

			}

			/**
			 * Get the checked content version
			 * 
			 * @uses option "{$this->unique_prefix}_checked_content_version"
			 * 
			 * @return [array]
			**/
			public function get_the_checked_content_version()
			{

				// Update the checked version
				return apply_filters(
					$this->get_prefixed_filter_hook( 'checked_content_version' ),
					get_option( $this->get_prefixed_value( 'checked_content_version' ), '0' ),
					$this,
					'get'
				);

			}

			/**
			 * Get bool if this require update the json
			 * Flag to require update
			 * 
			 * @uses option "{$this->unique_prefix}_flag_to_require_update"
			 * 
			 * @return [bool]
			**/
			public function get_is_token_expired()
			{

				return apply_filters(
					$this->get_prefixed_filter_hook( 'is_token_expired' ),
					get_option( $this->get_prefixed_value( 'is_token_expired' ), false ),
					$this,
					'get'
				);

			}

		/**
		 * Update options
		**/
			/**
			 * Update ntvwc client validation token
			 * @uses option "{$this->unique_prefix}_ntvwc_client_validation_token"
			 * @return [bool]
			**/
			public function update_ntvwc_client_validation_token( $value )
			{

				// Vars
				$value = $this->sanitize_token( $value );

				// Case invalid data
				if ( ! is_string( $value ) || '' === $value ) {
					delete_option(
						$this->get_prefixed_value( 'ntvwc_client_validation_token' )
					);
					return false;
				}

				// Update
				return apply_filters(
					$this->get_prefixed_filter_hook( 'update_ntvwc_client_validation_token' ),
					update_option( $this->get_prefixed_value( 'ntvwc_client_validation_token' ), $value ),
					$this
				);

			}

				/**
				 * Reset cron
				**/
				public function reset_cron_for_update_token( $update_result )
				{

					if ( $this->get_prefixed_filter_hook( 'update_ntvwc_client_validation_token' ) !== current_filter() ) {
						return $update_result;
					}

					if ( $update_result ) {
						$this->result = array();
						$this->result_data = array();
						$this->reset_cron();
						$this->delete_saved_result_data();
						$this->delete_the_timer();
					}

					return $update_result;

				}

			/**
			 * Update ntvwc client update token
			 * Flag to require update
			 * 
			 * @uses option "{$this->unique_prefix}_ntvwc_client_update_token"
			 * 
			 * @return [bool]
			**/
			public function update_ntvwc_client_update_token( $value )
			{

				// Vars
				$value = $this->sanitize_update_token( $value );

				// Case invalid data
				if ( ! is_string( $value ) || '' === $value ) {
					delete_option(
						$this->get_prefixed_value( 'ntvwc_client_update_token' )
					);
					return false;
				}

				// Update
				return apply_filters(
					$this->get_prefixed_filter_hook( 'update_ntvwc_client_update_token' ),
					update_option( $this->get_prefixed_value( 'ntvwc_client_update_token' ), $value ),
					$this
				);

			}

			/**
			 * Update the option '_is_the_latest_version'
			 *
			 * @param  [bool] $value
			 * 
			 * @return [bool] True if is the latest version
			 */
			public function update_is_the_latest_version( $value )
			{

				// Sanitize
				$value = $this->sanitize_is_the_latest_version( $value );

				if ( is_bool( $value ) ) {
					// Update
					return apply_filters(
						$this->get_prefixed_filter_hook( 'update_is_the_latest_version' ),
						update_option( $this->get_prefixed_value( 'is_the_latest_version' ), $value ),
						$this
					);

				}

				// Fail
				return false;

			}

			/**
			 * Update the option 'is_token_expired'
			 *
			 * @param  [bool] $value
			 * 
			 * @return [bool] True if is the latest version
			 */
			public function update_is_token_expired( $value )
			{

				if ( ! isset( $value ) || ! is_bool( $value ) ) {
					delete_option( $this->get_prefixed_value( 'is_token_expired' ) );
					return false;
				}

				$result = update_option( $this->get_prefixed_value( 'is_token_expired' ), boolval( $value ) );
				return $result;

				// Sanitize
				$value = $this->sanitize_is_token_expired( $value );

				if ( is_bool( $value ) ) {
					// Update
					return apply_filters(
						$this->get_prefixed_filter_hook( 'update_is_token_expired' ),
						update_option( $this->get_prefixed_value( 'is_token_expired' ), $value ),
						$this
					);

				}

				// Fail
				return false;

			}

		/**
		 * Sanitize
		**/
			/**
			 * Sanitize the flag if the version is the latest
			 * 
			 * @uses option "{$this->unique_prefix}_flag_to_require_update"
			 * 
			 * @param  [bool] $value description
			 * 
			 * @return [bool]
			**/
			public function sanitize_is_the_latest_version( $value )
			{

				if ( is_bool( $value ) ) {
					return apply_filters(
						$this->get_prefixed_filter_hook( 'is_the_latest_version' ),
						boolval( $value ),
						$this
					);
				}

				return null;

			}

			/**
			 * Sanitize the flag if the token is expired
			 * 
			 * @uses option "{$this->unique_prefix}_flag_to_require_update"
			 * 
			 * @param  [bool] $value description
			 * 
			 * @return [bool]
			**/
			public function sanitize_is_token_expired( $value )
			{

				if ( is_bool( $value ) ) {
					return apply_filters(
						$this->get_prefixed_filter_hook( 'is_token_expired' ),
						boolval( $value ),
						$this
					);
				}

				return null;

			}

	/**
	 * Notices
	**/
		/**
		 * Called in hook "all_admin_notices"
		 * 
		 * @uses $this->notice_messages
		**/
		public function all_admin_notices()
		{

			$notice_messages = $this->get_notice_messages();

			if ( 0 < count( $notice_messages ) ) {
				foreach( $notice_messages as $notice_message ) {
					echo $this->wrap_as_notices( $notice_message['text'], $notice_message['type'] );
				}
			}

		}

		/**
		 * Wrap the text in notice format
		 * 
		 * @param string $notice_message : Message to be wrapped
		 * @param string $type           : 'notice', 'warning', 'updated'
		 * 
		 * @see ntvwc_is_string_and_not_empty( $string )
		 * 
		 * @return string
		**/
		protected function wrap_as_notices( $notice_message = '', $type = 'notice' )
		{

			// Check the param
			if ( ! is_string( $notice_message ) ) {
				ob_start();
				var_dump( $notice_message );
				$notice_message = ob_get_clean();
				ob_start();
				echo '<pre>';
				echo esc_html( $notice_message );
				echo '</pre>';
				$notice_message = ob_get_clean();
			}

			// Init Message
			$format = '<div class="notice %s wc-stripe-apple-pay-notice is-dismissible"><p>%s</p></div>' . PHP_EOL;
			$notice_type = ( in_array( $type, array( 'warning' ) )
				? 'notice-' . $type
				: $type
			);
			$notice = sprintf( $format, $notice_type, $notice_message );

			// End
			return $notice;

		}

		/**
		 * Get
		 * 
		 * @return [array] description
		**/
		public function get_notice_messages()
		{
			return apply_filters( $this->get_prefixed_filter_hook( 'notice_messages' ), $this->notice_messages );
		}

		/**
		 * Add
		 * 
		 * @param [string] $message
		 * @param [string] $type
		 * 
		 * @return [bool]
		**/
		public function add_notice_message( $text, $type = 'notice' )
		{

			if ( ! is_string( $text ) || '' === $text ) {
				ob_start();
				echo '<pre>';
				var_dump( $text );
				echo '</pre>';
				$text = ob_get_clean();
			}

			if ( ! is_string( $type ) || ! in_array( $type, apply_filters(
				$this->get_prefixed_filter_hook( 'notice_types' ),
				array( 'succeed', 'notice', 'warning', 'error' )
			) ) ) {
				return false;
			}

			if ( did_action( 'all_admin_notices' ) ) {
				echo $this->wrap_as_notices( $text, $type );
				return true;
			}

			if ( 0 < count( $this->notice_messages ) ) {
				foreach ( $this->notice_messages as $notice_message ) {
					if ( $text === $notice_message['text'] ) {
						return false;
					}
				}
			}

			array_push( $this->notice_messages, array(
				'type' => $type,
				'text' => $text
			) );
			return true;

		}

		/**
		 * Set messages
		 * 
		 * @param [array] $messages
		 * 
		 * @return [array]
		**/
		public function set_notice_messages( $messages = array() )
		{

			if ( 0 < count( $messages ) ) {
			foreach ( $messages as $message ) {
				$this->add_notice_message( $message['text'], $message['type'] );
			}
			}

			return apply_filters( $this->get_prefixed_filter_hook( 'notice_messages' ), $this->notice_messages );

		}

	/**
	 * Cron
	**/
		/**
		 * Reset Schedule
		**/
		public function reset_cron( $args = array() )
		{

			// Reset Schedules
				// Deactivate
				$this->delete_cron();

				// Set
				if ( ! wp_next_scheduled( $this->get_prefixed_action_hook( 'cron_validation' ) ) ) {
					wp_schedule_event( time(), 'daily_cron_validation', $this->get_prefixed_action_hook( 'cron_validation' ), $args );
				}
		}

		/**
		 * Reset Schedule
		**/
		public function delete_cron()
		{

			// Reset Schedules
				// Deactivate
				$schedule_name = $this->get_prefixed_action_hook( 'cron_validation' );
				wp_unschedule_event( current_time( 'timestamp', true ), $schedule_name );
				wp_clear_scheduled_hook( $schedule_name );

		}

		/**
		 * Intervals
		 * @param array $schedules
		 * @return array
		**/
		function add_cron_interval( $schedules )
		{

			$schedules['daily_cron_validation'] = array(
				'interval' => DAY_IN_SECONDS,
				'display'  => esc_html__( 'Once a day', $this->get_textdomain() )
			);

			return $schedules;

		}

		/**
		 * Exec Cron
		 *  Hooked in $this->get_prefixed_action_hook( 'cron_validation' );
		**/
		public function cron_validate_token()
		{

		}


}
}

