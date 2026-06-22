<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	?>
	<section class="ttbm_overview_section ttbm_details_block">
		<h2 class="ttbm_section_title"><?php esc_html_e( 'Overview', 'tour-booking-manager' ); ?></h2>
		<div class="ttbm_wp_editor" data-placeholder="">
			<div class="ttbm_widget_content"><?php the_content(); ?></div>
		</div>
	</section>
