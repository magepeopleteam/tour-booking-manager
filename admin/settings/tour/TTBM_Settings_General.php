<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists('TTBM_Settings_General') ) {
		class TTBM_Settings_General {
			public function __construct() {
				add_action('add_ttbm_settings_tab_content', [$this, 'general_settings']);
				add_action('ttbm_settings_save', [$this, 'save_general_settings']);
			}
			public function general_settings($tour_id) {
				?>
				<div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_general_info">
					<h2><?php esc_html_e('General Information Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('general_settings_description'); ?></p>
					<section class="bg-light">
						<label class="label">
							<div class="label-inner">
								<p><?php TTBM_Settings::des_p('tour_general_settings'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('tour_settings_des'); ?></span>
							</div>
						</label>
					</section>
					<div class="dFlex">
						<div class="col-left">
							<?php $this->tour_duration($tour_id); ?>
							<?php $this->starting_price($tour_id); ?>
							<?php $this->age_range($tour_id); ?>
						</div>
						<div class="col-right">
							<?php $this->stay_night($tour_id); ?>
							<?php $this->max_people($tour_id); ?>
							<?php $this->tour_language($tour_id); ?>
						</div>
					</div>
					<?php
						$this->starting_place($tour_id);
						$this->short_description($tour_id);
						
					?>
				</div>
			<?php
			}
			public function stay_night($tour_id) {
				$display_name = 'ttbm_display_duration_night';
				$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = ($display == 'off') ? '' : 'checked';
				$active = ($display == 'off') ? '' : 'mActive';
				$placeholder='';
				?>
				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Stay Night', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Turn on/off stay night settings.', 'tour-booking-manager'); ?></span></i></p>
						</div>	
						<div class="_dFlex_alignCenter_justfyBetween">
							<?php MP_Custom_Layout::switch_button($display_name, $checked); ?>
							<input type="number" data-collapse="#<?php echo esc_attr($display_name); ?>" min="0" class="ms-2 <?php echo esc_attr($active); ?>" name="ttbm_travel_duration_night" value="<?php echo MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_night'); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
						</div>
					</div>
				</section>
				<?php
			}

			public function tour_duration($tour_id) {
				$value_name = 'ttbm_travel_duration';
				$value = MP_Global_Function::get_post_info($tour_id, $value_name);
				$duration_type = MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_type', 'day');
				$placeholder = esc_html__('Ex: 3', 'tour-booking-manager');
			?>
				<section>
					<label class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Duration', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('duration'); ?></span></i></p>
						</div>
						<div class="dFlex">
							<input class="small rounded text-center" min="0.1" step="0.1" type="number" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
							<select class="rounded ms-2" name="ttbm_travel_duration_type">
								<option value="day" <?php echo esc_attr($duration_type == 'day' ? 'selected' : ''); ?>><?php esc_html_e('Days', 'tour-booking-manager'); ?></option>
								<option value="hour" <?php echo esc_attr($duration_type == 'hour' ? 'selected' : ''); ?>><?php esc_html_e('Hours', 'tour-booking-manager'); ?></option>
								<option value="min" <?php echo esc_attr($duration_type == 'min' ? 'selected' : ''); ?>><?php esc_html_e('Minutes', 'tour-booking-manager'); ?> </option>
							</select>
						</div>
					</label>
				</section>
				<?php
			}
			public function max_people($tour_id) {
				$max_people_status_field_name = 'ttbm_display_max_people';
				$max_people_field_status = MP_Global_Function::get_post_info($tour_id, $max_people_status_field_name, 'on');
				$max_people_field_name = 'ttbm_travel_max_people_allow';
				$max_people_field_value = MP_Global_Function::get_post_info($tour_id, $max_people_field_name);
				$max_people_placeholder = esc_html__('50', 'tour-booking-manager');
				$max_people_status_checked = ($max_people_field_status == 'off') ? '' : 'checked';
				$max_people_status_active = ($max_people_field_status == 'off') ? '' : 'mActive';
				?>
				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Max People', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('max_people'); ?></span></i></p>
						</div>
						<div class="_dFlex_alignCenter_justifyBetween">
							<?php MP_Custom_Layout::switch_button($max_people_status_field_name, $max_people_status_checked); ?>
							<input type="number" min="0" data-collapse="#<?php echo esc_attr($max_people_status_field_name); ?>" class="ms-2 rounded <?php echo esc_attr($max_people_status_active); ?>" name="<?php echo esc_attr($max_people_field_name); ?>" value="<?php echo esc_attr($max_people_field_value); ?>" placeholder="<?php echo esc_attr($max_people_placeholder); ?>"/>
						</div>
					</div>
				</section>
				<?php
			}
			public function starting_price($tour_id) {
				$display_name = 'ttbm_display_price_start';
				$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_travel_start_price';
				$value = MP_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Type Start Price', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				
				?>
				
				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Start Price', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('start_price'); ?></span></i> </p>
							
						</div>
						<div class="_dFlex_alignCenter_justifyBetween">
							<?php MP_Custom_Layout::switch_button($display_name, $checked); ?>
							<input type="number"  min="0" data-collapse="#<?php echo esc_attr($display_name); ?>" class="ms-2 rounded <?php echo esc_attr($active); ?>" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
						</div>
					</div>
				</section>
			<?php
			}
			
			public function starting_place($tour_id) {
				$status_field_name = 'ttbm_display_start_location';
				$status = MP_Global_Function::get_post_info($tour_id, $status_field_name, 'on');
				$location_field_name = 'ttbm_travel_start_place';
				$location_field_value = MP_Global_Function::get_post_info($tour_id, $location_field_name);
				$location_placeholder = esc_html__('Type Start Place...', 'tour-booking-manager');
				$status_checked = $status == 'off' ? '' : 'checked';
				$status_active = $status == 'off' ? '' : 'mActive';
				?>
				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Start Place', 'tour-booking-manager'); ?></p>
							<span class="text"><?php TTBM_Settings::des_p('start_place'); ?></span>
						</div>
						<div class="_dFlex_alignCenter_justifyBetween">
							<?php MP_Custom_Layout::switch_button($status_field_name, $status_checked); ?>
							<input type="text" data-collapse="#<?php echo esc_attr($status_field_name); ?>" class="ms-2 rounded <?php echo esc_attr($status_active); ?>" name="<?php echo esc_attr($location_field_name); ?>" value="<?php echo esc_attr($location_field_value); ?>" placeholder="<?php echo esc_attr($location_placeholder); ?>"/>
						</div>
					</div>
				</section>
				<?php
			}
			public function age_range($tour_id) {
				$display_name = 'ttbm_display_min_age';
				$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_travel_min_age';
				$value = MP_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Ex: 5 - 50 Years', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				
				?>

				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Age Range', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('age_range'); ?></span></i></p>
						</div>
						<div class="_dFlex_alignCenter_justifyBetween">
							<?php MP_Custom_Layout::switch_button($display_name, $checked); ?>
							<input type="text" data-collapse="#<?php echo esc_attr($display_name); ?>" class="ms-2 rounded <?php echo esc_attr($active); ?>" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
						</div>
					</div>
				</section>

			<?php
			}
			
			public function tour_language ($tour_id) {
				$display_name = 'ttbm_travel_language_status';
				$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$language = 'ttbm_travel_language';
				$language = MP_Global_Function::get_post_info($tour_id, $language);
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				$language_lists = MP_Global_Function::get_languages();
				?>

				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Tour Language', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Easily select your preferred language to enhance your travel experience.', 'tour-booking-manager'); ?></span></i></p>
						</div>
						<div class="_dFlex_alignCenter_justifyBetween">
							<?php MP_Custom_Layout::switch_button($display_name, $checked); ?>
							
							<select class="rounded ms-2 <?php echo esc_attr($active); ?>" name="ttbm_travel_language" data-collapse="#<?php echo esc_attr($display_name); ?>">
								<?php foreach($language_lists as $key => $value): ?>
									<option value="<?php echo esc_html($key); ?>" <?php echo esc_attr($key == $language ? 'selected' : ''); ?>><?php esc_html_e($value); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
                </section>
				<?php
			}
			public function short_description($tour_id) {
				$display_name = 'ttbm_display_description';
				$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_short_description';
				$value = MP_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Please Type Short Description...', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				?>

				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Short Description', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('short_des'); ?></span></i></p>
						</div>
						<div class="_dFlex_alignCenter_justifyBetween">
							<?php MP_Custom_Layout::switch_button($display_name, $checked); ?>
							<textarea data-collapse="#<?php echo esc_attr($display_name); ?>" class="ms-2 rounded <?php echo esc_attr($active); ?>" cols="72" rows="2" name="<?php echo esc_attr($value_name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_attr($value); ?></textarea>
						</div>
					</div>
                </section>
				<?php
			}
			//*****************//
			public function save_general_settings($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					/***************/
					$ttbm_travel_duration = MP_Global_Function::get_submit_info('ttbm_travel_duration');
					$ttbm_travel_duration_type = MP_Global_Function::get_submit_info('ttbm_travel_duration_type', 'day');
					update_post_meta($tour_id, 'ttbm_travel_duration', $ttbm_travel_duration);
					update_post_meta($tour_id, 'ttbm_travel_duration_type', $ttbm_travel_duration_type);
					$ttbm_display_duration = MP_Global_Function::get_submit_info('ttbm_display_duration_night') ? 'on' : 'off';
					$ttbm_travel_duration_night = MP_Global_Function::get_submit_info('ttbm_travel_duration_night');
					update_post_meta($tour_id, 'ttbm_travel_duration_night', $ttbm_travel_duration_night);
					update_post_meta($tour_id, 'ttbm_display_duration_night', $ttbm_display_duration);
					/***************/
					$ttbm_display_price_start = MP_Global_Function::get_submit_info('ttbm_display_price_start') ? 'on' : 'off';
					$ttbm_travel_start_price = MP_Global_Function::get_submit_info('ttbm_travel_start_price');
					update_post_meta($tour_id, 'ttbm_display_price_start', $ttbm_display_price_start);
					update_post_meta($tour_id, 'ttbm_travel_start_price', $ttbm_travel_start_price);
					/***************/
					$ttbm_display_max_people = MP_Global_Function::get_submit_info('ttbm_display_max_people') ? 'on' : 'off';
					$ttbm_travel_max_people_allow = MP_Global_Function::get_submit_info('ttbm_travel_max_people_allow');
					update_post_meta($tour_id, 'ttbm_display_max_people', $ttbm_display_max_people);
					update_post_meta($tour_id, 'ttbm_travel_max_people_allow', $ttbm_travel_max_people_allow);
					/***************/
					$ttbm_display_min_age = MP_Global_Function::get_submit_info('ttbm_display_min_age') ? 'on' : 'off';
					$ttbm_travel_min_age = MP_Global_Function::get_submit_info('ttbm_travel_min_age');
					update_post_meta($tour_id, 'ttbm_display_min_age', $ttbm_display_min_age);
					update_post_meta($tour_id, 'ttbm_travel_min_age', $ttbm_travel_min_age);
					/***************/
					$visible_start_location = MP_Global_Function::get_submit_info('ttbm_display_start_location') ? 'on' : 'off';
					$start_location = MP_Global_Function::get_submit_info('ttbm_travel_start_place');
					update_post_meta($tour_id, 'ttbm_display_start_location', $visible_start_location);
					update_post_meta($tour_id, 'ttbm_travel_start_place', $start_location);
					/***************/
					$ttbm_display_location = MP_Global_Function::get_submit_info('ttbm_display_location') ? 'on' : 'off';
					$ttbm_location_name = MP_Global_Function::get_submit_info('ttbm_location_name');
					update_post_meta($tour_id, 'ttbm_display_location', $ttbm_display_location);
					update_post_meta($tour_id, 'ttbm_location_name', $ttbm_location_name);
					$location = get_term_by('name',$ttbm_location_name,'ttbm_tour_location');
					$ttbm_country_name = get_term_meta($location->term_id, 'ttbm_country_location',true);
					update_post_meta($tour_id, 'ttbm_country_name', $ttbm_country_name);
					/***************/
					$visible_description = MP_Global_Function::get_submit_info('ttbm_display_description') ? 'on' : 'off';
					$description = MP_Global_Function::get_submit_info('ttbm_short_description');
					update_post_meta($tour_id, 'ttbm_display_description', $visible_description);
					update_post_meta($tour_id, 'ttbm_short_description', $description);
					/***************/
					$language_status = MP_Global_Function::get_submit_info('ttbm_travel_language_status') ? 'on' : 'off';
					$language = MP_Global_Function::get_submit_info('ttbm_travel_language','en_US');
					update_post_meta($tour_id, 'ttbm_travel_language_status', $language_status);
					update_post_meta($tour_id, 'ttbm_travel_language', $language);
					/***************/
				}
			}
		}
		new TTBM_Settings_General();
	}