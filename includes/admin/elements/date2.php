<?php

/**
 * Outputs a custom date element
 *
 *
 */
	$description = $args['description'];
	$class = 'wpe-date-control form-control ' . $args['class'];
	$id = $args['__id'];
	$placeholder = $args['placeholder'];
	$value = $args['_current'];
	$attr = $args['_custom_attributes'];
	
	echo "<div class = 'elementa-row'>
				<div class = 'col s8'>
					<input value='$value' $attr  name='$id' id='$id' type='text' class='$class' placeholder='$placeholder'/>
				</div>
				<div class = 'col s8'>
					<input value='$value' $attr  name='$id' id='$id' type='text' class='$class' placeholder='$placeholder'/>
				</div>
		";
	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}
