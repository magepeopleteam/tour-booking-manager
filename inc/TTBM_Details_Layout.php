<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Details_Layout')) {
		class TTBM_Details_Layout {
			public function __construct() {
				add_action('ttbm_details_title', array($this, 'details_title'));
				add_action('ttbm_section_title', array($this, 'section_title'), 10, 2);
				add_action('ttbm_section_titles', array($this, 'section_titles'), 10, 2);
				add_action('ttbm_slider', array($this, 'slider'));
				add_action('ttbm_details_location', array($this, 'details_location'));
				add_action('ttbm_description', array($this, 'description'));
				add_action('ttbm_include_exclude', array($this, 'include_exclude'));
				add_action('ttbm_include_feature', array($this, 'include_feature'));
				add_action('ttbm_exclude_service', array($this, 'exclude_service'));
				add_action('ttbm_short_details', array($this, 'short_details'), 10);
				add_action('ttbm_location_map', array($this, 'location_map'), 10, 1);
				add_action('ttbm_smart_activity', array($this, 'smart_activity'));
				add_action('ttbm_activity', array($this, 'activity'));
				add_action('ttbm_hiphop_place', array($this, 'hiphop_place'));
				add_action('ttbm_day_wise_details', array($this, 'day_wise_details'));
				add_action('ttbm_faq', array($this, 'faq'));
				add_action('ttbm_why_choose_us', array($this, 'why_choose_us'));
				add_action('ttbm_sidebar_cta', array($this, 'sidebar_cta'));
				add_action('ttbm_get_a_question', array($this, 'get_a_question'));
				add_action('ttbm_tour_guide', array($this, 'tour_guide'));
				add_action('ttbm_details_particular_area', array($this, 'particular_area'));
				add_action('ttbm_related_tour', array($this, 'related_tour'));
				add_action('ttbm_dynamic_sidebar', array($this, 'dynamic_sidebar'), 10, 1);
				add_action('ttbm_registration', array($this, 'ticket_registration'));
				add_action('ttbm_smart_registration_controls', array($this, 'smart_registration_controls'));
				add_action('ttbm_smart_registration_panel', array($this, 'smart_registration_panel'));
				add_filter('ttbm_regular_ticket_file', array($this, 'smart_regular_ticket_file'), 10, 2);
				add_action('ttbm_travel_analytics_display', array($this, 'travel_analytics_display'), 10, 2);
			}
			public function smart_regular_ticket_file($file, $tour_id) {
				if (TTBM_Global_Function::get_post_info($tour_id, 'ttbm_theme_file', 'default.php') === 'smart.php') {
					return TTBM_Function::template_path('ticket/regular_ticket_smart.php');
				}
				return $file;
			}
			private function smart_booking_badges($tour_id, $all_dates) {
				$badges = array();
				$today = gmdate('Y-m-d');
				$has_today = false;
				if (is_array($all_dates)) {
					foreach ($all_dates as $date_item) {
						$date_only = gmdate('Y-m-d', strtotime($date_item));
						if ($date_only === $today) {
							$has_today = true;
							break;
						}
					}
				}
				if ($has_today) {
					$badges[] = array(
						'class' => 'is-available',
						'label' => esc_html__('Available Today', 'tour-booking-manager'),
					);
				}
				$is_best_seller = apply_filters(
					'ttbm_smart_best_seller_badge',
					TTBM_Global_Function::get_post_info($tour_id, 'ttbm_best_seller', 'off') === 'on' || $this->smart_tour_has_discount($tour_id, $all_dates),
					$tour_id
				);
				if ($is_best_seller) {
					$badges[] = array(
						'class' => 'is-bestseller',
						'label' => esc_html__('Best Seller', 'tour-booking-manager'),
					);
				}
				return $badges;
			}
			private function get_first_tour_date($all_dates) {
				if (!is_array($all_dates) || empty($all_dates)) {
					return '';
				}
				$first_date = reset($all_dates);
				return $first_date ? (string) $first_date : '';
			}
			private function get_smart_default_date($tour_id, $all_dates, $travel_type) {
				if (!is_array($all_dates) || empty($all_dates)) {
					return '';
				}
				if ($travel_type === 'particular') {
					$particular_dates = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_particular_dates', array());
					if (!is_array($particular_dates) || empty($particular_dates)) {
						return '';
					}
					foreach ($particular_dates as $particular_date) {
						$start_date = $particular_date['ttbm_particular_start_date'] ?? '';
						$start_time = $particular_date['ttbm_particular_start_time'] ?? '';
						if (!$start_date) {
							continue;
						}
						$option_date = $start_time ? $start_date . ' ' . $start_time : $start_date;
						$normalized_option_date = TTBM_Function::get_date_by_time_check($tour_id, $option_date, '');
						if ($normalized_option_date) {
							return $normalized_option_date;
						}
					}
					return '';
				}
				if ($travel_type === 'repeated') {
					$first_date = $this->get_first_tour_date($all_dates);
					if ($first_date === '') {
						return '';
					}
					return TTBM_Function::get_date_by_time_check($tour_id, $first_date, '') ?: $first_date;
				}
				return '';
			}
			private function smart_tour_has_discount($tour_id, $all_dates) {
				$ticket_lists = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticket_type', array());
				$sample_date = $this->get_first_tour_date($all_dates);
				if (!is_array($ticket_lists) || empty($ticket_lists) || $sample_date === '') {
					return false;
				}
				foreach ($ticket_lists as $ticket) {
					$ticket_name = $ticket['ticket_type_name'] ?? '';
					if ($ticket_name && TTBM_Function::check_discount_price_exit($tour_id, $ticket_name, '', '', $sample_date)) {
						return true;
					}
				}
				return false;
			}
			private function get_registration_context($ttbm_post_id = 0) {
				$ttbm_post_id = $ttbm_post_id > 0 ? $ttbm_post_id : get_the_id();
				$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
				return array(
					'ttbm_post_id' => $ttbm_post_id,
					'tour_id' => $tour_id,
					'ttbm_display_registration' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_registration', 'on'),
					'all_dates' => TTBM_Function::get_date($tour_id),
					'tour_type' => TTBM_Function::get_tour_type($tour_id),
					'travel_type' => TTBM_Function::get_travel_type($tour_id),
					'check_ability' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticketing_system', 'regular_ticket'),
					'date' => '',
				);
			}
			public function smart_registration_controls($ttbm_post_id = 0) {
				$context = $this->get_registration_context($ttbm_post_id);
				if ($context['ttbm_display_registration'] == 'off') {
					return;
				}
				$tour_id = $context['tour_id'];
				$all_dates = $context['all_dates'];
				$tour_type = $context['tour_type'];
				$travel_type = $context['travel_type'];
				$date = $context['date'];
				if (in_array($travel_type, array('repeated', 'particular'), true)) {
					$date = $this->get_smart_default_date($tour_id, $all_dates, $travel_type);
				}
				$badges = $this->smart_booking_badges($tour_id, $all_dates);
				?>
				<div class="ttbm_smart_registration_controls">
					<?php if (!empty($badges)) { ?>
						<div class="ttbm_smart_booking_badges">
							<?php foreach ($badges as $badge) { ?>
								<span class="ttbm_smart_booking_badge <?php echo esc_attr($badge['class']); ?>">
									<?php if ($badge['class'] === 'is-available') { ?>
										<span class="ttbm_smart_booking_badge__dot" aria-hidden="true"></span>
									<?php } ?>
									<?php echo esc_html($badge['label']); ?>
								</span>
							<?php } ?>
						</div>
					<?php } ?>
					<h3 class="ttbm_smart_booking_title"><?php esc_html_e('Instant Booking Summary', 'tour-booking-manager'); ?></h3>
					<p class="ttbm_smart_booking_subtitle"><?php esc_html_e('Select dates to see final price and availability in real time.', 'tour-booking-manager'); ?></p>
					<input type="hidden" name="ttbm_id" value="<?php echo esc_attr($tour_id); ?>"/>
					<?php
					if (sizeof($all_dates) > 0 && $tour_type == 'general' && $travel_type != 'particular') {
						$check_ability = $context['check_ability'];
						if ($date && strtotime($date) !== false) {
							$time = TTBM_Function::get_time($tour_id, $date);
							$time = is_array($time) ? $time[0]['time'] : $time;
							$date = $time ? $date . ' ' . $time : $date;
							if (strtotime($date) !== false) {
								$date = $time ? gmdate('Y-m-d H:i', strtotime($date)) : gmdate('Y-m-d', strtotime($date));
							} else {
								$date = '';
							}
						} else {
							$date = '';
						}
						$date_format = TTBM_Global_Function::date_picker_format();
						$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
						$hidden_date = ($date && strtotime($date) !== false) ? gmdate('Y-m-d', strtotime($date)) : '';
						$visible_date = ($date && strtotime($date) !== false) ? date_i18n($date_format, strtotime($date)) : '';
						if ($travel_type == 'repeated') {
							$time_slots_enabled = TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no') != 'no';
							$slot_check_date = $hidden_date ? gmdate('Y-m-d', strtotime($hidden_date)) : $this->get_first_tour_date($all_dates);
							?>
							<div class="ttbm_date_time_select ttbm_smart_date_time_select _fullWidth_mp_zero">
								<label class="ttbm_smart_date_field">
									<span class="ttbm_smart_date_label"><?php esc_html_e('Select Date', 'tour-booking-manager'); ?></span>
									<span class="date-picker-icon _fullWidth_mp_zero">
										<i class="far fa-calendar-alt" aria-hidden="true"></i>
										<input type="hidden" name="ttbm_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
										<input id="ttbm_select_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="formControl mb-0" placeholder="<?php echo esc_attr($now); ?>" readonly required/>
									</span>
								</label>
								<?php if ($time_slots_enabled) {
									$style = $visible_date ? '' : 'display:none;';
									?>
									<div class="flexWrap ttbm_select_time_area" style="<?php echo esc_attr($style); ?>">
										<?php do_action('ttbm_time_select', $tour_id, $slot_check_date); ?>
									</div>
								<?php } ?>
							</div>
							<?php
							do_action('ttbm_load_date_picker_js', '#ttbm_select_date', $all_dates);
						}
					} elseif (sizeof($all_dates) > 0 && $tour_type == 'general' && $travel_type == 'particular') {
						?>
						<div class="ttbm_date_time_select ttbm_smart_date_time_select _fullWidth_mp_zero">
							<label class="ttbm_smart_date_field">
								<span class="ttbm_smart_date_label"><?php esc_html_e('Select Date', 'tour-booking-manager'); ?></span>
								<span class="date-picker-icon _fullWidth_mp_zero">
									<i class="far fa-calendar-alt" aria-hidden="true"></i>
									<select class="formControl mb-0" name="ttbm_date" required>
										<option value="" disabled><?php esc_html_e('Select Date', 'tour-booking-manager'); ?></option>
										<?php
										$particular_dates = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_particular_dates', array());
										$default_particular_date = $date;
										if (is_array($particular_dates) && !empty($particular_dates)) {
											foreach ($particular_dates as $particular_date) {
												$start_date = $particular_date['ttbm_particular_start_date'] ?? '';
												$start_time = $particular_date['ttbm_particular_start_time'] ?? '';
												if (!$start_date) {
													continue;
												}
												$option_date = $start_time ? $start_date . ' ' . $start_time : $start_date;
												$normalized_option_date = TTBM_Function::get_date_by_time_check($tour_id, $option_date, '');
												if (!$normalized_option_date) {
													continue;
												}
												$option_label = $start_time ? TTBM_Global_Function::date_format($option_date, 'full') : TTBM_Global_Function::date_format($option_date);
												$is_selected = $default_particular_date && $normalized_option_date === $default_particular_date;
												?>
												<option value="<?php echo esc_attr($normalized_option_date); ?>"<?php echo $is_selected ? ' selected' : ''; ?>>
													<?php echo esc_html($option_label); ?>
												</option>
												<?php
											}
										}
										?>
									</select>
								</span>
							</label>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			public function smart_registration_panel($ttbm_post_id = 0) {
				$context = $this->get_registration_context($ttbm_post_id);
				if ($context['ttbm_display_registration'] == 'off') {
					return;
				}
				$tour_id = $context['tour_id'];
				$all_dates = $context['all_dates'];
				$tour_type = $context['tour_type'];
				$travel_type = $context['travel_type'];
				$date = $context['date'];
				if (in_array($travel_type, array('repeated', 'particular'), true)) {
					$date = $this->get_smart_default_date($tour_id, $all_dates, $travel_type);
				}
				$check_ability = $context['check_ability'];
				$time_slots_enabled = ($travel_type === 'repeated') && TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no') != 'no';
				$prerender_tickets = $date
					&& in_array($travel_type, array('repeated', 'particular'), true)
					&& $check_ability === 'regular_ticket'
					&& !$time_slots_enabled;
				?>
				<div class="ttbm_smart_registration_panel">
					<div class="ttbm_smart_tickets_placeholder">
						<p><?php esc_html_e('Select a date to view tickets and pricing.', 'tour-booking-manager'); ?></p>
					</div>
					<?php if (sizeof($all_dates) > 0) {
						if ($tour_type == 'general') { ?>
							<div class="ttbm_booking_panel placeholder_area">
								<?php if ($travel_type == 'fixed') {
									do_action('ttbm_booking_panel', $tour_id, $date);
								} elseif ($prerender_tickets) {
									do_action('ttbm_booking_panel', $tour_id, $date);
								} ?>
							</div>
						<?php }
						if ($travel_type != 'particular') {
							include(TTBM_Function::template_path('ticket/particular_item_area.php'));
						}
					} else { ?>
						<div class="dLayout allCenter bgWarning">
							<h3 class="textWhite"><?php esc_html_e('Date Expired ! ', 'tour-booking-manager') ?></h3>
						</div>
					<?php }
					if (sizeof($all_dates) > 0 && $tour_type == 'hotel' && $travel_type != 'particular') {
						include(TTBM_Function::template_path('ticket/hotel_default_selection.php'));
					}
					?>
				</div>
				<?php
			}
			public function ticket_registration() {
				$ttbm_post_id = $ttbm_post_id ?? get_the_id();
				$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
				$template_name = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_theme_file', 'default.php');
				if ($template_name === 'smart.php') {
					return;
				}
				$ttbm_post_id = $ttbm_post_id ?? get_the_id();
				$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
				$ttbm_display_registration = $ttbm_display_registration ?? TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_registration', 'on');
				if ($ttbm_display_registration != 'off') {
					?>
                    <div class="ttbm-sidebar-booking ttbm_registration_area">
                        <div class="ttbm-title-price"><?php esc_html_e("From", 'tour-booking-manager'); ?><?php include(TTBM_Function::template_path('layout/start_price_box.php')); ?></div>
                        <input type="hidden" name="ttbm_id" value="<?php echo esc_attr($tour_id); ?>"/>
						<?php
							$all_dates = $all_dates ?? TTBM_Function::get_date($tour_id);
							$date = ''; // Do not default to first date
							$tour_type = $tour_type ?? TTBM_Function::get_tour_type($tour_id);
							$travel_type = $travel_type ?? TTBM_Function::get_travel_type($tour_id);
							if (sizeof($all_dates) > 0 && $tour_type == 'general' && $travel_type != 'particular') {
								$check_ability = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticketing_system', 'regular_ticket');
								if ($date && strtotime($date) !== false) {
									$time = TTBM_Function::get_time($tour_id, $date);
									$time = is_array($time) ? $time[0]['time'] : $time;
									$date = $time ? $date . ' ' . $time : $date;
									// Check again after appending time
									if (strtotime($date) !== false) {
										$date = $time ? gmdate('Y-m-d H:i', strtotime($date)) : gmdate('Y-m-d', strtotime($date));
									} else {
										$date = '';
									}
								} else {
									$date = '';
								}
								/************/
								$date_format = TTBM_Global_Function::date_picker_format();
								$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
								$hidden_date = ($date && strtotime($date) !== false) ? gmdate('Y-m-d', strtotime($date)) : '';
								$visible_date = ($date && strtotime($date) !== false) ? date_i18n($date_format, strtotime($date)) : '';
								if ($travel_type == 'repeated') {
									$time_slots_enabled = TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no') != 'no';
									$slot_check_date = $this->get_first_tour_date($all_dates);
									$time_slots = TTBM_Function::get_time($tour_id, $slot_check_date);
									?>
                                    <div class="ttbm_date_time_select _fullWidth_mp_zero">
                                        <label>
                                                    <span class="date-picker-icon _fullWidth_mp_zero">
                                                                <i class="far fa-calendar-alt"></i>
                                                                <input type="hidden" name="ttbm_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
                                                                <input id="ttbm_select_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="formControl mb-0 " placeholder="<?php echo esc_attr($now); ?>" readonly required/>
                                                     </span>
                                        </label>
										<?php //TTBM_Layout::availability_button($tour_id); ?>

										<?php if ($time_slots_enabled) {
                                            // Hide initially if no date selected
                                            $style = $visible_date ? '' : 'display:none;';
                                            ?>
                                            <div class="flexWrap ttbm_select_time_area" style="<?php echo esc_attr($style); ?>">
												<?php do_action('ttbm_time_select', $tour_id, $slot_check_date); ?>
                                            </div>
										<?php } ?>
                                    </div>
									<?php
									do_action('ttbm_load_date_picker_js', '#ttbm_select_date', $all_dates);
								}
							} elseif (sizeof($all_dates) > 0 && $tour_type == 'general' && $travel_type == 'particular') {
								?>
                                <div class="ttbm_date_time_select _fullWidth_mp_zero">
                                    <label>
                                        <span class="date-picker-icon _fullWidth_mp_zero">
                                            <i class="far fa-calendar-alt"></i>
                                            <select class="formControl mb-0" name="ttbm_date" required>
                                                <option value="" selected disabled><?php esc_html_e('Select Date', 'tour-booking-manager'); ?></option>
												<?php
													$particular_dates = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_particular_dates', array());
													if (is_array($particular_dates) && !empty($particular_dates)) {
														foreach ($particular_dates as $particular_date) {
															$start_date = $particular_date['ttbm_particular_start_date'] ?? '';
															$start_time = $particular_date['ttbm_particular_start_time'] ?? '';
															if (!$start_date) {
																continue;
															}
															$option_date = $start_time ? $start_date . ' ' . $start_time : $start_date;
															$normalized_option_date = TTBM_Function::get_date_by_time_check($tour_id, $option_date, '');
															if (!$normalized_option_date) {
																continue;
															}
															$option_label = $start_time ? TTBM_Global_Function::date_format($option_date, 'full') : TTBM_Global_Function::date_format($option_date);
															?>
                                                            <option value="<?php echo esc_attr($normalized_option_date); ?>">
																<?php echo esc_html($option_label); ?>
                                                            </option>
															<?php
														}
													}
												?>
                                            </select>
                                        </span>
                                    </label>
                                </div>
								<?php
							}
							//include(TTBM_Function::template_path('ticket/date_selection.php'));
							//include(TTBM_Function::template_path('ticket/tour_default_selection.php'));
						?>
                        <!-- <button type="button" class="_dButton_bgBlue_fullWidth" data-target-popup="registration-popup"> -->
                        <button type="button" class="_dButton_fullWidth ttbm_load_popup_reg">
                            <span class="fas fa-plus-square"></span>
							<?php esc_html_e('Check Availability', 'tour-booking-manager'); ?>
                        </button>
                        <ul class="ttbm-registration-info">
                            <li><?php echo TTBM_Function::cancellation_policy_text(); ?></li>
                            <li><?php echo TTBM_Function::reserve_pay_later_text(); ?></li>
                        </ul>
                        <input type="hidden" class="registration_popup" data-target-popup="registration-popup">
                        <div class="ttbm_popup ttbm_style" data-popup="registration-popup">
                            <div class="popupMainArea">
                                <span class="fas fa-times popupCloseBtn"></span>
								<div class="popupBody">
									<?php if (sizeof($all_dates) > 0) {
										if ($tour_type == 'general') { ?>
                                            <div class="ttbm_booking_panel placeholder_area">
												<?php if ($travel_type == 'fixed') {
													do_action('ttbm_booking_panel', $tour_id, $date);
												} ?>
                                            </div>
										<?php }
										if ($travel_type != 'particular') {
											include(TTBM_Function::template_path('ticket/particular_item_area.php'));
										}
									} else { ?>
                                        <div class="dLayout allCenter bgWarning">
                                            <h3 class="textWhite"><?php esc_html_e('Date Expired ! ', 'tour-booking-manager') ?></h3>
                                        </div>
									<?php }
										if (sizeof($all_dates) > 0 && $tour_type == 'hotel' && $travel_type != 'particular') {
											include(TTBM_Function::template_path('ticket/hotel_default_selection.php'));
										}
										//include(TTBM_Function::template_path('ticket/particular_item_area.php'));
									?>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php
				}
			}
			public function particular_area() {
				include(TTBM_Function::template_path('ticket/particular_item_area.php'));
			}
			public function details_location() {
				include(TTBM_Function::template_path('layout/location.php'));
			}
			public function details_title() {
				include(TTBM_Function::template_path('layout/title_details_page.php'));
			}
			public function section_title($option_name, $default_title) {
				include(TTBM_Function::template_path('layout/title_section.php'));
			}
			public function section_titles($tour_id, $ttbm_title) {
				include(TTBM_Function::template_path('layout/section_title.php'));
			}
			public function slider() {
				include(TTBM_Function::template_path('layout/slider.php'));
			}
			public function description() {
				include(TTBM_Function::template_path('layout/description.php'));
			}
			//***************Feature************************//
			public function include_exclude() {
				include(TTBM_Function::template_path('layout/include_exclude.php'));
			}
			public function include_feature() {
				include(TTBM_Function::template_path('layout/include_feature.php'));
			}
			public function exclude_service() {
				include(TTBM_Function::template_path('layout/exclude_service.php'));
			}
			//*******************************************//
			public function short_details() {
				$ttbm_post_id = $ttbm_post_id ?? get_the_id();
				$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
				$tour_type = $tour_type ?? TTBM_Function::get_tour_type($tour_id);
				if ($tour_type != 'hotel') {
					$count = 0;
					?>
                    <div class="item_section">
						<?php do_action( 'ttbm_short_details_before', $ttbm_post_id ); ?>
						<?php include(TTBM_Function::template_path('layout/duration_box.php')); ?>
						<?php include(TTBM_Function::template_path('layout/start_price_box.php')); ?>
						<?php include(TTBM_Function::template_path('layout/max_people_box.php')); ?>
						<?php include(TTBM_Function::template_path('layout/start_location_box.php')); ?>
						<?php include(TTBM_Function::template_path('layout/age_range_box.php')); ?>
						<?php include(TTBM_Function::template_path('layout/seat_info.php')); ?>
						<?php include(TTBM_Function::template_path('layout/language_box.php')); ?>
                    </div>
					<?php
				}
			}
			public function location_map($tour_id) {
				include(TTBM_Function::template_path('layout/location_map.php'));
			}
			//*******************************************//
			public function smart_activity() {
				include(TTBM_Function::template_path('layout/smart_activity.php'));
			}
			public function activity() {
				include(TTBM_Function::template_path('layout/activity.php'));
			}
			public function hiphop_place() {
				include(TTBM_Function::template_path('layout/hiphop_place.php'));
			}
			public function day_wise_details() {
				include(TTBM_Function::template_path('layout/day_wise_details.php'));
			}
			public function faq() {
				include(TTBM_Function::template_path('layout/faq.php'));
			}
			public function why_choose_us() {
				include(TTBM_Function::template_path('layout/why_choose_us.php'));
			}
			public function sidebar_cta() {
				include(TTBM_Function::template_path('layout/sidebar_cta.php'));
			}
			public function get_a_question() {
				include(TTBM_Function::template_path('layout/get_a_question.php'));
			}
			public function tour_guide() {
				include(TTBM_Function::template_path('layout/tour_guide.php'));
			}
			public function related_tour() {
				include(TTBM_Function::template_path('layout/related_tour.php'));
			}
			//********************************************//
			public function dynamic_sidebar($tour_id) {
				if (TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_sidebar', 'on') != 'off') {
					dynamic_sidebar('ttbm_details_sidebar');
				}
			}
			public function travel_analytics_display($found_posts, $analytics_Data) { 
				// Get dynamic data
				$top_destination = isset($analytics_Data['top_destination']) && !empty($analytics_Data['top_destination']) 
					? $analytics_Data['top_destination'] 
					: esc_html__('No Data', 'tour-booking-manager');
				
				$average_price = isset($analytics_Data['average_price']) ? $analytics_Data['average_price'] : 0;
				$currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';
				$formatted_avg_price = $average_price > 0 ? $currency_symbol . number_format($average_price, 0) : $currency_symbol . '0';
				?>
                <div class="ttbm_travel_analytics-container">
                    <div class="ttbm_travel_analytics-bar">
                        <div class="ttbm_travel_metrics-group">
                            <div class="ttbm_travel_metric-card">
                                <div class="ttbm_travel_icon-circle ttbm_travel_blue-bg">
                                    <i class="fas fa-umbrella-beach ttbm_travel_blue-text"></i>
                                </div>
                                <h3 class="ttbm_travel_metric-label"><?php esc_attr_e('Tours', 'tour-booking-manager'); ?></h3>
                                <h2 class="ttbm_travel_metric-value ttbm_travel_blue-text" id="total-tours"><?php echo esc_attr($found_posts); ?></h2>
                            </div>
                            <div class="ttbm_travel_metric-card">
                                <div class="ttbm_travel_icon-circle ttbm_travel_purple-bg">
                                    <i class="fas fa-calendar-alt ttbm_travel_purple-text"></i>
                                </div>
                                <h3 class="ttbm_travel_metric-label"><?php esc_attr_e('Active', 'tour-booking-manager'); ?></h3>
                                <h2 class="ttbm_travel_metric-value ttbm_travel_purple-text" id="active-tours"><?php echo esc_attr($analytics_Data['active_tour']) ?></h2>
                            </div>
                            <div class="ttbm_travel_metric-card">
                                <div class="ttbm_travel_icon-circle ttbm_travel_amber-bg">
                                    <i class="fas fa-map-marker-alt ttbm_travel_amber-text"></i>
                                </div>
                                <h3 class="ttbm_travel_metric-label"><?php esc_attr_e('Locations', 'tour-booking-manager'); ?></h3>
                                <h2 class="ttbm_travel_metric-value ttbm_travel_amber-text" id="ttbm_travel_total-locations"><?php echo esc_attr($analytics_Data['location_count']) ?></h2>
                            </div>
                        </div>
                        <div class="ttbm_travel_metrics-group">
                            <div class="ttbm_travel_info-card">
                                <div class="ttbm_travel_info-section">
                                    <h3 class="ttbm_travel_metric-label"><?php esc_attr_e('Top Destination', 'tour-booking-manager'); ?></h3>
                                    <h2 class="ttbm_travel_metric-value ttbm_travel_indigo-text" id="top-destination"><?php echo esc_attr($top_destination); ?></h2>
                                </div>
                                <div class="ttbm_travel_info-section">
                                    <h3 class="ttbm_travel_metric-label"><?php esc_attr_e('Avg Price', 'tour-booking-manager'); ?></h3>
                                    <h2 class="ttbm_travel_metric-value ttbm_travel_pink-text" id="avg-price"><?php echo esc_attr($formatted_avg_price); ?></h2>
                                </div>
                            </div>
                            <div class="ttbm_travel_status-card">
                                <p class="ttbm_travel_metric-label"><?php esc_attr_e('Status', 'tour-booking-manager'); ?></p>
                                <div class="ttbm_travel_status-indicators">
                                    <h3 class="ttbm_travel_status-dot ttbm_travel_dot-active"></h3>
                                    <h2 class="ttbm_travel_status-text" id="ttbm_travel_active-count"> <?php echo esc_attr($analytics_Data['active_tour']);
											esc_attr_e(' Active', 'tour-booking-manager'); ?></h2>
                                    <h3 class="ttbm_travel_status-dot ttbm_travel_dot-expired"></h3>
                                    <h2 class="ttbm_travel_status-text" id="ttbm_travel_expired-count"><?php echo esc_attr($analytics_Data['expired_tour']);
											esc_attr_e(' Expired', 'tour-booking-manager'); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
			<?php }
		}
		new TTBM_Details_Layout();
	}
