<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_activity')) {
		class TTBM_Settings_activity {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
				add_action('add_ttbm_settings_tab_content', [$this, 'ttbm_settings_activities'], 10, 1);
				//*********Activity***************//
				add_action('wp_ajax_load_ttbm_activity_form', [$this, 'load_ttbm_activity_form']);
				add_action('wp_ajax_nopriv_load_ttbm_activity_form', [$this, 'load_ttbm_activity_form']);
				add_action('wp_ajax_ttbm_reload_activity_list', [$this, 'ttbm_reload_activity_list']);
				add_action('wp_ajax_nopriv_ttbm_reload_activity_list', [$this, 'ttbm_reload_activity_list']);
				//******************//
				add_action('ttbm_settings_save', [$this, 'save_activities']);
				add_action('wp_ajax_ttbm_new_activity_save', [$this, 'ttbm_new_activity_save']);
				add_action('wp_ajax_nopriv_ttbm_new_activity_save', [$this, 'ttbm_new_activity_save']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_activies">
					<i class="fas fa-clipboard-list"></i><?php esc_html_e(' Activities', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function ttbm_settings_activities($tour_id) {
				$ttbm_label = TTBM_Function::get_name();
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_activities', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				
				<div class="tabsItem mp_settings_area ttbm_settings_activities" data-tabs="#ttbm_settings_activies">
					<h2><?php esc_html_e('Activity Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('activity_settings_description'); ?></p>
					
					<section class="bg-light">
						<label for="" class="label">
							<div>
								<p><?php esc_html_e('Activity Settings', 'tour-booking-manager'); ?></p>
								<span class="text"><?php esc_html_e('You can add tour activity here', 'tour-booking-manager'); ?></span>
							</div>
						</label>
					</section>
					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e($ttbm_label . ' Activities Settings', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_activities'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_activities', $checked); ?> 
						</label>
                    </section>
					<div data-collapse="#ttbm_display_activities" class="ttbm_activities_area <?php echo esc_attr($active); ?>">
						<?php $this->activities($tour_id); ?>
					</div>
				</div>
				<?php
			}
			public function activities($tour_id) {
				$activities = MP_Global_Function::get_taxonomy('ttbm_tour_activities');
				$tour_activities = MP_Global_Function::get_post_info($tour_id, 'ttbm_tour_activities', []);
				?>
				<div class="ttbm_activities_table">
					<section>
						<label class="label">
						<div>
							<p><?php esc_html_e('Create activities', 'tour-booking-manager'); ?></p>
							<span><?php esc_html_e('To create new activity, click this button', 'tour-booking-manager'); ?></span>
						</div>
						<?php MP_Custom_Layout::popup_button_xs('add_new_activity_popup', esc_html__('Create New Activity', 'tour-booking-manager')); ?>
						</label>
					</section>

					<section>
						<label class="label">
							<div> 
								<p><?php esc_html_e('Activities', 'tour-booking-manager'); ?></p>
								<span><?php TTBM_Settings::des_p('activities'); ?></span>
							</div>
							<div class="w-50">
								<select name="ttbm_tour_activities[]" multiple='multiple' class='mp_select2' data-placeholder="<?php esc_html_e('Please Select a Activities ', 'tour-booking-manager'); ?>">
									<?php foreach ($activities as $activity) { ?>
										<option value="<?php echo esc_attr($activity->name) ?>" <?php echo in_array($activity->name, $tour_activities) ? 'selected' : ''; ?>>
											<?php echo esc_html($activity->name); ?>
										</option>
									<?php } ?>
								</select>
							</div>
						</label>
					</section>
				</div>
				<?php
				$this->add_new_activity_popup();
			}
			public function add_new_activity_popup() {
				?>
				<div class="mpPopup" data-popup="add_new_activity_popup">
					<div class="popupMainArea">
						<div class="popupHeader">
							<h4>
								<?php esc_html_e('Add New Activity', 'tour-booking-manager'); ?>
								<p class="_textSuccess_ml_dNone ttbm_success_info">
									<span class="fas fa-check-circle mR_xs"></span>
									<?php esc_html_e('Activity is added successfully.', 'tour-booking-manager') ?>
								</p>
							</h4>
							<span class="fas fa-times popupClose"></span>
						</div>
						<div class="popupBody ttbm_activity_form_area">
						</div>
						<div class="popupFooter">
							<div class="buttonGroup">
								<button class="_themeButton ttbm_new_activity_save" type="button"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
								<button class="_warningButton ttbm_new_activity_save_close" type="button"><?php esc_html_e('Save & Close', 'tour-booking-manager'); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			public function load_ttbm_activity_form() {
				?>
				<label class="flexEqual">
					<span><?php esc_html_e('Activity Name : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span> <input type="text" name="ttbm_activity_name" class="formControl" required>
				</label>
				<p class="textRequired" data-required="ttbm_activity_name">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Activity name is required!', 'tour-booking-manager'); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_activity_name'); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e('Activity Description : ', 'tour-booking-manager'); ?></span> <textarea name="ttbm_activity_description" class="formControl" rows="3"></textarea>
				</label>
				<?php TTBM_Settings::des_p('ttbm_activity_description'); ?>
				<div class="divider"></div>
				<div class="flexEqual">
					<span><?php esc_html_e('Activity Icon : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span>
					<?php do_action('mp_input_add_icon', 'ttbm_activity_icon'); ?>
				</div>
				<p class="textRequired" data-required="ttbm_activity_icon">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Activity icon is required!', 'tour-booking-manager'); ?>
				</p>
				<?php
				die();
			}
			public function ttbm_reload_activity_list() {
				$ttbm_id = MP_Global_Function::data_sanitize($_POST['ttbm_id']);
				$this->activities($ttbm_id);
				die();
			}
			public function save_activities($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$display_activities = MP_Global_Function::get_submit_info('ttbm_display_activities') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_activities', $display_activities);
					$activities = MP_Global_Function::get_submit_info('ttbm_tour_activities', array());
					update_post_meta($tour_id, 'ttbm_tour_activities', $activities);
				}
			}
			public function ttbm_new_activity_save() {
				$name = MP_Global_Function::data_sanitize($_POST['activity_name']);
				$description = MP_Global_Function::data_sanitize($_POST['activity_description']);
				$icon = MP_Global_Function::data_sanitize($_POST['activity_icon']);
				$query = wp_insert_term($name,   // the term
					'ttbm_tour_activities', // the taxonomy
					array('description' => $description));
				if (is_array($query) && $query['term_id'] != '') {
					$term_id = $query['term_id'];
					update_term_meta($term_id, 'ttbm_activities_icon', $icon);
				}
				die();
			}
		}
		new TTBM_Settings_activity();
	}