<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Hotel_Activity')) {
		class TTBM_Settings_Hotel_Activity {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'settings_activity'], 10, 1);
				add_action('ttbm_single_activity', [$this, 'show_activity_in_frontend'], 10, 1);
				add_action('save_post', [$this, 'features_save']);
				//********Add New Feature************//
				// add_action('wp_ajax_load_ttbm_feature_form', [$this, 'load_ttbm_feature_form']);
				// add_action('wp_ajax_nopriv_load_ttbm_feature_form', [$this, 'load_ttbm_feature_form']);
				// add_action('wp_ajax_ttbm_reload_feature_list', [$this, 'ttbm_reload_feature_list']);
				// add_action('wp_ajax_nopriv_ttbm_reload_feature_list', [$this, 'ttbm_reload_feature_list']);
				// /************add New Feature********************/
				// add_action('wp_ajax_ttbm_new_feature_save', [$this, 'ttbm_new_feature_save']);
				// add_action('wp_ajax_nopriv_ttbm_new_feature_save', [$this, 'ttbm_new_feature_save']);
			}
			public function settings_activity($tour_id) {
				?>
                <div class="tabsItem ttbm_settings_hotel_activity" data-tabs="#ttbm_settings_hotel_activity">
                    <h2 class="h4 px-0 text-primary"><?php esc_html_e('Activity Settings', 'tour-booking-manager'); ?></h2>
                    <p>
						<?php TTBM_Settings::des_p('featrue_settings_description') ?>
						<a class="btn" target="_blank" href="edit.php?post_type=ttbm_tour&page=ttbm_hotel_booking_lists"> Add new activity</a>
					</p>
                    <div class="mtb ttbm_features_table">
						<?php $this->activity_section($tour_id); ?>
                    </div>
                </div>
				<?php
			}

			public function activity_section($tour_id) {
				$features = TTBM_Global_Function::get_taxonomy('ttbm_hotel_activities_list');
				$features_status = get_post_meta($tour_id, 'ttbm_hotel_activity_status', 'on');
				
				$features_active = $features_status == 'off' ? '' : 'mActive';
				$features_checked = $features_status == 'off' ? '' : 'checked';

				if (sizeof($features) > 0) { ?>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-clipboard-list"></i><?php esc_html_e('Included Features', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_hotel_activity_status', $features_checked); ?>
                        </div>
                        <div data-collapse="#ttbm_hotel_activity_status" class="includedd-features-section <?php echo esc_attr($features_active); ?>">
							<?php $this->activity_lists($tour_id,'ttbm_hotel_activity_selection'); ?>
                    </section>
					<?php
				}
			}

			public function activity_lists($tour_id,$term_key_name) {
				$all_features = TTBM_Global_Function::get_taxonomy('ttbm_hotel_activities_list');
				$features = TTBM_Function::get_feature_list($tour_id, $term_key_name);
				$feature_ids = is_array($features) ? implode(',', $features) : '';

				if (sizeof($all_features) > 0) {
					?>
                    <div class="groupCheckBox">
                        <input type="hidden" name="<?php echo esc_html($term_key_name); ?>" value="<?php echo esc_attr($feature_ids); ?>"/>
						<?php foreach ($all_features as $feature_list) { ?>
							<?php $icon = get_term_meta($feature_list->term_id, 'ttbm_hotel_activity_icon', true) ? get_term_meta($feature_list->term_id, 'ttbm_hotel_activity_icon', true) : ''; ?>
                            <label class="customCheckboxLabel">
								<input type="checkbox" <?php echo in_array($feature_list->term_id, $features) ? 'checked' : ''; ?> data-checked="<?php echo esc_attr($feature_list->term_id); ?>"/>
                                <span class="customCheckbox">
									<i class="<?php echo esc_attr($icon); ?>"></i>
									<?php echo esc_html($feature_list->name); ?>
								</span>
                            </label>
						<?php } ?>
                    </div>
					<?php
				}
			}

			public function show_activity_in_frontend() {
				$tour_id = get_the_ID();

				$selected_features = TTBM_Function::get_feature_list($tour_id, 'ttbm_hotel_activity_selection');
				$selected_features = is_array($selected_features) ? $selected_features : [];
				$all_features = TTBM_Global_Function::get_taxonomy('ttbm_hotel_activities_list');
				$features_status = get_post_meta($tour_id, 'ttbm_hotel_activity_status', true);
				if (!empty($selected_features) && $features_status === 'on') { ?>
					<div class="widgets parking-info">
                        <h2>Activities</h2>
						<?php foreach ($all_features as $feature) : ?>
							<?php if (in_array($feature->term_id, $selected_features)) : 
								$icon = get_term_meta($feature->term_id, 'ttbm_hotel_activity_icon', true);
								$icon = $icon ? $icon : 'mi mi-forward';
							?>
								<div class="widgets-text">
                                    <!-- <i class="<?php echo esc_attr($icon); ?>"></i> -->
									<span><?php echo esc_html($feature->name); ?></span>
                                </div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php }
			}

			public function array_to_string($feat_selection){
				if (!empty($feat_selection)) {
					$feat_selection = array_map('trim', explode(',', $feat_selection));
				} else {
					$feat_selection = [];
				}
				return $feat_selection;
			}

			public function features_save($post_id){
				if (!isset($_POST['ttbm_hotel_type_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_hotel_type_nonce'])), 'ttbm_hotel_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
					return;
				}
				if (get_post_type($post_id) == 'ttbm_hotel') {
					$features_status = isset($_POST['ttbm_hotel_activity_status']) && sanitize_text_field(wp_unslash($_POST['ttbm_hotel_activity_status'])) ? 'on' : 'off';
					update_post_meta($post_id, 'ttbm_hotel_activity_status', $features_status);
					
					$feat_selection = isset($_POST['ttbm_hotel_activity_selection']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_activity_selection'])) : [];
					
					$feat_selection = $this->array_to_string($feat_selection);

					update_post_meta($post_id, 'ttbm_hotel_activity_selection', $feat_selection);
				}
				
			}
			/************************/
		}
		new TTBM_Settings_Hotel_Activity();
	}