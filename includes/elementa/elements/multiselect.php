<?php

/**
 * Outputs elements for multiselect
 *
 *
 */
	$description = $args['description'];
	$class = 'form-control ' . $args['class'];
	$id = $args['__id'];
	$name = $id;
	$placeholder = $args['placeholder'];
	$value = $args['_value'];
	$options = $args['options'];

	echo "<select multiple name='{$name}[]' class='$class' id='$id' data-placeholder='$placeholder' data-allow-empty='true' data-allow-clear='true'>";
	
	//Loop over options and print them
	if ( is_array( $options ) ) {
		
		foreach ( $options as $key => $val ) {
			
			$value = esc_attr( $key );
			$current = $args['_value'];
			
			echo "<option value='$value' ";
			
			//Check if the current field is being shown
			if ( is_array( $current ) ) {
				
					selected( in_array( $value, $current ), true );
					
				} else {
					
					selected( $current, $value );
					
				}
			
			echo ">$val</option>";
		}
		
	}
	echo "</select>";
	
		
	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}