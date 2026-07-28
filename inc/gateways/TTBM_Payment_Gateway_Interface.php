<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	interface TTBM_Payment_Gateway_Interface {
		public function get_id();
		public function get_title();
		public function is_enabled();
		public function process_payment($order_data);
		public function verify_payment($order_id);
	}
