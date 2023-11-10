<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_why_book_with_us')) {
		class TTBM_Settings_why_book_with_us {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
				add_action('add_ttbm_settings_tab_content', [$this, 'why_chose_us_settings'], 10, 1);
				add_action('ttbm_settings_save', [$this, 'save_why_chose_us']);
			}
			public function add_tab() {
				?>
				<li class="nav-item" data-tabs-target="#ttbm_settings_why_chose_us">
					<i class="fas fa-info-circle"></i> <?php esc_html_e('Why Book With Us ?', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function why_chose_us_settings($tour_id) {
				$ttbm_label = TTBM_Function::get_name();
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_why_choose_us', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem mp_settings_area ttbm_settings_why_chose_us" data-tabs="#ttbm_settings_why_chose_us">
					<h2 class="h4 px-0 text-primary"><?php esc_html_e('Why Book With Us?', 'tour-booking-manager'); ?></h2>
                    <hr>
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e('Why Chose Us' . $ttbm_label . ' Settings', 'tour-booking-manager'); ?></span>
						<?php MP_Custom_Layout::switch_button('ttbm_display_why_choose_us', $checked); ?>
					</h5>
					<?php TTBM_Settings::des_p('ttbm_display_why_choose_us'); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_why_choose_us" class="<?php echo esc_attr($active); ?>">
						<?php $this->why_chose_us($tour_id); ?>
					</div>
				</div>
				<?php
			}
			public function why_chose_us($tour_id) {
				$why_chooses = MP_Global_Function::get_post_info($tour_id, 'ttbm_why_choose_us_texts', array());
				?>
				<table class="layoutFixed">
					<tbody>
					<tr>
						<th>
							<?php esc_html_e('Why Book With Us?', 'tour-booking-manager'); ?>
							<?php TTBM_Settings::des_p('why_chose_us'); ?>
						</th>
						<td colspan="3">
							<table>
								<thead>
								<tr>
									<th><?php esc_html_e('Item List.', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
								</tr>
								</thead>
								<tbody class="mp_sortable_area mp_item_insert">
								<?php
									if (sizeof($why_chooses)) {
										foreach ($why_chooses as $why_choose) {
											$this->why_chose_us_item($why_choose);
										}
									}
									else {
										$this->why_chose_us_item();
									}
								?>
								</tbody>
							</table>
							<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Item', 'tour-booking-manager')); ?>
						</td>
					</tr>
					</tbody>
				</table>
				<div class="mp_hidden_content">
					<table>
						<tbody class="mp_hidden_item">
						<?php $this->why_chose_us_item(); ?>
						</tbody>
					</table>
				</div>
				<?php
			}
			public function why_chose_us_item($why_choose = '') {
				?>
				<tr class="mp_remove_area">
					<td>
						<label>
							<input class="formControl mp_name_validation" name="ttbm_why_choose_us_texts[]" value="<?php echo esc_attr($why_choose); ?>"/>
						</label>
					</td>
					<td><?php MP_Custom_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}
			public function save_why_chose_us($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$why_chose_us_info = array();
					$why_choose_display = MP_Global_Function::get_submit_info('ttbm_display_why_choose_us') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_why_choose_us', $why_choose_display);
					$why_chose_infos = MP_Global_Function::get_submit_info('ttbm_why_choose_us_texts', array());
					if (sizeof($why_chose_infos) > 0) {
						foreach ($why_chose_infos as $why_chose) {
							if ($why_chose) {
								$why_chose_us_info[] = $why_chose;
							}
						}
					}
					update_post_meta($tour_id, 'ttbm_why_choose_us_texts', $why_chose_us_info);
				}
			}
		}
		new TTBM_Settings_why_book_with_us();
	}