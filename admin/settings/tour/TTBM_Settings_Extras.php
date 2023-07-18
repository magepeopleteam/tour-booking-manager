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
				<li data-tabs-target="#ttbm_settings_extras">
					<span class="fas fa-file-alt"></span><?php esc_html_e('Extras ', 'tour-booking-manager'); ?>
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
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e('On/Off Get a Questions', 'tour-booking-manager'); ?></span>
						<?php MP_Custom_Layout::switch_button('ttbm_display_get_question', $checked_gaq); ?>
					</h5>
					<?php TTBM_Settings::des_p('ttbm_display_get_question'); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_get_question" class="<?php echo esc_attr($active_gaq); ?>">
						<table class="layoutFixed">
							<tbody>
							<tr>
								<th><?php esc_html_e('Contact E-Mail', 'tour-booking-manager'); ?></th>
								<td colspan="3">
									<label>
										<input class="formControl" name="ttbm_contact_email" value="<?php echo esc_attr($contact_email); ?>" placeholder="<?php esc_html_e('Please enter Contact Email', 'tour-booking-manager'); ?>"/>
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php TTBM_Settings::des_p('ttbm_contact_email'); ?></td>
							</tr>
							<tr>
								<th><?php esc_html_e('Contact Phone', 'tour-booking-manager'); ?></th>
								<td colspan="3">
									<label>
										<input class="formControl" name="ttbm_contact_phone" value="<?php echo esc_attr($contact_phone); ?>" placeholder="<?php esc_html_e('Please enter Contact Phone', 'tour-booking-manager'); ?>"/>
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php TTBM_Settings::des_p('ttbm_contact_phone'); ?></td>
							</tr>
							<tr>
								<th><?php esc_html_e('Short Description', 'tour-booking-manager'); ?></th>
								<td colspan="3">
									<label>
										<textarea class="formControl" name="ttbm_contact_text" rows="4" placeholder="<?php esc_html_e('Please Enter Contact Section Text', 'tour-booking-manager'); ?>"><?php echo esc_attr($contact_text); ?></textarea>
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php TTBM_Settings::des_p('ttbm_contact_text'); ?></td>
							</tr>
							</tbody>
						</table>
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