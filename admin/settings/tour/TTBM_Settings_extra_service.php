<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_extra_service')) {
		class TTBM_Settings_extra_service {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 10);
				add_action('add_ttbm_settings_tab_content', [$this, 'extra_service_tab_content'], 10, 1);
				
				add_action('ttbm_extra_service_item', array($this, 'extra_service_item'));
				add_action('ttbm_settings_save', [$this, 'extra_service']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_extra_service">
					<i class="fas fa-parachute-box"></i><?php esc_html_e(' Extra Service', 'tour-booking-manager'); ?>
				</li>
				<?php
				do_action('ttbm_meta_box_tab_after_pricing');
			}
			public function extra_service_tab_content($tour_id) {

				?>
				<div class="tabsItem ttbm_settings_pricing" data-tabs="#ttbm_settings_extra_service">
					<h2><?php esc_html_e('Extra Service', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Extra Service details', 'tour-booking-manager'); ?></p>
					<?php do_action('ttbm_tour_exs_pricing_before', $tour_id); ?>
					<?php $this->ttbm_extra_service_config($tour_id); ?>
					<?php do_action('ttbm_tour_exs_pricing_after', $tour_id); ?>
					
				</div>
				<?php
			}

			public function ttbm_extra_service_config($post_id) {
				$tour_label = TTBM_Function::get_name();
				$ttbm_extra_service_data = TTBM_Global_Function::get_post_info($post_id, 'ttbm_extra_service_data', array());
				wp_nonce_field('ttbm_extra_service_data_nonce', 'ttbm_extra_service_data_nonce');
				?>
				<div class="ttbm_settings_area mt-2">
					<section>
						<div class="ttbm-header">
							<h4><i class="fas fa-parachute-box"></i><?php esc_html_e('Extra Service', 'tour-booking-manager'); ?></h4>
						</div>
						<div class="ovAuto mt_xs">
							<table>
								<thead>
								<tr>
									<th><?php esc_html_e(' Icon', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Service Name', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Short description', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Price', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Available Qty', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Qty Box Type', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
								</tr>
								</thead>
								<tbody class="ttbm_sortable_area ttbm_item_insert">
								<?php
									if (sizeof($ttbm_extra_service_data) > 0) {
										foreach ($ttbm_extra_service_data as $field) {
											$this->extra_service_item($field);
										}
									}
								?>
								</tbody>
							</table>
						</div>
						<div class="d-flex justify-content-end">
							<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add Extra New Service', 'tour-booking-manager')); ?>
						</div>
						<?php do_action('add_ttbm_hidden_table', 'ttbm_extra_service_item'); ?>
					</section>
				</div>
				<?php
			}
			public function extra_service_item($field = array()) {
				$field = $field ?: array();
				$tour_id = get_the_id();
				$service_icon = array_key_exists('service_icon', $field) ? $field['service_icon'] : '';
				$service_name = array_key_exists('service_name', $field) ? $field['service_name'] : '';
				$service_price = array_key_exists('service_price', $field) ? $field['service_price'] : '';
				$service_qty = array_key_exists('service_qty', $field) ? $field['service_qty'] : '';
				$input_type = array_key_exists('service_qty_type', $field) ? $field['service_qty_type'] : 'inputbox';
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_extra_advance', 'off');
				$active = $display == 'off' ? '' : 'mActive';
				$description = array_key_exists('extra_service_description', $field) ? $field['extra_service_description'] : '';
				?>
				<tr class="ttbm_remove_area">
					<?php do_action('ttbm_ticket_type_content_start', $field, $tour_id) ?>
					<td><?php do_action('ttbm_input_add_icon', 'service_icon[]', $service_icon); ?></td>
					<td>
						<label>
							<input type="text" class="formControl medium ttbm_name_validation" name="service_name[]" placeholder="Ex: Cap" value="<?php echo esc_attr($service_name); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="text" class="formControl" name="extra_service_description[]" placeholder="Ex: description" value="<?php echo esc_attr($description); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="number" pattern="[0-9]*" step="0.01" class="small ttbm_price_validation" name="service_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($service_price); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="number" pattern="[0-9]*" step="1" class="small ttbm_number_validation" name="service_qty[]" placeholder="Ex: 100" value="<?php echo esc_attr($service_qty); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<select name="service_qty_type[]" class='medium'>
								<option value="inputbox" <?php echo esc_attr($input_type == 'inputbox' ? 'selected' : ''); ?>><?php esc_html_e('Input Box', 'tour-booking-manager'); ?></option>
								<option value="dropdown" <?php echo esc_attr($input_type == 'dropdown' ? 'selected' : ''); ?>><?php esc_html_e('Dropdown List', 'tour-booking-manager'); ?></option>
							</select>
						</label>
					</td>
					<td><?php TTBM_Custom_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}
			
			/***********/
			public function extra_service($tour_id) {
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
				
					//*********Extra service price**************//
					$new_extra_service = array();
					$extra_icon = TTBM_Global_Function::get_submit_info('service_icon', array());
					$extra_names = TTBM_Global_Function::get_submit_info('service_name', array());
					$extra_price = TTBM_Global_Function::get_submit_info('service_price', array());
					$extra_qty = TTBM_Global_Function::get_submit_info('service_qty', array());
					$extra_qty_type = TTBM_Global_Function::get_submit_info('service_qty_type', array());
					$extra_service_description = TTBM_Global_Function::get_submit_info('extra_service_description', array());
					$extra_count = count($extra_names);
					for ($i = 0; $i < $extra_count; $i++) {
						if ($extra_names[$i] && $extra_price[$i] >= 0 && $extra_qty[$i] > 0) {
							$new_extra_service[$i]['service_icon'] = $extra_icon[$i] ?? '';
							$new_extra_service[$i]['service_name'] = $extra_names[$i];
							$new_extra_service[$i]['service_price'] = $extra_price[$i];
							$new_extra_service[$i]['service_qty'] = $extra_qty[$i];
							$new_extra_service[$i]['service_qty_type'] = $extra_qty_type[$i] ?? 'inputbox';
							$new_extra_service[$i]['extra_service_description'] = $extra_service_description[$i] ?? '';
						}
					}
					$extra_service_data_arr = apply_filters('ttbm_extra_service_arr_save', $new_extra_service);
					update_post_meta($tour_id, 'ttbm_extra_service_data', $extra_service_data_arr);
				}
			}
		}
		new TTBM_Settings_extra_service();
	}