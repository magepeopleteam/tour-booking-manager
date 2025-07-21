<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Dates')) {
		class TTBM_Settings_Dates {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'date_tab']);
			}
			public function date_tab($tour_id) {
				$tour_label = TTBM_Function::get_name();
				$travel_type = TTBM_Function::travel_type_array();
				$date_format = TTBM_Global_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$date_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_type', 'fixed');
				$start_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date');
				$hidden_start_date = $start_date ? gmdate('Y-m-d', strtotime($start_date)) : '';
				$visible_start_date = $start_date ? date_i18n($date_format, strtotime($start_date)) : '';
				$start_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date_time');
				$end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_date');
				$end_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_time');
				$hidden_end_date = $end_date ? gmdate('Y-m-d', strtotime($end_date)) : '';
				$visible_end_date = $end_date ? date_i18n($date_format, strtotime($end_date)) : '';
				$reg_end = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date');
				$hidden_reg_end_date = $reg_end ? gmdate('Y-m-d', strtotime($reg_end)) : '';
				$visible_reg_end_date = $reg_end ? date_i18n($date_format, strtotime($reg_end)) : '';
				$reg_end_time = TTBM_Global_Function::get_post_info($tour_id, 'reg_end_time');
				//echo $date_type;
				?>
                <div class="tabsItem ttbm_settings_dates" data-tabs="#ttbm_settings_dates">
                    <div class="gptLayout">
                        <div class="gpt_header">
                            <h3><?php esc_html_e('Date Configuration', 'tour-booking-manager'); ?></h3>
                            <p class="info_text"><?php esc_html_e('Tour type and date time can be easily configured here, providing a crucial feature for recurring, fixed, or specific date tours.', 'tour-booking-manager') ?></p>
                        </div>
                        <div class="gpt_content">
                            <div class="groupRadioBox">
                                <input type="hidden" value="<?php echo esc_attr($date_type); ?>" name="ttbm_travel_type"/>
                                <label><?php echo esc_html($tour_label) . ' ' . esc_html__('Type', 'tour-booking-manager'); ?></label>
                                <div class="_dFlex_mT">
									<?php foreach ($travel_type as $key => $value) { ?>
                                        <button class="_mpBtn_mR <?php echo esc_attr($key == $date_type ? 'active' : ''); ?>" type="button" data-group-radio="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></button>
									<?php } ?>
                                </div>
                            </div>
                            <div class="flexEqual">
                                <div class="_fdColumn">
                                    <label><?php esc_html_e('Start Date & Time', 'tour-booking-manager'); ?></label>
                                    <div class="_dFlex_mT_xs">
                                        <label>
                                            <input type="hidden" name="ttbm_travel_start_date" value="<?php echo esc_attr($hidden_start_date); ?>"/>
                                            <input value="<?php echo esc_attr($visible_start_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                                        </label>
                                        <label>
                                            <input type="time" name="ttbm_travel_start_date_time" class="formControl" value="<?php echo esc_attr($start_time); ?>" style="width:100px;"/>
                                        </label>
                                    </div>
                                </div>
                                <div class="_fdColumn">
                                    <label><?php esc_html_e('End Date & Time', 'tour-booking-manager'); ?></label>
                                    <div class="_dFlex_mT_xs">
                                        <label>
                                            <input type="hidden" name="ttbm_travel_end_date" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                                            <input value="<?php echo esc_attr($visible_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                                        </label>
                                        <label>
                                            <input type="time" name="ttbm_travel_end_time" class="formControl" value="<?php echo esc_attr($end_time); ?>" style="width:100px;"/>
                                        </label>
                                    </div>
                                </div>
                                <div class="_fdColumn">
                                    <label><?php esc_html_e('Reg. End Date & Time', 'tour-booking-manager'); ?></label>
                                    <div class="_dFlex_mT_xs">
                                        <label>
                                            <input type="hidden" name="ttbm_travel_reg_end_date" value="<?php echo esc_attr($hidden_reg_end_date); ?>"/>
                                            <input value="<?php echo esc_attr($visible_reg_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                                        </label>
                                        <label>
                                            <input type="time" name="ttbm_travel_end_time" class="formControl" value="<?php echo esc_attr($reg_end_time); ?>" style="width:100px;"/>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function date_item($name, $date='') {


				?>

				<?php
			}
		}
		new TTBM_Settings_Dates();
	}