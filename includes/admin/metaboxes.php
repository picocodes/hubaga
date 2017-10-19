<?php

/**
 * Hubaga Metaboxes Class
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'H_Metaboxes' ) ) :

/**
 * H_Metaboxes Class.
 *
 * Handles the edit posts views for our custom post types.
 */
class H_Metaboxes {

	/**
	 * @var The product post type
	 */
	private $product_post_type = '';

	/**
	 * @var The coupons post type
	 */
	private $coupon_post_type = '';

	/**
	 * @var The orders post type
	 */
	private $order_post_type = '';

	/**
	 * @var The order statuses
	 */
	private $orders_statuses = array();

	/**
	 * The main Hubaga topics admin loader
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Admin globals
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 */
	private function setup_globals() {
		$this->product_post_type 		= hubaga_get_product_post_type();
		$this->coupon_post_type	 		= hubaga_get_coupon_post_type();
		$this->order_post_type	 		= hubaga_get_order_post_type();
		$this->orders_statuses	 		= hubaga_get_order_statuses();
		$this->admin_dir				= hubaga()->admin->admin_dir;
	}

	/**
	 * Hooks into specific actions and removes other hooks
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses add_filter() To add various filters
	 */
	private function setup_actions() {

		$product = $this->product_post_type;
		$coupon  = $this->coupon_post_type;
		$order   = $this->order_post_type;

		/* An array of filters in the form of
		 * callback => filter
		 */

		$filters = array(
			//Messages
			'updated_messages' 			 => 'post_updated_messages',
			'bulk_post_updated_messages' => 'bulk_post_updated_messages',

			//Register custom columnms
			'product_columns'			 => "manage_{$product}_posts_columns",
			'coupon_columns'			 => "manage_{$coupon}_posts_columns",
			'order_columns'			     => "manage_{$order}_posts_columns",

			//Sort using custom columns
			'product_sortable_columns'	 => "manage_edit_{$product}_posts_columns",
			'coupon_sortable_columns'	 => "manage_edit_{$coupon}_posts_columns",
			'order_sortable_columns'	 => "manage_edit_{$order}_posts_columns",
		);

		foreach ($filters as $callback => $filter) {
			add_filter( $filter, array( $this, $callback ), 10, 2 );
		}

		/* An array of filters in the form of
		 * callback => filter
		 */
		$actions = array(

			//Display our custom columns
			'render_product_columns' => "manage_{$product}_posts_custom_column",
			'render_order_columns'   => "manage_{$order}_posts_custom_column",
			'render_coupon_columns'  => "manage_{$coupon}_posts_custom_column",

			//Register custom metaboxes
			'add_product_metabox'    => "add_meta_boxes_$product",
			'add_order_metabox'      => "add_meta_boxes_$order",
			'add_coupon_metabox'     => "add_meta_boxes_$coupon",

			//Save custom metaboxes
			'save_product_metabox'   => "save_post_$product",
			'save_order_metabox'     => "save_post_$order",
			'save_coupon_metabox'    => "save_post_$coupon",

			//Misc
			'_sort'					 => 'pre_get_posts',
			'footer_scripts'		 => 'admin_footer-post.php',
		);

		foreach ($actions as $callback => $action) {
			add_action( $action, array( $this, $callback ), 10, 2 );
		}

	}

