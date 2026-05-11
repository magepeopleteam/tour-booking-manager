<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Tour_List')) {
		class TTBM_Tour_List {
			public function __construct() {
				add_action('ttbm_all_list_item', array($this, 'all_list_item'), 10, 2);
				add_action('ttbm_all_grid_list', array($this, 'all_grid_list_item'), 10, 2);
			}

			public function all_list_item($loop, $params) {
				$style = $params['style'] ?: 'modern';
				$style = $style == 'list' ? 'modern' : $style;
				$grid_class = 'grid_' . $params['column'];
				$per_page = $params['show'] > 1 ? $params['show'] : $loop->post_count;
				$count = 0;
				$category_filter = '';
				$title_filter = '';
				$type_filter = '';
				$organizer_filter = '';
				$location_filter = '';
				$country_filter = '';
				$month_filter = '';
				$feature_filter = '';
				$tag_filter = '';
				$duration_filter = '';
				$activity_filter = '';
				if (isset($_GET['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
					$category_filter = isset($_GET['category_filter']) ? sanitize_text_field(wp_unslash($_GET['category_filter'])) : '';
					$title_filter = isset($_GET['title_filter']) ? sanitize_text_field(wp_unslash($_GET['title_filter'])) : '';
					$type_filter = isset($_GET['type_filter']) ? sanitize_text_field(wp_unslash($_GET['type_filter'])) : '';
					$organizer_filter = isset($_GET['organizer_filter']) ? sanitize_text_field(wp_unslash($_GET['organizer_filter'])) : '';
					$location_filter = isset($_GET['location_filter']) ? sanitize_text_field(wp_unslash($_GET['location_filter'])) : '';
					$country_filter = isset($_GET['country_filter']) ? sanitize_text_field(wp_unslash($_GET['country_filter'])) : '';
					$month_filter = isset($_GET['month_filter']) ? sanitize_text_field(wp_unslash($_GET['month_filter'])) : '';
					$feature_filter = isset($_GET['feature_filter']) ? sanitize_text_field(wp_unslash($_GET['feature_filter'])) : '';
					$tag_filter = isset($_GET['tag_filter']) ? sanitize_text_field(wp_unslash($_GET['tag_filter'])) : '';
					$duration_filter = isset($_GET['duration_filter']) ? sanitize_text_field(wp_unslash($_GET['duration_filter'])) : '';
					$activity_filter = isset($_GET['activity_filter']) ? sanitize_text_field(wp_unslash($_GET['activity_filter'])) : '';
				}
				$sortable_tours = [];
				$term_ids = '';
				$terms_id_array = [];
				foreach ($loop->posts as $tour) {
					$ttbm_post_id = $tour->ID;
					$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
					$display_order = get_post_meta($tour_id, 'ttbm_display_order_tour', true);
					$travel_rank = get_post_meta($tour_id, 'ttbm_travel_rank_tour', true);
					if (isset($params['filter_by_activity']) && $params['filter_by_activity'] === 'yes') {
						$terms = TTBM_Function::get_taxonomy_name_to_id_string($tour_id, 'ttbm_tour_activities', 'ttbm_tour_activities');
						$term_ids .= $terms . ',';
					}
					if ($display_order == 'on') {
						$sortable_tours[] = [
							'tour' => $tour,
							'rank' => (int)$travel_rank
						];
					} else {
						$sortable_tours[] = [
							'tour' => $tour,
							'rank' => PHP_INT_MAX
						];
					}
				}
				usort($sortable_tours, function ($a, $b) {
					return $a['rank'] <=> $b['rank'];
				});
				if ($term_ids !== '') {
					$term_ids = rtrim($term_ids, ',');
					$terms_id_array = array_unique(explode(',', $term_ids));
				}
				$activities = TTBM_Global_Function::get_taxonomy('ttbm_tour_activities');
				?>
                <div class="all_filter_item">
						<?php if ($params['filter_by_activity'] === 'yes') { ?>
                        <div class="ttbm_all_item_activities_wrapper">
                            <button class="scroll-left">←</button>
                            <div class="ttbm_all_item_activities_holder">
							<?php
									if (is_array($activities) && count($activities) > 0) {
										foreach ($activities as $activitie) {
											if (in_array($activitie->term_id, $terms_id_array)) {
										?>
										<div class="ttbm_item_activity">
                                                    <div class="ttbm_item_filter_by_activity" id="<?php echo esc_attr($activitie->term_id); ?>">
														<?php echo esc_attr($activitie->name); ?>
											</div>
										</div>
											<?php }
								}
							}
							?>
					</div>
                            <button class="scroll-right">→</button>
				</div>
					<?php } ?>
                    <div class="flexWrap <?php echo esc_attr($style); ?>">
						<?php foreach ($sortable_tours as $tour_data) {
							$tour = $tour_data['tour'];
							$ttbm_post_id = $tour->ID;
							$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
							//if ($ttbm_post_id == $tour_id) {
							$active_class = $count < $per_page ? $grid_class : $grid_class . ' dNone';
							$count++;
							?>
                            <div class="filter_item placeholder_area <?php echo esc_attr($active_class); ?>"
								<?php if ($params['title-filter'] == 'yes' || $title_filter) { ?>
                                    data-title="<?php echo esc_attr(get_the_title($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['type-filter'] == 'yes' || $type_filter) { ?>
                                    data-type="<?php echo esc_attr(TTBM_Function::get_tour_type($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['category-filter'] == 'yes' || $category_filter) { ?>
                                    data-category="<?php echo esc_attr(TTBM_Function::get_taxonomy_id_string($tour_id, 'ttbm_tour_cat')); ?>"
								<?php } ?>
								<?php if ($params['organizer-filter'] == 'yes' || $organizer_filter) { ?>
                                    data-organizer="<?php echo esc_attr(TTBM_Function::get_taxonomy_id_string($tour_id, 'ttbm_tour_org')); ?>"
								<?php } ?>
								<?php if ($params['location-filter'] == 'yes' || $location_filter) {
									$location = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_location_name');
									$location_id = $location ? get_term_by('name', $location, 'ttbm_tour_location')->term_id : '';
									?>
                                    data-location="<?php echo esc_attr($location_id); ?>"
								<?php } ?>
								<?php if ($params['country-filter'] == 'yes' || $country_filter) { ?>
                                    data-country="<?php echo esc_attr(TTBM_Function::get_country($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['month-filter'] == 'yes' || $month_filter) { ?>
                                    data-month="<?php echo esc_attr(TTBM_Global_Function::get_post_info($tour_id, 'ttbm_month_list')); ?>"
								<?php } ?>
								<?php
									if ($params['feature-filter'] == 'yes' || $feature_filter) {
										$include_services = TTBM_Function::get_feature_list($tour_id, 'ttbm_service_included_in_price');
										?>
                                        data-feature="<?php echo esc_attr(TTBM_Function::feature_array_to_string($include_services)); ?>"
									<?php } ?>
								<?php
									if ($params['tag-filter'] == 'yes' || $tag_filter) {
										$tour_tags = wp_get_post_terms($tour_id, 'ttbm_tour_tag', array("fields" => "all"));
										?>
                                        data-tag="<?php echo esc_attr(TTBM_Function::get_tag_id($tour_tags)); ?>"
									<?php } ?>
								<?php if ($params['duration-filter'] == 'yes' || $duration_filter) { ?>
                                    data-duration="<?php echo esc_attr(TTBM_Function::get_duration($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['activity-filter'] == 'yes' || $activity_filter) { ?>
                                    data-activity="<?php echo esc_attr(TTBM_Function::get_taxonomy_name_to_id_string($tour_id, 'ttbm_tour_activities', 'ttbm_tour_activities')); ?>"
								<?php } ?>
                            >
                                <input type="hidden" name="ttbm_item_activities" value="<?php echo esc_attr(TTBM_Function::get_taxonomy_name_to_id_string($tour_id, 'ttbm_tour_activities', 'ttbm_tour_activities')); ?>"/>
								<?php
									if ($params['style'] == 'blossom') {
										include(TTBM_Function::template_path('list/blossom_list.php'));
									} elseif ($params['style'] == 'flora') {
										include(TTBM_Function::template_path('list/flora_list.php'));
									} elseif ($params['style'] == 'orchid') {
										include(TTBM_Function::template_path('list/orchid_list.php'));
									} elseif ($params['style'] == 'lotus') {
										include(TTBM_Function::template_path('list/lotus_list.php'));
									} elseif ($params['style'] == 'grid') {
										include(TTBM_Function::template_path('list/grid_list.php'));
									} else {
										include(TTBM_Function::template_path('list/default.php'));
									}
								?>
                            </div>
							<?php //} ?>
						<?php } ?>
                    </div>
                </div>
				<?php
			}

			public function all_grid_list_item($loop, $params) {
				$style = $params['style'] ?: 'modern';
				$style = $style == 'list' ? 'modern' : $style;
				$grid_class = 'grid_' . $params['column'];
				$per_page = $params['show'] > 1 ? $params['show'] : $loop->post_count;
				$count = 0;
				$category_filter = '';
				$title_filter = '';
				$type_filter = '';
				$organizer_filter = '';
				$location_filter = '';
				$country_filter = '';
				$month_filter = '';
				$feature_filter = '';
				$tag_filter = '';
				$duration_filter = '';
				$activity_filter = '';
				if (isset($_GET['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
					$category_filter = isset($_GET['category_filter']) ? sanitize_text_field(wp_unslash($_GET['category_filter'])) : '';
					$title_filter = isset($_GET['title_filter']) ? sanitize_text_field(wp_unslash($_GET['title_filter'])) : '';
					$type_filter = isset($_GET['type_filter']) ? sanitize_text_field(wp_unslash($_GET['type_filter'])) : '';
					$organizer_filter = isset($_GET['organizer_filter']) ? sanitize_text_field(wp_unslash($_GET['organizer_filter'])) : '';
					$location_filter = isset($_GET['location_filter']) ? sanitize_text_field(wp_unslash($_GET['location_filter'])) : '';
					$country_filter = isset($_GET['country_filter']) ? sanitize_text_field(wp_unslash($_GET['country_filter'])) : '';
					$month_filter = isset($_GET['month_filter']) ? sanitize_text_field(wp_unslash($_GET['month_filter'])) : '';
					$feature_filter = isset($_GET['feature_filter']) ? sanitize_text_field(wp_unslash($_GET['feature_filter'])) : '';
					$tag_filter = isset($_GET['tag_filter']) ? sanitize_text_field(wp_unslash($_GET['tag_filter'])) : '';
					$duration_filter = isset($_GET['duration_filter']) ? sanitize_text_field(wp_unslash($_GET['duration_filter'])) : '';
					$activity_filter = isset($_GET['activity_filter']) ? sanitize_text_field(wp_unslash($_GET['activity_filter'])) : '';
				}
				$sortable_tours = [];
				$term_ids = '';
				$terms_id_array = [];
				foreach ($loop->posts as $tour) {
					$ttbm_post_id = $tour->ID;
					$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
					$display_order = get_post_meta($tour_id, 'ttbm_display_order_tour', true);
					$travel_rank = get_post_meta($tour_id, 'ttbm_travel_rank_tour', true);
					if (isset($params['filter_by_activity']) && $params['filter_by_activity'] === 'yes') {
						$terms = TTBM_Function::get_taxonomy_name_to_id_string($tour_id, 'ttbm_tour_activities', 'ttbm_tour_activities');
						$term_ids .= $terms . ',';
					}
					if ($display_order == 'on') {
						$sortable_tours[] = [
							'tour' => $tour,
							'rank' => (int)$travel_rank
						];
					} else {
						$sortable_tours[] = [
							'tour' => $tour,
							'rank' => PHP_INT_MAX
						];
					}
				}
				usort($sortable_tours, function ($a, $b) {
					return $a['rank'] <=> $b['rank'];
				});
				if ($term_ids !== '') {
					$term_ids = rtrim($term_ids, ',');
					$terms_id_array = array_unique(explode(',', $term_ids));
				}
				$activities = TTBM_Global_Function::get_taxonomy('ttbm_tour_activities');
				?>
                <div class="all_filter_item <?php //echo esc_attr($style); ?>">
					<!-- ═══ Modern Filter Bar ═══════════════════════════════════════ -->
					<div class="ttbm-filter-bar">

						<!-- Left: date-range tab pills -->
						<div class="ttbm-filter-tabs">
							<button type="button" class="ttbm-tab-btn ttbm-tab-active" data-filter-tab="all">
								<?php esc_html_e( 'All Tours', 'tour-booking-manager' ); ?>
							</button>
							<button type="button" class="ttbm-tab-btn" data-filter-tab="week">
								<?php esc_html_e( 'This Week', 'tour-booking-manager' ); ?>
							</button>
							<button type="button" class="ttbm-tab-btn" data-filter-tab="month">
								<?php esc_html_e( 'This Month', 'tour-booking-manager' ); ?>
							</button>
							<button type="button" class="ttbm-tab-btn" data-filter-tab="year">
								<?php esc_html_e( 'This Year', 'tour-booking-manager' ); ?>
							</button>

							<?php if (isset($params['filter_by_activity']) && $params['filter_by_activity'] === 'yes') { ?>
							<?php /* Hidden scrollable activity pills (still functional via JS) */ ?>
							<div class="ttbm_all_item_activities_holder ttbm-activity-pills-hidden">
								<?php
								if ( is_array( $activities ) && count( $activities ) > 0 ) {
									foreach ( $activities as $activitie ) {
										if ( in_array( $activitie->term_id, $terms_id_array ) ) {
											?>
											<div class="ttbm_item_activity">
												<div class="ttbm_item_filter_by_activity" id="<?php echo esc_attr( $activitie->term_id ); ?>">
													<?php echo esc_html( $activitie->name ); ?>
												</div>
											</div>
											<?php
										}
									}
								}
								?>
							</div>
							<?php } ?>
						</div>

						<!-- Right: Sort + View switcher -->
						<div class="ttbm-filter-controls">

							<!-- Sort dropdown -->
							<div class="ttbm-sort-dropdown">
								<select class="ttbm-sort-select formControl" name="sort_by_filter" aria-label="<?php esc_attr_e( 'Sort tours', 'tour-booking-manager' ); ?>">
									<option value=""><?php esc_html_e( 'Sort by: Most Popular', 'tour-booking-manager' ); ?></option>
									<option value="price_asc"><?php esc_html_e( 'Price: Low to High', 'tour-booking-manager' ); ?></option>
									<option value="price_desc"><?php esc_html_e( 'Price: High to Low', 'tour-booking-manager' ); ?></option>
									<option value="date_desc"><?php esc_html_e( 'Newest First', 'tour-booking-manager' ); ?></option>
									<option value="title_asc"><?php esc_html_e( 'Title: A–Z', 'tour-booking-manager' ); ?></option>
								</select>
							</div>

							<!-- View switcher -->
							<div class="ttbm-view-switcher" role="group" aria-label="<?php esc_attr_e( 'View mode', 'tour-booking-manager' ); ?>">
								<button type="button" class="ttbm-view-btn ttbm_grid_view ttbm-view-active" title="<?php esc_attr_e( 'Grid View', 'tour-booking-manager' ); ?>" aria-pressed="true">
									<span class="mi mi-grid"></span>
								</button>
								<button type="button" class="ttbm-view-btn ttbm_list_view" title="<?php esc_attr_e( 'List View', 'tour-booking-manager' ); ?>" aria-pressed="false">
									<span class="mi mi-list"></span>
								</button>
							</div>

						</div>
					</div>
					<!-- ═══════════════════════════════════════════════════════════ -->
                    <div class="flexWrap">
						<?php foreach ($sortable_tours as $tour_data) {
							$tour = $tour_data['tour'];
							$ttbm_post_id = $tour->ID;
							$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
							//if ($ttbm_post_id == $tour_id) {
							$active_class = $count < $per_page ? $grid_class : $grid_class . ' dNone';
							$count++;
							?>
                            <div class="filter_item placeholder_area <?php echo esc_attr($active_class); ?>"
								<?php if ($params['title-filter'] == 'yes' || $title_filter) { ?>
                                    data-title="<?php echo esc_attr(get_the_title($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['type-filter'] == 'yes' || $type_filter) { ?>
                                    data-type="<?php echo esc_attr(TTBM_Function::get_tour_type($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['category-filter'] == 'yes' || $category_filter) { ?>
                                    data-category="<?php echo esc_attr(TTBM_Function::get_taxonomy_id_string($tour_id, 'ttbm_tour_cat')); ?>"
								<?php } ?>
								<?php if ($params['organizer-filter'] == 'yes' || $organizer_filter) { ?>
                                    data-organizer="<?php echo esc_attr(TTBM_Function::get_taxonomy_id_string($tour_id, 'ttbm_tour_org')); ?>"
								<?php } ?>
								<?php if ($params['location-filter'] == 'yes' || $location_filter) {
									$location = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_location_name');
									$location_id = $location ? get_term_by('name', $location, 'ttbm_tour_location')->term_id : '';
									?>
                                    data-location="<?php echo esc_attr($location_id); ?>"
								<?php } ?>
								<?php if ($params['country-filter'] == 'yes' || $country_filter) { ?>
                                    data-country="<?php echo esc_attr(TTBM_Function::get_country($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['month-filter'] == 'yes' || $month_filter) { ?>
                                    data-month="<?php echo esc_attr(TTBM_Global_Function::get_post_info($tour_id, 'ttbm_month_list')); ?>"
								<?php } ?>
								<?php
									if ($params['feature-filter'] == 'yes' || $feature_filter) {
										$include_services = TTBM_Function::get_feature_list($tour_id, 'ttbm_service_included_in_price');
										?>
                                        data-feature="<?php echo esc_attr(TTBM_Function::feature_array_to_string($include_services)); ?>"
									<?php } ?>
								<?php
									if ($params['tag-filter'] == 'yes' || $tag_filter) {
										$tour_tags = wp_get_post_terms($tour_id, 'ttbm_tour_tag', array("fields" => "all"));
										?>
                                        data-tag="<?php echo esc_attr(TTBM_Function::get_tag_id($tour_tags)); ?>"
									<?php } ?>
								<?php if ($params['duration-filter'] == 'yes' || $duration_filter) { ?>
                                    data-duration="<?php echo esc_attr(TTBM_Function::get_duration($tour_id)); ?>"
								<?php } ?>
								<?php if ($params['activity-filter'] == 'yes' || $activity_filter) { ?>
                                    data-activity="<?php echo esc_attr(TTBM_Function::get_taxonomy_name_to_id_string($tour_id, 'ttbm_tour_activities', 'ttbm_tour_activities')); ?>"
								<?php } ?>

								data-price="<?php echo esc_attr(get_post_meta($tour_id, 'ttbm_price', true)); ?>"
								data-date="<?php echo esc_attr(get_the_date('Y-m-d', $tour_id)); ?>"
                            >
                                <input type="hidden" name="ttbm_item_activities" value="<?php echo esc_attr(TTBM_Function::get_taxonomy_name_to_id_string($tour_id, 'ttbm_tour_activities', 'ttbm_tour_activities')); ?>"/>
								<?php
									if ($params['style'] == 'blossom') {
										include(TTBM_Function::template_path('list/blossom_list.php'));
									} elseif ($params['style'] == 'flora') {
										include(TTBM_Function::template_path('list/flora_list.php'));
									} elseif ($params['style'] == 'orchid') {
										include(TTBM_Function::template_path('list/orchid_list.php'));
									} elseif ($params['style'] == 'lotus') {
										include(TTBM_Function::template_path('list/lotus_list.php'));
									} elseif ($params['style'] == 'grid') {
										include(TTBM_Function::template_path('list/grid_list.php'));
									} else {
										include(TTBM_Function::template_path('list/grid_list_style.php'));
									}
								?>
                            </div>
							<?php //} ?>
						<?php } ?>
                    </div>
                </div>
				<?php
			}
		}
		new TTBM_Tour_List();
	}
