<?php

namespace Hametuha\Nichan\API;


use Hametuha\Nichan\Pattern\ApiBase;

/**
 * Comment customizer.
 *
 * @package Hametuha\Nichan\API
 */
class Comment extends ApiBase {

	/**
	 * Register hooks.
	 */
	protected function initialize() {
		parent::initialize();
		add_filter( 'comment_form_fields', array( $this, 'comment_form_fields' ) );
		add_action( 'pre_comment_on_post', array( $this, 'preprocess_comment_submit' ) );
		add_filter( 'pre_comment_approved', array( $this, 'preprocess_comment' ), 10, 2 );
		add_action( 'comment_post', array( $this, 'comment_posted' ) );
	}

	/**
	 * Register REST API
	 *
	 * @param \WP_REST_Server $wp_rest_server
	 */
	public function rest_api_init( $wp_rest_server ) {
		// Nothing to register.
	}

	/**
	 * Check comment stability
	 *
	 * @param int $comment_post_ID
	 */
	public function preprocess_comment_submit($comment_post_ID){
		if ( ! is_user_logged_in() && $this->is_thread( get_post_type( $comment_post_ID ) ) && $this->input->verify_nonce('nichan_comment', '_nichancommentnonce') ) {
			$recaptcha = $this->recaptcha->verify( $this->option->recaptcha_priv_key, $this->input->post( 'g-recaptcha-response' ), $this->input->remote_ip() );
			if ( ! $recaptcha || is_wp_error($recaptcha) ) {
				// This is anonymous comment.
				wp_die(
					__( 'Anonimous comment requires spam check of reCAPTCHA', '2ch' ),
					get_status_header_desc( 401 ) . ' | ' . get_bloginfo( 'name' ),
					array(
						'back_link' => true,
						'response'  => 401,
					)
				);
			} else {
				// Set current user as Anonymous user.
				wp_set_current_user( $this->option->post_as );
			}
		}
	}

	/**
	 * Requires moderation if this is anonymous comment
	 *
	 * @param string|int $approved
	 * @param array $comment_data
	 *
	 * @return mixed
	 */
	public function preprocess_comment( $approved, $comment_data ){
		if ( $this->is_thread( get_post_type( $comment_data['comment_post_ID'] ) ) ) {
			$user_id = $comment_data['user_id'];
			if ( $this->option->require_moderation && $user_id && $user_id == $this->option->post_as ) {
				return 0;
			}
		}
		return $approved;
	}

	/**
	 * Executed comment posted
	 *
	 * @param int $comment_id
	 */
	public function comment_posted($comment_id){
		$comment = get_comment( $comment_id );
		if ( $this->is_thread( get_post_type( $comment->comment_post_ID ) ) ) {
			// This may anonymous comment.
			if ( $this->input->post( '_nichancommentnonce' ) && $comment->user_id && $comment->user_id == $this->option->post_as ) {
				// Mark this as anonymous comment
				update_comment_meta( $comment_id, '_is_anonymous', 1 );
				// If hash exists, save it
				if ( $this->option->use_trip && ( $trip = $this->input->post( 'trip' ) ) ) {
					update_comment_meta( $comment_id, '_trip', $this->hash->generate( $trip ) );
				}
				// Put cookie for anonymous user.
				if ( isset( $_COOKIE['nichan_posted'] ) ) {
					$cookies = explode( '-', $_COOKIE['nichan_posted'] );
				} else {
					$cookies = array();
				}
				if ( false === array_search( $comment->comment_post_ID, $cookies ) ) {
					$cookies[] = $comment->comment_post_ID;
				}
				setcookie( 'nichan_posted', implode( '-', $cookies ), current_time( 'timestamp', true ) + 60 * 30, '/' );
			}
		}
	}

	/**
	 * Remove Fields
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function comment_form_fields( $args ){
		if( $this->is_thread( get_post_type() ) ) {
			if ( !is_user_logged_in() ) {
				wp_enqueue_script( '2ch-form' );
				foreach ( array( 'author', 'email', 'url' ) as $key ) {
					if( isset( $args[$key] ) ){
						unset($args[$key]);
					}
				}
				// Add trip
				if( $this->option->use_trip ){
					$title = __('Hash', '2ch');
					$place_holder = nichan_hash_description();
					$args['trip'] = <<<HTML
<p class="comment-form-trip">
<label for="trip">{$title}</label>
<input name="trip" id="trip" type="text" value="" size="30"/>
</p>
<p class="nichan-thread__description">{$place_holder}</p>
HTML;

				}
				// Add recaptcha
				$title = esc_html__('Spam Check', '2ch');
				$key = esc_attr( $this->option->recaptcha_pub_key );
				$nonce = wp_nonce_field( 'nichan_comment', '_nichancommentnonce', false, false );
				$id = get_the_ID();
				$args['recaptcha'] = <<<HTML
<p class="comment-form-recaptcha">
<label for="recaptcha">{$title}<span class="required">*</span></label>
</p>
<div id="nichan-recaptcha" data-post-id="{$id}" class="g-recaptcha nichan-thread__recaptcha" data-sitekey="{$key}"></div>
{$nonce}
HTML;
			}
		}
		return $args;
	}
}
