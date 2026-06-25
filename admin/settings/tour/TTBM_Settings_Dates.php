<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Dates')) {
		class TTBM_Settings_Dates {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'date_tab']);
			}
			public function date_tab($tour_id) {
				$tour_label = TTBM_Function::get_name();
				$travel_type = TTBM_Function::travel_type_array();
				$date_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_type', 'fixed');
				?>
                <div class="tabsItem ttbm_settings_dates" data-tabs="#ttbm_settings_dates">
                    <h2><?php esc_html_e('Date Configuration', 'tour-booking-manager'); ?></h2>
                    <p class="info_text"><?php esc_html_e('Tour type and date time can be easily configured here, providing a crucial feature for recurring, fixed, or specific date tours.', 'tour-booking-manager') ?></p>
                    <section>
                        <?php
                        $travel_type_cards = array(
                            'fixed' => array(
                                'icon' => 'fa-calendar',
                                'desc' => __('Pre-set schedule for regular occurrences.', 'tour-booking-manager'),
                            ),
                            'particular' => array(
                                'icon' => 'fa-calendar-day',
                                'desc' => __('One-off dates selected specifically for this tour.', 'tour-booking-manager'),
                            ),
                            'repeated' => array(
                                'icon' => 'fa-calendar-week',
                                'desc' => __('Automated recurrence based on a set frequency.', 'tour-booking-manager'),
                            ),
                        );
                        ?>
                        <div class="groupRadioBox ttbm-tour-type-selector" data-active-type="<?php echo esc_attr($date_type); ?>">
                            <input type="hidden" value="<?php echo esc_attr($date_type); ?>" name="ttbm_travel_type"/>
                            <div class="ttbm-tour-type-selector__label">
                                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                <span><?php echo esc_html($tour_label) . ' ' . esc_html__('Type', 'tour-booking-manager'); ?></span>
                            </div>
                            <div class="ttbm-date-type-cards">
                                <?php foreach ($travel_type as $key => $value) {
                                    $card = $travel_type_cards[$key] ?? array('icon' => 'fa-calendar', 'desc' => '');
                                    ?>
                                    <button data-collapse-radio="<?php echo esc_attr('#ttbm_' . $key); ?>" class="ttbm-date-type-card <?php echo esc_attr($key == $date_type ? 'active' : ''); ?>" type="button" data-group-radio="<?php echo esc_attr($key); ?>">
                                        <span class="ttbm-date-type-card__icon"><i class="fas <?php echo esc_attr($card['icon']); ?>" aria-hidden="true"></i></span>
                                        <span class="ttbm-date-type-card__title"><?php echo esc_html($value); ?></span>
                                        <span class="ttbm-date-type-card__desc"><?php echo esc_html($card['desc']); ?></span>
                                        <span class="ttbm-date-type-card__check" aria-hidden="true"><i class="fas fa-check"></i></span>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                        <?php $this->fixed_date($tour_id, $date_type); ?>
                        <?php $this->particular_dates($tour_id, $date_type); ?>
                        
						<?php $this->repeat_dates($tour_id, $date_type); ?>
                    </section>
					<?php $this->repeat_optional($tour_id, $date_type); ?>
                </div>
				<?php
			}
			public function fixed_date($tour_id, $date_type) {
				$date_format = TTBM_Global_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$start_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date');
				$hidden_start_date = $start_date ? gmdate('Y-m-d', strtotime($start_date)) : '';
				$visible_start_date = $start_date ? date_i18n($date_format, strtotime($start_date)) : '';
				$start_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_time');
				$end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_date');
				$end_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_time');
				$hidden_end_date = $end_date ? gmdate('Y-m-d', strtotime($end_date)) : '';
				$visible_end_date = $end_date ? date_i18n($date_format, strtotime($end_date)) : '';
				$reg_end = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date');
				$hidden_reg_end_date = $reg_end ? gmdate('Y-m-d', strtotime($reg_end)) : '';
				$visible_reg_end_date = $reg_end ? date_i18n($date_format, strtotime($reg_end)) : '';
				$reg_end_time = TTBM_Global_Function::get_post_info($tour_id, 'reg_end_time');
				?>
                <div data-target-radio="#ttbm_fixed" class="ttbm-date-target-panel <?php echo esc_attr($date_type == 'fixed' ? 'mActive' : ''); ?>">
                    <div class="gptLayout ttbm-date-config-panel ttbm-fixed-dates-panel">
                        <div class="ttbm-fixed-dates-header">
                            <h6 class="ttbm-fixed-dates-header__title">
                                <i class="fas fa-sliders-h" aria-hidden="true"></i>
                                <?php esc_html_e('Date Management', 'tour-booking-manager'); ?>
                            </h6>
                        </div>
                        <p id="ttbm_fixed_dates_error" class="textRequired ttbm-date-panel-error" style="display:none;"></p>
                        <div class="ttbm-fixed-date-grid">
                            <div class="ttbm-fixed-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('Start Date', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="fixed">*</sup></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--date">
                                    <input type="hidden" name="ttbm_travel_start_date" value="<?php echo esc_attr($hidden_start_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_start_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                            <div class="ttbm-fixed-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('End Date', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="fixed">*</sup></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--date">
                                    <input type="hidden" name="ttbm_travel_end_date" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                            <div class="ttbm-fixed-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('Registration End Date', 'tour-booking-manager'); ?></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--date">
                                    <input type="hidden" name="ttbm_travel_reg_end_date" value="<?php echo esc_attr($hidden_reg_end_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_reg_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                            <div class="ttbm-fixed-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('Start Time', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="fixed">*</sup></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--time">
                                    <input type="time" name="ttbm_travel_start_time" class="formControl ttbm_travel_start_time" value="<?php echo esc_attr($start_time); ?>" onclick="document.querySelectorAll('.ttbm_travel_repeated_start_time').forEach(function(input){input.removeAttribute('name');});"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                            <div class="ttbm-fixed-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('End Time', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="fixed">*</sup></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--time ttbm-input-icon--time-end">
                                    <input type="time" name="ttbm_travel_end_time" class="formControl" value="<?php echo esc_attr($end_time); ?>"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                            <div class="ttbm-fixed-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('Registration End Time', 'tour-booking-manager'); ?></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--time ttbm-input-icon--time-reg">
                                    <input type="time" name="reg_end_time" class="formControl" value="<?php echo esc_attr($reg_end_time); ?>"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function particular_dates($tour_id, $date_type) {
				$particular_date_lists = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_particular_dates', array());
				if (!is_array($particular_date_lists)) {
					$particular_date_lists = array();
				}
				$configured_count = 0;
				foreach ($particular_date_lists as $particular_date) {
					if (is_array($particular_date) && !empty($particular_date['ttbm_particular_start_date'])) {
						$configured_count++;
					}
				}
				$has_saved_rows = false;
				foreach ($particular_date_lists as $particular_date) {
					if ($particular_date) {
						$has_saved_rows = true;
						break;
					}
				}
				?>
                <div data-target-radio="#ttbm_particular" class="ttbm-date-target-panel ttbm_settings_area _mT <?php echo esc_attr($date_type == 'particular' ? 'mActive' : ''); ?>">
                    <div class="gptLayout ttbm-date-config-panel ttbm-particular-dates-panel">
                        <div class="ttbm-particular-dates-header">
                            <div class="ttbm-particular-dates-header__title">
                                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                                <span><?php esc_html_e('Date Management', 'tour-booking-manager'); ?></span>
                            </div>
                            <span class="ttbm-particular-dates-header__badge">
                                <i class="fas fa-info-circle" aria-hidden="true"></i>
								<?php
								echo esc_html(
									sprintf(
										/* translators: %d: number of configured particular dates */
										_n('%d Entry Configured', '%d Entries Configured', $configured_count, 'tour-booking-manager'),
										$configured_count
									)
								);
								?>
                            </span>
                        </div>
                        <p id="ttbm_particular_dates_error" class="textRequired ttbm-date-panel-error" style="display:none;"></p>
                        <div class="ttbm_item_insert ttbm_sortable_area ttbm-particular-dates-list">
							<?php
							if ($has_saved_rows) {
								foreach ($particular_date_lists as $particular_date) {
									if ($particular_date) {
										self::particular_date_item($particular_date);
									}
								}
							} else {
								self::particular_date_item();
							}
							?>
                        </div>
						<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add New Particular Date', 'tour-booking-manager'), 'ttbm_add_item', 'ttbm-particular-dates-add-btn', 'fas fa-plus'); ?>
                    </div>
                    <div class="ttbm_hidden_content">
                        <div class="ttbm_hidden_item">
							<?php self::particular_date_item(); ?>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function repeat_dates($tour_id, $date_type) {
				$date_format = TTBM_Global_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$start_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_date');
				$start_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_time');
				if ($start_time === '') {
					$start_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_time');
				}
				$hidden_start_date = $start_date ? gmdate('Y-m-d', strtotime($start_date)) : '';
				$visible_start_date = $start_date ? date_i18n($date_format, strtotime($start_date)) : '';
				$end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_end_date');
				$hidden_end_date = $end_date ? gmdate('Y-m-d', strtotime($end_date)) : '';
				$visible_end_date = $end_date ? date_i18n($date_format, strtotime($end_date)) : '';
				$repeated_after = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_after', 1);
				$repeat_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_repeat_type', 'fixed');
				$repeat_number = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_repeat_number', 1);
				$repeat_array = TTBM_Function::travel_repeat_array();
				?>
                <div data-target-radio="#ttbm_repeated" class="ttbm-date-target-panel _mT <?php echo esc_attr($date_type == 'repeated' ? 'mActive' : ''); ?>">
                    <div class="gptLayout ttbm-date-config-panel">
                        <p id="ttbm_repeated_dates_error" class="textRequired ttbm-date-panel-error" style="display:none;"></p>
                        <div class="ttbm-repeat-pattern">
                            <span class="ttbm-field-label"><?php esc_html_e('Tour Repeat Pattern', 'tour-booking-manager'); ?></span>
                            <div class="groupRadioBox ttbm-pill-group">
                                <div class="ttbm-pill-row">
									<?php foreach ($repeat_array as $key => $value) { ?>
                                        <button class="ttbm-pill-btn <?php echo esc_attr($key == $repeated_after ? 'active' : ''); ?>" type="button" data-group-radio="<?php echo esc_attr($key); ?>">
                                            <span class="ttbm-pill-btn__check" aria-hidden="true"><i class="fas fa-check"></i></span>
                                            <?php echo esc_html($value); ?>
                                        </button>
									<?php } ?>
                                    <span class="ttbm-pill-custom-wrap">
                                        <button class="ttbm-pill-btn input_active <?php echo esc_attr((1 == $repeated_after || 7 == $repeated_after || 30 == $repeated_after) ? '' : 'active'); ?>" type="button" data-group-radio="<?php echo esc_attr($repeated_after); ?>" data-pill-custom="1">
                                            <span class="ttbm-pill-btn__check" aria-hidden="true"><i class="fas fa-check"></i></span>
                                            <span class="ttbm-pill-btn__label"><?php esc_html_e('Custom', 'tour-booking-manager'); ?></span>
                                        </button>
                                        <input type="number" min="1" class="formControl xs _w_75 ttbm_number_validation ttbm-pill-custom-input <?php echo esc_attr((1 == $repeated_after || 7 == $repeated_after || 30 == $repeated_after) ? 'dNone' : ''); ?>" value="<?php echo esc_attr($repeated_after); ?>" name="ttbm_travel_repeated_after"/>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="ttbm-date-datetime-grid">
                            <div class="ttbm-datetime-col ttbm-repeated-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('Start Date', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="repeated">*</sup></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--date">
                                    <input type="hidden" name="ttbm_travel_repeated_start_date" value="<?php echo esc_attr($hidden_start_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_start_date); ?>" class="formControl date_type ttbm-repeated-start-date" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                            <div class="ttbm-datetime-col ttbm-repeated-date-field">
                                <span class="ttbm-field-label"><?php esc_html_e('Start Time', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="repeated">*</sup></span>
								<?php self::datetime_clear_wrap_open(); ?>
                                <label class="ttbm-input-icon ttbm-input-icon--time">
                                    <input type="time" name="ttbm_travel_repeated_start_time" class="formControl ttbm_travel_repeated_start_time" value="<?php echo esc_attr($start_time); ?>" onclick="document.querySelectorAll('input.ttbm_travel_start_time[name=&quot;ttbm_travel_start_time&quot;]').forEach(function(input){input.removeAttribute('name');});"/>
                                </label>
								<?php self::datetime_clear_wrap_close(); ?>
                            </div>
                        </div>

                        <div class="ttbm-repeat-end ttbm-repeated-date-field">
                            <span class="ttbm-field-label"><?php esc_html_e('End Repeat Logic', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="repeated">*</sup></span>
                            <div class="groupRadioBox ttbm-radio-group">
                                <input type="hidden" value="<?php echo esc_attr($repeat_type); ?>" name="ttbm_repeat_type"/>
                                <div class="ttbm-radio-row">
                                    <button class="ttbm-radio-btn <?php echo esc_attr('continue' == $repeat_type ? 'active' : ''); ?>" type="button" data-group-radio="continue"><?php esc_html_e('Never', 'tour-booking-manager'); ?></button>
                                    <button class="ttbm-radio-btn input_active <?php echo esc_attr('occurrence' == $repeat_type ? 'active' : ''); ?>" type="button" data-group-radio="occurrence">
                                        <span class="ttbm-radio-btn__label"><?php esc_html_e('After Occurrence', 'tour-booking-manager'); ?></span>
                                        <input type="number" class="formControl xs ttbm_number_validation ttbm-radio-btn__input <?php echo esc_attr('occurrence' == $repeat_type ? '' : 'dNone'); ?>" value="<?php echo esc_attr($repeat_number); ?>" name="ttbm_repeat_number" placeholder="1"/>
                                    </button>
                                    <button class="ttbm-radio-btn input_active <?php echo esc_attr('fixed' == $repeat_type ? 'active' : ''); ?>" type="button" data-group-radio="fixed">
                                        <span class="ttbm-radio-btn__label"><?php esc_html_e('On Date', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark ttbm-repeated-end-date-required" data-ttbm-date-required="repeated">*</sup></span>
										<?php self::datetime_clear_wrap_open( 'ttbm-repeat-end-date-field' ); ?>
                                            <input type="hidden" name="ttbm_travel_repeated_end_date" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                                            <input value="<?php echo esc_attr($visible_end_date); ?>" class="formControl date_type ttbm-radio-btn__input ttbm-repeated-end-date <?php echo esc_attr('fixed' == $repeat_type ? '' : 'dNone'); ?>" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
										<?php self::datetime_clear_wrap_close(); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function repeat_optional($tour_id, $date_type) {
				$display_time = TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no');
				$active_time = $display_time == 'no' ? '' : 'mActive';
				$checked_time = $display_time == 'no' ? '' : 'checked';
				?>
                <div data-target-radio="#ttbm_repeated" class="<?php echo esc_attr($date_type == 'repeated' ? 'mActive' : ''); ?>">
                    <section>

                        <div class="gptLayout ttbm-ticket-time ttbm-time-slots-panel">
                            <div class="ttbm-time-slots-header">
                                <div class="ttbm-time-slots-header__title">
                                    <i class="fas fa-stopwatch" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Tour Time Slots', 'tour-booking-manager'); ?></span>
                                </div>
                                <div class="ttbm-time-slots-header__toggle">
                                    <span class="ttbm-time-slots-header__toggle-label"><?php esc_html_e('Enabled', 'tour-booking-manager'); ?></span>
                                    <?php TTBM_Custom_Layout::switch_button('mep_disable_ticket_time', $checked_time); ?>
                                </div>
                            </div>
                            <div data-collapse="#mep_disable_ticket_time" class="ttbm-time-slots-body _mT <?php echo esc_attr($active_time); ?>">
                                <?php
                                $all_time_slot_infos = self::time_slot_array();
                                if (sizeof($all_time_slot_infos) > 0) {
                                    ?>
                                    <div class="ttbmTabs ttbm-time-slots-tabs _mT">
                                        <ul class="tabLists ttbm-time-slots-tablists">
                                            <?php foreach ($all_time_slot_infos as $key => $value) { ?>
                                                <li data-tabs-target="#<?php echo esc_attr($key); ?>"><?php echo esc_html(array_key_exists('type', $value) && $value['type'] ? $value['type'] : ''); ?></li>
                                            <?php } ?>
                                            <?php do_action('ttbm_time_slot_tab'); ?>
                                        </ul>
                                        <div class="tabsContent ttbm-time-slots-tab-content">
                                            <?php
                                                foreach ($all_time_slot_infos as $key => $value) {
                                                    $default_times = TTBM_Global_Function::get_post_info($tour_id, $key, array());
                                                    $title = array_key_exists('title', $value) && $value['title'] ? $value['title'] : '';
                                                    $des = array_key_exists('des', $value) && $value['des'] ? $value['des'] : '';
                                                    $label_key = array_key_exists('label_key', $value) && $value['label_key'] ? $value['label_key'] : '';
                                                    $time_key = array_key_exists('time_key', $value) && $value['time_key'] ? $value['time_key'] : '';
                                                    ?>
                                                    <div class="tabsItem ttbm-time-slots-tab-pane _mT" data-tabs="#<?php echo esc_attr($key); ?>">

                                                        <div class="ttbm_settings_area ttbm-time-slots-tab-panel">
                                                            <div class="ttbm_item_insert ttbm_sortable_area ttbm-time-slots-list">
                                                                <?php
                                                                    if (sizeof($default_times)) {
                                                                        foreach ($default_times as $default_time) {
                                                                            if (is_array($default_time) && sizeof($default_time) > 0) {
                                                                                self::time_slot_item($label_key, $time_key, $default_time);
                                                                            }
                                                                        }
                                                                    }
                                                                ?>
                                                            </div>
                                                            <?php TTBM_Custom_Layout::add_new_button(esc_html__('Add Slot', 'tour-booking-manager'), 'ttbm_add_item', 'ttbm-time-slots-add-btn', 'fas fa-plus'); ?>
                                                            <div class="ttbm_hidden_content">
                                                                <div class="ttbm_hidden_item">
                                                                    <?php self::time_slot_item($label_key, $time_key); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php do_action('ttbm_time_slot_content', $tour_id); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </section>
                    <?php $this->off_schedule($tour_id); ?>
                </div>
				<?php
			}
			public function off_schedule($tour_id) {
				$enable_off_schedule = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_enable_off_schedule', 'no');
				$active_off_schedule = $enable_off_schedule === 'yes' ? 'mActive' : '';
				$checked_off_schedule = $enable_off_schedule === 'yes' ? 'checked' : '';
				?>
                    <section>
                        <div class="gptLayout ttbm-off-schedule-panel">
                            <div class="ttbm-off-schedule-header">
                                <div class="ttbm-off-schedule-header__title">
                                    <i class="fas fa-calendar-times" aria-hidden="true"></i>
                                    <span><?php esc_html_e('Tour Off Days And Dates', 'tour-booking-manager'); ?></span>
                                </div>
                                <div class="ttbm-off-schedule-header__toggle">
                                    <span class="ttbm-off-schedule-header__toggle-label"><?php esc_html_e('Enabled', 'tour-booking-manager'); ?></span>
									<?php TTBM_Custom_Layout::switch_button('ttbm_enable_off_schedule', $checked_off_schedule); ?>
                                </div>
                            </div>
                            <div data-collapse="#ttbm_enable_off_schedule" class="ttbm-off-schedule-body _mT <?php echo esc_attr($active_off_schedule); ?>">
                            <div class="ttbmTabs ttbm-off-schedule-tabs">
                                <ul class="tabLists ttbm-off-schedule-tablists">
                                    <li data-tabs-target="#ttbm_off_days"><?php esc_html_e('Off Days', 'tour-booking-manager'); ?></li>
                                    <li data-tabs-target="#ttbm_off_dates"><?php esc_html_e('Off Dates', 'tour-booking-manager'); ?></li>
                                </ul>
                                <div class="tabsContent ttbm-off-schedule-tab-content _mT">
                                    <div class="tabsItem" data-tabs="#ttbm_off_days">
										<?php
											$off_day_array = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_offdays');
											if (!is_array($off_day_array)) {
												$maybe_unserialized = @unserialize($off_day_array, ['allowed_classes' => false]);
												if (is_array($maybe_unserialized)) {
													$off_day_array = $maybe_unserialized;
												} else {
													$off_day_array = explode(',', (string)$off_day_array);
												}
											}
											$off_days = $off_day_array ? implode(',', $off_day_array) : '';
											$days = TTBM_Global_Function::week_day();
											$day_short_labels = array(
												'mon' => __('Mon', 'tour-booking-manager'),
												'tue' => __('Tue', 'tour-booking-manager'),
												'wed' => __('Wed', 'tour-booking-manager'),
												'thu' => __('Thu', 'tour-booking-manager'),
												'fri' => __('Fri', 'tour-booking-manager'),
												'sat' => __('Sat', 'tour-booking-manager'),
												'sun' => __('Sun', 'tour-booking-manager'),
											);
										?>
                                        <div class="ttbm-off-schedule-tip">
                                            <span class="ttbm-off-schedule-tip__icon" aria-hidden="true"><i class="fas fa-info-circle"></i></span>
                                            <div class="ttbm-off-schedule-tip__content">
                                                <strong class="ttbm-off-schedule-tip__title"><?php esc_html_e('Pro Tip: Recurring Blocks', 'tour-booking-manager'); ?></strong>
                                                <p class="ttbm-off-schedule-tip__text"><?php esc_html_e('Selecting recurring off-days automatically blocks booking slots for those days across all future tours. This ensures consistent downtime for your team without manual day-by-day intervention.', 'tour-booking-manager'); ?></p>
                                            </div>
                                        </div>
                                        <div class="groupCheckBox ttbm-off-days-grid">
                                            <input type="hidden" name="mep_ticket_offdays" value="<?php echo esc_attr($off_days); ?>"/>
											<?php foreach ($days as $key => $day) {
												$short_label = $day_short_labels[$key] ?? $day;
												?>
                                                <label class="customCheckboxLabel ttbm-off-day-card" title="<?php echo esc_attr($day); ?>">
                                                    <input type="checkbox" <?php echo esc_attr(in_array($key, $off_day_array) ? 'checked' : ''); ?> data-checked="<?php echo esc_attr($key); ?>"/>
                                                    <span class="ttbm-off-day-card__mark" aria-hidden="true"></span>
                                                    <span class="customCheckbox ttbm-off-day-card__label"><?php echo esc_html($short_label); ?></span>
                                                </label>
											<?php } ?>
                                        </div>
                                    </div>
                                    <div class="tabsItem" data-tabs="#ttbm_off_dates">
                                        <div class="ttbm-off-schedule-tip ttbm-off-schedule-tip--dates">
                                            <span class="ttbm-off-schedule-tip__icon" aria-hidden="true"><i class="fas fa-info-circle"></i></span>
                                            <div class="ttbm-off-schedule-tip__content">
                                                <strong class="ttbm-off-schedule-tip__title"><?php esc_html_e('Pro Tip: Specific Date Blocks', 'tour-booking-manager'); ?></strong>
                                                <p class="ttbm-off-schedule-tip__text"><?php esc_html_e('Use off dates for holidays, maintenance windows, or one-time closures. These dates override your regular schedule without affecting recurring off-days.', 'tour-booking-manager'); ?></p>
                                            </div>
                                        </div>
                                        <div class="ttbm_settings_area ttbm-off-dates-area">
                                            <div class="ttbm_item_insert ttbm_sortable_area ttbm-off-dates-list">
												<?php
													$all_off_dates = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_off_dates', array());
													$off_dates = array();
													foreach ($all_off_dates as $off_date) {
														$off_dates[] = $off_date['mep_ticket_off_date'];
													}
													if (sizeof($off_dates)) {
														foreach ($off_dates as $off_date) {
															if ($off_date) {
																self::off_date_item($off_date);
															}
														}
													} else {
														self::off_date_item();
													}
												?>
                                            </div>
											<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add Off Date', 'tour-booking-manager'), 'ttbm_add_item', 'ttbm-off-dates-add-btn', 'fas fa-plus'); ?>
                                            <div class="ttbm_hidden_content">
                                                <div class="ttbm_hidden_item">
													<?php self::off_date_item(); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </section>
				<?php
			}
			public function off_date_item($date = '') {
				$date_format = TTBM_Global_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$hidden_date = $date ? date_i18n('Y-m-d', strtotime($date)) : '';
				$visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
				?>
                <div class="ttbm_remove_area ttbm-off-date-card _mT_xs">
                    <div class="ttbm-off-date-card__inner">
                        <?php self::datetime_clear_wrap_open( 'ttbm-off-date-card__field' ); ?>
                        <label class="ttbm-input-icon ttbm-input-icon--date">
                            <input type="hidden" name="mep_ticket_off_dates[]" value="<?php echo esc_attr($hidden_date); ?>"/>
                            <input value="<?php echo esc_attr($visible_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
                        </label>
						<?php self::datetime_clear_wrap_close(); ?>
                        <div class="ttbm-off-date-card__actions">
                            <div class="ttbm-off-date-card__sort ttbm_sortable_button" type="" title="<?php esc_attr_e('Drag to reorder', 'tour-booking-manager'); ?>">
                                <span class="fas fa-expand-arrows-alt mp_zero" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public static function particular_date_item($particular_info = []) {
				$date_format = TTBM_Global_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$date = array_key_exists('ttbm_particular_start_date', $particular_info) ? $particular_info['ttbm_particular_start_date'] : '';
				$end_date = array_key_exists('ttbm_particular_end_date', $particular_info) ? $particular_info['ttbm_particular_end_date'] : '';
				$time = array_key_exists('ttbm_particular_start_time', $particular_info) ? $particular_info['ttbm_particular_start_time'] : '';
				$hidden_date = $date ? date_i18n('Y-m-d', strtotime($date)) : '';
				$visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
				$hidden_end_date = $end_date ? date_i18n('Y-m-d', strtotime($end_date)) : '';
				$visible_end_date = $end_date ? date_i18n($date_format, strtotime($end_date)) : '';
				?>
                <div class="ttbm_remove_area ttbm-particular-date-card _mT_xs">
                    <div class="ttbm-particular-date-card__inner">
                        <div class="ttbm-particular-date-card__drag ttbm_sortable_button" type="" title="<?php esc_attr_e('Drag to reorder', 'tour-booking-manager'); ?>">
                            <span class="ttbm-particular-date-card__drag-icon" aria-hidden="true"></span>
                        </div>
                        <div class="ttbm-particular-date-card__field ttbm-particular-date-card__field--checkin-date">
                            <span class="ttbm-particular-date-field-label"><?php esc_html_e('Check in Date', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="particular">*</sup></span>
							<?php self::datetime_clear_wrap_open(); ?>
                            <label class="ttbm-input-icon ttbm-input-icon--date">
                                <input type="hidden" name="ttbm_particular_start_date[]" value="<?php echo esc_attr($hidden_date); ?>"/>
                                <input value="<?php echo esc_attr($visible_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
                            </label>
							<?php self::datetime_clear_wrap_close(); ?>
                        </div>
                        <div class="ttbm-particular-date-card__field ttbm-particular-date-card__field--checkin-time">
                            <span class="ttbm-particular-date-field-label"><?php esc_html_e('Check in Time', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="particular">*</sup></span>
							<?php self::datetime_clear_wrap_open(); ?>
                            <label class="ttbm-input-icon ttbm-input-icon--time">
                                <input type="time" name="ttbm_particular_start_time[]" class="formControl" value="<?php echo esc_attr($time); ?>"/>
                            </label>
							<?php self::datetime_clear_wrap_close(); ?>
                        </div>
                        <div class="ttbm-particular-date-card__field ttbm-particular-date-card__field--checkout-date">
                            <span class="ttbm-particular-date-field-label"><?php esc_html_e('Check Out Date', 'tour-booking-manager'); ?><sup class="textRequired ttbm-date-required-mark" data-ttbm-date-required="particular">*</sup></span>
							<?php self::datetime_clear_wrap_open(); ?>
                            <label class="ttbm-input-icon ttbm-input-icon--date">
                                <input type="hidden" name="ttbm_particular_end_date[]" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                                <input value="<?php echo esc_attr($visible_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>" autocomplete="off"/>
                            </label>
							<?php self::datetime_clear_wrap_close(); ?>
                        </div>
                        <div class="ttbm-particular-date-card__actions">
                            <div class="ttbm-particular-date-card__sort ttbm_sortable_button" type="" title="<?php esc_attr_e('Drag to reorder', 'tour-booking-manager'); ?>">
                                <span class="fas fa-expand-arrows-alt mp_zero" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public static function time_slot_item($label_key, $time_key, $default_time = []) {
				$label = array_key_exists('mep_ticket_time_name', $default_time) ? $default_time['mep_ticket_time_name'] : '';
				if ($label === '' && array_key_exists($label_key, $default_time)) {
					$label = $default_time[$label_key];
				}
				$time = array_key_exists('mep_ticket_time', $default_time) ? $default_time['mep_ticket_time'] : '';
				if ($time === '' && array_key_exists($time_key, $default_time)) {
					$time = $default_time[$time_key];
				}
				?>
                <div class="ttbm_remove_area ttbm-time-slot-card _mT_xs">
                    <div class="ttbm-time-slot-card__inner">
                        <div class="ttbm-time-slot-card__field ttbm-time-slot-card__field--name">
                            <span class="ttbm-time-slot-field-label"><?php esc_html_e('Slot Name', 'tour-booking-manager'); ?></span>
                            <input type="text" name="<?php echo esc_attr($label_key . '[]'); ?>" value="<?php echo esc_attr($label); ?>" class="formControl" placeholder="<?php esc_attr_e('Morning Session', 'tour-booking-manager'); ?>"/>
                        </div>
                        <div class="ttbm-time-slot-card__field ttbm-time-slot-card__field--time">
                            <span class="ttbm-time-slot-field-label"><?php esc_html_e('Start Time', 'tour-booking-manager'); ?></span>
							<?php self::datetime_clear_wrap_open( 'ttbm-time-slot-time-wrap' ); ?>
                            <label class="ttbm-time-slot-time-field">
                                <input type="time" name="<?php echo esc_attr($time_key . '[]'); ?>" class="formControl" value="<?php echo esc_attr($time); ?>" placeholder="00:00"/>
                            </label>
							<?php self::datetime_clear_wrap_close(); ?>
                        </div>
                        <div class="ttbm-time-slot-card__actions">
                            <div class="ttbm-time-slot-card__sort ttbm_sortable_button" type="" title="<?php esc_attr_e('Drag to reorder', 'tour-booking-manager'); ?>">
                                <span class="fas fa-expand-arrows-alt mp_zero" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			private static function datetime_clear_wrap_open( $extra_class = '' ) {
				$class = 'ttbm-datetime-clear-wrap ttbm-input-icon--has-clear';
				if ( $extra_class ) {
					$class .= ' ' . $extra_class;
				}
				echo '<span class="' . esc_attr( $class ) . '">';
			}
			private static function datetime_clear_wrap_close() {
				self::field_clear_button();
				echo '</span>';
			}
			private static function field_clear_button() {
				?>
				<span role="button" tabindex="0" class="ttbm-field-clear" title="<?php esc_attr_e( 'Clear', 'tour-booking-manager' ); ?>" aria-label="<?php esc_attr_e( 'Clear', 'tour-booking-manager' ); ?>">
					<i class="fas fa-times" aria-hidden="true"></i>
				</span>
				<?php
			}
			public static function time_slot_array() {
				return
					[
						'mep_ticket_times_global' => [
							'type' => __('Default', 'tour-booking-manager'),
							'title' => __('Default Time Slots Configuration', 'tour-booking-manager'),
							'des' => __('These tour time slots will apply to all dates unless overridden', 'tour-booking-manager'),
							'label_key' => 'mep_ticket_time_name',
							'time_key' => 'mep_ticket_time',
						],
						'mep_ticket_times_mon' => [
						'type' => __('Monday', 'tour-booking-manager'),
						'title' => __('Monday Ticket Times', 'tour-booking-manager'),
						'des' => __('Please Add Monday Ticket Times', 'tour-booking-manager'),
						'label_key' => 'mep_ticket_time_name_mon',
						'time_key' => 'mep_ticket_time_mon',
					], 'mep_ticket_times_tue' => [
						'type' => __('Tuesday', 'tour-booking-manager'),
						'title' => __('Tuesday Ticket Times', 'tour-booking-manager'),
						'des' => __('Please Add Tuesday Ticket Times', 'tour-booking-manager'),
						'label_key' => 'mep_ticket_time_name_tue',
						'time_key' => 'mep_ticket_time_tue',
					], 'mep_ticket_times_wed' => [
						'type' => __('Wednesday', 'tour-booking-manager'),
						'title' => __('Wednesday Ticket Times', 'tour-booking-manager'),
						'des' => __('Please Add Wednesday Ticket Times', 'tour-booking-manager'),
						'label_key' => 'mep_ticket_time_name_wed',
						'time_key' => 'mep_ticket_time_wed',
					], 'mep_ticket_times_thu' => [
						'type' => __('Thursday', 'tour-booking-manager'),
						'title' => __('Thursday Ticket Times', 'tour-booking-manager'),
						'des' => __('Please Add Thursday Ticket Times', 'tour-booking-manager'),
						'label_key' => 'mep_ticket_time_name_thu',
						'time_key' => 'mep_ticket_time_thu',
					], 'mep_ticket_times_fri' => [
						'type' => __('Friday', 'tour-booking-manager'),
						'title' => __('Friday Ticket Times', 'tour-booking-manager'),
						'des' => __('Please Add Friday Ticket Times', 'tour-booking-manager'),
						'label_key' => 'mep_ticket_time_name_fri',
						'time_key' => 'mep_ticket_time_fri',
					], 'mep_ticket_times_sat' => [
						'type' => __('Saturday', 'tour-booking-manager'),
						'title' => __('Saturday Ticket Times', 'tour-booking-manager'),
						'des' => __('Please Add Saturday Ticket Times', 'tour-booking-manager'),
						'label_key' => 'mep_ticket_time_name_sat',
						'time_key' => 'mep_ticket_time_sat',
					], 'mep_ticket_times_sun' => [
						'type' => __('Sunday', 'tour-booking-manager'),
						'title' => __('Sunday Ticket Times', 'tour-booking-manager'),
						'des' => __('Please Add Sunday Ticket Times', 'tour-booking-manager'),
						'label_key' => 'mep_ticket_time_name_sun',
						'time_key' => 'mep_ticket_time_sun',
					],
					];
			}
		}
		new TTBM_Settings_Dates();
	}
