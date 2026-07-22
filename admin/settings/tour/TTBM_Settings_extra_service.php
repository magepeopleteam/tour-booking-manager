<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_extra_service')) {
		class TTBM_Settings_extra_service {
			public function __construct() {
				add_action('ttbm_tour_pricing_after', [$this, 'render_extra_service_section'], 999, 1);
				add_action('ttbm_extra_service_item', array($this, 'extra_service_item'));
			}
			public function render_extra_service_section($tour_id) {
				do_action('ttbm_tour_exs_pricing_before', $tour_id);
				$this->ttbm_extra_service_config($tour_id);
				do_action('ttbm_tour_exs_pricing_after', $tour_id);
			}
			public function ttbm_extra_service_config($post_id) {
				$ttbm_extra_service_data = TTBM_Global_Function::get_post_info($post_id, 'ttbm_extra_service_data', array());
				?>
                <section class="ttbm_settings_area ttbm-extra-service-area">
                        <div class="ttbm-header">
                            <h4><i class="fas fa-parachute-box" aria-hidden="true"></i><?php esc_html_e('Extra Service', 'tour-booking-manager'); ?></h4>
                        </div>
                        <div class="ttbm-extra-service-panel">
                            <div class="ttbm-extra-service-table-wrap">
                                <table class="ttbm-extra-service-table">
                                    <thead>
                                    <tr>
                                        <th class="ttbm-extra-col--icon"><?php esc_html_e('Icon', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-extra-col--name"><?php esc_html_e('Service Name', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-extra-col--desc"><?php esc_html_e('Short Description', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-extra-col--price"><?php esc_html_e('Price', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-extra-col--sale"><?php esc_html_e('Sale Price', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-extra-col--qty"><?php esc_html_e('Available Qty', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-extra-col--qty-type"><?php esc_html_e('Qty Box Type', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-extra-col--actions"><span class="screen-reader-text"><?php esc_html_e('Action', 'tour-booking-manager'); ?></span></th>
                                    </tr>
                                    </thead>
                                    <tbody class="ttbm_sortable_area ttbm_item_insert ttbm_insert_extra_service">
									<?php
										if (!empty($ttbm_extra_service_data)) {
											foreach ($ttbm_extra_service_data as $field) {
												$this->extra_service_item($field);
											}
										} else {
											$this->extra_service_item(array());
										}
									?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
							<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add Extra Service', 'tour-booking-manager'), 'ttbm_add_item', 'ttbm-ticket-types-add-btn', 'fas fa-plus'); ?>
						<?php do_action('add_ttbm_hidden_table', 'ttbm_extra_service_item'); ?>
                </section>
				<?php
			}
			public function extra_service_item($field = array()) {
				$field = $field ?: array();
				$tour_id = get_the_id();
				$service_icon = array_key_exists('service_icon', $field) ? $field['service_icon'] : '';
				$service_name = array_key_exists('service_name', $field) ? $field['service_name'] : '';
				$service_price = array_key_exists('service_price', $field) ? $field['service_price'] : '';
				$service_sale_price = array_key_exists('service_sale_price', $field) ? $field['service_sale_price'] : '';
				$service_qty = array_key_exists('service_qty', $field) ? $field['service_qty'] : '';
				$input_type = array_key_exists('service_qty_type', $field) ? $field['service_qty_type'] : 'inputbox';
				$description = array_key_exists('extra_service_description', $field) ? $field['extra_service_description'] : '';
				?>
                <tr class="ttbm_remove_area ttbm-extra-service-row">
					<?php do_action('ttbm_ticket_type_content_start', $field, $tour_id) ?>
                    <td class="ttbm-extra-col--icon">
                        <div class="ttbm-extra-service-icon">
							<?php do_action('ttbm_input_add_icon', 'service_icon[]', $service_icon); ?>
                        </div>
                    </td>
                    <td class="ttbm-extra-col--name">
                        <input type="text" class="formControl ttbm_name_validation" name="service_name[]" placeholder="<?php esc_attr_e('Ex: Cap', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($service_name); ?>"/>
                    </td>
                    <td class="ttbm-extra-col--desc">
                        <input type="text" class="formControl" name="extra_service_description[]" placeholder="<?php esc_attr_e('Ex: description', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($description); ?>"/>
                    </td>
                    <td class="ttbm-extra-col--price">
                        <label class="ttbm-ticket-input-prefix">
                            <span class="ttbm-ticket-input-prefix__symbol" aria-hidden="true"><?php echo function_exists('get_woocommerce_currency_symbol') ? esc_html(get_woocommerce_currency_symbol()) : '$'; ?></span>
                            <input type="text" class="formControl ttbm_price_validation" name="service_price[]" placeholder="0.00" value="<?php echo esc_attr($service_price); ?>"/>
                        </label>
                    </td>
                    <td class="ttbm-extra-col--sale">
                        <label class="ttbm-ticket-input-prefix">
                            <span class="ttbm-ticket-input-prefix__symbol" aria-hidden="true"><?php echo function_exists('get_woocommerce_currency_symbol') ? esc_html(get_woocommerce_currency_symbol()) : '$'; ?></span>
                            <input type="text" class="formControl ttbm_price_validation" name="service_sale_price[]" placeholder="0.00" value="<?php echo esc_attr($service_sale_price); ?>"/>
                        </label>
                    </td>
                    <td class="ttbm-extra-col--qty">
                        <input type="number" pattern="[0-9]*" step="1" class="formControl ttbm_number_validation" name="service_qty[]" placeholder="<?php esc_attr_e('Ex: 100', 'tour-booking-manager'); ?>" value="<?php echo esc_attr($service_qty); ?>"/>
                    </td>
                    <td class="ttbm-extra-col--qty-type">
                        <select name="service_qty_type[]" class="formControl">
                            <option value="inputbox" <?php echo esc_attr($input_type == 'inputbox' ? 'selected' : ''); ?>><?php esc_html_e('Input Box', 'tour-booking-manager'); ?></option>
                            <option value="dropdown" <?php echo esc_attr($input_type == 'dropdown' ? 'selected' : ''); ?>><?php esc_html_e('Dropdown List', 'tour-booking-manager'); ?></option>
                        </select>
                    </td>
                    <td class="ttbm-extra-col--actions">
                        <div class="ttbm-ticket-row__actions">
                            <button class="ttbm-ticket-row__delete ttbm_item_remove" type="button" title="<?php esc_attr_e('Remove extra service', 'tour-booking-manager'); ?>">
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
		}
		new TTBM_Settings_extra_service();
	}
