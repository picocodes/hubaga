<?php
/**
 * Template manager. Responsible for all rendering
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'H_Template' ) ) :
/**
 * Responsible for rendering the frontend
 *
 * @since  Hubaga 1.0.0
 */
class H_Template {

	/**
	 * @var string URL to the account page
	 */
	public $account_url = '';

	/**
	 * @var string URL to the checkout page
	 */
	public $checkout_url = '';

	/**
	 * Main constructor
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function __construct() {
		add_action( 'hubaga_init', array( $this, 'init' ), 5 );
	}

	/**
	 * Initialize everything
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function init() {
		$this->account_url 	= hubaga_get_account_url();
		$this->checkout_url = hubaga_get_checkout_url();
		$this->setup_actions();

		/**
		 * Fires after Hubaga Template Manage Initializes
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'hubaga_template_init' );
	}

	/**
	 * Setup the hooks.
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 *
	 */
	private function setup_actions() {

		//View order
		add_action( 'hubaga_view_order', array( $this, 'print_notices' ), 15 );
		add_action( 'hubaga_view_order', array( $this, 'print_order_details' ), 20 );
		add_action( 'hubaga_view_order', array( $this, 'print_order_downlods' ), 25 );

		//Checkout
		add_action( 'hubaga_checkout_form', array( $this, 'print_checkout_title' ), 5 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_notices' ), 10 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_checkout_form_open' ), 15 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_checkout_fields' ), 20 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_gateway_select' ), 30 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_order_total' ), 25 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_order_coupon_use' ), 26 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_checkout_submit' ), 35 );
		add_action( 'hubaga_checkout_form', array( $this, 'print_checkout_form_close' ), 40 );

		//Account page
		add_action( 'hubaga_account_page_html', array( $this, 'print_account_wrapper_open' ), 5 );
		add_action( 'hubaga_account_page_html', array( $this, 'print_notices' ), 10 );
		add_action( 'hubaga_account_page_html', array( $this, 'print_account_introduction' ), 15 );
		add_action( 'hubaga_account_page_html', array( $this, 'print_latest_orders' ), 20 );
		add_action( 'hubaga_account_page_html', array( $this, 'print_account_wrapper_close' ), 25 );

		//Instacheck
		if( hubaga_get_option( 'enable_instacheck' ) ) {
			add_action( 'wp_footer', array( $this, 'print_instacheck_template' ) );
		}

	}

	/**
	 * Returns the logout url
	 *
	 */
	public function get_logout_url( $redirect = '' ) {
		$redirect  	= $redirect ? $redirect : $this->account_url;
		$url 		= wp_logout_url( $redirect );
		return apply_filters( 'hubaga_logout_url', $url , $redirect );
	}

	/**
	 * Returns the lost password url
	 *
	 */
	public function get_lost_password_url( $redirect = '' ) {
		$redirect  = $redirect ? $redirect : $this->account_url;
		$url = wp_lostpassword_url( $redirect );
		return apply_filters( 'hubaga_lost_password_url', $url , $redirect );
	}

	/**
	 * Prints all available notices
	 *
	 */
	public function print_notices() {
		echo hubaga_get_notices_html();
	}


	/**
	 * Returns the login form html
	 *
	 */
	public function get_login_form( $args = array() ) {

		$defaults = array(
			'echo' 			=> false,
			'redirect' 		=> $this->account_url,
			'form_id' 		=> 'hubaga_loginform',
			'id_username' 	=> 'hubaga_user_login',
			'id_password' 	=> 'hubaga_user_pass',
			'id_remember' 	=> 'hubaga_rememberme',
			'id_submit' 	=> 'hubaga_wp-submit',
			'remember' 		=> true,
		);
		$args = wp_parse_args( $args, $defaults );
		$form = wp_login_form( $args );
		return apply_filters( 'hubaga_login_form', $form , $args );

	}

	/**
	 * Returns a html string that displays members of an array
	 *
	 */
	public function convert_array_to_html( $array, $args = array() ) {

		if (! is_array( $array ) or empty( $array ) )
			return '';

		$defaults = array(
			'wrapper' 		=> 'ul',
			'wrapper_class' => 'list-group',
			'element' 		=> 'li',
			'element_class' => 'list-group-item',
		);

		$args 			= (object) wp_parse_args( $args, $defaults );
		$return = "<{$args->wrapper} class='{$args->wrapper_class}'>";

		foreach( $array as $single ) {
			//<li>single</li> or <li> <ul>....</ul></li>
			$return .= "<{$args->element} class='{$args->element_class}'>";
			if( is_array( $single ) ){
				$return .= $this->convert_array_to_html( $single, (array) $args );
			} else {
				$return .= "$single";
			}

			$return .= "</{$args->element}>";
		}
		$return .= "</{$args->wrapper}>";

		return apply_filters( 'hubaga_array_to_html', $return, $array, $args );

	}

	/**
	 * Returns html that displays an order complete confirmation on the instacheck overlay
	 *
	 */
	public function get_instacheck_order_complete_html( $order = false ) {

		//Setup the order
		$order = hubaga_get_order( $order );
		if(! hubaga_is_order( $order ) ) {

			$error = __( 'This order is invalid.', 'hubaga' );
			hubaga_add_error( $error );
			return $error;

		}

		$msg = sprintf(
					__( '%1$s Order %2$s Thank you. We have sent you an email containing your downloads. If you have any questions, please contact us. %3$s', 'hubaga' ),
					'<div class="hubaga-instacheck"><div class="hubaga-checkout-heading"><h2>',
					"#$order->ID </h2></div><div class='hubaga-instacheck-order-complete'>",
					'</div></div>');

		return apply_filters( 'hubaga_instacheck_order_complete_html', $msg, $order );
	}

	/**
	 * Returns html that displays an order
	 *
	 */
	public function get_view_order_html( $order = false ) {

		//Setup the order
		$order = hubaga_get_order( $order );
		if(! hubaga_is_order( $order ) ) {

			$error = __( 'This order is invalid.', 'hubaga' );
			hubaga_add_error( $error );
			return $error;

		}

		ob_start();

		echo sprintf(
			__( '%1$s View order %2$s' ),
			"<div class='hubaga-checkout-heading'>",
			"<h2>#$order->ID</h2></div>"
		);

		$this->print_order_status( $order );

		echo "<div class='hubaga-order-start'>";
		/**
		 * Fires when the view order html is being printed
		 *
		 * EVENTS
		 *
		 * self::print_notices 15
		 * self::print_order_details 20
		 * self::print_order_downlods 25
		 *
		 * @since 1.0.0
		 */
		do_action( 'hubaga_view_order', $order );

		echo "</div>";

		$html = ob_get_clean();
		return $html;

	}

	/**
	 * Prints the order status info
	 *
	 */
	public function print_order_status( $order ) {
		$status 	= hubaga_get_order_status( $order );
		$statuses 	= hubaga_get_order_statuses();

		if( array_key_exists( $status, $statuses ) ){
			$_status = $statuses[ $status ];

			if( hubaga_is_array_key_valid( $_status, 'label', 'is_string' ) ) {
				$label = $_status[ 'label' ];
				echo sprintf( __( '%1$s This order has been marked as: %2$s', 'hubaga' ), "<div class='hubaga-order-status $status'>", "<strong>$label</strong></div>" );
			}
		}
	}

	/**
	 * Prints details about an order
	 *
	 */
	public function print_order_details( $order ) {

		echo '<div class="hubaga-order-details"><h3 class= "hubaga-order-details-title">' . __( 'Order Details', 'hubaga' ) . '</h3>';

		$order_details = hubaga_get_order_details( $order );
		$args = array(
			'wrapper_class' => 'hubaga-order-details',
			'element_class' => 'hubaga-order-detail hubaga-grid',
		);
		echo $this->convert_array_to_html( $order_details, $args );
		echo '</div>';

	}

	/**
	 * Prints the order downloads
	 *
	 */
	public function print_order_downlods( $order ) {

		//Get the order
		$order = hubaga_get_order( $order );

		//If the order is not complete; abort the mission
		if( ! hubaga_is_order_complete( $order ) )
			return;

		echo '<div class="hubaga-order-downloads"><h3 class= "hubaga-order-downloads-title">' . __( 'Order Downloads', 'hubaga' ) . '</h3>';

		//Fetch the downloads
		$order_downloads = hubaga_get_order_downloads( $order );
		if( empty( $order_downloads ) ) {
			echo '<p>' . __( 'No downloads found.', 'hubaga' ) . '</p>';
			return;
		}

		//Create the base url for all downloads
		$args = array(
			'action' => 'hubaga_download',
			'order'  => hubaga_get_order_id( $order ),
		);
		$url		 = add_query_arg( $args, $this->account_url );
		$order_token = get_transient( hubaga_get_order_id( $order ) . '_download_token' );

		if( $order_token ) {
			$url = add_query_arg( 'token', $order_token, $url );
		}


		$formatted_downloads = array();
		foreach( $order_downloads as $key => $args ) {

			$_url  = esc_url( add_query_arg( 'download_key', $key, $url ) );
			$name = 'download';
			if(! empty( $args['name'] ) )
				$name = sanitize_file_name( $args['name'] );

			$formatted_downloads[$key] = "<a href='$_url'>$name</a>";

		}

		$formatted_downloads = apply_filters( 'hubaga_order_downloads_html_list', $formatted_downloads, $order );

		$args = array(
			'wrapper_class' => 'hubaga-order-downloads',
			'element_class' => 'hubaga-order-download',
		);
		echo hubaga()->template->convert_array_to_html( $formatted_downloads, $args );

		echo '</div>';
	}

	/**
	 * Returns the checkout page html
	 *
	 */
	public function get_checkout_html( $product = false ) {

		/**
		 * Fires before checkout page html is printed
		 *
		 * EVENTS
		 *
		 * paypal validate PDT 5
		 *
		 * @since 1.0.0
		 */
		do_action( 'hubaga_before_checkout_page_html' );

		//If a form has already been set ( for example by a gateway ); return it
		if ( isset( hubaga()->checkout_form ) && !empty( hubaga()->checkout_form ) ) {
			return hubaga()->checkout_form;
		}

		//Setup the product
		if(! $product ) {
			if(! empty( $_REQUEST['product'] ) ){
				$product = $_REQUEST['product'];
			}

			if(! empty( $_REQUEST['hubaga_buy'] ) ){
				$product = $_REQUEST['hubaga_buy'];
			}

			if(! empty( $_REQUEST['hubagaBuy'] ) ){
				$product = $_REQUEST['hubagaBuy'];
			}

			if(! $product ){
				return __( 'Your cart is empty. Continue shopping!', 'hubaga' );
			}
		}

		$product = hubaga_get_product( $product );

		//Check the product availability
		if (! $product->can_buy() ) {
			return __( 'This product is invalid. Continue shopping!' );
		}

		// Great. render the checkout form
		ob_start();

		// Wraps the checkout
		$class = 'hubaga-checkout';
		if ( wp_doing_ajax() ) {
			$class = 'hubaga-instacheck';
		}

		echo "<div class='$class'>";
		/**
		 * Fires when the checkout html is being printed
		 *
		 * EVENTS
		 * self::print_checkout_title 5
		 * self::print_notices 10
		 * self::print_checkout_form_open 15
		 * self::print_checkout_fields 20
		 * self::print_gateway_select 25
		 * self::print_order_total	30
		 * self::print_checkout_submit 35
		 * self::print_checkout_form_close 40
		 *
		 * @since 1.0.0
		 */
		do_action( 'hubaga_checkout_form', $product );

		echo '<div>';
		$html = ob_get_clean();
		return $html;

	}

	/**
	 * Prints the checkout title
	 *
	 */
	public function  print_checkout_title( $product ) {
		$title = hubaga_get_product_title( $product );
		echo "<div class='hubaga-checkout-heading'><h2>$title</h2></div>";
	}

	/**
	 * Prints the checkout title
	 *
	 */
	public function  print_checkout_form_open( $product ) {
		echo "<form class='hubaga-checkout-form' method='post' action='$this->checkout_url'>";
	}

	/**
	 * Prints the checkout fields
	 *
	 */
	public function  print_checkout_fields( $product ) {

		$fields = hubaga_get_checkout_fields();
		foreach( $fields as $field => $args ){
			if( isset ( $args['html'] ) ){
				echo $args['html'];
			}
		}

	}

	/**
	 * Prints the gateway select radio buttons
	 *
	 */
	public function  print_gateway_select( $product ) {

		if( hubaga_is_product_free( $product ) ){
			return; //Free products don't require a payment gateway
		}

		$gateways = hubaga_get_active_gateways();
		if( empty( $gateways ) or !is_array( $gateways ) ){
			echo '<p style=" color: red; ">' . __( 'No gateway is available to process your order.', 'hubaga' ) .'</p>';
			return;
		}

		$extra_html = '';

		if (! wp_doing_ajax() ) {
			$extra_html = "onchange='this.form.submit()'";
		}

		foreach( $gateways as $gateway ) {

			$gateway = hubaga_get_gateway( $gateway );
			$value	 = esc_attr( $gateway->meta['id'] );
			$label	 = esc_html( $gateway->meta['button_text'] );
			$class	 = "hubaga-gateway-radio hubaga-gateway-$value";

			echo "<label class='$class' id='gateway_{$value}_label'> <input name='gateway' value='$value' id='gateway_$value' type='radio' $extra_html>$label </label>";

		}

	}

	/**
	 * Prints the order totals
	 *
	 */
	public function  print_order_total( $product ) {


		if( hubaga_is_product_free( $product ) ){
			$total = __( 'Free', 'hubaga' );
		} else {
			$total = hubaga_get_formatted_product_price( $product );
		}

		echo sprintf(
				__( '%1$s Order Total: %2$s %3$s' ),
				'<div class="hubaga-total-div">',
				"<span class='hubaga-order-total'>$total</span>",
				'</div>'
			);


	}

	/**
	 * Prints the insert coupon field
	 *
	 */
	public function  print_order_coupon_use( $product ) {

		// Free orders do not need a coupon
		if( hubaga_is_product_free( $product ) ){
			return;
		}

		// Are coupons enabled
		if(! hubaga_get_option( 'enable_coupons' ) ){
			return;
		}

		echo sprintf(
				__( '%1$s Have a coupon? %2$s Click here to enter your code %3$s' ),
				'<div class="hubaga-coupon-notice">',
				"<a href='#' class='hubaga-show-coupon'>",
				'</a></div>'
			);

		//The input button displaying a coupon
		echo '
			<div class="hubaga-grid hubaga-coupon-grid">
				<div class="hubaga-coupon-notices col ps10"></div>

				<div class="col ps7 hubaga-coupon-input-wrapper">
					<input type="text" class="hubaga-coupon-input" name="coupon_code" id="coupon_code" value="" />
				</div>

				<div class="col ps3 hubaga-coupon-button-wrapper">
					<a href="#" class="hubaga-btn hubaga-coupon-btn"> Apply </a>
				</div>

			</div>
		';
	}

	/**
	 * Product submit button
	 *
	 */
	public function  print_checkout_submit( $product ) {

		if(! hubaga_is_product_free( $product ) ){
			return; //No need for a submit button here
		}

		$value = __( 'Download', 'hubaga' );
		echo "<input type='submit' value='$value' class='hubaga-btn hubaga-gateway-radio'>";
	}

	/**
	 * Product the form close wrapper
	 *
	 */
	public function print_checkout_form_close( $product ) {
		$product = hubaga_get_product( $product );
		echo "<input type='hidden' name='hubaga_buy' value='{$product->ID}'>";
		echo "<input type='hidden' name='action' value='hubaga_handle_checkout'>";
		if ( ! empty( $_REQUEST['fetchedBy'] ) ) {
			$fetcher = esc_attr( $_REQUEST['fetchedBy'] );
			echo "<input type='hidden' name='fetchedBy' value='$fetcher'>";
		}
		wp_nonce_field( 'hubaga_checkout' );
		echo "</form>";
	}


	/**
	 * Returns the account page html
	 *
	 */
	public function get_account_html() {

		/**
		 * Fires before account page html is printed
		 *
		 * @since 1.0.0
		 */
		do_action( 'hubaga_before_account_page_html' );

		//Users must be logged in
		if(! is_user_logged_in() ){
			return $this->get_login_form();
		}

		//The user is requesting a single order
		if(! empty( $_REQUEST['view_order'] ) ) {
			$order = hubaga_get_order( $_REQUEST['view_order'] );

			// The order should exist and this user should own it
			if(! hubaga_is_order( $order ) OR $order->customer_id != get_current_user_id() ) {
				return __( 'Error: This order is invalid.', 'hubaga' );
			}

			return $this->get_view_order_html( $order );
		}

		ob_start();

		/**
		 * Fires when the account page is printed
		 *
		 * EVENTS
		 *
		 * self::print_account_wrapper_open 5
		 * self::print_account_introduction 10
		 * self::print_latest_orders 20
		 * self::print_account_wrapper_close 25
		 *
		 * @since 1.0.0
		 */
		do_action( 'hubaga_account_page_html' );

		$html = ob_get_clean();
		return $html;

	}

	/**
	 * Prints the account wrapper open
	 *
	 */
	public function print_account_wrapper_open() {
		echo "<div class='hubaga-account-wrapper'>";
	}

	/**
	 * Prints the account page introduction
	 *
	 */
	public function print_account_introduction() {
		$name 	= esc_html( hubaga_get_customer_name( get_current_user_id() ) );
		$logout = esc_url( $this->get_logout_url() );

		echo sprintf(
			 __( '%1$sWelcome %2$s.%3$s Logout %4$s', 'hubaga' ),
			 '<p>',
			 "<strong>$name</strong>",
			 "<a href='$logout' class='hubaga-logout-link'>",
			'</a></p>'
			 );
	}

	/**
	 * Prints the account page latest orders
	 *
	 */
	public function print_latest_orders() {

		$customer = hubaga_get_customer( get_current_user_id() );
		$orders = $customer->get_orders_by();

		echo '<h3>' . __( 'Latest Orders' , 'hubaga' ) . '</h3>';

		if( empty( $orders ) ) {
			echo sprintf( __( '%1$sYou have not bought any product.%2$s', 'hubaga' ), '<p>', '</p>' );
			return;
		}

		$_orders = array();
		$statuses= hubaga_get_order_statuses();

		echo '<ul class="hubaga-orders-list">';

		foreach ( $orders as $order ) {

			$order = hubaga_get_order( $order );
			if(! hubaga_is_order( $order ) )
				continue;

			$order_num = hubaga_get_order_id( $order );
			$order_url = hubaga_get_order_url( $order );
			$product   = hubaga_get_order_product( $order );

			if( empty ( $order_num ) )
				continue;

			//If it does not have a status then we outta here
			$status 	 = hubaga_get_order_status( $order );
			if( empty( $status ) or 'trash' == $status ) {
				continue;
			}

			$class	   = "hubaga-order-list-item hubaga-order-$order_num $status";

			echo sprintf(
				__( '%1$s ORDER #%2$s ', 'hubaga' ),
				"<li class='$class'><a href='$order_url'><strong>",
				"$order_num</strong></a>"
			);

			if( array_key_exists( $status, $statuses ) ) {

				$label = $statuses[$status]['label'];
				echo "<span class='order-status'>- $label</span></li>";

			}

			echo '</li>';

		}

		echo '</ul>';
	}

	/**
	 * Prints the account wrapper close
	 *
	 */
	public function print_account_wrapper_close() {
		echo '</div>';
	}

	/**
	 * Buy button
	 *
	 */
	public function get_buy_button( $args ) {

		extract( wp_parse_args( $args, array(
			'id' 		=> 0,
			'class' 	=> '',
			'content' 	=> 'BUY',
		)) );


		$product = hubaga_get_product( $id );
		if(! $product->is_product() ) {
			return esc_html__( 'Invalid product.', 'hubaga' );
		}

		//Link data
		$data  		 = array( 'hubaga_buy' => $product->ID );
		$price 		 = hubaga_get_formatted_product_price( $product );

		$url 		 = add_query_arg( $data, hubaga_get_checkout_url() );
		$url 		 = apply_filters( 'hubaga_product_buy_url', $url, $product, $args );

		$product_id	 = $product->ID;
		$content 	.= " <span class='hubaga-btn-price'>$price</span>";
		$class		 = 'hubaga-btn hubaga-buy ' . $class;
		$_data		 = "data-product='$product_id' data-action='hubaga_buy'";

		foreach( $data as $key=>$value ) {
			$value   = esc_attr( $value );
			$_data   .= " data-$key=$value";
		}

		return "<a href='$url' class='$class' $_data>$content</a>";
	}

	/**
	 * View Product Html
	 *
	 */
	public function get_view_product_html( $args ) {

		extract( wp_parse_args( $args, array(
			'id' 		=> 0,
			'class' 	=> '',
		)) );

		$product = hubaga_get_product( $id );
		if(! $product->is_product() ) {
			return esc_html__( 'Invalid product.', 'hubaga' );
		}

		if( $product->is_free() ){
			$total = esc_html__( 'Free', 'hubaga' );
		} else {
			$total = hubaga_get_formatted_product_price( $product );
		}

		$description = $product->short_description;
		$title		 = $product->post_title;
		$url 		 = add_query_arg( 'hubaga_buy', $product->ID, hubaga_get_checkout_url() );
		$content	 = esc_html__( 'Buy', 'hubaga' );
		$product_id	 = $product->ID;

		return "
			<div class='hubaga-product-card $class'>
				<div class='hubaga-product-card-header'></div>
				<div class='hubaga-product-card-body'>
					<h2>$title</h2>
					<p><span class='hubaga-card-price'>$total</span>$description</p>
					<p> <a href='$url' data-hubaga-buy='$product_id' data-product='$product_id' data-action='hubaga_buy'  class='hubaga-btn hubaga-buy'>$content</a></p>
				</div>
			</div>
		";
	}

	public function print_instacheck_template(){
		echo '
			<div class="hubaga-overlay-wrapper hubaga-loader-wrapper" style="display:none">
				<div class="hubaga-loader"></div>
			</div>
			<div class="hubaga-overlay-wrapper hubaga-instacheck-wrapper"  style="display:none">
				<div class="hubaga-instacheck-overlay-middle">
					<div class="hubaga-instacheck-overlay"></div>
				</div>
			</div>
		';
	}

}

endif; // class_exists check
