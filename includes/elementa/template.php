<?php
/**
 * 
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !isset ( $elements ) OR ! is_array ( $elements ) ) {
	return;
}

$current_section = $instance_args[ 'active_section' ];
?>
<div class="wrap elementa" id="<?php echo $instance_id;?>">
	<h1> Hubaga </h1>
	<nav class="nav-tab-wrapper" style="margin-bottom: 2rem;">
			<?php
				
				//Display a list of available sections
				foreach ( $sections as $section => $sub_sections ) {
					
					if( isset( $_GET['sub_section'] ) && in_array( urldecode( $_GET['sub_section'] ), $sub_sections )){
						$default_section= urldecode( $_GET['sub_section'] );
					} else {
						$default_section= empty( $sub_sections ) ? $section : $sub_sections[0];
					}
					
					$default_section= sanitize_html_class( $default_section );
					$section		= esc_html( $section );
					$section_clean 	= sanitize_html_class( $section );
					$id 			= 'elementa-section-wrapper-' . sanitize_html_class( $section );
					$class 			= sanitize_html_class($current_section) == $section_clean ? 'nav-tab-active' : '';
					
					echo "<a href='#$section_clean' id='$id' data-default-section='$default_section' data-section = '$section_clean' class='elementa-section-wrapper nav-tab $class'>$section</a>";
				
				}
			?>
	</nav>
	<?php
					
		//Display the sub sections in each section
		foreach ( $sections as $section => $sub_sections ) {
		
			if( !is_array( $sub_sections ) ) continue;
			if( count( $sub_sections ) < 2 ) continue;
			
			$section = sanitize_html_class($section);
			echo "<ul class='elementa_sub_section_wrapper elementa_section_$section d-none'>";
			foreach( $sub_sections as $sub ){
				$_sub = esc_html( $sub );
				$sub  = sanitize_html_class( $sub );
				
				echo "<li><a href='#' data-section='$section' data-sub-section='$sub' class='elementa-sub_section-changer elementa_sub_section_$sub'>$_sub</a></li>";
			}
			
			echo "</ul>";
		}
				
	?>
	<form action="" method="post" style=" max-width: 610px;">
		<?php
				
			foreach ( $elements as $element ) {
				$this->render_element( $element );
			}
			
			//Keep the following lines if you would like elementa to save options for you
			wp_nonce_field( 'elementa' );
			echo "<input name='elementa_action' type='hidden' value='$instance_id' />";
		?>
	</form>

</div>
<script>
	( function( $, _args ) {
		new $.Elementa( _args );
	})( jQuery, <?php echo wp_json_encode( $instance_args );?> );
</script>
<?php
