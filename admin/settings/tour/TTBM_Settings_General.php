<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_General')) {
		class TTBM_Settings_General {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'general_settings']);
			}
			public function general_settings($tour_id) {
				?>
                <div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_general_info">
                    <h2><?php esc_html_e('General Information Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('general_settings_description'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-tools"></i><?php esc_html_e('Genearl Information', 'tour-booking-manager'); ?></h4>
                        </div>
                        <div class="dFlex">
                            <div class="col-left">
								<?php $this->tour_duration($tour_id); ?>
								<?php $this->starting_price($tour_id); ?>
								<?php $this->age_range($tour_id); ?>
								<?php $this->starting_place($tour_id); ?>
                            </div>
                            <div class="col-right">
								<?php $this->stay_night($tour_id); ?>
								<?php $this->max_people($tour_id); ?>
								<?php $this->tour_language($tour_id); ?>
								<?php $this->short_description_toggle($tour_id); ?>
                            </div>
                        </div>
						<?php $this->short_description($tour_id); ?>
                    </section>
                </div>
				<?php
			}
			public function stay_night($tour_id) {
				$display_name = 'ttbm_display_duration_night';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = ($display == 'off') ? '' : 'checked';
				$active = ($display == 'off') ? '' : 'mActive';
				$placeholder = '';
				?>
                <div>
                    <div class="label">
                        <div class="label-inner">
                            <p><?php esc_html_e('Stay Night', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Turn on/off stay night settings.', 'tour-booking-manager'); ?></span></i></p>
                        </div>
                        <div class="_dFlex_alignCenter_justfyBetween">
							<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                            <input type="number" data-collapse="#<?php echo esc_attr($display_name); ?>" min="0" class="ms-2 <?php echo esc_attr($active); ?>" name="ttbm_travel_duration_night" value="<?php echo esc_attr(TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_night')); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function tour_duration($tour_id) {
				$value_name = 'ttbm_travel_duration';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$duration_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_type', 'day');
				$placeholder = esc_html__('Ex: 3', 'tour-booking-manager');
				?>
                <label class="label">
                    <p><?php esc_html_e('Duration', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('duration'); ?></span></i></p>
                    <div class="dFlex">
                        <input style="margin-right: 10px;" class="small" min="0.1" step="0.1" type="number" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                        <select class="rounded" name="ttbm_travel_duration_type">
                            <option value="day" <?php echo esc_attr($duration_type == 'day' ? 'selected' : ''); ?>><?php esc_html_e('Days', 'tour-booking-manager'); ?></option>
                            <option value="hour" <?php echo esc_attr($duration_type == 'hour' ? 'selected' : ''); ?>><?php esc_html_e('Hours', 'tour-booking-manager'); ?></option>
                            <option value="min" <?php echo esc_attr($duration_type == 'min' ? 'selected' : ''); ?>><?php esc_html_e('Minutes', 'tour-booking-manager'); ?> </option>
                        </select>
                    </div>
                </label>
				<?php
			}
			public function max_people($tour_id) {
				$max_people_status_field_name = 'ttbm_display_max_people';
				$max_people_field_status = TTBM_Global_Function::get_post_info($tour_id, $max_people_status_field_name, 'on');
				$max_people_field_name = 'ttbm_travel_max_people_allow';
				$max_people_field_value = TTBM_Global_Function::get_post_info($tour_id, $max_people_field_name);
				$max_people_placeholder = esc_html__('50', 'tour-booking-manager');
				$max_people_status_checked = ($max_people_field_status == 'off') ? '' : 'checked';
				$max_people_status_active = ($max_people_field_status == 'off') ? '' : 'mActive';
				?>
                <div class="label">
                    <div class="label-inner">
                        <p><?php esc_html_e('Max People', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('max_people'); ?></span></i></p>
                    </div>
                    <div class="_dFlex_alignCenter_justifyBetween">
						<?php TTBM_Custom_Layout::switch_button($max_people_status_field_name, $max_people_status_checked); ?>
                        <input type="number" min="0" data-collapse="#<?php echo esc_attr($max_people_status_field_name); ?>" class="ms-2 rounded <?php echo esc_attr($max_people_status_active); ?>" name="<?php echo esc_attr($max_people_field_name); ?>" value="<?php echo esc_attr($max_people_field_value); ?>" placeholder="<?php echo esc_attr($max_people_placeholder); ?>"/>
                    </div>
                </div>
				<?php
			}
			public function starting_price($tour_id) {
				$display_name = 'ttbm_display_price_start';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_travel_start_price';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Type Start Price', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				?>
                <div class="label">
                    <div class="label-inner">
                        <p><?php esc_html_e('Start Price', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('start_price'); ?></span></i></p>
                    </div>
                    <div class="_dFlex_alignCenter_justifyBetween">
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <input type="number" min="0" data-collapse="#<?php echo esc_attr($display_name); ?>" class="ms-2 rounded <?php echo esc_attr($active); ?>" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                    </div>
                </div>
				<?php
			}
			public function starting_place($tour_id) {
				$status_field_name = 'ttbm_display_start_location';
				$status = TTBM_Global_Function::get_post_info($tour_id, $status_field_name, 'on');
				$location_field_name = 'ttbm_travel_start_place';
				$location_field_value = TTBM_Global_Function::get_post_info($tour_id, $location_field_name);
				$location_placeholder = esc_html__('Type Start Place...', 'tour-booking-manager');
				$status_checked = $status == 'off' ? '' : 'checked';
				$status_active = $status == 'off' ? '' : 'mActive';
				?>
                <div class="label">
                    <div class="label-inner">
                        <p><?php esc_html_e('Start Place', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('start_place'); ?></span></i></p>
                    </div>
                    <div class="_dFlex_alignCenter_justifyBetween">
						<?php TTBM_Custom_Layout::switch_button($status_field_name, $status_checked); ?>
                        <input type="text" data-collapse="#<?php echo esc_attr($status_field_name); ?>" class="ms-2 rounded <?php echo esc_attr($status_active); ?>" name="<?php echo esc_attr($location_field_name); ?>" value="<?php echo esc_attr($location_field_value); ?>" placeholder="<?php echo esc_attr($location_placeholder); ?>"/>
                    </div>
                </div>
				<?php
			}
			public function age_range($tour_id) {
				$display_name = 'ttbm_display_min_age';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_travel_min_age';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Ex: 5 - 50 Years', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				?>
                <div>
                    <div class="label">
                        <div class="label-inner">
                            <p><?php esc_html_e('Age Range', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('age_range'); ?></span></i></p>
                        </div>
                        <div class="_dFlex_alignCenter_justifyBetween">
							<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                            <input type="text" data-collapse="#<?php echo esc_attr($display_name); ?>" class="ms-2 rounded <?php echo esc_attr($active); ?>" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function tour_language($tour_id) {
				$display_name = 'ttbm_travel_language_status';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$language = 'ttbm_travel_language';
				$language = TTBM_Global_Function::get_post_info($tour_id, $language);
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				$language_lists = TTBM_Global_Function::get_languages();
				?>
                <div>
                    <div class="label">
                        <div class="label-inner">
                            <p><?php esc_html_e('Tour Language', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Easily select your preferred language to enhance your travel experience.', 'tour-booking-manager'); ?></span></i></p>
                        </div>
                        <div class="_dFlex_alignCenter_justifyBetween">
							<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                            <select class="rounded ms-2 <?php echo esc_attr($active); ?>" name="ttbm_travel_language" data-collapse="#<?php echo esc_attr($display_name); ?>">
								<?php foreach ($language_lists as $key => $value): ?>
                                    <option value="<?php echo esc_html($key); ?>" <?php echo esc_attr($key == $language ? 'selected' : ''); ?>><?php echo esc_html($value); ?></option>
								<?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function short_description_toggle($tour_id) {
				$display_name = 'ttbm_display_description';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <div class="label">
                    <div class="label-inner">
                        <p><?php esc_html_e('Short Description Enable/Disable', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('short_des'); ?></span></i></p>
                    </div>
					<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                </div>
				<?php
			}
			public function short_description($tour_id) {
				$display_name = 'ttbm_display_description';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$value_name = 'ttbm_short_description';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Please Type Short Description...', 'tour-booking-manager');
				?>
                <div class="<?php echo esc_attr($active); ?>" data-collapse="#<?php echo esc_attr($display_name); ?>">
                    <div class="label">
                        <div class="label-inner">
                            <p><?php esc_html_e('Short Description', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('short_des'); ?></span></i></p>
                        </div>
                    </div>
                    <textarea class="ms-2 rounded" cols="50" rows="5" name="<?php echo esc_attr($value_name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_attr($value); ?></textarea>
                </div>
				<?php
			}
		}
		new TTBM_Settings_General();
	}