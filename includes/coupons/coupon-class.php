<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hubaga coupons.
 *
 * The Hubaga coupons class gets coupon data from storage and checks coupon validity.
 * Adapted from WooCommerce
 *
 */
class H_Coupon {
	
	//Coupon id
	protected $ID = null;
	
	/**
	 * Coupon information
	 * @since 1.0.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * Coupon constructor. Loads coupon data.
	 * @param mixed $coupon Coupon ID, array, or H_Coupon instance
	 * @param string $coupon_code Optionally get a coupon by its code
	 */
	public function __construct( $coupon = false, $coupon_code = false ) {
		
		if ( $coupon instanceof H_Coupon ) {
			$this->init( $coupon->data );
			return;
		} elseif ( is_array( $coupon ) ) {
			$this->init( $coupon );
			return;
		}
		
		//Try fetching the coupon by its post id
		$data = false;
		
		if ( ! empty( $coupon ) && is_numeric( $coupon ) ) {
			$coupon = absint( $coupon );
			$data = self::get_data_by( 'id', $coupon );
		}

		if ( $data ) {
			$this->init( $data );
			return;
		}
		
		//Try fetching the coupon by its coupon code
		if ( $coupon_code && is_string( $coupon_code ) ) {
			$data = self::get_data_by( 'coupon_code', $coupon_code );
		}

		if ( $data ) {
			$this->init( $data );
			return;
		} 
		
		//If we are here then the coupon code does not exist
		$this->init( array() );
	}
	
	/**
	 * Sets up object properties
	 *
	 * @param array $data An array containing the coupons data
	 */
	public function init( $data ) {
		$data = $this->sanitize_coupon_data( $data );
		$this->data = $data;
		$this->ID = $data['ID'];
	}
	
