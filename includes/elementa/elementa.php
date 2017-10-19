<?php

/**
 * Elementa main Class
 *
 * Static properties affect all instances. Non-static ones are unique to each created instance
 * Rename the Elementa class to something unique
 *
 * REMEMBER: Do not overthink anything
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Incase the class is already loaded

if ( class_exists( 'Elementa' ) ) {
	return;
}


/**
 * Main WP_Elements Class.
 *
 * @class WP_Elements
 * @version	0.1.0
 */
class Elementa {

	/**
	 * Current version.
	 *
	 * @var string
	 */
	public static $version = '0.1.5';

	/**
	 * Instances of this class; Makes it easy to alter your instance anywhere in your code
	 *
	 * @var Object
	 * @since 0.1.0
	 */
	protected static $instances = array();

	/**
	 * Scripts will only be loaded when a page matching this hook suffix is requested
	 */
	public $hook_suffix = '';

	/**
	 * Scripts that should be loaded when the instance is requested
	 * Should be set before the enqeue_scripts action runs
	 */
	public $scripts = array();

	/**
	 * An array of all elements that have been qeued for rendering
	 * Can be set at any time until just before you call self::render
	 */
	public $qeued_elements = array();

	/**
	 * An array of qeued sections
	 */
	public $sections = array();

	/**
	 * An array of user set options for this instance
	 */
	public $options = null;

	/**
	 * The unique id for this instance. Data is saved to the db using this id
	 */
	public $id = null;

	/**
	 * This is the template that will be used to render data
	 */
	public $template = '';

	/**
	 * An array of all registered elements
	 *
	 * @see self::register_element()
	 * @var array
	 */
	protected static $elements  = null;

	/**
	 * The plugin base url
	 *
	 * @see self::__construct()
	 * @var string
	 */
	protected static $base_url = null;

	/**
	 * The plugin base path
	 *
	 * @see self::__construct()
	 * @var string
	 */
	protected static $path = null;

	/**
	 * Callbacks used to retrieve custom form data such as users and posts
	 *
	 * You can register your own callback by calling self::register_data_callback()
	 * @see self::get_data()
	 * @var string
	 */
	protected static $data_callbacks = null;

	/**
	 * Retrieves an elementa instance.
	 *
	 * If you dont specify an instance id then the returned instance will not be saved
	 *
	 * @since 0.1.0
	 * @return Elementa - Main instance.
	 */
	public static function instance( $instance_id = false ) {

		//No id specified. Simply return a new instance of the class
		if(! $instance_id ){
			return new self( $instance_id );
		}

		//Maybe return a previously saved instance
		if( isset( self::$instances[ $instance_id ] ) ){
			return self::$instances[ $instance_id ];
		}

		//Create a new instance, save it then return it
		self::$instances[ $instance_id ] = new self( $instance_id );
		return self::$instances[ $instance_id ];

	}

	/**
	 * Elementa Constructor. Creates a new instance of the class
	 *
	 */
	private function __construct( $id ) {

		//The id for the current instance
		if( !$id ) {
			$this->id = md5( time() . wp_generate_password() );
		} else {
			$this->id = $id;
		}

		// Register core elements
		if( is_null( self::$elements ) ){
			self::$elements = $this->get_core_elements();
		}

		// Register our core data callbacks
		if( is_null( self::$data_callbacks ) ) {
			self::$data_callbacks = $this->get_core_data_callbacks();
		}

		// Our plugin base url
		self::$base_url 	= trailingslashit( plugins_url( '/', __FILE__ ) );
		self::$path 		= trailingslashit( plugin_dir_path( __FILE__ ) );

		//Set the template
		$this->template = self::$path . 'template.php';

		//Set options
		$this->options  = get_option( $this->id, null );

		//Maybe save settings. Done now so that they can take effect ASAP
		if( isset( $_REQUEST['elementa_action'] ) && $_REQUEST['elementa_action'] == esc_attr( $this->id ) ) {
			$this->save();
		}

		// Enques scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'enqeue_scripts'), 5);

	}

