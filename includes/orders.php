<?php
/**
 * Hubaga Orders Functions
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//Require the main order object
require_once hubaga_get_includes_path( 'order-class.php' );

/**
 * Saves an order an existing order
 *
 * Make sure that the order already exists in the database. To create an order instead,
 * call wp_insert_post
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param array $data Order data
 * @return mixed false on fail or H_Order on success
 */
function hubaga_save_order( $data ) {
	$order = new H_Order( $data );
	if( $order->save() ) {
		return $order;
	}
	return false;
}

/**
 * Retrieves an existing order
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param mixed $order order id, data or H_Order instance
 * @return object H_Order instance
 */
function hubaga_get_order( $order ) {
	return new H_Order( $order );
}

/**
 * Retrieves the customer associated with an order
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 * @uses H_Customer
 *
 * @param mixed $order order id, data or H_Order instance
 * @return mixed. object H_Customer instance or false on failure
 */
function hubaga_get_order_customer( $order ) {

	$order = hubaga_get_order( $order );
	if( hubaga_is_order( $order ) ){
		return hubaga_get_customer( $order->customer_id );
	}

	return false;
}

/**
 * Retrieves the email of the customer associated with an order
 *
 * @since Hubaga 1.0.0
 *
 * @uses hubaga_get_order_customer()
 *
 * @param mixed $order order id, data or H_Order instance
 * @return mixed. string email or empty on failure
 */
function hubaga_get_order_customer_email( $order ) {

	$customer = hubaga_get_order_customer( $order );
	if(! $customer ){
		return '';
	}

	return hubaga_get_customer_email( $customer );
}

/**
 * Retrieves the name of the customer associated with an order
 *
 * @since Hubaga 1.0.0
 *
 * @uses hubaga_get_order_customer()
 *
 * @param mixed $order order id, data or H_Order instance
 * @return mixed. string name or empty on failure
 */
function hubaga_get_order_customer_name( $order ) {
	$customer = hubaga_get_order_customer( $order );
	if(! $customer ){
		return '';
	}

	return hubaga_get_customer_name( $customer );
}

/**
 * Retrieves an orders status
 *
 * @since Hubaga 1.0.0
 *
 * @param mixed $order order id, data or H_Order instance
 * @return string
 */
function hubaga_get_order_status( $order ) {
	return hubaga_get_order( $order )->post_status;
}

/**
 * Checks if an order is complete
 *
 * @since Hubaga 1.0.0
 *
 * @param mixed $order order id, data or H_Order instance
 * @return bool
 */
function hubaga_is_order_complete( $order ) {
	return hubaga_get_order( $order )->is_complete();
}

/**
 * Retrieves the order url
 *
 * @since Hubaga 1.0.0
 *
 * @param mixed $order order id, data or H_Order instance
 * @return mixed. string email or empty on failure
 */
function hubaga_get_order_url( $order ) {
	return hubaga_get_order( $order )->order_url();
}

/**
 * Returns an order ID
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order id
 */
function hubaga_get_order_id( $order ) {
	return hubaga_get_order( $order )->ID;
}
//Remove this if you are using strings in your order ids
add_action( 'hubaga_order_id', 'absint' );

/**
 * Returns an order date
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order date
 */
function hubaga_get_order_date( $order ) {
	return hubaga_get_order( $order )->post_date;
}

/**
 * Returns an order currency
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order currency
 */
function hubaga_get_order_currency( $order ) {
	return hubaga_get_order( $order )->currency;
}

/**
 * Checks if an order is free or not
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order total
 */
function hubaga_is_order_free( $order ) {
	return hubaga_get_order( $order )->is_free();
}

/**
 * Returns an order total
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order total
 */
function hubaga_get_order_total( $order ) {
	return hubaga_get_order( $order )->total;
}

/**
 * Returns an order discount total
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order total
 */
function hubaga_get_order_discount_total( $order ){
	return hubaga_get_order( $order )->discount_total;
}

/**
 * Returns an order total before discount is applied
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order total
 */
function hubaga_get_order_pre_discount_total( $order ){
	$total = hubaga_get_order_discount_total( $order ) + hubaga_get_order_total( $order );
	return apply_filters( 'hubaga_order_pre_discount_total', $total, $order );
}

