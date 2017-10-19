<?php
/**
 * Renders the coupon metabox
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(
	
	'code' 				=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Coupon Code', 'hubaga' ),
		'description' 	=> esc_html__( 'The coupon code that customers enter during checkout. The shorter the better.', 'hubaga' ),
		'placeholder' 	=> 'SUMMERSALE',
	),
	
	'discount_type' 	=> array (
		'type' 			=> 'select',
		'options' 		=> hubaga_get_coupon_types(),
		'title' 		=> esc_html__( 'Coupon Type', 'hubaga' ),
		'description' 	=> esc_html__( 'The coupon type.', 'hubaga' ),
	),
	
	'amount' 			=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Amount', 'hubaga' ),
		'description' 	=> esc_html__( 'This is the amount that is discounted from the total order.', 'hubaga' ),
	),
	
	'date_expires' 		=> array (
		'type' 			=> 'date',
		'title' 		=> esc_html__( 'Expiry Date', 'hubaga' ),
		'placeholder' 	=> esc_html__( 'No expiry date', 'hubaga' ),
		'description' 	=> esc_html__( 'If you leave this blank then the coupon will never expire.', 'hubaga' ),
	),
	
	'usage_limit' 		=> array (
		'type' 			=> 'number',
		'title' 		=> esc_html__( 'Coupon Limit', 'hubaga' ),
		'placeholder' 	=> esc_html__( 'Unlimited', 'hubaga' ),
		'description' 	=> esc_html__( 'Set this to zero if you want the coupon to be used for an unlimited number of times.', 'hubaga' ),
	),
	
	'minimum_amount' 	=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Minimum Spend', 'hubaga' ),
		'placeholder' 	=> esc_html__( 'No Minimum Amount', 'hubaga' ),
		'description' 	=> esc_html__( 'The coupon will only be valid if the user spents at least the value you provide here.', 'hubaga' ),
	),
	
	'maximum_amount' 	=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Maximum Spend', 'hubaga' ),
		'placeholder' 	=> esc_html__( 'No Maximum Amount', 'hubaga' ),
		'description' 	=> esc_html__( 'Set this to 0 if you do not want to prevent users that spent more than the given amount from using this coupon.', 'hubaga' ),
	),
	
	'product_ids' 		=> array (
		'type' 			=> 'multiselect',
		'data_args'		=> array( 'post_type' => hubaga_get_product_post_type() ),
		'data' 			=> "posts",
		'title' 		=> esc_html__( 'Products', 'hubaga' ),
		'placeholder' 	=> esc_html__( 'Applicable on all products', 'hubaga' ),
		'description' 	=> esc_html__( 'Leave this blank if you want it applicable on all products.', 'hubaga' ),
	),
	
	'excluded_product_ids'	=> array (
		'type' 				=> 'multiselect',
		'data_args'			=> array( 'post_type' => hubaga_get_product_post_type() ),
		'data' 				=> "posts",
		'title' 			=> esc_html__( 'Excluded Products', 'hubaga' ),
		'placeholder' 		=> esc_html__( 'Do not exclude any product', 'hubaga' ),
		'description' 		=> esc_html__( 'Leave this blank if you do not want to exclude any products.', 'hubaga' ),
	),
	
	'email_restrictions'	=> array (
		'type' 				=> 'tag',
		'title' 			=> esc_html__( 'Emails', 'hubaga' ),
		'placeholder' 		=> esc_html__( 'Can be used by anyone', 'hubaga' ),
		'description' 		=> esc_html__( 'Leave this blank if you do not want to restrict the coupon to specific customer email addresses.', 'hubaga' ),
	),
	
);
