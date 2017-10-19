<?php
/**
 * Renders the product metabox
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !isset ( $elements ) OR ! is_array ( $elements ) ) {
	return;
}

//Current post
global $post;
$product = hubaga_get_product( $post->ID );

echo '<div class="wrap elementa" id="hubaga_product_editor">';

	foreach ( $elements as $element ) {
		$this->render_element( $element );
	}
	//Security check
	wp_nonce_field('hubaga_product_inner_custom_box','hubaga_product_inner_custom_box_nonce');

echo '</div>';
?>
<script>

( function( $ ) {
	new $.Elementa({ 'id' : 'hubaga_product_editor' })
} )( jQuery );
</script>
