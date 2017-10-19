<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Payments Class
 *
 * Extended by individual payment processors to create new order processing capabilities.
 *
 * Please; note that this is not intented to be used by gateway processors
 *
 * @version  1.0.0
 */
abstract class H_Abstract_Payments {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Processor Instance.
	 *
	 * Ensures only one instance of the processor is loaded or can be loaded.
	 * Here as a guide since PHP does not allow static methods to be abstract
	 * Make sure to implement it in your processor
	 *
	 *
	 * @since 1.0.0
	 * @static
	 * @return object.
	 */
	//abstract public static function instance();

	/**
	 * Processes the checkout the redirects the user to the appropriate page.
	 */
	protected function __construct( ) {
		/**
		 * Gateways should use the hubaga_init with a priority of less than 100
		 * hook to register themselves
		 */
		add_action( 'hubaga_init',  array( $this, 'init'), 100 );

	}

	public function init( ) {
		$this->init_settings();

		//Maybe handle the checkout
		if(! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'hubaga_handle_checkout' ) {
			$this->process_checkout();
		}

		//Sandbox settings
		if( hubaga_is_sandbox() ) {
			add_action( 'admin_notices' , array( $this, 'sandbox_notices' ) );
		}
	}

	/**
	 * Payment settings
	 */
	public function init_settings( ) {

		//Register the gateway settings
		$gateways = $this->get_gateways();

		foreach( $gateways as $gateway ) {

			$gateway_obj = hubaga_get_gateway( $gateway );
			$default = 0;
			if ( $gateway == 'paypal' )
				$default = 1; //PayPal is usually active by default

			$_title = $gateway_obj->meta['title'];
			$title 	= sprintf( esc_html__( 'Enable %s', 'hubaga' ), $_title );

			$description = '';
			if ( isset( $gateway_obj->meta['description'] ) ) {
				$description = $gateway_obj->meta['description'];
			}

			hubaga_add_option(
				array(
					'id' 			=> "is_gateway_{$gateway}_active",
					'type' 			=> 'switch',
					'default' 		=> $default,
					'class' 		=> 'filled-in',
					'title' 		=> $title,
					'description' 	=> $description,
					'section' 		=> 'Gateways',
				)
			);

			if ( is_callable( array( $gateway_obj, 'init_settings' ) ) ) {
				$gateway_obj->init_settings();
			}
		}

	}

	/**
	 * Warn the user to disable sandbox when he is ready to start selling
	 */
	 public function sandbox_notices(){
		echo '<div class="notice notice-info is-dismissible" style=" padding: 1em; ">';
			esc_html_e( 'You have activated sandbox mode. Do not forget to disable it when you are ready to start selling!', 'hubaga' );

		echo '</div>';
	}

	/**
	 * Processes the checkout then redirects the user to the appropriate page.
	 */
	 abstract public function process_checkout();

	/**
	 * Registers a new gateway
	 * @see hubaga_register_core_gateways
	 */
	abstract public function add_gateway( $gateway, $object );

	/**
	 * Removes a registered gateway
	 */
	abstract public function remove_gateway( $gateway );

	/**
	 * Gets a registered gateway object
	 */
	abstract public function get_gateway( $gateway );

	/**
	 * Gets all registered gateway objects
	 */
	abstract public function get_gateways();

	/**
	 * Gets all active gateway objects
	 */
	abstract public function get_active_gateways();

	/**
	 * Checks if a given gateway is active or not
	 */
	abstract public function is_gateway_active( $gateway );

	/**
	 * Checks if an order is refundable
	 * @see hubaga_get_order
	 */
	abstract public function is_refundable( $order );

	/**
	 * Refunds an order
	 * @see hubaga_get_order
	 */
	abstract public function refund( $order );

}
