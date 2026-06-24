<?php
// Template Name: Smart Theme

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
$tour_id          = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
$class_location   = $class_location ?? '';
$ttbm_display_reg = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_registration', 'on' );
$travel_type      = TTBM_Function::get_travel_type( $tour_id );
$ttbm_auto_date   = in_array( $travel_type, array( 'repeated', 'particular' ), true );
?>
<div class="ttbm_smart_theme">
	<div class="ttbm_style ttbm_wraper placeholderLoader ttbm_details_page_loader">
		<div class="ttbm_container">
			<div class="ttbm_smart_gallery placeholder_area">
				<?php
				$ttbm_smart_overlay_cb = static function () use ( $ttbm_post_id ) {
					?>
					<div class="ttbm_smart_hero_overlay">
						<div class="ttbm_smart_hero_overlay_inner">
							<div class="ttbm_smart_hero_overlay_head">
								<?php do_action( 'ttbm_details_title' ); ?>
							</div>
							<div class="ttbm_smart_hero_meta">
								<div class="ttbm-review-location-area">
									<?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>
									<?php do_action( 'ttbm_details_location' ); ?>
								</div>
							</div>
						</div>
					</div>
					<?php
				};
				add_action( 'ttbm_slider_all_item_overlay', $ttbm_smart_overlay_cb );
				do_action( 'ttbm_slider' );
				remove_action( 'ttbm_slider_all_item_overlay', $ttbm_smart_overlay_cb );
				?>
			</div>

			<div class="ttbm_details_page">
				<div class="ttbm_content_area">
					<div class="ttbm_content__left">
						<div class="ttbm_hero_stats ttbm_smart_hero_stats placeholder_area">
							<?php
							TTBM_Function::enable_hero_stat_limit();
							ob_start();
							$ttbm_hero_loc_cb = static function ( $hero_post_id ) use ( $ttbm_post_id ) {
								$ttbm_post_id = $hero_post_id ?: $ttbm_post_id;
								include TTBM_Function::template_path( 'layout/hero_location_box.php' );
							};
							add_action( 'ttbm_short_details_before', $ttbm_hero_loc_cb, 10, 1 );
							do_action( 'ttbm_short_details' );
							remove_action( 'ttbm_short_details_before', $ttbm_hero_loc_cb, 10 );
							$ttbm_hero_stats_html = ob_get_clean();
							$ttbm_hero_stat_count = TTBM_Function::get_hero_stat_render_count();
							TTBM_Function::disable_hero_stat_limit();
							?>
							<?php if ( $ttbm_hero_stat_count > 5 ) : ?>
							<style>
								.ttbm_smart_theme .ttbm_hero_stats_grid--collapsed .item_icon.ttbm_hero_stat_item--extra { display: none !important; }
							</style>
							<?php endif; ?>
							<div class="ttbm_hero_stats_grid ttbm_hero_stats_grid--collapsed">
								<?php echo $ttbm_hero_stats_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php if ( $ttbm_hero_stat_count > 5 ) : ?>
								<div class="ttbm_hero_stats_more is-visible">
									<button
										type="button"
										class="ttbm_hero_stats_load_more"
										aria-expanded="false"
										data-label-more="<?php esc_attr_e( 'Load more', 'tour-booking-manager' ); ?>"
										data-label-less="<?php esc_attr_e( 'Show less', 'tour-booking-manager' ); ?>"
									><?php esc_html_e( 'Load more', 'tour-booking-manager' ); ?></button>
								</div>
								<?php endif; ?>
							</div>
						</div>
						<?php do_action( 'ttbm_details_particular_area' ); ?>

						<div class="ttbm-smart-overview-anchor" id="ttbm_smart_overview_anchor"></div>

						<div class="ttbm_smart_overview placeholder_area">
							<?php do_action( 'ttbm_description' ); ?>
							<div class="ttbm_smart_inclusions">
								<?php do_action( 'ttbm_include_exclude' ); ?>
								<?php do_action( 'ttbm_smart_activity' ); ?>
							</div>
							<?php do_action( 'ttbm_registration_before', $ttbm_post_id ); ?>
							<?php do_action( 'ttbm_hiphop_place' ); ?>
							<?php do_action( 'ttbm_day_wise_details' ); ?>
							<?php do_action( 'ttbm_faq' ); ?>
							<?php do_action( 'ttbm_review' ); ?>
							<?php do_action( 'ttbm_enquery_popup' ); ?>
						</div>
					</div>

					<aside class="ttbm_content__right placeholder_area" id="ttbm_booking_section" aria-label="<?php esc_attr_e( 'Tour booking', 'tour-booking-manager' ); ?>">
						<div class="ttbm-smart-booking-origin"></div>
						<?php if ( $ttbm_display_reg !== 'off' ) : ?>
							<div class="ttbm-sidebar-booking ttbm_registration_area ttbm_smart_inline_booking"<?php echo $ttbm_auto_date ? ' data-ttbm-auto-date="1"' : ''; ?>>
								<div class="ttbm_smart_booking_card">
									<?php do_action( 'ttbm_smart_registration_controls', $ttbm_post_id ); ?>
									<?php do_action( 'ttbm_smart_registration_panel', $ttbm_post_id ); ?>
								</div>
							</div>
						<?php endif; ?>
						<?php do_action( 'ttbm_why_choose_us' ); ?>
						<?php do_action( 'ttbm_get_a_question' ); ?>
						<?php do_action( 'ttbm_tour_guide' ); ?>
						<?php do_action( 'ttbm_dynamic_sidebar', $ttbm_post_id ); ?>
					</aside>
				</div>
			</div>

			<div class="ttbm_smart_related mT placeholder_area">
				<?php do_action( 'ttbm_related_tour' ); ?>
			</div>
		</div>
	</div>
	<?php do_action( 'ttbm_single_tour_after' ); ?>
</div>
