<?php

/**
 * Renders an extension card
 *
 *
 */
	$description 	= esc_html( $args['description'] );
	$id 			= $args['__id'];
	$title 			= esc_html( $args['title'] );
	$price 			= $args['price'];
	$view 			= esc_html__( 'View', 'hubaga' );

	echo "
		<div class='hubaga-hubaga-product-card'>
			<div class='hubaga-hubaga-product-card-header deep-orange'></div>
			<div class='hubaga-hubaga-product-card-body'>
					<h2>$title</h2>
					<p><span class='hubaga-hubaga-card-price z-depth-1'>$price</span>$description</p>
					<p> <a href='https://hubaga.com/extensions/$id' class='hubaga-hubaga-btn hubaga-hubaga-buy elementa-btn deep-orange white-text'>$view</a></p>
			</div>
		</div>
	";