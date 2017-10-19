<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hubaga Paypal Payment Handler
 *
 */
class H_PayPal_Gateway {

	/*
	 * This methods meta data such as name, title, etc.
	 * This data is shown on the frontend and debuging logs
	 */
	public $meta = array();

	/**
	 * Class constructor
	 * @return void
	 */
	public function __construct(){

		$this->meta = array(
				'id' 			=> 'paypal',
				'title' 		=> 'PayPal',
				'button_text' 	=> __( 'Pay Via PayPal', 'hubaga' ),
				'description' 	=> 'Receive payments securely via paypal standard.',
				'url' 			=> 'https://hubaga.com/docs/paypal_standard/',
				'author' 		=> 'Hubaga',
				'author_url' 	=> 'https://hubaga.com/',
		);

		$checkout_title = hubaga_get_option( 'paypal_title' );
		if( $checkout_title ) {
			$this->meta['button_text'] = trim( hubaga_clean( $checkout_title ) );
		}

		add_action( 'wp_ajax_nopriv_hubaga_validate_paypal_ipn', array( $this, 'process_ipn' ) );
		add_action( 'hubaga_before_checkout_page_html', array( $this, 'process_pdt' ) );
	}

	/**
	 * Process the payment and return the result.
	 * @param  object $order
	 * @return array
	 */
	public function process_payment( $order ) {

		//Redirect the user to paypal to complete the payment
		return array(
			'action'   	=> 'redirect',
			'url' 		=> $this->get_request_url( $order ),
		);
	}

	/**
	 * Get the PayPal request URL used to pay an order
	 * @return string
	 */
	protected function get_request_url( $order ) {
		return $this->get_paypal_base_url() . http_build_query( $this->get_paypal_args( $order ), '', '&' );
	}

	/**
	 * Return paypal base request url
	 * @return string
	 */
	public function get_paypal_base_url() {
		return hubaga_is_sandbox() ?
			'https://www.sandbox.paypal.com/cgi-bin/webscr?test_ipn=1&' :
			'https://www.paypal.com/cgi-bin/webscr?';
	}

	/**
	 * Order specific arguments passed over to paypal
	 * @param  H_Order $order
	 * @return array
	 */
	protected function get_paypal_args( $order ) {

		$order 		= hubaga_get_order( $order );
		$product	= hubaga_get_product( hubaga_get_order_product( $order ));

		return apply_filters(
			'hubaga_paypal_args',
			array(
				'cmd'           => '_xclick',
				'notify_url'    => hubaga()->ajax_url . '?action=hubaga_validate_paypal_ipn',				
				'amount'   		=> hubaga_get_order_total( $order ),		
				'item_name'     => substr( $product->post_title, 0, 127 ),
				'item_number'   => $product->ID,
				'currency_code' => hubaga_get_order_currency( $order ),
				'custom'        => $order->id,
				'invoice'       => substr( hubaga_get_option( 'paypal_invoice_prefix' ) . $order->id, 0, 127 ),
				'business'      => sanitize_email( hubaga_get_option( 'paypal_email' ) ),
				'no_note'       => 1,
				'no_shipping'   => 1,
				'charset'       => 'utf-8',
				'return'        => hubaga_get_checkout_url() . '?hubaga_paypal=1&utm_nooverride=1&product=' . $product->ID,
				'cancel_return' => hubaga_get_checkout_url() . '?utm_nooverride=1&product=' . $product->ID,
				'email'         => substr( hubaga_get_order_customer_email( $order ), 0, 127  ),
			),
		$order );

	}

