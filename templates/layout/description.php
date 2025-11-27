<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	?>
	<div class="ttbm_wp_editor" data-placeholder="">
		<h2 class="content-title"><?php esc_html_e( "Overview", 'tour-booking-manager' ); ?></h2>
		<div class="ttbm_widget_content" style="padding:15px 0;"><?php the_content(); ?></div>
	</div>