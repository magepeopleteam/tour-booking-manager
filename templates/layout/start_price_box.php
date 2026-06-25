<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id      = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
	$start_price  = TTBM_Function::get_tour_start_price( $tour_id );
	$count       = $count ?? 0;
	if ( $start_price && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_price_start', 'on' ) != 'off' ) {
		?>
			<div class="item_icon<?php echo esc_attr( TTBM_Function::hero_stat_item_class( false ) ); ?>" title="<?php esc_html_e( 'Price', 'tour-booking-manager' ); ?>">
				<i class="mi mi-coins"></i>
				<?php include TTBM_Function::template_path( 'layout/start_price_display.php' ); ?>
			</div>
		<?php
		$count++;
	}
?>