	/**
	 * Handles order refunds
	 */
	public function refund_order( $order, $amount = null, $reason = '' ) {

		$order = hubaga_get_order( $order );
		$request = array(
			'VERSION'       => '84.0',
			'SIGNATURE'     => $this->api_signature(),
			'USER'          => $this->api_username(),
			'PWD'           => $this->api_password(),
			'METHOD'        => 'RefundTransaction',
			'TRANSACTIONID' => $order->transaction_id,
			'NOTE'          => html_entity_decode( substr( $reason, 0, 127 ), ENT_NOQUOTES, 'UTF-8' ),
			'REFUNDTYPE'    => 'Full',
		);

		if ( ! is_null( $amount ) ) {
			$request['AMT']          = number_format( $amount, 2, '.', '' );
			$request['CURRENCYCODE'] = $order->currency;
			$request['REFUNDTYPE']   = 'Partial';
		}

		$raw_response = wp_safe_remote_post(
			$this->api_url(),
			array(
				'method'      => 'POST',
				'body'        => $request,
				'timeout'     => 70,
				'user-agent'  => hubaga()->user_agent,
				'httpversion' => '1.1',
			)
		);

		if ( empty( $raw_response['body'] ) OR is_wp_error( $raw_response ) ) {
			$order->add_note( __( 'Unable to process the refund. How about you manually refund it in you PayPal dashboard and mark the status as refunded?!', 'hubaga' ) );
			$order->save();
			return false;
		}

		$order->order_status = hubaga_get_refunded_order_status();
		return $order->save();
	}

	/**
	 * Returns the api url
	 * @return string
	 */
	protected function api_url() {
		return hubaga_is_sandbox() ?
			'https://api-3t.sandbox.paypal.com/nvp' :
			'https://api-3t.paypal.com/nvp';
	}

	/**
	 * Returns the api signature
	 * @return string
	 */
	protected function api_signature() {
		return hubaga_get_option( 'paypal_api_signature' );
	}

	/**
	 * Returns the api username
	 * @return string
	 */
	protected function api_username() {
		return hubaga_get_option( 'paypal_api_username' );
	}

	/**
	 * Returns the api password
	 * @return string
	 */
	protected function api_password() {
		return hubaga_get_option( 'paypal_api_password' );
	}

	/**
	 * Returns the PDT identity_token
	 * @return string
	 */
	protected function identity_token() {
		return hubaga_get_option( 'paypal_pdt_token' );
	}

	/**
	 * Whether or not we should process this PDT/IPN request
	 * @return bool
	 */
	protected function should_process( $order ) {
		return hubaga_is_order( $order ) && !hubaga_is_order_complete( $order );
	}

	/**
	 * Validates PayPal receiver and amount paid
	 * @return bool|string
	 */
	protected function validate_receiver_amount( $order, $transaction_result, $type = 'PDT' ) {

		//Validate the paypal business
		$_business 		= sanitize_email( hubaga_get_option( 'paypal_email' ));
		$business		= sanitize_email( $this->clean( $transaction_result['business'] ));

		if( $_business != $business ) {
			return sprintf(
				__( '%s Validation error: Amount was paid to %s instead of %s.', 'hubaga' ),
				 $type,
				 $business,
				 $_business
			);
		}

		//Validate the amount paid
		$valid_amount = apply_filters( 'hubaga_paypal_amount_validate', null, $transaction_result, $order, $type );

		if( null != $valid_amount ){
			return $valid_amount;
		}

		$amount  		   = $this->clean( $transaction_result['mc_gross']);
		$amount 		   = hubaga_format_price( $amount, $order->currency );
		if( $order->total != $amount ) {
			return sprintf(
				__( '%s Validation error: PayPal amounts do not match.  You paid  %s instead of %s.', 'hubaga' ),
				 $type,
				 $amount,
				 $order->total
			);
		}

		return true;

	}

	/**
	 * Processes payment validation errors
	 * @return bool
	 */
	protected function payment_validation_error( $order, $error ) {
		$order->add_note( $error );
		$order->save();
		hubaga_add_error( $error );
		hubaga()->checkout_form = hubaga()->template->get_checkout_html( $order->product );
	}

