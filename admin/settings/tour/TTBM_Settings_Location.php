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

				add_action('ttbm_hiphop_place_map',[$this,'show_map_frontend']);
				add_action('ttbm_common_script',[$this,'osmap_script']);
			}

		public function osmap_script(){
			//openstreet map css
			wp_enqueue_style('ttbm_leaflet_style', TTBM_PLUGIN_URL . '/assets/osmap/leaflet.css', array(), time());
			wp_enqueue_style('fullScreen_style', TTBM_PLUGIN_URL . '/assets/osmap/Control.FullScreen.css', array(), time());
			wp_enqueue_style('autocomplete_style', TTBM_PLUGIN_URL . '/assets/osmap/autocomplete.min.css', array(), time());
			//openstreet map js
			wp_enqueue_script('ttbm_leaflet_script', TTBM_PLUGIN_URL . '/assets/osmap/leaflet.js', array('jquery'), time(), true);
			wp_enqueue_script('autocomplete_script', TTBM_PLUGIN_URL . '/assets/osmap/autocomplete.min.js', array('jquery'), time(), true);
			wp_enqueue_script('fullScreen_script', TTBM_PLUGIN_URL . '/assets/osmap/Control.FullScreen.js', array('jquery'), time(), true);
			
			$pro_key = TTBM_Function::get_general_settings('ttbm_gmap_api_key');
			$free_key = get_option('ttbm_google_map_settings');
			$api_key  = $free_key?$free_key['ttbm_gmap_api_key']:$pro_key;
			if (!empty($api_key)) {
				wp_enqueue_script( 'google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places&callback=initMap', [], null,true);
			}
			wp_localize_script('ttbm_leaflet_script', 'ttbm_map', array(
				'api_key'   => esc_attr($api_key),
			));
		}
		public function add_tab($tour_id){
			?>
			<li data-tabs-target="#ttbm_settings_location" class="ttbm_settings_location">
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
						$this->map_display($tour_id);						
					?>
				</div>	
			<?php
		}
		//*************location setup***********//
		
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

		public function show_map_frontend($tour_id) {

			$location_name = get_post_meta($tour_id, 'ttbm_full_location_name', true);
			$location_name = !empty($location_name) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';

			$latitude = get_post_meta($tour_id, 'ttbm_map_latitude', true);
			$latitude = !empty($latitude) ? $latitude : '40.712776'; // Default Latitude for New York

			$longitude = get_post_meta($tour_id, 'ttbm_map_longitude', true);
			$longitude = !empty($longitude) ? $longitude : '-74.005974';

			$map_settings = get_option('ttbm_google_map_settings'); 
			$gmap_api_key = isset($map_settings['ttbm_gmap_api_key']) ? $map_settings['ttbm_gmap_api_key'] : '';
			?>
			<div class="ttbm_default_widget" style="width: 100%; height: 400px;">
				<div id="<?php echo esc_attr($gmap_api_key? 'gmap_canvas':'osmap_canvas'); ?>" style="width: 100%; height:100%;" data-lati="<?php echo esc_attr($latitude); ?>" data-longdi="<?php echo esc_attr($longitude); ?>"></div>
			</div>	
			<?php
		}
		public function map_display($tour_id) {
			$location_name = get_post_meta($tour_id, 'ttbm_full_location_name', true);
			$location_name = !empty($location_name) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';

			$latitude = get_post_meta($tour_id, 'ttbm_map_latitude', true);
			$latitude = !empty($latitude) ? $latitude : '40.712776'; // Default Latitude for New York

			$longitude = get_post_meta($tour_id, 'ttbm_map_longitude', true);
			$longitude = !empty($longitude) ? $longitude : '-74.005974';

			$map_settings = get_option('ttbm_google_map_settings'); 
			$gmap_api_key = isset($map_settings['ttbm_gmap_api_key']) ? $map_settings['ttbm_gmap_api_key'] : '';
			?>
			
			<section>
				<label class="label">
					<div class="label-inner">
						<p><?php $gmap_api_key? esc_html_e('Google Map Location', 'tour-booking-manager'):esc_html_e('OSMap Location', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('full_location'); ?></span></i></p>
					</div>
					<div style="width: 80%;" class="auto-search-wrapper loupe">
						<input style="padding-left:30px" id="<?php echo esc_attr($gmap_api_key? 'ttbm_map_location':'ttbm_osmap_location'); ?>" name="ttbm_full_location_name" placeholder="<?php esc_html_e('Please type location...', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($location_name); ?>">
					</div>
				</label>
				<?php if(!$gmap_api_key): ?>
					<p class="text"><?php esc_html_e('To use google map, you have to add google map API key from', 'tour-booking-manager'); ?>
					<a href="<?php echo admin_url('edit.php?post_type=ttbm_tour&page=ttbm_settings_page'); ?>"><?Php esc_html_e('settings.','tour-booking-manager'); ?></a>
				</p>
				<?php endif; ?>
			</section>

			<section>
				<div id="<?php echo esc_attr($gmap_api_key? 'gmap_canvas':'osmap_canvas'); ?>" style="width: 100%; height: 400px;"></div>
				<div style="margin-top: 10px;">
					<?php esc_html_e('Latitude ', 'tour-booking-manager'); ?>
					<input type="text" id="map_latitude" name="ttbm_map_latitude" value="<?php echo esc_attr($latitude); ?>" >
					<?php esc_html_e('Longitude ', 'tour-booking-manager'); ?>
					<input type="text" id="map_longitude" name="ttbm_map_longitude" value="<?php echo esc_attr($longitude); ?>" >
				</div>
			</section>
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