<?php	if ( ! defined( 'ABSPATH' ) ) {		die;	}	$tour_id   = $tour_id ?? get_the_id();	$thumbnail = TTBM_Function::get_image_url( $tour_id );?><div class="bg_image_area" data-placeholder>	<div data-bg-image="<?php echo esc_attr( $thumbnail ); ?>" data-href="<?php echo get_the_permalink( $tour_id ); ?>"></div></div>