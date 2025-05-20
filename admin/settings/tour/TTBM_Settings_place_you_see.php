<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_place_you_see')) {
		class TTBM_Settings_place_you_see {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
				add_action('add_ttbm_settings_tab_content', [$this, 'place_you_see_settings'], 10, 1);
				//*********add new Place***************//
				add_action('wp_ajax_load_ttbm_place_you_see_form', [$this, 'load_ttbm_place_you_see_form']);
				add_action('wp_ajax_nopriv_load_ttbm_place_you_see_form', [$this, 'load_ttbm_place_you_see_form']);
				add_action('wp_ajax_ttbm_reload_place_you_see_list', [$this, 'ttbm_reload_place_you_see_list']);
				add_action('wp_ajax_nopriv_ttbm_reload_place_you_see_list', [$this, 'ttbm_reload_place_you_see_list']);
				/******************************/
				add_action('ttbm_settings_save', [$this, 'save_place_you_see']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_place_you_see">
					<i class="fas fa-map-marker-alt"></i><?php esc_html_e(' Places You\'ll Visit', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function place_you_see_settings($tour_id) {
				$ttbm_label = TTBM_Function::get_name();
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hiphop', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_area ttbm_settings_place_you_see" data-tabs="#ttbm_settings_place_you_see">
					<h2><?php esc_html_e('Places You\'ll Visit', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('places_visit_description'); ?> </p>

					<section>
						<div class="ttbm-header">
							<h4><i class="fas fa-map-marker-alt"></i><?php esc_html_e('Places You\'ll Visit', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_hiphop', $checked); ?>
						</div>
						<div data-collapse="#ttbm_display_hiphop" class="ttbm_place_you_see_area <?php echo esc_attr($active); ?>">
							<?php $this->place_you_see($tour_id); ?>
						</div>
                    </section>
				</div>
				<?php
			}
			public function place_you_see($tour_id) {
				$hiphop_places = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_hiphop_places', array());
				$all_places = TTBM_Global_Function::query_post_type('ttbm_places');
				$places = $all_places->posts;
				?>
				<div class="ttbm_place_you_see_table">
					<?php if ($all_places->post_count > 0) { ?>
						<div>
							<div class="label">
								<p>
									<?php esc_html_e('To create new place click', 'tour-booking-manager'); ?>
									<a href="edit.php?post_type=ttbm_places" ><?php esc_html_e('Here', 'tour-booking-manager'); ?></a>
									<i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_place_you_see'); ?></span></i>
								</p>
							</div>
							<table>
								<thead>
									<tr>
										<th><?php esc_html_e('Place Name', 'tour-booking-manager'); ?></th>
										<th><?php esc_html_e('Place', 'tour-booking-manager'); ?></th>
										<th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
									</tr>
									</thead>
									<tbody class="ttbm_sortable_area ttbm_item_insert">
									<?php
										if (sizeof($hiphop_places)) {
											foreach ($hiphop_places as $hiphop_place) {
												$this->place_you_see_item($places, $hiphop_place);
											}
										}
										else {
											$this->place_you_see_item($places);
										}
									?>
								</tbody>
							</table>
							<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add New Place', 'tour-booking-manager')); ?>
						</div>
					<?php
						
					}
					?>
				</div>
				<div class="ttbm_hidden_content">
					<table>
						<tbody class="ttbm_hidden_item">
						<?php $this->place_you_see_item($places); ?>
						</tbody>
					</table>
				</div>
				<?php
				wp_reset_postdata();
			}
			public function place_you_see_item($places, $hiphop_place = array()) {
				$place_id = is_array($hiphop_place) && array_key_exists('ttbm_city_place_id', $hiphop_place) ? $hiphop_place['ttbm_city_place_id'] : '';
				$place_name = is_array($hiphop_place) && array_key_exists('ttbm_place_label', $hiphop_place) ? $hiphop_place['ttbm_place_label'] : '';
				$place_name = $place_id && !$place_name ? get_the_title($place_id) : $place_name;
				?>
				<tr class="ttbm_remove_area">
					<td>
						<label>
							<input class="ttbm_name_validation" name="ttbm_place_label[]" value="<?php echo esc_attr($place_name); ?>" placeholder="Place name"/>
						</label>
					</td>
					<td>
						<label>
							<select class=" <?php echo esc_attr(is_array($hiphop_place) && sizeof($hiphop_place) > 0 ? 'ttbm_select2' : 'add_ttbm_select2'); ?>" name="ttbm_city_place_id[]">
								<option value="" selected disabled>
									<?php esc_html_e('Please Select a Place', 'tour-booking-manager'); ?>
								</option>
								<?php
									foreach ($places as $place) {
										$id = $place->ID;
										?>
										<option value="<?php echo esc_attr($id); ?>" <?php echo esc_attr($id == $place_id ? 'selected' : ''); ?>>
											<?php echo esc_html($place->post_title); ?>
										</option>
									<?php } ?>
							</select>
						</label>
					</td>
					<td class="textRight"><?php TTBM_Custom_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}

			public function load_ttbm_place_you_see_form() {
				?>
				<label class="flexEqual">
					<span><?php esc_html_e('Place Name : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span> <input type="text" name="ttbm_place_name" class="formControl" required>
				</label>
				<p class="textRequired" data-required="ttbm_place_name">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Place name is required!', 'tour-booking-manager'); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_place_name'); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e('Place Description : ', 'tour-booking-manager'); ?></span> <textarea name="ttbm_place_description" class="formControl" rows="3"></textarea>
				</label>
				<?php TTBM_Settings::des_p('ttbm_place_description'); ?>
				<div class="divider"></div>
				<div class="flexEqual">
					<span><?php esc_html_e('Place Image : ', 'tour-booking-manager'); ?><sup class="textRequired">*</sup></span>
					<?php TTBM_Layout::single_image_button('ttbm_place_image'); ?>
				</div>
				<p class="textRequired" data-required="ttbm_place_image">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e('Place image is required!', 'tour-booking-manager'); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_place_image'); ?>
				<?php
				die();
			}
			public function ttbm_reload_place_you_see_list() {
				$ttbm_id = TTBM_Global_Function::data_sanitize($_POST['ttbm_id']);
				$this->place_you_see($ttbm_id);
				die();
			}
			public function save_place_you_see($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$place_info = array();
					$hiphop = TTBM_Global_Function::get_submit_info('ttbm_display_hiphop') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_hiphop', $hiphop);
					$place_labels = TTBM_Global_Function::get_submit_info('ttbm_place_label', array());
					$place_ids = TTBM_Global_Function::get_submit_info('ttbm_city_place_id', array());
					if (sizeof($place_ids) > 0) {
						foreach ($place_ids as $key => $place_id) {
							if ($place_id && $place_id > 0) {
								$place_name = $place_labels[$key];
								$place_info[$key]['ttbm_city_place_id'] = $place_id;
								$place_info[$key]['ttbm_place_label'] = $place_name ?: get_the_title($place_id);
							}
						}
					}
					update_post_meta($tour_id, 'ttbm_hiphop_places', $place_info);
				}
			}

		}
		new TTBM_Settings_place_you_see();
	}