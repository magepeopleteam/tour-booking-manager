<?php
	/**
	 * Plugin Name: Travel Booking Plugin | Tour & Hotel Booking Solution For WooCommerce – wptravelly
	 * Plugin URI: http://mage-people.com
	 * Description: A Complete Tour and Travel Solution for WordPress by MagePeople.
	 * Version: 1.8.4
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
					// define('TTBM_PLUGIN_DATA', get_plugin_data(__FILE__));
				}
				require_once TTBM_PLUGIN_DIR . '/mp_global/MP_Global_File_Load.php';
				$this->load_global_file();
				if (MP_Global_Function::check_woocommerce() == 1) {
					// add_action('activated_plugin', array($this, 'activation_redirect'), 90, 1);
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
                $page_exists = MP_Global_Function::get_page_by_slug($page_data['slug']); // Check by slug
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
                    $start_date = MP_Global_Function::get_post_info($post_id, 'ttbm_travel_start_date');
                    $end_date = MP_Global_Function::get_post_info($post_id, 'ttbm_travel_end_date');
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
