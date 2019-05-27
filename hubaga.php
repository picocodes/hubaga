<?php
/**
 * Plugin Name: Hubaga
 * Plugin URI: https://hubaga.com/
 * Description: Use this light-weight plugin to sell your software products.
 * Version: 1.0.4
 * Author: Hubaga
 * Author URI: https://hubaga.com
 * Requires at least: 4.4
 * Tested up to: 4.9
 *
 * Text Domain: hubaga
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Hubaga' ) ) :

/**
 * Main Hubaga Class.
 *
 */
final class Hubaga {

	/**
	 * Hubaga uses many variables, several of which can be filtered to
	 * customize the way it operates. Most of these variables are stored in a
	 * private array that gets updated with the help of PHP magic methods.
	 *
	 * This is a precautionary measure, to avoid potential errors produced by
	 * unanticipated direct manipulation of Hubaga's run-time data.
	 *
	 * @see Hubaga::setup_globals()
	 * @var array
	 * @since 1.0.0
	 */
	private $data;

	/**
	 * The single instance of the class.
	 *
	 * @var Hubaga
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Hubaga Instance.
	 *
	 * Ensures only one instance of Hubaga is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see hubaga()
	 * @return Hubaga - Main instance.
	 */
	public static function instance() {

		//Maybe initialise the instance
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Magic method for checking the existence of a certain custom field
	 *
	 * @since Hubaga 1.0.0
	 */
	public function __isset( $key ) {
		return isset( $this->data[$key] );
	}

	/**
	 * Magic method for getting Hubaga variables
	 *
	 * @since Hubaga 1.0.0
	 */
	public function __get( $key ) {
		return isset( $this->data[$key] ) ? $this->data[$key] : null;
	}

	/**
	 * Magic method for setting Hubaga variables
	 *
	 * @since Hubaga 1.0.0
	 */
	public function __set( $key, $value ) {
		$this->data[$key] = $value;
	}

	/**
	 * Magic method for unsetting Hubaga variables
	 *
	 * @since Hubaga 1.0.0
	 */
	public function __unset( $key ) {
		if ( isset( $this->data[$key] ) ) unset( $this->data[$key] );
	}

	/**
	 * Hubaga Constructor.
	 *
	 * Sets up the environment necessary for Hubaga to run.
	 *
	 * @since 1.0.0
	 * @return Hubaga - Main instance.
	 */
	private function __construct() {

		//Init the plugin after all plugins have loaded
		add_action( 'init', array( $this, 'init' ), 5 );

		/**
		 * Fires after Hubaga is loaded
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'hubaga_loaded' );

	}


	/**
	 * Initializes Hubaga
	 *
	 * This happens after WordPress has initialized to prevent several known errors
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function init(){

		/**
		 * Fires before Hubaga initializes
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'before_hubaga_init' );

		//Setup Hubaga globals
		$this->setup_globals();

		//Load core files
		$this->includes();

		//Initiate the admin class
		$this->admin = new H_Admin();

		//Load optional files
		$this->include_optional();

		//Maybe upgrade the db
		if( get_option( 'hubaga_db_version', '0' ) != $this->db_version ) {
			$this->upgrade_db();
		}

		//Initiate the download handler
		new H_Download();

		//The template manager
		$this->template = new H_Template();

		//Notification handler
		$this->notifications = new H_Notifications();

		//Initiate the shortcodes class
		$this->shortcodes = new H_Shortcodes();

		//Register post types after settings are loaded
		$this->register_post_types();
		$this->register_post_statuses();
		$this->load_plugin_textdomain();

		//Enque necessary styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ));

		//Register our product widget
		add_action( 'widgets_init', array( $this, 'register_widgets' ));

		/**
		 * Fires after Hubaga initializes
		 *
		 * Register your gateways during this hook, with a priority of less than 100
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'hubaga_init' );
	}

	/**
	 * Setup Hubaga Globals
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function setup_globals() {

		// Versions
		$this->version    = '1.0.4';
		$this->db_version = '100';

		// Post type identifiers
		$this->product_post_type = apply_filters( 'hubaga_product_post_type','h_product');
		$this->order_post_type   = apply_filters( 'hubaga_order_post_type',  'h_order'  );
		$this->coupon_post_type  = apply_filters( 'hubaga_coupon_post_type', 'h_coupon' );


		// Paths
		$this->file        		= __FILE__;
		$this->basename    		= apply_filters( 'hubaga_plugin_basenname', plugin_basename( $this->file ) );
		$this->plugin_path 		= apply_filters( 'hubaga_plugin_dir_path',  plugin_dir_path( $this->file ) );
		$this->plugin_url		= apply_filters( 'hubaga_plugin_dir_url',   plugin_dir_url ( $this->file ) );
		$this->includes_path 	= apply_filters( 'hubaga_includes_dir', trailingslashit( $this->plugin_path . 'includes'  ) );
		$this->includes_url 	= apply_filters( 'hubaga_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );
		$this->admin_menu_url	= apply_filters( 'hubaga_admin_menu_url', add_query_arg( 'post_type', $this->product_post_type, admin_url( 'edit.php' )) );

		// Misc
		$this->notices   		= new WP_Error(); //@see hubaga_add_notice
		$this->user_agent		= sprintf( 'Hubaga/%s Hubaga.com (WordPress/%s)', $this->version, $GLOBALS['wp_version'] );

	}

	/**
	 * Loads core Hubaga files and plugins
	 *
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 */
	protected function includes() {

		//Core functions
        require_once $this->includes_path . 'functions.php';

		//Product functions
		require_once $this->includes_path . 'products.php';

		//Order functions
		require_once $this->includes_path . 'orders.php';

		//Checkout functions
		require_once $this->includes_path . 'checkout.php';

		//Default gateways
		require_once $this->includes_path . 'gateways/test.php';

		//Customer functions
		require_once $this->includes_path . 'customers.php';

		//Notifications
		require_once $this->includes_path . 'notifications.php';

		//Shortcodes
		require_once $this->includes_path . 'shortcodes.php';

		//Ajax
		require_once $this->includes_path . 'ajax.php';

		//Elementa
		require_once $this->includes_path . 'elementa/elementa.php';

		//Administration
		require_once $this->includes_path . 'admin/admin.php';

		//Templates
		require_once $this->includes_path . 'template.php';

		//Downloads
		require_once $this->includes_path . 'download.php';

		//Product widget
		require_once $this->includes_path . 'product-widget.php';

	}

	/**
	 * Loads core Hubaga files and plugins
	 *
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 */
	protected function include_optional() {

		//Coupons are optional
		if( hubaga_get_option( 'enable_coupons' ) ) {
			require_once $this->includes_path . 'coupons.php';			
		}
	}

	/**
	 * Load Localisation files.
	 *
	 */
	public function register_post_types() {

		if ( ! is_blog_installed() || post_type_exists(  hubaga_get_product_post_type() ) ) {
			return;
		}

		/**
		 * Fires before custom post types are registered
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'hubaga_register_post_type' );

		//Products
		register_post_type( hubaga_get_product_post_type()	, hubaga_get_product_post_type_details() );

		//Coupons
		if( hubaga_get_option( 'enable_coupons' ) ) {
			register_post_type( hubaga_get_coupon_post_type()	, hubaga_get_coupon_post_type_details() );
		}

		//Orders
		register_post_type( hubaga_get_order_post_type()	, hubaga_get_order_post_type_details() );

		/**
		 * Fires after custom post types are registered
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'hubaga_after_register_post_type' );

	}

	/**
	 * Register the post statuses used by Hubaga
	 *
	 *
	 * @since Hubaga 1.0.0
	 * @uses register_post_status() To register post statuses
	 */
	public function register_post_statuses() {

		$order_statuses = hubaga_get_order_statuses();
		foreach ( $order_statuses as $order_status => $values ) {
			register_post_status( $order_status, $values );
		}

	}

	/**
	 * Register our custom widgets
	 *
	 *
	 * @since Hubaga 1.0.0
	 * @uses register_widget() To register widgets
	 */
	public function register_widgets() {
		register_widget( 'H_Product_Widget' );
	}

	/**
	 * Load Localisation files.
	 *
	 */
	public function load_plugin_textdomain() {
		 load_plugin_textdomain(
			'hubaga',
			false,
			$this->plugin_path . 'languages/'
		);
	}

	/**
	 * Loads necessary scripts
	 *
	 */
	public function load_scripts() {

		$nonce  = wp_create_nonce( 'hubaga_nonce' );
		$params = array(
			'ajaxurl' 				=> admin_url( 'admin-ajax.php' ),
			'nonce'					=> $nonce,
			'empty_coupon'			=> __( 'Please provide a coupon code first.', 'hubaga' ),
			'coupon_error'  		=> __( 'Unable to apply this coupon.', 'hubaga' ),
			'checkout_url'			=> hubaga_get_checkout_url(),
			'account_url'			=> hubaga_get_account_url(),
		);

		//Main Hubaga script
		$modified = filemtime($this->plugin_path .  'assets/js/hubaga.js');
		wp_register_script( 'hubaga_js', $this->plugin_url .  'assets/js/hubaga.js', array( 'jquery' ), $modified, true );
		wp_localize_script( 'hubaga_js', 'hubaga_params', $params );
		wp_enqueue_script( 'hubaga_js' );

		//Frontend css styles
		$modified = filemtime($this->plugin_path .  'assets/css/hubaga.css');
		wp_enqueue_style( 'hubaga_css', $this->plugin_url .  'assets/css/hubaga.css', array(), $modified );

	}

	/**
	 * Runs installation
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function upgrade_db() {
		require plugin_dir_path( __FILE__ ) . 'includes/install.php';
		$current = get_option( 'hubaga_db_version', '0' );
		new H_Install( $current );
		update_option( 'hubaga_db_version', $this->db_version );
	}

	/**
	 * Provides a link to Elementa, the UI Framework
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function Elementa( $id = 'hubaga' ) {
		return Elementa::instance( $id );
	}

	/**
	 * Retrieve a user set option
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key setting key to retrieve
	 * @param string $id  Elementa id to use
	 * @return the filtered user set option
	 */
	public function get_option( $key, $id = 'hubaga' ) {
		$value = $this->Elementa( $id )->get_option( $key );

		/**
		 * Filters a user set variable
		 * @param $value Mixed. The value assigned by a user to the option
		 * @param $key String. The option key being requested
		 * @param $id String. The Elementa instance id used to retrieve the option
		 * @since 1.0.0
		 *
		*/
		return apply_filters( "{$id}_get_{$key}" , $value, $key, $id );
	}

}

endif;

//And off we GO!!!!!!!!
Hubaga::instance();
