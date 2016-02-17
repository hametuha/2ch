<?php

namespace Hametuha\Nichan\API;

use Hametuha\Nichan\Pattern\ApiBase;
use Hametuha\Nichan\Pattern\Application;

/**
 * Thread manager
 *
 * @package Hametuha\Nichan\Template
 */
class Thread extends ApiBase {

	/**
	 * Constructor
	 */
	protected function initialize() {
		parent::initialize();
		add_action( 'init', array( $this, 'create_post_type' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'query_vars', array($this, 'query_vars') );
		add_filter( 'rewrite_rules_array',  array($this, 'rewrite_rules_array'));
		add_action( 'pre_get_posts', array($this, 'pre_get_posts') );
		add_action( 'transition_post_status', array($this, 'transition_post_status'), 10, 3 );
		add_action( 'template_redirect', array($this, 'template_redirect') );
	}

	/**
	 * Add query vars
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function query_vars( $vars ){
		$vars[] = 'nichan_preview';
		return $vars;
	}

	/**
	 * クエリバーを追加
	 *
	 * @param array $rules
	 *
	 * @return array
	 */
	public function rewrite_rules_array($rules){
		return array_merge( array(
			'^preview/([^/]+)/([^/]+)/?$' => 'index.php?post_type=$matches[1]&nichan_preview=$matches[2]',
		), $rules );
	}

	/**
	 * Change qeury vars
	 *
	 * @param $wp_query
	 */
	public function pre_get_posts( &$wp_query ){
		if ( $wp_query->is_main_query() && ( $hash = $wp_query->get('nichan_preview') ) ) {
			$post_type = $wp_query->get( 'post_type' );
			$wp_query->set( 'post_status', 'pending' );
			$wp_query->set( 'posts_per_page', 1 );
			$wp_query->set( 'meta_query', array(
				array(
					'key' => '_nichan_hash',
					'value' => $hash,
				),
				array(
					'key' => '_nichan_hash_limit',
					'value' => current_time('timestamp'),
					'compare' => '>',
					'type' => 'NUMERIC',
				)
			) );
			$wp_query->is_singular = true;
			$wp_query->is_archive = false;
		}
	}

	/**
	 * Register post type
	 */
	public function create_post_type(){
		if( $this->option->create_post_type ){
			/**
			 * nichan_post_type_args
			 *
			 * @param array $args Arguments passed to `register_post_type`.
			 * @param string $post_type Post type name
			 * @return array
			 */
			$args = apply_filters('nichan_post_type_args', array(
				'label' => $this->option->post_type_label_plural,
				'labels' => array(
					'name' => $this->option->post_type_label_plural,
					'singular_name' => $this->option->post_type_label_single,
					'add_new_item' => sprintf( __('Add New %s', '2ch'), $this->option->post_type_label_single ),
					'edit_item' => sprintf( __('Edit %s', '2ch'), $this->option->post_type_label_single ),
					'new_item' => sprintf( __('New %s', '2ch'), $this->option->post_type_label_single ),
					'view_item' => sprintf( __('View %s', '2ch'), $this->option->post_type_label_single ),
					'search_items' => sprintf( __('Search %s', '2ch'), $this->option->post_type_label_plural ),
					'not_found' => sprintf( __('No %s found.', '2ch'), $this->option->post_type_label_plural ),
					'not_found_in_trash' => sprintf( __('No %s found in Trash.', '2ch'),  $this->option->post_type_label_single ),
					'parent_item_colon' => sprintf( __('Parent %s:', '2ch'), $this->option->post_type_label_single ),
					'all_items' => sprintf( __( 'All %s', '2ch' ), $this->option->post_type_label_plural ),
					'archives' => sprintf( __( '%s Archives', '2ch' ), $this->option->post_type_label_single ),
					'insert_into_item' => sprintf( __( 'Insert into %s', '2ch' ), $this->option->post_type_label_single ),
					'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', '2ch' ), $this->option->post_type_label_single ),
					'filter_items_list' => sprintf( __( 'Filter %s list', '2ch' ), $this->option->post_type_label_plural ),
					'items_list_navigation' => sprintf( __( '%s list navigation', '2ch' ), $this->option->post_type_label_plural ),
					'items_list' => sprintf( __( '%s list', '2ch' ), $this->option->post_type_label_plural ),
				),
				'public' => true,
				'has_archive' => true,
				'supports' => array( 'title', 'editor', 'author', 'comments' ),
			), $this->option->post_type_name );
			register_post_type( $this->option->post_type_name, $args );
		}
	}

	/**
	 * Load style sheet.
	 */
	public function enqueue_scripts(){
		/**
		 * nichan_load_style
		 *
		 * If css can be load
		 *
		 * @param bool $load_style Flag used for deciding to load plugin's CSS.
		 * @return bool
		 */
		$load_style = apply_filters( 'nichan_load_style', true);
		if( $load_style  ) {
			wp_enqueue_style( '2ch-style', _2ch_plugin_dir_url('/dist/css/2ch.css'), array(), PLUGIN_2CH_VERSION );
		}
	}

	/**
	 * Show form
	 *
	 * @param string $post_type
	 */
	public function form( $post_type ) {
		if( $this->is_thread( $post_type ) ){
			wp_enqueue_script( '2ch-form' );
			$this->load_template( 'thread-form', array(
				'post_type' => get_post_type_object( $post_type ),
				'recaptcha' => $this->option->recaptcha_pub_key,
				'endpoint'  => rest_url($this->base.'/thread/'.$post_type.'/'),
				'use_hash'   => $this->option->use_trip,
			) );
		}
	}

	/**
	 * Register the_content filter
	 */
	public function template_redirect(){
		if( is_singular() ){
			add_filter( 'the_content', array( $this, 'the_content' ) );
		}
	}

	/**
	 * Show form automatically
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function the_content($content) {
		if ( $this->is_thread( get_post_type() ) && $this->option->show_form_automatically ) {
			ob_start();
			$this->form( get_post_type() );
			$form = ob_get_contents();
			ob_end_clean();
			$label = sprintf( __( 'Create %s', '2ch' ), get_post_type_object(get_post_type())->labels->singular_name );
			$content .= <<<HTML
<div class="nichan-thread__toggler">
	<button type="button" class="nichan-thread__button">{$label}</button>
</div>
{$form}
HTML;
		}
		return $content;
	}


	/**
	 * Register API
	 *
	 * @param \WP_REST_Server $wp_rest_server
	 */
	public function rest_api_init( $wp_rest_server ) {
		$self = $this;
		register_rest_route( $this->base, '/thread/(?P<post_type>[^/]+)/?$', array(
			array(
				'methods' => \WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'post_thread' ),
				'args' => array(
					'post_type' => array(
						'validate_callback' => function($param, $request, $key) use ($self) {
							return $self->is_thread( $param );
						},
					),
					'post_title' => array(
						'validate_callback' => function($param){
							return ! empty( $param );
						},
						'required' => true,
					),
					'post_content' => array(
						'validate_callback' => function($param){
							return ! empty( $param );
						},
						'required' => true,
					),
					'taxonomies' => array(
						'default' => array()
					),
					'g-recaptcha-response' => array(
						'validate_callback' => function($param){
							return ! empty( $param );
						},
						'required' => true,
					),
					'trip' => array(
						'default' => '',
					),
				),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		) );
	}

	/**
	 * Check permission
	 *
	 * @return bool|\WP_Error
	 */
	public function permission_callback(){
		return $this->recaptcha->verify(
			$this->option->recaptcha_priv_key,
			$this->input->post('g-recaptcha-response'),
			$this->input->remote_ip()
		);
	}


	/**
	 * Create thread
	 *
	 * @param array $params
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function post_thread($params){
		$post_status = $this->option->require_moderation ? 'pending' : 'publish';
		/**
		 * nichan_post_args
		 *
		 * Post array passed to `wp_insert_post` on thread creation.
		 *
		 * @param array $posts_arr
		 * @return array
		 */
		$posts_arr = apply_filters( 'nichan_post_args', array(
			'post_title'   => $params['post_title'],
			'post_content' => $params['post_content'],
			'post_type'    => $params['post_type'],
			'post_status'  => $post_status,
			'post_author'  => $this->option->post_as,
		) ) ;
		// Insert post
		$post_id = wp_insert_post( $posts_arr, true );
		if( is_wp_error( $post_id ) ){
			return $post_id;
		}
		// Assign object terms
		foreach( (array) $params['taxonomies'] as $taxonomy => $terms ){
			if ( is_numeric( $terms ) ) {
				// Hierarchical
				wp_set_object_terms( $post_id, intval($terms), $taxonomy );
			} elseif ( is_array( $terms ) && ! empty( $terms ) ) {
				// Tags
				wp_set_object_terms( $post_id, array_map( function( $term_id ) {
					return intval( $term_id );
				}, $terms ), $taxonomy );
			}
		}
		$post_type_object = get_post_type_object( $params['post_type'] );
		/**
		 * nichan_thread_message
		 *
		 * Message displayed on thread creation.
		 *
		 * @param string $message
		 * @param string $post_type
		 * @return string
		 */
		$message = apply_filters( 'nichan_thread_message', sprintf( __( '%s was successfully created', '2ch' ), $post_type_object->label), $post_type_object->name );
		// Add trip if specified.
		if( $params['trip'] && $this->option->use_trip ){
			$trip = $this->hash->generate( $params['trip'] );
			update_post_meta( $post_id, '_trip', $trip );
		}
		// Add hash
		$hash = md5( $post_type_object->name.'-'.$post_id );
		update_post_meta( $post_id, '_nichan_hash', $hash );
		/**
		 * nichan_preview_limit
		 *
		 * Preview URL's time limit.
		 *
		 * @param int $time_limit Time limit in seconds.
		 * @return int
		 */
		$hash_limit = apply_filters( 'nichan_preview_limit',  60 * 60 * 24 * 3 );
		update_post_meta( $post_id, '_nichan_hash_limit', current_time( 'timestamp' ) + $hash_limit );
		// Get permalink.
		if( 'publish' == $post_status ){
			$url = get_permalink( $post_id );
		}else{
			if ( get_option( 'rewrite_rules' ) ) {
				$url = home_url("/preview/{$post_type_object->name}/{$hash}");
			}else{
				$url = add_query_arg( array(
					'nichan_preview' => $hash,
					'post_type' => $post_type_object->name,
				), home_url() );
			}
		}
		return new \WP_REST_Response( array(
			'status' => $post_status,
			'permalink' => $url,
			'message' => $message,
		) );
	}

	/**
	 * Send email
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param $post
	 */
	public function transition_post_status( $new_status, $old_status, $post ){
		if ( 'pending' == $new_status && $this->is_thread( $post->post_type ) && $this->option->post_as == $post->post_author ) {

			/**
			 * nichan_pending_mail
			 *
			 * E-mail address to which review query will be sent.
			 *
			 * @param string E-mail. Default, admin's e-mail.
			 * @param \WP_Post $post Newly created post object.
			 * @return false|string If false returned, mail was not sent.
			 */
			$mail_to = apply_filters('nichan_pending_mail', get_option('admin_email'), $post );
			if ( $mail_to ) {
				$title   = $post->post_title;
				$body    = array(
					__( 'To Administrator', '2ch' ),
					'',
					'',
					__( 'New thread has been created and waiting for your review.', '2ch' ),
					__( 'Please go to the link below and moderate it.', '2ch' ),
					$title,
					get_permalink( $post ),
				);
				/**
				 * nichan_pending_mail_body
				 *
				 * Mail body of review query.
				 *
				 * @param string $body Mail message
				 * @param \WP_Post $post
				 * @return string
				 */
				$body    = apply_filters( 'nichan_pending_mail_body', implode( "\n", $body ), $post );
				wp_mail( $mail_to, sprintf( __( '[%s] Thread has been created', '2ch' ), get_bloginfo( 'name' ) ), $body );
			}
		}
	}
}
