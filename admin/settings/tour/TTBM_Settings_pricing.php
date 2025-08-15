<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_pricing')) {
		class TTBM_Settings_pricing {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'pricing_tab_content'], 10, 1);
				add_action('ttbm_price_item', array($this, 'pricing_item'));
				add_action('wp_ajax_get_ttbm_insert_ticket_type', array($this, 'ticket_table'));
				add_action('wp_ajax_nopriv_get_ttbm_insert_ticket_type', array($this, 'ticket_table'));
			}
			public function pricing_tab_content($tour_id) {
				$all_types = TTBM_Function::tour_type();
				$ttbm_type = TTBM_Function::get_tour_type($tour_id);
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_registration', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
                <div class="tabsItem ttbm_settings_pricing" data-tabs="#ttbm_settings_pricing">
                    <div class="gptLayout">
                        <div class="alignCenter justifyBetween">
                            <h5><?php esc_html_e('Pricing Settings', 'tour-booking-manager'); ?></h5>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_registration', $checked); ?>
                        </div>
                        <span class="info_text"><?php TTBM_Settings::des_p('price_settings_description'); ?> </span>
                        <div data-collapse="#ttbm_display_registration" class="<?php echo esc_attr($active); ?>">
                            <div class="divider"></div>
                            <div class="dLayout">
                                <div class="alignCenter justifyBetween">
                                    <h6><?php esc_html_e('Tour Type', 'tour-booking-manager'); ?></h6>
                                    <label>
                                        <select class="formControl" name="ttbm_type">
											<?php foreach ($all_types as $key => $type) { ?>
                                                <option value="<?php echo esc_attr($key) ?>" <?php echo esc_attr($ttbm_type == $key ? 'selected' : ''); ?>><?php echo esc_html($type) ?></option>
											<?php } ?>
                                        </select>
                                    </label>
                                </div>
                                <span class="info_text"><?php TTBM_Settings::des_p('ttbm_display_registration'); ?></span>
								<?php do_action('ttbm_hotel_pricing_before', $tour_id); ?>
								<?php do_action('ttbm_tour_pricing_before', $tour_id); ?>
								<?php $this->ttbm_hotel_config($tour_id); ?>
								<?php do_action('ttbm_hotel_pricing_after', $tour_id); ?>
								<?php $this->ttbm_ticket_config($tour_id); ?>
                            </div>
                        </div>
                    </div>
					<?php do_action('ttbm_tour_pricing_inner', $tour_id); ?>
                    <div style="margin-bottom: 20px;">
						<?php $this->advertise_addon(); ?>
                    </div>
					<?php do_action('ttbm_tour_pricing_after', $tour_id); ?>
					<?php $this->ttbm_add_to_cart_form_shortcode($tour_id); ?>
                </div>
				<?php
			}
			public function ttbm_add_to_cart_form_shortcode($tour_id) {
				?>
                <section>
                    <div class="ttbm-header">
                        <h4><i class="fas fa-laptop-code"></i><?php esc_html_e('Pricing shortcode', 'tour-booking-manager'); ?></h4>
                        <code>[ttbm-registration ttbm_id="<?php echo esc_html($tour_id); ?>"]</code>
                    </div>
                    <p><?php esc_html_e('Displays a registration form with pricing details for a specific tour.', 'tour-booking-manager'); ?></p>
                </section>
				<?php
			}
			public function ttbm_ticket_config($tour_id) {
				$ticket_type = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticket_type', array());
				$ttbm_type = TTBM_Function::get_tour_type($tour_id);
				$type_class = $ttbm_type == 'general' ? '' : 'dNone';
				$all_forms = TTBM_Global_Function::query_post_type('ttbm_ticket_types');
				?>
                <div class="ttbm_ticket_config  <?php echo esc_html($type_class); ?>">
                    <div class="ttbm_settings_area ttbm_price_config">
						<?php do_action('ttbm_ticket_type_before', $tour_id); ?>
						<?php if ($all_forms->post_count > 0) { ?>
                            <div class="alignCenter justifyBetween _mT">
                                <h6><?php esc_html_e('Import Ticket type', 'tour-booking-manager'); ?></h6>
                                <label>
                                    <select class="formControl" name="ticket_type_import">
                                        <option value="" selected><?php esc_html_e('Select a Import Ticket type', 'tour-booking-manager'); ?></option>
										<?php foreach ($all_forms->posts as $form) { ?>
                                            <option value="<?php echo esc_attr($form->ID) ?>">
												<?php echo esc_html(get_the_title($form->ID)); ?>
                                            </option>
										<?php } ?>
                                    </select>
                                </label>
                            </div>
                            <span class="info_text"><?php TTBM_Settings::des_p('get_ticket_type'); ?></span>
						<?php } ?>
                        <div class="ttbm_settings_area _mT_xs">
                            <div class="ovAuto">
                                <table class="price_config_table">
                                    <thead>
                                    <tr>
										<?php do_action('ttbm_ticket_type_headeing_start', $tour_id); ?>
                                        <th><?php esc_html_e('Icon', 'tour-booking-manager'); ?></th>
                                        <th><?php esc_html_e('Ticket Type', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                        <th>
											<?php esc_html_e('Short Description', 'tour-booking-manager'); ?>
                                        </th>
                                        <th><?php esc_html_e('Regular Price', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                        <th>
											<?php esc_html_e('Sale Price', 'tour-booking-manager'); ?>
                                        </th>
                                        <th <?php do_action('ttbm_aq_target_hook', $tour_id); ?>><?php esc_html_e('Capacity', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                        <th>
											<?php esc_html_e('Default Qty', 'tour-booking-manager'); ?>
                                        </th>
                                        <th>
											<?php esc_html_e("Reserve Qty", "tour-booking-manager"); ?>
                                            <i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Add Reserve quantity', 'tour-booking-manager'); ?></span></i>
                                        </th>
										<?php do_action('ttbm_ticket_type_headeing_end', $tour_id); ?>
                                        <th><?php esc_html_e('Qty Box Type', 'tour-booking-manager'); ?></th>
                                        <th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="ttbm_sortable_area ttbm_item_insert ttbm_insert_ticket_type">
									<?php
										if (sizeof($ticket_type) > 0) {
											foreach ($ticket_type as $field) {
												$this->pricing_item($field);
											}
										}
									?>
                                    </tbody>
                                </table>
                            </div>
							<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add New Ticket Type', 'tour-booking-manager')); ?>
							<?php do_action('add_ttbm_hidden_table', 'ttbm_price_item'); ?>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function ticket_table() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$form_id = isset($_POST['form_id']) ? sanitize_text_field(wp_unslash($_POST['form_id'])) : '';
				$post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
				$ticket_type = TTBM_Global_Function::get_post_info($form_id, 'ttbm_ticket_type', array());
				if (sizeof($ticket_type) > 0) {
					foreach ($ticket_type as $field) {
						$this->pricing_item($field, $post_id);
					}
				}
				die();
			}
			public function pricing_item($field = array(), $tour_id = '') {
				$tour_id = $tour_id ?: get_the_id();
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_advance', 'off');
				$active = $display == 'off' ? '' : 'mActive';
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
                <tr class="ttbm_remove_area">
					<?php do_action('ttbm_ticket_type_content_start', $field, $tour_id) ?>
                    <td><?php do_action('ttbm_input_add_icon', 'ticket_type_icon[]', $icon); ?></td>
                    <td>
                        <input type="hidden" name="ttbm_hidden_ticket_text[]" value="<?php echo esc_attr($name_text); ?>"/>
                        <input type="text" class="medium ttbm_name_validation" name="ticket_type_name[]" placeholder="Ex: Adult" value="<?php echo esc_attr($name); ?>" data-input-text="<?php echo esc_attr($name_text); ?>"/>
                    </td>
                    <td>
                        <input type="text" class="" name="ticket_type_description[]" placeholder="Ex: description" value="<?php echo esc_attr($description); ?>"/>
                    </td>
                    <td>
                        <input type="text" class="medium ttbm_price_validation" name="ticket_type_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($price); ?>"/>
                    </td>
                    <td>
                        <input type="text" class="medium ttbm_price_validation" name="ticket_type_sale_price[]" placeholder="Ex: 10" value="<?php echo esc_attr($sale_price); ?>"/>
                    </td>
                    <td <?php do_action('ttbm_aq_target_hook', $tour_id); ?>>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="medium ttbm_number_validation" data-same-input="ticket_type_qty" name="ticket_type_qty[]" placeholder="Ex: 500" value="<?php echo esc_attr($qty); ?>"/>
                    </td>
                    <td>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="medium ttbm_number_validation" name="ticket_type_default_qty[]" placeholder="Ex: 1" value="<?php echo esc_attr($default_qty); ?>"/>
                    </td>
                    <td>
                        <input type="number" size="4" pattern="[0-9]*" step="1" class="medium ttbm_number_validation" data-same-input="ticket_type_resv_qty" name="ticket_type_resv_qty[]" placeholder="Ex: 5" value="<?php echo esc_attr($reserve_qty); ?>"/>
                    </td>
					<?php do_action('ttbm_ticket_type_content_end', $field, $tour_id) ?>
                    <td>
                        <select name="ticket_type_qty_type[]" class='medium formControl'>
                            <option value="inputbox" <?php echo esc_attr($input_type == 'inputbox' ? 'selected' : ''); ?>><?php esc_html_e('Input Box', 'tour-booking-manager'); ?></option>
                            <option value="dropdown" <?php echo esc_attr($input_type == 'dropdown' ? 'selected' : ''); ?>><?php esc_html_e('Dropdown List', 'tour-booking-manager'); ?></option>
                        </select>
                    </td>
                    <td><?php TTBM_Custom_Layout::move_remove_button(); ?></td>
                </tr>
				<?php
			}
			public function ttbm_hotel_config($tour_id) {
				$ttbm_hotels = TTBM_Function::get_hotel_list($tour_id);
				$hotel_lists = TTBM_Global_Function::query_post_type('ttbm_hotel');
				$ttbm_type = TTBM_Function::get_tour_type($tour_id);
				$hotel_class = $ttbm_type == 'hotel' ? 'dBlock' : 'dNone';
				?>
                <div class="ttbm_tour_hotel_setting <?php echo esc_attr($hotel_class); ?>">
                    <label class="label">
                        <div>
                            <p><?php esc_html_e('Hotel Configuration', 'tour-booking-manager'); ?> <i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttip_hotel_config') ?></span></i></p>
                            <span class="text"><?php TTBM_Settings::des_p('hotel_config'); ?><a href="<?php echo esc_url(admin_url('post-new.php?post_type=ttbm_hotel')); ?>"><?php TTBM_Settings::des_p('hotel_config_click') ?></a></span>
                        </div>
                    </label>
                    <select name="ttbm_hotels[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php esc_html_e('Please Select Hotel', 'tour-booking-manager'); ?>">
						<?php
							foreach ($hotel_lists->posts as $hotel) {
								$hotel_id = $hotel->ID;
								?>
                                <option value="<?php echo esc_attr($hotel_id) ?>" <?php echo esc_attr(in_array($hotel_id, $ttbm_hotels) ? 'selected' : ''); ?>><?php echo esc_html(get_the_title($hotel_id)); ?></option>
							<?php } ?>
                    </select>
                </div>
				<?php
			}
			/*******************************/
			public function advertise_addon() {
				if (!class_exists('TTBMA_Seasonal_Pricing')) {
					?>
                    <div class="_dLayout_bgYellow_77">
                        <div class="textColor_1 alignCenter d-flex align-items-center">
                            <span class="fas fa-dollar-sign fa-2x"></span> &nbsp;&nbsp;
                            <strong>
								<?php esc_html_e('Seasonal pricing addon allow different pricing  based on  date range, time slot etc..  ', 'tour-booking-manager'); ?>&nbsp;
                                <a href="https://mage-people.com/product/seasonal-pricing-addon-for-woocommerce-tour-plugin/" target="_blank"><?php esc_html_e('Get your Seasonal price addon now', 'tour-booking-manager'); ?></a> </strong>
                        </div>
                    </div>
					<?php
				}
				if (!class_exists('TTBMA_Group_Pricing')) {
					?>
                    <div class="_dLayout_bgColor_3">
                        <div class="textColor_1 alignCenter d-flex align-items-center">
                            <span class="fas fa-fill-drip fa-2x"></span> &nbsp;&nbsp;
                            <strong>
								<?php esc_html_e('Group price allow different pricing during buying based on quantity .', 'tour-booking-manager'); ?>&nbsp;
                                <a href="https://mage-people.com/product/group-pricing-or-bulk-qty-discount-addon-for-tour-plugin/" target="_blank"><?php esc_html_e('Get your Group pricing addon now', 'tour-booking-manager'); ?></a> </strong>
                        </div>
                    </div>
					<?php
				}
			}
		}
		new TTBM_Settings_pricing();
	}