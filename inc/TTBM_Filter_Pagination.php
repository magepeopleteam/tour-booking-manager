<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Filter_Pagination')) {
		class TTBM_Filter_Pagination {
			public $upcomming_date = '';
			public function __construct() {
				add_action('ttbm_top_filter_static', array($this, 'top_filter_static'), 10, 1);
				add_action('ttbm_left_filter', array($this, 'left_filter'), 10, 1);
				add_action('ttbm_top_filter', array($this, 'top_filter'), 10, 1);
				add_action('ttbm_pagination', array($this, 'pagination'), 10, 3);
				add_action('ttbm_filter_top_bar', array($this, 'filter_top_bar'), 10, 2);
				add_action('ttbm_sort_result', array($this, 'sort_result'), 10, 2);
				$this->upcomming_date = array_reverse(TTBM_Function::get_meta_values('ttbm_upcoming_date', 'ttbm_tour'));
			}
			public function top_filter_static($params) {
				?>
                <div class="mpStyle placeholderLoader ttbm_wraper ttbm_top_filter">
                    <form method="get" action="<?php echo get_home_url() . '/find/'; ?>">
                        <div class="flexWrap justifyCenter">
							<?php $this->title_filter($params); ?>
							<?php $this->type_filter($params); ?>
							<?php $this->duration_filter($params); ?>
							<?php $this->category_filter($params); ?>
							<?php $this->organizer_filter($params); ?>
							<?php $this->location_filter($params); ?>
							<?php $this->country_filter($params); ?>
							<?php $this->activity_filter($params); ?>
							<?php $this->month_filter($params); ?>
                            <button type="submit" class="dButton min_200" data-placeholder><?php esc_html_e('Find Tours', 'tour-booking-manager'); ?></button>
                        </div>
                    </form>
                </div>
				<?php
			}
			public function top_filter($params) {
				ob_start();
				$filter = $params['search-filter'];
				if ($filter == 'yes') {
					?>
                    <div class="ttbm_filter ttbm_top_filter flexWrap justifyCenter">
						<?php $this->type_filter($params); ?>
						<?php $this->category_filter($params); ?>
						<?php $this->organizer_filter($params); ?>
						<?php $this->location_filter($params); ?>
						<?php $this->country_filter($params); ?>
						<?php $this->activity_filter($params); ?>
						<?php $this->duration_filter($params); ?>
						<?php $this->month_filter($params); ?>
						<?php $this->title_filter($params); ?>
                    </div>
					<?php
				}
				echo ob_get_clean();
			}
			public function left_filter($params) {
				?>
				<div class="filter-top-label" >
					<h4 data-placeholder><span class="mR_xs fas fa-filter"></span><?php esc_html_e('Filters', 'tour-booking-manager'); ?></h4>
				</div>
				<div class="ttbm_filter">
					<?php $this->location_filter_multiple($params); ?>
					<?php $this->country_filter_left($params); ?>
					<?php $this->title_filter_left($params); ?>
					<?php $this->type_filter_left($params); ?>
					<?php $this->category_filter_left($params); ?>
					<?php $this->month_filter_left($params); ?>
					<?php $this->duration_filter_multiple($params); ?>
					<?php $this->feature_filter_multiple($params); ?>
					<?php $this->activity_filter_multiple($params); ?>
					<?php $this->tag_filter_multiple($params); ?>
					<?php $this->organizer_filter_left($params); ?>
				</div>
				<?php
			}
			//****************************************/
			public function title_filter($params) {
				if ($params['title-filter'] == 'yes') {
					?>
                    <label data-placeholder>
                        <input name="title_filter" value="<?php echo esc_attr($_GET['title_filter'] ?? ''); ?>" placeholder="<?php esc_html_e('Search ....', 'tour-booking-manager'); ?>" class="formControl"/>
                    </label>
					<?php
				}
			}
			public function title_filter_left($params) {
				if ($params['title-filter'] == 'yes') {
					?>
                    <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#ttbm_title_filter" data-placeholder>
						<?php esc_html_e('Filters By Tour Name', 'tour-booking-manager'); ?>
                        <span data-icon class="fas fa-chevron-down"></span>
                    </h5>
                    <div class="divider"></div>
                    <div class="mActive" data-collapse="#ttbm_title_filter" data-placeholder>
						<?php $this->title_filter($params); ?>
                    </div>
					<?php
				}
			}
			//****************************************/
			public function type_filter($params) {
				if ($params['type-filter'] == 'yes') {
					$url = $_GET['type_filter'] ?? '';
					?>
                    <label data-placeholder>
                        <select class="formControl" name="type_filter">
                            <option selected value=""><?php esc_html_e('All Type', 'tour-booking-manager'); ?></option>
                            <option value="general" <?php echo esc_attr($url == 'general' ? 'selected' : ''); ?>><?php esc_html_e('Tour', 'tour-booking-manager'); ?></option>
                            <option value="hotel" <?php echo esc_attr($url == 'hotel' ? 'selected' : ''); ?>><?php esc_html_e('Hotel', 'tour-booking-manager'); ?></option>
                        </select>
                    </label>
					<?php
				}
			}
			public function type_filter_left($params) {
				if ($params['type-filter'] == 'yes') {
					?>
                    <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#ttbm_type_filter_left" data-placeholder>
						<?php esc_html_e('Filters By Type', 'tour-booking-manager'); ?>
                        <span data-icon class="fas fa-chevron-down"></span>
                    </h5>
                    <div class="divider"></div>
                    <div class="mActive" data-collapse="#ttbm_type_filter_left" data-placeholder>
						<?php $this->type_filter($params); ?>
                    </div>
					<?php
				}
			}
			//****************************************/
			public function category_filter($params, $categories = '') {
				if ($params['category-filter'] == 'yes') {
					$categories = is_array($categories) ? $categories : MP_Global_Function::get_taxonomy('ttbm_tour_cat');
					if (is_array($categories) && sizeof($categories) > 0) {
						$url = $_GET['category_filter'] ?? '';
						$current = $url ? get_term_by('id', $url, 'ttbm_tour_cat')->term_id : '';
						?>
                        <label data-placeholder>
                            <select class="formControl" name="category_filter">
                                <option selected value=""><?php esc_html_e('All Category', 'tour-booking-manager'); ?></option>
								<?php foreach ($categories as $category) { ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>" <?php echo esc_attr($url && $current == $category->term_id ? 'selected' : ''); ?>><?php echo esc_html($category->name); ?></option>
								<?php } ?>
                            </select>
                        </label>
						<?php
					}
				}
			}
			public function category_filter_left($params) {
				if ($params['category-filter'] == 'yes') {
					$categories = MP_Global_Function::get_taxonomy('ttbm_tour_cat');
					if (sizeof($categories) > 0) {
						?>
                        <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#ttbm_category_filter_left" data-placeholder>
							<?php esc_html_e('Filters By Category', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#ttbm_category_filter_left" data-placeholder>
							<?php $this->category_filter($params, $categories); ?>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function organizer_filter($params, $organizers = array()) {
				if ($params['organizer-filter'] == 'yes') {
					$organizers = sizeof($organizers) > 0 ? $organizers : MP_Global_Function::get_taxonomy('ttbm_tour_org', 'ttbm_tour');
					if (sizeof($organizers) > 0) {
						$url = $_GET['organizer_filter'] ?? '';
						$current = $url ? get_term_by('id', $url, 'ttbm_tour_org')->term_id : '';
						?>
                        <label data-placeholder>
                            <select class="formControl" name="organizer_filter">
                                <option selected value=""><?php esc_html_e('All Organizer', 'tour-booking-manager'); ?></option>
								<?php foreach ($organizers as $organizer) {
									if (get_term($organizer->term_id, 'ttbm_tour_org')->count) { ?>
                                        <option value="<?php echo esc_attr($organizer->term_id); ?>" <?php echo esc_attr($url && $current == $organizer->term_id ? 'selected' : ''); ?>><?php echo esc_html($organizer->name) ?></option>
									<?php }
								} ?>
                            </select>
                        </label>
						<?php
					}
				}
			}
			public function organizer_filter_left($params) {
				if ($params['organizer-filter'] == 'yes') {
					$organizers = MP_Global_Function::get_taxonomy('ttbm_tour_org');
					$total_organizers = 0;
					foreach ($organizers as $organizer) {
						if (get_term($organizer->term_id, 'ttbm_tour_org')->count) {
							$total_organizers++;
						}
					}
					if (sizeof($organizers) > 0 && $total_organizers) {
						?>
                        <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#ttbm_organizer_filter_left" data-placeholder>
							<?php esc_html_e('Filters By Organizer', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#ttbm_organizer_filter_left" data-placeholder>
							<?php $this->organizer_filter($params, $organizers); ?>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function location_filter($params) {
				if ($params['location-filter'] == 'yes') {
					$locations = MP_Global_Function::get_taxonomy('ttbm_tour_location');
					if (sizeof($locations) > 0) {
						$url = $_GET['location_filter'] ?? '';
						?>
                        <label data-placeholder>
                            <select class="formControl" name="location_filter">
                                <option selected value=""><?php esc_html_e('All Location', 'tour-booking-manager'); ?></option>
								<?php foreach ($locations as $location) { ?>
									<?php $name = get_term_meta($location->term_id, 'ttbm_country_location'); ?>
                                    <option value="<?php echo esc_attr($location->term_id); ?>" <?php echo esc_attr($url && $location->term_id == $url ? 'selected' : ''); ?>>
										<?php echo esc_html($location->name); ?>
										<?php
											if (is_array($name) && sizeof($name) > 0) {
												echo esc_html(' - ' . $name[0]);
											}
										?>
                                    </option>
								<?php } ?>
                            </select>
                        </label>
						<?php
					}
				}
			}
			public function location_filter_multiple($params) {
				if ($params['location-filter'] == 'yes') {
					$upcomming_date = $this->upcomming_date;
					$locations = TTBM_Function::get_meta_values('ttbm_location_name', 'ttbm_tour');
					$exist_locations = [];
					for ($i = 0; $i < count($locations); $i++) {
						if (is_array($upcomming_date) && $upcomming_date[$i] && $locations[$i]) {
							$exist_locations[$i] = $locations[$i];
						}
					}
					$exist_locations = array_unique($exist_locations);
					if (sizeof($exist_locations) > 0) {
						$url_location = $_GET['location_filter'] ?? '';
						$current_location = $url_location ? get_term_by('id', $url_location, 'ttbm_tour_location')->term_id : '';
						?>
                        <h5 class="justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#ttbm_location_filter_multiple" data-placeholder>
							<?php esc_html_e('Filters By Location', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#ttbm_location_filter_multiple" data-placeholder>
                            <div class="groupCheckBox _dFlex flexColumn" >
                                <input type="hidden" name="location_filter_multiple" value="<?php echo esc_attr($current_location); ?>"/>
								<?php foreach ($exist_locations as $location) { ?>
									<?php $term_id = get_term_by('name', $location, 'ttbm_tour_location')->term_id; ?>
									<?php $checked = $current_location == $term_id ? 'checked' : ''; ?>
                                    <label class="customCheckboxLabel">
                                        <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr($term_id); ?>" <?php echo esc_attr($checked); ?> />
                                        <span class="customCheckbox"><?php echo esc_html($location); ?></span>
                                    </label>
								<?php } ?>
                            </div>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function country_filter($params, $countries = '') {
				if ($params['country-filter'] == 'yes') {
					$countries = is_array($countries) ? $countries : TTBM_Function::get_all_country();
					if (sizeof($countries) > 0) {
						$url = $_GET['country_filter'] ?? '';
						?>
                        <label data-placeholder>
                            <select class="formControl" name="country_filter">
                                <option value="" selected><?php esc_html_e('All Country', 'tour-booking-manager'); ?></option>
								<?php foreach ($countries as $country) { ?>
									<?php $selected = $country == $url ? 'selected' : ''; ?>
                                    <option value="<?php echo esc_attr($country); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($country); ?></option>
								<?php } ?>
                            </select>
                        </label>
						<?php
					}
				}
			}
			public function country_filter_left($params) {
				if ($params['country-filter'] == 'yes') {
					$countries = TTBM_Function::get_all_country();
					if (sizeof($countries) > 0) {
						?>
                        <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#country_filter" data-placeholder>
							<?php esc_html_e('Filters By Country', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#country_filter" data-placeholder>
							<?php $this->country_filter($params, $countries); ?>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function duration_filter($params) {
				if ($params['duration-filter'] == 'yes') {
					$durations = TTBM_Function::get_all_duration();
					if (sizeof($durations) > 0) {
						$url = $_GET['duration_filter'] ?? '';
						?>
                        <label data-placeholder>
                            <select class="formControl" name="duration_filter">
                                <option value="" selected><?php esc_html_e('All Duration', 'tour-booking-manager'); ?></option>
								<?php foreach ($durations as $duration) { ?>
									<?php if ($duration > 0) { ?>
										<?php $selected = $duration == $url ? 'selected' : ''; ?>
                                        <option value="<?php echo esc_attr($duration); ?>" <?php echo esc_attr($selected); ?>>
											<?php echo esc_html($duration); ?>&nbsp;
											<?php if ($duration == 1) {
												esc_html_e('Day Tour', 'tour-booking-manager');
											} else {
												esc_html_e('Days Tour', 'tour-booking-manager');
											} ?>
                                        </option>
									<?php } ?>
								<?php } ?>
                            </select>
                        </label>
						<?php
					}
				}
			}
			public function duration_filter_multiple($params) {
				if ($params['duration-filter'] == 'yes') {
					$durations = TTBM_Function::get_all_duration();
					if (sizeof($durations) > 0) {
						$url = $_GET['duration_filter'] ?? '';
						?>
                        <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#ttbm_duration_filter_multiple" data-placeholder>
							<?php esc_html_e('Filters By Duration', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#ttbm_duration_filter_multiple" data-placeholder>
                            <div class="groupCheckBox _dFlex flexColumn">
                                <input type="hidden" name="duration_filter_multiple" value="<?php echo esc_attr($url); ?>"/>
								<?php foreach ($durations as $duration) { ?>
									<?php if ($duration > 0) { ?>
										<?php $checked = $url == $duration ? 'checked' : ''; ?>
                                        <label class="customCheckboxLabel">
                                            <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr($duration); ?>" <?php echo esc_attr($checked); ?>/>
                                            <span class="customCheckbox">

											<?php esc_html_e($duration); ?>&nbsp;

											<?php if ($duration == 1) {
												esc_html_e('Day Tour', 'tour-booking-manager');
											} else {
												esc_html_e('Days Tour', 'tour-booking-manager');
											} ?>

										</span>
                                        </label>
									<?php } ?>
								<?php } ?>
                            </div>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function feature_filter_multiple($params) {
				if ($params['feature-filter'] == 'yes') {
					$features = TTBM_Function::get_meta_values('ttbm_service_included_in_price', 'ttbm_tour');
					$upcomming_date = $this->upcomming_date;
					$exist_feature = [];
					for ($i = 0; $i < count($features); $i++) {
						if ($upcomming_date[$i]) {
							$exist_feature = array_unique(array_merge($exist_feature, unserialize($features[$i])));
						}
					}
					if (sizeof($exist_feature) > 0) {
						$url = $_GET['feature_filter'] ?? '';
						?>
                        <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#feature_filter_multiple" data-placeholder>
							<?php esc_html_e('Filters By Feature', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#feature_filter_multiple" data-placeholder>
                            <div class="groupCheckBox _dFlex flexColumn">
                                <input type="hidden" name="feature_filter_multiple" value="<?php echo esc_attr($url); ?>"/>
								<?php foreach ($exist_feature as $feature_item) { ?>
									<?php
									$term_id = get_term_by('name', $feature_item, 'ttbm_tour_features_list')->term_id;
									$icon = get_term_meta($term_id, 'ttbm_feature_icon', true) ? get_term_meta($term_id, 'ttbm_feature_icon', true) : 'fas fa-forward'; ?>
                                    <label class="customCheckboxLabel">
                                        <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr($term_id); ?>"/>
                                        <span class="customCheckbox"><span class="mR_xs <?php echo esc_attr($icon); ?>"></span><?php esc_html_e($feature_item); ?></span>
                                    </label>
								<?php } ?>
                            </div>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function activity_filter($params) {
				if ($params['activity-filter'] == 'yes') {
					$activities = MP_Global_Function::get_taxonomy('ttbm_tour_activities');
					if (sizeof($activities) > 0) {
						$url_activity = $_GET['activity_filter'] ?? '';
						$current_activity = $url_activity ? get_term_by('id', $url_activity, 'ttbm_tour_activities')->term_id : '';
						?>
                        <label data-placeholder>
                            <select class="formControl" name="activity_filter">
                                <option selected value=""><?php esc_html_e('All Activity', 'tour-booking-manager'); ?></option>
								<?php foreach ($activities as $activity) { ?>
									<?php $selected = $current_activity == $activity->term_id ? 'selected' : ''; ?>
                                    <option value="<?php echo esc_attr($activity->term_id); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($activity->name); ?></option>
								<?php } ?>
                            </select>
                        </label>
						<?php
					}
				}
			}
			public function activity_filter_multiple($params) {
				if ($params['activity-filter'] == 'yes') {
					$activities = TTBM_Function::get_meta_values('ttbm_tour_activities', 'ttbm_tour');
					$upcomming_date = $this->upcomming_date;
					$exist_activities = [];
					for ($i = 0; $i < count($activities); $i++) {
						if ($upcomming_date[$i] && is_array($activities[$i])) {
							$exist_activities = array_unique(array_merge($exist_activities, unserialize($activities[$i])));
						}
					}
					if (sizeof($exist_activities) > 0) {
						$url_activity = $_GET['activity_filter'] ?? '';
						$current_activity = $url_activity ? get_term_by('id', $url_activity, 'ttbm_tour_activities')->term_id : '';
						?>
                        <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#activity_filter_multiple" data-placeholder>
							<?php esc_html_e('Filter By Activity', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#activity_filter_multiple" data-placeholder>
                            <div class="groupCheckBox _dFlex flexColumn">
                                <input type="hidden" name="activity_filter_multiple" value="<?php echo esc_attr($current_activity); ?>"/>
								<?php foreach ($exist_activities as $activity) { ?>
									<?php $term_id = get_term_by('name', $activity, 'ttbm_tour_activities')->term_id; ?>
									<?php $checked = $current_activity == $term_id ? 'checked' : ''; ?>
                                    <label class="customCheckboxLabel">
                                        <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr($term_id); ?>" <?php echo esc_attr($checked); ?>/>
                                        <span class="customCheckbox"><?php esc_html_e($activity); ?></span>
                                    </label>
								<?php } ?>
                            </div>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function tag_filter_multiple($params) {
				if ($params['tag-filter'] == 'yes') {
					$tags = MP_Global_Function::get_taxonomy('ttbm_tour_tag');
					$total_tags = 0;
					foreach ($tags as $tag) {
						if (get_term($tag->term_id, 'ttbm_tour_tag')->count) {
							$total_tags++;
						}
					}
					if (sizeof($tags) > 0 && $total_tags) {
						?>
                        <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#tag_filter_multiple" data-placeholder>
							<?php esc_html_e('Specials', 'tour-booking-manager'); ?>
                            <span data-icon class="fas fa-chevron-down"></span>
                        </h5>
                        <div class="divider"></div>
                        <div class="mActive" data-collapse="#tag_filter_multiple" data-placeholder>
                            <div class="groupCheckBox _dFlex flexColumn">
                                <input type="hidden" name="tag_filter_multiple" value=""/>
								<?php foreach ($tags as $tag) {
									if (get_term($tag->term_id, 'ttbm_tour_tag')->count) { ?>
                                        <label class="customCheckboxLabel">
                                            <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr($tag->term_id); ?>"/>
                                            <span class="customCheckbox"><?php esc_html_e($tag->name); ?></span>
                                        </label>
									<?php }
								} ?>
                            </div>
                        </div>
						<?php
					}
				}
			}
			//****************************************/
			public function month_filter($params) {
				if ($params['month-filter'] == 'yes') {
					$url_month = $_GET['month_filter'] ?? '';
					$first_date = date('Y-m-01');
					$current_date = current_time('Y-m-d');
					$month = date('n', strtotime($current_date));
					$selected = $url_month == $month ? 'selected' : '';
					$date_format = MP_Global_Function::get_settings('mp_global_settings', 'date_format_short', 'M , Y');
					?>
                    <label data-placeholder>
                        <select class="formControl" name="month_filter">
                            <option selected value=""><?php esc_html_e('All Month', 'tour-booking-manager'); ?></option>
                            <option value="<?php echo esc_attr($month); ?>" <?php echo esc_attr($selected); ?>><?php echo date_i18n($date_format, strtotime($current_date)); ?></option>
							<?php
								for ($i = 1; $i < 12; $i++) {
									$first_date = date('y-m-d', strtotime("+1 month", strtotime($first_date)));
									$month = date('n', strtotime($first_date));
									$selected = $url_month == $month ? 'selected' : '';
									?>
                                    <option value="<?php echo esc_attr($month); ?>" <?php echo esc_attr($selected); ?>><?php echo date_i18n($date_format, strtotime($first_date)); ?></option>
									<?php
								}
							?>
                        </select>
                    </label>
					<?php
				}
			}
			public function month_filter_left($params) {
				if ($params['month-filter'] == 'yes') { ?>
                    <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#month_filter_left" data-placeholder>
						<?php esc_html_e('Filters By Month', 'tour-booking-manager'); ?>
                        <span data-icon class="fas fa-chevron-down"></span>
                    </h5>
                    <div class="divider"></div>
                    <div class="mActive" data-collapse="#month_filter_left" data-placeholder>
						<?php $this->month_filter($params); ?>
                    </div>
				<?php }
			}
			//****************************************/
			public function pagination($params, $total_item, $active_page = 0) {
				ob_start();
				$per_page = $params['show'] > 1 ? $params['show'] : $total_item;
				$search_filter = array_key_exists('search-filter', $params) ? $params['search-filter'] : '';
				?>
                <input type="hidden" name="pagination_per_page" value="<?php echo esc_attr($per_page); ?>"/>
                <input type="hidden" name="pagination_style" value="<?php echo esc_attr($params['pagination-style']); ?>"/>
				<?php if (($search_filter == 'yes' || $params['pagination'] == 'yes') && $total_item > $per_page) { ?>
                    <div class="allCenter pagination_area" data-placeholder>
						<?php
							if ($params['pagination-style'] == 'load_more') {
								?>
                                <button type="button" class="_dButton_min_200 pagination_load_more" data-load-more="0">
									<?php esc_html_e('Load More', 'tour-booking-manager'); ?>
                                </button>
								<?php
							} else {
								$page_mod = $total_item % $per_page;
								$total_page = (int)($total_item / $per_page) + ($page_mod > 0 ? 1 : 0);
								?>
                                <div class="buttonGroup">
									<?php if ($total_page > 2) { ?>
                                        <button class="dButton_xs page_prev" type="button" title="<?php esc_html_e('GoTO Previous Page', 'tour-booking-manager'); ?>" disabled>
                                            <span class="fas fa-chevron-left mp_zero"></span>
                                        </button>
									<?php } ?>

									<?php if ($total_page > 5) { ?>
                                        <button class="dButton_xs ellipse_left" type="button" disabled>
                                            <span class="fas fa-ellipsis-h mp_zero"></span>
                                        </button>
									<?php } ?>

									<?php for ($i = 0; $i < $total_page; $i++) { ?>
                                        <button class="dButton_xs <?php echo esc_html($i) == $active_page ? 'active_pagination' : ''; ?>" type="button" data-pagination="<?php echo esc_html($i); ?>"><?php echo esc_html($i + 1); ?></button>
									<?php } ?>

									<?php if ($total_page > 5) { ?>
                                        <button class="dButton_xs ellipse_right" type="button" disabled>
                                            <span class="fas fa-ellipsis-h mp_zero"></span>
                                        </button>
									<?php } ?>

									<?php if ($total_page > 2) { ?>
                                        <button class="dButton_xs page_next" type="button" title="<?php esc_html_e('GoTO Next Page', 'tour-booking-manager'); ?>">
                                            <span class="fas fa-chevron-right mp_zero"></span>
                                        </button>
									<?php } ?>
                                </div>
							<?php } ?>
                    </div>
					<?php
				}
				echo ob_get_clean();
			}
			public function filter_top_bar($loop, $params) {
				$style = $params['style'] ?: 'modern';
				$style = $style == 'list' ? 'modern' : $style;
				if (is_page('find')) {
					?>
                    <div class="placeholder_area filter_top_bar justifyBetween">
						<span>
							<strong class="total_filter_qty"><?php echo esc_html($loop->post_count); ?></strong>
							<?php esc_html_e(' Trips match your search criteria', 'tour-booking-manager'); ?>
						</span>
                        <div class="dFlex">
                            <button class="ttbm_grid_view " type="button" <?php echo esc_attr($style == 'grid' ? 'disabled' : ''); ?> title="<?php esc_attr_e('Grid view', 'tour-booking-manager'); ?>">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button class="ttbm_list_view" type="button" <?php echo esc_attr($style == 'modern' ? 'disabled' : ''); ?> title="<?php esc_attr_e('LIst view', 'tour-booking-manager'); ?>">
                                <i class="fas fa-th-list"></i>
                            </button>
                        </div>
                    </div>
					<?php
				}
			}
			public function sort_result($loop, $params) {
				?>
                <div class="search_result_empty" data-placeholder><?php esc_html_e('No Match Result Found!', 'tour-booking-manager'); ?></div>
                <div class="filter_short_result" data-placeholder>
					<?php esc_html_e('Showing', 'tour-booking-manager'); ?>
                    <strong class="qty_count"><?php echo esc_html($params['show']); ?></strong>
					<?php esc_html_e('of', 'tour-booking-manager'); ?>
                    <strong class="total_filter_qty"><?php echo esc_html($loop->post_count); ?></strong>
                </div>
				<?php
			}
			//**************************************//
		}
		new  TTBM_Filter_Pagination();
	}
