<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_place_you_see')) {
		class TTBM_Settings_place_you_see {
			public function __construct() {
				add_action('ttbm_location_tab_bottom', [$this, 'render_location_card'], 10, 1);
				//*********add new Place***************//
				add_action('wp_ajax_load_ttbm_place_you_see_form', [$this, 'load_ttbm_place_you_see_form']);
				add_action('wp_ajax_nopriv_load_ttbm_place_you_see_form', [$this, 'load_ttbm_place_you_see_form']);
				add_action('wp_ajax_ttbm_reload_place_you_see_list', [$this, 'ttbm_reload_place_you_see_list']);
				add_action('wp_ajax_nopriv_ttbm_reload_place_you_see_list', [$this, 'ttbm_reload_place_you_see_list']);
				add_action('wp_ajax_ttbm_reload_place_dropdown_options', [$this, 'ttbm_reload_place_dropdown_options']);
				add_action('wp_ajax_nopriv_ttbm_reload_place_dropdown_options', [$this, 'ttbm_reload_place_dropdown_options']);
				add_action('wp_ajax_ttbm_new_place_save', [$this, 'ttbm_new_place_save']);
				add_action('wp_ajax_nopriv_ttbm_new_place_save', [$this, 'ttbm_new_place_save']);
				/******************************/
			}
			private function current_user_can_edit_tour($tour_id) {
				return $tour_id > 0 && current_user_can('edit_post', $tour_id);
			}
			public function render_location_card($tour_id) {
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hiphop', 'on');
				$active  = $display === 'off' ? '' : 'mActive';
				$checked = $display === 'off' ? '' : 'checked';
				?>
                <section class="ttbm-places-visit-card">
                    <div class="ttbm-header ttbm-header--with-switch">
                        <h4><i class="fas fa-map-marker-alt"></i><?php esc_html_e('Places You\'ll Visit', 'tour-booking-manager'); ?></h4>
						<?php TTBM_Custom_Layout::switch_button('ttbm_display_hiphop', $checked); ?>
                    </div>
                    <div data-collapse="#ttbm_display_hiphop" class="ttbm_settings_area ttbm_settings_place_you_see ttbm_place_you_see_area <?php echo esc_attr($active); ?>">
						<?php $this->place_you_see($tour_id); ?>
						<?php self::add_new_place_popup(); ?>
                    </div>
                </section>
				<?php
			}
			public function place_you_see($tour_id) {
				$hiphop_places = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hiphop_places', array());
				$all_places = TTBM_Global_Function::query_post_type('ttbm_places');
				$places = $all_places->posts;
				?>
                <div class="ttbm_place_you_see_table">
					<?php if ($all_places->post_count > 0) { ?>
                        <div>
                            <table>
                                <thead>
                                <tr>
                                    <th><?php esc_html_e('Place Name', 'tour-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Place', 'tour-booking-manager'); ?></th>
                                    <th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
                                </tr>
                                </thead>
                                <tbody class="ttbm_sortable_area ttbm_item_insert">
								<?php
									if (sizeof($hiphop_places)) {
										$is_first_place_row = true;
										foreach ($hiphop_places as $hiphop_place) {
											$this->place_you_see_item($places, $hiphop_place, $is_first_place_row);
											$is_first_place_row = false;
										}
									} else {
										$this->place_you_see_item($places, array(), true);
									}
								?>
                                </tbody>
                            </table>
							<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add New Place', 'tour-booking-manager')); ?>
                        </div>
						<?php
					}
					?>
                </div>
                <div class="ttbm_hidden_content">
                    <table>
                        <tbody class="ttbm_hidden_item">
						<?php $this->place_you_see_item($places, array(), false); ?>
                        </tbody>
                    </table>
                </div>
				<?php
				wp_reset_postdata();
			}
			public function place_you_see_item($places, $hiphop_place = array(), $show_hint = false) {
				$place_id = is_array($hiphop_place) && array_key_exists('ttbm_city_place_id', $hiphop_place) ? $hiphop_place['ttbm_city_place_id'] : '';
				$place_name = is_array($hiphop_place) && array_key_exists('ttbm_place_label', $hiphop_place) ? $hiphop_place['ttbm_place_label'] : '';
				$place_name = $place_id && !$place_name ? get_the_title($place_id) : $place_name;
				?>
                <tr class="ttbm_remove_area">
                    <td>
                        <div class="dFlex align-items-center">
                            <input class="formControl ttbm_name_validation" name="ttbm_place_label[]" value="<?php echo esc_attr($place_name); ?>" placeholder="<?php esc_attr_e('Place name', 'tour-booking-manager'); ?>"/>
                        </div>
                    </td>
                    <td>
                        <div class="ttbm-place-select-wrap">
                            <select class="<?php echo esc_attr(is_array($hiphop_place) && sizeof($hiphop_place) > 0 ? 'ttbm_select2' : 'add_ttbm_select2'); ?>" name="ttbm_city_place_id[]">
								<?php self::render_place_select_options($places, $place_id); ?>
                            </select>
							<?php if ($show_hint) : ?>
								<p class="ttbm-place-hint">
									<?php esc_html_e("If your place isn't listed,", 'tour-booking-manager'); ?>
									<button type="button" class="ttbm-location-add-link ttbm-place-add-link" data-target-popup="add_new_place_popup">
										<?php esc_html_e('Add place', 'tour-booking-manager'); ?>
									</button>
								</p>
							<?php endif; ?>
						</div>
                    </td>
                    <td class="textRight"><?php TTBM_Custom_Layout::move_remove_button(); ?></td>
                </tr>
				<?php
			}
			public static function add_new_place_popup() {
				?>
                <div class="ttbm_popup ttbm-location-popup ttbm-place-popup" data-popup="add_new_place_popup">
                    <div class="popupMainArea">
                        <div class="popupHeader ttbm-location-popup__header">
							<div class="ttbm-location-popup__title-wrap">
								<h4>
									<i class="fas fa-map-pin" aria-hidden="true"></i>
									<?php esc_html_e('Add New Place', 'tour-booking-manager'); ?>
								</h4>
								<p class="ttbm-location-popup__success ttbm_success_info _textSuccess_ml_dNone">
									<span class="fas fa-check-circle" aria-hidden="true"></span>
									<?php esc_html_e('Place is added successfully.', 'tour-booking-manager'); ?>
								</p>
							</div>
                            <button type="button" class="ttbm-location-popup__close popupClose" aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">
								<span class="fas fa-times" aria-hidden="true"></span>
							</button>
                        </div>
                        <div class="popupBody ttbm_place_you_see_form_area"></div>
                        <div class="popupFooter ttbm-location-popup__footer">
                            <p class="ttbm-place-save-error ttbm-location-popup-error" role="alert" style="display:none;"></p>
                            <div class="buttonGroup">
                                <button class="_themeButton ttbm_new_place_save" type="button"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
                                <button class="_warningButton ttbm_new_place_save_close" type="button"><?php esc_html_e('Cancel', 'tour-booking-manager'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function load_ttbm_place_you_see_form() {
				if (!current_user_can('edit_posts')) {
					wp_die(esc_html__('You do not have permission to access this form.', 'tour-booking-manager'), '', ['response' => 403]);
				}
				wp_nonce_field('ttbm_add_new_place_popup', 'ttbm_add_new_place_popup');
				?>
				<div class="ttbm-location-popup-fields">
					<div class="ttbm-location-popup-field ttbm-location-popup-field--required">
						<label class="ttbm-location-popup-label" for="ttbm_place_name">
							<?php esc_html_e('Name', 'tour-booking-manager'); ?>
							<span class="ttbm-location-popup-required" aria-hidden="true">*</span>
						</label>
						<input type="text" id="ttbm_place_name" name="ttbm_place_name" class="formControl" placeholder="<?php esc_attr_e('Place name', 'tour-booking-manager'); ?>" required>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_place_name'); ?></p>
						<p class="textRequired ttbm-location-popup-error" data-required="ttbm_place_name">
							<span class="fas fa-info-circle" aria-hidden="true"></span>
							<?php esc_html_e('Place name is required!', 'tour-booking-manager'); ?>
						</p>
					</div>
					<div class="ttbm-location-popup-field">
						<label class="ttbm-location-popup-label" for="ttbm_place_description">
							<?php esc_html_e('Description', 'tour-booking-manager'); ?>
						</label>
						<textarea id="ttbm_place_description" name="ttbm_place_description" class="formControl" rows="3" placeholder="<?php esc_attr_e('Short description', 'tour-booking-manager'); ?>"></textarea>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_place_description'); ?></p>
					</div>
					<div class="ttbm-location-popup-field ttbm-location-popup-field--required">
						<label class="ttbm-location-popup-label">
							<?php esc_html_e('Image', 'tour-booking-manager'); ?>
							<span class="ttbm-location-popup-required" aria-hidden="true">*</span>
						</label>
						<?php TTBM_Layout::single_image_button('ttbm_place_image'); ?>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_place_image'); ?></p>
						<p class="textRequired ttbm-location-popup-error" data-required="ttbm_place_image">
							<span class="fas fa-info-circle" aria-hidden="true"></span>
							<?php esc_html_e('Place image is required!', 'tour-booking-manager'); ?>
						</p>
					</div>
				</div>
				<?php
				die();
			}
			public function ttbm_new_place_save() {
				if (!isset($_POST['_wp_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wp_nonce'])), 'ttbm_add_new_place_popup')) {
					wp_send_json_error(['message' => esc_html__('Security check failed. Please close and reopen the popup.', 'tour-booking-manager')]);
				}
				if (!current_user_can('edit_posts')) {
					wp_send_json_error(['message' => esc_html__('You do not have permission to create places.', 'tour-booking-manager')], 403);
				}
				$name        = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
				$description = isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : '';
				$image       = isset($_POST['image']) ? absint(wp_unslash($_POST['image'])) : 0;
				if ($name === '' || $image <= 0) {
					wp_send_json_error(['message' => esc_html__('Place name and image are required.', 'tour-booking-manager')]);
				}
				$post_id = wp_insert_post(
					[
						'post_title'   => $name,
						'post_content' => $description,
						'post_status'  => 'publish',
						'post_type'    => 'ttbm_places',
					],
					true
				);
				if (is_wp_error($post_id)) {
					wp_send_json_error(['message' => $post_id->get_error_message()]);
				}
				set_post_thumbnail($post_id, $image);
				wp_send_json_success(
					[
						'post_id' => $post_id,
						'name'    => $name,
					]
				);
			}
			public static function render_place_select_options($places, $selected_id = '') {
				$selected_id = absint($selected_id);
				?>
                <option value="" <?php echo $selected_id ? '' : 'selected'; ?> disabled>
					<?php esc_html_e('Please Select a Place', 'tour-booking-manager'); ?>
                </option>
				<?php
				foreach ($places as $place) {
					$id = $place->ID;
					?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_id, $id); ?>>
						<?php echo esc_html($place->post_title); ?>
                    </option>
					<?php
				}
			}
			public function ttbm_reload_place_dropdown_options() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
				}
				if (!current_user_can('edit_posts')) {
					wp_send_json_error(['message' => esc_html__('You do not have permission to access places.', 'tour-booking-manager')], 403);
				}
				$all_places = TTBM_Global_Function::query_post_type('ttbm_places');
				$places     = $all_places->posts;
				ob_start();
				self::render_place_select_options($places);
				$options_html = ob_get_clean();
				wp_reset_postdata();
				wp_send_json_success(
					[
						'options' => $options_html,
					]
				);
			}
			public function ttbm_reload_place_you_see_list() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$ttbm_id = isset($_POST['ttbm_id']) ? absint(wp_unslash($_POST['ttbm_id'])) : 0;
				if (!$this->current_user_can_edit_tour($ttbm_id)) {
					wp_send_json_error(['message' => esc_html__('You do not have permission to access this tour.', 'tour-booking-manager')], 403);
				}
				$this->place_you_see($ttbm_id);
				die();
			}
		}
		new TTBM_Settings_place_you_see();
	}
