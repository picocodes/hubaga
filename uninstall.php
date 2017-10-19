<?php
/**
 * Hubaga Uninstall
 *
 * Uninstalling Hubaga deletes user roles, pages, tables, and options.
 *
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'hubaga' ); //Our database options
delete_option( 'hubaga_db_version' ); //database version
delete_transient( 'hubaga_core_pages' ); //Core pages
