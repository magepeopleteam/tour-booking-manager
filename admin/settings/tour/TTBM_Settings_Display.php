<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Display')) {
		class TTBM_Settings_Display {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 90 );
				add_action('add_ttbm_settings_tab_content', [$this, 'display_settings']);
				add_action('ttbm_settings_save', [$this, 'save_display']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_display_settings">
					<i class="fas fa-chalkboard"></i><?php esc_html_e(' Display settings', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function display_settings($tour_id) {
				$seat_details_checked = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_seat_details', 'on') == 'off' ? '' : 'checked';
				$tour_type_checked = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_tour_type', 'on') == 'off' ? '' : 'checked';
				$hotel_checked = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_hotels', 'on') == 'off' ? '' : 'checked';
				$sidebar_checked = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_sidebar', 'off') == 'off' ? '' : 'checked';
				$duration_checked = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_duration', 'on') == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_display_settings" data-tabs="#ttbm_display_settings">
					<h2><?php esc_html_e('Display Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('display_settings_description'); ?> </p>

					<?php $content_title_style = MP_Global_Function::get_post_info($tour_id, 'ttbm_section_title_style') ?: 'ttbm_title_style_2'; ?>
					<?php $ticketing_system = MP_Global_Function::get_post_info($tour_id, 'ttbm_ticketing_system', 'availability_section'); ?>
					
					<section class="bg-light">
                        <label for="" class="label">
							<div>
								<p><?php esc_html_e('Display Settings', 'tour-booking-manager'); ?></p> 
								<span class="text"><?php esc_html_e('Here you can set what will be display or not.', 'tour-booking-manager'); ?> </span>
							</div>
						</label>
					</section>

					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e('Section Title Style?', 'tour-booking-manager'); ?></p> 
								<span class="text"><?php TTBM_Settings::des_p('ttbm_section_title_style'); ?>  </span>
							</div>
							<select class="formControl" name="ttbm_section_title_style">
								<option value="style_1" <?php echo esc_attr($content_title_style == 'style_1' ? 'selected' : ''); ?>><?php esc_html_e('Style One', 'tour-booking-manager'); ?></option>
								<option value="ttbm_title_style_2" <?php echo esc_attr($content_title_style == 'ttbm_title_style_2' ? 'selected' : ''); ?>><?php esc_html_e('Style Two', 'tour-booking-manager'); ?></option>
								<option value="ttbm_title_style_3" <?php echo esc_attr($content_title_style == 'ttbm_title_style_3' ? 'selected' : ''); ?>><?php esc_html_e('Style Three', 'tour-booking-manager'); ?></option>
							</select>
						</label>
					</section>
					<section>
						<label class="label">
							<div>
								<p><?php esc_html_e('Ticket Purchase Settings', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttip_ticketing_system'); ?></span></i></p>  
								<span class="text"><?php TTBM_Settings::des_p('ttbm_ticketing_system'); ?></span>
							</div>
							<select class="formControl" name="ttbm_ticketing_system">
								<option value="regular_ticket" <?php echo esc_attr(!$ticketing_system ? 'selected' : ''); ?>><?php esc_html_e('Ticket Open', 'tour-booking-manager'); ?></option>
								<option value="availability_section" <?php echo esc_attr($ticketing_system == 'availability_section' ? 'selected' : ''); ?>><?php esc_html_e('Ticket Collapse System', 'tour-booking-manager'); ?></option>
							</select>
						</label>
                    </section>
					
					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e('On/Off Seat Info', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_seat_details'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_seat_details', $seat_details_checked); ?> 
						</label>
					</section>
					
					<section>
						<label class="label">
							<div>
								<p><?php esc_html_e('On/Off Tour Type', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_tour_type'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_tour_type', $tour_type_checked); ?>
						</label> 
                    </section>

					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e('On/Off Hotels', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_hotels'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_hotels', $hotel_checked); ?>
						</label> 
					</section>

					<section>
						<label class="label">
							<div>
								<p><?php esc_html_e('On/Off Sidebar widget', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_sidebar'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_sidebar', $sidebar_checked); ?>
						</label> 
                    </section>

					<section>
                        <label class="label">
							<div> 
								<p><?php esc_html_e('On/Off Duration', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_duration'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_duration', $duration_checked); ?>
						</label> 
                    </section>
						<?php
       						 $this->rank_tour($tour_id); 
       					 ?>


					<?php do_action('add_ttbm_display_settings', $tour_id); ?>
					
				</div>
				<?php
			}
			public function rank_tour($tour_id) {
				$display_name = 'ttbm_display_order_tour';
				$display = MP_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = ($display == 'off') ? '' : 'checked';
				$active = ($display == 'off') ? '' : 'mActive';
				$placeholder='';
				?>
				<section>
					<label class="label">
						<div class="label-inner">
							<p><?php esc_html_e('Rank Tour', 'tour-booking-manager'); ?></p>
							<span class="text"><?php esc_html_e('Turn on/off rank tour settings.', 'tour-booking-manager'); ?></span>
						</div>	
						<div class="_dFlex_alignCenter_justfyBetween">
							<?php MP_Custom_Layout::switch_button($display_name, $checked); ?>
							<input type="number" data-collapse="#<?php echo esc_attr($display_name); ?>" min="0" class="ms-2 <?php echo esc_attr($active); ?>" name="ttbm_travel_rank_tour" value="<?php echo MP_Global_Function::get_post_info($tour_id, 'ttbm_travel_rank_tour'); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
						</div>
					</label>
				</section>
				<?php
			}
			public function save_display($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$content_title_style = MP_Global_Function::get_submit_info('ttbm_section_title_style') ?: 'style_1';
					$ticketing_system = MP_Global_Function::get_submit_info('ttbm_ticketing_system', 'availability_section');
					$seat_info = MP_Global_Function::get_submit_info('ttbm_display_seat_details') ? 'on' : 'off';
					$sidebar = MP_Global_Function::get_submit_info('ttbm_display_sidebar') ? 'on' : 'off';
					$tour_type = MP_Global_Function::get_submit_info('ttbm_display_tour_type') ? 'on' : 'off';
					$hotels = MP_Global_Function::get_submit_info('ttbm_display_hotels') ? 'on' : 'off';
					$duration = MP_Global_Function::get_submit_info('ttbm_display_duration') ? 'on' : 'off';
					$ttbm_display_rank = MP_Global_Function::get_submit_info('ttbm_display_order_tour') ? 'on' : 'off';
					$ttbm_travel_rank_tour = MP_Global_Function::get_submit_info('ttbm_travel_rank_tour');
					update_post_meta($tour_id, 'ttbm_travel_rank_tour', $ttbm_travel_rank_tour);
					update_post_meta($tour_id, 'ttbm_display_order_tour', $ttbm_display_rank);
					update_post_meta($tour_id, 'ttbm_section_title_style', $content_title_style);
					update_post_meta($tour_id, 'ttbm_ticketing_system', $ticketing_system);
					update_post_meta($tour_id, 'ttbm_display_seat_details', $seat_info);
					update_post_meta($tour_id, 'ttbm_display_sidebar', $sidebar);
					update_post_meta($tour_id, 'ttbm_display_tour_type', $tour_type);
					update_post_meta($tour_id, 'ttbm_display_hotels', $hotels);
					update_post_meta($tour_id, 'ttbm_display_duration', $duration);
				}
			}
		}
		new TTBM_Settings_Display();
	}