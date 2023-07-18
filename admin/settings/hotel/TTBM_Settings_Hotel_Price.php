<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Hotel_Price')) {
		class TTBM_Settings_Hotel_Price {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'price_content'], 1);
				add_action('ttbm_room_item', array($this, 'room_item'));
				add_action('ttbm_hotel_settings_save', array($this, 'save_hotel_price'), 10, 1);
			}
			public function price_content($hotel_id) {
				$room_lists = MP_Global_Function::get_post_info($hotel_id, 'ttbm_room_details', array());
				$display = MP_Global_Function::get_post_info($hotel_id, 'ttbm_display_advance', 'off');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem" data-tabs="#ttbm_settings_pricing">
					<div class="_mT_dLayout_xs mp_settings_area">
						<h5><?php esc_html_e(' Price Configuration :', 'tour-booking-manager'); ?></h5>
						<div class="divider"></div>
						<h5 class="dFlex">
							<span class="mR"><?php esc_html_e('Show advance columns', 'tour-booking-manager'); ?></span>
							<?php MP_Custom_Layout::switch_button('ttbm_display_advance', $checked); ?>
						</h5>
						<?php TTBM_Settings::des_p('ttbm_display_advance'); ?>
						<div class="ovAuto _mT">
							<table>
								<thead>
								<tr>
									<th><?php esc_html_e('Room Icon', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Room Name', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
									<th data-collapse="#ttbm_display_advance" class="<?php echo esc_attr($active); ?>">
										<?php esc_html_e('Short Description', 'tour-booking-manager'); ?>
									</th>
									<th><?php esc_html_e('Regular Price', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
									<th data-collapse="#ttbm_display_advance" class="<?php echo esc_attr($active); ?>">
										<?php esc_html_e('Sale Price', 'tour-booking-manager'); ?>
									</th>
									<th><?php esc_html_e('Available Qty', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
									<th data-collapse="#ttbm_display_advance" class="<?php echo esc_attr($active); ?>">
										<?php esc_html_e("Reserve Qty", "tour-booking-manager"); ?>
									</th>
									<th>
										<?php esc_html_e('Adult Capacity', 'tour-booking-manager'); ?>
									</th>
									<th>
										<?php esc_html_e('Child Capacity', 'tour-booking-manager'); ?>
									</th>
									<th><?php esc_html_e('Qty Box Type', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
								</tr>
								</thead>
								<tbody class="mp_sortable_area mp_item_insert">
								<?php
									if (sizeof($room_lists) > 0) {
										foreach ($room_lists as $field) {
											$this->room_item($field);
										}
									}
								?>
								</tbody>
							</table>
						</div>
						<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Room', 'tour-booking-manager')); ?>
						<?php do_action('add_mp_hidden_table', 'ttbm_room_item'); ?>
					</div>
				</div>
				<?php
			}
			public function room_item($field = array()) {
				$tour_id = get_the_id();
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_advance', 'off');
				$active = $display == 'off' ? '' : 'mActive';
				$field = $field ?: array();
				$icon = array_key_exists('room_type_icon', $field) ? $field['room_type_icon'] : '';
				$name = array_key_exists('ttbm_hotel_room_name', $field) ? $field['ttbm_hotel_room_name'] : '';
				$name_text = preg_replace("/[{}()<>+ ]/", '_', $name) . '_' . $tour_id;
				$price = array_key_exists('ttbm_hotel_room_price', $field) ? $field['ttbm_hotel_room_price'] : '';
				$sale_price = array_key_exists('sale_price', $field) ? $field['sale_price'] : '';
				$qty = array_key_exists('ttbm_hotel_room_qty', $field) ? $field['ttbm_hotel_room_qty'] : '';
				$adult_qty = array_key_exists('ttbm_hotel_room_capacity_adult', $field) ? $field['ttbm_hotel_room_capacity_adult'] : '';
				$child_qty = array_key_exists('ttbm_hotel_room_capacity_child', $field) ? $field['ttbm_hotel_room_capacity_child'] : '';
				$reserve_qty = array_key_exists('room_reserve_qty', $field) ? $field['room_reserve_qty'] : '';
				$input_type = array_key_exists('room_qty_type', $field) ? $field['room_qty_type'] : 'inputbox';
				$description = array_key_exists('room_description', $field) ? $field['room_description'] : '';
				?>
				<tr class="mp_remove_area">
					<td><?php do_action('mp_input_add_icon', 'room_type_icon[]', $icon); ?></td>
					<td>
						<input type="hidden" name="ttbm_hidden_ticket_text[]" value="<?php echo esc_attr($name_text); ?>"/>
						<label>
							<input type="text" class="formControl mp_name_validation" name="ttbm_hotel_room_name[]" placeholder="Ex: AC" value="<?php echo esc_attr($name); ?>" data-input-text="<?php echo esc_attr($name_text); ?>"/>
						</label>
					</td>
					<td data-collapse="#ttbm_display_advance" class="<?php echo esc_attr($active); ?>">
						<label>
							<input type="text" class="formControl" name="room_description[]" placeholder="Ex: description" value="<?php echo esc_attr($description); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="text" class="formControl mp_price_validation" name="ttbm_hotel_room_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($price); ?>"/>
						</label>
					</td>
					<td data-collapse="#ttbm_display_advance" class="<?php echo esc_attr($active); ?>">
						<label>
							<input type="text" class="formControl mp_price_validation" name="sale_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($sale_price); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="number" size="4" pattern="[0-9]*" step="1" class="formControl mp_number_validation" name="ttbm_hotel_room_qty[]" placeholder="Ex: 500" value="<?php echo esc_attr($qty); ?>"/>
						</label>
					</td>
					<td data-collapse="#ttbm_display_advance" class="<?php echo esc_attr($active); ?>">
						<label>
							<input type="number" size="4" pattern="[0-9]*" step="1" class="formControl mp_number_validation" name="room_reserve_qty[]" placeholder="Ex: 5" value="<?php echo esc_attr($reserve_qty); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="number" size="4" pattern="[0-9]*" step="1" class="formControl mp_number_validation" name="ttbm_hotel_room_capacity_adult[]" placeholder="Ex: 1" value="<?php echo esc_attr($adult_qty); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="number" size="4" pattern="[0-9]*" step="1" class="formControl mp_number_validation" name="ttbm_hotel_room_capacity_child[]" placeholder="Ex: 1" value="<?php echo esc_attr($child_qty); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<select name="room_qty_type[]" class='formControl'>
								<option value="inputbox" <?php echo esc_attr($input_type == 'inputbox' ? 'selected' : ''); ?>><?php esc_html_e('Input Box', 'tour-booking-manager'); ?></option>
								<option value="dropdown" <?php echo esc_attr($input_type == 'dropdown' ? 'selected' : ''); ?>><?php esc_html_e('Dropdown List', 'tour-booking-manager'); ?></option>
							</select>
						</label>
					</td>
					<td><?php MP_Custom_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}
			public function save_hotel_price($post_id) {
				if (get_post_type($post_id) == 'ttbm_hotel') {
					$advance_option = MP_Global_Function::get_submit_info('ttbm_display_advance') ? 'on' : 'off';
					update_post_meta($post_id, 'ttbm_display_advance', $advance_option);
					/************************/
					$old_ticket_type = MP_Global_Function::get_post_info($post_id, 'ttbm_room_details', array());
					$new_ticket_type = array();
					$icon = MP_Global_Function::get_submit_info('room_type_icon', array());
					$names = MP_Global_Function::get_submit_info('ttbm_hotel_room_name', array());
					$ticket_price = MP_Global_Function::get_submit_info('ttbm_hotel_room_price', array());
					$sale_price = MP_Global_Function::get_submit_info('sale_price', array());
					$qty = MP_Global_Function::get_submit_info('ttbm_hotel_room_qty', array());
					$adult_qty = MP_Global_Function::get_submit_info('ttbm_hotel_room_capacity_adult', array());
					$child_qty = MP_Global_Function::get_submit_info('ttbm_hotel_room_capacity_child', array());
					$rsv = MP_Global_Function::get_submit_info('room_reserve_qty', array());
					$qty_type = MP_Global_Function::get_submit_info('room_qty_type', array());
					$description = MP_Global_Function::get_submit_info('room_description', array());
					$count = count($names);
					for ($i = 0; $i < $count; $i++) {
						if ($names[$i] && $ticket_price[$i] >= 0 && $qty[$i] > 0) {
							$new_ticket_type[$i]['room_type_icon'] = $icon[$i] ?? '';
							$new_ticket_type[$i]['ttbm_hotel_room_name'] = $names[$i];
							$new_ticket_type[$i]['ttbm_hotel_room_price'] = $ticket_price[$i];
							$new_ticket_type[$i]['sale_price'] = $sale_price[$i];
							$new_ticket_type[$i]['ttbm_hotel_room_qty'] = $qty[$i];
							$new_ticket_type[$i]['ttbm_hotel_room_capacity_adult'] = $adult_qty[$i] ?? 0;
							$new_ticket_type[$i]['ttbm_hotel_room_capacity_child'] = $child_qty[$i] ?? 0;
							$new_ticket_type[$i]['room_reserve_qty'] = $rsv[$i] ?? 0;
							$new_ticket_type[$i]['room_qty_type'] = $qty_type[$i] ?? 'inputbox';
							$new_ticket_type[$i]['room_description'] = $description[$i] ?? '';
						}
					}
					$ticket_type_list = apply_filters('ttbm_hotel_type_arr_save', $new_ticket_type);
					if (!empty($ticket_type_list) && $ticket_type_list != $old_ticket_type) {
						update_post_meta($post_id, 'ttbm_room_details', $ticket_type_list);
					}
					elseif (empty($ticket_type_list) && $old_ticket_type) {
						delete_post_meta($post_id, 'ttbm_room_details', $old_ticket_type);
					}
				}
			}
		}
		new TTBM_Settings_Hotel_Price();
	}