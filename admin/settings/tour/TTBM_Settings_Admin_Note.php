<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Admin_Note')) {
		class TTBM_Settings_Admin_Note {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'admin_note_settings']);
			}
			public function admin_note_settings($tour_id) {
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_admin_note', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				$admin_note = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_admin_note');
				$admin_note = $admin_note ? html_entity_decode($admin_note) : '';
				?>
                <div class="tabsItem ttbm_settings_admin_note" data-tabs="#ttbm_settings_admin_note">
                    <h2><?php esc_html_e('Admin Note Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('admin_note_settings_description'); ?></p>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-edit"></i><?php esc_html_e('Admin Note Settings', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_admin_note', $checked); ?>
                        </div>
                        <div data-collapse="#ttbm_display_admin_note" class="<?php echo esc_attr($active); ?>">
                            <label class="label">
                                <p><?php esc_html_e('Note ', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_admin_note'); ?></span></i></p>
                            </label>
                            <textarea name="ttbm_admin_note" cols="5" rows="2"><?php echo esc_attr($admin_note); ?></textarea>
                        </div>
                    </section>
                </div>
				<?php
			}
		}
		new TTBM_Settings_Admin_Note();
	}