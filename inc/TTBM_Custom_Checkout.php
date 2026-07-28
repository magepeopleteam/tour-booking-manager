<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * Custom (non-WooCommerce) checkout for tour bookings, mirroring the Event
	 * Manager Pro implementation. When active it:
	 *   1. renders a payment-method selector (PayPal / Stripe / Offline) above
	 *      the Book Now button (hook: ttbm_before_add_cart_btn),
	 *   2. intercepts the booking form's add-to-cart POST before WooCommerce
	 *      (wp_loaded priority 15 < WC_Form_Handler's 20), creates a
	 *      ttbm_custom_order record and redirects to the gateway's hosted
	 *      checkout page,
	 *   3. handles the gateway return/cancel URLs on init, verifies payment
	 *      server-side, then creates the same ttbm_booking / ttbm_service_booking
	 *      records the WooCommerce flow creates — so attendee lists, seat
	 *      availability (TTBM_Query reads ttbm_order_status meta) and reports
	 *      keep working.
	 *
	 * Active when at least one custom gateway is enabled AND WooCommerce
	 * checkout is unavailable or turned off (Settings → Payments → "Enable
	 * WooCommerce Payment"). Otherwise the normal WooCommerce flow runs.
	 *
	 * The ticket/price collectors below are ports of the static helpers in the
	 * free plugin's TTBM_Woocommerce, which is only loaded while WooCommerce is
	 * active — they are duplicated here so custom checkout never depends on it.
	 */
	if (!class_exists('TTBM_Custom_Checkout')) {
		class TTBM_Custom_Checkout {
			private static $instance = null;
			public static function instance() {
				if (null === self::$instance) {
					self::$instance = new self();
				}
				return self::$instance;
			}
			public function __construct() {
				// Lets the free plugin's booking panel render without WooCommerce
				// when a custom gateway can take the payment instead.
				add_filter('ttbm_booking_available', array($this, 'filter_booking_available'));
				// Tells the free plugin's Booking Mode selector that Pro is active,
				// so the custom-payment flow can exist at all (this filter callback
				// only runs when Pro is loaded, so it's true by definition — a
				// missing gateway is a separate, non-blocking warning, see
				// filter_custom_payment_has_gateway()).
				add_filter('ttbm_custom_payment_available', array($this, 'filter_custom_payment_available'));
				add_filter('ttbm_custom_payment_has_gateway', array($this, 'filter_custom_payment_has_gateway'));
				// Marker + hidden billing/payment fields in the book-now area;
				// the modal UI opens on Book Now (see ttbm-custom-checkout.js).
				add_action('ttbm_before_add_cart_btn', array($this, 'payment_method_selector'), 10, 2);
				add_action('wp_footer', array($this, 'render_checkout_modal'));
				// Priority 5: strip add-to-cart from modal AJAX posts before
				// WooCommerce's Form Handler (wp_loaded:20) can hijack admin-ajax
				// and redirect to the Checkout page HTML.
				add_action('wp_loaded', array($this, 'sanitize_ajax_checkout_request'), 5);
				// Priority 15 runs before WC_Form_Handler::add_to_cart_action (wp_loaded, 20).
				add_action('wp_loaded', array($this, 'maybe_intercept_booking'), 15);
				add_action('init', array($this, 'handle_payment_return'));
				// Priority 20 so the free plugin's login-gate script is already
				// registered when we declare it as a dependency.
				add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 20);
				add_action('wp_ajax_ttbm_custom_checkout', array($this, 'ajax_custom_checkout'));
				add_action('wp_ajax_nopriv_ttbm_custom_checkout', array($this, 'ajax_custom_checkout'));
			}
			/**
			 * Modal AJAX posts FormData that still includes the booking form's
			 * add-to-cart button value. On admin-ajax.php, WooCommerce treats that
			 * as a storefront add-to-cart, redirects to Checkout, and the AJAX
			 * action never runs — Completing Registration then fails with HTML.
			 */
			public function sanitize_ajax_checkout_request() {
				if (empty($_POST['ttbm_custom_checkout_ajax'])) {
					return;
				}
				if (!empty($_POST['add-to-cart']) && empty($_POST['ttbm_product_id'])) {
					$_POST['ttbm_product_id'] = absint($_POST['add-to-cart']);
					$_REQUEST['ttbm_product_id'] = $_POST['ttbm_product_id'];
				}
				unset($_POST['add-to-cart'], $_REQUEST['add-to-cart']);
			}
			//----------------------------------------------------------------------
			// Availability
			//----------------------------------------------------------------------
			public function filter_custom_payment_available($available) {
				return true;
			}
			public function filter_custom_payment_has_gateway($available) {
				return $available || !empty(TTBM_Payment_Gateway_Manager::get_instance()->get_available_gateways());
			}
			// True when the effective Booking Mode (free plugin's Payments tab)
			// is 'woocommerce' — this already auto-resolves for us (WC inactive,
			// or no gateway configured, etc.), so no separate fallback logic
			// is needed here.
			public static function wc_payment_enabled() {
				return !class_exists('TTBM_Payment_Settings') || TTBM_Payment_Settings::get_booking_mode() === 'woocommerce';
			}
			/**
			 * Whether bookings should go through the custom gateways instead of
			 * the WooCommerce cart/checkout.
			 */
			public static function is_active() {
				if (self::wc_payment_enabled()) {
					return false;
				}
				$gateways = TTBM_Payment_Gateway_Manager::get_instance()->get_available_gateways();
				return !empty($gateways);
			}
			public static function currency_code() {
				return apply_filters('ttbm_custom_checkout_currency', get_option('woocommerce_currency', 'USD'));
			}
			public function filter_booking_available($available) {
				return $available || self::is_active();
			}
			//----------------------------------------------------------------------
			// Frontend modal (Book Now → Complete Registration)
			//----------------------------------------------------------------------
			public function enqueue_assets() {
				if (!self::is_active()) {
					return;
				}
				$css = TTBM_PLUGIN_DIR . '/assets/frontend/ttbm-custom-checkout.css';
				$js = TTBM_PLUGIN_DIR . '/assets/frontend/ttbm-custom-checkout.js';
				wp_enqueue_style(
					'ttbm-custom-checkout',
					TTBM_PLUGIN_URL . '/assets/frontend/ttbm-custom-checkout.css',
					array(),
					file_exists($css) ? (string) filemtime($css) : TTBM_PLUGIN_VERSION
				);
				// Load after the login gate so its interceptor runs first when
				// login is required; custom-checkout then owns the resumed click.
				$deps = array('jquery');
				if (wp_script_is('ttbm-login-gate', 'registered') || wp_script_is('ttbm-login-gate', 'enqueued')) {
					$deps[] = 'ttbm-login-gate';
				}
				wp_enqueue_script(
					'ttbm-custom-checkout',
					TTBM_PLUGIN_URL . '/assets/frontend/ttbm-custom-checkout.js',
					$deps,
					file_exists($js) ? (string) filemtime($js) : TTBM_PLUGIN_VERSION,
					true
				);
				wp_localize_script('ttbm-custom-checkout', 'ttbmCustomCheckout', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'select_ticket' => esc_html__('Please Select Ticket Type', 'tour-booking-manager'),
					'billing_required' => esc_html__('Please fill in all billing details (name, email and phone).', 'tour-booking-manager'),
					'email_invalid' => esc_html__('Please enter a valid email address.', 'tour-booking-manager'),
					'payment_required' => esc_html__('Please select a payment method.', 'tour-booking-manager'),
					'guest_info' => esc_html__('Guest Information', 'tour-booking-manager'),
					'guest_n' => esc_html__('Guest Information #%d', 'tour-booking-manager'),
					'generic_error' => esc_html__('Something went wrong while processing your booking. Please try again.', 'tour-booking-manager'),
					'done_label' => esc_html__('Done', 'tour-booking-manager'),
					'modal_title' => esc_html__('Complete Registration', 'tour-booking-manager'),
					'review_subtitle' => esc_html__('Review your booking and confirm your details.', 'tour-booking-manager'),
					'received_title' => esc_html__('Booking received', 'tour-booking-manager'),
				));
			}
			/**
			 * Hidden marker + billing/payment fields posted with the booking form.
			 * Payment radios live in the modal; JS copies the chosen values here
			 * before triggering the real add-to-cart submit.
			 */
			public function payment_method_selector($product_id, $tour_id) {
				if (!self::is_active()) {
					return;
				}
				$gateways = TTBM_Payment_Gateway_Manager::get_instance()->get_available_gateways();
				$default_gateway = !empty($gateways) ? array_key_first($gateways) : 'offline';
				?>
				<span class="ttbm_custom_checkout_trigger" hidden
					data-tour-id="<?php echo esc_attr($tour_id); ?>"
					data-tour-title="<?php echo esc_attr(get_the_title($tour_id)); ?>"
					data-product-id="<?php echo esc_attr($product_id); ?>"></span>
				<input type="hidden" name="ttbm_billing_name" value="" class="ttbm_billing_name_field">
				<input type="hidden" name="ttbm_billing_email" value="" class="ttbm_billing_email_field">
				<input type="hidden" name="ttbm_billing_phone" value="" class="ttbm_billing_phone_field">
				<input type="hidden" name="ttbm_payment_method" value="<?php echo esc_attr($default_gateway); ?>" class="ttbm_payment_method_field">
				<?php
			}
			public function render_checkout_modal() {
				if (!self::is_active() || is_admin()) {
					return;
				}
				$template = TTBM_PLUGIN_DIR . '/templates/ttbm_custom_checkout_modal.php';
				if (file_exists($template)) {
					include $template;
				}
			}
			//----------------------------------------------------------------------
			// Booking form interception
			//----------------------------------------------------------------------
			public function maybe_intercept_booking() {
				if (empty($_POST['add-to-cart']) || !isset($_POST['ttbm_form_nonce'])) {
					return;
				}
				// Modal AJAX path handles its own response — ignore classic form POST.
				if (!empty($_POST['ttbm_custom_checkout_ajax'])) {
					return;
				}
				if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_form_nonce'])), 'ttbm_form_nonce')) {
					return;
				}
				if (!self::is_active()) {
					return;
				}
				$product_id = absint($_POST['add-to-cart']);
				$tour_id = $this->resolve_tour_id_from_product($product_id);
				if (!$tour_id) {
					return;
				}
				if (class_exists('TTBM_Payment_Settings') && TTBM_Payment_Settings::login_required_for_booking() && !is_user_logged_in()) {
					wp_safe_redirect(wp_login_url(get_permalink($tour_id)));
					exit;
				}
				$result = $this->process_booking($tour_id);
				$this->respond_booking_result($result, $tour_id, false);
			}
			/**
			 * AJAX Complete Registration from the custom checkout modal — returns
			 * confirmation HTML so the modal can update without a full page reload.
			 * Hosted gateways (PayPal/Stripe) still return a redirect URL.
			 */
			public function ajax_custom_checkout() {
				// Discard any prior output, then buffer notices produced while
				// processing so WP_DEBUG_DISPLAY cannot corrupt the JSON body
				// (that used to make jQuery treat a successful booking as a fail).
				while (ob_get_level() > 0) {
					ob_end_clean();
				}
				ob_start();
				if (!self::is_active()) {
					$this->ajax_discard_buffer();
					wp_send_json_error(array('message' => esc_html__('Custom checkout is not available.', 'tour-booking-manager')));
				}
				if (empty($_POST['ttbm_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_form_nonce'])), 'ttbm_form_nonce')) {
					$this->ajax_discard_buffer();
					wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh and try again.', 'tour-booking-manager')));
				}
				$product_id = !empty($_POST['ttbm_product_id'])
					? absint($_POST['ttbm_product_id'])
					: (!empty($_POST['add-to-cart']) ? absint($_POST['add-to-cart']) : 0);
				$tour_id = $this->resolve_tour_id_from_product($product_id);
				if (!$tour_id && !empty($_POST['ttbm_id'])) {
					$tour_id = TTBM_Function::post_id_multi_language(absint($_POST['ttbm_id']));
					if (get_post_type($tour_id) != TTBM_Function::get_cpt_name()) {
						$tour_id = 0;
					}
				}
				if (!$tour_id) {
					$this->ajax_discard_buffer();
					wp_send_json_error(array('message' => esc_html__('Invalid tour booking request.', 'tour-booking-manager')));
				}
				if (class_exists('TTBM_Payment_Settings') && TTBM_Payment_Settings::login_required_for_booking() && !is_user_logged_in()) {
					$this->ajax_discard_buffer();
					wp_send_json_error(array(
						'message' => esc_html__('Please log in to complete your booking.', 'tour-booking-manager'),
						'login_required' => true,
						'login_url' => wp_login_url(get_permalink($tour_id)),
					));
				}
				try {
					$result = $this->process_booking($tour_id);
					$this->ajax_discard_buffer();
					$this->respond_booking_result($result, $tour_id, true);
				} catch (Throwable $e) {
					$this->ajax_discard_buffer();
					wp_send_json_error(array(
						'message' => esc_html__('Something went wrong while processing your booking. Please try again.', 'tour-booking-manager'),
					));
				}
			}
			private function ajax_discard_buffer() {
				while (ob_get_level() > 0) {
					ob_end_clean();
				}
			}
			private function resolve_tour_id_from_product($product_id) {
				$product_id = absint($product_id);
				if (!$product_id) {
					return 0;
				}
				$linked = TTBM_Global_Function::get_post_info($product_id, 'link_ttbm_id', $product_id);
				$tour_id = is_string(get_post_status($linked)) ? $linked : $product_id;
				$tour_id = TTBM_Function::post_id_multi_language($tour_id);
				if (get_post_type($tour_id) != TTBM_Function::get_cpt_name()) {
					return 0;
				}
				return (int) $tour_id;
			}
			/**
			 * @param array|WP_Error $result
			 * @param int            $tour_id
			 * @param bool           $ajax
			 */
			private function respond_booking_result($result, $tour_id, $ajax) {
				if (is_wp_error($result)) {
					if ($ajax) {
						wp_send_json_error(array('message' => $result->get_error_message()));
					}
					$this->redirect_with_notice(get_permalink($tour_id), 'failed', 0, '', $result->get_error_message());
				}
				$state = isset($result['state']) ? $result['state'] : 'pending';
				$order_id = isset($result['order_id']) ? (int) $result['order_id'] : 0;
				$order_key = isset($result['order_key']) ? (string) $result['order_key'] : '';
				$redirect = !empty($result['redirect']) ? $result['redirect'] : '';
				$message = isset($result['message']) ? (string) $result['message'] : '';

				if ($ajax) {
					if ($redirect) {
						wp_send_json_success(array(
							'redirect' => $redirect,
							'order_id' => $order_id,
							'state' => $state,
						));
					}
					$html = class_exists('TTBM_Custom_Order_Confirmation')
						? TTBM_Custom_Order_Confirmation::render_order_confirmation($order_id, $state)
						: '';
					wp_send_json_success(array(
						'html' => $html,
						'order_id' => $order_id,
						'order_key' => $order_key,
						'state' => $state,
						'confirmation_url' => add_query_arg(array(
							'ttbm_state' => $state,
							'ttbm_order' => $order_id,
							'key' => $order_key,
						), $this->confirmation_base_url($tour_id)),
					));
				}

				if ($redirect) {
					wp_redirect($redirect); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- external gateway URL
					exit;
				}
				$base = in_array($state, array('failed', 'cancelled'), true)
					? get_permalink($tour_id)
					: $this->confirmation_base_url($tour_id);
				$this->redirect_with_notice($base, $state, $order_id, $order_key, $message);
			}
			/**
			 * Create the custom order and advance payment / finalization.
			 *
			 * @param int $tour_id
			 * @return array|WP_Error {
			 *   @type int    $order_id
			 *   @type string $order_key
			 *   @type string $state     success|pending|failed
			 *   @type string $redirect  Optional hosted gateway URL.
			 *   @type string $message   Optional notice.
			 * }
			 */
			private function process_booking($tour_id) {
				$ticket_info = $this->collect_ticket_info($tour_id);
				if (empty($ticket_info)) {
					return new WP_Error('ttbm_no_ticket', esc_html__('Please select at least one ticket.', 'tour-booking-manager'));
				}
				$hotel_info = apply_filters('ttbm_hotel_info_filter', $this->collect_hotel_info(), $tour_id);
				$service_info = $this->collect_extra_service_info($tour_id);
				$user_info = apply_filters('ttbm_user_info_data', array(), $tour_id);
				$total = $this->calculate_total($tour_id);
				$start_date = isset($_POST['ttbm_start_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_start_date'])) : '';
				$end_date = isset($_POST['ttbm_end_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_end_date'])) : '';
				$gateway_id = isset($_POST['ttbm_payment_method']) ? sanitize_key(wp_unslash($_POST['ttbm_payment_method'])) : '';
				$manager = TTBM_Payment_Gateway_Manager::get_instance();
				$available = $manager->get_available_gateways();
				if (!isset($available[$gateway_id])) {
					$gateway_id = !empty($available) ? array_key_first($available) : '';
				}
				$gateway = $gateway_id ? $manager->get_gateway($gateway_id) : null;
				if (!$gateway && $total > 0) {
					return new WP_Error('ttbm_no_gateway', esc_html__('No payment method is available. Please contact the site administrator.', 'tour-booking-manager'));
				}
				// Modal billing fields are required for custom checkout.
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in maybe_intercept_booking()/ajax_custom_checkout()
				$posted_name = isset($_POST['ttbm_billing_name']) ? sanitize_text_field(wp_unslash($_POST['ttbm_billing_name'])) : '';
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$posted_email = isset($_POST['ttbm_billing_email']) ? sanitize_email(wp_unslash($_POST['ttbm_billing_email'])) : '';
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$posted_phone = isset($_POST['ttbm_billing_phone']) ? sanitize_text_field(wp_unslash($_POST['ttbm_billing_phone'])) : '';
				if (!$posted_name || !$posted_email || !is_email($posted_email) || !$posted_phone) {
					return new WP_Error('ttbm_billing', esc_html__('Please fill in all billing details (name, email and phone).', 'tour-booking-manager'));
				}
				$customer = $this->resolve_customer($user_info);
				$order_id = wp_insert_post(array(
					/* translators: %s: order date and time */
					'post_title' => sprintf(esc_html__('Order - %s', 'tour-booking-manager'), date_i18n('M d, Y @ h:i A')),
					'post_type' => 'ttbm_custom_order',
					'post_status' => 'pending',
					'post_author' => get_current_user_id(),
				));
				if (is_wp_error($order_id) || !$order_id) {
					return new WP_Error('ttbm_order_create', esc_html__('Could not create the order record. Please try again.', 'tour-booking-manager'));
				}
				$order_key = wp_generate_password(13, false);
				update_post_meta($order_id, '_ttbm_order_key', $order_key);
				update_post_meta($order_id, '_ttbm_tour_id', $tour_id);
				update_post_meta($order_id, '_ttbm_order_total', $total);
				update_post_meta($order_id, '_ttbm_customer_id', get_current_user_id());
				update_post_meta($order_id, '_ttbm_customer_name', $customer['name']);
				update_post_meta($order_id, '_ttbm_customer_email', $customer['email']);
				update_post_meta($order_id, '_ttbm_customer_phone', $customer['phone']);
				update_post_meta($order_id, '_ttbm_ticket_info', $ticket_info);
				update_post_meta($order_id, '_ttbm_hotel_info', $hotel_info);
				update_post_meta($order_id, '_ttbm_service_info', $service_info);
				update_post_meta($order_id, '_ttbm_user_info', $user_info);
				update_post_meta($order_id, '_ttbm_date', $start_date);
				update_post_meta($order_id, '_ttbm_end_date', $end_date);
				update_post_meta($order_id, '_ttbm_payment_gateway', $gateway_id);
				do_action('ttbm_custom_order_created', $order_id, $tour_id);
				// Free booking — confirm immediately.
				if ($total <= 0) {
					$this->finalize_order($order_id, 'publish', 'free');
					return array(
						'order_id' => $order_id,
						'order_key' => $order_key,
						'state' => 'success',
					);
				}
				// Offline (or any gateway without a hosted-checkout URL) — record as
				// pending; the admin confirms manually once the payment arrives.
				$payment_url = $gateway->get_payment_url($this->payment_args($order_id, $tour_id, $total, $customer, $gateway_id, $order_key));
				if ($payment_url === null) {
					$this->finalize_order($order_id, 'pending', $gateway_id);
					return array(
						'order_id' => $order_id,
						'order_key' => $order_key,
						'state' => 'pending',
					);
				}
				if (is_wp_error($payment_url)) {
					wp_update_post(array('ID' => $order_id, 'post_status' => 'failed'));
					update_post_meta($order_id, '_ttbm_gateway_error', $payment_url->get_error_message());
					return array(
						'order_id' => $order_id,
						'order_key' => $order_key,
						'state' => 'failed',
						'message' => $payment_url->get_error_message(),
					);
				}
				return array(
					'order_id' => $order_id,
					'order_key' => $order_key,
					'state' => 'pending',
					'redirect' => $payment_url,
				);
			}
			private function payment_args($order_id, $tour_id, $total, $customer, $gateway_id, $order_key) {
				$return_url = add_query_arg(array(
					'ttbm_payment_return' => 1,
					'order_id' => $order_id,
					'gateway' => $gateway_id,
					'key' => $order_key,
				), home_url('/'));
				$cancel_url = add_query_arg(array(
					'ttbm_payment_cancel' => 1,
					'order_id' => $order_id,
					'gateway' => $gateway_id,
					'key' => $order_key,
				), home_url('/'));
				return array(
					'order_id' => $order_id,
					'total' => $total,
					'currency' => self::currency_code(),
					'tour_id' => $tour_id,
					'customer_name' => $customer['name'],
					'customer_email' => $customer['email'],
					'return_url' => $return_url,
					'cancel_url' => $cancel_url,
				);
			}
			/**
			 * Customer identity for the order: modal billing fields first, then
			 * the logged-in account, then the first attendee-form entry, else Guest.
			 */
			private function resolve_customer($user_info) {
				$customer = array('name' => '', 'email' => '', 'phone' => '');
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in maybe_intercept_booking()
				if (!empty($_POST['ttbm_billing_name'])) {
					$customer['name'] = sanitize_text_field(wp_unslash($_POST['ttbm_billing_name']));
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if (!empty($_POST['ttbm_billing_email'])) {
					$customer['email'] = sanitize_email(wp_unslash($_POST['ttbm_billing_email']));
				}
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				if (!empty($_POST['ttbm_billing_phone'])) {
					$customer['phone'] = sanitize_text_field(wp_unslash($_POST['ttbm_billing_phone']));
				}
				if (is_user_logged_in()) {
					$user = wp_get_current_user();
					if (!$customer['name']) {
						$customer['name'] = $user->display_name;
					}
					if (!$customer['email']) {
						$customer['email'] = $user->user_email;
					}
					if (!$customer['phone']) {
						$customer['phone'] = (string) get_user_meta($user->ID, 'billing_phone', true);
					}
				}
				$first = (is_array($user_info) && !empty($user_info) && is_array(reset($user_info))) ? reset($user_info) : array();
				foreach ($first as $field => $value) {
					if (!is_scalar($value) || $value === '') {
						continue;
					}
					$field_lc = strtolower($field);
					if (!$customer['email'] && is_email($value)) {
						$customer['email'] = sanitize_email($value);
					} elseif (!$customer['name'] && strpos($field_lc, 'name') !== false) {
						$customer['name'] = sanitize_text_field($value);
					} elseif (!$customer['phone'] && (strpos($field_lc, 'phone') !== false || strpos($field_lc, 'mobile') !== false)) {
						$customer['phone'] = sanitize_text_field($value);
					}
				}
				if (!$customer['name']) {
					$customer['name'] = esc_html__('Guest', 'tour-booking-manager');
				}
				return $customer;
			}
			//----------------------------------------------------------------------
			// Gateway return / cancel handling
			//----------------------------------------------------------------------
			public function handle_payment_return() {
				// phpcs:disable WordPress.Security.NonceVerification.Recommended -- gateway callbacks carry the per-order key instead
				if (isset($_GET['ttbm_payment_return'], $_GET['order_id'])) {
					$order_id = absint($_GET['order_id']);
					$gateway_id = isset($_GET['gateway']) ? sanitize_key(wp_unslash($_GET['gateway'])) : '';
					if (get_post_type($order_id) !== 'ttbm_custom_order') {
						return;
					}
					$tour_id = (int) get_post_meta($order_id, '_ttbm_tour_id', true);
					$order_key = (string) get_post_meta($order_id, '_ttbm_order_key', true);
					// Refresh / replay of the return URL for an already-paid order:
					// don't re-verify, just show the confirmation again.
					$paid_statuses = array('processing', 'publish');
					if (in_array(get_post_status($order_id), $paid_statuses, true)) {
						$this->redirect_with_notice($this->confirmation_base_url($tour_id), 'success', $order_id, $order_key);
					}
					$gateway = TTBM_Payment_Gateway_Manager::get_instance()->get_gateway($gateway_id);
					if ($gateway && $gateway->verify_payment($order_id)) {
						$this->finalize_order($order_id, 'processing', $gateway_id);
						$this->redirect_with_notice($this->confirmation_base_url($tour_id), 'success', $order_id, $order_key);
					}
					wp_die(esc_html__('Payment verification failed. Please contact us if you believe this is an error.', 'tour-booking-manager'));
				}
				if (isset($_GET['ttbm_payment_cancel'], $_GET['order_id'])) {
					$order_id = absint($_GET['order_id']);
					$key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
					if (get_post_type($order_id) !== 'ttbm_custom_order') {
						return;
					}
					$order_key = (string) get_post_meta($order_id, '_ttbm_order_key', true);
					// Only a pending (unpaid, not yet finalized) order may be
					// cancelled from the browser, and only with its own key.
					if (!$order_key || !hash_equals($order_key, $key) || get_post_status($order_id) !== 'pending') {
						return;
					}
					wp_update_post(array('ID' => $order_id, 'post_status' => 'cancelled'));
					$tour_id = (int) get_post_meta($order_id, '_ttbm_tour_id', true);
					$this->redirect_with_notice(get_permalink($tour_id), 'cancelled', $order_id, $order_key);
				}
				// phpcs:enable WordPress.Security.NonceVerification.Recommended
			}
			//----------------------------------------------------------------------
			// Order finalization — create the same records the WooCommerce flow does
			//----------------------------------------------------------------------
			/**
			 * Set the order status and create the ttbm_booking / ttbm_service_booking
			 * records (idempotent — safe to call again on a replayed return URL).
			 *
			 * @param int    $order_id    ttbm_custom_order post ID.
			 * @param string $post_status pending | processing | publish (completed).
			 * @param string $gateway_id  Gateway id, or 'free'.
			 */
			public function finalize_order($order_id, $post_status, $gateway_id) {
				wp_update_post(array('ID' => $order_id, 'post_status' => $post_status));
				update_post_meta($order_id, '_ttbm_payment_gateway', $gateway_id);
				$existing = get_post_meta($order_id, '_ttbm_booking_ids', true);
				if (!empty($existing)) {
					// Bookings already created (e.g. replayed return URL) — only align their status.
					self::propagate_status($order_id, TTBM_Custom_Order_CPT::booking_status($post_status));
					return;
				}
				$booking_ids = $this->create_booking_records($order_id, TTBM_Custom_Order_CPT::booking_status($post_status), $gateway_id);
				update_post_meta($order_id, '_ttbm_booking_ids', $booking_ids);
				// Skipped once TTBM_Custom_Order_Mail is active and listening on
				// the hook fired immediately below — its templated created/paid
				// emails replace this bare inline one, never both.
				if (!has_action('ttbm_custom_order_status_changed')) {
					$this->send_order_email($order_id);
				}
				do_action('ttbm_custom_order_status_changed', $order_id, $post_status, 'pending');
			}
			/**
			 * Port of TTBM_Woocommerce::add_billing_data() + add_extra_service_data(),
			 * reading from the custom order's meta instead of a WC order. Creates one
			 * ttbm_booking post per seat (honoring the group-ticket filters and the
			 * form-builder's ttbm_user_booking_data_arr) plus ttbm_service_booking
			 * posts, so seat availability, attendee lists and reports keep working.
			 */
			private function create_booking_records($order_id, $booking_status, $gateway_id) {
				$tour_id = (int) get_post_meta($order_id, '_ttbm_tour_id', true);
				$ticket_info = (array) get_post_meta($order_id, '_ttbm_ticket_info', true);
				$hotel_info = (array) get_post_meta($order_id, '_ttbm_hotel_info', true);
				$service_info = (array) get_post_meta($order_id, '_ttbm_service_info', true);
				$user_info = (array) get_post_meta($order_id, '_ttbm_user_info', true);
				$user_id = (int) get_post_meta($order_id, '_ttbm_customer_id', true);
				$billing_name = (string) get_post_meta($order_id, '_ttbm_customer_name', true);
				$billing_email = (string) get_post_meta($order_id, '_ttbm_customer_email', true);
				$billing_phone = (string) get_post_meta($order_id, '_ttbm_customer_phone', true);
				$hotel_id = !empty($hotel_info['hotel_id']) ? $hotel_info['hotel_id'] : 0;
				$checkin_date = isset($hotel_info['ttbm_checkin_date']) ? $hotel_info['ttbm_checkin_date'] : '';
				$checkout_date = isset($hotel_info['ttbm_checkout_date']) ? $hotel_info['ttbm_checkout_date'] : '';
				$num_of_day = !empty($hotel_info['ttbm_hotel_num_of_day']) ? max(1, absint($hotel_info['ttbm_hotel_num_of_day'])) : 1;
				$booking_ids = array();
				$count = 0;
				foreach ($ticket_info as $_ticket) {
					$qty = isset($_ticket['ticket_qty']) ? absint($_ticket['ticket_qty']) : 0;
					for ($key = 0; $key < $qty; $key++) {
						$group_attendee_id = '';
						$group_count = 0;
						$actual_qty = apply_filters('ttbm_group_ticket_qty_actual', 1, $tour_id, $_ticket['ticket_name']);
						for ($j = 0; $j < $actual_qty; $j++) {
							$group_id = $actual_qty > 1 ? 'on' : '';
							if ($group_count > 0) {
								$current_group_id = TTBM_Global_Function::get_post_info($group_attendee_id, 'ttbm_group_id');
								$group_id = ($current_group_id && $current_group_id != 'on') ? $current_group_id : $group_attendee_id;
							}
							$data = array(
								'ttbm_ticket_name' => $_ticket['ticket_name'],
								'ttbm_ticket_price' => $_ticket['ticket_price'] * $num_of_day,
								'ttbm_ticket_total_price' => ($_ticket['ticket_price'] * $qty) * $num_of_day,
								'ttbm_date' => isset($_ticket['ttbm_date']) ? $_ticket['ttbm_date'] : '',
								'ttbm_ticket_qty' => $_ticket['ticket_qty'],
								'ttbm_group_id' => $group_id,
								'ttbm_hotel_id' => $hotel_id,
								'ttbm_checkin_date' => $checkin_date,
								'ttbm_checkout_date' => $checkout_date,
								'ttbm_hotel_num_of_day' => $num_of_day,
								'ttbm_id' => $tour_id,
								'ttbm_order_id' => $order_id,
								'ttbm_order_status' => $booking_status,
								'ttbm_payment_method' => $gateway_id,
								'ttbm_user_id' => $user_id,
								'ttbm_billing_name' => $billing_name,
								'ttbm_billing_email' => $billing_email,
								'ttbm_billing_phone' => $billing_phone,
								'ttbm_billing_address' => '',
							);
							$user_data = apply_filters('ttbm_user_booking_data_arr', $data, $count, $user_info, $tour_id);
							$group_attendee_id = self::add_cpt_data('ttbm_booking', $user_data['ttbm_billing_name'], $user_data);
							$booking_ids[] = $group_attendee_id;
							$count++;
							$group_count++;
						}
					}
				}
				foreach ($service_info as $_service) {
					self::add_cpt_data('ttbm_service_booking', '#' . $order_id . $_service['service_name'], array(
						'ttbm_service_name' => $_service['service_name'],
						'ttbm_service_price' => $_service['service_price'],
						'ttbm_service_total_price' => ($_service['service_price'] * $_service['service_qty']),
						'ttbm_date' => isset($_service['ttbm_date']) ? $_service['ttbm_date'] : '',
						'ttbm_service_qty' => $_service['service_qty'],
						'ttbm_id' => $tour_id,
						'ttbm_order_id' => $order_id,
						'ttbm_order_status' => $booking_status,
						'ttbm_payment_method' => $gateway_id,
						'ttbm_user_id' => $user_id,
					));
				}
				return $booking_ids;
			}
			/**
			 * Align the ttbm_booking / ttbm_service_booking records with an order
			 * status change (same effect as the free plugin's
			 * ttbm_wc_order_status_change handler, without requiring WooCommerce).
			 */
			public static function propagate_status($order_id, $booking_status) {
				foreach (array('ttbm_booking', 'ttbm_service_booking') as $post_type) {
					$loop = new WP_Query(array(
						'post_type' => $post_type,
						'posts_per_page' => -1,
						'fields' => 'ids',
						'meta_query' => array(
							array(
								'key' => 'ttbm_order_id',
								'value' => $order_id,
								'compare' => '=',
							),
						),
					));
					foreach ($loop->posts as $booking_id) {
						update_post_meta($booking_id, 'ttbm_order_status', $booking_status);
					}
					wp_reset_postdata();
				}
			}
			/**
			 * Port of TTBM_Woocommerce::add_cpt_data() (free plugin, only loaded
			 * while WooCommerce is active).
			 */
			public static function add_cpt_data($cpt_name, $title, $meta_data = array(), $status = 'publish') {
				$post_id = wp_insert_post(array(
					'post_title' => $title,
					'post_content' => '',
					'post_status' => $status,
					'post_type' => $cpt_name,
				));
				foreach ($meta_data as $key => $value) {
					update_post_meta($post_id, $key, $value);
				}
				if ($cpt_name == 'ttbm_booking') {
					$ttbm_pin = $meta_data['ttbm_user_id'] . $meta_data['ttbm_order_id'] . $meta_data['ttbm_id'] . $post_id;
					update_post_meta($post_id, 'ttbm_pin', $ttbm_pin);
				}
				return $post_id;
			}
			//----------------------------------------------------------------------
			// Confirmation redirect + email
			//----------------------------------------------------------------------
			/**
			 * Base URL to land on after checkout: the Booking Confirmation Page
			 * chosen under Settings → Payments → Custom Payment, else the tour page.
			 */
			private function confirmation_base_url($tour_id) {
				$page_id = (int) TTBM_Global_Function::get_settings('ttbm_payment_settings', 'ttbm_payment_confirmation_page', 0);
				if ($page_id > 0 && get_post_status($page_id) === 'publish') {
					return get_permalink($page_id);
				}
				return get_permalink($tour_id);
			}
			/**
			 * Redirect carrying the booking state; TTBM_Custom_Order_Confirmation
			 * renders the banner/summary from these query args. Always exits.
			 *
			 * Uses "ttbm_state", not "ttbm_booking": the latter collides with the
			 * public ttbm_booking CPT's auto-registered query_var (public => true
			 * with no query_var override defaults it to the post type name), so
			 * WordPress tries to resolve ?ttbm_booking=success as a single
			 * ttbm_booking post named "success", fails, and 404s the entire
			 * request — including the confirmation page itself.
			 */
			private function redirect_with_notice($base_url, $state, $order_id = 0, $order_key = '', $message = '') {
				$args = array('ttbm_state' => $state);
				if ($order_id) {
					$args['ttbm_order'] = $order_id;
					$args['key'] = $order_key;
				}
				if ($message) {
					set_transient('ttbm_booking_notice_' . ($order_id ? $order_id : get_current_user_id()), $message, 5 * MINUTE_IN_SECONDS);
				}
				wp_safe_redirect(add_query_arg($args, $base_url));
				exit;
			}
			/**
			 * Booking email for custom orders. The free/pro WooCommerce mail path
			 * (ttbm_send_mail) needs a WC order, so custom orders send their own.
			 */
			private function send_order_email($order_id) {
				$email = (string) get_post_meta($order_id, '_ttbm_customer_email', true);
				if (!$email || !is_email($email)) {
					return;
				}
				$tour_id = (int) get_post_meta($order_id, '_ttbm_tour_id', true);
				$status = get_post_status($order_id);
				$paid = in_array($status, array('processing', 'publish'), true);
				$total = (float) get_post_meta($order_id, '_ttbm_order_total', true);
				$date = (string) get_post_meta($order_id, '_ttbm_date', true);
				$data_format = TTBM_Global_Function::check_time_exit_date($date) ? 'full' : 'date';
				$subject = $paid
					? sprintf(esc_html__('Your booking is confirmed — Order #%d', 'tour-booking-manager'), $order_id)
					: sprintf(esc_html__('Your booking is received — Order #%d', 'tour-booking-manager'), $order_id);
				$lines = array();
				$lines[] = $paid
					? esc_html__('Thank you! Your payment was received and your booking is confirmed.', 'tour-booking-manager')
					: esc_html__('Thank you! Your booking has been received and will be confirmed once payment is completed.', 'tour-booking-manager');
				$lines[] = '';
				$lines[] = esc_html__('Booking details', 'tour-booking-manager') . ':';
				$lines[] = sprintf('%s: %s', TTBM_Function::get_name(), get_the_title($tour_id));
				if ($date) {
					$lines[] = sprintf('%s: %s', esc_html__('Date', 'tour-booking-manager'), TTBM_Global_Function::date_format($date, $data_format));
				}
				$ticket_info = (array) get_post_meta($order_id, '_ttbm_ticket_info', true);
				foreach ($ticket_info as $ticket) {
					if (empty($ticket['ticket_qty'])) {
						continue;
					}
					$lines[] = sprintf('%s x %d', $ticket['ticket_name'], $ticket['ticket_qty']);
				}
				$service_info = (array) get_post_meta($order_id, '_ttbm_service_info', true);
				foreach ($service_info as $service) {
					if (empty($service['service_qty'])) {
						continue;
					}
					$lines[] = sprintf('%s x %d', $service['service_name'], $service['service_qty']);
				}
				$lines[] = sprintf('%s: %s %s', esc_html__('Total', 'tour-booking-manager'), number_format_i18n($total, 2), self::currency_code());
				$lines[] = sprintf('%s: %d', esc_html__('Order number', 'tour-booking-manager'), $order_id);
				$body = implode("\n", $lines);
				$from_email = get_option('woocommerce_email_from_address') ?: get_option('admin_email');
				$from_name = get_option('woocommerce_email_from_name') ?: get_option('blogname');
				$headers = array(sprintf('From: %s <%s>', $from_name, $from_email));
				$subject = apply_filters('ttbm_custom_order_email_subject', $subject, $order_id);
				$body = apply_filters('ttbm_custom_order_email_body', $body, $order_id);
				wp_mail($email, $subject, nl2br($body), $headers);
				$admin_email = get_option('admin_email');
				if ($admin_email && $admin_email !== $email) {
					wp_mail($admin_email, sprintf(esc_html__('New tour booking — Order #%d', 'tour-booking-manager'), $order_id), nl2br($body), $headers);
				}
			}
			//----------------------------------------------------------------------
			// Collectors — ports of the free plugin's TTBM_Woocommerce statics
			//----------------------------------------------------------------------
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in maybe_intercept_booking()
			private function collect_ticket_info($tour_id) {
				$ticket_info = array();
				$start_date = isset($_POST['ttbm_start_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_start_date'])) : '';
				$hotel_id = isset($_POST['ttbm_tour_hotel_list']) ? sanitize_text_field(wp_unslash($_POST['ttbm_tour_hotel_list'])) : 0;
				$names = isset($_POST['ticket_name']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_name'])) : array();
				$qty = isset($_POST['ticket_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_qty'])) : array();
				$max_qty = isset($_POST['ticket_max_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_max_qty'])) : array();
				for ($i = 0; $i < count($names); $i++) {
					if (isset($qty[$i]) && $qty[$i] > 0) {
						$name = $names[$i] ?? '';
						$ticket_info[$i]['ticket_name'] = $name;
						$ticket_info[$i]['ticket_price'] = TTBM_Function::get_price_by_name($name, $tour_id, $hotel_id, $qty[$i], $start_date);
						$ticket_info[$i]['ticket_qty'] = $qty[$i];
						$ticket_info[$i]['qroup_qty'] = apply_filters('ttbm_group_actual_qty', 1, $tour_id, $name);
						$ticket_info[$i]['ttbm_max_qty'] = $max_qty[$i] ?? '';
						$ticket_info[$i]['ttbm_date'] = $start_date;
					}
				}
				return apply_filters('ttbm_cart_ticket_info_data_prepare', $ticket_info, $tour_id);
			}
			private function collect_hotel_info() {
				$hotel_info = array();
				$hotel_id = isset($_POST['ttbm_tour_hotel_list']) ? sanitize_text_field(wp_unslash($_POST['ttbm_tour_hotel_list'])) : 0;
				if ($hotel_id > 0) {
					$hotel_info['hotel_id'] = $hotel_id;
					$hotel_info['ttbm_checkin_date'] = isset($_POST['ttbm_checkin_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_checkin_date'])) : '';
					$hotel_info['ttbm_checkout_date'] = isset($_POST['ttbm_checkout_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_checkout_date'])) : '';
					$hotel_info['ttbm_hotel_num_of_day'] = isset($_POST['ttbm_hotel_num_of_day']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_num_of_day'])) : '';
				}
				return $hotel_info;
			}
			private function collect_extra_service_info($tour_id) {
				$extra_service = array();
				$start_date = isset($_POST['ttbm_start_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_start_date'])) : '';
				$service_name = isset($_POST['service_name']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_name'])) : array();
				$service_qty = isset($_POST['service_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_qty'])) : array();
				for ($i = 0; $i < count($service_name); $i++) {
					$requested_qty = isset($service_qty[$i]) ? absint($service_qty[$i]) : 0;
					if ($requested_qty > 0) {
						$name = $service_name[$i] ?? '';
						$available = TTBM_Function::get_extra_service_available($tour_id, $start_date, $name);
						if ($available < 1) {
							continue;
						}
						if ($requested_qty > $available) {
							$requested_qty = $available;
						}
						$extra_service[$i]['service_name'] = $name;
						$extra_service[$i]['service_price'] = TTBM_Function::get_extra_service_price_by_name($tour_id, $name);
						$extra_service[$i]['service_qty'] = $requested_qty;
						$extra_service[$i]['ttbm_date'] = $start_date;
					}
				}
				return $extra_service;
			}
			private function calculate_total($tour_id) {
				$total_price = 0;
				$total_qty = 0;
				$names = isset($_POST['ticket_name']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_name'])) : array();
				$qty = isset($_POST['ticket_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_qty'])) : array();
				$hotel_id = isset($_POST['ttbm_tour_hotel_list']) ? sanitize_text_field(wp_unslash($_POST['ttbm_tour_hotel_list'])) : 0;
				$start_date = isset($_POST['ttbm_start_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_start_date'])) : '';
				$num_of_day = isset($_POST['ttbm_hotel_num_of_day']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_num_of_day'])) : 0;
				foreach ($names as $i => $name) {
					if (isset($qty[$i]) && $qty[$i] > 0) {
						$total_qty += $qty[$i];
						$price = TTBM_Function::get_price_by_name($name, $tour_id, $hotel_id, $qty[$i], $start_date) * $qty[$i];
						if ($hotel_id > 0) {
							$price = $price * $num_of_day;
						}
						$total_price += $price;
					}
				}
				if ($total_qty > 0) {
					$total_price = apply_filters('ttbm_total_price_filter', $total_price, $tour_id, $total_qty);
				}
				foreach ($this->collect_extra_service_info($tour_id) as $service) {
					$total_price += $service['service_price'] * $service['service_qty'];
				}
				return (float) $total_price;
			}
			// phpcs:enable WordPress.Security.NonceVerification.Missing
		}
		TTBM_Custom_Checkout::instance();
	}
