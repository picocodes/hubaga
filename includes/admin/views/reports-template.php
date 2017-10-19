<?php
/**
 * Renders the reports
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !isset ( $elements ) OR ! is_array ( $elements ) ) {
	return;
}

$title = esc_html__( 'Reports', 'hubaga' );
echo "
	<div class='wrap elementa white' id='hubaga_reports'>
		<h1>$title</h1>";

	foreach ( $elements as $element ) {
		$this->render_element( $element );
	}

echo '</div>';

?>

<script>

( function( $, data ) {

	//Main reporting instance
	var reports = hubaga.reportsVue( data, '#hubaga_reports' );

	$(window).on( 'load', function(){
		$('#wpfooter').hide();
		console.log( $('#wpfooter') );
	})
	<?php
	/**
	 * Fires when generating the reports screen
	 * @since 1.0.0
	 */
	do_action( 'hubaga_reports_js' );
	?>
} )( jQuery, <?php echo wp_json_encode( hubaga()->report_data ) ?> );
</script>

<?php
