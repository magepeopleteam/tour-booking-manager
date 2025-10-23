<?php
	/**
	 * Template Settings
	 *
	 * @package Tour Booking Manager
	 * @since 1.8.7
	 * @version 1.0.0
	 * @author Shahadat Hossain <raselsha@gmail.com>
	 *
	 */
	defined('ABSPATH')|| exit;

	if (!class_exists('TTBM_Settings_Template')) {
		class TTBM_Settings_Template {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'tab_content'], 10, 1);
			}

			public function tab_content($post_id) {
				?>
                <div class="tabsItem ttbm_settings_template" data-tabs="#ttbm_settings_template">
                    <h2><?php esc_html_e('Template Settings', 'tour-booking-manager'); ?></h2>
					<p><?php esc_html_e('Select suitable template to dispaly your tour.', 'tour-booking-manager'); ?></p>
                    
					<section>
                        <div class="ttbm-header">
                            <h4><i class="mi mi-table-layout"></i><?php esc_html_e('Template Settings', 'tour-booking-manager'); ?></h4>
                        </div>
                    </section>
                </div>
				<?php
			}
		}
		new TTBM_Settings_Template();
	}