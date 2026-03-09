<?php
	if (!defined('ABSPATH')) {
		die;
	}
	if (!class_exists('TTBM_Schedule_Planner')) {
		class TTBM_Schedule_Planner {
			public function __construct() {
				add_filter('ttbm_get_time', [$this, 'filter_time'], 20, 4);
				add_filter('ttbm_get_date', [$this, 'filter_date'], 20, 3);
				add_action('ttbm_time_select', [$this, 'render_time_select'], 10, 2);
				add_action('wp_ajax_get_ttbm_time_slots', [$this, 'get_ttbm_time_slots']);
				add_action('wp_ajax_nopriv_get_ttbm_time_slots', [$this, 'get_ttbm_time_slots']);
			}
			public static function extract_time_value($slot): string {
				if (is_array($slot)) {
					if (!empty($slot['time'])) {
						return sanitize_text_field($slot['time']);
					}
					if (!empty($slot['mep_ticket_time'])) {
						return sanitize_text_field($slot['mep_ticket_time']);
					}
				}
				return is_string($slot) ? sanitize_text_field($slot) : '';
			}
			public static function extract_time_label($slot): string {
				if (is_array($slot)) {
					if (!empty($slot['label'])) {
						return sanitize_text_field($slot['label']);
					}
					if (!empty($slot['mep_ticket_time_name'])) {
						return sanitize_text_field($slot['mep_ticket_time_name']);
					}
				}
				return self::extract_time_value($slot);
			}
			public static function get_rules($tour_id): array {
				$rules = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_schedule_planner_rules', []);
				return is_array($rules) ? array_values($rules) : [];
			}
			public function filter_time($time, $tour_id, $date, $expire) {
				$date_only = $this->normalize_date($date);
				if (!$date_only) {
					return $time;
				}
				$travel_type = TTBM_Function::get_travel_type($tour_id);
				$time_slots_enabled = $this->time_slots_enabled($tour_id);
				$slots = $this->build_base_slots($tour_id, $date_only, $time, $travel_type);
				$result = $this->apply_rules($slots, self::get_rules($tour_id), $date_only);
				if ($result['cancelled']) {
					return $travel_type === 'particular' ? [] : ($time_slots_enabled ? [] : '');
				}
				$formatted = $this->format_slots_for_output($result['slots'], $travel_type, $time_slots_enabled, $time);
				if ($expire && is_array($formatted) && empty($formatted) && !$time_slots_enabled) {
					return $time;
				}
				return $formatted;
			}
			public function filter_date($dates, $tour_id, $expire) {
				if (empty($dates)) {
					return $dates;
				}
				$travel_type = TTBM_Function::get_travel_type($tour_id);
				$time_slots_enabled = $this->time_slots_enabled($tour_id);
				if ($travel_type === 'fixed') {
					if (!is_array($dates) || empty($dates['date'])) {
						return $dates;
					}
					$is_available = $this->is_date_available($tour_id, $dates['date'], $time_slots_enabled);
					return $is_available ? $dates : [];
				}
				$filtered = [];
				foreach ((array)$dates as $date) {
					$date_only = $this->normalize_date($date);
					if (!$date_only) {
						continue;
					}
					if ($this->is_date_available($tour_id, $date_only, $time_slots_enabled)) {
						$filtered[] = $date_only;
					}
				}
				return array_values(array_unique($filtered));
			}
			public function render_time_select($tour_id, $date) {
				$date_only = $this->normalize_date($date);
				$slots = $this->get_renderable_slots($tour_id, $date_only);
				if (empty($slots)) {
					echo '<div class="ttbm-schedule-time-empty">' . esc_html__('No time slots available for the selected date.', 'tour-booking-manager') . '</div>';
					return;
				}
				$selected_value = $date_only . ' ' . $slots[0]['time'];
				echo '<div class="ttbm_schedule_time_slots">';
				echo '<label class="ttbm_time_dropdown_wrap">';
				echo '<select name="ttbm_select_time" class="formControl">';
				foreach ($slots as $slot) {
					$slot_datetime = gmdate('Y-m-d H:i', strtotime($date_only . ' ' . $slot['time']));
					echo '<option value="' . esc_attr($slot_datetime) . '"' . selected($slot_datetime, gmdate('Y-m-d H:i', strtotime($selected_value)), false) . '>' . esc_html($slot['label']) . '</option>';
				}
				echo '</select>';
				echo '</label>';
				echo '</div>';
			}
			public function get_ttbm_time_slots() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$tour_id = isset($_REQUEST['tour_id']) ? absint($_REQUEST['tour_id']) : 0;
				$date = isset($_REQUEST['tour_date']) ? sanitize_text_field(wp_unslash($_REQUEST['tour_date'])) : '';
				$date = $this->normalize_date($date);
				$this->render_time_select($tour_id, $date);
				die();
			}
			private function is_date_available($tour_id, $date, $time_slots_enabled): bool {
				$travel_type = TTBM_Function::get_travel_type($tour_id);
				$current_time = TTBM_Function::get_time($tour_id, $date, 'yes');
				if (!$time_slots_enabled && $travel_type !== 'particular') {
					return !empty($current_time) || !empty($this->get_fallback_time($tour_id, $travel_type));
				}
				$slots = $this->get_renderable_slots($tour_id, $date);
				if (!empty($slots)) {
					return true;
				}
				// Preserve repeated-date availability when no slot source is configured for this weekday.
				if ($travel_type === 'repeated' && !$this->date_uses_slot_rules($tour_id, $date)) {
					return true;
				}
				return false;
			}
			private function get_renderable_slots($tour_id, $date): array {
				$raw = TTBM_Function::get_time($tour_id, $date, 'yes');
				return $this->normalize_slots($raw);
			}
			private function time_slots_enabled($tour_id): bool {
				return TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no') !== 'no';
			}
			private function build_base_slots($tour_id, $date, $time, $travel_type): array {
				if ($travel_type === 'particular') {
					$slots = $this->normalize_slots($time);
					if (!empty($slots)) {
						return $slots;
					}
				}
				$special_date_slots = $this->normalize_slots($this->get_special_date_slots($tour_id, $date));
				if (!empty($special_date_slots)) {
					return $special_date_slots;
				}
				$weekday_slots = $this->normalize_slots($this->get_weekday_slots($tour_id, $date));
				if (!empty($weekday_slots)) {
					return $weekday_slots;
				}
				$global_slots = $this->normalize_slots(TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_times_global', []));
				if (!empty($global_slots)) {
					return $global_slots;
				}
				$slots = $this->normalize_slots($time);
				if (!empty($slots)) {
					return $slots;
				}
				$fallback = $this->get_fallback_time($tour_id, $travel_type);
				return $fallback ? [['label' => $fallback, 'time' => $fallback]] : [];
			}
			private function date_uses_slot_rules($tour_id, $date): bool {
				if (!empty($this->get_special_date_slots($tour_id, $date))) {
					return true;
				}
				if (!empty($this->get_weekday_slots($tour_id, $date))) {
					return true;
				}
				if (!empty(TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_times_global', []))) {
					return true;
				}
				foreach (self::get_rules($tour_id) as $rule) {
					if ($this->rule_matches($rule, $date)) {
						return true;
					}
				}
				return false;
			}
			private function get_special_date_slots($tour_id, $date): array {
				if (class_exists('TTBM_Function_PRO') && method_exists('TTBM_Function_PRO', 'get_sd_time_slot')) {
					$special_slots = TTBM_Function_PRO::get_sd_time_slot($tour_id, $date);
					return is_array($special_slots) ? $special_slots : [];
				}
				return [];
			}
			private function get_weekday_slots($tour_id, $date) {
				$days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
				$index = (int)gmdate('w', strtotime($date));
				return TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_times_' . $days[$index], []);
			}
			private function get_fallback_time($tour_id, $travel_type): string {
				if ($travel_type === 'repeated') {
					$repeated_start_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_time');
					if ($repeated_start_time) {
						return $repeated_start_time;
					}
				}
				return TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date_time');
			}
			private function apply_rules(array $slots, array $rules, string $date): array {
				$lookup = [];
				foreach ($slots as $slot) {
					$lookup[$slot['time']] = $slot;
				}
				foreach ($rules as $rule) {
					if (!$this->rule_matches($rule, $date)) {
						continue;
					}
					if (($rule['type'] ?? '') === 'single' && !empty($rule['full_cancel'])) {
						return ['cancelled' => true, 'slots' => []];
					}
					if (($rule['type'] ?? '') === 'bulk' && ($rule['bulk_mode'] ?? '') === 'cancel') {
						return ['cancelled' => true, 'slots' => []];
					}
					if (($rule['type'] ?? '') === 'single') {
						foreach ((array)($rule['removed'] ?? []) as $time) {
							unset($lookup[$time]);
						}
						foreach ((array)($rule['added'] ?? []) as $time) {
							$lookup[$time] = ['label' => $time, 'time' => $time];
						}
						continue;
					}
					if (($rule['bulk_mode'] ?? '') === 'remove') {
						foreach ((array)($rule['selected_times'] ?? []) as $time) {
							unset($lookup[$time]);
						}
						continue;
					}
					if (($rule['bulk_mode'] ?? '') === 'add') {
						foreach (array_merge((array)($rule['selected_times'] ?? []), (array)($rule['added_times'] ?? [])) as $time) {
							$lookup[$time] = ['label' => $time, 'time' => $time];
						}
					}
				}
				ksort($lookup);
				return ['cancelled' => false, 'slots' => array_values($lookup)];
			}
			private function rule_matches($rule, $date): bool {
				if (!is_array($rule) || empty($rule['type'])) {
					return false;
				}
				if ($rule['type'] === 'single') {
					return !empty($rule['date']) && $rule['date'] === $date;
				}
				$selected_dates = [];
				if (!empty($rule['dates']) && is_array($rule['dates'])) {
					foreach ($rule['dates'] as $selected_date) {
						$normalized_date = $this->normalize_date($selected_date);
						if ($normalized_date) {
							$selected_dates[] = $normalized_date;
						}
					}
					$selected_dates = array_values(array_unique($selected_dates));
				}
				if (!empty($selected_dates)) {
					return in_array($date, $selected_dates, true);
				}
				if ($rule['type'] !== 'bulk' || empty($rule['start']) || empty($rule['end'])) {
					return false;
				}
				if ($date < $rule['start'] || $date > $rule['end']) {
					return false;
				}
				$days = isset($rule['days']) && is_array($rule['days']) ? $rule['days'] : [];
				if (empty($days)) {
					return true;
				}
				return in_array((int)gmdate('w', strtotime($date)), array_map('intval', $days), true);
			}
			private function normalize_slots($slots): array {
				$normalized = [];
				if (empty($slots)) {
					return $normalized;
				}
				if (!is_array($slots)) {
					$time = self::extract_time_value($slots);
					if ($time) {
						$normalized[] = ['label' => $time, 'time' => $time];
					}
					return $normalized;
				}
				foreach ($slots as $slot) {
					$time = self::extract_time_value($slot);
					if (!$time) {
						continue;
					}
					$normalized[$time] = [
						'label' => self::extract_time_label($slot) ?: $time,
						'time' => $time,
					];
				}
				ksort($normalized);
				return array_values($normalized);
			}
			private function format_slots_for_output(array $slots, string $travel_type, bool $time_slots_enabled, $original) {
				if ($travel_type === 'particular') {
					return array_map(function ($slot) {
						return $slot['time'];
					}, $slots);
				}
				if (!$time_slots_enabled && count($slots) <= 1) {
					if (count($slots) === 1) {
						return $slots[0]['time'];
					}
					return is_array($original) ? [] : '';
				}
				return $slots;
			}
			private function normalize_date($date): string {
				if (!$date) {
					return '';
				}
				$timestamp = strtotime(str_replace('/', '-', (string)$date));
				return $timestamp ? gmdate('Y-m-d', $timestamp) : '';
			}
		}
		new TTBM_Schedule_Planner();
	}
