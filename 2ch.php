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

load_plugin_textdomain( '2ch', false, '2ch/languages' );

if ( version_compare( phpversion(), '5.3.*', '<' ) ) {

} else {
	/**
	 * Show Error message on admin screen.
	 */
	function _2ch_version_error(){
		printf( '<div class="error"><p>%s</p></div>', esc_html( sprintf(
			__('[Error] Plugin 2ch requires PHP 5.3 and later, but your version is %s.', '2ch'),
			phpversion()
		) ) );
	}
	add_action( 'admin_notices', '_2ch_version_error' );
}
