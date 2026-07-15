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
				$free_key = get_option( 'ttbm_google_map_settings' );
				$pro_key  = TTBM_Function::get_general_settings( 'ttbm_gmap_api_key' );
				$api_key  = ( ! empty( $free_key['ttbm_gmap_api_key'] ) ) ? $free_key['ttbm_gmap_api_key'] : $pro_key;

				if ( ! empty( $api_key ) ) {
					wp_enqueue_script(
						'google-maps-api',
						'https://maps.googleapis.com/maps/api/js?key=' . esc_attr( $api_key ) . '&libraries=places&callback=initMap',
						array(),
						null,
						true
					);
				} else {
					wp_enqueue_style( 'autocomplete_style', TTBM_PLUGIN_URL . '/assets/osmap/autocomplete.min.css', array(), TTBM_PLUGIN_VERSION );
					wp_enqueue_script( 'autocomplete_script', TTBM_PLUGIN_URL . '/assets/osmap/autocomplete.min.js', array( 'jquery' ), TTBM_PLUGIN_VERSION, true );
				}

				wp_localize_script(
					'ttbm_admin_script',
					'ttbm_map',
					array(
						'api_key' => esc_attr( $api_key ),
					)
				);
			}
			public function location_tab_content($tour_id) {
				$display_map = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotel_map', 'on');
				$active = $display_map == 'off' ? '' : 'mActive';
				?>
                <div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_settings_hotel_location">
                    <h2><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Here you can set your tour locatoin Settings', 'tour-booking-manager'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-map-marker-alt"></i><?php esc_html_e('Location Settings', 'tour-booking-manager'); ?></h4>
							<?php $this->map_enable($tour_id); ?>
						</div>
                        <div class="<?php echo esc_attr($active); ?>" data-collapse="#<?php echo esc_attr('ttbm_display_hotel_map'); ?>">
							<?php $this->map_display($tour_id); ?>
						</div>
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
				$tour_id       = get_the_ID();
				$location_name = get_post_meta( $tour_id, 'ttbm_hotel_map_location', true );
				$location_name = ! empty( $location_name ) ? $location_name : '650 Manchester Road, New York, NY 10007, USA';
				$latitude      = get_post_meta( $tour_id, 'ttbm_map_latitude', true );
				$latitude      = ! empty( $latitude ) ? $latitude : '40.712776';
				$longitude     = get_post_meta( $tour_id, 'ttbm_map_longitude', true );
				$longitude     = ! empty( $longitude ) ? $longitude : '-74.005974';
				$map_settings  = get_option( 'ttbm_google_map_settings' );
				$gmap_api_key  = isset( $map_settings['ttbm_gmap_api_key'] ) ? $map_settings['ttbm_gmap_api_key'] : '';
				$display_map   = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_hotel_map', 'on' );
				if ( 'on' !== $display_map ) {
					return;
				}
				?>
				<div class="map-container" style="width: 100%; height: 250px;">
					<?php if ( $gmap_api_key ) : ?>
						<div id="gmap_canvas" style="width: 100%; height:100%;" data-lati="<?php echo esc_attr( $latitude ); ?>" data-longdi="<?php echo esc_attr( $longitude ); ?>" data-location="<?php echo esc_attr( $location_name ); ?>"></div>
					<?php else : ?>
						<iframe
							src="<?php echo esc_url( 'https://maps.google.com/maps?q=' . rawurlencode( $location_name ) . '&z=14&output=embed' ); ?>"
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
				$display_map = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotel_map', 'on');
				$checked = $display_map == 'off' ? '' : 'checked';
				?>
                <?php TTBM_Custom_Layout::switch_button('ttbm_display_hotel_map', $checked); ?>
				<?php
			}
			public function map_display( $tour_id ) {
				$location_name = (string) get_post_meta( $tour_id, 'ttbm_hotel_map_location', true );
				$latitude      = (string) get_post_meta( $tour_id, 'ttbm_map_latitude', true );
				$longitude     = (string) get_post_meta( $tour_id, 'ttbm_map_longitude', true );
				$map_query     = $location_name !== '' ? $location_name : 'Cox\'s Bazar, Bangladesh';
				$map_settings  = get_option( 'ttbm_google_map_settings' );
				$gmap_api_key  = isset( $map_settings['ttbm_gmap_api_key'] ) ? $map_settings['ttbm_gmap_api_key'] : '';
				$settings_url  = admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_settings_page' );
				$iframe_src    = 'https://maps.google.com/maps?q=' . rawurlencode( $map_query ) . '&z=14&output=embed';
				?>
				<div>
					<label class="label">
						<div class="label-inner">
							<p>
								<?php esc_html_e( 'Google Map Location', 'tour-booking-manager' ); ?>
								<i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p( 'full_location' ); ?></span></i>
							</p>
						</div>
						<div style="width:80%;" class="auto-search-wrapper loupe">
							<input
								style="padding-left:30px"
								id="ttbm_hotel_map_location"
								class="ttbm_hotel_map_location_input ttbm-map-location-input"
								data-ttbm-map-sync="ttbm_hotel_map_location"
								placeholder="<?php esc_attr_e( 'Please type location...', 'tour-booking-manager' ); ?>"
								value="<?php echo esc_attr( $location_name ); ?>"
								autocomplete="off"
							>
						</div>
					</label>
					<p id="ttbm_hotel_map_location_error" class="ttbm-field-inline-error" style="display:none;color:#dc2626;font-size:12px;font-weight:500;margin:8px 0 0;">
						<span style="margin-right:4px;">&#9888;</span><?php esc_html_e('Please enter a map location before saving.', 'tour-booking-manager'); ?>
					</p>

					<div style="width:100%;margin-top:12px;">
						<?php if ( $gmap_api_key ) : ?>
							<div id="gmap_canvas" style="width:100%;height:400px;border-radius:8px;overflow:hidden;"></div>
						<?php else : ?>
							<div style="border-radius:8px;overflow:hidden;border:1px solid #e5e7eb;">
								<iframe
									id="ttbm_gmap_iframe"
									width="100%"
									height="400"
									frameborder="0"
									scrolling="no"
									marginheight="0"
									marginwidth="0"
									src="<?php echo esc_url( $iframe_src ); ?>"
									style="border:0;display:block;"
									loading="lazy"
									referrerpolicy="no-referrer-when-downgrade"
									allowfullscreen>
								</iframe>
							</div>
							<p style="font-size:12px;color:#6b7280;margin:8px 0 0;line-height:1.6;">
								<span style="color:#2271b1;margin-right:4px;">&#9432;</span>
								<?php esc_html_e( 'Type an address to update the map pointer automatically.', 'tour-booking-manager' ); ?>
								<a href="<?php echo esc_url( $settings_url ); ?>" style="color:#2271b1;font-weight:500;">
									<?php esc_html_e( 'Add a Google Maps API key', 'tour-booking-manager' ); ?>
								</a>
								<?php esc_html_e( 'for Google Places autocomplete and draggable markers.', 'tour-booking-manager' ); ?>
							</p>
						<?php endif; ?>
					</div>

					<div class="ttbm-map-latlng-fields" style="display:flex;gap:16px;flex-wrap:wrap;margin-top:14px;">
						<label class="label" style="flex:1;min-width:180px;">
							<div class="label-inner">
								<p><?php esc_html_e( 'Latitude', 'tour-booking-manager' ); ?></p>
							</div>
							<input type="text" id="map_latitude" class="ttbm-map-coord-input" data-ttbm-map-sync="ttbm_map_latitude" value="<?php echo esc_attr( $latitude ); ?>" placeholder="<?php esc_attr_e( 'Latitude', 'tour-booking-manager' ); ?>">
						</label>
						<label class="label" style="flex:1;min-width:180px;">
							<div class="label-inner">
								<p><?php esc_html_e( 'Longitude', 'tour-booking-manager' ); ?></p>
							</div>
							<input type="text" id="map_longitude" class="ttbm-map-coord-input" data-ttbm-map-sync="ttbm_map_longitude" value="<?php echo esc_attr( $longitude ); ?>" placeholder="<?php esc_attr_e( 'Longitude', 'tour-booking-manager' ); ?>">
						</label>
					</div>
				</div>
				<?php
			}

		}
		new TTBM_Settings_Hotel_Location();
	}