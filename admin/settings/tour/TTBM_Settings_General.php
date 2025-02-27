<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists('TTBM_Settings_General') ) {
		class TTBM_Settings_General {
			public function __construct() {
				add_action('add_ttbm_settings_tab_content', [$this, 'general_settings']);
				add_action('ttbm_settings_save', [$this, 'save_general_settings']);
				//********Location************//
				add_action('wp_ajax_load_ttbm_location_form', [$this, 'load_ttbm_location_form']);
				add_action('wp_ajax_nopriv_load_ttbm_location_form', [$this, 'load_ttbm_location_form']);
				add_action('wp_ajax_ttbm_reload_location_list', [$this, 'ttbm_reload_location_list']);
				add_action('wp_ajax_nopriv_ttbm_reload_location_list', [$this, 'ttbm_reload_location_list']);
				/************add New location save********************/
				add_action('wp_ajax_ttbm_new_location_save', [$this, 'ttbm_new_location_save']);
				add_action('wp_ajax_nopriv_ttbm_new_location_save', [$this, 'ttbm_new_location_save']);
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
						
						$this->location($tour_id);
						$this->full_location($tour_id);
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

			
			public function full_location($tour_id) {
				$location_name = get_post_meta($tour_id, 'ttbm_full_location_name', true);
				$location_name = $location_name?$location_name:'650 Manchester Road, New York, NY 10007, USA';
				$latitude = get_post_meta($tour_id, 'ttbm_map_latitude', true);
				$latitude = $latitude?$latitude:'';
				$longitude = get_post_meta($tour_id, 'ttbm_map_longitude', true);
				$longitude = $longitude?$longitude:'';
				
				?>
				

				<section>
					<label class="label">
						<div class="label-inner">
						<p><?php esc_html_e('Google Map Location ', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('full_location'); ?></span></i></p>
						</div>
						<input style="width: 80%;" id="full_location" name="ttbm_full_location_name" placeholder="<?php esc_html_e('Please type location...', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($location_name); ?>">
					</label>
				</section>

				<section>
					<div id="map_canvas" style="width: 100%; height: 300px;"></div>
					<div style="margin-top: 10px;">
						<?php esc_html_e('Latitude ', 'tour-booking-manager'); ?>
						<input type="text" id="map_latitude" name="ttbm_map_latitude" value="<?php echo esc_attr($latitude); ?>" >
						<?php esc_html_e('Longitude ', 'tour-booking-manager'); ?>
						<input type="text" id="map_longitude" name="ttbm_map_longitude" value="<?php echo esc_attr($longitude); ?>" >
					</div>
				</section>
				<script>
					let map, marker, autocomplete, geocoder;

					function initMap() {
						let lati = parseFloat(document.getElementById('map_latitude').value);
						let longdi = parseFloat(document.getElementById('map_longitude').value);

						// Initialize the map
						map = new google.maps.Map(document.getElementById('map_canvas'), {
							center: { lat: lati, lng: longdi },
							zoom: 12,
							zoomControl: true,
							streetViewControl: false,
							mapTypeControl: false,
							scaleControl: true
						});

						// Initialize the marker
						marker = new google.maps.Marker({
							position: { lat: lati, lng: longdi },
							map: map,
							title: "Selected Location",
							draggable: true
						});

						// Initialize the geocoder for reverse geocoding
						geocoder = new google.maps.Geocoder();

						// Update latitude and longitude when the marker is dragged
						marker.addListener("dragend", function (event) {
							document.getElementById('map_latitude').value = event.latLng.lat();
							document.getElementById('map_longitude').value = event.latLng.lng();
							reverseGeocode(event.latLng); // Update location name when dragging the marker
						});

						// Initialize Autocomplete for the address input field
						autocomplete = new google.maps.places.Autocomplete(document.getElementById("full_location"));
						autocomplete.addListener("place_changed", onPlaceChanged);

						// Add a click event listener to the map
						map.addListener("click", function(event) {
							let clickedLatLng = event.latLng;

							// Move the marker to the clicked position
							marker.setPosition(clickedLatLng);

							// Update the latitude and longitude input fields
							document.getElementById('map_latitude').value = clickedLatLng.lat();
							document.getElementById('map_longitude').value = clickedLatLng.lng();

							// Reverse geocode the clicked position to get the address
							reverseGeocode(clickedLatLng);
						});
					}

					function enableEditing() {
						// Allow the user to edit the location name when clicked
						document.getElementById('full_location').readOnly = false;
						document.getElementById('save_location_name').style.display = 'inline-block'; // Show save button
					}

					function saveLocationName() {
						// Disable editing after saving the location name
						document.getElementById('full_location').readOnly = true;
						document.getElementById('save_location_name').style.display = 'none'; // Hide save button

						// You may want to send the updated location name to the server or process it here
						// For example:
						console.log('New Location Name: ' + document.getElementById('full_location').value);
					}

					function reverseGeocode(latLng) {
						geocoder.geocode({ 'location': latLng }, function(results, status) {
							if (status === google.maps.GeocoderStatus.OK) {
								if (results[0]) {
									// Set the location name from the geocode results
									document.getElementById('full_location').value = results[0].formatted_address;
								} else {
									console.error("No results found for the given location.");
								}
							} else {
								console.error("Geocoder failed due to: " + status);
							}
						});
					}

					function onPlaceChanged() {
						let place = autocomplete.getPlace();

						if (!place.geometry) {
							console.error("No details available for the selected place.");
							return;
						}

						let location = place.geometry.location;
						let lat = location.lat();
						let lng = location.lng();

						map.setCenter(location);
						marker.setPosition(location);
						document.getElementById("map_latitude").value = lat;
						document.getElementById("map_longitude").value = lng;

						reverseGeocode(location);
					}
					</script>
				<?php
			}
			
			
			//*************location setup***********//
			public function location($tour_id) {
				$display_name = 'ttbm_display_location';
				$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';

				?>
				<section class="bg-light" style="margin-top: 20px;">
					<label class="label">
						<div class="label-inner">
							<p><?php _e('Location Settings','tour-booking-manager'); ?></p>
							<span class="text"><?php _e('Here you can set tour location, place, map etc.','tour-booking-manager'); ?></span>
						</div>
					</label>
				</section>
				<section>
					<div class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Tour Location', 'tour-booking-manager'); ?> </p>
							<span class="text"><?php TTBM_Settings::des_p('location'); ?></span>
						</div>
						<div class="_dFlex_alignCenter_justifyBetween">
							<div class="me-2"><?php MP_Custom_Layout::popup_button_xs('add_new_location_popup', esc_html__('', 'tour-booking-manager')); ?></div>
							<?php MP_Custom_Layout::switch_button($display_name, $checked); ?>
							<?php self::location_select($tour_id); ?>
						</div>
					</div>
				</section>
				<?php
				self::add_new_location_popup();
			}
			public static function location_select($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$location_key = 'ttbm_location_name';
				} else {
					$location_key = 'ttbm_hotel_location';
				}
				$value = MP_Global_Function::get_post_info($tour_id, $location_key, array());
				$all_location = TTBM_Function::get_all_location();
				?>
				<select class="rounded ms-2" name="<?php echo esc_attr($location_key); ?>">
					<?php foreach ($all_location as $key => $location) : ?>
						<option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($key == $value ? 'selected' : ''); ?>><?php echo esc_html($location); ?></option>
					<?php endforeach; ?>
				</select>
				<?php
			}
			public static function add_new_location_popup() {
				?>
				<div class="mpPopup" data-popup="add_new_location_popup">
					<div class="popupMainArea">
						<div class="popupHeader">
							<h4 class="text-primary">
								<?php esc_html_e('Add New Location', 'tour-booking-manager'); ?>
								<p class="_textSuccess_ml_dNone ttbm_success_info">
									<span class="fas fa-check-circle mR_xs text-primary"></span>
									<?php esc_html_e('Location is added successfully.', 'tour-booking-manager') ?>
								</p>
							</h4>
							<span class="fas fa-times popupClose"></span>
						</div>
						<div class="popupBody ttbm_location_form_area">
						</div>
						<div class="popupFooter">
							<div class="buttonGroup">
								<button class="btn ttbm_new_location_save" type="button"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
								<button class="_warningButton ttbm_new_location_save_close" type="button"><?php esc_html_e('Save & Close', 'tour-booking-manager'); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			public function load_ttbm_location_form() {
				$all_countries = ttbm_get_coutnry_arr();
				?>
				<label class="flexEqual">
					<span><?php esc_html_e('Location Name : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span>
					<input type="text" name="ttbm_new_location_name" class="formControl" required>
				</label>
				<p class="textRequired" data-required="ttbm_new_location_name">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Location name is required!', 'tour-booking-manager'); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_new_location_name'); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e('Location Description : ', 'tour-booking-manager'); ?></span>
					<textarea name="ttbm_location_description" class="formControl" rows="3"></textarea>
				</label>
				<?php TTBM_Settings::des_p('ttbm_location_description'); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e('Location Address : ', 'tour-booking-manager'); ?></span>
					<textarea name="ttbm_location_address" class="formControl" rows="3"></textarea>
				</label>
				<?php TTBM_Settings::des_p('ttbm_location_address'); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e('Location Country : ', 'tour-booking-manager'); ?></span>
					<select class="formControl" name="ttbm_location_country>">
						<?php foreach ($all_countries as $key => $country) { ?>
							<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($country); ?></option>
						<?php } ?>
					</select>
				</label>
				<?php TTBM_Settings::des_p('ttbm_location_country'); ?>
				<div class="divider"></div>
				<div class="flexEqual">
					<span><?php esc_html_e('Location Image : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span>
					<?php TTBM_Layout::single_image_button('ttbm_location_image'); ?>
				</div>
				<p class="textRequired" data-required="ttbm_location_image">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Location image is required!', 'tour-booking-manager'); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_location_image'); ?>
				<?php
				die();
			}
			public function ttbm_reload_location_list() {
				$ttbm_id = MP_Global_Function::data_sanitize($_POST['ttbm_id']);
				self::location_select($ttbm_id);
				die();
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
					$ttbm_display_map = MP_Global_Function::get_submit_info('ttbm_display_map') ? 'on' : 'off';
					$ttbm_full_location_name = MP_Global_Function::get_submit_info('ttbm_full_location_name');
					update_post_meta($tour_id, 'ttbm_display_map', $ttbm_display_map);
					update_post_meta($tour_id, 'ttbm_full_location_name', $ttbm_full_location_name);
					/***************/
					$map_latitude = MP_Global_Function::get_submit_info('ttbm_map_latitude');
					$map_longitude = MP_Global_Function::get_submit_info('ttbm_map_longitude');
					update_post_meta($tour_id, 'ttbm_map_latitude', $map_latitude);
					update_post_meta($tour_id, 'ttbm_map_longitude', $map_longitude);
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
			public function ttbm_new_location_save() {
				$name = MP_Global_Function::data_sanitize($_POST['name']);
				$description = MP_Global_Function::data_sanitize($_POST['description']);
				$address = MP_Global_Function::data_sanitize($_POST['address']);
				$country = MP_Global_Function::data_sanitize($_POST['country']);
				$image = MP_Global_Function::data_sanitize($_POST['image']);
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
		new TTBM_Settings_General();
	}