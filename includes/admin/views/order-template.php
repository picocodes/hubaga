<?php
/**
 * Renders the order metabox
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !isset ( $elements ) OR ! is_array ( $elements ) ) {
	return;
}

global $post;

echo '<div class="wrap elementa" id="hubaga_order_editor">
		<div class="elementa-row">
			<div class="col s12" style="max-width: 600px;">
	';
				echo "<h1>Order #$post->ID</h1><div class='divider'></div>";
				
				foreach ( $elements as $element ) {
					$this->render_element( $element );
				}
				//Security check
				wp_nonce_field('hubaga_order_inner_custom_box','hubaga_order_inner_custom_box_nonce');
	
		echo'</div>
			<div class="col s12" style="max-width: 500px;">';
				hubaga()->Elementa('hubaga_order_details')->render_element( array( 'type' => 'order_overview', 'id' => 'order_overview' ) );
		echo'</div>
		</div>
	</div>';
?>	
<script>

( function( $ ) {
	new $.Elementa({ 'id' : 'hubaga_order_editor' })
} )( jQuery );
</script>
