<?php
/**
 * Hubaga Checkout Functions
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Retrieves the payment processor
 *
 * @since Hubaga 1.0.0
 *
 * @return object an object of the payment processor
 */
function hubaga_get_payment_processor() {

	if( isset( hubaga()->payments ) && is_object( hubaga()->payments ) )
		return hubaga()->payments;

	$processor = apply_filters( 'hubaga_payments_processor', false );
	if (! is_object( $processor ) )
		$processor = H_Payments::instance();

	hubaga()->payments = $processor;
	return $processor;
}

/**
 * Adds a payment gateway
 *
 * @since Hubaga 1.0.0
 */
function hubaga_add_gateway( $gateway, $object ) {
	return hubaga_get_payment_processor()->add_gateway( $gateway, $object );
}

/**
 * Retrieves a payment gateway
 *
 * @since Hubaga 1.0.0
 *
 * @return object an object of the payment gateway or false
 */
function hubaga_get_gateway( $gateway ) {
	$gateway = trim( $gateway );
	$processor = hubaga_get_payment_processor();
	return $processor->get_gateway( $gateway );
}

/**
 * Retrieves a gateway title
 *
 * @since Hubaga 1.0.0
 *
 * @return string
 */
function hubaga_get_gateway_title( $id ) {
	$gateway = hubaga_get_gateway( $id );
	if(! $gateway ) {
		return $id;
	}

	return $gateway->meta['title'];
}

/**
 * Retrieves all active payment gateways
 *
 * @since Hubaga 1.0.0
 *
 */
function hubaga_get_active_gateways() {
	$processor = hubaga_get_payment_processor();
	return $processor->get_active_gateways();
}

/**
 * Checks if a gateway is active
 *
 * @since Hubaga 1.0.0
 *
 * @return object an object of the payment gateway or false
 */
function hubaga_is_active_gateway( $gateway ) {
	$gateway 	= trim( $gateway );
	$processor 	= hubaga_get_payment_processor();
	return $processor->is_gateway_active( $gateway );
}

/**
 * Registers core payment gateways
 *
 * @since Hubaga 1.0.0
 */
function hubaga_register_core_payment_gateways() {

	require_once hubaga_get_includes_path() . 'checkout/gateways/test-gateway.php';
	require_once hubaga_get_includes_path() . 'checkout/gateways/paypal/paypal-gateway.php';
	hubaga_add_gateway( 'test', 	new H_Test_Gateway() );
	hubaga_add_gateway( 'paypal', 	new H_PayPal_Gateway() );

}
add_action( 'hubaga_init', 'hubaga_register_core_payment_gateways', 11 );


/**
 * Validates checkout fields using the posted data
 *
 * @since Hubaga 1.0.0
 */
function hubaga_validate_checkout_fields() {
	$checkout_fields = hubaga_get_checkout_fields();

	foreach( $checkout_fields as $field => $arguments ){

		$field = esc_attr( $field );
		$title = $arguments[ 'title' ];

		if( isset( $arguments['required'] ) && !hubaga_is_array_key_valid( $_REQUEST, $field ) ) {
			hubaga_add_error( __( "$title is required", 'hubaga' ) );
			continue;
		}

		if( isset( $arguments['validation'] ) && !hubaga_is_array_key_valid( $_REQUEST, $field, $arguments['validation'] ) ) {
			hubaga_add_error( __( "$title is invalid", 'hubaga' ) );
		}

	}

	/**
	 * Fires when validating checkout fields
	 *
	 * Use this hook to run your own checkout validations
	 *
	 * @since 1.0.0
	 *
	 */
	do_action( 'hubaga_validating_checkout_fields' );

}
add_action( 'hubaga_before_checkout_process', 'hubaga_validate_checkout_fields', 20 );


/**
 * Retrieves the checkout fields
 *
 * @since Hubaga 1.0.0
 */
function hubaga_get_checkout_fields() {

	//Setup the default email
	$default_email = '';

	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$default_email = $user->user_email;
	}

	if ( isset( $_REQUEST['email'] ) ) {
		$default_email = sanitize_email( $default_email );
	}
	
	$label= __( 'Email', 'hubaga' );
	$class= '';
	if(! $default_email ){
		$class= 'hubaga-is-empty';
	}
	$html = "
	<label class='hubaga-label'>
		<input value='$default_email' name='email'  class='hubaga-field $class' type='email' placeholder='email@example.com' />
		<span><span>$label</span></span>
	</label>";
	$fields = array(
		'email' => array(
			'validation'  	=> 'is_email',
			'title'  		=> __( 'Email', 'hubaga' ),
			'required'  	=> true,
			'html'  	    => $html,
		),
	);
	return apply_filters( 'hubaga_checkout_fields', $fields );

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
 * @return string Url to checkout page
 */
function hubaga_checkout_form( $form = null ){

	if( null !== $form ){
		hubaga()->checkout_form = $form;
	}
	return hubaga()->checkout_form;

}
