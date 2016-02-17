<?php

namespace Hametuha\Nichan\Pattern;

/**
 * API Base endpoint
 *
 * @package Hametuha\Nichan\Pattern
 */
abstract class ApiBase extends Application{

	/**
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * @var string Suffix for endpoint
	 */
	protected $base = 'nichan/v1';

	/**
	 * Constructor
	 */
	protected function initialize() {
		add_action('rest_api_init', array($this, 'rest_api_init'));
		if ( ! self::$initialized ) {
			self::$initialized = true;
			add_action( 'init', array( $this, 'register_scripts' ) );
		}
	}

	/**
	 * Register assets
	 */
	final public function register_scripts(){
		// JS Cookie
		wp_register_script( 'js-cookie', _2ch_plugin_dir_url('/dist/js/js.cookie.js'), array(), '2.1.0', true );
		// Google reCAPTCHA
		wp_register_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, false);
		// Form helper
		wp_register_script( '2ch-form', _2ch_plugin_dir_url('/dist/js/2ch.js'), array('jquery-form', 'js-cookie', 'recaptcha'), PLUGIN_2CH_VERSION, true );
		wp_localize_script( '2ch-form', 'NichanVars', array(
			'root'     => get_rest_url(),
			'nonce'    => wp_create_nonce('wp_rest'),
			'callback' => false,
			'message'  => __( 'Comment has been posted and waiting form moderation.', '2ch' )
		) );
	}

	/**
	 * Handle response
	 *
	 * @param \WP_REST_Server $wp_rest_server
	 */
	abstract public function rest_api_init( $wp_rest_server );


}
