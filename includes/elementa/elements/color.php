<?php

/**
 * Outputs elements for color
 *
 *
 */
 	
	$type = 'text';
	$description = $args['description'];
	$class = 'elementa-color form-control ' . $args['class'];
	$id = $args['__id'];
	$placeholder = $args['placeholder'];
	$value = $args['_current'];
	$attr = $args['_custom_attributes'];
	
	echo "<div class='elementa-color-wrapper'><div class='elementa-color-preview' style='background: $value;'></div>";
	
	echo "<input value='$value' $attr  name='$id' id='$id' type='$type' class='$class' placeholder='$placeholder'/>";

	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}
	echo "</div>"; // elementa-color-wrapper