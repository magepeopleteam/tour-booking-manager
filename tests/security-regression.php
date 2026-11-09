<?php
/**
 * Run with WP-CLI, with application plugins disabled:
 * wp --skip-plugins --skip-themes eval-file wp-content/plugins/tour-booking-manager/tests/security-regression.php
 * Set TTBM_TEST_PRO_CHECKOUT=1 to test the PRO checkout implementation as well.
 * Fixtures run in a rolled-back transaction; outbound HTTP is mocked.
 */
if (!defined('ABSPATH') || !defined('WP_CLI') || !WP_CLI) {
	exit;
}
if (!defined('DOING_AJAX')) {
	define('DOING_AJAX', true);
}

$ttbm_test_root = dirname(__DIR__);
$ttbm_test_pro = dirname($ttbm_test_root) . '/tour-booking-manager-pro';
set_error_handler(static function ($severity, $message, $file, $line) use ($ttbm_test_root, $ttbm_test_pro) {
	if ((error_reporting() & $severity) && (0 === strpos($file, $ttbm_test_root . '/') || 0 === strpos($file, $ttbm_test_pro . '/'))) {
		throw new ErrorException($message, 0, $severity, $file, $line);
	}
	return false;
});
define('TTBM_PLUGIN_DIR', $ttbm_test_root);
define('TTBM_PLUGIN_DIR_PRO', $ttbm_test_pro);
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once dirname($ttbm_test_root) . '/woocommerce/includes/wc-formatting-functions.php';
require_once $ttbm_test_root . '/inc/TTBM_Global_Function.php';
require_once $ttbm_test_root . '/inc/TTBM_Function.php';
require_once $ttbm_test_root . '/inc/TTBM_Query.php';
require_once $ttbm_test_root . '/inc/TTBM_Security.php';
require_once $ttbm_test_root . '/admin/TTBM_CPT.php';
require_once $ttbm_test_root . '/inc/TTBM_Custom_Order_CPT.php';
require_once $ttbm_test_root . '/inc/TTBM_Woocommerce.php';
require_once $ttbm_test_root . '/admin/TTBM_Payment_Settings.php';
require_once $ttbm_test_pro . '/inc/TTBM_Function_PRO.php';
require_once $ttbm_test_pro . '/admin/TTBM_Pro_CSV.php';
require_once $ttbm_test_pro . '/admin/TTBM_Booking_List.php';
require_once $ttbm_test_pro . '/inc/TTBM_Review_Rating.php';
require_once $ttbm_test_pro . '/admin/TTBM_Attendee_List.php';
require_once $ttbm_test_pro . '/admin/TTBM_Form_Builder_Settings.php';
require_once $ttbm_test_pro . '/admin/TTBM_PRO_License.php';
require_once $ttbm_test_pro . '/inc/gateways/TTBM_Payment_Gateway_Manager.php';
TTBM_Payment_Gateway_Manager::get_instance();
require_once (getenv('TTBM_TEST_PRO_CHECKOUT') ? $ttbm_test_pro : $ttbm_test_root) . '/inc/TTBM_Custom_Checkout.php';

$ttbm_test_checks = 0;
$ttbm_test_assert = static function ($condition, $message) use (&$ttbm_test_checks) {
	if (!$condition) {
		throw new RuntimeException($message);
	}
	$ttbm_test_checks++;
};
$ttbm_test_user = static function ($role) {
	$user = new WP_User();
	$user->ID = 'administrator' === $role ? 999999 : ('subscriber' === $role ? 999998 : 0);
	$user->allcaps = 'administrator' === $role ? array('manage_options' => true, 'read' => true) : array('read' => true);
	$GLOBALS['current_user'] = $user;
};
$ttbm_test_die = static function () {
	return static function ($message) { throw new RuntimeException('request_rejected'); };
};
add_filter('wp_die_handler', $ttbm_test_die);
add_filter('wp_die_ajax_handler', $ttbm_test_die);
$ttbm_test_rejected = static function ($callback) use ($ttbm_test_assert) {
	try {
		$callback();
	} catch (RuntimeException $error) {
		$ttbm_test_assert('request_rejected' === $error->getMessage(), 'Unexpected request failure: ' . $error->getMessage());
		return;
	}
	throw new RuntimeException('Unsafe request was accepted.');
};

