<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Tour_List')) {
		class TTBM_Tour_List {
			public function __construct() {
				add_action('ttbm_all_list_item', array($this, 'all_list_item'), 10, 2);
			}
			public function all_list_item($loop, $params) {
				$style = $params['style'] ?: 'modern';
				$style = $style == 'list' ? 'modern' : $style;
				$grid_class = 'grid_' . $params['column'];
				$per_page = $params['show'] > 1 ? $params['show'] : $loop->post_count;
				$count = 0;
				$category_filter=isset($_GET['category_filter']) && $_GET['category_filter']?$_GET['category_filter']:'';
				$title_filter=isset($_GET['title_filter']) && $_GET['title_filter']?$_GET['title_filter']:'';
				$type_filter=isset($_GET['type_filter']) && $_GET['type_filter']?$_GET['type_filter']:'';
				$organizer_filter=isset($_GET['organizer_filter']) && $_GET['organizer_filter']?$_GET['organizer_filter']:'';
				$location_filter=isset($_GET['location_filter']) && $_GET['location_filter']?$_GET['location_filter']:'';
				$country_filter=isset($_GET['country_filter']) && $_GET['country_filter']?$_GET['country_filter']:'';
				$month_filter=isset($_GET['month_filter']) && $_GET['month_filter']?$_GET['month_filter']:'';
				$feature_filter=isset($_GET['feature_filter']) && $_GET['feature_filter']?$_GET['feature_filter']:'';
				$tag_filter=isset($_GET['tag_filter']) && $_GET['tag_filter']?$_GET['tag_filter']:'';
				$duration_filter=isset($_GET['duration_filter']) && $_GET['duration_filter']?$_GET['duration_filter']:'';
				$activity_filter=isset($_GET['activity_filter']) && $_GET['activity_filter']?$_GET['activity_filter']:'';
				 $sortable_tours = [];

					foreach ($loop->posts as $tour) {
						$ttbm_post_id = $tour->ID;
						$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
						$display_order = get_post_meta($tour_id, 'ttbm_display_order_tour', true);
						$travel_rank = get_post_meta($tour_id, 'ttbm_travel_rank_tour', true);

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

					usort($sortable_tours, function($a, $b) {
						return $a['rank'] <=> $b['rank'];
					});
				?>
				<div class="all_filter_item">
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
										<?php if ($params['category-filter'] == 'yes'  || $category_filter) { ?>
											data-category="<?php echo esc_attr(TTBM_Function::get_taxonomy_id_string($tour_id, 'ttbm_tour_cat')); ?>"
										<?php } ?>
										<?php if ($params['organizer-filter'] == 'yes' || $organizer_filter) { ?>
											data-organizer="<?php echo esc_attr(TTBM_Function::get_taxonomy_id_string($tour_id, 'ttbm_tour_org')); ?>"
										<?php } ?>
										<?php if ($params['location-filter'] == 'yes' || $location_filter) {
											$location = MP_Global_Function::get_post_info($tour_id, 'ttbm_location_name');
											$location_id = $location ? get_term_by('name', $location, 'ttbm_tour_location')->term_id : '';
											?>
											data-location="<?php echo esc_attr($location_id); ?>"
										<?php } ?>
										<?php if ($params['country-filter'] == 'yes' || $country_filter) { ?>
											data-country="<?php echo esc_attr(TTBM_Function::get_country($tour_id)); ?>"
										<?php } ?>
										<?php if ($params['month-filter'] == 'yes' || $month_filter) { ?>
											data-month="<?php echo esc_attr(MP_Global_Function::get_post_info($tour_id, 'ttbm_month_list')); ?>"
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
										<?php
											if ($params['style'] == 'blossom') {
												include(TTBM_Function::template_path('list/blossom_list.php'));
											}
											elseif ($params['style'] == 'flora') {
												include(TTBM_Function::template_path('list/flora_list.php'));
											}
											elseif ($params['style'] == 'orchid') {
												include(TTBM_Function::template_path('list/orchid_list.php'));
											}
											elseif ($params['style'] == 'lotus') {
												include(TTBM_Function::template_path('list/lotus_list.php'));
											}
											elseif ($params['style'] == 'grid') {
												include(TTBM_Function::template_path('list/grid_list.php'));
											}
											else {
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
		}
		new TTBM_Tour_List();
	}
