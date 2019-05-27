<?php
/**
 * Hubaga Checkout Functions
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Retrieves all active payment gateways
 *
 * @since Hubaga 1.0.0
 *
 */
function hubaga_get_active_gateways() {

	$gateways 			= hubaga_get_registered_gateways();
	$active_gateways 	= array();
	foreach( $gateways as $id => $info ){
		if( hubaga_get_option( "is_gateway_{$id}_active" ) )
			$active_gateways[$id] = $info;
	}
	return $active_gateways;

}

/**
 * Retrieves all registered payment gateways
 *
 * @since Hubaga 1.0.4
 *
 */
function hubaga_get_registered_gateways() {
	return apply_filters( 'hubaga_get_registered_gateways', array() );
}

/**
 * Checks if a gateway is active
 *
 * @since Hubaga 1.0.0
 *
 * @return bool
 */
function hubaga_is_active_gateway( $gateway ) {
	return hubaga_get_option( "is_gateway_{$gateway}_active" );
}

/**
 * Retrieves gateway details
 *
 * @since Hubaga 1.0.4
 *
 * @return array
 */
function hubaga_get_gateway( $gateway ) {

	$gateways = hubaga_get_registered_gateways();
	if( empty($gateways[$gateway]) ){
		return false;
	}
	return$gateways[$gateway];

}

/**
 * Handles checkout response 
 *
 * @since Hubaga 1.0.4
 *
 * @return void
 */
function hubaga_send_checkout_response( $action, $body ) {
	die( wp_json_encode( array(
		'action' => $action,
		'body'	 => $body,	
	)));
}

/**
 * This function processes payments
 *
 * @since Hubaga 1.0.4
 *
 * @return void
 */
function hubaga_process_checkout() {

	//Basic security check
	if ( empty( $_REQUEST['hubaga_nonce_field'] ) || ! wp_verify_nonce( $_REQUEST['hubaga_nonce_field'], 'hubaga_nonce_action' ) ) {
		$error = esc_html__( 'We were unable to process your order! Please refresh the page and try again.', 'hubaga' );
		hubaga_send_checkout_response( 'error', $error );
	}

	//Process the product
	$product = hubaga_process_checkout_product();
	
	//Posted data
	$data   = wp_unslash( $_REQUEST );

	/**
	 * Fires before the checkout is processed
	 *
	 * The security nonce has already been verified
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	do_action( 'hubaga_before_checkout_process', $data );

	//In case any handler attached to the above hook threw an error
	if ( hubaga_has_errors() ) {
		hubaga_send_checkout_response( 'error', hubaga_get_errors_html() );
	}

	//Process the customer
	$customer 	= hubaga_process_checkout_customer( $data['email'] );

	//Process the order total
	$product_price 	= array( 
		'order_total' 	  => hubaga_get_product_price( $product, false ),
		'discount_total'  => 0,
		'coupon'		  => 0,
	);
	$order_total    = apply_filters( 'hubaga_checkout_order_total', $product_price, $product, $customer, $data );

	// Create the order
	$status = hubaga_get_pending_order_status();
	if( $order_total['order_total'] == 0 ) {
		$status = hubaga_get_completed_order_status();
	}

	$args = array_merge(
		$order_total,
		array(
			'customer' 		=> $customer,
			'product'  		=> $product->ID,
			'post_author' 	=> $customer,
			'post_status' 	=> hubaga_get_pending_order_status(),
			'post_type' 	=> $status,
		)
	);
	$order = hubaga_create_checkout_order( $args );

	/**
	 * Fires after the checkout is processed but before it is paid for
	 *
	 * The order is already saved in the database.
	 *
	 * @since 1.0.0
	 *
	 */
	do_action( 'hubaga_after_checkout_process', $order->ID, $data );

	//If we are here, it's because no gateway processed the payment, let's redirect the user to the order page
	hubaga_send_checkout_response( 'redirect', hubaga_get_order_url( $order ) );
	
}
add_action( 'wp_ajax_hubaga_process_checkout', 		    'hubaga_process_checkout' );
add_action( 'wp_ajax_nopriv_hubaga_process_checkout',   'hubaga_process_checkout' );

/**
 * Processes the checkout product.
 *
 * @return object|void
 */
function hubaga_process_checkout_product(){

	//Ensure there is a product...
	if ( empty($_REQUEST['hubaga_buy']) ) {
		$error = esc_html__( 'No product to process.', 'hubaga' );
		hubaga_send_checkout_response( 'error', $error );
	}

	//... and it is available for sale
	$product = hubaga_get_product( $_REQUEST['hubaga_buy'] );
	if (! hubaga_can_buy_product( $product ) ) {	
		$error = esc_html__( 'Product not found.', 'hubaga' );
		hubaga_send_checkout_response( 'error', $error );
	}

	return $product;
}

