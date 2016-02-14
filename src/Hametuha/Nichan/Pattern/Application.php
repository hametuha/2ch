<?php

namespace Hametuha\Nichan\Pattern;
use Hametuha\Nichan\Utility\Input;
use Hametuha\Nichan\Utility\Option;

/**
 * Class Application
 * @package Hametuha\Nichan\Pattern
 * @property-read Input $input
 * @property-read option $option
 */
abstract class Application extends Singleton{

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
			default:
				return null;
				break;
		}
	}
}