/**
 * Returns the method used to pay an order
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the payment method
 */
function hubaga_get_order_payment_method( $order ){
	return hubaga_get_order( $order )->payment_method;
}

/**
 * Returns a price formatted according to the order
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the order total
 */
function hubaga_get_order_formatted_price( $order, $price ){
	$currency = hubaga_get_order( $order )->currency;
	return hubaga_price( $price, $currency );
}

/**
 * Returns the date an order was paid for
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the payment date
 */
function hubaga_get_order_payment_date( $order ){
	return hubaga_get_order( $order )->payment_date;
}

/**
 * Retrieves the product associated with an order
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param mixed $order order id, data or H_Order instance
 * @return string
 */
function hubaga_get_order_product( $order ) {
	return hubaga_get_order( $order )->product;
}

/**
 * Retrieves the downloads associated with an order
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param mixed $order order id, data or H_Order instance
 * @return array
 */
function hubaga_get_order_downloads( $order ) {
	$product 		= hubaga_get_order_product( $order );
	$_downloads 	= hubaga_get_product_downloads( $product );
	$downloads 		= array();

	foreach( $_downloads as $key => $value ) {
		$downloads[ md5( $key ) ] = $value;
	}

	return apply_filters( 'hubaga_order_downloads', $downloads, $order );
}

/**
 * Retrieves the notes associated with an order
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param mixed $order order id, data or H_Order instance
 * @return array
 */
function hubaga_get_order_notes( $order ) {
	return hubaga_get_order( $order )->notes;
}

/**
 * Retrieves the details associated to an order
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param mixed $order order id, data or H_Order instance
 * @return array
 */
function hubaga_get_order_details( $order, $raw = true ) {

	$order 		= hubaga_get_order( $order );
	if(! hubaga_is_order( $order ) ) {
		return array();
	}

	$currency = hubaga_get_order_currency( $order );
	$details  = array();

	//Order number
	$details[ 'Order Number' ] 		= hubaga_get_order_id( $order );
	$details[ 'Order Date' ] 		= hubaga_get_order_date( $order );
	$details[ 'Product' ] 			= hubaga_get_product_title( hubaga_get_order_product( $order ) );
	$details[ 'Payment Method' ] 	= hubaga_get_gateway_title( hubaga_get_order_payment_method( $order ) );
	$details[ 'Subtotal' ] 			= hubaga_price( hubaga_get_order_pre_discount_total( $order ), $currency );
	$details[ 'Total Discount' ] 	= hubaga_price( hubaga_get_order_discount_total( $order ), $currency );
	$details[ 'Order Total' ] 		= hubaga_price( hubaga_get_order_total( $order ), $currency );

	$details = apply_filters( 'hubaga_order_details', $details, $order );

	if( !$raw ){
		return $details;
	}
	$return = array();
	foreach( $details as $left => $right ){
		$return[] = "<div class='col ps10 pm5'>$left</div> <div class='col ps10 pm5'><strong>$right</strong></div>";
	}
	return $return;

}

/**
 * Checks if an order exists
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param mixed $order order id, data or H_Order instance
 * @return bool Whether or not there is an order matching your information
 */
function hubaga_is_order( $order ) {
	return hubaga_get_order( $order )->is_order();
}

/**
 * Updates an order parameter and saves it to the database
 *
 * @since Hubaga 1.0.0
 *
 * @uses H_Order
 *
 * @param mixed $order order id, data or H_Order instance
 * @param string $key the order field to update
 * @return object The updated order object
 */
function hubaga_update_order( $order, $key, $value ){
	$order = hubaga_get_order( $order );
	$order->$key = $value;
	$order->save();
	return $order;
}


/**
 * Returns all registered order statuses
 */
