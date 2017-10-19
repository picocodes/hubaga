<?php

/**
 * Outputs elements for a switch
 *
 *
 */
	$type = $args['type'];
	$id = $args['__id'];
	$attr = $args['_custom_attributes'];
	$class = $args['class'];
	$class .= ' wpe-set-'. $type;
		
	$description = $args['description'];
	$current = $args['_current'];

	echo "<div class='switch'> <label><input type='checkbox' name='$id' value='1' $attr";
	checked( 1, $current );
	echo "><span class='lever $class'></span>$description</label></div>";
