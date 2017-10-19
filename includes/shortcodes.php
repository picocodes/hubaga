<?php

/**
 * Hubaga Shortcodes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'H_Shortcodes' ) ) :
/**
 * Hubaga Shortcode Class
 *
 * @since Hubaga 1.0.0
 */
class H_Shortcodes {
	
	/**
	 * @var object the template renderer
	 */
	public $template = null;

	/**
	 * Kickstarts everything
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @uses setup_globals()
	 * @uses add_shortcodes()
	 */
	public function __construct() {
		$this->add_shortcodes();	
		$this->template = hubaga()->template;
	}

	/**
	 * Registers shortocodes
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 *
	 * @uses apply_filters()
	 */
	private function add_shortcodes() {
		
		// Available shortcodes
		$shortcodes = apply_filters( 'hubaga_shortcodes', array(
			'h-product'    => array( $this, 'display_product'    ),
			'h-checkout'   => array( $this, 'display_checkout'   ),
			'h-account'    => array( $this, 'display_account'    ),
			'h-buy-button' => array( $this, 'display_buy_button' ),
		) );
		
		foreach ( $shortcodes as $code => $cb ) {
			add_shortcode( $code, $cb );
		}
	}
	
	/**
	 * Displays a single product
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @param array $attr
	 * @return string
	 */
	public function display_product( $atts ) {
		
		$options = shortcode_atts( array(
			'id' 	=> 0,
			'class' => '',
		), $atts );
		
		return $this->template->get_view_product_html( $options );
	}
	
	/**
	 * Displays a the checkout page
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @return string
	 */
	public function display_checkout() {		
		return $this->template->get_checkout_html();
	}
	
	/**
	 * Displays a the account page
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @return string
	 */
	public function display_account() {
		return $this->template->get_account_html();
	}
	
	/**
	 * Displays a list of products
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @return string
	 */
	public function display_buy_button( $atts, $content ) {
		
		if(! $content ) {
			$content = 'BUY';
		}
		
		$options = shortcode_atts( array(
			'id' 		=> 0,
			'class' 	=> '',
			'content' 	=> $content,
		), $atts );

		return $this->template->get_buy_button( $options );
	}

}
endif;