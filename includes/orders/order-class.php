<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order Class.
 *
 * Provides an interface for dealing with orders
 *
 * @class    H_Order
 * @version  1.0.0
 */
class H_Order {

	//Order id
	protected $ID = null;

	/**
	 * Order information
	 * @since 1.0.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * Order constructor. Loads order data.
	 * @param mixed $order Order ID, array, or H_Order instance
	 * @param string $transaction_id Optionally get an order by its transaction id
	 */
	public function __construct( $order = false, $transaction_id = false ) {

		if ( $order instanceof H_Order ) {
			$this->init( $order->data );
			return;
		} elseif ( is_array( $order ) ) {
			$this->init( $order );
			return;
		}

		//Try fetching the order by its post id
		if ( ! empty( $order ) && is_numeric( $order ) ) {
			$order = absint( $order );
			$data = self::get_data_by( 'id', $order );

			if ( $data ) {
				$this->init( $data );
				return;
			}
		}



		//Try fetching the order by its transaction id
		if ( $transaction_id && is_string( $transaction_id ) ) {
			$data = self::get_data_by( 'transaction_id', $transaction_id );
			if ( $data ) {
				$this->init( $data );
				return;
			}
		}

		//If we are here then the order does not exist
		$this->init( array() );
	}

	/**
	 * Sets up object properties
	 *
	 * @param array $data contains order details
	 */
	public function init( $data ) {
		$data 				= $this->sanitize_order_data( $data );
		$this->data 		= $data;
		$this->ID 			= $data['ID'];
	}

	/**
	 * Fetch an order from the db/cache
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $field The field to query against: 'ID', 'transaction_id'
	 * @param string|int $value The field value
	 * @return array|false array of order details on success. False otherwise.
	 */
	public function get_data_by( $field, $value ) {
		global $wpdb;

		// 'ID' is an alias of 'id'.
		if ( 'ID' === $field ) {
			$field = 'id';
		}

		if ( 'id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) )
				return false;
			$value = intval( $value );
			if ( $value < 1 )
				return false;
		} else {
			$value = trim( $value );
		}

		if ( !$value )
			return false;

		switch ( $field ) {
			case 'id':
				$order_id = $value;
				$db_field = 'ID';
				$join	  = false;
				break;
			case 'transaction_id':
				$order_id = wp_cache_get( $value, 'pc_transaction_ids' );
				$meta_key = $wpdb->prepare( 'meta_key= %s ', '_order_transaction_id' );
				$db_field = "$meta_key AND meta_value";
				$join	  = true;
				break;
			default:
				return false;
		}

		if ( false !== $order_id ) {
			if ( $order = wp_cache_get( $order_id, 'H_Orders' ) )
				return $order;
		}

		$sql 	= "SELECT * FROM {$wpdb->posts}";

		if( $join ) {
			$sql 	.= " INNER JOIN {$wpdb->postmeta} ON ( ID = post_id ) ";
		}

		$sql 	.= " WHERE $db_field = %s LIMIT 1";
		$sql 	= $wpdb->prepare( $sql, $value );

		$_order = $wpdb->get_row( $sql );
		$post_type = hubaga_get_order_post_type();

		//Check if this is an order
		if ( !$_order || $_order->post_type != $post_type  )
			return false;

		//So the order exists; great
		$order = array();
		$order['ID']					= $_order->ID;
		$order['post_date'] 			= $_order->post_date;
		$order['post_modified'] 		= $_order->post_modified;
		$order['post_status'] 			= $_order->post_status;
		$order['currency']				= get_post_meta( $_order->ID, '_order_currency', true );
		$order['transaction_id']		= get_post_meta( $_order->ID, '_order_transaction_id', true );
		$order['total'] 				= get_post_meta( $_order->ID, '_order_total', true );
		$order['discount_total'] 		= get_post_meta( $_order->ID, '_order_discount_total', true );
		$order['customer_id'] 			= get_post_meta( $_order->ID, '_order_customer_id', true );
		$order['product'] 				= get_post_meta( $_order->ID, '_order_product', true );
		$order['payment_method']		= get_post_meta( $_order->ID, '_order_payment_method', true );
		$order['payment_date']			= get_post_meta( $_order->ID, '_order_payment_date', true );
		$order['notes']					= get_post_meta( $_order->ID, '_order_notes', true );
		$order['coupon']				= get_post_meta( $_order->ID, '_order_coupon', true );
		$order['platform']				= get_post_meta( $_order->ID, '_order_platform', true );
		$order['browser']				= get_post_meta( $_order->ID, '_order_browser', true );

		//Update the cache with out data
		wp_cache_add( $order['ID'], $order, 'H_Orders' );
		if ( $order['transaction_id'] ) {
			wp_cache_add( $order['transaction_id'], $_order->ID, 'pc_transaction_ids' );
		}

		return $this->sanitize_order_data( $order );
	}

