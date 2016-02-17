<?php

namespace Hametuha\Nichan\Utility;


use Hametuha\Nichan\Pattern\Singleton;

/**
 * Generate hash
 *
 * @package Hametuha\Nichan\Utility
 */
class Hash extends Singleton {

	/**
	 * Convert 16 digit to 10 digit
	 *
	 * @param string $hex
	 * @return int
	 */
	private function convert($hex) {
		$digit = base_convert($hex, 16, 35);
		return $digit;
	}

	/**
	 * Generate hash.
	 *
	 * @param string $string
	 * @return string
	 */
	public function generate($string){
		$md5 = md5($string);
		$hash_table = array();
		for( $i = 0; $i < 8; $i++ ){
			$hash_table[] = substr($md5, $i, 4);
		}
		$hash = '';
		foreach( $hash_table as $hex ){
			$hash .= $this->convert( $hex );
		}
		return substr($hash, max(0, strlen($hash) - 16 ) );
	}

}
