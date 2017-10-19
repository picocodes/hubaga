<?php

/**
 * Outputs elements for textarea
 *
 *
 */
 	
	$type = 'textarea';
	$description = $args['description'];
	$class = 'form-control ' . $args['class'];
	$id = $args['__id'];
	$placeholder = $args['placeholder'];
	$value = format_to_edit( $args['_current'] );
	$attr = $args['_custom_attributes'];
	$rows = (isset ( $args['textarea_rows'] )) ? $args['textarea_rows'] : 10;

	echo "<textarea rows='$rows' $attr name='$id' id='$id' class='$class' placeholder='$placeholder'>$value</textarea>";

	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}
	