<?php
/**
 * Hubaga Coupon Functions
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Filters on data used in admin and frontend.
 */
add_filter( 'hubaga_coupon_code', 'html_entity_decode' );
add_filter( 'hubaga_coupon_code', 'sanitize_text_field' );
add_filter( 'hubaga_coupon_code', 'strtolower' );
add_filter( 'hubaga_coupon_code', 'urldecode' );

/* 
 * fetches a coupon code by its id 
 * @param $id coupon id, array of data or H_Coupon object
 * @param $code string coupon code in case you want to search by code
 * @return H_Coupon object
 */
function hubaga_get_coupon( $id = false, $coupon_code = false ) {
	return new H_Coupon( $id, $coupon_code );
}

/* 
 * Checks if a coupon exists
 * @param $id coupon id, array of data or H_Coupon object
 * @param $coupon_code string coupon code in case you want to search by code
 * @return bool
 */
function hubaga_coupon_exists( $id = false, $coupon_code = false ) {
	$coupon = new H_Coupon( $id, $coupon_code );
	return $coupon->exists();
}

/* 
 * Creates / Updates a new coupon code
 * @param $id coupon id, array of data or H_Coupon object
 * @param $coupon_code string coupon code in case you want to search by code
 * @return bool
 */
function hubaga_save_coupon( $id = false, $coupon_code = false ) {
	$coupon = new H_Coupon( $id, $coupon_code );
	return $coupon->save();
}

/* 
 * Calculates the amounts for a given coupon
 *
 * @param $coupon the coupon to apply
 * @param $order_total the current order total
 * @param $customer the customer using this coupon
 * @param $product the product that has been bought
 *
 * @return array. Returns an array with the new order_total and discount total
 */
function hubaga_apply_coupon( $coupon, $order_total, $customer, $product ) {	
	
	$coupon		= apply_filters( 'hubaga_coupon_code', $coupon );
	$coupon 	= hubaga_get_coupon( false, $coupon );
	$product 	= hubaga_get_product( $product );
	$customer 	= hubaga_get_customer( $customer );
	$order_total= floatval( $order_total );
	$return 	= array(
		'order_total' 		=> $order_total,
		'discount_total' 	=> 0,
	);
	
	//Is the coupon available for use?		
	$can_use = true;
	if(! $coupon->exists() ) {
		$can_use = false; //coupon does not exist
	}
	
	if( !$coupon->is_active() ) {
		$can_use = false; //Not published
	}
	
	if( $coupon->has_exeeded_limit() ){
		$can_use = false; //Exeeded limit
	}
	
	if( $coupon->is_expired() ){
		$can_use = false; //Expired
	}
	
	if(! $coupon->is_valid_for_product( $product->ID ) ){
		$can_use = false; //Not valid for this product
	}
	
	if(! $coupon->is_valid_for_email( hubaga_get_customer_email( $customer ) ) ){
		$can_use = false; //Not valid for this user
	}
	
	if(! $coupon->is_valid_for_amount( $order_total ) ){
		$can_use = false; //Not valid for this amount
	}
	
	if(! $can_use ) {
		hubaga_add_error( esc_html__( "Error. This coupon does not exist.", 'hubaga' ) );
		return false;
	}
	
	$coupon_amount = floatval( $coupon->amount );
	 //Nothing to do here
	if( $coupon_amount < 1 ){
		hubaga_add_error( esc_html__( "Error. You cannot use that coupon.", 'hubaga' ) );
		return false;
	}
	
	//Get the order total and discount amount	
	if( $coupon->is_type( 'percentage' ) ) {
		if( $coupon_amount > 100 ){
			$coupon_amount = 100;
		}
		
		$return[ 'discount_total' ] = hubaga_price( ( $coupon_amount/100 ) * $order_total, false, false );
		$return[ 'order_total' ] 	= hubaga_price( $order_total - $return[ 'discount_total' ], false, false );
		
	} else {
		
		if( $coupon_amount > $order_total ){
			$coupon_amount = $order_total;
		}
		
		$return[ 'discount_total' ] = hubaga_price( $order_total - $coupon_amount, false, false );
		$return[ 'order_total' ] 	= hubaga_price( $order_total - $return[ 'discount_total' ], false, false );
		
	}
	
	return apply_filters( 'hubaga_apply_coupon', $return, $coupon, $order_total, $customer, $product );
}

/* 
 * Applies a coupon to a given order
 * @param $order id, object or H_Order instance
 * @param $coupon id, object or H_Coupon instance
 * @return object. H_Order with the coupon applied OR WP_Error on error
 */
function hubaga_get_coupon_types() {
	return apply_filters( 'hubaga_coupon_types', 
		array(
			'fixed'        => esc_html__( 'Fixed Amount', 'hubaga' ),
			'percentage'   => esc_html__( 'Percentage Discount', 'hubaga' ),
		));
}

/**
 * A wrapper for wp_insert_post() that also includes the necessary meta values
 * for the forum to function properly.
 */
function hubaga_get_coupon_post_type_labels() {
	return apply_filters( 'hubaga_coupon_post_type_labels', 
		array(
			'name'                  => esc_html__( 'Coupons', 'hubaga' ),
			'singular_name'         => esc_html__( 'Coupon', 'hubaga' ),
			'add_new'               => esc_html__( 'Add coupon', 'hubaga' ),
			'add_new_item'          => esc_html__( 'Add new coupon', 'hubaga' ),
			'edit'                  => esc_html__( 'Edit', 'hubaga' ),
			'edit_item'             => esc_html__( 'Edit coupon', 'hubaga' ),
			'new_item'              => esc_html__( 'New coupon', 'hubaga' ),
			'view'                  => esc_html__( 'View coupon', 'hubaga' ),
			'view_item'             => esc_html__( 'View coupon', 'hubaga' ),
			'search_items'          => esc_html__( 'Search coupons', 'hubaga' ),
			'not_found'             => esc_html__( 'No coupons found', 'hubaga' ),
			'not_found_in_trash'    => esc_html__( 'No coupons found in trash', 'hubaga' ),
			'parent'                => esc_html__( 'Parent coupons', 'hubaga' ),
			'menu_name'             => esc_html_x( 'Coupons', 'Admin menu name', 'hubaga' ),
			'filter_items_list'     => esc_html__( 'Filter coupons', 'hubaga' ),
			'items_list_navigation' => esc_html__( 'Coupons navigation', 'hubaga' ),
			'items_list'            => esc_html__( 'Coupons list', 'hubaga' ),
		) );
}


/**
 * Returns coupon post type details
 */
function hubaga_get_coupon_post_type_details(){
	return apply_filters( 
		'hubaga_coupon_post_type_details', 
		array(
			'labels'              => hubaga_get_coupon_post_type_labels(),
			'description'         => esc_html__( 'This is where store coupons are stored.', 'hubaga' ),
			'public'              => false,
			'show_ui'             => true,
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => hubaga_get_coupon_post_type_menu_name(),
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => false,
			'has_archive'         => false,
		));
}

/**
 * Returns coupon post type
 */
function hubaga_get_coupon_post_type(){
	return hubaga()->coupon_post_type;
}

/**
 * Returns coupon post type menu name
 */
function hubaga_get_coupon_post_type_menu_name(){
	return apply_filters( 'hubaga_coupon_post_type_menu_name', hubaga_get_product_post_type_menu_name());
}