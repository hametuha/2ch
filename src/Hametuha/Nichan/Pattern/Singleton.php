<?php

namespace Hametuha\Nichan\Pattern;

/**
 * Singleton Patern.
 *
 * @package Hametuha\Nichan\Pattern
 */
abstract class Singleton{

	/**
	 * @var array Instance holder.
	 */
	private static $instances = [];

	/**
	 * Constructor
	 */
	final protected function __construct() {
		$this->initialize();
	}

	/**
	 * Executed on constructor
	 */
	protected function initialize(){
		// Do nothing.
		// Override if required.
	}

	/**
	 * Get instance
	 *
	 * @return static
	 */
	final public static function instance(){
		$class_name = get_called_class();
		if( !isset(self::$instances[$class_name]) ){
			self::$instances[$class_name] = new $class_name();
		}
		return self::$instances[$class_name];
	}

}
