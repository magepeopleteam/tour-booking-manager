<?php
	/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Global_Function')) {
		class TTBM_Global_Function {
			public function __construct() {
				add_action('ttbm_load_date_picker_js', [$this, 'date_picker_js'], 10, 2);
			}
			public static function esc_html( $string ): string {
				$allow_attr = array(
					'input'    => [
						'type'               => [],
						'class'              => [],
						'id'                 => [],
						'name'               => [],
						'value'              => [],
						'size'               => [],
						'placeholder'        => [],
						'min'                => [],
						'max'                => [],
						'checked'            => [],
						'required'           => [],
						'disabled'           => [],
						'readonly'           => [],
						'step'               => [],
						'data-default-color' => [],
						'data-price'         => [],
					],
					'p'        => [ 'class' => [] ],
					'img'      => [ 'class' => [], 'id' => [], 'src' => [], 'alt' => [], ],
					'fieldset' => [
						'class' => []
					],
					'label'    => [
						'for'   => [],
						'class' => []
					],
					'select'   => [
						'class'      => [],
						'name'       => [],
						'id'         => [],
						'data-price' => [],
					],
					'option'   => [
						'class'    => [],
						'value'    => [],
						'id'       => [],
						'selected' => [],
					],
					'textarea' => [
						'class' => [],
						'rows'  => [],
						'id'    => [],
						'cols'  => [],
						'name'  => [],
					],
					'h1'       => [ 'class' => [], 'id' => [], ],
					'h2'       => [ 'class' => [], 'id' => [], ],
					'h3'       => [ 'class' => [], 'id' => [], ],
					'h4'       => [ 'class' => [], 'id' => [], ],
					'h5'       => [ 'class' => [], 'id' => [], ],
					'h6'       => [ 'class' => [], 'id' => [], ],
					'a'        => [ 'class' => [], 'id' => [], 'href' => [], ],
					'div'      => [
						'class'                 => [],
						'id'                    => [],
						'data-ticket-type-name' => [],
					],
					'span'     => [
						'class'             => [],
						'id'                => [],
						'data'              => [],
						'data-input-change' => [],
					],
					'i'        => [
						'class' => [],
						'id'    => [],
						'data'  => [],
					],
					'table'    => [
						'class' => [],
						'id'    => [],
						'data'  => [],
					],
					'tr'       => [
						'class' => [],
						'id'    => [],
						'data'  => [],
					],
					'td'       => [
						'class' => [],
						'id'    => [],
						'data'  => [],
					],
					'thead'    => [
						'class' => [],
						'id'    => [],
						'data'  => [],
					],
					'tbody'    => [
						'class' => [],
						'id'    => [],
						'data'  => [],
					],
					'th'       => [
						'class' => [],
						'id'    => [],
						'data'  => [],
					],
					'svg'      => [
						'class'   => [],
						'id'      => [],
						'width'   => [],
						'height'  => [],
						'viewBox' => [],
						'xmlns'   => [],
					],
					'g'        => [
						'fill' => [],
					],
					'path'     => [
						'd' => [],
					],
					'br'       => array(),
					'em'       => array(),
					'strong'   => array(),
				);

				return wp_kses( $string, $allow_attr );
			}
			public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => 'publish'
				);
				return new WP_Query($args);
			}
			public static function get_all_post_id($post_type, $show = -1, $page = 1, $status = 'publish'): array {
				$all_data = get_posts(array(
					'fields' => 'ids',
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => $status
				));
				return array_unique($all_data);
			}
			public static function get_post_info($post_id, $key, $default = '') {
				$data = get_post_meta($post_id, $key, true) ?: $default;
				return self::data_sanitize($data);
			}
			//***********************************//
			public static function get_taxonomy($name) {
				return get_terms(array('taxonomy' => $name, 'hide_empty' => false));
			}
			public static function get_term_meta($meta_id, $meta_key, $default = '') {
				$data = get_term_meta($meta_id, $meta_key, true) ?: $default;
				return self::data_sanitize($data);
			}
			public static function get_all_term_data($term_name, $value = 'name') {
				$all_data = [];
				$taxonomies = self::get_taxonomy($term_name);
				if ($taxonomies && is_array($taxonomies) && sizeof($taxonomies) > 0) {
					foreach ($taxonomies as $taxonomy) {
						$all_data[] = $taxonomy->$value;
					}
				}
				return $all_data;
			}
			//***********************************//
			public static function get_submit_info($key, $default = '') {
				return self::data_sanitize($_POST[$key] ?? $default);
			}
			public static function data_sanitize($array) {
				if (is_serialized($array)) {
					$array = unserialize($array);
				}
				if (is_string($array) && is_array(json_decode($array, true))) {
					$array = json_decode($array, true);
				}
				if (!is_array($array)) {
					return sanitize_text_field($array);
				}
				foreach ($array as $key => $value) {
					if (is_array($value)) {
						$array[$key] = self::data_sanitize($value);
					} else {
						if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
							$array[$key] = sanitize_email($value);
						} elseif (filter_var($value, FILTER_VALIDATE_URL)) {
							$array[$key] = esc_url_raw($value);
						} else {
							$array[$key] = sanitize_text_field($value);
						}
					}
				}
				return $array;
			}
			//**************Date related*********************//
			public static function date_picker_format_without_year($key = 'date_format'): string {
				$format = TTBM_Global_Function::get_settings('ttbm_global_settings', $key, 'D d M , yy');
				$date_format = 'm-d';
				$date_format = $format == 'yy/mm/dd' ? 'm/d' : $date_format;
				$date_format = $format == 'yy-dd-mm' ? 'd-m' : $date_format;
				$date_format = $format == 'yy/dd/mm' ? 'd/m' : $date_format;
				$date_format = $format == 'dd-mm-yy' ? 'd-m' : $date_format;
				$date_format = $format == 'dd/mm/yy' ? 'd/m' : $date_format;
				$date_format = $format == 'mm-dd-yy' ? 'm-d' : $date_format;
				$date_format = $format == 'mm/dd/yy' ? 'm/d' : $date_format;
				$date_format = $format == 'd M , yy' ? 'j M' : $date_format;
				$date_format = $format == 'D d M , yy' ? 'D j M' : $date_format;
				$date_format = $format == 'M d , yy' ? 'M  j' : $date_format;
				return $format == 'D M d , yy' ? 'D M  j' : $date_format;
			}
			public static function date_picker_format($key = 'date_format'): string {
				$format = TTBM_Global_Function::get_settings('ttbm_global_settings', $key, 'D d M , yy');
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
			public function date_picker_js($selector, $dates) {
				//echo '<pre>';print_r($dates);echo '</pre>';
				$start_date = $dates[0];
				$start_year = gmdate('Y', strtotime($start_date));
				$start_month = (gmdate('n', strtotime($start_date)) - 1);
				$start_day = gmdate('j', strtotime($start_date));
				$end_date = end($dates);
				$end_year = gmdate('Y', strtotime($end_date));
				$end_month = (gmdate('n', strtotime($end_date)) - 1);
				$end_day = gmdate('j', strtotime($end_date));
				$all_date = [];
				foreach ($dates as $date) {
					$all_date[] = '"' . gmdate('j-n-Y', strtotime($date)) . '"';
				}
				?>
                <script>
                    jQuery(document).ready(function () {
                        jQuery("<?php echo esc_attr($selector); ?>").datepicker({
                            dateFormat: ttbm_date_format,
                            minDate: new Date(<?php echo esc_attr($start_year); ?>, <?php echo esc_attr($start_month); ?>,  <?php echo esc_attr($start_day); ?>),
                            maxDate: new Date(<?php echo esc_attr($end_year); ?>, <?php echo esc_attr($end_month); ?>, <?php echo esc_attr($end_day); ?>),
                            autoSize: true,
                            changeMonth: true,
                            changeYear: true,
                            beforeShowDay: WorkingDates,
                            onSelect: function (dateString, data) {
                                let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                            }
                        });
                        function WorkingDates(date) {
                            let availableDates = [<?php echo implode( ',', $all_date ); ?>];
                            let dmy = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear();
                            if (jQuery.inArray(dmy, availableDates) !== -1) {
                                return [true, "", "Available"];
                            } else {
                                return [false, "", "unAvailable"];
                            }
                        }
                    });
                </script>
				<?php
			}
			public static function date_format($date, $format = 'date') {
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');
				$wp_settings = $date_format . '  ' . $time_format;
				//$timezone = wp_timezone_string();
				$timestamp = strtotime($date);
				if ($format == 'date') {
					$date = date_i18n($date_format, $timestamp);
				} elseif ($format == 'time') {
					$date = date_i18n($time_format, $timestamp);
				} elseif ($format == 'full') {
					$date = date_i18n($wp_settings, $timestamp);
				} elseif ($format == 'day') {
					$date = date_i18n('d', $timestamp);
				} elseif ($format == 'month') {
					$date = date_i18n('M', $timestamp);
				} elseif ($format == 'year') {
					$date = date_i18n('Y', $timestamp);
				} else {
					$date = date_i18n($format, $timestamp);
				}
				return $date;
			}
			public static function date_separate_period($start_date, $end_date, $repeat = 1): DatePeriod {
				$repeat = max($repeat, 1);
				$_interval = "P" . $repeat . "D";
				$end_date = gmdate('Y-m-d', strtotime($end_date . ' +1 day'));
				return new DatePeriod(new DateTime($start_date), new DateInterval($_interval), new DateTime($end_date));
			}
			public static function check_time_exit_date($date) {
				if ($date) {
					$parse_date = date_parse($date);
					if (($parse_date['hour'] && $parse_date['hour'] > 0) || ($parse_date['minute'] && $parse_date['minute'] > 0) || ($parse_date['second'] && $parse_date['second'] > 0)) {
						return true;
					}
				}
				return false;
			}
			public static function check_licensee_date($date) {
				if ($date) {
					if ($date == 'lifetime') {
						return esc_html__('Lifetime', 'tour-booking-manager');
					} else if (strtotime(current_time('Y-m-d H:i')) < strtotime(gmdate('Y-m-d H:i', strtotime($date)))) {
						return TTBM_Global_Function::date_format($date, 'full');
					} else {
						return esc_html__('Expired', 'tour-booking-manager');
					}
				}
				return $date;
			}
			public static function sort_date($a, $b) {
				return strtotime($a) - strtotime($b);
			}
			public static function sort_date_array($a, $b) {
				$dateA = strtotime($a['time']);
				$dateB = strtotime($b['time']);
				if ($dateA == $dateB) {
					return 0;
				} elseif ($dateA > $dateB) {
					return 1;
				} else {
					return -1;
				}
			}
			public static function date_difference($startdate, $enddate) {
				$starttimestamp = strtotime($startdate);
				$endtimestamp = strtotime($enddate);
				$difference = abs($endtimestamp - $starttimestamp) / 3600;
				//return $difference;
				$datetime1 = new DateTime($startdate);
				$datetime2 = new DateTime($enddate);
				$interval = $datetime1->diff($datetime2);
				return $interval->format('%h') . "H " . $interval->format('%i') . "M";
			}
			//***********************************//
			public static function get_settings($section, $key, $default = '') {
				$options = get_option($section);
				if (isset($options[$key])) {
					if (is_array($options[$key])) {
						if (!empty($options[$key])) {
							return $options[$key];
						} else {
							return $default;
						}
					} else {
						if (!empty($options[$key])) {
							return wp_kses_post($options[$key]);
						} else {
							return $default;
						}
					}
				}
				if (is_array($default)) {
					return $default;
				} else {
					return wp_kses_post($default);
				}
			}
			public static function get_style_settings($key, $default = '') {
				return self::get_settings('ttbm_style_settings', $key, $default);
			}
			public static function get_slider_settings($key, $default = '') {
				return self::get_settings('ttbm_slider_settings', $key, $default);
			}
			public static function get_licence_settings($key, $default = '') {
				return self::get_settings('ttbm_license_settings', $key, $default);
			}
			//***********************************//
			public static function price_convert_raw($price) {
				$price = wp_strip_all_tags($price);
				$price = str_replace(get_woocommerce_currency_symbol(), '', $price);
				$price = str_replace(wc_get_price_thousand_separator(), 't_s', $price);
				$price = str_replace(wc_get_price_decimal_separator(), 'd_s', $price);
				$price = str_replace('t_s', '', $price);
				$price = str_replace('d_s', '.', $price);
				$price = str_replace('&nbsp;', '', $price);
				return max($price, 0);
			}
			public static function wc_price($post_id, $price, $args = array()): string {
				$num_of_decimal = get_option('woocommerce_price_num_decimals', 2);
				$args = wp_parse_args($args, array(
					'qty' => '',
					'price' => '',
				));
				$_product = self::get_post_info($post_id, 'link_wc_product', $post_id);
				$product = wc_get_product($_product);
				$qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;
				$tax_with_price = get_option('woocommerce_tax_display_shop');
				if ('' === $price) {
					return '';
				} elseif (empty($qty)) {
					return 0.0;
				}
				$line_price = (float)$price * (int)$qty;
				$return_price = $line_price;
				if ($product && $product->is_taxable()) {
					if (!wc_prices_include_tax()) {
						$tax_rates = WC_Tax::get_rates($product->get_tax_class());
						$taxes = WC_Tax::calc_tax($line_price, $tax_rates);
						if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
							$taxes_total = array_sum($taxes);
						} else {
							$taxes_total = array_sum(array_map('wc_round_tax_total', $taxes));
						}
						$return_price = $tax_with_price == 'excl' ? round($line_price, $num_of_decimal) : round($line_price + $taxes_total, $num_of_decimal);
					} else {
						$tax_rates = WC_Tax::get_rates($product->get_tax_class());
						$base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
						if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) { // @codingStandardsIgnoreLine.
							$remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);
							if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
								$remove_taxes_total = array_sum($remove_taxes);
							} else {
								$remove_taxes_total = array_sum(array_map('wc_round_tax_total', $remove_taxes));
							}
							// $return_price = round( $line_price, $num_of_decimal);
							$return_price = round($line_price - $remove_taxes_total, $num_of_decimal);
						} else {
							$base_taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true);
							$modded_taxes = WC_Tax::calc_tax($line_price - array_sum($base_taxes), $tax_rates);
							if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
								$base_taxes_total = array_sum($base_taxes);
								$modded_taxes_total = array_sum($modded_taxes);
							} else {
								$base_taxes_total = array_sum(array_map('wc_round_tax_total', $base_taxes));
								$modded_taxes_total = array_sum(array_map('wc_round_tax_total', $modded_taxes));
							}
							$return_price = $tax_with_price == 'excl' ? round($line_price - $base_taxes_total, $num_of_decimal) : round($line_price - $base_taxes_total + $modded_taxes_total, $num_of_decimal);
						}
					}
				}
				$return_price = apply_filters('woocommerce_get_price_including_tax', $return_price, $qty, $product);
				$display_suffix = get_option('woocommerce_price_display_suffix') ? get_option('woocommerce_price_display_suffix') : '';
				return wc_price($return_price) . ' ' . $display_suffix;
			}
			public static function get_wc_raw_price($post_id, $price, $args = array()) {
				$price = self::wc_price($post_id, $price, $args = array());
				return self::price_convert_raw($price);
			}
			//***********************************//
			public static function get_image_url($post_id = '', $image_id = '', $size = 'full') {
				if ($post_id) {
					$image_id = get_post_thumbnail_id($post_id);
					$image_id = $image_id ?: self::get_post_info($post_id, 'mp_thumbnail');
				}
				return wp_get_attachment_image_url($image_id, $size);
			}
			public static function get_page_by_slug($slug) {
				if ($pages = get_pages()) {
					foreach ($pages as $page) {
						if ($slug === $page->post_name) {
							return $page;
						}
					}
				}
				return false;
			}
			//***********************************//
			public static function check_plugin($plugin_dir_name, $plugin_file): int {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				$plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
				if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
					return 1;
				} elseif (is_dir($plugin_dir)) {
					return 2;
				} else {
					return 0;
				}
			}
			public static function check_woocommerce(): int {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
				if (is_plugin_active('woocommerce/woocommerce.php')) {
					return 1;
				} elseif (is_dir($plugin_dir)) {
					return 2;
				} else {
					return 0;
				}
			}
			public static function get_order_item_meta( $item_id, $key ): string {
				global $wpdb;
				$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
				$results    = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM $table_name WHERE order_item_id = %d AND meta_key = %s", $item_id, $key ) );
				foreach ( $results as $result ) {
					$value = $result->meta_value;
				}

				return $value ?? '';
			}
			public static function wc_product_sku($product_id) {
				if ($product_id) {
					return new WC_Product($product_id);
				}
				return null;
			}
			//***********************************//
			public static function all_tax_list(): array {
				$cache_key = 'wc_all_tax_classes';
				$cache_group = 'tax_classes';

				// Try to get cached value first
				$cached = wp_cache_get($cache_key, $cache_group);
				if (false !== $cached) {
					return $cached;
				}

				// Use WooCommerce API functions instead of direct DB query
				$tax_classes = WC_Tax::get_tax_classes();
				$tax_list = [];

				// Standard tax classes that aren't returned by get_tax_classes()
				$standard_classes = [
					'standard' => __('Standard rate', 'tour-booking-manager')
				];

				// Format the tax classes array
				foreach ($tax_classes as $tax_class) {
					$slug = sanitize_title($tax_class);
					$tax_list[$slug] = $tax_class;
				}

				// Merge with standard classes
				$tax_list = array_merge($standard_classes, $tax_list);

				// Cache the results for 24 hours
				wp_cache_set($cache_key, $tax_list, $cache_group, DAY_IN_SECONDS);

				return $tax_list;
			}
			public static function array_to_string($array) {
				$ids = '';
				if (sizeof($array) > 0) {
					foreach ($array as $data) {
						if ($data) {
							$ids = $ids ? $ids . ',' . $data : $data;
						}
					}
				}
				return $ids;
			}
			//***********************************//
			public static function week_day(): array {
				return [
					'mon' => esc_html__( 'Monday', 'mage-eventpress' ),
					'tue' => esc_html__( 'Tuesday', 'mage-eventpress' ),
					'wed' => esc_html__( 'Wednesday', 'mage-eventpress' ),
					'thu' => esc_html__( 'Thursday', 'mage-eventpress' ),
					'fri' => esc_html__( 'Friday', 'mage-eventpress' ),
					'sat' => esc_html__( 'Saturday', 'mage-eventpress' ),
					'sun' => esc_html__( 'Sunday', 'mage-eventpress' ),
				];
			}
			//***********************************//
			public static function license_error_text($response, $license_data, $plugin_name) {
				if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
					$message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : esc_html__('An error occurred, please try again.', 'tour-booking-manager');
				} else {
					if (false === $license_data->success) {
						switch ($license_data->error) {
							case 'expired':
								$message = esc_html__('Your license key expired on ', 'tour-booking-manager') . ' ' . date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')));
								break;
							case 'revoked':
								$message = esc_html__('Your license key has been disabled.', 'tour-booking-manager');
								break;
							case 'missing':
								$message = esc_html__('Missing license.', 'tour-booking-manager');
								break;
							case 'invalid':
								$message = esc_html__('Invalid license.', 'tour-booking-manager');
								break;
							case 'site_inactive':
								$message = esc_html__('Your license is not active for this URL.', 'tour-booking-manager');
								break;
							case 'item_name_mismatch':
								$message = esc_html__('This appears to be an invalid license key for .', 'tour-booking-manager') . ' ' . $plugin_name;
								break;
							case 'no_activations_left':
								$message = esc_html__('Your license key has reached its activation limit.', 'tour-booking-manager');
								break;
							default:
								$message = esc_html__('An error occurred, please try again.', 'tour-booking-manager');
								break;
						}
					} else {
						$payment_id = $license_data->payment_id;
						$expire = $license_data->expires;
						$message = esc_html__('Success, License Key is valid for the plugin', 'tour-booking-manager') . ' ' . $plugin_name . ' ' . esc_html__('Your Order id is', 'tour-booking-manager') . ' ' . $payment_id . ' ' . $plugin_name . ' ' . esc_html__('Validity of this licenses is', 'tour-booking-manager') . ' ' . TTBM_Global_Function::check_licensee_date($expire);
					}
				}
				return $message;
			}
			// ================== Get languages =============
			public static function get_languages() {
				$languages = array(
					'en_US' => 'English',
					'es_ES' => 'Spanish',
					'fr_FR' => 'French',
					'de_DE' => 'German',
					'it_IT' => 'Italian',
					'pt_PT' => 'Portuguese',
					'ru_RU' => 'Russian',
					'zh_CN' => 'Chinese',
					'ja_JP' => 'Japanese',
					'ko_KR' => 'Korean',
					'ar_SA' => 'Arabic',
					'hi_IN' => 'Hindi',
					'bn_BD' => 'Bengali',
					'tr_TR' => 'Turkish',
					'nl_NL' => 'Dutch',
					'sv_SE' => 'Swedish',
					'pl_PL' => 'Polish',
					'da_DK' => 'Danish',
					'fi_FI' => 'Finnish',
					'no_NO' => 'Norwegian',
					'cs_CZ' => 'Czech',
					'el_GR' => 'Greek',
					'hu_HU' => 'Hungarian',
					'th_TH' => 'Thai',
					'vi_VN' => 'Vietnamese'
				);
				return $languages;
			}
			//***********************************//
			public static function pa_get_full_room_ticket_info($hotel_id, $check_in, $check_out) {
				$room_details = get_post_meta($hotel_id, 'ttbm_room_details', true); // Serialized room details
				$bookings = get_post_meta($hotel_id, 'ttbm_hotel_bookings', true);
				if (!is_array($bookings))
					$bookings = [];
				$ticket_info = [];
				if (is_array($room_details)) {
					foreach ($room_details as $room) {
						$room_name_raw = $room['ttbm_hotel_room_name'];
						$room_name = str_replace(' ', '', $room_name_raw); // Remove spaces
						$total_rooms = isset($room['ttbm_hotel_room_qty']) ? (int)$room['ttbm_hotel_room_qty'] : 0;
						$booked = 0;
						foreach ($bookings as $booking) {
							if (
								$booking['room_type'] === $room_name &&
								$booking['check_out'] > $check_in &&
								$booking['check_in'] < $check_out
							) {
								$booked += (int)$booking['rooms_booked'];
							}
						}
						$room['room_type_key'] = $room_name; // Optional: add normalized key
						$room['booked'] = $booked;
						$room['available'] = max(0, $total_rooms - $booked);
						$ticket_info[] = $room;
					}
				}
				return $ticket_info;
			}
			public static function pa_add_multiple_room_type_booking($hotel_id, $booking_request, $check_in, $check_out) {
				$bookings = get_post_meta($hotel_id, 'ttbm_hotel_bookings', true);
				if (!is_array($bookings))
					$bookings = [];
				foreach ($booking_request as $room_type => $rooms_booked) {
					$room_type_normalized = str_replace(' ', '', $room_type);
					$bookings[] = [
						'room_type' => $room_type_normalized,
						'check_in' => $check_in,
						'check_out' => $check_out,
						'rooms_booked' => $rooms_booked
					];
				}
				update_post_meta($hotel_id, 'ttbm_hotel_bookings', $bookings);
			}
		}
		new TTBM_Global_Function();
	}
	$active_plugins = get_option('active_plugins');
	if (
		(
			(in_array('tour-booking-manager-pro/tour-booking-manager-pro.php', $active_plugins) && get_option('ttbm_conflict_update_pro') != 'completed') ||
			(in_array('ttbm-addon-group-pricing/TTBMA_Group_Pricing.php', $active_plugins) && get_option('ttbm_conflict_update_gp') != 'completed') ||
			(in_array('ttbm-addon-group-ticket/ttbm-addon-group-ticket.php', $active_plugins) && get_option('ttbm_conflict_update_gt') != 'completed') ||
			(in_array('ttbm-addon-backend-order/TTBM_Addon_Backend_Order.php', $active_plugins) && get_option('ttbm_conflict_update_bo') != 'completed') ||
			(in_array('ttbm-addon-early-bird/TTBMA_Early_Bird.php', $active_plugins) && get_option('ttbm_conflict_update_eb') != 'completed') ||
			(in_array('ttbm-addon-order-request/TTBMA_Order_Request.php', $active_plugins) && get_option('ttbm_conflict_update_or') != 'completed') ||
			(in_array('ttbm-addon-seasonal-price/TTBMA_Seasonal_Pricing.php', $active_plugins) && get_option('ttbm_conflict_update_sep') != 'completed') ||
			(in_array('ttbm-addon-qr-code/qr_code.php', $active_plugins) && get_option('ttbm_conflict_update_qr') != 'completed') ||
			(in_array('ttbm-addon-seat-plan/TTBMA_Seat_Plan.php', $active_plugins) && get_option('ttbm_conflict_update_sp') != 'completed')
		)
		&&
		!class_exists('MP_Global_Function')
	) {
		class MP_Global_Function {
			public function __construct() {
				add_action('mp_load_date_picker_js', [$this, 'date_picker_js'], 10, 2);
			}
			public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => 'publish'
				);
				return new WP_Query($args);
			}
			public static function get_all_post_id($post_type, $show = -1, $page = 1, $status = 'publish'): array {
				$all_data = get_posts(array(
					'fields' => 'ids',
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => $status
				));
				return array_unique($all_data);
			}
			public static function get_post_info($post_id, $key, $default = '') {
				$data = get_post_meta($post_id, $key, true) ?: $default;
				return self::data_sanitize($data);
			}
			//***********************************//
			public static function get_taxonomy($name) {
				return get_terms(array('taxonomy' => $name, 'hide_empty' => false));
			}
			public static function get_term_meta($meta_id, $meta_key, $default = '') {
				$data = get_term_meta($meta_id, $meta_key, true) ?: $default;
				return self::data_sanitize($data);
			}
			public static function get_all_term_data($term_name, $value = 'name') {
				$all_data = [];
				$taxonomies = self::get_taxonomy($term_name);
				if ($taxonomies && is_array($taxonomies) && sizeof($taxonomies) > 0) {
					foreach ($taxonomies as $taxonomy) {
						$all_data[] = $taxonomy->$value;
					}
				}
				return $all_data;
			}
			//***********************************//
			public static function data_sanitize($array) {
				if (is_serialized($array)) {
					$array = unserialize($array);
				}
				if (is_string($array) && is_array(json_decode($array, true))) {
					$array = json_decode($array, true);
				}
				if (!is_array($array)) {
					return sanitize_text_field($array);
				}
				foreach ($array as $key => $value) {
					if (is_array($value)) {
						$array[$key] = self::data_sanitize($value);
					} else {
						if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
							$array[$key] = sanitize_email($value);
						} elseif (filter_var($value, FILTER_VALIDATE_URL)) {
							$array[$key] = esc_url_raw($value);
						} else {
							$array[$key] = sanitize_text_field($value);
						}
					}
				}
				return $array;
			}
			//**************Date related*********************//
			public static function date_picker_format_without_year($key = 'date_format'): string {
				$format = TTBM_Global_Function::get_settings('ttbm_global_settings', $key, 'D d M , yy');
				$date_format = 'm-d';
				$date_format = $format == 'yy/mm/dd' ? 'm/d' : $date_format;
				$date_format = $format == 'yy-dd-mm' ? 'd-m' : $date_format;
				$date_format = $format == 'yy/dd/mm' ? 'd/m' : $date_format;
				$date_format = $format == 'dd-mm-yy' ? 'd-m' : $date_format;
				$date_format = $format == 'dd/mm/yy' ? 'd/m' : $date_format;
				$date_format = $format == 'mm-dd-yy' ? 'm-d' : $date_format;
				$date_format = $format == 'mm/dd/yy' ? 'm/d' : $date_format;
				$date_format = $format == 'd M , yy' ? 'j M' : $date_format;
				$date_format = $format == 'D d M , yy' ? 'D j M' : $date_format;
				$date_format = $format == 'M d , yy' ? 'M  j' : $date_format;
				return $format == 'D M d , yy' ? 'D M  j' : $date_format;
			}
			public static function date_picker_format($key = 'date_format'): string {
				$format = TTBM_Global_Function::get_settings('ttbm_global_settings', $key, 'D d M , yy');
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
			public static function date_format($date, $format = 'date') {
				$date_format = get_option('date_format');
				$time_format = get_option('time_format');
				$wp_settings = $date_format . '  ' . $time_format;
				//$timezone = wp_timezone_string();
				$timestamp = strtotime($date);
				if ($format == 'date') {
					$date = date_i18n($date_format, $timestamp);
				} elseif ($format == 'time') {
					$date = date_i18n($time_format, $timestamp);
				} elseif ($format == 'full') {
					$date = date_i18n($wp_settings, $timestamp);
				} elseif ($format == 'day') {
					$date = date_i18n('d', $timestamp);
				} elseif ($format == 'month') {
					$date = date_i18n('M', $timestamp);
				} elseif ($format == 'year') {
					$date = date_i18n('Y', $timestamp);
				} else {
					$date = date_i18n($format, $timestamp);
				}
				return $date;
			}
			public static function date_separate_period($start_date, $end_date, $repeat = 1): DatePeriod {
				$repeat = max($repeat, 1);
				$_interval = "P" . $repeat . "D";
				$end_date = gmdate('Y-m-d', strtotime($end_date . ' +1 day'));
				return new DatePeriod(new DateTime($start_date), new DateInterval($_interval), new DateTime($end_date));
			}
			public static function check_time_exit_date($date) {
				if ($date) {
					$parse_date = date_parse($date);
					if (($parse_date['hour'] && $parse_date['hour'] > 0) || ($parse_date['minute'] && $parse_date['minute'] > 0) || ($parse_date['second'] && $parse_date['second'] > 0)) {
						return true;
					}
				}
				return false;
			}
			public static function check_licensee_date($date) {
				if ($date) {
					if ($date == 'lifetime') {
						return esc_html__('Lifetime', 'tour-booking-manager');
					} else if (strtotime(current_time('Y-m-d H:i')) < strtotime(gmdate('Y-m-d H:i', strtotime($date)))) {
						return TTBM_Global_Function::date_format($date, 'full');
					} else {
						return esc_html__('Expired', 'tour-booking-manager');
					}
				}
				return $date;
			}
			public static function sort_date($a, $b) {
				return strtotime($a) - strtotime($b);
			}
			public static function sort_date_array($a, $b) {
				$dateA = strtotime($a['time']);
				$dateB = strtotime($b['time']);
				if ($dateA == $dateB) {
					return 0;
				} elseif ($dateA > $dateB) {
					return 1;
				} else {
					return -1;
				}
			}
			//***********************************//
			public static function get_settings($section, $key, $default = '') {
				$options = get_option($section);
				if (isset($options[$key])) {
					if (is_array($options[$key])) {
						if (!empty($options[$key])) {
							return $options[$key];
						} else {
							return $default;
						}
					} else {
						if (!empty($options[$key])) {
							return wp_kses_post($options[$key]);
						} else {
							return $default;
						}
					}
				}
				if (is_array($default)) {
					return $default;
				} else {
					return wp_kses_post($default);
				}
			}
			public static function get_style_settings($key, $default = '') {
				return self::get_settings('ttbm_style_settings', $key, $default);
			}
			public static function get_slider_settings($key, $default = '') {
				return self::get_settings('ttbm_slider_settings', $key, $default);
			}
			public static function get_licence_settings($key, $default = '') {
				return self::get_settings('ttbm_license_settings', $key, $default);
			}
			//***********************************//
			public static function price_convert_raw($price) {
				$price = wp_strip_all_tags($price);
				$price = str_replace(get_woocommerce_currency_symbol(), '', $price);
				$price = str_replace(wc_get_price_thousand_separator(), 't_s', $price);
				$price = str_replace(wc_get_price_decimal_separator(), 'd_s', $price);
				$price = str_replace('t_s', '', $price);
				$price = str_replace('d_s', '.', $price);
				$price = str_replace('&nbsp;', '', $price);
				return max($price, 0);
			}
			public static function wc_price($post_id, $price, $args = array()): string {
				$num_of_decimal = get_option('woocommerce_price_num_decimals', 2);
				$args = wp_parse_args($args, array(
					'qty' => '',
					'price' => '',
				));
				$_product = self::get_post_info($post_id, 'link_wc_product', $post_id);
				$product = wc_get_product($_product);
				$qty = '' !== $args['qty'] ? max(0.0, (float)$args['qty']) : 1;
				$tax_with_price = get_option('woocommerce_tax_display_shop');
				if ('' === $price) {
					return '';
				} elseif (empty($qty)) {
					return 0.0;
				}
				$line_price = (float)$price * (int)$qty;
				$return_price = $line_price;
				if ($product && $product->is_taxable()) {
					if (!wc_prices_include_tax()) {
						$tax_rates = WC_Tax::get_rates($product->get_tax_class());
						$taxes = WC_Tax::calc_tax($line_price, $tax_rates);
						if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
							$taxes_total = array_sum($taxes);
						} else {
							$taxes_total = array_sum(array_map('wc_round_tax_total', $taxes));
						}
						$return_price = $tax_with_price == 'excl' ? round($line_price, $num_of_decimal) : round($line_price + $taxes_total, $num_of_decimal);
					} else {
						$tax_rates = WC_Tax::get_rates($product->get_tax_class());
						$base_tax_rates = WC_Tax::get_base_tax_rates($product->get_tax_class('unfiltered'));
						if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) { // @codingStandardsIgnoreLine.
							$remove_taxes = apply_filters('woocommerce_adjust_non_base_location_prices', true) ? WC_Tax::calc_tax($line_price, $base_tax_rates, true) : WC_Tax::calc_tax($line_price, $tax_rates, true);
							if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
								$remove_taxes_total = array_sum($remove_taxes);
							} else {
								$remove_taxes_total = array_sum(array_map('wc_round_tax_total', $remove_taxes));
							}
							// $return_price = round( $line_price, $num_of_decimal);
							$return_price = round($line_price - $remove_taxes_total, $num_of_decimal);
						} else {
							$base_taxes = WC_Tax::calc_tax($line_price, $base_tax_rates, true);
							$modded_taxes = WC_Tax::calc_tax($line_price - array_sum($base_taxes), $tax_rates);
							if ('yes' === get_option('woocommerce_tax_round_at_subtotal')) {
								$base_taxes_total = array_sum($base_taxes);
								$modded_taxes_total = array_sum($modded_taxes);
							} else {
								$base_taxes_total = array_sum(array_map('wc_round_tax_total', $base_taxes));
								$modded_taxes_total = array_sum(array_map('wc_round_tax_total', $modded_taxes));
							}
							$return_price = $tax_with_price == 'excl' ? round($line_price - $base_taxes_total, $num_of_decimal) : round($line_price - $base_taxes_total + $modded_taxes_total, $num_of_decimal);
						}
					}
				}
				$return_price = apply_filters('woocommerce_get_price_including_tax', $return_price, $qty, $product);
				$display_suffix = get_option('woocommerce_price_display_suffix') ? get_option('woocommerce_price_display_suffix') : '';
				return wc_price($return_price) . ' ' . $display_suffix;
			}
			public static function get_wc_raw_price($post_id, $price, $args = array()) {
				$price = self::wc_price($post_id, $price, $args = array());
				return self::price_convert_raw($price);
			}
			//***********************************//
			public static function get_image_url($post_id = '', $image_id = '', $size = 'full') {
				if ($post_id) {
					$image_id = get_post_thumbnail_id($post_id);
					$image_id = $image_id ?: self::get_post_info($post_id, 'mp_thumbnail');
				}
				return wp_get_attachment_image_url($image_id, $size);
			}
			public static function get_page_by_slug($slug) {
				if ($pages = get_pages()) {
					foreach ($pages as $page) {
						if ($slug === $page->post_name) {
							return $page;
						}
					}
				}
				return false;
			}
			//***********************************//
			public static function check_plugin($plugin_dir_name, $plugin_file): int {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				$plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
				if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
					return 1;
				} elseif (is_dir($plugin_dir)) {
					return 2;
				} else {
					return 0;
				}
			}
			public static function check_woocommerce(): int {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
				if (is_plugin_active('woocommerce/woocommerce.php')) {
					return 1;
				} elseif (is_dir($plugin_dir)) {
					return 2;
				} else {
					return 0;
				}
			}
			public static function get_order_item_meta( $item_id, $key ): string {
				global $wpdb;
				$table_name = $wpdb->prefix . "woocommerce_order_itemmeta";
				$results    = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM $table_name WHERE order_item_id = %d AND meta_key = %s", $item_id, $key ) );
				foreach ( $results as $result ) {
					$value = $result->meta_value;
				}

				return $value ?? '';
			}
			public static function wc_product_sku($product_id) {
				if ($product_id) {
					return new WC_Product($product_id);
				}
				return null;
			}
			//***********************************//
			public static function all_tax_list(): array {
				$cache_key = 'wc_all_tax_classes';
				$cache_group = 'tax_classes';

				// Try to get cached value first
				$cached = wp_cache_get($cache_key, $cache_group);
				if (false !== $cached) {
					return $cached;
				}

				// Use WooCommerce API functions instead of direct DB query
				$tax_classes = WC_Tax::get_tax_classes();
				$tax_list = [];

				// Standard tax classes that aren't returned by get_tax_classes()
				$standard_classes = [
					'standard' => __('Standard rate', 'tour-booking-manager')
				];

				// Format the tax classes array
				foreach ($tax_classes as $tax_class) {
					$slug = sanitize_title($tax_class);
					$tax_list[$slug] = $tax_class;
				}

				// Merge with standard classes
				$tax_list = array_merge($standard_classes, $tax_list);

				// Cache the results for 24 hours
				wp_cache_set($cache_key, $tax_list, $cache_group, DAY_IN_SECONDS);

				return $tax_list;
			}
			public static function week_day(): array {
				return [
					'monday' => esc_html__('Monday', 'tour-booking-manager'),
					'tuesday' => esc_html__('Tuesday', 'tour-booking-manager'),
					'wednesday' => esc_html__('Wednesday', 'tour-booking-manager'),
					'thursday' => esc_html__('Thursday', 'tour-booking-manager'),
					'friday' => esc_html__('Friday', 'tour-booking-manager'),
					'saturday' => esc_html__('Saturday', 'tour-booking-manager'),
					'sunday' => esc_html__('Sunday', 'tour-booking-manager'),
				];
			}
			public static function get_plugin_data($data) {
				$plugin_data = get_plugin_data(__FILE__);
				return $plugin_data[$data];
			}
			public static function array_to_string($array) {
				$ids = '';
				if (sizeof($array) > 0) {
					foreach ($array as $data) {
						if ($data) {
							$ids = $ids ? $ids . ',' . $data : $data;
						}
					}
				}
				return $ids;
			}
			//***********************************//
			public static function license_error_text($response, $license_data, $plugin_name) {
				if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
					$message = (is_wp_error($response) && !empty($response->get_error_message())) ? $response->get_error_message() : esc_html__('An error occurred, please try again.', 'tour-booking-manager');
				} else {
					if (false === $license_data->success) {
						switch ($license_data->error) {
							case 'expired':
								$message = esc_html__('Your license key expired on ', 'tour-booking-manager') . ' ' . date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')));
								break;
							case 'revoked':
								$message = esc_html__('Your license key has been disabled.', 'tour-booking-manager');
								break;
							case 'missing':
								$message = esc_html__('Missing license.', 'tour-booking-manager');
								break;
							case 'invalid':
								$message = esc_html__('Invalid license.', 'tour-booking-manager');
								break;
							case 'site_inactive':
								$message = esc_html__('Your license is not active for this URL.', 'tour-booking-manager');
								break;
							case 'item_name_mismatch':
								$message = esc_html__('This appears to be an invalid license key for .', 'tour-booking-manager') . ' ' . $plugin_name;
								break;
							case 'no_activations_left':
								$message = esc_html__('Your license key has reached its activation limit.', 'tour-booking-manager');
								break;
							default:
								$message = esc_html__('An error occurred, please try again.', 'tour-booking-manager');
								break;
						}
					} else {
						$payment_id = $license_data->payment_id;
						$expire = $license_data->expires;
						$message = esc_html__('Success, License Key is valid for the plugin', 'tour-booking-manager') . ' ' . $plugin_name . ' ' . esc_html__('Your Order id is', 'tour-booking-manager') . ' ' . $payment_id . ' ' . $plugin_name . ' ' . esc_html__('Validity of this licenses is', 'tour-booking-manager') . ' ' . TTBM_Global_Function::check_licensee_date($expire);
					}
				}
				return $message;
			}
			// ================== Get languages =============
			public static function get_languages() {
				$languages = array(
					'en_US' => 'English',
					'es_ES' => 'Spanish',
					'fr_FR' => 'French',
					'de_DE' => 'German',
					'it_IT' => 'Italian',
					'pt_PT' => 'Portuguese',
					'ru_RU' => 'Russian',
					'zh_CN' => 'Chinese',
					'ja_JP' => 'Japanese',
					'ko_KR' => 'Korean',
					'ar_SA' => 'Arabic',
					'hi_IN' => 'Hindi',
					'bn_BD' => 'Bengali',
					'tr_TR' => 'Turkish',
					'nl_NL' => 'Dutch',
					'sv_SE' => 'Swedish',
					'pl_PL' => 'Polish',
					'da_DK' => 'Danish',
					'fi_FI' => 'Finnish',
					'no_NO' => 'Norwegian',
					'cs_CZ' => 'Czech',
					'el_GR' => 'Greek',
					'hu_HU' => 'Hungarian',
					'th_TH' => 'Thai',
					'vi_VN' => 'Vietnamese'
				);
				return $languages;
			}
			//***********************************//
			public static function pa_get_full_room_ticket_info($hotel_id, $check_in, $check_out) {
				$room_details = get_post_meta($hotel_id, 'ttbm_room_details', true); // Serialized room details
				$bookings = get_post_meta($hotel_id, 'ttbm_hotel_bookings', true);
				if (!is_array($bookings))
					$bookings = [];
				$ticket_info = [];
				if (is_array($room_details)) {
					foreach ($room_details as $room) {
						$room_name_raw = $room['ttbm_hotel_room_name'];
						$room_name = str_replace(' ', '', $room_name_raw); // Remove spaces
						$total_rooms = isset($room['ttbm_hotel_room_qty']) ? (int)$room['ttbm_hotel_room_qty'] : 0;
						$booked = 0;
						foreach ($bookings as $booking) {
							if (
								$booking['room_type'] === $room_name &&
								$booking['check_out'] > $check_in &&
								$booking['check_in'] < $check_out
							) {
								$booked += (int)$booking['rooms_booked'];
							}
						}
						$room['room_type_key'] = $room_name; // Optional: add normalized key
						$room['booked'] = $booked;
						$room['available'] = max(0, $total_rooms - $booked);
						$ticket_info[] = $room;
					}
				}
				return $ticket_info;
			}
			public static function pa_add_multiple_room_type_booking($hotel_id, $booking_request, $check_in, $check_out) {
				$bookings = get_post_meta($hotel_id, 'ttbm_hotel_bookings', true);
				if (!is_array($bookings))
					$bookings = [];
				foreach ($booking_request as $room_type => $rooms_booked) {
					$room_type_normalized = str_replace(' ', '', $room_type);
					$bookings[] = [
						'room_type' => $room_type_normalized,
						'check_in' => $check_in,
						'check_out' => $check_out,
						'rooms_booked' => $rooms_booked
					];
				}
				update_post_meta($hotel_id, 'ttbm_hotel_bookings', $bookings);
			}
		}
		new MP_Global_Function();
	}