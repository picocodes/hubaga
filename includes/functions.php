<?php
/**
 * Hubaga Core Functions
 *
 * General core functions available on both the front-end and admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main instance of Hubaga.
 *
 * @since  1.0.0
 * @return Hubaga
 */
function hubaga(){
	return Hubaga::instance();
}

/**
 * A helper function to access elementa
 *
 *
 * @since  1.0.0
 * @return Elementa
 */
function hubaga_elementa( $id = 'hubaga' ){
	return hubaga()->Elementa( $id );
}

/**
 * A helper function to access a user option
 *
 *
 * @since  1.0.0
 * @return mixed
 */
function hubaga_get_option( $key, $id = 'hubaga' ){
	return hubaga()->get_option( $key, $id );
}

/**
 * A helper function to register a user option
 *
 *
 * @since  1.0.0
 * @return void
 */
function hubaga_add_option( $args, $id = 'hubaga' ){
	hubaga()->Elementa( $id )->queue_control( $args );
}

/**
 * A helper function to check whether this is a test store or not
 *
 *
 * @since  1.0.0
 * @return bool
 */
function hubaga_is_sandbox(){
	return hubaga_get_option( 'sandbox' );
}

/**
 * A helper function to access the includes path
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_includes_path( $append = '' ){
	
	$path = hubaga()->includes_path . $append;
	if( is_file( $path ) ){
		return $path;
	}
	return trailingslashit( $path  );
	
}

/**
 * A helper function to access the includes url
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_includes_url( $append = '' ){
	return esc_url( trailingslashit( hubaga()->includes_url . $append  ) );
}

/**
 * A helper function to access the plugin path
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_plugin_path( $append = '' ){
	
	$path = hubaga()->plugin_path . $append;
	if( is_file( $path ) ){
		return $path;
	}
	return trailingslashit( $path  );

}

/**
 * A helper function to access the plugin url
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_plugin_url( $append = '' ){
	return esc_url( hubaga()->plugin_url . $append ) ;
}

/**
 * A helper function to access the base admin menu page
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_admin_menu_url( $append = '' ){
	return esc_url( hubaga()->admin_menu_url . $append ) ;
}

/**
 * A helper function to access the settings page
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_admin_settings_url(){
	return hubaga_admin_menu_url( '&page=hubaga-settings' );
}

/**
 * A helper function to access the notifications page
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_admin_notifications_url(){
	return hubaga_admin_menu_url( '&page=hubaga-notifications' );
}

/**
 * A helper function to access the status page
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_admin_status_url(){
	return hubaga_admin_menu_url( '&page=hubaga-status' );
}

/**
 * A helper function to access the extensions page
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_admin_extensions_url(){
	return hubaga_admin_menu_url( '&page=hubaga-extensions' );
}

/**
 * A helper function to access the reports page
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_admin_reports_url(){
	return hubaga_admin_menu_url( '&page=hubaga-reports' );
}

/**
 * A helper function to access the plugin version
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_version(){
	return hubaga()->version;
}

/**
 * A helper function to access the plugin database version
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_database_version(){
	return hubaga()->db_version;
}

/**
 * A helper function to access the plugin base name
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_plugin_basename(){
	return hubaga()->basename;
}

/**
 * A helper function to access the plugin file
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_plugin_file(){
	return hubaga()->file;
}

/**
 * A helper function to access the user agent
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_user_agent(){
	return hubaga()->user_agent;
}

/**
 * A helper function to access the ajax url
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_ajax_url(){
	return hubaga()->ajax_url;
}

/**
 * A helper function to access the current notices
 *
 *
 * @since  1.0.0
 * @param $code the notice code to retrieve. Defaults to empty, which retrives notices from all codes
 * @return string
 */
function hubaga_get_notices( $code = '' ){
	return hubaga()->notices->get_error_messages( $code );
}

/**
 * A helper function to access the current errors
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_errors(){
	return hubaga_get_notices( 'error' );
}

/**
 * A helper function to access the current notice codes
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_get_notice_codes(){
	return hubaga()->notices->get_error_codes();
}

/**
 * A helper function to check if there are any notices
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_has_notices( $code = '' ){
	
	if ( empty($code) )
		return hubaga()->notices->get_error_code() != '';
	
	return in_array( $code, hubaga_get_notice_codes() );
		
}

/**
 * A helper function to check if there are any errors
 *
 *
 * @since  1.0.0
 * @return string
 */
function hubaga_has_errors(){
	return hubaga_has_notices( 'error' );		
}

/**
 * Adds a message to the list of notices
 * @return void
 */
