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

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'TTBM_Settings_Location' ) ){
	class TTBM_Settings_Location{
		public function __construct(){
				add_action('ttbm_meta_box_tab_name', [$this, 'add_tab'], 10);
				add_action('add_ttbm_settings_tab_content', [$this, 'location_tab_content'], 10, 1);
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

		public function add_tab($tour_id){
			?>
			<li data-tabs-target="#ttbm_settings_location">
				<i class="fas fa-map-marker-alt"></i><?php esc_html_e(' Location', 'tour-booking-manager'); ?>
			</li>
			<?php
		}

		public function location_tab_content($tour_id){
			?>
			<div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_settings_location">
					<h2><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></h2>
					<p><?php esc_html_e('Here you can set your tour locatoin Settings', 'tour-booking-manager'); ?></p>
					<section class="bg-light">
						<label class="label">
							<div class="label-inner">
								<p><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></p>
								<span class="text"><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></span>
							</div>
						</label>
					</section>
					<?php 
						$this->location($tour_id);

						$map_settings = get_option('ttbm_google_map_settings'); // Get the entire settings array
						$gmap_api_key = isset($map_settings['ttbm_gmap_api_key']) ? $map_settings['ttbm_gmap_api_key'] : '';

						if($gmap_api_key){
							$this->google_map_display($tour_id);
						}else{
							$this->open_street_map_display($tour_id);
						}
						
					?>
				</div>	
			<?php
		}
		//*************location setup***********//
		public function open_street_map_display($tour_id){
		?>
			<section>
				<div class="auto-search-wrapper  loupe">
					<input type="text" autocomplete="off" id="search" style="width:100%;padding-left:32px;" class="" placeholder="enter the city name" />
				</div>
				<div id="map" class="map"></div>
			</section>
			<script>
				document.addEventListener("DOMContentLoaded", function () {
				new Autocomplete("search", {
					selectFirst: true,
					insertToInput: true,
					cache: true,
					howManyCharacters: 2,

					// Fetch results from Nominatim API
					onSearch: ({ currentValue }) => {
						const api = `https://nominatim.openstreetmap.org/search?format=geojson&limit=5&q=${encodeURIComponent(currentValue)}`;
						return fetch(api)
							.then((response) => response.json())
							.then((data) => data.features)
							.catch((error) => console.error("Error:", error));
									},
							onResults: ({ currentValue, matches, template }) => {
								const regex = new RegExp(currentValue, "gi");
								return matches.length === 0
									? template('<li>No results found</li>')
									: matches
										.map((element) => {
											return `
											<li>
												<p>${element.properties.display_name.replace(regex, "<b>$&</b>")}</p>
											</li>`;
										})
										.join("");
							},

							// Handle selected location
							onSubmit: ({ object }) => {
								map.eachLayer((layer) => {
									if (layer instanceof L.Marker) {
										map.removeLayer(layer);
									}
								});

								const { display_name } = object.properties;
								const [lng, lat] = object.geometry.coordinates;

								const marker = L.marker([lat, lng], { title: display_name });
								marker.addTo(map).bindPopup(display_name);
								map.setView([lat, lng], 8);
							},

							// Handle no results
							noResults: ({ currentValue, template }) => template(`<li>No results found for "${currentValue}"</li>`),
						});

						// OpenStreetMap Configuration
						const config = { minZoom: 4, maxZoom: 18 };
						const zoom = 3;
						const lat = 10.531020008464989;
						const lng = 78.22265625000001;

						const map = L.map("map", config).setView([lat, lng], zoom);

						// Add fullscreen control
						const fsControl = L.control.fullscreen();
						map.addControl(fsControl);

						map.on("enterFullscreen", () => console.log("Enter Fullscreen"));
						map.on("exitFullscreen", () => console.log("Exit Fullscreen"));

						// Click event to get Lat/Lng
						map.on("click", (e) => {
							alert("Lat, Lon: " + e.latlng.lat + ", " + e.latlng.lng);
						});

						// Add OSM tile layer
						L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
							attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
						}).addTo(map);
					});
			</script>
		<?php
		}

		public function location($tour_id) {
			$display_name = 'ttbm_display_location';
			$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'on');
			$checked = $display == 'off' ? '' : 'checked';

			?>
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

		public function google_map_display($tour_id) {
			$location_name = get_post_meta($tour_id, 'ttbm_full_location_name', true);
			$location_name = !empty($location_name) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';

			$latitude = get_post_meta($tour_id, 'ttbm_map_latitude', true);
			$latitude = !empty($latitude) ? $latitude : '40.712776'; // Default Latitude for New York

			$longitude = get_post_meta($tour_id, 'ttbm_map_longitude', true);
			$longitude = !empty($longitude) ? $longitude : '-74.005974';
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

		public function save_general_settings($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					
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
	new TTBM_Settings_Location();
}