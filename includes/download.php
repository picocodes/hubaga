<?php

/**
 * Manages file downloads
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'H_Download' ) ) :
/**
 * Hubaga Downloads Class
 *
 * @since Hubaga 1.0.0
 */
class H_Download {

	/**
	 * @var string Download method
	 */
	public $method = 'force'; //Or redirect
	
	/**
	 * @var string Download token
	 *
	 * Allows the user to download a file without logging in
	 */
	public $token = null;
	
	/**
	 * @var string Download order
	 *
	 * The order that is being downloaded
	 */
	public $order = null;
	
	/**
	 * @var string Download file
	 *
	 * A unique key representing the file that is being dowloaded
	 */
	public $download_key = null;
	
	/**
	 * @var string File path
	 *
	 * The file being downloaded
	 */
	public $path = null;
	
	/**
	 * @var string File url
	 *
	 * The http url to the file being downloaded
	 */
	public $url = null;
	
	/**
	 * @var string File name
	 *
	 * The name by which the downloaded file will be saved
	 */
	public $name = 'download';

	/**
	 * Registers our actions
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @uses add_action()
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_handle_download' ) );
	}
	
	/**
	 * Handles the file download in case this is a download request
	 *
	 * @since Hubaga 1.0.0
	 *
	 * @uses validate_download()
	 * @uses process_download()
	 */
	public function maybe_handle_download() {
		
		if( empty ( $_GET['action'] ) OR $_GET['action'] != 'hubaga_download' ){
			return;
		}
		
		//Setup the download method
		$method = hubaga_get_option( 'download_method' );
		if( $method == 'redirect' ){
			$this->method = 'redirect';
		}
		
		//Setup the token; This allows the user to download a file without logging in
		if(!empty ( $_GET['token'] ) ){
			$this->token = hubaga_clean( $_GET['token'] );
		}
		
		//Setup up the order that is being downloaded
		if(!empty ( $_GET['order'] ) ){
			$this->order = hubaga_clean( $_GET['order'] );
		}
		
		//Setup up the file that is being downloaded
		if(!empty ( $_GET['download_key'] ) ){
			$this->download_key = hubaga_clean( $_GET['download_key'] );
		}
		
		if(! $this->validate_download() OR !$this->process_download() ){
			status_header( WP_Http::FORBIDDEN );
			wp_die( __( 'You are not allowed to download this file.', 'hubaga' ) );
		}
		
	}

	/**
	 * Validates a download
	 *
	 * @since Hubaga 1.0.0
	 * @access protected
	 *
	 * @return bool
	 */
	protected function validate_download(){
		
		//No order; Abort
		if(! $this->order ) return false;
		
		//No download key; Abort
		if(! $this->download_key ) return false;
		
		//Fetch the order
		$order = hubaga_get_order( $this->order );
		
		//The order should exist and be marked complete		
		if(! hubaga_is_order( $order ) ) return false;
		if(! hubaga_is_order_complete( $order ) ) return false;
		
		/**
		 * The user must be logged in and own the order 
		 * An exception is when there is a token that 
		 * grants the user access to download the order.
		 *
		 * Tokens are usually generated when an order is created
		 * and have a lifetime of 2 hours.
		 *
		 */		
		$can_user_download = ( get_current_user_id() == $order->customer_id );
		
		if(! $can_user_download && $this->token ) {
			
			$order_token = get_transient( $this->order . '_download_token' );
			if( $order_token && $order_token == $this->token ) {
				$can_user_download = true;
			}
			
		}
		
		if(! $can_user_download ) return false;

		/**
		 * If we are here; this user can download files from this order
		 * Just confirm that the download key exists
		 * in the list of this orders downloads
		 *
		 */
		
		$downloads = hubaga_get_order_downloads( $order );
		if(! is_array( $downloads ) ) return false;
		if(! array_key_exists( $this->download_key, $downloads ) ) return false;
		if(! array_key_exists( 'url', $downloads[$this->download_key] ) ) return false;
		
		//We made it
		$this->url = $downloads[$this->download_key]['url'];
		if(!empty( $downloads[$this->download_key]['name'] ) ){
			$this->name = sanitize_file_name( $downloads[$this->download_key]['name'] );
		}
		$this->get_local_path();
		
		return true;
	}
	
	
	/**
	 * Converts a http path into a local path
	 *
	 * Wont work unless pretty permalinks are enabled
	 * @since Hubaga 1.0.0
	 */
	protected function get_local_path(){
		
		$upload_dir = wp_get_upload_dir();
		$base_dir   = $upload_dir['basedir'];
		$base_url   = str_ireplace( 'https://' , 'http://', $upload_dir['baseurl'] );
		$file_url   = str_ireplace( 'https://' , 'http://', $this->url );
		
		if( stripos( $file_url, $base_url ) == 0 ) {
			
			//This is a local file
			
			//Remove query vars
			list( $file_url ) = explode( '?', $file_url, 1 );
			list( $file_url ) = explode( '#', $file_url, 1 );
			
			//Simply replace the base url with the base dir
			$this->path = str_ireplace( $base_url, $base_dir, $file_url );
			
		}
	}
	
