<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$ttbm_post_id      = $ttbm_post_id ?? get_the_id();
	$full_address = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_full_location_name' );
	if ( $full_address && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_map', 'on' ) != 'off' ) {
		?>
		<div class="ttbm_default_widget">
			<?php $this->section_title( 'ttbm_string_tour_location', esc_html__( 'Location Map', 'tour-booking-manager' ) ); ?>
			<div class='ttbm_widget_content ttbm_map_area'>
				<iframe
					id="gmap_canvas"
					src="<?php echo esc_url( 'https://maps.google.com/maps?q=' . rawurlencode( $full_address ) . '&t=&z=13&ie=UTF8&iwloc=&output=embed' ); ?>"
					frameborder="0"
					scrolling="no"
					marginheight="0"
					marginwidth="0"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					allowfullscreen
					style="width:100%;height:100%;border:0;display:block;">
				</iframe>
			</div>
		</div>
	<?php } ?>