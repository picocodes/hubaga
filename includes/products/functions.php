<?php
/**
 * Hubaga Product Functions
 * These functions provide a modular access to H_Product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Retrieves a product by ID or object.
 *
 * @since 1.0
 * @param mixed $product ID, details or object
 * @return H_Product $product
 */
function hubaga_get_product( $product = null ) {
	return new H_Product($product);
}

/**
 * Saves a product. The product should already exist in the database
 * @since 1.0
 * @param mixed $product ID, details or object
 * @return bool
 */
function hubaga_save_product( $product ) {
	$product = hubaga_get_product( $product );
	return $product->save();
}

/**
 * Returns a products ID
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product id
 */
function hubaga_get_product_id( $product ) {
	return hubaga_get_product( $product )->ID;
}
add_filter( 'hubaga_product_ID', 'absint' );


/**
 * Returns a products title
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product title
 */
function hubaga_get_product_title( $product ) {
	return hubaga_get_product( $product )->post_title;
}
add_filter( 'hubaga_product_title', 'sanitize_text_field' );


/**
 * Returns a products short description
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product description
 */
function hubaga_get_product_description( $product ) {
	return hubaga_get_product( $product )->short_description;
}
add_filter( 'hubaga_product_short_description', 'wptexturize' );
add_filter( 'hubaga_product_short_description', 'convert_smilies' );
add_filter( 'hubaga_product_short_description', 'convert_chars' );
add_filter( 'hubaga_product_short_description', 'wpautop' );
add_filter( 'hubaga_product_short_description', 'shortcode_unautop' );
add_filter( 'hubaga_product_short_description', 'prepend_attachment' );
add_filter( 'hubaga_product_short_description', 'do_shortcode', 11 ); // AFTER wpautop()

/**
 * Returns the number of times a product has been sold
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product sell count
 */
function hubaga_get_product_sell_count( $product ) {
	return hubaga_get_product( $product )->sell_count;
}
add_filter( 'hubaga_product_sell_count', 'absint' );


/**
 * Returns a product type
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product type
 */
function hubaga_get_product_type( $product ) {
	return hubaga_get_product( $product )->type;
}
add_filter( 'hubaga_product_type', 'sanitize_title' );

/**
 * Returns the downloads attached to a product
 *
 * @since Hubaga 1.0.0
 *
 * @returns an array containing the downloads.
 */
function hubaga_get_product_downloads( $product ) {
	$product  = hubaga_get_product( $product );
	$downloads= array();
	if( $product->download_url ){
		$downloads[] = array(
			'name' => $product->download_name,
			'url'  => $product->download_url,
		);
	}
	return apply_filters( 'hubaga_product_downloads', $downloads, $product);
}

/**
 * Returns a product status
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product status
 */
function hubaga_get_product_status( $product ) {
	return hubaga_get_product( $product )->post_status;
}


/**
 * Returns a product price with the symbol
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product price
 */
function hubaga_get_product_price( $product  ) {
	return hubaga_get_product( $product )->price;
}

/**
 * Returns a formatted product price with the symbol
 *
 * @since Hubaga 1.0.0
 *
 * @returns a string containing the product price
 */
function hubaga_get_formatted_product_price( $product  ) {
	$price 		= hubaga_price( hubaga_get_product_price( $product ) );
	return apply_filters( 'hubaga_formatted_product_price', $price, $product );
}

/**
 * Checks if the provided product exists in the db
 * @since 1.0
 * @param mixed $product ID, details or object
 * @return bool
 */
function hubaga_is_product( $product ) {
	return hubaga_get_product( $product )->is_product();
}

/**
 * Checks if the provided product can be bought
 * @since 1.0
 * @param mixed $product ID, details or object
 * @return bool
 */
function hubaga_can_buy_product( $product ) {
	return hubaga_get_product( $product )->can_buy();
}

/**
 * Checks if the provided product is free
 * @since 1.0
 * @param mixed $product ID, details or object
 * @return bool
 */
function hubaga_is_product_free( $product ) {
	return hubaga_get_product( $product )->is_free();
}

/**
 * Updates a products sell count
 * @since 1.0
 * @param mixed $product ID, details or object
 * @return bool
 */
function hubaga_update_product_sell_count( $product, $by = 1 ) {
	return hubaga_get_product( $product )->update_sell_count( $by );
}

/**
 * Retrieves a list of product types
 *
 * @since 1.0
 * @return array
 */
function hubaga_get_product_types() {
	return apply_filters( 'hubaga_get_product_types', array(
		'normal'   	=> _x( 'Normal Product',    'A normal product that accepts a single payment',  'hubaga' ),
	) );
}

/**
 * Retrieves a list of product labels
 *
 * @since 1.0
 * @return array
 */
function hubaga_get_product_post_type_labels() {
	return apply_filters(
		'hubaga_product_post_type_labels',
		array(
			'name'                  => __( 'Products', 'hubaga' ),
			'singular_name'         => __( 'Product', 'hubaga' ),
			'menu_name'             => _x( 'Hubaga', 'Admin menu name', 'hubaga' ),
			'add_new'               => __( 'Add product', 'hubaga' ),
			'add_new_item'          => __( 'Add new product', 'hubaga' ),
			'edit'                  => __( 'Edit', 'hubaga' ),
			'edit_item'             => __( 'Edit product', 'hubaga' ),
			'new_item'              => __( 'New product', 'hubaga' ),
			'view'                  => __( 'View product', 'hubaga' ),
			'view_item'             => __( 'View product', 'hubaga' ),
			'search_items'          => __( 'Search products', 'hubaga' ),
			'not_found'             => __( 'No products found', 'hubaga' ),
			'not_found_in_trash'    => __( 'No products found in trash', 'hubaga' ),
			'parent'                => __( 'Parent product', 'hubaga' ),
			'featured_image'        => __( 'Product image', 'hubaga' ),
			'set_featured_image'    => __( 'Set product image', 'hubaga' ),
			'remove_featured_image' => __( 'Remove product image', 'hubaga' ),
			'use_featured_image'    => __( 'Use as product image', 'hubaga' ),
			'insert_into_item'      => __( 'Insert into product', 'hubaga' ),
			'uploaded_to_this_item' => __( 'Uploaded to this product', 'hubaga' ),
			'filter_items_list'     => __( 'Filter products', 'hubaga' ),
			'items_list_navigation' => __( 'Products navigation', 'hubaga' ),
			'items_list'            => __( 'Products list', 'hubaga' ),
		) );
}



/**
 * Returns product post type details
 */
function hubaga_get_product_post_type_details(){
	return apply_filters(
		'hubaga_product_post_type_details',
		array(
			'labels'              => hubaga_get_product_post_type_labels(),
			'description'         => __( 'This is where you can add new digital products to your website.', 'hubaga' ),
			'public'              => false,
			'show_ui'             => true,
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
			'query_var'           => true,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'menu_icon'   		  => 'dashicons-products'
		));
}

/**
 * Returns product post type
 */
function hubaga_get_product_post_type(){
	return hubaga()->product_post_type;
}

/**
 * Returns product post type menu name
 */
function hubaga_get_product_post_type_menu_name(){
	return apply_filters( 'hubaga_product_post_type_menu_name', 'edit.php?post_type=' . hubaga_get_product_post_type());
}

/**
 * Trigger a product purchase hook
 */
function hubaga_trigger_product_purchased_hook( $order ){
	do_action( 'hubaga_product_purchased', hubaga_get_product( $order->product ), $order );
}
add_action( 'hubaga_order_complete', 'hubaga_trigger_product_purchased_hook' );
