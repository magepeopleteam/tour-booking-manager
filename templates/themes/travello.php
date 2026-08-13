<?php
	// Template Name: Travello
	/**
	 * A new, fully self-contained single-tour template matching the
	 * reference design in travail/tour-details.html — completely isolated
	 * from default.php/smart.php/viator.php (no shared markup, no shared
	 * hook handlers edited). Every content section below is the SAME real,
	 * data-backed hook default.php already uses (gallery, description,
	 * itinerary, includes, reviews, FAQ, related tours, and — most
	 * importantly — the actual interactive ticket/date/price-calculation
	 * booking widget, included completely unmodified). Only the assembly,
	 * the tour-header/tabs/organizer-card markup, and the CSS are new.
	 *
	 * @package TourBookingManager
	 */

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	$ttbm_post_id           = $ttbm_post_id ?? get_the_id();
	$tour_id                = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
	$ttbm_booking_tour_type = TTBM_Function::get_tour_type( $tour_id );

	// --- Badges (real data: admin "Bestseller" flag + up to 2 real activity terms) ---
	$ttbm_travello_top_picks_on  = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_top_picks_deals', 'on' ) !== 'off';
	$ttbm_travello_top_picks     = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_top_picks_deals', array() );
	$ttbm_travello_is_bestseller = $ttbm_travello_top_picks_on && is_array( $ttbm_travello_top_picks ) && in_array( 'popular', $ttbm_travello_top_picks, true );
	$ttbm_travello_activities    = get_the_terms( $tour_id, 'ttbm_tour_activities' );
	$ttbm_travello_activities    = is_array( $ttbm_travello_activities ) ? array_slice( $ttbm_travello_activities, 0, 2 ) : array();

	// --- Meta row (real data — same source functions the *_box.php hero-stat partials use, just laid out as one compact inline row instead of a stat grid) ---
	$ttbm_travello_location = TTBM_Function::get_full_location( $tour_id );
	if ( ! $ttbm_travello_location ) {
		/* get_full_location() only reads the short 'ttbm_location_name' field,
		   which several tours on this install don't have set (they only have
		   'ttbm_full_location_name' — a separate field populated by a different
		   importer). Falling back to it here, scoped to just this template's
		   own meta row, rather than changing the shared function every other
		   template/partial also calls. */
		$ttbm_travello_location = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_full_location_name' );
	}
	$ttbm_travello_day        = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_duration' );
	$ttbm_travello_night      = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_duration_night' );
	$ttbm_travello_dur_type   = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_duration_type', 'day' );
	$ttbm_travello_duration_label = '';
	if ( ( $ttbm_travello_day || $ttbm_travello_night ) && $ttbm_booking_tour_type === 'general' && TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_duration', 'on' ) !== 'off' ) {
		if ( $ttbm_travello_day ) {
			$ttbm_travello_duration_label .= (int) $ttbm_travello_day . ' ';
			if ( 'day' === $ttbm_travello_dur_type ) {
				$ttbm_travello_duration_label .= $ttbm_travello_day > 1 ? __( 'Days', 'tour-booking-manager' ) : __( 'Day', 'tour-booking-manager' );
			} elseif ( 'min' === $ttbm_travello_dur_type ) {
				$ttbm_travello_duration_label .= $ttbm_travello_day > 1 ? __( 'Minutes', 'tour-booking-manager' ) : __( 'Minute', 'tour-booking-manager' );
			} else {
				$ttbm_travello_duration_label .= $ttbm_travello_day > 1 ? __( 'Hours', 'tour-booking-manager' ) : __( 'Hour', 'tour-booking-manager' );
			}
		}
		if ( TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_duration_night', 'off' ) !== 'off' && $ttbm_travello_night ) {
			$ttbm_travello_duration_label .= ' / ' . (int) $ttbm_travello_night . ' ' . ( $ttbm_travello_night > 1 ? __( 'Nights', 'tour-booking-manager' ) : __( 'Night', 'tour-booking-manager' ) );
		}
	}
	$ttbm_travello_max_people = $ttbm_booking_tour_type === 'general' && TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_max_people', 'on' ) !== 'off'
		? TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_max_people_allow' )
		: '';
	$ttbm_travello_language_raw = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_language' );
	$ttbm_travello_languages    = is_array( $ttbm_travello_language_raw ) ? array_filter( $ttbm_travello_language_raw ) : ( $ttbm_travello_language_raw ? array( $ttbm_travello_language_raw ) : array() );

	// --- Price (real, same helper the archive cards + hero price use) ---
	$ttbm_travello_start_price = TTBM_Function::get_tour_start_price( $tour_id );

	// --- Organizer mini-card: real term name only, no fabricated stats; simply omitted if none assigned ---
	$ttbm_travello_organizer_terms = get_the_terms( $tour_id, 'ttbm_tour_org' );
	$ttbm_travello_organizer_name  = ( is_array( $ttbm_travello_organizer_terms ) && ! empty( $ttbm_travello_organizer_terms ) ) ? $ttbm_travello_organizer_terms[0]->name : '';

	// --- Wishlist state (reuses the plugin's own real toggle button + AJAX handler) ---
	$ttbm_travello_in_wishlist = class_exists( 'TTBM_Wishlist' ) && is_user_logged_in() && TTBM_Wishlist::is_in_wishlist( $tour_id );
