<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	// Optional per-tour rating (Display Settings tab). Off by default and hidden
	// entirely unless an admin has explicitly turned it on and filled in both an
	// average and a review count — no fabricated numbers are ever shown.
	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
	$display_rating   = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_rating', 'off' );
	$rating_average   = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_rating_average' );
	$rating_count     = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_rating_count' );

	if ( $display_rating === 'on' && is_numeric( $rating_average ) && (int) $rating_count > 0 ) {
		?>
		<div class="ttbm-gc-rating" data-placeholder>
			<span class="mi mi-star" aria-hidden="true"></span>
			<span class="ttbm-gc-rating-value"><?php echo esc_html( number_format_i18n( (float) $rating_average, 1 ) ); ?></span>
			<span class="ttbm-gc-rating-count">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: review count */
						_n( '(%s review)', '(%s reviews)', (int) $rating_count, 'tour-booking-manager' ),
						number_format_i18n( (int) $rating_count )
					)
				);
				?>
			</span>
		</div>
		<?php
	}
