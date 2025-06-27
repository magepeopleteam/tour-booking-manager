<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Display')) {
		class TTBM_Settings_Display {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 90 );
				add_action('add_ttbm_settings_tab_content', [$this, 'display_settings']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_display_settings">
					<i class="fas fa-chalkboard"></i><?php esc_html_e(' Display settings', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function display_settings($tour_id) {
				$seat_details_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_seat_details', 'on') == 'off' ? '' : 'checked';
				$tour_type_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_tour_type', 'on') == 'off' ? '' : 'checked';
				$hotel_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotels', 'on') == 'off' ? '' : 'checked';
				$sidebar_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_sidebar', 'off') == 'off' ? '' : 'checked';
				$duration_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_duration', 'on') == 'off' ? '' : 'checked';
				$template_name = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
				$template_lists = TTBM_Function::all_details_template();
				$display_enquiry = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_enquiry', 'on');
				$enquiry_checked = $display_enquiry == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_display_settings" data-tabs="#ttbm_display_settings">
					<h2><?php esc_html_e('Display Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('display_settings_description'); ?> </p>

					<?php $content_title_style = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_section_title_style') ?: 'ttbm_title_style_2'; ?>
					<?php $ticketing_system = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticketing_system', 'availability_section'); ?>
					
					<section>
						<div class="ttbm-header">
							<h4><i class="fas fa-chalkboard"></i><?php esc_html_e('Display Settings', 'tour-booking-manager'); ?></h4>
						</div>
						<div class="dFlex">
							<div class="col-left">
								<label class="label">
									<div>
										<p><?php esc_html_e('Section Title Style', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_section_title_style'); ?></span></i></p>
									</div>
									<select class="" name="ttbm_section_title_style">
										<option value="style_1" <?php echo esc_attr($content_title_style == 'style_1' ? 'selected' : ''); ?>><?php esc_html_e('Style One', 'tour-booking-manager'); ?></option>
										<option value="ttbm_title_style_2" <?php echo esc_attr($content_title_style == 'ttbm_title_style_2' ? 'selected' : ''); ?>><?php esc_html_e('Style Two', 'tour-booking-manager'); ?></option>
										<option value="ttbm_title_style_3" <?php echo esc_attr($content_title_style == 'ttbm_title_style_3' ? 'selected' : ''); ?>><?php esc_html_e('Style Three', 'tour-booking-manager'); ?></option>
									</select>
								</label>
								<div class="label">
									<div>
										<p><?php esc_html_e('Display Seat Count', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_seat_details'); ?></span></i></p>
									</div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_seat_details', $seat_details_checked); ?> 
								</div>
								<div class="label">
									<div>
										<p><?php esc_html_e('Display Hotels Info', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_hotels'); ?></span></i></p>
										
									</div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_hotels', $hotel_checked); ?>
								</div>
								<div class="label">
									<div> 
										<p><?php esc_html_e('Display Duration', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_duration'); ?></span></i></p>
									</div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_duration', $display_enquiry); ?>
								</div>
								<label class="label">
									<div>
										<p><?php esc_html_e('Get Enquiry', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Enable/Disable Get Enquiry Button in frontend'); ?></span></i></p>
									</div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_enquiry', $enquiry_checked); ?>
								</label>
							</div>
							<div class="col-right">
								<label class="label">
									<div>
										<p><?php esc_html_e('Template', 'tour-booking-manager'); ?></p>
									</div>
									<select class="" name="ttbm_theme_file">
										<option><?php esc_html_e('Please select ...', 'tour-booking-manager'); ?></option>
										<?php foreach($template_lists as $key => $value): ?>
											<?php if($key != 'hotel_default.php'): ?> 
											<option value="<?php echo esc_attr($key); ?>" <?php echo esc_attr($template_name == $key? 'selected' : ''); ?>><?php echo esc_attr($value); ?></option>
											<?php endif; ?>
										<?php endforeach; ?>
									</select>
								</label>
								<label class="label">
									<div>
										<p><?php esc_html_e('Ticket Purchase', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttip_ticketing_system'); ?></span></i></p> 
									</div>
									<select name="ttbm_ticketing_system">
										<option value="regular_ticket" <?php echo esc_attr(!$ticketing_system ? 'selected' : ''); ?>><?php esc_html_e('Ticket Open', 'tour-booking-manager'); ?></option>
										<option value="availability_section" <?php echo esc_attr($ticketing_system == 'availability_section' ? 'selected' : ''); ?>><?php esc_html_e('Ticket Collapse System', 'tour-booking-manager'); ?></option>
									</select>
								</label>
								<div class="label">
									<div>
										<p><?php esc_html_e('Display Tour Type', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_tour_type'); ?></span></i></p>
									</div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_tour_type', $tour_type_checked); ?>
								</div> 
								<div class="label">
									<div>
										<p><?php esc_html_e('Dispaly Sidebar widget', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_sidebar'); ?></span></i></p>
									</div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_sidebar', $sidebar_checked); ?>
								</div>
								<?php $this->rank_tour($tour_id);  ?>
							</div>
							<?php do_action('add_ttbm_display_settings', $tour_id); ?>
						</div>
						
					</section>
				</div>
				<?php
			}
			public function rank_tour($tour_id) {
				$display_name = 'ttbm_display_order_tour';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = ($display == 'off') ? '' : 'checked';
				$active = ($display == 'off') ? '' : 'mActive';
				$placeholder='';
				?>
				<div class="label">
					<div class="label-inner">
						<p><?php esc_html_e('Rank Tour', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Turn on/off rank tour settings.', 'tour-booking-manager'); ?></span></i></p>
					</div>	
					<div class="_dFlex_alignCenter_justfyBetween">
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
						<input type="number" data-collapse="#<?php echo esc_attr($display_name); ?>" min="0" class="ms-2 <?php echo esc_attr($active); ?>" name="ttbm_travel_rank_tour" value="<?php echo TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_rank_tour'); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
					</div>
				</div>
				<?php
			}
		}
		new TTBM_Settings_Display();
	}