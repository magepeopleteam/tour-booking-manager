<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Contact')) {
		class TTBM_Settings_Contact {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 90 );
				add_action('add_ttbm_settings_tab_content', [$this, 'extras_settings']);
				add_action('ttbm_settings_save', [$this, 'save_extras']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_extras">
					<i class="fas fa-file-alt"></i><?php esc_html_e('Contact ', 'tour-booking-manager'); ?>
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
					<h2><?php esc_html_e('Contact Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('contact_settings_description'); ?></p>


					<section class="bg-light">
                        <label for="" class="label">
							<div>
								<p><?php esc_html_e('Contact Settings', 'tour-booking-manager'); ?></p>
								<span class="text"><?php esc_html_e('Here you can set contact information.', 'tour-booking-manager'); ?></span>
							</div>
						</label>
                    </section>

					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e('On/Off Contact', 'tour-booking-manager'); ?> </p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_get_question'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_get_question', $checked_gaq); ?>
						</label>
                    </section>

					<div data-collapse="#ttbm_display_get_question" class=" <?php echo esc_attr($active_gaq); ?>">
						<section>
							<label class="label">
								<div>
									<p><?php esc_html_e('Contact E-Mail', 'tour-booking-manager'); ?></p>
									<span class="text"><?php TTBM_Settings::des_p('ttbm_contact_email'); ?></span>
								</div>
								<input class="formControl" name="ttbm_contact_email" value="<?php echo esc_attr($contact_email); ?>" placeholder="<?php esc_html_e('Please enter Contact Email', 'tour-booking-manager'); ?>"/>
							</label>
						</section>
						<section>
							<label class="label">
								<div>
									<p><?php esc_html_e('Contact Phone', 'tour-booking-manager'); ?> </p>
									<span class="text"><?php TTBM_Settings::des_p('ttbm_contact_phone'); ?></span>
								</div>
								<input class="formControl" name="ttbm_contact_phone" value="<?php echo esc_attr($contact_phone); ?>" placeholder="<?php esc_html_e('Please enter Contact Phone', 'tour-booking-manager'); ?>"/>
							</label>
						</section>

						<section>
							<label class="label">
								<div>
									<p><?php esc_html_e('Short Description', 'tour-booking-manager'); ?></p>
									<span class="text"><?php TTBM_Settings::des_p('ttbm_contact_text'); ?></span>
								</div>
								<textarea class="w-50" name="ttbm_contact_text" rows="4" placeholder="<?php esc_html_e('Please Enter Contact Section Text', 'tour-booking-manager'); ?>"><?php echo esc_attr($contact_text); ?></textarea>
							</label>
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
		new TTBM_Settings_Contact();
	}