	/**
	 * Sanitizes order data
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array the sanitized data
	 */
	public function sanitize_order_data( $data ) {

		$return = array(
			'ID'                          => null,
			'post_date'                   => null,
			'post_modified'               => null,
			'payment_date'                => null,
			'post_status'                 => hubaga_get_pending_order_status(),
			'payment_method'              => null,
			'product'                 	  => null,
			'customer_id'              	  => null,
			'discount_total'        	  => 0,
			'total'                 	  => 0,
			'transaction_id'              => null,
			'currency'              	  => hubaga_get_currency(),
			'notes'              	  	  => array(),
			'coupon'              	  	  => 0,
			'browser'              	  	  => 'Other',
			'platform'              	  => 'Other',
			'country'              	  	  => 'Other',
		);


		//Arrays only please
		if (! is_array( $data ) )
			return $return;

		if ( hubaga_is_array_key_valid( $data, 'ID', 'is_numeric' ) )
			$return['ID'] = absint( $data['ID'] );

		if ( hubaga_is_array_key_valid( $data, 'post_date', 'is_string'  ))
			$return['post_date'] = $data['post_date'];

		if ( hubaga_is_array_key_valid( $data, 'post_modified', 'is_string' ))
			$return['post_modified'] = $data['post_modified'];

		if ( hubaga_is_array_key_valid( $data, 'payment_date', 'is_string' ))
			$return['payment_date'] = $data['payment_date'];

		if ( hubaga_is_array_key_valid( $data, 'post_status', 'is_string' ))
			$return['post_status'] = $data['post_status'];

		if ( hubaga_is_array_key_valid( $data, 'payment_method' , 'is_string' ))
			$return['payment_method'] = $data['payment_method'];

		if ( hubaga_is_array_key_valid( $data, 'product', 'is_numeric' ))
			$return['product'] =  $data['product'] ;

		if ( hubaga_is_array_key_valid( $data, 'customer_id', 'is_numeric' ))
			$return['customer_id'] = $data['customer_id'];

		if ( hubaga_is_array_key_valid( $data, 'discount_total', 'is_numeric' ))
			$return['discount_total'] = floatval( $data['discount_total'] );

		if ( hubaga_is_array_key_valid( $data, 'total', 'is_numeric' ))
			$return['total'] = floatval( $data['total'] );

		if ( hubaga_is_array_key_valid( $data, 'transaction_id', 'is_string' ))
			$return['transaction_id'] = $data['transaction_id'];

		if ( hubaga_is_array_key_valid( $data, 'currency', 'is_string' ))
			$return['currency'] = $data['currency'];

		if ( hubaga_is_array_key_valid( $data, 'notes', 'is_array' ))
			$return['notes'] = $data['notes'];

		if ( hubaga_is_array_key_valid( $data, 'coupon', 'is_numeric' ))
			$return['coupon'] = $data['coupon'];

		if ( hubaga_is_array_key_valid( $data, 'platform', 'is_string' ))
			$return['platform'] = $data['platform'];

		if ( hubaga_is_array_key_valid( $data, 'browser', 'is_string' ))
			$return['browser'] = $data['browser'];

		if ( hubaga_is_array_key_valid( $data, 'country', 'is_string' ))
			$return['country'] = $data['country'];

		return $return;
	}

	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the given order field is set.
	 */
	public function __isset( $key ) {
		return isset( $this->data[$key] ) && $this->data[$key] != null;
	}

	/**
	 * Magic method for accessing custom fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key Order meta key to retrieve.
	 * @return mixed Value of the given order meta key (if set). If `$key` is 'id', the order ID.
	 */
	public function __get( $key ) {

		if ( $key == 'id' ) {
			$key = 'ID';
		}

		if( $key == 'total' ) {

			$value = hubaga_format_price( $this->data['total'], $this->data['currency'] );

		} elseif( $key == 'discount_total' ){

			$value = hubaga_format_price( $this->data['discount_total'], $this->data['currency'] );

		}else {

			$value = $this->data[$key];

		}

		return apply_filters( "hubaga_order_{$key}", $value, $this );
	}

