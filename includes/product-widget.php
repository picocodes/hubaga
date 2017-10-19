<?php
/**
 * Product widgets
 *
 *
 * @link https://codex.wordpress.org/Widgets_API#Developing_Widgets
 */

class H_Product_Widget extends WP_Widget {

	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		parent::__construct( 'widget_product_title', esc_html__( 'Hubaga Product', 'hubaga' ), array(
			'classname'   => 'hubaga_widget_product',
			'description' => esc_html__( 'Display a product in the sidebar.', 'hubaga' ),
			'customize_selective_refresh' => true,
		) );

		$this->products = hubaga_elementa()->get_data( 'posts', array(
			'numberposts' => '-1',
			'post_type'	  => hubaga_get_product_post_type(),
			'post_status' => 'publish',
		));
	}

	/**
	 * Output the HTML for this widget.
	 *
	 * @access public
	 *
	 * @param array $args     An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		$_args = array(
			'id' 	=> isset( $instance['product_id'] ) && array_key_exists($instance['product_id'], $this->products ) ? $instance['product_id'] : 0,
			'class' => '',
		);

		echo $args['before_widget'];

		if ( !empty($instance['title']) )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];

		echo hubaga()->template->get_view_product_html( $_args );

		echo $args['after_widget'];

	}

	/**
	 * Handles updating settings for the current widget instance
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}
		if ( ! empty( $new_instance['product_id'] ) ) {
			$instance['product_id'] = (int) $new_instance['product_id'];
		}
		return $instance;
	}

	/**
	 * Display the form for this widget on the Widgets page of the Admin area.
	 *
	 *
	 * @param array $instance
	 */
	function form( $instance ) {
		$product_id  = empty( $instance['product_id'] ) ? '' : esc_attr( $instance['product_id'] );
		$title		 = empty( $instance['title'] ) ? '' : esc_html( $instance['title'] );
		?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'hubaga' ) ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'product_id' ); ?>"><?php _e( 'Select Product:', 'hubaga' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'product_id' ); ?>" name="<?php echo $this->get_field_name( 'product_id' ); ?>">
					<option value="0"><?php _e( '&mdash; Select &mdash;', 'hubaga' ); ?></option>
					<?php foreach ( $this->products as $product=>$label ) : ?>
						<option value="<?php echo esc_attr( $product ); ?>" <?php selected( $product_id, $product ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		<?php
	}
}
