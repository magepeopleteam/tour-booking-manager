<?php
	if (!defined('ABSPATH')) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$upcoming_date = MP_Global_Function::get_post_info($tour_id, 'ttbm_upcoming_date');
	$tour_type = TTBM_Function::get_tour_type($tour_id);
	if (!$upcoming_date && $tour_type == 'general') { ?>
		<div class="ttbm_list_info _bgWarning" data-placeholder>
			<?php esc_html_e('Expired !', 'tour-booking-manager'); ?>
		</div>
		<?php
	}
	if ($upcoming_date && $tour_type == 'general') {
		$available_seat = TTBM_Function::get_total_available($tour_id);
		if ($available_seat < 1) {
			$any_date_available = TTBM_Function::get_any_date_seat_available($tour_id);
			if ($any_date_available < 1) {
				?>
				<div class="ttbm_list_info _bgWarning" data-placeholder>
					<?php esc_html_e('Fully Booked !', 'tour-booking-manager'); ?>
				</div>
				<?php
			}
		}
	}
?>