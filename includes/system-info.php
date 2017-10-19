<?php
/**
 * Generates system information.
 * Do not translate any of this code since it is 
 * used to handle support requests.
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'H_About_System' ) ) :

/**
 * Generates current system information 
 *
 * @since Hubaga 1.0.0
 */
class H_About_System {
	
	private $info = null;
	
	/**
	 * The main constructor
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function __construct() {
		$this->info = $this->get_info();
	}

	/**
	 * Returns an array of system information
	 * in the form
	 *
	 * array( 'category' => array( ) )
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function get_info() {
		
		$plugins = hubaga_get_all_plugins();
		
		//These have intentionally been untranslated
		return apply_filters( 'hubaga_system_info', array(
			'Site Info' 				=> $this->get_site_info(),
			'WordPress Configuration' 	=> $this->get_wordpress_config(),
			'Server Info' 				=> $this->get_server_info(),
			'Active Plugins' 			=> $plugins['active_plugins'],
			'Inactive Plugins' 			=> $plugins['inactive_plugins'],
		) );
	}
	
	/**
	 * Returns the system information as a text string for use in textarea elements
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function get_info_as_text() {	
		$info = $this->info;
		$text = "### Begin System Info ###\n\n";
		
		foreach( $info as $cat => $info ){
			
			if( is_array( $info ) ) {
				
				$text .= "\n\n ----------- $cat -------------- \n\n";
			
				foreach( $info as $label => $value ){
					
					if ( is_array( $value ) ) {
						$value = print_r( $value, true );
					}
					
					$text .= "\t $label : $value \n\n";
				}
				
			} else {
				$text .= "$cat : $info \n\n";
			}
		}
		
		$text .= "\n ### End System Info ###";
		return apply_filters( 'hubaga_system_info_text', $text );
	}
	
	
	/**
	 * Returns the system information as a json string for use with apis or download
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function get_info_as_json() {
		$info = wp_json_encode( $this->info );
		return apply_filters( 'hubaga_system_info_json', $info );
	}
	
	/**
	 * Returns information about the current WordPress site configuration
	 *
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function get_site_info() {
		
		return apply_filters( 'hubaga_site_system_info', array(
			'Site URL'  => site_url(),
			'Home URL'  => home_url(),
			'Multisite' => ( is_multisite() ? 'Yes' : 'No' ),
		) );
	}
	
	/**
	 * Returns information about the current WordPress site configuration
	 *
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function get_wordpress_config() {
		
		global $wpdb;
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version . ' (' . $theme_data->uri . ')';
	
		return apply_filters( 'hubaga_wordpress_config_system_info', array(
			'Version'  				=> get_bloginfo( 'version' ),
			'Language'  			=> get_locale(),
			'Permalink Structure' 	=> ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ),
			'Active Theme'  		=> $theme,
			'Table Prefix'  		=> $wpdb->prefix,
			'WP_DEBUG'  			=> ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ),
			'Memory Limit'  		=> WP_MEMORY_LIMIT,
			'Registered Post Stati' => implode( ', ', get_post_stati() ),
		) );
	}
	
	/**
	 * Returns information about the server
	 *
	 *
	 * @since Hubaga 1.0.0
	 *
	 */
	public function get_server_info() {
		
		global $wpdb;
		$server_data = array();
		
		if ( function_exists( 'ini_get' ) ) {
			
			$server_data['Post Max Size']    	= size_format( ini_get( 'post_max_size' ) );
			$server_data['Safe Mode'] 			= ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled';
			$server_data['Memory Limit'] 		= ini_get( 'memory_limit' );
			$server_data['Upload Max Size'] 	= ini_get( 'upload_max_filesize' );
			$server_data['Time Limit'] 			= ini_get( 'max_execution_time' );
			$server_data['Max Input Vars'] 		= ini_get( 'max_input_vars' );
			
		}
	
		return apply_filters( 'hubaga_server_system_info', array_merge( array(
			'PHP Version'    => PHP_VERSION,
			'MySQL Version'  => $wpdb->db_version(),
			'Server'      	 => ( empty( $_SERVER['SERVER_SOFTWARE'] ) ? 'Unknown' : $_SERVER['SERVER_SOFTWARE'] ),
			'cURL'    		 => ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ),
			'fsockopen'    	 => ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ),
			'SOAP Client'    => ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ),
			'Suhosin'    	 => ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ),
		), $server_data ) );
	}
	
}
endif; // class_exists check
