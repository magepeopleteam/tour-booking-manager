<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
	$tour_id     = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
	$class_price = $class_price ?? '';
	$start_price = TTBM_Function::get_tour_start_price( $tour_id );
	if ( $start_price !== '' && (float) $start_price > 0 ) {
		?>
		<div class="ttbm_list_info <?php echo esc_attr( $class_price ); ?>" data-placeholder>
			<span class="mi mi-money-bill-wave"></span>
			<?php esc_html_e( 'From', 'tour-booking-manager' ); ?>&nbsp;
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wc_price( $start_price );
			?>
		</div>
	<?php } ?>