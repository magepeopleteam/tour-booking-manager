<?php
// FIXED: Removed leading tab before PHP opening tag - 2025-01-21 by Shahnur Alam
if (!defined('ABSPATH')) {
	die;
}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
	$all_dates = $all_dates ?? TTBM_Function::get_date($tour_id);
	$travel_type = $travel_type ?? TTBM_Function::get_travel_type($tour_id);
	$tour_type = $tour_type ?? TTBM_Function::get_tour_type($tour_id);
	$template_name = $template_name ?? TTBM_Global_Function::get_post_info($tour_id, 'ttbm_theme_file', 'default.php');
	if (sizeof($all_dates) > 0 && $tour_type == 'general' && $travel_type != 'particular') {
		$date = ''; // Do not default to first date
		$check_ability = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticketing_system', 'regular_ticket');
		if ($date && strtotime($date) !== false) {
            $date = $current_date = gmdate('Y-m-d', strtotime($date));
			$time = TTBM_Function::get_time($tour_id, $date);
			$time = is_array($time) ? $time[0]['time'] : $time;
			$date = $time ? $date . ' ' . $time : $date;
			if (strtotime($date) !== false) {
				$date = $time ? gmdate('Y-m-d H:i', strtotime($date)) : gmdate('Y-m-d', strtotime($date));
			} else {
				$date = '';
				$current_date = '';
			}
		} else {
            $current_date = '';
			$date = '';
        }
		/************/
		$date_format = TTBM_Global_Function::date_picker_format();
		$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
		$hidden_date = ($date && strtotime($date) !== false) ? gmdate('Y-m-d', strtotime($date)) : '';
		$visible_date = ($date && strtotime($date) !== false) ? gmdate($date_format, strtotime($date)) : '';
		?>
        <div class="ttbm_registration_area <?php echo esc_attr($check_ability); ?>">
            <input type="hidden" name="ttbm_id" value="<?php echo esc_attr($tour_id); ?>"/>
			<?php
				if ($travel_type == 'repeated') {
					$slot_check_date = $current_date ? $current_date : current($all_dates);
					$time_slots = TTBM_Function::get_time($tour_id, $slot_check_date);
					$time_slots_enabled = TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no') != 'no';
					$ticketing_system = get_post_meta($tour_id, 'ttbm_ticketing_system', true);
					?>
                    <div class=" ttbm_date_time_select">
                        <div class="ttbm_select_date_area">
                            <?php if ($time_slots_enabled && $ticketing_system != 'regular_ticket'): ?>
                            <div class="ttbm-title" data-placeholder>
								<?php esc_html_e('Make your booking', 'tour-booking-manager'); ?>
                            </div>
							<?php endif; ?>
                            <div class="booking-button">
                                <div class="date-picker">
                                    <div class="date_time_label ttbm-title"><?php echo $time_slots_enabled ? esc_html__('Select Date & Time : ', 'tour-booking-manager') : esc_html__('Select Date  : ', 'tour-booking-manager'); ?></div>
                                    <label class="date-picker-icon">
                                        <i class="far fa-calendar-alt"></i>
                                        <input type="hidden" name="ttbm_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
                                        <input id="ttbm_select_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="formControl mb-0 " placeholder="<?php echo esc_attr($now); ?>" readonly required/>
                                    </label>
                                </div>
								<?php
									$template_name = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_theme_file', 'default.php');
									TTBM_Layout::availability_button($tour_id);
								?>
                            </div>
							<?php if ($time_slots_enabled && $check_ability == 'regular_ticket' && $template_name != 'viator.php') { 
                                $style = $visible_date ? '' : 'display:none;';
                                ?>
                                <div class="flexWrap ttbm_select_time_area" style="<?php echo esc_attr($style); ?>">
									<?php do_action('ttbm_time_select', $tour_id, $current_date ? $current_date : current($all_dates)); ?>
                                </div>
							<?php } ?>
                        </div>
						<?php if ($time_slots_enabled && ($template_name == 'viator.php' || $check_ability == 'availability_section')) { 
                            $style = $visible_date ? '' : 'display:none;';
                            ?>
                            <div class="flexWrap ttbm_select_time_area" style="<?php echo esc_attr($style); ?>">
								<?php do_action('ttbm_time_select', $tour_id, $current_date ? $current_date : current($all_dates)); ?>
                            </div>
						<?php } ?>
                    </div>
					<?php
					//echo '<pre>';print_r($all_dates);echo '</pre>';
					do_action('ttbm_load_date_picker_js', '#ttbm_select_date', $all_dates);
				}
			?>
            <div class="ttbm_booking_panel placeholder_area">
				<?php if ($check_ability == 'regular_ticket' || $travel_type == 'fixed') { ?>
					<?php do_action('ttbm_booking_panel', $tour_id, $date, '', $check_ability); ?>
				<?php } ?>
            </div>
        </div>
	<?php } ?>