	/**
	 * Updates the order payment details
	 * @return void
	 */
	protected function update_order_payment_details( $order, $transaction_result ) {

		$customer = hubaga_get_order_customer( $order );

		$order->add_note( __( 'Payment confirmed!', 'hubaga' ) );
		$order->transaction_id = $this->clean($transaction_result['txn_id']);
		$order->payment_date   = gmdate( 'D, d M Y H:i:s e' ); //GMT time

		//Transaction fee
		if ( ! empty( $transaction_result['mc_fee'] ) ) {
			$order->add_note( sprintf( __( 'PayPal Transaction Fee - %s.', 'hubaga' ), $transaction_result['mc_fee'] ) );
		}

		//Customer first name
		if ( ! empty( $transaction_result['first_name'] ) && empty( $customer->first_name ) ) {
			update_user_meta( $customer->ID, 'first_name', $this->clean( $transaction_result['first_name'] ));
		}

		//Customer last name
		if ( ! empty( $transaction_result['last_name'] ) && empty( $customer->last_name ) ) {
			update_user_meta( $customer->ID, 'last_name', $this->clean( $transaction_result['last_name'] ) );
		}

		//Country
		if ( ! empty( $transaction_result['address_country'] ) ) {
			$order->country = $this->clean( $transaction_result['address_country']);
		}

		//Update the order status
		$order->update_status( hubaga_get_completed_order_status() );
		$order->save();
	}

	/**
	 * Cleans a string
	 * @return string
	 */
	protected function clean( $string ) {
		return hubaga_clean( stripslashes( $string ) );
	}

	/**
	 * Processes PDT Requests
	 * @return string
	 */
	public function process_pdt() {
		
		if ( empty( $_REQUEST['cm'] ) || empty( $_REQUEST['tx'] ) ) {
			return; //Nothing to do
		}

		$order = hubaga_get_order( $this->clean( $_REQUEST['cm'] ) );

		//If this is not a  valid order; or the order is complete; abort
		if ( ! $this->should_process( $order ) ) {
			return;
		}

		//Fetch payment info from PayPal
		$transaction_result = $this->validate_pdt_transaction();
		if( !$transaction_result ){
			$error = __( 'Error: Unable to connect to PayPal for payment confirmation.', 'hubaga' );
			return $this->payment_validation_error( $order, $error );
		}

		//Confirm if the Payment was completed
		$payment_status = strtolower( $this->clean( $transaction_result['payment_status'] ));
		if ( 'completed' !== $payment_status ) {
			return $this->payment_validation_error( $order, $transaction_result['pending_reason'] );
		}

		//Make sure the amount was paid to the correct recipient
		$valid = $this->validate_receiver_amount( $order, $transaction_result );
		if( is_string($valid) ){
			return $this->payment_validation_error( $valid );
		}

		//Great. Update order payment details
		$this->update_order_payment_details( $order, $transaction_result );

		//Then display it to the user
		hubaga()->order_completed = true;

		//Great. Display the order to our customer so that they can download their files
		hubaga()->checkout_form = hubaga()->template->get_view_order_html( $order );

	}

	/**
	 * Validate a PDT transaction to ensure its authentic.
	 * @param  string $transaction
	 * @return bool|array False or result array
	 */
	protected function validate_pdt_transaction() {
		$pdt = array(
			'body'	  => array(
				'cmd' => '_notify-synch',
				'tx'  => $this->clean( $_REQUEST['tx'] ),
				'at'  => $this->identity_token(),
			),
			'timeout' 		=> 60,
			'httpversion'   => '1.1',
			'user-agent'	=> hubaga()->user_agent,
		);

		// Post back to get a response.
		$response = wp_safe_remote_post( $this->get_paypal_base_url(), $pdt );

		if ( is_wp_error( $response ) || ! strpos( $response['body'], "SUCCESS" ) === 0 ) {
			return false;
		}

		// Parse transaction result data
		$transaction_result  = array_map( 'hubaga_clean', array_map( 'urldecode', explode( "\n", $response['body'] ) ) );
		$transaction_results = array();

		foreach ( $transaction_result as $line ) {
			$line                            = explode( "=", $line );
			$transaction_results[ $line[0] ] = isset( $line[1] ) ? $line[1] : '';
		}

		if ( ! empty( $transaction_results['charset'] ) && function_exists( 'iconv' ) ) {
			foreach ( $transaction_results as $key => $value ) {
				$transaction_results[ $key ] = iconv( $transaction_results['charset'], 'utf-8', $value );
			}
		}

		return $transaction_results;
	}

