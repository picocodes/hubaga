<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Class.
 *
 * All properties are run through the hubaga_product_{$property} filter hook
 *
 * @see hubaga_get_product
 *
 * @class    H_Product
 * @version  1.0.0
 */
class H_Product {

	//Product id
	protected $ID = null;

	/**
	 * Product information
	 * @since 1.0.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * Product constructor. Loads product data.
	 * @param mixed $product Product ID, array, or H_Product instance
	 */
	public function __construct( $product = false ) {

		if ( $product instanceof H_Product ) {
			$this->init( $product->data );
			return;
		} elseif ( is_array( $product ) ) {
			$this->init( $product );
			return;
		}

		//Try fetching the product by its post id
		if ( ! empty( $product ) && is_numeric( $product ) ) {
			$product = absint( $product );

			if ( $data = self::get_data_by( 'id', $product ) ) {
				$this->init( $data );
				return;
			}
		}


		//If we are here then the product does not exist
		$this->init( array() );
	}

	/**
	 * Sets up object properties
	 *
	 * @param array $data contains product details
	 */
	public function init( $data ) {

		$data 				= $this->sanitize_product_data( $data );
		$this->data 		= $data;
		$this->ID 			= $data['ID'];

	}

	/**
	 * Fetch a product from the db/cache
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $field The field to query against: At the moment only ID is allowed
	 * @param string|int $value The field value
	 * @return array|false array of product details on success. False otherwise.
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
			return false;
		}

		if ( !$value )
			return false;


		if ( $product = wp_cache_get( $value, 'h_products' ) )
			return $product;

		$post_type = hubaga_get_product_post_type();
		$_product  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d LIMIT 1", $value ) );
		if ( !$_product || $_product->post_type != $post_type  )
			return false;

		//So the product exists; great
		$product = array();
		$product['ID']					= $_product->ID;
		$product['post_date'] 			= $_product->post_date;
		$product['post_modified'] 		= $_product->post_modified;
		$product['post_status'] 		= $_product->post_status;
		$product['post_title'] 			= $_product->post_title;
		$product['price']				= get_post_meta( $_product->ID, '_product_price', true );
		$product['sell_count']			= get_post_meta( $_product->ID, '_product_sell_count', true );
		$product['short_description']	= get_post_meta( $_product->ID, '_product_short_description', true );
		$product['type'] 				= get_post_meta( $_product->ID, '_product_type', true );
		$product['download_url'] 		= get_post_meta( $_product->ID, '_product_download_url', true );
		$product['download_name'] 		= get_post_meta( $_product->ID, '_product_download_name', true );

		//Update the cache with out data
		wp_cache_add( $product['ID'], $product, 'h_products' );

		return $this->sanitize_product_data( $product );
	}

	/**
	 * Sanitizes product data
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array the sanitized data
	 */
	public function sanitize_product_data( $data ) {

		$return = array(
			'ID'                    => null,
			'post_date'             => null,
			'post_modified'         => null,
			'post_status'           => 'draft',
			'post_title'           	=> null,
			'price'             	=> 0,
			'download_name'         => 'download.zip',
			'download_url'          => null,
			'short_description'     => null,
			'type'              	=> 'normal',
			'sell_count'            => 0,
		);

		$product_types = hubaga_get_product_types();

		//Arrays only please
		if (! is_array( $data ) )
			return $return;

		if ( hubaga_is_array_key_valid( $data, 'ID', 'is_numeric' ) )
			$return['ID'] = absint( $data['ID'] );

		if ( hubaga_is_array_key_valid( $data, 'post_date', 'is_string'  ))
			$return['post_date'] = $data['post_date'];

		if ( hubaga_is_array_key_valid( $data, 'post_modified', 'is_string' ))
			$return['post_modified'] = $data['post_modified'];

		if ( hubaga_is_array_key_valid( $data, 'post_status', 'is_string' ))
			$return['post_status'] = $data['post_status'];

		if ( hubaga_is_array_key_valid( $data, 'post_title' , 'is_string' ))
			$return['post_title'] = $data['post_title'];

		if ( hubaga_is_array_key_valid( $data, 'price' , 'is_numeric' ))
			$return['price'] = floatval( $data['price'] );

		if ( hubaga_is_array_key_valid( $data, 'download_url', 'is_string' ))
			$return['download_url'] =  $data['download_url'] ;

		if ( hubaga_is_array_key_valid( $data, 'download_name', 'is_string' ))
			$return['download_name'] =  $data['download_name'] ;

		if ( hubaga_is_array_key_valid( $data, 'short_description', 'is_string' ))
			$return['short_description'] =  $data['short_description'] ;

		if ( hubaga_is_array_key_valid( $data, 'type', 'is_string' ) && array_key_exists( $data['type'], $product_types))
			$return['type'] = $data['type'];

		if ( hubaga_is_array_key_valid( $data, 'sell_count', 'is_numeric' ))
			$return['sell_count'] = absint( $data['sell_count'] );

		return $return;
	}

	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the given product field is set.
	 */
	public function __isset( $key ) {

		if ( $key == 'id' ) {
			$key = 'ID';
		}
		return isset( $this->data[$key] ) && $this->data[$key] != null;

	}

