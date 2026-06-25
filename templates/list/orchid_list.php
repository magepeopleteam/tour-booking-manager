<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id      = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
$thumbnail    = TTBM_Global_Function::get_image_url( $tour_id );
$regular_price = TTBM_Function::check_discount_price_exit( $tour_id );
?>

<div class="ttbm-orchid-image-wrap" data-href="<?php echo esc_url( get_the_permalink( $tour_id ) ); ?>" data-placeholder>
	<?php if ( $regular_price ) : ?>
		<div class="ttbm-orchid-badge-sale" data-placeholder><?php esc_html_e( 'ON SALE!', 'tour-booking-manager' ); ?></div>
	<?php endif; ?>
	<div class="ttbm-orchid-thumb" data-bg-image="<?php echo esc_attr( $thumbnail ); ?>"></div>
</div>

<div class="ttbm-orchid-body fdColumn">
	<?php include TTBM_Function::template_path( 'layout/list_title.php' ); ?>
	<?php include TTBM_Function::template_path( 'layout/location.php' ); ?>
	<hr class="ttbm-orchid-divider">
	<?php include TTBM_Function::template_path( 'layout/orchid_card_footer.php' ); ?>
</div>
