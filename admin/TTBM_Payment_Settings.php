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
				add_filter('woocommerce_add_to_cart_redirect', array($this, 'add_to_cart_redirect'));
				add_filter('woocommerce_get_checkout_order_received_url', array($this, 'checkout_order_received_url'), 10, 2);
				add_filter('woocommerce_checkout_fields', array($this, 'maybe_hide_billing_fields'));
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
				?>
                <div class="ttbm-pay-subtabs nav-tab-wrapper">
                    <a href="#" class="ttbm-pay-subtab-link is-active" data-subtab="woocommerce"><?php esc_html_e('WooCommerce', 'tour-booking-manager'); ?></a>
                    <a href="#" class="ttbm-pay-subtab-link" data-subtab="custom"><?php esc_html_e('Custom Payment', 'tour-booking-manager'); ?></a>
                </div>

                <div class="ttbm-pay-subtab-panel" data-subtab-panel="woocommerce">
					<?php $this->render_woocommerce_subtab(); ?>
                </div>
                <div class="ttbm-pay-subtab-panel" data-subtab-panel="custom" style="display:none;">
					<?php $this->render_custom_payment_subtab(); ?>
                </div>

                <div class="justifyBetween _mT">
                    <div></div>
					<?php submit_button(); ?>
                </div>
				<?php
			}
			private function render_woocommerce_subtab() {
				if (!TTBM_Global_Function::has_woocommerce()) {
					$this->render_wc_inactive_notice();
					return;
				}
				$wc_enabled = $this->opt('ttbm_wc_payment_enabled', 'on') === 'on';
				?>
                <div class="ttbm-pay-field-row">
                    <label class="ttbm-pay-toggle-label">
                        <span><?php esc_html_e('Enable WooCommerce Payment', 'tour-booking-manager'); ?></span>
                        <span class="ttbm-pay-toggle-sub"><?php esc_html_e('If enabled, WooCommerce payment gateways are used for checkout.', 'tour-booking-manager'); ?></span>
                    </label>
                    <label class="ttbm-gw-switch">
                        <input type="checkbox" name="ttbm_payment_settings[ttbm_wc_payment_enabled]" value="on" <?php checked($wc_enabled); ?>>
                        <span class="ttbm-gw-slider"></span>
                    </label>
                </div>

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
				$after_order_redirect = $this->opt('ttbm_payment_after_order_redirect', 'plugin_thankyou');
				$require_login = $this->opt('ttbm_payment_require_login', 'off') === 'on';
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
                    <label class="ttbm-pay-field-label"><?php esc_html_e('After Confirming the Order, Redirect To', 'tour-booking-manager'); ?></label>
                    <div class="ttbm-pay-field-control">
                        <select name="ttbm_payment_settings[ttbm_payment_after_order_redirect]" class="formControl">
                            <option value="plugin_thankyou" <?php selected($after_order_redirect, 'plugin_thankyou'); ?>><?php esc_html_e('Plugin Thank You Page', 'tour-booking-manager'); ?></option>
                            <option value="default" <?php selected($after_order_redirect, 'default'); ?>><?php esc_html_e('WooCommerce Default', 'tour-booking-manager'); ?></option>
                        </select>
                        <p class="ttbm-pay-field-desc"><?php esc_html_e('Select where to redirect after confirming the order. Choose the actual page under Custom Payment → Booking Confirmation Page.', 'tour-booking-manager'); ?></p>
                    </div>
                </div>
                <div class="ttbm-pay-field-row">
                    <label class="ttbm-pay-field-label"><?php esc_html_e('Require Account Login', 'tour-booking-manager'); ?></label>
                    <div class="ttbm-pay-field-control">
                        <label class="ttbm-pay-checkbox">
                            <input type="checkbox" name="ttbm_payment_settings[ttbm_payment_require_login]" value="on" <?php checked($require_login); ?>>
							<?php esc_html_e('Require login to book a tour.', 'tour-booking-manager'); ?>
                        </label>
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
			private function render_custom_payment_subtab() {
				$specs = $this->gateway_specs();
				$opts = (array) get_option('ttbm_payment_settings', array());
				$is_pro = $this->is_pro_active();
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
                        <p class="ttbm-pay-field-desc"><?php esc_html_e('Optional. Redirect customers here instead of the default WooCommerce order-received page after a successful booking.', 'tour-booking-manager'); ?></p>
                    </div>
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
			public function checkout_order_received_url($url, $order) {
				if (!($order instanceof WC_Order)) {
					return $url;
				}
				if ('plugin_thankyou' !== $this->opt('ttbm_payment_after_order_redirect', 'plugin_thankyou')) {
					return $url;
				}
				$page_id = (int) $this->opt('ttbm_payment_confirmation_page', 0);
				if ($page_id <= 0 || get_post_status($page_id) !== 'publish' || !$this->order_has_ttbm_item($order)) {
					return $url;
				}
				return add_query_arg('order-received', $order->get_id(), get_permalink($page_id));
			}
			private function order_has_ttbm_item($order) {
				foreach ($order->get_items() as $item) {
					if ($item->get_meta('_ttbm_id')) {
						return true;
					}
				}
				return false;
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
			public static function login_required_for_booking() {
				return TTBM_Global_Function::get_settings('ttbm_payment_settings', 'ttbm_payment_require_login', 'off') === 'on';
			}
			public static function render_login_prompt() {
				?>
                <div class="ttbm_book_now_area ttbm_login_required_notice">
                    <p><?php esc_html_e('Please log in to book this tour.', 'tour-booking-manager'); ?></p>
                    <a class="dButton ttbm_book_now" href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">
						<?php esc_html_e('Login to Book', 'tour-booking-manager'); ?>
                    </a>
                </div>
				<?php
			}
		}
		new TTBM_Payment_Settings();
	}
