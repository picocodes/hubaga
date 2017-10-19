<?php
/**
 * Handles wp json api request.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'PC_Rest' ) ) :

/**
 * Main Class.
 *
 */
class PC_Rest {
	
	const PUBLIC_KEY_LENGTH 	= 12;
	const SECRET_KEY_LENGTH 	= 24;
	
	/**
	 * Authentication type
	 *
	 * (e.g. oauth1, oauth2, basic, etc)
	 * @var string
	 */
	protected $type = 'basic';
	
	/**
	 * Errors that occurred during authentication
	 * @var WP_Error|null|boolean True if succeeded, WP_Error if errored, null if not Basic Auth
	 */
	protected $auth_status = null;
	
	/**
	 * Should we attempt to run?
	 *
	 * Stops infinite recursion in certain circumstances.
	 * @var boolean
	 */
	protected $should_attempt = true;
	
	/**
	 * Hubaga Constructor.
	 *
	 * Sets up the environment necessary for Hubaga to run.
	 *
	 * @since 1.0.0
	 * @return Hubaga - Main instance.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'maybe_handle_aunthentications' ), 2 );
	}
	
	/**
	 * Conditionally handles authentications
	 *
	 * @return bool
	 */
	public function maybe_handle_aunthentications() {
		
		//Only move on if this is WP > 4.4 and the request is for our api endpoint
		$can_handle = apply_filters( 'hubaga_can_handle_rest_basic_auth', $this->can_handle_basic_oauth() );
		if(! $can_handle ) {
			return;
		}
		
		// Filters
		add_filter( 'rest_authentication_errors', array( $this, 'authentication_errors' ) );
		add_filter( 'determine_current_user', array( $this, 'authenticate' ), 20 );
		
		//Actions
		add_action( 'init',  array( $this, 'force_reauthentication' ), 1000 );
		
	}
	
	/**
	 * Check if we can handle basic oauth
	 *
	 * @return bool
	 */
	protected function can_handle_basic_oauth() {
		
		if ( empty( $_SERVER['REQUEST_URI'] ) 
			|| !function_exists( 'rest_get_url_prefix' )
			|| !$this->get_authorization_header()
		) {
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() ) . 'hubaga';

		// Check if our endpoint.
		return ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );

	}
	
	/**
	 * Parse the Authorization header into parameters
	 *
	 * @param string $header Authorization header value (not including "Authorization: " prefix)
	 * @return array|boolean Map of parameter values, false if not a Basic header
	 */
	public function parse_header( $header ) {
		if ( substr( $header, 0, 6 ) !== 'Basic ' ) {
			return false;
		}

		$params = array();
		if ( preg_match_all( '/(pk_[a-z_-]*)=(:?"([^"]*)"|([^,]*))/', $header, $matches ) ) {
			foreach ($matches[1] as $i => $h) {
				$params[$h] = urldecode( empty($matches[3][$i]) ? $matches[4][$i] : $matches[3][$i] );
			}
			if (isset($params['realm'])) {
				unset($params['realm']);
			}
		}
		return $params;

	}
	
	/**
	 * Get the authorization header
	 *
	 * On certain systems and configurations, the Authorization header will be
	 * stripped out by the server or PHP. Typically this is then used to
	 * generate `PHP_AUTH_USER`/`PHP_AUTH_PASS` but not passed on. We use
	 * `getallheaders` here to try and grab it out instead.
	 *
	 * @return string|null Authorization header if set, null otherwise
	 */
	public function get_authorization_header() {
		if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			return wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] );
		}

		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();

			// Check for the authoization header case-insensitively
			foreach ( $headers as $key => $value ) {
				if ( strtolower( $key ) === 'authorization' ) {
					return $value;
				}
			}
		}

		return null;
	}
	
	/**
	 * Aunthenticates a user
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function authenticate( $user ) {
		if ( ! empty( $user ) || ! $this->should_attempt ) {
			return $user;
		}

		$public_key    		= '';
		$secret_key 		= '';

		// If the $_GET parameters are present, use those first.
		if ( ! empty( $_GET['public_key'] ) && ! empty( $_GET['secret_key'] ) ) {
			$public_key    	= $_GET['consumer_key'];
			$secret_key 	= $_GET['consumer_secret'];
		}

		// If the above is not present, we will do full basic auth.
		if ( ! $public_key && ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
			$public_key    = $_SERVER['PHP_AUTH_USER'];
			$secret_key = $_SERVER['PHP_AUTH_PW'];
		}
		
		// Maybe extract this info from the headers
		if ( ! $public_key ) {
			$keys = $this->parse_header();
			if( is_array ( $keys ) ) {
				$public_key    = $keys['public'];
				$secret_key = $keys['secret'];
			}
		}

		// Stop if don't have any key.
		if ( ! $public_key || ! $secret_key ) {
			return false;
		}

		// Get user data.
		$user = $this->get_user_data_by_consumer_key( $consumer_key );
		if ( empty( $user ) ) {
			return false;
		}

		// Validate user secret.
		if ( ! hash_equals( $user->consumer_secret, $consumer_secret ) ) {
			$wc_rest_authentication_error = new WP_Error( 'woocommerce_rest_authentication_error', __( 'Consumer secret is invalid.', 'woocommerce' ), array( 'status' => 401 ) );

			return false;
		}

		// Check API Key permissions.
		if ( ! $this->check_permissions( $user->permissions ) ) {
			return false;
		}

		// Update last access.
		$this->update_last_access( $user->key_id );

		return $user->user_id;
	}
	
	/**
	 * Checks for authentication errors
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function authentication_errors( $value ) {
		if ( $value !== null ) {
			return $value;
		}

		return $this->auth_status;
	}
	
	/**
	 * Forces reathentication
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	 
	public function force_reauthentication( ) {
		if ( is_user_logged_in() ) {
			return;
		}

		// Force reauthentication
		global $current_user;
		$current_user = null;

		wp_get_current_user();
	}

}

endif;

Hubaga::instance();
