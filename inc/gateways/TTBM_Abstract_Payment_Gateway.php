<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * Base class for the custom (non-WooCommerce) payment gateways.
	 *
	 * Credentials are read from the `ttbm_payment_settings` option, which the
	 * free plugin's Settings → Payments → Custom Payment tab saves via the
	 * `ttbm_save_gateway_settings` AJAX handler (see TTBM_Payment_Settings in
	 * the free plugin). Key pattern: ttbm_{gateway_id}_{setting}.
	 */
	abstract class TTBM_Abstract_Payment_Gateway implements TTBM_Payment_Gateway_Interface {
		protected $id;
		protected $title;
		protected $settings;
		public function __construct() {
			$this->settings = (array) get_option('ttbm_payment_settings', array());
			$this->init_gateway();
		}
		abstract protected function init_gateway();
		public function get_id() {
			return $this->id;
		}
		public function get_title() {
			return $this->title;
		}
		public function is_enabled() {
			$key = 'ttbm_' . $this->id . '_enable';
			return isset($this->settings[$key]) && 'on' === $this->settings[$key];
		}
		protected function get_setting($key, $default = '') {
			$full_key = 'ttbm_' . $this->id . '_' . $key;
			return isset($this->settings[$full_key]) ? $this->settings[$full_key] : $default;
		}
		protected function is_sandbox() {
			return 'on' === $this->get_setting('sandbox', 'off');
		}
		/**
		 * Return the external hosted-checkout URL without redirecting.
		 * Gateways that redirect to an external payment page override this.
		 * Offline-style gateways return null (no redirect needed).
		 *
		 * @param array $order_data Same shape as process_payment() receives.
		 * @return string|WP_Error|null
		 */
		public function get_payment_url($order_data) {
			return null;
		}
	}
