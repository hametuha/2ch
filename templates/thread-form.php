<?php
/*

If you want to customize, copy and paste this file to your
theme's 2ch/ directory.(e.g. your-theme/2ch/thread-form.php)

Required elements are:

- Root form with (class=nichan-thread, method=post, action=endpoint)
- Each input(name=post_title) and textarea(name=post_content)
- Submit button.
- div.g-recaptha element for Google's reCapthca.

For taxonomy work around:

- By default, every public taxonomy will be displayed.
- Hierarchials(e.g. category) are drop-down, Non-hierarchials(e.g. post tags) are checkboxes.
- Their name should be (name=taxonomies[category]) on drop-down, (name=taxonomies[post_tag][]) on checkbox.
  value attribute should be term_id(NOT term_taxonmy_id!)

*/


/** @var Hametuha\Nichan\API\Thread $this */
/** @var stdClass $post_type */
/** @var string   $endpoint  */
/** @var string   $recaptcha */
?>

<form class="nichan-thread" method="post" action="<?php echo esc_url( $endpoint ) ?>">

	<div class="nichan-thread__title">
		<?php echo esc_html( sprintf( __('Add New %s', '2ch'), $post_type->labels->singular_name ) ); ?>
	</div>

	<!-- Title -->
	<div class="nichan-thread__group">
		<label class="nichan-thread__label" for="nichan-thread-title"><?php esc_html_e( 'Title', '2ch' ) ?></label>
		<input type="text" id="nichan-thread-title" name="post_title" value=""
		       placeholder="<?php esc_attr_e( 'Enter title here.', '2ch' ) ?>">
	</div>

	<!-- Content -->
	<div class="nichan-thread__group">
		<label class="nichan-thread__label" for="nichan-thread-content"><?php esc_html_e( 'Content', '2ch' ) ?></label>
		<textarea id="nichan-thread-content" name="post_content"
		          placeholder="<?php echo esc_attr( sprintf( __( 'Enter %s\'s detail here.', '2ch'), $post_type->labels->singular_name ) ) ?>"></textarea>
	</div>

	<!-- Taxonomies -->
	<?php foreach( get_object_taxonomies( $post_type->name, OBJECT ) as $taxonomy ) : if( $taxonomy->public ) :  ?>
	<div class="nichan-thread__group">
		<?php
		if ( $taxonomy->hierarchical ) :
		?>
		<label class="nichan-thread__label">
			<?php  echo esc_html( $taxonomy->label ); ?>

			<?php
			// Show drop down if this is hierarchial taxonomy.
			wp_dropdown_categories( array (
				'taxonomy' => $taxonomy->name,
				'name' => "taxonomies[$taxonomy->name]",
				'class' => 'nichan-thread__select',
				'hierarchical' => true,
				'hide_empty' => false,
			) );
			?>
		</label>
		<?php else : ?>
			<label class="nichan-thread__label"><?php echo esc_html( $taxonomy->label ) ?></label>
			<?php foreach( get_terms( $taxonomy->name, array( 'hide_empty' => false ) ) as $term ) :  ?>
				<label class="nichan-thread__label--inline">
					<input type="checkbox" class="nichan-thread__checkbox"
					       name="taxonomies[<?php echo esc_attr( $term->taxonomy ) ?>][]"
					       value="<?php echo esc_attr( $term->term_id ) ?>" />
					<?php echo esc_html( $term->name ) ?>
				</label>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php endif; endforeach; ?>

	<?php
	/**
	 * nichan_after_form
	 *
	 * Executed on create thread form
	 *
	 * @param stdClass $post_type
	 */
	do_action( 'nichan_after_form', $post_type )
	?>

	<!-- reCaptcha -->
	<div class="g-recaptcha nichan-thread__recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha ) ?>"></div>

	<!-- Submit -->
	<p class="nichan-thread__submit">
		<input type="submit" value="<?php esc_attr_e( 'Submit', '2ch' ) ?>">
	</p>

</form>
