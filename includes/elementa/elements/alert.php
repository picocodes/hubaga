<?php

/**
 * Outputs an alert box
 *
 *
 */
	$not_dismissible = ( isset( $args['alert_dismissible'] ) && $args['alert_dismissible'] == false );
 	$class = 'alert ';
	
	if( isset( $args['state'] )) {
		$class .= ' alert-' . $args['state'] . ' ';
	} else {
		$class .= ' alert-warning ';
	}
	
	if( isset( $args['animation'] ) ) {
		$class .= ' ' . $args['animation'] . ' ';
	}
	
	if(! $not_dismissible ) {
		$class .= ' alert-dismissible ';
	}
	
	if( isset( $args['class'] ) ) {
		$class .= ' ' . $args['class'] . ' ';
	}
	
	$class .= ' active ';
	
	if(! isset( $args['text'] ) ) {
		$args['text'] = ' ';
	}

	$id = $args['__id'];
	
	echo "<div id='$id' class='$class' role='alert'>";
	
	if(! $not_dismissible ) {
		
		echo '<button type="button" class="close alert-close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span> </button>';
 
	}
	echo "{$args['text']} </div>";