<?php
/**
 * Hubaga Customer Functions
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//Require the main customer object
require_once hubaga_get_includes_path( 'customer-class.php' );

/**
 * Retrieves a given customer
 * @since 1.0.0
 * @return object or false if the customer does not exist
 */
function hubaga_get_customer( $customer ){
	
	$customer = new H_Customer( $customer );
	if ( $customer->exists() )
		return $customer;
	
	return false;
}

/**
 * Retrieves a given customer's display name
 * @since 1.0.0
 * @return string
 */
function hubaga_get_customer_name( $customer ){
	$customer = hubaga_get_customer( $customer );
	return apply_filters( 'hubaga_customer_name', $customer->display_name, $customer );
}
add_filter( 'hubaga_customer_name', 'strip_tags' );

/**
 * Retrieves a given customer's email address
 * @since 1.0.0
 * @return string
 */
function hubaga_get_customer_email( $customer ){
	$customer = hubaga_get_customer( $customer );
	return apply_filters( 'hubaga_customer_email', $customer->user_email, $customer );
}
add_filter( 'hubaga_customer_email', 'sanitize_email' );

/**
 * Retrieves a given customer's description
 * @since 1.0.0
 * @return string
 */
function hubaga_get_customer_description( $customer ){
	$customer = hubaga_get_customer( $customer );
	return apply_filters( 'hubaga_customer_description', $customer->user_description, $customer );
}
add_filter( 'hubaga_customer_description', 'esc_html' );

/**
 * Retrieves a given customer's date of registration
 * @since 1.0.0
 * @return string
 */
function hubaga_get_customer_registered( $customer ){
	$customer = hubaga_get_customer( $customer );
	return apply_filters( 'hubaga_customer_registered', $customer->user_registered, $customer );
}

/**
 * Retrieves a given customer's ID
 * @since 1.0.0
 * @return string
 */
function hubaga_get_customer_id( $customer ){
	$customer = hubaga_get_customer( $customer );
	return apply_filters( 'hubaga_customer_id', $customer->ID, $customer );
}
add_filter( 'hubaga_customer_id', 'absint' );

/**
 * Returns a customer's orders
 *
 * @since Hubaga 1.0.0
 *
 * @return string
 */
 
function hubaga_get_customer_orders( $customer, $where = array() ){
	
	$customer = hubaga_get_customer( $customer );
	return $customer->get_orders_by( $where );

}


/**
 * Gets the url to the account page.
 *
 * @since  1.0.0
 *
 * @return string Url to account page
 */
function hubaga_get_account_url(){
	
	$id 		= intval( hubaga_get_option( 'account_page_id' ) );
	$permalink 	= 1 > $id ? get_home_url() : get_permalink( $id );
	return apply_filters( 'hubaga_get_account_url', $permalink );
	
}
add_filter( 'hubaga_get_account_url', 'esc_url' );

/**
 * Checks whether or not this is a checkout page
 *
 * @since  1.0.0
 *
 * @return bool
 */
 function hubaga_is_account_page(){
	
		$id = intval( hubaga_get_option( 'account_page_id' ) );
		if( !$id ){
			return false;
		}
		return is_page( $id );
	
}

/**
 * Prevent any user who cannot 'edit_posts' (subscribers, customers etc) from seeing the admin bar.
 *
 * @access public
 * @param bool $show_admin_bar
 * @return bool
 */
function hubaga_disable_admin_bar( $show_admin_bar ){
	return current_user_can( 'edit_posts' );
}
add_filter( 'show_admin_bar', 'hubaga_disable_admin_bar' );