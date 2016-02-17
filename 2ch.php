<?php
/*
Plugin Name: 2ch
Description: WordPress plugin for Japanese notorious anonymous BBS clone.
Version: 1.0.0
Plugin URI: https://github.com/hametuha/2ch
Author: Takahashi_Fumiki
Author URI: https://hametuha.co.jp
License: GPLv3 or later
Text Domain: 2ch
Domain Path: /languages
*/

// Avoid direct load.
defined('ABSPATH') or die('Do not load directly');

/**
 * Define version
 * @const string
 */
define( 'PLUGIN_2CH_VERSION', '1.0.0' );

/**
 * Define template directory.
 * @const string
 */
define( 'PLUGIN_2CH_DIR', dirname( __FILE__ )  );

// Load i18n.
load_plugin_textdomain( '2ch', false,  basename(dirname(__FILE__)). DIRECTORY_SEPARATOR . 'languages' );

// Check smallest availability.
if ( version_compare( phpversion(), '5.3.*', '>=' ) ) {
	if( file_exists( PLUGIN_2CH_DIR.'/vendor/autoload.php' ) ){
		// Load template tags.
		require PLUGIN_2CH_DIR.'/template-tags.php';
		// Initialize instance.
		require PLUGIN_2CH_DIR.'/vendor/autoload.php';
		call_user_func( array( 'Hametuha\\Nichan\\Bootstrap', 'instance' ) );
	}else{
		/**
		 * Show Error message.
		 * @ignore
		 */
		function _2ch_composer_error(){
			printf( '<div class="error"><p>%s</p></div>', sprintf(
				__('[Error 2ch] Composer auto loader <code>%s</code> is missing. If you get this plugin from github, just run <code>composer install</code>.', '2ch'),
				esc_html( PLUGIN_2CH_DIR.'/vendor/autoload.php' )
			) );
		}
		add_action( 'admin_notices', '_2ch_composer_error' );
	}
} else {
	/**
	 * Show Error message on admin screen.
	 * @ignore
	 */
	function _2ch_version_error(){
		printf( '<div class="error"><p>%s</p></div>', esc_html( sprintf(
			__('[Error 2ch] Plugin 2ch requires PHP 5.3 and later, but your version is %s.', '2ch'),
			phpversion()
		) ) );
	}
	add_action( 'admin_notices', '_2ch_version_error' );
}
