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
					<i class="fas fa-images"></i><?php esc_html_e('Gallery ', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function gallery_settings($tour_id) {
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_slider', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				$image_ids = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_gallery_images', array());
				?>
				
				<div class="tabsItem ttbm_settings_gallery" data-tabs="#ttbm_settings_gallery">
					<h2 ><?php esc_html_e('Gallery Settings', 'tour-booking-manager'); ?></h2>
					<p ><?php TTBM_Settings::des_p('gallery_settings_description'); ?></p>

					<section>
						<div class="ttbm-header">
							<h4><i class="fas fa-images"></i><?php esc_html_e('Enable/Disable Gallery', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_slider', $checked); ?>
						</div>
						<div data-collapse="#ttbm_display_slider" class="<?php echo esc_attr($active); ?>">
							<div >
								<label class="label"><p><?php esc_html_e('Gallery Images ', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php echo esc_html__('Please upload gallary images size in ratio 4:3. Ex: Image size width=1200px and height=900px. gallery and feature image should be in same size.','tour-booking-manager'); ?></span></i></p></label>
								<div class="mt-2"></div>
								<?php TTBM_Layout::add_multi_image('ttbm_gallery_images', $image_ids); ?>
							</div>
						</div>
					</section>
				</div>
				<?php
			}
			public function save_gallery($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$slider = TTBM_Global_Function::get_submit_info('ttbm_display_slider') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_slider', $slider);
					$images = TTBM_Global_Function::get_submit_info('ttbm_gallery_images', array());
					$all_images = explode(',', $images);
					update_post_meta($tour_id, 'ttbm_gallery_images', $all_images);
				}
			}
		}
		new TTBM_Settings_Gallery();
	}