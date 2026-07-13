<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * Shared source-agnostic view over the two places a tour booking can live:
	 * the ttbm_custom_order CPT (Pro's WooCommerce-optional checkout) and a
	 * WooCommerce order (linked via the ttbm_order_id meta on ttbm_booking
	 * records). Lives in the free plugin so both free and Pro admin screens can
	 * build one merged list without either side depending on the other's
	 * internal schema.
	 */
	if (!class_exists('TTBM_Booking_Normalizer')) {
		class TTBM_Booking_Normalizer {
			const SOURCE_CUSTOM = 'custom';
			const SOURCE_WOO = 'woo';
			private static $wc_order_cache = array();
			//----------------------------------------------------------------------
			// Status
			//----------------------------------------------------------------------
			public static function status_map() {
				return apply_filters('ttbm_booking_status_map', array(
					'pending' => array('label' => __('Pending', 'tour-booking-manager'), 'class' => 'pending'),
					'processing' => array('label' => __('Processing', 'tour-booking-manager'), 'class' => 'processing'),
					'on-hold' => array('label' => __('On hold', 'tour-booking-manager'), 'class' => 'on-hold'),
					'completed' => array('label' => __('Completed', 'tour-booking-manager'), 'class' => 'completed'),
					'cancelled' => array('label' => __('Cancelled', 'tour-booking-manager'), 'class' => 'cancelled'),
					'refunded' => array('label' => __('Refunded', 'tour-booking-manager'), 'class' => 'refunded'),
					'failed' => array('label' => __('Failed', 'tour-booking-manager'), 'class' => 'failed'),
					'partially-paid' => array('label' => __('Partially paid', 'tour-booking-manager'), 'class' => 'partially-paid'),
					'draft' => array('label' => __('Draft', 'tour-booking-manager'), 'class' => 'pending'),
					'trash' => array('label' => __('Trashed', 'tour-booking-manager'), 'class' => 'cancelled'),
				));
			}
			// Folds a raw status from either source (native post_status, or a WC
			// status with/without its "wc-" prefix) onto one shared slug set.
			public static function normalize_status($status) {
				$status = strtolower((string) $status);
				$status = preg_replace('/^wc-/', '', $status);
				if ($status === 'publish') {
					$status = 'completed';
				}
				if ($status === 'canceled') {
					$status = 'cancelled';
				}
				return $status;
			}
			public static function status_label($status) {
				$slug = self::normalize_status($status);
				$map = self::status_map();
				return isset($map[$slug]) ? $map[$slug]['label'] : ucwords(str_replace(array('-', '_'), ' ', $slug));
			}
			public static function status_class($status) {
				$slug = self::normalize_status($status);
				$map = self::status_map();
				return isset($map[$slug]) ? $map[$slug]['class'] : sanitize_html_class($slug);
			}
			// Single source of truth for "is this booking confirmed enough to
			// release the ticket" — reads the same ttbm_set_book_status option the
			// Payments tab's "Confirm Ticket Based on Payment Status" already
			// writes, so seat availability and ticket-readiness never disagree.
			public static function is_ticket_ready($status) {
				$slug = self::normalize_status($status);
				$ready = TTBM_Function::get_general_settings('ttbm_set_book_status', array('processing', 'completed'));
				$ready = is_array($ready) ? $ready : array($ready);
				$ready = array_map(array(__CLASS__, 'normalize_status'), $ready);
				return in_array($slug, $ready, true);
			}
			public static function source_label($source) {
				return $source === self::SOURCE_WOO
					? __('WooCommerce', 'tour-booking-manager')
					: __('Custom Payment', 'tour-booking-manager');
			}
			//----------------------------------------------------------------------
			// WooCommerce order resolution (request-scoped cache)
			//----------------------------------------------------------------------
			public static function resolve_wc_order($order_id) {
				$order_id = (int) $order_id;
				if (!$order_id || !function_exists('wc_get_order')) {
					return false;
				}
				if (!array_key_exists($order_id, self::$wc_order_cache)) {
					self::$wc_order_cache[$order_id] = wc_get_order($order_id);
				}
				return self::$wc_order_cache[$order_id];
			}
			public static function wc_order_edit_url($order_id) {
				$order = self::resolve_wc_order($order_id);
				return $order ? $order->get_edit_order_url() : '';
			}
			public static function format_price($amount) {
				if (function_exists('wc_price') && TTBM_Global_Function::has_woocommerce()) {
					return wp_strip_all_tags(wc_price((float) $amount));
				}
				return number_format_i18n((float) $amount, 2);
			}
			//----------------------------------------------------------------------
			// Index (cheap — no WC_Order objects, no per-row queries)
			//----------------------------------------------------------------------
			public static function query_index() {
				$rows = array_merge(self::query_custom_index(), self::query_woo_index());
				usort($rows, function ($a, $b) {
					return strcmp($b['placed_at'], $a['placed_at']);
				});
				return apply_filters('ttbm_booking_normalizer_index', $rows);
			}
			// Bookings belonging to one customer — an account (user_id > 0) OR a
			// guest identified by email + the order's own reference token
			// (verified with hash_equals, never ==). A user_id of 0 must never
			// match "any guest" — only ever add that clause for a real account id.
			public static function query_for_customer($user_id, $email = '', $guest_token = '') {
				$user_id = (int) $user_id;
				$email = sanitize_email($email);
				$rows = array_filter(self::query_index(), function ($row) use ($user_id, $email, $guest_token) {
					if ($user_id > 0 && (int) $row['user_id'] === $user_id) {
						return true;
					}
					if ($email && $guest_token && $row['customer_email'] && strtolower($row['customer_email']) === strtolower($email)) {
						return hash_equals((string) $row['order_key'], (string) $guest_token);
					}
					return false;
				});
				return array_values($rows);
			}
			private static function query_custom_index() {
				if (!post_type_exists('ttbm_custom_order')) {
					return array();
				}
				$ids = get_posts(array(
					'post_type' => 'ttbm_custom_order',
					'post_status' => array('pending', 'processing', 'on-hold', 'publish', 'cancelled', 'refunded', 'failed'),
					'posts_per_page' => -1,
					'fields' => 'ids',
					'orderby' => 'date',
					'order' => 'DESC',
				));
				$rows = array();
				foreach ($ids as $order_id) {
					$rows[] = array(
						'source' => self::SOURCE_CUSTOM,
						'id' => $order_id,
						'status' => get_post_status($order_id),
						'tour_id' => (int) get_post_meta($order_id, '_ttbm_tour_id', true),
						'tour_date' => (string) get_post_meta($order_id, '_ttbm_date', true),
						'user_id' => (int) get_post_meta($order_id, '_ttbm_customer_id', true),
						'order_key' => (string) get_post_meta($order_id, '_ttbm_order_key', true),
						'customer_name' => (string) get_post_meta($order_id, '_ttbm_customer_name', true),
						'customer_email' => (string) get_post_meta($order_id, '_ttbm_customer_email', true),
						'total' => (float) get_post_meta($order_id, '_ttbm_order_total', true),
						'gateway' => (string) get_post_meta($order_id, '_ttbm_payment_gateway', true),
						'ticket_qty' => self::sum_ticket_qty((array) get_post_meta($order_id, '_ttbm_ticket_info', true)),
						'placed_at' => get_post_field('post_date', $order_id),
					);
				}
				return $rows;
			}
			// WooCommerce never gets a dedicated "tour order" CPT here — a WC order
			// is identified by grouping ttbm_booking records (one per ticket/
			// attendee) on their shared ttbm_order_id meta, same as
			// TTBM_Function_PRO::all_order_by_month_with_mode() already does.
			// Two bounded queries regardless of catalog size: one aggregate SQL
			// query for the groups, one update_meta_cache() priming call so the
			// per-row get_post_meta() below doesn't hit the DB again.
			private static function query_woo_index() {
				global $wpdb;
				if (!post_type_exists('ttbm_booking')) {
					return array();
				}
				// TTBM_Custom_Checkout (Pro) writes the SAME ttbm_order_id meta key
				// on ttbm_booking records, but pointing at a ttbm_custom_order post
				// id rather than a real WC order id — exclude those explicitly so
				// native orders don't get double-counted as phantom WooCommerce rows.
				$groups = $wpdb->get_results(
					"SELECT pm.meta_value AS order_id, MIN(p.ID) AS rep_post_id, MIN(p.post_date) AS placed_at, COUNT(*) AS ticket_qty
					 FROM {$wpdb->postmeta} pm
					 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					 WHERE pm.meta_key = 'ttbm_order_id' AND p.post_type = 'ttbm_booking' AND pm.meta_value REGEXP '^[0-9]+$'
					 AND NOT EXISTS (
						 SELECT 1 FROM {$wpdb->posts} co WHERE co.ID = pm.meta_value AND co.post_type = 'ttbm_custom_order'
					 )
					 GROUP BY pm.meta_value"
				);
				if (!$groups) {
					return array();
				}
				update_meta_cache('post', wp_list_pluck($groups, 'rep_post_id'));
				$wc_data = self::bulk_wc_order_data(wp_list_pluck($groups, 'order_id'));
				$rows = array();
				foreach ($groups as $group) {
					$rep_id = (int) $group->rep_post_id;
					$order_id = (int) $group->order_id;
					$rows[] = array(
						'source' => self::SOURCE_WOO,
						'id' => $order_id,
						'status' => (string) get_post_meta($rep_id, 'ttbm_order_status', true),
						'tour_id' => (int) get_post_meta($rep_id, 'ttbm_id', true),
						'tour_date' => (string) get_post_meta($rep_id, 'ttbm_date', true),
						'user_id' => (int) get_post_meta($rep_id, 'ttbm_user_id', true),
						'order_key' => isset($wc_data[$order_id]) ? $wc_data[$order_id]['order_key'] : '',
						'customer_name' => (string) get_post_meta($rep_id, 'ttbm_billing_name', true),
						'customer_email' => (string) get_post_meta($rep_id, 'ttbm_billing_email', true),
						'total' => isset($wc_data[$order_id]) ? $wc_data[$order_id]['total'] : 0.0,
						'gateway' => (string) get_post_meta($rep_id, 'ttbm_payment_method', true),
						'ticket_qty' => (int) $group->ticket_qty,
						'placed_at' => $group->placed_at,
					);
				}
				return $rows;
			}
			// One bulk query for order totals + order keys across the whole index
			// (never one query per order) — branches on HPOS vs legacy post-based
			// storage. The order key is the WC-side equivalent of _ttbm_order_key:
			// a guest's capability token for the customer portal.
			private static function bulk_wc_order_data($order_ids) {
				global $wpdb;
				$order_ids = array_filter(array_map('absint', $order_ids));
				if (empty($order_ids)) {
					return array();
				}
				$placeholders = implode(',', array_fill(0, count($order_ids), '%d'));
				$data = array();
				if (
					class_exists('Automattic\WooCommerce\Utilities\OrderUtil')
					&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()
				) {
					$table = $wpdb->prefix . 'wc_orders';
					$rows = $wpdb->get_results($wpdb->prepare(
						"SELECT id, total_amount, order_key FROM {$table} WHERE id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$order_ids
					));
					foreach ($rows as $row) {
						$data[(int) $row->id] = array('total' => (float) $row->total_amount, 'order_key' => (string) $row->order_key);
					}
					return $data;
				}
				foreach ($order_ids as $id) {
					$data[$id] = array('total' => 0.0, 'order_key' => '');
				}
				$rows = $wpdb->get_results($wpdb->prepare(
					"SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_order_total', '_order_key') AND post_id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$order_ids
				));
				foreach ($rows as $row) {
					$id = (int) $row->post_id;
					if ($row->meta_key === '_order_total') {
						$data[$id]['total'] = (float) $row->meta_value;
					} else {
						$data[$id]['order_key'] = (string) $row->meta_value;
					}
				}
				return $data;
			}
			private static function sum_ticket_qty($ticket_info) {
				$qty = 0;
				foreach ($ticket_info as $ticket) {
					$qty += isset($ticket['ticket_qty']) ? (int) $ticket['ticket_qty'] : 0;
				}
				return $qty;
			}
			// Resolves real WC_Order objects only for the given (already-paged)
			// slice — never call this on the full index.
			public static function hydrate($rows) {
				foreach ($rows as &$row) {
					if ($row['source'] !== self::SOURCE_WOO) {
						continue;
					}
					$order = self::resolve_wc_order($row['id']);
					if (!$order) {
						$row['missing'] = true;
						continue;
					}
					$row['status'] = $order->get_status();
					$row['total'] = (float) $order->get_total();
					$row['customer_name'] = trim($order->get_formatted_billing_full_name());
					$row['customer_email'] = $order->get_billing_email();
					$row['gateway'] = $order->get_payment_method_title();
					$row['edit_url'] = $order->get_edit_order_url();
					$row['order_key'] = $order->get_order_key();
				}
				unset($row);
				return $rows;
			}
		}
	}
