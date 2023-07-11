<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Related')) {
		class TTBM_Settings_Related {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
				add_action('add_ttbm_settings_tab_content', [$this, 'related_tour_settings']);
				add_action('ttbm_settings_save', [$this, 'save_related_tour']);
			}
			public function add_tab() {
				$ttbm_label = TTBM_Function::get_name();
				?>
				<li data-tabs-target="#ttbm_settings_related_tour">
					<span class="fas fa-map-marked-alt"></span><?php echo esc_html__('Related ', 'tour-booking-manager') . $ttbm_label; ?>
				</li>
				<?php
			}
			public function related_tour_settings($tour_id) {
				$ttbm_label = TTBM_Function::get_name();
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_related', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$related_tours = TTBM_Function::get_related_tour($tour_id);
				$all_tours = TTBM_Query::query_post_type('ttbm_tour');
				$tours = $all_tours->posts;
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem" data-tabs="#ttbm_settings_related_tour">
					<h5 class="dFlex">
						<span class="mR"><?php echo esc_html__('Related ', 'tour-booking-manager') . $ttbm_label . esc_html__(' Settings', 'tour-booking-manager') ?></span>
						<?php MP_Custom_Layout::switch_button('ttbm_display_related', $checked); ?>
					</h5>
					<?php TTBM_Settings::des_p('ttbm_display_related'); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_related" class="<?php echo esc_attr($active); ?>">
						<table class="layoutFixed">
							<tbody>
							<tr>
								<th>
									<?php esc_html_e('Related ' . $ttbm_label . ' : ', 'tour-booking-manager'); ?>
									<?php TTBM_Settings::des_p('ttbm_related_tour'); ?>
								</th>
								<td colspan="3">
									<label>
										<select name="ttbm_related_tour[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php echo esc_html__('Please Select ', 'tour-booking-manager') . $ttbm_label; ?>">
											<?php
												foreach ($tours as $tour) {
													$ttbm_id = $tour->ID;
													?>
													<option value="<?php echo esc_attr($ttbm_id) ?>" <?php echo in_array($ttbm_id, $related_tours) ? 'selected' : ''; ?>><?php echo get_the_title($ttbm_id); ?></option>
												<?php } ?>
										</select>
									</label>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php
				wp_reset_postdata();
			}
			public function save_related_tour($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$related = TTBM_Function::get_submit_info('ttbm_display_related') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_related', $related);
					$related_tours = TTBM_Function::get_submit_info('ttbm_related_tour', array());
					update_post_meta($tour_id, 'ttbm_related_tour', $related_tours);
				}
			}
		}
		new TTBM_Settings_Related();
	}