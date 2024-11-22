<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_License')) {
		class TTBM_License {
			public function __construct() {
				add_action('mp_license_page_plugin_list', [$this, 'tour_licence'], 50);
			}
			public function tour_licence() {
				?>
				<tr>
					<th colspan="4" class="_textLeft"><?php echo esc_html('Travel Booking Plugin | Tour & Hotel Booking Solution For WooCommerce – wptravelly','tour-booking-manager'); ?></th>
					<th><?php esc_html_e('Free','tour-booking-manager'); ?></th>
					<th></th>
					<th colspan="2"><?php esc_html_e('Unlimited','tour-booking-manager'); ?></th>
					<th colspan="3"><?php esc_html_e('No Need','tour-booking-manager'); ?></th>
					<th class="textSuccess"><?php esc_html_e('Active','tour-booking-manager'); ?></th>
					<td colspan="2"></td>
				</tr>
				<?php
				do_action('ttbm_addon_list');
			}
		}
		new TTBM_License();
	}