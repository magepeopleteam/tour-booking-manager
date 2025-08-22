<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Function')) {
		class TTBM_Function {
			public function __construct() {
			}
			//**************Support multi Language*********************//
			public static function post_id_multi_language($post_id) {
				if (function_exists('wpml_loaded')) {
					global $sitepress;
					$default_language = function_exists('wpml_loaded') ? $sitepress->get_default_language() : get_locale();
					return apply_filters('wpml_object_id', $post_id, TTBM_Function::get_cpt_name(), TRUE, $default_language);
				}
				if (function_exists('pll_get_post_translations')) {
					$defaultLanguage = function_exists('pll_default_language') ? pll_default_language() : get_locale();
					$translations = function_exists('pll_get_post_translations') ? pll_get_post_translations($post_id) : [];
					return sizeof($translations) > 0 && array_key_exists($defaultLanguage, $translations) ? $translations[$defaultLanguage] : $post_id;
				}
				return $post_id;
			}
			//***********Template********************//
			public static function all_details_template() {
				$template_path = get_stylesheet_directory() . '/ttbm_templates/themes/';
				$default_path = TTBM_PLUGIN_DIR . '/templates/themes/';
				// Get the list of template files
				$dir = is_dir($template_path) ? glob($template_path . "*") : glob($default_path . "*");
				$templates = [];
				foreach ($dir as $filename) {
					if (is_file($filename) && is_readable($filename)) {
						$file = basename($filename);
						$file_contents = file_get_contents($filename);
						preg_match('/Template Name:\s*(.+)/i', $file_contents, $matches);
						$template_name = !empty($matches[1]) ? trim($matches[1]) : $file;
						$templates[$file] = $template_name;
					}
				}
				return apply_filters('ttbm_template_list_arr', $templates);
			}
			public static function details_template_path(): string {
				$tour_id = get_the_id();
				$template_name = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_theme_file', 'default.php');
				$file_name = 'themes/' . $template_name;
				$dir = TTBM_PLUGIN_DIR . '/templates/' . $file_name;
				if (!file_exists($dir)) {
					$file_name = 'themes/default.php';
				}
				return self::template_path($file_name);
			}
			public static function details_template_file_path($post_id = ''): string {
				$post_id = $post_id ?? get_the_id();
				$template_name = TTBM_Global_Function::get_post_info($post_id, 'ttbm_hotel_template', 'hotel_default.php');
				$file_name = 'themes/' . $template_name;
				$dir = TTBM_PLUGIN_DIR . '/templates/' . $file_name;
				if (!file_exists($dir)) {
					$file_name = 'themes/hotel_default.php';
				}
				return self::template_path($file_name);
			}
			public static function template_path($file_name): string {
				$template_path = get_stylesheet_directory() . '/ttbm_templates/';
				$default_dir = TTBM_PLUGIN_DIR . '/templates/';
				$dir = is_dir($template_path) ? $template_path : $default_dir;
				$file_path = $dir . $file_name;
				return locate_template(array('ttbm_templates/' . $file_name)) ? $file_path : $default_dir . $file_name;
			}
			//*********Date and Time**********************//
			public static function get_date($tour_id, $expire = '') {
				$tour_date = [];
				$travel_type = TTBM_Function::get_travel_type($tour_id);
				$now = strtotime(current_time('Y-m-d H:i:s'));
				if ($travel_type == 'particular') {
					$particular_dates = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_particular_dates', array());
					if (sizeof($particular_dates) > 0) {
						foreach ($particular_dates as $date) {
							$time = $date['ttbm_particular_start_time'] ?: '23.59.59';
							$full_date = TTBM_Function::reduce_stop_sale_hours($date['ttbm_particular_start_date'] . ' ' . $time);
							if ($expire || $now <= strtotime($full_date)) {
								$tour_date[] = $date['ttbm_particular_start_date'];
							}
						}
					}
					$tour_date = array_unique($tour_date);
				} else if ($travel_type == 'repeated') {
					$now_date = strtotime(current_time('Y-m-d'));
					$start_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_date');
					$start_date = $start_date ? gmdate('Y-m-d', strtotime($start_date)) : '';
					$ttbm_repeat_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_repeat_type');
					$end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_end_date');
					if($ttbm_repeat_type=='continue'){
						$end_date=$start_date ? gmdate('Y-m-d', strtotime($start_date.' +365 day')):'';
					}

					$end_date = $end_date ? gmdate('Y-m-d', strtotime($end_date)) : '';
					$off_days = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_offdays', array());
					$all_off_dates = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_off_dates', array());
					$off_dates = array();
					foreach ($all_off_dates as $off_date) {
						$off_dates[] = $off_date['mep_ticket_off_date'];
					}
					$tour_date = array();
					if ($start_date == $end_date) {
						$date = $start_date;
						$day = strtolower(gmdate('D', strtotime($date)));
						if (!in_array($day, $off_days) && !in_array($date, $off_dates)) {
							$current_date = self::get_date_by_time_check($tour_id, $date, $expire);
							if ($current_date) {
								$tour_date[] = $current_date;
							}
						}
					} else {
						$interval = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_after', 1);
						$all_dates = TTBM_Global_Function::date_separate_period($start_date, $end_date, $interval);
						foreach ($all_dates as $date) {
							$date = $date->format('Y-m-d'); // Convert DateTime object to string
							if ($expire || $now_date <= strtotime($date)) {
								$day = strtolower(gmdate('D', strtotime($date))); // Get the day in lowercase
								// Ensure $off_days and $off_dates are arrays before using in_array()
								if (!in_array($day, (array)$off_days) && !in_array($date, (array)$off_dates)) {
									$current_date = self::get_date_by_time_check($tour_id, $date, $expire);
									if ($current_date) {
										$tour_date[] = $current_date;
									}
								}
							}
						}
					}
				} else {
					$date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date');
					if ($date) {
						$time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date_time');
						$full_date = $time ? $date . ' ' . $time : $date . ' ' . '23.59.59';
						$tour_status = self::get_tour_status($tour_id);
						$end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date');
						$end_date_time = $end_date . ' ' . '23.59.59';
						$full_date = self::reduce_stop_sale_hours($end_date ? $end_date_time : $full_date);
						if ($expire || ($now <= strtotime($full_date) && $tour_status == 'active')) {
							$tour_date['date'] = $date;
							$tour_date['expire'] = $expire;
							$tour_date['now'] = $now;
							$tour_date['fulldate'] = $full_date;
							$tour_date['end_date'] = $end_date;
							$tour_date['checkout_date'] = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_date');
						}
					}
				}
				return apply_filters('ttbm_get_date', $tour_date, $tour_id, $expire);
			}
			public static function get_date_by_time_check($tour_id, $date, $expire) {
				$tour_date = '';
				$now = strtotime(current_time('Y-m-d H:i:s'));
				$times = TTBM_Function::get_time($tour_id, $date, true);
				if (is_array($times) && sizeof($times) > 0) {
					foreach ($times as $time) {
						$full_date = $time['time'] ? $date . ' ' . $time['time'] : $date . ' ' . '23.59.59';
						$full_date = TTBM_Function::reduce_stop_sale_hours($full_date);
						if ($expire || $now <= strtotime($full_date)) {
							$tour_date = $date;
						}
					}
				} else {
					$full_date = TTBM_Function::reduce_stop_sale_hours($date . ' ' . '23.59.59');
					if ($expire || $now <= strtotime($full_date)) {
						$tour_date = $date;
					}
				}
				return $tour_date;
			}
			public static function reduce_stop_sale_hours($date): string {
				$stop_hours = (int)self::get_general_settings('ttbm_ticket_expire_time') * 60 * 60;
				return gmdate('Y-m-d H:i:s', strtotime($date) - $stop_hours);
			}
			public static function get_time($tour_id, $date = '', $expire = '') {
				$date = $date ? gmdate('Y-m-d', strtotime($date)) : '';
				if ($date) {
					$time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date_time');
					return apply_filters('ttbm_get_time', $time, $tour_id, $date, $expire);
				}
				return false;
			}
			public static function update_upcoming_date_month($tour_id, $update = '', $all_date = array()): void {
				$now = strtotime(current_time('Y-m-d'));
				$db_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_upcoming_date');
				$db_date = gmdate('Y-m-d', strtotime($db_date));
				$month_list = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_month_list');
				if (!$month_list || !$db_date || $update || strtotime($db_date) < $now) {
					$date = '';
					$end_date = '';
					$all_date = sizeof($all_date) > 0 ? $all_date : self::get_date($tour_id);
					if (sizeof($all_date) > 0) {
						$date = current($all_date);
						$travel_type = TTBM_Function::get_travel_type($tour_id);
						if ($travel_type == 'particular' || $travel_type == 'repeated') {
							$end_date = end($all_date);
						} else {
							$reg_end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date');
							$end_date = $reg_end_date ?: $date;
						}
					}
					update_post_meta($tour_id, 'ttbm_upcoming_date', $date);
					update_post_meta($tour_id, 'ttbm_reg_end_date', $end_date);
					self::update_month_list($tour_id, $all_date);
				}
			}
			public static function get_upcoming_date_month($tour_id, $update = '', $all_date = array()) {
				$date = '';
				$now = strtotime(current_time('Y-m-d'));
				$db_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_upcoming_date');
				$db_date = gmdate('Y-m-d', strtotime($db_date));
				$month_list = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_month_list');
				if (!$month_list || !$db_date || $update || strtotime($db_date) < $now) {
					$all_date = sizeof($all_date) > 0 ? $all_date : self::get_date($tour_id);
					if (sizeof($all_date) > 0) {
						$date = current($all_date);
					}
				}
				return $date;
			}
			public static function update_all_upcoming_date_month(): void {
				$tour_ids = TTBM_Global_Function::get_all_post_id(TTBM_Function::get_cpt_name());
				foreach ($tour_ids as $tour_id) {
					self::update_upcoming_date_month($tour_id);
				}
			}
			public static function update_month_list($tour_id, $dates): void {
				$month = '';
				if (is_array($dates)) {
					$all_months = array();
					foreach ($dates as $date) {
						$all_months[] = gmdate('n', strtotime($date));
					}
					$all_months = array_unique($all_months);
					foreach ($all_months as $all_month) {
						$month = $month ? $month . ',' . $all_month : $all_month;
					}
				} else {
					$month = gmdate('n', strtotime($dates));
				}
				update_post_meta($tour_id, 'ttbm_month_list', $month);
			}
			public static function get_reg_end_date($tour_id) {
				$end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date');
				return apply_filters('ttbm_tour_reg_end_date', $end_date, $tour_id);
			}
			public static function datetime_format($date, $type = 'date-time-text') {
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');
				$wp_settings = $date_format . '  ' . $time_format;
				$timezone = wp_timezone_string();
				$timestamp = strtotime($date . ' ' . $timezone);
				if ($type == 'date-time') {
					$date = wp_date($wp_settings, $timestamp);
				} elseif ($type == 'date-text') {
					$date = wp_date($date_format, $timestamp);
				} elseif ($type == 'date') {
					$date = wp_date($date_format, $timestamp);
				} elseif ($type == 'time') {
					$date = wp_date($time_format, $timestamp, wp_timezone());
				} elseif ($type == 'day') {
					$date = wp_date('d', $timestamp);
				} elseif ($type == 'month') {
					$date = wp_date('M', $timestamp);
				} elseif ($type == 'date-time-text') {
					$date = wp_date($wp_settings, $timestamp, wp_timezone());
				} else {
					$date = wp_date($type, $timestamp);
				}
				return $date;
			}
			public static function date_format(): string {
				$format = self::get_general_settings('ttbm_date_format', 'D d M , yy');
				$date_format = 'Y-m-d';
				$date_format = $format == 'yy/mm/dd' ? 'Y/m/d' : $date_format;
				$date_format = $format == 'yy-dd-mm' ? 'Y-d-m' : $date_format;
				$date_format = $format == 'yy/dd/mm' ? 'Y/d/m' : $date_format;
				$date_format = $format == 'dd-mm-yy' ? 'd-m-Y' : $date_format;
				$date_format = $format == 'dd/mm/yy' ? 'd/m/Y' : $date_format;
				$date_format = $format == 'mm-dd-yy' ? 'm-d-Y' : $date_format;
				$date_format = $format == 'mm/dd/yy' ? 'm/d/Y' : $date_format;
				$date_format = $format == 'd M , yy' ? 'j M , Y' : $date_format;
				$date_format = $format == 'D d M , yy' ? 'D j M , Y' : $date_format;
				$date_format = $format == 'M d , yy' ? 'M  j, Y' : $date_format;
				return $format == 'D M d , yy' ? 'D M  j, Y' : $date_format;
			}
			//*************Price*********************************//
			public static function get_tour_start_price($tour_id, $start_date = ''): string {
				$start_price = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_price');
				$ticket_list = self::get_ticket_type($tour_id);
				$ticket_price = [];
				if (!$start_price && sizeof($ticket_list) > 0) {
					if (!$start_date) {
						$all_dates = TTBM_Function::get_date($tour_id);
						$start_date = sizeof($all_dates) > 0 ? current($all_dates) : $start_date;
					}
					foreach ($ticket_list as $ticket) {
						$ticket_name = $ticket['ticket_type_name'];
						$price = $ticket['ticket_type_price'];
						$price = array_key_exists('sale_price', $ticket) && $ticket['sale_price'] ? $ticket['sale_price'] : $price;
						$price = apply_filters('ttbm_filter_ticket_price', $price, $tour_id, $start_date, $ticket_name);
						$price = apply_filters('ttbm_price_by_name_filter', $price, $tour_id, 1, $start_date);
						$ticket_price[] = $price;
					}
					$start_price = min($ticket_price);
				}
				return $start_price;
			}
			public static function get_hotel_room_min_price($hotel_id) {
				$room_lists = TTBM_Global_Function::get_post_info($hotel_id, 'ttbm_room_details', array());
				$price = array();
				foreach ($room_lists as $room_list) {
					$price[] = $room_list['ttbm_hotel_room_price'];
				}
				return min($price);
			}
			public static function get_price_by_name($ticket_name, $tour_id, $hotel_id = '', $qty = '', $start_date = '') {
				$ttbm_type = self::get_tour_type($tour_id);
				$price = '';
				if ($ttbm_type == 'general') {
					$ticket_types = self::get_ticket_type($tour_id);
					foreach ($ticket_types as $ticket_type) {
						if ($ticket_type['ticket_type_name'] == $ticket_name) {
							$price = $ticket_type['ticket_type_price'];
							$price = array_key_exists('sale_price', $ticket_type) && $ticket_type['sale_price'] ? $ticket_type['sale_price'] : $price;
							$price = apply_filters('ttbm_filter_ticket_price', $price, $tour_id, $start_date, $ticket_name);
							$price = apply_filters('ttbm_price_by_name_filter', $price, $tour_id, $qty, $start_date);
						}
					}
				}
				if ($ttbm_type == 'hotel') {
					$room_lists = TTBM_Global_Function::get_post_info($hotel_id, 'ttbm_room_details', array());
					foreach ($room_lists as $room_list) {
						if ($room_list['ttbm_hotel_room_name'] == $ticket_name) {
							$price = $room_list['ttbm_hotel_room_price'];
						}
					}
				}
				return $price;
			}
			public static function check_discount_price_exit($tour_id, $ticket_name = '', $hotel_id = '', $qty = '', $start_date = '') {
				$ttbm_type = self::get_tour_type($tour_id);
				$price = '';
				if ($ttbm_type == 'general') {
					$ticket_types = self::get_ticket_type($tour_id);
					foreach ($ticket_types as $ticket_type) {
						if (!$ticket_name || $ticket_type['ticket_type_name'] == $ticket_name) {
							$regular_price = $ticket_type['ticket_type_price'];
							$sale_price = array_key_exists('sale_price', $ticket_type) && $ticket_type['sale_price'] ? $ticket_type['sale_price'] : '';
							$price = $regular_price && $sale_price ? $regular_price : '';
							return apply_filters('ttbm_filter_ticket_discount_price_check', $price, $tour_id, $start_date, $ticket_name);
							//$price = apply_filters( 'ttbm_price_by_name_filter', $price, $tour_id, $qty );
						}
					}
				}
				return $price;
			}
			public static function get_extra_service_price_by_name($tour_id, $service_name) {
				$extra_services = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_extra_service_data', array());
				$price = '';
				if (sizeof($extra_services) > 0) {
					foreach ($extra_services as $service) {
						if ($service['service_name'] == $service_name) {
							return $service['service_price'];
						}
					}
				}
				return $price;
			}
			//************************************//
			public static function get_submit_info($key, $default = '') {
				return self::data_sanitize($_POST[$key] ?? $default);
			}
			//***********Duration*************************//
			public static function get_duration($tour_id) {
				$duration = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_duration', 0);
				return apply_filters('ttbm_tour_duration', $duration, $tour_id);
			}
			public static function get_all_duration(): array {
				$tour_ids = TTBM_Global_Function::get_all_post_id(TTBM_Function::get_cpt_name());
				$duration = array();
				foreach ($tour_ids as $tour_id) {
					$duration[] = self::get_duration($tour_id);
				}
				$duration = array_unique($duration);
				natsort($duration);
				return $duration;
			}
			//************Seat***********************//
			public static function get_total_seat($tour_id) {
				$total_seat = 0;
				$tour_type = self::get_tour_type($tour_id);
				if ($tour_type == 'general') {
					$ticket_list = self::get_ticket_type($tour_id);
					if (sizeof($ticket_list) > 0) {
						foreach ($ticket_list as $_ticket_list) {
							$total_seat = $_ticket_list['ticket_type_qty'] + $total_seat;
						}
					}
				}
				return apply_filters('ttbm_get_total_seat_filter', $total_seat, $tour_id);
			}
			public static function get_total_reserve($tour_id) {
				$reserve = 0;
				$tour_type = self::get_tour_type($tour_id);
				if ($tour_type == 'general') {
					$ticket_list = self::get_ticket_type($tour_id);
					if (sizeof($ticket_list) > 0) {
						foreach ($ticket_list as $_ticket_list) {
							if (array_key_exists('ticket_type_resv_qty', $_ticket_list) && $_ticket_list['ticket_type_resv_qty'] > 0) {
								$reserve = $_ticket_list['ticket_type_resv_qty'] + $reserve;
							}
						}
					}
				}
				return apply_filters('ttbm_get_total_reserve_filter', $reserve, $tour_id);
			}
			public static function get_total_sold($tour_id, $tour_date = '', $type = '', $hotel_id = ''): int {
				$tour_date = $tour_date ?: TTBM_Global_Function::get_post_info($tour_id, 'ttbm_upcoming_date');
				$type = apply_filters('ttbm_type_filter', $type, $tour_id);
				$sold_query = TTBM_Query::query_all_sold($tour_id, $tour_date, $type, $hotel_id);
				return $sold_query->post_count;
			}
			public static function get_total_available($tour_id, $tour_date = '') {
				$total = self::get_total_seat($tour_id);
				$reserve = self::get_total_reserve($tour_id);
				$sold = self::get_total_sold($tour_id, $tour_date);
				$available = $total - ($reserve + $sold);
				return max(0, $available);
			}
			public static function get_any_date_seat_available($tour_id) {
				$travel_type = TTBM_Function::get_travel_type($tour_id);
				if ($travel_type != 'fixed') {
					$total = self::get_total_seat($tour_id);
					$reserve = self::get_total_reserve($tour_id);
					$all_dates = TTBM_Function::get_date($tour_id);
					if (sizeof($all_dates) > 0) {
						foreach ($all_dates as $date) {
							$time_slots = TTBM_Function::get_time($tour_id, $date);
							$slot_length = is_array($time_slots) && sizeof($time_slots) > 0 ? sizeof($time_slots) : 1;
							$date_total = $total * $slot_length;
							$date_reserve = $reserve * $slot_length;
							$sold = self::get_total_sold($tour_id, $date);
							$available = $date_total - ($date_reserve + $sold);
							$available = max(0, $available);
							if ($available > 0) {
								return $available;
							}
						}
					}
					return 0;
				} else {
					return self::get_total_available($tour_id);
				}
			}
			//*********************************//
			public static function get_ticket_type($tour_id) {
				$ttbm_type = self::get_tour_type($tour_id);
				$tickets = array();
				if ($ttbm_type == 'general') {
					$tickets = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticket_type', array());
					$tickets = apply_filters('ttbm_ticket_type_filter', $tickets, $tour_id);
				}
				return $tickets;
			}
			//*********************************//
			public static function tour_type() {
				$type = array('general' => __('General Tour', 'tour-booking-manager'), 'hotel' => __('Hotel Base Tour', 'tour-booking-manager'));
				return apply_filters('add_ttbm_tour_type', $type);
			}
			public static function get_tour_type($tour_id) {
				$tour_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_type', 'general');
				if ($tour_type == 'hiphop') {
					update_post_meta($tour_id, 'ttbm_type', 'general');
					$tour_type = 'general';
				}
				return $tour_type;
			}
			public static function get_travel_type($tour_id) {
				$type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_type', 'fixed');
				return apply_filters('ttbm_tour_type', $type, $tour_id);
			}
			public static function travel_type_array(): array {
				return array('fixed' => __('Fixed Dates', 'tour-booking-manager'), 'particular' => __('Particular Dates', 'tour-booking-manager'), 'repeated' => __('Repeated Dates', 'tour-booking-manager'));
			}
			public static function travel_repeat_array(): array {
				return array('1' => __('Daily', 'tour-booking-manager'), '7' => __('Weekly', 'tour-booking-manager'), '30' => __('Monthly', 'tour-booking-manager'));
			}
			public static function get_tour_status($tour_id, $status = 'active') {
				$tour_type = self::get_tour_type($tour_id);
				$date_type = TTBM_Function::get_travel_type($tour_id);
				if ($tour_type == 'general' && $date_type == 'fixed') {
					$now = current_time('Y-m-d H:i:s');
					$reg_end_date = self::get_reg_end_date($tour_id);
					$end_time = gmdate('Y-m-d H:i:s', strtotime($reg_end_date));
					$_status = strtotime($now) < strtotime($end_time) ? 'active' : 'expired';
					$status = !empty($reg_end_date) ? $_status : 'active';
				}
				return $status;
			}
			//***********Location & Place*************************//
			public static function get_all_location(): array {
				$locations = TTBM_Global_Function::get_taxonomy('ttbm_tour_location');
				$arr = array('' => esc_html__('--Select a city--', 'tour-booking-manager'));
				foreach ($locations as $_terms) {
					$arr[$_terms->name] = $_terms->name;
				}
				return $arr;
			}
			public static function get_full_location($tour_id): string {
				$city = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_location_name');
				$country = self::get_country($tour_id);
				$full_location = $city && $country ? $city . ' , ' . $country : '';
				$full_location = $city && !$country ? $city : $full_location;
				$full_location = is_array($full_location) ? '' : $full_location;
				return !$city && $country ? $country : $full_location;
			}
			public static function get_country($tour_id) {
				$location = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_location_name');
				$country = '';
				if ($location) {
					$term = get_term_by('name', $location, 'ttbm_tour_location');
					$name = $term && $term->term_id ? get_term_meta($term->term_id, 'ttbm_country_location') : array();
					if (is_array($name) && sizeof($name) > 0) {
						$country = $name[0];
					}
				}
				return $country;
			}
			public static function get_all_country(): array {
				$locations = TTBM_Global_Function::get_taxonomy('ttbm_tour_location');
				$country = [];
				if (sizeof($locations) > 0) {
					foreach ($locations as $location) {
						$name = get_term_meta($location->term_id, 'ttbm_country_location');
						if (is_array($name) && sizeof($name) > 0) {
							$country[] = $name[0];
						}
					}
				}
				return array_unique($country);
			}
			//*******************************//
			public static function get_hotel_list($tour_id) {
				$type = self::get_tour_type($tour_id);
				$hotel_lists = array();
				if ($type == 'hotel') {
					$hotel_lists = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotels', $hotel_lists);
				}
				return $hotel_lists;
			}
			//**********************//
			public static function get_feature_list($tour_id, $name): array {
				$services = TTBM_Global_Function::get_post_info($tour_id, $name);
				if (is_array($services) && sizeof($services) > 0) {
					$terms = array();
					foreach ($services as $service) {
						if (is_array($service) && array_key_exists('name', $service)) {
							$terms[] = $service['name'];
						} else {
							if (is_array($service)) {
								$terms[] = $service['ttbm_feature_item'];
							} else {
								$terms[] = $service;
							}
						}
					}
					$services = $terms;
				} else {
					$services = self::feature_id_to_array($services);
				}
				return $services;
			}
			public static function feature_id_to_array($ids): array {
				$ids = $ids ? explode(',', $ids) : array();
				$data = array();
				foreach ($ids as $id) {
					if ($id) {
						$term = get_term_by('id', $id, 'ttbm_tour_features_list');
						if ($term) {
							$data[] = $term->name;
						}
					}
				}
				return $data;
			}
			public static function feature_array_to_string($features): string {
				$ids = '';
				if (sizeof($features) > 0) {
					foreach ($features as $feature) {
						$term = get_term_by('name', $feature, 'ttbm_tour_features_list');
						if ($term) {
							$ids = $ids ? $ids . ',' . $term->term_id : $term->term_id;
						}
					}
				}
				return $ids;
			}
			public static function check_exit_feature($features, $features_name): bool {
				if (sizeof($features) > 0) {
					foreach ($features as $feature) {
						if ($feature == $features_name) {
							return true;
						}
					}
				}
				return false;
			}
			/********************/
			public static function get_tag_id($tags) {
				if (is_array($tags)) {
					$term_id = '';
					foreach ($tags as $tag) {
						$term_id = $term_id ? $term_id . ',' . $tag->term_id : $tag->term_id;
					}
					$tags = $term_id;
				}
				return $tags;
			}
			//*******************************//
			public static function get_taxonomy_name_to_id_string_old($tour_id, $key, $taxonomy) {
				$infos = TTBM_Global_Function::get_post_info($tour_id, $key, array());
				$id = '';
				if ($infos && sizeof($infos) > 0) {
					foreach ($infos as $info) {
						$term = get_term_by('name', $info, $taxonomy);
						if ($term && $term->term_id) {
							$id = $id ? $id . ',' . $term->term_id : $term->term_id;
						}
					}
				}
				return $id;
			}
			public static function get_taxonomy_name_to_id_string($tour_id, $key, $taxonomy) {
				$infos = TTBM_Global_Function::get_post_info($tour_id, $key, array());
				$id = '';
				if ($infos && sizeof($infos) > 0) {
					foreach ($infos as $info) {
						$id = $id . ',' . $info;
					}
					$id = ltrim($id, ',');
				}
				return $id;
			}
			public static function get_taxonomy_id_string($tour_id, $taxonomy) {
				$infos = get_the_terms($tour_id, $taxonomy);
				$id = '';
				if (is_array($infos) && sizeof($infos) > 0) {
					foreach ($infos as $info) {
						$id = $id ? $id . ',' . $info->term_id : $info->term_id;
					}
				}
				return $id;
			}
			public static function get_taxonomy_string($tour_id, $taxonomy) {
				$infos = get_the_terms($tour_id, $taxonomy);
				$id = '';
				if (is_array($infos) && sizeof($infos) > 0) {
					foreach ($infos as $info) {
						$id = $id ? $id . ' , ' . $info->name : $info->name;
					}
				}
				return $id;
			}
			//************************//
			public static function get_settings($key, $option_name, $default = '') {
				$options = get_option($option_name);
				return self::get_ttbm_settings($options, $key, $default);
			}
			public static function get_ttbm_settings($options, $key, $default = '') {
				if (isset($options[$key]) && $options[$key]) {
					$default = $options[$key];
				}
				return $default;
			}
			public static function get_general_settings($key, $default = '') {
				$options = get_option('ttbm_basic_gen_settings');
				return self::get_ttbm_settings($options, $key, $default);
			}
			public static function get_translation_settings($key, $default = '') {
				$options = get_option('ttbm_basic_translation_settings');
				return self::get_ttbm_settings($options, $key, $default);
			}
			public static function translation_settings($key, $default = '') {
				$options = get_option('ttbm_basic_translation_settings');
				echo esc_html(self::get_ttbm_settings($options, $key, $default));
			}
			//***************************//
			public static function get_map_api() {
				$options = get_option('ttbm_basic_gen_settings');
				$default = '';
				if (isset($options['ttbm_gmap_api_key']) && $options['ttbm_gmap_api_key']) {
					$default = $options['ttbm_gmap_api_key'];
				}
				return $default;
			}
			public static function ticket_name_text() {
				return self::get_translation_settings('ttbm_string_ticket_name', esc_html__('Name', 'tour-booking-manager'));
			}
			public static function ticket_price_text() {
				return self::get_translation_settings('ttbm_string_ticket_price', esc_html__('Price', 'tour-booking-manager'));
			}
			public static function ticket_qty_text() {
				return self::get_translation_settings('ttbm_string_ticket_qty', esc_html__('Qty', 'tour-booking-manager'));
			}
			public static function service_name_text() {
				return self::get_translation_settings('ttbm_string_service_name', esc_html__('Name', 'tour-booking-manager'));
			}
			public static function service_price_text() {
				return self::get_translation_settings('ttbm_string_service_price', esc_html__('Price', 'tour-booking-manager'));
			}
			public static function service_qty_text() {
				return self::get_translation_settings('ttbm_string_service_qty', esc_html__('Qty', 'tour-booking-manager'));
			}
			//*****************//
			public static function get_cpt_name(): string {
				return 'ttbm_tour';
			}
			public static function get_name() {
				return self::get_general_settings('ttbm_travel_label', 'Tour');
			}
			public static function get_slug() {
				return self::get_general_settings('ttbm_travel_slug', 'tour');
			}
			public static function get_icon() {
				return self::get_general_settings('ttbm_travel_icon', 'dashicons-admin-site-alt2');
			}
			public static function get_category_label() {
				return self::get_general_settings('ttbm_travel_cat_label', 'Category');
			}
			public static function get_category_slug() {
				return self::get_general_settings('ttbm_travel_cat_slug', 'travel-category');
			}
			public static function get_organizer_label() {
				return self::get_general_settings('ttbm_travel_org_label', 'Organizer');
			}
			public static function get_organizer_slug() {
				return self::get_general_settings('ttbm_travel_org_slug', 'travel-organizer');
			}
			//***********************//
			public static function recurring_check($tour_id) {
				$travel_type = self::get_travel_type($tour_id);
				$tour_type = self::get_tour_type($tour_id);
				if ($tour_type == 'general' && ($travel_type == 'particular' || $travel_type == 'repeated')) {
					return true;
				}
				return '';
			}
			//******************************************************************** Remove nearly no use any where***********//
			public static function get_post_info($tour_id, $key, $default = '') {
				$data = get_post_meta($tour_id, $key, true) ?: $default;
				return TTBM_Global_Function::data_sanitize($data);
			}
			public static function data_sanitize($data) {
				if (is_array($data)) {
					foreach ($data as &$value) {
						if (is_array($value)) {
							$value = self::data_sanitize($value);
						} else {
							$value = sanitize_text_field($value);
						}
					}
					return $data;
				}
				if (is_string($data) && !empty($data)) {
					$unserialized = @unserialize($data, ['allowed_classes' => false]);
					if ($unserialized !== false || $data === 'b:0;') {
						return self::data_sanitize($unserialized);
					}
					return sanitize_text_field($data);
				}
				return $data;
			}
			public static function get_image_url($post_id = '', $image_id = '', $size = 'full') {
				if ($post_id) {
					$image_id = TTBM_Global_Function::get_post_info($post_id, 'ttbm_list_thumbnail');
					$image_id = $image_id ?: get_post_thumbnail_id($post_id);
				}
				return wp_get_attachment_image_url($image_id, $size);
			}
			public static function check_time($tour_id, $date): bool {
				$time_slots = self::get_time($tour_id, $date);
				if ($time_slots) {
					if (is_array($time_slots)) {
						if (sizeof($time_slots) > 0) {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				}
				return false;
			}
			public static function price_convert_raw($price) {
				$price = wp_strip_all_tags($price);
				$price = str_replace(get_woocommerce_currency_symbol(), '', $price);
				$price = str_replace(wc_get_price_thousand_separator(), 't_s', $price);
				$price = str_replace(wc_get_price_decimal_separator(), 'd_s', $price);
				$price = str_replace('t_s', '', $price);
				$price = str_replace('d_s', '.', $price);
				return max($price, 0);
			}
			public static function get_active_tours($args) {
				$tours = array();
				$query = new WP_Query($args);
				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						$tour_id = '';
						$tour_id = get_the_ID();
						$tour_id = TTBM_Function::post_id_multi_language($tour_id);
						$dates = TTBM_Function::get_date($tour_id);
						$ticket_lists = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticket_type', array());
						$available_seat = TTBM_Function::get_total_available($tour_id);
						if (is_array($dates) && count($dates)) {
							if ($available_seat > 0 && sizeof($ticket_lists) > 0) {
								$tours[] = $tour_id;
							}
						}
					}
					wp_reset_postdata();
					if (count($tours)) {
						unset($args);
						$args = array(
							'post_type' => array(TTBM_Function::get_cpt_name()),
							'posts_per_page' => -1,
							'order' => 'ASC',
							'orderby' => 'meta_value',
							'post__in' => $tours,
						);
						return new WP_Query($args);
					}
					return $query;
				}
				return $query;
			}
			public static function esc_html($string): string {
				$allow_attr = array(
					'input' => ['type' => [], 'class' => [], 'id' => [], 'name' => [], 'value' => [], 'size' => [], 'placeholder' => [], 'min' => [], 'max' => [], 'checked' => [], 'required' => [], 'disabled' => [], 'readonly' => [], 'step' => [], 'data-default-color' => [], 'data-price' => [],],
					'p' => ['class' => []],
					'img' => ['class' => [], 'id' => [], 'src' => [], 'alt' => [],],
					'fieldset' => ['class' => []],
					'label' => ['for' => [], 'class' => []],
					'select' => ['class' => [], 'name' => [], 'id' => [], 'data-price' => [],],
					'option' => ['class' => [], 'value' => [], 'id' => [], 'selected' => [],],
					'textarea' => ['class' => [], 'rows' => [], 'id' => [], 'cols' => [], 'name' => [],],
					'h2' => ['class' => [], 'id' => [],],
					'a' => ['class' => [], 'id' => [], 'href' => [],],
					'div' => ['class' => [], 'id' => [], 'data-ticket-type-name' => [],],
					'span' => ['class' => [], 'id' => [], 'data' => [], 'data-input-change' => [],],
					'i' => ['class' => [], 'id' => [], 'data' => [],],
					'table' => ['class' => [], 'id' => [], 'data' => [],],
					'tr' => ['class' => [], 'id' => [], 'data' => [],],
					'td' => ['class' => [], 'id' => [], 'data' => [],],
					'thead' => ['class' => [], 'id' => [], 'data' => [],],
					'tbody' => ['class' => [], 'id' => [], 'data' => [],],
					'th' => ['class' => [], 'id' => [], 'data' => [],],
					'svg' => ['class' => [], 'id' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'xmlns' => [],],
					'g' => ['fill' => [],],
					'path' => ['d' => [],],
					'br' => array(),
					'em' => array(),
					'strong' => array(),
				);
				return wp_kses($string, $allow_attr);
			}
			public static function get_meta_values($meta_key = '', $post_type = 'post', $post_status = 'publish') {
				if (empty($meta_key) || !is_string($meta_key)) {
					return false;
				}
				// Use WP_Query to get posts with the specified criteria
				$args = [
					'post_type' => $post_type,
					'post_status' => $post_status,
					'posts_per_page' => -1, // Get all posts
					'fields' => 'ids', // Only get post IDs for better performance
					'meta_query' => [
						[
							'key' => $meta_key,
							'compare' => 'EXISTS', // Posts that have this meta key
						]
					]
				];
				$query = new WP_Query($args);
				$meta_values = [];
				if ($query->have_posts()) {
					foreach ($query->posts as $post_id) {
						$value = get_post_meta($post_id, $meta_key, true);
						if ($value !== '') { // Skip empty values
							$meta_values[] = $value;
						}
					}
					// Return only unique values

				// $meta_values = array_unique($meta_values);
          
				}
				return $meta_values;
			}
			public static function get_travel_analytical_data() {
				$result_date = array();
				$travel_args = array(
					'post_type' => 'ttbm_tour',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'orderby' => 'date',
					'order' => 'DESC',
				);
				$all_travel_query = new WP_Query($travel_args);
				$active_tour = $expired_tour = $total_price = $price_count = $average_price = 0;
				$location_counts = array();
				$top_destination = '';
				if ($all_travel_query->have_posts()) {
					while ($all_travel_query->have_posts()) {
						$all_travel_query->the_post();
						$travel_post_id = get_the_ID();
						$status = TTBM_Function::get_tour_status($travel_post_id);
						if ($status === 'active') {
							$active_tour = $active_tour + 1;
						} else {
							$expired_tour = $expired_tour + 1;
						}
						$get_price = get_post_meta($travel_post_id, 'ttbm_travel_start_price', true);
						if ($get_price > 0) {
							$price_count++;
							$total_price = $total_price + $get_price;
						}
						// Count locations for top destination
						$location = get_post_meta($travel_post_id, 'ttbm_location_name', true);
						if (!empty($location)) {
							if (isset($location_counts[$location])) {
								$location_counts[$location]++;
							} else {
								$location_counts[$location] = 1;
							}
						}
					}
				}
				if ($price_count > 0 && $total_price > 0) {
					$average_price = $total_price / $price_count;
				}
				// Find top destination
				if (!empty($location_counts)) {
					arsort($location_counts);
					$top_destination = array_key_first($location_counts);
				}
				$all_location = TTBM_Function::get_all_location();
				unset($all_location['']);
				$location_count = count($all_location);
				return array(
					'location_count' => $location_count,
					'average_price' => $average_price,
					'active_tour' => $active_tour,
					'expired_tour' => $expired_tour,
					'top_destination' => $top_destination,
				);
			}

            public static function get_top_deals_post_ids($type) {
                $allowed_types = array('popular', 'trending', 'feature', 'deal-discount' );

                if (!in_array($type, $allowed_types)) {
                    return array();
                }

                $args = array(
                    'post_type'      => 'ttbm_tour', // Change to your post type if needed
                    'posts_per_page' => -1,
                    'fields'         => 'ids', // Only return post IDs
                    'meta_query'     => array(
                        array(
                            'key'     => 'ttbm_top_picks_deals',
                            'value'   => $type,
                            'compare' => 'LIKE',
                        ),
                    ),
                );

                $query = new WP_Query($args);

                return $query->posts;
            }

            public static function get_city_place_ids_with_post_ids( $num_of_places = 0 ) {
                $args = [
                    'post_type'      => 'ttbm_tour',
                    'posts_per_page' => -1,
                    'meta_query'     => [
                        [
                            'key'     => 'ttbm_hiphop_places',
                            'compare' => 'EXISTS',
                        ],
                    ],
                    'fields' => 'ids',
                ];

                $query = new WP_Query( $args );

                $city_place_map = [];

                if ( $query->have_posts() ) {
                    foreach ( $query->posts as $post_id ) {
                        $places = get_post_meta( $post_id, 'ttbm_hiphop_places', true );
                        $places = maybe_unserialize( $places );

                        if ( is_array( $places ) ) {
                            foreach ( $places as $place ) {
                                if ( isset( $place['ttbm_city_place_id'] ) ) {
                                    $place_id = $place['ttbm_city_place_id'];

                                    if ( ! isset( $city_place_map[ $place_id ] ) ) {
                                        $city_place_map[ $place_id ] = [];
                                    }

                                    if ( ! in_array( $post_id, $city_place_map[ $place_id ] ) ) {
                                        $city_place_map[ $place_id ][] = $post_id;
                                    }
                                }
                            }
                        }
                    }
                }

                uasort( $city_place_map, function( $a, $b ) {
                    return count( $b ) - count( $a );
                });

                /*if ( $num_of_places > 0 ) {
                    $city_place_map = array_slice( $city_place_map, 0, $num_of_places, true );
                }*/

                return $city_place_map;
            }

            public static function get_all_activity_ids_from_posts_old( $num_of_ids = 0 ) {
                $query = new WP_Query(array(
                    'post_type'      => 'ttbm_tour', // আপনার CPT
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_key'       => 'ttbm_tour_activities',
                    'fields'         => 'ids',
                ));

                $activity_ids = [];

                foreach ($query->posts as $post_id) {
                    $meta_value = get_post_meta($post_id, 'ttbm_tour_activities', true);

                    if (!empty($meta_value)) {
                        // unserialize the value
                        $unserialized = maybe_unserialize($meta_value);

                        if (is_array($unserialized)) {
                            foreach ($unserialized as $activity_id) {
                                $activity_ids[] = $activity_id;
                            }
                        }
                    }
                }

                // FIXED: Removed excessive blank lines - 2025-01-21 by Shahnur Alam
                $activity_ids = array_unique($activity_ids);

                if ( $num_of_ids > 0 ) {
                    $activity_ids = array_slice( $activity_ids, 0, $num_of_ids, true );
                }

                return $activity_ids;
            }
            public static function get_all_category_with_assign_post( $taxonomy ) {

                $terms = get_terms([
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => true,
                ]);

                $result = [];

                if ( !is_wp_error( $terms) && !empty( $terms ) ) {
                    foreach ( $terms as $term ) {

                        // Get post IDs for each term
                        $query = new WP_Query([
                            'post_type'      => 'ttbm_tour',
                            'post_status'    => 'publish',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                            'tax_query'      => [
                                [
                                    'taxonomy' => $taxonomy,
                                    'field'    => 'term_id',
                                    'terms'    => $term->term_id,
                                ],
                            ],
                        ]);

                        if (!empty($query->posts)) {
                            $result[] = [
                                'term_id'   => $term->term_id,
                                'term_name' => $term->name,
                                'term_slug' => $term->slug,
                                'term_description' => $term->description,
                                'post_ids'  => $query->posts,
                            ];
                        }

                        wp_reset_postdata();
                    }
                }

                return $result;

            }
            public static function get_all_activity_ids_from_posts( $num_of_ids = 0 ) {
                $query = new WP_Query(array(
                    'post_type'      => 'ttbm_tour', // CPT ঠিক দিন
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_key'       => 'ttbm_tour_activities',
                    'fields'         => 'ids',
                ));

                $activity_posts = [];

                foreach ($query->posts as $post_id) {

                    $meta_value = get_post_meta($post_id, 'ttbm_tour_activities', true);

                    if (!empty($meta_value)) {
                        $unserialized = maybe_unserialize($meta_value);

                        if (is_array($unserialized)) {
                            foreach ($unserialized as $activity_id) {
                                if (!isset($activity_posts[$activity_id])) {
                                    $activity_posts[$activity_id] = [];
                                }
                                $activity_posts[$activity_id][] = $post_id;
                            }
                        }
                    }
                }

                // Optional: Limit number of activity IDs returned
                /*if ($num_of_ids > 0) {
                    $activity_posts = array_slice($activity_posts, 0, $num_of_ids, true);
                }*/

                return $activity_posts;
            }
            public static function get_location_feature( $taxonomy, $num_of_ids = 0 ) {
                $query = new WP_Query(array(
                    'post_type'      => 'ttbm_tour', // CPT ঠিক দিন
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_key'       => $taxonomy,
                    'fields'         => 'ids',
                ));

                $activity_posts = [];

                foreach ($query->posts as $post_id) {

                    $meta_value = get_post_meta( $post_id, $taxonomy, true);

                    if (!empty($meta_value)) {
                        $unserialized = maybe_unserialize($meta_value);

                        if (is_array($unserialized)) {
                            foreach ($unserialized as $activity_id) {
                                if (!isset($activity_posts[$activity_id])) {
                                    $activity_posts[$activity_id] = [];
                                }
                                $activity_posts[$activity_id][] = $post_id;
                            }
                        }
                    }
                }

                $terms_data = [];
                $taxonomy_name = 'ttbm_tour_features_list';

                if( is_array( $activity_posts ) && !empty( $activity_posts ) ) {
                    foreach ( $activity_posts  as $term_name => $post_ids ) {
                        $term = get_term_by('name', $term_name, $taxonomy_name );
                        if ($term && !is_wp_error($term)) {
                            $terms_data[] = [
                                'term_id' => $term->term_id,
                                'term_name' => $term->name,
                                'term_slug' => $term->slug,
                                'term_description' => $term->description,
                                'post_ids' => $post_ids,
                            ];
                        }
                    }
                }

                return $terms_data;
            }

            public static function ttbm_get_term_data( $term_type, $term_ids = [] ) {
                $args = [
                    'taxonomy'   => $term_type,
                    'hide_empty' => false,
                ];

                if ( ! empty( $term_ids ) ) {
                    $args['include'] = $term_ids;
                }

                return get_terms( $args );
            }

        }
		new TTBM_Function();
	}
	if (!function_exists('mep_esc_html')) {
		function mep_esc_html($string): string {
			$allow_attr = array(
				'input' => [
					'type' => [],
					'class' => [],
					'id' => [],
					'name' => [],
					'value' => [],
					'size' => [],
					'placeholder' => [],
					'min' => [],
					'max' => [],
					'checked' => [],
					'required' => [],
					'disabled' => [],
					'readonly' => [],
					'step' => [],
					'data-default-color' => [],
					'data-price' => [],
				],
				'p' => ['class' => [], 'style' => []],
				'img' => ['class' => [], 'id' => [], 'src' => [], 'alt' => [], 'style' => []],
				'fieldset' => ['class' => [], 'style' => []],
				'label' => ['for' => [], 'class' => [], 'style' => []],
				'select' => [
					'class' => [],
					'name' => [],
					'id' => [],
					'data-price' => [],
					'style' => [],
				],
				'option' => [
					'class' => [],
					'value' => [],
					'id' => [],
					'selected' => [],
					'style' => [],
				],
				'textarea' => [
					'class' => [],
					'rows' => [],
					'id' => [],
					'cols' => [],
					'name' => [],
					'style' => []
				],
				'h2' => ['class' => [], 'id' => [], 'style' => []],
				'a' => ['class' => [], 'id' => [], 'href' => [], 'style' => []],
				'div' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data-ticket-type-name' => [],
				],
				'span' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'i' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'table' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'tr' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'td' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'thead' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'tbody' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'th' => [
					'class' => [], 'style' => [],
					'id' => [],
					'data' => [],
				],
				'svg' => [
					'class' => [], 'style' => [],
					'id' => [],
					'width' => [],
					'height' => [],
					'viewBox' => [],
					'xmlns' => [],
				],
				'g' => [
					'fill' => [], 'style' => [],
				],
				'path' => [
					'd' => [], 'style' => [],
				],
				'br' => array(),
				'em' => array(),
				'strong' => array(),
			);
			return wp_kses($string, $allow_attr);
		}
	}
	function ttbm_elementor_get_tax_term($tax, $type = 'id'): array {
		$terms = get_terms(array(
			'taxonomy' => $tax,
			'hide_empty' => false,
		));
		$list = array('0' => __('Show All', 'tour-booking-manager'));
		foreach ($terms as $_term) {
			if ($type == 'id') {
				$list[$_term->term_id] = $_term->name;
			} else {
				$list[$_term->slug] = $_term->name;
			}
		}
		return $list;
	}
	function ttbm_get_coutnry_arr(): array {
		$countries = array(
			"Afghanistan",
			"Albania",
			"Algeria",
			"American Samoa",
			"Andorra",
			"Angola",
			"Anguilla",
			"Antarctica",
			"Antigua and Barbuda",
			"Argentina",
			"Armenia",
			"Aruba",
			"Australia",
			"Austria",
			"Azerbaijan",
			"Bahamas",
			"Bahrain",
			"Bangladesh",
			"Barbados",
			"Belarus",
			"Belgium",
			"Belize",
			"Benin",
			"Bermuda",
			"Bhutan",
			"Bolivia",
			"Bosnia and Herzegowina",
			"Botswana",
			"Bouvet Island",
			"Brazil",
			"British Indian Ocean Territory",
			"Brunei Darussalam",
			"Bulgaria",
			"Burkina Faso",
			"Burundi",
			"Cambodia",
			"Cameroon",
			"Canada",
			"Cape Verde",
			"Cayman Islands",
			"Central African Republic",
			"Chad",
			"Chile",
			"China",
			"Christmas Island",
			"Cocos (Keeling) Islands",
			"Colombia",
			"Comoros",
			"Congo",
			"Congo, the Democratic Republic of the",
			"Cook Islands",
			"Costa Rica",
			"Cote d'Ivoire",
			"Croatia (Hrvatska)",
			"Cuba",
			"Cyprus",
			"Czech Republic",
			"Denmark",
			"Djibouti",
			"Dominica",
			"Dominican Republic",
			"East Timor",
			"Ecuador",
			"Egypt",
			"El Salvador",
			"Equatorial Guinea",
			"Eritrea",
			"Estonia",
			"Ethiopia",
			"Falkland Islands (Malvinas)",
			"Faroe Islands",
			"Fiji",
			"Finland",
			"France",
			"France Metropolitan",
			"French Guiana",
			"French Polynesia",
			"French Southern Territories",
			"Gabon",
			"Gambia",
			"Georgia",
			"Germany",
			"Ghana",
			"Gibraltar",
			"Greece",
			"Greenland",
			"Grenada",
			"Guadeloupe",
			"Guam",
			"Guatemala",
			"Guinea",
			"Guinea-Bissau",
			"Guyana",
			"Haiti",
			"Heard and Mc Donald Islands",
			"Holy See (Vatican City State)",
			"Honduras",
			"Hong Kong",
			"Hungary",
			"Iceland",
			"India",
			"Indonesia",
			"Iran (Islamic Republic of)",
			"Iraq",
			"Ireland",
			"Israel",
			"Italy",
			"Jamaica",
			"Japan",
			"Jordan",
			"Kazakhstan",
			"Kenya",
			"Kiribati",
			"Korea, Democratic People's Republic of",
			"Korea, Republic of",
			"Kuwait",
			"Kyrgyzstan",
			"Lao, People's Democratic Republic",
			"Latvia",
			"Lebanon",
			"Lesotho",
			"Liberia",
			"Libyan Arab Jamahiriya",
			"Liechtenstein",
			"Lithuania",
			"Luxembourg",
			"Macau",
			"Macedonia, The Former Yugoslav Republic of",
			"Madagascar",
			"Malawi",
			"Malaysia",
			"Maldives",
			"Mali",
			"Malta",
			"Marshall Islands",
			"Martinique",
			"Mauritania",
			"Mauritius",
			"Mayotte",
			"Mexico",
			"Micronesia, Federated States of",
			"Moldova, Republic of",
			"Monaco",
			"Mongolia",
			"Montserrat",
			"Morocco",
			"Mozambique",
			"Myanmar",
			"Namibia",
			"Nauru",
			"Nepal",
			"Netherlands",
			"Netherlands Antilles",
			"New Caledonia",
			"New Zealand",
			"Nicaragua",
			"Niger",
			"Nigeria",
			"Niue",
			"Norfolk Island",
			"Northern Mariana Islands",
			"Norway",
			"Oman",
			"Pakistan",
			"Palau",
			"Panama",
			"Papua New Guinea",
			"Paraguay",
			"Peru",
			"Philippines",
			"Pitcairn",
			"Poland",
			"Portugal",
			"Puerto Rico",
			"Qatar",
			"Reunion",
			"Romania",
			"Russian Federation",
			"Rwanda",
			"Saint Kitts and Nevis",
			"Saint Lucia",
			"Saint Vincent and the Grenadines",
			"Samoa",
			"San Marino",
			"Sao Tome and Principe",
			"Saudi Arabia",
			"Senegal",
			"Seychelles",
			"Sierra Leone",
			"Singapore",
			"Slovakia (Slovak Republic)",
			"Slovenia",
			"Solomon Islands",
			"Somalia",
			"South Africa",
			"South Georgia and the South Sandwich Islands",
			"Spain",
			"Sri Lanka",
			"St. Helena",
			"St. Pierre and Miquelon",
			"Sudan",
			"Suriname",
			"Svalbard and Jan Mayen Islands",
			"Swaziland",
			"Sweden",
			"Switzerland",
			"Syrian Arab Republic",
			"Taiwan, Province of China",
			"Tajikistan",
			"Tanzania, United Republic of",
			"Thailand",
			"Togo",
			"Tokelau",
			"Tonga",
			"Trinidad and Tobago",
			"Tunisia",
			"Turkey",
			"Turkmenistan",
			"Turks and Caicos Islands",
			"Tuvalu",
			"Uganda",
			"Ukraine",
			"United Arab Emirates",
			"United Kingdom",
			"United States",
			"United States Minor Outlying Islands",
			"Uruguay",
			"Uzbekistan",
			"Vanuatu",
			"Venezuela",
			"Vietnam",
			"Virgin Islands (British)",
			"Virgin Islands (U.S.)",
			"Wallis and Futuna Islands",
			"Western Sahara",
			"Yemen",
			"Yugoslavia",
			"Zambia",
			"Zimbabwe"
		);
		$arr = array(
			'' => esc_html__('Please Select a Country', 'tour-booking-manager')
		);
		foreach ($countries as $_terms) {
			$arr[$_terms] = $_terms;
		}
		return $arr;
	}

