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
                        <div class="ttbm-header">
                            <h4><i class="fas fa-calendar-days"></i><?php esc_html_e('Essential Date Configuration', 'tour-booking-manager'); ?></h4>
                        </div>
                        
                        <div class="groupRadioBox">
                            <input type="hidden" value="<?php echo esc_attr($date_type); ?>" name="ttbm_travel_type"/>
                            <h5><?php echo esc_html($tour_label) . ' ' . esc_html__('Type', 'tour-booking-manager'); ?></h5>
                            <div class="_dFlex_mT_xs">
                                <?php foreach ($travel_type as $key => $value) { ?>
                                    <button data-collapse-radio="<?php echo esc_attr('#ttbm_' . $key); ?>" class="_mpBtn_mR <?php echo esc_attr($key == $date_type ? 'active' : ''); ?>" type="button" data-group-radio="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></button>
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
				$start_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date_time');
				$end_date = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_date');
				$end_time = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_time');
				$hidden_end_date = $end_date ? gmdate('Y-m-d', strtotime($end_date)) : '';
				$visible_end_date = $end_date ? date_i18n($date_format, strtotime($end_date)) : '';
				$reg_end = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date');
				$hidden_reg_end_date = $reg_end ? gmdate('Y-m-d', strtotime($reg_end)) : '';
				$visible_reg_end_date = $reg_end ? date_i18n($date_format, strtotime($reg_end)) : '';
				$reg_end_time = TTBM_Global_Function::get_post_info($tour_id, 'reg_end_time');
				?>
                <div data-target-radio="#ttbm_fixed" class="<?php echo esc_attr($date_type == 'fixed' ? 'mActive' : ''); ?>">
                    <div class="gptLayout">
                        <div class="flexEqual">
                            <div class="fdColumn tLayout">
                                <h6><?php esc_html_e('Start Date & Time', 'tour-booking-manager'); ?></h6>
                                <label class="_mT_xs">
                                    <input type="hidden" name="ttbm_travel_start_date" value="<?php echo esc_attr($hidden_start_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_start_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                                </label>
                                <label class="_mT_xs">
                                    <input type="time" name="ttbm_travel_start_date_time" class="formControl" value="<?php echo esc_attr($start_time); ?>"/>
                                </label>
                            </div>
                            <div class="fdColumn tLayout">
                                <h6><?php esc_html_e('End Date & Time', 'tour-booking-manager'); ?></h6>
                                <label class="_mT_xs">
                                    <input type="hidden" name="ttbm_travel_end_date" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                                </label>
                                <label class="_mT_xs">
                                    <input type="time" name="ttbm_travel_end_time" class="formControl" value="<?php echo esc_attr($end_time); ?>"/>
                                </label>
                            </div>
                            <div class="fdColumn tLayout">
                                <h6><?php esc_html_e('Registration End Date & Time', 'tour-booking-manager'); ?></h6>
                                <label class="_mT_xs">
                                    <input type="hidden" name="ttbm_travel_reg_end_date" value="<?php echo esc_attr($hidden_reg_end_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_reg_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                                </label>
                                <label class="_mT_xs">
                                    <input type="time" name="reg_end_time" class="formControl" value="<?php echo esc_attr($reg_end_time); ?>"/>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function particular_dates($tour_id, $date_type) {
				$particular_date_lists = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_particular_dates', array());
				?>
                <div data-target-radio="#ttbm_particular" class="ttbm_settings_area _mT <?php echo esc_attr($date_type == 'particular' ? 'mActive' : ''); ?>">
                    <div class="gptLayout">
                        <div class="ttbm_item_insert ttbm_sortable_area">
                            <div class="_dFlex_mT_xs">
                                <h6 class="_w_300"><?php esc_html_e('Check in Date & Time', 'tour-booking-manager') ?></h6>
                                <h6 class="_w_200"><?php esc_html_e('Check Out Date', 'tour-booking-manager') ?></h6>
                                <h6 class="_w_100"><?php esc_html_e('Action', 'tour-booking-manager') ?></h6>
                            </div>
							<?php
								if (sizeof($particular_date_lists)) {
									foreach ($particular_date_lists as $particular_date) {
										if ($particular_date) {
											self::particular_date_item($particular_date);
										}
									}
								}
							?>
                        </div>
						<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add New Particular date', 'tour-booking-manager')); ?>
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
                <div data-target-radio="#ttbm_repeated" class="_mT <?php echo esc_attr($date_type == 'repeated' ? 'mActive' : ''); ?>">
                    <div class="gptLayout">
                        <div class="_mT">
                            <h6><?php esc_html_e('Tour Repeat Pattern', 'tour-booking-manager'); ?></h6>
                            <div class="groupRadioBox">
                                <div class="_dFlex_mT_xs">
									<?php foreach ($repeat_array as $key => $value) { ?>
                                        <button class="_mpBtn_mR <?php echo esc_attr($key == $repeated_after ? 'active' : ''); ?>" type="button" data-group-radio="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></button>
									<?php } ?>
                                    <button class="_mpBtn_mR input_active <?php echo esc_attr((1 == $repeated_after || 7 == $repeated_after || 30 == $repeated_after) ? '' : 'active'); ?>" type="button" data-group-radio="<?php echo esc_attr($repeated_after); ?>">
                                        <label class="allCenter">
                                            <span class="_mR_xs"><?php esc_html_e('Custom', 'tour-booking-manager'); ?></span>
                                            <input type="number" class="formControl xs _w_75 ttbm_number_validation <?php echo esc_attr((1 == $repeated_after || 7 == $repeated_after || 30 == $repeated_after) ? 'dNone' : ''); ?>" value="<?php echo esc_attr($repeated_after); ?>" name="ttbm_travel_repeated_after"/>
                                        </label>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="_dFlex_justifyBetween_mT">
                            <div class="_mT">
                                <h6><?php esc_html_e('Tour Start Date & Time', 'tour-booking-manager'); ?></h6>
                                <label class="_mT_xs">
                                    <input type="hidden" name="ttbm_travel_repeated_start_date" value="<?php echo esc_attr($hidden_start_date); ?>"/>
                                    <input value="<?php echo esc_attr($visible_start_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                                </label>
                                <label class="_mT_xs">
                                    <input type="time" name="ttbm_travel_repeated_start_time" class="formControl" value="<?php echo esc_attr($start_time); ?>"/>
                                </label>
                            </div>

                            <div class="_mT">
                                <h6><?php esc_html_e('End Repeat', 'tour-booking-manager'); ?></h6>
                                <div class="groupRadioBox">
                                    <input type="hidden" value="<?php echo esc_attr($repeat_type); ?>" name="ttbm_repeat_type"/>
                                    <div class="_dFlex_mT_xs">
                                        <button class="_mpBtn_mR  <?php echo esc_attr('continue' == $repeat_type ? 'active' : ''); ?>" type="button" data-group-radio="fixed"><?php esc_html_e('Never', 'tour-booking-manager'); ?></button>
                                        <button class="_mpBtn_mR input_active <?php echo esc_attr('occurrence' == $repeat_type ? 'active' : ''); ?>" type="button" data-group-radio="occurrence">
                                            <label class="allCenter">
                                                <span class="_mR_xs"><?php esc_html_e('After occurrence', 'tour-booking-manager'); ?></span>
                                                <input type="number" class="formControl xs _w_75 ttbm_number_validation <?php echo esc_attr('occurrence' == $repeat_type ? '' : 'dNone'); ?>" value="<?php echo esc_attr($repeat_number); ?>" name="ttbm_repeat_number"/>
                                            </label>
                                        </button>
                                        <button class="_mpBtn_mR input_active <?php echo esc_attr('fixed' == $repeat_type ? 'active' : ''); ?>" type="button" data-group-radio="fixed">
                                            <label class="allCenter">
                                                <span class="_mR_xs"><?php esc_html_e('On', 'tour-booking-manager'); ?></span>
                                                <input type="hidden" name="ttbm_travel_repeated_end_date" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                                                <input value="<?php echo esc_attr($visible_end_date); ?>" class="formControl xs date_type <?php echo esc_attr('fixed' == $repeat_type ? '' : 'dNone'); ?>" placeholder="<?php echo esc_attr($now); ?>"/>
                                            </label>
                                        </button>
                                    </div>
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
                        <div class="ttbm-header">
                            <h4><i class="fas fa-calendar-days"></i><?php esc_html_e('Optional Tour Configuration', 'tour-booking-manager'); ?></h4>
                        </div>
                        <div class="gptLayout">
                            <h6><?php esc_html_e('Tour Time Slots Configuration', 'tour-booking-manager'); ?><?php TTBM_Custom_Layout::switch_button('mep_disable_ticket_time', $checked_time); ?></h6>
                            <div data-collapse="#mep_disable_ticket_time" class="_mT <?php echo esc_attr($active_time); ?>">
								<?php
									$all_time_slot_infos = self::time_slot_array();
									if (sizeof($all_time_slot_infos) > 0) {
										?>
                                        <div class="ttbmTabs _mT">
                                            <ul class="tabLists">
												<?php foreach ($all_time_slot_infos as $key => $value) { ?>
                                                    <li data-tabs-target="#<?php echo esc_attr($key); ?>"><?php echo esc_html(array_key_exists('type', $value) && $value['type'] ? $value['type'] : ''); ?></li>
												<?php } ?>
												<?php do_action('ttbm_time_slot_tab'); ?>
                                            </ul>
                                            <div class="tabsContent">
												<?php
													foreach ($all_time_slot_infos as $key => $value) {
														$default_times = TTBM_Global_Function::get_post_info($tour_id, $key, array());
														$title = array_key_exists('title', $value) && $value['title'] ? $value['title'] : '';
														$des = array_key_exists('des', $value) && $value['des'] ? $value['des'] : '';
														$label_key = array_key_exists('label_key', $value) && $value['label_key'] ? $value['label_key'] : '';
														$time_key = array_key_exists('time_key', $value) && $value['time_key'] ? $value['time_key'] : '';
														?>
                                                        <div class="tabsItem _mT" data-tabs="#<?php echo esc_attr($key); ?>">
                                                           
                                                            <div class="ttbm_settings_area">
                                                                <div class="ttbm_item_insert ttbm_sortable_area">
                                                                    <div class="_dFlex">
                                                                        <h6 class="_w_200_mR_xs"><?php esc_html_e('Time Slot Label', 'tour-booking-manager') ?></h6>
                                                                        <h6 class="_w_100_mR_xs"><?php esc_html_e('Time', 'tour-booking-manager') ?></h6>
                                                                        <h6 class="_w_100"><?php esc_html_e('Action', 'tour-booking-manager') ?></h6>
                                                                    </div>
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
																<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add Slot', 'tour-booking-manager')); ?>
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
                        <div class="divider"></div>
                        <div class="gptLayout">
                            <h6><?php esc_html_e('Tour Off Days And Dates', 'tour-booking-manager'); ?></h6>
                            <div class="ttbmTabs _mT">
                                <ul class="tabLists">
                                    <li data-tabs-target="#ttbm_off_days"><?php esc_html_e('Off Days ', 'tour-booking-manager'); ?></li>
                                    <li data-tabs-target="#ttbm_off_dates"><?php esc_html_e('Off Dates ', 'tour-booking-manager'); ?></li>
                                </ul>
                                <div class="tabsContent _mT">
                                    <div class="tabsItem" data-tabs="#ttbm_off_days">
                                        <h6><?php esc_html_e('Select Off Days ', 'tour-booking-manager'); ?></h6>
										<?php
											$off_day_array = TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_offdays');
											if (!is_array($off_day_array)) {
												$maybe_unserialized = @unserialize($off_day_array);
												if (is_array($maybe_unserialized)) {
													$off_day_array = $maybe_unserialized;
												} else {
													$off_day_array = explode(',', (string)$off_day_array);
												}
											}
											$off_days = $off_day_array ? implode(',', $off_day_array) : '';
											$days = TTBM_Global_Function::week_day();
										?>
                                        <div class="groupCheckBox _mT">
                                            <input type="hidden" name="mep_ticket_offdays" value="<?php echo esc_attr($off_days); ?>"/>
											<?php foreach ($days as $key => $day) { ?>
                                                <label class="customCheckboxLabel ">
                                                    <input type="checkbox" <?php echo esc_attr(in_array($key, $off_day_array) ? 'checked' : ''); ?> data-checked="<?php echo esc_attr($key); ?>"/>
                                                    <span class="customCheckbox"><?php echo esc_html($day); ?></span>
                                                </label>
											<?php } ?>
                                        </div>
                                    </div>
                                    <div class="tabsItem" data-tabs="#ttbm_off_dates">
                                        <label><?php esc_html_e('Select Off Dates ', 'tour-booking-manager'); ?></label>
                                        <span class="info_text"><?php esc_html_e('Configure Tour Off Dates ', 'tour-booking-manager'); ?></span>
                                        <div class="ttbm_settings_area">
                                            <div class="ttbm_item_insert ttbm_sortable_area">
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
													}
												?>
                                            </div>
											<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add Off Date', 'tour-booking-manager')); ?>
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
                    </section>
                </div>
				<?php
			}
			public function off_date_item($date = '') {
				$date_format = TTBM_Global_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$hidden_date = $date ? date_i18n('Y-m-d', strtotime($date)) : '';
				$visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
				?>
                <div class="ttbm_remove_area _mT_xs">
                    <div class="groupContent">
                        <label>
                            <input type="hidden" name="mep_ticket_off_dates[]" value="<?php echo esc_attr($hidden_date); ?>"/>
                            <input value="<?php echo esc_attr($visible_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                        </label>
                        <div class="allCenter"><?php TTBM_Custom_Layout::move_remove_button(); ?></div>
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
                <div class="ttbm_remove_area _dFlex_mT_xs">
                    <div class="groupContent _w_300">
                        <label>
                            <input type="hidden" name="ttbm_particular_start_date[]" value="<?php echo esc_attr($hidden_date); ?>"/>
                            <input value="<?php echo esc_attr($visible_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                        </label>
                        <label>
                            <input type="time" name="ttbm_particular_start_time[]" class="formControl" value="<?php echo esc_attr($time); ?>"/>
                        </label>
                    </div>
                    <div class="_w_200">
                        <label>
                            <input type="hidden" name="ttbm_particular_end_date[]" value="<?php echo esc_attr($hidden_end_date); ?>"/>
                            <input value="<?php echo esc_attr($visible_end_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                        </label>
                    </div>
                    <div class="_w_100"><?php TTBM_Custom_Layout::move_remove_button(); ?></div>
                </div>
				<?php
			}
			public static function time_slot_item($label_key, $time_key, $default_time = []) {
				$label = array_key_exists('mep_ticket_time_name', $default_time) ? $default_time['mep_ticket_time_name'] : '';
				$time = array_key_exists('mep_ticket_time', $default_time) ? $default_time['mep_ticket_time'] : '';
				?>
                <div class="ttbm_remove_area _mT_xs">
                    <div class="_dFlex">
                        <label class="_w_200_mR_xs">
                            <input type="text" name="<?php echo esc_attr($label_key . '[]'); ?>" value="<?php echo esc_attr($label); ?>" class="formControl ttbm_name_validation" placeholder="<?php esc_attr_e('Time Slot Label', 'tour-booking-manager'); ?>"/>
                        </label>
                        <label class="_w_100_mR_xs">
                            <input type="time" name="<?php echo esc_attr($time_key . '[]'); ?>" class="formControl" value="<?php echo esc_attr($time); ?>" placeholder="00:00"/>
                        </label>
                        <div class="allCenter_w_100 "><?php TTBM_Custom_Layout::move_remove_button(); ?></div>
                    </div>
                </div>
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
					], 'mep_ticket_times_mon' => [
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
					],
					];
			}
		}
		new TTBM_Settings_Dates();
	}