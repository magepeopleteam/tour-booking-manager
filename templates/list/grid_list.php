<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id      = TTBM_Function::post_id_multi_language( $ttbm_post_id );
$thumbnail    = TTBM_Global_Function::get_image_url( $tour_id );
$term_count   = 3;
?>

<?php /* ── LEFT: Image column ─────────────────────────────── */ ?>
<div class="ttbm-lv-image-col" data-href="<?php echo esc_url( get_the_permalink( $tour_id ) ); ?>" data-placeholder>

	<?php
	/* Top-left badge: an admin-flagged "Bestseller"/"Popular" tag takes priority
	   over the automatic sale badge so a tour isn't shown wearing two badges. */
	$top_picks_on  = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_top_picks_deals', 'on' ) !== 'off';
	$top_picks     = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_top_picks_deals', array() );
	$is_bestseller = $top_picks_on && is_array( $top_picks ) && in_array( 'popular', $top_picks, true );
	$regular_price = TTBM_Function::check_discount_price_exit( $tour_id );
	if ( $is_bestseller ) : ?>
		<div class="ttbm-gc-badge-sale ttbm-gc-badge-bestseller" data-placeholder><?php esc_html_e( 'Bestseller', 'tour-booking-manager' ); ?></div>
	<?php elseif ( $regular_price ) : ?>
		<div class="ttbm-gc-badge-sale" data-placeholder><?php esc_html_e( 'ON SALE!', 'tour-booking-manager' ); ?></div>
	<?php endif; ?>

	<?php /* Wishlist button — TTBM_Wishlist only loads while WooCommerce is active */ ?>
	<?php if ( class_exists( 'TTBM_Wishlist' ) ) : ?>
		<?php $in_wishlist = is_user_logged_in() && TTBM_Wishlist::is_in_wishlist( $tour_id ); ?>
		<button type="button" class="ttbm-gc-wishlist<?php echo $in_wishlist ? ' active' : ''; ?>" data-tour-id="<?php echo esc_attr( $tour_id ); ?>" aria-label="<?php esc_attr_e( 'Add to wishlist', 'tour-booking-manager' ); ?>">
			<span class="mi <?php echo $in_wishlist ? 'mi-wishlist-heart' : 'mi-heart'; ?>"></span>
		</button>
	<?php endif; ?>

	<?php /* Thumbnail — background-image set directly inline so it renders immediately instead
	of waiting on JS to apply it from data-bg-image alone. */ ?>
	<div class="ttbm-lv-thumb" data-bg-image="<?php echo esc_attr( $thumbnail ); ?>"<?php echo $thumbnail ? ' style="background-image:url(\'' . esc_url( $thumbnail ) . '\');"' : ''; ?>></div>

	<?php /* Booking-status overlay (Expired! / Fully Booked!) only — duration moved into the content meta row below */ ?>
	<div class="ttbm-gc-duration-badge fdColumn" data-placeholder>
		<?php include( TTBM_Function::template_path( 'layout/expire_msg.php' ) ); ?>
	</div>

</div>

<?php /* ── RIGHT: Content column ───────────────────────────── */ ?>
<div class="ttbm-lv-content-col">

	<?php /* Rating (optional — only renders when an admin has set it) */ ?>
	<?php include( TTBM_Function::template_path( 'layout/list_rating.php' ) ); ?>

	<?php /* Title */ ?>
	<?php include( TTBM_Function::template_path( 'layout/list_title.php' ) ); ?>

	<?php /* Location + duration on one line */ ?>
	<div class="ttbm-lv-meta-row" data-placeholder>
		<?php include( TTBM_Function::template_path( 'layout/location.php' ) ); ?>
		<?php include( TTBM_Function::template_path( 'layout/list_duration.php' ) ); ?>
	</div>

	<?php /* Activity tag pills */ ?>
	<?php include( TTBM_Function::template_path( 'layout/list_tags.php' ) ); ?>

	<?php /* Short description — only visible in list view (hidden via CSS in grid mode); reuses the same partial templates/list/default.php already renders, no duplicate content logic. */ ?>
	<?php include( TTBM_Function::template_path( 'layout/description_short.php' ) ); ?>

	<?php /* Divider */ ?>
	<hr class="ttbm-gc-divider">

	<?php /* Footer: price + view */ ?>
	<?php include( TTBM_Function::template_path( 'layout/gc_card_footer.php' ) ); ?>

</div>
