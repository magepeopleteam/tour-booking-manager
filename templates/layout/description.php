<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	?>
	<div class="ttbm_default_widget ttbm_wp_editor" data-placeholder="">
		<?php do_action( 'ttbm_section_title', 'ttbm_string_overview', esc_html__( "Overview", 'tour-booking-manager' ) ); ?>
		<div class="ttbm_widget_content" style="padding: 15px;"><?php the_content(); ?></div>
	</div>