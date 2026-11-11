<?php
/**
 * wp eval-file /absolute/path/to/tests/travel-end-date.php
 * Run with WooCommerce and Tour Free active.
 *
 * The cart, checkout and order line showed "<Tour> End Date" from the Fixed
 * panel's End Date meta for every tour. A tour switched to Repeated or
 * Particular dates keeps that hidden value, so customers saw a date years
 * after their departure. Temporary fixtures are removed even when an
 * assertion fails.
 */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) {
	exit;
}

$checks = 0;
$failures = array();
$posts = array();
$saved_post = $_POST;
$assert = static function ($condition, $message) use (&$checks, &$failures) {
	$checks++;
	if (!$condition) {
		$failures[] = $message;
		WP_CLI::log('FAIL ' . $message);
	}
};
$make_tour = static function ($meta) use (&$posts) {
	$id = wp_insert_post(array('post_type' => TTBM_Function::get_cpt_name(), 'post_status' => 'publish', 'post_title' => 'End date regression fixture'));
	$posts[] = $id;
	$meta += array(
		'ttbm_type' => 'general',
		'ttbm_ticket_type' => array(array('ticket_type_name' => 'Adult', 'ticket_type_qty' => 10, 'ticket_type_price' => 20)),
		// The stale value every tour on the reporting site carried.
		'ttbm_travel_end_date' => '2034-03-31',
	);
	foreach ($meta as $key => $value) {
		update_post_meta($id, $key, $value);
	}
	TTBM_Function::flush_request_cache();
	return $id;
};
$start = gmdate('Y-m-d', strtotime('+10 days'));
$end = gmdate('Y-m-d', strtotime('+17 days'));
$second_start = gmdate('Y-m-d', strtotime('+40 days'));
$second_end = gmdate('Y-m-d', strtotime('+45 days'));
$woocommerce = (new ReflectionClass('TTBM_Woocommerce'))->newInstanceWithoutConstructor();
$render_cart = static function ($tour, $cart_item) use ($woocommerce) {
	ob_start();
	$woocommerce->show_cart_item($cart_item + array('ttbm_id' => $tour), $tour);
	return ob_get_clean();
};
$order_meta = static function ($tour, $values) use ($woocommerce) {
	$item = new WC_Order_Item_Product();
	$woocommerce->checkout_create_order_line_item($item, 'end-date-test', $values + array('ttbm_id' => $tour));
	$meta = array();
	foreach ($item->get_meta_data() as $row) {
		$meta[ $row->key ] = $row->value;
	}
	return $meta;
};
$has_end_label = static function ($keys) {
	foreach ($keys as $key) {
		if (false !== stripos((string) $key, 'End Date') && 0 !== strpos((string) $key, '_')) {
			return true;
		}
	}
	return false;
};