global $wpdb;
$wpdb->query('START TRANSACTION');
register_shutdown_function(static function () use ($wpdb) { $wpdb->query('ROLLBACK'); });
$ttbm_test_temp = sys_get_temp_dir() . '/ttbm-security-' . wp_generate_uuid4();
wp_mkdir_p($ttbm_test_temp . '/child/ttbm_templates/themes');
wp_mkdir_p($ttbm_test_temp . '/parent/ttbm_templates/themes');
$ttbm_test_child = static function () use ($ttbm_test_temp) { return $ttbm_test_temp . '/child'; };
$ttbm_test_parent = static function () use ($ttbm_test_temp) { return $ttbm_test_temp . '/parent'; };
add_filter('stylesheet_directory', $ttbm_test_child);
add_filter('template_directory', $ttbm_test_parent);
try {
	(new ReflectionClass('TTBM_CPT'))->newInstanceWithoutConstructor()->ttbm_cpt();
	$service_type = get_post_type_object('ttbm_service_booking');
	$ttbm_test_assert($service_type && !$service_type->public && !$service_type->show_in_rest && !$service_type->show_ui, 'Service booking records are not registered privately.');
	$ttbm_test_assert('manage_options' === $service_type->cap->edit_post, 'Service booking records lack the booking-screen permission.');
	TTBM_Custom_Order_CPT::register_cpt();
	$tour_id = wp_insert_post(array('post_type' => 'ttbm_tour', 'post_status' => 'publish', 'post_title' => 'Security fixture'));
	$order_id = wp_insert_post(array('post_type' => 'ttbm_custom_order', 'post_status' => 'pending', 'post_title' => 'Security fixture order'));
	$GLOBALS['post'] = get_post($tour_id);

	$default = realpath($ttbm_test_root . '/templates/themes/default.php');
	$hotel_default = realpath($ttbm_test_root . '/templates/themes/hotel_default.php');
	$ttbm_test_assert($default === TTBM_Function::details_template_path(), 'Default tour template failed.');
	$ttbm_test_assert($hotel_default === TTBM_Function::details_template_file_path($tour_id), 'Default hotel template failed.');
	$ttbm_test_assert(is_protected_meta('ttbm_theme_file', 'post'), 'Core custom fields can overwrite the tour template selector.');
	$ttbm_test_assert(is_protected_meta('ttbm_hotel_template', 'post'), 'Core custom fields can overwrite the hotel template selector.');
	$ttbm_test_assert(!is_protected_meta('ordinary_custom_field', 'post'), 'Unrelated custom fields were hidden.');
	file_put_contents($ttbm_test_temp . '/canary.gif', '<?php throw new RuntimeException("outside template executed");');
	$attacks = array('../../../../etc/passwd', $ttbm_test_temp . '/canary.gif', '../../../../uploads/canary.gif', '..\\canary.php', 'php://filter/resource=default.php', "default.php\0.gif", array('default.php'), 'missing.php');
	foreach ($attacks as $attack) {
		update_post_meta($tour_id, 'ttbm_theme_file', $attack);
		update_post_meta($tour_id, 'ttbm_hotel_template', $attack);
		$ttbm_test_assert($default === TTBM_Function::details_template_path(), 'Tour traversal was not rejected.');
		$ttbm_test_assert($hotel_default === TTBM_Function::details_template_file_path($tour_id), 'Hotel traversal was not rejected.');
	}
	file_put_contents($ttbm_test_temp . '/child/ttbm_templates/themes/custom.php', '<?php /* Template Name: Custom */');
	file_put_contents($ttbm_test_temp . '/parent/ttbm_templates/themes/parent.php', '<?php /* Template Name: Parent */');
	file_put_contents($ttbm_test_temp . '/canary.php', '<?php /* outside template */');
	symlink($ttbm_test_temp . '/canary.php', $ttbm_test_temp . '/child/ttbm_templates/themes/escape.php');
	$ttbm_test_assert('' === TTBM_Function::template_path('themes/escape.php'), 'Escaping symlink was accepted.');
	$ttbm_test_assert(!isset(TTBM_Function::all_details_template()['escape.php']), 'Escaping symlink entered the template list.');
	foreach (array('custom.php' => 'child', 'parent.php' => 'parent') as $name => $theme) {
		update_post_meta($tour_id, 'ttbm_theme_file', $name);
		update_post_meta($tour_id, 'ttbm_hotel_template', $name);
		$expected = realpath($ttbm_test_temp . '/' . $theme . '/ttbm_templates/themes/' . $name);
		$ttbm_test_assert($expected === TTBM_Function::details_template_path(), 'Theme tour override failed.');
		$ttbm_test_assert($expected === TTBM_Function::details_template_file_path($tour_id), 'Theme hotel override failed.');
	}
	$ttbm_test_assert('' === TTBM_Function_PRO::template_path('../canary.php'), 'PRO traversal was accepted.');
	$ttbm_test_assert(is_file(TTBM_Function_PRO::template_path('attendee/filter.php')), 'PRO template failed.');
	$ttbm_test_assert(is_file(TTBM_Function::template_path('ticket/book_now.php')), 'Shared ticket template failed.');
	$ttbm_test_assert(array() === TTBM_Function::get_meta_values("missing' OR 1=1 --", 'ttbm_tour'), 'SQL-like meta input altered the query.');
	$wpdb->insert($wpdb->prefix . 'woocommerce_order_items', array('order_item_name' => 'Fixture ticket', 'order_item_type' => 'line_item', 'order_id' => $order_id));
	$item_id = $wpdb->insert_id;
	foreach (array('_ttbm_id' => $tour_id, '_ttbm_date' => '2027-01-01', '_ttbm_ticket_info' => maybe_serialize(array(array('ticket_name' => 'Adult', 'ticket_qty' => 1)))) as $key => $value) {
		$wpdb->insert($wpdb->prefix . 'woocommerce_order_itemmeta', array('order_item_id' => $item_id, 'meta_key' => $key, 'meta_value' => $value));
	}
	$calendar_method = new ReflectionMethod('TTBM_Booking_List', 'build_events');
	$events = $calendar_method->invoke(new TTBM_Booking_List());
	$ttbm_test_assert(is_array($events) && '' === $wpdb->last_error, 'Calendar SQL or order hydration failed.');

	register_post_type('ttbm_booking', array('public' => true, 'show_in_rest' => true));
	rest_get_server();
	foreach (array('', 'subscriber') as $role) {
		$ttbm_test_user($role);
		$request = new WP_REST_Request('GET', '/wp/v2/ttbm_booking');
		$ttbm_test_assert(is_wp_error(TTBM_Security::rest_access(null, null, $request)), 'Private REST records were exposed.');
		$ttbm_test_assert(is_wp_error(TTBM_Security::rest_access(null, null, new WP_REST_Request('GET', '/wp/v2/TTBM_BOOKING/1'))), 'Case-insensitive REST routing bypassed authorization.');
		$ttbm_test_assert(in_array(rest_do_request($request)->get_status(), array(401, 403), true), 'Actual REST dispatch exposed private bookings.');
		$ttbm_test_assert(!current_user_can('edit_post', $order_id), 'A low-privilege user can edit orders.');
		$ttbm_test_assert(null === TTBM_Security::rest_access(null, null, new WP_REST_Request('GET', '/wp/v2/ttbm_tour')), 'Public tours were blocked.');
	}
	$ttbm_test_user('administrator');
	$ttbm_test_assert(current_user_can('edit_post', $order_id), 'Administrator order access failed.');
	$ttbm_test_assert(null === TTBM_Security::rest_access(null, null, $request), 'Administrator REST access failed.');
	$ttbm_test_assert(200 === rest_do_request($request)->get_status(), 'Actual administrator REST dispatch failed.');
	$ttbm_test_assert(!get_post_type_object('ttbm_booking')->publicly_queryable, 'Booking archive remains public.');
	$review = new TTBM_Review_Rating();
	update_option('ttbm_review_permission_settings', array('ttbm_review_permission' => 'loggedin'));
	$ttbm_test_user('');
	$ttbm_test_assert(!$review->check_review_permission($tour_id), 'Guest bypassed logged-in-only reviews.');
	$ttbm_test_user('subscriber');
	$ttbm_test_assert($review->check_review_permission($tour_id), 'Logged-in reviews were blocked.');
	update_option('ttbm_review_permission_settings', array('ttbm_review_permission' => 'verified'));
	$ttbm_test_assert(!$review->check_review_permission($tour_id), 'An unverified account bypassed verified-only reviews.');
	update_option('ttbm_review_permission_settings', array('ttbm_review_permission' => 'all'));
	$ttbm_test_user('');
	$ttbm_test_assert($review->check_review_permission($tour_id), 'Open guest reviews were blocked.');
	$ttbm_test_user('administrator');
	$review->ttbm_review_post_type();
	$review_id = wp_insert_post(array('post_type' => 'ttbm_tour_review', 'post_status' => 'publish', 'post_title' => 'Fixture review'));
	update_post_meta($review_id, 'ttbm_tour_review_cust_name', '<svg onload="alert(1)">');
	ob_start();
	$review->show_rating_column_data('user_name', $review_id);
	$reviewer_html = ob_get_clean();
	$ttbm_test_assert(false === strpos($reviewer_html, '<svg') && false !== strpos($reviewer_html, '&lt;svg'), 'Stored reviewer markup was executed in the admin list.');

	$_REQUEST = $_POST = array();
	foreach (array(array(new TTBM_Attendee_List(), 'ttbm_delete_attendee'), array(new TTBM_Attendee_List(), 'save_ttbm_attendee_list_theme'), array(new TTBM_Form_Builder_Settings(), 'save_mp_form_reg_data'), array(new TTBM_PRO_License(), 'ttbm_pro_license_key_inactive'), array(new TTBM_PRO_License(), 'ttbm_pro_license_key_active')) as $callback) {
		$ttbm_test_rejected($callback);
	}
	$ttbm_test_user('subscriber');
	foreach (array(array(new TTBM_Attendee_List(), 'ttbm_delete_attendee', 'ttbm_pro_admin'), array(new TTBM_Form_Builder_Settings(), 'save_mp_form_reg_data', 'ttbm_fbm_nonce'), array(new TTBM_PRO_License(), 'ttbm_pro_license_key_inactive', 'ttbm_pro_admin')) as $case) {
		$_REQUEST = $_POST = array('nonce' => wp_create_nonce($case[2]));
		$ttbm_test_rejected(array($case[0], $case[1]));
	}
	$ttbm_test_user('administrator');
	$ttbm_test_assert("'=SUM(1,2)" === TTBM_Pro_CSV::csv_safe_value('=SUM(1,2)'), 'CSV formula injection was accepted.');
	$ttbm_test_assert("'\t+123" === TTBM_Pro_CSV::csv_safe_value("\t+123"), 'CSV control-prefix injection was accepted.');
	$ttbm_test_assert('Alice' === TTBM_Pro_CSV::csv_safe_value('Alice'), 'Normal CSV value changed.');

	$total_method = new ReflectionMethod('TTBM_Custom_Checkout', 'calculate_total');
	$checkout = TTBM_Custom_Checkout::instance();
	$valid_ticket = array(array('ticket_name' => 'Adult', 'ticket_price' => '10.00', 'ticket_qty' => 2));
	$ttbm_test_assert(20.0 === $total_method->invoke($checkout, $tour_id, $valid_ticket, array(), array()), 'Ordinary ticket total changed.');
	$ttbm_test_assert(60.0 === $total_method->invoke($checkout, $tour_id, $valid_ticket, array('hotel_id' => 1, 'ttbm_hotel_num_of_day' => 3), array()), 'Hotel-night total failed.');
	foreach (array('', -1, INF, array('10')) as $price) {
		$invalid_ticket = array(array('ticket_price' => $price, 'ticket_qty' => 1));
		$ttbm_test_assert(is_wp_error($total_method->invoke($checkout, $tour_id, $invalid_ticket, array(), array())), 'Invalid ticket price became a free booking.');
	}
	$ttbm_test_assert(0.0 === $total_method->invoke($checkout, $tour_id, array(array('ticket_price' => 0, 'ticket_qty' => 1)), array(), array()), 'A valid free ticket was blocked.');
	$slot_method = new ReflectionMethod('TTBM_Function_PRO', 'requested_slot_tour_id');
	$slots = new TTBM_Function_PRO();
	$ttbm_test_user('');
	$_REQUEST = array('nonce' => wp_create_nonce('ttbm_frontend_nonce'), 'tour_id' => $tour_id);
	$ttbm_test_assert($tour_id === $slot_method->invoke($slots), 'Published tour slots were blocked.');
	wp_update_post(array('ID' => $tour_id, 'post_status' => 'draft'));
	$ttbm_test_rejected(static function () use ($slot_method, $slots) { $slot_method->invoke($slots); });
	$ttbm_test_user('administrator');
	$_REQUEST['nonce'] = wp_create_nonce('ttbm_pro_admin');
	$ttbm_test_assert($tour_id === $slot_method->invoke($slots), 'Admin draft tour slots were blocked.');
	wp_update_post(array('ID' => $tour_id, 'post_status' => 'publish'));
	$_POST = array('ttbm_form_nonce' => wp_create_nonce('ttbm_form_nonce'), 'ttbm_tour_hotel_list' => '1', 'ttbm_checkin_date' => '2027-01-01', 'ttbm_checkout_date' => '2027-01-04', 'ttbm_hotel_num_of_day' => '0');
	$ttbm_test_assert(3 === TTBM_Woocommerce::cart_hotel_info()['ttbm_hotel_num_of_day'], 'WooCommerce trusted a forged hotel-night count.');
	$_POST['ttbm_checkout_date'] = '2026-12-31';
	$ttbm_test_assert(array() === TTBM_Woocommerce::cart_hotel_info(), 'Reversed hotel dates were accepted.');
	$normalize = new ReflectionMethod('TTBM_Woocommerce', 'normalize_price');
	$ttbm_test_assert(null === $normalize->invoke(null, ''), 'An unknown WooCommerce ticket became free.');
	$ttbm_test_assert(null === $normalize->invoke(null, -1), 'Negative WooCommerce pricing was accepted.');
	$ttbm_test_assert(0.0 === $normalize->invoke(null, '0'), 'A free WooCommerce ticket was blocked.');

	// Real standalone order creation with a local-only offline gateway.
	$offline = new class extends TTBM_Abstract_Payment_Gateway {
		protected function init_gateway() { $this->id = 'offline'; $this->title = 'Fixture'; }
		public function is_enabled() { return true; }
		public function process_payment($data) { return true; }
		public function verify_payment($id) { return false; }
	};
	TTBM_Payment_Gateway_Manager::get_instance()->register_gateway($offline);
	add_filter('pre_wp_mail', '__return_true');
	$ttbm_test_user('');
	update_post_meta($tour_id, 'ttbm_ticket_type', array(array('ticket_type_name' => 'Adult', 'ticket_type_price' => 10, 'ticket_type_qty' => 100)));
	$_POST = array('ticket_name' => array('Adult'), 'ticket_qty' => array('2'), 'ttbm_billing_name' => 'Fixture Guest', 'ttbm_billing_email' => 'fixture@example.invalid', 'ttbm_billing_phone' => '123', 'ttbm_start_date' => '2027-01-01', 'ttbm_payment_method' => 'offline');
	$process = new ReflectionMethod('TTBM_Custom_Checkout', 'process_booking');
	$booking = $process->invoke($checkout, $tour_id);
	$ttbm_test_assert(is_array($booking) && 'pending' === $booking['state'], 'Valid offline checkout failed.');
	$ttbm_test_assert(20.0 === (float) get_post_meta($booking['order_id'], '_ttbm_order_total', true), 'Offline checkout total changed.');
	// Legacy/imported orders may omit optional collections or contain broken rows.
	$valid_ticket = array('ticket_name' => 'Adult', 'ticket_price' => 10, 'ticket_qty' => 2, 'ttbm_date' => '2027-01-02 10:00');
	$valid_service = array('service_name' => 'Bike', 'service_price' => 5, 'service_qty' => 1, 'ttbm_date' => '2027-01-02 10:00');
	foreach (array('missing', 'empty', 'string', 'mixed') as $case) {
		$legacy_order = wp_insert_post(array('post_type' => 'ttbm_custom_order', 'post_status' => 'pending', 'post_title' => 'Legacy collection fixture'));
		update_post_meta($legacy_order, '_ttbm_tour_id', $tour_id);
		update_post_meta($legacy_order, '_ttbm_ticket_info', array($valid_ticket, '', array('ticket_qty' => 1), array('ticket_name' => 'Bad', 'ticket_price' => array(), 'ticket_qty' => 1)));
		update_post_meta($legacy_order, '_ttbm_customer_name', 'Fixture Guest');
		if ('missing' !== $case) {
			$value = 'empty' === $case ? '' : ('string' === $case ? 'invalid legacy value' : array());
			foreach (array('_ttbm_hotel_info', '_ttbm_service_info', '_ttbm_user_info') as $meta_key) {
				update_post_meta($legacy_order, $meta_key, $value);
			}
		}
		if ('mixed' === $case) {
			update_post_meta($legacy_order, '_ttbm_service_info', array('', array(), array('service_name' => 'Bad'), array('service_name' => 'Bad', 'service_price' => array(), 'service_qty' => 1), $valid_service));
		}
		$checkout->finalize_order($legacy_order, 'pending', 'offline');
		$guest_ids = (array) get_post_meta($legacy_order, '_ttbm_booking_ids', true);
		$ttbm_test_assert(2 === count($guest_ids), "$case optional meta: valid guests were not retained.");
		$service_ids = get_posts(array('post_type' => 'ttbm_service_booking', 'fields' => 'ids', 'numberposts' => -1, 'meta_key' => 'ttbm_order_id', 'meta_value' => $legacy_order));
		$ttbm_test_assert(('mixed' === $case ? 1 : 0) === count($service_ids), "$case optional meta: invalid service rows created records.");
		$ttbm_test_user('administrator');
		foreach ($service_ids as $service_id) {
			$ttbm_test_assert(current_user_can('edit_post', $service_id), 'Administrator cannot manage a service booking.');
		}
		$ttbm_test_user('subscriber');
		foreach ($service_ids as $service_id) {
			$ttbm_test_assert(!current_user_can('edit_post', $service_id), 'A customer can edit another service booking.');
		}
		$ttbm_test_user('');
		$checkout->finalize_order($legacy_order, 'processing', 'offline');
		$ttbm_test_assert($guest_ids === get_post_meta($legacy_order, '_ttbm_booking_ids', true), "$case optional meta: a replay duplicated guests.");
		$ttbm_test_assert('processing' === get_post_meta($guest_ids[0], 'ttbm_order_status', true), "$case optional meta: status propagation failed.");
	}
	$_POST['ticket_name'] = array('Does not exist');
	$ttbm_test_assert(is_wp_error($process->invoke($checkout, $tour_id)), 'Unknown ticket type became a free order.');
	wp_update_post(array('ID' => $tour_id, 'post_status' => 'draft'));
	$ttbm_test_assert(is_wp_error($process->invoke($checkout, $tour_id)), 'A draft tour could be booked.');
	wp_update_post(array('ID' => $tour_id, 'post_status' => 'private'));
	$ttbm_test_assert(is_wp_error($process->invoke($checkout, $tour_id)), 'A guest could book a private tour.');
	$ttbm_test_user('administrator');
	$GLOBALS['current_user']->allcaps['read_private_posts'] = true;
	$_POST['ticket_name'] = array('Adult');
	$private_booking = $process->invoke($checkout, $tour_id);
	$ttbm_test_assert(is_array($private_booking), 'An authorized user could no longer book a private tour.');
	wp_update_post(array('ID' => $tour_id, 'post_status' => 'publish'));
	$hotel_id = wp_insert_post(array('post_type' => 'ttbm_hotel', 'post_status' => 'publish', 'post_title' => 'Fixture hotel'));
	update_post_meta($tour_id, 'ttbm_type', 'hotel');
	update_post_meta($tour_id, 'ttbm_hotels', array($hotel_id));
	update_post_meta($hotel_id, 'ttbm_room_details', array(array('ttbm_hotel_room_name' => 'Standard', 'ttbm_hotel_room_price' => 25)));
	$_POST['ticket_name'] = array('Standard');
	$_POST['ttbm_tour_hotel_list'] = $hotel_id;
	$_POST['ttbm_checkin_date'] = '2027-01-01';
	$_POST['ttbm_checkout_date'] = '2027-01-04';
	$_POST['ttbm_hotel_num_of_day'] = '0';
	$hotel_booking = $process->invoke($checkout, $tour_id);
	$ttbm_test_assert(is_array($hotel_booking), 'A valid hotel booking was blocked.');
	$ttbm_test_assert(150.0 === (float) get_post_meta($hotel_booking['order_id'], '_ttbm_order_total', true), 'Forged hotel-night count reduced the order total.');
	$_POST['ttbm_tour_hotel_list'] = $hotel_id + 99999;
	$ttbm_test_assert(is_wp_error($process->invoke($checkout, $tour_id)), 'A hotel outside the tour could be booked.');
	$ttbm_test_user('');
	update_option('default_role', 'administrator');
	$signup_email = 'ttbm-security-' . wp_generate_uuid4() . '@example.invalid';
	$_REQUEST = $_POST = array('nonce' => wp_create_nonce('ttbm_login_gate'), 'user_email' => $signup_email, 'user_name' => 'Fixture Customer', 'user_password' => wp_generate_password(24, false));
	ob_start();
	$ttbm_test_rejected(array(new TTBM_Payment_Settings(), 'ajax_portal_register'));
	$signup_response = json_decode(ob_get_clean(), true);
	$signup = get_user_by('email', $signup_email);
	$ttbm_test_assert(!empty($signup_response['success']) && $signup && !user_can($signup, 'manage_options'), 'Public signup inherited an administrator default role.');
	$ttbm_test_user('administrator');
	require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
	require_once ABSPATH . 'wp-admin/includes/screen.php';
	// WooCommerce is skipped in this run but still listed as active, so render the Custom Payment manager.
	$ttbm_test_no_wc = static function ($plugins) { return array_values(array_diff((array) $plugins, array('woocommerce/woocommerce.php'))); };
	add_filter('option_active_plugins', $ttbm_test_no_wc);
	$GLOBALS['current_screen'] = WP_Screen::get('ttbm_tour');
	$payment_settings = new TTBM_Payment_Settings();
	$render_edit_payment = static function () use ($payment_settings) {
		ob_start();
		$payment_settings->maybe_render_edit_payment_notice(null);
		return ob_get_clean();
	};
	$ttbm_test_assert(false !== strpos($render_edit_payment(), 'ttbm-payment-settings-content'), 'The tour editor payment manager was hidden from administrators.');
	$ttbm_test_user('subscriber');
	$ttbm_test_assert(false === strpos($render_edit_payment(), 'ttbm-payment-settings-content'), 'The tour editor exposed payment gateway settings to a non-administrator.');
	ob_start();
	$payment_settings->render_tab_content();
	$ttbm_test_assert('' === trim(ob_get_clean()), 'Payment gateway settings rendered for a non-administrator.');
	$ttbm_test_user('administrator');
	$GLOBALS['current_screen'] = null;
	remove_filter('option_active_plugins', $ttbm_test_no_wc);


	update_post_meta($order_id, '_ttbm_order_total', 20);
	update_post_meta($order_id, '_ttbm_order_currency', 'USD');
	update_post_meta($order_id, '_ttbm_stripe_session_id', 'test_session');
	$_GET = array('session_id' => 'test_session');
	$stripe = new class extends TTBM_Stripe_Gateway {
		public $reply;
		public function api_request($endpoint, $method = 'GET', $data = array()) { return $this->reply; }
	};
	$stripe->reply = array('payment_status' => 'paid', 'client_reference_id' => (string) $order_id, 'amount_total' => 2000, 'currency' => 'usd');
	$ttbm_test_assert($stripe->verify_payment($order_id), 'Valid Stripe payment failed.');
	$stripe->reply['amount_total'] = 1;
	$ttbm_test_assert(!$stripe->verify_payment($order_id), 'Underpaid Stripe session was accepted.');
	$stripe->reply['amount_total'] = 2000;
	$stripe->reply['currency'] = 'eur';
	$ttbm_test_assert(!$stripe->verify_payment($order_id), 'Wrong Stripe currency was accepted.');
	delete_post_meta($order_id, '_ttbm_stripe_session_id');
	$ttbm_test_assert(!$stripe->verify_payment($order_id), 'Unbound Stripe session was accepted.');

	$paypal = new class extends TTBM_PayPal_Gateway {
		public function get_access_token() { return 'test-only-token'; }
	};
	$unit = array('reference_id' => (string) $order_id, 'amount' => array('currency_code' => 'USD', 'value' => '20.00'), 'payments' => array('captures' => array(array('status' => 'COMPLETED', 'amount' => array('currency_code' => 'USD', 'value' => '20.00')))));
	$reply = array('id' => 'paypal_fixture', 'status' => 'COMPLETED', 'purchase_units' => array($unit));
	$capture_reply = array('status' => 'COMPLETED');
	$http_mock = static function ($pre, $args, $url) use (&$reply, &$capture_reply) {
		return array('response' => array('code' => 200), 'headers' => array(), 'body' => wp_json_encode('POST' === $args['method'] ? $capture_reply : $reply));
	};
	add_filter('pre_http_request', $http_mock, 10, 3);
	update_post_meta($order_id, '_ttbm_paypal_order_id', 'paypal_fixture');
	$_GET = array('token' => 'paypal_fixture');
	$ttbm_test_assert($paypal->verify_payment($order_id), 'Valid PayPal capture failed.');
	$capture_reply = array('details' => array(array('issue' => 'ORDER_ALREADY_CAPTURED')));
	$ttbm_test_assert($paypal->verify_payment($order_id), 'A valid PayPal capture replay failed.');
	$reply['purchase_units'][0]['amount']['value'] = '0.01';
	$ttbm_test_assert(!$paypal->verify_payment($order_id), 'An underpaid PayPal order was accepted.');
	$reply['purchase_units'][0]['amount']['value'] = '20.00';
	$reply['purchase_units'][0]['amount']['currency_code'] = 'EUR';
	$ttbm_test_assert(!$paypal->verify_payment($order_id), 'A PayPal order in another currency was accepted.');
	$reply['purchase_units'][0]['amount']['currency_code'] = 'USD';
	$reply['purchase_units'][0]['reference_id'] = 'another_order';
	$ttbm_test_assert(!$paypal->verify_payment($order_id), 'Another order\'s PayPal capture was accepted.');
	$_GET['token'] = 'another_payment';
	$ttbm_test_assert(!$paypal->verify_payment($order_id), 'Unbound PayPal token was accepted.');
	remove_filter('pre_http_request', $http_mock, 10);
	WP_CLI::success($ttbm_test_checks . ' security regression checks passed (' . (getenv('TTBM_TEST_PRO_CHECKOUT') ? 'PRO' : 'free') . ' checkout).');
} finally {
	$wpdb->query('ROLLBACK');
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ttbm_test_temp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($iterator as $file) {
		if ($file->isDir() && !$file->isLink()) {
			rmdir($file->getPathname());
		} else {
			unlink($file->getPathname());
		}
	}
	rmdir($ttbm_test_temp);
}
