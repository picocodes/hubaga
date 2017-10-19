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
$view = esc_html__( 'Extensions', 'hubaga' );

echo '<div class="wrap elementa hubaga-extensions-template">';
echo "<h1>$view</h1>";

	foreach ( $elements as $element ) {
		$this->render_element( $element );
	}
		
echo '</div>';
