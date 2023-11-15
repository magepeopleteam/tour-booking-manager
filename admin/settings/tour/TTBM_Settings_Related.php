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
				<li class="nav-item" data-tabs-target="#ttbm_settings_related_tour">
					<i class="fas fa-map-marked-alt"></i><?php echo esc_html__('Related ', 'tour-booking-manager') . $ttbm_label; ?>
				</li>
				<?php
			}
			public function related_tour_settings($tour_id) {
				$ttbm_label = TTBM_Function::get_name();
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_related', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$related_tours = MP_Global_Function::get_post_info($tour_id, 'ttbm_related_tour', array());
				$all_tours = MP_Global_Function::query_post_type(TTBM_Function::get_cpt_name());
				$tours = $all_tours->posts;
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem" data-tabs="#ttbm_settings_related_tour">
					<h2 class="h4 px-0 text-primary"><?php esc_html_e('Related'.$ttbm_label.'Settings', 'tour-booking-manager'); ?></h2>
                    
					<section class="component d-flex justify-content-between align-items-center mb-2">
                        <div class="w-100 d-flex justify-content-between align-items-center">
                            <label for=""><?php echo esc_html__('Related ', 'tour-booking-manager') . $ttbm_label . esc_html__(' Settings', 'tour-booking-manager') ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('ttbm_display_related'); ?></i></label>
                            <div class=" d-flex justify-content-between">
								<?php MP_Custom_Layout::switch_button('ttbm_display_related', $checked); ?>
                            </div>    
                        </div>
                    </section>

					<div data-collapse="#ttbm_display_related" class="<?php echo esc_attr($active); ?>">
						<section class="component d-flex justify-content-between align-items-center mb-2">
							<div class="w-50 d-flex justify-content-start align-items-center">
								<label for=""><?php esc_html_e('Related ' . $ttbm_label . ' : ', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><?php TTBM_Settings::des_p('ttbm_related_tour'); ?></i></label>
							</div>
							<div class="w-50 d-flex justify-content-end align-items-center">
								<div class=" d-flex justify-content-end">
									<select name="ttbm_related_tour[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php echo esc_html__('Please Select ', 'tour-booking-manager') . $ttbm_label; ?>">
										<?php
											foreach ($tours as $tour) {
												$ttbm_id = $tour->ID;
												?>
												<option value="<?php echo esc_attr($ttbm_id) ?>" <?php echo in_array($ttbm_id, $related_tours) ? 'selected' : ''; ?>><?php echo get_the_title($ttbm_id); ?></option>
											<?php } ?>
									</select>
								</div>    
							</div>
						</section>
					</div>
				</div>
				<?php
				wp_reset_postdata();
			}
			public function save_related_tour($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$related = MP_Global_Function::get_submit_info('ttbm_display_related') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_related', $related);
					$related_tours = MP_Global_Function::get_submit_info('ttbm_related_tour', array());
					update_post_meta($tour_id, 'ttbm_related_tour', $related_tours);
				}
			}
		}
		new TTBM_Settings_Related();
	}