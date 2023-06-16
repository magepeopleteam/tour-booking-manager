<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Hotel')) {
		class TTBM_Settings_Hotel {
			public function __construct() {
				add_action('add_meta_boxes', [$this, 'hotel_settings_meta']);
				add_action('save_post', array($this, 'save_hotel'), 99, 1);
			}
			public function hotel_settings_meta() {
				$ttbm_label = TTBM_Function::get_name();
				add_meta_box('mp_meta_box_panel', '<span class="fas fa-hotel"></span>' . $ttbm_label . esc_html__(' Hotel Settings : ', 'tour-booking-manager') . get_the_title(get_the_id()), array($this, 'hotel_settings'), 'ttbm_hotel', 'normal', 'high');
			}
			public function hotel_settings() {
				$hotel_id = get_the_id();
				?>
				<div class="mpStyle ttbm_settings">
					<div class="mpTabs leftTabs">
						<ul class="tabLists">
							<li data-tabs-target="#ttbm_general_info">
								<span class="fas fa-cog"></span><?php esc_html_e('General Info', 'tour-booking-manager'); ?>
							</li>
							<li data-tabs-target="#ttbm_settings_pricing">
								<span class="fas fa-money-bill"></span><?php esc_html_e(' Pricing', 'tour-booking-manager'); ?>
							</li>
							<li data-tabs-target="#ttbm_settings_feature">
								<span class="fas fa-tasks"></span><?php esc_html_e(' Features', 'tour-booking-manager'); ?>
							</li>
						</ul>
						<div class="tabsContent tab-content">
							<?php
								wp_nonce_field('ttbm_hotel_type_nonce', 'ttbm_hotel_type_nonce');
								do_action('add_ttbm_settings_hotel_tab_content', $hotel_id);
							?>
						</div>
					</div>
				</div>
				<?php
			}
			public function save_hotel($post_id) {
				if (!isset($_POST['ttbm_hotel_type_nonce']) || !wp_verify_nonce($_POST['ttbm_hotel_type_nonce'], 'ttbm_hotel_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
					return;
				}
				if (get_post_type($post_id) == 'ttbm_hotel') {
					do_action('ttbm_settings_feature_save', $post_id);
					do_action('ttbm_hotel_settings_save', $post_id);
				}
			}
		}
		new TTBM_Settings_Hotel();
	}