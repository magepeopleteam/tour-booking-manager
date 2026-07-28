<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * Offline payment (bank transfer, cash, pay at venue). No external redirect:
	 * the order is recorded as pending and the admin confirms it manually from
	 * the Tour Booking page once payment arrives.
	 */
	class TTBM_Offline_Gateway extends TTBM_Abstract_Payment_Gateway {
		protected function init_gateway() {
			$this->id = 'offline';
			$label = $this->get_setting('label');
			$this->title = $label !== '' ? $label : esc_html__('Offline Payment', 'tour-booking-manager');
		}
		public function process_payment($order_data) {
			// Nothing to charge — TTBM_Custom_Checkout finalizes the order as pending.
			return true;
		}
		public function verify_payment($order_id) {
			// Offline payments are confirmed manually by the admin.
			return false;
		}
	}
