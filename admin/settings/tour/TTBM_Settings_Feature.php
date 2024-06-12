<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Feature')) {
		class TTBM_Settings_Feature {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
				add_action('add_ttbm_settings_tab_content', [$this, 'ttbm_settings_feature'], 10, 1);
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'ttbm_settings_feature'], 10, 1);
				//********Add New Feature************//
				add_action('wp_ajax_load_ttbm_feature_form', [$this, 'load_ttbm_feature_form']);
				add_action('wp_ajax_nopriv_load_ttbm_feature_form', [$this, 'load_ttbm_feature_form']);
				add_action('wp_ajax_ttbm_reload_feature_list', [$this, 'ttbm_reload_feature_list']);
				add_action('wp_ajax_nopriv_ttbm_reload_feature_list', [$this, 'ttbm_reload_feature_list']);
				/************add New Feature********************/
				add_action('wp_ajax_ttbm_new_feature_save', [$this, 'ttbm_new_feature_save']);
				add_action('wp_ajax_nopriv_ttbm_new_feature_save', [$this, 'ttbm_new_feature_save']);
				//************************//
				add_action('ttbm_settings_save', [$this, 'save_features']);
				add_action('ttbm_settings_feature_save', [$this, 'save_feature_data']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_feature">
					<i class="fas fa-clipboard-list"></i><?php esc_html_e(' Features', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function ttbm_settings_feature($tour_id) {
				?>
				<div class="tabsItem ttbm_settings_feature" data-tabs="#ttbm_settings_feature">
					<h2 class="h4 px-0 text-primary"><?php esc_html_e('Features Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('featrue_settings_description') ?></p>
					<div class="mtb ttbm_features_table">
						<?php $this->feature($tour_id); ?>
					</div>
					
				</div>
				<?php
			}
			public function feature($tour_id) {
				$features = MP_Global_Function::get_taxonomy('ttbm_tour_features_list');
				$include_display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_include_service', 'on');
				$include_active = $include_display == 'off' ? '' : 'mActive';
				$exclude_display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_exclude_service', 'on');
				$exclude_active = $exclude_display == 'off' ? '' : 'mActive';
				$in_checked = $include_display == 'off' ? '' : 'checked';
				$ex_checked = $exclude_display == 'off' ? '' : 'checked';
				if (sizeof($features) > 0) { ?>
					<section class="bg-light">
						<label class="label">
							<div>
								<p><?php esc_html_e('Include Exclude Feature', 'tour-booking-manager'); ?></p>
								<span class="text"><?php esc_html_e('You can add new feature to Include/Exclude Feature of tours', 'tour-booking-manager'); ?></span>
							</div>
						</label>
					</section>
					<section>
						<label class="label">
							<div>
								<p><?php esc_html_e('Create a feature', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttip_add_new_feature_popup'); ?></span></i></p>
								<span class="text"><?php TTBM_Settings::des_p('add_new_feature_popup'); ?></span>
							</div>
							<?php MP_Custom_Layout::popup_button('add_new_feature_popup', esc_html__('Create New Feature', 'tour-booking-manager')); ?>
						</label>
						<?php $this->add_new_feature_popup(); ?>
					</section>
					<section>
                        <label class="label"> 
                            <div class="w-50 _dFlex_justifyBetween_alignCenter">
                                <label for=""><?php esc_html_e('Price Included Feature', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_include_service'); ?></span></i></label>
                                <?php MP_Custom_Layout::switch_button('ttbm_display_include_service', $in_checked); ?> 
                            </div>
                            <div class="w-50 _dFlex_justifyBetween_alignCenter_mL">
                                <label for=""><?php esc_html_e('Price Excluded Feature', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_exclude_service'); ?></span></i></label>
                                <?php MP_Custom_Layout::switch_button('ttbm_display_exclude_service', $ex_checked); ?> 
                            </div>
                        </label>
						<label class="label"> 
                            <div class="w-50 _mT">
								<div data-collapse="#ttbm_display_include_service" class="<?php echo esc_attr($include_active); ?>">
									<div class="includedd-features-section">
										<?php $this->feature_list($tour_id, 'ttbm_service_included_in_price'); ?> 
									</div>
								</div>
                            </div>
                            <div class="w-50 _mT_mL">
								<div data-collapse="#ttbm_display_exclude_service" class="<?php echo esc_attr($include_active); ?>">
									<div class="excludedd-features-section">	
										<?php $this->feature_list($tour_id, 'ttbm_service_excluded_in_price'); ?>
									</div>
								</div>
                            </div>
                        </label>
                    </section>
					<section>
						
					</section>

					<?php
				}
			}
			public function feature_list($tour_id, $feature_name) {
				$all_features = MP_Global_Function::get_taxonomy('ttbm_tour_features_list');
				$features = TTBM_Function::get_feature_list($tour_id, $feature_name);
				$feature_ids = TTBM_Function::feature_array_to_string($features);
				if (sizeof($all_features) > 0) {
					?>
					<div class="groupCheckBox _dFlex flexColumn">
						<input type="hidden" name="<?php echo esc_attr($feature_name); ?>" value="<?php echo esc_attr($feature_ids); ?>"/>
						<?php foreach ($all_features as $feature_list) { ?>
							<?php $icon = get_term_meta($feature_list->term_id, 'ttbm_feature_icon', true) ? get_term_meta($feature_list->term_id, 'ttbm_feature_icon', true) : 'fas fa-forward'; ?>
							<label class="customCheckboxLabel py-1">
								<input class="text-black" type="checkbox" <?php echo TTBM_Function::check_exit_feature($features, $feature_list->name) ? 'checked' : ''; ?> data-checked="<?php echo esc_attr($feature_list->term_id); ?>"/> <span class="customCheckbox text-black"><i class="me-1 text-primary fa-lg <?php echo esc_attr($icon); ?>"></i><?php esc_html_e($feature_list->name); ?></span>
							</label>
						<?php } ?>
					</div>
					<?php
				}
			}
			public function add_new_feature_popup() {
				?>
				<div class="mpPopup" data-popup="add_new_feature_popup">
					<div class="popupMainArea">
						<div class="popupHeader">
							<h4>
								<?php esc_html_e('Add New Feature', 'tour-booking-manager'); ?>
								<p class="_textSuccess_ml_dNone ttbm_success_info"><span class="fas fa-check-circle mR_xs"></span><?php esc_html_e('Feature is added successfully.', 'tour-booking-manager') ?></p>
							</h4>
							<span class="fas fa-times popupClose"></span>
						</div>
						<div class="popupBody ttbm_feature_form_area">
						</div>
						<div class="popupFooter">
							<div class="buttonGroup">
								<button class="_themeButton ttbm_new_feature_save" type="button"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
								<button class="_warningButton ttbm_new_feature_save_close" type="button"><?php esc_html_e('Save & Close', 'tour-booking-manager'); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			public function load_ttbm_feature_form() {
				?>
				<label class="flexEqual">
					<span><?php esc_html_e('Feature Name : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span> <input type="text" name="ttbm_feature_name" class="formControl" required>
				</label>
				<p class="textRequired" data-required="ttbm_feature_name">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Feature name is required!', 'tour-booking-manager'); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_feature_name'); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e('Feature Description : ', 'tour-booking-manager'); ?></span> <textarea name="ttbm_feature_description" class="formControl" rows="3"></textarea>
				</label>
				<?php TTBM_Settings::des_p('ttbm_feature_description'); ?>
				<div class="divider"></div>
				<div class="flexEqual">
					<span><?php esc_html_e('Feature Icon : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span>
					<?php do_action('mp_input_add_icon', 'ttbm_feature_icon'); ?>
				</div>
				<p class="textRequired" data-required="ttbm_feature_icon">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Feature icon is required!', 'tour-booking-manager'); ?>
				</p>
				<?php
				die();
			}
			// public function ttbm_reload_feature_list() {
			// 	$ttbm_id = MP_Global_Function::data_sanitize($_POST['ttbm_id']);
			// 	$this->feature($ttbm_id);
			// 	die();
			// }
			public function ttbm_reload_feature_list()
			{
				$ttbm_id = MP_Global_Function::data_sanitize($_POST['ttbm_id']);

				// Load the included and excluded features sections
				ob_start();
				$this->feature($ttbm_id);
				$included_features_html = ob_get_clean();

				ob_start();
				$this->feature($ttbm_id);
				$excluded_features_html = ob_get_clean();

				// Return the HTML content of both sections
				wp_send_json_success(array(
					'included_features_html' => $included_features_html,
					'excluded_features_html' => $excluded_features_html
				));
			}
			//*********//
			public function save_features($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$this->save_feature_data($tour_id);
				}
			}
			public function save_feature_data($tour_id) {
				$include_service = MP_Global_Function::get_submit_info('ttbm_display_include_service') ? 'on' : 'off';
				$exclude_service = MP_Global_Function::get_submit_info('ttbm_display_exclude_service') ? 'on' : 'off';
				update_post_meta($tour_id, 'ttbm_display_include_service', $include_service);
				update_post_meta($tour_id, 'ttbm_display_exclude_service', $exclude_service);
				$include = MP_Global_Function::get_submit_info('ttbm_service_included_in_price', array());
				$new_include = TTBM_Function::feature_id_to_array($include);
				update_post_meta($tour_id, 'ttbm_service_included_in_price', $new_include);
				$exclude = MP_Global_Function::get_submit_info('ttbm_service_excluded_in_price', array());
				$new_exclude = TTBM_Function::feature_id_to_array($exclude);
				update_post_meta($tour_id, 'ttbm_service_excluded_in_price', $new_exclude);
			}
			/************************/
			public function ttbm_new_feature_save() {
				$feature_name = MP_Global_Function::data_sanitize($_POST['feature_name']);
				$feature_description = MP_Global_Function::data_sanitize($_POST['feature_description']);
				$feature_icon = MP_Global_Function::data_sanitize($_POST['feature_icon']);
				$query = wp_insert_term($feature_name,   // the term
					'ttbm_tour_features_list', // the taxonomy
					array('description' => $feature_description));
				if (is_array($query) && $query['term_id'] != '') {
					$term_id = $query['term_id'];
					update_term_meta($term_id, 'ttbm_feature_icon', $feature_icon);
				}
				die();
			}
		}
		new TTBM_Settings_Feature();
	}