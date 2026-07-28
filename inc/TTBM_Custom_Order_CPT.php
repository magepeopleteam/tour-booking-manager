<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * The `ttbm_custom_order` post type stores bookings paid through the custom
	 * (non-WooCommerce) payment gateways — PayPal, Stripe and Offline. Order
	 * state is carried on post_status, mirroring WooCommerce order statuses;
	 * `publish` doubles as "Completed" and `pending` is the core pending status.
	 */
	if (!class_exists('TTBM_Custom_Order_CPT')) {
		class TTBM_Custom_Order_CPT {
			public static function init() {
				add_action('init', array(__CLASS__, 'register_cpt'));
				add_action('init', array(__CLASS__, 'register_statuses'));
			}
			/**
			 * Register the WooCommerce-equivalent order statuses so custom orders
			 * can carry them, show correct labels, and stay included in
			 * post_status => 'any' queries. `pending` and `publish` are core WP
			 * statuses already, so they are not re-registered.
			 */
			public static function register_statuses() {
				$statuses = array(
					'processing' => _x('Processing', 'Order status', 'tour-booking-manager'),
					'on-hold' => _x('On hold', 'Order status', 'tour-booking-manager'),
					'cancelled' => _x('Cancelled', 'Order status', 'tour-booking-manager'),
					'refunded' => _x('Refunded', 'Order status', 'tour-booking-manager'),
					'failed' => _x('Failed', 'Order status', 'tour-booking-manager'),
				);
				foreach ($statuses as $status => $label) {
					register_post_status($status, array(
						'label' => $label,
						'public' => false,
						'internal' => false,
						'exclude_from_search' => false,
						'show_in_admin_all_list' => true,
						'show_in_admin_status_list' => true,
						/* translators: %s: number of orders with this status */
						'label_count' => _n_noop($label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'tour-booking-manager'),
					));
				}
			}
			public static function register_cpt() {
				$labels = array(
					'name' => _x('Tour Booking', 'Post Type General Name', 'tour-booking-manager'),
					'singular_name' => _x('Tour Booking', 'Post Type Singular Name', 'tour-booking-manager'),
					'menu_name' => esc_html__('Tour Booking', 'tour-booking-manager'),
					'all_items' => esc_html__('All Bookings', 'tour-booking-manager'),
					'view_item' => esc_html__('View Booking', 'tour-booking-manager'),
					'search_items' => esc_html__('Search Booking', 'tour-booking-manager'),
					'not_found' => esc_html__('Not found', 'tour-booking-manager'),
					'not_found_in_trash' => esc_html__('Not found in Trash', 'tour-booking-manager'),
				);
				$args = array(
					'label' => esc_html__('Tour Booking', 'tour-booking-manager'),
					'description' => esc_html__('Custom tour booking orders', 'tour-booking-manager'),
					'labels' => $labels,
					'supports' => array('title', 'custom-fields'),
					'hierarchical' => false,
					'public' => false,
					'show_ui' => true,
					'show_in_menu' => false, // Listed on the custom Tour Booking admin page instead.
					'show_in_admin_bar' => false,
					'show_in_nav_menus' => false,
					'can_export' => true,
					'has_archive' => false,
					'exclude_from_search' => true,
					'publicly_queryable' => false,
					'capability_type' => 'post',
					'show_in_rest' => false,
				);
				register_post_type('ttbm_custom_order', $args);
			}
			/**
			 * Human label for an order's post_status ('publish' reads as Completed).
			 */
			public static function status_label($status) {
				$labels = array(
					'pending' => esc_html__('Pending', 'tour-booking-manager'),
					'processing' => esc_html__('Processing', 'tour-booking-manager'),
					'on-hold' => esc_html__('On hold', 'tour-booking-manager'),
					'publish' => esc_html__('Completed', 'tour-booking-manager'),
					'cancelled' => esc_html__('Cancelled', 'tour-booking-manager'),
					'refunded' => esc_html__('Refunded', 'tour-booking-manager'),
					'failed' => esc_html__('Failed', 'tour-booking-manager'),
					'trash' => esc_html__('Trash', 'tour-booking-manager'),
				);
				return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
			}
			/**
			 * Map an order post_status to the WooCommerce-style status stored in
			 * the ttbm_booking records' ttbm_order_status meta (what seat counting
			 * in TTBM_Query compares against the "Seat Booked Status" setting).
			 */
			public static function booking_status($post_status) {
				return $post_status === 'publish' ? 'completed' : $post_status;
			}
		}
	}
