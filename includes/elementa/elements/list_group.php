<?php

/**
 * Outputs a list group
 *
 *
 */
 if (! isset( $args['lists'] ) OR ! is_array( $args['lists'] ) ) {
	if( isset( $args['options'] ) && !empty ( $args['options'] ) ) {
		$args['lists'] = array_values( $args['options'] );
	} else {
	  return;		
	}
 }

?>
<ul class="list-group">
	<?php 
		foreach( $args['lists'] as $list ) {
			if( is_array( $list ) && isset( $list['data'] ) ) {
				
				$active = (isset( $list['active'] ) && $list['active'] == true ) ? 'active' : '';
				$list = $list['data'];
				
			} else {
				
				$active = '';

			}
			echo "<li class='list-group-item $active'> $list </li>";
		}
	?>
</ul>