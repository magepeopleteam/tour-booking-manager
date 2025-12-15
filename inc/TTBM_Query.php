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
			public static function ttbm_query_for_top_search($show, $sort, $sort_by, $status, $organizer_filter, $location, $activity, $date_filter = '', $flexible_dates = 'no', $person_filter = 0): WP_Query {
				
				$post_in_ids = null; // Default null means no ID restriction from date logic
                $selected_date_filter = '';

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
					
					if ($flexible_dates === 'yes' && $compare === 'BETWEEN') {
                        // Use Raw SQL for performance to avoid execution timeout on complex meta queries
                        global $wpdb;
                        $cpt = TTBM_Function::get_cpt_name();
                        
                        // Calculate Search Duration (in days, inclusive)
                        $start_ts = strtotime($start_date);
                        $end_ts = strtotime($end_date);
                        $search_duration = round(($end_ts - $start_ts) / (60 * 60 * 24)) + 1;
                        
                        // 1. Get IDs of Repeated Tours that overlap AND fit in duration
                        $repeated_sql = $wpdb->prepare(
                            "SELECT DISTINCT p.ID 
                            FROM {$wpdb->posts} p
                            INNER JOIN {$wpdb->postmeta} pm_type ON (p.ID = pm_type.post_id AND pm_type.meta_key = 'ttbm_travel_type' AND pm_type.meta_value = 'repeated')
                            INNER JOIN {$wpdb->postmeta} pm_start ON (p.ID = pm_start.post_id AND pm_start.meta_key = 'ttbm_travel_repeated_start_date')
                            LEFT JOIN {$wpdb->postmeta} pm_end ON (p.ID = pm_end.post_id AND pm_end.meta_key = 'ttbm_travel_repeated_end_date')
                            LEFT JOIN {$wpdb->postmeta} pm_dur ON (p.ID = pm_dur.post_id AND pm_dur.meta_key = 'ttbm_travel_duration')
                            WHERE p.post_type = %s AND p.post_status = 'publish'
                            AND pm_start.meta_value <= %s
                            AND (pm_end.meta_value >= %s OR pm_end.meta_value IS NULL OR pm_end.meta_value = '')
                            AND (pm_dur.meta_value IS NULL OR CAST(pm_dur.meta_value AS UNSIGNED) <= %d)",
                            $cpt, 
                            $end_date, 
                            $start_date,
                            $search_duration
                        );
                        
                        $repeated_ids = $wpdb->get_col($repeated_sql);
                        
                        // 2. Get IDs of Standard Tours (between dates) AND fit in duration
                        $standard_sql = $wpdb->prepare(
                            "SELECT DISTINCT p.ID
                            FROM {$wpdb->posts} p
                            INNER JOIN {$wpdb->postmeta} pm_upcoming ON (p.ID = pm_upcoming.post_id AND pm_upcoming.meta_key = 'ttbm_upcoming_date')
                            LEFT JOIN {$wpdb->postmeta} pm_dur ON (p.ID = pm_dur.post_id AND pm_dur.meta_key = 'ttbm_travel_duration')
                            WHERE p.post_type = %s AND p.post_status = 'publish'
                            AND pm_upcoming.meta_value BETWEEN %s AND %s
                            AND (pm_dur.meta_value IS NULL OR CAST(pm_dur.meta_value AS UNSIGNED) <= %d)",
                            $cpt,
                            $start_date,
                            $end_date,
                            $search_duration
                        );
                        
                        $standard_ids = $wpdb->get_col($standard_sql);
                        
                        // Merge IDs
                        $merged_ids = array_unique(array_merge($repeated_ids, $standard_ids));
                        
                        if (empty($merged_ids)) {
                            $post_in_ids = array(0); // Force no results
                        } else {
                            $post_in_ids = $merged_ids;
                        }
                        
                        $selected_date_filter = ''; // Clear meta query since we used IDs

					} else {
						$selected_date_filter = $start_date ? array(
							'key' => 'ttbm_upcoming_date',
							'value' => $date,
							'compare' => $compare,
							'type' => 'DATE',
						) : '';
					}

				} else {
					$selected_date_filter = '';
				}

				// TTBM_Function::update_all_upcoming_date_month();
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
                
                if ($post_in_ids !== null) {
                    $args['post__in'] = $post_in_ids;
                }
                
                // Filter by Person (Seat Availability)
                if ($person_filter > 0) {
                    $args_all = $args;
                    $args_all['posts_per_page'] = -1;
                    $args_all['fields'] = 'ids'; // Only fetch IDs
                    
                    $all_posts = get_posts($args_all);
                    $available_ids = [];
                    
                    foreach ($all_posts as $post_id) {
                        $match_found = false;
                        
                        if (!empty($start_date) && !empty($end_date)) {
                            // Check availability within the selected date range
                            // We need to check if ANY valid date in the range has enough seats
                            $range_availability = TTBM_Function::get_ticket_availability_for_date_range($post_id, $start_date, $end_date);
                            
                            if (is_array($range_availability)) {
                                foreach ($range_availability as $date_info) {
                                    // Extract available quantity from the info array
                                    // Structure depends on get_ticket_availability_info return
                                    // Usually it returns an array of ticket types. Need to sum or check max?
                                    // get_ticket_availability_info returns detailed array of ticket types.
                                    // We need to sum the available_qty across ticket types if the user just wants "spots"
                                    // OR check if any single ticket type has enough seats.
                                    // Usually "Person" means "Can I book X tickets?". 
                                    // If tickets are "Adult", "Child", they share capacity or have separate?
                                    // TTBM usually sums capacity.
                                    
                                    // Let's rely on `available_qty` from the first ticket type or sum them?
                                    // A safer bet given the previous `get_total_available` logic is to check the max available for that day.
                                    
                                    $day_available = 0;
                                    foreach($date_info as $ticket){
                                         if(isset($ticket['available_qty'])){
                                             $day_available += $ticket['available_qty'];
                                         }
                                    }
                                    
                                    if ($day_available >= $person_filter) {
                                        $match_found = true;
                                        break; 
                                    }
                                }
                            }
                        } else {
                            // No date selected: Check if ANY future date has enough seats
                            // We must iterate all future dates to find if at least one has capacity >= filter.
                            // The previous `get_any_date_seat_available` simply returned the first one with > 0 seats, which is incorrect for filtering.
                            
                            $travel_type = TTBM_Function::get_travel_type($post_id);
                            
                            if ($travel_type == 'fixed') {
                                $avail = TTBM_Function::get_total_available($post_id);
                                if ($avail >= $person_filter) {
                                    $match_found = true;
                                }
                            } else {
                                $all_dates = TTBM_Function::get_date($post_id);
                                $max_avail_found = 0;
                                
                                if (is_array($all_dates)) {
                                    foreach ($all_dates as $date) {
                                        // Handle if get_date returns complex array or simple list (usually simple list for repeated/particular)
                                        // If associative (fixed keys), this loop might be wrong, but we handled fixed above.
                                        // Repeated/Particular return list of date strings.
                                        if (is_string($date)) {
                                            $avail = TTBM_Function::get_total_available($post_id, $date);
                                            if ($avail > $max_avail_found) {
                                                $max_avail_found = $avail;
                                            }
                                            
                                            if ($avail >= $person_filter) {
                                                $match_found = true;
                                                break; // Found a valid date
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        if ($match_found) {
                            $available_ids[] = $post_id;
                        }
                    }
                    
                    if (empty($available_ids)) {
                         $args['post__in'] = array(0); // Force no results
                    } else {
                         $args['post__in'] = $available_ids;
                    }
                }
                
				if ($status == 'active') {
					return TTBM_Function::get_active_tours($args);
				} else {
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
				$date_filter = !empty($tour_date) ? array(
					'key' => 'ttbm_date',
					'value' => $tour_date,
					'compare' => 'LIKE'
				) : '';
				$hotel_filter = !empty($hotel_id) ? array(
					'key' => 'ttbm_hotel_id',
					'value' => $hotel_id,
					'compare' => '='
				) : '';
				$args = array(
					'post_type' => 'ttbm_booking',
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
				$date_filter = !empty($tour_date) ? array(
					'key' => 'ttbm_date',
					'value' => $tour_date,
					'compare' => 'LIKE'
				) : '';
				$args = array(
					'post_type' => 'ttbm_service_booking',
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
