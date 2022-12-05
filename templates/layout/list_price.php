<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$tour_id     = $tour_id ?? get_the_id();
	$class_price = $class_price ?? '';
	$start_price = TTBM_Function::get_tour_start_price( $tour_id );
	if ( $start_price ) {
		?>
		<div class="ttbm_list_info <?php echo esc_attr( $class_price ); ?>" data-placeholder>
			<span class="fas fa-money-bill"></span>
			<?php esc_html_e( 'From', 'tour-booking-manager' ); ?>&nbsp;<?php echo wc_price($start_price); ?>
		</div>
	<?php } ?>