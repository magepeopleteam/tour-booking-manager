<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Extras')) {
		class TTBM_Settings_Extras {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 90 );
				add_action('add_ttbm_settings_tab_content', [$this, 'extras_settings']);
				add_action('ttbm_settings_save', [$this, 'save_extras']);
			}
			public function add_tab() {
				?>
				<li class="nav-item" data-tabs-target="#ttbm_settings_extras">
					<i class="fas fa-file-alt"></i><?php esc_html_e('Extras ', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function extras_settings($tour_id) {
				$contact_text = MP_Global_Function::get_post_info($tour_id, 'ttbm_contact_text');
				$contact_phone = MP_Global_Function::get_post_info($tour_id, 'ttbm_contact_phone');
				$contact_email = MP_Global_Function::get_post_info($tour_id, 'ttbm_contact_email');
				$display_gaq = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_get_question', 'on');
				$active_gaq = $display_gaq == 'off' ? '' : 'mActive';
				$checked_gaq = $display_gaq == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_extras" data-tabs="#ttbm_settings_extras">
					<h2 class="h4 px-0 text-primary"><?php esc_html_e('Extras Settings', 'tour-booking-manager'); ?></h2>
					
					<section class="component d-flex justify-content-between align-items-center mb-2">
                        <div class="w-100 d-flex justify-content-between align-items-center">
                            <label for=""><?php esc_html_e('On/Off Get a Questions', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('ttbm_display_get_question'); ?></i></label>
                            <div class=" d-flex justify-content-between">
								<?php MP_Custom_Layout::switch_button('ttbm_display_get_question', $checked_gaq); ?>
                            </div>    
                        </div>
                    </section>

					<div data-collapse="#ttbm_display_get_question" class=" <?php echo esc_attr($active_gaq); ?>">
						<section class="component d-flex justify-content-between align-items-center mb-2">
							<div class="w-50 d-flex justify-content-between align-items-center">
								<label for=""><?php esc_html_e('Contact E-Mail', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('ttbm_contact_email'); ?></i></label>
								<div class=" d-flex justify-content-between">
									<input class="formControl" name="ttbm_contact_email" value="<?php echo esc_attr($contact_email); ?>" placeholder="<?php esc_html_e('Please enter Contact Email', 'tour-booking-manager'); ?>"/>
								</div>
							</div>
							<div class="w-50 ms-5 d-flex justify-content-between align-items-center">
								<label for=""><?php esc_html_e('Contact Phone', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('ttbm_contact_phone'); ?></i></label>
								<div class=" d-flex justify-content-between">
									<input class="formControl" name="ttbm_contact_phone" value="<?php echo esc_attr($contact_phone); ?>" placeholder="<?php esc_html_e('Please enter Contact Phone', 'tour-booking-manager'); ?>"/>
								</div>
							</div>
						</section>

						<section class="component d-flex justify-content-between align-items-center mb-2">
							<div class="w-50 d-flex justify-content-between align-items-center">
								<label for=""><?php esc_html_e('Short Description', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('ttbm_contact_text'); ?></i></label>
							</div>
							<div class="w-50 ms-5 d-flex justify-content-between align-items-center">
								<textarea class="w-100" name="ttbm_contact_text" rows="4" placeholder="<?php esc_html_e('Please Enter Contact Section Text', 'tour-booking-manager'); ?>"><?php echo esc_attr($contact_text); ?></textarea>
							</div>
						</section>
					</div>
				</div>
				<?php
			}
			public function save_extras($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$get_question = MP_Global_Function::get_submit_info('ttbm_display_get_question') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_get_question', $get_question);
					$email = MP_Global_Function::get_submit_info('ttbm_contact_email');
					$phone = MP_Global_Function::get_submit_info('ttbm_contact_phone');
					$des = MP_Global_Function::get_submit_info('ttbm_contact_text');
					update_post_meta($tour_id, 'ttbm_contact_email', $email);
					update_post_meta($tour_id, 'ttbm_contact_phone', $phone);
					update_post_meta($tour_id, 'ttbm_contact_text', $des);
				}
			}
		}
		new TTBM_Settings_Extras();
	}