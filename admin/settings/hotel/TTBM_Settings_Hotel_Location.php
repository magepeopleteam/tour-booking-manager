<?php
	/**
	 * TTBM_Settings_Hotel_Location class will represent the settings for the location
	 * @package TourBookingManager
	 * @since 1.8.5
	 * @version 1.0.0
	 * @category settings
	 * @author Shahadat Hossain <raselsha@gmail.com>
	 * @copyright 2025 magepeople
	 */
	if (!defined('ABSPATH'))
		exit;
	if (!class_exists('TTBM_Settings_Hotel_Location')) {
		class TTBM_Settings_Hotel_Location {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'location_tab_content'], 10, 1);
				//********Location************//
				/************add New location save********************/
				add_action('ttbm_single_hotel_location', [$this, 'show_map_frontend']);
				add_action('ttbm_common_script', [$this, 'osmap_script']);
			}
			public function osmap_script() {
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
				$api_key = $free_key ? $free_key['ttbm_gmap_api_key'] : $pro_key;
				if (!empty($api_key)) {
					wp_enqueue_script('google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places&callback=initMap', [], null, true);
				}
				wp_localize_script('ttbm_leaflet_script', 'ttbm_map', array(
					'api_key' => esc_attr($api_key),
				));
			}
			public function location_tab_content($tour_id) {
				?>
                <div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_settings_hotel_location">
                    <h2><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Here you can set your tour locatoin Settings', 'tour-booking-manager'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-map-marker-alt"></i><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></h4>
							<?php $this->map_enable($tour_id); ?>
						</div>
                        
						<?php $this->map_display($tour_id); ?>
                    </section>
                </div>
				<?php
			}
			//*************location setup***********//
			public function location_enable($tour_id) {
				$display_name = 'ttbm_display_location';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <div class="label">
                    <div class="label-inner">
                        <p><?php esc_html_e('Location Enable/Disable', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Show/Hide location in frontend', 'tour-booking-manager'); ?></span></i></p>
                    </div>
					<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                </div>
				<?php
			}
			
			public function show_map_frontend() {
				$tour_id = get_the_ID();
				$location_name = get_post_meta($tour_id, 'ttbm_hotel_map_location', true);
				$location_name = !empty($location_name) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';
				$latitude = get_post_meta($tour_id, 'ttbm_map_latitude', true);
				$latitude = !empty($latitude) ? $latitude : '40.712776'; // Default Latitude for New York
				$longitude = get_post_meta($tour_id, 'ttbm_map_longitude', true);
				$longitude = !empty($longitude) ? $longitude : '-74.005974';
				$map_settings = get_option('ttbm_google_map_settings');
				$gmap_api_key = isset($map_settings['ttbm_gmap_api_key']) ? $map_settings['ttbm_gmap_api_key'] : '';
				$display_map = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotel_map', 'on');
				if ($display_map == 'on'):
					?>
                    <div class="map-container" style="width: 100%; height: 250px;">
                        <div id="<?php echo esc_attr($gmap_api_key ? 'gmap_canvas' : 'osmap_canvas'); ?>" style="width: 100%; height:100%;" data-lati="<?php echo esc_attr($latitude); ?>" data-longdi="<?php echo esc_attr($longitude); ?>" data-location="<?php echo esc_attr($location_name); ?>"></div>
                    </div>
				<?php
				endif;
			}
			public function map_enable($tour_id) {
				$display_map = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotel_map', 'on');
				$checked = $display_map == 'off' ? '' : 'checked';
				?>
                <?php TTBM_Custom_Layout::switch_button('ttbm_display_hotel_map', $checked); ?>
				<?php
			}
			public function map_display($tour_id) {
				$location_name = get_post_meta($tour_id, 'ttbm_hotel_map_location', true);
				$location_name = !empty($location_name) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';
				$latitude = get_post_meta($tour_id, 'ttbm_map_latitude', true);
				$latitude = !empty($latitude) ? $latitude : '40.712776'; // Default Latitude for New York
				$longitude = get_post_meta($tour_id, 'ttbm_map_longitude', true);
				$longitude = !empty($longitude) ? $longitude : '-74.005974';
				$map_settings = get_option('ttbm_google_map_settings');
				$gmap_api_key = isset($map_settings['ttbm_gmap_api_key']) ? $map_settings['ttbm_gmap_api_key'] : '';
				$display_map = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotel_map', 'on');
				$active = $display_map == 'off' ? '' : 'mActive';
				?>
                <div class="<?php echo esc_attr($active); ?>" data-collapse="#<?php echo esc_attr('ttbm_display_hotel_map'); ?>">
                    <label class="label">
                        <div class="label-inner">
                            <p><?php $gmap_api_key ? esc_html_e('Google Map Location', 'tour-booking-manager') : esc_html_e('OSMap Location', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('full_location'); ?></span></i></p>
                        </div>
                        <div style="width: 80%;" class="auto-search-wrapper loupe">
                            <input style="padding-left:30px" id="<?php echo esc_attr($gmap_api_key ? 'ttbm_map_location' : 'ttbm_osmap_location'); ?>" name="ttbm_hotel_map_location" placeholder="<?php esc_html_e('Please type location...', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($location_name); ?>">
                        </div>
                    </label>
                    <div class="label">
						<?php if (!$gmap_api_key): ?>
                            <div class="label-inner">
                                <p><?php esc_html_e('To use google map, you have to add google map API key from', 'tour-booking-manager'); ?>
                                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_settings_page')); ?>"><?Php esc_html_e('settings.', 'tour-booking-manager'); ?></a>
                                </p>
                            </div>
						<?php endif; ?>
                    </div>
                    <div class="label">
                        <div style="width: 100%;">
                            <div id="<?php echo esc_attr($gmap_api_key ? 'gmap_canvas' : 'osmap_canvas'); ?>" style="width: 100%; height: 400px;"></div>
                            <div style="margin-top: 10px;">
								<?php esc_html_e('Latitude ', 'tour-booking-manager'); ?>
                                <input type="text" id="map_latitude" name="ttbm_map_latitude" value="<?php echo esc_attr($latitude); ?>">
								<?php esc_html_e('Longitude ', 'tour-booking-manager'); ?>
                                <input type="text" id="map_longitude" name="ttbm_map_longitude" value="<?php echo esc_attr($longitude); ?>">
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}

		}
		new TTBM_Settings_Hotel_Location();
	}