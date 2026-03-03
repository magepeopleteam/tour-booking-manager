<?php
	if (!defined('ABSPATH')) {
		die;
	}
	if (!class_exists('TTBM_Settings_Capacity_Override')) {
		class TTBM_Settings_Capacity_Override {
			public function __construct() {
				add_action('ttbm_meta_box_tab_name', [$this, 'tab_name'], 16, 1);
				add_action('ttbm_meta_box_tab_content', [$this, 'tab_content'], 16, 1);
			}
			public static function meta_key(): string {
				return 'ttbm_capacity_override_rules';
			}
			public function tab_name($tour_id) {
				unset($tour_id);
				?>
                <li data-tabs-target="#ttbm_settings_capacity_override">
                    <i class="fas fa-ticket-alt"></i>
                    <span><?php esc_html_e(' Capacity Override', 'tour-booking-manager'); ?></span>
                </li>
				<?php
			}
			public function tab_content($tour_id) {
				$rules = self::sanitize_rules(TTBM_Global_Function::get_post_info($tour_id, self::meta_key(), []));
				$tickets = $this->get_ticket_types($tour_id);
				$available_dates = $this->get_available_dates($tour_id);
				$default_date = !empty($available_dates) ? $available_dates[0] : current_time('Y-m-d');
				$shared_capacity_enabled = TTBM_Function::get_tour_type($tour_id) === 'general' && TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_global_qty', 'off') === 'on';
				$shared_capacity = (int) TTBM_Global_Function::get_post_info($tour_id, 'ttbm_global_qty', 0);
				$config = [
					'rules' => $rules,
					'tickets' => $tickets,
					'sharedCapacityEnabled' => $shared_capacity_enabled,
					'sharedCapacity' => $shared_capacity,
					'availableDates' => $available_dates,
					'slotMap' => $this->get_slot_map($tour_id, $available_dates),
					'today' => $default_date,
					'labels' => [
						'fullDays' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
					],
				];
				?>
                <div class="tabsItem ttbm_settings_capacity_override" data-tabs="#ttbm_settings_capacity_override">
                    <h2><?php esc_html_e('Capacity Override', 'tour-booking-manager'); ?></h2>
                    <p class="info_text"><?php esc_html_e('Create ticket-type-specific capacity overrides for a single date or an upcoming date range without changing the default ticket capacity.', 'tour-booking-manager'); ?></p>
                    <section class="ttbm-capacity-override" data-capacity-root>
                        <textarea class="dNone" name="<?php echo esc_attr(self::meta_key()); ?>" data-capacity-store><?php echo esc_textarea(wp_json_encode($rules)); ?></textarea>
                        <script type="application/json" data-capacity-config><?php echo wp_json_encode($config); ?></script>
                        <div class="ttbm-capacity-shell">
                            <div class="ttbm-capacity-header">
                                <div>
                                    <div class="ttbm-capacity-kicker"><?php esc_html_e('Ticket Control', 'tour-booking-manager'); ?></div>
                                    <h3><?php esc_html_e('Capacity Override', 'tour-booking-manager'); ?></h3>
                                    <p><?php esc_html_e('Adjust seat capacity date by date for a specific ticket type while keeping your main ticket settings untouched.', 'tour-booking-manager'); ?></p>
                                </div>
                                <div class="ttbm-capacity-summary">
                                    <span><?php esc_html_e('Saved overrides', 'tour-booking-manager'); ?></span>
                                    <strong data-capacity-count>0</strong>
                                </div>
                            </div>
                            <div class="ttbm-capacity-body"></div>
                        </div>
                    </section>
					<?php $this->render_inline_assets(); ?>
                </div>
				<?php
			}
			private function get_ticket_types($tour_id): array {
				$ticket_types = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticket_type', []);
				$items = [];
				if (!is_array($ticket_types)) {
					return $items;
				}
				foreach ($ticket_types as $ticket) {
					$name = isset($ticket['ticket_type_name']) ? sanitize_text_field($ticket['ticket_type_name']) : '';
					if (!$name) {
						continue;
					}
					$items[] = [
						'name' => $name,
						'capacity' => max(0, (int)($ticket['ticket_type_qty'] ?? 0)),
						'icon' => isset($ticket['ticket_type_icon']) ? sanitize_html_class($ticket['ticket_type_icon']) : '',
					];
				}
				return array_values($items);
			}
			private function get_available_dates($tour_id): array {
				$dates = TTBM_Function::get_date($tour_id, 'yes');
				if (isset($dates['date'])) {
					return !empty($dates['date']) ? [sanitize_text_field($dates['date'])] : [];
				}
				if (!is_array($dates)) {
					return [];
				}
				$normalized = [];
				foreach ($dates as $date) {
					$timestamp = strtotime($date);
					if ($timestamp) {
						$normalized[] = gmdate('Y-m-d', $timestamp);
					}
				}
				$normalized = array_values(array_unique(array_filter($normalized)));
				sort($normalized);
				return $normalized;
			}
			private function get_slot_map($tour_id, array $available_dates): array {
				$slot_map = [];
				foreach ($available_dates as $date) {
					$slot_map[$date] = $this->normalize_slots(TTBM_Function::get_time($tour_id, $date, 'yes'));
				}
				return $slot_map;
			}
			private function normalize_slots($slots): array {
				$normalized = [];
				if (empty($slots)) {
					return $normalized;
				}
				if (!is_array($slots)) {
					$time = $this->extract_time_value($slots);
					if ($time) {
						$normalized[] = [
							'time' => $time,
							'label' => $time,
						];
					}
					return $normalized;
				}
				foreach ($slots as $slot) {
					$time = $this->extract_time_value($slot);
					if (!$time) {
						continue;
					}
					$normalized[$time] = [
						'time' => $time,
						'label' => $this->extract_time_label($slot) ?: $time,
					];
				}
				ksort($normalized);
				return array_values($normalized);
			}
			private function extract_time_value($slot): string {
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
			private function extract_time_label($slot): string {
				if (is_array($slot)) {
					if (!empty($slot['label'])) {
						return sanitize_text_field($slot['label']);
					}
					if (!empty($slot['mep_ticket_time_name'])) {
						return sanitize_text_field($slot['mep_ticket_time_name']);
					}
				}
				return $this->extract_time_value($slot);
			}
			public static function sanitize_rules($raw_rules): array {
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
					$type = sanitize_text_field($rule['type'] ?? 'single');
					$amount = isset($rule['amount']) ? max(0, (int)$rule['amount']) : -1;
					if (!$ticket_type || !$start || !$end || !in_array($mode, ['add', 'reduce', 'override'], true) || $amount < 0) {
						continue;
					}
					if ($start > $end) {
						$temp = $start;
						$start = $end;
						$end = $temp;
					}
					$rules[] = [
						'id' => sanitize_text_field($rule['id'] ?? uniqid('capacity_', true)),
						'type' => $type === 'range' ? 'range' : 'single',
						'ticket_type' => $ticket_type,
						'start' => $start,
						'end' => $end,
						'time' => $time,
						'adjustment_mode' => $mode,
						'amount' => $amount,
					];
				}
				return array_values($rules);
			}
			private static function sanitize_date($date): string {
				$date = sanitize_text_field($date);
				$timestamp = strtotime($date);
				if (!$timestamp) {
					return '';
				}
				$normalized = gmdate('Y-m-d', $timestamp);
				return $normalized === $date ? $normalized : '';
			}
			private static function sanitize_time($time): string {
				$time = sanitize_text_field((string)$time);
				return preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '';
			}
			private function render_inline_assets() {
				?>
                <style>
                    .ttbm-capacity-override {
                        --co-bg: #f7f7fc;
                        --co-surface: #ffffff;
                        --co-text: #1f2337;
                        --co-muted: #7a80a0;
                        --co-border: #e1e5f2;
                        --co-pink: #e8175d;
                        --co-pink-soft: #fff0f5;
                        --co-green: #00a56a;
                        --co-green-soft: #edf9f3;
                        --co-orange: #d97706;
                        --co-orange-soft: #fff5ea;
                        background: linear-gradient(180deg, #fafbff 0%, #f5f7ff 100%);
                        border: 1px solid var(--co-border);
                        border-radius: 18px;
                        padding: 24px;
                    }
                    .ttbm-capacity-header {
                        display: flex;
                        justify-content: space-between;
                        gap: 18px;
                        margin-bottom: 22px;
                    }
                    .ttbm-capacity-kicker {
                        display: inline-flex;
                        padding: 4px 10px;
                        border-radius: 999px;
                        background: rgba(232, 23, 93, 0.08);
                        color: var(--co-pink);
                        font-size: 11px;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                    }
                    .ttbm-capacity-header h3,
                    .ttbm-capacity-header h4,
                    .ttbm-capacity-header p {
                        margin: 0;
                    }
                    .ttbm-capacity-header p {
                        color: var(--co-muted);
                        margin-top: 6px;
                    }
                    .ttbm-capacity-summary {
                        min-width: 180px;
                        background: var(--co-surface);
                        border: 1px solid var(--co-border);
                        border-radius: 16px;
                        padding: 16px;
                    }
                    .ttbm-capacity-summary span {
                        display: block;
                        color: var(--co-muted);
                        font-size: 12px;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                        margin-bottom: 8px;
                    }
                    .ttbm-capacity-summary strong {
                        font-size: 30px;
                        color: var(--co-text);
                    }
                    .ttbm-capacity-card {
                        background: var(--co-surface);
                        border: 1px solid var(--co-border);
                        border-radius: 16px;
                        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
                        overflow: hidden;
                    }
                    .ttbm-capacity-card + .ttbm-capacity-card {
                        margin-top: 18px;
                    }
                    .ttbm-capacity-card-head {
                        padding: 18px 22px;
                        border-bottom: 1px solid var(--co-border);
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .ttbm-capacity-card-head i {
                        width: 34px;
                        height: 34px;
                        border-radius: 10px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        background: #f4f6ff;
                        color: var(--co-pink);
                    }
                    .ttbm-capacity-card-head p {
                        margin-top: 2px;
                        color: var(--co-muted);
                        font-size: 12px;
                    }
                    .ttbm-capacity-card-body {
                        padding: 20px 22px;
                    }
                    .ttbm-capacity-ticket-pills,
                    .ttbm-capacity-mode-tabs,
                    .ttbm-capacity-footer-actions,
                    .ttbm-capacity-preview,
                    .ttbm-capacity-rule-actions {
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    }
                    .ttbm-capacity-ticket-pills {
                        margin-bottom: 18px;
                    }
                    .ttbm-capacity-ticket-pill {
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 9px 14px;
                        border-radius: 999px;
                        border: 1px solid var(--co-border);
                        background: #fff;
                        cursor: pointer;
                        font-weight: 600;
                        color: var(--co-muted);
                    }
                    .ttbm-capacity-ticket-pill.active {
                        background: var(--co-pink);
                        border-color: var(--co-pink);
                        color: #fff;
                        box-shadow: 0 8px 18px rgba(232, 23, 93, 0.24);
                    }
                    .ttbm-capacity-ticket-pill small {
                        padding: 2px 8px;
                        border-radius: 999px;
                        background: rgba(255,255,255,0.2);
                        font-size: 11px;
                    }
                    .ttbm-capacity-ticket-pill:not(.active) small {
                        background: #f4f6fb;
                        color: var(--co-muted);
                    }
                    .ttbm-capacity-mode-tabs {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        margin-bottom: 18px;
                    }
                    .ttbm-capacity-mode-tab {
                        border: 1px solid var(--co-border);
                        background: #f8f9ff;
                        border-radius: 12px;
                        padding: 14px 16px;
                        cursor: pointer;
                        display: flex;
                        flex-direction: column;
                        gap: 4px;
                    }
                    .ttbm-capacity-mode-tab.active {
                        border-color: rgba(232, 23, 93, 0.4);
                        background: var(--co-pink-soft);
                    }
                    .ttbm-capacity-mode-tab strong {
                        display: block;
                    }
                    .ttbm-capacity-mode-tab div {
                        color: var(--co-muted);
                        font-size: 12px;
                        line-height: 1.4;
                    }
                    .ttbm-capacity-grid {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: 14px;
                        margin-bottom: 18px;
                    }
                    .ttbm-capacity-grid.single {
                        grid-template-columns: minmax(0, 320px);
                    }
                    .ttbm-capacity-field label,
                    .ttbm-capacity-note {
                        display: block;
                        color: var(--co-muted);
                        font-size: 11px;
                        font-weight: 700;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 8px;
                    }
                    .ttbm-capacity-field input {
                        width: 100%;
                        border: 1px solid var(--co-border);
                        border-radius: 10px;
                        padding: 11px 13px;
                        background: #fff;
                    }
                    .ttbm-capacity-date-input {
                        cursor: pointer;
                    }
                    .ttbm-capacity-adjust {
                        background: var(--co-bg);
                        border-radius: 14px;
                        padding: 18px;
                    }
                    .ttbm-capacity-adjust-head {
                        display: flex;
                        justify-content: space-between;
                        gap: 12px;
                        margin-bottom: 14px;
                        align-items: center;
                    }
                    .ttbm-capacity-adjust-grid {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: 12px;
                    }
                    .ttbm-capacity-action {
                        border: 1px solid var(--co-border);
                        border-radius: 12px;
                        padding: 16px;
                        background: #fff;
                    }
                    .ttbm-capacity-action.add {
                        background: var(--co-green-soft);
                        border-color: rgba(0, 165, 106, 0.22);
                    }
                    .ttbm-capacity-action.reduce {
                        background: var(--co-pink-soft);
                        border-color: rgba(232, 23, 93, 0.22);
                    }
                    .ttbm-capacity-action.override {
                        background: var(--co-orange-soft);
                        border-color: rgba(217, 119, 6, 0.22);
                        grid-column: 1 / -1;
                    }
                    .ttbm-capacity-action strong {
                        display: block;
                        margin-bottom: 10px;
                    }
                    .ttbm-capacity-stepper {
                        display: inline-flex;
                        border: 1px solid var(--co-border);
                        border-radius: 10px;
                        overflow: hidden;
                        background: #fff;
                    }
                    .ttbm-capacity-stepper button,
                    .ttbm-capacity-btn,
                    .ttbm-capacity-log button {
                        cursor: pointer;
                        border: 1px solid var(--co-border);
                        background: #fff;
                        color: var(--co-text);
                    }
                    .ttbm-capacity-stepper button {
                        width: 34px;
                        height: 34px;
                        border: 0;
                        background: #f4f6fb;
                        font-size: 18px;
                    }
                    .ttbm-capacity-stepper input,
                    .ttbm-capacity-override-input {
                        border: 0;
                        text-align: center;
                        font-weight: 700;
                        font-size: 14px;
                    }
                    .ttbm-capacity-stepper input {
                        width: 58px;
                        border-left: 1px solid var(--co-border);
                        border-right: 1px solid var(--co-border);
                    }
                    .ttbm-capacity-override-input {
                        width: 90px;
                        border: 1px solid rgba(217, 119, 6, 0.24) !important;
                        border-radius: 10px !important;
                    }
                    .ttbm-capacity-preview {
                        margin-top: 16px;
                        padding: 14px 16px;
                        border-radius: 12px;
                        border: 1px dashed #cfd4ef;
                        background: #f7f8ff;
                        align-items: center;
                    }
                    .ttbm-capacity-preview-chip {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 6px 10px;
                        border-radius: 999px;
                        background: #fff;
                        border: 1px solid var(--co-border);
                    }
                    .ttbm-capacity-preview-old {
                        text-decoration: line-through;
                        color: var(--co-muted);
                    }
                    .ttbm-capacity-preview-new.up {
                        color: var(--co-green);
                        font-weight: 700;
                    }
                    .ttbm-capacity-preview-new.down {
                        color: var(--co-pink);
                        font-weight: 700;
                    }
                    .ttbm-capacity-preview-new.override {
                        color: var(--co-orange);
                        font-weight: 700;
                    }
                    .ttbm-capacity-card-footer {
                        padding: 16px 22px;
                        border-top: 1px solid var(--co-border);
                        background: #fafbff;
                        display: flex;
                        justify-content: space-between;
                        gap: 12px;
                        align-items: center;
                    }
                    .ttbm-capacity-btn {
                        padding: 10px 16px;
                        border-radius: 10px;
                        font-weight: 700;
                    }
                    .ttbm-capacity-btn.primary {
                        background: var(--co-pink);
                        border-color: var(--co-pink);
                        color: #fff;
                    }
                    .ttbm-capacity-log {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .ttbm-capacity-log th,
                    .ttbm-capacity-log td {
                        padding: 12px 14px;
                        border-bottom: 1px solid var(--co-border);
                        text-align: left;
                    }
                    .ttbm-capacity-log thead th {
                        font-size: 11px;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        color: var(--co-muted);
                        background: #f7f8ff;
                    }
                    .ttbm-capacity-log tbody tr:last-child td {
                        border-bottom: 0;
                    }
                    .ttbm-capacity-type {
                        display: inline-flex;
                        padding: 4px 9px;
                        border-radius: 999px;
                        background: #f3f5ff;
                        color: #4f46e5;
                        font-weight: 600;
                    }
                    .ttbm-capacity-change.up {
                        color: var(--co-green);
                        font-weight: 700;
                    }
                    .ttbm-capacity-change.down {
                        color: var(--co-pink);
                        font-weight: 700;
                    }
                    .ttbm-capacity-empty {
                        border: 1px dashed var(--co-border);
                        border-radius: 14px;
                        padding: 28px 18px;
                        text-align: center;
                        color: var(--co-muted);
                        background: #fff;
                    }
                    @media (max-width: 782px) {
                        .ttbm-capacity-header,
                        .ttbm-capacity-card-footer {
                            flex-direction: column;
                            align-items: stretch;
                        }
                        .ttbm-capacity-grid,
                        .ttbm-capacity-adjust-grid,
                        .ttbm-capacity-mode-tabs {
                            grid-template-columns: 1fr;
                        }
                    }
                </style>
                <script>
                    window.TTBMCapacityOverrideStrings = {
                        noTickets: <?php echo wp_json_encode(__('Add ticket types in Pricing first to use capacity override.', 'tour-booking-manager')); ?>,
						selectTicketType: <?php echo wp_json_encode(__('Select Ticket Type', 'tour-booking-manager')); ?>,
						ticketSub: <?php echo wp_json_encode(__('Choose which ticket type should receive the override.', 'tour-booking-manager')); ?>,
						sharedSelectTicketType: <?php echo wp_json_encode(__('Select Ticket Label', 'tour-booking-manager')); ?>,
						sharedTicketSub: <?php echo wp_json_encode(__('Shared Quantity is enabled. The selected ticket label identifies the rule, but the override changes the shared capacity pool for all ticket types.', 'tour-booking-manager')); ?>,
						sharedPoolLabel: <?php echo wp_json_encode(__('Shared Pool', 'tour-booking-manager')); ?>,
						sharedCapShort: <?php echo wp_json_encode(__('shared', 'tour-booking-manager')); ?>,
						capShort: <?php echo wp_json_encode(__('cap', 'tour-booking-manager')); ?>,
						adjustCapacity: <?php echo wp_json_encode(__('Adjust Capacity', 'tour-booking-manager')); ?>,
						adjustSub: <?php echo wp_json_encode(__('Override capacity for one date or a continuous date range.', 'tour-booking-manager')); ?>,
						sharedAdjustSub: <?php echo wp_json_encode(__('This override updates the shared quantity pool used by every ticket type on the selected date or range.', 'tour-booking-manager')); ?>,
						singleDate: <?php echo wp_json_encode(__('Single Date', 'tour-booking-manager')); ?>,
						singleDateSub: <?php echo wp_json_encode(__('Override one specific day', 'tour-booking-manager')); ?>,
						dateRange: <?php echo wp_json_encode(__('Date Range', 'tour-booking-manager')); ?>,
                        dateRangeSub: <?php echo wp_json_encode(__('Override a span of days', 'tour-booking-manager')); ?>,
                        selectDate: <?php echo wp_json_encode(__('Select Date', 'tour-booking-manager')); ?>,
						startDate: <?php echo wp_json_encode(__('Start Date', 'tour-booking-manager')); ?>,
                        endDate: <?php echo wp_json_encode(__('End Date', 'tour-booking-manager')); ?>,
						timeSlot: <?php echo wp_json_encode(__('Time Slot', 'tour-booking-manager')); ?>,
						allTimeSlots: <?php echo wp_json_encode(__('All time slots', 'tour-booking-manager')); ?>,
						noTimeSlots: <?php echo wp_json_encode(__('No time slots available for this selection.', 'tour-booking-manager')); ?>,
						capacityAdjustment: <?php echo wp_json_encode(__('Capacity Adjustment', 'tour-booking-manager')); ?>,
						defaultSeats: <?php echo wp_json_encode(__('Default', 'tour-booking-manager')); ?>,
						sharedDefaultSeats: <?php echo wp_json_encode(__('Shared Default', 'tour-booking-manager')); ?>,
						addSeats: <?php echo wp_json_encode(__('Add Seats', 'tour-booking-manager')); ?>,
						reduceSeats: <?php echo wp_json_encode(__('Reduce Seats', 'tour-booking-manager')); ?>,
						fixedOverride: <?php echo wp_json_encode(__('Set Fixed Override', 'tour-booking-manager')); ?>,
                        fixedOverrideRange: <?php echo wp_json_encode(__('Set Fixed Override (All Days in Range)', 'tour-booking-manager')); ?>,
                        preview: <?php echo wp_json_encode(__('Preview', 'tour-booking-manager')); ?>,
                        pendingChanges: <?php echo wp_json_encode(__('Pending changes', 'tour-booking-manager')); ?>,
                        discard: <?php echo wp_json_encode(__('Discard', 'tour-booking-manager')); ?>,
                        saveOverride: <?php echo wp_json_encode(__('Save Override', 'tour-booking-manager')); ?>,
                        overrideLog: <?php echo wp_json_encode(__('Override Log', 'tour-booking-manager')); ?>,
                        overrideLogSub: <?php echo wp_json_encode(__('Saved capacity overrides for upcoming dates.', 'tour-booking-manager')); ?>,
						logDate: <?php echo wp_json_encode(__('Date', 'tour-booking-manager')); ?>,
						logTicket: <?php echo wp_json_encode(__('Ticket Type', 'tour-booking-manager')); ?>,
						logDefault: <?php echo wp_json_encode(__('Default', 'tour-booking-manager')); ?>,
						logSharedDefault: <?php echo wp_json_encode(__('Shared Default', 'tour-booking-manager')); ?>,
						logOverride: <?php echo wp_json_encode(__('Override', 'tour-booking-manager')); ?>,
						logChange: <?php echo wp_json_encode(__('Change', 'tour-booking-manager')); ?>,
						logAction: <?php echo wp_json_encode(__('Action', 'tour-booking-manager')); ?>,
						logScope: <?php echo wp_json_encode(__('Scope', 'tour-booking-manager')); ?>,
                        remove: <?php echo wp_json_encode(__('Remove', 'tour-booking-manager')); ?>,
                        edit: <?php echo wp_json_encode(__('Edit', 'tour-booking-manager')); ?>,
                        noOverrides: <?php echo wp_json_encode(__('No capacity overrides saved yet.', 'tour-booking-manager')); ?>,
                        daysAffected: <?php echo wp_json_encode(__('days affected', 'tour-booking-manager')); ?>
                    };
                </script>
				<?php
			}
		}
		new TTBM_Settings_Capacity_Override();
	}
