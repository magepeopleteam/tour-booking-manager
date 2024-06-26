<?php
	/**
	 * Plugin Name: Travel Booking Plugin | Tour & Hotel Booking Solution For WooCommerce – wptravelly
	 * Plugin URI: http://mage-people.com
	 * Description: A Complete Tour and Travel Solution for WordPress by MagePeople.
	 * Version: 1.7.5
	 * Author: MagePeople Team
	 * Author URI: http://www.mage-people.com/
	 * Text Domain: tour-booking-manager
	 * Domain Path: /languages/
	 */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Woocommerce_Plugin')) {
		class TTBM_Woocommerce_Plugin {
			public function __construct() {
				$this->load_ttbm_plugin();
			}
			private function load_ttbm_plugin() {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if (!defined('TTBM_PLUGIN_DIR')) {
					define('TTBM_PLUGIN_DIR', dirname(__FILE__));
				}
				if (!defined('TTBM_PLUGIN_URL')) {
					define('TTBM_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
				}
				if (!defined('TTBM_PLUGIN_DATA')) {
					define('TTBM_PLUGIN_DATA', get_plugin_data(__FILE__));
				}
				require_once TTBM_PLUGIN_DIR . '/mp_global/MP_Global_File_Load.php';
				$this->load_global_file();
				if (MP_Global_Function::check_woocommerce() == 1) {
					// add_action('activated_plugin', array($this, 'activation_redirect'), 90, 1);
					$this->appsero_init_tracker_ttbm();
					require_once TTBM_PLUGIN_DIR . '/lib/classes/class-ttbm.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Dependencies.php';
				} else {
					require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Quick_Setup.php';
					//add_action('admin_notices', [$this, 'woocommerce_not_active']);
					// add_action('activated_plugin', array($this, 'activation_redirect_setup'), 90, 1);
				}
				add_action('admin_init', array($this, 'activation_redirect_setup'), 90);

			}
			public function load_global_file() {
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Style.php';
			}
			public function appsero_init_tracker_ttbm() {
				if (!class_exists('Appsero\Client')) {
					require_once __DIR__ . '/lib/appsero/src/Client.php';
				}
				$client = new Appsero\Client('5e44d3f4-ddea-4784-8c15-4502ad6e7426', 'Tour Booking Manager For Woocommerce', __FILE__);
				$client->insights()->init();
			}
			public function activation_redirect($plugin) {
				if (MP_Global_Function::check_woocommerce() == 1) {
					self::on_activation_page_create();
				}
				$ttbm_quick_setup_done = get_option('ttbm_quick_setup_done');
				if ($ttbm_quick_setup_done != 'yes') {
					exit(wp_redirect(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_quick_setup')));
				}
			}
			public function activation_redirect_setup($plugin) {
				if (MP_Global_Function::check_woocommerce() == 1) {
					self::on_activation_page_create();
				}
				$ttbm_quick_setup_done = get_option('ttbm_quick_setup_done') ? get_option('ttbm_quick_setup_done') : 'no';
				if ($ttbm_quick_setup_done == 'no') {

					if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'ttbm_quick_setup'){
						return null;
					}else{
						exit(wp_redirect(admin_url('admin.php?post_type=ttbm_tour&page=ttbm_quick_setup')));
					}
					
				}
			}
			public static function on_activation_page_create() {
				if (!MP_Global_Function::get_page_by_slug('find')) {
					$ttbm_search_page = array(
						'post_type' => 'page',
						'post_name' => 'find',
						'post_title' => 'Tour Search Result',
						'post_content' => '[ttbm-search-result]',
						'post_status' => 'publish',
					);
					unset($find_page_id);
					$find_page_id = wp_insert_post($ttbm_search_page);
					if (is_wp_error($find_page_id)) {
						printf('<div class="error" style="background:red; color:#fff;"><p>%s</p></div>', $find_page_id->get_error_message());
					}
				}
				if (!MP_Global_Function::get_page_by_slug('lotus-grid')) {
					$ttbm_search_page = array(
						'post_type' => 'page',
						'post_name' => 'lotus-grid',
						'post_title' => 'Tour Lotus Grid View',
						'post_content' => "[travel-list style='lotus' column=4 show='12' pagination='yes']",
						'post_status' => 'publish',
					);
					unset($find_page_id);
					$find_page_id = wp_insert_post($ttbm_search_page);
					if (is_wp_error($find_page_id)) {
						printf('<div class="error" style="background:red; color:#fff;"><p>%s</p></div>', $find_page_id->get_error_message());
					}
				}
				if (!MP_Global_Function::get_page_by_slug('orchid-grid')) {
					$ttbm_search_page = array(
						'post_type' => 'page',
						'post_name' => 'orchid-grid',
						'post_title' => 'Tour Orchid Grid View',
						'post_content' => "[travel-list style='orchid' column=4 pagination='yes' show=12]",
						'post_status' => 'publish',
					);
					unset($find_page_id);
					$find_page_id = wp_insert_post($ttbm_search_page);
					if (is_wp_error($find_page_id)) {
						printf('<div class="error" style="background:red; color:#fff;"><p>%s</p></div>', $find_page_id->get_error_message());
					}
				}
				if (get_option('ttbm_repeated_field_update') != 'completed') {
					$args = array(
						'post_type' => 'ttbm_tour',
						'posts_per_page' => -1
					);
					$qr = new WP_Query($args);
					foreach ($qr->posts as $result) {
						$post_id = $result->ID;
						$start_date = MP_Global_Function::get_post_info($post_id, 'ttbm_travel_start_date');
						$end_date = MP_Global_Function::get_post_info($post_id, 'ttbm_travel_end_date');
						update_post_meta($post_id, 'ttbm_travel_repeated_start_date', $start_date);
						update_post_meta($post_id, 'ttbm_travel_repeated_end_date', $end_date);
					}
					update_option('ttbm_repeated_field_update', 'completed');
				}
			}
			public function woocommerce_not_active() {
				$wc_install_url = get_admin_url() . 'plugin-install.php?s=woocommerce&tab=search&type=term';
				printf('<div class="error" style="background:red; color:#fff;"><p>%s</p></div>', __('You Must Install WooCommerce Plugin before activating Tour Booking Manager, Because It is dependent on Woocommerce Plugin. <a class="btn button" href=' . $wc_install_url . '>Click Here to Install</a>'));
			}
		}
		new TTBM_Woocommerce_Plugin();
	}
