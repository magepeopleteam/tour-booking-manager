<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Admin_Note')) {
		class TTBM_Settings_Admin_Note {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 90 );
				add_action('add_ttbm_settings_tab_content', [$this, 'admin_note_settings']);
				add_action('ttbm_settings_save', [$this, 'save_admin_note']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_admin_note">
					<i class="fas fa-edit"></i><?php esc_html_e( 'Admin Note', 'tour-booking-manager' ); ?>
				</li>
				<?php
			}
			public function admin_note_settings($tour_id) {
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_admin_note', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				$admin_note = MP_Global_Function::get_post_info($tour_id, 'ttbm_admin_note');
				$admin_note       = $admin_note ? html_entity_decode( $admin_note ) : '';
				?>
				<div class="tabsItem ttbm_settings_admin_note" data-tabs="#ttbm_settings_admin_note">
					<h2><?php esc_html_e('Admin Note Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('admin_note_settings_description'); ?></p>

					<section class="bg-light">
						<label for="" class="label">
							<div>
								<p><?php esc_html_e('Admin Note Settings', 'tour-booking-manager'); ?></p>
								<span class="text"><?php esc_html_e('Here you can add admin not and on/off admin note.', 'tour-booking-manager'); ?></span>
							</div>
						</label>
					</section>
					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e('On/Off Admin Note', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"></i></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_admin_note'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_admin_note', $checked); ?>
						</label>
                    </section>

					<div data-collapse="#ttbm_display_admin_note" class="<?php echo esc_attr($active); ?>">
						
						<section>
							<label class="label">
								<div>
									<p><?php esc_html_e('Note ', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"></i></p>   
									<span class="text"><?php TTBM_Settings::des_p('ttbm_admin_note'); ?></span> 
								</div>
								<textarea name="ttbm_admin_note" cols="50" rows="2"><?php echo esc_attr($admin_note); ?></textarea>
							</label>
						</section>

					</div>
				</div>
				<?php
			}
			public function save_admin_note($post_id) {
				if (get_post_type($post_id) == TTBM_Function::get_cpt_name()) {
					$display_note = MP_Global_Function::get_submit_info('ttbm_display_admin_note') ? 'on' : 'off';
					update_post_meta($post_id, 'ttbm_display_admin_note', $display_note);
					$note=MP_Global_Function::get_submit_info('ttbm_admin_note');
					update_post_meta($post_id, 'ttbm_admin_note', $note);
				}
			}
		}
		new TTBM_Settings_Admin_Note();
	}