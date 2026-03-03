<?php
	if (!defined('ABSPATH')) {
		die;
	}
	if (!class_exists('TTBM_Ticket_Capacity_Override')) {
		class TTBM_Ticket_Capacity_Override {
			public static function meta_key(): string {
				return 'ttbm_capacity_override_rules';
			}
			public function __construct() {
				add_filter('ttbm_ticket_capacity', [$this, 'filter_capacity'], 20, 4);
			}
			public static function get_rules($tour_id): array {
				$rules = TTBM_Global_Function::get_post_info($tour_id, self::meta_key(), []);
				return self::sanitize_rules($rules);
			}
			public function filter_capacity($capacity, $tour_id, $tour_date, $ticket_name) {
				$datetime = self::split_tour_datetime($tour_date);
				$ticket_name = sanitize_text_field($ticket_name);
				if (empty($datetime['date']) || !$ticket_name) {
					return max(0, (int)$capacity);
				}
				return self::apply_rules_to_capacity($capacity, self::get_rules($tour_id), $datetime['date'], $datetime['time'], $ticket_name);
			}
			public static function apply_rules_to_capacity($capacity, array $rules, string $date, string $time = '', string $ticket_name = '', bool $ignore_ticket = false): int {
				$current = max(0, (int)$capacity);
				foreach (array_reverse($rules) as $rule) {
					if (!self::rule_matches($rule, $date, $time, $ticket_name, $ignore_ticket)) {
						continue;
					}
					$amount = max(0, (int)($rule['amount'] ?? 0));
					$mode = $rule['adjustment_mode'] ?? 'add';
					if ($mode === 'override') {
						$current = $amount;
						continue;
					}
					if ($mode === 'reduce') {
						$current = max(0, $current - $amount);
						continue;
					}
					$current += $amount;
				}
				return max(0, (int)$current);
			}
			public static function split_tour_datetime($tour_date): array {
				$tour_date = sanitize_text_field((string)$tour_date);
				if (!$tour_date) {
					return ['date' => '', 'time' => ''];
				}
				$timestamp = strtotime($tour_date);
				if (!$timestamp) {
					return ['date' => '', 'time' => ''];
				}
				$has_time = preg_match('/\b\d{1,2}:\d{2}\b/', $tour_date) === 1;
				return [
					'date' => gmdate('Y-m-d', $timestamp),
					'time' => $has_time ? gmdate('H:i', $timestamp) : '',
				];
			}
			private static function rule_matches(array $rule, string $date, string $time, string $ticket_name, bool $ignore_ticket): bool {
				if (!$ignore_ticket && ($rule['ticket_type'] ?? '') !== $ticket_name) {
					return false;
				}
				if (!self::matches_date($rule, $date)) {
					return false;
				}
				$rule_time = $rule['time'] ?? '';
				if (!$rule_time) {
					return true;
				}
				return !empty($time) && $rule_time === $time;
			}
			private static function matches_date(array $rule, string $date): bool {
				$start = $rule['start'] ?? '';
				$end = $rule['end'] ?? '';
				if (!$start || !$end) {
					return false;
				}
				return $date >= $start && $date <= $end;
			}
			private static function sanitize_rules($raw_rules): array {
				if (is_string($raw_rules)) {
					$decoded = json_decode(wp_unslash($raw_rules), true);
					$raw_rules = is_array($decoded) ? $decoded : [];
				}
				if (!is_array($raw_rules)) {
					return [];
				}
				$rules = [];
				foreach ($raw_rules as $rule) {
					if (!is_array($rule)) {
						continue;
					}
					$ticket_type = sanitize_text_field($rule['ticket_type'] ?? '');
					$start = self::sanitize_date($rule['start'] ?? '');
					$end = self::sanitize_date($rule['end'] ?? $start);
					$time = self::sanitize_time($rule['time'] ?? '');
					$mode = sanitize_text_field($rule['adjustment_mode'] ?? '');
					if (!$ticket_type || !$start || !$end || !in_array($mode, ['add', 'reduce', 'override'], true)) {
						continue;
					}
					$rules[] = [
						'id' => sanitize_text_field($rule['id'] ?? uniqid('capacity_', true)),
						'type' => sanitize_text_field($rule['type'] ?? 'single') === 'range' ? 'range' : 'single',
						'ticket_type' => $ticket_type,
						'start' => $start,
						'end' => $end,
						'time' => $time,
						'adjustment_mode' => $mode,
						'amount' => max(0, (int)($rule['amount'] ?? 0)),
					];
				}
				return $rules;
			}
			private static function sanitize_date($date): string {
				$date = sanitize_text_field((string)$date);
				$timestamp = strtotime($date);
				return $timestamp ? gmdate('Y-m-d', $timestamp) : '';
			}
			private static function sanitize_time($time): string {
				$time = sanitize_text_field((string)$time);
				return preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '';
			}
		}
		new TTBM_Ticket_Capacity_Override();
	}
