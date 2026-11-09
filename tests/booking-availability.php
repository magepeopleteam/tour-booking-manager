<?php
/**
 * wp eval-file /absolute/path/to/tests/booking-availability.php
 * Run with WooCommerce, Tour Free/PRO and Rental Free/PRO active.
 * Temporary fixtures are removed even when an assertion fails.
 */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) {
	exit;
}

$checks = 0;
$failures = array();
$posts = array();
$saved_post = $GLOBALS['post'] ?? null;
$saved_cart = WC()->cart ? WC()->cart->cart_contents : array();
$saved_request = $_POST;
$assert = static function ($condition, $message) use (&$checks, &$failures) {
	$checks++;
	if (!$condition) {
		$failures[] = $message;
		WP_CLI::log('FAIL ' . $message);
	}
};
$make_post = static function ($type, $meta) use (&$posts) {
	$id = wp_insert_post(array('post_type' => $type, 'post_status' => 'publish', 'post_title' => 'Availability regression fixture'));
	$posts[] = $id;
	foreach ($meta as $key => $value) {
		update_post_meta($id, $key, $value);
	}
	return $id;
};
$settings = get_option('ttbm_basic_gen_settings');
$settings_filter = static function () use ($settings) {
	$value = is_array($settings) ? $settings : array();
	$value['ttbm_set_book_status'] = array('processing', 'completed');
	return $value;
};
add_filter('pre_option_ttbm_basic_gen_settings', $settings_filter);
add_filter('pre_wp_mail', '__return_false');
$day = gmdate('Y-m-d', strtotime('+12 days'));