function hubaga_add_notice( $message, $code='notice' ){
	return hubaga()->notices->add( $code, $message );
}

/**
 * Adds an error to the list of notices
 * @return void
 */
function hubaga_add_error( $message ){
	return hubaga()->notices->add( 'error', $message );
}

/**
 * Removes a notice code from the list of notices
 * @return void
 */
function hubaga_remove_notice_code( $code ) {

	if( empty( $code ) ) {
		foreach( hubaga()->notices->get_error_codes() as $code ) {
			hubaga()->notices->remove( $code );
		}
	} else {
		hubaga()->notices->remove( $code );
	}
	
}

/**
 * Clears all errors
 * @return void
 */
function hubaga_clear_errors() {
	return hubaga_remove_notice_code( 'error' );
}

/**
 * Fetches a html string of all the current notices
 * @return string
 */
function hubaga_get_notices_html(  $code = '', $args = array() ){
	
	$defaults = array(
		'wrapper' 		=> 'ul',
		'wrapper_class' => 'hubaga-notices hubaga-notices-'.$code,
		'element' 		=> 'li',
		'element_class' => 'hubaga-notice hubaga-notice-'.$code,
	);	
	
	$args 	 = wp_parse_args( $args, $defaults );
	$notices =  hubaga_get_notices( $code ); 
	hubaga_remove_notice_code( $code );
	return hubaga()->template->convert_array_to_html( $notices, $args );
	
}

/**
 * Fetches a html string of all the current errors
 * @return string
 */
function hubaga_get_errors_html( $args = array() ){
	return hubaga_get_notices_html(  'error', $args );
}

/**
 * cleans a value for db insertion
 *
 * @since 1.0.0
 */
function hubaga_wpdb_clean( $value, $modifier = '%s' ) {
	
	global $wpdb;
	
	if ( is_array( $value ) ) {
		return array_map( 'hubaga_wpdb_clean', $value );
	} else {
		return $wpdb->prepare( $modifier, $value );
	}

}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function hubaga_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'hubaga_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( trim( $var ) ) : $var;
	}
}

/**
 * Returns the current store currency
 *
 * @return string
 */
function hubaga_get_currency() {
	return hubaga_clean( hubaga_get_option( 'currency' ) );	
}

/**
 * Returns all available currencies
 *
 * @return string
 */
function hubaga_get_currencies() {
	return hubaga()->currencies;
}

/**
 * Returns a currency symbol for the provided currency
 *
 * @return string
 */
function hubaga_get_currency_symbol( $currency = false ) {
	
	( $currency ) || $currency = hubaga_get_currency();
	$symbols = hubaga_get_currency_symbols();
	$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';
	return apply_filters( 'hubaga_currency_symbol', $currency_symbol, $currency );
	
}

/**
 * Returns all available currency symbols
 *
 * @return string
 */
function hubaga_get_currency_symbols() {
	return hubaga()->currency_symbols;
}

/**
 * Returns the currency position
 *
 * @return string
 */
function hubaga_get_currency_position() {
	return hubaga_clean( hubaga_get_option( 'currency_position' )  );
}

/**
 * Format the price with a currency symbol.
 *
 * @param float $price
 * @param string $currency
 * @return string
 */
function hubaga_price( $price, $currency = '', $with_symbol = true ) {
	
	if(! $currency ) $currency = hubaga_get_currency();
	
	$price = hubaga_format_price( $price, $currency );	
	$currency = hubaga_get_currency_symbol( $currency );
	
	$return = $price;
	if ( 'right' == hubaga_get_currency_position() && $with_symbol ) {
		$return = $price . $currency;
	}
	
	if ( 'left' == hubaga_get_currency_position() && $with_symbol ) {
		$return = $currency . $price;
	}
			
	return apply_filters( 'hubaga_price', $return, $price, $currency, $with_symbol );
}

/**
 * Formats the price
 *
 * @return float
 */
function hubaga_format_price( $price, $currency = false ) {
	
	if(! $currency ) {
		$currency = hubaga_get_currency();
	}
	
	//Number of decimals to round
	$precision = 2;
	if( in_array( $currency, array( 'HUF', 'JPY', 'TWD' ) ) ) {
		$precision = 0;
	}
	
	$decimal_separator  = hubaga_get_option( 'decimal_separator' );
	$thousand_separator = hubaga_get_option( 'thousand_separator' );
	
	//Return the formatted price
	return number_format( $price, $precision, $decimal_separator, $thousand_separator );
	
}

/**
 * Get all plugins grouped into activated or not.
 * @return array
 */
