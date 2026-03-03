<?php
	if (!defined('ABSPATH')) {
		die;
	}
	if (!class_exists('TTBM_Settings_Schedule_Planner')) {
		class TTBM_Settings_Schedule_Planner {
			public function __construct() {
				add_action('ttbm_meta_box_tab_name', [$this, 'tab_name'], 15, 1);
				add_action('ttbm_meta_box_tab_content', [$this, 'tab_content'], 15, 1);
			}
			public static function meta_key(): string {
				return 'ttbm_schedule_planner_rules';
			}
			public function tab_name($tour_id) {
				unset($tour_id);
				?>
                <li data-tabs-target="#ttbm_settings_schedule_planner">
                    <i class="mi mi-calendar"></i>
                    <span><?php esc_html_e(' Schedule Planner', 'tour-booking-manager'); ?></span>
                </li>
				<?php
			}
			public function tab_content($tour_id) {
				$rules = self::sanitize_rules(TTBM_Global_Function::get_post_info($tour_id, self::meta_key(), []));
				$available_dates = $this->get_available_dates($tour_id);
				$default_date = !empty($available_dates) ? $available_dates[0] : current_time('Y-m-d');
				$config = [
					'rules' => is_array($rules) ? array_values($rules) : [],
					'globalTimes' => $this->get_global_times($tour_id),
					'weekdayTimes' => $this->get_weekday_times($tour_id),
					'availableDates' => $available_dates,
					'labels' => [
						'days' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
						'fullDays' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
					],
					'today' => $default_date,
				];
				?>
                <div class="tabsItem ttbm_settings_schedule_planner" data-tabs="#ttbm_settings_schedule_planner">
                    <h2><?php esc_html_e('Schedule Planner', 'tour-booking-manager'); ?></h2>
                    <p class="info_text"><?php esc_html_e('Create reusable single-date and date-range schedule rules, then save them with the tour to control booking availability on the frontend.', 'tour-booking-manager'); ?></p>
                    <section class="ttbm-schedule-planner" data-planner-root>
                        <textarea class="dNone" name="<?php echo esc_attr(self::meta_key()); ?>" data-planner-store><?php echo esc_textarea(wp_json_encode($config['rules'])); ?></textarea>
                        <script type="application/json" data-planner-config><?php echo wp_json_encode($config); ?></script>
                        <div class="ttbm-schedule-shell">
                            <div class="ttbm-schedule-header">
                                <div>
                                    <div class="ttbm-schedule-kicker"><?php esc_html_e('Tour Management', 'tour-booking-manager'); ?></div>
                                    <h3><?php esc_html_e('Schedule Planner', 'tour-booking-manager'); ?></h3>
                                    <p><?php esc_html_e('Build date-specific overrides without touching your base weekday or default time-slot setup.', 'tour-booking-manager'); ?></p>
                                </div>
                                <div class="ttbm-schedule-summary">
                                    <span><?php esc_html_e('Rules saved with this tour', 'tour-booking-manager'); ?></span>
                                    <strong data-planner-count>0</strong>
                                </div>
                            </div>
                            <div class="ttbm-schedule-tabs" role="tablist">
                                <button type="button" class="active" data-planner-tab="single"><?php esc_html_e('Single Date', 'tour-booking-manager'); ?></button>
                                <button type="button" data-planner-tab="bulk"><?php esc_html_e('Bulk / Date Range', 'tour-booking-manager'); ?></button>
                                <button type="button" data-planner-tab="rules"><?php esc_html_e('Scheduled Rules', 'tour-booking-manager'); ?></button>
                            </div>
                            <div class="ttbm-schedule-panel active" data-planner-panel="single"></div>
                            <div class="ttbm-schedule-panel" data-planner-panel="bulk"></div>
                            <div class="ttbm-schedule-panel" data-planner-panel="rules"></div>
                            <div class="ttbm-schedule-footer">
                                <span><?php esc_html_e('Rules are stored when you update the tour.', 'tour-booking-manager'); ?></span>
                                <div class="ttbm-schedule-footer-actions">
                                    <button type="button" class="btn" data-planner-clear><?php esc_html_e('Clear All', 'tour-booking-manager'); ?></button>
                                </div>
                            </div>
                        </div>
                    </section>
					<?php $this->render_inline_assets(); ?>
                </div>
				<?php
			}
			private function get_global_times($tour_id): array {
				$times = $this->extract_times(TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_times_global', []));
				$fallback = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date_time');
				if ($fallback) {
					$times[] = $fallback;
				}
				return array_values(array_unique(array_filter($times)));
			}
			private function get_weekday_times($tour_id): array {
				$map = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
				$data = [];
				foreach ($map as $index => $suffix) {
					$data[$index] = $this->extract_times(TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_times_' . $suffix, []));
				}
				return $data;
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
					$normalized_date = gmdate('Y-m-d', strtotime($date));
					if ($normalized_date) {
						$normalized[] = $normalized_date;
					}
				}
				$normalized = array_values(array_unique($normalized));
				sort($normalized);
				return $normalized;
			}
			private function extract_times($items): array {
				$times = [];
				if (!is_array($items)) {
					return $times;
				}
				foreach ($items as $item) {
					if (is_array($item) && !empty($item['mep_ticket_time'])) {
						$times[] = sanitize_text_field($item['mep_ticket_time']);
						continue;
					}
					if (is_string($item) && $item) {
						$times[] = sanitize_text_field($item);
					}
				}
				return $times;
			}
			public static function sanitize_rules($raw_rules): array {
				if (is_string($raw_rules)) {
					$decoded = json_decode(wp_unslash($raw_rules), true);
					$raw_rules = is_array($decoded) ? $decoded : [];
				}
				if (!is_array($raw_rules)) {
					return [];
				}
				$sanitized = [];
				foreach ($raw_rules as $rule) {
					if (!is_array($rule) || !isset($rule['type'])) {
						continue;
					}
					$type = sanitize_text_field($rule['type']);
					if ($type === 'single') {
						$date = self::sanitize_date($rule['date'] ?? '');
						if (!$date) {
							continue;
						}
						$sanitized[] = [
							'id' => sanitize_text_field($rule['id'] ?? uniqid('single_', true)),
							'type' => 'single',
							'date' => $date,
							'full_cancel' => !empty($rule['full_cancel']),
							'removed' => self::sanitize_times($rule['removed'] ?? []),
							'added' => self::sanitize_times($rule['added'] ?? []),
						];
						continue;
					}
					if ($type === 'bulk') {
						$start = self::sanitize_date($rule['start'] ?? '');
						$end = self::sanitize_date($rule['end'] ?? '');
						if (!$start || !$end) {
							continue;
						}
						if ($start > $end) {
							$temp = $start;
							$start = $end;
							$end = $temp;
						}
						$mode = sanitize_text_field($rule['bulk_mode'] ?? 'add');
						if (!in_array($mode, ['add', 'remove', 'cancel'], true)) {
							$mode = 'add';
						}
						$days = [];
						if (!empty($rule['days']) && is_array($rule['days'])) {
							foreach ($rule['days'] as $day) {
								$day = (int)$day;
								if ($day >= 0 && $day <= 6) {
									$days[] = $day;
								}
							}
						}
						$sanitized[] = [
							'id' => sanitize_text_field($rule['id'] ?? uniqid('bulk_', true)),
							'type' => 'bulk',
							'start' => $start,
							'end' => $end,
							'days' => array_values(array_unique($days)),
							'bulk_mode' => $mode,
							'selected_times' => $mode === 'cancel' ? [] : self::sanitize_times($rule['selected_times'] ?? []),
							'added_times' => $mode === 'add' ? self::sanitize_times($rule['added_times'] ?? []) : [],
						];
					}
				}
				return $sanitized;
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
			private static function sanitize_times($times): array {
				$clean = [];
				if (!is_array($times)) {
					return $clean;
				}
				foreach ($times as $time) {
					$time = sanitize_text_field($time);
					if (preg_match('/^\d{2}:\d{2}$/', $time)) {
						$clean[] = $time;
					}
				}
				$clean = array_values(array_unique($clean));
				sort($clean);
				return $clean;
			}
			private function render_inline_assets() {
				?>
                <style>
                    .ttbm-schedule-planner {
                        --sp-bg: #f4f6fb;
                        --sp-surface: #ffffff;
                        --sp-border: #dbe3f1;
                        --sp-text: #1f2940;
                        --sp-muted: #66728f;
                        --sp-primary: #1d4ed8;
                        --sp-primary-soft: rgba(29, 78, 216, 0.12);
                        --sp-accent: #d97706;
                        --sp-accent-soft: rgba(217, 119, 6, 0.12);
                        --sp-danger: #dc2626;
                        --sp-danger-soft: rgba(220, 38, 38, 0.1);
                        --sp-success: #059669;
                        background: linear-gradient(135deg, #eff4ff 0%, #f9fbff 100%);
                        border: 1px solid var(--sp-border);
                        border-radius: 18px;
                        padding: 24px;
                    }
                    .ttbm-schedule-shell h3,
                    .ttbm-schedule-shell h4 {
                        margin: 0;
                        color: var(--sp-text);
                    }
                    .ttbm-schedule-header {
                        display: flex;
                        justify-content: space-between;
                        gap: 16px;
                        margin-bottom: 24px;
                    }
                    .ttbm-schedule-header p {
                        margin: 6px 0 0;
                        color: var(--sp-muted);
                    }
                    .ttbm-schedule-kicker {
                        display: inline-flex;
                        padding: 4px 10px;
                        border-radius: 999px;
                        background: rgba(29, 78, 216, 0.08);
                        color: var(--sp-primary);
                        font-size: 11px;
                        font-weight: 600;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                    }
                    .ttbm-schedule-summary {
                        min-width: 180px;
                        border: 1px solid var(--sp-border);
                        background: var(--sp-surface);
                        border-radius: 16px;
                        padding: 16px;
                        display: flex;
                        flex-direction: column;
                        gap: 6px;
                        align-items: flex-start;
                        justify-content: center;
                    }
                    .ttbm-schedule-summary span {
                        color: var(--sp-muted);
                        font-size: 12px;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                    }
                    .ttbm-schedule-summary strong {
                        font-size: 28px;
                        line-height: 1;
                        color: var(--sp-text);
                    }
                    .ttbm-schedule-tabs {
                        display: flex;
                        gap: 6px;
                        padding: 6px;
                        background: rgba(148, 163, 184, 0.14);
                        border-radius: 14px;
                        margin-bottom: 18px;
                        flex-wrap: wrap;
                    }
                    .ttbm-schedule-tabs button {
                        border: 0;
                        background: transparent;
                        color: var(--sp-muted);
                        padding: 12px 18px;
                        border-radius: 10px;
                        cursor: pointer;
                        font-weight: 600;
                    }
                    .ttbm-schedule-tabs button.active {
                        background: var(--sp-surface);
                        color: var(--sp-text);
                        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
                    }
                    .ttbm-schedule-panel {
                        display: none;
                    }
                    .ttbm-schedule-panel.active {
                        display: block;
                    }
                    .ttbm-schedule-card {
                        border: 1px solid var(--sp-border);
                        background: var(--sp-surface);
                        border-radius: 18px;
                        padding: 20px;
                        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
                    }
                    .ttbm-schedule-card + .ttbm-schedule-card {
                        margin-top: 16px;
                    }
                    .ttbm-schedule-card-title {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        margin-bottom: 18px;
                        font-size: 16px;
                        font-weight: 700;
                    }
                    .ttbm-schedule-card-title::before {
                        content: "";
                        width: 10px;
                        height: 10px;
                        border-radius: 50%;
                        background: var(--sp-primary);
                    }
                    .ttbm-schedule-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                        gap: 14px;
                    }
                    .ttbm-schedule-field label,
                    .ttbm-schedule-note-label {
                        display: block;
                        color: var(--sp-muted);
                        font-size: 12px;
                        font-weight: 600;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        margin-bottom: 8px;
                    }
                    .ttbm-schedule-field input,
                    .ttbm-schedule-field select {
                        width: 100%;
                        border: 1px solid var(--sp-border);
                        border-radius: 12px;
                        padding: 12px 14px;
                        background: #f8fbff;
                        color: var(--sp-text);
                    }
                    .ttbm-schedule-row {
                        display: flex;
                        gap: 12px;
                        flex-wrap: wrap;
                        align-items: center;
                    }
                    .ttbm-schedule-days,
                    .ttbm-schedule-modes,
                    .ttbm-schedule-pills,
                    .ttbm-schedule-draft-pills,
                    .ttbm-schedule-preview-times {
                        display: flex;
                        gap: 8px;
                        flex-wrap: wrap;
                    }
                    .ttbm-schedule-days button,
                    .ttbm-schedule-modes button,
                    .ttbm-schedule-pills button,
                    .ttbm-schedule-rule-remove,
                    .ttbm-schedule-action {
                        border: 1px solid var(--sp-border);
                        background: var(--sp-surface);
                        color: var(--sp-text);
                        cursor: pointer;
                    }
                    .ttbm-schedule-days button {
                        width: 42px;
                        height: 42px;
                        border-radius: 999px;
                        font-weight: 700;
                    }
                    .ttbm-schedule-days button.active {
                        background: var(--sp-accent);
                        border-color: var(--sp-accent);
                        color: #111827;
                    }
                    .ttbm-schedule-days button.disabled,
                    .ttbm-schedule-days button:disabled {
                        opacity: 0.38;
                        cursor: not-allowed;
                        background: #f3f6fc;
                        color: var(--sp-muted);
                        border-color: var(--sp-border);
                    }
                    .ttbm-schedule-modes button {
                        border-radius: 999px;
                        padding: 9px 16px;
                        font-weight: 600;
                    }
                    .ttbm-schedule-modes button.active-add {
                        color: var(--sp-primary);
                        border-color: rgba(29, 78, 216, 0.24);
                        background: var(--sp-primary-soft);
                    }
                    .ttbm-schedule-modes button.active-remove {
                        color: var(--sp-danger);
                        border-color: rgba(220, 38, 38, 0.24);
                        background: var(--sp-danger-soft);
                    }
                    .ttbm-schedule-modes button.active-cancel {
                        color: var(--sp-accent);
                        border-color: rgba(217, 119, 6, 0.24);
                        background: var(--sp-accent-soft);
                    }
                    .ttbm-schedule-pills button,
                    .ttbm-schedule-draft-pills span {
                        padding: 8px 14px;
                        border-radius: 999px;
                        background: #f6f8fc;
                        font-weight: 600;
                    }
                    .ttbm-schedule-pills button.active-remove {
                        border-color: rgba(220, 38, 38, 0.28);
                        background: var(--sp-danger-soft);
                        color: var(--sp-danger);
                        text-decoration: line-through;
                    }
                    .ttbm-schedule-pills button.active-add {
                        border-color: rgba(29, 78, 216, 0.28);
                        background: var(--sp-primary-soft);
                        color: var(--sp-primary);
                    }
                    .ttbm-schedule-draft-pills span {
                        display: inline-flex;
                        gap: 8px;
                        align-items: center;
                    }
                    .ttbm-schedule-draft-pills button {
                        border: 0;
                        background: transparent;
                        color: inherit;
                        cursor: pointer;
                        padding: 0;
                    }
                    .ttbm-schedule-toggle {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 12px;
                        padding: 14px 16px;
                        border-radius: 14px;
                        border: 1px dashed rgba(220, 38, 38, 0.35);
                        background: #fff6f6;
                    }
                    .ttbm-schedule-toggle strong {
                        color: var(--sp-danger);
                    }
                    .ttbm-schedule-toggle button {
                        width: 54px;
                        height: 30px;
                        border: 0;
                        border-radius: 999px;
                        background: #cbd5e1;
                        position: relative;
                        cursor: pointer;
                    }
                    .ttbm-schedule-toggle button::after {
                        content: "";
                        position: absolute;
                        top: 3px;
                        left: 3px;
                        width: 24px;
                        height: 24px;
                        border-radius: 50%;
                        background: #fff;
                        transition: transform 0.18s ease;
                    }
                    .ttbm-schedule-toggle button.active {
                        background: var(--sp-danger);
                    }
                    .ttbm-schedule-toggle button.active::after {
                        transform: translateX(24px);
                    }
                    .ttbm-schedule-notice {
                        padding: 12px 14px;
                        border-radius: 12px;
                        border: 1px solid #bfdbfe;
                        background: #eff6ff;
                        color: var(--sp-primary);
                        margin-bottom: 16px;
                    }
                    .ttbm-schedule-preview {
                        margin-top: 18px;
                        border-radius: 16px;
                        border: 1px solid var(--sp-border);
                        background: #f8fbff;
                        padding: 18px;
                    }
                    .ttbm-schedule-preview h4 {
                        margin-bottom: 14px;
                        font-size: 13px;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                        color: var(--sp-muted);
                    }
                    .ttbm-schedule-preview-item {
                        display: flex;
                        gap: 12px;
                        align-items: flex-start;
                        padding: 10px 0;
                        border-top: 1px solid var(--sp-border);
                    }
                    .ttbm-schedule-preview-item:first-child {
                        border-top: 0;
                        padding-top: 0;
                    }
                    .ttbm-schedule-preview-date {
                        min-width: 130px;
                        font-weight: 700;
                        color: var(--sp-primary);
                    }
                    .ttbm-schedule-badge {
                        display: inline-flex;
                        align-items: center;
                        padding: 5px 10px;
                        border-radius: 999px;
                        font-size: 12px;
                        font-weight: 600;
                        border: 1px solid transparent;
                    }
                    .ttbm-schedule-badge.keep {
                        color: var(--sp-success);
                        background: rgba(5, 150, 105, 0.12);
                        border-color: rgba(5, 150, 105, 0.24);
                    }
                    .ttbm-schedule-badge.add {
                        color: var(--sp-primary);
                        background: var(--sp-primary-soft);
                        border-color: rgba(29, 78, 216, 0.22);
                    }
                    .ttbm-schedule-badge.remove,
                    .ttbm-schedule-badge.cancel {
                        color: var(--sp-danger);
                        background: var(--sp-danger-soft);
                        border-color: rgba(220, 38, 38, 0.22);
                    }
                    .ttbm-schedule-actions {
                        margin-top: 18px;
                        display: flex;
                        gap: 10px;
                        flex-wrap: wrap;
                    }
                    .ttbm-schedule-action {
                        border-radius: 12px;
                        padding: 10px 16px;
                        font-weight: 700;
                    }
                    .ttbm-schedule-action.primary {
                        background: var(--sp-primary);
                        border-color: var(--sp-primary);
                        color: #fff;
                    }
                    .ttbm-schedule-rule {
                        padding: 18px;
                        border-radius: 16px;
                        background: var(--sp-surface);
                        border: 1px solid var(--sp-border);
                        display: flex;
                        justify-content: space-between;
                        gap: 16px;
                        align-items: flex-start;
                    }
                    .ttbm-schedule-rule + .ttbm-schedule-rule {
                        margin-top: 12px;
                    }
                    .ttbm-schedule-rule h4 {
                        margin-bottom: 4px;
                    }
                    .ttbm-schedule-rule p {
                        margin: 0;
                        color: var(--sp-muted);
                    }
                    .ttbm-schedule-rule-remove {
                        border-radius: 10px;
                        padding: 8px 14px;
                        color: var(--sp-danger);
                        background: var(--sp-danger-soft);
                    }
                    .ttbm-schedule-empty {
                        border: 1px dashed var(--sp-border);
                        border-radius: 16px;
                        padding: 36px 18px;
                        text-align: center;
                        color: var(--sp-muted);
                        background: rgba(255, 255, 255, 0.7);
                    }
                    .ttbm-schedule-footer {
                        margin-top: 20px;
                        display: flex;
                        justify-content: space-between;
                        gap: 16px;
                        align-items: center;
                        color: var(--sp-muted);
                    }
                    @media (max-width: 782px) {
                        .ttbm-schedule-header,
                        .ttbm-schedule-footer,
                        .ttbm-schedule-rule,
                        .ttbm-schedule-toggle {
                            flex-direction: column;
                            align-items: stretch;
                        }
                        .ttbm-schedule-summary {
                            min-width: 0;
                        }
                    }
                </style>
                <script>
                    window.TTBMSchedulePlannerStrings = {
                        noDatePreview: <?php echo wp_json_encode(__('Select a date to preview the final schedule.', 'tour-booking-manager')); ?>,
                        fullDayCancelled: <?php echo wp_json_encode(__('Full day cancelled', 'tour-booking-manager')); ?>,
                        noActiveSlots: <?php echo wp_json_encode(__('No active slots', 'tour-booking-manager')); ?>,
                        pickDate: <?php echo wp_json_encode(__('Pick a Date', 'tour-booking-manager')); ?>,
                        selectDate: <?php echo wp_json_encode(__('Select Date', 'tour-booking-manager')); ?>,
                        baseScheduleLoaded: <?php echo wp_json_encode(__('Base schedule loaded for this date.', 'tour-booking-manager')); ?>,
                        cancelEntireDay: <?php echo wp_json_encode(__('Cancel entire day', 'tour-booking-manager')); ?>,
                        cancelHelp: <?php echo wp_json_encode(__('Use this when the whole date should disappear from booking.', 'tour-booking-manager')); ?>,
                        existingSlots: <?php echo wp_json_encode(__('Existing slots - click to mark for removal', 'tour-booking-manager')); ?>,
                        noBaseSlots: <?php echo wp_json_encode(__('No base slots found. Add a custom slot below.', 'tour-booking-manager')); ?>,
                        addNewSlot: <?php echo wp_json_encode(__('Add New Slot', 'tour-booking-manager')); ?>,
                        addSlot: <?php echo wp_json_encode(__('Add Slot', 'tour-booking-manager')); ?>,
                        previewTitle: <?php echo wp_json_encode(__('Preview - Final Schedule', 'tour-booking-manager')); ?>,
                        saveRule: <?php echo wp_json_encode(__('Save Rule', 'tour-booking-manager')); ?>,
                        reset: <?php echo wp_json_encode(__('Reset', 'tour-booking-manager')); ?>,
                        dateRangeWeekdays: <?php echo wp_json_encode(__('Date Range and Weekdays', 'tour-booking-manager')); ?>,
                        startDate: <?php echo wp_json_encode(__('Start Date', 'tour-booking-manager')); ?>,
                        endDate: <?php echo wp_json_encode(__('End Date', 'tour-booking-manager')); ?>,
                        appliesToWeekdays: <?php echo wp_json_encode(__('Applies to weekdays', 'tour-booking-manager')); ?>,
                        actionType: <?php echo wp_json_encode(__('Action type', 'tour-booking-manager')); ?>,
                        addSlots: <?php echo wp_json_encode(__('Add Slots', 'tour-booking-manager')); ?>,
                        removeSlots: <?php echo wp_json_encode(__('Remove Slots', 'tour-booking-manager')); ?>,
                        cancelDays: <?php echo wp_json_encode(__('Cancel Days', 'tour-booking-manager')); ?>,
                        existingSlotsShort: <?php echo wp_json_encode(__('Existing slots', 'tour-booking-manager')); ?>,
                        noBaseSlotsConfigured: <?php echo wp_json_encode(__('No base slots are configured yet.', 'tour-booking-manager')); ?>,
                        addCustomSlots: <?php echo wp_json_encode(__('Add custom slots', 'tour-booking-manager')); ?>,
                        affectedDatesPreview: <?php echo wp_json_encode(__('Affected dates preview', 'tour-booking-manager')); ?>,
                        selectRangePreview: <?php echo wp_json_encode(__('Select a range to preview matching dates.', 'tour-booking-manager')); ?>,
                        saveBulkRule: <?php echo wp_json_encode(__('Save Bulk Rule', 'tour-booking-manager')); ?>,
                        noRulesYet: <?php echo wp_json_encode(__('No planner rules yet. Save a single-date or bulk rule and it will appear here.', 'tour-booking-manager')); ?>,
                        singleDateCancel: <?php echo wp_json_encode(__('Single Date Cancel', 'tour-booking-manager')); ?>,
                        singleDateOverride: <?php echo wp_json_encode(__('Single Date Override', 'tour-booking-manager')); ?>,
                        bulkDayCancel: <?php echo wp_json_encode(__('Bulk Day Cancel', 'tour-booking-manager')); ?>,
                        bulkSlotRemoval: <?php echo wp_json_encode(__('Bulk Slot Removal', 'tour-booking-manager')); ?>,
                        bulkSlotAddition: <?php echo wp_json_encode(__('Bulk Slot Addition', 'tour-booking-manager')); ?>,
                        cancelled: <?php echo wp_json_encode(__('Cancelled', 'tour-booking-manager')); ?>,
                        allDays: <?php echo wp_json_encode(__('All days', 'tour-booking-manager')); ?>,
                        remove: <?php echo wp_json_encode(__('Remove', 'tour-booking-manager')); ?>
                    };
                </script>
                <script>
                    (function () {
                        if (window.TTBMSchedulePlannerLoaded) {
                            window.TTBMSchedulePlannerBoot && window.TTBMSchedulePlannerBoot();
                            return;
                        }
                        window.TTBMSchedulePlannerLoaded = true;
                    }());
                </script>
				<?php
			}
		}
		new TTBM_Settings_Schedule_Planner();
	}
