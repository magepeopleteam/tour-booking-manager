<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Gallery_Hotel')) {
		class TTBM_Settings_Gallery_Hotel {
			public function __construct() {
				add_action('ttbm_hotel_right_sidebar_content', [$this, 'gallery_sidebar'], 15);
			}

			public function gallery_sidebar($hotel_id) {
				$display   = TTBM_Global_Function::get_post_info($hotel_id, 'ttbm_display_slider_hotel', 'on');
				$active    = $display === 'off' ? '' : 'mActive';
				$checked   = $display === 'off' ? '' : 'checked';
				$image_ids = TTBM_Global_Function::get_post_info($hotel_id, 'ttbm_gallery_images_hotel', array());
				?>
				<div class="ttbm-sb-card ttbm-sb-gallery-card">
					<div class="ttbm-sb-gallery-toggle-row">
						<span class="ttbm-sb-gallery-toggle-label"><?php esc_html_e('Enable/Disable Gallery', 'tour-booking-manager'); ?></span>
						<input type="hidden" name="ttbm_display_slider_hotel" value="off" />
						<?php TTBM_Custom_Layout::switch_button('ttbm_display_slider_hotel', $checked); ?>
					</div>
					<div data-collapse="#ttbm_display_slider_hotel" class="ttbm-sb-gallery-body <?php echo esc_attr($active); ?>">
						<p class="ttbm-sb-gallery-images-label">
							<?php esc_html_e('Gallery Images', 'tour-booking-manager'); ?>
							<i class="fas fa-question-circle ttbm-sb-gallery-tip" title="<?php echo esc_attr__('Please upload gallery images in 4:3 ratio (e.g. 1200×900px). Gallery and featured image should use the same size.', 'tour-booking-manager'); ?>"></i>
						</p>
						<?php TTBM_Layout::add_multi_image('ttbm_gallery_images_hotel', $image_ids); ?>
					</div>
				</div>
				<?php
			}
		}
		new TTBM_Settings_Gallery_Hotel();
	}