function hubaga_get_order_statuses(){

	return apply_filters( 'hubaga_order_post_statuses',
			array(
				'pc-pending'    => array(
					'label'                     => _x( 'Pending payment', 'Order status', 'hubaga' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Pending payment <span class="count">(%s)</span>', 'Pending payment <span class="count">(%s)</span>', 'hubaga' ),
				),
				'pc-completed'  => array(
					'label'                     => _x( 'Completed', 'Order status', 'hubaga' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'hubaga' ),
				),
				'pc-cancelled'  => array(
					'label'                     => _x( 'Cancelled', 'Order status', 'hubaga' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'hubaga' ),
				),
				'pc-refunded'   => array(
					'label'                     => _x( 'Refunded', 'Order status', 'hubaga' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'hubaga' ),
				),
				'pc-failed'     => array(
					'label'                     => _x( 'Failed', 'Order status', 'hubaga' ),
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Failed <span class="count">(%s)</span>', 'Failed <span class="count">(%s)</span>', 'hubaga' ),
				),
			)
		);

}

/**
 * Sanitizes an order status and returns the completed order status in
 * case the passed status fails validation
 */
function hubaga_sanitize_order_status( $status ){
	$statuses = hubaga_get_order_statuses();
	if( hubaga_is_array_key_valid( $statuses, $status ) )
		return $status;

	return hubaga_get_completed_order_status();

}

/**
 * Returns the order status for a completed order
 */
function hubaga_get_completed_order_status(){
	return apply_filters( 'hubaga_completed_order_status', 'pc-completed' );
}

/**
 * Returns the order status for an order that is pending payment
 */
function hubaga_get_pending_order_status(){
	return apply_filters( 'hubaga_pending_order_status', 'pc-pending' );
}

/**
 * Returns the order status for an order that is cancelled
 */
function hubaga_get_cancelled_order_status(){
	return apply_filters( 'hubaga_cancelled_order_status', 'pc-cancelled' );
}

/**
 * Returns the order status for an order that has failed payment
 */
function hubaga_get_failed_order_status(){
	return apply_filters( 'hubaga_failed_order_status', 'pc-failed' );
}

/**
 * Returns the order status for an order that has been refunded
 */
function hubaga_get_refunded_order_status(){
	return apply_filters( 'hubaga_refunded_order_status', 'pc-refunded' );
}

/**
 * Returns orders post type labels
 */
function hubaga_get_order_post_type_labels(){
	return apply_filters(
		'hubaga_order_post_type_labels',
		array(
			'name'                  => __( 'Orders', 'hubaga' ),
			'singular_name'         => _x( 'Order', 'shop_order post type singular name', 'hubaga' ),
			'add_new'               => __( 'Add order', 'hubaga' ),
			'add_new_item'          => __( 'Add new order', 'hubaga' ),
			'edit'                  => __( 'Edit', 'hubaga' ),
			'edit_item'             => __( 'Edit order', 'hubaga' ),
			'new_item'              => __( 'New order', 'hubaga' ),
			'view'                  => __( 'View order', 'hubaga' ),
			'view_item'             => __( 'View order', 'hubaga' ),
			'search_items'          => __( 'Search orders', 'hubaga' ),
			'not_found'             => __( 'No orders found', 'hubaga' ),
			'not_found_in_trash'    => __( 'No orders found in trash', 'hubaga' ),
			'parent'                => __( 'Parent orders', 'hubaga' ),
			'menu_name'             => _x( 'Orders', 'Admin menu name', 'hubaga' ),
			'filter_items_list'     => __( 'Filter orders', 'hubaga' ),
			'items_list_navigation' => __( 'Orders navigation', 'hubaga' ),
			'items_list'            => __( 'Orders list', 'hubaga' ),
		) );
}

/**
 * Returns orders post type details
 */
function hubaga_get_order_post_type_details(){
	return apply_filters(
		'hubaga_order_post_type_details',
		array(
			'labels'              => hubaga_get_order_post_type_labels(),
			'description'         => __( 'Stores site orders.', 'hubaga' ),
			'public'              => false,
			'show_ui'             => true,
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => hubaga_get_order_post_type_menu_name(),
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => false,
			'has_archive'         => false,
		));
}

/**
 * Returns orders post type
 */
function hubaga_get_order_post_type(){
	return hubaga()->order_post_type;
}

/**
 * Returns orders post type menu name
 */
function hubaga_get_order_post_type_menu_name(){
	return apply_filters( 'hubaga_order_post_type_menu_name', hubaga_get_product_post_type_menu_name());
}