	/**
	 * Processes IPN requests
	 * @param  object $order
	 * @return array
	 */
	public function process_ipn_request() {

		if ( empty( $_POST['custom'] )) {
			wp_die(); //Nothing to do here
		}

		$order = hubaga_get_order( $this->clean( $_REQUEST['custom'] ) );

		//If this is not a  valid order; or the order is complete; abort
		if ( ! $this->should_process( $order ) ) {
			wp_die();
		}

		//Validate the request
		if(! $this->validate_ipn_request( $order ) ) {
			wp_die();
		}

		//Parse transaction result data
		$posted = wp_unslash( $_POST );

		//Normalize the charset
		if ( ! empty( $posted['charset'] ) && function_exists( 'iconv' ) ) {
			foreach ( $posted as $key => $value ) {
				$posted[ $key ] = iconv( $posted['charset'], 'utf-8', $value );
			}
		}

		// If this is a sandbox payment; mark it as complete
		$posted['payment_status'] = strtolower( $posted['payment_status'] );
		if ( isset( $posted['test_ipn'] ) && 1 == $posted['test_ipn'] && 'pending' == $posted['payment_status'] ) {
			$posted['payment_status'] = 'completed';
		}

		//Payment complete. Nice
		if ( 'completed' === $payment_status ) {

			//Validate payment details
			$valid = $this->validate_receiver_amount( $order, $transaction_result, 'IPN' );
			if( is_string($valid) ){
				$this->payment_validation_error( $valid );
				wp_die();
			}

			//Great. Update order payment details
			$this->update_order_payment_details( $order, $transaction_result );
			wp_die();

		}

		//Failed payment
		if( in_array( $payment_status, array( 'failed', 'denied', 'expired', 'voided' ) ) ){

			$order->add_note( sprintf( __( 'Order marked as failed after PayPal IPN returned the status: %s.', 'hubaga' ), $payment_status ) );
			$order->update_status( hubaga_get_failed_order_status() );
			$order->save();
			wp_die();

		}

		//Pending payment
		if ( 'pending' === $payment_status ) {
			$order->add_note( $posted['pending_reason'] );
			$order->save();
		}

		wp_die();

	}

	/**
	 * Validate IPN request
	 * @param  string $transaction
	 * @return bool
	 */
	protected function validate_ipn_request( $order ) {

		$order = hubaga_get_order();
		$order->add_note( __( 'Validating IPN response', 'hubaga' ) );

		// Get received values from post data
		$req        = wp_unslash( $_POST );
		$req['cmd'] = '_notify-validate';

		$url = 'https://www.paypal.com/cgi-bin/webscr';
		if( array_key_exists( $req['test_ipn'] ) && 1 == (int) $req['test_ipn'] ) {
			$url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}

		// Send back post vars to paypal
		$params = array(
			'body'        => $req,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => hubaga()->user_agent,
		);

		// Post back to get a response.
		$response = wp_safe_remote_post( $url, $params );

		// Check to see if the request was valid.
		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
			$order->add_note( __( 'IPN response successfully verified', 'hubaga' ) );
			$order->save();
			return true;
		}

		$order->add_note( __( 'Unable to verify IPN response', 'hubaga' ) );
		$order->save();

		return false;

	}

	/**
	 * Gateway Settings
	 * @param  object $order
	 * @return array
	 */
	public function init_settings() {

		$settings = include hubaga_get_includes_path() . 'checkout/gateways/paypal/settings-paypal.php';

		foreach( $settings as $id => $details ) {
			$details['sub_section'] = 'PayPal';
			$details['section'] 	= 'Gateways';
			$details['id'] 			= $id;
			hubaga_elementa()->queue_control( $details );
		}

	}

}
