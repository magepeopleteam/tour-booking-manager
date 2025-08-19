<?php
	/**
	 * @author Shahadat Hossain <raselsha@gmail.com>
	 * @package tour-booking-manager
	 * @version 1.0.0
	 */

	defined('ABSPATH')  || exit;

	if (!class_exists('TTBM_Settings_Hotel_FAQ')) {
		class TTBM_Settings_Hotel_FAQ {

			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'faq_settings']);
			}

			public function faq_settings($tour_id) {
				$faq_status = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hotel_faq_status', 'on');
				$active = $faq_status == 'off' ? '' : 'mActive';
				$checked = $faq_status == 'off' ? '' : 'checked';
				?>

				<div class="tabsItem ttbm_settings_hotel_faq" data-tabs="#ttbm_settings_hotel_faq">
                    <h2><?php esc_html_e('FAQ Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php  esc_html_e('FAQ Settings', 'tour-booking-manager'); ?></p>
                    <section>
                        <div class="label">
                            <?php esc_html_e('FAQ Settings', 'tour-booking-manager'); ?>
                            
							<?php TTBM_Custom_Layout::switch_button('ttbm_hotel_faq_status', $checked); ?>
                        </div>
						<div data-collapse="#ttbm_hotel_faq_status" class="<?php echo esc_attr($active); ?>">
							<?php esc_html_e('FAQ', 'tour-booking-manager'); ?>
						</div>
                    </section>
				</div>

				<?php
			}
		}
		new TTBM_Settings_Hotel_FAQ();
	}