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
				<li data-tabs-target="#ttbm_settings_why_chose_us">
					<i class="fas fa-info-circle"></i> <?php esc_html_e('Promotional Text', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function why_chose_us_settings($tour_id) {
				$ttbm_label = TTBM_Function::get_name();
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_why_choose_us', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_area ttbm_settings_why_chose_us" data-tabs="#ttbm_settings_why_chose_us">
					<h2 class=""><?php esc_html_e('Why Book With Us?', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('why_book_settings_description'); ?></p>
					
					<section>
						<div class="ttbm-header">
							<h4><i class="fas fa-info-circle"></i><?php esc_html_e('Promotional Text', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_why_choose_us', $checked); ?>
						</div>
						<div data-collapse="#ttbm_display_why_choose_us" class="<?php echo esc_attr($active); ?>">
							<?php $this->why_chose_us($tour_id); ?>
						</div>
                    </section>

				</div>
				<?php
			}
			public function why_chose_us($tour_id) {
				$why_chooses = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_why_choose_us_texts', array());
				?>
				<div class="promotional-text">
					<!-- <div class="w-100 mb-2 d-flex justify-content-between align-items-center">
						<label for=""><?php esc_html_e('Why Book With Us?', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('why_chose_us'); ?></i></label>
					</div> -->
					
					<table>
						<thead>
						<tr>
							<th colspan="2"><?php esc_html_e('Item List.', 'tour-booking-manager'); ?></th>
						</tr>
						</thead>
						<tbody class="ttbm_sortable_area ttbm_item_insert">
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
					<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add New Item', 'tour-booking-manager')); ?>
				</div>
				
				<div class="ttbm_hidden_content">
					<table>
						<tbody class="ttbm_hidden_item">
							<?php $this->why_chose_us_item(); ?>
						</tbody>
					</table>
				</div>
				<?php
			}
			public function why_chose_us_item($why_choose = '') {
				?>
				<tr class="ttbm_remove_area">
					<td class="">
						<label>
							<input class="ttbm_name_validation input-fullwidth" name="ttbm_why_choose_us_texts[]" value="<?php echo esc_attr($why_choose); ?>"/>
						</label>
					</td>
					<td>
						<div class="textRight">
							<?php TTBM_Custom_Layout::move_remove_button(); ?>
						</div>
					</td>
				</tr>
				<?php
			}
			public function save_why_chose_us($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$why_chose_us_info = array();
					$why_choose_display = TTBM_Global_Function::get_submit_info('ttbm_display_why_choose_us') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_why_choose_us', $why_choose_display);
					$why_chose_infos = TTBM_Global_Function::get_submit_info('ttbm_why_choose_us_texts', array());
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