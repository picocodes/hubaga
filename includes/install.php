<?php
/**
 * Installation related functions and actions.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * H_Install Class.
 */
class H_Install {

	/**
	 * Install Hubaga.
	 */
	public function __construct() {
		global $wpdb;

		if ( ! is_blog_installed() || is_array(get_transient( 'hubaga_core_pages' )) ) {
			return;
		}

		$this->create_pages();
		$this->create_options();
		$this->create_roles();

	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public function create_pages() {

		$pages = apply_filters( 'hubaga_create_pages', array(
			'checkout' => array(
				'name'    => _x( 'checkout', 'Page slug', 'hubaga' ),
				'title'   => _x( 'Checkout', 'Page title', 'hubaga' ),
				'content' => '[h-checkout]',
			),
			'account' => array(
				'name'    => _x( 'account', 'Page slug', 'hubaga' ),
				'title'   => _x( 'Account Details', 'Page title', 'hubaga' ),
				'content' => '[h-account]',
			),
		) );
		$return = array();

		foreach ( $pages as $key => $page ) {
			$page_id = wp_insert_post( array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => $page['name'],
				'post_title'     => $page['title'],
				'post_content'   => $page['content'],
				'comment_status' => 'closed',
			) );
			$return[$key] = $page_id;
		}

		set_transient( 'hubaga_core_pages', $return );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		$pages    = get_transient( 'hubaga_core_pages' );
		$to_save  = array();

		// Include settings so that we can run through defaults
		$settings = require plugin_dir_path( __FILE__ ) . 'admin/settings.php';
		foreach( $settings as $id=>$args ){
			if(isset($args['default'])){
				$to_save[$id] = $args['default'];
			}
		}

		//Paypal settings
		$paypal  = require plugin_dir_path( __FILE__ ) . 'checkout/gateways/paypal/settings-paypal.php';
		foreach( $paypal as $id=>$args ){
			if(isset($args['default'])){
				$to_save[$id] = $args['default'];
			}
		}
		$to_save['is_gateway_paypal_active'] = 1;

		//Set shop pages
		foreach (get_transient( 'hubaga_core_pages' ) as $page => $id) {
			$to_save["{$page}_page_id"] = $id;
		}
		add_option( 'hubaga', $to_save );
	}


	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		// Customer role
		add_role( 'customer', 'Customer',  array( 'read' => true ) );
	}

}
