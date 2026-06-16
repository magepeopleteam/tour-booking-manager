<?php
	// Template Name: Default Theme
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$class_location = $class_location ?? '';
?>
	<div class="ttbm_default_theme">
		<div class='ttbm_style ttbm_wraper'>
			<div class="ttbm_container">
				<div class="ttbm_details_page">
					<div class="ttbm_content_area">
						<div class="ttbm_content__left">
							<div class="ttbm_hero">
								<?php do_action( 'ttbm_slider' ); ?>
								<div class="ttbm_hero_overlay">
									<div class="ttbm_hero_overlay_inner">
										<?php do_action( 'ttbm_details_title' ); ?>
										<?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>
										<div class="ttbm_hero_stats">
											<?php
												$ttbm_hero_loc = TTBM_Function::get_full_location( $ttbm_post_id );
												if ( $ttbm_hero_loc && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_location', 'on' ) != 'off' ) :
											?>
												<div class="item_icon ttbm_hero_loc" title="<?php esc_attr_e( 'Location', 'tour-booking-manager' ); ?>">
													<i class="mi mi-marker"></i>
													<span><?php echo esc_html( $ttbm_hero_loc ); ?></span>
												</div>
											<?php endif; ?>
											<?php do_action( 'ttbm_short_details' ); ?>
										</div>
										<?php
											$ttbm_hero_display_reg = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_registration', 'on' );
											$ttbm_hero_price       = TTBM_Function::get_tour_start_price( $ttbm_post_id );
											if ( $ttbm_hero_display_reg != 'off' ) :
										?>
											<div class="ttbm_hero_cta">
												<?php if ( $ttbm_hero_price ) : ?>
													<div class="ttbm_hero_price">
														<span class="ttbm_hero_price_label"><?php esc_html_e( 'Prices Start At', 'tour-booking-manager' ); ?></span>
														<span class="ttbm_hero_price_value"><?php echo wp_kses_post( wc_price( $ttbm_hero_price ) ); ?></span>
													</div>
												<?php endif; ?>
												<button type="button" class="ttbm_hero_book_now" data-ttbm-book-now>
													<?php esc_html_e( 'Book Now', 'tour-booking-manager' ); ?>
													<span class="fas fa-chevron-right"></span>
												</button>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<div class="ttbm_booking_section" id="ttbm_booking_section">
								<?php include( TTBM_Function::template_path( 'ticket/registration.php' ) ); ?>
								<?php include( TTBM_Function::template_path( 'ticket/particular_item_area.php' ) ); ?>
							</div>
							<?php do_action( 'ttbm_description' ); ?>
							<?php do_action( 'ttbm_registration_before', $ttbm_post_id ); ?>
							<?php do_action( 'ttbm_hiphop_place' ); ?>
							<?php do_action( 'ttbm_day_wise_details' ); ?>
							<?php do_action( 'ttbm_faq' ); ?>
                            <?php do_action( 'ttbm_review' ); ?>
							<?php do_action('ttbm_enquery_popup'); ?>
						</div>
						<div class="ttbm_content__right">
							<?php do_action( 'ttbm_include_feature' ); ?>
							<?php do_action( 'ttbm_exclude_service' ); ?>
							<?php do_action( 'ttbm_activity' ); ?>
							<?php //do_action( 'ttbm_hotel_list' ); ?>
							<?php do_action( 'ttbm_why_choose_us' ); ?>
							<?php do_action( 'ttbm_get_a_question' ); ?>
							<?php do_action( 'ttbm_tour_guide' ); ?>
							<?php do_action( 'ttbm_dynamic_sidebar', $ttbm_post_id ); ?>
						</div>
					</div>
				</div>
				<div class="mT">
					<?php do_action( 'ttbm_related_tour' ); ?>
				</div>
				
			</div>
		</div>
		<?php do_action( 'ttbm_single_tour_after' ); ?>
		
	</div>
