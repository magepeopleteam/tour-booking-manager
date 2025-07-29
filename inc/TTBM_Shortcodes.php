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
				add_shortcode('wptravelly-tour-list', array($this, 'ttbm_tour_list'));
				add_shortcode('ttbm-top-attractions', array($this, 'top_attractions'));
				add_shortcode('ttbm-activity_browse', array($this, 'activity_browse'));
				add_shortcode('ttbm-texonomy-display', array($this, 'texonomy_display'));
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
				$loop = TTBM_Query::ttbm_query($show, $params['sort'], $params['cat'], $params['org'], $params['city'], $params['country'], $params['status'], $params['tour-type'], $params['activity'],$params['sort_by'], $params['attraction'], $params['feature']);
				ob_start();
				?>
				<div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
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
			public function list_with_left_filter_for_search( $attribute, $date_filter='') {
				$defaults = $this->default_attribute('modern', 12, 'no', 'yes', 'yes', 'yes', $month_filter = 'yes', $tour_type = '');
				$params = shortcode_atts($defaults, $attribute);
				$show = $params['show'];
				$pagination = $params['pagination'];
				$search = $params['sidebar-filter'];
				
				// Extract search parameters from GET request
				$organizer_filter = '';
				$location_filter = '';
				$activity_filter = '';
				
				if (isset($_GET['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
					$organizer_filter = isset($_GET['organizer_filter']) ? sanitize_text_field(wp_unslash($_GET['organizer_filter'])) : '';
					$location_filter = isset($_GET['location_filter']) ? sanitize_text_field(wp_unslash($_GET['location_filter'])) : '';
					$activity_filter = isset($_GET['activity_filter']) ? sanitize_text_field(wp_unslash($_GET['activity_filter'])) : '';
				}
				
				$show = ($search == 'yes' || $pagination == 'yes') ? -1 : $show;

				$loop = TTBM_Query::ttbm_query_for_top_Search($show, $params['sort'], $params['sort_by'], $params['status'], $organizer_filter, $location_filter, $activity_filter, $date_filter );
//echo '<pre>';print_r($loop->post_count);echo '</pre>';
				?>
				<div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
					<div class="mpContainer">
					<?php
						include( TTBM_Function::template_path( 'layout/filter_hidden.php' ) );
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

							do_action('ttbm_all_list_item', $loop, $params);
							do_action('ttbm_sort_result', $loop, $params);
							do_action('ttbm_pagination', $params, $loop->post_count);
						}
					?>
					</div>
				</div>
				<?php

			}
			public function list_with_top_filter($attribute) {
				$defaults = $this->default_attribute();
				$defaults['shuffle'] = 'no'; 
				$params = shortcode_atts($defaults, $attribute);
				$pagination = $params['pagination'];
				$search = $params['search-filter'];
				$show = $params['show'];
				$show = ($search == 'yes' || $pagination == 'yes') ? -1 : $show;
				$loop = TTBM_Query::ttbm_query($show, $params['sort'], $params['cat'], $params['org'], $params['city'], $params['country'], $params['status'], $params['tour-type'], $params['activity'], $params['sort_by']);
				if (isset($params['shuffle']) && $params['shuffle'] == 'yes') {
					$posts = $loop->posts;
					shuffle($posts);
					$loop->posts = $posts;
					$loop->post_count = count($posts);
				}
				ob_start();
				?>
				<div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
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
				$locations = TTBM_Global_Function::get_taxonomy('ttbm_tour_location');
				if (is_array($locations) && sizeof($locations)) {
					$grid_class = (int)$params['column'] > 0 ? 'grid_' . (int)$params['column'] : 'grid_1';
					?>
					<div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area ttbm_location_list">
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
				
				// Process search parameters from GET request
				$date_filter = array();
				if (isset($_GET['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
					if (isset($_GET['date_filter_start']) && !empty($_GET['date_filter_start'])) {
						$date_filter['start_date'] = sanitize_text_field(wp_unslash($_GET['date_filter_start']));
					}
					if (isset($_GET['date_filter_end']) && !empty($_GET['date_filter_end'])) {
						$date_filter['end_date'] = sanitize_text_field(wp_unslash($_GET['date_filter_end']));
					}
				}
				
				echo $this->list_with_left_filter_for_search( $attribute, $date_filter );
				return ob_get_clean();
			}
			public function hotel_list($attribute) {
				ob_start();
				 $this->list_with_left_filter($attribute, 'hotel', 'no');
				return ob_get_clean();
			}
			public function registration($attribute) {
				$defaults = array('ttbm_id' => '');
				$params = shortcode_atts($defaults, $attribute);
				ob_start();
				$tour_id = $params['ttbm_id'] ?? get_the_id();
				if ($tour_id) {
					?>
					<div class="ttbm_style">
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
					<div class="ttbm_style">
						<div class="mpContainer">
						<?php include(TTBM_Function::template_path('layout/related_tour.php')); ?>
						</div>
					</div>
					<?php
				}
				return ob_get_clean();
			}
            public function top_attractions( $attribute ) {
                $defaults = array('show' => 4, 'column' => 3, 'carousel' => 'no', 'load-more-button' => 'yes' );
                $params = shortcode_atts($defaults, $attribute);
                $num_of_places = $params['show'];

                ob_start();
                $place_tour = TTBM_Function::get_city_place_ids_with_post_ids( $num_of_places );
                if( is_array( $place_tour ) && !empty( $place_tour ) ) {
                    $count_grid_class = (int)$params['column'] > 0 ? 'grid_' . (int)$params['column'] : 'grid_1';
                    ?>
                    <div class="ttbm_style ttbm_wraper ttbm_filter_area ttbm_location_list">
                        <div class="mpContainer">
                            <?php include(TTBM_Function::template_path('layout/attraction_display.php')); ?>
                        </div>
                    </div>
                    <?php

                }

                return ob_get_clean();
            }
            public function activity_browse( $attribute ) {
                $defaults = array( 'show' => 4, 'column' => 3, 'carousel' => 'no', 'load-more-button' => 'yes' );
                $params = shortcode_atts($defaults, $attribute);
                $num_of_ids = $params['show'];
                $activity_term_ids = TTBM_Function::get_all_activity_ids_from_posts( $num_of_ids );
                ob_start();
                if( is_array( $activity_term_ids ) && !empty( $activity_term_ids ) ) {
                    ?>
                    <div class="ttbm_style ttbm_wraper ttbm_filter_area ttbm_location_list">
                        <div class="mpContainer">
                            <?php include(TTBM_Function::template_path('layout/browse_activity.php')); ?>
                        </div>
                    </div>
                    <?php

                }

                return ob_get_clean();
            }
            public function texonomy_display( $attribute ) {
                $defaults = array( 'type'=>'category', 'show' => 4, 'column' => 3, 'carousel' => 'no', 'load-more-button' => 'yes' );
                $params = shortcode_atts($defaults, $attribute);
                $num_of_ids = $params['show'];
                $taxonomy_type = $params['type'];


                if( $taxonomy_type == 'organizer' ){
                    $taxonomy = 'ttbm_tour_org';
                    $terms_data = TTBM_Function::get_all_category_with_assign_post( $taxonomy );
                }else if( $taxonomy_type == 'category' ){
                    $taxonomy = 'ttbm_tour_cat';
                    $terms_data = TTBM_Function::get_all_category_with_assign_post( $taxonomy );
                }else if( $taxonomy_type == 'tag' ){
                    $taxonomy = 'ttbm_tour_tag';
                    $terms_data = TTBM_Function::get_all_category_with_assign_post( $taxonomy );
                }else if( $taxonomy_type == 'feature' ){
                    $taxonomy = 'ttbm_tour_features_list';
                    $get_taxonomy = 'ttbm_service_included_in_price';
                    $terms_data = TTBM_Function::get_location_feature( $get_taxonomy );
//                    $terms_data = [];

                }else{
                    $terms_data = [];
                }

                ob_start();
                if( is_array( $terms_data ) && !empty( $terms_data ) ) {
                    ?>
                    <div class="ttbm_style ttbm_wraper ttbm_filter_area ttbm_location_list">
                        <div class="mpContainer">
                            <?php include(TTBM_Function::template_path('layout/texonomy_shortcode_display.php')); ?>
                        </div>
                    </div>
                    <?php

                }

                return ob_get_clean();
            }

            public function ttbm_tour_list($attribute) {
				$defaults = array( 'type' => 'feature', 'column' => 3, 'carousel' => 'no', 'show' => '' );
				$params = shortcode_atts($defaults, $attribute);
				ob_start();
				$tour_id = 164;
				$num_of_tour = $params['column'];
				$type_tour = $params['type'];
				if ($type_tour) {
					?>
					<div class="ttbm_style">
						<div class="mpContainer">
						<?php include(TTBM_Function::template_path('layout/top_picks_deals_tour.php')); ?>
						</div>
					</div>
					<?php
				}
				return ob_get_clean();
			}
			//***************************//
			public function default_attribute($style = 'grid', $show = 9, $search_filter = 'yes', $sidebar_filter = 'no', $feature_filter = 'no', $tag_filter = 'no', $month_filter = 'yes', $tour_type = '', $sort_by = '', $shuffle = 'no'): array {
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
				"feature" => "0",
				"attraction" => "0",
				"org" => "0",
				"activity" => "0",
				'search-filter' => $search_filter,
				'sidebar-filter' => $sidebar_filter,
				'title-filter' => 'no',
				'category-filter' => 'no',
				'organizer-filter' => 'no',
				'location-filter' => 'yes',
				'country-filter' => 'no',
				'activity-filter' => 'yes',
				'month-filter' => $month_filter,
				'tag-filter' => $tag_filter,
				'feature-filter' => $feature_filter,
				'duration-filter' => 'no',
				'type-filter' => 'no',
				'shuffle' => $shuffle,
				'filter_by_activity' => 'yes',
			);
		}

		}
		new TTBM_Shortcode();
	}
