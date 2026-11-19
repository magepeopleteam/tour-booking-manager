<?php
/**
 * wp eval-file /absolute/path/to/tests/hotel-booking-security.php
 * Run with WooCommerce and Tour Free active.
 *
 * Regression coverage for the hotel booking payment bypass:
 * - a hidden hotel product cannot be bought through ?add-to-cart;
 * - the validated booking is stored on the order line, not the shared product;
 * - processing an order creates a booking only from that line's own data and
 *   only when the line amount matches the booking amount;
 * - one order cannot overwrite or reuse another order's booking data;
 * - an order without line booking data never falls back to the shared product
 *   meta (pre-fix orders can be created manually by an administrator).
 *
 * Fixtures run in a rolled-back transaction. The cart and request globals are
 * restored afterwards.
 */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) {
	exit;
}
if (!defined('DOING_AJAX')) {
	define('DOING_AJAX', true);
}
if (!class_exists('TTBM_Hotel_Booking') || !class_exists('WC_Order')) {
	WP_CLI::error('Activate WooCommerce and Tour Booking Manager before running this test.');
}

$checks = 0;
$failures = array();
$fixtures = array();
$saved_post = $_POST;
$saved_request = $_REQUEST;
$saved_cart = WC()->cart ? WC()->cart->cart_contents : array();
$assert = static function ($condition, $message) use (&$checks, &$failures) {
	$checks++;
	if (!$condition) {
		$failures[] = $message;
		WP_CLI::log('FAIL ' . $message);
	}
};
$make_hotel = static function ($price = 200, $qty = 2) use (&$fixtures) {
	$hotel_id = wp_insert_post(array('post_type' => 'ttbm_hotel', 'post_status' => 'publish', 'post_title' => 'Hotel security fixture'));
	update_post_meta($hotel_id, 'ttbm_room_details', array(array(
		'ttbm_hotel_room_name' => 'Deluxe',
		'ttbm_hotel_room_qty' => $qty,
		'ttbm_hotel_room_price' => $price,
	)));
	$product_id = wp_insert_post(array('post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Hotel security fixture product'));
	update_post_meta($hotel_id, 'link_wc_product', $product_id);
	update_post_meta($product_id, 'link_ttbm_id', $hotel_id);
	foreach (array('_price' => 0, '_regular_price' => 0, '_virtual' => 'yes', '_sold_individually' => 'yes', '_stock_status' => 'instock', '_manage_stock' => 'no') as $key => $value) {
		update_post_meta($product_id, $key, $value);
	}
	wp_set_object_terms($product_id, array('exclude-from-catalog', 'exclude-from-search'), 'product_visibility');
	$fixtures[] = $hotel_id;
	$fixtures[] = $product_id;
	return array($hotel_id, $product_id);
};
$payload = static function ($hotel_id, $check_in, $check_out, $days, $price, $quantity = 1) {
	return array(
		'hotel_room_ordered_data_info' => array('Deluxe' => array('quantity' => $quantity, 'price' => $price / max(1, $days * $quantity), 'name' => 'Deluxe')),
		'price' => $price,
		'ttbm_hotel_info' => array(
			'hotel_id' => $hotel_id,
			'ttbm_checkin_date' => $check_in,
			'ttbm_checkout_date' => $check_out,
			'ttbm_hotel_num_of_day' => $days,
		),
		'quantity' => $quantity,
		'booking_days' => $days,
		'hotel_booking' => 'yes',
	);
};
$values = static function ($hotel_id, $product_id, $check_in, $check_out, $price) use ($payload) {
	$days = (int) date_create($check_in)->diff(date_create($check_out))->days;
	return array(
		'ttbm_hotel_booking' => 'yes',
		'ttbm_hotel_id' => $product_id,
		'room_ordered_data_info' => $payload($hotel_id, $check_in, $check_out, $days, $price)['hotel_room_ordered_data_info'],
		'price' => $price,
		'custom_price' => $price,
		'ttbm_hotel_info' => $payload($hotel_id, $check_in, $check_out, $days, $price)['ttbm_hotel_info'],
		'quantity' => 1,
		'days' => $days,
	);
};
$make_order = static function ($product_id, $amount, $values = null, $display_dates = null) use (&$fixtures) {
	$order = wc_create_order(array('status' => 'pending'));
	$item = new WC_Order_Item_Product();
	$item->set_name(get_the_title($product_id));
	$item->set_product_id($product_id);
	$item->set_variation_id(0);
	$item->set_quantity(1);
	$item->set_subtotal($amount);
	$item->set_total($amount);
	if (null !== $values) {
		do_action('woocommerce_checkout_create_order_line_item', $item, 'fixture-cart-key', $values, $order);
	}
	if (is_array($display_dates)) {
		$item->add_meta_data(__('Check In Date', 'tour-booking-manager'), $display_dates[0]);
		$item->add_meta_data(__('Check Out Date', 'tour-booking-manager'), $display_dates[1]);
	}
	$item->save();
	$order->add_item($item);
	$order->set_total($amount);
	$order->save();
	$fixtures[] = $order->get_id();
	return array($order, $item);
};
$bookings_for = static function ($order_id) {
	return get_posts(array(
		'post_type' => 'ttbm_hotel_booking',
		'post_status' => 'any',
		'fields' => 'ids',
		'posts_per_page' => -1,
		'meta_key' => '_ttbm_hotel_booking_order_id',
		'meta_value' => $order_id,
	));
};
$clear_cart = static function () {
	if (WC()->cart) {
		WC()->cart->cart_contents = array();
	}
	wc_clear_notices();
};
$ajax_booking = static function ($hotel_id, $date_range, $rooms) {
	$_POST = array(
		'nonce' => wp_create_nonce('ttbm_frontend_nonce'),
		'hotel_id' => $hotel_id,
		'date_range' => $date_range,
		'room_data_info' => wp_json_encode($rooms),
	);
	$_REQUEST = $_POST;
	$die_handler = static function () {
		return static function () {
			throw new RuntimeException('ttbm_ajax_done');
		};
	};
	add_filter('wp_die_ajax_handler', $die_handler);
	ob_start();
	try {
		$handler = (new ReflectionClass('TTBM_Hotel_Booking'))->newInstanceWithoutConstructor();
		$handler->ttbm_hotel_room_booking();
	} catch (RuntimeException $error) {
		if ('ttbm_ajax_done' !== $error->getMessage()) {
			throw $error;
		}
	}
	$json = ob_get_clean();
	remove_filter('wp_die_ajax_handler', $die_handler);
	return json_decode($json, true);
};