	/**
	 * Magic method for setting custom order fields.
	 *
	 * This method does not update custom fields in the database. It only stores
	 * the value on the H_Order instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function __set( $key, $value ) {

		if ( 'id' == strtolower( $key ) ) {

			$this->ID = $value;
			$this->data['ID'] = $value;
			return;

		}

		if ( $key == 'post_status' ) {
			$this->update_status( $value );
			return;
		}

		$this->data[$key] = $value;

	}

	/**
	 * Saves the current order to the database
	 * Make sure that the order already exists in the database.
	 * To create a new order, call wp_insert_post first
	 *
	 * To update an order status; call the update_status method of this class
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function save() {

		//Prepare the data
		$data = $this->sanitize_order_data( $this->data );
		if(! $data[ 'ID' ] )
			return false;

		$id = $data[ 'ID' ];
		unset( $data['ID'] );
		unset( $data['post_modified'] );
		unset( $data['post_date'] );
		unset( $data['post_status'] );

		foreach ( $data as $key => $value ) {
			$key = trim($key);
			$key = "_order_$key";
			update_post_meta( $id, $key, $value );
		}

		//Update the cache with our new data
		wp_cache_delete( $id, 'H_Orders' );
		wp_cache_add( $id, $this->data, 'H_Orders' );

		if ( $this->data['transaction_id'] ) {
			wp_cache_delete(  $this->data['transaction_id'] , 'pc_transaction_ids' );
			wp_cache_add( $this->data['transaction_id'], $id , 'pc_transaction_ids' );
		}

		return true;
	}

	/**
	 * Determine whether the order exists in the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if order exists in the database, false if not.
	 */
	public function exists() {
		return null != $this->ID;
	}

	/**
	 * Checks whether an order is refundable or not
	 *
	 * @return bool
	 */
	public function is_refundable() {
		return hubaga_get_payment_processor()->is_refundable( $this );
	}

	/**
	 * Refunds an order
	 *
	 * @return bool
	 */
	public function refund( $amount = null, $reason = '' ) {
		return hubaga_get_payment_processor()->refund( $this, $amount );
	}

	/**
	 * Updates an order status before saving it to the database
	 *
	 * @return bool
	 */
	public function update_status( $new_status ) {

		//The order already has the provided status
		if( !$new_status OR $this->post_status == $new_status ) {
			return false;
		}

		$old_status = $this->post_status;
		$fields = array(
			'post_status' 	=> $new_status,
			'ID' 			=> $this->ID,
		);

		if( wp_update_post( $fields  ) ) {
			$this->data[ 'post_status' ] = $new_status;

			$new_status = str_ireplace( 'pc-', '', $new_status );

			$this->add_note( __( "Order marked as $new_status.", 'hubaga' ) );
			$this->save();

			//Throw the right hook
			do_action( "hubaga_order_{$new_status}", $this, $old_status, $this->data[ 'post_status' ] );

			//Update the cache
			wp_cache_delete( $this->ID, 'H_Orders' );
			wp_cache_add( $this->ID, $this->data, 'H_Orders' );

			return true;
		}

		return false;
	}

	/**
	 * Checks whether an order is free or not
	 *
	 * @return bool
	 */
	public function is_free() {
		return apply_filters( 'hubaga_is_order_free', !$this->is_payable(), $this );
	}

	/**
	 * Checks whether an order is payable or not
	 *
	 * @return bool
	 */
	public function is_payable() {
		$is_payable = ( $this->total ) > 0;
		return apply_filters( 'hubaga_is_order_payable', $is_payable, $this );
	}

	/**
	 * Checks whether an order is complete
	 *
	 * @return bool
	 */
	public function is_complete() {
		return $this->post_status == hubaga_get_completed_order_status();
	}

	/**
	 * Checks whether an order is payable or not
	 *
	 * @return bool
	 */
	public function order_url() {
		$url = add_query_arg( 'view_order', $this->ID, hubaga_get_account_url() );
		return apply_filters( "hubaga_order_url", $url, $this->ID, $this );
	}

	/**
	 * Checks whether this is a real order and is saved to the database
	 *
	 * @return bool
	 */
	public function is_order() {
		$is_order = ( $this->exists() && get_post_type( $this->ID ) == hubaga_get_order_post_type() );
		return apply_filters( "hubaga_is_order", $is_order, $this );
	}

	/**
	 * Adds a note to an order
	 *
	 * @return array an array of notes
	 */
	public function add_note( $note, $author = 'Hubabot' ) {

		// Indirect modification of overloaded property has no effect
		$notes 		= $this->notes;
		$notes[] 	= array(
			'content' => esc_html($note),
			'author'  => esc_html($author),
			'date'    => gmdate( 'D, d M Y H:i:s e' ),
		);

		$this->notes= $notes;
	}

	/**
	 * Fetches all order data
	 *
	 * @return array an array of order data
	 */
	public function get_all_data(){
		return $this->data;
	}
}
