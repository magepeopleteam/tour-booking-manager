<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Hotel_Booking')) {
		class TTBM_Hotel_Booking {
			/**
			 * The hidden hotel product is intentionally priced at 0 and its real
			 * price is applied by the booking handler. It must only be added from
			 * that handler, after the room request has been validated.
			 *
			 * @var int
			 */
			private static $allow_linked_product_add = 0;
			public function __construct() {
				add_action('wp_ajax_ttbm_get_hotel_room_list', array($this, 'ttbm_get_hotel_room_list'));
				add_action('wp_ajax_nopriv_ttbm_get_hotel_room_list', array($this, 'ttbm_get_hotel_room_list'));
				add_action('wp_ajax_ttbm_hotel_room_booking', array($this, 'ttbm_hotel_room_booking'));
				add_action('wp_ajax_nopriv_ttbm_hotel_room_booking', array($this, 'ttbm_hotel_room_booking'));
				add_action('ttbm_hotel_booking_panel', array($this, 'hotel_booking_panel'), 10, 4);
				add_filter('woocommerce_add_cart_item_data', [$this, 'set_custom_price_cart_item'], 10, 2);
				add_action('woocommerce_before_calculate_totals', [$this, 'update_cart_item_price'], 10, 1);
				add_filter('woocommerce_get_item_data', [$this, 'display_custom_cart_item_data'], 10, 2);
				add_action('woocommerce_order_item_meta_end', [$this, 'display_order_meta'], 10, 3);
				add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);
				add_action('woocommerce_order_status_changed', [$this, 'custom_function_on_order_status_change'], 10, 4);
				// filter_is_purchasable() is a no-op before WooCommerce 7.0, which
				// lacks the per-cart-item override used to keep valid hotel lines;
				// those versions still rely on add-to-cart and checkout validation.
				add_filter('woocommerce_is_purchasable', [$this, 'filter_is_purchasable'], 10, 2);
				add_filter('woocommerce_cart_item_is_purchasable', [$this, 'filter_cart_item_is_purchasable'], 10, 4);
				add_action('woocommerce_check_cart_items', [$this, 'validate_cart_hotel_bookings'], 20);
			}
			private static function parse_date_range($date_range) {
				$parsed = TTBM_Global_Function::parse_date_range_string( $date_range );
				if ( ! $parsed ) {
					return false;
				}

				$check_in  = $parsed[0];
				$check_out = $parsed[1];
				if ( strtotime( $check_out ) <= strtotime( $check_in ) ) {
					return false;
				}

				$interval = date_create( $check_in )->diff( date_create( $check_out ) );
				return [
					'check_in' => $check_in,
					'check_out' => $check_out,
					'days' => (int) $interval->days,
				];
			}
			private static function build_hotel_booking_request($hotel_id, $date_range, $room_data_info) {
				$date_info = self::parse_date_range($date_range);
				if (!$date_info || !is_array($room_data_info)) {
					return new WP_Error('invalid_booking_request', __('Invalid booking request.', 'tour-booking-manager'));
				}

				$available_rooms = TTBM_Global_Function::ttbm_get_full_room_ticket_info($hotel_id, $date_info['check_in'], $date_info['check_out']);
				$rooms_by_key = [];
				foreach ($available_rooms as $room) {
					$key = isset($room['room_type_key']) ? $room['room_type_key'] : preg_replace('/[^A-Za-z0-9]/', '', (string) ($room['ttbm_hotel_room_name'] ?? ''));
					if ($key) {
						$rooms_by_key[$key] = $room;
					}
				}

				$sanitized_rooms = [];
				$total_price = 0.0;
				foreach ($room_data_info as $room_key => $room_request) {
					$room_key = preg_replace('/[^A-Za-z0-9]/', '', (string) $room_key);
					$quantity = isset($room_request['quantity']) ? absint($room_request['quantity']) : 0;
					if (!$room_key || $quantity < 1) {
						continue;
					}
					if (!isset($rooms_by_key[$room_key])) {
						return new WP_Error('invalid_room', __('One or more selected rooms are invalid.', 'tour-booking-manager'));
					}

					$room = $rooms_by_key[$room_key];
					$available = isset($room['available']) ? absint($room['available']) : 0;
					if ($quantity > $available) {
						return new WP_Error('room_unavailable', __('One or more selected rooms are no longer available.', 'tour-booking-manager'));
					}

					$nightly_price = isset($room['ttbm_hotel_room_price']) ? (float) $room['ttbm_hotel_room_price'] : 0.0;
					$sanitized_rooms[$room_key] = [
						'quantity' => $quantity,
						'price' => $nightly_price,
						'name' => $room['ttbm_hotel_room_name'] ?? $room_key,
					];
					$total_price += $nightly_price * $quantity * $date_info['days'];
				}

				if (empty($sanitized_rooms) || $total_price < 0) {
					return new WP_Error('empty_booking', __('Please select at least one available room.', 'tour-booking-manager'));
				}

				return [
					'rooms' => $sanitized_rooms,
					'total_price' => $total_price,
					'date_info' => $date_info,
				];
			}
			/**
			 * The hotel post a hidden WooCommerce product is linked to, or 0.
			 */
			private static function hotel_id_for_product($product_id) {
				$product_id = absint($product_id);
				if (!$product_id) {
					return 0;
				}
				$hotel_id = absint(get_post_meta($product_id, 'link_ttbm_id', true));
				if (!$hotel_id || 'ttbm_hotel' !== get_post_type($hotel_id)) {
					return 0;
				}
				$linked_product = absint(get_post_meta($hotel_id, 'link_wc_product', true));
				if ($linked_product && $linked_product !== $product_id) {
					return 0;
				}
				return $hotel_id;
			}
			public static function is_hotel_linked_product($product_id) {
				return self::hotel_id_for_product($product_id) > 0;
			}
			private static function normalize_booking_payload($payload) {
				if (is_string($payload) && is_serialized($payload)) {
					$payload = @unserialize($payload, array('allowed_classes' => false));
				}
				if (is_string($payload) && is_array(json_decode($payload, true))) {
					$payload = json_decode($payload, true);
				}
				return is_array($payload) ? $payload : array();
			}
			private static function normalize_amount($value) {
				if (!is_scalar($value) || '' === trim((string) $value)) {
					return null;
				}
				$value = wc_format_decimal((string) $value);
				return is_numeric($value) && is_finite((float) $value) && (float) $value >= 0 ? (float) $value : null;
			}
			private static function validate_payload_structure($payload, $product_id, $require_published = false) {
				$payload = self::normalize_booking_payload($payload);
				$hotel_info = isset($payload['ttbm_hotel_info']) && is_array($payload['ttbm_hotel_info']) ? $payload['ttbm_hotel_info'] : array();
				$hotel_id = isset($hotel_info['hotel_id']) ? absint($hotel_info['hotel_id']) : 0;
				$check_in = isset($hotel_info['ttbm_checkin_date']) ? sanitize_text_field((string) $hotel_info['ttbm_checkin_date']) : '';
				$check_out = isset($hotel_info['ttbm_checkout_date']) ? sanitize_text_field((string) $hotel_info['ttbm_checkout_date']) : '';
				$marker = isset($payload['hotel_booking']) ? $payload['hotel_booking'] : '';
				if (!$hotel_id || empty($marker)) {
					return new WP_Error('ttbm_hotel_booking_missing', __('The hotel booking details are missing.', 'tour-booking-manager'));
				}
				if (self::hotel_id_for_product($product_id) !== $hotel_id) {
					return new WP_Error('ttbm_hotel_booking_mismatch', __('The hotel booking does not belong to this product.', 'tour-booking-manager'));
				}
				if ($require_published && 'publish' !== get_post_status($hotel_id)) {
					return new WP_Error('ttbm_hotel_unavailable', __('This hotel is not available for booking.', 'tour-booking-manager'));
				}
				$date_info = self::parse_date_range($check_in . ' - ' . $check_out);
				if (!$date_info) {
					return new WP_Error('ttbm_hotel_invalid_dates', __('The hotel dates are invalid. Please select the dates again.', 'tour-booking-manager'));
				}
				$rooms = isset($payload['hotel_room_ordered_data_info']) && is_array($payload['hotel_room_ordered_data_info']) ? $payload['hotel_room_ordered_data_info'] : array();
				$price = self::normalize_amount(isset($payload['price']) ? $payload['price'] : null);
				$room_request = array();
				$normalized_rooms = array();
				foreach ($rooms as $room_key => $room) {
					$room_key = preg_replace('/[^A-Za-z0-9]/', '', (string) $room_key);
					$quantity = is_array($room) && isset($room['quantity']) ? absint($room['quantity']) : 0;
					if (!$room_key || $quantity < 1 || isset($room_request[$room_key])) {
						return new WP_Error('ttbm_hotel_invalid_rooms', __('The selected hotel rooms are invalid. Please select them again.', 'tour-booking-manager'));
					}
					$nightly_price = is_array($room) && isset($room['price']) ? self::normalize_amount($room['price']) : 0;
					if (null === $nightly_price) {
						$nightly_price = 0;
					}
					$room_request[$room_key] = array('quantity' => $quantity);
					$normalized_rooms[$room_key] = array(
						'quantity' => $quantity,
						'price' => $nightly_price,
						'name' => is_array($room) && isset($room['name']) ? sanitize_text_field((string) $room['name']) : $room_key,
					);
				}
				if (empty($room_request) || null === $price) {
					return new WP_Error('ttbm_hotel_invalid_booking', __('The hotel booking details are invalid. Please try again.', 'tour-booking-manager'));
				}
				return array(
					'hotel_id' => $hotel_id,
					'check_in' => $date_info['check_in'],
					'check_out' => $date_info['check_out'],
					'days' => (int) $date_info['days'],
					'rooms' => $normalized_rooms,
					'room_request' => $room_request,
					'price' => (float) $price,
					'marker' => $marker,
				);
			}
			private static function validate_payload_amount($payload) {
				if (is_wp_error($payload)) {
					return $payload;
				}
				$calculated = 0.0;
				foreach ($payload['rooms'] as $room) {
					$calculated += (float) $room['price'] * (int) $room['quantity'] * (int) $payload['days'];
				}
				if (abs($calculated - (float) $payload['price']) > 0.01) {
					return new WP_Error('ttbm_hotel_invalid_amount', __('The hotel booking amount is invalid.', 'tour-booking-manager'));
				}
				return $payload;
			}
			private static function validate_payload($payload, $product_id, $deep = false, $require_published = false) {
				$base = self::validate_payload_structure($payload, $product_id, $require_published);
				if (is_wp_error($base) || !$deep) {
					return $base;
				}
				$validated = self::build_hotel_booking_request($base['hotel_id'], $base['check_in'] . ' - ' . $base['check_out'], $base['room_request']);
				if (is_wp_error($validated)) {
					return $validated;
				}
				$expected_keys = array_keys($validated['rooms']);
				$requested_keys = array_keys($base['rooms']);
				sort($expected_keys);
				sort($requested_keys);
				if ($expected_keys !== $requested_keys) {
					return new WP_Error('ttbm_hotel_invalid_rooms', __('The selected hotel rooms are invalid. Please select them again.', 'tour-booking-manager'));
				}
				foreach ($base['rooms'] as $room_key => $room) {
					if ((int) $room['quantity'] !== (int) $validated['rooms'][$room_key]['quantity']) {
						return new WP_Error('ttbm_hotel_invalid_rooms', __('The selected hotel rooms are invalid. Please select them again.', 'tour-booking-manager'));
					}
				}
				if (abs((float) $validated['total_price'] - (float) $base['price']) > 0.01) {
					return new WP_Error('ttbm_hotel_price_changed', __('The hotel room price has changed. Please select the rooms and dates again.', 'tour-booking-manager'));
				}
				$base['rooms'] = $validated['rooms'];
				$base['price'] = (float) $validated['total_price'];
				$base['days'] = (int) $validated['date_info']['days'];
				$base['check_in'] = $validated['date_info']['check_in'];
				$base['check_out'] = $validated['date_info']['check_out'];
				return $base;
			}
			public static function validate_linked_product_add($product_id, $cart_item_data = array()) {
				if (!self::is_hotel_linked_product($product_id)) {
					return true;
				}
				if (!is_array($cart_item_data) || empty($cart_item_data['ttbm_hotel_booking'])) {
					return new WP_Error('ttbm_hotel_booking_required', __('Hotel rooms can only be booked through the hotel booking form.', 'tour-booking-manager'));
				}
				return self::validate_hotel_cart_item($product_id, $cart_item_data, true, true);
			}
			private static function cart_item_payload($cart_item) {
				return array(
					'hotel_room_ordered_data_info' => isset($cart_item['room_ordered_data_info']) ? $cart_item['room_ordered_data_info'] : array(),
					'price' => array_key_exists('custom_price', $cart_item) ? $cart_item['custom_price'] : (isset($cart_item['price']) ? $cart_item['price'] : null),
					'ttbm_hotel_info' => isset($cart_item['ttbm_hotel_info']) ? $cart_item['ttbm_hotel_info'] : array(),
					'quantity' => isset($cart_item['quantity']) ? $cart_item['quantity'] : 1,
					'booking_days' => isset($cart_item['days']) ? $cart_item['days'] : 0,
					'hotel_booking' => isset($cart_item['ttbm_hotel_booking']) ? $cart_item['ttbm_hotel_booking'] : '',
				);
			}
			private static function validate_hotel_cart_item($product_id, $cart_item, $deep = true, $require_published = false) {
				if (!self::is_hotel_linked_product($product_id)) {
					return true;
				}
				if (!is_array($cart_item) || empty($cart_item['ttbm_hotel_booking'])) {
					return new WP_Error('ttbm_hotel_booking_required', __('Hotel rooms can only be booked through the hotel booking form.', 'tour-booking-manager'));
				}
				return self::validate_payload(self::cart_item_payload($cart_item), $product_id, $deep, $require_published);
			}
			public function filter_is_purchasable($purchasable, $product) {
				if (!$purchasable || !is_object($product) || !method_exists($product, 'get_id')) {
					return $purchasable;
				}
				if (!defined('WC_VERSION') || version_compare(WC_VERSION, '7.0', '<')) {
					return $purchasable;
				}
				$product_id = absint($product->get_id());
				if (!self::is_hotel_linked_product($product_id)) {
					return $purchasable;
				}
				if ((int) self::$allow_linked_product_add === $product_id) {
					return true;
				}
				return self::cart_contains_valid_hotel_item($product_id);
			}
			/**
			 * WooCommerce checks the product's purchasable flag while loading the
			 * cart from the session, before cart contents are available. Override
			 * it for a cart line that carries its own validated booking data so a
			 * genuine booking is not removed as "no longer purchasable".
			 */
			public function filter_cart_item_is_purchasable($is_purchasable, $cart_item_key, $values, $product) {
				unset($cart_item_key);
				if (!is_object($product) || !method_exists($product, 'get_id')) {
					return $is_purchasable;
				}
				$product_id = absint($product->get_id());
				if (!self::is_hotel_linked_product($product_id)) {
					return $is_purchasable;
				}
				return !is_wp_error(self::validate_hotel_cart_item($product_id, $values, false, true));
			}
			private static function cart_contains_valid_hotel_item($product_id) {
				if (!function_exists('WC') || !WC()->cart) {
					return false;
				}
				foreach ((array) WC()->cart->get_cart() as $cart_item) {
					$item_product_id = isset($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
					if ($item_product_id !== $product_id) {
						continue;
					}
					if (!is_wp_error(self::validate_hotel_cart_item($product_id, $cart_item, false, true))) {
						return true;
					}
				}
				return false;
			}
			public function validate_cart_hotel_bookings() {
				if (!function_exists('WC') || !WC()->cart) {
					return;
				}
				foreach (WC()->cart->get_cart() as $cart_item) {
					$product_id = isset($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
					if (!self::is_hotel_linked_product($product_id)) {
						continue;
					}
					$validation = self::validate_hotel_cart_item($product_id, $cart_item, true, true);
					if (is_wp_error($validation)) {
						wc_add_notice($validation->get_error_message(), 'error');
					}
				}
			}
			private static function order_payload($validated) {
				return array(
					'hotel_room_ordered_data_info' => $validated['rooms'],
					'price' => (float) $validated['price'],
					'ttbm_hotel_info' => array(
						'hotel_id' => (int) $validated['hotel_id'],
						'ttbm_checkin_date' => $validated['check_in'],
						'ttbm_checkout_date' => $validated['check_out'],
						'ttbm_hotel_num_of_day' => (int) $validated['days'],
					),
					'quantity' => 1,
					'booking_days' => (int) $validated['days'],
					'hotel_booking' => 'yes',
				);
			}
			private static function validate_order_payload($payload, $product_id) {
				return self::validate_payload_amount(self::validate_payload_structure($payload, $product_id, false));
			}
			private function hotel_booking_exists($order_id, $hotel_id) {
				$existing = get_posts(array(
					'post_type' => 'ttbm_hotel_booking',
					'post_status' => 'any',
					'fields' => 'ids',
					'posts_per_page' => 1,
					'no_found_rows' => true,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => '_ttbm_hotel_booking_order_id',
							'value' => absint($order_id),
							'compare' => '=',
						),
						array(
							'key' => '_ttbm_hotel_id',
							'value' => absint($hotel_id),
							'compare' => '=',
						),
					),
				));
				return !empty($existing);
			}
			private function create_hotel_booking_record($order, $item_id, $validated, $payment_method) {
				$order_id = $order->get_id();
				$order_created = $order->get_date_created();
				$order_created_date = $order_created ? $order_created->date('Y-m-d H:i:s') : current_time('mysql');
				$customer_id = $order->get_customer_id();
				$customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
				$customer_email = $order->get_billing_email();
				$hotel_id = (int) $validated['hotel_id'];
				$hotel_title = get_the_title($hotel_id);
				$custom_order_id = wp_insert_post(array(
					'post_title' => 'Hotel Booking #' . $order_id,
					'post_type' => 'ttbm_hotel_booking',
					'post_status' => 'publish',
					'post_author' => 1,
				));
				if (is_wp_error($custom_order_id) || !$custom_order_id) {
					return 0;
				}
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_order_id', $order_id);
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_order_item_id', absint($item_id));
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_date', $order_created_date);
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_status', 'In Progress');
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_customer_id', $customer_id);
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_customer_name', sanitize_text_field($customer_name));
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_customer_email', sanitize_email($customer_email));
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_room_info', $validated['rooms']);
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_checkin_date', $validated['check_in']);
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_checkout_date', $validated['check_out']);
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_days', (int) $validated['days']);
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_price', (float) $validated['price']);
				update_post_meta($custom_order_id, '_ttbm_hotel_id', $hotel_id);
				update_post_meta($custom_order_id, '_ttbm_hotel_title', sanitize_text_field($hotel_title));
				update_post_meta($custom_order_id, '_ttbm_hotel_booking_payment_method', sanitize_text_field((string) $payment_method));
				return $custom_order_id;
			}
			function custom_function_on_order_status_change( $order_id, $old_status, $new_status, $order) {
				if ('processing' !== $new_status) {
					return;
				}
				if (!($order instanceof WC_Order)) {
					$order = wc_get_order($order_id);
				}
				if (!$order) {
					return;
				}
				$order_id = $order->get_id();
				$payment_method = $order->get_payment_method_title();
				foreach ($order->get_items() as $item_id => $item) {
					if (!($item instanceof WC_Order_Item_Product)) {
						continue;
					}
					$product = $item->get_product();
					if (!$product) {
						continue;
					}
					$product_id = absint($product->get_id());
					if (!self::is_hotel_linked_product($product_id)) {
						continue;
					}
					$payload = $item->get_meta('_ttbm_hotel_booking_data', true);
					$validated = self::validate_order_payload($payload, $product_id);
					if (is_wp_error($validated)) {
						if ((float) $item->get_subtotal() > 0 && method_exists($order, 'add_order_note')) {
							$order->add_order_note(__('No validated hotel booking data was found for this order line, so no hotel booking was created. If this is a pre-update order, create the hotel booking manually.', 'tour-booking-manager'));
						}
						continue;
					}
					if (abs((float) $item->get_subtotal() - (float) $validated['price']) > 0.01) {
						continue;
					}
					if ($this->hotel_booking_exists($order_id, (int) $validated['hotel_id'])) {
						continue;
					}
					$this->create_hotel_booking_record($order, $item_id, $validated, $payment_method);
				}
			}
			function add_order_item_meta($item, $cart_item_key, $values, $order) {
				if (isset($values['ttbm_hotel_booking'])) {
					$meta_fields = [
						'description_order' => __('Hotel Booking Details', 'tour-booking-manager'),
						'booking_seats' => __('Seats', 'tour-booking-manager'),
						'checkin_date' => __('Check In Date', 'tour-booking-manager'),
						'checkout_date' => __('Check Out Date', 'tour-booking-manager'),
					];
					foreach ($meta_fields as $key => $label) {
						if (!empty($values[$key])) {
							$value = is_array($values[$key]) ? implode(', ', $values[$key]) : $values[$key];
							$item->add_meta_data($label, $value, true);
						}
					}
					$customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
					if (!empty($customer_name)) {
						$item->add_meta_data('Customer Name', $customer_name, true);
					}
				}
				if (isset($values['ttbm_hotel_booking'])) {
					$product_id = isset($values['ttbm_hotel_id']) ? absint($values['ttbm_hotel_id']) : (int) $item->get_product_id();
					if (!$product_id) {
						return;
					}
					/*
					 * The validated booking belongs to this order line, never to
					 * the shared hidden product: a product-level value would let a
					 * later order overwrite this one and let an unrelated order
					 * create a booking from it.
					 */
					$payload = self::validate_payload(self::cart_item_payload($values), $product_id, false, true);
					$payload = self::validate_payload_amount($payload);
					if (is_wp_error($payload)) {
						if (is_object($order) && method_exists($order, 'add_order_note')) {
							$order->add_order_note(sprintf(__('Hotel booking data was rejected: %s', 'tour-booking-manager'), $payload->get_error_message()));
						}
						return;
					}
					$item->add_meta_data('_ttbm_hotel_booking_data', self::order_payload($payload), true);
				}
			}
			public function ttbm_get_hotel_room_list() {
				if (!isset( $_POST['nonce' ] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_frontend_nonce' ) ) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$hotel_id = isset($_REQUEST['hotel_id']) ? absint(wp_unslash($_REQUEST['hotel_id'])) : 0;
				$date_range = isset($_REQUEST['date_range']) ? sanitize_text_field(wp_unslash($_REQUEST['date_range'])) : '';
				$date = TTBM_Global_Function::parse_date_range_string( $date_range );
				if ( ! $date ) {
					wp_send_json_error( [ 'message' => __( 'Invalid date range.', 'tour-booking-manager' ) ] );
					die;
				}
				$start_date = $date[0];
				$end_date   = $date[1];
				do_action('ttbm_hotel_booking_panel', $start_date, $end_date, $hotel_id);
				die();
			}
			public function ttbm_hotel_room_booking() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_frontend_nonce' ) ) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}

				$hotel_id = isset($_REQUEST['hotel_id']) ? absint(wp_unslash($_REQUEST['hotel_id'])) : 0;
				$date_range = isset($_REQUEST['date_range']) ? sanitize_text_field(wp_unslash($_REQUEST['date_range'])) : '';
				$room_data_info = isset($_REQUEST['room_data_info']) ? json_decode(wp_unslash($_REQUEST['room_data_info']), true) : [];
				$booking_request = self::build_hotel_booking_request($hotel_id, $date_range, $room_data_info);
				if (is_wp_error($booking_request)) {
					wp_send_json_error(['message' => $booking_request->get_error_message()]);
				}

				$room_data_info = $booking_request['rooms'];
				$price = $booking_request['total_price'];
				$check_in = $booking_request['date_info']['check_in'];
				$check_out = $booking_request['date_info']['check_out'];
				$days = $booking_request['date_info']['days'];
				$room_output = "Room List\n\n";
				$currency_symbol = get_woocommerce_currency_symbol();
				$room_output .= '<div class="ttbm_hotel_ordered_room_list">';
				foreach ($room_data_info as $roomName => $info) {
					$qty = $info['quantity'];
					$room_price = $info["price"];
					$total = $room_price * $qty * $days;
					// Format room name: insert space before capital letters except first
					$formattedRoomName = isset($info['name']) ? $info['name'] : preg_replace('/(?<!^)([A-Z])/', ' $1', $roomName);
					// Build the output
					$room_output .= " <div class='ttbm_hotel_room'>";
					$room_output .= " <div class='ttbm_hotel_room_name'> Room Name: {$formattedRoomName}</div> ";
					$room_output .= " <div class='ttbm_hotel_qty'>Qty: {$qty}</div> ";
					$room_output .= " <div class='ttbm_hotel_price'>Price: ( " . number_format($room_price, 2) . " {$currency_symbol} x {$qty} x {$days} ) = " . number_format($total, 2) . " {$currency_symbol}</div> ";
					$room_output .= " </div> ";
				}
				$room_output .= '</div>';
				$post_id = absint(get_post_meta($hotel_id, 'link_wc_product', true));
				if (!$post_id || !wc_get_product($post_id)) {
					wp_send_json_error(['message' => __('The selected hotel is not linked to a purchasable product.', 'tour-booking-manager')]);
				}
				$quantity = 1;
				$hotel_info = array();
				$hotel_info['hotel_id'] = $hotel_id;
				$hotel_info['ttbm_checkin_date'] = $check_in;
				$hotel_info['ttbm_checkout_date'] = $check_out;
				$hotel_info['ttbm_hotel_num_of_day'] = $days;
				$cart_item_data = [
					'ttbm_hotel_booking' => 'yes',
					'ttbm_hotel_id' => $post_id,
					'description_order' => $room_output,
					'room_ordered_data_info' => $room_data_info,
					'price' => $price,
					'custom_price' => $price,
					'ttbm_tp' => $price,
					'line_total' => $price,
					'line_subtotal' => $price,
					'checkin_date' => $check_in,
					'checkout_date' => $check_out,
					'days' => $days,
					'ttbm_hotel_info' => apply_filters('ttbm_hotel_info_filter', $hotel_info, $hotel_id),
				];
				if (!class_exists('WC_Cart')) {
					wp_send_json_error('WooCommerce is not active.');
				}
				WC()->cart->empty_cart();
				self::$allow_linked_product_add = absint($post_id);
				try {
					$cart_item_key = WC()->cart->add_to_cart($post_id, $quantity, 0, [], $cart_item_data);
				} finally {
					self::$allow_linked_product_add = 0;
				}
				if ($cart_item_key) {
					wp_send_json_success('Item added to cart.');
				} else {
					wp_send_json_error('Failed to add to cart.');
				}
			}
			public function display_order_meta($item_id, $item, $order) {
				$meta_fields = [
					'description_order' => 'Book Details',
					'booking_seats' => 'Seats',
					'checkin_date' => 'Check In Date',
					'checkout_date' => 'Check Out Date',
				];
				echo '<div class="mptrs-order-meta"><h4 class="mptrs-meta-title">Order Details</h4>';
				foreach ($meta_fields as $key => $label) {
					$value = $item->get_meta($key, true);
					if (!empty($value)) {
						$value = preg_replace('/<\/li>,/', '</li>', $value);
						echo '<p><strong>' . esc_attr($label) . ':</strong> ' . wp_kses_post($value) . '</p>';
					}
				}
				echo '</div>';
			}
			public function display_custom_cart_item_data($item_data, $cart_item) {
				$fields = [
					'description_order' => 'Book Details',
					'booking_seats' => 'Seats',
					'checkin_date' => 'Check In Date',
					'checkout_date' => 'Check Out Date',
				];
				foreach ($fields as $key => $label) {
					if (!empty($cart_item[$key])) {
						$value = is_array($cart_item[$key]) ? implode(', ', $cart_item[$key]) : $cart_item[$key];
						$item_data[] = [
							'name' => $label,
							'value' => wp_kses_post($value),
						];
					}
				}
				return $item_data;
			}
			public function hotel_booking_panel($tour_date = '', $end_Date = '', $hotel_id = '') {
				$action = apply_filters('ttbm_form_submit_path', '', $hotel_id);
				?>
                <form action="<?php echo esc_attr($action); ?>" method='post' class="mp_tour_ticket_form">
                    <input type="hidden" name='ttbm_total_price' id="ttbm_total_price" value='0'/>
					<?php
						wp_nonce_field('ttbm_hotel_nonce', 'ttbm_hotel_nonce');
                        require_once TTBM_Function::template_path('ticket/hotel_book_ticket.php');
                        ?>
                </form>
				<?php
			}
			public function set_custom_price_cart_item( $cart_item_data, $product_id) {

				if (isset($cart_item_data['ttbm_hotel_booking']) && (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce'))) {
					if (empty($cart_item_data['custom_price'])) {
						$hotel_id = isset($_POST['hotel_id']) ? absint(wp_unslash($_POST['hotel_id'])) : 0;
						$date_range = isset($_POST['date_range']) ? sanitize_text_field(wp_unslash($_POST['date_range'])) : '';
						$room_data_info = isset($_POST['room_data_info']) ? json_decode(wp_unslash($_POST['room_data_info']), true) : [];
						$booking_request = self::build_hotel_booking_request($hotel_id, $date_range, $room_data_info);
						if (!is_wp_error($booking_request)) {
							$cart_item_data['custom_price'] = $booking_request['total_price'];
						}
					}

				}
				return $cart_item_data;
			}
			public function update_cart_item_price($cart) {
				if (is_admin() && !defined('DOING_AJAX'))
					return;
				foreach ($cart->get_cart() as $cart_item) {
					if (isset($cart_item['ttbm_hotel_booking'])) {
						if (!empty($cart_item['custom_price'])) {
							$cart_item['data']->set_price($cart_item['custom_price']);
						}
					}
				}
			}
		}
		new TTBM_Hotel_Booking();
	}
