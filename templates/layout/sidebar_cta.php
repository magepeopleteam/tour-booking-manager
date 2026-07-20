<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! function_exists( 'ttbm_sidebar_cta_format_ticket' ) ) {
		function ttbm_sidebar_cta_format_ticket( $tour_id, $ticket, $start_date ) {
			$name    = $ticket['ticket_type_name'];
			$regular = floatval( $ticket['ticket_type_price'] );
			$sale    = ! empty( $ticket['sale_price'] ) ? floatval( $ticket['sale_price'] ) : 0;
			$current = $sale ? $sale : $regular;
			$current = apply_filters( 'ttbm_filter_ticket_price', $current, $tour_id, $start_date, $name );
			$display_regular = ( $sale && $regular > $sale ) ? $regular : 0;
			$discount = 0;

			if ( $display_regular && $current < $display_regular ) {
				$discount = (int) round( ( ( $display_regular - $current ) / $display_regular ) * 100 );
			}

			return array(
				'label'    => $name,
				'price'    => $current,
				'regular'  => $display_regular,
				'discount' => $discount,
			);
		}
	}

	if ( ! function_exists( 'ttbm_sidebar_cta_ticket' ) ) {
		function ttbm_sidebar_cta_ticket( $tour_id, $keywords, $fallback_index = null, $exclude_name = '' ) {
			$tickets = TTBM_Function::get_ticket_type( $tour_id );
			if ( empty( $tickets ) ) {
				return null;
			}

			$start_date = '';
			$all_dates  = TTBM_Function::get_date( $tour_id );
			if ( ! empty( $all_dates ) ) {
				$start_date = TTBM_Function::get_effective_booking_date( $tour_id, $all_dates );
			}

			foreach ( $tickets as $ticket ) {
				$name = $ticket['ticket_type_name'];
				if ( $exclude_name && $name === $exclude_name ) {
					continue;
				}
				foreach ( (array) $keywords as $keyword ) {
					if ( stripos( $name, $keyword ) !== false ) {
						return ttbm_sidebar_cta_format_ticket( $tour_id, $ticket, $start_date );
					}
				}
			}

			if ( null !== $fallback_index && isset( $tickets[ $fallback_index ] ) ) {
				$ticket = $tickets[ $fallback_index ];
				if ( $exclude_name && $ticket['ticket_type_name'] === $exclude_name ) {
					return null;
				}
				return ttbm_sidebar_cta_format_ticket( $tour_id, $ticket, $start_date );
			}

			return null;
		}
	}

	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id      = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );

	if ( TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' ) === 'off' ) {
		return;
	}

	$adult_ticket = ttbm_sidebar_cta_ticket( $tour_id, array( 'adult' ), 0 );
	$child_ticket = ttbm_sidebar_cta_ticket( $tour_id, array( 'child', 'kid', 'infant' ), 1, $adult_ticket['label'] ?? '' );

	if ( ! $adult_ticket && ! $child_ticket ) {
		$start_price = TTBM_Function::get_tour_start_price( $tour_id );
		if ( ! $start_price ) {
			return;
		}
		$start_regular = TTBM_Function::get_tour_start_regular_price( $tour_id );
		$adult_ticket = array(
			'label'    => __( 'Adult', 'tour-booking-manager' ),
			'price'    => floatval( $start_price ),
			'regular'  => $start_regular ? floatval( $start_regular ) : 0,
			'discount' => 0,
		);
		if ( $adult_ticket['regular'] && $adult_ticket['price'] < $adult_ticket['regular'] ) {
			$adult_ticket['discount'] = (int) round( ( ( $adult_ticket['regular'] - $adult_ticket['price'] ) / $adult_ticket['regular'] ) * 100 );
		}
	}

	$discount_badge = max( (int) ( $adult_ticket['discount'] ?? 0 ), (int) ( $child_ticket['discount'] ?? 0 ) );
	$display_enquiry = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_enquiry', 'on' );
	?>
	<div class="ttbm_sidebar_cta">
		<?php if ( $discount_badge > 0 ) : ?>
			<span class="ttbm_sidebar_cta_badge"><?php echo esc_html( $discount_badge . '% ' . __( 'Off', 'tour-booking-manager' ) ); ?></span>
		<?php endif; ?>

		<div class="ttbm_sidebar_cta_pricing<?php echo $discount_badge > 0 ? ' has-badge' : ''; ?>">
			<?php if ( $adult_ticket ) : ?>
				<div class="ttbm_sidebar_cta_price_col">
					<span class="ttbm_sidebar_cta_from"><?php esc_html_e( 'From', 'tour-booking-manager' ); ?></span>
					<div class="ttbm_sidebar_cta_amount">
						<?php if ( ! empty( $adult_ticket['regular'] ) && floatval( $adult_ticket['regular'] ) > floatval( $adult_ticket['price'] ) ) : ?>
							<span class="ttbm_sidebar_cta_regular ttbm_regular_price strikeLine"><?php echo wp_kses_post( wc_price( $adult_ticket['regular'] ) ); ?></span>
						<?php endif; ?>
						<strong><?php echo wp_kses_post( wc_price( $adult_ticket['price'] ) ); ?></strong>
						<span class="ttbm_sidebar_cta_per">/ <?php echo esc_html( $adult_ticket['label'] ); ?></span>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $child_ticket ) : ?>
				<div class="ttbm_sidebar_cta_price_col">
					<span class="ttbm_sidebar_cta_from"><?php esc_html_e( 'From', 'tour-booking-manager' ); ?></span>
					<div class="ttbm_sidebar_cta_amount">
						<?php if ( ! empty( $child_ticket['regular'] ) && floatval( $child_ticket['regular'] ) > floatval( $child_ticket['price'] ) ) : ?>
							<span class="ttbm_sidebar_cta_regular ttbm_regular_price strikeLine"><?php echo wp_kses_post( wc_price( $child_ticket['regular'] ) ); ?></span>
						<?php endif; ?>
						<strong><?php echo wp_kses_post( wc_price( $child_ticket['price'] ) ); ?></strong>
						<span class="ttbm_sidebar_cta_per">/ <?php echo esc_html( $child_ticket['label'] ); ?></span>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="ttbm_sidebar_cta_actions">
			<button type="button" class="ttbm_sidebar_cta_btn" data-ttbm-book-now>
				<?php echo esc_html( TTBM_Function::get_translation_settings( 'ttbm_string_check_availability', esc_html__( 'Check Availability', 'tour-booking-manager' ) ) ); ?>
			</button>

			<?php if ( $display_enquiry !== 'off' ) : ?>
				<p class="ttbm_sidebar_cta_help">
					<?php esc_html_e( 'Need help with booking?', 'tour-booking-manager' ); ?>
					<button type="button" class="ttbm_sidebar_cta_link" data-target-popup="get-enquiry-popup">
						<?php esc_html_e( 'Send Us A Message', 'tour-booking-manager' ); ?>
					</button>
				</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
