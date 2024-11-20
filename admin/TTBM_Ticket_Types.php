<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Ticket_Types')) {
		class TTBM_Ticket_Types {
			public function __construct() {
				add_action('add_meta_boxes', array($this, 'ticket_type_meta'));
				add_action('ttbm_ticket_item', array($this, 'pricing_item'));
				add_action('save_post', array($this, 'save_ticket_item'), 99, 1);
			}
			public function ticket_type_meta() {
				$label = TTBM_Function::get_name();
				add_meta_box('mp_meta_box_panel', '<span class="dashicons dashicons-tickets-alt"></span>' . $label . '  ' . esc_html__('Ticket Type Settings : ', 'tour-booking-manager') . get_the_title(get_the_id()), array($this, 'ttbm_ticket_types'), 'ttbm_ticket_types', 'normal', 'high');
			}
			public function ttbm_ticket_types() {
				$post_id = get_the_ID();
				$ticket_type = MP_Global_Function::get_post_info($post_id, 'ttbm_ticket_type', array());
				wp_nonce_field('ttbm_ticket_item_nonce', 'ttbm_ticket_item_nonce');
				?>
                <div class="mpStyle">
                    <div class="mp_settings_area padding">
                        <table class="price_config_table">
                            <thead>
                            <tr>
                                <th><?php esc_html_e('Ticket Icon', 'tour-booking-manager'); ?></th>
                                <th><?php esc_html_e('Ticket Name', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                <th> <?php esc_html_e('Short Description', 'tour-booking-manager'); ?></th>
                                <th><?php esc_html_e('Regular Price', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                <th> <?php esc_html_e('Sale Price', 'tour-booking-manager'); ?></th>
                                <th><?php esc_html_e('Capacity', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                <th><?php esc_html_e('Default Qty', 'tour-booking-manager'); ?></th>
                                <th><?php esc_html_e("Reserve Qty", "tour-booking-manager"); ?> </th>
                                <th><?php esc_html_e('Qty Box Type', 'tour-booking-manager'); ?></th>
                                <th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="mp_sortable_area mp_item_insert">
			                <?php
				                if (sizeof($ticket_type) > 0) {
					                foreach ($ticket_type as $field) {
						                $this->pricing_item($field);
					                }
				                }
			                ?>
                            </tbody>
                        </table>
		                <?php MP_Custom_Layout::add_new_button(esc_html__('Add New Ticket Type', 'tour-booking-manager')); ?>
		                <?php do_action('add_mp_hidden_table', 'ttbm_ticket_item'); ?>
                    </div>
                </div>
				<?php
			}
			public function pricing_item($field = array()) {
				$tour_id = get_the_id();
				$field = $field ?: array();
				$icon = array_key_exists('ticket_type_icon', $field) ? $field['ticket_type_icon'] : '';
				$name = array_key_exists('ticket_type_name', $field) ? $field['ticket_type_name'] : '';
				$name_text = preg_replace("/[{}()<>+ ]/", '_', $name) . '_' . $tour_id;
				$price = array_key_exists('ticket_type_price', $field) ? $field['ticket_type_price'] : '';
				$sale_price = array_key_exists('sale_price', $field) ? $field['sale_price'] : '';
				$qty = array_key_exists('ticket_type_qty', $field) ? $field['ticket_type_qty'] : '';
				$default_qty = array_key_exists('ticket_type_default_qty', $field) ? $field['ticket_type_default_qty'] : '';
				$reserve_qty = array_key_exists('ticket_type_resv_qty', $field) ? $field['ticket_type_resv_qty'] : '';
				$input_type = array_key_exists('ticket_type_qty_type', $field) ? $field['ticket_type_qty_type'] : 'inputbox';
				$description = array_key_exists('ticket_type_description', $field) ? $field['ticket_type_description'] : '';
				?>
                <tr class="mp_remove_area">
                    <td><?php do_action('mp_input_add_icon', 'ticket_type_icon[]', $icon); ?></td>
                    <td>
                        <input type="hidden" name="ttbm_hidden_ticket_text[]" value="<?php echo esc_attr($name_text); ?>"/>
                        <input type="text" class="medium mp_name_validation" name="ticket_type_name[]" placeholder="Ex: Adult" value="<?php echo esc_attr($name); ?>" data-input-text="<?php echo esc_attr($name_text); ?>"/>
                    </td>
                    <td>
                        <input type="text" class="" name="ticket_type_description[]" placeholder="Ex: description" value="<?php echo esc_attr($description); ?>"/>
                    </td>
                    <td>
                        <input type="text" class="medium mp_price_validation" name="ticket_type_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($price); ?>"/>
                    </td>
                    <td>
                        <input type="text" class="medium mp_price_validation" name="ticket_type_sale_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($sale_price); ?>"/>
                    </td>
                    <td>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="medium mp_number_validation" data-same-input="ticket_type_qty" name="ticket_type_qty[]" placeholder="Ex: 500" value="<?php echo esc_attr($qty); ?>"/>
                    </td>
                    <td>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="medium mp_number_validation" name="ticket_type_default_qty[]" placeholder="Ex: 1" value="<?php echo esc_attr($default_qty); ?>"/>
                    </td>
                    <td>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="medium mp_number_validation" data-same-input="ticket_type_resv_qty" name="ticket_type_resv_qty[]" placeholder="Ex: 5" value="<?php echo esc_attr($reserve_qty); ?>"/>
                    </td>
                    <td>
                        <select name="ticket_type_qty_type[]" class='medium formControl'>
                            <option value="inputbox" <?php echo esc_attr($input_type == 'inputbox' ? 'selected' : ''); ?>><?php esc_html_e('Input Box', 'tour-booking-manager'); ?></option>
                            <option value="dropdown" <?php echo esc_attr($input_type == 'dropdown' ? 'selected' : ''); ?>><?php esc_html_e('Dropdown List', 'tour-booking-manager'); ?></option>
                        </select>
                    </td>
                    <td><?php MP_Custom_Layout::move_remove_button(); ?></td>
                </tr>
				<?php
			}
			public function save_ticket_item($tour_id) {
				if (get_post_type($tour_id) == 'ttbm_ticket_types') {
					if (!isset($_POST['ttbm_ticket_item_nonce']) || !wp_verify_nonce($_POST['ttbm_ticket_item_nonce'], 'ttbm_ticket_item_nonce')) {
						return;
					}
					if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
						return;
					}
					if (!current_user_can('edit_post', $tour_id)) {
						return;
					};
					$new_ticket_type = array();
					$icon = MP_Global_Function::get_submit_info('ticket_type_icon', array());
					$names = MP_Global_Function::get_submit_info('ticket_type_name', array());
					$ticket_price = MP_Global_Function::get_submit_info('ticket_type_price', array());
					$sale_price = MP_Global_Function::get_submit_info('ticket_type_sale_price', array());
					$qty = MP_Global_Function::get_submit_info('ticket_type_qty', array());
					$default_qty = MP_Global_Function::get_submit_info('ticket_type_default_qty', array());
					$rsv = MP_Global_Function::get_submit_info('ticket_type_resv_qty', array());
					$qty_type = MP_Global_Function::get_submit_info('ticket_type_qty_type', array());
					$description = MP_Global_Function::get_submit_info('ticket_type_description', array());
					$count = count($names);
					for ($i = 0; $i < $count; $i++) {
						if ($names[$i] && $ticket_price[$i] >= 0 && $qty[$i] > 0) {
							$new_ticket_type[$i]['ticket_type_icon'] = $icon[$i] ?? '';
							$new_ticket_type[$i]['ticket_type_name'] = $names[$i];
							$new_ticket_type[$i]['ticket_type_price'] = $ticket_price[$i];
							$new_ticket_type[$i]['sale_price'] = $sale_price[$i];
							$new_ticket_type[$i]['ticket_type_qty'] = $qty[$i];
							$new_ticket_type[$i]['ticket_type_default_qty'] = $default_qty[$i] ?? 0;
							$new_ticket_type[$i]['ticket_type_resv_qty'] = $rsv[$i] ?? 0;
							$new_ticket_type[$i]['ticket_type_qty_type'] = $qty_type[$i] ?? 'inputbox';
							$new_ticket_type[$i]['ticket_type_description'] = $description[$i] ?? '';
						}
					}
					update_post_meta($tour_id, 'ttbm_ticket_type', $new_ticket_type);
				}
			}
		}
		new TTBM_Ticket_Types();
	}