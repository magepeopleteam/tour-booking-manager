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

	<?php /* Sale badge */ ?>
	<?php
	$regular_price = TTBM_Function::check_discount_price_exit( $tour_id );
	if ( $regular_price ) : ?>
		<div class="ttbm-gc-badge-sale" data-placeholder><?php esc_html_e( 'ON SALE!', 'tour-booking-manager' ); ?></div>
	<?php endif; ?>

	<?php /* Wishlist button */ ?>
	<button type="button" class="ttbm-gc-wishlist" aria-label="<?php esc_attr_e( 'Add to wishlist', 'tour-booking-manager' ); ?>" data-placeholder>
		<span class="mi mi-heart"></span>
	</button>

	<?php /* Thumbnail */ ?>
	<div class="ttbm-lv-thumb" data-bg-image="<?php echo esc_attr( $thumbnail ); ?>"></div>

</div>

<?php /* ── RIGHT: Content column ───────────────────────────── */ ?>
<div class="ttbm-lv-content-col">

	<?php /* Location */ ?>
	<?php include( TTBM_Function::template_path( 'layout/location.php' ) ); ?>

	<?php /* Title */ ?>
	<?php include( TTBM_Function::template_path( 'layout/list_title.php' ) ); ?>

	<?php /* Short description */ ?>
	<?php include( TTBM_Function::template_path( 'layout/description_short.php' ) ); ?>

	<?php /* Feature tags */ ?>
	<div class="ttbm-gc-tags" data-placeholder>
		<?php $term_name = true; include( TTBM_Function::template_path( 'layout/include_feature_list.php' ) ); ?>
	</div>

	<?php /* Divider */ ?>
	<hr class="ttbm-gc-divider">

	<?php /* Footer: price + explore */ ?>
	<?php include( TTBM_Function::template_path( 'layout/gc_card_footer.php' ) ); ?>

</div>