	/**
	 * Fetch an coupon from the db/cache
	 *
	 *
	 * @static
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $field The field to query against: 'ID', 'coupon_code'
	 * @param string|int $value The field value
	 * @return array|false array of coupon details on success. False otherwise.
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
				$coupon_id = $value;
				$db_field = 'ID';
				$join	  = false;
				break;
			case 'coupon_code':
				$coupon_id = wp_cache_get( $value, 'H_Coupon_codes' );
				$meta_key = $wpdb->prepare( 'meta_key= %s ', '_coupon_code' );
				$db_field = "$meta_key AND meta_value";
				$join	  = true;
				break;
			default:
				return false;
		}

		if ( false !== $coupon_id ) {
			if ( $coupon = wp_cache_get( $coupon_id, 'H_Coupons' ) )
				return $coupon;
		}
				
		$sql 	= "SELECT * FROM {$wpdb->posts}";
		
		if( $join ) {
			$sql 	.= " INNER JOIN {$wpdb->postmeta} ON ( ID = post_id ) ";
		}
		
		$sql 		.= " WHERE $db_field = %s LIMIT 1";
		$sql 		= $wpdb->prepare( $sql, $value );
		$_coupon 	= $wpdb->get_row( $sql );
		$post_type = hubaga_get_coupon_post_type();
		
		//Validate the coupon availability
		if ( !$_coupon OR $_coupon->post_type != $post_type )
			return false;
		
		//So the coupon exists; great
		$coupon = array();
		$coupon['ID']					= $_coupon->ID;
		$coupon['post_modified'] 		= $_coupon->post_modified;
		$coupon['post_date'] 			= $_coupon->post_date;
		$coupon['post_status'] 			= $_coupon->post_status;
		$coupon['date_expires']			= get_post_meta( $_coupon->ID, '_coupon_date_expires', true );
		$coupon['code']					= get_post_meta( $_coupon->ID, '_coupon_code', true );
		$coupon['amount'] 				= get_post_meta( $_coupon->ID, '_coupon_amount', true );		
		$coupon['discount_type'] 		= get_post_meta( $_coupon->ID, '_coupon_discount_type', true );
		$coupon['usage_count'] 			= get_post_meta( $_coupon->ID, '_coupon_usage_count', true );
		$coupon['product_ids'] 			= get_post_meta( $_coupon->ID, '_coupon_product_ids', true );
		$coupon['excluded_product_ids']	= get_post_meta( $_coupon->ID, '_coupon_excluded_product_ids', true );
		$coupon['usage_limit']			= get_post_meta( $_coupon->ID, '_coupon_usage_limit', true );
		$coupon['minimum_amount']		= get_post_meta( $_coupon->ID, '_coupon_minimum_amount', true );
		$coupon['maximum_amount']		= get_post_meta( $_coupon->ID, '_coupon_maximum_amount', true );
		$coupon['email_restrictions']	= get_post_meta( $_coupon->ID, '_coupon_email_restrictions', true );
		
		//Update the cache with out data
		wp_cache_add( $coupon['ID'], $coupon, 'H_Coupons' );
		wp_cache_add( $coupon['code'], $_coupon->ID, 'H_Coupon_codes' );

		return $this->sanitize_coupon_data( $coupon );
	}
	
	/**
	 * Sanitizes coupon data
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array the sanitized data
	 */
	public function sanitize_coupon_data( $data ) {
		
		$allowed_discount_types = array(
			'fixed',
			'percentage'
		);
		
		$return = array(
			'ID'                          => null,
			'code'                        => '',
			'amount'                      => 0,
			'post_date'               	  => null,
			'post_modified'               => null,
			'date_expires'                => null,
			'post_status'                 => 'draft',
			'discount_type'               => 'fixed',
			'usage_count'                 => 0,
			'product_ids'                 => array(),
			'excluded_product_ids'        => array(),
			'usage_limit'                 => 0,
			'minimum_amount'              => 0,
			'maximum_amount'              => 0,
			'email_restrictions'          => array(),
		);
		
				
		//Arrays only please
		if (! is_array( $data ) )
			return $return;
		
		if ( hubaga_is_array_key_valid( $data, 'ID', 'is_numeric' ) )
			$return['ID'] = absint( $data['ID'] );
		
		if ( hubaga_is_array_key_valid( $data, 'code', 'is_string' ))
			$return['code'] = $data['code'];
		
		if ( hubaga_is_array_key_valid( $data, 'amount', 'is_numeric' ))
			$return['amount'] = floatval( $data['amount'] );
		
		if ( hubaga_is_array_key_valid( $data, 'post_date', 'is_string'  ))
			$return['post_date'] = $data['post_date'];
		
		if ( hubaga_is_array_key_valid( $data, 'post_modified', 'is_string' ))
			$return['post_modified'] = $data['post_modified'];
		
		if ( hubaga_is_array_key_valid( $data, 'date_expires', 'is_string' ))
			$return['date_expires'] = $data['date_expires'];
		
		if ( hubaga_is_array_key_valid( $data, 'post_status', 'is_string' ))
			$return['post_status'] = $data['post_status'];
		
		if ( hubaga_is_array_key_valid( $data, 'discount_type' , 'is_string' )  && in_array( $data['discount_type'], $allowed_discount_types )  )
			$return['discount_type'] = $data['discount_type'];
		
		if ( hubaga_is_array_key_valid( $data, 'usage_count', 'is_numeric' ))
			$return['usage_count'] = absint( $data['usage_count'] );
		
		if ( hubaga_is_array_key_valid( $data, 'product_ids', 'is_array' ))
			$return['product_ids'] = $data['product_ids'];
		
		if ( hubaga_is_array_key_valid( $data, 'excluded_product_ids', 'is_array' ))
			$return['excluded_product_ids'] = $data['excluded_product_ids'];
		
		if ( hubaga_is_array_key_valid( $data, 'usage_limit', 'is_numeric' ))
			$return['usage_limit'] = absint( $data['usage_limit'] );
		
		if ( hubaga_is_array_key_valid( $data, 'minimum_amount', 'is_numeric' ))
			$return['minimum_amount'] = floatval( $data['minimum_amount'] );
		
		if ( hubaga_is_array_key_valid( $data, 'maximum_amount', 'is_numeric' ))
			$return['maximum_amount'] = floatval( $data['maximum_amount'] );
		
		if ( hubaga_is_array_key_valid( $data, 'email_restrictions', 'is_array' ))
			$return['email_restrictions'] = $data['email_restrictions'];
		
		return $return;
	}
	
	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool Whether the given coupon field is set.
	 */
	public function __isset( $key ){
		return isset( $this->data[$key] );
	}
	
