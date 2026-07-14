<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Payment_Settings')) {
		class TTBM_Payment_Settings {
			/**
			 * Gateway spec for the Custom Payment cards (PayPal / Stripe / Offline).
			 * Mirrors the EventPress "Payment Gateways" cards field-for-field. These
			 * are stored settings only (saved into the ttbm_payment_settings option)
			 * — no processing engine is wired up yet, matching this plugin's
			 * WooCommerce-only checkout flow. All three (PayPal, Stripe, Offline
			 * Payment) require the Tour Pro addon (TTBM_Woocommerce_Plugin_Pro)
			 * to configure.
			 */
			private function gateway_specs() {
				return array(
					'paypal' => array(
						'label' => esc_html__('PayPal', 'tour-booking-manager'),
						'desc' => esc_html__('Accept payments via PayPal.', 'tour-booking-manager'),
						'enable_key' => 'ttbm_paypal_enable',
						'pro' => true,
						'fields' => array(
							array('key' => 'ttbm_paypal_sandbox', 'type' => 'toggle', 'label' => esc_html__('Sandbox / Test Mode', 'tour-booking-manager'), 'desc' => esc_html__('Use sandbox credentials for testing.', 'tour-booking-manager')),
							array('key' => 'ttbm_paypal_client_id', 'type' => 'text', 'label' => esc_html__('PayPal Client ID', 'tour-booking-manager'), 'placeholder' => esc_html__('Enter your PayPal Client ID', 'tour-booking-manager')),
							array('key' => 'ttbm_paypal_secret', 'type' => 'password', 'label' => esc_html__('PayPal Secret Key', 'tour-booking-manager'), 'placeholder' => esc_html__('Enter your PayPal Secret Key', 'tour-booking-manager')),
						),
					),
					'stripe' => array(
						'label' => esc_html__('Stripe', 'tour-booking-manager'),
						'desc' => esc_html__('Accept payments via Stripe.', 'tour-booking-manager'),
						'enable_key' => 'ttbm_stripe_enable',
						'pro' => true,
						'fields' => array(
							array('key' => 'ttbm_stripe_sandbox', 'type' => 'toggle', 'label' => esc_html__('Sandbox / Test Mode', 'tour-booking-manager'), 'desc' => esc_html__('Use test keys instead of live keys.', 'tour-booking-manager')),
							array('key' => 'ttbm_stripe_test_pub', 'type' => 'text', 'label' => esc_html__('Test Publishable Key', 'tour-booking-manager'), 'placeholder' => 'pk_test_...'),
							array('key' => 'ttbm_stripe_test_sec', 'type' => 'password', 'label' => esc_html__('Test Secret Key', 'tour-booking-manager'), 'placeholder' => 'sk_test_...'),
							array('key' => 'ttbm_stripe_live_pub', 'type' => 'text', 'label' => esc_html__('Live Publishable Key', 'tour-booking-manager'), 'placeholder' => 'pk_live_...'),
							array('key' => 'ttbm_stripe_live_sec', 'type' => 'password', 'label' => esc_html__('Live Secret Key', 'tour-booking-manager'), 'placeholder' => 'sk_live_...'),
						),
					),
					'offline' => array(
						'label' => esc_html__('Offline Payment', 'tour-booking-manager'),
						'desc' => esc_html__('Let customers pay offline (bank transfer, cash, pay at venue).', 'tour-booking-manager'),
						'enable_key' => 'ttbm_offline_enable',
						'pro' => true,
						'fields' => array(
							array('key' => 'ttbm_offline_label', 'type' => 'text', 'label' => esc_html__('Payment Label', 'tour-booking-manager'), 'placeholder' => esc_html__('e.g. Pay at Venue / Bank Transfer', 'tour-booking-manager'), 'desc' => esc_html__('Shown to customers on the frontend payment page.', 'tour-booking-manager')),
						),
					),
				);
			}
			private function is_pro_active() {
				return class_exists('TTBM_Woocommerce_Plugin_Pro');
			}
			private function opt($key, $default = '') {
				return TTBM_Global_Function::get_settings('ttbm_payment_settings', $key, $default);
			}
			public function __construct() {
				add_filter('ttbm_settings_sec_reg', array($this, 'register_section'), 95, 1);
				add_action('wsa_form_bottom_ttbm_payment_settings', array($this, 'render_tab_content'));
				add_action('wp_ajax_ttbm_save_gateway_settings', array($this, 'ajax_save_custom_gateway'));
				add_action('wp_ajax_ttbm_wc_save_gateway', array($this, 'ajax_save_gateway'));
				add_action('wp_ajax_ttbm_wc_toggle_gateway', array($this, 'ajax_toggle_gateway'));
				add_action('wp_ajax_ttbm_save_book_status', array($this, 'ajax_save_book_status'));
				add_action('wp_ajax_ttbm_save_booking_mode', array($this, 'ajax_save_booking_mode'));
				add_action('wp_ajax_ttbm_save_misc_fields', array($this, 'ajax_save_misc_fields'));
				add_action('wp_ajax_ttbm_portal_login', array($this, 'ajax_portal_login'));
				add_action('wp_ajax_nopriv_ttbm_portal_login', array($this, 'ajax_portal_login'));
				add_action('wp_ajax_ttbm_portal_register', array($this, 'ajax_portal_register'));
				add_action('wp_ajax_nopriv_ttbm_portal_register', array($this, 'ajax_portal_register'));
				// Logged-in only, deliberately no _nopriv sibling — see the
				// docblock on ajax_render_book_now() for why it also has no nonce.
				add_action('wp_ajax_ttbm_render_book_now', array($this, 'ajax_render_book_now'));
				add_action('wp_enqueue_scripts', array($this, 'enqueue_login_gate_assets'));
				add_action('admin_init', array($this, 'maybe_migrate_booking_mode'));
				add_action('admin_notices', array($this, 'maybe_render_gateway_notice'));
				add_action('edit_form_top', array($this, 'maybe_render_edit_payment_notice'));
				add_action('admin_enqueue_scripts', array($this, 'maybe_enqueue_edit_payment_assets'));
				add_action('ttbm_right_sidebar_content', array($this, 'render_payment_sidebar_card'), 16);
				add_action('ttbm_hotel_right_sidebar_content', array($this, 'render_payment_sidebar_card'), 16);
				add_filter('woocommerce_add_to_cart_redirect', array($this, 'add_to_cart_redirect'));
				// Real WooCommerce orders always use WooCommerce's own order-received/
				// thank-you page — no filter on woocommerce_get_checkout_order_received_url
				// here. The "Booking Confirmation" page (TTBM_Custom_Order_Confirmation,
				// Pro-only) is exclusively for the custom (non-WooCommerce) payment
				// checkout flow, which redirects itself and never touches WooCommerce's
				// order-received URL at all.
				add_filter('woocommerce_checkout_fields', array($this, 'maybe_hide_billing_fields'));
			}
			//------------------------------------------------------------------
			// Booking mode (WooCommerce Checkout vs Custom Payment)
			//------------------------------------------------------------------
			// True once Pro is active — Pro hooks this filter itself (see
			// TTBM_Custom_Checkout::filter_custom_payment_available()). This is
			// deliberately just "can the custom-payment flow exist at all", NOT
			// "is a gateway configured" — matching rbfw_booking_and_rental's
			// has_pro() gate. A missing gateway is a separate warning shown
			// inside the selector, not a reason to hide the selector itself.
			public static function custom_payment_available() {
				return (bool) apply_filters('ttbm_custom_payment_available', false);
			}
			// Whether at least one gateway is actually enabled for $mode — used
			// only for the inline warning, never to hide/block the selector.
			public static function has_gateway_for_mode($mode) {
				if ($mode === 'custom') {
					return (bool) apply_filters('ttbm_custom_payment_has_gateway', false);
				}
				if (!TTBM_Global_Function::has_woocommerce() || !function_exists('WC') || !WC()->payment_gateways()) {
					return false;
				}
				return !empty(WC()->payment_gateways()->get_available_payment_gateways());
			}
			// The four states the selector can be in — mirrors the reference
			// plugins' mode_availability(): 'both' is the only state where the
			// admin has a real choice; the other three auto-resolve.
			public static function mode_availability() {
				$wc = TTBM_Global_Function::has_woocommerce();
				$custom = self::custom_payment_available();
				if ($wc && $custom) {
					return 'both';
				}
				if ($wc) {
					return 'woocommerce_only';
				}
				if ($custom) {
					return 'custom_only';
				}
				return 'none';
			}
			// The effective booking mode: 'woocommerce' or 'custom'. Auto-resolves
			// to whichever path can actually run when there's no real choice, so a
			// stored preference never wins over reality (e.g. Pro deactivated after
			// Custom Payment was selected).
			public static function get_booking_mode() {
				if (!TTBM_Global_Function::has_woocommerce()) {
					return 'custom';
				}
				if (!self::custom_payment_available()) {
					return 'woocommerce';
				}
				$opts = (array) get_option('ttbm_payment_settings', array());
				return (isset($opts['ttbm_booking_mode']) && $opts['ttbm_booking_mode'] === 'custom') ? 'custom' : 'woocommerce';
			}
			// One-time migration from the legacy "Enable WooCommerce Payment"
			// toggle: toggle off already meant Custom Payment was the effective
			// checkout path (see the pre-migration TTBM_Custom_Checkout::
			// wc_payment_enabled()), so a site with the toggle off must land in
			// Custom Payment mode here too, not silently flip back to WooCommerce.
			public function maybe_migrate_booking_mode() {
				$opts = (array) get_option('ttbm_payment_settings', array());
				if (isset($opts['ttbm_booking_mode'])) {
					return;
				}
				$legacy_enabled = !isset($opts['ttbm_wc_payment_enabled']) || $opts['ttbm_wc_payment_enabled'] === 'on';
				$opts['ttbm_booking_mode'] = $legacy_enabled ? 'woocommerce' : 'custom';
				update_option('ttbm_payment_settings', $opts);
			}
			// Mode-aware "no gateway configured" message — only ever describes the
			// currently ACTIVE mode, never both possible modes at once.
			public static function gateway_warning($mode = null) {
				$mode = $mode ?: self::get_booking_mode();
				if (self::has_gateway_for_mode($mode)) {
					return '';
				}
				return $mode === 'custom'
					? esc_html__('No custom payment gateway is configured yet — bookings cannot be paid for.', 'tour-booking-manager')
					: esc_html__('No WooCommerce payment gateway is enabled yet — bookings cannot be paid for.', 'tour-booking-manager');
			}

			/**
			 * True when the active booking mode has at least one usable gateway
			 * (mirrors service-booking-manager's has_functional_payment_method()).
			 */
			public static function has_functional_payment_method(): bool {
				return self::has_gateway_for_mode(self::get_booking_mode());
			}

			/**
			 * Human-readable label for the active booking mode.
			 */
			public static function get_booking_mode_label(): string {
				$mode = self::get_booking_mode();
				if ($mode === 'custom') {
					return __('Custom Payment', 'tour-booking-manager');
				}
				if ($mode === 'woocommerce') {
					return __('WooCommerce', 'tour-booking-manager');
				}
				return __('Not set', 'tour-booking-manager');
			}

			/**
			 * Names of gateways currently enabled for the active booking mode.
			 *
			 * @return string[]
			 */
			public static function get_active_gateway_names(): array {
				$mode = self::get_booking_mode();
				$names = array();
				if ($mode === 'woocommerce') {
					if (!function_exists('WC') || !WC()->payment_gateways()) {
						return $names;
					}
					foreach (WC()->payment_gateways()->payment_gateways() as $gateway) {
						if (isset($gateway->enabled) && $gateway->enabled === 'yes') {
							$names[] = $gateway->get_method_title();
						}
					}
					return $names;
				}
				$opts = (array) get_option('ttbm_payment_settings', array());
				$map = array(
					'ttbm_paypal_enable' => __('PayPal', 'tour-booking-manager'),
					'ttbm_stripe_enable' => __('Stripe', 'tour-booking-manager'),
					'ttbm_offline_enable' => __('Offline Payment', 'tour-booking-manager'),
				);
				foreach ($map as $key => $label) {
					if (isset($opts[$key]) && $opts[$key] === 'on') {
						$names[] = $label;
					}
				}
				return $names;
			}

			/**
			 * Compact Payment Method card for the tour edit right sidebar
			 * (matches service-booking-manager SME rail payment card).
			 */
			public function render_payment_sidebar_card($tour_id = 0) {
				$pm_active = self::has_functional_payment_method();
				$pm_type_label = self::get_booking_mode_label();
				$pm_gateway_names = self::get_active_gateway_names();
				?>
				<div class="ttbm-sb-card ttbm-sb-payment-card">
					<p class="ttbm-sb-card-title"><?php esc_html_e('Payment Method', 'tour-booking-manager'); ?></p>
					<div class="ttbm-sb-payment-info-list">
						<div class="ttbm-sb-payment-info-row">
							<span><?php esc_html_e('Active Method', 'tour-booking-manager'); ?></span>
							<strong><?php echo esc_html($pm_type_label); ?></strong>
						</div>
						<div class="ttbm-sb-payment-info-row">
							<span><?php esc_html_e('Active Gateway', 'tour-booking-manager'); ?></span>
							<strong><?php echo esc_html($pm_gateway_names ? implode(', ', $pm_gateway_names) : __('None', 'tour-booking-manager')); ?></strong>
						</div>
						<?php if ($pm_gateway_names) : ?>
							<p class="ttbm-sb-payment-link">
								<a href="#" data-ttbm-payment-modal-open><?php esc_html_e('Payment Settings', 'tour-booking-manager'); ?></a>
							</p>
						<?php endif; ?>
						<?php if (!$pm_active) : ?>
							<p class="ttbm-sb-payment-warning">
								<a href="#" data-ttbm-payment-modal-open><?php esc_html_e('Configure payment method', 'tour-booking-manager'); ?></a>
							</p>
						<?php endif; ?>
					</div>
				</div>
				<?php
			}

			private function is_tour_or_hotel_edit_screen(): bool {
				return self::is_tour_or_hotel_edit_screen_static();
			}

			private static function is_tour_or_hotel_edit_screen_static(): bool {
				$screen = function_exists('get_current_screen') ? get_current_screen() : null;
				if (!$screen || $screen->base !== 'post') {
					return false;
				}
				$cpt = class_exists('TTBM_Function') ? TTBM_Function::get_cpt_name() : 'ttbm_tour';
				return in_array($screen->post_type, array($cpt, 'ttbm_hotel'), true);
			}

			public function maybe_render_gateway_notice() {
				$screen = function_exists('get_current_screen') ? get_current_screen() : null;
				if (!$screen || strpos((string) $screen->id, 'ttbm_settings_page') === false) {
					return;
				}
				$warning = self::gateway_warning();
				if (!$warning) {
					return;
				}
				?>
				<div class="notice notice-warning ttbm-pay-gateway-notice"><p><?php echo esc_html($warning); ?></p></div>
				<?php
			}

			/**
			 * Yellow banner (when needed) + payment modal on tour/hotel edit screens.
			 * Banner markup is moved to the top of #poststuff via JS; the modal is
			 * always available so the sidebar Payment card can open it too.
			 */
			public function maybe_render_edit_payment_notice($post) {
				if (!$this->is_tour_or_hotel_edit_screen()) {
					return;
				}
				$needs_notice = !self::has_functional_payment_method();
				if ($needs_notice) {
					?>
					<div class="ttbm-edit-payment-notice" id="ttbm-edit-payment-notice">
						<?php esc_html_e('No payment method is currently configured.', 'tour-booking-manager'); ?>
						<a href="#" class="ttbm-edit-payment-notice-link" data-ttbm-payment-modal-open>
							<?php esc_html_e('Please configure a payment method to accept bookings.', 'tour-booking-manager'); ?>
						</a>
					</div>
					<?php
				}
				?>
				<div class="ttbm-edit-payment-modal" id="ttbm-edit-payment-modal" data-ttbm-payment-modal style="display:none;">
					<div class="ttbm-edit-payment-modal-box">
						<div class="ttbm-edit-payment-modal-head">
							<h2><?php esc_html_e('Payment Method', 'tour-booking-manager'); ?></h2>
							<button type="button" class="ttbm-edit-payment-modal-close" data-ttbm-payment-modal-close aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">&times;</button>
						</div>
						<div class="ttbm-edit-payment-modal-body" id="ttbm-edit-payment-modal-body">
							<?php $this->render_tab_content(); ?>
						</div>
					</div>
				</div>
				<?php
			}

			public function maybe_enqueue_edit_payment_assets($hook) {
				if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
					return;
				}
				if (!$this->is_tour_or_hotel_edit_screen()) {
					return;
				}
				wp_enqueue_style(
					'ttbm-global-settings',
					TTBM_PLUGIN_URL . '/assets/admin/ttbm-global-settings.css',
					array(),
					filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-global-settings.css')
				);
				if (TTBM_Global_Function::has_woocommerce()) {
					wp_enqueue_style('woocommerce_admin_styles');
					wp_enqueue_script('wc-enhanced-select');
					wp_enqueue_script('wc-jquery-tiptip');
				}
				wp_enqueue_script(
					'ttbm-payment-settings',
					TTBM_PLUGIN_URL . '/assets/admin/ttbm-payment-settings.js',
					array('jquery', 'ttbm_hotel_booking', 'ttbm-admin-toast'),
					filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-payment-settings.js'),
					true
				);
				wp_localize_script('ttbm-payment-settings', 'ttbmPaymentSettings', array(
					'enabled_label' => esc_html__('Enabled', 'tour-booking-manager'),
					'disabled_label' => esc_html__('Disabled', 'tour-booking-manager'),
					'error_label' => esc_html__('An error occurred. Please try again.', 'tour-booking-manager'),
					'saving_label' => esc_html__('Saving…', 'tour-booking-manager'),
					/* translators: %s: booking mode name, e.g. "WooCommerce Checkout" */
					'mode_saved_label' => esc_html__('Booking mode changed to %s.', 'tour-booking-manager'),
					'wc_mode_label' => esc_html__('WooCommerce Checkout', 'tour-booking-manager'),
					'custom_mode_label' => esc_html__('Custom Payment', 'tour-booking-manager'),
					'active_label' => esc_html__('Active', 'tour-booking-manager'),
				));
				wp_add_inline_style('ttbm-global-settings', $this->edit_payment_notice_css());
				wp_add_inline_script('ttbm-payment-settings', $this->edit_payment_notice_js());
			}

			private function edit_payment_notice_css(): string {
				return <<<'CSS'
.ttbm-edit-payment-notice{text-align:center;background:#fef3c7;color:#b45309;font-size:13px;font-weight:600;padding:10px 26px;margin:0 0 12px;border-radius:0;}
.ttbm-edit-payment-notice-link{color:#b45309;text-decoration:underline;margin-left:4px;}
.ttbm-edit-payment-notice-link:hover{color:#92400e;}
.ttbm-edit-payment-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:100001;align-items:center;justify-content:center;padding:20px;}
.ttbm-edit-payment-modal-box{background:#fff;border-radius:12px;max-width:860px;width:100%;max-height:88vh;overflow-y:auto;box-shadow:0 24px 60px rgba(15,23,42,.35);}
.ttbm-edit-payment-modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #e5e7eb;position:sticky;top:0;background:#fff;z-index:1;}
.ttbm-edit-payment-modal-head h2{margin:0;font-size:17px;font-weight:700;color:#111827;}
.ttbm-edit-payment-modal-close{border:none;background:transparent;font-size:22px;line-height:1;cursor:pointer;color:#6b7280;padding:4px 8px;}
.ttbm-edit-payment-modal-close:hover{color:#111827;}
.ttbm-edit-payment-modal-body{padding:20px 24px 28px;}
body.ttbm-modern-edit-page #poststuff > .ttbm-edit-payment-notice{margin-left:-20px;margin-right:-20px;width:calc(100% + 40px);box-sizing:border-box;}
CSS;
			}

			private function edit_payment_notice_js(): string {
				return <<<'JS'
jQuery(function ($) {
	var $notice = $('#ttbm-edit-payment-notice');
	var $modal = $('#ttbm-edit-payment-modal');
	if ($notice.length && $('#poststuff').length) {
		$('#poststuff').prepend($notice);
	}
	if ($modal.length) {
		$modal.appendTo('body');
	}
	if (!$modal.length) { return; }
	$(document).on('click', '[data-ttbm-payment-modal-open]', function (e) {
		e.preventDefault();
		$modal.css('display', 'flex');
	});
	$(document).on('click', '[data-ttbm-payment-modal-close]', function () {
		$modal.hide();
	});
	$modal.on('click', function (e) {
		if (e.target === this) { $modal.hide(); }
	});
	$(document).on('keydown', function (e) {
		if ((e.key === 'Escape' || e.keyCode === 27) && $modal.is(':visible')) {
			$modal.hide();
		}
	});
});
JS;
			}

			// The choice is only meaningful when both systems are available;
			// otherwise the mode is auto-resolved and shouldn't be overridden —
			// matches the reference plugins exactly (they don't block on a
			// missing gateway either, only warn about it after saving).
			public function ajax_save_booking_mode() {
				check_ajax_referer('ttbm_save_booking_mode', 'nonce');
				if (!current_user_can('manage_options')) {
					wp_send_json_error(esc_html__('Permission denied.', 'tour-booking-manager'));
				}
				$mode = (isset($_POST['mode']) && sanitize_key(wp_unslash($_POST['mode'])) === 'custom') ? 'custom' : 'woocommerce';
				if (self::mode_availability() !== 'both') {
					wp_send_json_error(esc_html__('Booking mode can only be changed when both WooCommerce and the Pro custom gateways are available.', 'tour-booking-manager'));
				}
				$opts = (array) get_option('ttbm_payment_settings', array());
				$opts['ttbm_booking_mode'] = $mode;
				update_option('ttbm_payment_settings', $opts);
				wp_send_json_success(array(
					'mode' => $mode,
					'has_gateway' => self::has_gateway_for_mode($mode),
					'warning' => self::gateway_warning($mode),
				));
			}
			public function register_section($sections) {
				$sections[] = array(
					'id' => 'ttbm_payment_settings',
					'title' => esc_html__('Payments', 'tour-booking-manager'),
					'icon' => 'mi mi-credit-card',
					'desc' => esc_html__('Manage how bookings are paid for — WooCommerce checkout and payment gateways.', 'tour-booking-manager'),
				);
				return $sections;
			}
			/**
			 * Everything below is hand-rendered (instead of declared via
			 * TTBM_Setting_API's add_field) so the WooCommerce/Custom Payment
			 * sub-tabs and accordions can be laid out exactly as designed. All
			 * inputs still use name="ttbm_payment_settings[...]" so WordPress's
			 * Settings API (already registered for this section by
			 * TTBM_Setting_API::admin_init()) saves them normally on submit.
			 */
			public function render_tab_content() {
				// Default sub-tab follows the active Booking Mode, not always
				// WooCommerce — an admin running Custom Payment shouldn't land
				// on the WooCommerce sub-tab on every page load.
				$custom_is_default = self::get_booking_mode() === 'custom';
				?>
                <div class="ttbm-pay-subtabs nav-tab-wrapper">
                    <a href="#" class="ttbm-pay-subtab-link<?php echo $custom_is_default ? '' : ' is-active'; ?>" data-subtab="woocommerce"><?php esc_html_e('WooCommerce', 'tour-booking-manager'); ?></a>
                    <a href="#" class="ttbm-pay-subtab-link<?php echo $custom_is_default ? ' is-active' : ''; ?>" data-subtab="custom"><?php esc_html_e('Custom Payment', 'tour-booking-manager'); ?></a>
                </div>

				<?php $this->render_booking_mode_selector(); ?>

                <div class="ttbm-pay-subtab-panel" data-subtab-panel="woocommerce" <?php echo $custom_is_default ? 'style="display:none;"' : ''; ?>>
					<?php $this->render_woocommerce_subtab(); ?>
                </div>
                <div class="ttbm-pay-subtab-panel" data-subtab-panel="custom" <?php echo $custom_is_default ? '' : 'style="display:none;"'; ?>>
					<?php $this->render_custom_payment_subtab(); ?>
                </div>
				<?php
			}
			// Full-width, sits directly below the WooCommerce/Custom-Payment
			// sub-tab bar. Always renders as a titled "Booking Mode" section
			// with both cards — when mode_availability() isn't 'both', the
			// unavailable card renders disabled (with an explanation) instead
			// of the whole section collapsing into a single line of text, so
			// the feature is always visibly present, matching the reference
			// plugins' card layout in every state.
			private function render_booking_mode_selector() {
				$availability = self::mode_availability();
				$wc_active = TTBM_Global_Function::has_woocommerce();
				$custom_active = self::custom_payment_available();
				$mode = self::get_booking_mode();
				$is_wc = $mode === 'woocommerce';
				$is_custom = $mode === 'custom';
				$can_choose = 'both' === $availability;
				$has_gateway = self::has_gateway_for_mode($mode);
				?>
                <div class="ttbm-booking-mode <?php echo $can_choose ? '' : 'is-locked'; ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_save_booking_mode')); ?>" data-can-choose="<?php echo $can_choose ? '1' : '0'; ?>">
                    <div class="ttbm-booking-mode-head">
                        <h3><?php esc_html_e('Booking Mode', 'tour-booking-manager'); ?></h3>
						<?php if ($can_choose) : ?>
                            <p><?php esc_html_e('Choose exactly one flow to process bookings. This single switch decides everything below, so WooCommerce and Custom Payment never both try to handle the same booking. Your choice is saved instantly.', 'tour-booking-manager'); ?></p>
						<?php elseif ('none' === $availability) : ?>
                            <p class="ttbm-booking-mode-auto"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('No booking flow is available yet: WooCommerce is not active and the Tour Pro addon is not active. Activate one of them to start taking bookings.', 'tour-booking-manager'); ?></p>
						<?php elseif ('woocommerce_only' === $availability) : ?>
                            <p class="ttbm-booking-mode-auto"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('WooCommerce is the only booking flow available right now — bookings are processed through it automatically. Activate the Tour Pro addon to unlock Custom Payment and a mode switch here.', 'tour-booking-manager'); ?></p>
						<?php else : ?>
                            <p class="ttbm-booking-mode-auto"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e('Custom Payment is the only booking flow available right now — WooCommerce is not active, so bookings are processed through Custom Payment automatically. Activate WooCommerce to unlock a mode switch here.', 'tour-booking-manager'); ?></p>
						<?php endif; ?>
                    </div>
                    <div class="ttbm-booking-mode-cards">
                        <div class="ttbm-booking-mode-card <?php echo $is_wc ? 'is-active' : ''; ?> <?php echo $wc_active ? '' : 'is-disabled'; ?>" data-mode="woocommerce" data-subtab="woocommerce">
                            <span class="ttbm-booking-mode-card-icon dashicons dashicons-cart"></span>
                            <div class="ttbm-booking-mode-card-body">
                                <div class="ttbm-booking-mode-card-head">
                                    <span class="ttbm-booking-mode-card-title"><?php esc_html_e('WooCommerce Checkout', 'tour-booking-manager'); ?></span>
									<?php if ($is_wc) : ?>
                                        <span class="ttbm-booking-mode-badge"><?php esc_html_e('Active', 'tour-booking-manager'); ?></span>
									<?php endif; ?>
                                </div>
                                <p class="ttbm-booking-mode-card-desc">
									<?php if ($wc_active) : ?>
										<?php esc_html_e('Bookings go through the WooCommerce cart and checkout, using WooCommerce\'s own payment gateways.', 'tour-booking-manager'); ?>
									<?php else : ?>
										<?php esc_html_e('Requires WooCommerce to be active.', 'tour-booking-manager'); ?>
									<?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <div class="ttbm-booking-mode-card <?php echo $is_custom ? 'is-active' : ''; ?> <?php echo $custom_active ? '' : 'is-disabled'; ?>" data-mode="custom" data-subtab="custom">
                            <span class="ttbm-booking-mode-card-icon dashicons dashicons-money-alt"></span>
                            <div class="ttbm-booking-mode-card-body">
                                <div class="ttbm-booking-mode-card-head">
                                    <span class="ttbm-booking-mode-card-title"><?php esc_html_e('Custom Payment', 'tour-booking-manager'); ?></span>
									<?php if ($is_custom) : ?>
                                        <span class="ttbm-booking-mode-badge"><?php esc_html_e('Active', 'tour-booking-manager'); ?></span>
									<?php endif; ?>
                                </div>
                                <p class="ttbm-booking-mode-card-desc">
									<?php if ($custom_active) : ?>
										<?php esc_html_e('Bookings skip WooCommerce entirely and pay through PayPal, Stripe, or Offline directly.', 'tour-booking-manager'); ?>
									<?php else : ?>
										<?php esc_html_e('Requires the Tour Pro addon to be active.', 'tour-booking-manager'); ?>
									<?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="ttbm-booking-mode-msg" aria-live="polite"></p>
                    <div class="ttbm-booking-mode-warning-slot">
						<?php if (!$has_gateway) : ?>
                            <div class="ttbm-booking-mode-warning">
                                <span class="dashicons dashicons-warning"></span>
                                <p><?php echo esc_html(self::gateway_warning($mode)); ?></p>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
				<?php
			}
			private function render_woocommerce_subtab() {
				if (!TTBM_Global_Function::has_woocommerce()) {
					$this->render_wc_inactive_notice();
					return;
				}
				?>
                <div class="ttbm-pay-accordion">
                    <button type="button" class="ttbm-pay-acc-header is-open" data-acc="methods">
                        <span><?php esc_html_e('WooCommerce Payment Methods', 'tour-booking-manager'); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <div class="ttbm-pay-acc-body" data-acc-body="methods">
						<?php $this->render_wc_payment_methods(); ?>
                    </div>
                </div>
                <div class="ttbm-pay-accordion">
                    <button type="button" class="ttbm-pay-acc-header" data-acc="additional">
                        <span><?php esc_html_e('Additional Settings', 'tour-booking-manager'); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <div class="ttbm-pay-acc-body" data-acc-body="additional" style="display:none;">
						<?php $this->render_additional_settings(); ?>
                    </div>
                </div>
				<?php
			}
			private function render_wc_inactive_notice() {
				$is_installed = file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');
				$modal_btn = $is_installed
					? esc_html__('Activate WooCommerce Now', 'tour-booking-manager')
					: esc_html__('Install & Activate Now', 'tour-booking-manager');
				?>
                <div class="ttbm-pay-wc-warning">
                    <p class="ttbm-pay-wc-warning-title"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Notice: WooCommerce is Not Activated', 'tour-booking-manager'); ?></p>
                    <p class="ttbm-pay-wc-warning-desc"><?php esc_html_e('To process payments and manage payment gateways here, you must install and activate WooCommerce.', 'tour-booking-manager'); ?></p>
                    <button type="button" id="ttbm-woo-install-trigger" class="button button-primary">
						<?php echo esc_html($modal_btn); ?>
                    </button>
                </div>

                <div id="ttbm-woo-install-modal" class="ttbm-gw-modal" style="display:none;">
                    <div class="ttbm-gw-modal-box ttbm-woo-modal-box">
                        <div class="ttbm-gw-modal-header">
                            <h2><span class="dashicons dashicons-plugins-checked"></span> <?php esc_html_e('Set Up WooCommerce', 'tour-booking-manager'); ?></h2>
                            <button type="button" id="ttbm-woo-install-modal-close" class="ttbm-gw-modal-close">&times;</button>
                        </div>
                        <div class="ttbm-gw-modal-body">
                            <div id="ttbm-woo-modal-info">
                                <p class="ttbm-woo-modal-desc">
									<?php
									echo $is_installed
										? esc_html__('WooCommerce is already installed but not active. Click below to activate it now.', 'tour-booking-manager')
										: esc_html__('WooCommerce is required to process payments. We will securely download, install, and activate it for you now.', 'tour-booking-manager');
									?>
                                </p>
                                <button type="button" id="ttbm-woo-modal-action-btn" class="button button-primary" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_woo_step')); ?>">
									<?php echo esc_html($modal_btn); ?>
                                </button>
                            </div>
                            <div id="ttbm-woo-modal-progress" style="display:none;">
                                <div class="ttbm-pay-progress-track"><div class="ttbm-pay-progress-fill" id="ttbm-woo-modal-progress-fill"></div></div>
                                <p id="ttbm-woo-modal-status-text"></p>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			private function render_additional_settings() {
				$cart_redirect = $this->opt('ttbm_payment_cart_redirect', 'checkout');
				$show_billing = $this->opt('ttbm_payment_show_billing_info', 'on') === 'on';
				$book_status_opts = get_option('ttbm_basic_gen_settings', array());
				$book_status = isset($book_status_opts['ttbm_set_book_status']) && is_array($book_status_opts['ttbm_set_book_status'])
					? $book_status_opts['ttbm_set_book_status']
					: array('processing' => 'processing', 'completed' => 'completed');
				$status_options = array(
					'pending' => esc_html__('Pending payment', 'tour-booking-manager'),
					'processing' => esc_html__('Processing', 'tour-booking-manager'),
					'on-hold' => esc_html__('On hold', 'tour-booking-manager'),
					'completed' => esc_html__('Completed', 'tour-booking-manager'),
				);
				?>
                <div class="ttbm-pay-field-row">
                    <label class="ttbm-pay-field-label"><?php esc_html_e('After Adding to Cart, Redirect to', 'tour-booking-manager'); ?></label>
                    <div class="ttbm-pay-field-control">
                        <select name="ttbm_payment_settings[ttbm_payment_cart_redirect]" class="formControl">
                            <option value="checkout" <?php selected($cart_redirect, 'checkout'); ?>><?php esc_html_e('Checkout', 'tour-booking-manager'); ?></option>
                            <option value="cart" <?php selected($cart_redirect, 'cart'); ?>><?php esc_html_e('Cart', 'tour-booking-manager'); ?></option>
                        </select>
                        <p class="ttbm-pay-field-desc"><?php esc_html_e('Select where to redirect after adding a tour to cart.', 'tour-booking-manager'); ?></p>
                    </div>
                </div>
                <div class="ttbm-pay-field-row">
                    <label class="ttbm-pay-field-label"><?php esc_html_e('Show Billing Info', 'tour-booking-manager'); ?></label>
                    <div class="ttbm-pay-field-control">
                        <label class="ttbm-pay-checkbox">
                            <input type="checkbox" name="ttbm_payment_settings[ttbm_payment_show_billing_info]" value="on" <?php checked($show_billing); ?>>
							<?php esc_html_e('Show billing info on the WooCommerce checkout page.', 'tour-booking-manager'); ?>
                        </label>
                    </div>
                </div>
                <div class="justifyBetween _mT">
                    <div></div>
                    <button type="button" class="button button-primary ttbm-pay-misc-save-btn" data-fields="ttbm_payment_cart_redirect,ttbm_payment_show_billing_info" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_admin_nonce')); ?>"><?php esc_html_e('Save Changes', 'tour-booking-manager'); ?></button>
                </div>
                <div class="ttbm-pay-field-row">
                    <label class="ttbm-pay-field-label"><?php esc_html_e('Confirm Ticket Based on Payment Status', 'tour-booking-manager'); ?></label>
                    <div class="ttbm-pay-field-control">
                        <div class="ttbm-pay-book-status" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_admin_nonce')); ?>">
							<?php foreach ($status_options as $key => $label) : ?>
                                <label class="ttbm-pay-checkbox">
                                    <input type="checkbox" class="ttbm-pay-book-status-input" data-key="<?php echo esc_attr($key); ?>" <?php checked(isset($book_status[$key])); ?>>
									<?php echo esc_html($label); ?>
                                </label>
							<?php endforeach; ?>
                            <span class="ttbm-pay-book-status-msg"></span>
                        </div>
                        <p class="ttbm-pay-field-desc"><?php esc_html_e('Select the order statuses that confirm a booking and reduce seat availability. Shared with the Seat Booked Status field on the General settings tab — saved instantly.', 'tour-booking-manager'); ?></p>
                    </div>
                </div>
				<?php
			}
			private function render_custom_payment_subtab() {
				$specs = $this->gateway_specs();
				$opts = (array) get_option('ttbm_payment_settings', array());
				$is_pro = $this->is_pro_active();
				$allow_guest_booking = $this->opt('ttbm_payment_allow_guest_booking', 'yes');
				?>
                <div class="ttbm-pm-wrap ttbm-pm-gateways">
					<?php foreach ($specs as $gateway_id => $spec) :
						$is_enabled = isset($opts[$spec['enable_key']]) && $opts[$spec['enable_key']] === 'on';
						$locked = !empty($spec['pro']) && !$is_pro;
						?>
                        <div class="ttbm-pm-card ttbm-pm-card-<?php echo esc_attr($gateway_id); ?> <?php echo $is_enabled ? 'is-enabled' : 'is-disabled'; ?>" data-gateway-id="<?php echo esc_attr($gateway_id); ?>">
                            <div class="ttbm-pm-head">
                                <div class="ttbm-pm-head-main">
                                    <span class="ttbm-pm-title"><?php echo esc_html($spec['label']); ?></span>
                                </div>
								<?php if ($locked) : ?>
                                    <span class="ttbm-pm-pro-badge"><?php esc_html_e('PRO', 'tour-booking-manager'); ?></span>
								<?php else : ?>
                                    <span class="ttbm-pm-badge"><?php echo $is_enabled ? esc_html__('Enabled', 'tour-booking-manager') : esc_html__('Disabled', 'tour-booking-manager'); ?></span>
                                    <button type="button" class="button ttbm-pm-configure-btn" data-modal="ttbm-gw-modal-<?php echo esc_attr($gateway_id); ?>"><?php esc_html_e('Configure', 'tour-booking-manager'); ?></button>
								<?php endif; ?>
                            </div>
                            <div class="ttbm-pm-desc"><?php echo esc_html($spec['desc']); ?></div>
                        </div>
					<?php endforeach; ?>
                </div>

				<?php foreach ($specs as $gateway_id => $spec) :
					if (!empty($spec['pro']) && !$is_pro) {
						continue; // Locked behind Pro — no modal to configure yet.
					}
					$is_enabled = isset($opts[$spec['enable_key']]) && $opts[$spec['enable_key']] === 'on';
					?>
                    <div id="ttbm-gw-modal-<?php echo esc_attr($gateway_id); ?>" class="ttbm-gw-modal" style="display:none;">
                        <div class="ttbm-gw-modal-box">
                            <div class="ttbm-gw-modal-header">
                                <h2><?php echo esc_html($spec['label']); ?> <?php esc_html_e('Configuration', 'tour-booking-manager'); ?></h2>
                                <button type="button" class="ttbm-gw-modal-close">&times;</button>
                            </div>
                            <div class="ttbm-gw-modal-body">
                                <div class="ttbm-gw-toggle-row">
                                    <div>
                                        <div class="ttbm-gw-toggle-label"><?php
											/* translators: %s: gateway name, e.g. PayPal */
											echo esc_html(sprintf(__('Enable %s', 'tour-booking-manager'), $spec['label'])); ?></div>
                                        <div class="ttbm-gw-toggle-sub"><?php echo esc_html($spec['desc']); ?></div>
                                    </div>
                                    <label class="ttbm-gw-switch">
                                        <input type="checkbox" data-field="<?php echo esc_attr($spec['enable_key']); ?>" <?php checked($is_enabled); ?>>
                                        <span class="ttbm-gw-slider"></span>
                                    </label>
                                </div>
								<?php foreach ($spec['fields'] as $field) :
									$value = isset($opts[$field['key']]) ? $opts[$field['key']] : '';
									?>
									<?php if ('toggle' === $field['type']) : ?>
                                        <div class="ttbm-gw-toggle-row">
                                            <div>
                                                <div class="ttbm-gw-toggle-label"><?php echo esc_html($field['label']); ?></div>
												<?php if (!empty($field['desc'])) : ?>
                                                    <div class="ttbm-gw-toggle-sub"><?php echo esc_html($field['desc']); ?></div>
												<?php endif; ?>
                                            </div>
                                            <label class="ttbm-gw-switch">
                                                <input type="checkbox" data-field="<?php echo esc_attr($field['key']); ?>" <?php checked($value === 'on'); ?>>
                                                <span class="ttbm-gw-slider"></span>
                                            </label>
                                        </div>
									<?php else : ?>
                                        <div class="ttbm-gw-field">
                                            <label class="ttbm-gw-label"><?php echo esc_html($field['label']); ?></label>
                                            <input type="<?php echo esc_attr($field['type']); ?>" data-field="<?php echo esc_attr($field['key']); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>">
											<?php if (!empty($field['desc'])) : ?>
                                                <p class="ttbm-gw-field-desc"><?php echo esc_html($field['desc']); ?></p>
											<?php endif; ?>
                                        </div>
									<?php endif; ?>
								<?php endforeach; ?>
                            </div>
                            <div class="ttbm-gw-modal-footer">
                                <button type="button" class="button button-primary ttbm-gw-save-btn" data-gateway="<?php echo esc_attr($gateway_id); ?>"><?php
									/* translators: %s: gateway name, e.g. PayPal */
									echo esc_html(sprintf(__('Save %s Settings', 'tour-booking-manager'), $spec['label'])); ?></button>
                                <span class="ttbm-gw-save-msg"></span>
                            </div>
                        </div>
                    </div>
				<?php endforeach; ?>

				<?php
				$confirmation_page = (int) $this->opt('ttbm_payment_confirmation_page', 0);
				?>
                <div class="ttbm-pay-field-row ttbm-pay-confirmation-page">
                    <label class="ttbm-pay-field-label"><?php esc_html_e('Booking Confirmation Page', 'tour-booking-manager'); ?></label>
                    <div class="ttbm-pay-field-control">
						<?php
						// wp_dropdown_pages() already escapes its own output; wp_kses_post()
						// would strip <select>/<option> entirely since they're not in its
						// allowed-HTML list, leaving just a jumble of page-title text.
						echo wp_dropdown_pages(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'selected' => $confirmation_page,
							'name' => 'ttbm_payment_settings[ttbm_payment_confirmation_page]',
							'class' => 'formControl',
							'show_option_none' => esc_html__('— Select a page —', 'tour-booking-manager'),
							'option_none_value' => 0,
							'echo' => 0,
						));
						?>
                        <p class="ttbm-pay-field-desc"><?php esc_html_e('Used only by the Custom Payment checkout (PayPal/Stripe/Offline via Pro). Regular WooCommerce bookings always use WooCommerce\'s own order-received page.', 'tour-booking-manager'); ?></p>
                    </div>
                </div>
                <div class="ttbm-pay-field-row">
                    <label class="ttbm-pay-field-label"><?php esc_html_e('Allow Guest Booking', 'tour-booking-manager'); ?></label>
                    <div class="ttbm-pay-field-control">
                        <select name="ttbm_payment_settings[ttbm_payment_allow_guest_booking]" class="formControl">
                            <option value="yes" <?php selected($allow_guest_booking, 'yes'); ?>><?php esc_html_e('Yes — anyone can book without an account', 'tour-booking-manager'); ?></option>
                            <option value="no" <?php selected($allow_guest_booking, 'no'); ?>><?php esc_html_e('No — require login or registration to book', 'tour-booking-manager'); ?></option>
                        </select>
                        <p class="ttbm-pay-field-desc"><?php esc_html_e('Custom Payment only (PayPal/Stripe/Offline). When set to No, a logged-out visitor sees an inline log in / register panel when they click to book — never a page reload. WooCommerce checkout has its own separate guest-checkout setting.', 'tour-booking-manager'); ?></p>
                    </div>
                </div>
                <div class="justifyBetween _mT">
                    <div></div>
                    <button type="button" class="button button-primary ttbm-pay-misc-save-btn" data-fields="ttbm_payment_confirmation_page,ttbm_payment_allow_guest_booking" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_admin_nonce')); ?>"><?php esc_html_e('Save Changes', 'tour-booking-manager'); ?></button>
                </div>
				<?php
			}
			/**
			 * WooCommerce keeps its currently enabled gateways in registration order;
			 * `woocommerce_gateway_order` holds the admin's drag-sorted order, so we
			 * mirror WooCommerce's own Payments tab ordering instead of PHP's default.
			 */
			private function get_gateways() {
				if (!TTBM_Global_Function::has_woocommerce() || !method_exists(WC(), 'payment_gateways') || !WC()->payment_gateways()) {
					return array();
				}
				$gateways = WC()->payment_gateways()->payment_gateways();

				// WooCommerce core suppresses some of its own built-in gateways
				// (notably PayPal Standard) from the loaded/active list by default
				// — e.g. for store currencies it doesn't support — even though the
				// class is still available and configurable. Surface them here too
				// so the manager still offers a Configure card for them.
				$wc_defaults = array('WC_Gateway_BACS', 'WC_Gateway_Cheque', 'WC_Gateway_COD', 'WC_Gateway_Paypal');
				$gateway_classes = apply_filters('woocommerce_payment_gateways', $wc_defaults);
				foreach ($gateway_classes as $class) {
					if (!is_string($class) || !class_exists($class)) {
						continue;
					}
					$already = false;
					foreach ($gateways as $g) {
						if ($g instanceof $class) {
							$already = true;
							break;
						}
					}
					if ($already) {
						continue;
					}
					$instance = new $class();
					if ($instance instanceof WC_Payment_Gateway && !isset($gateways[$instance->id])) {
						$gateways[$instance->id] = $instance;
					}
				}

				$order = (array) get_option('woocommerce_gateway_order', array());
				if (!empty($order)) {
					uasort($gateways, function ($a, $b) use ($order) {
						$pa = isset($order[$a->id]) ? (int) $order[$a->id] : 999;
						$pb = isset($order[$b->id]) ? (int) $order[$b->id] : 999;
						return $pa <=> $pb;
					});
				}
				return $gateways;
			}
			public function render_wc_payment_methods() {
				$gateways = $this->get_gateways();
				if (empty($gateways)) {
					?>
                    <p class="ttbm-pm-inactive-notice"><?php esc_html_e('No WooCommerce payment gateways found.', 'tour-booking-manager'); ?></p>
					<?php
					return;
				}
				?>
                <div class="ttbm-pm-wrap">
                    <div class="ttbm-pm-bar">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wc-settings&tab=checkout')); ?>" class="button button-small ttbm-pm-wc-link" target="_blank" rel="noopener">
							<?php esc_html_e('Open in WooCommerce', 'tour-booking-manager'); ?>
                            <span class="dashicons dashicons-external"></span>
                        </a>
                    </div>
					<?php foreach ($gateways as $gateway) :
						$is_enabled = $gateway->enabled === 'yes';
						$desc = $gateway->get_method_description() ?: $gateway->get_description();
						?>
                        <div class="ttbm-pm-card <?php echo $is_enabled ? 'is-enabled' : 'is-disabled'; ?>" data-gateway-id="<?php echo esc_attr($gateway->id); ?>">
                            <div class="ttbm-pm-head">
                                <div class="ttbm-pm-head-main">
                                    <label class="roundSwitchLabel ttbm-pm-toggle" title="<?php esc_attr_e('Enable / disable', 'tour-booking-manager'); ?>">
                                        <input type="checkbox" class="ttbm-pm-toggle-input" data-gateway-id="<?php echo esc_attr($gateway->id); ?>" <?php checked($is_enabled); ?>>
                                        <span class="roundSwitch"></span>
                                    </label>
                                    <span class="ttbm-pm-title"><?php echo esc_html($gateway->get_method_title() ?: $gateway->get_title()); ?></span>
                                    <span class="ttbm-pm-badge"><?php echo $is_enabled ? esc_html__('Enabled', 'tour-booking-manager') : esc_html__('Disabled', 'tour-booking-manager'); ?></span>
                                </div>
                                <button type="button" class="button ttbm-pm-configure-btn"><?php esc_html_e('Configure', 'tour-booking-manager'); ?></button>
                            </div>
							<?php if ($desc) : ?>
                                <div class="ttbm-pm-desc"><?php echo wp_kses_post(wpautop($desc)); ?></div>
							<?php endif; ?>
                            <div class="ttbm-pm-body" style="display:none;">
                                <form class="ttbm-pm-form" data-gateway-id="<?php echo esc_attr($gateway->id); ?>">
                                    <table class="form-table ttbm-pm-form-table">
										<?php echo $gateway->generate_settings_html($gateway->get_form_fields(), false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                    </table>
                                    <div class="ttbm-pm-form-footer">
                                        <button type="submit" class="button button-primary ttbm-pm-save-btn"><?php esc_html_e('Save changes', 'tour-booking-manager'); ?></button>
                                        <span class="ttbm-pm-status"></span>
                                    </div>
                                </form>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
				<?php
			}
			private function verify_admin_ajax() {
				check_ajax_referer('ttbm_admin_nonce', 'nonce');
				if (!current_user_can('manage_options')) {
					wp_send_json_error(esc_html__('Permission denied.', 'tour-booking-manager'));
				}
			}
			private function verify_admin_ajax_wc() {
				$this->verify_admin_ajax();
				if (!TTBM_Global_Function::has_woocommerce()) {
					wp_send_json_error(esc_html__('WooCommerce is not active.', 'tour-booking-manager'));
				}
			}
			public function ajax_save_gateway() {
				$this->verify_admin_ajax_wc();
				$gateway_id = isset($_POST['gateway_id']) ? sanitize_key(wp_unslash($_POST['gateway_id'])) : '';
				$gateways = $this->get_gateways();
				if (!isset($gateways[$gateway_id])) {
					wp_send_json_error(esc_html__('Gateway not found.', 'tour-booking-manager'));
				}
				$gateway = $gateways[$gateway_id];
				$gateway->process_admin_options();
				$errors = $gateway->get_errors();
				if (!empty($errors)) {
					wp_send_json_error(implode(' ', array_map('wp_strip_all_tags', $errors)));
				}
				do_action('woocommerce_update_options_payment_gateways_' . $gateway->id);
				if (WC()->payment_gateways()) {
					WC()->payment_gateways()->init();
				}
				$refreshed = $this->get_gateways();
				$enabled = isset($refreshed[$gateway_id]) && $refreshed[$gateway_id]->enabled === 'yes';
				wp_send_json_success(array(
					'message' => esc_html__('Settings saved successfully!', 'tour-booking-manager'),
					'enabled' => $enabled ? 'yes' : 'no',
				));
			}
			public function ajax_toggle_gateway() {
				$this->verify_admin_ajax_wc();
				$gateway_id = isset($_POST['gateway_id']) ? sanitize_key(wp_unslash($_POST['gateway_id'])) : '';
				$enabled = (isset($_POST['enabled']) && sanitize_text_field(wp_unslash($_POST['enabled'])) === 'yes') ? 'yes' : 'no';
				if (empty($gateway_id)) {
					wp_send_json_error(esc_html__('Invalid gateway.', 'tour-booking-manager'));
				}
				$option_key = 'woocommerce_' . $gateway_id . '_settings';
				$opts = get_option($option_key, array());
				if (!is_array($opts)) {
					$opts = array();
				}
				$opts['enabled'] = $enabled;
				update_option($option_key, $opts);
				if (WC()->payment_gateways()) {
					WC()->payment_gateways()->init();
				}
				// WooCommerce can refuse to enable a gateway (e.g. currency not
				// supported) even after the option is saved, so report back what
				// actually took effect instead of the requested state.
				$gateways = $this->get_gateways();
				$real_enabled = isset($gateways[$gateway_id]) && $gateways[$gateway_id]->enabled === 'yes';
				$response = array('enabled' => $real_enabled ? 'yes' : 'no');
				if ('yes' === $enabled && !$real_enabled) {
					$opts['enabled'] = 'no';
					update_option($option_key, $opts);
					$name = isset($gateways[$gateway_id]) ? ($gateways[$gateway_id]->get_method_title() ?: $gateways[$gateway_id]->get_title()) : $gateway_id;
					$response['notice'] = sprintf(
						/* translators: %s: gateway name */
						esc_html__('%s could not be enabled for your store right now.', 'tour-booking-manager'),
						$name
					);
				}
				wp_send_json_success($response);
			}
			public function ajax_save_custom_gateway() {
				$this->verify_admin_ajax();
				$gateway_id = isset($_POST['gateway']) ? sanitize_key(wp_unslash($_POST['gateway'])) : '';
				$specs = $this->gateway_specs();
				if (!isset($specs[$gateway_id])) {
					wp_send_json_error(esc_html__('Invalid gateway.', 'tour-booking-manager'));
				}
				$spec = $specs[$gateway_id];
				if (!empty($spec['pro']) && !$this->is_pro_active()) {
					wp_send_json_error(esc_html__('This gateway requires the Tour Pro addon.', 'tour-booking-manager'));
				}
				$posted = isset($_POST['fields']) && is_array($_POST['fields']) ? wp_unslash($_POST['fields']) : array();
				$existing = (array) get_option('ttbm_payment_settings', array());

				$toggle_keys = array($spec['enable_key']);
				$text_keys = array();
				foreach ($spec['fields'] as $field) {
					if ('toggle' === $field['type']) {
						$toggle_keys[] = $field['key'];
					} else {
						$text_keys[] = $field['key'];
					}
				}
				foreach ($toggle_keys as $key) {
					$existing[$key] = (isset($posted[$key]) && $posted[$key] === 'on') ? 'on' : 'off';
				}
				foreach ($text_keys as $key) {
					$existing[$key] = isset($posted[$key]) ? sanitize_text_field($posted[$key]) : '';
				}
				update_option('ttbm_payment_settings', $existing);
				wp_send_json_success(array(
					'message' => esc_html__('Settings saved successfully!', 'tour-booking-manager'),
					'enabled' => $existing[$spec['enable_key']],
				));
			}
			/**
			 * Saves the "Confirm Ticket Based on Payment Status" checkboxes from
			 * the Payments tab straight into ttbm_basic_gen_settings — the SAME
			 * option the General tab's "Seat Booked Status" field already reads
			 * (see TTBM_Settings_Global::get_settings_fields() and 3 read sites in
			 * inc/TTBM_Query.php). Deliberately not a separate field/option: two
			 * copies of this value could silently disagree and corrupt seat-
			 * availability math, so both tabs edit one shared source of truth.
			 */
			public function ajax_save_book_status() {
				$this->verify_admin_ajax();
				$statuses = isset($_POST['statuses']) && is_array($_POST['statuses']) ? array_map('sanitize_key', wp_unslash($_POST['statuses'])) : array();
				$allowed = array('pending', 'processing', 'on-hold', 'completed');
				$statuses = array_intersect($statuses, $allowed);
				$value = array();
				foreach ($statuses as $status) {
					$value[$status] = $status;
				}
				$existing = (array) get_option('ttbm_basic_gen_settings', array());
				$existing['ttbm_set_book_status'] = $value;
				update_option('ttbm_basic_gen_settings', $existing);
				wp_send_json_success(array('message' => esc_html__('Saved.', 'tour-booking-manager')));
			}
			// Scoped save for the plain (non-gateway, non-auto-saving) fields on
			// this page's two sub-tabs — each "Save Changes" button only submits
			// the couple of field keys it's actually next to (via data-fields),
			// never the whole page/both sub-tabs, matching every other control
			// here (gateway cards, booking mode, book-status) already being a
			// small scoped AJAX save instead of one giant form POST.
			public function ajax_save_misc_fields() {
				$this->verify_admin_ajax();
				$allowed = array(
					'ttbm_payment_confirmation_page',
					'ttbm_payment_allow_guest_booking',
					'ttbm_payment_cart_redirect',
					'ttbm_payment_show_billing_info',
				);
				$fields = isset($_POST['fields']) && is_array($_POST['fields']) ? wp_unslash($_POST['fields']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized per key below
				$opts = (array) get_option('ttbm_payment_settings', array());
				foreach ($fields as $key => $value) {
					$key = sanitize_key($key);
					if (!in_array($key, $allowed, true)) {
						continue;
					}
					if ('ttbm_payment_confirmation_page' === $key) {
						$opts[$key] = absint($value);
					} elseif ('ttbm_payment_allow_guest_booking' === $key) {
						$opts[$key] = 'no' === $value ? 'no' : 'yes';
					} elseif ('ttbm_payment_cart_redirect' === $key) {
						$opts[$key] = 'cart' === $value ? 'cart' : 'checkout';
					} elseif ('ttbm_payment_show_billing_info' === $key) {
						$opts[$key] = 'on' === $value ? 'on' : 'off';
					}
				}
				update_option('ttbm_payment_settings', $opts);
				wp_send_json_success(array('message' => esc_html__('Settings saved.', 'tour-booking-manager')));
			}
			public function add_to_cart_redirect($url) {
				if (!isset($_POST['ttbm_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_form_nonce'])), 'ttbm_form_nonce')) {
					return $url;
				}
				$mode = $this->opt('ttbm_payment_cart_redirect', 'checkout');
				if ('cart' === $mode && function_exists('wc_get_cart_url')) {
					return wc_get_cart_url();
				}
				if (function_exists('wc_get_checkout_url')) {
					return wc_get_checkout_url();
				}
				return $url;
			}
			/**
			 * "Show Billing Info" — when turned off, drop WooCommerce's billing
			 * field group from checkout for carts that contain a tour booking.
			 * Scoped to TTBM carts only so it never affects unrelated WooCommerce
			 * products checked out on the same store.
			 */
			public function maybe_hide_billing_fields($fields) {
				if ($this->opt('ttbm_payment_show_billing_info', 'on') === 'on') {
					return $fields;
				}
				if (!function_exists('WC') || !WC()->cart || !$this->cart_has_ttbm_item()) {
					return $fields;
				}
				unset($fields['billing']);
				return $fields;
			}
			private function cart_has_ttbm_item() {
				foreach (WC()->cart->get_cart() as $cart_item) {
					if (!empty($cart_item['ttbm_id'])) {
						return true;
					}
				}
				return false;
			}
			// WooCommerce handles its own account/guest checkout, so the setting
			// only applies in Custom Payment mode. Default "yes" (guest booking
			// allowed) — login is only required once an admin explicitly opts
			// into "No" on the Custom Payment tab.
			public static function login_required_for_booking() {
				if (self::get_booking_mode() === 'woocommerce') {
					return false;
				}
				return TTBM_Global_Function::get_settings('ttbm_payment_settings', 'ttbm_payment_allow_guest_booking', 'yes') === 'no';
			}
			private static function login_gate_templates() {
				return array('book_now', 'book_now_smart', 'hotel_book_now');
			}
			// Shared login/register fields for both render_login_prompt() (legacy
			// whole-form replacement, kept for any direct callers/integrations)
			// and render_login_modal() (current flow — see its docblock).
			private static function render_login_panel_fields($allow_register) {
				?>
                <div class="ttbm-login-gate-panel" data-mode="login">
                    <p class="ttbm-login-gate-title"><?php esc_html_e('Log in to book this tour', 'tour-booking-manager'); ?></p>
                    <div class="ttbm-login-gate-fields-login">
                        <input type="text" class="ttbm-login-gate-field ttbm-login-gate-user" placeholder="<?php esc_attr_e('Username or email', 'tour-booking-manager'); ?>" autocomplete="username">
                        <input type="password" class="ttbm-login-gate-field ttbm-login-gate-pass" placeholder="<?php esc_attr_e('Password', 'tour-booking-manager'); ?>" autocomplete="current-password">
                    </div>
					<?php if ($allow_register) : ?>
                        <div class="ttbm-login-gate-fields-register" style="display:none;">
                            <input type="text" class="ttbm-login-gate-field ttbm-login-gate-name" placeholder="<?php esc_attr_e('Full name', 'tour-booking-manager'); ?>" autocomplete="name">
                            <input type="email" class="ttbm-login-gate-field ttbm-login-gate-email" placeholder="<?php esc_attr_e('Email address', 'tour-booking-manager'); ?>" autocomplete="email">
                            <input type="tel" class="ttbm-login-gate-field ttbm-login-gate-phone" placeholder="<?php esc_attr_e('Phone number', 'tour-booking-manager'); ?>" autocomplete="tel">
                            <input type="password" class="ttbm-login-gate-field ttbm-login-gate-reg-pass" placeholder="<?php esc_attr_e('Password', 'tour-booking-manager'); ?>" autocomplete="new-password">
                        </div>
					<?php endif; ?>
                    <button type="button" class="dButton ttbm-confirm-btn ttbm-login-gate-submit"><?php esc_html_e('Log In to Book', 'tour-booking-manager'); ?></button>
                    <p class="ttbm-login-gate-msg" aria-live="polite"></p>
					<?php if ($allow_register) : ?>
                        <p class="ttbm-login-gate-switch">
                            <a href="#" class="ttbm-login-gate-toggle" data-to="register"><?php esc_html_e("Don't have an account? Register", 'tour-booking-manager'); ?></a>
                            <a href="#" class="ttbm-login-gate-toggle" data-to="login" style="display:none;"><?php esc_html_e('Already have an account? Log in', 'tour-booking-manager'); ?></a>
                        </p>
					<?php endif; ?>
                </div>
				<?php
			}
			// Legacy: replaces the whole booking-form area with the login panel
			// at page-load time. No longer called by the three book_now partials
			// (see render_login_modal() below for the current click-time flow) —
			// kept as a public API surface for any direct integration still
			// calling it, and as the ajax_render_book_now() "what to put back"
			// fallback is no longer needed either, but removing a public static
			// method is a bigger compatibility risk than an unused-but-correct one.
			public static function render_login_prompt($tour_id = 0, $template = 'book_now') {
				if (!in_array($template, self::login_gate_templates(), true)) {
					$template = 'book_now';
				}
				?>
                <div class="ttbm_book_now_area ttbm_login_required_notice ttbm-login-gate" data-tour-id="<?php echo esc_attr($tour_id); ?>" data-template="<?php echo esc_attr($template); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_login_gate')); ?>">
					<?php self::render_login_panel_fields(true); ?>
                </div>
				<?php
			}
			// Current flow: the ticket/date booking form is ALWAYS visible to
			// everyone, logged in or not (browsing and selecting tickets never
			// requires an account). Only when a logged-out visitor actually
			// clicks the submit button does assets/frontend/ttbm-login-gate.js
			// intercept it and open this modal instead of submitting — login or
			// register here, then the original click is replayed automatically.
			// Rendered once per booking area, hidden until JS opens it.
			public static function render_login_modal($tour_id = 0, $template = 'book_now') {
				if (!in_array($template, self::login_gate_templates(), true)) {
					$template = 'book_now';
				}
				?>
                <div class="ttbm-login-gate ttbm-login-required-modal" style="display:none;" data-tour-id="<?php echo esc_attr($tour_id); ?>" data-template="<?php echo esc_attr($template); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_login_gate')); ?>">
                    <div class="ttbm-login-required-modal-overlay" data-ttbm-login-modal-close></div>
                    <div class="ttbm-login-required-modal-dialog" role="dialog" aria-modal="true">
                        <button type="button" class="ttbm-login-required-modal-close" data-ttbm-login-modal-close aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">&times;</button>
						<?php
						// Always offer registration here, independent of WordPress's
						// site-wide "Anyone can register" setting (Settings → General):
						// that toggle is for the wp-login.php admin-area registration
						// form and most sites deliberately leave it off, but a customer
						// with "Allow Guest Booking" set to No still needs SOME way to
						// get an account to complete a booking — this creates the same
						// low-privilege (subscriber-role) account wp_create_user() would
						// via that other form, just offered in the right place.
						self::render_login_panel_fields(true);
						?>
                    </div>
                </div>
				<?php
			}
			//------------------------------------------------------------------
			// Inline login/register (never a page reload — see render_login_prompt)
			//------------------------------------------------------------------
			public function ajax_portal_login() {
				check_ajax_referer('ttbm_login_gate', 'nonce');
				$login = isset($_POST['user_login']) ? sanitize_text_field(wp_unslash($_POST['user_login'])) : '';
				$password = isset($_POST['user_password']) ? (string) wp_unslash($_POST['user_password']) : '';
				if (!$login || !$password) {
					wp_send_json_error(esc_html__('Please enter your username/email and password.', 'tour-booking-manager'));
				}
				$user = wp_signon(array('user_login' => $login, 'user_password' => $password, 'remember' => true), is_ssl());
				if (is_wp_error($user)) {
					wp_send_json_error(wp_strip_all_tags($user->get_error_message()));
				}
				wp_set_current_user($user->ID);
				wp_send_json_success();
			}
			// Not gated on WordPress's site-wide users_can_register — see the
			// matching comment in render_login_modal() for why.
			public function ajax_portal_register() {
				check_ajax_referer('ttbm_login_gate', 'nonce');
				$email = isset($_POST['user_email']) ? sanitize_email(wp_unslash($_POST['user_email'])) : '';
				$full_name = isset($_POST['user_name']) ? sanitize_text_field(wp_unslash($_POST['user_name'])) : '';
				$phone = isset($_POST['user_phone']) ? sanitize_text_field(wp_unslash($_POST['user_phone'])) : '';
				$password = isset($_POST['user_password']) ? (string) wp_unslash($_POST['user_password']) : '';
				if (!is_email($email)) {
					wp_send_json_error(esc_html__('Please enter a valid email address.', 'tour-booking-manager'));
				}
				if (email_exists($email)) {
					wp_send_json_error(esc_html__('An account with this email already exists — please log in instead.', 'tour-booking-manager'));
				}
				if (strlen($password) < 8) {
					wp_send_json_error(esc_html__('Password must be at least 8 characters.', 'tour-booking-manager'));
				}
				$username = sanitize_user(current(explode('@', $email)), true);
				$base_username = $username ?: 'guest';
				$suffix = 0;
				while (username_exists($username) || !validate_username($username)) {
					$suffix++;
					$username = $base_username . $suffix;
				}
				$name_parts = preg_split('/\s+/', trim($full_name), 2);
				$first_name = $name_parts[0] ?? '';
				$last_name = $name_parts[1] ?? '';
				$user_id = wp_insert_user(array(
					'user_login' => $username,
					'user_pass' => $password,
					'user_email' => $email,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'display_name' => $full_name ?: $username,
				));
				if (is_wp_error($user_id)) {
					wp_send_json_error(wp_strip_all_tags($user_id->get_error_message()));
				}
				if ($phone) {
					update_user_meta($user_id, 'billing_phone', $phone);
				}
				wp_new_user_notification($user_id, null, 'both');
				wp_set_current_user($user_id);
				wp_set_auth_cookie($user_id, true);
				wp_send_json_success();
			}
			public function enqueue_login_gate_assets() {
				if (!self::login_required_for_booking()) {
					return;
				}
				wp_enqueue_script('ttbm-login-gate', TTBM_PLUGIN_URL . '/assets/frontend/ttbm-login-gate.js', array('jquery'), filemtime(TTBM_PLUGIN_DIR . '/assets/frontend/ttbm-login-gate.js'), true);
				wp_enqueue_style('ttbm-login-gate', TTBM_PLUGIN_URL . '/assets/frontend/ttbm-login-gate.css', array(), filemtime(TTBM_PLUGIN_DIR . '/assets/frontend/ttbm-login-gate.css'));
				wp_localize_script('ttbm-login-gate', 'ttbmLoginGate', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'error_label' => esc_html__('Something went wrong. Please try again.', 'tour-booking-manager'),
					'login_label' => esc_html__('Log In to Book', 'tour-booking-manager'),
					'register_label' => esc_html__('Register to Book', 'tour-booking-manager'),
				));
			}
			// Re-renders the booking-panel button area now that the visitor is
			// logged in, so the JS above can swap it in for the login panel
			// without ever reloading the page. Deliberately has NO nonce and NO
			// wp_ajax_nopriv_ counterpart:
			//
			// wp_set_auth_cookie()/wp_signon() only queue a Set-Cookie response
			// header — they never retroactively populate $_COOKIE within that
			// same request. A nonce minted anywhere in this same request/
			// response cycle (even right after wp_set_current_user()) still
			// hashes against the STALE pre-login auth token, so it fails
			// verification on this very next call. is_user_logged_in() (with no
			// nopriv handler registered, so WordPress itself rejects guests
			// before this method runs) plus a resource-id check is the correct
			// guard for a read-only endpoint that only ever discloses the
			// current user's own already-visible booking panel.
			public function ajax_render_book_now() {
				if (!is_user_logged_in()) {
					wp_send_json_error('', 403);
				}
				$tour_id = isset($_POST['tour_id']) ? absint($_POST['tour_id']) : 0;
				$template = isset($_POST['template']) ? sanitize_key(wp_unslash($_POST['template'])) : 'book_now';
				if (!in_array($template, self::login_gate_templates(), true)) {
					$template = 'book_now';
				}
				if (!$tour_id || get_post_type($tour_id) !== TTBM_Function::get_cpt_name() || get_post_status($tour_id) !== 'publish') {
					wp_send_json_error('', 404);
				}
				$file = TTBM_Function::template_path('ticket/' . $template . '.php');
				if (!file_exists($file)) {
					wp_send_json_error('', 404);
				}
				ob_start();
				$ttbm_post_id = $tour_id;
				include $file;
				$html = ob_get_clean();
				wp_send_json_success(array('html' => $html));
			}
		}
		new TTBM_Payment_Settings();
	}
