<?php
/**
 * Renders the order metabox
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$gateways = array();
foreach( hubaga_get_active_gateways() as $gateway ){
	$gateways[$gateway] = esc_html( hubaga_get_gateway_title( $gateway ) );
}

return array(

	'post_status' 	=> array (
		'type' 			=> 'select',
		'title' 		=> esc_html__( 'Status', 'hubaga' ),
		'options' 		=> wp_list_pluck( hubaga_get_order_statuses(), 'label' ),
	),
	
	'payment_date' 	=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Payment Date', 'hubaga' ),
	),
	
	'product' 		=> array (
		'type' 			=> 'select',
		'title' 		=> esc_html__( 'Product', 'hubaga' ),
		'placeholder'  	=> __( 'Select Product', 'hubaga' ),
		'data'  		=> 'posts',
		'data_args'  	=> array( 
			'numberposts' 	=> '100',
			'post_type'	  	=> hubaga_get_product_post_type(),
			'post_status' 	=> 'publish',
			),
	),
	
	'payment_method' 	=> array (
		'type' 			=> 'select',
		'title' 		=> esc_html__( 'Payment Method', 'hubaga' ),
		'options'  		=> $gateways,
	),
	
	'customer_id' 		=> array (
		'type' 			=> 'select',
		'title' 		=> esc_html__( 'Customer', 'hubaga' ),
		'placeholder'  	=> __( 'Select Customer', 'hubaga' ),
		'data'  		=> 'users',
		'data_args'  	=> array( 
			'count_total' => false,
			'post_type'	  => hubaga_get_product_post_type(),
			'fields' 	  => array( 'ID', 'display_name' ),
			),
	),
	
	'total' 			=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Order Total', 'hubaga' ),
	),
	
	'discount_total' 	=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Total Discount', 'hubaga' ),
	),
	
	'currency' 			=> array (
		'type' 			=> 'select',
		'title' 		=> esc_html__( 'Currency', 'hubaga' ),
		'options' 		=> hubaga_get_currencies(),
	),
);