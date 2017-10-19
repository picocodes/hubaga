<?php

//Hubaga ajax handler
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_hubaga_get_checkout', 		    'hubaga_ajax_get_checkout' );
add_action( 'wp_ajax_nopriv_hubaga_get_checkout',   'hubaga_ajax_get_checkout' );
add_action( 'wp_ajax_hubaga_handle_checkout', 	    'hubaga_ajax_get_checkout' );
add_action( 'wp_ajax_nopriv_hubaga_handle_checkout', 'hubaga_ajax_get_checkout' );

//Returns the checkout form HTML
function hubaga_ajax_get_checkout( ) {

	//Check nonce
	$nonce = $_REQUEST['nonce'];

	if (! wp_verify_nonce( $nonce, 'hubaga_nonce' ) ) {
		wp_die ( __( 'There was a problem processing the request. Please refresh the page and try again.', 'hubaga' ) );
	}

	echo hubaga()->template->get_checkout_html();

	exit(); //Keep this!

}

add_action( 'wp_ajax_hubaga_apply_coupon', 'hubaga_ajax_apply_coupon' );
add_action( 'wp_ajax_nopriv_hubaga_apply_coupon', 'hubaga_ajax_apply_coupon' );

//Coupon requests
function hubaga_ajax_apply_coupon( ){

	//Check nonce
	$nonce = $_REQUEST['nonce'];

	if (! wp_verify_nonce( $nonce, 'hubaga_nonce' ) ) {
		wp_send_json( array(
			'result' => 'error',
			'error'  => __( 'Unable to complete your request.', 'hubaga' ),
		) );
		exit();
	}

	$data   = wp_unslash( $_REQUEST );

	//Validate the product
	if ( empty( $data['product'] ) OR !( hubaga_is_product( $data['product'] ) )  ) {
		wp_send_json( array(
			'result' => 'error',
			'error'  => __( 'Error: Invalid product.', 'hubaga' ),
		) );
		exit();
	}

	//Validate the coupon
	if ( empty( $data['coupon'] ) OR !hubaga_coupon_exists( false, $data['coupon'] ) ) {
		wp_send_json( array(
			'result' => 'error',
			'error'  => __( 'Error: Invalid coupon.', 'hubaga' ),
		) );
		exit();
	}

	$customer = 0;
	if (! empty( $data['email'] ) ) {
		$customer_data = get_user_by( 'email', $data['email'] );
		if( $customer_data->ID ) {
			$customer = $customer_data->ID;
		}
	}

	$price  = hubaga_get_product_price( $data['product']  );
	if( ! $price ) {
		wp_send_json( array(
			'result' => 'error',
			'error'  => __( 'Unable to apply this coupon.', 'hubaga' ),
		) );
		exit();
	}

	$result = hubaga_apply_coupon( $data['coupon'], $price, $customer, $data['product'] );

	if( ! is_array( $result ) ) {

		$error = __( 'Error applying this coupon.', 'hubaga' );
		if( hubaga_has_errors() ) {
			$error = strip_tags( hubaga_get_errors_html() );
		}
		wp_send_json( array(
			'result' => 'error',
			'error'  => $error,
		) );
		exit();
	}

	$price  = "<span class='hubaga-original-price'> $price </span> {$result['order_total']} ";
	wp_send_json( array(
		'result' => 'success',
		'price'  => $price,
	) );
	exit();

}


//Logged in users only
add_action( 'wp_ajax_hubaga_handle_report_data', 'hubaga_handle_report_data' );

//Handles requests for reports
function hubaga_handle_report_data() {

	$is_auth = wp_verify_nonce( $_REQUEST['nonce'], 'hubaga_reports_nonce' );
	if ( !$is_auth OR !current_user_can( 'manage_options' ) ) {
		wp_die ( __( 'You are not allowed to view reports.', 'hubaga' ) );
	}

	$reports = new H_Report();
	wp_send_json( $reports->vue_data() ) ;
	die();
}
