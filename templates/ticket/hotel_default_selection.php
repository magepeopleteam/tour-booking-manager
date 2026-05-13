<?php
// FIXED: Removed leading tab before PHP opening tag - 2025-01-21 by Shahnur Alam
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$travel_type = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
	$tour_type   = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
	$all_dates   = $all_dates ?? TTBM_Function::get_date( $tour_id );
	if ( sizeof( $all_dates ) > 0 && $tour_type == 'hotel' ) {
		$ttbm_hotels = TTBM_Function::get_hotel_list( $tour_id );
		if ( sizeof( $ttbm_hotels ) > 0 ) {
			?>
			<div class="ttbm_hotel_area <?php echo esc_attr( $travel_type == 'fixed' ? '' : 'dNone' ); ?>">
				<?php foreach ( $ttbm_hotels as $hotel_id ) {
					// Gather hotel data
					$hotel_title     = get_the_title( $hotel_id );
					$hotel_permalink = get_permalink( $hotel_id );
					$hotel_image     = TTBM_Global_Function::get_image_url( $hotel_id );
					if ( ! $hotel_image ) {
						$hotel_image = TTBM_PLUGIN_URL . '/assets/images/no_image.png';
					}
					$hotel_rating   = TTBM_Global_Function::get_post_info( $hotel_id, 'ttbm_hotel_rating' );
					$hotel_location = TTBM_Global_Function::get_post_info( $hotel_id, 'ttbm_hotel_location' );
					$hotel_distance = TTBM_Global_Function::get_post_info( $hotel_id, 'ttbm_hotel_distance_des' );
					$hotel_excerpt  = get_post_field( 'post_excerpt', $hotel_id );
					if ( ! $hotel_excerpt ) {
						$hotel_excerpt = wp_trim_words( get_post_field( 'post_content', $hotel_id ), 30 );
					}
					$hotel_min_price = TTBM_Function::get_hotel_room_min_price( $hotel_id );
					$hotel_feat_ids  = TTBM_Global_Function::get_post_info( $hotel_id, 'ttbm_hotel_feat_selection' );
					$hotel_feat_ids  = is_array( $hotel_feat_ids ) ? $hotel_feat_ids : array();

					// Location text
					$loc_parts = array();
					if ( $hotel_location ) $loc_parts[] = $hotel_location;
					if ( $hotel_distance ) $loc_parts[] = $hotel_distance;
					$location_text = implode( ' ', $loc_parts );
				?>
				<div class="ttbm_registration_area">
					<?php /* .ttbm_hotel_item is required by JS: closest('.ttbm_hotel_item') */ ?>
					<div class="ttbm_hotel_item">
						<input type="hidden" name="ttbm_id" value="<?php echo esc_attr( $tour_id ); ?>"/>
						<input type="hidden" name="ttbm_hotel_id" value="<?php echo esc_attr( $hotel_id ); ?>"/>

						<!-- Image 1 design card -->
						<div class="ttbm_hdc_card">
							<!-- Left: image -->
							<div class="ttbm_hdc_img">
								<img src="<?php echo esc_url( $hotel_image ); ?>" alt="<?php echo esc_attr( $hotel_title ); ?>">
							</div>

							<!-- Right: content -->
							<div class="ttbm_hdc_body">

								<!-- Row 1: title + rating badge -->
								<div class="ttbm_hdc_header">
									<a href="<?php echo esc_url( $hotel_permalink ); ?>" class="ttbm_hdc_title_link">
										<h3 class="ttbm_hdc_title"><?php echo esc_html( $hotel_title ); ?></h3>
									</a>
									<?php if ( $hotel_rating > 0 ) : ?>
									<span class="ttbm_hdc_rating">
										<?php esc_html_e( 'Rating', 'tour-booking-manager' ); ?> <?php echo esc_html( $hotel_rating ); ?>
									</span>
									<?php endif; ?>
								</div>

								<!-- Row 2: location -->
								<?php if ( $location_text ) : ?>
								<div class="ttbm_hdc_location">
									<span class="ttbm_hdc_pin">&#x1F4CD;</span>
									<span><?php echo esc_html( $location_text ); ?></span>
								</div>
								<?php endif; ?>

								<!-- Row 3: description (2 lines) -->
								<?php if ( $hotel_excerpt ) : ?>
								<p class="ttbm_hdc_desc"><?php echo esc_html( $hotel_excerpt ); ?></p>
								<?php endif; ?>

								<!-- Row 4: feature tag pills -->
								<?php if ( ! empty( $hotel_feat_ids ) ) : ?>
								<div class="ttbm_hdc_tags">
									<?php foreach ( $hotel_feat_ids as $feat_id ) :
										$term = get_term( intval( $feat_id ), 'ttbm_hotel_features_list' );
										if ( $term && ! is_wp_error( $term ) ) :
											$icon = get_term_meta( $term->term_id, 'ttbm_hotel_feature_icon', true );
											$icon = $icon ? $icon : 'fas fa-check';
									?>
										<span class="ttbm_hdc_tag">
											<i class="<?php echo esc_attr( $icon ); ?>"></i>
											<?php echo esc_html( $term->name ); ?>
										</span>
									<?php endif; endforeach; ?>
								</div>
								<?php endif; ?>

								<!-- Divider -->
								<hr class="ttbm_hdc_divider">

								<!-- Row 5: footer -->
								<div class="ttbm_hdc_footer">
									<div class="ttbm_hdc_nights">
										<span><?php esc_html_e( '1 nights, 2 adults', 'tour-booking-manager' ); ?></span>
										<span class="ttbm_hdc_note"><?php esc_html_e( 'Additional charges may apply', 'tour-booking-manager' ); ?></span>
									</div>
									<div class="ttbm_hdc_price_action">
										<?php if ( $hotel_min_price > 0 ) : ?>
										<span class="ttbm_hdc_price">
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											echo wc_price( $hotel_min_price );
											?>
										</span>
										<?php endif; ?>
										<?php /* ttbm_hotel_open_room_list class is required by JS */ ?>
										<button class="ttbm_hdc_btn ttbm_hotel_open_room_list" type="button">
											<?php esc_html_e( 'See availability', 'tour-booking-manager' ); ?>
										</button>
									</div>
								</div>

							</div><!-- .ttbm_hdc_body -->
						</div><!-- .ttbm_hdc_card -->

						<?php /* .ttbm_booking_panel is required by JS to inject room list */ ?>
						<div class="ttbm_booking_panel placeholder_area"></div>
					</div><!-- .ttbm_hotel_item -->
				</div><!-- .ttbm_registration_area -->
				<?php } ?>
			</div>
			<?php
		}
	}
?>