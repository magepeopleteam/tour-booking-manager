<?php
if (!defined('ABSPATH')) {
	die;
}

if (!class_exists('TTBM_Easy_Planner')) {
	class TTBM_Easy_Planner {
		private $tab_id = 'ttbm_settings_easy_planner';

		public function __construct() {
			add_action('ttbm_meta_box_tab_name', [$this, 'tab_name']);
			add_action('ttbm_meta_box_tab_content', [$this, 'tab_content']);
			add_action('ttbm_settings_save', [$this, 'save_settings'], 20);
			add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
			add_action('ttbm_time_select', [$this, 'render_time_select'], 10, 2);
			add_action('wp_ajax_get_ttbm_time_slots', [$this, 'ajax_time_slots']);
			add_action('wp_ajax_nopriv_get_ttbm_time_slots', [$this, 'ajax_time_slots']);
		}

		public function tab_name($tour_id) {
			if (!$tour_id) {
				return;
			}
			?>
			<li data-tabs-target="#<?php echo esc_attr($this->tab_id); ?>">
				<i class="mi mi-calendar-range"></i>
				<span><?php esc_html_e('Schedule Planner', 'tour-booking-manager'); ?></span>
			</li>
			<?php
		}

		public function tab_content($tour_id) {
			if (!$tour_id) {
				return;
			}

			$planner_state = $this->get_planner_state($tour_id);
			$travel_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_type', 'fixed');
			$time_status = TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no');
			?>
			<div class="tabsItem ttbm-easy-planner-tab" data-tabs="#<?php echo esc_attr($this->tab_id); ?>">
				<?php if ($travel_type !== 'repeated') : ?>
					<div class="ttbm-easy-planner-admin-notice">
						<?php esc_html_e('This planner is designed for repeated tours. Switch the tour type to Repeated if you want date-specific slot rules to affect frontend booking dates and times.', 'tour-booking-manager'); ?>
					</div>
				<?php endif; ?>

				<?php if ($time_status === 'no') : ?>
					<div class="ttbm-easy-planner-admin-notice is-warning">
						<?php esc_html_e('Time slot selection is currently disabled in the existing Date Configuration tab. Saving planner slot rules will enable time slots automatically.', 'tour-booking-manager'); ?>
					</div>
				<?php endif; ?>

				<div class="ttbm-easy-planner" data-tour-id="<?php echo esc_attr($tour_id); ?>">
					<input type="hidden" name="ttbm_planner_enabled" value="0" class="ttbm-easy-planner-enabled"/>
					<input type="hidden" name="ttbm_planner_payload" value="" class="ttbm-easy-planner-payload"/>
					<script type="application/json" class="ttbm-easy-planner-state"><?php echo wp_json_encode($planner_state); ?></script>

					<div class="ttbm-easy-planner__header">
						<div class="ttbm-easy-planner__eyebrow"><?php esc_html_e('Tour Management', 'tour-booking-manager'); ?></div>
						<h3 class="ttbm-easy-planner__title"><?php esc_html_e('Schedule Planner', 'tour-booking-manager'); ?></h3>
						<p class="ttbm-easy-planner__subtitle"><?php esc_html_e('Manage tour time slots: add, remove, cancel, or bulk-edit by date range.', 'tour-booking-manager'); ?></p>
					</div>

					<div class="ttbm-easy-planner__tabs" role="tablist" aria-label="<?php esc_attr_e('Planner views', 'tour-booking-manager'); ?>">
						<button type="button" class="ttbm-easy-planner__tab is-active" data-planner-tab="single"><?php esc_html_e('Single Date', 'tour-booking-manager'); ?></button>
						<button type="button" class="ttbm-easy-planner__tab" data-planner-tab="bulk"><?php esc_html_e('Bulk / Date Range', 'tour-booking-manager'); ?></button>
						<button type="button" class="ttbm-easy-planner__tab" data-planner-tab="rules"><?php esc_html_e('Scheduled Rules', 'tour-booking-manager'); ?></button>
					</div>

					<div class="ttbm-easy-planner__pane is-active" data-planner-pane="single">
						<div class="ttbm-easy-planner__card">
							<div class="ttbm-easy-planner__card-title">
								<span class="ttbm-easy-planner__dot"></span>
								<?php esc_html_e('Pick a Date', 'tour-booking-manager'); ?>
							</div>
							<div class="ttbm-easy-planner__field-row">
								<div class="ttbm-easy-planner__field">
									<label for="ttbm-easy-planner-single-date-<?php echo esc_attr($tour_id); ?>"><?php esc_html_e('Select Date', 'tour-booking-manager'); ?></label>
									<input type="hidden" id="ttbm-easy-planner-single-date-hidden-<?php echo esc_attr($tour_id); ?>" data-role="single-date"/>
									<input type="text" id="ttbm-easy-planner-single-date-<?php echo esc_attr($tour_id); ?>" class="ttbm-easy-planner__input" data-role="single-date-display" readonly/>
								</div>
							</div>

							<div class="ttbm-easy-planner__single-panel" data-role="single-panel" hidden>
								<div class="ttbm-easy-planner__notice" data-role="single-notice"></div>

								<div class="ttbm-easy-planner__toggle-row">
									<div>
										<div class="ttbm-easy-planner__toggle-title"><?php esc_html_e('Cancel entire day', 'tour-booking-manager'); ?></div>
										<div class="ttbm-easy-planner__toggle-text"><?php esc_html_e('Cancels all tours on this date.', 'tour-booking-manager'); ?></div>
									</div>
									<button type="button" class="ttbm-easy-planner__toggle" data-role="single-cancel-toggle" aria-pressed="false"></button>
								</div>

								<div class="ttbm-easy-planner__slot-panel" data-role="single-slot-panel">
									<div class="ttbm-easy-planner__section-label"><?php esc_html_e('Choose Action', 'tour-booking-manager'); ?></div>
									<div class="ttbm-easy-planner__mode-row">
										<button type="button" class="ttbm-easy-planner__mode is-active-add" data-role="single-mode" data-mode="add"><?php esc_html_e('+ Add Slot', 'tour-booking-manager'); ?></button>
										<button type="button" class="ttbm-easy-planner__mode" data-role="single-mode" data-mode="remove"><?php esc_html_e('x Remove Slot', 'tour-booking-manager'); ?></button>
									</div>

									<div class="ttbm-easy-planner__section-label"><?php esc_html_e('Existing Slots', 'tour-booking-manager'); ?></div>
									<div class="ttbm-easy-planner__pill-row" data-role="single-pills"></div>

									<div class="ttbm-easy-planner__divider"></div>

									<div class="ttbm-easy-planner__section-label"><?php esc_html_e('Add New Slot', 'tour-booking-manager'); ?></div>
									<div class="ttbm-easy-planner__inline-row">
										<input type="text" class="ttbm-easy-planner__input ttbm-easy-planner__time-input" maxlength="5" placeholder="<?php esc_attr_e('e.g. 13:00', 'tour-booking-manager'); ?>" data-role="single-new-time"/>
										<button type="button" class="ttbm-easy-planner__button is-primary is-small" data-role="single-add-time"><?php esc_html_e('+ Add', 'tour-booking-manager'); ?></button>
									</div>
									<div class="ttbm-easy-planner__pill-row" data-role="single-new-pills"></div>
								</div>

								<div class="ttbm-easy-planner__preview">
									<h4><?php esc_html_e('Preview - Final Schedule', 'tour-booking-manager'); ?></h4>
									<div data-role="single-preview"></div>
								</div>

								<div class="ttbm-easy-planner__actions">
									<button type="button" class="ttbm-easy-planner__button is-primary" data-role="single-save"><?php esc_html_e('Save Rule', 'tour-booking-manager'); ?></button>
									<button type="button" class="ttbm-easy-planner__button is-ghost" data-role="single-reset"><?php esc_html_e('Reset', 'tour-booking-manager'); ?></button>
								</div>
							</div>
						</div>
					</div>

					<div class="ttbm-easy-planner__pane" data-planner-pane="bulk">
						<div class="ttbm-easy-planner__card">
							<div class="ttbm-easy-planner__card-title">
								<span class="ttbm-easy-planner__dot"></span>
								<?php esc_html_e('Date Range & Weekdays', 'tour-booking-manager'); ?>
							</div>
							<div class="ttbm-easy-planner__field-row is-two-column">
								<div class="ttbm-easy-planner__field">
									<label for="ttbm-easy-planner-bulk-start-<?php echo esc_attr($tour_id); ?>"><?php esc_html_e('Start Date', 'tour-booking-manager'); ?></label>
									<input type="hidden" id="ttbm-easy-planner-bulk-start-hidden-<?php echo esc_attr($tour_id); ?>" data-role="bulk-start"/>
									<input type="text" id="ttbm-easy-planner-bulk-start-<?php echo esc_attr($tour_id); ?>" class="ttbm-easy-planner__input" data-role="bulk-start-display" readonly/>
								</div>
								<div class="ttbm-easy-planner__field">
									<label for="ttbm-easy-planner-bulk-end-<?php echo esc_attr($tour_id); ?>"><?php esc_html_e('End Date', 'tour-booking-manager'); ?></label>
									<input type="hidden" id="ttbm-easy-planner-bulk-end-hidden-<?php echo esc_attr($tour_id); ?>" data-role="bulk-end"/>
									<input type="text" id="ttbm-easy-planner-bulk-end-<?php echo esc_attr($tour_id); ?>" class="ttbm-easy-planner__input" data-role="bulk-end-display" readonly/>
								</div>
							</div>

							<div class="ttbm-easy-planner__section-label"><?php esc_html_e('Applies to Weekdays', 'tour-booking-manager'); ?></div>
							<div class="ttbm-easy-planner__weekday-row" data-role="bulk-weekdays">
								<?php foreach ($this->planner_weekdays() as $index => $day) : ?>
									<button type="button" class="ttbm-easy-planner__weekday" data-day="<?php echo esc_attr($index); ?>"><?php echo esc_html($day); ?></button>
								<?php endforeach; ?>
							</div>

							<div class="ttbm-easy-planner__divider"></div>

							<div class="ttbm-easy-planner__section-label"><?php esc_html_e('Action Type', 'tour-booking-manager'); ?></div>
							<div class="ttbm-easy-planner__mode-row">
								<button type="button" class="ttbm-easy-planner__mode is-active-add" data-role="bulk-mode" data-mode="add"><?php esc_html_e('+ Add Slots', 'tour-booking-manager'); ?></button>
								<button type="button" class="ttbm-easy-planner__mode" data-role="bulk-mode" data-mode="remove"><?php esc_html_e('x Remove Slots', 'tour-booking-manager'); ?></button>
								<button type="button" class="ttbm-easy-planner__mode" data-role="bulk-mode" data-mode="cancel"><?php esc_html_e('Cancel Days', 'tour-booking-manager'); ?></button>
							</div>

							<div class="ttbm-easy-planner__bulk-slot-panel" data-role="bulk-slot-panel">
								<div class="ttbm-easy-planner__section-label"><?php esc_html_e('Existing Slots', 'tour-booking-manager'); ?></div>
								<div class="ttbm-easy-planner__pill-row" data-role="bulk-pills"></div>

								<div class="ttbm-easy-planner__divider"></div>

								<div class="ttbm-easy-planner__section-label"><?php esc_html_e('Custom Slots', 'tour-booking-manager'); ?></div>
								<div class="ttbm-easy-planner__inline-row">
									<input type="text" class="ttbm-easy-planner__input ttbm-easy-planner__time-input" maxlength="5" placeholder="<?php esc_attr_e('e.g. 14:00', 'tour-booking-manager'); ?>" data-role="bulk-new-time"/>
									<button type="button" class="ttbm-easy-planner__button is-primary is-small" data-role="bulk-add-time"><?php esc_html_e('+ Add', 'tour-booking-manager'); ?></button>
								</div>
								<div class="ttbm-easy-planner__pill-row" data-role="bulk-new-pills"></div>
							</div>

							<div class="ttbm-easy-planner__preview">
								<h4><?php esc_html_e('Affected Dates Preview', 'tour-booking-manager'); ?></h4>
								<div data-role="bulk-preview"></div>
							</div>

							<div class="ttbm-easy-planner__actions">
								<button type="button" class="ttbm-easy-planner__button is-primary" data-role="bulk-save"><?php esc_html_e('Save Bulk Rule', 'tour-booking-manager'); ?></button>
								<button type="button" class="ttbm-easy-planner__button is-ghost" data-role="bulk-reset"><?php esc_html_e('Reset', 'tour-booking-manager'); ?></button>
							</div>
						</div>
					</div>

					<div class="ttbm-easy-planner__pane" data-planner-pane="rules">
						<div class="ttbm-easy-planner__rules" data-role="rules-list"></div>
						<div class="ttbm-easy-planner__empty" data-role="rules-empty"><?php esc_html_e('No rules yet. Add a single-date or bulk rule.', 'tour-booking-manager'); ?></div>
					</div>

					<div class="ttbm-easy-planner__savebar">
						<div class="ttbm-easy-planner__savebar-count">
							<?php esc_html_e('Pending rules:', 'tour-booking-manager'); ?>
							<strong data-role="pending-count">0</strong>
						</div>
						<div class="ttbm-easy-planner__savebar-actions">
							<button type="button" class="ttbm-easy-planner__button is-ghost is-small" data-role="clear-all"><?php esc_html_e('Clear All', 'tour-booking-manager'); ?></button>
							<button type="button" class="ttbm-easy-planner__button is-primary" data-role="apply-all"><?php esc_html_e('Apply All Rules', 'tour-booking-manager'); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		public function enqueue_assets($hook) {
			if (!$this->is_tour_edit_screen($hook)) {
				return;
			}

			wp_enqueue_style(
				'ttbm-easy-planner-fonts',
				'https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap',
				[],
				null
			);
			wp_enqueue_style(
				'ttbm-easy-planner',
				TTBM_PLUGIN_URL . '/assets/admin/ttbm_easy_planner.css',
				['ttbm_admin', 'ttbm-easy-planner-fonts'],
				filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm_easy_planner.css')
			);
			wp_enqueue_script(
				'ttbm-easy-planner',
				TTBM_PLUGIN_URL . '/assets/admin/ttbm_easy_planner.js',
				[],
				filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm_easy_planner.js'),
				true
			);
		}

		public function save_settings($tour_id) {
			if (get_post_type($tour_id) !== TTBM_Function::get_cpt_name()) {
				return;
			}

			$planner_enabled = isset($_POST['ttbm_planner_enabled']) ? sanitize_text_field(wp_unslash($_POST['ttbm_planner_enabled'])) : '0';
			if ($planner_enabled !== '1') {
				return;
			}

			$raw_payload = isset($_POST['ttbm_planner_payload']) ? wp_unslash($_POST['ttbm_planner_payload']) : '';
			if (!$raw_payload || !is_string($raw_payload)) {
				return;
			}

			$payload = json_decode($raw_payload, true);
			if (!is_array($payload)) {
				return;
			}

			$off_date_map = [];
			$payload_off_dates = isset($payload['offDates']) && is_array($payload['offDates']) ? $payload['offDates'] : [];
			foreach ($payload_off_dates as $off_date) {
				$formatted_date = self::sanitize_date_value($off_date);
				if ($formatted_date) {
					$off_date_map[$formatted_date] = $formatted_date;
				}
			}

			$payload_rules = isset($payload['dateRules']) && is_array($payload['dateRules']) ? $payload['dateRules'] : [];
			$special_dates = [];
			foreach ($payload_rules as $date => $slots) {
				$formatted_date = self::sanitize_date_value($date);
				if (!$formatted_date) {
					continue;
				}

				$normalized_slots = self::normalize_slots_value($slots);
				if (empty($normalized_slots)) {
					$off_date_map[$formatted_date] = $formatted_date;
					continue;
				}

				unset($off_date_map[$formatted_date]);
				$special_dates[] = [
					'date_label' => sprintf(
						/* translators: %s: date */
						esc_html__('Planner %s', 'tour-booking-manager'),
						$formatted_date
					),
					'start_date' => $formatted_date,
					'end_date' => $formatted_date,
					'time' => $normalized_slots,
				];
			}

			ksort($off_date_map);
			$off_dates = [];
			foreach ($off_date_map as $off_date) {
				$off_dates[] = ['mep_ticket_off_date' => $off_date];
			}

			update_post_meta($tour_id, 'mep_ticket_off_dates', $off_dates);
			update_post_meta($tour_id, 'ttbm_special_date_info', $special_dates);

			if (!empty($special_dates)) {
				update_post_meta($tour_id, 'mep_disable_ticket_time', 'yes');
			}
		}

		public function render_time_select($tour_id, $date) {
			echo self::get_time_select_markup($tour_id, $date); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		public function ajax_time_slots() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce')) {
				wp_die();
			}

			$tour_id = isset($_POST['tour_id']) ? absint($_POST['tour_id']) : 0;
			$date = isset($_POST['tour_date']) ? sanitize_text_field(wp_unslash($_POST['tour_date'])) : '';
			echo self::get_time_select_markup($tour_id, $date); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die();
		}

		public static function get_slots_for_date($tour_id, $date) {
			$date = self::sanitize_date_value($date);
			if (!$tour_id || !$date) {
				return [];
			}

			$all_off_dates = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_off_dates', []);
			if (is_array($all_off_dates)) {
				foreach ($all_off_dates as $off_date) {
					if (!empty($off_date['mep_ticket_off_date']) && self::sanitize_date_value($off_date['mep_ticket_off_date']) === $date) {
						return [];
					}
				}
			}

			$date_rules = self::get_saved_date_rules($tour_id);
			if (array_key_exists($date, $date_rules)) {
				return $date_rules[$date];
			}

			return self::get_base_slots_for_date($tour_id, $date);
		}

		private static function get_time_select_markup($tour_id, $date) {
			$date = self::sanitize_date_value($date);
			$slots = self::get_slots_for_date($tour_id, $date);
			if (!$tour_id || !$date) {
				return '';
			}

			ob_start();
			?>
			<div class="time_select_box">
				<input type="hidden" name="ttbm_select_time" value=""/>
				<?php if (empty($slots)) : ?>
					<span class="ttbm-time-error" style="display:block;width:100%;">
						<?php esc_html_e('No time slots available for this date.', 'tour-booking-manager'); ?>
					</span>
				<?php else : ?>
					<?php foreach ($slots as $slot) : ?>
						<?php
						$time = isset($slot['mep_ticket_time']) ? sanitize_text_field((string) $slot['mep_ticket_time']) : '';
						$label = isset($slot['mep_ticket_time_name']) ? sanitize_text_field((string) $slot['mep_ticket_time_name']) : $time;
						if (!$time) {
							continue;
						}
						?>
						<button type="button" class="customRadio button_type" data-role="ttbm-time-choice" data-date-time="<?php echo esc_attr($date . ' ' . $time); ?>">
							<?php echo esc_html($label); ?>
						</button>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php
			return ob_get_clean();
		}

		private function get_planner_state($tour_id) {
			return [
				'tourId' => (int) $tour_id,
				'travelType' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_type', 'fixed'),
				'isProActive' => true,
				'availableDates' => $this->get_available_dates($tour_id),
				'baseSlots' => $this->get_base_slots($tour_id),
				'offDays' => $this->get_off_days($tour_id),
				'offDates' => $this->get_off_dates($tour_id),
				'dateRules' => $this->get_date_rules($tour_id),
			];
		}

		private function get_base_slots($tour_id) {
			$slots = [
				'default' => [],
				'sun' => [],
				'mon' => [],
				'tue' => [],
				'wed' => [],
				'thu' => [],
				'fri' => [],
				'sat' => [],
			];

			foreach (self::time_slot_meta_map() as $key => $meta_key) {
				$slots[$key] = self::normalize_slots_value(TTBM_Global_Function::get_post_info($tour_id, $meta_key, []));
			}

			return $slots;
		}

		private function get_off_days($tour_id) {
			$raw_off_days = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_offdays', []);
			if (!is_array($raw_off_days)) {
				$maybe_unserialized = @unserialize($raw_off_days);
				if (is_array($maybe_unserialized)) {
					$raw_off_days = $maybe_unserialized;
				} elseif (is_string($raw_off_days)) {
					$raw_off_days = array_filter(array_map('trim', explode(',', $raw_off_days)));
				} else {
					$raw_off_days = [];
				}
			}

			$mapped = [];
			foreach ($raw_off_days as $off_day) {
				$short_key = self::normalize_day_key_value($off_day);
				if ($short_key) {
					$mapped[] = $short_key;
				}
			}

			return array_values(array_unique($mapped));
		}

		private function get_off_dates($tour_id) {
			$all_off_dates = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_off_dates', []);
			$off_dates = [];

			if (!is_array($all_off_dates)) {
				return $off_dates;
			}

			foreach ($all_off_dates as $off_date) {
				if (!empty($off_date['mep_ticket_off_date'])) {
					$formatted_date = self::sanitize_date_value($off_date['mep_ticket_off_date']);
					if ($formatted_date) {
						$off_dates[] = $formatted_date;
					}
				}
			}

			sort($off_dates);
			return array_values(array_unique($off_dates));
		}

		private function get_date_rules($tour_id) {
			return self::get_saved_date_rules($tour_id);
		}

		private function get_available_dates($tour_id) {
			$dates = [];
			$all_dates = TTBM_Function::get_date($tour_id);

			if (is_array($all_dates) && array_key_exists('date', $all_dates)) {
				$all_dates = [$all_dates['date']];
			}

			if (is_array($all_dates)) {
				foreach ($all_dates as $date) {
					$formatted_date = self::sanitize_date_value($date);
					if ($formatted_date) {
						$dates[$formatted_date] = $formatted_date;
					}
				}
			}

			foreach ($this->get_off_dates($tour_id) as $off_date) {
				$dates[$off_date] = $off_date;
			}

			foreach (array_keys($this->get_date_rules($tour_id)) as $rule_date) {
				$dates[$rule_date] = $rule_date;
			}

			ksort($dates);
			return array_values($dates);
		}

		private static function get_saved_date_rules($tour_id) {
			$special_dates = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_special_date_info', []);
			$date_rules = [];

			if (!is_array($special_dates)) {
				return $date_rules;
			}

			foreach ($special_dates as $special_date) {
				$start_date = !empty($special_date['start_date']) ? self::sanitize_date_value($special_date['start_date']) : '';
				$end_date = !empty($special_date['end_date']) ? self::sanitize_date_value($special_date['end_date']) : '';
				$slots = !empty($special_date['time']) ? self::normalize_slots_value($special_date['time']) : [];

				if (!$start_date || !$end_date || empty($slots)) {
					continue;
				}

				foreach (self::expand_date_range_static($start_date, $end_date) as $date) {
					$date_rules[$date] = $slots;
				}
			}

			ksort($date_rules);
			return $date_rules;
		}

		private static function get_base_slots_for_date($tour_id, $date) {
			$date = self::sanitize_date_value($date);
			if (!$tour_id || !$date) {
				return [];
			}

			$day_key = strtolower(gmdate('D', strtotime($date)));
			$meta_map = self::time_slot_meta_map();
			$day_meta = array_key_exists($day_key, $meta_map) ? $meta_map[$day_key] : '';
			$day_slots = $day_meta ? self::normalize_slots_value(TTBM_Global_Function::get_post_info($tour_id, $day_meta, [])) : [];
			if (!empty($day_slots)) {
				return $day_slots;
			}

			return self::normalize_slots_value(TTBM_Global_Function::get_post_info($tour_id, $meta_map['default'], []));
		}

		private static function time_slot_meta_map() {
			return [
				'default' => 'mep_ticket_times_global',
				'sun' => 'mep_ticket_times_sun',
				'mon' => 'mep_ticket_times_mon',
				'tue' => 'mep_ticket_times_tue',
				'wed' => 'mep_ticket_times_wed',
				'thu' => 'mep_ticket_times_thu',
				'fri' => 'mep_ticket_times_fri',
				'sat' => 'mep_ticket_times_sat',
			];
		}

		private static function expand_date_range_static($start_date, $end_date) {
			$dates = [];

			try {
				$current = new DateTimeImmutable($start_date);
				$end = new DateTimeImmutable($end_date);
			} catch (Exception $exception) {
				return $dates;
			}

			if ($current > $end) {
				return $dates;
			}

			while ($current <= $end) {
				$dates[] = $current->format('Y-m-d');
				$current = $current->modify('+1 day');
			}

			return $dates;
		}

		private static function normalize_slots_value($slots) {
			$normalized = [];
			$seen = [];

			if (!is_array($slots)) {
				return $normalized;
			}

			foreach ($slots as $slot) {
				if (!is_array($slot)) {
					continue;
				}

				$time = isset($slot['mep_ticket_time']) ? sanitize_text_field((string) $slot['mep_ticket_time']) : '';
				$label = isset($slot['mep_ticket_time_name']) ? sanitize_text_field((string) $slot['mep_ticket_time_name']) : '';
				if (!self::is_valid_time_value($time) || isset($seen[$time])) {
					continue;
				}

				$seen[$time] = true;
				$normalized[] = [
					'mep_ticket_time_name' => $label ?: $time,
					'mep_ticket_time' => $time,
					'label' => $label ?: $time,
					'time' => $time,
				];
			}

			usort($normalized, function ($left, $right) {
				return strcmp($left['mep_ticket_time'], $right['mep_ticket_time']);
			});

			return $normalized;
		}

		private function planner_weekdays() {
			return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		}

		private static function normalize_day_key_value($day_key) {
			$day_key = strtolower(trim((string) $day_key));
			$map = [
				'monday' => 'mon',
				'tuesday' => 'tue',
				'wednesday' => 'wed',
				'thursday' => 'thu',
				'friday' => 'fri',
				'saturday' => 'sat',
				'sunday' => 'sun',
				'mon' => 'mon',
				'tue' => 'tue',
				'wed' => 'wed',
				'thu' => 'thu',
				'fri' => 'fri',
				'sat' => 'sat',
				'sun' => 'sun',
			];

			return array_key_exists($day_key, $map) ? $map[$day_key] : '';
		}

		private static function sanitize_date_value($date) {
			$date = sanitize_text_field((string) $date);
			$timestamp = strtotime($date);
			if (!$timestamp) {
				return '';
			}

			return gmdate('Y-m-d', $timestamp);
		}

		private static function is_valid_time_value($time) {
			return (bool) preg_match('/^\d{2}:\d{2}$/', (string) $time);
		}

		private function is_tour_edit_screen($hook) {
			if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
				return false;
			}

			$screen = get_current_screen();
			if (!$screen) {
				return false;
			}

			return $screen->post_type === TTBM_Function::get_cpt_name();
		}
	}

	new TTBM_Easy_Planner();
}
