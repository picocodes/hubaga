<?php

/**
 * Outputs elements for date
 *
 *
 */
 	
	$description = $args['description'];
	$class = 'wpe-date-control form-control ' . $args['class'];
	$id = $args['__id'];
	$placeholder = $args['placeholder'];
	$value = $args['_current'];
	$attr = $args['_custom_attributes'];
	
	echo "<input value='$value' $attr  name='$id' id='$id' type='text' class='$class' placeholder='$placeholder'/>";

	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}
	