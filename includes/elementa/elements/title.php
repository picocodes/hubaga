<?php

/**
 * Renders a title
 *
 * Modified for our plugin. Not similar to the original element
 *
 *
 */
 $class = '';
 if( isset ( $args['section'] ) &&  $args['section'] ) {
	$section = sanitize_html_class( $args['section'] );
	$sub_section = sanitize_html_class( $args['sub_section'] );
	$class = "elementa-section-wrapper-$section elementa-sub_section-element-$section-$sub_section";
 }			

echo "<div class='$class'>";

	if ( isset( $args['title'] ) ) {
		echo '<h2 class="' . $args['class'] .  '">' . $args['title'] . '</h2>';
	}
	
	if ( isset( $args['subtitle'] ) ) {
		echo $args['subtitle'];
	}
	
	if (! empty( $args['description'] ) ) {
		echo "<br><small class='form-text text-muted'>{$args['description']}</small>";
	}
	
	echo '<div class="divider"></div></div>';