?>
<div class="ttbm_style ttbm_travello_theme placeholderLoader">
	<div class="ttbm-travello-container">

		<div class="ttbm-travello-gallery placeholder_area" id="ttbm_travello_gallery">
			<?php do_action( 'ttbm_slider' ); ?>
		</div>

		<div class="ttbm-travello-layout">

			<div class="ttbm-travello-main">

				<div class="ttbm-travello-header">
					<?php if ( $ttbm_travello_is_bestseller || ! empty( $ttbm_travello_activities ) ) : ?>
						<div class="ttbm-travello-badges">
							<?php if ( $ttbm_travello_is_bestseller ) : ?>
								<span class="ttbm-travello-badge"><?php esc_html_e( 'Bestseller', 'tour-booking-manager' ); ?></span>
							<?php endif; ?>
							<?php foreach ( $ttbm_travello_activities as $ttbm_travello_activity_term ) : ?>
								<span class="ttbm-travello-badge ttbm-travello-badge--outline"><?php echo esc_html( $ttbm_travello_activity_term->name ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h1 class="ttbm-travello-title"><?php echo esc_html( get_the_title( $tour_id ) ); ?></h1>

					<?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>

					<?php if ( $ttbm_travello_location || $ttbm_travello_duration_label || $ttbm_travello_max_people || ! empty( $ttbm_travello_languages ) ) : ?>
						<div class="ttbm-travello-meta-row">
							<?php if ( $ttbm_travello_location ) : ?>
								<span class="ttbm-travello-meta-item"><span class="mi mi-marker" aria-hidden="true"></span><strong><?php echo esc_html( $ttbm_travello_location ); ?></strong></span>
							<?php endif; ?>
							<?php if ( $ttbm_travello_duration_label ) : ?>
								<span class="ttbm-travello-meta-item"><span class="mi mi-clock-three" aria-hidden="true"></span><strong><?php echo esc_html( $ttbm_travello_duration_label ); ?></strong></span>
							<?php endif; ?>
							<?php if ( $ttbm_travello_max_people ) : ?>
								<span class="ttbm-travello-meta-item"><span class="mi mi-people" aria-hidden="true"></span><?php esc_html_e( 'Max', 'tour-booking-manager' ); ?> <strong><?php echo esc_html( $ttbm_travello_max_people ); ?></strong></span>
							<?php endif; ?>
							<?php if ( ! empty( $ttbm_travello_languages ) ) : ?>
								<span class="ttbm-travello-meta-item"><span class="mi mi-language" aria-hidden="true"></span><strong><?php echo esc_html( implode( ', ', $ttbm_travello_languages ) ); ?></strong></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<div class="ttbm-travello-actions">
						<button type="button" class="ttbm-gc-wishlist ttbm-travello-action-btn<?php echo $ttbm_travello_in_wishlist ? ' active' : ''; ?>" data-tour-id="<?php echo esc_attr( $tour_id ); ?>">
							<span class="mi <?php echo $ttbm_travello_in_wishlist ? 'mi-wishlist-heart' : 'mi-heart'; ?>" aria-hidden="true"></span>
							<span class="ttbm-travello-action-label"><?php echo $ttbm_travello_in_wishlist ? esc_html__( 'Saved', 'tour-booking-manager' ) : esc_html__( 'Save to wishlist', 'tour-booking-manager' ); ?></span>
						</button>
						<button type="button" class="ttbm-travello-action-btn" id="ttbm-travello-share-btn" data-share-title="<?php echo esc_attr( get_the_title( $tour_id ) ); ?>">
							<span class="mi mi-share" aria-hidden="true"></span>
							<span class="ttbm-travello-action-label"><?php esc_html_e( 'Share', 'tour-booking-manager' ); ?></span>
						</button>
					</div>
				</div>

				<nav class="ttbm-travello-tabs" id="ttbm-travello-tabs" aria-label="<?php esc_attr_e( 'Tour details sections', 'tour-booking-manager' ); ?>">
					<button type="button" class="ttbm-travello-tab is-active" data-target="ttbm-travello-overview"><?php esc_html_e( 'Overview', 'tour-booking-manager' ); ?></button>
					<button type="button" class="ttbm-travello-tab" data-target="ttbm-travello-itinerary"><?php esc_html_e( 'Itinerary', 'tour-booking-manager' ); ?></button>
					<button type="button" class="ttbm-travello-tab" data-target="ttbm-travello-includes"><?php esc_html_e( "What's included", 'tour-booking-manager' ); ?></button>
					<button type="button" class="ttbm-travello-tab" data-target="ttbm-travello-reviews"><?php esc_html_e( 'Reviews', 'tour-booking-manager' ); ?></button>
					<button type="button" class="ttbm-travello-tab" data-target="ttbm-travello-location"><?php esc_html_e( 'Location', 'tour-booking-manager' ); ?></button>
					<button type="button" class="ttbm-travello-tab" data-target="ttbm-travello-faq"><?php esc_html_e( 'FAQ', 'tour-booking-manager' ); ?></button>
				</nav>

				<div id="ttbm-travello-overview" class="ttbm-travello-section placeholder_area">
					<?php do_action( 'ttbm_description' ); ?>
				</div>

				<div id="ttbm-travello-itinerary" class="ttbm-travello-section placeholder_area">
					<?php do_action( 'ttbm_day_wise_details' ); ?>
				</div>

				<div id="ttbm-travello-includes" class="ttbm-travello-section placeholder_area">
					<?php do_action( 'ttbm_include_exclude' ); ?>
					<?php do_action( 'ttbm_activity' ); ?>
				</div>

				<div id="ttbm-travello-reviews" class="ttbm-travello-section placeholder_area">
					<?php do_action( 'ttbm_review' ); ?>
				</div>

				<div id="ttbm-travello-location" class="ttbm-travello-section placeholder_area">
					<?php
					/* layout/location_map.php already prints its own "Location Map"
					   heading before the iframe, so no separate <h2> is added here —
					   doing so would just duplicate it. */
					do_action( 'ttbm_location_map', $tour_id );
					?>
				</div>

				<div id="ttbm-travello-faq" class="ttbm-travello-section placeholder_area">
					<?php do_action( 'ttbm_faq' ); ?>
				</div>

				<?php
				/* ttbm_hiphop_place isn't part of the reference design (no "Places
				   You'll See" section there), and firing it also unconditionally
				   fires the separate ttbm_hiphop_place_map hook (Pro's own map
				   widget) even when a tour has no hiphop places assigned — which
				   rendered as a second, confusing "Location"-looking block right
				   under the real one. Left out entirely rather than fired for a
				   section the reference doesn't have.
				*/
				do_action( 'ttbm_registration_before', $ttbm_post_id );
				do_action( 'ttbm_enquery_popup' );
				?>
			</div><!-- .ttbm-travello-main -->

			<aside class="ttbm-travello-sidebar">
				<div class="ttbm-travello-booking-card placeholder_area" id="ttbm_booking_section">
					<?php include( TTBM_Function::template_path( 'ticket/registration.php' ) ); ?>
					<?php include( TTBM_Function::template_path( 'ticket/particular_item_area.php' ) ); ?>

					<?php if ( $ttbm_travello_organizer_name ) : ?>
						<div class="ttbm-travello-organizer">
							<span class="ttbm-travello-organizer-avatar mi mi-user" aria-hidden="true"></span>
							<span class="ttbm-travello-organizer-name"><?php echo esc_html( $ttbm_travello_organizer_name ); ?></span>
						</div>
					<?php endif; ?>
				</div>
				<?php /* why_choose_us / get_a_question / tour_guide / dynamic_sidebar deliberately left out — the reference's sidebar is just the booking card (operator mini-card included as its last element), nothing else. */ ?>
			</aside>

		</div><!-- .ttbm-travello-layout -->

		<div class="ttbm-travello-similar placeholder_area">
			<?php do_action( 'ttbm_related_tour' ); ?>
		</div>

	</div><!-- .ttbm-travello-container -->

	<?php if ( $ttbm_travello_start_price ) : ?>
		<div class="ttbm-travello-mobile-bar">
			<div class="ttbm-travello-mobile-bar-price-block">
				<p class="ttbm-travello-mobile-bar-label"><?php esc_html_e( 'From', 'tour-booking-manager' ); ?></p>
				<p class="ttbm-travello-mobile-bar-price">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wc_price( $ttbm_travello_start_price );
					?>
				</p>
			</div>
			<a href="#ttbm_booking_section" class="ttbm-travello-mobile-bar-cta"><?php esc_html_e( 'Reserve now', 'tour-booking-manager' ); ?></a>
		</div>
	<?php endif; ?>
</div><!-- .ttbm_travello_theme -->
<?php do_action( 'ttbm_single_tour_after' ); ?>