	/**
	 * Custom user feedback messages for our custom post types
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @global int $post_ID
	 * @global object $post
	 * @uses wp_post_revision_title()
	 * @uses esc_url()
	 * @uses __()
	 * @uses add_query_arg()
	 *
	 * @param array $messages
	 *
	 * @return array
	 */
	public function updated_messages( $messages ) {
		global $post, $post_ID;

		//Maybe fetch the current revision
		$revision = false;
		if( isset( $_GET['revision'] ) ){
			$revision = wp_post_revision_title( (int) $_GET['revision'], false );
		}

		// Product Messages array
		$messages[$this->product_post_type] = array(
			0 =>  '',
			1 => __( 'Product updated.', 'hubaga' ),
			2 => __( 'Custom field updated.', 'hubaga' ),
			3 => __( 'Custom field deleted.', 'hubaga' ),
			4 => __( 'Product updated.', 'hubaga' ),
			5 => sprintf( __( 'Product restored to revision from %s', 'hubaga' ), $revision),
			6 => sprintf( __( 'Product created.', 'hubaga' ) ),
			7 => __( 'Product saved.', 'hubaga' ),
			8 => __( 'Product submitted.', 'hubaga' ),
			9 => sprintf(
					__( 'Product scheduled for: <strong>%1$s</strong>.', 'hubaga' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'hubaga' ),
					strtotime( $post->post_date ) ) ),
			10 => sprintf( __( 'Product draft updated.', 'hubaga' ) ),
		);