	/**
	 * Magic method for accessing custom fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key Product meta key to retrieve.
	 * @return mixed Value of the given product meta key (if set). If `$key` is 'id', the product ID.
	 */
	public function __get( $key ) {

		if ( $key == 'id' ) {
			$key = 'ID';
		}

		if( $key == 'price' ) {
			$value = hubaga_format_price( $this->data['price'] );
		} else {
			$value = $this->data[$key];
		}

		return apply_filters( "hubaga_product_{$key}", $value, $this );
	}

	/**
	 * Magic method for setting custom product fields.
	 *
	 * This method does not update custom fields in the database. It only stores
	 * the value on the H_Product instance.
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

		$this->data[$key] = $value;

	}

	/**
	 * Saves the current product to the database
	 *
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function save() {

		$data = $this->sanitize_product_data( $this->data );

		if(! $data[ 'ID' ] )
			return false;

		$id = $data[ 'ID' ];
		unset( $data['ID'] );
		unset( $data['post_date'] );
		unset( $data['post_modified'] );
		unset( $data['post_status'] );

		foreach ( $data as $key => $value ){
			$key = trim($key);
			$key = "_product_$key";
			update_post_meta( $id, $key, $value );
		}

		//Update the cache with our new data
		wp_cache_delete( $id, 'h_products' );
		wp_cache_add($id, $this->data, 'h_products' );

		return true;
	}

	////////### Conditionals #####///////

	/**
	 * Determine whether the product exists in the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if product exists in the database, false if not.
	 */
	public function exists() {
		return null != $this->ID;
	}

	/**
	 * Determines whether this product has been published
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if product exists in the database, false if not.
	 */
	public function is_published() {
		return $this->post_status == 'publish';
	}

	/**
	 * Checks whether an product is free or not
	 *
	 * @return bool
	 */
	public function is_free() {
		$is_free = !( $this->price > 0 );
		return apply_filters( "hubaga_is_product_free", $is_free, $this );
	}

	/**
	 * Checks whether or not a given product can be bought
	 *
	 * @return bool
	 */
	public function can_buy() {
		$can_buy = ( $this->is_product() && $this->is_published() );
		return apply_filters( "hubaga_can_buy_product", $can_buy, $this );
	}

	/**
	 * Checks whether this is a real product and is saved to the database
	 *
	 * @return bool
	 */
	public function is_product() {
		$is_product = ( $this->exists() && get_post_type( $this->ID ) == hubaga_get_product_post_type() );
		return apply_filters( "hubaga_is_product", $is_product, $this );
	}

	/**
	 * Returns the product view shortcode
	 *
	 * @return string
	 */
	public function product_shortcode() {
		return '[h-product ID="' . $this->ID .'"]';
	}

	/**
	 * Returns the buy button shortcode
	 *
	 * @return string
	 */
	public function buy_shortcode() {
		return '[h-buy-button ID="' . $this->ID .'"] BUY [/h-buy-button]';
	}

	/**
	 * Increases a products sell count
	 *
	 * @return bool
	 */
	public function update_sell_count( $by = 1 ){
		$sell_count = $this->sell_count + $by;
		$this->sell_count =$sell_count;
		return $this->save();
	}

	/**
	 * Fetches all product data
	 *
	 * @return array an array of order data
	 */
	public function get_all_data(){
		return $this->data;
	}
}
