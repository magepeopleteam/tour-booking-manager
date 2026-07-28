<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.

	require_once __DIR__ . '/TTBM_Payment_Gateway_Interface.php';
	require_once __DIR__ . '/TTBM_Abstract_Payment_Gateway.php';
	require_once __DIR__ . '/TTBM_Offline_Gateway.php';

	/**
	 * Free standalone gateway registry.
	 *
	 * Offline Payment is included here. Tour Pro supplies its own manager with
	 * PayPal and Stripe when that addon is active, so the free runtime is not
	 * loaded in that case.
	 */
	if (!class_exists('TTBM_Payment_Gateway_Manager')) {
		class TTBM_Payment_Gateway_Manager {
			private static $instance = null;
			private $gateways = array();

			public static function get_instance() {
				if (null === self::$instance) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			private function __construct() {
				$this->register_gateway(new TTBM_Offline_Gateway());
				do_action('ttbm_register_payment_gateways', $this);
			}

			public function register_gateway(TTBM_Payment_Gateway_Interface $gateway) {
				$this->gateways[$gateway->get_id()] = $gateway;
			}

			public function get_gateway($id) {
				return isset($this->gateways[$id]) ? $this->gateways[$id] : null;
			}

			public function get_all_gateways() {
				return $this->gateways;
			}

			public function get_available_gateways() {
				return array_filter(
					$this->gateways,
					static function ($gateway) {
						return $gateway->is_enabled();
					}
				);
			}
		}
	}
