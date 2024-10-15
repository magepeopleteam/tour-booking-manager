<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
		?>
		<div class="ttbm_description mp_wp_editor" data-placeholder="">
			<h2><?php _e('Overview','tour-booking-manager'); ?></h2>
			<?php the_content(); ?>
		</div>