	/**
	 * Magic method for accessing custom fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key Coupon data to retrieve
	 * @return mixed Value of the given coupon meta key (if set). If `$key` is 'id', the coupon ID.
	 */
	public function __get( $key ) {
		
		if ( $key == 'id' ) {
			$key = 'ID';
		}
		
		if( $key 	== 'amount' ) {
			$value 	= hubaga_format_price( $this->data['amount'] );
		} elseif( $key 	== 'minimum_amount' ){
			$value 		= hubaga_format_price( $this->data['minimum_amount'] );
		} elseif( $key 	== 'maximum_amount' ){
			$value 		= hubaga_format_price( $this->data['maximum_amount'] );
		} else {
			$value = $this->data[$key];
		}
		
		$value = $this->data[$key];
		return apply_filters( "hubaga_coupon_{$key}", $value, $this );

	}
	
	/**
	 * Magic method for setting custom coupon fields.
	 *
	 * This method does not update custom fields in the database. It only stores
	 * the value on the H_Coupon instance.
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
	 * Saves a coupon to the database
	 *
	 * This method persits a H_Coupon instance to the database. It does not create a new coupon.
	 * For that; use wp_insert_post instead
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function save(){
		
		$data = $this->sanitize_coupon_data( $this->data );
		if(! $data[ 'ID' ] )
			return false;
		
		$id = $data[ 'ID' ];
		unset( $data['ID'] );
		unset( $data['post_modified'] );
		unset( $data['post_date'] );
		unset( $data['post_status'] );
		
		foreach ( $data as $key => $value ) {
			$key = trim($key);
			$key = "_coupon_$key";
			update_post_meta( $id, $key, $value );
		}
		
		//Update the cache with our new data
		wp_cache_delete( $id, 'H_Coupons' );
		wp_cache_add( $id, $this->data, 'H_Coupons' );
		wp_cache_delete( $this->code, 'H_Coupon_codes' );
		wp_cache_add( $this->code, $id, 'H_Coupon_codes' );

		return true;		
	}
	
	
	/**
	 * Checks whether a coupon exists in the database or not
	 */
	public function exists(){
		return null != $this->ID;
	}
	
	// Boolean methods
	
	/**
	 * Checks the coupon type.
	 * @param  string $type the coupon type to check against
	 * @return bool
	 */
	public function is_type( $type ) {
		return $this->discount_type == $type;
	}
	
	/**
	 * Checks whether the coupon is published or not
	 * @return bool
	 */
	public function is_active() {
		return $this->post_status == 'publish';
	}
	
	/**
	 * Checks whether the coupon is has exided the usage limit or not
	 * @return bool
	 */
	public function has_exeeded_limit() {
		if( 0 === $this->usage_limit ) return false;
		
		return $this->usage_count >= $this->usage_limit;
	}
	
	/**
	 * Checks if the coupon is expired
	 * @return bool
	 */
	public function is_expired() {
		
		if( is_null ( $this->date_expires ) ) {
			return false;
		}
		try {
			if( time() > strtotime( $this->date_expires ) )
				return true;
		} catch ( Exception $e ) {
			return false; // The user provided an invalid expiry time
		}
		
	}
	
	/**
	 * Check if a coupon is valid for a given product id.
	 *
	 * @param  H_Product  $product
	 * @return boolean
	 */
	public function is_valid_for_product( $product_id ) {
		return (
			( empty( $this->product_ids ) || in_array( $product_id, $this->product_ids ) )
			&& ( empty( $this->excluded_product_ids ) || !( in_array( $product_id, $this->excluded_product_ids ) ) )
		);
	}
	
	/**
	 * Check if a coupon is valid for a given email
	 *
	 * @param  string $email the email to check against
	 * @return boolean
	 */
	public function is_valid_for_email( $email ) {
		$restrictions = array();
		foreach( $this->email_restrictions as $restriction ) {
			if( is_email( $restriction ) ) {
				$restrictions[] = $restriction; 
			}
		}

		return ( empty( $restrictions ) || in_array( $email, $restrictions ) );
	}
	
	/**
	 * Check if a coupon is valid for the given amount
	 *
	 * @param  float  $amount The amount to check against
	 * @return boolean
	 */
	public function is_valid_for_amount( $amount ) {
		
		//Amount passed is not valid
		if(! is_numeric ( $amount ) ) {
			return false;
		}
		$amount = floatval( $amount );
		$minimum_valid = true;
		$maximum_valid = true;
		
		//check if it meets the minimum amount valid
		if( $this->minimum_amount > 0 && $amount < $this->minimum_amount ) {
			$minimum_valid = false;
		}
		
		//check if it meets the maximum amount valid
		if( $this->maximum_amount > 0 && $amount > $this->maximum_amount ) {
			$maximum_valid = false;
		}
		
		return ( $minimum_valid && $maximum_valid );
	}
	
}