add_filter('pre_wp_mail', '__return_false');
global $wpdb;
$wpdb->query('START TRANSACTION');
register_shutdown_function(static function () use ($wpdb) {
	$wpdb->query('ROLLBACK');
});

try {
	$clear_cart();
	list($hotel_id, $product_id) = $make_hotel();

	/* 1. A direct product request is not a hotel booking. */
	$assert(false === apply_filters('woocommerce_add_to_cart_validation', true, $product_id, 1), 'A direct add-to-cart of the hidden hotel product was accepted.');
	$assert(is_wp_error(TTBM_Hotel_Booking::validate_linked_product_add($product_id, array())), 'The hotel add-to-cart validator accepted a request without booking data.');
	$assert(true === apply_filters('woocommerce_add_to_cart_validation', true, $product_id, 1, 0, array(), $values($hotel_id, $product_id, '2027-06-01', '2027-06-04', 600)), 'The hotel handler cart item was rejected.');
	if (defined('WC_VERSION') && version_compare(WC_VERSION, '7.0', '>=')) {
		$assert(false === wc_get_product($product_id)->is_purchasable(), 'The hidden hotel product is purchasable without a booking.');
		$assert(true === apply_filters('woocommerce_cart_item_is_purchasable', false, 'fixture-cart-key', $values($hotel_id, $product_id, '2027-06-01', '2027-06-04', 600), wc_get_product($product_id)), 'A valid hotel cart line was not kept purchasable while loading the session.');
		$assert(false === apply_filters('woocommerce_cart_item_is_purchasable', false, 'fixture-cart-key', array('product_id' => $product_id), wc_get_product($product_id)), 'A direct hotel cart line was kept purchasable while loading the session.');
	}
	wc_clear_notices();

	/* 2. The real hotel handler still adds the product at the server price. */
	$response = $ajax_booking($hotel_id, '2027-06-01 - 2027-06-04', array('Deluxe' => array('quantity' => 1)));
	$cart = WC()->cart ? WC()->cart->get_cart() : array();
	$assert(!empty($response['success']), 'The hotel booking handler no longer adds a valid room.');
	$assert(1 === count($cart), 'The hotel booking handler did not create one cart line.');
	$cart_item = reset($cart);
	$cart_key = key($cart);
	$assert(isset($cart_item['ttbm_hotel_booking']) && 'yes' === $cart_item['ttbm_hotel_booking'], 'The hotel cart line lost its booking marker.');
	$assert(600.0 === (float) ($cart_item['custom_price'] ?? 0), 'The hotel cart line did not keep the server-calculated price.');
	if (defined('WC_VERSION') && version_compare(WC_VERSION, '7.0', '>=')) {
		$assert(true === wc_get_product($product_id)->is_purchasable(), 'A valid hotel cart line is not considered purchasable.');
	}
	wc_clear_notices();
	do_action('woocommerce_check_cart_items');
	$assert(0 === wc_notice_count('error'), 'A valid hotel cart line was rejected at checkout.');

	/* 3. Checkout stores the validated booking on the line, not the product. */
	$order = wc_create_order(array('status' => 'pending'));
	$item = new WC_Order_Item_Product();
	$item->set_name($cart_item['data']->get_name());
	$item->set_product_id($product_id);
	$item->set_variation_id(0);
	$item->set_quantity(1);
	$item->set_subtotal(600);
	$item->set_total(600);
	do_action('woocommerce_checkout_create_order_line_item', $item, $cart_key, $cart_item, $order);
	$item->save();
	$order->add_item($item);
	$order->set_total(600);
	$order->save();
	$order_id = $order->get_id();
	$item_id = $item->get_id();
	$line_data = $item->get_meta('_ttbm_hotel_booking_data', true);
	$assert(is_array($line_data) && isset($line_data['hotel_booking']), 'The order line did not store the validated hotel booking data.');
	$assert(600.0 === (float) ($line_data['price'] ?? 0), 'The order line booking amount is wrong.');
	$assert('' === (string) get_post_meta($product_id, '_ttbm_hotel_booking_data', true), 'Checkout still writes booking data to the shared product.');

	/* 4. Processing creates one booking from that line, and a replay does not duplicate it. */
	$clear_cart();
	do_action('woocommerce_order_status_changed', $order_id, 'pending', 'processing', $order);
	$bookings = $bookings_for($order_id);
	$assert(1 === count($bookings), 'Processing the valid hotel order did not create exactly one booking.');
	if ($bookings) {
		$assert('2027-06-01' === get_post_meta($bookings[0], '_ttbm_hotel_booking_checkin_date', true), 'The booking check-in date came from the wrong data.');
		$assert(600.0 === (float) get_post_meta($bookings[0], '_ttbm_hotel_booking_price', true), 'The booking price is wrong.');
		$assert((int) $item_id === (int) get_post_meta($bookings[0], '_ttbm_hotel_booking_order_item_id', true), 'The booking is not tied to its own order line.');
	}
	do_action('woocommerce_order_status_changed', $order_id, 'completed', 'processing', $order);
	$assert(1 === count($bookings_for($order_id)), 'A repeated processing transition duplicated the booking.');

	/* 5. A 0.00 direct order cannot reuse a previous order's shared product meta. */
	$legacy = $payload($hotel_id, '2027-06-01', '2027-06-04', 3, 600);
	update_post_meta($product_id, '_ttbm_hotel_booking_data', maybe_serialize($legacy));
	list($free_order) = $make_order($product_id, 0, null, null);
	do_action('woocommerce_order_status_changed', $free_order->get_id(), 'pending', 'processing', $free_order);
	$assert(0 === count($bookings_for($free_order->get_id())), 'A 0.00 order created a hotel booking from another order\'s product meta.');

	/* 6. A line with booking data but a zero amount cannot create a booking. */
	list($zero_order) = $make_order($product_id, 0, $values($hotel_id, $product_id, '2027-06-01', '2027-06-04', 600), null);
	do_action('woocommerce_order_status_changed', $zero_order->get_id(), 'pending', 'processing', $zero_order);
	$assert(0 === count($bookings_for($zero_order->get_id())), 'A zero-total order created a booking from valid line data.');

	/* 7. Two orders keep their own room/date data even if the product meta is overwritten. */
	list($first_order) = $make_order($product_id, 600, $values($hotel_id, $product_id, '2027-07-01', '2027-07-04', 600), null);
	list($second_order) = $make_order($product_id, 600, $values($hotel_id, $product_id, '2027-08-01', '2027-08-04', 600), null);
	update_post_meta($product_id, '_ttbm_hotel_booking_data', maybe_serialize($payload($hotel_id, '2027-08-01', '2027-08-04', 3, 600)));
	do_action('woocommerce_order_status_changed', $first_order->get_id(), 'pending', 'processing', $first_order);
	do_action('woocommerce_order_status_changed', $second_order->get_id(), 'pending', 'processing', $second_order);
	$first_bookings = $bookings_for($first_order->get_id());
	$second_bookings = $bookings_for($second_order->get_id());
	$assert(1 === count($first_bookings) && '2027-07-01' === get_post_meta($first_bookings[0], '_ttbm_hotel_booking_checkin_date', true), 'The first order received the second order\'s hotel dates.');
	$assert(1 === count($second_bookings) && '2027-08-01' === get_post_meta($second_bookings[0], '_ttbm_hotel_booking_checkin_date', true), 'The second order did not keep its own hotel dates.');

	/* 8. Shared product meta is never authoritative for an order. */
	$legacy_order_payload = $payload($hotel_id, '2027-09-01', '2027-09-04', 3, 600);
	update_post_meta($product_id, '_ttbm_hotel_booking_data', maybe_serialize($legacy_order_payload));
	list($legacy_order) = $make_order($product_id, 600, null, array('September 1, 2027', 'September 4, 2027'));
	do_action('woocommerce_order_status_changed', $legacy_order->get_id(), 'pending', 'processing', $legacy_order);
	$assert(0 === count($bookings_for($legacy_order->get_id())), 'An order without line booking data created a booking from the shared product meta.');

	/* 9. A direct cart line without booking data is rejected by checkout validation. */
	$clear_cart();
	WC()->cart->cart_contents = array('direct-line' => array(
		'data' => wc_get_product($product_id),
		'product_id' => $product_id,
		'variation_id' => 0,
		'quantity' => 1,
	));
	do_action('woocommerce_check_cart_items');
	$assert(wc_notice_count('error') > 0, 'Checkout accepted a direct hotel product line without booking data.');
	$clear_cart();
} finally {
	$_POST = $saved_post;
	$_REQUEST = $saved_request;
	if (WC()->cart) {
		WC()->cart->cart_contents = $saved_cart;
	}
	wc_clear_notices();
	remove_filter('pre_wp_mail', '__return_false');
}
if ($failures) {
	WP_CLI::error(count($failures) . ' of ' . $checks . ' hotel booking checks failed.');
}
WP_CLI::success($checks . ' hotel booking security checks passed.');
