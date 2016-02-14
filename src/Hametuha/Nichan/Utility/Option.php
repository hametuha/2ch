<?php

namespace Hametuha\Nichan\Utility;

use Hametuha\Nichan\Pattern\Singleton;

/**
 * Option helper
 *
 * @package Hametuha\Nichan\Utility
 *
 * @property-read array  $editable_post_types
 * @property-read bool   $create_post_type
 * @property-read string $post_type_name
 * @property-read string $post_type_label_single
 * @property-read string $post_type_label_plural
 * @property-read bool   $require_moderation
 * @property-read bool   $show_form_automatically
 * @property-read bool   $require_moderation
 * @property-read bool   $use_trip
 * @property-read int    $post_as
 * @property-read string $recaptcha_pub_key
 * @property-read string $recaptcha_priv_key
 */
class Option extends Singleton{

	/**
	 * @var string Option key name
	 */
	private $option_key = '2ch-settings';

	/**
	 * @var array Default option value.
	 */
	public $default_options = array(
		'editable_post_types'  => array(),
	    'create_post_type' => false,
	    'post_type_name' => 'thread',
	    'post_type_label_single' => '',
	    'post_type_label_plural' => '',
	    'require_moderation' => true,
	    'show_form_automatically' => false,
	    'use_trip' => false,
	    'post_as'  => 0,
		'recaptcha_pub_key' => '',
		'recaptcha_priv_key' => '',
	);

	/**
	 * Update option
	 *
	 * @param array $option
	 *
	 * @return bool|\WP_Error
	 */
	public function save($option){
		$option = (array) $option;
		// Validating
		$error = new \WP_Error();
		foreach( $this->default_options as $key => $val ){
			if ( ! isset( $option[ $key ] ) ) {
				$error->add( 400, sprintf( __( '%s is not specified.', '2ch' ), $key ) );
			}
		}
		if( $error->get_error_messages() ){
			return $error;
		}
		if( ! is_numeric($option['post_as']) ){
			$error->add(400, __('User ID is wrong format.', '2ch'));
		}
		$new_value = array();
		foreach( $this->default_options as $key => $val ){
			switch($key){
				case 'editable_post_types':
					$new_value[ $key ] = (array) $option[ $key ];
					break;
				case 'post_as':
					$new_value[ $key ] = (int) $option[ $key ];
					break;
				case 'create_post_type':
				case 'require_moderation':
				case 'show_form_automatically':
				case 'use_trip':
					$new_value[ $key ] = (bool) $option[ $key ];
					break;
				default:
					$new_value[ $key ] = (string) $option[ $key ];
					break;
			}
		}
		return update_option( $this->option_key, $new_value );
	}

	/**
	 * Get option value
	 *
	 * @return array
	 */
	private function get_option(){
		static $initialized = false;
		$option = (array) get_option( $this->option_key, array() );
		if ( !$initialized ) {
			$this->default_options['post_type_label_single'] = __('Thread', '2ch');
			$this->default_options['post_type_label_plural'] = __('Threads', '2ch');
			$initialized = true;
		}
		foreach( $this->default_options as $key => $value){
			if( !isset($option[$key]) ){
				$option[$key] = $value;
			}
		}
		return $option;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return null
	 */
	public function __get($name){
		$option = $this->get_option();
		if( isset($option[$name]) ){
			return $option[$name];
		}else{
			return null;
		}
	}
}