try {
	$assert(post_type_exists('ttbm_service_booking'), 'Service booking post type registered by the plugin');
	$tour = $make_post(TTBM_Function::get_cpt_name(), array(
		'ttbm_tour_type' => 'general',
		'ttbm_travel_type' => 'repeated',
		'ttbm_ticket_type' => array(array('ticket_type_name' => 'Adult', 'ticket_type_qty' => 1, 'ticket_type_price' => 20)),
	));
	$booking = $make_post('ttbm_booking', array(
		'ttbm_id' => $tour, 'ttbm_date' => $day . ' 10:00',
		'ttbm_ticket_name' => 'Adult', 'ttbm_ticket_qty' => 1,
		'ttbm_order_id' => 0, 'ttbm_order_status' => 'pending',
	));
	$service = $make_post('ttbm_service_booking', array(
		'ttbm_id' => $tour, 'ttbm_date' => $day . ' 10:00',
		'ttbm_service_name' => 'Bike', 'ttbm_service_qty' => 1, 'ttbm_order_status' => 'pending',
	));
	foreach (array('pending', 'on-hold', 'processing', 'completed', 'failed', 'cancelled', 'refunded') as $status) {
		update_post_meta($booking, 'ttbm_order_status', $status);
		update_post_meta($service, 'ttbm_order_status', $status);
		$held = in_array($status, array('pending', 'on-hold', 'processing', 'completed'), true) ? 1 : 0;
		TTBM_Function::flush_request_cache();
		$assert($held === TTBM_Function::get_total_sold($tour, $day . ' 10:00'), "$status: sold seats before priming");
		$assert(1 - $held === (int) TTBM_Function::get_total_available($tour, $day . ' 10:00'), "$status: available seats");
		$assert($held === (int) TTBM_Query::query_all_service_sold($tour, $day . ' 10:00', 'Bike'), "$status: service inventory");
		TTBM_Function::flush_request_cache();
		TTBM_Function::prime_sold_cache(array($tour));
		$assert($held === TTBM_Function::get_total_sold($tour, $day . ' 10:00'), "$status: primed sold seats");
		$assert(0 === TTBM_Function::get_total_sold($tour, $day . ' 12:00'), "$status: other departure stays available");
	}
	update_post_meta($booking, 'ttbm_order_status', 'processing');
	update_post_meta($service, 'ttbm_order_status', 'processing');
	foreach (array('00:00', '10:00', '10:00:00') as $stored_time) {
		update_post_meta($booking, 'ttbm_date', $day . ' ' . $stored_time);
		update_post_meta($service, 'ttbm_date', $day . ' ' . $stored_time);
		$time = substr($stored_time, 0, 5);
		$other_time = '00:00' === $time ? '10:00' : '00:00';
		foreach (array(false, true) as $primed) {
			TTBM_Function::flush_request_cache();
			if ($primed) {
				TTBM_Function::prime_sold_cache(array($tour));
			}
			$assert(1 === TTBM_Function::get_total_sold($tour, $day . ' ' . $time), "$stored_time: own departure counted (primed=$primed)");
			$assert(0 === TTBM_Function::get_total_sold($tour, $day . ' ' . $other_time), "$stored_time: midnight and daytime pools isolated (primed=$primed)");
			$assert(1 === (int) TTBM_Function::get_ticket_availability_info($tour, $day . ' ' . $other_time)['Adult']['available_qty'], "$stored_time: ticket table isolates midnight (primed=$primed)");
			$assert(1 === TTBM_Function::get_total_sold($tour, $day), "$stored_time: whole-day count includes all slots (primed=$primed)");
		}
		$assert(1 === (int) TTBM_Query::query_all_service_sold($tour, $day . ' ' . $time, 'Bike'), "$stored_time: service date format agrees");
	}

	// Ordinary (non-shared) tickets also need a server-side stock gate.
	update_post_meta($booking, 'ttbm_date', $day . ' 10:00');
	$ticket = array('ticket_name' => 'Adult', 'ticket_qty' => 1, 'ticket_price' => 20, 'ttbm_date' => $day . ' 10:00');
	$product = new WC_Product_Simple();
	$product->set_name('Availability regression hidden product');
	$product->set_status('publish');
	$product->set_regular_price(20);
	$product->save();
	$posts[] = $product->get_id();
	update_post_meta($product->get_id(), 'link_ttbm_id', $tour);
	update_post_meta($tour, 'link_wc_product', $product->get_id());
	$_POST = array('ttbm_form_nonce' => wp_create_nonce('ttbm_form_nonce'), 'ttbm_id' => $tour, 'ticket_name' => array('Adult'), 'ticket_qty' => array(1), 'ttbm_start_date' => $day . ' 10:00');
	$woocommerce = (new ReflectionClass('TTBM_Woocommerce'))->newInstanceWithoutConstructor();
	wc_clear_notices();
	$assert(false === $woocommerce->validate_add_to_cart(true, $product->get_id()), 'Sold-out ordinary ticket rejected at add-to-cart');
	wc_clear_notices();
	if (method_exists('TTBM_Function', 'validate_ticket_availability')) {
		$assert(is_wp_error(TTBM_Function::validate_ticket_availability($tour, array($ticket))), 'Stale ordinary ticket rejected by common checkout validator');
		$ticket['ttbm_date'] = $day . ' 12:00';
		$assert(true === TTBM_Function::validate_ticket_availability($tour, array($ticket)), 'Other departure accepted by common checkout validator');
		$assert(is_wp_error(TTBM_Function::validate_ticket_availability($tour, array($ticket, $ticket))), 'Duplicate ticket rows cannot bypass capacity');
		$assert(is_wp_error(TTBM_Function::validate_ticket_availability($tour, array($ticket, array_merge($ticket, array('ticket_name' => 'Unknown'))))), 'Unknown ticket type rejected');
		if (WC()->cart) {
			$cart_base = array('data' => $product, 'product_id' => $product->get_id(), 'variation_id' => 0, 'quantity' => 1);
			WC()->cart->cart_contents = array('availability-test' => $cart_base + array('ttbm_id' => $tour, 'ttbm_date' => $day . ' 10:00', 'ttbm_ticket_info' => array(array_merge($ticket, array('ttbm_date' => $day . ' 10:00')))));
			wc_clear_notices();
			$woocommerce->after_checkout_validation();
			$assert(wc_notice_count('error') > 0, 'Stale cart rejected at checkout');
			wc_clear_notices();
			do_action('woocommerce_check_cart_items');
			$expected = TTBM_Function::validate_ticket_availability($tour, array(array_merge($ticket, array('ttbm_date' => $day . ' 10:00'))));
			$assert(is_wp_error($expected) && in_array($expected->get_error_message(), array_column(wc_get_notices('error'), 'notice'), true), 'Stale cart rejected by the WooCommerce cart inventory hook');
			WC()->cart->cart_contents = array('availability-test-a' => $cart_base + array('ttbm_id' => $tour, 'ttbm_ticket_info' => array($ticket)), 'availability-test-b' => $cart_base + array('ttbm_id' => $tour, 'ttbm_ticket_info' => array($ticket)));
			wc_clear_notices();
			$woocommerce->after_checkout_validation();
			$assert(wc_notice_count('error') > 0, 'Two cart lines share the same inventory limit');
			WC()->cart->cart_contents = array();
			wc_clear_notices();
		}
		update_post_meta($tour, 'ttbm_enable_timewise_stock', 'on');
		update_post_meta($tour, 'mep_ticket_times_global', array(array('mep_ticket_time' => '12:00', 'mep_ticket_time_stock' => 1)));
		TTBM_Function::flush_request_cache();
		$assert(is_wp_error(TTBM_Function::validate_ticket_availability($tour, array($ticket, $ticket))), 'Timewise stock enforced by common validator');
		update_post_meta($tour, 'ttbm_enable_timewise_stock', 'off');
		update_post_meta($tour, 'ttbm_display_global_qty', 'on');
		update_post_meta($tour, 'ttbm_global_qty', 1);
		update_post_meta($tour, 'ttbm_ticket_type', array(
			array('ticket_type_name' => 'Adult', 'ticket_type_qty' => 1, 'ticket_type_price' => 20),
			array('ticket_type_name' => 'Child', 'ticket_type_qty' => 1, 'ticket_type_price' => 10),
		));
		TTBM_Function::flush_request_cache();
		if (class_exists('TTBM_Function_PRO')) {
			$child = array_merge($ticket, array('ticket_name' => 'Child'));
			$assert(is_wp_error(TTBM_Function::validate_ticket_availability($tour, array($ticket, $child))), 'Adult/Child share the PRO capacity pool');
		}
		update_post_meta($tour, 'ttbm_display_global_qty', 'off');
		TTBM_Function::flush_request_cache();
		$native_order = $make_post('ttbm_custom_order', array(
			'_ttbm_tour_id' => $tour, '_ttbm_ticket_info' => array($ticket),
			'_ttbm_hotel_info' => array(), '_ttbm_service_info' => array(), '_ttbm_user_info' => array(),
			'_ttbm_customer_name' => 'Availability fixture', '_ttbm_customer_email' => 'availability@example.test',
		));
		$checkout = TTBM_Custom_Checkout::instance();
		$checkout->finalize_order($native_order, 'pending', 'offline');
		$native_bookings = (array) get_post_meta($native_order, '_ttbm_booking_ids', true);
		$posts = array_merge($posts, $native_bookings);
		$assert(1 === count($native_bookings), 'Offline checkout creates a real booking');
		$assert(0 === (int) TTBM_Function::get_ticket_availability_info($tour, $day . ' 12:00')['Adult']['available_qty'], 'Offline pending booking holds its seat');
		$_POST['ttbm_start_date'] = $day . ' 12:00';
		$process = new ReflectionMethod('TTBM_Custom_Checkout', 'process_booking');
		$process->setAccessible(true);
		$assert(is_wp_error($process->invoke($checkout, $tour)), 'Standalone checkout refuses a second sold-out booking');
		TTBM_Custom_Checkout::propagate_status($native_order, 'cancelled');
		$assert(1 === (int) TTBM_Function::get_ticket_availability_info($tour, $day . ' 12:00')['Adult']['available_qty'], 'Cancelling offline booking releases its seat');
	} else {
		$assert(false, 'Common checkout availability validator exists');
	}
	$assert(array('processing', 'completed') === TTBM_Function::get_general_settings('ttbm_set_book_status'), 'Inventory holds do not change ticket confirmation settings');
} finally {
	$_POST = $saved_request;
	if (WC()->cart) {
		WC()->cart->cart_contents = $saved_cart;
	}
	$GLOBALS['post'] = $saved_post;
	if (!empty($tour)) {
		$posts = array_merge($posts, get_posts(array('post_type' => array('ttbm_booking', 'ttbm_service_booking'), 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => -1, 'meta_key' => 'ttbm_id', 'meta_value' => $tour)));
	}
	foreach (array_reverse($posts) as $id) {
		$linked = (int) get_post_meta($id, 'link_wc_product', true);
		wp_delete_post($id, true);
		if ($linked && !in_array($linked, $posts, true)) {
			wp_delete_post($linked, true);
		}
	}
	remove_filter('pre_option_ttbm_basic_gen_settings', $settings_filter);
	remove_filter('pre_wp_mail', '__return_false');
	wc_clear_notices();
	TTBM_Function::flush_request_cache();
}
$assert($settings === get_option('ttbm_basic_gen_settings'), 'Stored settings unchanged');
if ($failures) {
	WP_CLI::error(count($failures) . " of $checks checks failed.");
}
WP_CLI::success("$checks checks passed.");
