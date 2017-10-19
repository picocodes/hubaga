<?php

/**
 * Outputs radio buttons
 *
 *
 */
    $id = $args['__id'];
	$class =  $args['class'];
	$attr = $args['_custom_attributes'];
	$options = $args['options'];
	$descprition = $args['description'];
	
	foreach( $options as $name => $label ) {
		echo "<p><input name='$id' class='$class' value='$name' id='{$id}_$name' type='radio' $attr ";
					
				checked( $args['_value'], $name );
	
			echo "><label for='{$id}_$name'> $label </label></p>";
	}
	
	if (! empty( $descprition ) ) {
		echo "<p class='descprition'>$descprition</p>";
	}
