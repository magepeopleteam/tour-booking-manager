<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Query')) {
		class TTBM_Query {
			public function __construct() {}
			public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => 'publish'
				);
				return new WP_Query($args);
			}
			public static function ttbm_query($show, $sort = '', $cat = '', $org = '', $city = '', $country = '', $status = '', $tour_type = '', $activity = '', $sort_by = ''): WP_Query {
				TTBM_Function::update_all_upcoming_date_month();
				$sort_by = $sort_by ?: 'meta_value';
				if (get_query_var('paged')) {
					$paged = get_query_var('paged');
				}
				elseif (get_query_var('page')) {
					$paged = get_query_var('page');
				}
				else {
					$paged = 1;
				}
				$now = current_time('Y-m-d');
				$compare = '>=';
				if ($status) {
					$compare = $status == 'expired' ? '<' : '>=';
				}
				else {
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
				$org_filter = !empty($org) ? array(
					'taxonomy' => 'ttbm_tour_org',
					'field' => 'term_id',
					'terms' => $org
				) : '';
				$activity = $activity ? get_term_by('id', $activity, 'ttbm_tour_activities')->name : '';
				$activity_filter = !empty($activity) ? array(
					'key' => 'ttbm_tour_activities',
					'value' => array($activity),
					'compare' => 'IN'
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
				$args = array(
					'post_type' => array(TTBM_Function::get_cpt_name()),
					'paged' => $paged,
					'posts_per_page' => $show,
					'order' => $sort,
					'orderby' => $sort_by,
					'meta_key' => 'ttbm_upcoming_date',
					'meta_query' => array(
						// 'relation' => 'AND',
						$expire_filter,
						$city_filter,
						$country_filter,
						$tour_type_filter,
						$activity_filter
					),
					'tax_query' => array(
						$cat_filter,
						$org_filter
					)
				);
				
				if($status == 'active')
				{
					return TTBM_Function::get_active_tours($args);
				}
				else
				{
					//return TTBM_Function::get_active_tours($args);
					return new WP_Query($args);
				}
			}
			public static function get_all_tour_in_location($location, $status = ''): WP_Query {
				$compare = '>=';
				if ($status) {
					$compare = $status == 'expired' ? '<' : '>=';
				}
				else {
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

				if($status == 'active')
				{
					return TTBM_Function::get_active_tours($args);
				}
				else
				{
					return new WP_Query($args);
				}				
			}
			public static function get_order_meta($item_id, $key): string {
				global $wpdb;
				$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
				$results = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM $table_name WHERE order_item_id = %d AND meta_key = %s", $item_id, $key));
				foreach ($results as $result) {
					$value = $result->meta_value;
				}
				return $value ?? '';
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
			public static function query_all_service_sold($tour_id, $tour_date, $type = ''){
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
				$ex_service_infos= new WP_Query($args);
				$total_qty=0;
				if ($ex_service_infos->post_count > 0) {
					$ex_service_info = $ex_service_infos->posts;
					foreach ($ex_service_info as $ex_service) {
						$service_id = $ex_service->ID;
						$qty = MP_Global_Function::get_post_info($service_id, 'ttbm_service_qty',0);
						$total_qty+=$qty;
					}
				}
				wp_reset_query();
				return max(0,$total_qty);
			}
			public static function query_group_id($group_id){
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
				$ex_service_infos= new WP_Query($args);
				$group_ids=[];
				if ($ex_service_infos->post_count > 0) {
					$ex_service_info = $ex_service_infos->posts;
					foreach ($ex_service_info as $ex_service) {
						$group_ids[]=$ex_service->ID;
					}
				}
				wp_reset_query();
				return $group_ids;
			}
		}
		new TTBM_Query();
	}