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

	<?php /* Wishlist / favourite button */ ?>
	<?php $in_wishlist = is_user_logged_in() && TTBM_Wishlist::is_in_wishlist( $tour_id ); ?>
	<button type="button" class="ttbm-gc-wishlist<?php echo $in_wishlist ? ' active' : ''; ?>" data-tour-id="<?php echo esc_attr( $tour_id ); ?>" aria-label="<?php esc_attr_e( 'Add to wishlist', 'tour-booking-manager' ); ?>">
		<span class="mi <?php echo $in_wishlist ? 'mi-wishlist-heart' : 'mi-heart'; ?>"></span>
	</button>

	<?php /* Tour thumbnail */ ?>
	<div class="ttbm-gc-thumb" data-bg-image="<?php echo esc_attr( $thumbnail ); ?>"></div>

	<?php /* Duration badge overlaid at bottom of image */ ?>
	<div class="ttbm-gc-duration-badge fdColumn" data-placeholder>
		<?php include( TTBM_Function::template_path( 'layout/list_duration.php' ) ); ?>
		<?php include( TTBM_Function::template_path( 'layout/expire_msg.php' ) ); ?>
	</div>

</div>

<div class="ttbm-gc-body fdColumn">

	<?php /* Location */ ?>
	<?php include( TTBM_Function::template_path( 'layout/location.php' ) ); ?>

	<?php /* Title */ ?>
	<?php include( TTBM_Function::template_path( 'layout/list_title.php' ) ); ?>

	<?php /* Short description */ ?>
	<?php include( TTBM_Function::template_path( 'layout/description_short.php' ) ); ?>

	<?php /* Feature tags (included services) — set $term_name=true to show labels */ ?>
	<div class="ttbm-gc-tags" >
		<?php $term_name = true; include( TTBM_Function::template_path( 'layout/include_feature_list.php' ) ); ?>
	</div>

	<?php /* Divider */ ?>
	<hr class="ttbm-gc-divider">

	<?php /* Footer: duration + price + explore */ ?>
	<?php include( TTBM_Function::template_path( 'layout/gc_card_footer.php' ) ); ?>

</div>
