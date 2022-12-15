<?php
	/**
	 * Plugin Name: Tour Booking Manager For Woocommerce
	 * Plugin URI: http://mage-people.com
	 * Description: A Complete Tour & Travel Solution for WordPress by MagePeople.
	 * Version: 1.4.3
	 * Author: MagePeople Team
	 * Author URI: http://www.mage-people.com/
	 * Text Domain: tour-booking-manager
	 * Domain Path: /languages/
	 * WC requires at least: 3.0.9
	 * WC tested up to: 5.0
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Woocommerce_Plugin' ) ) {
		class TTBM_Woocommerce_Plugin {
			public function __construct() {
				$this->load_ttbm_plugin();
			}
			private function load_ttbm_plugin() {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				if ( ! defined( 'TTBM_PLUGIN_DIR' ) ) {
					define( 'TTBM_PLUGIN_DIR', dirname( __FILE__ ) );
				}
				if ( ! defined( 'TTBM_PLUGIN_URL' ) ) {
					define( 'TTBM_PLUGIN_URL', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );
				}
				if ( self::check_woocommerce()==1 ) {
					add_action( 'activated_plugin', array( $this, 'activation_redirect' ), 90, 1 );
					register_activation_hook( __FILE__, array( $this, 'on_activation_page_create' ) );
					$this->appsero_init_tracker_ttbm();
					require_once TTBM_PLUGIN_DIR . '/lib/classes/class-ttbm.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Dependencies.php';
				} else {
					require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Quick_Setup.php';
					add_action( 'activated_plugin', array( $this, 'activation_redirect_setup' ), 90, 1 );
				}
			}
			public function appsero_init_tracker_ttbm() {
				if ( ! class_exists( 'Appsero\Client' ) ) {
					require_once __DIR__ . '/lib/appsero/src/Client.php';
				}
				$client = new Appsero\Client( '5e44d3f4-ddea-4784-8c15-4502ad6e7426', 'Tour Booking Manager For Woocommerce', __FILE__ );
				$client->insights()->init();
			}
			public function activation_redirect( $plugin ) {
				if ( $plugin == plugin_basename( __FILE__ ) ) {
					exit( wp_redirect( admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_quick_setup' ) ) );
				}
			}
			public function activation_redirect_setup( $plugin ) {
				if ( $plugin == plugin_basename( __FILE__ ) ) {
					exit( wp_redirect( admin_url( 'admin.php?post_type=ttbm_tour&page=ttbm_quick_setup' ) ) );
				}
			}
			public function on_activation_page_create() {
				if ( ! $this->get_page_by_slug( 'find' ) ) {
					$ttbm_search_page = array(
						'post_type'    => 'page',
						'post_name'    => 'find',
						'post_title'   => 'Tour Search Result',
						'post_content' => '[ttbm-search-result]',
						'post_status'  => 'publish',
					);
					wp_insert_post( $ttbm_search_page );
				}
				if ( get_option( 'ttbm_repeated_field_update' ) != 'completed' ) {
					$args = array(
						'post_type'      => TTBM_Function::get_cpt_name(),
						'posts_per_page' => - 1
					);
					$qr   = new WP_Query( $args );
					foreach ( $qr->posts as $result ) {
						$post_id    = $result->ID;
						$start_date = TTBM_Function::get_post_info( $post_id, 'ttbm_travel_start_date' );
						$end_date   = TTBM_Function::get_post_info( $post_id, 'ttbm_travel_end_date' );
						update_post_meta( $post_id, 'ttbm_travel_repeated_start_date', $start_date );
						update_post_meta( $post_id, 'ttbm_travel_repeated_end_date', $end_date );
					}
					update_option( 'ttbm_repeated_field_update', 'completed' );
				}
			}
			public function get_page_by_slug( $slug ) {
				if ( $pages = get_pages() ) {
					foreach ( $pages as $page ) {
						if ( $slug === $page->post_name ) {
							return $page;
						}
					}
				}
				return false;
			}
			public static function check_woocommerce(): int {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
				if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
					return 1;
				} elseif ( is_dir( $plugin_dir ) ) {
					return 2;
				} else {
					return 0;
				}
			}

		}
		new TTBM_Woocommerce_Plugin();
	}