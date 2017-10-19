<?php

/**
 * Outputs a tagging element
 *
 *
 */
	$description = $args['description'];
	$class = 'form-control hubaga-tagger' . $args['class'];
	$id = $args['__id'];
	$placeholder = $args['placeholder'];
	$value = $args['_value'];
	$options = $args['options'];

	echo "<input type='text' value='$value' name='$id' class='$class' id='$id' placeholder='$placeholder'>";	
		
	if (! empty( $description ) ) {
		echo "<p class='descprition'>$description</p>";
	}
	
	echo "
	<script>
		if ( typeof ( jQuery.fn.selectize ) != 'undefined' ) {
			jQuery('#$id').selectize({
				create : true,
				mode   : 'multi',
			});
		}
	</script>
	";