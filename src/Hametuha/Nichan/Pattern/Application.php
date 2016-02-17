<?php

namespace Hametuha\Nichan\Pattern;
use Hametuha\Nichan\Service\Recaptcha;
use Hametuha\Nichan\Utility\Hash;
use Hametuha\Nichan\Utility\Input;
use Hametuha\Nichan\Utility\Option;

/**
 * Application base
 *
 * @package Hametuha\Nichan\Pattern
 * @property-read Input $input
 * @property-read option $option
 * @property-read Recaptcha $recaptcha
 * @property-read Hash $hash
 */
abstract class Application extends Singleton{

	/**
	 * Load template if exists
	 *
	 * @param string $template
	 * @param array  $args
	 */
	protected function load_template( $template, $args = array() ) {
		$template_path = PLUGIN_2CH_DIR."/templates/{$template}.php";
		foreach ( array( get_template_directory(), get_stylesheet_directory() ) as $dir ) {
			$path = $dir . "/2ch/{$template}.php";
			if( file_exists($path) ){
				$template_path = $path;
			}
		}
		/**
		 * nichan_template_path
		 *
		 * Template path filter to change included file.
		 *
		 * @package 2ch
		 * @since 1.0.0
		 * @param string $template_path
		 * @param string $template
		 * @return string
		 */
		$template_path = apply_filters( 'nichan_template_path', $template_path, $template );
		if ( file_exists( $template_path ) ) {
			if( $args ){
				extract( $args );
			}
			include $template_path;
		}
	}

	/**
	 * Post type is thread or not.
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function is_thread($post_type){
		return ( false !== array_search( $post_type, $this->option->editable_post_types ) )
		         ||
		       ( ( $post_type === $this->option->post_type_name ) && $this->option->create_post_type ) ;
	}

	/**
	 * Detect if post type is anonymously commentable.
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function is_commentable( $post_type ) {
		return ( false !== array_search( $post_type, $this->option->anonymous_comment_post_types ) )
		         ||
		       ( $post_type === $this->option->post_type_name );
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return null|static
	 */
	public function __get($name){
		switch( $name ){
			case 'input':
				return Input::instance();
				break;
			case 'option':
				return Option::instance();
				break;
			case 'hash':
				return Hash::instance();
				break;
			case 'recaptcha':
				return Recaptcha::instance();
				break;
			default:
				return null;
				break;
		}
	}
}
