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
			private function current_user_can_edit_ticket_context($post_id, $form_id) {
				return $post_id > 0 && $form_id > 0 && current_user_can('edit_post', $post_id) && current_user_can('edit_post', $form_id);
			}
			public function pricing_tab_content($tour_id) {
				$all_types = TTBM_Function::tour_type();
				$ttbm_type = TTBM_Function::get_tour_type($tour_id);
				$display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_registration', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
                
				?>
                <div class="tabsItem ttbm_settings_pricing" data-tabs="#ttbm_settings_pricing">
                    <h2><?php esc_html_e('Price Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('You have the flexibility to configure your tour pricing or disable registration by toggling the options. This will ensure that all necessary tour information is accurately displayed.', 'tour-booking-manager'); ?></p>
                    
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="mi mi-coins"></i><?php esc_html_e('Tour Ticket Price Settings', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_registration', $checked); ?>
                        </div>
                        <div data-collapse="#ttbm_display_registration" class="<?php echo esc_attr($active); ?>">
                            <div class="ttbm-pricing-types">
                                <input type="hidden" name="ttbm_type" value="<?php echo esc_attr($ttbm_type); ?>"/>
                                <?php foreach ($all_types as $key => $type) { ?>
                                    <div data-price-type="<?php echo esc_attr($key) ?>" class="ttbm-pricing-type <?php echo esc_attr($ttbm_type == $key ? 'active' : ''); ?>">
                                        <div class="ttbm-pricing-type__icon">
                                            <i class="<?php echo esc_html($type['icon']) ?>"></i>
                                        </div>
                                        <div class="ttbm-pricing-type__content">
                                            <h6><?php echo esc_html($type['title']) ?></h6>
                                            <p><?php echo esc_html($type['description']) ?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="dLayout">
								
								<?php do_action('ttbm_tour_pricing_before', $tour_id); ?>
                                <?php $this->ttbm_ticket_config($tour_id); ?>
                                <?php do_action('ttbm_hotel_pricing_before', $tour_id); ?>
								<?php $this->ttbm_hotel_config($tour_id); ?>
								<?php do_action('ttbm_hotel_pricing_after', $tour_id); ?>
								
                            </div>
                        </div>
                    </section>
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
                            <div class="label">
                                <p><?php esc_html_e('Import Ticket type', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('You can import ticket types here . Create new ticket types', 'tour-booking-manager'); ?><a href="post-new.php?post_type=ttbm_ticket_types"> <?php esc_html_e('Click Me', 'tour-booking-manager'); ?></a></span></i></p>
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
						<?php } ?>
                        <div class="ttbm_settings_area _mT_xs ttbm-ticket-types-area">
                            <div class="ttbm-ticket-types-panel">
                                <div class="ttbm-ticket-types-table-wrap">
                                <table class="price_config_table ttbm-ticket-types-table">
                                    <thead>
                                    <tr>
										<?php do_action('ttbm_ticket_type_headeing_start', $tour_id); ?>
                                        <th class="ttbm-ticket-col--name"><?php esc_html_e('Ticket Name', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                        <th class="ttbm-ticket-col--desc">
											<?php esc_html_e('Short Description', 'tour-booking-manager'); ?>
                                        </th>
                                        <th class="ttbm-ticket-col--price"><?php esc_html_e('Reg. Price', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span></th>
                                        <th class="ttbm-ticket-col--sale">
											<?php esc_html_e('Sale Price', 'tour-booking-manager'); ?>
                                        </th>
                                        <th class="ttbm-ticket-col--cap">
											<span class="ttbm-ticket-th-label"><?php esc_html_e('Capacity', 'tour-booking-manager'); ?><span class="textRequired">&nbsp;*</span><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Total available seats for this ticket type (inventory).', 'tour-booking-manager'); ?></span></i></span>
                                        </th>
                                        <th class="ttbm-ticket-col--default">
											<?php esc_html_e('Def. Qty', 'tour-booking-manager'); ?>
                                        </th>
                                        <th class="ttbm-ticket-col--reserve">
											<span class="ttbm-ticket-th-label"><?php esc_html_e('Res. Qty', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Add Reserve quantity', 'tour-booking-manager'); ?></span></i></span>
                                        </th>
										<?php do_action('ttbm_ticket_type_headeing_end', $tour_id); ?>
                                        <th class="ttbm-ticket-col--qty-type"><?php esc_html_e('Qty Box Type', 'tour-booking-manager'); ?></th>
                                        <th class="ttbm-ticket-col--actions"><span class="screen-reader-text"><?php esc_html_e('Action', 'tour-booking-manager'); ?></span></th>
                                    </tr>
                                    </thead>
                                    <tbody class="ttbm_sortable_area ttbm_item_insert ttbm_insert_ticket_type">
									<?php
										if (!empty($ticket_type)) {
											foreach ($ticket_type as $field) {
												$this->pricing_item($field, $tour_id);
											}
										} else {
											$this->pricing_item(array(), $tour_id);
										}
									?>
                                    </tbody>
                                </table>
                                </div>
                                <p id="ttbm_ticket_types_error" class="textRequired ttbm-ticket-panel-error" style="display:none;"></p>
                            </div>
							<?php TTBM_Custom_Layout::add_new_button(esc_html__('Add Ticket Type', 'tour-booking-manager'), 'ttbm_add_item', 'ttbm-ticket-types-add-btn', 'fas fa-plus'); ?>
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
				$form_id = isset($_POST['form_id']) ? absint(wp_unslash($_POST['form_id'])) : 0;
				$post_id = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;
				if (!$this->current_user_can_edit_ticket_context($post_id, $form_id)) {
					wp_send_json_error(['message' => esc_html__('You do not have permission to access this ticket configuration.', 'tour-booking-manager')], 403);
				}
				$ticket_type = TTBM_Global_Function::get_post_info($form_id, 'ttbm_ticket_type', array());
				if (!empty($ticket_type)) {
					foreach ($ticket_type as $field) {
						$this->pricing_item($field, $post_id);
					}
				} else {
					$this->pricing_item(array(), $post_id);
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
				$default_qty = array_key_exists('ticket_type_default_qty', $field) && $field['ticket_type_default_qty'] !== '' ? $field['ticket_type_default_qty'] : '1';
				$reserve_qty = array_key_exists('ticket_type_resv_qty', $field) ? $field['ticket_type_resv_qty'] : '';
				$input_type = array_key_exists('ticket_type_qty_type', $field) ? $field['ticket_type_qty_type'] : 'inputbox';
				$description = array_key_exists('ticket_type_description', $field) ? $field['ticket_type_description'] : '';
				?>
                <tr class="ttbm_remove_area ttbm-ticket-type-row">
					<?php do_action('ttbm_ticket_type_content_start', $field, $tour_id) ?>
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
                            <span class="ttbm-ticket-input-prefix__symbol" aria-hidden="true"><?php echo function_exists('get_woocommerce_currency_symbol') ? esc_html(get_woocommerce_currency_symbol()) : '$'; ?></span>
                            <input type="text" class="formControl ttbm_price_validation" name="ticket_type_price[]" placeholder="0.00" value="<?php echo esc_attr($price); ?>"/>
                        </label>
                    </td>
                    <td class="ttbm-ticket-col--sale">
                        <label class="ttbm-ticket-input-prefix">
                            <span class="ttbm-ticket-input-prefix__symbol" aria-hidden="true"><?php echo function_exists('get_woocommerce_currency_symbol') ? esc_html(get_woocommerce_currency_symbol()) : '$'; ?></span>
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
					<?php do_action('ttbm_ticket_type_content_end', $field, $tour_id) ?>
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
				$show_seasonal = ! class_exists( 'TTBMA_Seasonal_Pricing' );
				$show_group    = ! class_exists( 'TTBMA_Group_Pricing' );
				if ( ! $show_seasonal && ! $show_group ) {
					return;
				}
				?>
				<div class="ttbm-addon-promos">
					<?php if ( $show_seasonal ) { ?>
					<div class="ttbm-addon-promo ttbm-addon-promo--amber">
						<div class="ttbm-addon-promo__icon">
							<i class="fas fa-calendar-alt"></i>
						</div>
						<div class="ttbm-addon-promo__content">
							<span class="ttbm-addon-promo__badge"><?php esc_html_e( 'Addon', 'tour-booking-manager' ); ?></span>
							<strong class="ttbm-addon-promo__title"><?php esc_html_e( 'Seasonal Pricing', 'tour-booking-manager' ); ?></strong>
							<p class="ttbm-addon-promo__desc"><?php esc_html_e( 'Set different prices based on date ranges, time slots, and seasons — perfect for peak & off-peak management.', 'tour-booking-manager' ); ?></p>
						</div>
						<a href="https://mage-people.com/product/seasonal-pricing-addon-for-woocommerce-tour-plugin/" target="_blank" class="ttbm-addon-promo__btn">
							<?php esc_html_e( 'Get Addon', 'tour-booking-manager' ); ?>
							<i class="fas fa-arrow-right"></i>
						</a>
					</div>
					<?php } ?>
					<?php if ( $show_group ) { ?>
					<div class="ttbm-addon-promo ttbm-addon-promo--blue">
						<div class="ttbm-addon-promo__icon">
							<i class="fas fa-users"></i>
						</div>
						<div class="ttbm-addon-promo__content">
							<span class="ttbm-addon-promo__badge"><?php esc_html_e( 'Addon', 'tour-booking-manager' ); ?></span>
							<strong class="ttbm-addon-promo__title"><?php esc_html_e( 'Group Pricing', 'tour-booking-manager' ); ?></strong>
							<p class="ttbm-addon-promo__desc"><?php esc_html_e( 'Offer quantity-based pricing for group bookings — ideal for bulk discounts and party rates.', 'tour-booking-manager' ); ?></p>
						</div>
						<a href="https://mage-people.com/product/group-pricing-or-bulk-qty-discount-addon-for-tour-plugin/" target="_blank" class="ttbm-addon-promo__btn">
							<?php esc_html_e( 'Get Addon', 'tour-booking-manager' ); ?>
							<i class="fas fa-arrow-right"></i>
						</a>
					</div>
					<?php } ?>
				</div>
				<?php
			}
		}
		new TTBM_Settings_pricing();
	}
