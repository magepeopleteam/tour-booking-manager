<?php
	// Template Name: Default Theme
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$class_location = $class_location ?? '';
	$ttbm_booking_tour_type = TTBM_Function::get_tour_type( $tour_id );
?>
	<div class="ttbm_default_theme">
		<div class='ttbm_style ttbm_wraper placeholderLoader ttbm_details_page_loader'>
			<div class="ttbm_container">
				<div class="ttbm_details_page">
					<div class="ttbm_content_area">
						<div class="ttbm_content__left">
							<div class="ttbm_hero placeholder_area">
								<?php
									$ttbm_hero_display_reg = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_registration', 'on' );
									$ttbm_hero_price       = TTBM_Function::get_tour_start_price( $tour_id );
									$ttbm_hero_next_date   = TTBM_Function::get_next_tour_date_display( $tour_id );
									$ttbm_hero_overlay_cb  = function () use ( $ttbm_post_id, $tour_id, $ttbm_hero_display_reg, $ttbm_hero_price, $ttbm_hero_next_date ) {
										?>
										<div class="ttbm_hero_overlay">
											<div class="ttbm_hero_overlay_inner">
												<div class="ttbm_hero_overlay_head">
													<?php do_action( 'ttbm_details_title' ); ?>
													<?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>
												</div>
												<?php if ( $ttbm_hero_display_reg != 'off' ) : ?>
													<div class="ttbm_hero_cta">
														<div class="ttbm_hero_cta_meta">
															<?php if ( $ttbm_hero_price ) : ?>
																<div class="ttbm_hero_price ttbm_hero_stat">
																	<span class="ttbm_hero_stat_icon" aria-hidden="true"><i class="mi mi-coins"></i></span>
																	<span class="ttbm_hero_stat_body">
																		<span class="ttbm_hero_price_label"><?php esc_html_e( 'Prices Start At', 'tour-booking-manager' ); ?></span>
																		<span class="ttbm_hero_price_value">
																			<?php
																			$start_price                 = $ttbm_hero_price;
																			$regular_price               = TTBM_Function::get_tour_start_regular_price( $tour_id );
																			$ttbm_force_hero_price       = true;
																			$wrapper_class               = 'ttbm_hero_price_values';
																			$original_class              = 'ttbm_hero_price_regular ttbm_regular_price strikeLine';
																			$current_class               = 'ttbm_hero_price_sale';
																			include TTBM_Function::template_path( 'layout/start_price_display.php' );
																			unset( $ttbm_force_hero_price );
																			?>
																		</span>
																	</span>
																</div>
															<?php endif; ?>
															<?php if ( $ttbm_hero_next_date ) : ?>
																<div class="ttbm_hero_date ttbm_hero_stat">
																	<span class="ttbm_hero_stat_icon" aria-hidden="true"><i class="mi mi-calendar-check"></i></span>
																	<span class="ttbm_hero_stat_body">
																		<span class="ttbm_hero_date_label"><?php echo esc_html( TTBM_Function::get_next_tour_date_label( $tour_id ) ); ?></span>
																		<span class="ttbm_hero_date_value"><?php echo esc_html( $ttbm_hero_next_date ); ?></span>
																	</span>
																</div>
															<?php endif; ?>
														</div>
														<button type="button" class="ttbm_hero_book_now" data-ttbm-book-now>
															<span class="ttbm_hero_book_now_text"><?php esc_html_e( 'Book Now', 'tour-booking-manager' ); ?></span>
														</button>
													</div>
												<?php endif; ?>
											</div>
										</div>
										<?php
									};
									add_action( 'ttbm_slider_all_item_overlay', $ttbm_hero_overlay_cb );
									do_action( 'ttbm_slider' );
									remove_action( 'ttbm_slider_all_item_overlay', $ttbm_hero_overlay_cb );
								?>
							</div>
							<?php if ( $ttbm_booking_tour_type !== 'hotel' ) : ?>
							<div class="ttbm_hero_stats placeholder_area">
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
									$ttbm_hero_stats_html  = ob_get_clean();
									$ttbm_hero_stat_count  = TTBM_Function::get_hero_stat_render_count();
									TTBM_Function::disable_hero_stat_limit();
								?>
								<?php if ( $ttbm_hero_stat_count > 5 ) : ?>
								<style>
									.ttbm_default_theme .ttbm_hero_stats_grid--collapsed .item_icon.ttbm_hero_stat_item--extra { display: none !important; }
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
							<?php endif; ?>
							<?php
							$ttbm_booking_section_cls = 'ttbm_booking_section placeholder_area';
							if ( $ttbm_booking_tour_type === 'hotel' ) {
								$ttbm_booking_section_cls .= ' ttbm_booking_section--hotel';
							}
							?>
							<div class="<?php echo esc_attr( $ttbm_booking_section_cls ); ?>" id="ttbm_booking_section">
								<?php if ( $ttbm_booking_tour_type !== 'hotel' ) : ?>
									<h3 class="ttbm-ticket-section-title"><?php esc_html_e( 'Choose the Ticket That Fits Your Journey', 'tour-booking-manager' ); ?></h3>
								<?php endif; ?>
								<?php include( TTBM_Function::template_path( 'ticket/registration.php' ) ); ?>
								<?php include( TTBM_Function::template_path( 'ticket/particular_item_area.php' ) ); ?>
							</div>
							<div class="ttbm_description_area placeholder_area">
								<?php do_action( 'ttbm_description' ); ?>
							</div>
							<div class="ttbm_inclusions_grid placeholder_area">
								<?php do_action( 'ttbm_include_exclude' ); ?>
								<?php do_action( 'ttbm_activity' ); ?>
							</div>
							<div class="ttbm_registration_before_area placeholder_area">
								<?php do_action( 'ttbm_registration_before', $ttbm_post_id ); ?>
							</div>
							<div class="ttbm_hiphop_area placeholder_area">
								<?php do_action( 'ttbm_hiphop_place' ); ?>
							</div>
							<div class="ttbm_day_wise_area placeholder_area">
								<?php do_action( 'ttbm_day_wise_details' ); ?>
							</div>
							<div class="ttbm_faq_area placeholder_area">
								<?php do_action( 'ttbm_faq' ); ?>
							</div>
							<div class="ttbm_review_area placeholder_area">
								<?php do_action( 'ttbm_review' ); ?>
							</div>
                            <?php do_action('ttbm_enquery_popup'); ?>
						</div>
						<div class="ttbm_content__right placeholder_area">
							<?php //do_action( 'ttbm_hotel_list' ); ?>
							<?php do_action( 'ttbm_sidebar_cta' ); ?>
							<?php do_action( 'ttbm_why_choose_us' ); ?>
							<?php do_action( 'ttbm_get_a_question' ); ?>
							<?php do_action( 'ttbm_tour_guide' ); ?>
							<?php do_action( 'ttbm_dynamic_sidebar', $ttbm_post_id ); ?>
						</div>
					</div>
				</div>
				<div class="mT placeholder_area">
					<?php do_action( 'ttbm_related_tour' ); ?>
				</div>
				
			</div>
		</div>
		<?php do_action( 'ttbm_single_tour_after' ); ?>
		
	</div>