		// Coupon Messages array
		$messages[$this->coupon_post_type] = array(
			0 =>  '',
			1 => __( 'Coupon updated.', 'hubaga' ),
			2 => __( 'Custom field updated.', 'hubaga' ),
			3 => __( 'Custom field deleted.', 'hubaga' ),
			4 => __( 'Coupon updated.', 'hubaga' ),
			5 => sprintf( __( 'Coupon restored to revision from %s', 'hubaga' ), $revision),
			6 => sprintf( __( 'Coupon created.', 'hubaga' ) ),
			7 => __( 'Coupon saved.', 'hubaga' ),
			8 => __( 'Coupon submitted.', 'hubaga' ),
			9 => sprintf(
					__( 'Coupon scheduled for: <strong>%1$s</strong>.', 'hubaga' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'hubaga' ),
					strtotime( $post->post_date ) ) ),
			10 => sprintf( __( 'Coupon draft updated.', 'hubaga' ) ),
		);

		// Order Messages array
		$messages[$this->order_post_type] = array(
			0 =>  '',
			1 => __( 'Order updated.', 'hubaga' ),
			2 => __( 'Custom field updated.', 'hubaga' ),
			3 => __( 'Custom field deleted.', 'hubaga' ),
			4 => __( 'Order updated.', 'hubaga' ),
			5 => sprintf( __( 'Order restored to revision from %s', 'hubaga' ), $revision),
			6 => sprintf( __( 'Order created.', 'hubaga' ) ),
			7 => __( 'Order saved.', 'hubaga' ),
			8 => __( 'Order submitted.', 'hubaga' ),
			9 => sprintf(
				 	__( 'Order scheduled for: <strong>%1$s</strong>.', 'hubaga' ),
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'hubaga' ),
					strtotime( $post->post_date ) ) ),
			10 => sprintf( __( 'Order draft updated.', 'hubaga' ) ),
		);
		return $messages;
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 * @param  array $bulk_messages
	 * @param  array $bulk_counts
	 * @return array
	 */
	public function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {

		$bulk_messages[$this->product_post_type] = array(

			/* translators: %s: product count */
			'updated'   => _n(
				'%s product updated.',
				'%s products updated.',
				$bulk_counts['updated'],
				'hubaga' ) ,

			/* translators: %s: product count */
			'locked'    => _n(
				'%s product not updated,
				somebody is editing it.',
				'%s products not updated, somebody is editing them.',
				$bulk_counts['locked'],
				'hubaga' ) ,

			/* translators: %s: product count */
			'deleted'   => _n(
				'%s product permanently deleted.',
				'%s products permanently deleted.',
				$bulk_counts['deleted'],
				'hubaga' ) ,

			/* translators: %s: product count */
			'trashed'   => _n(
				'%s product moved to the Trash.',
				'%s products moved to the Trash.',
				$bulk_counts['trashed'],
				'hubaga' ) ,

			/* translators: %s: product count */
			'untrashed' => _n(
				'%s product restored from the Trash.',
				'%s products restored from the Trash.',
				$bulk_counts['untrashed'],

				'hubaga' ) ,
		);

		$bulk_messages[$this->order_post_type] = array(

			/* translators: %s: order count */
			'updated'   => _n(
				'%s order updated.',
				'%s orders updated.',
				$bulk_counts['updated'],
				'hubaga' ),

			/* translators: %s: order count */
			'locked'    => _n(
				'%s order not updated, somebody is editing it.',
				'%s orders not updated, somebody is editing them.',
				$bulk_counts['locked'],
				'hubaga' ),

			/* translators: %s: order count */
			'deleted'   => _n(
				'%s order permanently deleted.',
				'%s orders permanently deleted.',
				$bulk_counts['deleted'],
				'hubaga' ),

			/* translators: %s: order count */
			'trashed'   => _n(
				'%s order moved to the Trash.',
				'%s orders moved to the Trash.',
				$bulk_counts['trashed'],
				'hubaga' ),

			/* translators: %s: order count */
			'untrashed' => _n(
				'%s order restored from the Trash.',
				'%s orders restored from the Trash.',
				$bulk_counts['untrashed'],
				'hubaga' ),
		);

		$bulk_messages[$this->coupon_post_type] = array(

			/* translators: %s: coupon count */
			'updated'   => _n(
				'%s coupon updated.',
				'%s coupons updated.',
				$bulk_counts['updated'],
				'hubaga'),

			/* translators: %s: coupon count */
			'locked'    => _n(
				'%s coupon not updated, somebody is editing it.',
				'%s coupons not updated, somebody is editing them.',
				$bulk_counts['locked'],
				'hubaga' ),

			/* translators: %s: coupon count */
			'deleted'   => _n(
				'%s coupon permanently deleted.',
				'%s coupons permanently deleted.',
				$bulk_counts['deleted'],
				'hubaga' ),

			/* translators: %s: coupon count */
			'trashed'   => _n(
				'%s coupon moved to the Trash.',
				'%s coupons moved to the Trash.',
				$bulk_counts['trashed'],
				'hubaga' ),

			/* translators: %s: coupon count */
			'untrashed' => _n(
				'%s coupon restored from the Trash.',
				'%s coupons restored from the Trash.',
				$bulk_counts['untrashed'],
				'hubaga' ),
		);

		return $bulk_messages;
	}


	/**
	 * Define custom columns for products.
	 * @param  array $columns
	 * @return array
	 */
	public function product_columns( $columns ) {

		unset( $columns['date'] );
		$columns['product-price'] 			= esc_html__('Price', 'hubaga');
		$columns['product-type']  			= esc_html__('Type', 'hubaga');
		$columns['product-sell-count'] 		= esc_html__('Sales', 'hubaga');
		$columns['product-shortcode']  		= esc_html__('Product Shortcode', 'hubaga');
		$columns['buy-shortcode']  			= esc_html__('Buy Shortcode', 'hubaga');
		$columns['date']  					= esc_html__('Date', 'hubaga');
		return $columns;

	}

	/**
	 * Define custom columns for coupons.
	 * @param  array $columns
	 * @return array
	 */
	public function coupon_columns( $columns ) {

		unset( $columns['date'] );
		unset( $columns['title'] );
		$columns['coupon-code'] 	= esc_html__('Code', 'hubaga');
		$columns['discount-type']  	= esc_html__('Type', 'hubaga');
		$columns['amount']  		= esc_html__('Amount', 'hubaga');
		$columns['usage-count']  	= esc_html__('Usage Count', 'hubaga');
		$columns['date']  			= esc_html__('Date', 'hubaga');
		return $columns;

	}

	/**
	 * Define custom columns for orders.
	 * @param  array $columns
	 * @return array
	 */
	public function order_columns( $columns ) {

		unset( $columns['date'] );
		unset( $columns['title'] );
		$columns['order-number'] 	= esc_html__('Order', 'hubaga');
		$columns['payment-method'] 	= esc_html__('Gateway', 'hubaga');
		$columns['customer-id']  	= esc_html__('Customer', 'hubaga');
		$columns['discount-total']  = esc_html__('Discount', 'hubaga');
		$columns['order-total']  	= esc_html__('Total', 'hubaga');
		$columns['product']  		= esc_html__('Product', 'hubaga');
		$columns['date']  			= esc_html__('Date Created', 'hubaga');
		$columns['payment-date']  	= esc_html__('Date Paid', 'hubaga');
		return $columns;

	}

	/**
	 * Renders our products custom columns
	 * @param  array $columns
	 * @return array
	 */
	public function render_product_columns(  $column, $post_id  ) {

		$product = hubaga_get_product( $post_id );
		$currency = hubaga_get_currency();

		switch ($column) {
			case "product-price":
				echo hubaga_price( $product->price, $currency );
				break;
			case "product-type":
				echo $product->type;
				break;
			case "product-shortcode":
				echo $product->product_shortcode();
				break;
			case "buy-shortcode":
				echo $product->buy_shortcode();
				break;

			case "product-sell-count":
				echo $product->sell_count;
				break;
		}

	}

	/**
	 * Renders our coupon custom columns
	 * @param  array $columns
	 * @return array
	 */
	public function render_coupon_columns(  $column, $post_id  ) {

		$coupon   = hubaga_get_coupon( $post_id );

		switch ($column) {
			case "coupon-code":
				echo $coupon->code;
				break;
			case "discount-type":
				echo $coupon->discount_type;
				break;
			case "amount":
				echo $coupon->amount;
				break;
			case "usage-count":
				echo $coupon->usage_count;
				break;
		}

	}

	/**
	 * Renders our order custom columns
	 * @param  array $columns
	 * @return array
	 */
	public function render_order_columns(  $column, $post_id  ) {

		$order   		= hubaga_get_order( $post_id );
		$customer_name	= hubaga_get_order_customer_name( $order );
		$customer_email	= hubaga_get_order_customer_email( $order );

		switch ($column) {
			case "payment-date":
				echo $order->payment_date;
				break;
			case "payment-method":
				echo hubaga_get_gateway_title( $order->payment_method );
				break;
			case "customer-id":
				echo "$customer_name ($customer_email)";
				break;
			case "discount-total":
				echo hubaga_price( $order->discount_total, $order->currency );
				break;
			case "order-total":
				echo hubaga_price( $order->total, $order->currency );
				break;
			case "order-number":
				echo "#$order->ID";
				break;
			case "product":
				echo hubaga_get_product_title( $order->product );
				break;
		}

	}

	/**
	 * Make product columns sortable
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function product_sortable_columns( $columns ) {
		$custom = array(
			'product-price'     => 'hubaga-product-price',
			'product-sell-count'=> 'hubaga-product-sell-count',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Make coupon columns sortable
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function coupon_sortable_columns( $columns ) {
		$custom = array(
			'amount'     		=> 'hubaga-amount',
			'usage-count'       => 'hubaga-usage-count',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Make order columns sortable
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function order_sortable_columns( $columns ) {
		$custom = array(
			'order-number' 		=> 'ID',
			'order-total'  		=> 'hubaga-order-total',
			'discount-total'   	=> 'hubaga-discount-total',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Adds product metaboxes
	 *
	 * @return void
	 */
	public function add_product_metabox() {
		add_meta_box(
			'hubaga_product_details',
			esc_html__( 'Product Details', 'hubaga' ),
			array( $this, 'render_product_metabox' ),
			$this->product_post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Adds coupon metaboxes
	 *
	 * @return void
	 */
	public function add_coupon_metabox() {
		add_meta_box(
			'hubaga_coupon_details',
			esc_html__( 'Coupon Details', 'hubaga' ),
			array( $this, 'render_coupon_metabox' ),
			$this->coupon_post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Adds order metaboxes
	 *
	 * @return void
	 */
	public function add_order_metabox() {
		add_meta_box(
			'hubaga_order_details',
			esc_html__( 'Order Details', 'hubaga' ),
			array( $this, 'render_order_metabox' ),
			$this->order_post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Renders product metaboxes
	 *
	 * @return void
	 */
	public function render_product_metabox() {

		global $post;
		$product  = hubaga_get_product( $post->ID );
		$fields   = include $this->admin_dir . 'views/product-details.php';
		$fields   = apply_filters( 'hubaga_product_metabox_fields', $fields, $product );

		foreach( $fields as $key => $args ) {

			$args['id'] = $key;
			if( isset($product->{$key}) ){
				$args['default'] = $product->{$key};
			}

			do_action( "hubaga_product_metabox_before_add_$key", $product );
			hubaga_add_option( $args, 'hubaga_product_details' );
			do_action( "hubaga_product_metabox_after_add_$key", $product );

		}

		hubaga_elementa( 'hubaga_product_details' )->set_template( $this->admin_dir . 'views/product-template.php' );
		hubaga_elementa( 'hubaga_product_details' )->render();

	}

	/**
	 * Renders order metaboxes
	 *
	 * @return void
	 */
	public function render_order_metabox() {

		global $post;
		$order 		= hubaga_get_order( $post->ID );
		$fields 	= include $this->admin_dir . 'views/order-details.php';
		$fields 	= apply_filters( 'hubaga_order_metabox_fields', $fields, $order );

		foreach( $fields as $key => $args ) {

			$args['id'] = $key;
			if( isset($order->{$key}) ){
				$args['default'] 	= $order->{$key};
			}

			do_action( "hubaga_order_metabox_before_add_$key", $order );
			hubaga_add_option( $args, 'hubaga_order_details' );
			do_action( "hubaga_order_metabox_after_add_$key", $order );

		}

		hubaga_elementa( 'hubaga_order_details' )->set_template( $this->admin_dir . 'views/order-template.php' );
		hubaga_elementa( 'hubaga_order_details' )->render();

	}

	/**
	 * Renders coupon metaboxes
	 *
	 * @param  array $columns
	 * @return array
	 */
	public function render_coupon_metabox() {

		global $post;
		$coupon 	= hubaga_get_coupon( $post->ID );
		$fields 	= include $this->admin_dir . 'views/coupon-details.php';
		$fields 	= apply_filters( 'hubaga_coupon_metabox_fields', $fields, $coupon );

		foreach( $fields as $key => $args ) {

			$args['id'] = $key;
			if( isset($coupon->{$key}) ){

				if( $key == 'email_restrictions' ){
					$args['default'] 	= implode( ',', $coupon->email_restrictions );
				} else {
					$args['default'] 	= $coupon->{$key};
				}

			}

			do_action( "hubaga_coupon_metabox_before_add_{$key}", $coupon );
			hubaga_add_option( $args, 'hubaga_coupon_details' );
			do_action( "hubaga_coupon_metabox_after_add_{$key}", $coupon );

		}

		hubaga_elementa( 'hubaga_coupon_details' )->set_template( $this->admin_dir . 'views/coupon-template.php' );
		hubaga_elementa( 'hubaga_coupon_details' )->render();

	}

	/**
	 * Checks whether or not we should save the post type
	 *
	 * @param  string $post_type The post type to save
	 *
	 * @return bool
	 */
	public function can_save( $post_type ) {

		//No if this is an autosave request
		if ( defined( 'DOUNG_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		$nonce_key = "hubaga_{$post_type}_inner_custom_box_nonce";
		$nonce_val = "hubaga_{$post_type}_inner_custom_box";

		//Ensure the security nonce is available
		if(!isset( $_POST[$nonce_key])) {
			return false;
		}

		//validate the nonce
		if( wp_verify_nonce( $_POST[$nonce_key], $nonce_val ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Saves product metaboxes
	 *
	 * @param  array $post id
	 * @param  array $post
	 * @return int post id
	 */
	public function save_product_metabox( $post_id, $post ) {

		if(! $this->can_save( 'product' ) ){
			return $post_id;
		}

		$data 				= (array) $post;
		$data 				= array_merge( $data, $_POST );
		$data['sell_count'] = get_post_meta( $post_id, '_product_sell_count', true );
		$product 			= hubaga_get_product( $data );

		$product->save();
		do_action( "hubaga_saved_product_metabox", $product );
		return $post_id;

	}

	/**
	 * Saves coupon metaboxes
	 *
	 * @param  array $post id
	 * @param  array $post
	 * @return array
	 */
	public function save_coupon_metabox( $post_id, $post ) {

		if(! $this->can_save( 'coupon' ) ){
			return $post_id;
		}

		$data 						= (array) $post;
		$data 						= array_merge( $data, $_POST );
		$data['usage_count']		= get_post_meta( $post_id, '_coupon_usage_count', true );
		$data['email_restrictions']	= explode( ',', $_POST[ 'email_restrictions' ] );
		$coupon 					= hubaga_get_coupon( $data );

		$coupon->save();
		do_action( "hubaga_saved_coupon_metabox", $coupon );
		return $post_id;

	}

	/**
	 * Saves order metaboxes
	 *
	 * @param  array $post id
	 * @param  array $post
	 * @return array
	 */
	public function save_order_metabox( $post_id, $post ) {

		if(! $this->can_save( 'order' ) ){
			return $post_id;
		}

		$data					= (array) $post;
		$data 					= array_merge( $data, $_POST );
		$data['coupon']  		= get_post_meta( $post_id, '_order_coupon', true );
		$data['platform']  		= get_post_meta( $post_id, '_order_platform', true );
		$data['browser']  		= get_post_meta( $post_id, '_order_browser', true );
		$data['country']  		= get_post_meta( $post_id, '_order_country', true );
		$data['transaction_id']	= get_post_meta( $post_id, '_order_transaction_id', true );
		$order					= hubaga_get_order( $data );

		$order->save();
		do_action( "hubaga_saved_order_metabox", $order );
		return $post_id;

	}

	/**
	 * Sorts our custom columns
	 *
	 * @return array
	 */
	public function _sort( $query ) {
		if( is_admin() ) {

			$orderby = $query->get('orderby');
			switch( $orderby ) {

				case "hubaga-product-price":

					$query->set( 'meta_key', '_product_price' );
					$query->set( 'orderby', 'meta_value_num' );
					break;

				case "hubaga-product-sell-count":

					$query->set( 'meta_key', '_product_sell_count' );
					$query->set( 'orderby', 'meta_value_num' );
					break;

				case "hubaga-amount":

					$query->set( 'meta_key', '_coupon_amount' );
					$query->set( 'orderby', 'meta_value_num' );
					break;

				case "hubaga-usage-count":

					$query->set( 'meta_key', '_coupon_usage_count' );
					$query->set( 'orderby', 'meta_value_num' );
					break;

				case "hubaga-order-total":

					$query->set( 'meta_key', '_order_total' );
					$query->set( 'orderby', 'meta_value' );
					break;

				case "hubaga-discount-total":

					$query->set( 'meta_key', '_order_discount_total' );
					$query->set( 'orderby', 'meta_value_num' );
					break;

			}
		}
	}

	/**
	 * Prints footer scripts
	 *
	 * @return array
	 */
	public function footer_scripts() {
		global $post;
		if( $post->post_type == $this->order_post_type ){

			echo "<script>
			jQuery( document ).ready( function() {
				jQuery( '#misc-publishing-actions' ).find( '.misc-pub-section' ).first().remove();
				jQuery( '#save-action' ).remove();
			} );
			</script>";

		}
	}

}
endif; // class_exists check
