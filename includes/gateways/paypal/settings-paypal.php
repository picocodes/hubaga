<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for PayPal Gateway.
 */
return array(
	'paypal_title' => array(
		'title'       => __( 'Title', 'hubaga' ),
		'type'        => 'text',
		'description' => __( 'The text to show on the checkout button.', 'hubaga' ),
		'default'     => __( 'Pay Via PayPal', 'hubaga' ),
	),
	'paypal_email' => array(
		'title'       => __( 'PayPal email', 'hubaga' ),
		'type'        => 'email',
		'description' => __( 'The email address associated with your PayPal account.', 'hubaga' ),
		'default'     => get_option( 'admin_email' ),
		'placeholder' => 'you@youremail.com',
	),
	'paypal_pdt_token' => array(
		'title'       => __( 'PDT Token', 'hubaga' ),
		'type'        => 'text',
		'description' => sprintf( 
			__( 'This is required before you can process transactions via PayPal. %s Get your token. %s', 'hubaga' ),
			'<a href="https://www.paypal.com/cgi-bin/customerprofileweb?cmd=_profile-website-payments">',
			'</a>'),
	),
	'paypal_invoice_prefix' => array(
		'title'       => __( 'Invoice prefix', 'hubaga' ),
		'type'        => 'text',
		'description' => __( 'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'hubaga' ),
		'default'     => 'H-',
	),
	'paypal_api_details' => array(
		'title'       => __( 'API credentials', 'hubaga' ),
		'type'        => 'title',
		'description' => sprintf( __( 'You will have to enter your api credentials before you can receive payments via PayPal. Learn how to access your <a href="%s">PayPal API Credentials</a>.', 'hubaga' ), 'https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/#creating-an-api-signature' ),
	),
	'paypal_api_username' => array(
		'title'       => __( 'API username', 'hubaga' ),
		'type'        => 'text',
		'default'     => '',
		'placeholder' => __( 'jb-us-seller_api1.paypal.com', 'hubaga' ),
	),
	'paypal_api_password' => array(
		'title'       => __( 'API password', 'hubaga' ),
		'type'        => 'password',
		'default'     => '',
		'placeholder' => __( 'WX4WTU3S8MY44S7F', 'hubaga' ),
	),
	'paypal_api_signature' => array(
		'title'       => __( 'API signature', 'hubaga' ),
		'type'        => 'text',
		'default'     => '',
		'placeholder' => __( 'AFcWxV21C7fd0v3bYYYRCpSSRl31A7yDhhsPUU2XhtMoZXsWHFxu-RWy', 'hubaga' ),
	),
);
