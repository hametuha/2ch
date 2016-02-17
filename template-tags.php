<?php
/**
 * Global Functions
 *
 * @package Nichan
 */


/**
 * Get plugin dir URL
 *
 * @package 2ch
 * @param string $path
 * @return string
 */
function _2ch_plugin_dir_url( $path = '' ){
	if( $path ){
		$path = '/'.ltrim($path, '/');
	}
	return untrailingslashit(plugin_dir_url(__FILE__)).$path;
}

/**
 * Show thread from
 *
 * @param string $post_type
 */
function nichan_thread_form($post_type){
	Hametuha\Nichan\API\Thread::instance()->form( $post_type );
}

/**
 * Return threads hash
 *
 * @param null|int|WP_Post $post
 *
 * @return string
 */
function nichan_hash($post = null) {
	$post = get_post($post);
	return (string) get_post_meta($post->ID, '_trip', true);
}

/**
 * Return comment's hash
 *
 * @param null|int|WP_Comment $comment
 *
 * @return string
 */
function nichan_comment_hash($comment = null) {
	$comment = get_comment($comment);
	return (string) get_comment_meta( $comment->comment_ID, '_trip', true );
}

/**
 * Return description about trip.
 *
 * @return string
 */
function nichan_hash_description(){
	return esc_html__( 'Hash is meaningless letters which is generated from other letter and always results same. If you enter "example", hash is always "w8lyfng5x19pmk8i". You can identify yourself with this uniqueness.', '2ch' );
}
