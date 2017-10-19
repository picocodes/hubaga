<?php

/**
 * Outputs elements for a range slider
 *
 *
 */
	$type = $args['type'];
	$id = $args['__id'];
	$attr = $args['_custom_attributes'];
	$class = $args['class'];
	$class .= ' wpe-set-'. $type;
	
	//Labels for our switches
	$enabled = __( 'Yes', 'elementa' );
	$disabled = __( 'No', 'elementa' );
	
	if ( isset( $args['enabled'] ) ) {
		$enabled = $args['enabled'];
	}
	
	if ( isset( $args['disabled'] ) ) {
		$disabled = $args['disabled'];
	}
		
		
	$description = $args['description'];
	$current = $args['_current'];

	echo "<div class='switch'> <label>$disabled <input type='range' name='$id' value='1' $attr";
	checked( 1, $current );
	echo "><span class='lever $class'></span>$enabled</label></div>";

	// The description
	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}
