<?php
/**
 * Renders the settings page
 *
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$currencies = array();
if (function_exists('hubaga_get_currencies')) {
	$currencies = hubaga_get_currencies();
}

return array(

	'account_page_id' 			=> array (
		'type' 					=> 'select',
		'data'  				=> 'pages',
		'data_args'  			=> array( 'number' => '100' ),
		'section'  				=> 'General',
		'placeholder'  			=> esc_html__( 'Select Page', 'hubaga' ),
		'title' 				=> esc_html__( 'Account Page', 'hubaga' ),
		'description' 			=> esc_html__( 'Do not forget to include the [h-account] shortcode on this page.', 'hubaga' ),
	),
	'checkout_page_id' 			=> array (
		'type' 					=> 'select',
		'data'  				=> 'pages',
		'data_args'  			=> array( 'number' => '100' ),
		'section'  				=> 'General',
		'placeholder'  			=> esc_html__( 'Select Page', 'hubaga' ),
		'title' 				=> esc_html__( 'Checkout Page', 'hubaga' ),
		'description' 			=> esc_html__( 'Do not forget to include the [h-checkout] shortcode on this page.', 'hubaga' ),
	),
	'download_method' 			=> array (
		'type' 					=> 'select',
		'options'  				=> array(
			'force' 	=> esc_html__( 'Force Downloads', 'hubaga' ),
			'redirect' 	=> esc_html__( 'Redirect', 'hubaga' ),
		),
		'section'  				=> 'General',
		'placeholder'  			=> esc_html__( 'Select Download Method', 'hubaga' ),
		'title' 				=> esc_html__( 'Download Method', 'hubaga' ),
		'description' 			=> esc_html__( 'Force Downloads is the best but might not work well for huge files.', 'hubaga' ),
	),
	'currency' 					=> array (
		'type' 					=> 'select',
		'options'  				=> $currencies,
		'default'  				=> 'USD',
		'section'  				=> 'General',
		'placeholder'  			=> esc_html__( 'Select Main Currency', 'hubaga' ),
		'title' 				=> esc_html__( 'Store Currency', 'hubaga' ),
		'description' 			=> esc_html__( 'This is the currency by which your customers will be charged.', 'hubaga' ),
	),
	'currency_position' 		=> array (
		'type' 					=> 'select',
		'options'  				=> array(
				'left' 	=> esc_html__( 'Left', 'hubaga' ),
				'right' => esc_html__( 'Right', 'hubaga' ),
			),
		'default'  				=> 'left',
		'section'  				=> 'General',
		'placeholder'  			=> esc_html__( 'Select Currency Location', 'hubaga' ),
		'title' 				=> esc_html__( 'Currency Position', 'hubaga' ),
	),
	'thousand_separator' 		=> array (
		'type' 					=> 'text',
		'default'  				=> ',',
		'section'  				=> 'General',
		'title' 				=> esc_html__( 'Thousand Separator', 'hubaga' ),
		'custom_attributes'  	=> array(
				'style' => 'max-width: 100px;'
			),
	),
	'decimal_separator' 		=> array (
		'type' 					=> 'text',
		'default'  				=> '.',
		'section'  				=> 'General',
		'title' 				=> esc_html__( 'Decimal Separator', 'hubaga' ),
		'custom_attributes'  	=> array(
				'style' => 'max-width: 100px;'
			),
	),
	'sandbox' 					=> array (
		'type' 					=> 'checkbox',
		'default'  				=> '0',
		'section'  				=> 'General',
		'class' 				=> 'filled-in',
		'title' 				=> esc_html__( 'Activate Test Mode', 'hubaga' ),
		'description' 			=> esc_html__( 'No actual transaction will happen. This is also called sandbox mode.', 'hubaga' ),
		'custom_attributes'  	=> array(
				'style' => 'max-width: 100px;'
			),
	),
	'enable_instacheck' 		=> array (
		'type' 					=> 'checkbox',
		'default'  				=> '1',
		'section'  				=> 'General',
		'class' 				=> 'filled-in',
		'title' 				=> esc_html__( 'Activate Instacheck', 'hubaga' ),
		'description' 			=> esc_html__( 'Instacheck allows customers on large screens to checkout without reloading pages.', 'hubaga' ),
		'custom_attributes'  	=> array(
				'style' => 'max-width: 100px;'
			),
	),
	'enable_coupons' 			=> array (
		'type' 					=> 'checkbox',
		'default'  				=> '1',
		'section'  				=> 'General',
		'class' 				=> 'filled-in',
		'title' 				=> esc_html__( 'Enable Coupons', 'hubaga' ),
		'description' 			=> esc_html__( 'Allow customers to use coupons during checkout.', 'hubaga' ),
		'custom_attributes'  	=> array(
				'style' => 'max-width: 100px;'
			),
	),
	//Force the gateway section to follow general section
	'gateway_section_title' 	=> array (
		'type' 					=> 'title',
		'section'  				=> 'Gateways',
		'title' 				=> esc_html__( 'Payment Gateways', 'hubaga' ),
		'subtitle' 				=> esc_html__( 'This section allows you to activate or deactivate the various payment gateways.', 'hubaga' ),
	),
	'notifications_title' 		=> array(
		'type' 					=> 'title',
		'title' 				=> esc_html__( 'Notifications', 'hubaga' ),
		'section'  				=> 'Notifications',
		'sub_section'  			=> 'Emails',
	),
	'mailer_from_name'			=> array(
		'type' 					=> 'text',
		'default'  				=> wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
		'title' 				=> esc_html__( 'From Name', 'hubaga' ),
		'description' 			=> esc_html__( 'Fills the "from" field of outgoing emails', 'hubaga' ),
		'section'  				=> 'Notifications',
		'sub_section'  			=> 'Emails',
	),
	'mailer_from_email'			=> array(
		'type' 					=> 'text',
		'default'  				=> wp_specialchars_decode( get_option( 'admin_email' ), ENT_QUOTES ),
		'title'					=> esc_html__( 'From Email', 'hubaga' ),
		'description' 			=> esc_html__( 'Fills the "reply to" field of outgoing emails', 'hubaga' ),
		'section'  				=> 'Notifications',
		'sub_section'  			=> 'Emails',
	),
	'mailer_header_bg'			=> array(
		'type' 					=> 'color',
		'default'  				=>  '#ff5722',
		'title'					=> esc_html__( 'Header Background', 'hubaga' ),
		'section'  				=> 'Notifications',
		'sub_section'  			=> 'Emails',
	),
	'mailer_header_color'		=> array(
		'type' 					=> 'color',
		'default'  				=>  '#fff',
		'title'					=> esc_html__( 'Header Color', 'hubaga' ),
		'section'  				=> 'Notifications',
		'sub_section'  			=> 'Emails',
	),
	'mailer_business_address'	=> array(
		'type' 					=> 'text',
		'default'  				=>  'Business Inc. 357 Westlands, Nairobi 00100',
		'title'					=> esc_html__( 'Business Address', 'hubaga' ),
		'description' 			=> esc_html__( 'Helps escape spam filters.', 'hubaga' ),
		'section'  				=> 'Notifications',
		'sub_section'  			=> 'Emails',
	),
);
