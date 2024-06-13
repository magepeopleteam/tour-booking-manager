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
					<p><?php TTBM_Settings::des_p('why_book_settings_description'); ?></p>
					
					<section class="bg-light">
                        <label for="" class="label">
							<div>
								<p><?php esc_html_e('Why Chose Us', 'tour-booking-manager'); ?></p>
								<span class="text"><?php esc_html_e('You can set why chose us settings', 'tour-booking-manager'); ?></span>
							</div>
						</label>
                    </section>
					
					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e('Why Chose Us' . $ttbm_label . ' Settings', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_why_choose_us'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_why_choose_us', $checked); ?>
						</label>
                    </section>

					<div data-collapse="#ttbm_display_why_choose_us" class="<?php echo esc_attr($active); ?>">
						<?php $this->why_chose_us($tour_id); ?>
					</div>
				</div>
				<?php
			}
			public function why_chose_us($tour_id) {
				$why_chooses = MP_Global_Function::get_post_info($tour_id, 'ttbm_why_choose_us_texts', array());
				?>
				<section >
					<!-- <div class="w-100 mb-2 d-flex justify-content-between align-items-center">
						<label for=""><?php esc_html_e('Why Book With Us?', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('why_chose_us'); ?></i></label>
					</div> -->
					<div>
						<table>
							<thead>
							<tr>
								<th class="w-70"><?php esc_html_e('Item List.', 'tour-booking-manager'); ?></th>
								<th class="w-10"><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
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
					</div>
					<div class="w-100 d-flex justify-content-start align-items-center my-2">
						<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Item', 'tour-booking-manager')); ?>
					</div>
				</section>
				
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
							<input class="p-1 bordered w-100 rounded  mp_name_validation" name="ttbm_why_choose_us_texts[]" value="<?php echo esc_attr($why_choose); ?>"/>
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