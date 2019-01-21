<?php
/*
Plugin Name: Woocommerce - Delete All Products
Description: Delete all Woocommerce products
Author: Stefan Olaru
Author URI: http://stefanolaru.com
Version: 1.0
*/

// prevent direct access
if(!defined('ABSPATH')) {
	exit;
}

class WooDAP {

	public static $version = '1.0';
	private $options;

	function __construct() {

		// install/uninstall hooks
		register_activation_hook( __FILE__, ['WooDAP', 'install']);
		register_deactivation_hook( __FILE__, ['WooDAP', 'deactivate']);
		register_uninstall_hook( __FILE__, ['WooDAP', 'uninstall']);

	}

	public static function install() {
	
		// add version option
		add_option( 'woodap_version', self::$version );
			
	}
	
	public static function deactivate() {
		// nothing here yet
	}
	
	public static function uninstall() {
		
		delete_option( 'woodap_version' );
		
	}

}

$WooDAP = new WooDAP();