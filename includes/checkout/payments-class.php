<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payments Class.
 *
 * The default payment processor responsible for processing the checkout page and charging customers
 * You can register your own payments processor using the 'hubaga_get_payment_processor' hook
 *
 * @class    H_Payments
 * @version  1.0.0
 */
class H_Payments extends H_Abstract_Payments{

	/**
	 * An array of all the registered gateways
	 *
	 * @since 1.0.0
	 */

	public $gateways = array();

	/**
	 * Add a gateway to the available list of gateways
	 *
	 * @param string $gateway gateway id
	 * @param object $object gateway object instance
	 * @return void
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Add a gateway to the available list of gateways
	 *
	 * @param string $gateway gateway id
	 * @param object $object gateway object instance
	 * @return void
	 */
	public function add_gateway( $gateway, $object ) {

		$gateway = trim( $gateway );
		/**
		 * Fires before a gateway is registered
		 *
		 *
		 * @since 1.0.0
		 *
		 */
		do_action( 'hubaga_before_add_gateway', $gateway, $object );
		if ( is_object( $object ) ) {
			$this->gateways[ $gateway ] = $object;
		}

	}

	/**
	 * Remove a gateway from our list of gateways
	 *
	 * @param string $gateway gateway id
	 * @return void
	 */
	public function remove_gateway( $gateway ) {

		$gateway = trim( $gateway );

		/**
		 * Fires before a gateway is deregistered
		 *
		 *
		 * @since 1.0.0
		 *
		 */
		do_action( 'hubaga_before_remove_gateway', $gateway );
		if ( isset( $this->gateways[ $gateway ] ) ) {
			unset( $this->gateways[ $gateway ] );
		}

	}

	/**
	 * Returns an array of active gateways
	 *
	 * @return array
	 */
	public function get_active_gateways() {

		//Find the intersection of active gateways and registered gateways
		$gateways 			= $this->get_gateways();
		$active_gateways 	= array();
		foreach( $gateways as $gateway ){
			if( hubaga_get_option( "is_gateway_{$gateway}_active" ) )
				$active_gateways[] = $gateway;
		}

		/**
		 * Filters the active gateways
		 *
		 *
		 * @since 1.0.0
		 *
		 */
		return apply_filters( 'hubaga_active_gateways', $active_gateways );

	}

	/**
	 * Checks if the given gateway is active
	 *
	 * @return bool
	 */
	public function is_gateway_active( $gateway ) {
		$gateway = trim( $gateway );
		return hubaga_get_option( "is_gateway_{$gateway}_active" );
	}

	/**
	 * Returns a gateway object
	 *
	 * @return object or false if the gateway is not registered
	 */
	public function get_gateway( $gateway ) {

		$gateway 	= trim( $gateway );
		$object 	= false;
		if ( isset( $this->gateways[ $gateway ] ) ) {
			$object = $this->gateways[ $gateway ] ;
		}
		return apply_filters( 'hubaga_get_gateway', $object, $gateway );

	}

	/**
	 * Returns all gateways
	 *
	 */
	public function get_gateways() {
		return apply_filters( 'hubaga_get_gateways', array_keys( $this->gateways ));
	}

	/**
	 * Checks if the provided order is refundable
	 *
	 * @return bool
	 */
	public function is_refundable( $order ) {

		$order = hubaga_get_order( $order );

		//Free orders not refundable
		if( $order->is_free() )
			return false;

		//The order has no gateway set or the gateway does not exist
		if(! isset( $order->payment_method ) || $this->is_gateway_active( $order->payment_method ) )
			return false;

		//The order has already been refunded
		if( isset( $order->order_status ) && $order->order_status == hubaga_get_refunded_order_status() )
			return false;

		$gateway = $this->get_gateway( $order->payment_method );

		return ( is_callable( array( $gateway, 'refund') ) );
	}

	/**
	 * Refunds an order
	 *
	 * @return bool
	 */
	public function refund( $order, $amount = null, $reason = '' ) {
		if(! $this->is_refundable( $order ) )
			return false;

		$gateway = $this->get_gateway( $order->payment_method );
		return $gateway->refund( $order, $amount, $reason );
	}

