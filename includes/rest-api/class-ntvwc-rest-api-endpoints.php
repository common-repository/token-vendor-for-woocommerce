<?php
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;


if ( ! class_exists( 'NTVWC_REST_API_Endpoints' ) ) {
/**
 * Auth in Public
**/
class NTVWC_REST_API_Endpoints {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var string The current version of this plugin.
	 */
	protected $version;

	/**
	 * The namespace to add to the api calls.
	 *
	 * @var string The namespace to add to the api call
	 */
	protected $namespace;

	/**
	 * The auth type.
	**/
	protected $type = 'basic';

	/**
	 * Store errors to display if the JWT is wrong
	 *
	 * @var WP_Error
	 */
	protected $jwt_error = null;

	/**
	 * Methods
	 * 
	 * @var array
	**/
	protected $api_methods = array();

	/**
	 * Methods
	 * 
	 * @var array
	**/
	protected $header_formats_error = array(
		400 => '%1$s 400 Bad Request',
	);

	/**
	 * Constructor
	 * 
	 * @param string $plugin_name
	 * @param string $version
	 * @param string $type        : 
	**/
	function __construct( $plugin_name, $version )
	{

		// Properties
			// Params
			$this->plugin_name = $plugin_name;
			$this->version     = $version;

			// namespace
			$this->namespace = $this->plugin_name . '/v' . intval( $this->version );

		// Init
		$this->init_hooks();

	}

	/**
	 * Init WP Hooks
	**/
	function init_hooks()
	{

		// Actions
		//add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		//add_action( 'rest_api_init', array( $this, 'add_cors_support' ) );

	}

	/**
	 * Register REST routes
	 * 
	 * @param WP_REST_Server $wp_rest_server
	**/
	public function register_rest_routes( $wp_rest_server = '' )
	{

	}

	/**
	 * Add CORs suppot to the request.
	 * Required define const "NTVWC_CORS_ENABLE_JWT_AUTH" to be true
	**/
	public function add_cors_support()
	{

		// Enable CORs
		$enable_cors = true;//defined( 'NTVWC_CORS_ENABLE_JWT_AUTH' ) ? NTVWC_CORS_ENABLE_JWT_AUTH : false;
		if ( $enable_cors ) {
			$headers = apply_filters( 
				'ntvwc_filter_cors_allow_headers',
				'Access-Control-Allow-Headers, Content-Type, Authorization'
			);
			header( sprintf( 'Access-Control-Allow-Headers: %s', $headers ) );
		}

	}

	/**
	 * Headers
	 * @param int $key
	**/
	protected function send_header( int $code = 200, $text = '' )
	{

		if ( ! is_numeric( $code ) ) {
			header( sprintf( $this->header_formats_error[ 400 ], $_SERVER["SERVER_PROTOCOL"] ) );
		}

		$code = intval( $code );

		if ( is_string( $text ) && '' !== $text ) {
			header( sprintf( '%1$s %2$s', $_SERVER["SERVER_PROTOCOL"], $text ) );
			return;
		}

		header( sprintf( $this->header_formats_error[ 400 ], $_SERVER["SERVER_PROTOCOL"] ) );
		return;

	}

}
}

