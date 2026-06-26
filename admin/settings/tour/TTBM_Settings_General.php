<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_General')) {
		class TTBM_Settings_General {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'general_settings']);
				add_action('init', [$this, 'remove_default_title_editor']);
			}
			public function remove_default_title_editor() {
				$cpt = TTBM_Function::get_cpt_name();
				remove_post_type_support($cpt, 'title');
				remove_post_type_support($cpt, 'editor');
			}
			public function general_settings($tour_id) {
				?>
                <div class="tabsItem ttbm_settings_general contentTab" data-tabs="#ttbm_general_info">
                    <h2><?php esc_html_e('General Information Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('general_settings_description'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-edit" aria-hidden="true"></i><?php esc_html_e('Tour Title & Content', 'tour-booking-manager'); ?></h4>
                        </div>
						<?php $this->post_title_field($tour_id); ?>
						<?php $this->post_content_field($tour_id); ?>
                    </section>
                    <section class="ttbm-general-info-card">
                        <div class="ttbm-header ttbm-general-info-card__header">
                            <h4><i class="fas fa-info-circle" aria-hidden="true"></i><?php esc_html_e('General Information', 'tour-booking-manager'); ?></h4>
                        </div>
                        <div class="ttbm-general-info-card__grid">
                            <div class="ttbm-general-info-card__row">
                                <div class="ttbm-general-info-card__col">
									<?php $this->tour_duration($tour_id); ?>
                                </div>
                                <div class="ttbm-general-info-card__col">
									<?php $this->stay_night($tour_id); ?>
                                </div>
                            </div>
                            <div class="ttbm-general-info-card__row">
                                <div class="ttbm-general-info-card__col">
									<?php $this->age_range($tour_id); ?>
                                </div>
                                <div class="ttbm-general-info-card__col">
									<?php $this->max_people($tour_id); ?>
                                </div>
                            </div>
                            <div class="ttbm-general-info-card__row">
                                <div class="ttbm-general-info-card__col">
									<?php $this->starting_place($tour_id); ?>
                                </div>
                                <div class="ttbm-general-info-card__col">
									<?php $this->tour_language($tour_id); ?>
                                </div>
                            </div>
                        </div>
						<?php $this->short_description_section($tour_id); ?>
                    </section>
                </div>
				<?php
			}
			public function stay_night($tour_id) {
				$display_name = 'ttbm_display_duration_night';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = ($display == 'off') ? '' : 'checked';
				$disabled = ($display == 'off') ? 'disabled' : '';
				$placeholder = esc_html__('Ex: 3', 'tour-booking-manager');
				?>
                <div class="ttbm-gen-field ttbm-gen-field--toggle ttbm-gen-field--inline<?php echo ($display == 'off') ? ' is-toggle-off' : ''; ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Stay Night', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('stay_night'); ?></span></i>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <input type="number" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>" min="0" class="ttbm-gen-field__input formControl" name="ttbm_travel_duration_night" value="<?php echo esc_attr(TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_night')); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo esc_attr($disabled); ?>/>
                    </div>
                </div>
				<?php
			}
			public function tour_duration($tour_id) {
				$value_name = 'ttbm_travel_duration';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$duration_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration_type', 'day');
				$placeholder = esc_html__('Ex: 3', 'tour-booking-manager');
				?>
                <div class="ttbm-gen-field ttbm-gen-field--inline ttbm-gen-field--duration">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Tour Duration', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('duration'); ?></span></i>
                        </p>
                        <span class="ttbm-gen-field__toggle-spacer" aria-hidden="true"></span>
                        <div class="ttbm-gen-duration-group">
                            <input class="ttbm-gen-duration-group__value formControl" min="0.1" step="0.1" type="number" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                            <select class="ttbm-gen-duration-group__unit formControl" name="ttbm_travel_duration_type" aria-label="<?php esc_attr_e('Duration unit', 'tour-booking-manager'); ?>">
                                <option value="day" <?php echo esc_attr($duration_type == 'day' ? 'selected' : ''); ?>><?php esc_html_e('Days', 'tour-booking-manager'); ?></option>
                                <option value="hour" <?php echo esc_attr($duration_type == 'hour' ? 'selected' : ''); ?>><?php esc_html_e('Hours', 'tour-booking-manager'); ?></option>
                                <option value="min" <?php echo esc_attr($duration_type == 'min' ? 'selected' : ''); ?>><?php esc_html_e('Minutes', 'tour-booking-manager'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function max_people($tour_id) {
				$max_people_status_field_name = 'ttbm_display_max_people';
				$max_people_field_status = TTBM_Global_Function::get_post_info($tour_id, $max_people_status_field_name, 'on');
				$max_people_field_name = 'ttbm_travel_max_people_allow';
				$max_people_field_value = TTBM_Global_Function::get_post_info($tour_id, $max_people_field_name);
				$max_people_placeholder = esc_html__('50', 'tour-booking-manager');
				$max_people_status_checked = ($max_people_field_status == 'off') ? '' : 'checked';
				?>
                <div class="ttbm-gen-field ttbm-gen-field--toggle ttbm-gen-field--inline<?php echo ($max_people_field_status == 'off') ? ' is-toggle-off' : ''; ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Max Guests', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('max_people'); ?></span></i>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($max_people_status_field_name, $max_people_status_checked); ?>
                        <input type="number" min="0" data-ttbm-toggle-field="#<?php echo esc_attr($max_people_status_field_name); ?>" class="ttbm-gen-field__input formControl" name="<?php echo esc_attr($max_people_field_name); ?>" value="<?php echo esc_attr($max_people_field_value); ?>" placeholder="<?php echo esc_attr($max_people_placeholder); ?>" <?php echo ($max_people_field_status == 'off') ? 'disabled' : ''; ?>/>
                    </div>
                </div>
				<?php
			}
			public function starting_price($tour_id) {
				$display_name = 'ttbm_display_price_start';
				$display      = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name   = 'ttbm_travel_start_price';
				$value        = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder  = esc_html__('Type Start Price', 'tour-booking-manager');
				$checked      = $display === 'off' ? '' : 'checked';
				$active       = $display === 'off' ? '' : 'mActive';
				?>
                <div class="label">
                    <div class="label-inner">
                        <p><?php esc_html_e('Tour Start Price', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('start_price'); ?></span></i></p>
                    </div>
                    <div class="_dFlex_alignCenter_justifyBetween">
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <input type="number" min="0" step="0.01" data-collapse="#<?php echo esc_attr($display_name); ?>" class="ms-2 rounded <?php echo esc_attr($active); ?>" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                    </div>
                </div>
				<?php
			}
			public function starting_place($tour_id) {
				$status_field_name = 'ttbm_display_start_location';
				$status = TTBM_Global_Function::get_post_info($tour_id, $status_field_name, 'on');
				$location_field_name = 'ttbm_travel_start_place';
				$location_field_value = TTBM_Global_Function::get_post_info($tour_id, $location_field_name);
				$location_placeholder = esc_html__('Type Start Point...', 'tour-booking-manager');
				$status_checked = $status == 'off' ? '' : 'checked';
				?>
                <div class="ttbm-gen-field ttbm-gen-field--toggle ttbm-gen-field--inline<?php echo ($status == 'off') ? ' is-toggle-off' : ''; ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Start Point', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('start_place'); ?></span></i>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($status_field_name, $status_checked); ?>
                        <input type="text" data-ttbm-toggle-field="#<?php echo esc_attr($status_field_name); ?>" class="ttbm-gen-field__input formControl" name="<?php echo esc_attr($location_field_name); ?>" value="<?php echo esc_attr($location_field_value); ?>" placeholder="<?php echo esc_attr($location_placeholder); ?>" <?php echo ($status == 'off') ? 'disabled' : ''; ?>/>
                    </div>
                </div>
				<?php
			}
			public function age_range($tour_id) {
				$display_name = 'ttbm_display_min_age';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$value_name = 'ttbm_travel_min_age';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Ex: 5 - 50 Years', 'tour-booking-manager');
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <div class="ttbm-gen-field ttbm-gen-field--toggle ttbm-gen-field--inline<?php echo ($display == 'off') ? ' is-toggle-off' : ''; ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Age Range', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('age_range'); ?></span></i>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <input type="text" data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>" class="ttbm-gen-field__input formControl" name="<?php echo esc_attr($value_name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo ($display == 'off') ? 'disabled' : ''; ?>/>
                    </div>
                </div>
				<?php
			}
			public function tour_language($tour_id) {
				$display_name = 'ttbm_travel_language_status';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$language = 'ttbm_travel_language';
				$language = TTBM_Global_Function::get_post_info($tour_id, $language);
				$selected_languages = is_array($language) ? $language : (!empty($language) ? array($language) : array());
				$checked = $display == 'off' ? '' : 'checked';
				$language_lists = array();
				$build_language_label = static function ($locale, $fallback = '') {
					$label = $fallback;
					if (class_exists('Locale')) {
						$english_name = \Locale::getDisplayLanguage($locale, 'en');
						$native_name = \Locale::getDisplayLanguage($locale, $locale);
						if ($english_name && $native_name && strtolower($english_name) !== strtolower($native_name)) {
							$label = $english_name . ' (' . $native_name . ')';
						} elseif ($english_name) {
							$label = $english_name;
						} elseif ($native_name) {
							$label = $native_name;
						}
					}
					return $label ? $label : $locale;
				};

				// Try to load all WordPress language packs dynamically.
				if (!function_exists('wp_get_available_translations')) {
					require_once ABSPATH . 'wp-admin/includes/translation-install.php';
				}
				if (function_exists('wp_get_available_translations')) {
					$translations = wp_get_available_translations();
					if (is_array($translations) && !empty($translations)) {
						foreach ($translations as $locale => $translation) {
							$native_name = $translation['native_name'] ?? '';
							$english_name = $translation['english_name'] ?? '';
							if ($native_name && $english_name && strtolower($native_name) !== strtolower($english_name)) {
								$language_lists[$locale] = $english_name . ' (' . $native_name . ')';
							} else {
								$language_lists[$locale] = $build_language_label($locale, $native_name ? $native_name : $english_name);
							}
						}
					}
				}

				// Fallback to installed languages if translation list is unavailable.
				if (empty($language_lists)) {
					$installed_locales = get_available_languages();
					foreach ($installed_locales as $locale) {
						$language_lists[$locale] = $build_language_label($locale);
					}
				}

				// Ensure active/current locales exist and sort by label.
				if (!isset($language_lists['en_US'])) {
					$language_lists['en_US'] = $build_language_label('en_US', 'English (United States)');
				}
				$current_locale = get_locale();
				if ($current_locale && !isset($language_lists[$current_locale])) {
					$language_lists[$current_locale] = $build_language_label($current_locale);
				}
				if (!empty($selected_languages)) {
					foreach ($selected_languages as $selected_language) {
						if ($selected_language && !isset($language_lists[$selected_language])) {
							$language_lists[$selected_language] = $build_language_label($selected_language);
						}
					}
				}
				asort($language_lists, SORT_NATURAL | SORT_FLAG_CASE);
				?>
                <div class="ttbm-gen-field ttbm-gen-field--toggle ttbm-gen-field--inline<?php echo ($display == 'off') ? ' is-toggle-off' : ''; ?>">
                    <div class="ttbm-gen-field__inline-row">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Tour Language', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Easily select your preferred language to enhance your travel experience.', 'tour-booking-manager'); ?></span></i>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <div data-ttbm-toggle-field="#<?php echo esc_attr($display_name); ?>" class="ttbm-gen-field__input-wrap">
                            <select class="ttbm-gen-field__input formControl ttbm_select2" name="ttbm_travel_language[]" multiple data-placeholder="<?php esc_attr_e('Select languages', 'tour-booking-manager'); ?>" <?php echo ($display == 'off') ? 'disabled' : ''; ?>>
							<?php foreach ($language_lists as $key => $value): ?>
                                <option value="<?php echo esc_html($key); ?>" <?php echo esc_attr(in_array($key, $selected_languages, true) ? 'selected' : ''); ?>><?php echo esc_html($value); ?></option>
							<?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function short_description_section($tour_id) {
				$display_name = 'ttbm_display_description';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'on');
				$checked = $display == 'off' ? '' : 'checked';
				$active = $display == 'off' ? '' : 'mActive';
				$value_name = 'ttbm_short_description';
				$value = TTBM_Global_Function::get_post_info($tour_id, $value_name);
				$placeholder = esc_html__('Please Type Short Description...', 'tour-booking-manager');
				$char_count = mb_strlen((string) $value);
				?>
                <div class="ttbm-gen-short-desc">
                    <div class="ttbm-gen-short-desc__toggle-row">
                        <p class="ttbm-gen-short-desc__toggle-label">
                            <i class="fas fa-file-alt" aria-hidden="true"></i>
							<?php esc_html_e('Short Description Enable/Disable', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('short_des'); ?></span></i>
                        </p>
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                    </div>
                    <div class="ttbm-gen-short-desc__body <?php echo esc_attr($active); ?>" data-collapse="#<?php echo esc_attr($display_name); ?>">
                        <p class="ttbm-gen-field__label">
							<?php esc_html_e('Short Description', 'tour-booking-manager'); ?>
                            <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('short_des'); ?></span></i>
                        </p>
                        <textarea class="ttbm-gen-short-desc__textarea formControl" cols="50" rows="5" name="<?php echo esc_attr($value_name); ?>" maxlength="500" placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_textarea($value); ?></textarea>
                        <div class="ttbm-gen-short-desc__meta">
                            <span class="ttbm-gen-short-desc__hint"><?php esc_html_e('Recommended: 150-300 characters', 'tour-booking-manager'); ?></span>
                            <span class="ttbm-gen-char-count" data-max="500"><?php echo esc_html($char_count); ?> / 500</span>
                        </div>
                    </div>
                </div>
				<?php
			}
		public function post_title_field($tour_id) {
				$title = get_the_title($tour_id);
				?>
                <div class="ttbm-tour-title-field">
                    <label class="ttbm-tour-title-field__label" for="ttbm_post_title">
						<?php esc_html_e('Tour Title', 'tour-booking-manager'); ?>
                        <span class="ttbm-tour-title-field__required" title="<?php esc_attr_e('Required', 'tour-booking-manager'); ?>">*</span>
                    </label>
                    <input
                        type="text"
                        id="ttbm_post_title"
                        name="post_title"
                        class="ttbm-tour-title-field__input"
                        value="<?php echo esc_attr($title); ?>"
                        placeholder="<?php esc_attr_e('Enter tour title (required)...', 'tour-booking-manager'); ?>"
                        autocomplete="off"
                    />
                    <p class="ttbm-tour-title-field__hint"><?php esc_html_e('Use a catchy, descriptive title for better conversion rates.', 'tour-booking-manager'); ?></p>
                    <p class="ttbm-title-error" role="alert">
                        <span class="ttbm-tour-title-field__error-icon" aria-hidden="true">&#9888;</span>
						<?php esc_html_e('Tour title is required before saving.', 'tour-booking-manager'); ?>
                    </p>
                </div>
				<?php
			}
			public function post_content_field($tour_id) {
				$content = get_post_field('post_content', $tour_id);
				?>
                <div class="ttbm-tour-description-field" data-autosave-label="<?php esc_attr_e('Auto-saved %s ago', 'tour-booking-manager'); ?>" data-autosave-just-now="<?php esc_attr_e('just now', 'tour-booking-manager'); ?>" data-autosave-min="<?php esc_attr_e('%d min', 'tour-booking-manager'); ?>" data-autosave-mins="<?php esc_attr_e('%d mins', 'tour-booking-manager'); ?>">
                    <div class="ttbm-tour-description-field__head">
                        <p class="ttbm-tour-description-field__label"><?php esc_html_e('Tour Description', 'tour-booking-manager'); ?></p>
                        <span class="ttbm-tour-description-field__autosave" data-default-text="<?php esc_attr_e('Ready to save', 'tour-booking-manager'); ?>"><?php esc_html_e('Ready to save', 'tour-booking-manager'); ?></span>
                    </div>
                    <div class="ttbm-tour-description-field__editor">
					<?php
					wp_editor($content, 'ttbm_post_content_editor', [
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
		}
		new TTBM_Settings_General();
	}