	/**
	 * Process the checkout after the confirm order button is pressed.
	 *
	 */
	public function process_checkout( ) {

		//Basic security check
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'hubaga_checkout' ) ) {
			return hubaga_add_error( esc_html__( 'We were unable to process your order! please refresh the page and try again.', 'hubaga' ) );
		}

		//Are there any products to process
		if (! hubaga_is_array_key_valid( $_REQUEST, 'hubaga_buy' ) ) {
			return hubaga_add_error( esc_html__( 'No product selected.', 'hubaga' ) );
		}

		///Posted data
		$data   = wp_unslash( $_REQUEST );

		/**
		 * Fires before the checkout is processed
		 *
		 * The security nonce has already been verified
		 *
		 * EVENTS
		 * hubaga_validate_checkout_fields 20
		 *
		 * @since 1.0.0
		 *
		 */
		do_action( 'hubaga_before_checkout_process', $data );

		//In case any handler attached to the above hook threw an error
		if ( hubaga_has_errors() )
			return;

		//Validate the checkout product
		$product = hubaga_get_product( $data['hubaga_buy'] );
		if (! hubaga_can_buy_product( $product ) ) {
			return hubaga_add_error( esc_html__( "Product not found.", 'hubaga' ) );
		}

		//Process the customer
		$customer 	= hubaga_process_checkout_customer( $data['email'] );
		if ( hubaga_has_errors() )
			return;

		$customer = hubaga_get_customer( $customer );

		//Process the order total
		$order_total 	= hubaga_get_product_price( $product, false );
		$discount_total = 0;
		$coupon			= 0;

		//Maybe apply a coupon
		if( $order_total > 0 ) {

			if (! empty( $data['coupon_code'] ) ) {
				$coupon_result = hubaga_apply_coupon( $data['coupon_code'], $order_total, $customer, $product );

				if ( hubaga_has_errors() )
					return;

				$order_total 	= $coupon_result['order_total'];
				$discount_total = $coupon_result['discount_total'];
				$coupon			= hubaga_get_coupon( false, $data['coupon_code'] )->ID;
			}
		}

		//Are we receiving payment
		$is_payable = apply_filters( 'hubaga_should_pay_order', $order_total > 0, $order_total, $discount_total, $coupon, $customer );
		if( $is_payable ) {

			//Is there an active gateway
			if (! hubaga_is_array_key_valid( $data, 'gateway', 'is_string' ) ) {
				return hubaga_add_error( esc_html__( 'Invalid payment gateway.', 'hubaga' ) );
			}

			if (! hubaga_is_active_gateway(  $data['gateway'] ) ) {
				return hubaga_add_error( esc_html__( 'Inactive payment gateway.', 'hubaga' ) );
			}

			$gateway = hubaga_get_gateway( $data['gateway'] );
			if (! is_object( $gateway ) ) {
				return hubaga_add_error( __( 'Invalid payment gateway. Try another gateway.', 'hubaga' ) );
			}

		}

		// Create the order
		$args  = array(
			'customer' 		=> $customer,
			'product'  		=> $product,
			'order_total' 	=> $order_total,
			'discount_total'=> $discount_total,
			'coupon'	    => $coupon,
		);
		$order = hubaga_create_checkout_order( $args );

		if ( hubaga_has_errors() )
			return;

		/**
		 * Fires after the checkout is processed but before it is paid for
		 *
		 * The order is already saved in the database.
		 *
		 * @since 1.0.0
		 *
		 */
		do_action( 'hubaga_after_checkout_process', $order->ID );

		//In case a plugin generated errors during the above hook
		if ( hubaga_has_errors() )
			return;

		//Regenerate the order
		$order = hubaga_get_order( $order->ID );

		//Handle the order payment creation
		if ( $is_payable ) {
			$this->process_order_payment( $order, $gateway );
		} else {
			$this->process_free_order( $order );
		}

	}

	/**
	 * Processes a payment using the provided gateway
	 *
	 */
	protected function process_order_payment( $order, $gateway ) {

		//Append the gateway to the order
		$order->payment_method = $gateway->meta['id'];
		$order->save();

		//Process the payment
		$result = $gateway->process_payment( $order );

		//Does the gateway display a new checkout form
		if ( isset( $result['action'] ) && 'form' === $result['action'] ) {
			return hubaga_checkout_form( $result['form'] );
		}

		//Does the gateway want to redirect the user to another url
		if ( isset( $result['action'] ) && 'redirect' === $result['action'] ) {
			return $this->send_redirect_response( $result['url'] );
		}

		//Or maybe the payment was completed
		if ( isset( $result['action'] ) && 'complete' === $result['action'] ) {
			return $this->send_order_complete_response( $order );
		}

		//IF we are here then the gateway returned an invalid response
		$order->add_note(
			sprintf(
				esc_html__( ' Error: %1$s ( gateway ) returned an invalid response while processing Order %2$s'),
				$order->payment_method,
				$order->ID
				) );

		$order->save();

	}

	/**
	 * Sends a redirect response
	 *
	 * @since  1.0.0
	 */
	protected function send_redirect_response( $url ) {

		if ( wp_doing_ajax() ) {
			wp_send_json( array(
				'action'   	=> 'redirect',
				'url' 		=> $url,
			) );
		} else {
			wp_redirect( $url );
			exit;
		}

	}

	/**
	 * Sends an order complete response
	 *
	 * @since  1.0.0
	 */
	protected function send_order_complete_response( $order ) {

		//Refresh the order with new data
		$order = hubaga_get_order( $order->ID );
		hubaga()->order_completed = true;

		if ( wp_doing_ajax() ) {
			return hubaga_checkout_form( hubaga()->template->get_instacheck_order_complete_html( $order ) );
		} else {
			return hubaga_checkout_form( hubaga()->template->get_view_order_html( $order ) );
		}

	}

	/**
	 * Process an order that doesn't require payment.
	 *
	 * @since  1.0.0
	 * @param  int $order_id
	 */
	protected function process_free_order( $order ) {
		$order->update_status( hubaga_get_completed_order_status() );
		$this->send_order_complete_response( $order );
	}

}
