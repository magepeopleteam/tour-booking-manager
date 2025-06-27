<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	?>
	<div class="ttbm_description ttbm_wp_editor" data-placeholder="">
		<h2 class="ttbm_description_title"><?php esc_html_e('Overview','tour-booking-manager'); ?></h2>
		<div class="ttbm_content"><?php the_content(); ?></div>
	</div>