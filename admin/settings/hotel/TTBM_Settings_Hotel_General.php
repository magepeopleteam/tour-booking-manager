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
                add_action('ttbm_single_review_testimonial', [$this, 'show_review_testimonial_frontend']);
                add_action('show_sharing_meta', [$this, 'show_sharing_meta']);
            }
			public function hotel_general_settings($hotel_id) {
				?>
                <div class="tabsItem ttbm_settings_general" data-tabs="#ttbm_general_info">
                    <h2><?php esc_html_e('General Information Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Here you can configure basic configureateion for hotel.','tour-booking-manager'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="mi mi-settings"></i><?php esc_html_e('General Settings', 'tour-booking-manager'); ?></h4>
                        </div>
                        <table class="layoutFixed">
                            <tbody>
                            <?php $this->location($hotel_id); ?>
                            <?php $this->distance_description($hotel_id); ?>
                            <?php $this->rating($hotel_id); ?>
                            <?php $this->property_highlights($hotel_id); ?>
                            <?php $this->parking_info($hotel_id); ?>
                            <?php $this->breakfast_info($hotel_id); ?>
                            <?php $this->review_info($hotel_id); ?>
                            <?php $this->service_info($hotel_id); ?>
                            <?php $this->testimonial_info($hotel_id); ?>
                            <?php $this->popular_info($hotel_id); ?>
                            <?php $this->make_feature_info($hotel_id); ?>

                            </tbody>
                        </table>
                    </section>
                     <?php TTBM_Settings_Location::add_new_location_popup(); ?>
                </div>
				<?php
			}

			public function location($tour_id) {
				$display_name = 'ttbm_display_hotel_location';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <tr>
                    <th colspan="2">
						<?php esc_html_e('Hotel Location', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::popup_button_xs('add_new_location_popup', esc_html__('Create New Location', 'tour-booking-manager')); ?></td>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td class="ttbm_location_select_area"><?php TTBM_Settings_Location::location_select($tour_id); ?></td>
                </tr>
				<?php
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
                    <th colspan="3"><?php esc_html_e('Distance From Tour Location', 'tour-booking-manager'); ?></th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td colspan="3">
                        <label data-collapse="#<?php echo esc_attr($display_name); ?>" class="<?php echo esc_attr($active); ?>">
                            <input class="formControl" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                        </label>
                    </td>
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
                    <td >
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
				<?php
			}

            public function review_info($tour_id) {
				$display_name = 'ttbm_display_hotel_review';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				$review_title = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_review_title');
				$review_rating = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_review_rating');
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Hotel Review and Rating ', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td >
                        <label data-collapse="#<?php echo esc_attr($display_name); ?>" class="<?php echo esc_attr($active); ?>">
                            <input type="text" class="formControl" placeholder="<?php echo esc_html__('Excellant','tour-booking-manager');  ?>" name="ttbm_hotel_review_title" value="<?php echo esc_attr($review_title);  ?>"/>
                            <input type="number" class="formControl" placeholder="<?php echo esc_html__('7.8','tour-booking-manager');  ?>" name="ttbm_hotel_review_rating" value="<?php echo esc_attr($review_rating);  ?>"/>
                        </label>
                    </td>
                </tr>
				<?php
			}

            public function service_info($tour_id) {
				$display_name = 'ttbm_display_service_review';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				$review_title = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_service_review');
				$review_rating = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_service_rating');
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Service Review and Rating ', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td >
                        <label data-collapse="#<?php echo esc_attr($display_name); ?>" class="<?php echo esc_attr($active); ?>">
                            <input type="text" class="formControl" placeholder="<?php echo esc_html__('Wifi','tour-booking-manager');  ?>" name="ttbm_hotel_service_review" value="<?php echo esc_attr($review_title);  ?>"/>
                            <input type="text" class="formControl" placeholder="<?php echo esc_html__('7.8','tour-booking-manager');  ?>" name="ttbm_hotel_service_rating" value="<?php echo esc_attr($review_rating);  ?>"/>
                        </label>
                    </td>
                </tr>
				<?php
			}

            public function testimonial_info($tour_id) {
				$display_name = 'ttbm_display_hotel_testimonial';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				$review_title = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_testimonial_title');
				$review_rating = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_testimonial_text');
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Display Testimonail', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td >
                        <label data-collapse="#<?php echo esc_attr($display_name); ?>" class="<?php echo esc_attr($active); ?>">
                            <input type="text" class="formControl" placeholder="<?php echo esc_html__('Guests who stayed here loved','tour-booking-manager');  ?>" name="ttbm_hotel_testimonial_title" value="<?php echo esc_attr($review_title);  ?>"/>
                            <textarea class="formControl" placeholder="<?php echo esc_html__('Write testimonail...','tour-booking-manager');  ?>" name="ttbm_hotel_testimonial_text"><?php echo esc_attr($review_rating);  ?></textarea>
                        </label>
                    </td>
                </tr>
				<?php
			}
            public function popular_info($tour_id) {
				$display_name = 'ttbm_display_hotel_popular';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = $display == 'off' ? '' : 'checked';
				$review_title = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_hotel_popular_text' );
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Make Popular', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td >
                        <input type="text" class="formControl" placeholder="<?php echo esc_html__('Popular','tour-booking-manager');  ?>" name="ttbm_display_hotel_popular_text" value="<?php echo esc_attr($review_title);  ?>"/>
                    </td>
                </tr>
				<?php
			}

            public function make_feature_info($tour_id) {
				$display_name = 'ttbm_display_hotel_feature';
				$display = TTBM_Global_Function::get_post_info( $tour_id, $display_name, 'off' );
				$checked = $display == 'off' ? '' : 'checked';
				$review_title = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_hotel_feature_text' );
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Make Feature', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td >
                        <input type="text" class="formControl" placeholder="<?php echo esc_html__('Feature','tour-booking-manager');  ?>" name="ttbm_display_hotel_feature_text" value="<?php echo esc_attr($review_title);  ?>"/>
                    </td>
                </tr>
				<?php
			}

			public function property_highlights($tour_id) {
				$display_name = 'ttbm_display_property_highlights';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$property_highlights =  get_post_meta($tour_id, 'ttbm_hotel_property_highlights', true);
				
				?>
                <tr>
                    <th colspan="3">
						<?php esc_html_e('Property highlights ', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td>
						<input type="text" class="formControl" placeholder="<?php echo esc_html__('Property highlights','tour-booking-manager');  ?>" name="ttbm_hotel_property_highlights" value="<?php echo esc_attr($property_highlights);  ?>"/>
                    </td>
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
						<?php esc_html_e('Parking Availability', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?></td>
                    <td>
						<input type="text" class="formControl" placeholder="<?php echo esc_html__('Free Parking Available On Site','tour-booking-manager');  ?>" name="ttbm_hotel_parking" value="<?php echo esc_attr($hotel_parking);  ?>"/>
                    </td>
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
						<?php esc_html_e('Breakfast Availability', 'tour-booking-manager'); ?>
                    </th>
                    <td><?php TTBM_Custom_Layout::switch_button($breakfast, $breakfast_checked); ?></td>
                    <td>
						<input type="text" class="formControl" placeholder="<?php echo esc_html__('American, Buffet ','tour-booking-manager');  ?>" name="ttbm_hotel_breakfast" value="<?php echo esc_attr($hotel_breakfast);  ?>"/>
                    </td>
                </tr>
				<?php
			}

            public function show_breakfast_parking_frontend(){
				$display_property = TTBM_Global_Function::get_post_info(get_the_ID(), 'ttbm_display_property_highlights', 'on');
                
				$property_highlights =  get_post_meta(get_the_ID(), 'ttbm_hotel_property_highlights', true);
				

                $hotel_parking =  get_post_meta(get_the_ID(), 'ttbm_display_hotel_parking', true);
                $ttbm_hotel_parking =  get_post_meta(get_the_ID(), 'ttbm_hotel_parking', true);
                $hotel_parking = $hotel_parking == 'on' ? $hotel_parking : 'off';
                
                $hotel_breakfast =  get_post_meta(get_the_ID(), 'ttbm_display_hotel_breakfast', true);
                $hotel_breakfast = $hotel_breakfast == 'on' ? $hotel_breakfast : 'off';
                $ttbm_hotel_breakfast =  get_post_meta(get_the_ID(), 'ttbm_hotel_breakfast', true);
                ?>
                <?php if($display_property=='on'): ?>
                    <div class="widgets property-highlights">
                        <h2><?php echo esc_html__('Property highlights','tour-booking-manager'); ?></h2>
                        <div class="widgets-text">
                            <i class="mi mi-marker"></i>                            
                            <?php echo esc_html($property_highlights); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($hotel_breakfast=='on'): ?>
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
                <?php endif;?>
                
                <?php
            }

            public function show_location_frontend(){
                $distance_des =  get_post_meta(get_the_ID(), 'ttbm_hotel_distance_des', true);
                $display =  get_post_meta(get_the_ID(), 'ttbm_display_hotel_map', true);
                $hotel_location =  get_post_meta(get_the_ID(), 'ttbm_hotel_map_location', true);
                $hotel_location =  !empty($hotel_location) ? $hotel_location : '650 Manchester Road, New York, NY 10007, USA';
				$checked = $display == 'on' ? $display : 'off';
                if($checked=='on'):
                ?>
                    <p class="location-info">
                        <i class="mi mi-marker"></i> 
                        <?php echo esc_html($hotel_location); ?>
                    </p>
                <?php
                endif;
            }

            public function show_review_testimonial_frontend(){
                $testimonial_status = TTBM_Global_Function::get_post_info(get_the_ID(), 'ttbm_display_hotel_testimonial', 'on');
                $testimonial_title = TTBM_Global_Function::get_post_info(get_the_ID(), 'ttbm_hotel_testimonial_title');
				$testimonial_text = TTBM_Global_Function::get_post_info(get_the_ID(), 'ttbm_hotel_testimonial_text');
                
                $display_hotel_review =  get_post_meta(get_the_ID(), 'ttbm_display_hotel_review', true);
                $review_title =  get_post_meta(get_the_ID(), 'ttbm_hotel_review_title', true);
                $review_title = $review_title ? $review_title:'Excellant';
                $review_rating =  get_post_meta(get_the_ID(), 'ttbm_hotel_review_rating', true);
                $review_rating = $review_rating ? $review_rating:0;
				$display_hotel_review = $display_hotel_review == 'on' ? $display_hotel_review : 'on';
                
                $display_service_review = get_post_meta(get_the_ID(), 'ttbm_display_service_review', true);
                $display_service_review = $display_service_review = 'on' ? $display_service_review : 'off';
                $service_review =  get_post_meta(get_the_ID(), 'ttbm_hotel_service_review', true);
                $service_rating =  get_post_meta(get_the_ID(), 'ttbm_hotel_service_rating', true);
                ?>
                <?php if($display_hotel_review=='on'): ?>
                    <div class="review-rating">
                        <div class="review">
                            <h3><?php echo esc_html($review_title); ?></h3>
                            <p><?php echo esc_html($review_rating)." ".__('reviews','tour-booking-manager'); ?> </p>
                        </div>
                        <div class="review-rate">
                            <?php echo esc_html(number_format($review_rating / 100, 1)); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if($testimonial_status=='on'): ?>
                    <div class="review-testimonial">
                        <h2><?php echo esc_html($testimonial_title); ?></h2>
                        <div class="testimonial">
                            <?php echo esc_html($testimonial_text); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if($display_service_review=='on'): ?>
                    <div class="service-rating">
                        <h3><?php echo esc_html($service_review); ?></h3>
                        <div class="service-rate">
                            <?php echo esc_html($service_rating); ?>
                        </div>
                    </div>
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