try {
	$repeated = $make_tour(array(
		'ttbm_travel_type' => 'repeated',
		'ttbm_travel_repeated_start_date' => $start,
		'ttbm_travel_repeated_start_time' => '00:00',
		'ttbm_repeat_type' => 'fixed',
		'ttbm_travel_repeated_end_date' => gmdate('Y-m-d', strtotime('+3 years')),
		'ttbm_travel_repeated_after' => '1',
	));
	$fixed = $make_tour(array(
		'ttbm_travel_type' => 'fixed',
		'ttbm_travel_start_date' => $start,
		'ttbm_travel_start_time' => '09:00',
		'ttbm_travel_end_date' => $end,
		'ttbm_travel_end_time' => '18:00',
		'ttbm_travel_reg_end_date' => $start,
	));
	$fixed_no_time = $make_tour(array(
		'ttbm_travel_type' => 'fixed',
		'ttbm_travel_start_date' => $start,
		'ttbm_travel_end_date' => $end,
	));
	$fixed_bad = $make_tour(array(
		'ttbm_travel_type' => 'fixed',
		'ttbm_travel_start_date' => $start,
		'ttbm_travel_end_date' => 'not a date',
	));
	$particular = $make_tour(array(
		'ttbm_travel_type' => 'particular',
		'ttbm_particular_dates' => array(
			array('ttbm_particular_start_date' => $start, 'ttbm_particular_end_date' => $end, 'ttbm_particular_start_time' => '09:00'),
			array('ttbm_particular_start_date' => $second_start, 'ttbm_particular_end_date' => $second_end, 'ttbm_particular_start_time' => '10:00'),
			'malformed row',
		),
	));

	// Helper: one source of truth per date type.
	$assert('' === TTBM_Function::get_travel_end_datetime($repeated, $start . ' 00:00'), 'Repeated tour ignores the hidden Fixed end date');
	$assert($end . ' 18:00' === TTBM_Function::get_travel_end_datetime($fixed, $start . ' 09:00'), 'Fixed tour keeps its end date and time');
	$assert($end === TTBM_Function::get_travel_end_datetime($fixed_no_time, $start), 'Fixed tour without end time returns the date only');
	$assert('' === TTBM_Function::get_travel_end_datetime($fixed_bad, $start), 'Unparseable Fixed end date is dropped, not rendered as "now"');
	$assert($end === TTBM_Function::get_travel_end_datetime($particular, $start . ' 09:00'), 'Particular date uses its own row check-out date');
	$assert($second_end === TTBM_Function::get_travel_end_datetime($particular, $second_start . ' 10:00'), 'Second particular date uses its own row');
	$assert('' === TTBM_Function::get_travel_end_datetime($particular, gmdate('Y-m-d', strtotime('+90 days'))), 'Unknown particular start date has no end date');
	$assert('' === TTBM_Function::get_travel_end_datetime($particular, ''), 'Particular tour without a start date has no end date');
	$assert('' === TTBM_Function::get_travel_end_datetime(0, $start), 'Missing tour has no end date');

	// Booking panel hidden field (feeds both checkout engines).
	foreach (array(array($repeated, $start . ' 00:00', ''), array($fixed, $start . ' 09:00', $end . ' 18:00')) as $case) {
		list($tour, $date, $expected) = $case;
		ob_start();
		(new ReflectionClass('TTBM_Booking'))->newInstanceWithoutConstructor()->booking_panel($tour, $date);
		$html = ob_get_clean();
		$found = preg_match("/name='ttbm_end_date'[^>]*value='([^']*)'/", $html, $match);
		$assert(1 === $found && $expected === $match[1], "Booking panel end date for tour $tour is '$expected'");
	}

	// Add to cart: a posted end date is not trusted.
	$_POST = array('ttbm_form_nonce' => wp_create_nonce('ttbm_form_nonce'), 'ticket_name' => array('Adult'), 'ticket_qty' => array(1), 'ttbm_start_date' => $start . ' 00:00', 'ttbm_end_date' => '2099-01-01');
	$cart_item = $woocommerce->add_cart_item_data(array(), $repeated);
	$assert('' === ($cart_item['ttbm_end_date'] ?? null), 'Repeated add-to-cart stores no end date, even when one is posted');
	$_POST['ttbm_start_date'] = $start . ' 09:00';
	$cart_item = $woocommerce->add_cart_item_data(array(), $fixed);
	$assert($end . ' 18:00' === ($cart_item['ttbm_end_date'] ?? null), 'Fixed add-to-cart stores the schedule end date, not the posted one');
	$_POST = $saved_post;

	// Cart / checkout summary, including carts saved before the fix.
	$stale = array('ttbm_end_date' => '2034-03-31', 'ttbm_ticket_info' => array(array('ticket_name' => 'Adult', 'ticket_qty' => 1, 'ticket_price' => 20)));
	$html = $render_cart($repeated, $stale + array('ttbm_date' => $start . ' 00:00'));
	$assert(false === stripos($html, 'End Date') && false === strpos($html, '2034'), 'Repeated cart line shows no end date, even from a stale cart');
	$html = $render_cart($fixed, $stale + array('ttbm_date' => $start . ' 09:00'));
	$assert(false !== stripos($html, 'End Date') && false === strpos($html, '2034'), 'Fixed cart line shows the schedule end date');
	$assert(false !== strpos($html, esc_html(TTBM_Global_Function::date_format($end . ' 18:00', 'full'))), 'Fixed cart line renders the end date and time');
	$html = $render_cart($particular, $stale + array('ttbm_date' => $second_start . ' 10:00'));
	$assert(false !== strpos($html, esc_html(TTBM_Global_Function::date_format($second_end, 'date'))) && false === strpos($html, '2034'), 'Particular cart line shows its own check-out date');

	// Order line item meta.
	$meta = $order_meta($repeated, $stale + array('ttbm_date' => $start . ' 00:00'));
	$assert(!$has_end_label(array_keys($meta)), 'Repeated order line has no visible end date');
	$assert('' === ($meta['_ttbm_end_date'] ?? null), 'Repeated order line stores an empty raw end date');
	$meta = $order_meta($fixed, $stale + array('ttbm_date' => $start . ' 09:00'));
	$assert($has_end_label(array_keys($meta)), 'Fixed order line keeps its visible end date');
	$assert($end . ' 18:00' === ($meta['_ttbm_end_date'] ?? null), 'Fixed order line stores the schedule end date');
} finally {
	$_POST = $saved_post;
	foreach ($posts as $id) {
		wp_delete_post($id, true);
	}
	TTBM_Function::flush_request_cache();
}

if ($failures) {
	WP_CLI::error(sprintf('%d of %d end-date checks failed.', count($failures), $checks));
}
WP_CLI::success(sprintf('%d end-date checks passed.', $checks));
