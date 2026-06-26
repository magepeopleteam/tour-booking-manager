<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Hotel_General')) {
		class TTBM_Settings_Hotel_General {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'hotel_general_settings']);
				add_action('init', [$this, 'remove_default_title_editor']);
                add_action('ttbm_single_location', [$this, 'show_location_frontend']);
                add_action('ttbm_single_sidebar', [$this, 'show_breakfast_parking_frontend']);
                add_action('ttbm_single_review_testimonial', [$this, 'show_review_testimonial_frontend']);
                add_action('show_sharing_meta', [$this, 'show_sharing_meta']);
            }
			public function remove_default_title_editor() {
				remove_post_type_support('ttbm_hotel', 'title');
				remove_post_type_support('ttbm_hotel', 'editor');
			}
			public function hotel_general_settings($hotel_id) {
				?>
                <div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_general_info">
                    <h2><?php esc_html_e('General Information Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Configure the hotel title, description, and core hotel details.', 'tour-booking-manager'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-edit" aria-hidden="true"></i><?php esc_html_e('Hotel Title & Content', 'tour-booking-manager'); ?></h4>
                        </div>
						<?php $this->post_title_field($hotel_id); ?>
						<?php $this->post_content_field($hotel_id); ?>
                    </section>
                    <section class="ttbm-general-info-card ttbm-hotel-details-card">
                        <div class="ttbm-hotel-details-card__body">
                            <div class="ttbm-hotel-details-section">
                                <h5 class="ttbm-hotel-details-section__title"><?php esc_html_e('General Information', 'tour-booking-manager'); ?></h5>
                                <div class="ttbm-hotel-details-section__grid">
                                    <div class="ttbm-hotel-details-section__row">
                                        <div class="ttbm-hotel-details-section__col">
                                            <?php $this->location($hotel_id); ?>
                                        </div>
                                        <div class="ttbm-hotel-details-section__col">
                                            <?php $this->distance_description($hotel_id); ?>
                                        </div>
                                    </div>
                                    <div class="ttbm-hotel-details-section__row">
                                        <div class="ttbm-hotel-details-section__col">
                                            <?php $this->rating($hotel_id); ?>
                                        </div>
                                        <div class="ttbm-hotel-details-section__col">
                                            <?php $this->property_highlights($hotel_id); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="ttbm-hotel-details-section">
                                <h5 class="ttbm-hotel-details-section__title"><?php esc_html_e('Availability & Status', 'tour-booking-manager'); ?></h5>
                                <?php $this->parking_info($hotel_id); ?>
                                <?php $this->breakfast_info($hotel_id); ?>
                            </div>
                            <div class="ttbm-hotel-details-section">
                                <h5 class="ttbm-hotel-details-section__title"><?php esc_html_e('Ratings & Reviews', 'tour-booking-manager'); ?></h5>
                                <?php $this->review_info($hotel_id); ?>
                                <?php $this->service_info($hotel_id); ?>
                                <?php $this->testimonial_info($hotel_id); ?>
                                <div class="ttbm-hotel-details-section__feature-cards">
                                    <?php $this->popular_info($hotel_id); ?>
                                    <?php $this->make_feature_info($hotel_id); ?>
                                </div>
                            </div>
                        </div>
                    </section>
                     <?php TTBM_Settings_Location::add_new_location_popup(); ?>
                </div>
				<?php
			}
			public function post_title_field($hotel_id) {
				$title = get_the_title($hotel_id);
				?>
                <div class="ttbm-tour-title-field">
                    <label class="ttbm-tour-title-field__label" for="ttbm_post_title">
						<?php esc_html_e('Hotel Title', 'tour-booking-manager'); ?>
                        <span class="ttbm-tour-title-field__required" title="<?php esc_attr_e('Required', 'tour-booking-manager'); ?>">*</span>
                    </label>
                    <input
                        type="text"
                        id="ttbm_post_title"
                        name="post_title"
                        class="ttbm-tour-title-field__input"
                        value="<?php echo esc_attr($title); ?>"
                        placeholder="<?php esc_attr_e('Enter hotel title (required)...', 'tour-booking-manager'); ?>"
                        autocomplete="off"
                        required
                    />
                    <p class="ttbm-tour-title-field__hint"><?php esc_html_e('Use a clear hotel name that matches your booking listings.', 'tour-booking-manager'); ?></p>
                    <p class="ttbm-title-error" role="alert">
                        <span class="ttbm-tour-title-field__error-icon" aria-hidden="true">&#9888;</span>
						<?php esc_html_e('Hotel title is required before saving.', 'tour-booking-manager'); ?>
                    </p>
                </div>
				<?php
			}
			public function post_content_field($hotel_id) {
				$content = get_post_field('post_content', $hotel_id);
				?>
                <div class="ttbm-tour-description-field">
                    <div class="ttbm-tour-description-field__head">
                        <p class="ttbm-tour-description-field__label"><?php esc_html_e('Hotel Description', 'tour-booking-manager'); ?></p>
                    </div>
                    <div class="ttbm-tour-description-field__editor">
					<?php
					wp_editor($content, 'ttbm_hotel_post_content_editor', [
						'textarea_name' => 'post_content',
						'textarea_rows'  => 10,
						'tinymce'        => [
							'height'           => 200,
							'wp_autoresize_on' => false,
						],
					]);
					?>
                    </div>
                </div>
				<?php
			}

			public function location($tour_id) {
				$display_name = 'ttbm_display_hotel_location';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				?>
                <div class="ttbm-gen-field ttbm-gen-field--inline ttbm-gen-field--toggle ttbm-hotel-field ttbm-hotel-field--location<?php echo esc_attr($toggle_off); ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Hotel Location', 'tour-booking-manager'); ?>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <div class="ttbm-hotel-location-controls" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>">
                            <div class="ttbm_location_select_area ttbm-hotel-location-controls__select">
								<?php TTBM_Settings_Location::location_select($tour_id); ?>
								<p id="ttbm_hotel_location_error" class="ttbm-field-inline-error" style="display:none;color:#dc2626;font-size:12px;font-weight:500;margin:6px 0 0;">
									<span style="margin-right:4px;">&#9888;</span><?php esc_html_e('Please select a hotel location before saving.', 'tour-booking-manager'); ?>
								</p>
                            </div>
                            <button type="button" class="ttbm-hotel-location-controls__new _themeButton_xs" data-target-popup="add_new_location_popup">
                                <i class="fas fa-plus" aria-hidden="true"></i><?php esc_html_e('New', 'tour-booking-manager'); ?>
                            </button>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function distance_description($tour_id) {
				$display_name = 'ttbm_display_hotel_distance';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_hotel_distance_des';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('EX. 1.9 km', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				$disabled = $display == 'off' ? 'disabled' : '';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				?>
                <div class="ttbm-gen-field ttbm-gen-field--inline ttbm-gen-field--toggle ttbm-hotel-field<?php echo esc_attr($toggle_off); ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label"><?php esc_html_e('Distance', 'tour-booking-manager'); ?></p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <input
                            type="text"
                            class="ttbm-gen-field__input formControl"
                            data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>"
                            name="<?php echo esc_attr($value_name); ?>"
                            value="<?php echo esc_attr($value); ?>"
                            placeholder="<?php echo esc_attr($placeholder); ?>"
							<?php echo esc_attr($disabled); ?>
                        />
                    </div>
                </div>
				<?php
			}

			public function rating($tour_id) {
				$display_name = 'ttbm_display_hotel_rating';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$disabled = $display == 'off' ? 'disabled' : '';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				$rating = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_rating');
				?>
                <div class="ttbm-gen-field ttbm-gen-field--inline ttbm-gen-field--toggle ttbm-hotel-field<?php echo esc_attr($toggle_off); ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Hotel Rating', 'tour-booking-manager'); ?>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <div class="ttbm-hotel-select-with-icon" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>">
                            <select class="ttbm-gen-field__input formControl" name="ttbm_hotel_rating" <?php echo esc_attr($disabled); ?>>
                                <option value="" <?php echo empty($rating) ? 'selected' : ''; ?>><?php esc_html_e('Select rating', 'tour-booking-manager'); ?></option>
                                <option value="1" <?php echo esc_attr($rating == '1' ? 'selected' : ''); ?>><?php esc_html_e('1 Star', 'tour-booking-manager'); ?></option>
                                <option value="2" <?php echo esc_attr($rating == '2' ? 'selected' : ''); ?>><?php esc_html_e('2 Star', 'tour-booking-manager'); ?></option>
                                <option value="3" <?php echo esc_attr($rating == '3' ? 'selected' : ''); ?>><?php esc_html_e('3 Star', 'tour-booking-manager'); ?></option>
                                <option value="4" <?php echo esc_attr($rating == '4' ? 'selected' : ''); ?>><?php esc_html_e('4 Star', 'tour-booking-manager'); ?></option>
                                <option value="5" <?php echo esc_attr($rating == '5' ? 'selected' : ''); ?>><?php esc_html_e('5 Star', 'tour-booking-manager'); ?></option>
                            </select>
                            <i class="fas fa-star ttbm-hotel-select-with-icon__icon" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
				<?php
			}

            public function review_info($tour_id) {
				$display_name = 'ttbm_display_hotel_review';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$disabled = $display == 'off' ? 'disabled' : '';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				$review_title = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_review_title');
				$review_rating = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_review_rating');
				?>
                <div class="ttbm-hotel-review-card<?php echo esc_attr($toggle_off); ?>">
                    <div class="ttbm-hotel-review-card__head">
                        <p class="ttbm-hotel-review-card__title">
							<?php esc_html_e('Hotel Review and Rating', 'tour-booking-manager'); ?>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                    </div>
                    <div class="ttbm-hotel-review-card__body" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>">
                        <div class="ttbm-hotel-review-card__inputs">
                            <input type="text" class="formControl" placeholder="<?php echo esc_attr__('Excellent', 'tour-booking-manager'); ?>" name="ttbm_hotel_review_title" value="<?php echo esc_attr($review_title); ?>" <?php echo esc_attr($disabled); ?>/>
                            <input type="number" class="formControl ttbm-hotel-review-card__score" placeholder="<?php echo esc_attr__('7.8', 'tour-booking-manager'); ?>" name="ttbm_hotel_review_rating" value="<?php echo esc_attr($review_rating); ?>" step="0.1" min="0" max="10" <?php echo esc_attr($disabled); ?>/>
                        </div>
                    </div>
                </div>
				<?php
			}

            public function service_info($tour_id) {
				$display_name = 'ttbm_display_service_review';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$disabled = $display == 'off' ? 'disabled' : '';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				$review_title = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_service_review');
				$review_rating = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_service_rating');
				?>
                <div class="ttbm-hotel-review-card<?php echo esc_attr($toggle_off); ?>">
                    <div class="ttbm-hotel-review-card__head">
                        <p class="ttbm-hotel-review-card__title">
							<?php esc_html_e('Service Review and Rating', 'tour-booking-manager'); ?>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                    </div>
                    <div class="ttbm-hotel-review-card__body" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>">
                        <div class="ttbm-hotel-review-card__inputs">
                            <input type="text" class="formControl" placeholder="<?php echo esc_attr__('Wifi', 'tour-booking-manager'); ?>" name="ttbm_hotel_service_review" value="<?php echo esc_attr($review_title); ?>" <?php echo esc_attr($disabled); ?>/>
                            <input type="text" class="formControl ttbm-hotel-review-card__score" placeholder="<?php echo esc_attr__('7.8', 'tour-booking-manager'); ?>" name="ttbm_hotel_service_rating" value="<?php echo esc_attr($review_rating); ?>" <?php echo esc_attr($disabled); ?>/>
                        </div>
                    </div>
                </div>
				<?php
			}

            public function testimonial_info($tour_id) {
				$display_name = 'ttbm_display_hotel_testimonial';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$disabled = $display == 'off' ? 'disabled' : '';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				$review_title = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_testimonial_title');
				$review_text = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_testimonial_text');
				?>
                <div class="ttbm-hotel-review-card ttbm-hotel-review-card--testimonial<?php echo esc_attr($toggle_off); ?>">
                    <div class="ttbm-hotel-review-card__head">
                        <p class="ttbm-hotel-review-card__title">
							<?php esc_html_e('Display Testimonial', 'tour-booking-manager'); ?>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                    </div>
                    <div class="ttbm-hotel-review-card__body" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>">
                        <input type="text" class="formControl" placeholder="<?php echo esc_attr__('Guest Name (e.g. Guests who stayed here loved)', 'tour-booking-manager'); ?>" name="ttbm_hotel_testimonial_title" value="<?php echo esc_attr($review_title); ?>" <?php echo esc_attr($disabled); ?>/>
                        <textarea class="formControl" placeholder="<?php echo esc_attr__('Write testimonial...', 'tour-booking-manager'); ?>" name="ttbm_hotel_testimonial_text" rows="3" <?php echo esc_attr($disabled); ?>><?php echo esc_textarea($review_text); ?></textarea>
                    </div>
                </div>
				<?php
			}
            public function popular_info($tour_id) {
				$display_name = 'ttbm_display_hotel_popular';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = $display == 'off' ? '' : 'checked';
				$popular_text = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotel_popular_text');
				if ('' === $popular_text) {
					$popular_text = 'Popular';
				}
				?>
                <div class="ttbm-hotel-feature-card">
                    <div class="ttbm-hotel-feature-card__main">
                        <p class="ttbm-hotel-feature-card__title"><?php esc_html_e('Make Popular', 'tour-booking-manager'); ?></p>
                        <p class="ttbm-hotel-feature-card__desc"><?php esc_html_e('Featured on the homepage trending section', 'tour-booking-manager'); ?></p>
                        <input type="hidden" name="ttbm_display_hotel_popular_text" value="<?php echo esc_attr($popular_text); ?>"/>
                    </div>
					<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                </div>
				<?php
			}

            public function make_feature_info($tour_id) {
				$display_name = 'ttbm_display_hotel_feature';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = $display == 'off' ? '' : 'checked';
				$feature_text = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotel_feature_text');
				if ('' === $feature_text) {
					$feature_text = 'Feature';
				}
				?>
                <div class="ttbm-hotel-feature-card">
                    <div class="ttbm-hotel-feature-card__main">
                        <p class="ttbm-hotel-feature-card__title"><?php esc_html_e('Make Feature', 'tour-booking-manager'); ?></p>
                        <p class="ttbm-hotel-feature-card__desc"><?php esc_html_e('Prioritized in search results & categories', 'tour-booking-manager'); ?></p>
                        <input type="hidden" name="ttbm_display_hotel_feature_text" value="<?php echo esc_attr($feature_text); ?>"/>
                    </div>
					<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                </div>
				<?php
			}

			public function property_highlights($tour_id) {
				$display_name = 'ttbm_display_property_highlights';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$disabled = $display == 'off' ? 'disabled' : '';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				$property_highlights = get_post_meta($tour_id, 'ttbm_hotel_property_highlights', true);
				?>
                <div class="ttbm-gen-field ttbm-gen-field--inline ttbm-gen-field--toggle ttbm-hotel-field<?php echo esc_attr($toggle_off); ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Highlights', 'tour-booking-manager'); ?>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <input type="text" class="ttbm-gen-field__input formControl" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>" placeholder="<?php echo esc_attr__('Property highlights', 'tour-booking-manager'); ?>" name="ttbm_hotel_property_highlights" value="<?php echo esc_attr($property_highlights); ?>" <?php echo esc_attr($disabled); ?>/>
                    </div>
                </div>
				<?php
			}

			public function parking_info($tour_id) {
				$display_name = 'ttbm_display_hotel_parking';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$disabled = $display == 'off' ? 'disabled' : '';
				$toggle_off = $display == 'off' ? ' is-toggle-off' : '';
				$hotel_parking = get_post_meta($tour_id, 'ttbm_hotel_parking', true);
				?>
                <div class="ttbm-hotel-field ttbm-hotel-field--availability<?php echo esc_attr($toggle_off); ?>">
                    <p class="ttbm-gen-field__label"><?php esc_html_e('Parking Availability', 'tour-booking-manager'); ?></p>
                    <div class="ttbm-hotel-field__availability-row">
                        <input type="text" class="formControl" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>" placeholder="<?php echo esc_attr__('Free Parking Available On Site', 'tour-booking-manager'); ?>" name="ttbm_hotel_parking" value="<?php echo esc_attr($hotel_parking); ?>" <?php echo esc_attr($disabled); ?>/>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                    </div>
                </div>
				<?php
			}

			public function breakfast_info($tour_id) {
                $breakfast = 'ttbm_display_hotel_breakfast';
				$breakfast_display = TTBM_Global_Function::get_post_info($tour_id, $breakfast, 'on');
				$hotel_breakfast = get_post_meta($tour_id, 'ttbm_hotel_breakfast', true);
				$breakfast_checked = $breakfast_display == 'off' ? '' : 'checked';
				$disabled = $breakfast_display == 'off' ? 'disabled' : '';
				$toggle_off = $breakfast_display == 'off' ? ' is-toggle-off' : '';
				?>
                <div class="ttbm-hotel-field ttbm-hotel-field--availability<?php echo esc_attr($toggle_off); ?>">
                    <p class="ttbm-gen-field__label"><?php esc_html_e('Breakfast Availability', 'tour-booking-manager'); ?></p>
                    <div class="ttbm-hotel-field__availability-row">
                        <input type="text" class="formControl" data-ttbm-toggle-field="#<?php echo esc_attr($breakfast); ?>" placeholder="<?php echo esc_attr__('American, Buffet', 'tour-booking-manager'); ?>" name="ttbm_hotel_breakfast" value="<?php echo esc_attr($hotel_breakfast); ?>" <?php echo esc_attr($disabled); ?>/>
						<?php TTBM_Custom_Layout::switch_button($breakfast, $breakfast_checked); ?>
                    </div>
                </div>
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

            public function show_location_frontend() {
                $display          = get_post_meta( get_the_ID(), 'ttbm_display_hotel_map', true );
                $hotel_location   = get_post_meta( get_the_ID(), 'ttbm_hotel_map_location', true );
                $hotel_location   = ! empty( $hotel_location ) ? $hotel_location : '650 Manchester Road, New York, NY 10007, USA';
                $checked          = 'on' === $display ? $display : 'off';
                if ( 'on' === $checked ) :
                    ?>
                    <div class="ttbm_hotel_location location-info">
                        <span class="ttbm_hotel_location__icon" aria-hidden="true"><i class="mi mi-marker"></i></span>
                        <span class="ttbm_hotel_location__text"><?php echo esc_html( $hotel_location ); ?></span>
                    </div>
                    <?php
                endif;
            }

            public function show_review_testimonial_frontend() {
                $testimonial_status   = TTBM_Global_Function::get_post_info( get_the_ID(), 'ttbm_display_hotel_testimonial', 'on' );
                $testimonial_title    = TTBM_Global_Function::get_post_info( get_the_ID(), 'ttbm_hotel_testimonial_title' );
                $testimonial_text     = TTBM_Global_Function::get_post_info( get_the_ID(), 'ttbm_hotel_testimonial_text' );

                $display_hotel_review = get_post_meta( get_the_ID(), 'ttbm_display_hotel_review', true );
                $review_title         = get_post_meta( get_the_ID(), 'ttbm_hotel_review_title', true );
                $review_rating        = get_post_meta( get_the_ID(), 'ttbm_hotel_review_rating', true );
                $review_title         = $review_title ? $review_title : __( 'Excellent', 'tour-booking-manager' );
                if ( 'Excellant' === $review_title ) {
                    $review_title = __( 'Excellent', 'tour-booking-manager' );
                }
                $review_rating        = ( '' !== $review_rating && null !== $review_rating ) ? (float) $review_rating : 0;
                $display_hotel_review = 'on' === $display_hotel_review ? $display_hotel_review : 'on';

                $display_service_review = get_post_meta( get_the_ID(), 'ttbm_display_service_review', true );
                $display_service_review = ( 'on' === $display_service_review ) ? 'on' : 'off';
                $service_review         = get_post_meta( get_the_ID(), 'ttbm_hotel_service_review', true );
                $service_rating         = get_post_meta( get_the_ID(), 'ttbm_hotel_service_rating', true );
                ?>
                <?php if ( 'on' === $display_hotel_review ) : ?>
                    <div class="review-rating">
                        <div class="review">
                            <h3><?php echo esc_html( $review_title ); ?></h3>
                            <p><?php esc_html_e( 'Guest rating', 'tour-booking-manager' ); ?></p>
                        </div>
                        <div class="review-rate">
                            <?php echo esc_html( number_format( $review_rating, 1 ) ); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( 'on' === $testimonial_status ) : ?>
                    <div class="review-testimonial">
                        <h2><?php echo esc_html( $testimonial_title ); ?></h2>
                        <div class="testimonial">
                            <?php echo esc_html( $testimonial_text ); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( 'on' === $display_service_review ) : ?>
                    <div class="service-rating">
                        <h3><?php echo esc_html( $service_review ); ?></h3>
                        <div class="service-rate">
                            <?php echo esc_html( $service_rating ); ?>
                        </div>
                    </div>
                <?php
                endif;
            }

            public function show_sharing_meta() {
                ?>
                <div class="sharing-meta ttbm_hotel_hero_header__actions">
                    <a class="button ttbm_hotel_reserve_btn" href="#ttbm_hotel_content_area">
                        <?php esc_html_e( 'Reserve', 'tour-booking-manager' ); ?>
                    </a>
                </div>
                <?php
            }
		}
		new TTBM_Settings_Hotel_General();
	}
