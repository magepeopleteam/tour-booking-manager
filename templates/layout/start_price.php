<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
	$start_price = $start_price ?? TTBM_Function::get_tour_start_price( $ttbm_post_id );
	if ( $start_price && TTBM_Function::show_start_price( $ttbm_post_id ) ) {
		?>
		<strong><?php include TTBM_Function::template_path( 'layout/start_price_display.php' ); ?></strong>
	<?php } ?>