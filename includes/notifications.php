<?php
/**
 * Notifications handler
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'H_Notifications' ) ) :
/**
 * Responsible for notifying users about important store events
 *
 * @since Hubaga 1.0.0
 */
class H_Notifications {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Returns an instance of the class
	 * @param string $gateway gateway id
	 * @param object $object gateway object instance
	 * @return void
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Main constructor
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function __construct() {
		add_action( 'hubaga_init', array( $this, 'init' ), 15 );
	}

	/**
	 * Initialize everything
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function init() {
		$this->blogname		= wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$this->admin_email	= wp_specialchars_decode( get_option( 'admin_email' ), ENT_QUOTES );
		$this->setup_actions();

		/**
		 * Fires after Hubaga Notifications handler initializes
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'hubaga_notifications_init' );
	}

	/**
	 * Setup the hooks. Overwrite this hooks in your themes for custom looks
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 */
	private function setup_actions() {

		//Order created
		add_action( 'hubaga_order_created',  array( $this, 'order_created' ), 10, 3 );

		//Order refunded
		add_action( 'hubaga_order_refunded',  array( $this, 'order_refunded' ), 10, 3 );

		//Order cancelled
		add_action( 'hubaga_order_cancelled',  array( $this, 'order_cancelled' ), 10, 3 );

		//Order failed
		add_action( 'hubaga_order_failed',  array( $this, 'order_failed' ), 10, 3 );

		//Order completed
		add_action( 'hubaga_order_completed',  array( $this, 'order_completed' ), 10, 3 );

		//Customer created
		add_action( 'hubaga_customer_created',  array( $this, 'send_new_customer_email' ), 10, 2 );

	}

