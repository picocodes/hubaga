<?php

/**
 * Outputs a card
 *
 *
 */	
if(! isset( $args['cards'] ) OR ! is_array ( $args['cards'] ) )
	return; //No cards to render

//Optional oppening wrapper
if( isset( $args['card_wrapper_start'] ) ) {
	echo $args['card_wrapper_start'];
}
$class = 'elementa-row ' . $args['class'];
//Main wrapper
echo "<div class='$class'>";

//Loop through the cards and display them
foreach( $args['cards'] as $card => $details ) {
	
	$class = 'col s12 ';
	$inner_class = ' ';
	if( isset( $details['card_class'] ) ) {
		$class .= $details['card_class'];
	}
	
	if( isset( $details['card_inner_class'] ) ) {
		$inner_class .= $details['card_inner_class'];
	}
		
	echo "<div class='$class'><div class='elementa-card $inner_class'>";
		if( isset( $details['card_image'] ) ) {
			$image = esc_url( $details['card_image'] );
			echo "<div class='elementa-card-image'><img src='$image'></div>";
		}
		
		if( isset( $details['card_content'] ) OR  isset( $details['card_title'] )) {

			echo '<div class="elementa-card-content">';
			if ( isset( $details['card_title'] ) )
				echo "<h3 class='elementa-card-title $inner_class'>{$details['card_title']}</h3>";
			
			if ( isset( $details['card_content'] ) )
				echo $details['card_content'];
			
			echo '</div>';
			
		}
		
		
		if( isset( $details['card_action'] ) ) {
			echo "<div class='elementa-card-action'>{$details['card_action']}</div>";
		}
		
	echo '</div></div>';
	
}

echo "</div>";
if( isset( $args['card_wrapper_end'] ) ) {
	echo $args['card_wrapper_end'];
}
