<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$description = TTBM_Function::get_post_info( $ttbm_post_id, 'ttbm_short_description' );
	$description = $description ?: get_post_field( 'post_content', $ttbm_post_id );
	if ( $description ) {
		?>
		<div class="ttbm_description mp_wp_editor" data-placeholder>
			<div>
				<?php echo TTBM_Function::esc_html( $description ); ?>
			</div>
		</div>
		<?php } ?>