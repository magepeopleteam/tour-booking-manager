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
				add_action('wp_ajax_ttbm_new_place_save', [$this, 'ttbm_new_place_save']);
				add_action('wp_ajax_nopriv_ttbm_new_place_save', [$this, 'ttbm_new_place_save']);
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
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_hiphop', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem mp_settings_area ttbm_settings_place_you_see" data-tabs="#ttbm_settings_place_you_see">
					<h2><?php esc_html_e('Places You\'ll Visit', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('places_visit_description'); ?> </p>
					
					<section class="bg-light">
                        <div>
                            <p><?php esc_html_e('Place Settings', 'tour-booking-manager'); ?></p>
							<span class="text"><?php esc_html_e('You can set your future places here.', 'tour-booking-manager'); ?></span>  
                        </div>
                    </section>

					<section>
                        <label class="label">
							<div>
								<p><?php esc_html_e('Places You\'ll Visit ' . $ttbm_label . ' Settings', 'tour-booking-manager'); ?></p>
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_hiphop'); ?></span>  
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_hiphop', $checked); ?>
						</label>
                    </section>

					<div data-collapse="#ttbm_display_hiphop" class="ttbm_place_you_see_area <?php echo esc_attr($active); ?>">
						<?php $this->place_you_see($tour_id); ?>
					</div>
				</div>
				<?php
			}
			public function place_you_see($tour_id) {
				$hiphop_places = MP_Global_Function::get_post_info($tour_id, 'ttbm_hiphop_places', array());
				$all_places = MP_Global_Function::query_post_type('ttbm_places');
				$places = $all_places->posts;
				?>
				<div class="ttbm_place_you_see_table">
					<section>
                        <label class="label">
							<div>
								<label for=""><?php esc_html_e('Create new place', 'tour-booking-manager'); ?></label>  
								<span><?php TTBM_Settings::des_p('ttbm_place_you_see'); ?></span>  
							</div>
							<?php MP_Custom_Layout::popup_button_xs('add_new_place_you_see_popup', esc_html__('Create New Place', 'tour-booking-manager')); ?>
						</label>
                    </section>
					<?php if ($all_places->post_count > 0) { ?>
						<section>
							<div class="w-100">
								<table class="mb-2">
									<thead>
										<tr>
											<th class="text-start"><?php esc_html_e('Place Label', 'tour-booking-manager'); ?></th>
											<th class="text-start"><?php esc_html_e('Place', 'tour-booking-manager'); ?></th>
											<th class="text-start"><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
										</tr>
										</thead>
										<tbody class="mp_sortable_area mp_item_insert">
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
								<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Place', 'tour-booking-manager')); ?>
								
							</div>
						</section>
					<?php
						
					}
					?>
				</div>
				<div class="mp_hidden_content">
					<table>
						<tbody class="mp_hidden_item">
						<?php $this->place_you_see_item($places); ?>
						</tbody>
					</table>
				</div>
				<?php
				wp_reset_postdata();
				$this->add_new_place_you_see_popup();
			}
			public function place_you_see_item($places, $hiphop_place = array()) {
				$place_id = is_array($hiphop_place) && array_key_exists('ttbm_city_place_id', $hiphop_place) ? $hiphop_place['ttbm_city_place_id'] : '';
				$place_name = is_array($hiphop_place) && array_key_exists('ttbm_place_label', $hiphop_place) ? $hiphop_place['ttbm_place_label'] : '';
				$place_name = $place_id && !$place_name ? get_the_title($place_id) : $place_name;
				?>
				<tr class="mp_remove_area">
					<td class="text-center">
						<label>
							<input class="formControl mp_name_validation" name="ttbm_place_label[]" value="<?php echo esc_attr($place_name); ?>"/>
						</label>
					</td>
					<td class="text-center">
						<label>
							<select class="formControl <?php echo esc_attr(is_array($hiphop_place) && sizeof($hiphop_place) > 0 ? 'ttbm_select2' : 'add_ttbm_select2'); ?>" name="ttbm_city_place_id[]">
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
					<td class="text-center"><?php MP_Custom_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}
			public function add_new_place_you_see_popup() {
				?>
				<div class="mpPopup" data-popup="add_new_place_you_see_popup">
					<div class="popupMainArea">
						<div class="popupHeader">
							<h4>
								<?php esc_html_e('Add New Place', 'tour-booking-manager'); ?>
								<p class="_textSuccess_ml_dNone ttbm_success_info">
									<span class="fas fa-check-circle mR_xs"></span>
									<?php esc_html_e('Place is added successfully.', 'tour-booking-manager') ?>
								</p>
							</h4>
							<span class="fas fa-times popupClose"></span>
						</div>
						<div class="popupBody ttbm_place_you_see_form_area">
						</div>
						<div class="popupFooter">
							<div class="buttonGroup">
								<button class="_themeButton ttbm_new_place_you_see_save" type="button"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
								<button class="_warningButton ttbm_new_place_you_see_save_close" type="button"><?php esc_html_e('Save & Close', 'tour-booking-manager'); ?></button>
							</div>
						</div>
					</div>
				</div>
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
				$ttbm_id = MP_Global_Function::data_sanitize($_POST['ttbm_id']);
				$this->place_you_see($ttbm_id);
				die();
			}
			public function save_place_you_see($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$place_info = array();
					$hiphop = MP_Global_Function::get_submit_info('ttbm_display_hiphop') ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_hiphop', $hiphop);
					$place_labels = MP_Global_Function::get_submit_info('ttbm_place_label', array());
					$place_ids = MP_Global_Function::get_submit_info('ttbm_city_place_id', array());
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
			public function ttbm_new_place_save() {
                if(is_admin() && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	                $place_name = MP_Global_Function::data_sanitize($_POST['place_name']);
	                $place_description = MP_Global_Function::data_sanitize($_POST['place_description']);
	                $place_image = MP_Global_Function::data_sanitize($_POST['place_image']);
	                $args = array('post_title' => $place_name, 'post_content' => $place_description, 'post_status' => 'publish', 'post_type' => 'ttbm_places');
	                $query = wp_insert_post($args);
	                if ($query) {
		                set_post_thumbnail($query, $place_image);
	                }
                }
				die();
			}
		}
		new TTBM_Settings_place_you_see();
	}