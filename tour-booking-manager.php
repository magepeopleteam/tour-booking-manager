<?php
	/**
	 * Plugin Name: Travel Booking Plugin | Tour & Hotel Booking Solution For WooCommerce – wptravelly
	 * Plugin URI: http://mage-people.com
	 * Description: A Complete Tour and Travel Solution for WordPress by MagePeople.
	 * Version: 1.9.9
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
				add_action('init', array($this, 'load_blocks'));
				add_action('wp_ajax_ttbm_submit_cancel_request', function() {
					if (!is_user_logged_in()) {
						wp_send_json_error(['message' => __('You must be logged in.', 'tour-booking-manager')]);
					}
					$user_id = get_current_user_id();
					$order_id = intval($_POST['order_id'] ?? 0);
					$tour_id = intval($_POST['tour_id'] ?? 0);
					$reason = sanitize_textarea_field($_POST['reason'] ?? '');
					if (!$order_id || !$tour_id || empty($reason)) {
						wp_send_json_error(['message' => __('Missing data.', 'tour-booking-manager')]);
					}
					// Prevent duplicate requests
					$existing = get_posts([
						'post_type' => 'ttbm_cancel_request',
						'post_status' => ['publish', 'pending', 'draft'],
						'meta_query' => [
							['key' => 'order_id', 'value' => $order_id],
							['key' => 'tour_id', 'value' => $tour_id],
							['key' => 'user_id', 'value' => $user_id],
						],
						'numberposts' => 1
					]);
					if ($existing) {
						wp_send_json_error(['message' => __('A cancellation request already exists for this order.', 'tour-booking-manager')]);
					}
					$post_id = wp_insert_post([
						'post_type' => 'ttbm_cancel_request',
						'post_status' => 'publish',
						'post_title' => 'Cancel Request #' . $order_id,
						'post_content' => $reason,
						'post_author' => $user_id,
					]);
					if ($post_id) {
						update_post_meta($post_id, 'order_id', $order_id);
						update_post_meta($post_id, 'tour_id', $tour_id);
						update_post_meta($post_id, 'user_id', $user_id);
						update_post_meta($post_id, 'reason', $reason);
						update_post_meta($post_id, 'cancel_status', 'pending');
						ttbm_send_cancel_email('admin_new', [
							'order_id' => $order_id,
							'tour_id' => $tour_id,
							'user_id' => $user_id,
							'reason' => $reason,
						]);
						wp_send_json_success(['message' => __('Cancellation request submitted.', 'tour-booking-manager')]);
					} else {
						wp_send_json_error(['message' => __('Failed to submit request.', 'tour-booking-manager')]);
					}
				});
				add_action('wp_ajax_nopriv_ttbm_submit_cancel_request', function() {
					wp_send_json_error(['message' => __('You must be logged in.', 'tour-booking-manager')]);
				});
			}
			private function load_ttbm_plugin() {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if (!defined('TTBM_PLUGIN_DIR')) {
					define('TTBM_PLUGIN_DIR', dirname(__FILE__));
				}
				if (!defined('TTBM_PLUGIN_URL')) {
					define('TTBM_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
				}
				require_once TTBM_PLUGIN_DIR . '/mp_global/TTBM_Global_File_Load.php';
				$this->load_global_file();
				if (TTBM_Global_Function::check_woocommerce() == 1) {
					// add_action('activated_plugin', array($this, 'activation_redirect'), 90, 1);
					require_once TTBM_PLUGIN_DIR . '/lib/classes/class-ttbm.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Dependencies.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Cancellation.php';
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
			
			public function activation_redirect($plugin) {
				if (TTBM_Global_Function::check_woocommerce() == 1) {
					self::on_activation_page_create();
				}
				$ttbm_quick_setup_done = get_option('ttbm_quick_setup_done');
				if ($ttbm_quick_setup_done != 'yes') {
					exit(wp_redirect(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_quick_setup')));
				}
			}
			public function activation_redirect_setup($plugin) {
				if (TTBM_Global_Function::check_woocommerce() == 1) {
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
                    $page_id = wp_insert_post($page);

                    if (is_wp_error($page_id)) {
                        printf('<div class="error" style="background:red; color:#fff;"><p>%s</p></div>', $page_id->get_error_message());
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
			public function woocommerce_not_active() {
				$wc_install_url = get_admin_url() . 'plugin-install.php?s=woocommerce&tab=search&type=term';
				printf('<div class="error" style="background:red; color:#fff;"><p>%s</p></div>', __('You Must Install WooCommerce Plugin before activating Tour Booking Manager, Because It is dependent on Woocommerce Plugin. <a class="btn button" href=' . $wc_install_url . '>Click Here to Install</a>'));
			}
		}
		new TTBM_Woocommerce_Plugin();
	}



	