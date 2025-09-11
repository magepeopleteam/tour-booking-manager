<?php
/**
 * Plugin Name: WpTravelly – Tour & Travel Booking Manager for WooCommerce | Tour & Hotel Booking Solution
 * Plugin URI: http://mage-people.com
 * Description: A Complete Tour and Travel Solution for WordPress by MagePeople.
 * Version: 2.1.0
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
if (!class_exists('TTBM_Woocommerce_Plugin')) {
	class TTBM_Woocommerce_Plugin {
			public function __construct() {
				$this->load_ttbm_plugin();
				add_action('init', array($this, 'load_blocks'));
			}
			private function load_ttbm_plugin() {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if (!defined('TTBM_PLUGIN_DIR')) {
					define('TTBM_PLUGIN_DIR', dirname(__FILE__));
				}
				if (!defined('TTBM_PLUGIN_URL')) {
					define('TTBM_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
				}
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Dependencies.php';
				add_action('admin_init', array($this, 'activation_redirect_setup'), 90);
			}
			public function activation_redirect_setup($plugin) {
				if (TTBM_Global_Function::check_woocommerce() == 1) {
					self::on_activation_page_create();
				}
				$ttbm_quick_setup_done = get_option('ttbm_quick_setup_done') ? get_option('ttbm_quick_setup_done') : 'no';
				// Only redirect if not already on the quick setup page and setup is not done
				if ($ttbm_quick_setup_done == 'no' &&
					(!isset($_GET['page']) || $_GET['page'] !== 'ttbm_quick_setup')) {
					// Check WooCommerce status to determine correct redirect URL
					$woo_status = TTBM_Global_Function::check_woocommerce();
					if ($woo_status == 1) {
						// WooCommerce is active - redirect to submenu under ttbm_tour post type
						wp_redirect(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_quick_setup'));
					} else {
						// WooCommerce is not active - redirect to main menu
						wp_redirect(admin_url('admin.php?page=ttbm_quick_setup'));
					}
					exit();
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
					'lotus-grid' => [
						'slug' => 'lotus-grid',
						'title' => 'Tour Lotus Grid View',
						'content' => "[travel-list style='lotus' column=4 show='12' pagination='yes']",
						'option_key' => 'ttbm_page_lotus_grid_created',
					],
					'orchid-grid' => [
						'slug' => 'orchid-grid',
						'title' => 'Tour Orchid Grid View',
						'content' => "[travel-list style='orchid' column=4 pagination='yes' show=12]",
						'option_key' => 'ttbm_page_orchid_grid_created',
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
