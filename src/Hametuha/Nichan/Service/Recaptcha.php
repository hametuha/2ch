<?php

namespace Hametuha\Nichan\Service;


use Hametuha\Nichan\Pattern\Singleton;

class Recaptcha extends Singleton {

	protected $endpoint = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * Verify reCAPTCHA
	 *
	 * @param string $private_key
	 * @param string $code
	 * @param string $ip
	 *
	 * @return bool|\WP_Error
	 */
	public function verify( $private_key, $code, $ip ){
		$ch = curl_init($this->endpoint);
		curl_setopt_array( $ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
		    CURLOPT_TIMEOUT => 10,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POSTFIELDS => array(
				'secret' => $private_key,
				'response' => $code,
				'remoteip' => $ip,
			),
		) );
		try{
			$result = curl_exec($ch);
			if ( ! $result || !( $result = json_decode($result) ) ) {
				throw new \Exception( sprintf( __( 'Error %s: Failed to get response.', '2ch'), curl_errno($ch)) , 500);
			}
			if( ! $result->success ){
				throw new \Exception( __( 'Failed to authenticate reCAPTCHA. Please try again.', '2ch' ), 500 );
			}
			curl_close($ch);
			return true;
		}catch( \Exception $e ){
			curl_close( $ch );
			return new \WP_Error( $e->getCode(), $e->getMessage() );
		}

	}

}