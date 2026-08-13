<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	// Small tag pills under the meta row (activities assigned to the tour).
	// Purely cosmetic grouping of data that already exists on the tour — no new field.
	$ttbm_post_id       = $ttbm_post_id ?? get_the_id();
	/* Reads the real ttbm_tour_activities TAXONOMY relationship (get_the_terms), not
	   the legacy postmeta array of the same name — that postmeta only exists for a
	   handful of older tours and holds stale term IDs that no longer match the live
	   taxonomy (see the matching fix + comment in TTBM_Tour_List.php's data-activity
	   attribute), so it silently showed no tags at all for most tours. */
	$tour_activities    = get_the_terms( $ttbm_post_id, 'ttbm_tour_activities' );
	$display_activities = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_activities', 'on' );
	$max_tags           = 2;

	if ( is_array( $tour_activities ) && sizeof( $tour_activities ) > 0 && $display_activities !== 'off' ) {
		$shown = 0;
		?>
		<div class="ttbm-gc-tags" data-placeholder>
			<?php
			foreach ( $tour_activities as $term ) {
				if ( $shown >= $max_tags ) {
					break;
				}
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
