<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
	$start_price = TTBM_Function::get_tour_start_price( $ttbm_post_id );
	$count       = $count ?? 0;
	if ( $start_price && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_price_start', 'on' ) != 'off' ) {
		?>
			<div class="item_icon">
				<i class="fas fa-coins"></i>
				<?php include( TTBM_Function::template_path( 'layout/start_price.php' ) ); ?>
			</div>
		<?php
		$count ++;
	}
?>