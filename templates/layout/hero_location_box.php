<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$ttbm_post_id  = $ttbm_post_id ?? get_the_id();
	$ttbm_hero_loc = TTBM_Function::get_full_location( $ttbm_post_id );
	if ( $ttbm_hero_loc && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_location', 'on' ) != 'off' ) :
		?>
		<div class="item_icon ttbm_hero_loc<?php echo esc_attr( TTBM_Function::hero_stat_item_class() ); ?>" title="<?php esc_attr_e( 'Location', 'tour-booking-manager' ); ?>">
			<i class="mi mi-marker"></i>
			<span><?php echo esc_html( $ttbm_hero_loc ); ?></span>
		</div>
		<?php
	endif;
