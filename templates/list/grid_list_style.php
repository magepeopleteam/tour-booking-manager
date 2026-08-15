<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id      = TTBM_Function::post_id_multi_language( $ttbm_post_id );
$thumbnail    = TTBM_Global_Function::get_image_url( $tour_id );
$term_count   = 3;
?>

<div class="ttbm-gc-image-wrap" data-href="<?php echo esc_url( get_the_permalink( $tour_id ) ); ?>" data-placeholder>

	<?php /* Sale ribbon badge */ ?>
	<?php
	$regular_price = TTBM_Function::check_discount_price_exit( $tour_id );
	if ( $regular_price ) : ?>
		<div class="ttbm-gc-badge-sale" data-placeholder><?php esc_html_e( 'ON SALE!', 'tour-booking-manager' ); ?></div>
	<?php endif; ?>

	<?php /* Wishlist / favourite button — TTBM_Wishlist only loads while WooCommerce is active */ ?>
	<?php if ( class_exists( 'TTBM_Wishlist' ) ) : ?>
		<?php $in_wishlist = is_user_logged_in() && TTBM_Wishlist::is_in_wishlist( $tour_id ); ?>
		<button type="button" class="ttbm-gc-wishlist<?php echo $in_wishlist ? ' active' : ''; ?>" data-tour-id="<?php echo esc_attr( $tour_id ); ?>" aria-label="<?php esc_attr_e( 'Add to wishlist', 'tour-booking-manager' ); ?>">
			<span class="mi <?php echo $in_wishlist ? 'mi-wishlist-heart' : 'mi-heart'; ?>"></span>
		</button>
	<?php endif; ?>

	<?php /* Tour thumbnail — background-image set directly inline (not left to JS to apply from
	data-bg-image alone) so the thumbnail renders immediately instead of waiting on a script. */ ?>
	<div class="ttbm-gc-thumb" data-bg-image="<?php echo esc_attr( $thumbnail ); ?>"<?php echo $thumbnail ? ' style="background-image:url(\'' . esc_url( $thumbnail ) . '\');"' : ''; ?>></div>

	<?php /* Duration badge overlaid at bottom of image */ ?>
	<div class="ttbm-gc-duration-badge fdColumn" data-placeholder>
		<?php include( TTBM_Function::template_path( 'layout/list_duration.php' ) ); ?>
		<?php include( TTBM_Function::template_path( 'layout/expire_msg.php' ) ); ?>
	</div>

</div>

<div class="ttbm-gc-body fdColumn">

	<div class="ttbm-lv-title-row" data-placeholder>
		<?php include( TTBM_Function::template_path( 'layout/list_title.php' ) ); ?>
	</div>

	<?php /* Location + duration on one line — same real partials + combined-row
	technique as list/grid_list.php's modern card (the main archive uses that
	template; this one's the "Similar tours"/list-mode card), just under this
	file's own .ttbm-gc- prefix instead of .ttbm-lv- since the wrapper here is
	.ttbm-gc-body, not .ttbm-lv-content-col. */ ?>
	<div class="ttbm-gc-meta-row" data-placeholder>
		<?php include( TTBM_Function::template_path( 'layout/location.php' ) ); ?>
		<?php include( TTBM_Function::template_path( 'layout/list_duration.php' ) ); ?>
	</div>

	<?php /* Activity tag pills — real ttbm_tour_activities taxonomy terms, same
	partial the modern card uses; $hide_gc_tags lets a caller (e.g. a very
	narrow card context) opt back out, same flag layout/related_tour.php
	already sets (now used for real instead of doing nothing). */ ?>
	<?php if ( empty( $hide_gc_tags ) ) : ?>
		<?php include( TTBM_Function::template_path( 'layout/list_tags.php' ) ); ?>
	<?php endif; ?>

	<?php /* Short description */ ?>
	<?php include( TTBM_Function::template_path( 'layout/description_short.php' ) ); ?>

	<?php /* Divider */ ?>
	<hr class="ttbm-gc-divider">

	<?php /* Footer: duration + price + explore */ ?>
	<?php include( TTBM_Function::template_path( 'layout/gc_card_footer.php' ) ); ?>

</div>
