<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Contact')) {
		class TTBM_Settings_Contact {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'extras_settings']);
			}
			public function extras_settings($tour_id) {
				$contact_text = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_contact_text');
				$contact_phone = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_contact_phone');
				$contact_email = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_contact_email');
				$display_gaq = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_get_question', 'on');
				$active_gaq = $display_gaq == 'off' ? '' : 'mActive';
				$checked_gaq = $display_gaq == 'off' ? '' : 'checked';
				?>
                <div class="tabsItem ttbm_settings_extras" data-tabs="#ttbm_settings_extras">
                    <h2><?php esc_html_e('Contact Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('contact_settings_description'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fab fa-telegram-plane"></i><?php esc_html_e('Contact Settings', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_get_question', $checked_gaq); ?>
                        </div>
                        <div data-collapse="#ttbm_display_get_question" class=" <?php echo esc_attr($active_gaq); ?>">
                            <label class="label">
                                <div>
                                    <p><?php esc_html_e('Contact E-Mail', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_contact_email'); ?></span></i></p>
                                </div>
                                <input class="formControl" style="width: 50%;" name="ttbm_contact_email" value="<?php echo esc_attr($contact_email); ?>" placeholder="<?php esc_html_e('Please enter Contact Email', 'tour-booking-manager'); ?>"/>
                            </label>
                            <label class="label">
                                <div>
                                    <p><?php esc_html_e('Contact Phone', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_contact_phone'); ?></span></i></p>
                                </div>
                                <input class="formControl" style="width: 50%;" name="ttbm_contact_phone" value="<?php echo esc_attr($contact_phone); ?>" placeholder="<?php esc_html_e('Please enter Contact Phone', 'tour-booking-manager'); ?>"/>
                            </label>
                            <label class="label">
                                <p><?php esc_html_e('Short Description', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_contact_text'); ?></span></i></p>
                            </label>
                            <textarea name="ttbm_contact_text" rows="4" placeholder="<?php esc_html_e('Please Enter Contact Section Text', 'tour-booking-manager'); ?>"><?php echo esc_attr($contact_text); ?></textarea>
                        </div>
                    </section>
                </div>
				<?php
			}
		}
		new TTBM_Settings_Contact();
	}