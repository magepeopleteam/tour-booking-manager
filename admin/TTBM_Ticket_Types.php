<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Ticket_Types')) {
		class TTBM_Ticket_Types {
			public function __construct() {
				add_action('add_meta_boxes', array($this, 'ticket_type_meta'));
				add_action('ttbm_ticket_item', array($this, 'pricing_item'));
				add_action('save_post_ttbm_ticket_types', array($this, 'save_ticket_types'));
			}
			public function ticket_type_meta() {
				$label = TTBM_Function::get_name();
				add_meta_box('ttbm_meta_box_panel', '<span class="dashicons dashicons-tickets-alt"></span>' . $label . '  ' . esc_html__('Ticket Type Settings : ', 'tour-booking-manager') . get_the_title(get_the_id()), array($this, 'ttbm_ticket_types'), 'ttbm_ticket_types', 'normal', 'high');
			}
			public function ttbm_ticket_types() {
				$post_id = get_the_ID();
				$ticket_type = TTBM_Global_Function::get_post_info($post_id, 'ttbm_ticket_type', array());
				wp_nonce_field('ttbm_ticket_item_nonce', 'ttbm_ticket_item_nonce');
				?>
                <div class="ttbm_style">
                    <div class="ttbm_settings_area _mT_xs ttbm-ticket-types-area">
                        <div class="ttbm-ticket-types-panel">
                            <div class="ttbm-ticket-types-table-wrap">
                                <table class="price_config_table ttbm-ticket-types-table">
                                    <thead>
                                    <tr>
                                        <th class="ttbm-ticket-col--name"><?php esc_html_e('Ticket Name', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                        <th class="ttbm-ticket-col--desc"><?php esc_html_e('Short Description', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-ticket-col--price"><?php esc_html_e('Reg. Price', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                        <th class="ttbm-ticket-col--sale"><?php esc_html_e('Sale Price', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-ticket-col--cap">
                                            <span class="ttbm-ticket-th-label"><?php esc_html_e('Capacity', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Total available seats for this ticket type (inventory).', 'tour-booking-manager'); ?></span></i></span>
                                        </th>
                                        <th class="ttbm-ticket-col--default"><?php esc_html_e('Def. Qty', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-ticket-col--reserve">
                                            <span class="ttbm-ticket-th-label"><?php esc_html_e('Res. Qty', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Add Reserve quantity', 'tour-booking-manager'); ?></span></i></span>
                                        </th>
                                        <th class="ttbm-ticket-col--qty-type"><?php esc_html_e('Qty Box Type', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-ticket-col--actions"><span class="screen-reader-text"><?php esc_html_e('Action', 'tour-booking-manager'); ?></span></th>
                                    </tr>
                                    </thead>
                                    <tbody class="ttbm_sortable_area ttbm_item_insert">
                                    <?php
                                        if (!empty($ticket_type)) {
                                            foreach ($ticket_type as $field) {
                                                $this->pricing_item($field);
                                            }
                                        }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <p id="ttbm_ticket_types_error" class="textRequired ttbm-ticket-panel-error" style="display:none;"></p>
                        </div>
                        <?php TTBM_Custom_Layout::add_new_button(esc_html__('Add New Ticket Type', 'tour-booking-manager'), 'ttbm_add_item', 'ttbm-ticket-types-add-btn', 'fas fa-plus'); ?>
                        <?php do_action('add_ttbm_hidden_table', 'ttbm_ticket_item'); ?>
                    </div>
                </div>
				<?php
			}
			public function pricing_item($field = array()) {
				$tour_id = get_the_id();
				$field = $field ?: array();
				$icon = array_key_exists('ticket_type_icon', $field) ? $field['ticket_type_icon'] : '';
				$name = array_key_exists('ticket_type_name', $field) ? $field['ticket_type_name'] : '';
				$name_text = $name ? preg_replace("/[{}()<>+ ]/", '_', $name) . '_' . $tour_id : '';
				$price = array_key_exists('ticket_type_price', $field) ? $field['ticket_type_price'] : '';
				$sale_price = array_key_exists('sale_price', $field) ? $field['sale_price'] : '';
				$qty = array_key_exists('ticket_type_qty', $field) ? $field['ticket_type_qty'] : '';
				$default_qty = array_key_exists('ticket_type_default_qty', $field) && $field['ticket_type_default_qty'] !== '' ? $field['ticket_type_default_qty'] : '1';
				$reserve_qty = array_key_exists('ticket_type_resv_qty', $field) ? $field['ticket_type_resv_qty'] : '';
				$input_type = array_key_exists('ticket_type_qty_type', $field) ? $field['ticket_type_qty_type'] : 'inputbox';
				$description = array_key_exists('ticket_type_description', $field) ? $field['ticket_type_description'] : '';
				$currency = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '$';
				?>
                <tr class="ttbm_remove_area ttbm-ticket-type-row">
                    <td class="ttbm-ticket-col--name">
                        <div class="ttbm-ticket-name-field">
                            <div class="ttbm-ticket-name-field__icon">
                                <?php do_action('ttbm_input_add_icon', 'ticket_type_icon[]', $icon); ?>
                            </div>
                            <div class="ttbm-ticket-name-field__input">
                                <input type="hidden" name="ttbm_hidden_ticket_text[]" value="<?php echo esc_attr($name_text); ?>"/>
                                <input type="text" class="formControl ttbm_name_validation" name="ticket_type_name[]" placeholder="<?php esc_attr_e('Ex: Adult', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($name); ?>" data-input-text="<?php echo esc_attr($name_text); ?>"/>
                            </div>
                        </div>
                    </td>
                    <td class="ttbm-ticket-col--desc">
                        <input type="text" class="formControl" name="ticket_type_description[]" placeholder="<?php esc_attr_e('Ex: Regular', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($description); ?>"/>
                    </td>
                    <td class="ttbm-ticket-col--price">
                        <label class="ttbm-ticket-input-prefix">
                            <span class="ttbm-ticket-input-prefix__symbol" aria-hidden="true"><?php echo esc_html($currency); ?></span>
                            <input type="text" class="formControl ttbm_price_validation" name="ticket_type_price[]" placeholder="0.00" value="<?php echo esc_attr($price); ?>"/>
                        </label>
                    </td>
                    <td class="ttbm-ticket-col--sale">
                        <label class="ttbm-ticket-input-prefix">
                            <span class="ttbm-ticket-input-prefix__symbol" aria-hidden="true"><?php echo esc_html($currency); ?></span>
                            <input type="text" class="formControl ttbm_price_validation" name="ticket_type_sale_price[]" placeholder="0.00" value="<?php echo esc_attr($sale_price); ?>"/>
                        </label>
                    </td>
                    <td class="ttbm-ticket-col--cap">
                        <input type="number" pattern="[0-9]*" step="1" min="0" class="formControl ttbm_number_validation" data-same-input="ticket_type_qty" name="ticket_type_qty[]" placeholder="500" value="<?php echo esc_attr($qty); ?>"/>
                    </td>
                    <td class="ttbm-ticket-col--default">
                        <input type="number" pattern="[0-9]*" step="1" class="formControl ttbm_number_validation" name="ticket_type_default_qty[]" placeholder="1" value="<?php echo esc_attr($default_qty); ?>"/>
                    </td>
                    <td class="ttbm-ticket-col--reserve">
                        <input type="number" pattern="[0-9]*" step="1" class="formControl ttbm_number_validation" data-same-input="ticket_type_resv_qty" name="ticket_type_resv_qty[]" placeholder="5" value="<?php echo esc_attr($reserve_qty); ?>"/>
                    </td>
                    <td class="ttbm-ticket-col--qty-type">
                        <select name="ticket_type_qty_type[]" class="formControl">
                            <option value="inputbox" <?php echo esc_attr($input_type == 'inputbox' ? 'selected' : ''); ?>><?php esc_html_e('Input Box', 'tour-booking-manager'); ?></option>
                            <option value="dropdown" <?php echo esc_attr($input_type == 'dropdown' ? 'selected' : ''); ?>><?php esc_html_e('Dropdown List', 'tour-booking-manager'); ?></option>
                        </select>
                    </td>
                    <td class="ttbm-ticket-col--actions">
                        <div class="ttbm-ticket-row__actions">
                            <button class="ttbm-ticket-row__delete ttbm_item_remove" type="button" title="<?php esc_attr_e('Remove ticket type', 'tour-booking-manager'); ?>">
                                <span class="fas fa-trash-alt" aria-hidden="true"></span>
                            </button>
                            <div class="ttbm-ticket-row__sort ttbm_sortable_button" title="<?php esc_attr_e('Drag to reorder', 'tour-booking-manager'); ?>">
                                <span class="fas fa-grip-vertical" aria-hidden="true"></span>
                            </div>
                        </div>
                    </td>
                </tr>
				<?php
			}
			public function save_ticket_types($post_id) {
				if (!isset($_POST['ttbm_ticket_item_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_ticket_item_nonce'])), 'ttbm_ticket_item_nonce')) {
					return;
				}
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
					return;
				}
				if (wp_is_post_revision($post_id)) {
					return;
				}
				if (!current_user_can('edit_post', $post_id)) {
					return;
				}
				$new_ticket_type = array();
				$icon = isset($_POST['ticket_type_icon']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_icon'])) : array();
				$names = isset($_POST['ticket_type_name']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_name'])) : array();
				$ticket_price = isset($_POST['ticket_type_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_price'])) : array();
				$sale_price = isset($_POST['ticket_type_sale_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_sale_price'])) : array();
				$qty = isset($_POST['ticket_type_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_qty'])) : array();
				$qty = apply_filters('ttbm_ticket_type_qty', $qty, $post_id);
				$default_qty = isset($_POST['ticket_type_default_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_default_qty'])) : array();
				$rsv = isset($_POST['ticket_type_resv_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_resv_qty'])) : array();
				$rsv = apply_filters('ttbm_ticket_type_resv_qty', $rsv, $post_id);
				$qty_type = isset($_POST['ticket_type_qty_type']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_qty_type'])) : array();
				$description = isset($_POST['ticket_type_description']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_description'])) : array();
				$count = count($names);
				for ($i = 0; $i < $count; $i++) {
					if ($names[$i] && isset($ticket_price[$i]) && $ticket_price[$i] >= 0) {
						$capacity_value = !empty($qty[$i]) ? $qty[$i] : 0;
						$new_ticket_type[$i]['ticket_type_icon'] = $icon[$i] ?? '';
						$new_ticket_type[$i]['ticket_type_name'] = $names[$i];
						$new_ticket_type[$i]['ticket_type_price'] = $ticket_price[$i];
						$new_ticket_type[$i]['sale_price'] = $sale_price[$i] ?? '';
						$new_ticket_type[$i]['ticket_type_qty'] = $capacity_value;
						$new_ticket_type[$i]['ticket_type_default_qty'] = $default_qty[$i] ?? 0;
						$new_ticket_type[$i]['ticket_type_resv_qty'] = $rsv[$i] ?? 0;
						$new_ticket_type[$i]['ticket_type_qty_type'] = $qty_type[$i] ?? 'inputbox';
						$new_ticket_type[$i]['ticket_type_description'] = $description[$i] ?? '';
					}
				}
				$new_ticket_type = apply_filters('ttbm_ticket_type_arr_save', $new_ticket_type);
				update_post_meta($post_id, 'ttbm_ticket_type', $new_ticket_type);
			}
		}
		new TTBM_Ticket_Types();
	}
