<?php

/**
 * Outputs the WordPress Editor
 *
 *
 */
	$description = $args['description'];
	$id = $args['__id'];
	$value = $args['_value'];
	$options = array(
		'textarea_rows'       => 5,
		'teeny'               => true,
	);
	
	wp_editor( $value, $id, $options );
	
	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}