<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Hotel_General')) {
		class TTBM_Settings_Hotel_General {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'hotel_general_settings']);
                add_action('ttbm_single_location', [$this, 'show_location_frontend']);
                add_action('ttbm_single_sidebar', [$this, 'show_breakfast_parking_frontend']);
                add_action('show_sharing_meta', [$this, 'show_sharing_meta']);
            }
			public function hotel_general_settings($tour_id) {
				?>
                <div class="tabsItem ttbm_settings_general" data-tabs="#ttbm_general_info">
                    <h5><?php esc_html_e('General Information Settings', 'tour-booking-manager'); ?></h5>
                    <div class="divider"></div>
                    <table class="layoutFixed">
                        <tbody>
						<?php $this->location($tour_id); ?>
						<?php $this->distance_description($tour_id); ?>
						<?php $this->rating($tour_id); ?>
						<?php $this->parking_info($tour_id); ?>
						<?php $this->breakfast_info($tour_id); ?>
                        
                        </tbody>
                    </table>
                </div>
				<?php
			}

			public function location($tour_id) {
				$display_name = 'ttbm_display_hotel_location';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Hotel Location', 'tour-booking-manager'); ?>
						<?php TTBM_Custom_Layout::popup_button_xs('add_new_location_popup', esc_html__('Create New Location', 'tour-booking-manager')); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td colspan="3" class="ttbm_location_select_area"><?php TTBM_Settings_Location::location_select($tour_id); ?></td>
                </tr>
                <tr>
                    <td colspan="7"><?php TTBM_Settings::des_p('location'); ?></td>
                </tr>
				<?php
				TTBM_Settings_Location::add_new_location_popup();
			}

			public function distance_description($tour_id) {
				$display_name = 'ttbm_display_hotel_distance';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_hotel_distance_des';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('EX. 1.9 km from centre', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				?>
                <tr>
                    <th colspan="3"><?php esc_html_e('Distance Description', 'tour-booking-manager'); ?></th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td colspan="3">
                        <label data-collapse="#<?php echo esc_attr($display_name); ?>" class="<?php echo esc_attr($active); ?>">
                            <input class="formControl" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan="7"><?php TTBM_Settings::des_p('ttbm_display_hotel_distance'); ?></td>
                </tr>
				<?php
			}

			public function rating($tour_id) {
				$display_name = 'ttbm_display_hotel_rating';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				$rating = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_rating');
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Hotel Rating ', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td colspan="3">
                        <label data-collapse="#<?php echo esc_attr($display_name); ?>" class="<?php echo esc_attr($active); ?>">
                            <select class="formControl" name="ttbm_hotel_rating">
                                <option value="" selected><?php esc_html_e('please select hotel rating', 'tour-booking-manager'); ?></option>
                                <option value="1" <?php echo esc_attr($rating == '1' ? 'selected' : ''); ?>><?php esc_html_e('1 Star', 'tour-booking-manager'); ?></option>
                                <option value="2" <?php echo esc_attr($rating == '2' ? 'selected' : ''); ?>><?php esc_html_e('2 Star', 'tour-booking-manager'); ?></option>
                                <option value="3" <?php echo esc_attr($rating == '3' ? 'selected' : ''); ?>><?php esc_html_e('3 Star', 'tour-booking-manager'); ?> </option>
                                <option value="4" <?php echo esc_attr($rating == '4' ? 'selected' : ''); ?>><?php esc_html_e('4 Star', 'tour-booking-manager'); ?> </option>
                                <option value="5" <?php echo esc_attr($rating == '5' ? 'selected' : ''); ?>><?php esc_html_e('5 Star', 'tour-booking-manager'); ?> </option>
                            </select>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td colspan="7"><?php TTBM_Settings::des_p('ttbm_display_hotel_rating'); ?></td>
                </tr>
				<?php
			}

			public function parking_info($tour_id) {
				$display_name = 'ttbm_display_hotel_parking';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$hotel_parking =  get_post_meta($tour_id, 'ttbm_hotel_parking', true);
				
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Hotel Parking ', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <th>
						<input type="text" class="formControl" placeholder="<?php echo esc_html__('Free Parking Available On Site','tour-booking-manager');  ?>" name="ttbm_hotel_parking" value="<?php echo esc_attr($hotel_parking);  ?>"/>
                    </th>
                </tr>
				<?php
			}

			public function breakfast_info($tour_id) {

                $breakfast = 'ttbm_display_hotel_breakfast';
				$breakfast_display = TTBM_Global_Function::get_post_info($tour_id, $breakfast, 'on');
				$hotel_breakfast =  get_post_meta($tour_id, 'ttbm_hotel_breakfast', true);
				$breakfast_checked = $breakfast_display == 'off' ? '' : 'checked';
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Hotel Breakfast ', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($breakfast, $breakfast_checked); ?></td>
                    <th>
						<input type="text" class="formControl" placeholder="<?php echo esc_html__('American, Buffet ','tour-booking-manager');  ?>" name="ttbm_hotel_breakfast" value="<?php echo esc_attr($hotel_breakfast);  ?>"/>
                    </th>
                </tr>
				<?php
			}

            public function show_breakfast_parking_frontend(){
                $hotel_parking =  get_post_meta(get_the_ID(), 'ttbm_display_hotel_parking', true);
                $ttbm_hotel_parking =  get_post_meta(get_the_ID(), 'ttbm_hotel_parking', true);
                $hotel_parking = $hotel_parking == 'on' ? $hotel_parking : 'off';
                $hotel_breakfast =  get_post_meta(get_the_ID(), 'ttbm_display_hotel_breakfast', true);
                $hotel_breakfast = $hotel_breakfast == 'on' ? $hotel_breakfast : 'off';
                $ttbm_hotel_breakfast =  get_post_meta(get_the_ID(), 'ttbm_hotel_breakfast', true);
                if($hotel_breakfast=='on'):
                ?>
                    <div class="widgets breakfast-info">
                        <h2><?php echo esc_html__('Breakfast info','tour-booking-manager'); ?></h2>
                        <div class="widgets-text">
                            <i class="mi mi-burger-glass"></i>
                            <?php echo esc_html($ttbm_hotel_breakfast); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if($hotel_parking=='on'): ?>
                    <div class="widgets parking-info">
                        <h2><?php echo esc_html__('Parking info','tour-booking-manager'); ?></h2>
                        <div class="widgets-text">
                            <i class="mi mi-parking-circle"></i>
                            <?php echo esc_html($ttbm_hotel_parking); ?>
                        </div>
                    </div>
                <?php
                endif;
            }

            public function show_location_frontend(){
                $distance_des =  get_post_meta(get_the_ID(), 'ttbm_hotel_distance_des', true);
                $display =  get_post_meta(get_the_ID(), 'ttbm_display_hotel_location', true);
                $hotel_location =  get_post_meta(get_the_ID(), 'ttbm_hotel_location', true);
				$checked = $display == 'on' ? $display : 'off';
                if($checked=='on'):
                ?>
                    <p class="location-info">
                        <i class="mi mi-marker"></i> 
                        <?php echo esc_html($hotel_location); ?>
                        <a href="#"><?php echo esc_html($distance_des); ?></a>
                    </p>
                <?php
                endif;
            }

            public function show_sharing_meta(){
                ?>
                <div class="sharing-meta">
                    <div class="sharing-info">
                        <span>
                            <!-- <i class="mi mi-heart"></i> -->
                            <!-- <i class="mi mi-share"></i> -->
                        </span>
                        <a class="button" href="#ttbm_hotel_content_area"><?php echo __('Reserve','tour-booking-manager'); ?></a>
                    </div>
                    <!-- <div class="price-match">
                        <button><i class="mi mi-tags"></i> <?php echo __('We Price Match','tour-booking-manager'); ?></button>
                    </div> -->
                </div>
                <?php
            }
		}
		new TTBM_Settings_Hotel_General();
	}