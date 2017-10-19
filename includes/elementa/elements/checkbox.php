<?php

/**
 * Outputs checkboxes
 *
 *
 */

    $id = $args['__id'];
	$class =  $args['class'];
	$description = ''; ;
	$attr = $args['_custom_attributes'];
	
	if ( isset( $args['description'] ) ) {
		$description = esc_html( $args['description'] );
	}
	
	echo "<input name='{$id}' class='$class' value='1' id='$id' type='checkbox' $attr ";
					
				checked( $args['_value'], 1 );
	
			echo "><label for='$id'> $description </label>";
