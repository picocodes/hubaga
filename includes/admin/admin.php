<?php
/**
 * Main Hubaga Admin Class
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


if ( !class_exists( 'H_Admin' ) ) :
/**
 * Loads Hubaga plugin admin area
 *
 * @since Hubaga 1.0.0
 */
class H_Admin {

	/**
	 * @var string Path to the Hubaga admin directory
	 */
	public $admin_dir = '';

	/**
	 * @var string URL to the Hubaga admin directory
	 */
	public $admin_url = '';

	/**
	 * @var string URL to the Hubaga images directory
	 */
	public $images_url = '';

	/**
	 * @var string URL to the Hubaga admin css directory
	 */
	public $css_url = '';

	/**
	 * @var string URL to the Hubaga admin js directory
	 */
	public $js_url = '';

	/**
	 * The main Hubaga constructor
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function __construct() {
		add_action( 'hubaga_init', array( $this, 'init' ), 1 );
	}

	/**
	 * The main Hubaga admin loader
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function init() {
		$this->setup_globals();
		$this->register_custom_elements();
		$this->includes();
		$this->setup_actions();

		/**
		 * Fires after Hubaga admin initializes
		 *
		 * @since 1.0.0
		 *
		*/
		do_action( 'hubaga_admin_init' );
	}

	/**
	 * Admin globals
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 */
	private function setup_globals() {

		$this->admin_dir  = hubaga_get_includes_path( 'admin' ); // Admin path
		$this->admin_url  = hubaga_get_includes_url( 'admin' ); // Admin url
		$this->images_url = $this->admin_url   	. 'assets/images/'; // Admin images URL
		$this->css_url    = $this->admin_url   	. 'assets/css/'; // Admin css URL
		$this->js_url     = $this->admin_url   	. 'assets/js/'; // Admin js URL

	}

	/**
	 * Registers custom ELementa elements
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 */
	private function register_custom_elements() {

		$elementa = hubaga_elementa();
		//Tagging element
		$elementa->register_element( 'tag', array(
			'callback' 	=> array( $this, 'render_element'),
			'enqeue' 	=> array( $elementa, 'enque_select'),
		) );

		//Editor
		$elementa->register_element( 'editor', array(
			'callback' 	=> array( $this, 'render_element'),
		) );

		//Order overview
		$elementa->register_element( 'order_overview', array(
			'callback' 	=> array( $this, 'render_element'),
			'render_default_markup' => false,
		) );

		//Reports header
		$elementa->register_element( 'reports_header', array(
			'callback' 	=> array( $this, 'render_element'),
			'render_default_markup' => false,
		) );

		//Reports header
		$elementa->register_element( 'reports_footer', array(
			'callback' 	=> array( $this, 'render_element'),
			'render_default_markup' => false,
		) );

		//Reports cards
		$elementa->register_element( 'reports_cards', array(
			'callback' 				=> array( $this, 'render_element'),
			'render_default_markup' => false,
			'enqeue' 				=> array( $this, 'enqueue_chart'),
		) );
	}

	/**
	 * Renders our custom elements
	 *
	 * @since Hubaga 1.0.0
	 * @access public
	 */
	public function render_element( $args ) {
		if ( isset ( $args['type'] ) )
			include $this->admin_dir . "elements/{$args['type']}.php";
	}

	/**
	 * Include required files
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 */
	private function includes() {

		if ( is_admin() ) {
			//The metaboxes used on add/edit post screen
			require_once( $this->admin_dir . 'metaboxes.php'     );
			$this->metaboxes = new H_Metaboxes();

			//Order reports
			require_once( $this->admin_dir . 'reports.php' );
		}


		//Plugin settings
		$settings 	= include $this->admin_dir . 'settings.php';
		$settings 	= apply_filters( 'hubaga_setting_fields', $settings );

		foreach( $settings as $id => $args ) {

			$args['id'] 		= $id;
			do_action( "hubaga_settings_before_add_{$id}" );
			hubaga_add_option( $args );

		}

	}

	/**
	 * Setup the admin hooks, actions and filters
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 *
	 * @uses add_action() To add various actions
	 * @uses add_filter() To add various filters
	 */
	private function setup_actions() {

		add_action( 'admin_menu',              	   		array( $this, 'admin_menus'                		));
		add_action( 'admin_enqueue_scripts',       		array( $this, 'enqueue_styles'             		));
		add_action( 'admin_enqueue_scripts',       		array( $this, 'enqueue_scripts'            		));
		add_action( 'wp_dashboard_setup',          		array( $this, 'dashboard_widget_right_now' 		));
		add_filter( 'plugin_action_links', 				array( $this, 'modify_plugin_action_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', 					array( $this, 'modify_plugin_row_meta' ), 10, 2 	);
	}

	/**
	 * Add the admin menus
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @uses add_submenu_page() To add our custom menus to the Products menu
	 */
	public function admin_menus() {

		$product_slug = hubaga_get_product_post_type_menu_name();

		//Settings page
		$settings = add_submenu_page(
			$product_slug,
			esc_html__( 'Settings', 'hubaga' ),
			esc_html__( 'Settings', 'hubaga' ),
			'manage_options',
			'hubaga-settings',
			array( $this, 'render_settings' )
		);
		hubaga_elementa('hubaga')->hook_suffix = $settings;
		hubaga_elementa('hubaga')->scripts = array( 'select', 'color');

		//Reports pages
		$reports = add_submenu_page(
			$product_slug,
			esc_html__( 'Reports', 'hubaga' ),
			esc_html__( 'Reports', 'hubaga' ),
			'manage_options',
			'hubaga-reports',
			array( $this, 'render_reports' )
		);
		hubaga_elementa('hubaga_reports')->hook_suffix = $reports;
		hubaga_elementa('hubaga_reports')->scripts = array( 'select', 'date', 'reports_cards');


		//View system status pages
		add_submenu_page(
			$product_slug,
			esc_html__( 'System Status', 'hubaga' ),
			esc_html__( 'System Status', 'hubaga' ),
			'manage_options',
			'hubaga-status',
			array( $this, 'render_status' )
		);

		//Tell Elementa to load on the post edit screen
		$hook = ( isset($_GET['post']) ) ? 'post.php':'post-new.php';
		hubaga_elementa('hubaga_product_details')->hook_suffix = $hook;
		hubaga_elementa('hubaga_product_details')->scripts = array( 'select', 'date' );

	}

	/**
	 * Renders the edit settings page
	 *
	 * @since Hubaga 1.0.0
	 */
	public function render_settings() {

		if( current_user_can( 'manage_options' ) ){

			//Import field
			hubaga_add_option(
				array(
					'id' 			=> 'import',
					'section'  		=> 'Import / Export',
					'type' 			=> 'import',
					'title'		 	=> esc_html__( 'Import Or Export your settings to another website.', 'hubaga' ),
				)
			);

			//Save field
			hubaga_add_option(
				array(
					'id' 			=> 'save',
					'type' 			=> 'save',
				)
			);
			hubaga_elementa()->render();
		}

	}

	/**
	 * Renders the view system status page
	 *
	 * @since Hubaga 1.0.0
	 */
	public function render_status() {

		if(! current_user_can( 'manage_options' ) ){
			return;
		}

		if ( !class_exists( 'H_About_System' ) ) {
			require_once( hubaga_get_includes_path( 'system-info.php' ) );
		}

		$status = new H_About_System();
		$title  = esc_attr__( "To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).", "hubaga" );

		echo '<div class="wrap">
				<h1>System Status</h1>
				<form>
					<textarea
						readonly="readonly"
						rows="20"
						style=" width: 100%; max-width: 930px; "
						onclick="this.focus(); this.select()"
						title="' . $title . '">';

		echo esc_textarea( $status->get_info_as_text() );

		echo '</textarea></form></div>';
	}

	/**
	 * Renders the reports page
	 *
	 * @since Hubaga 1.0.0
	 */
	public function render_reports() {

		if( current_user_can( 'manage_options' ) ){
			$reports = new H_Report();
			$reports->output();
		}

	}

	/**
	 * Adds our styles to the admin page
	 *
	 * @since Hubaga 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'hubaga_admin',  $this->css_url . 'admin.css');
	}

	/**
	 * Adds our scripts to the admin page
	 *
	 * @since Hubaga 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'hubaga_admin_scripts', $this->js_url .  'admin.js', array( 'jquery', 'vue' ), '1.0.0', false );
		wp_enqueue_script( 'vue', $this->js_url .  'vue.min.js', array(), '2.3.3', false );
	}

	/**
	 * Enques chartist when rendering the reports page
	 *
	 * @since Hubaga 1.0.0
	 */
	public function enqueue_chart() {
		wp_enqueue_script( 'flot', $this->js_url .  'jquery.flot.js', array( 'vue' ), '0.8.3', false );
		wp_enqueue_script( 'flot-time', $this->js_url .  'jquery.flot.time.js', array( 'flot' ), '0.8.3', false );
		wp_enqueue_script( 'flot-resize', $this->js_url .  'jquery.flot.resize.js', array( 'flot' ), '0.8.3', false );
		wp_enqueue_script( 'flot-categories', $this->js_url .  'jquery.flot.categories.js', array( 'flot' ), '0.8.3', false );
	}

	/**
	 * Registers the orders live stream in the dashboard
	 *
	 * @since Hubaga 1.0.0
	 */
	public function dashboard_widget_right_now() {

		if( current_user_can( 'manage_options' ) ){
			wp_add_dashboard_widget( 'hubaga_sales_stream', 'Hubaga', array( $this, 'display_dashboard_widget' ) );
		}

	}

	/**
	 * Displays the orders live stream in the dashboard
	 *
	 * @since Hubaga 1.0.0
	 */
	public function display_dashboard_widget() {

		if(! current_user_can( 'manage_options' ) ){
			return;
		}

		if( !class_exists( 'H_Report' ) ){
			require_once( $this->admin_dir . 'reports.php' );
		}

		$reports = new H_Report();
		$streams  = $reports->revenue_streams();

		//Do we have orders
		if( empty( $streams ) ){
			esc_html_e( 'No Orders Yet.', 'hubaga' );
			return;
		}

		echo '<div class="elementa"><ul class="list-group-inner">';

			foreach( $streams as $stream ){

				$id 		= $stream['id'];
				$price  	= $stream['price'];
				$class  	= $stream['priceClass'];
				$status 	= $stream['status'];
				$view   	= esc_html__( 'View', 'hubaga' );
				$view_url   = admin_url( 'post.php?post=' . $id . '&action=edit' );
				$date   	= $stream['date'];

				echo "
					<li class='list-group-inner-item'><div>
					<span class='hubaga-stream-price $class'>$price</span>
					<span>$status</span>
					<a href='$view_url' class='hubaga-unstyled'>$view</a>
					<span class='hubaga-price-muted'>$date</span>
					</div></li>
				";

			}
		echo'</ul></div>';
	}

	/**
	 * Adds the settings links to the plugin screen
	 *
	 * @since Hubaga 1.0.0
	 */
	public function modify_plugin_action_links( $links, $file ) {

		if ( hubaga()->basename  == $file ) {
			$url 				= esc_url( hubaga_admin_settings_url() );
			$attr				= esc_attr__( 'Settings', 'hubaga' );
			$title				= esc_html__( 'Settings', 'hubaga' );
			$links['settings']  = "<a href='$url' aria-label='$attr'> $title </a>";
		}

		return $links;
	}

	/**
	 * Add a more links to our plugins screen
	 *
	 * @since Hubaga 1.0.0
	 */
	public function modify_plugin_row_meta( $links, $file ) {

		if ( hubaga()->basename  == $file ) {
			$row_meta = array(
				'support' 		=> '<a href="' . esc_url(  'https://hubaga.freshdesk.com/'  ) . '" aria-label="' . esc_attr__( 'Visit premium customer support', 'hubaga' ) . '">' . esc_html__( 'Premium support', 'hubaga' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return $links;

	}
}

endif; // class_exists check
