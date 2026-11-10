<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Payment_Settings')) {
		class TTBM_Payment_Settings {
			/**
			 * Gateway spec for the Custom Payment cards (PayPal / Stripe / Offline).
			 * Offline Payment and its standalone checkout are included in Free.
			 * PayPal and Stripe are registered by the Tour Pro addon.
			 */
			private function gateway_specs() {
				return array(
					'paypal' => array(
						'label' => esc_html__('PayPal', 'tour-booking-manager'),
						'desc' => esc_html__('Take card and PayPal payments online.', 'tour-booking-manager'),
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
						'desc' => esc_html__('Take card payments online.', 'tour-booking-manager'),
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
						'desc' => esc_html__('Bank transfer, cash or pay at the venue. You confirm each booking once paid.', 'tour-booking-manager'),
						'enable_key' => 'ttbm_offline_enable',
						'pro' => false,
						'fields' => array(
							array('key' => 'ttbm_offline_label', 'type' => 'text', 'label' => esc_html__('Payment Label', 'tour-booking-manager'), 'placeholder' => esc_html__('e.g. Pay at Venue / Bank Transfer', 'tour-booking-manager'), 'desc' => esc_html__('Shown to customers on the frontend payment page.', 'tour-booking-manager')),
						),
					),
				);
			}
			/**
			 * Compact brand mark for Custom Payment gateway cards.
			 *
			 * @param string $gateway_id paypal|stripe|offline
			 * @return string Safe inline SVG.
			 */
			private static function gateway_card_icon($gateway_id) {
				$icons = array(
					'paypal' => '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M7.5 20.5H5.2c-.4 0-.7-.3-.6-.7L7.2 3.7c.1-.4.4-.7.8-.7h5.6c3.1 0 5.2 1.6 4.8 4.7-.4 3.4-2.7 5.1-5.9 5.1H9.8l-1 5c-.1.4-.4.7-.8.7H7.5z"/><path opacity=".55" d="M9.2 12.8h2.5c2.5 0 4.3-1.2 4.6-3.7.2-1.7-.8-2.6-2.6-2.6H10.4L9.2 12.8z"/></svg>',
					'stripe' => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 10h18"/><path d="M8 15h3"/></svg>',
					'offline' => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="6" width="17" height="12" rx="2"/><path d="M3.5 10h17"/><circle cx="8" cy="14" r="1.2"/><path d="M12 14h5"/></svg>',
				);
				return isset($icons[$gateway_id]) ? $icons[$gateway_id] : $icons['offline'];
			}
			private function is_pro_active() {
				return class_exists('TTBM_Woocommerce_Plugin_Pro');
			}
			private function opt($key, $default = '') {
				return TTBM_Global_Function::get_settings('ttbm_payment_settings', $key, $default);
			}
			// The payment manager echoes stored gateway credentials (WooCommerce
			// gateway forms, TTBM PayPal/Stripe secrets), so every render path
			// needs the same capability as ttbm_settings_page and its AJAX handlers.
			private function can_manage_payments(): bool {
				return current_user_can('manage_options');
			}
			public function __construct() {
				add_filter('ttbm_settings_sec_reg', array($this, 'register_section'), 95, 1);
				add_action('wsa_form_bottom_ttbm_payment_settings', array($this, 'render_tab_content'));
				add_action('wp_ajax_ttbm_save_gateway_settings', array($this, 'ajax_save_custom_gateway'));
				add_action('wp_ajax_ttbm_wc_save_gateway', array($this, 'ajax_save_gateway'));
				add_action('wp_ajax_ttbm_wc_toggle_gateway', array($this, 'ajax_toggle_gateway'));
				add_action('wp_ajax_ttbm_refresh_payment_settings', array($this, 'ajax_refresh_payment_settings'));
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
				// Runs early on every request (front end too), before any gateway reads the settings.
				add_action('init', array($this, 'maybe_default_offline_payment'), 5);
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
			// Custom Payment exists in Free through Offline Payment. Pro uses the
			// same filter while its PayPal/Stripe runtime is loaded. This reports
			// engine availability only; gateway enablement is a separate warning.
			public static function custom_payment_available() {
				return (bool) apply_filters('ttbm_custom_payment_available', true);
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
			/**
			 * Turn Offline Payment on by default while bookings go through Custom Payment
			 * (no WooCommerce checkout), so guests can book out of the box. Only when the
			 * setting has never been saved: an admin who turned it off keeps it off.
			 */
			public function maybe_default_offline_payment() {
				$opts = get_option('ttbm_payment_settings', array());
				if (!is_array($opts) || isset($opts['ttbm_offline_enable'])) {
					return;
				}
				if ('custom' !== self::get_booking_mode() || !self::custom_payment_available()) {
					return;
				}
				$opts['ttbm_offline_enable'] = 'on';
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
					'ttbm_offline_enable' => !empty($opts['ttbm_offline_label']) ? $opts['ttbm_offline_label'] : __('Offline Payment', 'tour-booking-manager'),
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
				$can_manage = $this->can_manage_payments();
				?>
				<div class="ttbm-sb-card ttbm-sb-payment-card <?php echo $pm_active ? 'is-ready' : 'is-missing'; ?>">
					<p class="ttbm-sb-card-title"><?php esc_html_e('Payment Method', 'tour-booking-manager'); ?></p>
					<p class="ttbm-sb-payment-status" data-ready-text="<?php esc_attr_e('Ready to accept bookings', 'tour-booking-manager'); ?>" data-missing-text="<?php esc_attr_e('Setup required: no payment method', 'tour-booking-manager'); ?>">
						<span class="ttbm-sb-payment-status__icon" aria-hidden="true"></span>
						<span class="ttbm-sb-payment-status__text"><?php echo $pm_active ? esc_html__('Ready to accept bookings', 'tour-booking-manager') : esc_html__('Setup required: no payment method', 'tour-booking-manager'); ?></span>
					</p>
					<div class="ttbm-sb-payment-info-list">
						<div class="ttbm-sb-payment-info-row">
							<span><?php esc_html_e('Active Method', 'tour-booking-manager'); ?></span>
							<strong><?php echo esc_html($pm_type_label); ?></strong>
						</div>
						<div class="ttbm-sb-payment-info-row">
							<span><?php esc_html_e('Active Gateway', 'tour-booking-manager'); ?></span>
							<strong><?php echo esc_html($pm_gateway_names ? implode(', ', $pm_gateway_names) : __('None', 'tour-booking-manager')); ?></strong>
						</div>
						<?php if ($can_manage && $pm_gateway_names) : ?>
							<p class="ttbm-sb-payment-link">
								<a href="#" data-ttbm-payment-modal-open><?php esc_html_e('Payment Settings', 'tour-booking-manager'); ?></a>
							</p>
						<?php endif; ?>
						<?php if ($can_manage && !$pm_active) : ?>
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
				$mode = self::get_booking_mode();
				$settings_url = admin_url('edit.php?post_type=ttbm_tour&page=ttbm_settings_page#ttbm_payment_settings');
				$action_label = 'custom' === $mode
					? esc_html__('Enable Offline Payment', 'tour-booking-manager')
					: esc_html__('Configure WooCommerce Payments', 'tour-booking-manager');
				?>
				<div class="notice ttbm-pay-gateway-notice ttbm-pay-gateway-notice-modern">
					<div class="ttbm-pay-gateway-notice-inner">
						<span class="ttbm-pay-gateway-notice-icon" aria-hidden="true">
							<span class="dashicons dashicons-money-alt"></span>
						</span>
						<div class="ttbm-pay-gateway-notice-copy">
							<span class="ttbm-pay-gateway-notice-kicker"><?php esc_html_e('Payment setup required', 'tour-booking-manager'); ?></span>
							<h3><?php esc_html_e('Bookings cannot accept payments yet', 'tour-booking-manager'); ?></h3>
							<p><?php echo esc_html($warning); ?></p>
						</div>
						<a class="ttbm-pay-gateway-notice-action" href="<?php echo esc_url($settings_url); ?>">
							<span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
							<?php echo esc_html($action_label); ?>
						</a>
					</div>
				</div>
				<?php
			}

			/**
			 * Yellow banner (when needed) + payment modal on tour/hotel edit screens.
			 * Banner markup is moved to the top of #post-body via JS; for users who
			 * can manage payments the modal is always available so the sidebar
			 * Payment card can open it too. Other editors (e.g. Contributors) only
			 * see the informational banner.
			 */
			public function maybe_render_edit_payment_notice($post) {
				if (!$this->is_tour_or_hotel_edit_screen()) {
					return;
				}
				$can_manage = $this->can_manage_payments();
				$needs_notice = !self::has_functional_payment_method();
				if ($needs_notice) {
					?>
					<div class="ttbm-edit-payment-notice" id="ttbm-edit-payment-notice">
						<div class="ttbm-edit-payment-notice-main">
							<span class="ttbm-edit-payment-notice-icon" aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path></svg>
							</span>
							<div class="ttbm-edit-payment-notice-text">
								<strong><?php esc_html_e('No payment method is currently configured.', 'tour-booking-manager'); ?></strong>
								<span><?php esc_html_e('Please configure a payment method to accept bookings.', 'tour-booking-manager'); ?></span>
							</div>
						</div>
						<?php if ($can_manage) : ?>
							<a href="#" class="ttbm-edit-payment-notice-link" data-ttbm-payment-modal-open>
								<?php esc_html_e('Configure payment', 'tour-booking-manager'); ?>
							</a>
						<?php endif; ?>
					</div>
					<?php
				}
				if (!$can_manage) {
					return;
				}
				?>
				<div class="ttbm-edit-payment-modal" id="ttbm-edit-payment-modal" data-ttbm-payment-modal style="display:none;">
					<div class="ttbm-edit-payment-modal-box" role="dialog" aria-modal="true" aria-labelledby="ttbm-edit-payment-modal-title">
						<div class="ttbm-edit-payment-modal-head">
							<div class="ttbm-edit-payment-modal-titles">
								<h2 id="ttbm-edit-payment-modal-title"><?php esc_html_e('Payments', 'tour-booking-manager'); ?></h2>
								<p><?php esc_html_e('Choose how guests pay for their bookings.', 'tour-booking-manager'); ?></p>
							</div>
							<button type="button" class="ttbm-edit-payment-modal-close" data-ttbm-payment-modal-close aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">
								<svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
							</button>
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
				wp_localize_script('ttbm-payment-settings', 'ttbmPaymentSettings', self::script_strings());
				wp_add_inline_style('ttbm-global-settings', $this->edit_payment_notice_css());
				wp_add_inline_script('ttbm-payment-settings', $this->edit_payment_notice_js());
			}

			/**
			 * Text used by ttbm-payment-settings.js, shared by the Settings › Payments tab
			 * and the Payment Method popup on the tour and hotel editors.
			 */
			public static function script_strings(): array {
				return array(
					'enabled_label' => esc_html__('Enabled', 'tour-booking-manager'),
					'disabled_label' => esc_html__('Disabled', 'tour-booking-manager'),
					'on_label' => esc_html__('On', 'tour-booking-manager'),
					'off_label' => esc_html__('Off', 'tour-booking-manager'),
					'error_label' => esc_html__('An error occurred. Please try again.', 'tour-booking-manager'),
					'saving_label' => esc_html__('Saving…', 'tour-booking-manager'),
					'saved_label' => esc_html__('Settings saved.', 'tour-booking-manager'),
					'unsaved_label' => esc_html__('You have unsaved changes.', 'tour-booking-manager'),
					'in_use_label' => esc_html__('In use', 'tour-booking-manager'),
					/* translators: %s: booking mode name, e.g. "WooCommerce Checkout" */
					'mode_saved_label' => esc_html__('Checkout changed to %s.', 'tour-booking-manager'),
					'wc_mode_label' => esc_html__('WooCommerce Checkout', 'tour-booking-manager'),
					'custom_mode_label' => esc_html__('Custom Payment', 'tour-booking-manager'),
					'active_label' => esc_html__('Active', 'tour-booking-manager'),
					'payment_notice_kicker' => esc_html__('Payment setup required', 'tour-booking-manager'),
					'payment_notice_title' => esc_html__('Bookings cannot accept payments yet', 'tour-booking-manager'),
					'enable_offline_label' => esc_html__('Enable Offline Payment', 'tour-booking-manager'),
					'configure_wc_label' => esc_html__('Configure WooCommerce Payments', 'tour-booking-manager'),
					'none_label' => esc_html__('None', 'tour-booking-manager'),
					'rail_nothing' => esc_html__('Nothing turned on yet', 'tour-booking-manager'),
					'payment_settings_label' => esc_html__('Payment Settings', 'tour-booking-manager'),
					'configure_payment_label' => esc_html__('Configure payment method', 'tour-booking-manager'),
					'edit_notice_text' => esc_html__('No payment method is currently configured.', 'tour-booking-manager'),
					'edit_notice_action' => esc_html__('Please configure a payment method to accept bookings.', 'tour-booking-manager'),
					'edit_notice_button' => esc_html__('Configure payment', 'tour-booking-manager'),
					'payment_settings_url' => admin_url('edit.php?post_type=ttbm_tour&page=ttbm_settings_page#ttbm_payment_settings'),
				);
			}

			private function edit_payment_notice_css(): string {
				return <<<'CSS'
.ttbm-edit-payment-notice{display:flex;align-items:center;justify-content:space-between;gap:20px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 20px;margin:0 0 12px;color:#1e293b;}
.ttbm-edit-payment-notice-main{display:flex;align-items:center;gap:12px;min-width:0;}
.ttbm-edit-payment-notice-icon{flex-shrink:0;width:36px;height:36px;border-radius:10px;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;}
.ttbm-edit-payment-notice-text{display:flex;flex-direction:column;gap:2px;min-width:0;}
.ttbm-edit-payment-notice-text strong{font-size:14px;font-weight:600;line-height:1.4;}
.ttbm-edit-payment-notice-text span{font-size:13px;line-height:1.4;color:#475569;}
.ttbm-edit-payment-notice-link,.ttbm-edit-payment-notice-link:visited{flex-shrink:0;display:inline-flex;align-items:center;height:38px;padding:0 18px;border-radius:8px;background:var(--ttbm-ed-accent,var(--color_theme,#6b35e8));color:#fff;font-size:14px;font-weight:600;text-decoration:none;white-space:nowrap;}
.ttbm-edit-payment-notice-link:hover,.ttbm-edit-payment-notice-link:focus{background:var(--ttbm-ed-accent,var(--color_theme,#6b35e8));color:#fff;}
.ttbm-edit-payment-notice-link:focus-visible{outline:2px solid var(--ttbm-ed-accent,var(--color_theme,#6b35e8));outline-offset:2px;}
.ttbm-edit-payment-modal{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:100001;align-items:center;justify-content:center;padding:20px;}
.ttbm-edit-payment-modal-box{display:flex;flex-direction:column;background:#fff;border-radius:14px;max-width:960px;width:100%;max-height:calc(100vh - 40px);overflow:hidden;box-shadow:0 24px 64px rgba(15,23,42,.35);}
.ttbm-edit-payment-modal-head{flex:none;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 28px;border-bottom:1px solid #dde2e8;background:#fff;}
.ttbm-edit-payment-modal-titles{display:flex;flex-direction:column;gap:2px;}
.ttbm-edit-payment-modal-head h2{margin:0;padding:0;font-size:20px;line-height:1.3;font-weight:700;color:#1b2430;}
.ttbm-edit-payment-modal-head p{margin:0;font-size:14px;color:#4a5565;}
.ttbm-edit-payment-modal-close{flex:none;width:44px;height:44px;display:flex;align-items:center;justify-content:center;border:0;border-radius:8px;background:transparent;color:#4a5565;cursor:pointer;}
.ttbm-edit-payment-modal-close:hover{background:#f1f3f6;color:#1b2430;}
.ttbm-edit-payment-modal-close:focus-visible{outline:2px solid #0f766e;outline-offset:2px;}
.ttbm-edit-payment-modal-body{flex:1;min-height:0;overflow-y:auto;padding:0;}
body.ttbm-modern-edit-page #poststuff > .ttbm-edit-payment-notice,body.ttbm-modern-edit-page #post-body > .ttbm-edit-payment-notice{box-sizing:border-box;}
body.ttbm-modern-edit-page #post-body > .ttbm-edit-payment-notice{width:100%;max-width:1250px;margin:16px auto 0;clear:both;}
@media (max-width:1200px){body.ttbm-modern-edit-page #post-body > .ttbm-edit-payment-notice{max-width:none;margin:12px 16px 0;width:auto;}}
@media (max-width:782px){body.ttbm-modern-edit-page #poststuff > .ttbm-edit-payment-notice,body.ttbm-modern-edit-page #post-body > .ttbm-edit-payment-notice{flex-direction:column;align-items:stretch;padding:12px 14px;}.ttbm-edit-payment-notice-link{justify-content:center;}}
/* Payment Method modal: the WooCommerce panel keeps its own layout inside the new shell. */
#ttbm-edit-payment-modal .ttbm-pay-field-desc{font-size:12px;line-height:1.4;color:#475569;}
#ttbm-edit-payment-modal .ttbm-pay-misc-save-footer{position:sticky;bottom:0;margin:24px -28px 0;padding:16px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;z-index:1;}
#ttbm-edit-payment-modal .ttbm-pay-misc-save-btn.button.button-primary{height:40px !important;min-height:40px !important;padding:0 20px !important;border-radius:8px !important;background:var(--ttbm-ed-accent,var(--color_theme,#6b35e8)) !important;border-color:var(--ttbm-ed-accent,var(--color_theme,#6b35e8)) !important;font-size:14px !important;font-weight:600;}
#ttbm-edit-payment-modal .ttbm-pay-misc-save-btn.button.button-primary:hover{background:var(--ttbm-ed-accent,var(--color_theme,#6b35e8)) !important;border-color:var(--ttbm-ed-accent,var(--color_theme,#6b35e8)) !important;}
#ttbm-edit-payment-modal .ttbm-pm-section-title{display:block;margin:0 0 12px;font-size:14px;font-weight:700;color:#1e293b;}
#ttbm-edit-payment-modal .ttbm-pm-locked-btn.button{display:inline-flex;align-items:center;justify-content:center;width:100%;min-height:36px;border:1px solid #e2e8f0;border-radius:8px;background:#f1f5f9;color:#475569;font-size:13px;font-weight:600;box-shadow:none;opacity:1;cursor:not-allowed;}
#ttbm-edit-payment-modal .ttbm-pm-gateways{margin-top:0;}
#ttbm-edit-payment-modal .ttbm-booking-mode-head h3{margin:0 0 10px;font-size:14px;font-weight:700;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-head p{display:none;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-cards{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 18px;background:#eff6ff;border-radius:12px;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card{padding:0;border:0;background:transparent;border-radius:0;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-active{order:1;flex:1;padding:0;border:0;background:transparent;align-items:center;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-active .ttbm-booking-mode-card-desc{display:none;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-active .ttbm-booking-mode-card-head{justify-content:flex-start;gap:10px;margin:0;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-active .ttbm-booking-mode-card-title{font-size:15px;font-weight:700;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-disabled{order:2;flex:0 1 auto;cursor:default;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-disabled .ttbm-booking-mode-card-icon,#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-disabled .ttbm-booking-mode-card-head{display:none !important;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-disabled .ttbm-booking-mode-card-body{display:flex;align-items:center;gap:14px;}
#ttbm-edit-payment-modal .ttbm-booking-mode.is-locked .ttbm-booking-mode-card.is-disabled .ttbm-booking-mode-card-desc{opacity:1;max-width:260px;color:#1e3a8a;}
#ttbm-edit-payment-modal .ttbm-booking-mode-card-cta.button{margin:0 !important;min-height:34px;padding:0 14px !important;border:1px solid var(--ttbm-ed-accent,var(--color_theme,#6b35e8)) !important;border-radius:8px !important;background:#fff !important;color:var(--ttbm-ed-accent,var(--color_theme,#6b35e8)) !important;font-size:13px !important;font-weight:600;box-shadow:none !important;}
#ttbm-edit-payment-modal .ttbm-booking-mode-card-cta.button:hover{background:#eff6ff !important;}
@media (max-width:782px){#ttbm-edit-payment-modal .ttbm-pm-gateways{grid-template-columns:1fr;}#ttbm-edit-payment-modal .ttbm-pay-acc-body{grid-template-columns:1fr;}}
CSS;
			}

			private function edit_payment_notice_js(): string {
				return <<<'JS'
jQuery(function ($) {
	var $notice = $('#ttbm-edit-payment-notice');
	var $modal = $('#ttbm-edit-payment-modal');
	var $host = $('#post-body').length ? $('#post-body') : $('#poststuff');
	if ($notice.length && $host.length) {
		$host.prepend($notice);
	}
	if ($modal.length) {
		$modal.appendTo('body');
	}
	if (!$modal.length) { return; }
	var opener = null;
	function closeModal() {
		$modal.hide();
		if (opener && opener.focus) { opener.focus(); }
	}
	$(document).on('click', '[data-ttbm-payment-modal-open]', function (e) {
		e.preventDefault();
		opener = this;
		$modal.css('display', 'flex');
		$modal.find('.ttbm-pay-acc-body').show();
		$modal.find('.ttbm-pay-acc-header').addClass('is-open');
		$modal.find('.ttbm-edit-payment-modal-close').trigger('focus');
	});
	$(document).on('click', '[data-ttbm-payment-modal-close]', closeModal);
	$modal.on('click', function (e) {
		if (e.target === this) { closeModal(); }
	});
	$(document).on('keydown', function (e) {
		if ((e.key === 'Escape' || e.keyCode === 27) && $modal.is(':visible') && !$('.ttbm-gw-modal:visible, #ttbm-woo-install-modal:visible').length) {
			closeModal();
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
					wp_send_json_error(esc_html__('Booking mode can only be changed when both WooCommerce and Custom Payment are available.', 'tour-booking-manager'));
				}
				$opts = (array) get_option('ttbm_payment_settings', array());
				$opts['ttbm_booking_mode'] = $mode;
				update_option('ttbm_payment_settings', $opts);
				wp_send_json_success(array_merge(array(
					'mode' => $mode,
				), $this->payment_state_response()));
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
			 * Payments screen: a setup checklist on the left and, on the right, one status
			 * message, the checkout choice and the active checkout's settings. Shared by the
			 * Settings › Payments tab and the Payment Method popup on the tour editor.
			 */
			public function render_tab_content() {
				if (!$this->can_manage_payments()) {
					return;
				}
				$custom_is_default = self::get_booking_mode() === 'custom';
				?>
				<div class="ttbm-payment-settings-content ttbm-pset">
					<?php $this->render_setup_rail(); ?>
					<div class="ttbm-pay-main ttbm-pset-main">
						<?php $this->render_status_banner(); ?>
						<?php $this->render_booking_mode_selector(); ?>
						<div class="ttbm-pay-subtab-panel ttbm-pay-mode-panel" data-subtab-panel="woocommerce" <?php echo $custom_is_default ? 'style="display:none;"' : ''; ?>>
							<?php $this->render_woocommerce_subtab(); ?>
						</div>
						<div class="ttbm-pay-subtab-panel ttbm-pay-mode-panel ttbm-pset-panel" data-subtab-panel="custom" <?php echo $custom_is_default ? '' : 'style="display:none;"'; ?>>
							<?php $this->render_custom_payment_subtab(); ?>
						</div>
					</div>
				</div>
				<?php
			}
			/**
			 * Status message above the checkout choice: whether guests can book yet,
			 * and what to do next. Also sent back after every save so the page can update.
			 *
			 * @return array{0: bool, 1: string, 2: string} ready, title, text
			 */
			public static function status_copy(): array {
				$mode = self::get_booking_mode();
				if (self::has_functional_payment_method()) {
					$opts = (array) get_option('ttbm_payment_settings', array());
					$is_on = function ($key) use ($opts) {
						return isset($opts[$key]) && 'on' === $opts[$key];
					};
					if ('custom' === $mode && $is_on('ttbm_offline_enable') && !$is_on('ttbm_paypal_enable') && !$is_on('ttbm_stripe_enable')) {
						return array(true, __('Guests can book now.', 'tour-booking-manager'), __('New bookings come in as pending. Confirm each one once you\'ve been paid.', 'tour-booking-manager'));
					}
					$names = self::get_active_gateway_names();
					/* translators: %s: comma-separated payment method names */
					return array(true, __('Guests can book now.', 'tour-booking-manager'), $names ? sprintf(__('Guests pay with %s.', 'tour-booking-manager'), implode(', ', $names)) : '');
				}
				if ('woocommerce' === $mode) {
					$text = __('Turn on a WooCommerce payment method below to start taking bookings.', 'tour-booking-manager');
				} elseif (TTBM_Global_Function::has_woocommerce()) {
					$text = __('Turn on a payment method below to start taking bookings.', 'tour-booking-manager');
				} else {
					$text = __('Turn on Offline Payment below, or activate WooCommerce, to start taking bookings.', 'tour-booking-manager');
				}
				return array(false, __('Guests can\'t book yet.', 'tour-booking-manager'), $text);
			}
			private function render_status_banner() {
				list($ready, $title, $text) = self::status_copy();
				?>
				<div class="ttbm-pset-status <?php echo $ready ? 'is-ready' : 'is-warn'; ?>" data-pset-status role="status">
					<span class="ttbm-pset-status__icon" aria-hidden="true">
						<svg class="ttbm-pset-status__warn" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4L2.5 20h19L12 4z"/><path d="M12 10v4.5M12 17.5h.01"/></svg>
						<svg class="ttbm-pset-status__ok" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.8 2.8L16.5 9.5"/></svg>
					</span>
					<span class="ttbm-pset-status__copy">
						<b data-pset-status-title><?php echo esc_html($title); ?></b>
						<span data-pset-status-text><?php echo esc_html($text); ?></span>
					</span>
				</div>
				<?php
			}
			/**
			 * Step marker: a check when the step is done, its number otherwise (CSS picks).
			 */
			private static function rail_mark($number) {
				?>
				<span class="ttbm-pset-steps__mark" aria-hidden="true">
					<svg class="ttbm-pset-steps__check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
					<span class="ttbm-pset-steps__num"><?php echo esc_html($number); ?></span>
				</span>
				<?php
			}
			/**
			 * Left "setup checklist": checkout, payment method, booking rules.
			 * Rendered from the saved state; ttbm-payment-settings.js updates it after each save.
			 */
			private function render_setup_rail() {
				$names = self::get_active_gateway_names();
				$ready = self::has_functional_payment_method();
				$rules = 'custom' === self::get_booking_mode()
					? __('Confirmation page and guests', 'tour-booking-manager')
					: __('Cart redirect and confirmation', 'tour-booking-manager');
				?>
				<aside class="ttbm-pset-rail" aria-label="<?php esc_attr_e('Setup progress', 'tour-booking-manager'); ?>">
					<ol class="ttbm-pset-steps">
						<li class="is-done">
							<?php self::rail_mark(1); ?>
							<span class="ttbm-pset-steps__text"><b><?php esc_html_e('Checkout', 'tour-booking-manager'); ?></b><span data-pset-rail="mode"><?php echo esc_html(self::get_booking_mode_label()); ?></span></span>
						</li>
						<li class="<?php echo $ready ? 'is-done' : 'is-todo'; ?>" data-pset-rail="method-step"<?php echo $ready ? '' : ' aria-current="step"'; ?>>
							<?php self::rail_mark(2); ?>
							<span class="ttbm-pset-steps__text"><b><?php esc_html_e('Payment method', 'tour-booking-manager'); ?></b><span data-pset-rail="methods"><?php echo esc_html($ready && $names ? implode(', ', $names) : __('Nothing turned on yet', 'tour-booking-manager')); ?></span></span>
						</li>
						<li class="<?php echo $ready ? 'is-next' : ''; ?>" data-pset-rail="rules-step"<?php echo $ready ? ' aria-current="step"' : ''; ?>>
							<?php self::rail_mark(3); ?>
							<span class="ttbm-pset-steps__text"><b><?php esc_html_e('Booking rules', 'tour-booking-manager'); ?></b><span><?php echo esc_html($rules); ?></span></span>
						</li>
					</ol>
				</aside>
				<?php
			}
			/**
			 * Checkout choice as two tabs, so both checkouts read as options. With both
			 * available, picking a tab switches the checkout (saved instantly). Otherwise
			 * the tab of the checkout that can't be used yet still opens, to explain why
			 * and (for WooCommerce) offer to set it up; the checkout in use doesn't change.
			 */
			private function render_booking_mode_selector() {
				$availability = self::mode_availability();
				$mode = self::get_booking_mode();
				$can_choose = 'both' === $availability;
				$wc_active = TTBM_Global_Function::has_woocommerce();
				$available = array(
					'custom' => self::custom_payment_available(),
					'woocommerce' => $wc_active,
				);
				$icons = array(
					'custom' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M14.8 8.7c-.6-.6-1.5-.9-2.5-.9-1.5 0-2.7.8-2.7 2s1 1.8 2.7 2.2c1.7.4 2.7 1 2.7 2.2s-1.2 2-2.8 2c-1.1 0-2.2-.4-2.9-1.1M12.2 6.2v11.6"/></svg>',
					'woocommerce' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h2.2l2.1 9.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 7H6"/><circle cx="10" cy="19" r="1.4"/><circle cx="18" cy="19" r="1.4"/></svg>',
				);
				$modes = array(
					'custom' => array(
						'title' => __('Custom Payment', 'tour-booking-manager'),
						'sub' => __('Paid through this plugin', 'tour-booking-manager'),
					),
					'woocommerce' => array(
						'title' => __('WooCommerce Checkout', 'tour-booking-manager'),
						'sub' => __('WooCommerce cart and checkout', 'tour-booking-manager'),
					),
				);
				$info_icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>';
				?>
				<section class="ttbm-pset-section" aria-labelledby="ttbm-pset-checkout-title">
					<h3 class="ttbm-pset-heading" id="ttbm-pset-checkout-title"><?php esc_html_e('Checkout', 'tour-booking-manager'); ?></h3>
					<p class="ttbm-pset-lead">
						<?php echo $can_choose
							? esc_html__('Pick how guests pay. Only one checkout is used at a time, and switching changes it right away.', 'tour-booking-manager')
							: esc_html__('Pick how guests pay. Only one checkout is used at a time.', 'tour-booking-manager'); ?>
					</p>
					<div class="ttbm-pset-tabs" role="tablist" aria-labelledby="ttbm-pset-checkout-title"<?php echo $can_choose ? ' data-pset-mode-group data-nonce="' . esc_attr(wp_create_nonce('ttbm_save_booking_mode')) . '"' : ''; ?>>
						<?php foreach ($modes as $key => $info) :
							$selected = $mode === $key;
							if ($selected) {
								$badge = array('is-use', __('In use', 'tour-booking-manager'));
							} elseif (!$available[$key]) {
								$badge = array('is-off', __('Not active', 'tour-booking-manager'));
							} else {
								$badge = array('', '');
							}
							?>
							<button type="button" role="tab" id="ttbm-pset-tab-<?php echo esc_attr($key); ?>" aria-controls="ttbm-pset-tabpanel-<?php echo esc_attr($key); ?>" aria-selected="<?php echo $selected ? 'true' : 'false'; ?>" tabindex="<?php echo $selected ? '0' : '-1'; ?>" class="ttbm-pset-tab<?php echo $can_choose ? ' ttbm-booking-mode-card' : ''; ?><?php echo $selected ? ' is-active' : ''; ?>" data-mode="<?php echo esc_attr($key); ?>" data-subtab="<?php echo esc_attr($key); ?>">
								<span class="ttbm-pset-tab__icon" aria-hidden="true"><?php echo $icons[$key]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></span>
								<span class="ttbm-pset-tab__copy">
									<span class="ttbm-pset-tab__title">
										<b class="ttbm-booking-mode-card-title"><?php echo esc_html($info['title']); ?></b>
										<span class="ttbm-pset-badge <?php echo esc_attr($badge[0]); ?>" data-pset-badge><?php echo esc_html($badge[1]); ?></span>
									</span>
									<span class="ttbm-pset-tab__sub"><?php echo esc_html($info['sub']); ?></span>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
					<?php foreach ($modes as $key => $info) : ?>
						<div class="ttbm-pset-tabpanel" role="tabpanel" id="ttbm-pset-tabpanel-<?php echo esc_attr($key); ?>" aria-labelledby="ttbm-pset-tab-<?php echo esc_attr($key); ?>"<?php echo $mode === $key ? '' : ' hidden'; ?>>
							<?php if ('woocommerce' === $key && !$wc_active) : ?>
								<span class="ttbm-pset-tabpanel__text">
									<span class="ttbm-pset-tabpanel__icon is-warn" aria-hidden="true"><?php echo $info_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></span>
									<span><b><?php esc_html_e('WooCommerce isn\'t active.', 'tour-booking-manager'); ?></b> <?php esc_html_e('Activate it to take bookings through its cart and checkout.', 'tour-booking-manager'); ?></span>
								</span>
								<button type="button" class="ttbm-woo-install-trigger ttbm-pset-btn ttbm-pset-btn--primary">
									<?php echo file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php')
										? esc_html__('Activate WooCommerce', 'tour-booking-manager')
										: esc_html__('Install WooCommerce', 'tour-booking-manager'); ?>
								</button>
							<?php elseif ('custom' === $key && !$available['custom']) : ?>
								<span class="ttbm-pset-tabpanel__text">
									<span class="ttbm-pset-tabpanel__icon is-warn" aria-hidden="true"><?php echo $info_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></span>
									<span><?php esc_html_e('Custom Payment isn\'t available on this site.', 'tour-booking-manager'); ?></span>
								</span>
							<?php else : ?>
								<span class="ttbm-pset-tabpanel__text">
									<span class="ttbm-pset-tabpanel__icon" aria-hidden="true"><?php echo $info_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></span>
									<span><?php
										if ('woocommerce' === $key) {
											esc_html_e('Bookings go through the WooCommerce cart and checkout, with its payment methods.', 'tour-booking-manager');
										} elseif ($wc_active) {
											esc_html_e('Guests pay with the methods you turn on below, such as Offline Payment, without the WooCommerce cart.', 'tour-booking-manager');
										} else {
											esc_html_e('Guests pay with the methods you turn on below, such as Offline Payment. No WooCommerce needed.', 'tour-booking-manager');
										}
										?></span>
								</span>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</section>
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
                <div class="ttbm-pay-misc-save-row ttbm-pay-misc-save-footer">
                    <button type="button" class="button button-primary ttbm-pay-misc-save-btn" data-fields="ttbm_payment_cart_redirect,ttbm_payment_show_billing_info" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_admin_nonce')); ?>">
						<span class="dashicons dashicons-saved" aria-hidden="true"></span>
						<?php esc_html_e('Save Changes', 'tour-booking-manager'); ?>
					</button>
                </div>
				<?php
			}
			private function render_wc_inactive_notice() {
				$is_installed = file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');
				$modal_btn = $this->wc_install_button_label();
				?>
                <div class="ttbm-pay-wc-warning">
                    <p class="ttbm-pay-wc-warning-title"><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Notice: WooCommerce is Not Activated', 'tour-booking-manager'); ?></p>
                    <p class="ttbm-pay-wc-warning-desc"><?php esc_html_e('To process payments and manage payment gateways here, you must install and activate WooCommerce.', 'tour-booking-manager'); ?></p>
                    <button type="button" id="ttbm-woo-install-trigger" class="button button-primary ttbm-woo-install-trigger">
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
			private function wc_install_button_label() {
				return file_exists(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php')
					? esc_html__('Activate WooCommerce Now', 'tour-booking-manager')
					: esc_html__('Install & Activate Now', 'tour-booking-manager');
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
			/**
			 * Custom Payment settings: the payment methods (Offline Payment switches on
			 * right here; PayPal and Stripe come with PRO), the booking rules, and one
			 * Save button for everything on this panel.
			 */
			private function render_custom_payment_subtab() {
				$specs = $this->gateway_specs();
				$opts = (array) get_option('ttbm_payment_settings', array());
				$is_pro = $this->is_pro_active();
				$allow_guest_booking = $this->opt('ttbm_payment_allow_guest_booking', 'yes');
				$confirmation_page = (int) $this->opt('ttbm_payment_confirmation_page', 0);
				$pro_url = 'https://mage-people.com/product/woocommerce-tour-and-travel-booking-manager-pro/';
				$order = array_unique(array_merge(array('offline'), array_keys($specs)));
				?>
				<section class="ttbm-pset-section" aria-labelledby="ttbm-pset-methods-title">
					<h3 class="ttbm-pset-heading" id="ttbm-pset-methods-title"><?php esc_html_e('Payment methods', 'tour-booking-manager'); ?></h3>
					<div class="ttbm-pset-list">
						<?php foreach ($order as $gateway_id) :
							if (!isset($specs[$gateway_id])) {
								continue;
							}
							$spec = $specs[$gateway_id];
							$is_enabled = isset($opts[$spec['enable_key']]) && 'on' === $opts[$spec['enable_key']];
							$locked = !empty($spec['pro']) && !$is_pro;
							$inline = 'offline' === $gateway_id;
							$switch_id = 'ttbm-pset-' . $gateway_id;
							?>
							<div class="ttbm-pset-method<?php echo $is_enabled ? ' is-on' : ''; ?><?php echo $locked ? ' is-locked' : ''; ?>" data-gateway-id="<?php echo esc_attr($gateway_id); ?>">
								<div class="ttbm-pset-row">
									<span class="ttbm-pset-row__main">
										<span class="ttbm-pset-icon-box" aria-hidden="true"><?php echo self::gateway_card_icon($gateway_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup ?></span>
										<span class="ttbm-pset-row__copy">
											<span class="ttbm-pset-title-row">
												<?php if ($inline) : ?>
													<label class="ttbm-pset-title" for="<?php echo esc_attr($switch_id); ?>"><?php echo esc_html($spec['label']); ?></label>
												<?php else : ?>
													<b class="ttbm-pset-title"><?php echo esc_html($spec['label']); ?></b>
												<?php endif; ?>
												<?php if (!empty($spec['pro'])) : ?>
													<span class="ttbm-pset-pro"><?php esc_html_e('PRO', 'tour-booking-manager'); ?></span>
												<?php endif; ?>
											</span>
											<span class="ttbm-pset-desc"><?php echo esc_html($spec['desc']); ?></span>
										</span>
									</span>
									<?php if ($locked) : ?>
										<a class="ttbm-pset-link" href="<?php echo esc_url($pro_url); ?>" target="_blank" rel="noopener noreferrer">
											<?php esc_html_e('Get PRO', 'tour-booking-manager'); ?>
											<span class="screen-reader-text"><?php
												/* translators: %s: payment method name, e.g. PayPal */
												echo esc_html(sprintf(__('to use %s (opens in a new tab)', 'tour-booking-manager'), $spec['label'])); ?></span>
										</a>
									<?php elseif ($inline) : ?>
										<span class="ttbm-pset-switch-wrap">
											<span class="ttbm-pset-state" data-pset-state aria-hidden="true"><?php echo $is_enabled ? esc_html__('On', 'tour-booking-manager') : esc_html__('Off', 'tour-booking-manager'); ?></span>
											<button type="button" id="<?php echo esc_attr($switch_id); ?>" class="ttbm-pset-switch" role="switch" aria-checked="<?php echo $is_enabled ? 'true' : 'false'; ?>" data-field="<?php echo esc_attr($spec['enable_key']); ?>"><span aria-hidden="true"></span></button>
										</span>
									<?php else : ?>
										<span class="ttbm-pset-switch-wrap">
											<span class="ttbm-pset-state" data-pset-state><?php echo $is_enabled ? esc_html__('On', 'tour-booking-manager') : esc_html__('Off', 'tour-booking-manager'); ?></span>
											<button type="button" class="ttbm-pset-btn" data-ttbm-gw-modal="ttbm-gw-modal-<?php echo esc_attr($gateway_id); ?>">
												<?php esc_html_e('Set up', 'tour-booking-manager'); ?>
												<span class="screen-reader-text"><?php echo esc_html($spec['label']); ?></span>
											</button>
										</span>
									<?php endif; ?>
								</div>
								<?php if ($inline) : ?>
									<div class="ttbm-pset-method__detail"<?php echo $is_enabled ? '' : ' hidden'; ?>>
										<?php foreach ($spec['fields'] as $field) :
											if ('toggle' === $field['type']) {
												continue;
											}
											$field_id = 'ttbm-pset-' . $field['key'];
											$value = isset($opts[$field['key']]) ? $opts[$field['key']] : '';
											?>
											<label class="ttbm-pset-label" for="<?php echo esc_attr($field_id); ?>"><?php echo esc_html('ttbm_offline_label' === $field['key'] ? __('Name guests see', 'tour-booking-manager') : $field['label']); ?></label>
											<input type="<?php echo esc_attr($field['type']); ?>" id="<?php echo esc_attr($field_id); ?>" class="ttbm-pset-input" data-field="<?php echo esc_attr($field['key']); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr__('Offline Payment', 'tour-booking-manager'); ?>">
											<span class="ttbm-pset-hint"><?php esc_html_e('For example "Bank transfer" or "Pay at venue".', 'tour-booking-manager'); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</section>

				<?php foreach ($specs as $gateway_id => $spec) :
					// PayPal and Stripe keep their own setup popup (keys, test mode); only with PRO.
					if ('offline' === $gateway_id || empty($spec['pro']) || !$is_pro) {
						continue;
					}
					$is_enabled = isset($opts[$spec['enable_key']]) && $opts[$spec['enable_key']] === 'on';
					?>
					<div id="ttbm-gw-modal-<?php echo esc_attr($gateway_id); ?>" class="ttbm-gw-modal" style="display:none;">
						<div class="ttbm-gw-modal-box">
							<div class="ttbm-gw-modal-header">
								<h2><?php echo esc_html($spec['label']); ?> <?php esc_html_e('Configuration', 'tour-booking-manager'); ?></h2>
								<button type="button" class="ttbm-gw-modal-close" aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">&times;</button>
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

				<section class="ttbm-pset-section" aria-labelledby="ttbm-pset-rules-title">
					<h3 class="ttbm-pset-heading" id="ttbm-pset-rules-title"><?php esc_html_e('Booking rules', 'tour-booking-manager'); ?></h3>
					<div class="ttbm-pset-fields">
						<div class="ttbm-pset-field">
							<label class="ttbm-pset-label" for="ttbm-pset-confirm"><?php esc_html_e('Confirmation page', 'tour-booking-manager'); ?></label>
							<?php
							// wp_dropdown_pages() already escapes its own output.
							echo wp_dropdown_pages(array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'selected' => $confirmation_page,
								'name' => 'ttbm_payment_settings[ttbm_payment_confirmation_page]',
								'id' => 'ttbm-pset-confirm',
								'class' => 'ttbm-pset-select',
								'show_option_none' => esc_html__('Choose a page', 'tour-booking-manager'),
								'option_none_value' => 0,
								'echo' => 0,
							));
							?>
							<span class="ttbm-pset-hint"><?php esc_html_e('Where guests land after booking.', 'tour-booking-manager'); ?></span>
						</div>
						<div class="ttbm-pset-field">
							<label class="ttbm-pset-label" for="ttbm-pset-guest"><?php esc_html_e('Guest booking', 'tour-booking-manager'); ?></label>
							<select id="ttbm-pset-guest" class="ttbm-pset-select" name="ttbm_payment_settings[ttbm_payment_allow_guest_booking]">
								<option value="yes" <?php selected($allow_guest_booking, 'yes'); ?>><?php esc_html_e('Anyone can book', 'tour-booking-manager'); ?></option>
								<option value="no" <?php selected($allow_guest_booking, 'no'); ?>><?php esc_html_e('Require an account', 'tour-booking-manager'); ?></option>
							</select>
							<span class="ttbm-pset-hint"><?php esc_html_e('With an account required, guests log in when they book.', 'tour-booking-manager'); ?></span>
						</div>
					</div>
				</section>

				<div class="ttbm-pset-footer">
					<span class="ttbm-pset-footer__msg" data-pset-msg role="status" aria-live="polite"></span>
					<button type="button" class="ttbm-pset-btn ttbm-pset-cancel" data-pset-cancel><?php esc_html_e('Cancel', 'tour-booking-manager'); ?></button>
					<button type="button" class="ttbm-pset-btn ttbm-pset-btn--primary" data-pset-save data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_admin_nonce')); ?>"><?php esc_html_e('Save changes', 'tour-booking-manager'); ?></button>
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
				if (!$this->can_manage_payments()) {
					return;
				}
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
			/**
			 * State needed to update payment notices and the editor sidebar without
			 * reloading the current tour/hotel edit request.
			 */
			private function payment_state_response(): array {
				list(, $status_title, $status_text) = self::status_copy();
				return array(
					'has_gateway' => self::has_functional_payment_method(),
					'warning' => self::gateway_warning(),
					'mode_label' => self::get_booking_mode_label(),
					'gateway_names' => self::get_active_gateway_names(),
					'status_title' => $status_title,
					'status_text' => $status_text,
				);
			}
			/**
			 * Re-render the payment manager after WooCommerce becomes active in a
			 * previous AJAX request. This fresh request boots WooCommerce normally,
			 * so its gateway classes and settings forms are available immediately.
			 */
			public function ajax_refresh_payment_settings() {
				$this->verify_admin_ajax_wc();
				ob_start();
				$this->render_tab_content();
				$html = ob_get_clean();
				wp_send_json_success(array_merge(array('html' => $html), $this->payment_state_response()));
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
				wp_send_json_success(array_merge(array(
					'message' => esc_html__('Settings saved successfully!', 'tour-booking-manager'),
					'enabled' => $enabled ? 'yes' : 'no',
				), $this->payment_state_response()));
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
				wp_send_json_success(array_merge($response, $this->payment_state_response()));
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
				// The gateway objects read the option when they are built, so rebuild them
				// before reporting whether guests can now pay.
				if (class_exists('TTBM_Payment_Gateway_Manager') && method_exists('TTBM_Payment_Gateway_Manager', 'reset')) {
					TTBM_Payment_Gateway_Manager::reset();
				}
				wp_send_json_success(array_merge(array(
					'message' => esc_html__('Settings saved successfully!', 'tour-booking-manager'),
					'enabled' => $existing[$spec['enable_key']],
				), $this->payment_state_response()));
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
					'role' => get_role('customer') ? 'customer' : 'subscriber',
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
				$tour_status = $tour_id ? get_post_status($tour_id) : '';
				// A private tour is bookable by whoever may read it (administrators, or
				// any logged-in user under "Private Tours for Logged-in Users").
				$tour_readable = 'publish' === $tour_status || ('private' === $tour_status && current_user_can('read_post', $tour_id));
				if (!$tour_id || get_post_type($tour_id) !== TTBM_Function::get_cpt_name() || !$tour_readable) {
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
