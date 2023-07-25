<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id   = $ttbm_post_id ?? get_the_id();
	$thumbnail = MP_Global_Function::get_image_url( $ttbm_post_id );
?>
<div class="bg_image_area" data-placeholder>
	<div data-bg-image="<?php echo esc_attr( $thumbnail ); ?>" data-href="<?php echo get_the_permalink( $ttbm_post_id ); ?>"></div>
</div>