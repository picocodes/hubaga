<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 
 * Registers our custom gateway
 */
function hubaga_register_test_gateway( $gateways ){
	$gateways['test'] = array(
		'title'			=> 'Test Gateway',
		'button_text'	=> 'Test Gateway',
		'description'	=> 'Orders will be instantly marked as complete.',
		'url'			=> 'https://hubaga.com/',
		'author'		=> 'Picocodes',
		'author_url'	=> 'https://hubaga.com/',
		'checkout_description' => 'This gateway should ONLY be used when testing. Payments will instantly be marked as complete.',
	);
	return $gateways;
}
add_filter( 'hubaga_get_registered_gateways', 'hubaga_register_test_gateway' );

/**
 * Processes order payments
 */
function hubaga_test_gateway_process_payment( $order, $data ) {
	if( hubaga_is_active_gateway('test') && !empty($data['gateway']) && 'test' == $data['gateway']) {
		$order = hubaga_get_order( $order );
		$order->payment_date   = gmdate( 'D, d M Y H:i:s e' ); //GMT time
		$order->payment_method = 'test';
		$order->update_status( hubaga_get_completed_order_status() );
		$order->save();
		hubaga_send_checkout_response( 'redirect', hubaga_get_order_url( $order ) );
	}
}
add_action( 'hubaga_after_checkout_process', 'hubaga_test_gateway_process_payment', 10, 2 );

/**
 * Handles order refunds
 */
function hubaga_test_gateway_process_refund( $order ) {
	if( hubaga_is_active_gateway('test') && 'test' == $order->payment_method) {
		$order->update_status( hubaga_get_refunded_order_status() );
		$order->save();
	}
}
add_action( 'hubaga_process_refund', 'hubaga_test_gateway_process_refund', 10 );