	/**
	 * Delivers emails
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function send_emails( $subject, $body, $emails ) {
		foreach( array_unique($emails) as $email ) {
			$this->send_mail( $email, $subject, $body );
		}
	}

	/**
	 * Sends an email
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function send_mail( $to, $subject, $body ) {

		//Set email variables
		$email_header_bg    = hubaga_get_option('mailer_header_bg');
		$email_header_color = hubaga_get_option('mailer_header_color');
		$business_address   = hubaga_get_option('mailer_business_address');
		$email_header_title = $this->blogname;
		if(isset($body['title'])){
			$email_header_subtitle = $body['title'];
		}
		$email_content = $body['content'];

		ob_start();
		require hubaga_get_includes_path( 'data/email-template.php' );
		$message = ob_get_clean();

		add_filter( 'wp_mail_content_type', array( $this, 'mail_content_type' ) );
		add_filter( 'wp_mail_from', array( $this, 'mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );

		$return  = wp_mail( $to, $subject, $message );

		remove_filter( 'wp_mail_content_type', array( $this, 'mail_content_type' ) );
		remove_filter( 'wp_mail_from', array( $this, 'mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );

		return $return;
	}

	/**
	 * Returns the email content type
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function mail_content_type() {
		return 'text/html';
	}

	/**
	 * Returns the email from
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function mail_from( $_email ) {

		$email = sanitize_email( hubaga_get_option( 'mailer_from_email' ) );
		if( is_email( $email ) ){
			return $email;
		}
		return $_email;

	}

	/**
	 * Returns the email from name
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function mail_from_name( $_from ) {

		$from = hubaga_get_option( 'mailer_from_name' );
		if( $from ){
			return $from;
		}
		return $_from;

	}

	/**
	 * Sends an email to admin and customer when an order is complete
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function order_completed( $order ) {

		$emails	= array(
			hubaga_get_order_customer_email( $order ),
			$this->admin_email
		);

		$subject 	= sprintf( esc_html__( "(%s) - Your order is complete", 'hubaga' ), $this->blogname );
		$title		= esc_html( sprintf(
						__( "Hi %s. Your recent order (#%s) is complete.", 'hubaga' ),
						hubaga_get_order_customer_name( $order ),
						$order->ID
					) );

		$body 		= array(
			'title'  => $title,
			'content'=> $this->order_details( $order ) ,
		);

		$this->send_emails( $subject, $body, $emails );
	}

	/**
	 * Sends an email when an order fails
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function order_failed( $order ) {

		$emails	= array(
			hubaga_get_order_customer_email( $order ),
			$this->admin_email
		);

		$subject 	= sprintf( esc_html__( "(%s) - Your order has failed processing", 'hubaga' ), $this->blogname );
		$title		= esc_html( sprintf(
			__( "Hi %s. Your recent order (#%s) has been marked as failed.", 'hubaga' ),
			hubaga_get_order_customer_name( $order ),
			$order->ID
			) );

		$body = array(
			'title'  => $title,
			'content'=> $this->order_details( $order ) ,
		);

		$this->send_emails( $subject, $body, $emails );
	}

	/**
	 * Sends an email when an order is refunded
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function order_refunded( $order ) {

		$emails	= array(
			hubaga_get_order_customer_email( $order ),
			$this->admin_email
		);

		$subject 	= sprintf( esc_html__( "(%s) - Your order has been refunded", 'hubaga' ), $this->blogname );
		$title		= esc_html( sprintf(
						__( "Hi %s. Your order (#%s) has been refunded.", 'hubaga' ),
						hubaga_get_order_customer_name( $order ),
						$order->ID
					) );

		$body 		= array(
			'title'  => $title,
			'content'=> $this->order_details( $order ) ,
		);

		$this->send_emails( $subject, $body, $emails );
	}

	/**
	 * Sends an email when an order is cancelled
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function order_cancelled( $order ) {

		$emails	= array(
			hubaga_get_order_customer_email( $order ),
			$this->admin_email
		);

		$subject 	= sprintf( esc_html__( "(%s) - Order cancelled", 'hubaga' ), $this->blogname );
		$title		= esc_html( sprintf(
			__( "Hi %s. Your order (#%s) was cancelled.", 'hubaga' ),
			hubaga_get_order_customer_name( $order ),
			$order->ID
		) );

		$body 		= array(
			'title'  => $title,
			'content'=> $this->order_details( $order ) ,
		);

		$this->send_emails( $subject, $body, $emails );
	}

	/**
	 * Sends an email when an order is created
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function order_created( $order ) {

		$emails	= array(
			hubaga_get_order_customer_email( $order ),
			$this->admin_email
		);

		$subject 	= sprintf( esc_html__( "(%s) - New order", 'hubaga' ), $this->blogname );
		$title		= esc_html( sprintf(
				__( "Hi %s. Here are details about your recent order.", 'hubaga' ),
				hubaga_get_order_customer_name( $order ),
				$order->ID
		) );

		$body 		= array(
		'title'  => $title,
		'content'=> $this->order_details( $order ) ,
		);

		$this->send_emails( $subject, $body, $emails );
	}


	/**
	 * Sends an to new customers
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function send_new_customer_email( $customer_id, $customer_data ) {

		$emails		= array( $customer_data['user_email'] );
		$subject 	= sprintf( esc_html__( "(%s) - Account created", 'hubaga' ), $this->blogname );
		$title		= esc_html( sprintf(
						__( "Hi %s. Your account at %s has been created.", 'hubaga' ),
						$customer_data['user_login'],
						$this->blogname
					) );

		$body 	= array(
				'title'  => $title,
				'content'=> esc_html( sprintf(
						__( "Your username is %s and your password is %s. You can login here: %s ", 'hubaga' ),
						$customer_data['user_login'],
						$customer_data['user_pass'],
						hubaga_get_account_url()
					) ) ,
		);

		$this->send_emails( $subject, $body, $emails );

	}

	/**
	 * Generates order details HTML
	 */
	function order_details( $order ){
		//total, subtotal, discount, product, gateway, email, download
		$order_details = hubaga_get_order_details( $order, false );
		if( hubaga_is_order_complete( $order ) ){
			$order_details['More'] = esc_html( sprintf(
					__( "%sView more details and download your files. %s", 'hubaga' ),
					'<a href="' . hubaga_get_order_url( $order ) . '>',
					'</a>'
				) );
		}

		$return = '<table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0;"><tbody>';

		foreach( $order_details as $left => $right ){
			$return .= '
				<tr style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
					<td style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0; text-align: left;" valign="top"> ' . $left . '</td>
					<td class="alignright" style="font-family: Helvetica Neue,Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; vertical-align: top; text-align: right; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top"> ' . $right . '</td>
				</tr>
			';
		}
		return $return . '</tbody></table>';

	}

}

endif; // class_exists check
