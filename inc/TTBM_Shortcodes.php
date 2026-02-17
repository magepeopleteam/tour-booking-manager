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
                add_shortcode('wptravelly-hotel-list', array($this, 'hotel_list_with_left_filter'));
                add_shortcode('ttbm-hotel-location-list', array($this, 'hotel_location_list_with_left_filter'));
                add_shortcode('ttbm-feature-hotel', array($this, 'feature_hotel_list_with'));
                add_shortcode('ttbm-popular-hotel', array($this, 'popular_hotel_list_with'));
                add_shortcode('wptravelly-hotel-search-list', array($this, 'hotel_search_list_with_left_filter'));
                add_shortcode('wptravelly-hotel-search', array($this, 'ttbm_hotel_top_search'));

                add_shortcode('ttbm-hotel-rooms', array($this, 'ttbm_hotel_rooms_by_id') );
                add_shortcode('ttbm-hotel-map',  array( $this, 'ttbm_hotel_map_shortcode') );
                add_shortcode('ttbm-hotel-slider',  array( $this, 'ttbm_hotel_slider_shortcode') );
			}

            public function ttbm_hotel_slider_shortcode( $atts ){
                $params = shortcode_atts([
                    'hotel_id'  => '',
                ], $atts);

                 $hotel_id = isset( $params['hotel_id'] ) ? sanitize_text_field( wp_unslash( $params['hotel_id'] ) ) : '';
                 ob_start();
                ?>
                <div class="ttbm_default_theme">
                    <div class='ttbm_style'>
                        <div class="ttbm-hotel-details ttbm_hotel_item">
                        <div class="ttbm-hero-area">
                            <div class="slider-area">
                                <?php do_action( 'ttbm_hotel_slider_shortcode', $hotel_id ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php

            return ob_get_clean();
            }

            function ttbm_hotel_map_shortcode( $atts ) {

                $atts = shortcode_atts([
                    'city'  => '',
                    'zoom'  => 12,
                    'width' => '100%',
                    'height'=> '400px'
                ], $atts);

                if(empty($atts['city'])) {
                    return '<p style="color:red;">City name not provided.</p>';
                }

                $city = urlencode($atts['city']);

                $map = '<div class="mp-hotel-map">
                            <iframe 
                                width="'.$atts['width'].'" 
                                height="'.$atts['height'].'" 
                                frameborder="0" 
                                style="border:0"
                                loading="lazy"
                                allowfullscreen
                                src="https://www.google.com/maps?q='.$city.'&z='.$atts['zoom'].'&output=embed">
                            </iframe>
                        </div>';

                return $map;
            }
            public function ttbm_hotel_rooms_by_id( $attribute ){
                $defaults = array(
                    "hotel_id" => '',
                );
                $params = shortcode_atts( $defaults, $attribute );
                $hotel_id = isset( $params['hotel_id'] ) ? sanitize_text_field( wp_unslash( $params['hotel_id'] ) ) : '';

                ob_start();
                if( $hotel_id ){
                ?>
                <div class="ttbm_default_theme">
                    <div class='ttbm_style'>
                        <div class="ttbm-hotel-details ttbm_hotel_item">

                            <div class="ttbm-content-area">
                                <div class="ttbm-content-left">
                                    <div class="ttbm_hotel_content_area" id="ttbm_hotel_content_area">
                                        <input type="hidden" id="ttbm_booking_hotel_id" value="<?php echo esc_attr( $hotel_id )?>">
                                        <?php do_action('ttbm_make_hotel_booking', $hotel_id );?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                }

                return ob_get_clean();
            }

            public function ttbm_hotel_top_search(){

                $location = !empty($_GET['ttbm_hotel_location_search']) ? strip_tags($_GET['ttbm_hotel_location_search']) : '';
                $date_range = !empty($_GET['search_date_range']) ? strip_tags($_GET['search_date_range']) : '';
                $search_person = !empty($_GET['hotel_search_person']) ? strip_tags($_GET['hotel_search_person']) : '';

                $locations = TTBM_Function::get_meta_values('ttbm_hotel_location', 'ttbm_hotel');

                $exist_locations = array_unique( $locations );

                $wp_date_format = get_option('date_format');
                ob_start();
                ?>
                <!-- Hotel Search Bar -->
                <div class="mpContainer">
                    <form class="rbfw_search_form_new" action="<?php echo get_home_url() . '/hotel-search-result/';  ?>" method="GET">
                    <div class="ttbm_hotel_search_box">

                        <!--<div class="ttbm_hotel_search_field">
                            <span class="ttbm_hotel_search_icon">🛏️</span>
                            <input type="text" name="ttbm_hotel_location_search" class="ttbm_hotel_search_input" value="<?php /*echo esc_attr( $location );*/?>" placeholder="Where are you going?">
                        </div>-->
                        <div class="ttbm_hotel_search_field ttbm_location_wrapper">
                            <span class="ttbm_hotel_search_icon">🛏️</span>
                            <input type="text"
                                   name="ttbm_hotel_location_search"
                                   id="ttbm_location_input"
                                   class="ttbm_hotel_search_input"
                                   value="<?php echo esc_attr($location); ?>"
                                   placeholder="Where are you going?"
                                   autocomplete="off">

                            <!-- Dropdown -->
                            <ul class="ttbm_location_dropdown" style="display:none;">
                                <?php foreach ($exist_locations as $loc) : ?>
                                    <li><?php echo esc_html($loc); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>


                        <div class="ttbm_hotel_search_field">
                            <input type="hidden" name="ttbm_hotel_search_date_format" id="ttbm_hotel_search_date_format" value="<?php echo esc_attr($wp_date_format);?>">
                            <span class="ttbm_hotel_search_icon">📅</span>
                            <input type="text" name="search_date_range" id="ttbm_date_range" class="ttbm_date_input" value="<?php echo esc_attr( $date_range );?>" placeholder="Select date range" readonly>
                        </div>


                        <div class="ttbm_hotel_search_field">
                            <span class="ttbm_hotel_search_icon">👤</span>
                            <input name="hotel_search_person" type="number" class="ttbm_hotel_search_input" value="<?php echo esc_attr( $search_person );?>" placeholder="Number of 1 room">
                        </div>

                        <button class="ttbm_hotel_search_btn">Search</button>


                    </div>
                    </form>
                </div>

            <?php
            return ob_get_clean();
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

				$loop = TTBM_Query::ttbm_query_for_top_search($show, $params['sort'], $params['sort_by'], $params['status'], $organizer_filter, $location_filter, $activity_filter, $date_filter );
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
					/*if (isset($_GET['date_filter_start']) && !empty($_GET['date_filter_start'])) {
						$date_filter['start_date'] = sanitize_text_field(wp_unslash($_GET['date_filter_start']));
					}
					if (isset($_GET['date_filter_end']) && !empty($_GET['date_filter_end'])) {
						$date_filter['end_date'] = sanitize_text_field(wp_unslash($_GET['date_filter_end']));
					}*/

					if (isset($_GET['ttbm_date_start_end_input']) && !empty($_GET['ttbm_date_start_end_input'])) {
						$date_range = sanitize_text_field($_GET['ttbm_date_start_end_input']);
                        $dates = explode(' - ', $date_range);
                        if(count($dates) === 2){
                            $date_filter['start_date'] = $dates[0] ;
                            $date_filter['end_date']   = $dates[1] ;
                        }
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

            public static function ttbm__get_booked_hotels( $start_date, $end_date ) {

                $args = array(
                    'post_type'      => 'ttbm_hotel_booking', // Replace with your CPT
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        'relation' => 'AND',
                        array(
                            'key'     => '_ttbm_hotel_booking_checkin_date',
                            'value'   => $end_date,       // check-in should be <= filter end
                            'compare' => '<=',
                            'type'    => 'DATE',
                        ),
                        array(
                            'key'     => '_ttbm_hotel_booking_checkout_date',
                            'value'   => $start_date,     // check-out should be >= filter start
                            'compare' => '>=',
                            'type'    => 'DATE',
                        ),
                    ),
                );

                $query = new WP_Query($args);

                $bookings = array();
                if ( $query->have_posts() ) {
                    foreach ( $query->posts as $booking ) {
                        $hotel_id       = get_post_meta( $booking->ID, '_ttbm_hotel_id', true );
                        $room_info      = get_post_meta( $booking->ID, '_ttbm_hotel_booking_room_info', true );
                        $checkin_date   = get_post_meta( $booking->ID, '_ttbm_hotel_booking_checkin_date', true );
                        $checkout_date  = get_post_meta( $booking->ID, '_ttbm_hotel_booking_checkout_date', true );
                        $bookings[] = array(
                            'hotel_id'  => intval( $hotel_id ),
                            'checkin_date'  =>  $checkin_date ,
                            'checkout_date'  =>  $checkout_date ,
                            'room_info' => maybe_unserialize( $room_info ),
                        );
                    }
                }
                wp_reset_postdata();

                return $bookings;
            }
            public static function ttbm_get_available_hotels_in_date( $start_date, $end_date, $location, $search_room_num ) {

                $booked_data = self::ttbm__get_booked_hotels( $start_date, $end_date );

                $booked_summary = $hotel_booked = array();
                /*foreach ( $booked_data as $booking ) {
                    $hotel_id = $booking['hotel_id'];
                    foreach ( $booking['room_info'] as $room_name => $room ) {
                        $room_name = preg_replace('/[^A-Za-z0-9\-]/', '', $room_name );
                        $qty  = isset( $room['quantity'] ) ? intval( $room['quantity'] ) : 0;
                        $booked_summary[$hotel_id][$room_name] =
                            ( $booked_summary[$hotel_id][$room_name] ?? 0 ) + $qty;
                    }
                }*/

                foreach ( $booked_data as $booking ) {
                    $hotel_id = $booking['hotel_id'];
                    // check date overlap
                    if ($booking['checkin_date'] < $end_date && $booking['checkout_date'] > $start_date ) {

                        if( is_array( $booking['room_info'] ) && !empty( $booking['room_info'] ) ) {
                            foreach ($booking['room_info'] as $room_type => $room) {
                                if (!isset($hotel_booked[$hotel_id][$room_type])) {
                                    $hotel_booked[$hotel_id][$room_type] = 0;
                                }

                                $quantity = isset( $room['quantity'] ) ? $room['quantity'] : 0;
                                $hotel_booked[$hotel_id][$room_type] += $quantity;
                            }
                        }
                    }
                }

                $location_search = !empty( $location ) ? array(
                    'key'     => 'ttbm_hotel_location',
                    'value'   => $location,
                    'compare' => '=',
                ) : '';

                $args = array(
                    'post_type'      => 'ttbm_hotel',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'meta_query'     => array(
                        $location_search,
                    ),
                );

                $query   = new WP_Query( $args );
                $results = array();

                if ( $query->have_posts() ) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $hotel_id = get_the_ID();
                        $room_info = maybe_unserialize( get_post_meta( $hotel_id, 'ttbm_room_details', true ) );
                        $featured_image = get_the_post_thumbnail_url( $hotel_id, 'full' );


                        $available_rooms = array();
                        if( is_array( $room_info ) && !empty( $room_info ) ) {
                            foreach ($room_info as $room) {
                                $name = $room['ttbm_hotel_room_name'];
                                $name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
                                $capacity = intval($room['ttbm_hotel_room_qty']);
//                            $reserved = $booked_summary[$hotel_id][$name] ?? 0;
                                $reserved = $hotel_booked[$hotel_id][$name] ?? 0;

                                $available_qty = $capacity - $reserved;
                                if ($search_room_num > 0) {
                                    if ($available_qty >= $search_room_num) {
                                        $room['available_qty'] = $available_qty;
                                        $available_rooms[] = $room;
                                    }
                                } else {
                                    if ($available_qty > $search_room_num) {
                                        $room['available_qty'] = $available_qty;
                                        $available_rooms[] = $room;
                                    }
                                }

                            }
                        }

                        if ( ! empty( $available_rooms ) ) {

                            $results[] = array(
                                'id' => $hotel_id,
                                'hotel_room_details'            => $available_rooms,
                                'title'                         => get_the_title(),
                                'content'                       => get_the_title(),
                                'excerpt'                       => get_the_excerpt(),
                                'hotel_activity_status'         => get_post_meta( $hotel_id, 'ttbm_hotel_activity_status', true ),
                                'hotel_features'                => get_post_meta( $hotel_id, 'ttbm_hotel_feat_selection', true ),
                                'hotel_activities'              => get_post_meta( $hotel_id, 'ttbm_hotel_activity_selection', true ),
                                'hotel_area_info'               => get_post_meta( $hotel_id, 'ttbm_hotel_area_info', true ),
                                'hotel_map_location'            => get_post_meta( $hotel_id, 'ttbm_hotel_map_location', true ),
                                'hotel_location'                => get_post_meta( $hotel_id, 'ttbm_hotel_location', true ),
                                'hotel_gallery_images_ids'      => get_post_meta( $hotel_id, 'ttbm_gallery_images_hotel', true ),
                                'hotel_distance_description'    => get_post_meta( $hotel_id, 'ttbm_hotel_distance_des', true ),
                                'hotel_rating'                  => get_post_meta( $hotel_id, 'ttbm_hotel_rating', true ),
                                'hotel_featured_image'          => $featured_image,
                                'permalink'                     => get_permalink($hotel_id),
                            );
                        }
                    }
                }
                wp_reset_postdata();

                return $results;
            }




            public function hotel_search_list_with_left_filter( $attribute, $month_filter = 'yes') {
                $tour_type = 'ttbm_hotel';
                $location = !empty($_GET['ttbm_hotel_location_search']) ? strip_tags($_GET['ttbm_hotel_location_search']) : '';
                $date_range = !empty($_GET['search_date_range']) ? strip_tags($_GET['search_date_range']) : '';
                $search_room_num = !empty($_GET['hotel_search_person']) ? strip_tags($_GET['hotel_search_person']) : 0;

                if( $date_range !== '' ){
                    list( $start_date, $end_date ) = explode(" - ", $date_range);
                    $start_date = trim($start_date);
                    $end_date   = trim($end_date);

                    $start_date  = date('Y-m-d', strtotime($start_date));
                    $end_date    = date('Y-m-d', strtotime($end_date));
                }else{
                    $start_date = $end_date = date( 'Y-m-d');
                }

                $available_hotels = self::ttbm_get_available_hotels_in_date( $start_date, $end_date, $location, $search_room_num );

                $count_result = count( $available_hotels );


                $defaults = $this->default_attribute('modern', 12, 'no', 'yes', 'yes', 'yes', $month_filter, $tour_type);
                $params = shortcode_atts($defaults, $attribute);
                $show = $params['show'];
                $pagination = $params['pagination'];
                $search = $params['sidebar-filter'];
                $show = ($search == 'yes' || $pagination == 'yes') ? -1 : $show;

                ob_start();
                ?>
                <div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
                    <div class="mpContainer">
                        <?php
                        if ($params['sidebar-filter'] == 'yes') {
                            ?>
                            <div class="left_filter">
                                <div class="leftSidebar placeholder_area">
                                    <?php do_action('ttbm_hotel_left_filter', $params ); ?>
                                </div>
                                <div class="mainSection">
                                    <?php do_action('ttbm_filter_hotel_search_top_bar', $count_result, $params); ?>
                                    <?php do_action('ttbm_search_hotel_list_item', $available_hotels, $params); ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            do_action('ttbm_filter_hotel_search_top_bar', $count_result, $params );
                            do_action('ttbm_search_hotel_list_item', $available_hotels, $params);
                        }
                        ?>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }

            public function hotel_list_with_left_filter( $attribute, $month_filter = 'yes') {
                $tour_type = 'ttbm_hotel';
                $defaults = $this->default_attribute('modern', 15, 'no', 'yes', 'yes', 'yes', $month_filter, $tour_type);
                $params = shortcode_atts($defaults, $attribute);
                $city = $params['city'];
                $city_search = !empty( $city ) ? array(
                    'key'     => 'ttbm_hotel_location',
                    'value'   => $city,
                    'compare' => '=',
                ) : '';

                $args = array(
                    'post_type'      => 'ttbm_hotel',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        $city_search,
                    ),
                );

                $loop = new WP_Query($args);
                $list = 'hotel_list';
                $popular_feature = 'no';
                ob_start();
                ?>
                <div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
                    <div class="mpContainer">
                        <?php
                        if ($params['sidebar-filter'] == 'yes') {
                            ?>
                            <div class="left_filter">
                                <div class="leftSidebar placeholder_area">
                                    <?php do_action('ttbm_hotel_left_filter', $params ); ?>
                                </div>
                                <div class="mainHotelSection">
                                    <?php do_action('ttbm_hotel_filter_top_bar', $loop, $params ); ?>
                                    <?php do_action('ttbm_all_hotel_list_item', $loop, $params, $list, $popular_feature ); ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            do_action('ttbm_hotel_filter_top_bar', $loop, $params);
                            do_action('ttbm_all_hotel_list_item', $loop, $params,  $list, $popular_feature );
                        }
                        ?>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }

            public function hotel_location_list_with_left_filter( $attribute, $month_filter = 'yes') {
                $tour_type = 'ttbm_hotel';
                $top_bar = 'no';
//                $defaults = $this->default_attribute('modern', 12, 'no', 'no', 'yes', 'yes', $month_filter, $tour_type, $top_bar );
                $defaults = $this->default_attribute( 'modern', 12, 'no', 'no', 'no', 'no', $month_filter, $tour_type, '', 'no', 'yes' );
                $params = shortcode_atts($defaults, $attribute);
                $show =  isset(  $params['show'] ) ? sanitize_text_field( wp_unslash( $params['show'] ) ) : 'no';
                $pagination = isset(  $params['pagination'] ) ? sanitize_text_field( wp_unslash( $params['pagination'] ) ) : 'no';
                $search = isset(  $params['sidebar-filter'] ) ? sanitize_text_field( wp_unslash( $params['sidebar-filter'] ) ) : 'no';
                $city = isset(  $params['city'] ) ? sanitize_text_field( wp_unslash( $params['city'] ) ) : 'no';
                $top_bar = isset(  $params['top-bar'] ) ? sanitize_text_field( wp_unslash( $params['top-bar'] ) ) : 'no';

                $city_search = !empty( $city ) ? array(
                    'key'     => 'ttbm_hotel_location',
                    'value'   => $city,
                    'compare' => '=',
                ) : '';
                $args = array(
                    'post_type'      => 'ttbm_hotel',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        $city_search,
                    ),
                );

                $loop = new WP_Query($args);
                $list = 'hotel_list';
                $location_list = 'location';
                ob_start();
                ?>
                <div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
                    <div class="mpContainer">
                        <?php
                        if ($params['sidebar-filter'] == 'yes') {
                            ?>
                            <div class="left_filter">
                                <div class="leftSidebar placeholder_area">
                                    <?php do_action('ttbm_hotel_left_filter', $params ); ?>
                                </div>
                                <div class="mainHotelSection">
                                    <?php
                                    if( $top_bar === 'yes' ){
                                        do_action('ttbm_hotel_filter_top_bar', $loop, $params );
                                    }
                                    ?>
                                    <?php do_action('ttbm_all_hotel_list_item', $loop, $params, $list, $location_list ); ?>
                                </div>
                            </div>
                            <?php
                        } else {
                            if( $top_bar === 'yes' ){
                                do_action('ttbm_hotel_filter_top_bar', $loop, $params );
                            }
                            do_action('ttbm_all_hotel_list_item', $loop, $params,  $list, $location_list );
                        }
                        ?>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }
            public function popular_hotel_list_with( $attribute ) {
                $tour_type = 'ttbm_hotel';
                $defaults = $this->default_attribute('modern', 12, );
                $params = shortcode_atts($defaults, $attribute);
                $limit = $params['limit'];
                $popular_feature = 'yes';

                $popular =  array(
                    'key'     => 'ttbm_display_hotel_popular',
                    'value'   => 'on',
                    'compare' => '=',
                ) ;

                $args = array(
                    'post_type'      => $tour_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => $limit,
                    'meta_query'     => array(
                        $popular,
                    ),
                );

                $loop = new WP_Query($args);
                $list = 'hotel_list';
                ob_start();
                ?>
                <div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
                    <div class="mpContainer">
                        <div class="mainHotelSection">
                            <?php do_action('ttbm_all_hotel_list_item', $loop, $params, $list, $popular_feature ); ?>
                        </div>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }

            public function feature_hotel_list_with( $attribute ) {
                $tour_type = 'ttbm_hotel';
                $defaults = $this->default_attribute('modern', 12, );
                $params = shortcode_atts($defaults, $attribute);
                $limit = $params['limit'];

                $popular_feature = 'yes';

                $feature = array(
                    'key'     => 'ttbm_display_hotel_feature',
                    'value'   => 'on',
                    'compare' => '=',
                );

                $args = array(
                    'post_type'      => $tour_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => $limit,
                    'meta_query'     => array(
                        $feature,
                    ),
                );

                $loop = new WP_Query($args);
                $list = 'hotel_list';
                ob_start();
                ?>
                <div class="ttbm_style ttbm_wraper placeholderLoader ttbm_filter_area">
                    <div class="mpContainer">
                        <div class="mainHotelSection">
                            <?php do_action('ttbm_all_hotel_list_item', $loop, $params, $list, $popular_feature ); ?>
                        </div>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }

			//***************************//
			public function default_attribute( $style = 'grid', $show = 9, $search_filter = 'yes', $sidebar_filter = 'no', $feature_filter = 'no', $tag_filter = 'no', $month_filter = 'yes', $tour_type = '', $sort_by = '', $shuffle = 'no', $top_bar = 'yes' ): array {
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
				'price-filter' => 'yes',
				'limit' => $show,
				'list_grid' => 'list',
				'top-bar' => $top_bar,
			);
		}

		}
		new TTBM_Shortcode();
	}