	/**
	 * Registers core elements
	 */
	public function get_core_elements() {

		return array(

			'title'		=>	array(
				'callback' 				=> array( $this, 'default_cb'),
				'render_default_markup' => false,
			),

			'textarea' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'text' 			=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'email' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			//Displays a color picker
			'color' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
				'enqeue' 	=> array( $this, 'enque_color'),
			),

			//Displays a date picker
			'date' 			=> array(
				'callback' 	=> array( $this, 'default_cb'),
				'enqeue' 	=> array( $this, 'enque_date'),
			),

			'search' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'number' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'password' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			//Displays a selectize select box
			'select' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
				'enqeue' 	=> array( $this, 'enque_select'),
			),

			'multiselect' 	=> array(
				'callback' 	=> array( $this, 'default_cb'),
				'enqeue' 	=> array( $this, 'enque_select'),
			),

			'button' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			//Displays the save and reset buttons
			'save' 			=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			//Displays the import and export buttons
			'import' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'checkbox' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'radio' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			//Displays a yes / no
			'switch' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'list_group' 	=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'alert' 		=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			'card' 			=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),

			//Offers full flexibility
			'raw' 			=> array(
				'callback' 	=> array( $this, 'default_cb'),
			),
		);

	}

	/**
	 * The callback used to render all core elements
	 *
	 */
	public function default_cb( $args ) {
		if ( isset ( $args['type'] ) )
			include self::$path . "elements/{$args['type']}.php";
	}

	/**
	 * Enques styles for select elements
	 *
	 */
	public function enque_select() {
		wp_enqueue_script( 'selectize', self::$base_url . 'assets/js/selectize.min.js', array( 'jquery' ), '4.0.3', false );
		wp_enqueue_style( 'selectize-bootstrap3', self::$base_url . 'assets/css/selectize.bootstrap3.css' );
	}

	/**
	 * Enques styles for date elements
	 *
	 */
	public function enque_date() {
		wp_enqueue_script( 'wp-datepicker', self::$base_url . 'assets/js/datepicker.js', array( 'jquery' ), '0.4.0', false );
		wp_enqueue_style( 'wp-datepicker', self::$base_url . 'assets/css/datepicker.css' );
	}

	/**
	 * Enques styles for color elements
	 *
	 */
	public function enque_color() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * Registers multiple elements at once
	 *
	 */
	public function register_multiple_elements( $args = array() ) {
		if( !is_array( $args ) )
			return;

		foreach ( $args as $element => $options ) {
			$this->register_element( $element, $options );
		}

	}

	/**
	 * Registers a single element
	 *
	 * An element needs a render callback at minimum
	 */
	public function register_element( $element_type = false, $args = array() ) {
		if( is_string( $element_type ) ) self::$elements[$element_type] = $args;
	}

	/**
	 * Returns a list of all registered elements
	 *
	 */
	public function get_registered_elements() {
		return array_keys( self::$elements );
	}

	/**
	 * Updates an existing element
	 *
	 */
	public function update_element( $element_type = false, $key = false, $value = false ) {
		if( $element_type !== false && isset( self::$elements[$element_type] ) )
			self::$elements[$element_type][$key] = $value;
	}

	/**
	 * @Deprecated.
	 *
	 * @see self::$queue_element
	 * @since 0.1.0
	 * @access public
	 */
	public function queue_control( $args = array() ) {
		$this->queue_element( $args );
	}

	/**
	 * Queues an element for rendering
	 *
	 * @since 0.1.1
	 * @access public
	 */
	public function queue_element( $args = array() ) {
		if(! empty( $args['id'] ) ) {

			if( !isset( $args['default'] ) ) {
				$args['default'] = null;
			}

			if( isset( $args['section'] ) ) {
				if(! array_key_exists( $args['section'], $this->sections ) ) {
					$this->sections[$args['section']] = array();
				}

				if( empty( $args['sub_section'] ) ) {
					$args['sub_section'] = $args['section'];
				}

				if( !in_array( $args['sub_section'], $this->sections[$args['section']] ) ) {
					$this->sections[$args['section']][] = $args['sub_section'];
				}

			} else {
				$args['section'] = null;
			}

			$this->qeued_elements[$args['id']] = $args;

		}
	}

	/**
	 * Renders a registered element
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function render_element( $args = array(), $default_markup = null ) {

		//If the user passes as string
		if ( is_string ( $args ) ) {
			$args = array(
				'type' => $args,
				'id'   => $args . rand( 1, 10 ) . md5( time() ), //Generate a random id
			);
		}

		//is the element registered
		if ( empty ( $args['type'] ) || !isset ( self::$elements[ $args['type'] ][ 'callback' ] ) )
			return;

		//Normalize the user args
		$args = $this->clean_args( $args );

		$element = self::$elements[ $args['type'] ];

		if( null === $default_markup ) {
			//Optionally render a default markup
			$default_markup = ( !isset ( $element['render_default_markup'] ) || $element['render_default_markup'] == true );
		}

		if( $default_markup ) {
			$this->render_wrapper_open( $args );
		}

		//Call the element's render function
		call_user_func( self::$elements[ $args['type'] ][ 'callback' ], $args );

		if( $default_markup ) {
			$this->render_wrapper_end( $args );
		}

	}

	/**
	 * Outputs the opening wrapper around rendered elements
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function render_wrapper_open( $args ) {

		$is_full_field 	= ( isset( $args['full_width'] ) && $args['full_width'] == true );
		$content_class 	= 'col s12';
		$class			= 'elementa-row';

		if( isset ( $args['section'] ) &&  $args['section'] )
			$class .= ' elementa-section-wrapper-' . sanitize_html_class( $args['section'] );

		if( isset ( $args['sub_section'] ) &&  $args['sub_section'] )
			$class .= ' elementa-sub_section-element-' . sanitize_html_class( $args['section'] ) . '-'. sanitize_html_class( $args['sub_section'] );

		echo "<div class='$class'>";

		//If a title is set; reduce the content class
		if ( isset( $args['title'] ) ) {

			$content_class = 'col s12 m8';
			$title_class = 'col s12 m4';
			if ( $is_full_field ) {
				$title_class = 'col s12';
				$content_class = 'col s12';
			}

			$title = '<strong>' . $args['title']. '</strong>';

			if ( isset( $args['subtitle'] ) ) {
				$title .= "<br />{$args['subtitle']}";
			}

			echo "<div class='$title_class'>$title</div>";

		}

		echo "<div class='$content_class'>";

	}

	/**
	 * Outputs the closing wrapper around rendered elements
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function render_wrapper_end( $args ) {
		echo '</div></div>';
	}

	/**
	 * Normalizes element render args
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function clean_args( $args ) {

		//Data attibutes
		if(! isset( $args['custom_attributes'] )) {
			$args['custom_attributes'] = array();
		}

		$args['_custom_attributes'] = '';

		foreach ( $args['custom_attributes'] as $attr => $value ) {
			$attr  = sanitize_title( $attr );
			$value = esc_attr( $value );
			$args['_custom_attributes'] .= " $attr='$value'";
		}

		//Default
		if(! isset( $args['default'] ) OR is_null( $args['default'] ) ) {
			$args['default'] = '';
		}

		//Description
		if(! isset( $args['description'] )) {
			$args['description'] = '';
		}

		//Placeholder
		if(! isset( $args['placeholder'] )) {
			$args['placeholder'] = '';
		}

		//Option details for select etc
		if(! isset( $args['options'] )) {
			$args['options'] = array();
		}

		//Data args
		if(! isset( $args['data_args'] )) {
			$args['data_args'] = array();
		}

		//Data, replaces the options field
		if( isset( $args['data'] ) ) {
			$data = $this->get_data( $args['data'], $args['data_args'] );
			if(! empty( $data ) ) {
				$args['options'] = $data;
			}
		}

		//Class
		if(! isset( $args['class'] )) {
			$args['class'] = '';
		}

		//Value == current value
		$args['_value'] = $args['_current'] = $this->get_option( $args['id'] );

		//Id attribute
		$args['__id'] = esc_attr( $args['id'] );

		return $args;
	}

	/**
	 * Returns the data provided by a given data callback
	 */
	public function get_data( $type = '', $args = array()) {

		if( empty ( $type ) || !is_string( $type ) )
			return array();

		$type = trim( strtolower( $type ) );

		if( !isset ( self::$data_callbacks[ $type ] ) )
			return array();

		return call_user_func( self::$data_callbacks[$type], $args );

	}

	/**
	 * Registers data callbacks. Existing callback of the same key will be overwritten
	 * The callback returns an array of name=>label pairs
	 *
	 * $data should be lowercase
	 *
	 * @var $data string Required. The type of data e.g post
	 * @var $callback the callback used to fetch this data
	 */
	public function register_data_callback( $data, $callback ) {
		if (is_string( $data ) ) {
			self::$data_callbacks[$data] = $callback;
		}
	}

	/**
	 * Returns an array of all registered data callbacks
	 */
	public function get_registered_data_callbacks( ) {
		return self::$data_callbacks;
	}

	/**
	 * Registers multiple data callbacks
	 */
	public function get_core_data_callbacks() {

		return array(
			'category' 				=> array( $this, 'get_categories' ),
			'categories' 			=> array( $this, 'get_categories' ),
			'tag' 					=> array( $this, 'get_tags' ),
			'tags' 					=> array( $this, 'get_tags' ),
			'post_tag' 				=> array( $this, 'get_tags' ),
			'taxonomy' 				=> array( $this, 'get_taxonomies' ),
			'taxonomies' 			=> array( $this, 'get_taxonomies' ),
			'posts' 				=> array( $this, 'get_posts' ),
			'post' 					=> array( $this, 'get_posts' ),
			'menus' 				=> array( $this, 'get_menus' ),
			'menu' 					=> array( $this, 'get_menus' ),
			'page' 					=> array( $this, 'get_pages' ),
			'pages' 				=> array( $this, 'get_pages' ),
			'post_types' 			=> array( $this, 'get_post_types' ),
			'post_type' 			=> array( $this, 'get_post_types' ),
			'post_statuses'	 		=> array( $this, 'get_post_statuses' ),
			'post_status' 			=> array( $this, 'get_post_statuses' ),
			'user' 					=> array( $this, 'get_users' ),
			'users' 				=> array( $this, 'get_users' ),
			'roles' 				=> array( $this, 'get_roles' ),
			'role' 					=> array( $this, 'get_roles' ),
			'user_roles' 			=> array( $this, 'get_roles' ),
			'user_role' 			=> array( $this, 'get_roles' ),
			'capabilities' 			=> array( $this, 'get_capabilities' ),
			'capability' 			=> array( $this, 'get_capabilities' ),
			'user_capabilities' 	=> array( $this, 'get_capabilities' ),
			'user_capability' 		=> array( $this, 'get_capabilities' ),
			'country' 				=> array( $this, 'get_capabilities' ),
			'countries' 			=> array( $this, 'get_capabilities' ),
		);

	}

	/**
	 * Returns an array of post categories
	 */
	public function get_categories( $args ) {
		return wp_list_pluck( get_categories( $args ), 'name',  'term_id' );
	}

	/**
	 * Returns an array of post tags
	 */
	public function get_tags( $args ) {
		return wp_list_pluck( get_tags( $args ), 'name', 'term_id' );
	}

	/**
	 * Returns an array of taxonomies
	 */
	public function get_taxonomies( $args ) {
		return get_taxonomies( $args );
	}

	/**
	 * Returns an array of posts
	 */
	public function get_posts( $args ) {

		if(! isset( $args['numberposts'] ) )
			$args['numberposts'] = -1;

		return wp_list_pluck( get_posts( $args ), 'post_title', 'ID' );
	}

	/**
	 * Returns an array of menus
	 */
	public function get_menus( $args ) {
		return wp_list_pluck( wp_get_nav_menus( $args ), 'name', 'term_id' );
	}

	/**
	 * Returns an array of pages
	 */
	public function get_pages( $args ) {

		if(! isset( $args['numberposts'] ) )
			$args['numberposts'] = -1;

		return wp_list_pluck( get_pages( $args ), 'post_title', 'ID' );

	}

	/**
	 * Returns an array of post types
	 */
	public function get_post_types( $args ) {
		return wp_list_pluck( get_post_types( $args, false ), 'label', 'name' );
	}

	/**
	 * Returns an array of post statuses
	 */
	public function get_post_statuses( $args ) {
		global $wp_post_statuses;
		return wp_list_pluck( $wp_post_statuses, 'label' );
	}

	/**
	 * Returns an array of countries
	 */
	public function get_countries( $args ) {
		return require( 'data/countries.php' );
	}

	/**
	 * Returns an array of users
	 */
	public function get_users( $args ) {
		return wp_list_pluck( get_users( $args, false ), 'display_name', 'ID' );
	}

	/**
	 * Returns an array of user roles
	 */
	public function get_roles( $args ) {
		global $wp_roles;
		return $wp_roles->role_names;
	}

	/**
	 * Returns an array of all user capabilities or capabilities for the given user type
	 */
	public function get_capabilities( $args ) {

		global $wp_roles;
		$capabilities = array();

		//user wants all capabilities
		if( !isset( $args['user_type'] ) ) {

			foreach ( $wp_roles->roles as $role) {

				foreach ( $role['capabilities'] as $cap => $bool ) {
					if( $bool == true )
						$capabilities[$cap] = $this->titalize( $cap );
				}

			}
		} else {
			//User wants the capabilities of a single user type
			if ( isset ($wp_roles->roles[$args['user_type']]) ){

				foreach ( $wp_roles->roles[$args['user_type']]['capabilities'] as $cap => $bool ) {
					if( $bool == true )
						$capabilities[$cap] = $this->titalize( $cap );
				}

			}
		}

		return $capabilities;

	}

	/**
	 * Converts a string to readable form
	 */
	public function titalize( $string ) {
		return ucfirst( str_replace( '_', ' ', $string ) );
	}

	/**
	 * Sets the rendering template
	 */
	public function set_template( $template = false ) {
		$this->template = $template;
	}

	/**
	 * Outputs the settings page
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function render() {

		//Do we have a template
		if (! file_exists( $this->template ) )
			return;

		$elements 		= $this->qeued_elements;
		$instance_id    = esc_attr( $this->id );
		$sections 		= $this->sections;
		$section_keys 	= $this->get_sections();
		$active_section = null;
		$active_sub_section = null;

		if( count( $section_keys ) > 1 ){
			if(!empty( $_GET['tab'] ) && in_array( urldecode( $_GET['tab'] ), $section_keys ) ) {
				$active_section = esc_attr( urldecode( $_GET['tab'] ) );
			}else {
				$active_section = esc_attr( $section_keys[0] );
			}
		}

		if( isset( $_GET['sub_section'] ) ){
			$active_sub_section = urldecode( $_GET['sub_section'] );
		} elseif(! empty( $sections ) ) {
			$active_sub_section = $sections[$section_keys[0]][0];
		}

		$instance_args  = array(
			'id'			=> esc_attr( $this->id ),
			'translations'	=> array(
				'emptyData' => __( 'Please provide the import data.', 'elementa' ),
				'emptyJson' => __( 'You provided an empty object so nothing was imported.', 'elementa' ),
				'badFormat' => __( 'The data you provided is not it the right format. Sorry.', 'elementa' ),
				'importing' => __( 'Importing data...', 'elementa' ),
				'finalising'=> __( 'Almost done.', 'elementa' ),
				'finished'  => __( 'Done. Please wait for the page to reload.', 'elementa' ),
				'exit_prompt'=>__( 'Are you sure you want to exit before saving your settings?', 'elementa' ),
			),
			'has_sections'  => !empty( $sections ),
			'active_section'=> esc_js( $active_section ),
			'active_sub_section'=> esc_js( $active_sub_section ),
		);

		require_once ( $this->template );

	}

	/**
	 * Saves submitted data
	 *
	 * @since  0.1.0
	 * @access protected
	 */
	protected function save() {

		//Make sure to always include an elementa nonce field in your templates unless you save your own settings
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'elementa' ) )
			return;

		//Can the current user manage options
		if (! current_user_can( 'manage_options' ) ){
			return;
		}

		//Data is being imported.
		if ( isset( $_POST['elementa-imported-data'] ) ){
			return $this->_save( json_decode( wp_unslash( $_POST['elementa-imported-data'] ), true ) );
		}

		//Data is reset.
		if ( isset( $_POST['elementa-reset'] ) ){
			return $this->_save( $this->get_defaults() );
		}

		//Settings are being saved
		return $this->_save( $_POST );

	}

	/**
	 * Saves dava to the db
	 *
	 *
	 * @since 0.1.1
	 * @access protected
	 */
	protected function _save( $data ) {

		$data = wp_unslash( $data );

		if ( is_array ( $data )) {

			unset( $data['_wp_http_referer'] );
			unset( $data['_wpnonce'] );
			unset( $data['wpe-import'] );
			unset( $data['wpe-export'] );

			update_option( $this->id, $data );

			//Update cached data with our new values
			$this->options = $data;
		}

		return $this->options;

	}

	/**
	 * Returns all sections
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function get_sections(){
		return array_keys( $this->sections );
	}

	/**
	 * Returns all default values for the current instance
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function get_defaults(){
		return wp_list_pluck( $this->qeued_elements, 'default' );
	}

	/**
	 * User settings for this instance. Falls back to default if no data is saved to the db
	 *
	 * @since 0.1.1
	 * @access public
	 */
	public function get_options() {

		//If the options have already been set by the user; return them
		if( is_array( $this->options ) ){
			return $this->options;
		}
		return $this->get_defaults();

	}


	/**
	 * gets a user defined option
	 */
	public function get_option( $option = false ) {

		$options = $this->get_options();
		if( is_string( $option ) && array_key_exists( trim( $option ), $options )) {
			return $options[ trim( $option ) ];
		}
		return null;

	}

	/**
	 * Enques our styles and scripts
	 * @since  0.1.0
	 */
	public function enqeue_scripts( $hook_suffix ) {

		//Only enque styles on the pages that we render
		if ( $hook_suffix != $this->hook_suffix)
			return;

		//Main css file
		wp_enqueue_style( 'elementa', self::$base_url . 'assets/css/elementa.css');

		//Next enque scripts needed to render qeued elements
		foreach ( $this->scripts as $script ) {
			if ( isset ( self::$elements[$script]['enqeue'] ) )
				call_user_func( self::$elements[$script]['enqeue'] );
		}

		$deps = array( 'jquery', 'underscore' );
		if(in_array( 'color', $this->scripts )){
			$deps[] = 'wp-color-picker';
		}

		//Our main javascript file is enqeued last
		wp_enqueue_script( 'elementa', self::$base_url . 'assets/js/elementa.js', $deps, '0.1.5', false );

	}


	/**
	 * Plucks a given property from qeued elements
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function element_pluck( $property, $values = true ) {
		if( $values ) return array_values( wp_list_pluck( $this->qeued_elements, $property ) );
		return wp_list_pluck( $this->qeued_elements, $property );
	}

}

//Goodbye World!!!!!!!
