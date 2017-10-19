<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Hubaga customer class
 *
 * Extends WP_User by adding several utility methods specific to customers
 *
 * @class    H_Customer
 */
class H_Customer extends WP_User{

	/**
	 * Gets all orders belonging to the given customer
	 *
	 * If no parameters are passed then it matches all records
	 *
	 * @param $where An array of arrays containing field as key, value and operator as values
	 * @return array An array of order ids
	 */
	public function get_orders_by( $where = array() ){
		global $wpdb;
		
		if( !is_array( $where ) ){
			$where = array();
		}
		
		//customer_id ID in( SELECT ID FROM $wpdb->posts WHERE meta_key = '_order_platform' AND meta_value = %s )
		$id				= hubaga_wpdb_clean( $this->ID );
		$where[ 'ID' ] 	= array(
			'value'		=> "(SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_order_customer_id' AND meta_value = $id)" ,
			'operator'	=> 'in' ,
		);
		
		//post status
		$statuses				= implode( ', ', array_map( 'hubaga_wpdb_clean', array_keys( hubaga_get_order_statuses() ) ) );
		$where[ 'post_status' ] = array(
			'value'				=> "($statuses)" ,
			'operator'			=> 'in' ,
		);
		
		//post type
		$where[ 'post_type' ]   = array(
			'value'				=> hubaga_wpdb_clean( hubaga_get_order_post_type() ) ,
			'operator'			=> '=' ,
		);
		

		$sql 	= "SELECT DISTINCT(ID) FROM $wpdb->posts";
		$_where	= ' 1 = 1';
		$joined	= false;
		
		foreach( $where as $field => $args ){
			
			if( !is_array($args) ){
				continue;
			}
			
			if( empty( $args['operator'] ) ){
				$args['operator'] = '=';
			}
			$value		= $args['value'];
			$operator	= $args['operator'];
			
			if( in_array( $field, array( 'meta_key','meta_value' ) ) && !$joined ){
				$sql .= " INNER JOIN $wpdb->postmeta ON ID = post_id";
			}
			$_where .= " AND $field $operator $value";
		}

		$sql .= " WHERE $_where ORDER BY post_date DESC";
		
		return apply_filters( 'hubaga_customer_orders', $wpdb->get_col( $sql ), $this, $where );

	}

}
