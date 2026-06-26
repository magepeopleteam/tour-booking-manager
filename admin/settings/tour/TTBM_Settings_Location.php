<?php
	/**
	 * TTBM_Settings_Location class will represent the settings for the location
	 * @package TourBookingManager
	 * @since 1.8.5
	 * @version 1.0.0
	 * @category settings
	 * @author Shahadat Hossain <raselsha@gmail.com>
	 * @copyright 2025 magepeople
	 */
	if (!defined('ABSPATH'))
		exit;
	if (!class_exists('TTBM_Settings_Location')) {
		class TTBM_Settings_Location {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'location_tab_content'], 10, 1);
				//********Location************//
				add_action('wp_ajax_load_ttbm_location_form', [$this, 'load_ttbm_location_form']);
				add_action('wp_ajax_nopriv_load_ttbm_location_form', [$this, 'load_ttbm_location_form']);
				add_action('wp_ajax_ttbm_reload_location_list', [$this, 'ttbm_reload_location_list']);
				add_action('wp_ajax_nopriv_ttbm_reload_location_list', [$this, 'ttbm_reload_location_list']);
				/************add New location save********************/
				add_action('wp_ajax_ttbm_new_location_save', [$this, 'ttbm_new_location_save']);
				add_action('wp_ajax_nopriv_ttbm_new_location_save', [$this, 'ttbm_new_location_save']);
				add_action('ttbm_hiphop_place_map', [$this, 'show_map_frontend']);
				add_action('ttbm_common_script', [$this, 'osmap_script']);
			}
			private function current_user_can_manage_locations() {
				return current_user_can('manage_options');
			}
			private function current_user_can_edit_location_post($post_id) {
				return $post_id > 0 && current_user_can('edit_post', $post_id);
			}
			public function osmap_script() {
				$free_key = get_option('ttbm_google_map_settings');
				$pro_key  = TTBM_Function::get_general_settings('ttbm_gmap_api_key');
				$api_key  = (!empty($free_key['ttbm_gmap_api_key'])) ? $free_key['ttbm_gmap_api_key'] : $pro_key;

				if (!empty($api_key)) {
					wp_enqueue_script(
						'google-maps-api',
						'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places&callback=initMap',
						[],
						null,
						true
					);
				} else {
					wp_enqueue_style('autocomplete_style', TTBM_PLUGIN_URL . '/assets/osmap/autocomplete.min.css', array(), TTBM_PLUGIN_VERSION);
					wp_enqueue_script('autocomplete_script', TTBM_PLUGIN_URL . '/assets/osmap/autocomplete.min.js', array('jquery'), TTBM_PLUGIN_VERSION, true);
				}

				wp_localize_script(
					'ttbm_admin_script',
					'ttbm_map',
					array(
						'api_key' => esc_attr($api_key),
					)
				);
			}
			public function location_tab_content($tour_id) {
				$display_name = 'ttbm_display_location';
				$display      = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked      = $display === 'off' ? '' : 'checked';
				?>
                <div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_settings_location">
                    <h2><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Here you can set your tour locatoin Settings', 'tour-booking-manager'); ?></p>
                    <section>
                        <div class="ttbm-header ttbm-header--with-switch">
                            <h4><i class="fas fa-map-marker-alt"></i><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        </div>
                        <div class="ttbm-location-settings-body">
							<?php $this->location($tour_id); ?>
							<?php $this->map_enable($tour_id); ?>
                        </div>
						<?php $this->map_display($tour_id); ?>
                    </section>
					<?php do_action('ttbm_location_tab_bottom', $tour_id); ?>
					<?php self::add_new_location_popup(); ?>
                </div>
				<?php
			}
			//*************location setup***********//
			public function location($tour_id) {
				$display_name = 'ttbm_display_location';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$active = ($display == 'off') ? '' : 'mActive';
				?>
                <div class="<?php echo esc_attr($active); ?>" data-collapse="#ttbm_display_location">
                    <div class="label">
                        <div class="label-inner">
                            <p>
								<?php esc_html_e('Select Location', 'tour-booking-manager'); ?>
								<span class="ttbm-location-required-mark" style="color:#dc2626;font-weight:700;margin-left:3px;<?php echo $active ? '' : 'display:none;'; ?>" title="<?php esc_attr_e('Required', 'tour-booking-manager'); ?>">*</span>
								<i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Select Tour Location from this list', 'tour-booking-manager'); ?></span></i>
							</p>
                        </div>
						<div class="ttbm-location-select-wrap">
							<?php self::location_select($tour_id); ?>
							<p class="ttbm-location-hint"><?php esc_html_e("If your location isn't listed,", 'tour-booking-manager'); ?> <button type="button" class="ttbm-location-add-link" data-target-popup="add_new_location_popup"><?php esc_html_e('Add location', 'tour-booking-manager'); ?></button></p>
						</div>
                    </div>
					<p id="ttbm_location_error" style="display:none;color:#dc2626;font-size:12px;font-weight:500;margin:6px 0 0;">
						<span style="margin-right:4px;">&#9888;</span><?php esc_html_e('Please select a location before saving.', 'tour-booking-manager'); ?>
					</p>
                </div>
				<?php
			}
			public static function location_select($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$location_key = 'ttbm_location_name';
				} else {
					$location_key = 'ttbm_hotel_location';
				}
				$value = TTBM_Global_Function::get_post_info($tour_id, $location_key, array());
				$all_location = TTBM_Function::get_all_location();
				?>
                <select id="ttbm_location_select" name="<?php echo esc_attr($location_key); ?>">
					<option value=""><?php esc_html_e('— Select Location —', 'tour-booking-manager'); ?></option>
					<?php foreach ($all_location as $key => $location) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $value ? 'selected' : ''); ?>><?php echo esc_html($location); ?></option>
					<?php endforeach; ?>
                </select>
				<?php
			}
			public static function add_new_location_popup() {
				?>
                <div class="ttbm_popup ttbm-location-popup ttbm-place-popup" data-popup="add_new_location_popup">
                    <div class="popupMainArea">
                        <div class="popupHeader ttbm-location-popup__header">
							<div class="ttbm-location-popup__title-wrap">
								<h4 id="ttbm-location-popup-title">
									<i class="fas fa-map-marker-alt" aria-hidden="true"></i>
									<?php esc_html_e('Add New Location', 'tour-booking-manager'); ?>
								</h4>
								<p class="ttbm-location-popup__success ttbm_success_info _textSuccess_ml_dNone">
									<span class="fas fa-check-circle" aria-hidden="true"></span>
									<?php esc_html_e('Location is added successfully.', 'tour-booking-manager'); ?>
								</p>
							</div>
                            <button type="button" class="ttbm-location-popup__close popupClose" aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">
								<span class="fas fa-times" aria-hidden="true"></span>
							</button>
                        </div>
                        <div class="popupBody ttbm_location_form_area"></div>
                        <div class="popupFooter ttbm-location-popup__footer">
                            <p class="ttbm-location-save-error ttbm-location-popup-error" role="alert" style="display:none;"></p>
                            <div class="buttonGroup ttbm-location-popup__buttons">
                                <button class="ttbm-location-popup__btn ttbm-location-popup__btn--secondary ttbm_new_location_save_close" type="button"><?php esc_html_e('Cancel', 'tour-booking-manager'); ?></button>
                                <button class="ttbm-location-popup__btn ttbm-location-popup__btn--primary ttbm_new_location_save" type="button"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function load_ttbm_location_form() {
				if (!$this->current_user_can_manage_locations()) {
					wp_die(esc_html__('You do not have permission to access this form.', 'tour-booking-manager'), '', ['response' => 403]);
				}
				$all_countries = ttbm_get_coutnry_arr();
				wp_nonce_field('ttbm_add_new_location_popup', 'ttbm_add_new_location_popup');
				?>
				<div class="ttbm-location-popup-fields">
					<div class="ttbm-location-popup-field ttbm-location-popup-field--required">
						<label class="ttbm-location-popup-label" for="ttbm_new_location_name">
							<?php esc_html_e('Location Name', 'tour-booking-manager'); ?>
							<span class="ttbm-location-popup-required" aria-hidden="true">*</span>
						</label>
						<input type="text" id="ttbm_new_location_name" name="ttbm_new_location_name" class="formControl ttbm-location-popup-input" placeholder="<?php esc_attr_e('e.g. Paris, France', 'tour-booking-manager'); ?>" required>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_new_location_name'); ?></p>
						<p class="textRequired ttbm-location-popup-error" data-required="ttbm_new_location_name">
							<span class="fas fa-info-circle" aria-hidden="true"></span>
							<?php esc_html_e('Location name is required!', 'tour-booking-manager'); ?>
						</p>
					</div>
					<div class="ttbm-location-popup-field">
						<label class="ttbm-location-popup-label" for="ttbm_location_description">
							<?php esc_html_e('Location Description', 'tour-booking-manager'); ?>
						</label>
						<textarea id="ttbm_location_description" name="ttbm_location_description" class="formControl ttbm-location-popup-input ttbm-location-popup-textarea" rows="3" placeholder="<?php esc_attr_e('Enter a short description...', 'tour-booking-manager'); ?>"></textarea>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_location_description'); ?></p>
					</div>
					<div class="ttbm-location-popup-field">
						<label class="ttbm-location-popup-label" for="ttbm_location_address">
							<?php esc_html_e('Location Address', 'tour-booking-manager'); ?>
						</label>
						<textarea id="ttbm_location_address" name="ttbm_location_address" class="formControl ttbm-location-popup-input ttbm-location-popup-textarea" rows="3" placeholder="<?php esc_attr_e('Enter full address...', 'tour-booking-manager'); ?>"></textarea>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_location_address'); ?></p>
					</div>
					<div class="ttbm-location-popup-field">
						<label class="ttbm-location-popup-label" for="ttbm_location_country">
							<?php esc_html_e('Location Country', 'tour-booking-manager'); ?>
						</label>
						<div class="ttbm-location-popup-select-wrap">
							<select id="ttbm_location_country" class="formControl ttbm-location-popup-input ttbm-location-popup-select" name="ttbm_location_country">
								<?php foreach ($all_countries as $key => $country) : ?>
									<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($country); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_location_country'); ?></p>
					</div>
					<div class="ttbm-location-popup-field ttbm-location-popup-field--required">
						<label class="ttbm-location-popup-label">
							<?php esc_html_e('Location Image', 'tour-booking-manager'); ?>
							<span class="ttbm-location-popup-required" aria-hidden="true">*</span>
						</label>
						<div class="ttbm-location-popup-image">
							<?php TTBM_Layout::single_image_button('ttbm_location_image'); ?>
						</div>
						<p class="ttbm-location-popup-hint"><?php TTBM_Settings::des_p('ttbm_location_image'); ?></p>
						<p class="textRequired ttbm-location-popup-error" data-required="ttbm_location_image">
							<span class="fas fa-info-circle" aria-hidden="true"></span>
							<?php esc_html_e('Location image is required!', 'tour-booking-manager'); ?>
						</p>
					</div>
				</div>
				<?php
				die();
			}
			public function ttbm_reload_location_list() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$ttbm_id = isset($_POST['ttbm_id']) ? absint(wp_unslash($_POST['ttbm_id'])) : 0;
				if (!$this->current_user_can_edit_location_post($ttbm_id)) {
					wp_send_json_error(['message' => esc_html__('You do not have permission to access this location.', 'tour-booking-manager')], 403);
				}
				self::location_select($ttbm_id);
				die();
			}
			public function show_map_frontend($tour_id) {
				$location_name = get_post_meta($tour_id, 'ttbm_full_location_name', true);
				$location_name = !empty($location_name) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';
				$latitude      = get_post_meta($tour_id, 'ttbm_map_latitude', true);
				$latitude      = !empty($latitude)  ? $latitude  : '40.712776';
				$longitude     = get_post_meta($tour_id, 'ttbm_map_longitude', true);
				$longitude     = !empty($longitude) ? $longitude : '-74.005974';
				$map_settings  = get_option('ttbm_google_map_settings');
				$gmap_api_key  = isset($map_settings['ttbm_gmap_api_key']) ? $map_settings['ttbm_gmap_api_key'] : '';
				$display_map   = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_map', 'on');

				if ($display_map !== 'on') return;
				?>
				<h2 class="content-title"><?php esc_html_e('Location', 'tour-booking-manager'); ?></h2>

				<div style="width:100%;height:400px;margin:20px 0;border-radius:10px;overflow:hidden;">
					<?php if ($gmap_api_key) : ?>
						<!-- Google Maps JavaScript API (API key available) -->
						<div id="gmap_canvas"
							style="width:100%;height:100%;"
							data-lati="<?php echo esc_attr($latitude); ?>"
							data-longdi="<?php echo esc_attr($longitude); ?>"
							data-location="<?php echo esc_attr($location_name); ?>">
						</div>
					<?php else : ?>
						<!-- Google Maps iframe fallback (no API key required) -->
						<iframe
							src="<?php echo esc_url('https://maps.google.com/maps?q=' . rawurlencode($location_name) . '&z=13&ie=UTF8&iwloc=&output=embed'); ?>"
							width="100%"
							height="100%"
							frameborder="0"
							scrolling="no"
							marginheight="0"
							marginwidth="0"
							loading="lazy"
							referrerpolicy="no-referrer-when-downgrade"
							allowfullscreen
							style="border:0;display:block;">
						</iframe>
					<?php endif; ?>
				</div>
				<?php
			}
			public function map_enable($tour_id) {
				$display_location = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_location', 'on');
				$display_map      = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_map', 'on');
				$checked          = ( $display_map === 'off' ) ? '' : 'checked';
				$hidden_style     = ( $display_location === 'off' ) ? ' style="display:none;"' : '';
				?>
                <div class="ttbm-map-enable-wrap"<?php echo $hidden_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <label class="label">
                        <div class="label-inner">
                            <p><?php esc_html_e('Enable/Disable Map Location', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('To show Tour Location on Map enable It.', 'tour-booking-manager'); ?></span></i></p>
                        </div>
						<?php TTBM_Custom_Layout::switch_button('ttbm_display_map', $checked); ?>
                    </label>
                </div>
				<?php
			}
			public function map_display($tour_id) {
				$location_name = get_post_meta($tour_id, 'ttbm_full_location_name', true);
				$location_name = !empty($location_name) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';
				$latitude      = get_post_meta($tour_id, 'ttbm_map_latitude', true);
				$latitude      = !empty($latitude)  ? $latitude  : '40.712776';
				$longitude     = get_post_meta($tour_id, 'ttbm_map_longitude', true);
				$longitude     = !empty($longitude) ? $longitude : '-74.005974';
				$map_settings  = get_option('ttbm_google_map_settings');
				$gmap_api_key  = isset($map_settings['ttbm_gmap_api_key']) ? $map_settings['ttbm_gmap_api_key'] : '';
				$display_map   = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_map', 'on');
				$active        = $display_map == 'off' ? '' : 'mActive';
				$settings_url  = admin_url('edit.php?post_type=ttbm_tour&page=ttbm_settings_page');
				$iframe_src    = 'https://maps.google.com/maps?q=' . rawurlencode($location_name) . '&z=14&output=embed';
				?>
                <div class="<?php echo esc_attr($active); ?>" data-collapse="#ttbm_display_map">

                    <!-- Address input -->
                    <label class="label">
                        <div class="label-inner">
                            <p>
								<?php esc_html_e('Google Map Location', 'tour-booking-manager'); ?>
								<i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('full_location'); ?></span></i>
							</p>
                        </div>
                        <div style="width:80%;" class="auto-search-wrapper loupe">
                            <input
                                style="padding-left:30px"
                                id="<?php echo esc_attr($gmap_api_key ? 'ttbm_map_location' : 'ttbm_iframe_location'); ?>"
                                name="ttbm_full_location_name"
                                placeholder="<?php esc_attr_e('Please type location...', 'tour-booking-manager'); ?>"
                                value="<?php echo esc_attr($location_name); ?>"
                            >
                        </div>
                    </label>

                    <!-- Map display -->
                    <div style="width:100%;margin-top:12px;">
						<?php if ($gmap_api_key) : ?>
                            <!-- Google Maps JavaScript API -->
                            <div id="gmap_canvas" style="width:100%;height:400px;border-radius:8px;overflow:hidden;"></div>
						<?php else : ?>
                            <!-- Google Maps iframe fallback (no API key) -->
                            <div style="border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;">
                                <iframe
                                    id="ttbm_gmap_iframe"
                                    width="100%"
                                    height="400"
                                    frameborder="0"
                                    scrolling="no"
                                    marginheight="0"
                                    marginwidth="0"
                                    src="<?php echo esc_url($iframe_src); ?>"
                                    style="border:0;display:block;"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    allowfullscreen>
                                </iframe>
                            </div>
                            <p style="font-size:12px;color:#6b7280;margin:8px 0 0;line-height:1.6;">
                                <span style="color:#2271b1;margin-right:4px;">&#9432;</span>
								<?php esc_html_e('Type an address to update the map pointer automatically.', 'tour-booking-manager'); ?>
                                <a href="<?php echo esc_url($settings_url); ?>" style="color:#2271b1;font-weight:500;">
									<?php esc_html_e('Add a Google Maps API key', 'tour-booking-manager'); ?>
                                </a>
								<?php esc_html_e('for Google Places autocomplete and draggable markers.', 'tour-booking-manager'); ?>
                            </p>
						<?php endif; ?>
                    </div>

                    <!-- Lat / Lng fields — hidden, values preserved for saving -->
                    <div style="display:none;">
                        <input type="text" id="map_latitude" name="ttbm_map_latitude" value="<?php echo esc_attr($latitude); ?>">
                        <input type="text" id="map_longitude" name="ttbm_map_longitude" value="<?php echo esc_attr($longitude); ?>">
                    </div>

                </div>
				<?php
			}
			public function ttbm_new_location_save() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				if (!$this->current_user_can_manage_locations()) {
					wp_send_json_error(['message' => esc_html__('You do not have permission to create locations.', 'tour-booking-manager')], 403);
				}
				$name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
				$description = isset($_POST['description']) ? sanitize_text_field(wp_unslash($_POST['description'])) : '';
				$address = isset($_POST['address']) ? sanitize_text_field(wp_unslash($_POST['address'])) : '';
				$country = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';
				$image = isset($_POST['image']) ? sanitize_text_field(wp_unslash($_POST['image'])) : '';
				$query = wp_insert_term($name,   // the term
					'ttbm_tour_location', // the taxonomy
					array('description' => $description));
                
				if (is_array($query) && $query['term_id'] != '') {
					$term_id = $query['term_id'];
					update_term_meta($term_id, 'ttbm_location_address', $address);
					update_term_meta($term_id, 'ttbm_country_location', $country);
					update_term_meta($term_id, 'ttbm_location_image', $image);
				}
				die();
			}
		}
		new TTBM_Settings_Location();
	}
