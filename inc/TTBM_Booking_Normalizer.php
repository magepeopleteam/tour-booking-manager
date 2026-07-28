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
			private static $ticket_allocation_cache = array();
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
			//----------------------------------------------------------------------
			// Per-ticket price and extra-service allocation
			//----------------------------------------------------------------------
			/**
			 * Build one accounting-safe price breakdown for every printable ticket.
			 *
			 * Tour extra services are stored at order level, while PDFs are generated
			 * per ttbm_booking record. Split each service line evenly across the
			 * printable tickets and put any currency-rounding remainder on the first
			 * ticket. This mirrors the Bus voucher model and guarantees that the sum
			 * of all ticket totals is exactly the order total without duplicating an
			 * order-wide service on every PDF page.
			 *
			 * @param int $order_id WooCommerce or ttbm_custom_order ID.
			 * @return array<string,mixed>
			 */
			public static function ticket_price_allocations($order_id) {
				$order_id = absint($order_id);
				if (!$order_id) {
					return self::empty_ticket_allocations();
				}
				if (isset(self::$ticket_allocation_cache[$order_id])) {
					return self::$ticket_allocation_cache[$order_id];
				}

				$booking_ids = get_posts(array(
					'post_type' => 'ttbm_booking',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'orderby' => 'ID',
					'order' => 'ASC',
					'no_found_rows' => true,
					'meta_query' => array(
						array(
							'key' => 'ttbm_order_id',
							'value' => $order_id,
							'compare' => '=',
						),
					),
				));
				$booking_ids = array_values(array_unique(array_filter(array_map('absint', $booking_ids))));
				if (empty($booking_ids)) {
					self::$ticket_allocation_cache[$order_id] = self::empty_ticket_allocations($order_id);
					return self::$ticket_allocation_cache[$order_id];
				}

				// A group ticket creates child booking records whose group ID points to
				// the printable head record. Keep those children on the head page.
				$scopes = array();
				$children = array();
				foreach ($booking_ids as $booking_id) {
					$group_id = (string) get_post_meta($booking_id, 'ttbm_group_id', true);
					if ('' === $group_id || 'on' === $group_id) {
						$scopes[$booking_id] = array($booking_id);
					} else {
						$children[$booking_id] = absint($group_id);
					}
				}
				foreach ($children as $booking_id => $head_id) {
					if ($head_id && isset($scopes[$head_id])) {
						$scopes[$head_id][] = $booking_id;
					} else {
						// Preserve malformed legacy records as their own printable ticket.
						$scopes[$booking_id] = array($booking_id);
					}
				}
				ksort($scopes, SORT_NUMERIC);

				$tickets = array();
				$ticket_component_total = 0.0;
				foreach ($scopes as $booking_id => $member_ids) {
					$names = array();
					$ticket_amount = 0.0;
					foreach ($member_ids as $member_id) {
						$name = (string) get_post_meta($member_id, 'ttbm_ticket_name', true);
						if ('' !== $name) {
							$names[] = $name;
						}
						$ticket_amount += (float) get_post_meta($member_id, 'ttbm_ticket_price', true);
					}
					$names = array_values(array_unique($names));
					$qty = max(1, count($member_ids));
					$ticket_component_total += $ticket_amount;
					$tickets[] = array(
						'booking_id' => (int) $booking_id,
						'member_ids' => array_values(array_map('absint', $member_ids)),
						'name' => !empty($names) ? implode(', ', $names) : __('Tour Ticket', 'tour-booking-manager'),
						'qty' => $qty,
						'unit' => $qty > 0 ? $ticket_amount / $qty : $ticket_amount,
						'ticket_amount' => $ticket_amount,
						'services' => array(),
						'service_amount' => 0.0,
						'adjustment' => 0.0,
						'total' => $ticket_amount,
					);
				}

				$service_ids = get_posts(array(
					'post_type' => 'ttbm_service_booking',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'orderby' => 'ID',
					'order' => 'ASC',
					'no_found_rows' => true,
					'meta_query' => array(
						array(
							'key' => 'ttbm_order_id',
							'value' => $order_id,
							'compare' => '=',
						),
					),
				));
				$services = array();
				$service_component_total = 0.0;
				$ticket_count = count($tickets);
				$decimals = function_exists('wc_get_price_decimals') ? absint(wc_get_price_decimals()) : 2;
				foreach ($service_ids as $service_id) {
					$service_id = absint($service_id);
					$qty = absint(get_post_meta($service_id, 'ttbm_service_qty', true));
					if ($qty <= 0) {
						continue;
					}
					$unit = (float) get_post_meta($service_id, 'ttbm_service_price', true);
					$amount = metadata_exists('post', $service_id, 'ttbm_service_total_price')
						? (float) get_post_meta($service_id, 'ttbm_service_total_price', true)
						: $unit * $qty;
					$service = array(
						'service_id' => $service_id,
						'name' => (string) get_post_meta($service_id, 'ttbm_service_name', true) ?: __('Extra service', 'tour-booking-manager'),
						'qty' => $qty,
						'unit' => $unit,
						'amount' => $amount,
					);
					$services[] = $service;
					$service_component_total += $amount;
					$amount_shares = self::split_ticket_amount($amount, $ticket_count, $decimals);
					foreach ($tickets as $index => &$ticket) {
						$share = isset($amount_shares[$index]) ? $amount_shares[$index] : 0.0;
						$ticket['services'][] = array_merge($service, array(
							'qty_share' => $qty / $ticket_count,
							'amount_share' => $share,
						));
						$ticket['service_amount'] += $share;
						$ticket['total'] += $share;
					}
					unset($ticket);
				}

				$component_total = $ticket_component_total + $service_component_total;
				$order_total = $component_total;
				if ('ttbm_custom_order' === get_post_type($order_id) && metadata_exists('post', $order_id, '_ttbm_order_total')) {
					$order_total = (float) get_post_meta($order_id, '_ttbm_order_total', true);
				} elseif (function_exists('wc_get_order')) {
					$order = self::resolve_wc_order($order_id);
					if ($order) {
						$order_total = (float) $order->get_total();
					}
				}
				$order_total = (float) apply_filters('ttbm_ticket_allocation_order_total', $order_total, $order_id, $component_total);
				$adjustment = round($order_total - $component_total, $decimals);
				if (0.0 !== $adjustment) {
					$adjustment_shares = self::split_ticket_amount($adjustment, $ticket_count, $decimals);
					foreach ($tickets as $index => &$ticket) {
						$ticket['adjustment'] = isset($adjustment_shares[$index]) ? $adjustment_shares[$index] : 0.0;
						$ticket['total'] += $ticket['adjustment'];
					}
					unset($ticket);
				}

				$result = array(
					'order_id' => $order_id,
					'ticket_count' => $ticket_count,
					'tickets' => array_values($tickets),
					'services' => $services,
					'component_total' => $component_total,
					'adjustment' => $adjustment,
					'order_total' => $order_total,
				);
				$result = apply_filters('ttbm_ticket_price_allocations', $result, $order_id);
				self::$ticket_allocation_cache[$order_id] = $result;
				return $result;
			}

			private static function split_ticket_amount($amount, $parts, $decimals) {
				$parts = max(1, absint($parts));
				$share = round((float) $amount / $parts, $decimals);
				$shares = array_fill(0, $parts, $share);
				$shares[0] = round((float) $amount - ($share * ($parts - 1)), $decimals);
				return $shares;
			}

			private static function empty_ticket_allocations($order_id = 0) {
				return array(
					'order_id' => absint($order_id),
					'ticket_count' => 0,
					'tickets' => array(),
					'services' => array(),
					'component_total' => 0.0,
					'adjustment' => 0.0,
					'order_total' => 0.0,
				);
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
				// Do not gate this query with post_type_exists(). Older booking
				// records remain in wp_posts when the component that registered the
				// internal ttbm_booking type (for example Tour PRO) is deactivated.
				// The admin booking list must continue to discover those persisted
				// WooCommerce orders; the post_type predicate below keeps the query
				// narrowly scoped and naturally returns no rows on a fresh install.
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
					$orders_table = $wpdb->prefix . 'wc_orders';
					$operational_table = $wpdb->prefix . 'wc_order_operational_data';
					$rows = $wpdb->get_results($wpdb->prepare(
						"SELECT orders.id, orders.total_amount, operational.order_key
						 FROM {$orders_table} orders
						 LEFT JOIN {$operational_table} operational ON operational.order_id = orders.id
						 WHERE orders.id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
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
