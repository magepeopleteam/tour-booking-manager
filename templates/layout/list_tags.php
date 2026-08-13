<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	// Small tag pills under the meta row (activities assigned to the tour).
	// Purely cosmetic grouping of data that already exists on the tour — no new field.
	$ttbm_post_id       = $ttbm_post_id ?? get_the_id();
	$tour_activities    = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_tour_activities', array() );
	$display_activities = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_activities', 'on' );
	$max_tags           = 2;

	if ( is_array( $tour_activities ) && sizeof( $tour_activities ) > 0 && $display_activities !== 'off' ) {
		$shown = 0;
		?>
		<div class="ttbm-gc-tags" data-placeholder>
			<?php
			foreach ( $tour_activities as $tour_activity ) {
				if ( $shown >= $max_tags ) {
					break;
				}
				$term = get_term_by( 'id', $tour_activity, 'ttbm_tour_activities' );
				if ( ! $term || is_wp_error( $term ) ) {
					continue;
				}
				$shown++;
				?>
				<span class="ttbm-gc-tag"><?php echo esc_html( $term->name ); ?></span>
				<?php
			}
			?>
		</div>
		<?php
	}
