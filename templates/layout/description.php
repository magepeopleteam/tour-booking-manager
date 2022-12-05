<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$tour_id = $tour_id ?? get_the_id();
		?>
		<div class="ttbm_description mp_wp_editor" data-placeholder="">
			<?php the_content(); ?>
		</div>