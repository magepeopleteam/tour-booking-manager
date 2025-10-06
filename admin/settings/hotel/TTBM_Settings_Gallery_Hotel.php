<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Gallery_Hotel')) {
		class TTBM_Settings_Gallery_Hotel {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'gallery_settings']);
			}
			public function gallery_settings($tour_id) {
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_slider_hotel', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				$image_ids = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_gallery_images_hotel', array());
				?>
                <div class="tabsItem ttbm_settings_gallery" data-tabs="#ttbm_settings_gallery">
                    <h2><?php esc_html_e('Gallery Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('gallery_settings_description'); ?></p>

                    <section>
                        <div class="ttbm-header">
                            <h4><i class="mi mi-file-image"></i><?php esc_html_e('Enable/Disable Gallery', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_slider_hotel', $checked); ?>
                        </div>
                        <div data-collapse="#ttbm_display_slider_hotel" class="<?php echo esc_attr($active); ?>">
                            <div>
                                <p><?php echo esc_html__('Upload gallary images size in ratio 4:3. Ex: Image size width=1200px and height=900px. gallery and feature image should be in same size.', 'tour-booking-manager'); ?></p>
                                
                                <div class="mT"></div>
                                <?php TTBM_Layout::add_multi_image('ttbm_gallery_images_hotel', $image_ids); ?>
                            </div>
                        </div>
                    </section>
                </div>
				<?php
			}
		}
	}
	new TTBM_Settings_Gallery_Hotel();
