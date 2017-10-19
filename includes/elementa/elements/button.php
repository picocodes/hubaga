<?php

/**
 * Outputs elements for button
 *
 *
 */
 	
	$description = $args['description'];
	$class = 'elementa-btn ' . $args['class'];
	$id = $args['__id'];
	$value = ucfirst($type);
	$attr = $args['_custom_attributes'];
	$href = '#';
	
	if( isset( $args['state'] )) {
		$class .= ' btn-' . $args['state'];
	}
	
	if( isset( $args['href'] )) {
		$href = esc_url( $args['href'] );
	}
	
	echo "<a id='$id' href='$href' class='$class' $attr>$value</a>";
			
	if (! empty( $description ) )
		echo "<p class='descprition'>$description</p>";
