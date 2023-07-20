<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Gallery')) {
		class TTBM_Settings_Gallery {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
				add_action('add_ttbm_settings_tab_content', [$this, 'gallery_settings']);
				add_action('ttbm_settings_save', [$this, 'save_gallery']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_gallery">
					<span class="fas fa-images"></span><?php esc_html_e('Gallery ', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function gallery_settings($tour_id) {
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_slider', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				$image_ids = MP_Global_Function::get_post_info($tour_id, 'ttbm_gallery_images', array());
				?>
				<div class="tabsItem ttbm_settings_gallery" data-tabs="#ttbm_settings_gallery">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e('On/Off Slider', 'tour-booking-manager'); ?></span>
						<?php MP_Custom_Layout::switch_button('ttbm_display_slider', $checked); ?>
					</h5>
					<?php TTBM_Settings::des_p('ttbm_display_slider'); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_slider" class="<?php echo esc_attr($active); ?>">
						<table class="layoutFixed">
							<tbody>
							<tr>
								<th><?php esc_html_e('Gallery Images ', 'tour-booking-manager'); ?></th>
								<td colspan="3">
									<?php TTBM_Layout::add_multi_image('ttbm_gallery_images', $image_ids); ?>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php TTBM_Settings::des_p('ttbm_gallery_images'); ?></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			}
			public function save_gallery($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$slider = MP_Global_Function::get_submit_info('ttbm_display_slider') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_slider', $slider);
					$images = MP_Global_Function::get_submit_info('ttbm_gallery_images', array());
					$all_images = explode(',', $images);
					update_post_meta($tour_id, 'ttbm_gallery_images', $all_images);
				}
			}
		}
		new TTBM_Settings_Gallery();
	}