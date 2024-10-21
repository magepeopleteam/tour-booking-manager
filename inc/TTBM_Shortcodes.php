<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Shortcode')) {
		class TTBM_Shortcode {
			public function __construct() {
				add_shortcode('ttbm-top-search', array($this, 'static_filter'));
				add_shortcode('travel-list', array($this, 'list_with_left_filter'));
				add_shortcode('ttbm-top-filter', array($this, 'list_with_top_filter'));
				add_shortcode('travel-location-list', array($this, 'location_list'));
				add_shortcode('ttbm-search-result', array($this, 'search_result'));
				add_shortcode('ttbm-hotel-list', array($this, 'hotel_list'));
				add_shortcode('ttbm-registration', array($this, 'registration'));
				add_shortcode('ttbm-related', array($this, 'related'));
			}
			public function static_filter($attribute) {
				$defaults = $this->default_attribute();
				$params = shortcode_atts($defaults, $attribute);
				ob_start();
				do_action('ttbm_top_filter_static', $params);
				return ob_get_clean();
			}
			public function list_with_left_filter($attribute, $tour_type = '', $month_filter = 'yes') {
				$defaults = $this->default_attribute('modern', 12, 'no', 'yes', 'yes', 'yes', $month_filter, $tour_type);
				$params = shortcode_atts($defaults, $attribute);
				$show = $params['show'];
				$pagination = $params['pagination'];
				$search = $params['sidebar-filter'];
				$show = ($search == 'yes' || $pagination == 'yes') ? -1 : $show;
				$loop = TTBM_Query::ttbm_query($show, $params['sort'], $params['cat'], $params['org'], $params['city'], $params['country'], $params['status'], $params['tour-type'], $params['activity'],$params['sort_by']);
				ob_start();
				?>
				<div class="mpStyle ttbm_wraper placeholderLoader ttbm_filter_area">
					<div class="mpContainer">
					<?php
						if ($params['sidebar-filter'] == 'yes') {
							?>
							<div class="left_filter">
								<div class="leftSidebar placeholder_area">
									<?php do_action('ttbm_left_filter', $params); ?>
								</div>
								<div class="mainSection">
									<?php do_action('ttbm_filter_top_bar', $loop, $params); ?>
									<?php do_action('ttbm_all_list_item', $loop, $params); ?>
									<?php do_action('ttbm_sort_result', $loop, $params); ?>
									<?php do_action('ttbm_pagination', $params, $loop->post_count); ?>
								</div>
							</div>
							<?php
						} else {
							include( TTBM_Function::template_path( 'layout/filter_hidden.php' ) );
							do_action('ttbm_all_list_item', $loop, $params);
							do_action('ttbm_sort_result', $loop, $params);
							do_action('ttbm_pagination', $params, $loop->post_count);
						}
					?>
					</div>
				</div>
				<?php
				return ob_get_clean();
			}
			public function list_with_top_filter($attribute) {
				$defaults = $this->default_attribute();
				$params = shortcode_atts($defaults, $attribute);
				$pagination = $params['pagination'];
				$search = $params['search-filter'];
				$show = $params['show'];
				$show = ($search == 'yes' || $pagination == 'yes') ? -1 : $show;
				$loop = TTBM_Query::ttbm_query($show, $params['sort'], $params['cat'], $params['org'], $params['city'], $params['country'], $params['status'], $params['tour-type'], $params['activity'],$params['sort_by']);
				ob_start();
				?>
				<div class="mpStyle ttbm_wraper placeholderLoader ttbm_filter_area">
					<div class="mpContainer">
					<?php
						if ($search == 'yes') {
							do_action('ttbm_top_filter', $params);
						}
						do_action('ttbm_all_list_item', $loop, $params);
						do_action('ttbm_sort_result', $loop, $params);
						do_action('ttbm_pagination', $params, $loop->post_count);
					?>
					</div>
				</div>
				<?php
				return ob_get_clean();
			}
			public function location_list($attribute) {
				ob_start();
				$defaults = array(
					'column' => 3,
					'show' => 3,
					'search-filter' => '',
					"pagination-style" => "load_more",
					"pagination" => "yes",
					'status' => '',
				);
				$params = shortcode_atts($defaults, $attribute);
				$status = $params['status'];
				$locations = MP_Global_Function::get_taxonomy('ttbm_tour_location');
				if (is_array($locations) && sizeof($locations)) {
					$grid_class = (int)$params['column'] > 0 ? 'grid_' . (int)$params['column'] : 'grid_1';
					?>
					<div class="mpStyle ttbm_wraper placeholderLoader ttbm_filter_area ttbm_location_list">
						<div class="mpContainer">
						<div class="all_filter_item">
							<div class="placeholder_area flexWrap">
								<?php foreach ($locations as $location) { ?>
									<div class="filter_item <?php echo esc_attr($grid_class); ?>" data-placeholder>
										<?php
											$tour_list = TTBM_Query::get_all_tour_in_location($location->name, $status);  
											$thumb_id = get_term_meta($location->term_id, 'ttbm_location_image');
											$thumbnail_img = wp_get_attachment_url($thumb_id[0]);											
										?>
										<div class="ttbm_location_image">
											<div data-bg-image="<?php echo esc_html($thumbnail_img); ?>" data-href="<?php echo esc_url(get_term_link($location->term_id)) . '?location_filter=' . esc_attr($location->term_id) . '&location_status=' . esc_attr($status); ?>">
											
											</div>
											<div class="ttbm_location_info">
												<h2> <?php echo esc_html($location->name); ?></h2>
												<p>
													<?php echo esc_html($tour_list->post_count) . esc_attr__(' - Tour Available', 'tour-booking-manager'); ?>
												</p>
											</div>
										</div>
										
									</div>
								<?php } ?>
							</div>
						</div>
						<?php do_action('ttbm_pagination', $params, count($locations)); ?>
						</div>
					</div>
					<?php
				}
				return ob_get_clean();
			}
			public function search_result($attribute) {
				ob_start();
				$type_filter = $_GET['type_filter'] ?? '';
				echo $this->list_with_left_filter($attribute, $type_filter);
				return ob_get_clean();
			}
			public function hotel_list($attribute) {
				ob_start();
				echo $this->list_with_left_filter($attribute, 'hotel', 'no');
				return ob_get_clean();
			}
			public function registration($attribute) {
				$defaults = array('ttbm_id' => '');
				$params = shortcode_atts($defaults, $attribute);
				ob_start();
				$tour_id = $params['ttbm_id'] ?? get_the_id();
				if ($tour_id) {
					?>
					<div class="mpStyle">
						<div class="mpContainer">
						<?php include(TTBM_Function::template_path('ticket/registration.php')); ?>
						</div>
					</div>
					<?php
				}
				return ob_get_clean();
			}
			public function related($attribute) {
				$defaults = array('ttbm_id' => '', 'show' => 4);
				$params = shortcode_atts($defaults, $attribute);
				ob_start();
				$tour_id = $params['ttbm_id'] ?? get_the_id();
				$num_of_tour = $params['show'];
				if ($tour_id) {
					?>
					<div class="mpStyle">
						<div class="mpContainer">
						<?php include(TTBM_Function::template_path('layout/related_tour.php')); ?>
						</div>
					</div>
					<?php
				}
				return ob_get_clean();
			}
			//***************************//
			public function default_attribute($style = 'grid', $show = 9, $search_filter = 'yes', $sidebar_filter = 'no', $feature_filter = 'no', $tag_filter = 'no', $month_filter = 'yes', $tour_type = '',$sort_by=''): array {
				return array(
					"style" => $style,
					"show" => $show,
					"pagination" => "yes",
					"city" => "",
					"country" => "",
					'sort' => 'ASC',
					'sort_by' => $sort_by,
					'status' => '',
					"pagination-style" => "load_more",
					"column" => 3,
					"tour-type" => $tour_type,
					"cat" => "0",
					"org" => "0",
					"activity" => "0",
					'search-filter' => $search_filter,
					'sidebar-filter' => $sidebar_filter,
					'title-filter' => 'no',
					'category-filter' => 'no',
					'organizer-filter' => 'yes',
					'location-filter' => 'yes',
					'country-filter' => 'no',
					'activity-filter' => 'yes',
					'month-filter' => $month_filter,
					'tag-filter' => $tag_filter,
					'feature-filter' => $feature_filter,
					'duration-filter' => 'no',
					'type-filter' => 'no'
				);
			}
		}
		new TTBM_Shortcode();
	}
