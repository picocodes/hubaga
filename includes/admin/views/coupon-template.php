<?php
/**
 * Renders the coupon metabox
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !isset ( $elements ) OR ! is_array ( $elements ) ) {
	return;
}
echo '<div class="wrap elementa" id="hubaga_coupon_editor" style="max-width: 720px">';

	foreach ( $elements as $element ) {
		$this->render_element( $element );
	}
	//Security check
	wp_nonce_field('hubaga_coupon_inner_custom_box','hubaga_coupon_inner_custom_box_nonce');
	
echo '</div>';

?>	

<script>

( function( $ ) {
	new $.Elementa({ 'id' : 'hubaga_coupon_editor' })
} )( jQuery );
</script>