<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id    = $ttbm_post_id ?? get_the_id();
	$max_people = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_travel_max_people_allow');
	$tour_type  = TTBM_Function::get_tour_type( $ttbm_post_id );
	$count      = $count ?? 0;
	if ( $max_people && $tour_type == 'general' && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_max_people', 'on' ) != 'off' ) {
?>

<div class="item_icon" title="<?php esc_html_e( 'Max People', 'tour-booking-manager' ); ?>">
	<i class="mi mi-people"></i>
	<?php echo esc_html( $max_people ); ?>
</div>

<?php
// FIXED: Removed indentation before PHP tags to prevent whitespace output - 2025-01-21 by Shahnur Alam
$count ++;
}
?>