/**
 * Processes checkout customer.
 *
 * @return int (user ID) of created user or existing user or 0 on failure.
 */
function hubaga_process_checkout_customer(){

	// Check the email address.
	if ( empty( $_REQUEST['email'] ) || ! is_email( $_REQUEST['email'] ) ) {
		$error = esc_html__( 'Invalid email address.', 'hubaga' );
		hubaga_send_checkout_response( 'error', $error );
	}

	$customer = sanitize_text_field( $_REQUEST['email'] );
	$customer = email_exists( $email );
	if( false !== $customer){
		return $customer;
	}
		
	//The user does not exist; create one
	$username = hubaga_generate_username( $email );
	$password = wp_generate_password();	
		
	/**
	 * Filters a user data passed to wp_insert_user
	 *
	 * @since 1.0.0
	 *
	 * @param array the userdata being filtered.
	 */
	 
	$new_customer_data = apply_filters( 'hubaga_new_customer_data', array(
		'user_login' => $username,
		'user_pass'  => $password,
		'user_email' => $email,
		'role'       => 'customer',
	) );
	$customer_id = wp_insert_user( $new_customer_data );

	if ( is_wp_error( $customer_id ) ) {
		foreach( $customer_id->get_error_messages() as $error ){
			hubaga_add_error( $error );
		}
		hubaga_send_checkout_response( 'error', hubaga_get_errors_html() );
	}

	do_action( 'hubaga_customer_created', $customer_id, $new_customer_data );
	return $customer_id;

}


/**
 * Generate a unique username from the provided email
 * @param email the email used to generate the username
 * @since 1.0.0
 */
function hubaga_generate_username( $email ){
	$username = sanitize_user( current( explode( '@', $email ) ), true );
		
	// Ensure username is unique.
	$append     = 1;
	$o_username = $username;
		
	while ( username_exists( $username ) ) {
		$username = $o_username . $append;
		$append++;
	}
	
	return $username;
}

/**
 * Creates an order during checkout
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 * @uses hubaga_add_error
 *
 * @return object H_Order instance or false on failure
 */
function hubaga_create_checkout_order( $args ) {

	if (! $order_id = wp_insert_post( $args ) ) {
		$error = esc_html__( "We are unable to create your order. Please try again.", 'hubaga' );
		hubaga_send_checkout_response( 'error', $error );
	}

	extract( $args);

	$order 					= hubaga_get_order( $order_id );
	$order->customer_id		= $customer;
	$order->transaction_id	= $order_id; //Defaults to order id;
	$order->total			= $order_total;
	$order->discount_total	= $discount_total;
	$order->currency		= hubaga_get_currency();
	$order->product			= $product;
	$order->coupon			= $coupon;
	$order->browser			= hubaga_get_browser();
	$order->platform		= hubaga_get_platform();
	$order->add_note( __( "Order Created.", "hubaga" ) );
	$order->add_note( __( "Order marked as pending.", "hubaga" ) );

	if( $discount_total > 0 ) {
		$order->add_note( sprintf( __( "This customer saved %s using a coupon code.", "hubaga" ), $discount_total ) );
	}

	//Update the coupon usage count
	if( $coupon != 0 && function_exists( 'hubaga_get_coupon' ) ) {
		$coupon = hubaga_get_coupon( $coupon );
		$count = $coupon->usage_count;
		$coupon->usage_count = $count + 1;
		$coupon->save();
	}

	$product->update_sell_count();

	$order->save();

	// this allows a user to download an order without logging in;
	// expires 2 hours after an order is created
	$token = md5( $order->ID . wp_generate_password( 20, false ) . time() );
	set_transient( $order->ID . '_download_token', $token, 2 * 60 * 60 );

	/**
	 * Fires after a new order is created
	 *
	 * @since 1.0.0
	 *
	 * @param object   $order H_Order.
	 */
	do_action( 'hubaga_order_created', $order );
	return $order;
}


/**
 * Gets the url to the checkout page.
 *
 * @since  1.0.0
 *
 * @return string Url to checkout page
 */
function hubaga_get_checkout_url(){

	$id = intval( hubaga_get_option( 'checkout_page_id' ) );
	$permalink = 1 > $id ? get_home_url() : get_permalink( $id );
	return apply_filters( 'hubaga_get_checkout_url', $permalink );

}

/**
 * Checks whether or not this is a checkout page
 *
 * @since  1.0.0
 *
 * @return bool
 */
 function hubaga_is_checkout_page(){
	
		$id = intval( hubaga_get_option( 'checkout_page_id' ) );
		if( !$id ){
			return false;
		}
		return is_page( $id );
	
}

/**
 * Returns or sets the custom checkout form
 *
 * @since  1.0.0
 *
 * @return string checkout page html
 */
function hubaga_get_checkout_form( $product = false ){
	return hubaga()->template->get_checkout_html( $product );
}
