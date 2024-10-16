<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id        = $ttbm_post_id ?? get_the_id();
	$class_location = $class_location ?? '';
	$location       = TTBM_Function::get_full_location( $ttbm_post_id );
	if ( $location && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_location', 'on' ) != 'off' ) {
		?>
		<div class="ttbm_list_info location_name <?php echo esc_attr( $class_location ); ?>" data-placeholder>
			<span class="fas fa-map-marker-alt"></span>
			<?php echo esc_html( $location ); ?>
		</div>
	<?php } ?>