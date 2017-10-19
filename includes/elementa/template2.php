<?php
/**
 * This template looks best if you enable sections and full fields on all your fields
 * Settings template
 * Don't edit this file directly; instead use the `pc_settings_template` filter hook to 
 * provide the path to your own hook. The callback will be passed the name of the current 
 * template as the first parameter and the the current instances object as the second 
 * parameter
 *
 * The following variables are available for use
 * $method - form method
 * $tabs - This instance's tab list
 * $sections - The current tab's sections
 * $args - This instance's argument list
 * $this - This instance's object
 *
 * Don't make any changes to the instance's object. Instead use one of the many filters.
 * 
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !isset ( $elements ) OR ! is_array ( $elements ) ) {
	return;
}

$sections = array_unique ( $this->element_pluck('section') );
$sub_sections = array_unique ( $this->element_pluck('subsection') );
;
if ( count( $sections ) > 0 ) :
?>
<div class="elementa elementa-container">
	<div class="elementa-row">
		<div class="col s12 m4 l3" style="margin-top: 0.5rem;">
			<div class="list-group">
				<?php
					$first = true;
					foreach ( $sections as $section ) {
						
						if (! is_string( $section ) )
							continue;
						
						$id = 'wp-section-wrapper-' . sanitize_html_class( $section );
						$_section = ucwords( str_replace( '_', ' ',  $section  ) );
						$class = 'wp-section-wrapper list-group-item list-group-item-action';
						
						if ( $first ) {
							$class .= ' active';
							 $first = false;
						}
						
						echo "<a href='#' id='$id' class='$class'>$_section</a>";
						
					}
					
				?>
			</div>
		</div>
		<form class="col s12 m8 l7" action="#" method="post">
			<?php
				foreach ( $elements as $element ) {
					$this->render_element( $element );			
				}
				//Keep this line in your templates else options wont be saved
				wp_nonce_field( 'wp-elements' );
			?>
		</form>
	</div>
</div>
<script>
	( function( $ ) {
		//Elements in a section have a class that is similar to its id
		var active = $('.wp-section-wrapper.active').attr('id');

		/* Hide inactive elements; We didnt do this via css so as to support non-js browsers
		 * We also didnt target the .form-group class so as to enable rendering 
		 * non-sectioned elements. This way they will render on all pages
		 */
		$('[class*="wp-section-wrapper-"]:not(.' + active + ')').addClass('d-none');
		
		$('.wp-section-wrapper').on('click', function(){
			
			$('#' + active ).removeClass('active');
			$( this ).addClass('active');
			active = $( this ).attr('id');
			$('[class*="wp-section-wrapper-"]:not(.' + active + ')').addClass('d-none');
			$('.' + active ).removeClass('d-none');
		
		});		
	})( jQuery );
</script>

<?php
else :
?>

<div class="wp-elements-wrapper">
	<div class="container-left">
		<form action="#" method="post">
			<?php
				foreach ( $elements as $element ) {
					$this->render_element( $element );			
				}
				
				//Keep this line in your templates else options wont be saved
				wp_nonce_field( 'wp-elements' );
			?>
		</form>
	</div>
</div>
<?php
endif;