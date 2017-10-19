<?php
/**
 * Renders the extensions page
 *
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'stripe' 			=> array(
		'price' 		=> '$49.00',
		'title' 		=> esc_html__( 'Stripe Gateway', 'hubaga' ),
		'description' 	=> esc_html__( 'Accept credit cards directly on your store using stripe.', 'hubaga' ),
	),
	'all-downloads' 	=> array(
		'price' 		=> '$49.00',
		'title' 		=> esc_html__( 'All Downloads', 'hubaga' ),
		'description' 	=> esc_html__( 'Easily create products that give your customers access to all your store downloads.', 'hubaga' ),
	),
	'pdf-invoices' 		=> array(
		'price' 		=> '$49.00',
		'title' 		=> esc_html__( 'PDF Invoices', 'hubaga' ),
		'description' 	=> esc_html__( 'A Fully automatted PDF invoicing addon for Hubaga.', 'hubaga' ),
	),
	'mailchimp' 		=> array(
		'price' 		=> '$49.00',
		'title' 		=> esc_html__( 'Mailchimp', 'hubaga' ),
		'description' 	=> esc_html__( 'Subscribe your customers to mailchimp lists so you can remarket to them later.', 'hubaga' ),
	),
	'slack' 			=> array(
		'price' 		=> '$49.00',
		'title' 		=> esc_html__( 'Slack', 'hubaga' ),
		'description' 	=> esc_html__( 'Notifies your slack groups and channels whenever a store event fires. In realtime.', 'hubaga' ),
	),
	'twillio' 			=> array(
		'price' 		=> '$49.00',
		'title' 		=> esc_html__( 'Twillio', 'hubaga' ),
		'description' 	=> esc_html__( 'Instantly notifies you via SMS whenever a store event fires.', 'hubaga' ),
	),
);