	/**
	 * Processes a download
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	protected function process_download() {
		
		$method = $this->method; //force
		if( hubaga_get_option( 'download_method' ) == 'redirect' )  {
			$method = 'redirect';
		}
		
		//We dont want this url being cached
		nocache_headers();
		
		//No bots either
		header("Robots: none");
		
		//In case we are redirecting; abort early
		if( $method == 'redirect' && wp_redirect( $this->url, WP_Http::TEMPORARY_REDIRECT ) ) {
			exit();
		}
		
		if(! $this->force_download() ) {
			//Force download failed. Try the redirect
			if( wp_redirect( $this->url, WP_Http::TEMPORARY_REDIRECT ) ) {
				exit();
			}
		}
		
		return false;
	}
	
	/**
	 * Forces a file to download
	 *
	 * @since Hubaga 1.0.0
	 * @access private
	 *
	 * @return bool
	 */
	protected function force_download() {
		
		//We dont want buffered data being passed into our content
		wp_ob_end_flush_all();
		
		//Use the local path in case it is available
		$path = $this->url;
		if( $this->path ) $path = $this->path;
		
		
		//Set the file size so that the browser can show the percentage downloaded
		if ( $size = @filesize( $path ) ) {
			header("Content-Length: $size");
		}
		
		//The downloaded file will be saved using this name
		if ( $name = trim( $this->name ) ) {
			header("Content-Disposition: attachment;filename=$name");
		}
		
		//Tell the browser what type of file it is getting
		$ext  = wp_check_filetype( $path );
		$type = $ext['type'];
		$ext  = $ext['ext'];
		if(! $type )
			$type = 'application/octet-stream';
		
		header("Content-Type: $type");
		
		/**
		 * If this evaluates to true; we 
		 * can force download very large files without running
		 * into any memory problems or max execution times
		 * As long as the server can fetch the file in the confines
		 * of the max excecution time; The speed of the customers 
		 * connection does not matter
		 */
		
		if( $this->path OR @ini_get( 'allow_url_fopen' ) == 1 ) {
			
			if(! @file_exists( $path ) ) return false;				
			
			//Best case senario
			flush();
			if( @readfile( $path ) ) exit();
			
			// Default to fread; a bigger buffer size means
			// the user can download larger files before excution time 
			// maxes out. It how increases risks of exceeding memory size
			
			$buffer_size = 2 * 1024 * 1024; //2MB
			if ( $handle = @fopen( $path, 'r' ) ) {
				while ( ! @feof( $handle ) ) {
					echo @fread( $handle, $buffer_size );
				}
				
				@fclose( $handle );
				exit();
			}

		}
		
		//Bad News. This will fetch the whole file into memory 
		//Before releasing it for donwload. Not a problem for small files < 20MB
		$res = wp_remote_get($url, $args = array());
		$code= (int) wp_remote_retrieve_response_code( $res );
		
		if( $code < 400 && $code > 199 ){			
			echo wp_remote_retrieve_body( $res );
			exit();			
		};
		
		return false;
	}

}
endif;
