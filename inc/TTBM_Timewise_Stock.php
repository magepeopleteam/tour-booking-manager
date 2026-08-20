<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Timewise_Stock')) {
		/**
		 * Per-time-slot inventory for repeated tours.
		 *
		 * A repeated tour normally sells one pool of seats per ticket type for the
		 * whole date. When "Time-wise Stock" is on and a slot row carries a stock
		 * value, that slot becomes its own departure with its own shared pool: the
		 * 09:30 boat can be sold out while the 11:30 one still has seats.
		 *
		 * Nothing here writes its own numbers into templates. Every consumer already
		 * resolves availability through the ttbm_ticket_capacity / ttbm_sold_qty /
		 * ttbm_ticket_availability_info / ttbm_total_available_qty seams, so this
		 * class only redirects those seams at the slot pool -- which is also why the
		 * ticket table, the time slot chips, the quantity steppers and the cart
		 * validator cannot disagree with each other.
		 *
		 * Every hook is a no-op unless the tour has the feature enabled AND the
		 * requested date carries a time whose slot has a stock value, so a tour that
		 * never fills the stock column behaves exactly as before.
		 */
		class TTBM_Timewise_Stock {
			public function __construct() {
				/*
				 * Priority 20 -- after Pro's tour-wide "Shared Capacity" filters at
				 * 10. Both describe a shared pool; the per-slot one is the more
				 * specific statement, so when an admin configures both, the slot
				 * number is the one that must win.
				 */
				add_filter('ttbm_ticket_capacity', array($this, 'ticket_capacity'), 20, 4);
				add_filter('ttbm_sold_qty', array($this, 'sold_qty'), 20, 4);
				add_filter('ttbm_ticket_availability_info', array($this, 'availability_info'), 20, 4);
				add_filter('ttbm_total_available_qty', array($this, 'total_available_qty'), 20, 4);
				add_filter('ttbm_cart_ticket_info_data_prepare', array($this, 'normalize_cart_ticket_info'), 20, 2);
				add_action('ttbm_validate_cart_item', array($this, 'validate_cart_item'), 20, 2);
			}
			/**
			 * Resolve a tour + date argument to the slot pool it belongs to.
			 *
			 * Returns null -- meaning "leave the value alone" -- whenever the feature
			 * is off, the date has no time component (a whole-day figure, e.g. a list
			 * card), or the matched slot has no stock configured.
			 *
			 * @return array|null {enabled, capacity, sold, available, datetime}
			 */
			private function slot_context($tour_id, $tour_date) {
				$tour_id = (int) TTBM_Function::post_id_multi_language($tour_id);
				if (!$tour_id || !TTBM_Function::is_timewise_stock_enabled($tour_id)) {
					return null;
				}
				if (!is_scalar($tour_date)) {
					return null;
				}
				$tour_date = trim((string) $tour_date);
				/*
				 * Match on the raw string rather than TTBM_Global_Function::check_time_exit_date():
				 * that helper reports "no time" for midnight, which would silently drop
				 * a legitimate 00:00 departure back onto the whole-day pool.
				 */
				if ($tour_date === '' || !preg_match('/\d{1,2}:\d{2}/', $tour_date)) {
					return null;
				}
				$timestamp = strtotime($tour_date);
				if ($timestamp === false) {
					return null;
				}
				$context = TTBM_Function::get_timewise_slot_availability(
					$tour_id,
					gmdate('Y-m-d', $timestamp),
					gmdate('H:i', $timestamp)
				);
				return $context['enabled'] ? $context : null;
			}
			/**
			 * Every ticket type on this departure draws from the slot's pool, so the
			 * slot stock replaces each type's own capacity.
			 */
			public function ticket_capacity($capacity, $tour_id, $tour_date = '', $ticket_name = '') {
				$context = $this->slot_context($tour_id, $tour_date);
				return $context ? $context['capacity'] : $capacity;
			}
			/**
			 * ...and what has been sold against that pool is the whole departure, not
			 * just the rows matching one ticket type.
			 */
			public function sold_qty($sold, $tour_id, $tour_date = '', $ticket_name = '') {
				$context = $this->slot_context($tour_id, $tour_date);
				return $context ? $context['sold'] : $sold;
			}
			/**
			 * Restate every ticket type's availability against the shared slot pool.
			 *
			 * Flagging `shared_capacity_enabled` is what makes the existing frontend
			 * stepper logic (ttbmConstrainSharedCapacity in ttbm_price_calculation.js)
			 * treat the rows as competing for one pool, so picking 3 adults leaves
			 * only the remainder selectable for children. Reserved quantity is zeroed
			 * because it belongs to the per-ticket-type inventory this pool replaces;
			 * holding seats back per type and then again per slot would double-count.
			 */
			public function availability_info($availability_info, $tour_id, $tour_date = '', $ticket_type_name = '') {
				$context = $this->slot_context($tour_id, $tour_date);
				if (!$context || !is_array($availability_info)) {
					return $availability_info;
				}
				$capacity = $context['capacity'];
				$sold = $context['sold'];
				$available = $context['available'];
				$percentage_sold = $capacity > 0 ? round(($sold / $capacity) * 100, 2) : 0;
				foreach ($availability_info as $type_name => $info) {
					$availability_info[$type_name]['total_capacity'] = $capacity;
					$availability_info[$type_name]['reserved_qty'] = 0;
					$availability_info[$type_name]['sold_qty'] = $sold;
					$availability_info[$type_name]['available_qty'] = $available;
					$availability_info[$type_name]['percentage_sold'] = $percentage_sold;
					$availability_info[$type_name]['stock_status'] = TTBM_Function::get_stock_status($available, $capacity);
					$availability_info[$type_name]['is_sold_out'] = $available <= 0;
					$availability_info[$type_name]['shared_capacity_enabled'] = true;
					$availability_info[$type_name]['timewise_stock_enabled'] = true;
				}
				return $availability_info;
			}
			/**
			 * The whole-tour total is normally the sum of each ticket type's
			 * availability; with one shared pool that sum would multiply the slot's
			 * seats by the number of ticket types, so clamp it to the pool itself.
			 */
			public function total_available_qty($total_available, $tour_id, $tour_date = '', $ticket_lists = array()) {
				$context = $this->slot_context($tour_id, $tour_date);
				return $context ? $context['available'] : $total_available;
			}
			/**
			 * Seats a cart line is asking for, in real seats -- group tickets count as
			 * the number of people they seat, not as one line item.
			 */
			private function requested_seats(array $ticket_info, $tour_id): int {
				$requested = 0;
				foreach ($ticket_info as $ticket) {
					$ticket_qty = isset($ticket['ticket_qty']) ? (int) $ticket['ticket_qty'] : 0;
					if ($ticket_qty < 1) {
						continue;
					}
					$ticket_name = isset($ticket['ticket_name']) ? $ticket['ticket_name'] : '';
					$requested += max(0, (int) apply_filters('ttbm_group_ticket_qty_actual', $ticket_qty, $tour_id, $ticket_name));
				}
				return $requested;
			}
			/**
			 * Read the departure date off a prepared cart-ticket payload.
			 *
			 * cart_ticket_info() stamps ttbm_date on every row from the submitted
			 * ttbm_start_date; fall back to the raw POST field for payloads built by
			 * an addon that did not.
			 */
			private function ticket_info_date(array $ticket_info): string {
				foreach ($ticket_info as $ticket) {
					if (!empty($ticket['ttbm_date'])) {
						return sanitize_text_field($ticket['ttbm_date']);
					}
				}
				if (isset($_POST['ttbm_form_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_form_nonce'])), 'ttbm_form_nonce') && isset($_POST['ttbm_start_date'])) {
					return sanitize_text_field(wp_unslash($_POST['ttbm_start_date']));
				}
				return '';
			}
			/**
			 * Trim an add-to-cart request down to what the slot can still seat.
			 *
			 * The stepper already caps quantities in the browser, but a stale page
			 * (someone else booked the last seats while this form sat open) or a
			 * hand-crafted POST can still ask for more, and this is the last point
			 * where the request can be corrected instead of rejected.
			 */
			public function normalize_cart_ticket_info($ticket_info, $tour_id) {
				if (!is_array($ticket_info) || empty($ticket_info)) {
					return $ticket_info;
				}
				$context = $this->slot_context($tour_id, $this->ticket_info_date($ticket_info));
				if (!$context) {
					return $ticket_info;
				}
				$remaining = $context['available'];
				$normalized = array();
				foreach ($ticket_info as $ticket) {
					$ticket_qty = isset($ticket['ticket_qty']) ? (int) $ticket['ticket_qty'] : 0;
					if ($ticket_qty < 1 || $remaining < 1) {
						continue;
					}
					$ticket_name = isset($ticket['ticket_name']) ? $ticket['ticket_name'] : '';
					/* Seats one unit of this ticket occupies (1 for a normal ticket, N for a group ticket). */
					$unit_seats = max(1, (int) apply_filters('ttbm_group_ticket_qty_actual', 1, $tour_id, $ticket_name));
					$allowed_qty = min($ticket_qty, (int) floor($remaining / $unit_seats));
					if ($allowed_qty < 1) {
						continue;
					}
					$ticket['ticket_qty'] = $allowed_qty;
					$normalized[] = $ticket;
					$remaining -= $allowed_qty * $unit_seats;
				}
				return $normalized;
			}
			/**
			 * Final guard at checkout: a cart item can sit in the session long after
			 * the slot sold out, so re-check it against live availability instead of
			 * trusting what was available when it was added.
			 */
			public function validate_cart_item($values, $tour_id) {
				$ticket_info = isset($values['ttbm_ticket_info']) && is_array($values['ttbm_ticket_info']) ? $values['ttbm_ticket_info'] : array();
				if (empty($ticket_info)) {
					return;
				}
				$tour_date = isset($values['ttbm_date']) ? sanitize_text_field($values['ttbm_date']) : '';
				$context = $this->slot_context($tour_id, $tour_date);
				if (!$context) {
					return;
				}
				$requested = $this->requested_seats($ticket_info, $tour_id);
				if ($requested <= $context['available'] || !function_exists('wc_add_notice')) {
					return;
				}
				$slot_label = TTBM_Global_Function::date_format($context['datetime'], 'full');
				wc_add_notice(
					sprintf(
						/* translators: 1: departure date and time, 2: seats still available, 3: seats requested */
						esc_html__('Only %2$d seat(s) are left for the %1$s departure, but %3$d were requested. Please update the quantity.', 'tour-booking-manager'),
						esc_html($slot_label),
						(int) $context['available'],
						(int) $requested
					),
					'error'
				);
			}
		}
		new TTBM_Timewise_Stock();
	}
