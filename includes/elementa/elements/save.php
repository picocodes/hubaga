<?php

/**
 * Outputs elements for save
 *
 *
 */
 	
	$description = $args['description'];
	$class = 'elementa-btn ' . $args['class'];
	$id = $args['__id'];
	$attr = $args['_custom_attributes'];
		
	echo "<hr> <input id='$id' name='elementa-save' type='submit' class='$class' value='Save' $attr>";
	echo "<input id='{$id}_reset' name='elementa-reset' type='submit' class='$class grey' value='Reset'>";
			
	if (! empty( $description ) )
		echo "<p class='descprition'>$description</p>";
