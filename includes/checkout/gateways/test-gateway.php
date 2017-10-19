<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hubaga Test Payment Handler
 *
 * No actual payments will take place
 */
class H_Test_Gateway {

	/*
	 * This methods meta data such as name, title, etc.
	 * This data is shown on the frontend and debugin logs; so no putting sensitive information here
	 */
	public $meta = array();

	/**
	 * Class constructor
	 * @return void
	 */
	public function __construct() {

		/* Only the id and title are required */

		//Gateway id. Should be unique
		$this->meta['id'] = 'test';

		//Gateway title
		$this->meta['title'] = 'Test Gateway';

		//Gateway button text
		$this->meta['button_text'] = 'Test Gateway';

		//Gateway description. Shown in administration areas
		$this->meta['description'] = 'Orders will be instantly marked as complete.';

		//Gateway url. Link to this gateways homepage
		$this->meta['url'] = 'https://hubaga.com/hubaga/';

		//Gateway checkout page description
		$this->meta['checkout_description'] = 'This gateway should ONLY be used when testing. Payments will instantly be marked as complete.';

		//Gateway author
		$this->meta['author'] = 'Picocodes';

		//Gateway author url
		$this->meta['author_url'] = 'https://hubaga.com/';
	}

	/**
	 * Process the payment and return the result.
	 * @param  object $order
	 * @return array
	 */
	public function process_payment( $order ) {

		$order = hubaga_get_order( $order );
		$order->payment_date   = gmdate( 'D, d M Y H:i:s e' ); //GMT time
		$order->update_status( hubaga_get_completed_order_status() );

		return array(
			'action'   => 'complete',
		);

	}

	/**
	 * Handles order refunds
	 */
	public function refund( $order, $amount = null, $reason = '' ) {
		$order = hubaga_get_order( $order );
		return $order->update_status( hubaga_get_refunded_order_status() );
	}

}
