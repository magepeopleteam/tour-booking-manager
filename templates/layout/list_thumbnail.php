<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id   = $ttbm_post_id ?? get_the_id();
	$thumbnail = TTBM_Global_Function::get_image_url( $ttbm_post_id );
?>
<div class="bg_image_area" data-placeholder>
	<div data-bg-image="<?php echo esc_url( $thumbnail ); ?>" data-href="<?php echo esc_url( get_the_permalink( $ttbm_post_id ) ); ?>"></div>
</div>