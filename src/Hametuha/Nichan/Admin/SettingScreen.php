<?php

namespace Hametuha\Nichan\Admin;

use Hametuha\Nichan\Pattern\Application;

/**
 * Option screen.
 *
 * @package Hametuha\Nichan\Admin
 */
class SettingScreen extends Application{

	/**
	 * @var string Title.
	 */
	protected $title = '';

	/**
	 * Constructor
	 */
	protected function initialize() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init') );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 4 );
	}

	/**
	 * Register menu
	 */
	public function admin_menu(){
		$this->title = __('2ch Setting', '2ch');
		add_options_page( $this->title, $this->title, 'manage_options', '2ch-setting', array( $this, 'render' ) );
	}

	/**
	 * Add action link to plugin list.
	 *
	 * @param array $actions
	 * @param string $plugin_file
	 * @param array $plugin_data
	 * @param string $context
	 *
	 * @return mixed
	 */
	public function plugin_action_links($actions, $plugin_file, $plugin_data, $context){
		if( '2ch' === $plugin_data['Name'] ){
			$actions['setting'] = sprintf('<a class="setting" href="%s">%s</a>', admin_url('options-general.php?page=2ch-setting'), __('Setting', '2ch') );
		}
		return $actions;
	}

	/**
	 * Register Ajax request
	 */
	public function admin_init(){
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ){
			add_action('wp_ajax_2ch_option', array( $this, 'ajax_request' ));
			add_action('wp_ajax_2ch_user_search', array( $this, 'user_search'));
		}
	}

	/**
	 * Handle Ajax request.
	 */
	public function ajax_request() {
		try {
			if ( ! $this->input->verify_nonce( 'update_2ch' ) ) {
				throw new \Exception( __( 'Invalid access.', '2ch' ), 400 );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( __( 'You have no permission.', '2ch' ), 403 );
			}
			$new_option = array();
			foreach ( $this->option->default_options as $key => $val ) {
				if( true === $val || false === $val ) {
					$new_option[ $key ] = (bool) $this->input->post( $key );
				}elseif( is_array($val) ){
					$new_option[ $key ] = (array) $this->input->post($key);
				}else{
					$new_option[ $key ] = $this->input->post( $key );
				}
			}
			$result = $this->option->save( $new_option );
			if ( is_wp_error( $result ) ) {
				status_header( 400 );
				wp_send_json( array(
					'error'    => true,
					'status'   => 400,
					'messages' => $result->get_error_messages(),
				) );
			} else {
				flush_rewrite_rules();
				wp_send_json( array(
					'success' => true,
					'message' => __( 'Option updated.', '2ch' )
				) );
			}
		} catch ( \Exception $e ) {
			status_header( $e->getCode() );
			wp_send_json( array(
				'error'    => true,
				'status'   => $e->getCode(),
				'messages' => array( $e->getMessage() )
			) );
		}
	}

	/**
	 * Search User
	 */
	public function user_search(){
		try{
			$s = $this->input->get( 'term' );
			if ( !$s ) {
				throw new \Exception('', 404);
			}
			$s = "%{$s}%";
			global $wpdb;
			$query = <<<SQL
				SELECT ID, display_name FROM {$wpdb->users}
				WHERE ( user_login LIKE %s)
				   OR ( display_name LIKE %s )
				   OR ( user_email LIKE %s )
				LIMIT 10
SQL;
			$result = array();
			foreach( $wpdb->get_results($wpdb->prepare($query, $s, $s, $s)) as $user ){
				$result[] = array(
					'value' => $user->ID,
					'label' => $user->display_name,
					'image' => get_avatar($user->ID, 32),
				);
			}
			wp_send_json($result);
		}catch(\Exception $e){
			wp_send_json(array());
		}
	}

	/**
	 * Enqueue assets
	 *
	 * @param string $page Page slug.
	 */
	public function admin_enqueue_scripts($page){
		if( 'settings_page_2ch-setting' === $page ){
			wp_enqueue_script('2ch-admin', _2ch_plugin_dir_url('/dist/js/2ch-admin.js'), array( 'jquery-form', 'jquery-effects-highlight', 'jquery-ui-autocomplete' ), PLUGIN_2CH_VERSION, true);
			wp_localize_script('2ch-admin', 'NichanAdmin', array(
			    'endpoint' => admin_url('admin-ajax.php'),
				'actionSearch' => '2ch_user_search',
			) );
			wp_enqueue_style('2ch-admin', _2ch_plugin_dir_url('/dist/css/2ch-admin.css'), array(), PLUGIN_2CH_VERSION);
		}
	}

	/**
	 * Render admin screen
	 */
	public function render(){
		?>
		<div class="wrap">
			<h2><?php echo esc_html( $this->title ) ?></h2>
			<form id="form-2ch-setting" action="<?php echo admin_url('admin-ajax.php') ?>" method="post">
				<span class="indicator">
					<span class="dashicons dashicons-image-rotate"></span><br />
					<span class="indicator--text"><?php esc_attr_e('Loading...', '2ch') ?></span>
					<?php ?>
				</span>
				<input type="hidden" name="action" value="2ch_option">
				<?php wp_nonce_field( 'update_2ch' ) ?>
				<table class="form-table setting2ch">
					<tr>
						<th>
							<label><?php esc_html_e('Select Existing Post Types', '2ch') ?></label>
						</th>
						<td>
							<?php
							$default = $this->option->post_type_name;
							$post_types = array_filter( get_post_types( array(
								'public' => true,
							), OBJECT ), function( $post_type ) use ( $default ) {
								return post_type_supports( $post_type->name, 'comments' ) && $post_type->name != $default ;
							});
							foreach( $post_types as $post_type ){
								printf(
									'<label class="setting2ch__label--inline"><input type="checkbox" name="editable_post_types[]" value="%s"%s> %s</label>',
									esc_attr($post_type->name),
									checked( false !== array_search($post_type->name, $this->option->editable_post_types ), true, false ),
									esc_html( $post_type->label )
								);
							}
							?>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e('Create Post Type', '2ch') ?></label>
						</th>
						<td>
							<label class="setting2ch__label">
								<input type="checkbox" name="create_post_type" value="1"<?php checked( $this->option->create_post_type ) ?>> <?php esc_html_e('Create new post type for thread', '2ch') ?>
							</label>
							<hr />
							<label class="setting2ch__label">
								<?php esc_html_e('Post type name', '2ch') ?><br />
								<input type="text" class="regular-text" name="post_type_name" value="<?php echo esc_attr( $this->option->post_type_name ) ?>" />
							</label>
							<label class="setting2ch__label">
								<?php esc_html_e('Post type label(singular)', '2ch') ?><br />
								<input type="text" class="regular-text" name="post_type_label_single" value="<?php echo esc_attr( $this->option->post_type_label_single ) ?>" />
							</label>
							<label class="setting2ch__label">
								<?php esc_html_e('Post type label(plural)', '2ch') ?><br />
								<input type="text" class="regular-text" name="post_type_label_plural" value="<?php echo esc_attr( $this->option->post_type_label_plural ) ?>" />
							</label>
							<p class="description">
								<strong><?php esc_html_e('For advanced user: ', '2ch') ?></strong>
								<?php printf(
									esc_html( __('You can override post type setting with %s filter. For $arg\'s detail, see %s.', '2ch') ),
									'<code>nichan_post_type_args</code>',
									sprintf( '<a href="%s">Codex</a>', esc_url(__('https://codex.wordpress.org/Function_Reference/register_post_type', '2ch') ) )
								); ?>
							</p>
							<pre><?php
								$comment1 = esc_html__('Hide archive page.', '2ch');
								$comment2 = esc_html__('Hide admin screen if current user is not editor.', '2ch');
								echo <<<PHP
add_filter('nichan_post_type_args', function(\$arg){
    // {$comment1}
    \$arg['has_archive']  = false;
    // {$comment2}
    \$arg['show_ui'] = current_user_can('edit_others_posts');
    return \$arg;
});
PHP;
							?></pre>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e('Form Display', '2ch') ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="show_form_automatically" value="1"<?php checked( $this->option->show_form_automatically ) ?>> <?php esc_html_e('Show thread form automatically', '2ch') ?>
							</label>
							<p class="description">
								<?php esc_html_e('If you check this, thread form will be displayed after comments. You can manually display it with template tag:'); ?><br />
								<code>&lt;?php nichan_thread_form('post_type') ?&gt;</code>
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e('Moderation', '2ch') ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="require_moderation" value="1"<?php checked( $this->option->require_moderation ) ?>> <?php esc_html_e('Require moderation for thread', '2ch') ?>
							</label>
							<p class="description">
								<?php esc_html_e('If checked, every post\'s status will be pending and requires your moderation.', '2ch') ?>
							</p>
						</td>
					</tr>
					<tr>
						<th>
							<label><?php esc_html_e('Hash', '2ch') ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="use_trip" value="1"<?php checked( $this->option->use_trip ) ?>> <?php esc_html_e('Use hash to distinct each user', '2ch') ?>
							</label>
						</td>
					</tr>
					<tr>
						<th>
							<label for="setting-2ch-user"><?php esc_html_e('Post as', '2ch') ?></label>
						</th>
						<td class="setting2ch__user">
							<?php
								$user = get_userdata($this->option->post_as);
								$display_name = $user ? $user->display_name : '';
							?>
							<?php echo get_avatar($this->option->post_as, 32) ?>
							<input id="setting2ch-user" type="text" placeholder="<?php esc_attr_e('Type and Search...',  '2ch') ?>" class="regular-text" value="<?php echo esc_attr($display_name) ?>" >
							<input type="hidden" name="post_as" id="nichan-post-as" value="<?php echo esc_attr($this->option->post_as) ?>" />
							<p class="description">
								<?php esc_html_e('Every anonymous threads and comments will be owned by this user. It is strongly recommended creating new user like "anonymous".', '2ch') ?>
							</p>
						</td>
					</tr>
					<tr>
						<th><label>reCAPTCHA</label></th>
						<td>
							<label class="setting2ch__label">
								<?php esc_html_e('Site Key', '2ch') ?><br />
								<input type="text" class="regular-text" name="recaptcha_pub_key" value="<?php echo esc_attr( $this->option->recaptcha_pub_key ) ?>" />
							</label>
							<label class="setting2ch__label">
								<?php esc_html_e('Secret Key', '2ch') ?><br />
								<input type="text" class="regular-text" name="recaptcha_priv_key" value="<?php echo esc_attr( $this->option->recaptcha_priv_key ) ?>" />
							</label>
							<p class="description">
								<?php printf( __( 'You should register Google reCAPTCHA and get code <a target="_blank" href="%s">here</a>.', '2ch' ) , 'https://www.google.com/recaptcha/intro/index.html') ?>
							</p>
						</td>
					</tr>

				</table>

				<?php submit_button() ?>
			</form>
			<hr />
			<div class="setting2ch__footer">
				<?php echo get_avatar('takahashi.fumiki@hametuha.co.jp') ?>
				<h2 class="setting2ch__footer--title"><?php esc_html_e('From Plugin Author') ?></h2>
				<p>
					<?php _e( 'I\'m <a href="https://hametuha.co.jp" target="_blank">Takahash Fumiki</a>, the plugin developer. If you have question about 2ch, ask me via <a href="https://twitter.com/takahashifumiki/" target="_blank">twitter</a>. This plugin is hosted on <a target="_blank" href="https://github.com/hametuha/2ch/">github</a> and feel free to send <abbr>P.R.</abbr>', '2ch') ?>
				</p>
				<div style="clear:left;"></div>
			</div>

		</div>
		<?php
	}

}