function hubaga_get_all_plugins() {
	// Ensure get_plugins function is loaded
	if ( ! function_exists( 'get_plugins' ) ) {
		include ABSPATH . '/wp-admin/includes/plugin.php';
	}

	$plugins        	 = get_plugins();
	$active_plugins_keys = get_option( 'active_plugins', array() );
	$active_plugins 	 = array();

	foreach ( $plugins as $k => $v ) {
		// Take care of formatting the data how we want it.
		$formatted = array();
		$formatted['name'] = strip_tags( $v['Name'] );
		if ( isset( $v['Version'] ) ) {
			$formatted['version'] = strip_tags( $v['Version'] );
		}
		if ( isset( $v['Author'] ) ) {
			$formatted['author'] = strip_tags( $v['Author'] );
		}
		if ( isset( $v['Network'] ) ) {
			$formatted['network'] = strip_tags( $v['Network'] );
		}
		if ( isset( $v['PluginURI'] ) ) {
			$formatted['plugin_uri'] = strip_tags( $v['PluginURI'] );
		}
		if ( in_array( $k, $active_plugins_keys ) ) {
			// Remove active plugins from list so we can show active and inactive separately
			unset( $plugins[ $k ] );
			$active_plugins[ $k ] = $formatted;
		} else {
			$plugins[ $k ] = $formatted;
		}
	}

	return array( 'active_plugins' => $active_plugins, 'inactive_plugins' => $plugins );
}

/**
 * Helper method for checking if an array key is set, not empty && valid
 * @return bool
 */
function hubaga_is_array_key_valid( $array, $key, $sanitize = false ) {
	
	if( false === $key )
		return false;
	
	if( $sanitize && is_callable( $sanitize ) ) {
		return isset( $array[$key] ) && !empty( $array[$key] ) && call_user_func( $sanitize, $array[$key]);
	}
	return isset( $array[$key] ) && !empty( $array[$key] );
		
}

/**
 * Wrapper for print_r
 * @param $variable mixed. The variable to print
 * @param $return bool. Whether to return or echo the html
 */
function hubaga_print_r( $variable, $return = false ) {
	
	$variable = '<pre>' . print_r($variable, true) . '</pre>';
	if( $return ) {
		return $variable;
	}
	echo $variable;
	
}

/**
 * Filters the body classes
 */
function hubaga_body_classes( $classes ) {
	$classes[] = 'hubaga';
	return $classes;
}
add_filter( 'body_class', 'hubaga_body_classes' );

/**
 * Gets the current browser
 */
function hubaga_get_browser() {
	
	if( empty( $_SERVER['HTTP_USER_AGENT'] )){
		return __( 'Unknown', 'hubaga' );
	}
	
	$agent = $_SERVER['HTTP_USER_AGENT'] ;
	
	if( stripos( $agent, 'Opera Mini') OR stripos( $agent, 'OPR/') )
		return 'Opera Mini';
	
	if( stripos( $agent, 'Opera') OR stripos( $agent, 'OPR/') )
		return 'Opera';
	
	//Edge sometimes contains Chrome and Safari in its UA so check it first
	if( stripos( $agent, 'Edge'))
		return 'Edge';
	
	//Same as Chrome which contains Safari
	if( stripos( $agent, 'Chrome'))
		return 'Chrome';
	
	if( stripos( $agent, 'Safari'))
		return 'Safari';
	
	if( stripos( $agent, 'Firefox'))
		return 'Firefox';
	
	if( stripos( $agent, 'MSIE') OR stripos( $agent, 'Trident/7') )
		return 'Internet Explorer';
	
	if( stripos( $agent, 'UCWEB') OR stripos( $agent, 'UCBROWSER') OR stripos( $agent, 'UC BROWSER') )
		return 'UC BROWSER';
	
	return __( 'Other', 'hubaga' );
}


/**
 * Gets the current platform
 */
function hubaga_get_platform() {
	
	if( empty( $_SERVER['HTTP_USER_AGENT'] )){
		return __( 'Unknown', 'hubaga' );
	}
	
	$agent = $_SERVER['HTTP_USER_AGENT'] ;
	
	//Techinically, Android is linux but we should treat it diff
	if( stripos( $agent, 'Android') )
		return 'Android';
	
	if( stripos( $agent, 'iphone') )
		return 'iphone';
	
	if( stripos( $agent, 'ipad') )
		return 'ipad';
	
	if( stripos( $agent, 'Linux') )
		return 'Linux';
	
	if( stripos( $agent, 'Windows') OR stripos( $agent, 'win32'))
		return 'Windows';
	
	if( stripos( $agent, 'mac'))
		return 'Mac';
	
	return __( 'Other', 'hubaga' );
}