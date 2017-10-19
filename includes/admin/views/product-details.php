<?php
/**
 * Renders the product metabox
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(

	'type' 			=> array (
		'type' 		=> 'select',
		'options' 	=> hubaga_get_product_types(),
		'title' 	=> esc_html__( 'Product Type', 'hubaga' ),
	),

	'short_description' => array (
		'type' 			=> 'editor',
		'options' 		=> hubaga_get_product_types(),
		'title' 		=> esc_html__( 'Short Description', 'hubaga' ),
		'description' 	=> esc_html__( 'A short description about the product. Shortcodes are allowed.', 'hubaga' ),
	),

	'price' 			=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Price', 'hubaga' ),
		'description'	=> esc_html__( 'Do not include the currency symbol.', 'hubaga' ),
	),

	'download_name' 		=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Download Name', 'hubaga' ),
		'placeholder'	=> 'download.zip',
		'description'	=> esc_html__( 'This is the name by which the product will be saved.', 'hubaga' ),
	),

	'download_url' 		=> array (
		'type' 			=> 'text',
		'title' 		=> esc_html__( 'Download Url', 'hubaga' ),
		'placeholder'	=> 'https://example.com/downloads/download.zip',
		'description'	=> esc_html__( 'Link to the file. Your users won\'t see it.', 'hubaga' ),
	),

);
