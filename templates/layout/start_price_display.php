<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	$ttbm_post_id   = $ttbm_post_id ?? get_the_id();
	$tour_id        = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );

	if ( empty( $start_price ) ) {
		$start_price = TTBM_Function::get_tour_start_price( $tour_id );
	}
	if ( empty( $regular_price ) ) {
		$regular_price = TTBM_Function::get_tour_start_regular_price( $tour_id );
	}
	$wrapper_class  = $wrapper_class ?? '';
	$current_class  = $current_class ?? '';
	$original_class = $original_class ?? 'ttbm_regular_price strikeLine';

	if ( ! $start_price ) {
		return;
	}

	$show_price_start = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_price_start', 'on' ) !== 'off';
	if ( ! $show_price_start && empty( $ttbm_force_hero_price ) ) {
		return;
	}

	$has_discount = $regular_price && floatval( $regular_price ) > floatval( $start_price );
	?>
	<span class="ttbm_start_price_display<?php echo $wrapper_class ? ' ' . esc_attr( $wrapper_class ) : ''; ?>">
		<?php if ( $has_discount ) : ?>
			<span class="<?php echo esc_attr( $original_class ); ?>">
				<?php echo wp_kses_post( wc_price( $regular_price ) ); ?>
			</span>
		<?php endif; ?>
		<?php if ( $current_class ) : ?>
			<span class="<?php echo esc_attr( $current_class ); ?>">
				<?php echo wp_kses_post( wc_price( $start_price ) ); ?>
			</span>
		<?php else : ?>
			<?php echo wp_kses_post( wc_price( $start_price ) ); ?>
		<?php endif; ?>
	</span>
	<?php
