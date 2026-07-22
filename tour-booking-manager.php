<?php
/**
 * Plugin Name: Tour & Travel Booking Manager for WooCommerce | Tour & Hotel Booking Solution
 * Plugin URI: http://mage-people.com
 * Description: A Complete Tour and Travel Solution for WordPress by MagePeople.
 * Version: 2.1.9
 * Author: MagePeople Team
 * Author URI: http://www.mage-people.com/
 * Text Domain: tour-booking-manager
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
	die;
} // Cannot access pages directly.

// WooCommerce fallback stub functions to prevent fatal errors when WooCommerce is inactive.
// Hooked to plugins_loaded so that WooCommerce (if active or being activated) has loaded first,
// preventing any redeclaration conflicts.
add_action('plugins_loaded', 'ttbm_define_woocommerce_fallbacks', 1);
function ttbm_define_woocommerce_fallbacks() {
	if (class_exists('WooCommerce')) {
		return;
	}

	// Detect if WooCommerce is being activated during this request to avoid redeclaration conflicts.
	$is_activating = false;
	if (isset($GLOBALS['ttbm_activating_woocommerce']) && $GLOBALS['ttbm_activating_woocommerce']) {
		$is_activating = true;
	}
	if (!$is_activating && (is_admin() || (defined('WP_CLI') && WP_CLI) || isset($_SERVER['argv']))) {
		// Web activation check (single or bulk).
		if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'activate') {
			if (isset($_REQUEST['plugin']) && strpos(sanitize_text_field(wp_unslash($_REQUEST['plugin'])), 'woocommerce.php') !== false) {
				$is_activating = true;
			}
			if (isset($_REQUEST['checked']) && is_array($_REQUEST['checked'])) {
				foreach ($_REQUEST['checked'] as $checked_plugin) {
					if (strpos(sanitize_text_field(wp_unslash($checked_plugin)), 'woocommerce.php') !== false) {
						$is_activating = true;
						break;
					}
				}
			}
		}
		// TTBM_Woo_Installer's own AJAX-driven install/activate flow
		// (admin-ajax.php?action=ttbm_woo_step) — same collision risk as the
		// plugins.php activation URL above, but doesn't match that pattern.
		if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'ttbm_woo_step') {
			$is_activating = true;
		}
		// CLI / script activation check.
		if (!$is_activating && isset($_SERVER['argv']) && is_array($_SERVER['argv'])) {
			foreach ($_SERVER['argv'] as $arg) {
				if (strpos($arg, 'woocommerce') !== false) {
					$is_activating = true;
					break;
				}
			}
		}
	}

	if ($is_activating) {
		return;
	}

	if (!class_exists('TTBM_WC_Cart_Fallback')) {
		class TTBM_WC_Cart_Fallback {
			public function get_cart() { return array(); }
			public function empty_cart() {}
		}
	}
	if (!class_exists('TTBM_WC_Customer_Fallback')) {
		class TTBM_WC_Customer_Fallback {
			public function get_is_vat_exempt() { return false; }
		}
	}
	if (!class_exists('TTBM_WC_Fallback')) {
		class TTBM_WC_Fallback {
			public $cart;
			public $customer;
			public $version = '0.0.0';
			public function __construct() {
				$this->cart = new TTBM_WC_Cart_Fallback();
				$this->customer = new TTBM_WC_Customer_Fallback();
			}
		}
	}
	if (!function_exists('WC')) {
		function WC() {
			static $instance = null;
			if (null === $instance) {
				$instance = new TTBM_WC_Fallback();
			}
			return $instance;
		}
	}
	if (!function_exists('wc_get_orders')) {
		function wc_get_orders($args = array()) { return array(); }
	}
	if (!function_exists('wc_get_order')) {
		function wc_get_order($order_id) { return false; }
	}
	if (!function_exists('wc_get_product')) {
		function wc_get_product($product_id) { return false; }
	}
	if (!function_exists('wc_price')) {
		function wc_price($price, $args = array()) {
			$amount   = (float) $price;
			$settings = wp_parse_args(
				(array) get_option('ttbm_currency_settings', array()),
				array(
					'symbol'       => '$',
					'position'     => 'left',
					'decimal_sep'  => '.',
					'thousand_sep' => ',',
					'num_decimals' => 2,
				)
			);
			$symbol   = (string) $settings['symbol'];
			$position = (string) $settings['position'];
			$dec_sep  = (string) $settings['decimal_sep'];
			$thou_sep = (string) $settings['thousand_sep'];
			$decimals = (int) $settings['num_decimals'];
			$number   = number_format($amount, $decimals, $dec_sep, $thou_sep);
			switch ($position) {
				case 'right':       return '<span class="woocommerce-Price-amount amount">' . $number . '<span class="woocommerce-Price-currencySymbol">' . esc_html($symbol) . '</span></span>';
				case 'left_space':  return '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' . esc_html($symbol) . '</span>&nbsp;' . $number . '</span>';
				case 'right_space': return '<span class="woocommerce-Price-amount amount">' . $number . '&nbsp;<span class="woocommerce-Price-currencySymbol">' . esc_html($symbol) . '</span></span>';
				default:            return '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' . esc_html($symbol) . '</span>' . $number . '</span>';
			}
		}
	}
	if (!function_exists('get_woocommerce_currency')) {
		function get_woocommerce_currency() { return 'USD'; }
	}
	if (!function_exists('get_woocommerce_currency_symbol')) {
		function get_woocommerce_currency_symbol($currency = 'USD') {
			$settings = get_option('ttbm_currency_settings', array());
			return isset($settings['symbol']) ? (string) $settings['symbol'] : '$';
		}
	}
	if (!function_exists('wc_prices_include_tax')) {
		function wc_prices_include_tax() { return false; }
	}
	if (!function_exists('wc_get_price_thousand_separator')) {
		function wc_get_price_thousand_separator() {
			$settings = get_option('ttbm_currency_settings', array());
			return isset($settings['thousand_sep']) ? (string) $settings['thousand_sep'] : ',';
		}
	}
	if (!function_exists('wc_get_price_decimal_separator')) {
		function wc_get_price_decimal_separator() {
			$settings = get_option('ttbm_currency_settings', array());
			return isset($settings['decimal_sep']) ? (string) $settings['decimal_sep'] : '.';
		}
	}
	if (!function_exists('is_woocommerce')) {
		function is_woocommerce() { return false; }
	}
	if (!function_exists('is_product')) {
		function is_product() { return false; }
	}
	if (!function_exists('wc_get_cart_url')) {
		function wc_get_cart_url() { return ''; }
	}
	if (!function_exists('wc_get_checkout_url')) {
		function wc_get_checkout_url() { return ''; }
	}
	if (!function_exists('wc_get_account_endpoint_url')) {
		function wc_get_account_endpoint_url($endpoint) { return ''; }
	}
	if (!function_exists('wc_format_decimal')) {
		function wc_format_decimal($number, $dp = false, $trim_zeros = false) {
			$number = (float) str_replace(',', '.', (string) $number);
			return false === $dp ? $number : round($number, (int) $dp);
		}
	}
	if (!function_exists('wc_get_price_including_tax')) {
		function wc_get_price_including_tax($product, $args = array()) {
			$args = wp_parse_args($args, array('qty' => 1, 'price' => ''));
			return '' !== $args['price'] ? (float) $args['price'] * (float) $args['qty'] : 0.0;
		}
	}
	if (!function_exists('wc_get_price_excluding_tax')) {
		function wc_get_price_excluding_tax($product, $args = array()) {
			$args = wp_parse_args($args, array('qty' => 1, 'price' => ''));
			return '' !== $args['price'] ? (float) $args['price'] * (float) $args['qty'] : 0.0;
		}
	}
	if (!function_exists('wc_get_order_item_meta')) {
		function wc_get_order_item_meta($item_id, $key, $single = true) {
			return $single ? '' : array();
		}
	}
}

if (!class_exists('TTBM_Woocommerce_Plugin')) {


	class TTBM_Woocommerce_Plugin {

			public function __construct() {
				$this->load_ttbm_plugin();
				add_action('init', array($this, 'load_blocks'));
				register_activation_hook(__FILE__, array($this, 'plugin_activation'));
			}
			private function load_ttbm_plugin() {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if (!defined('TTBM_PLUGIN_DIR')) {
					define('TTBM_PLUGIN_DIR', dirname(__FILE__));
				}
				if (!defined('TTBM_PLUGIN_URL')) {
					define('TTBM_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
				}
				if (!defined('TTBM_PLUGIN_VERSION')) {
					define('TTBM_PLUGIN_VERSION', '2.1.9');
				}
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Dependencies.php';
				add_action('admin_init', array($this, 'activation_redirect_setup'), 90);
			}
			public function plugin_activation() {
				// Set transient to trigger WooCommerce check / redirect
				set_transient('ttbm_plugin_activated', true, 60);
			}
			public function activation_redirect_setup($plugin) {
				// Create pages if WooCommerce is active
				if (TTBM_Global_Function::check_woocommerce() == 1) {
					self::on_activation_page_create();
				}
			}
			public static function on_activation_page_create() {
				$pages_to_create = [
					'find' => [
						'slug' => 'find',
						'title' => 'Tour Search Result',
						'content' => '[ttbm-search-result]',
						'option_key' => 'ttbm_page_find_created',
					],
					'hotel-search' => [
						'slug' => 'hotel-search',
						'title' => 'Hotel Search Result',
						'content' => '[wptravelly-hotel-search]',
						'option_key' => 'ttbm_page_hotel_search_created',
					],
					'hotel-search-result' => [
						'slug' => 'hotel-search-result',
						'title' => 'Hotel Search Result',
						'content' => '[wptravelly-hotel-search-list]',
						'option_key' => 'ttbm_page_hotel_search_result_created',
					],
					'lotus-grid' => [
						'slug' => 'lotus-grid',
						'title' => 'Tour Lotus Grid View',
						'content' => "[travel-list style='lotus' column= show='12' pagination='yes']",
						'option_key' => 'ttbm_page_lotus_grid_created',
					],
					'orchid-grid' => [
						'slug' => 'orchid-grid',
						'title' => 'Tour Orchid Grid View',
						'content' => "[travel-list style='orchid' column= pagination='yes' show=12]",
						'option_key' => 'ttbm_page_orchid_grid_created',
					],
					'ttbm-tour-list' => [
						'slug' => 'ttbm-tour-list',
						'title' => 'Tour New Style',
						'content' => "[ttbm-tour-list column='3' pagination='yes' show=12]",
						'option_key' => 'ttbm_page_ttbm_tour_list_grid_created',
					],
				];
				foreach ($pages_to_create as $page_data) {
					$page_exists = TTBM_Global_Function::get_page_by_slug($page_data['slug']); // Check by slug
					$option_exists = get_option($page_data['option_key']); // Check option table
					if (!$page_exists && !$option_exists) {
						// Create the page if it doesn't exist
						$page = [
							'post_type' => 'page',
							'post_name' => $page_data['slug'],
							'post_title' => $page_data['title'],
							'post_content' => $page_data['content'],
							'post_status' => 'publish',
						];
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$page_id = wp_insert_post($page);
						if (is_wp_error($page_id)) {
							printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($page_id->get_error_message()));
						} else {
							update_option($page_data['option_key'], true);
						}
					}
				}
				// Update repeated fields
				self::update_repeated_fields();
			}

			
			public function load_blocks() {
				// Add block editor support
				require_once TTBM_PLUGIN_DIR . '/support/blocks/index.php';
				// Add block category
				add_filter('block_categories_all', array($this, 'ttbm_block_category'));
				// Register block editor assets
				add_action('enqueue_block_editor_assets', array($this, 'ttbm_enqueue_block_editor_assets'));
			}
			public function ttbm_block_category($categories) {
				return array_merge(
					array(
						array(
							'slug' => 'tour-booking-manager',
							'title' => __('Tour Booking Manager', 'tour-booking-manager'),
							'icon' => 'calendar-alt',
						),
					),
					$categories
				);
			}
			public function ttbm_enqueue_block_editor_assets() {
				wp_enqueue_script(
					'tour-booking-manager-blocks',
					plugins_url('support/blocks/build/index.js', __FILE__),
					array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor')
				);
			}
			private static function update_repeated_fields() {
				if (get_option('ttbm_repeated_field_update') === 'completed') {
					return; // Skip if already updated
				}
				$args = [
					'post_type' => 'ttbm_tour',
					'posts_per_page' => -1,
				];
				$query = new WP_Query($args);
				if ($query->have_posts()) {
					foreach ($query->posts as $post) {
						$post_id = $post->ID;
						$start_date = TTBM_Global_Function::get_post_info($post_id, 'ttbm_travel_start_date');
						$end_date = TTBM_Global_Function::get_post_info($post_id, 'ttbm_travel_end_date');
						update_post_meta($post_id, 'ttbm_travel_repeated_start_date', $start_date);
						update_post_meta($post_id, 'ttbm_travel_repeated_end_date', $end_date);
					}
				}
				wp_reset_postdata();
				update_option('ttbm_repeated_field_update', 'completed');
			}
		}
		new TTBM_Woocommerce_Plugin();
	}
