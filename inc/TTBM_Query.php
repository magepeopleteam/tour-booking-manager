<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Query')) {
		class TTBM_Query {
			public function __construct() { }
			public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => 'publish'
				);
				return new WP_Query($args);
			}
			public static function ttbm_query($show, $sort = '', $cat = '', $org = '', $city = '', $country = '', $status = '', $tour_type = '', $activity = '', $sort_by = '', $attraction = '', $feature = '' ): WP_Query {
				TTBM_Function::update_all_upcoming_date_month();
				$sort_by = $sort_by ?: 'meta_value';
				if (get_query_var('paged')) {
					$paged = get_query_var('paged');
				} elseif (get_query_var('page')) {
					$paged = get_query_var('page');
				} else {
					$paged = 1;
				}
				$now = current_time('Y-m-d');
				$compare = '>=';
				if ($status) {
					$compare = $status == 'expired' ? '<' : '>=';
				} else {
					$expire_tour = TTBM_Function::get_general_settings('ttbm_expire', 'yes');
					$compare = $expire_tour == 'yes' ? '' : $compare;
				}
				$expire_filter = !empty($compare) ? array(
					'key' => 'ttbm_upcoming_date',
					'value' => $now,
					'compare' => $compare
				) : '';
				$reg_filter = !empty($compare) ? array(
					'key' => 'ttbm_reg_end_date',
					'value' => $now,
					'compare' => '>'
				) : '';
				$cat_filter = !empty($cat) ? array(
					'taxonomy' => 'ttbm_tour_cat',
					'field' => 'term_id',
					'terms' => $cat
				) : '';
                $feature_filter = !empty($feature) ? array(
                    'key'     => 'ttbm_service_included_in_price',
                    'value'   => '"' . $feature . '"', // Important: wrap in quotes to match serialized string
                    'compare' => 'LIKE'
                ) : '';
				$org_filter = !empty($org) ? array(
					'taxonomy' => 'ttbm_tour_org',
					'field' => 'term_id',
					'terms' => $org
				) : '';
//				$activity = $activity ? get_term_by('id', $activity, 'ttbm_tour_activities')->name : '';
				$activity_filter = !empty($activity) ? array(
					'key' => 'ttbm_tour_activities',
					'value' => array($activity),
					'compare' => 'IN'
				) : '';

                $short_activity_filter = !empty($activity) ? array(
                    'key'     => 'ttbm_tour_activities',
                    'value'   => '"' . $activity . '"',
                    'compare' => 'LIKE'
                ) : '';

				$city_filter = !empty($city) ? array(
					'key' => 'ttbm_location_name',
					'value' => $city,
					'compare' => 'LIKE'
				) : '';
				$country_filter = !empty($country) ? array(
					'key' => 'ttbm_country_name',
					'value' => $country,
					'compare' => 'LIKE'
				) : '';
				$tour_type_filter = !empty($tour_type) ? array(
					'key' => 'ttbm_type',
					'value' => $tour_type,
					'compare' => 'LIKE'
				) : '';
                $attraction_filter = !empty($attraction) ? array(
                    'key'     => 'ttbm_hiphop_places',
                    'value'   => '"ttbm_city_place_id";s:' . strlen($attraction) . ':"' . $attraction . '"',
                    'compare' => 'LIKE'
                ) : '';
				$args = array(
					'post_type' => array(TTBM_Function::get_cpt_name()),
					'paged' => $paged,
					'posts_per_page' => $show,
					'order' => $sort,
					'orderby' => $sort_by,
					'meta_key' => 'ttbm_upcoming_date',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'relation' => 'OR',
							$expire_filter,
							$reg_filter,
						),
						$city_filter,
						$country_filter,
						$tour_type_filter,
//						$activity_filter,
                        $attraction_filter,
                        $short_activity_filter,
                        $feature_filter,
					),
					'tax_query' => array(
						$cat_filter,
						$org_filter
					)
				);
				if ($status == 'active') {
					return TTBM_Function::get_active_tours($args);
				} else {
					//return TTBM_Function::get_active_tours($args);
					return new WP_Query($args);
				}
			}
			public static function ttbm_query_for_top_search($show, $sort, $sort_by, $status, $organizer_filter, $location, $activity, $date_filter = ''): WP_Query {

                if (is_array($date_filter)) {
					if (!empty($date_filter['start_date'])) {
						$start_date_obj = DateTime::createFromFormat('F d, Y', $date_filter['start_date']);
						$start_date = ($start_date_obj !== false) ? $start_date_obj->format('Y-m-d') : '';
					} else {
						$start_date = '';
					}
					if (!empty($date_filter['end_date'])) {
						$end_date_obj = DateTime::createFromFormat('F d, Y', $date_filter['end_date']);
						$end_date = ($end_date_obj !== false) ? $end_date_obj->format('Y-m-d') : '';
					} else {
						$end_date = '';
					}

					if ($end_date === '' && $start_date === '') {
						$date = '';
						$compare = '';
					} elseif ($end_date === '' && $start_date !== '') {
						$date = $start_date;
						$compare = '=';
					} elseif ($end_date !== '' && $start_date === '') {
						$date = $end_date;
						$compare = '=';
					} else {
						$date = [$start_date, $end_date];
						$compare = 'BETWEEN';
					}
					$selected_date_filter = $start_date ? array(
						'key' => 'ttbm_upcoming_date',
						'value' => $date,
						'compare' => $compare,
						'type' => 'DATE',
					) : '';
				} else {
					$selected_date_filter = '';
				}
				TTBM_Function::update_all_upcoming_date_month();
				$sort_by = $sort_by ?: 'meta_value';
				if (get_query_var('paged')) {
					$paged = get_query_var('paged');
				} elseif (get_query_var('page')) {
					$paged = get_query_var('page');
				} else {
					$paged = 1;
				}
				$now = current_time('Y-m-d');
				$compare = '>=';
				if ($status) {
					$compare = $status == 'expired' ? '<' : '>=';
				} else {
					$expire_tour = TTBM_Function::get_general_settings('ttbm_expire', 'yes');
					$compare = $expire_tour == 'yes' ? '' : $compare;
				}
				$expire_filter = $compare ? array(
					'key' => 'ttbm_upcoming_date',
					'value' => $now,
					'compare' => $compare
				) : '';
				$cat_filter = !empty($cat) ? array(
					'taxonomy' => 'ttbm_tour_cat',
					'field' => 'term_id',
					'terms' => $cat
				) : '';
				$org_filter = !empty($organizer_filter) ? array(
					'taxonomy' => 'ttbm_tour_org',
					'field' => 'term_id',
					'terms' => $organizer_filter
				) : '';

               /* $activity_filter = !empty($activity) ? array(
                    'key' => 'ttbm_tour_activities',
                    'value' => array($activity),
                    'compare' => 'IN'
                ) : '';*/

                $activity_filter = !empty($activity) ? array(
                    'key'     => 'ttbm_tour_activities',
                    'value'   => '"' . $activity . '"',
                    'compare' => 'LIKE'
                ) : '';

				$location = $location ? get_term_by('id', $location, 'ttbm_tour_location')->name : '';
				$city_filter = !empty($location) ? array(
					'key' => 'ttbm_location_name',
					'value' => $location,
					'compare' => 'LIKE'
				) : '';
				$country_filter = !empty($country) ? array(
					'key' => 'ttbm_country_name',
					'value' => $country,
					'compare' => 'LIKE'
				) : '';
				$tour_type_filter = !empty($tour_type) ? array(
					'key' => 'ttbm_type',
					'value' => $tour_type,
					'compare' => 'LIKE'
				) : '';
				$args = array(
					'post_type' => array(TTBM_Function::get_cpt_name()),
					'paged' => $paged,
					'posts_per_page' => $show,
					'order' => $sort,
					'orderby' => $sort_by,
					'meta_key' => 'ttbm_upcoming_date',
					'meta_query' => array(
						'relation' => 'AND',
						$expire_filter,
						$city_filter,
						$country_filter,
						$tour_type_filter,
						$activity_filter,
						$selected_date_filter
					),
					'tax_query' => array(
						$cat_filter,
						$org_filter
					)
				);
				if ($status == 'active') {
					return TTBM_Function::get_active_tours($args);
				} else {
					//return TTBM_Function::get_active_tours($args);
					return new WP_Query($args);
				}
			}
			public static function get_all_tour_in_location($location, $status = ''): WP_Query {
				$compare = '>=';
				if ($status) {
					$compare = $status == 'expired' ? '<' : '>=';
				} else {
					$expire_tour = TTBM_Function::get_general_settings('ttbm_expire', 'yes');
					$compare = $expire_tour == 'yes' ? '' : $compare;
				}
				$location = !empty($location) ? array(
					'key' => 'ttbm_location_name',
					'value' => $location,
					'compare' => 'LIKE'
				) : '';
				$expire_filter = !empty($compare) ? array(
					'key' => 'ttbm_upcoming_date',
					'value' => current_time('Y-m-d'),
					'compare' => $compare
				) : '';
				$args = array(
					'post_type' => array(TTBM_Function::get_cpt_name()),
					'posts_per_page' => -1,
					'order' => 'ASC',
					'orderby' => 'meta_value',
					'meta_query' => array(
						$location,
						$expire_filter
					)
				);
				if ($status == 'active') {
					return TTBM_Function::get_active_tours($args);
				} else {
					return new WP_Query($args);
				}
			}
		public static function query_all_sold($tour_id, $tour_date, $type = '', $hotel_id = ''): WP_Query {
			$_seat_booked_status = TTBM_Function::get_general_settings('ttbm_set_book_status', array('processing', 'completed'));
			$seat_booked_status = !empty($_seat_booked_status) ? $_seat_booked_status : [];
			$type_filter = !empty($type) && !is_array($type) ? array(
				'key' => 'ttbm_ticket_name',
				'value' => $type,
				'compare' => '='
			) : '';
			
			// Fix: Use exact match for date+time to ensure time-slot specific availability
			// If tour_date contains time (e.g., "2026-02-21 10:00"), match exactly
			// If tour_date is date only (e.g., "2026-02-21"), use LIKE for backward compatibility
			$date_filter = '';
			if (!empty($tour_date)) {
				// Check if the date contains time component
				$has_time = TTBM_Global_Function::check_time_exit_date($tour_date);
				if ($has_time) {
					// Exact match for date with time - ensures time-slot specific counting
					$date_filter = array(
						'key' => 'ttbm_date',
						'value' => $tour_date,
						'compare' => '='
					);
				} else {
					// For date-only queries, match the date portion only
					// This handles backward compatibility for tours without time slots
					$date_filter = array(
						'key' => 'ttbm_date',
						'value' => $tour_date,
						'compare' => 'LIKE'
					);
				}
			}
			
			$hotel_filter = !empty($hotel_id) ? array(
				'key' => 'ttbm_hotel_id',
				'value' => $hotel_id,
				'compare' => '='
			) : '';
			$args = array(
				'post_type' => 'ttbm_booking',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'ttbm_id',
						'value' => $tour_id,
						'compare' => '='
					),
					array(
						'key' => 'ttbm_order_status',
						'value' => $seat_booked_status,
						'compare' => 'IN'
					),
					$type_filter,
					$hotel_filter,
					$date_filter
				)
			);
			return new WP_Query($args);
		}
			public static function query_all_service_sold($tour_id, $tour_date, $type = '') {
				$_seat_booked_status = TTBM_Function::get_general_settings('ttbm_set_book_status', array('processing', 'completed'));
				$seat_booked_status = !empty($_seat_booked_status) ? $_seat_booked_status : [];
				$type_filter = !empty($type) ? array(
					'key' => 'ttbm_service_name',
					'value' => $type,
					'compare' => '='
				) : '';
				
				// Fix: Use exact match for date+time to ensure time-slot specific availability
				$date_filter = '';
				if (!empty($tour_date)) {
					$has_time = TTBM_Global_Function::check_time_exit_date($tour_date);
					if ($has_time) {
						$date_filter = array(
							'key' => 'ttbm_date',
							'value' => $tour_date,
							'compare' => '='
						);
					} else {
						$date_filter = array(
							'key' => 'ttbm_date',
							'value' => $tour_date,
							'compare' => 'LIKE'
						);
					}
				}
				$args = array(
					'post_type' => 'ttbm_service_booking',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'ttbm_id',
							'value' => $tour_id,
							'compare' => '='
						),
						array(
							'key' => 'ttbm_order_status',
							'value' => $seat_booked_status,
							'compare' => 'IN'
						),
						$type_filter,
						$date_filter
					)
				);
				$ex_service_infos = new WP_Query($args);
				$total_qty = 0;
				if ($ex_service_infos->post_count > 0) {
					$ex_service_info = $ex_service_infos->posts;
					foreach ($ex_service_info as $ex_service) {
						$service_id = $ex_service->ID;
						$qty = TTBM_Global_Function::get_post_info($service_id, 'ttbm_service_qty', 0);
						$total_qty += $qty;
					}
				}
				wp_reset_postdata();
				return max(0, $total_qty);
			}
			public static function query_group_id($group_id) {
				$_seat_booked_status = TTBM_Function::get_general_settings('ttbm_set_book_status', array('processing', 'completed'));
				$seat_booked_status = !empty($_seat_booked_status) ? $_seat_booked_status : [];
				$args = array(
					'post_type' => 'ttbm_booking',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'ttbm_group_id',
							'value' => $group_id,
							'compare' => '='
						),
						array(
							'key' => 'ttbm_order_status',
							'value' => $seat_booked_status,
							'compare' => 'IN'
						)
					)
				);
				$ex_service_infos = new WP_Query($args);
				$group_ids = [];
				if ($ex_service_infos->post_count > 0) {
					$ex_service_info = $ex_service_infos->posts;
					foreach ($ex_service_info as $ex_service) {
						$group_ids[] = $ex_service->ID;
					}
				}
				wp_reset_postdata();
				return $group_ids;
			}
		}
		new TTBM_Query();
	}
