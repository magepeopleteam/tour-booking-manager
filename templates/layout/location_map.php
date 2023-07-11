<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$ttbm_post_id      = $ttbm_post_id ?? get_the_id();
	$full_address = MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_full_location_name' );
	if ( $full_address && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_map', 'on' ) != 'off' ) {
		?>
		<div class="ttbm_default_widget">
			<?php $this->section_title( 'ttbm_string_tour_location', esc_html__( 'Location Map', 'tour-booking-manager' ) ); ?>
			<div class='ttbm_widget_content ttbm_map_area'>
				<iframe id="gmap_canvas" src="https://maps.google.com/maps?q=<?php echo esc_html( $full_address ); ?>&t=&z=12&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
			</div>
		</div>
	<?php } ?>