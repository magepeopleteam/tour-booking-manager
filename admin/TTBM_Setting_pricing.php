<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Setting_pricing' ) ) {
		class TTBM_Setting_pricing {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 10 );
				add_action( 'add_ttbm_settings_tab_content', [ $this, 'pricing_tab_content' ], 10, 1 );
				add_action( 'ttbm_price_item', array( $this, 'pricing_item' ));
				add_action( 'ttbm_extra_service_item', array( $this, 'extra_service_item' ));
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_pricing">
					<span class="dashicons dashicons-money-alt"></span><?php esc_html_e( ' Pricing', 'tour-booking-manager' ); ?>
				</li>
				<?php
				do_action( 'ttbm_meta_box_tab_after_pricing' );
			}
			public function pricing_tab_content( $tour_id ) {
				$all_types = TTBM_Function::tour_type();
				$ttbm_type = TTBM_Function::get_tour_type( $tour_id );
				$display   = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' );
				$active    = $display == 'off' ? '' : 'mActive';
				$checked   = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_pricing" data-tabs="#ttbm_settings_pricing">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e( 'On/Off Registration', 'tour-booking-manager' ); ?></span>
						<?php TTBM_Layout::switch_button( 'ttbm_display_registration', $checked ); ?>
					</h5>
					<?php TTBM_Settings::des_p( 'ttbm_display_registration' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_registration" class="<?php echo esc_attr( $active ); ?>">
						<div class="_fdColumn_dLayout_xs">
							<label class="max_600">
								<span class="max_300"><?php esc_html_e( 'Tour Type', 'tour-booking-manager' ); ?></span>
								<select class="formControl" name="ttbm_type">
									<?php foreach ( $all_types as $key => $type ) { ?>
										<option value="<?php echo esc_attr( $key ) ?>" <?php echo esc_attr( $ttbm_type == $key ? 'selected' : '' ); ?>><?php echo esc_html( $type ) ?></option>
									<?php } ?>
								</select>
							</label>
							<?php TTBM_Settings::des_p( 'ttbm_type' ); ?>
							<?php do_action( 'ttbm_tour_pricing_before', $tour_id ); ?>
							<?php do_action( 'ttbm_hotel_pricing_before', $tour_id ); ?>
							<?php $this->ttbm_hotel_config( $tour_id ); ?>
							<?php do_action( 'ttbm_hotel_pricing_after', $tour_id ); ?>
						</div>
						<?php $this->ttbm_ticket_config( $tour_id ); ?>
						<?php do_action( 'ttbm_tour_pricing_after', $tour_id ); ?>
						<?php $this->advertise_addon(); ?>
						<?php do_action( 'ttbm_tour_exs_pricing_before', $tour_id ); ?>
						<?php $this->ttbm_extra_service_config( $tour_id ); ?>
						<?php do_action( 'ttbm_tour_exs_pricing_after', $tour_id ); ?>
						<div class="dLayout">
							<h5><?php esc_html_e( 'Add To Cart Form Shortcode : ', 'tour-booking-manager' ); ?> </h5><code> [ttbm-registration ttbm_id="<?php echo esc_html( $tour_id ); ?>"]</code>
							<?php TTBM_Settings::des_p( 'ttbm_short_code' ); ?>
						</div>
					</div>
				</div>
				<?php
			}
			public function ttbm_ticket_config( $tour_id ) {
				$ticket_type = TTBM_Function::get_post_info( $tour_id, 'ttbm_ticket_type', array() );
				$tour_label  = TTBM_Function::get_name();
				$ttbm_type   = TTBM_Function::get_tour_type( $tour_id );
				$type_class  = $ttbm_type == 'general' ? '' : 'dNone';
				$display     = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_advance', 'off' );
				$active      = $display == 'off' ? '' : 'mActive';
				$checked     = $display == 'off' ? '' : 'checked';
				?>

				<div class="ttbm_ticket_config <?php echo esc_html( $type_class ); ?>">
					<div class="_mT_dLayout_xs mp_settings_area ttbm_price_config">
						<h5><?php echo esc_html( $tour_label ) . ' ' . esc_html__( ' Price Configuration :', 'tour-booking-manager' ); ?></h5>
						<div class="divider"></div>
						<h5 class="dFlex">
							<span class="mR"><?php esc_html_e( 'Show advance columns', 'tour-booking-manager' ); ?></span>
							<?php TTBM_Layout::switch_button( 'ttbm_display_advance', $checked ); ?>
						</h5>
						<?php TTBM_Settings::des_p( 'ttbm_display_advance' ); ?>
						<?php do_action( 'ttbm_ticket_type_before', $tour_id ); ?>
						<div class="ovAuto _mT">
							<table>
								<thead>
								<tr>
									<?php do_action( 'ttbm_ticket_type_headeing_start', $tour_id ); ?>
									<th><?php esc_html_e( 'Ticket Icon', 'tour-booking-manager' ); ?></th>
									<th><?php esc_html_e( 'Ticket Name', 'tour-booking-manager' ); ?><span class="textRequired">&nbsp;*</span></th>
									<th data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
										<?php esc_html_e( 'Short Description', 'tour-booking-manager' ); ?>
									</th>
									<th><?php esc_html_e( 'Regular Price', 'tour-booking-manager' ); ?><span class="textRequired">&nbsp;*</span></th>
									<th data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
										<?php esc_html_e( 'Sale Price', 'tour-booking-manager' ); ?>
									</th>
									<th <?php do_action('ttbm_aq_target_hook',$tour_id); ?>><?php esc_html_e( 'Capacity', 'tour-booking-manager' ); ?><span class="textRequired">&nbsp;*</span></th>
									<th data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
										<?php esc_html_e( 'Default Qty', 'tour-booking-manager' ); ?>
									</th>
									<th data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
										<?php esc_html_e( "Reserve Qty", "tour-booking-manager" ); ?>
									</th>
									<?php do_action( 'ttbm_ticket_type_headeing_end', $tour_id ); ?>
									<th><?php esc_html_e( 'Qty Box Type', 'tour-booking-manager' ); ?></th>
									<th><?php esc_html_e( 'Action', 'tour-booking-manager' ); ?></th>
								</tr>
								</thead>
								<tbody class="mp_sortable_area mp_item_insert">
								<?php
									if ( sizeof( $ticket_type ) > 0 ) {
										foreach ( $ticket_type as $field ) {
											$this->pricing_item( $field );
										}
									}
								?>
								</tbody>
							</table>
						</div>
						<?php TTBM_Layout::add_new_button( esc_html__( 'Add New Ticket Type', 'tour-booking-manager' ) ); ?>
						<?php do_action( 'ttbm_hidden_item_table', 'ttbm_price_item' ); ?>
					</div>
					<?php do_action( 'ttbm_tour_pricing_inner', $tour_id ); ?>
				</div>
				<?php
			}
			public function pricing_item( $field = array() ) {
				$tour_id     = get_the_id();
				$display = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_advance', 'off' );
				$active  = $display == 'off' ? '' : 'mActive';
				$field       = $field ?: array();

				$icon        = array_key_exists( 'ticket_type_icon', $field ) ? $field['ticket_type_icon'] : '';
				$name        = array_key_exists( 'ticket_type_name', $field ) ? $field['ticket_type_name'] : '';
				$name_text   = preg_replace( "/[{}()<>+ ]/", '_', $name ) . '_' . $tour_id;
				$price       = array_key_exists( 'ticket_type_price', $field ) ? $field['ticket_type_price'] : '';
				$sale_price  = array_key_exists( 'sale_price', $field ) ? $field['sale_price'] : '';
				$qty         = array_key_exists( 'ticket_type_qty', $field ) ? $field['ticket_type_qty'] : '';
				$default_qty = array_key_exists( 'ticket_type_default_qty', $field ) ? $field['ticket_type_default_qty'] : '';
				$reserve_qty = array_key_exists( 'ticket_type_resv_qty', $field ) ? $field['ticket_type_resv_qty'] : '';
				$input_type  = array_key_exists( 'ticket_type_qty_type', $field ) ? $field['ticket_type_qty_type'] : 'inputbox';
				$description = array_key_exists( 'ticket_type_description', $field ) ? $field['ticket_type_description'] : '';
				?>
				<tr class="mp_remove_area">
					<?php do_action( 'ttbm_ticket_type_content_start', $field, $tour_id ) ?>
					<td><?php do_action( 'mp_input_add_icon', 'ticket_type_icon[]', $icon ); ?></td>
					<td>
						<input type="hidden" name="ttbm_hidden_ticket_text[]" value="<?php echo esc_attr( $name_text ); ?>"/>
						<label>
							<input type="text" class="formControl mp_name_validation" name="ticket_type_name[]" placeholder="Ex: Adult" value="<?php echo esc_attr( $name ); ?>" data-input-text="<?php echo esc_attr( $name_text ); ?>"/>
						</label>
					</td>
					<td data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
						<label>
							<input type="text" class="formControl" name="ticket_type_description[]" placeholder="Ex: description" value="<?php echo esc_attr( $description ); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="text" class="formControl mp_price_validation" name="ticket_type_price[]" placeholder="Ex: 10" value="<?php echo esc_attr( $price ); ?>"/>
						</label>
					</td>
					<td data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
						<label>
							<input type="text" class="formControl mp_price_validation" name="ticket_type_sale_price[]" placeholder="Ex: 10" value="<?php echo esc_attr( $sale_price ); ?>"/>
						</label>
					</td>
					<td <?php do_action('ttbm_aq_target_hook',$tour_id); ?>>
						<label>
							<input type="number" size="4" pattern="[0-9]*" step="1" class="formControl mp_number_validation" data-same-input="ticket_type_qty" name="ticket_type_qty[]" placeholder="Ex: 500" value="<?php echo esc_attr( $qty ); ?>"/>
						</label>
					</td>
					<td data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
						<label>
							<input type="number" size="4" pattern="[0-9]*" step="1" class="formControl mp_number_validation" name="ticket_type_default_qty[]" placeholder="Ex: 1" value="<?php echo esc_attr( $default_qty ); ?>"/>
						</label>
					</td>
					<td data-collapse="#ttbm_display_advance" class="<?php echo esc_attr( $active ); ?>">
						<label>
							<input type="number" size="4" pattern="[0-9]*" step="1" class="formControl mp_number_validation" data-same-input="ticket_type_resv_qty" name="ticket_type_resv_qty[]" placeholder="Ex: 5" value="<?php echo esc_attr( $reserve_qty ); ?>"/>
						</label>
					</td>
					<?php do_action( 'ttbm_ticket_type_content_end', $field, $tour_id ) ?>
					<td>
						<label>
							<select name="ticket_type_qty_type[]" class='formControl'>
								<option value="inputbox" <?php echo esc_attr( $input_type == 'inputbox' ? 'selected' : '' ); ?>><?php esc_html_e( 'Input Box', 'tour-booking-manager' ); ?></option>
								<option value="dropdown" <?php echo esc_attr( $input_type == 'dropdown' ? 'selected' : '' ); ?>><?php esc_html_e( 'Dropdown List', 'tour-booking-manager' ); ?></option>
							</select>
						</label>
					</td>
					<td><?php TTBM_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}
			public function ttbm_extra_service_config( $post_id ) {
				$tour_label              = TTBM_Function::get_name();
				$ttbm_extra_service_data = TTBM_Function::get_post_info( $post_id, 'ttbm_extra_service_data', array() );
				wp_nonce_field( 'ttbm_extra_service_data_nonce', 'ttbm_extra_service_data_nonce' );

				?>
				<div class="_mT_dLayout_xs mp_settings_area">
					<h5><?php echo esc_html( $tour_label ) . ' ' . esc_html__( ' Extra Service Price Configuration :', 'tour-booking-manager' ); ?></h5>
					<div class="divider"></div>

					<div class="ovAuto mt_xs">
						<table>
							<thead>
							<tr>
								<th><?php esc_html_e( 'Service Icon', 'tour-booking-manager' ); ?></th>
								<th><?php esc_html_e( 'Service Name', 'tour-booking-manager' ); ?></th>
								<th><?php esc_html_e( 'Short description', 'tour-booking-manager' ); ?></th>
								<th><?php esc_html_e( 'Service Price', 'tour-booking-manager' ); ?></th>
								<th><?php esc_html_e( 'Available Qty', 'tour-booking-manager' ); ?></th>
								<th><?php esc_html_e( 'Qty Box Type', 'tour-booking-manager' ); ?></th>
								<th><?php esc_html_e( 'Action', 'tour-booking-manager' ); ?></th>
							</tr>
							</thead>
							<tbody class="mp_sortable_area mp_item_insert">
							<?php
								if ( sizeof( $ttbm_extra_service_data ) > 0 ) {
									foreach ( $ttbm_extra_service_data as $field ) {
										$this->extra_service_item( $field );
									}
								}
							?>
							</tbody>
						</table>
					</div>
					<?php TTBM_Layout::add_new_button( esc_html__( 'Add Extra New Service', 'tour-booking-manager' ) ); ?>
					<?php do_action( 'ttbm_hidden_item_table', 'ttbm_extra_service_item' ); ?>
				</div>
				<?php
			}
			public function extra_service_item( $field = array() ) {
				$field         = $field ?: array();
				$tour_id       = get_the_id();
				$service_icon  = array_key_exists( 'service_icon', $field ) ? $field['service_icon'] : '';
				$service_name  = array_key_exists( 'service_name', $field ) ? $field['service_name'] : '';
				$service_price = array_key_exists( 'service_price', $field ) ? $field['service_price'] : '';
				$service_qty   = array_key_exists( 'service_qty', $field ) ? $field['service_qty'] : '';
				$input_type    = array_key_exists( 'service_qty_type', $field ) ? $field['service_qty_type'] : 'inputbox';
				$display     = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_extra_advance', 'off' );
				$active      = $display == 'off' ? '' : 'mActive';
				$description = array_key_exists( 'extra_service_description', $field ) ? $field['extra_service_description'] : '';
				?>
				<tr class="mp_remove_area">
					<?php do_action( 'ttbm_ticket_type_content_start', $field, $tour_id ) ?>
					<td><?php do_action( 'mp_input_add_icon', 'service_icon[]', $service_icon ); ?></td>
					<td>
						<label>
							<input type="text" class="formControl mp_name_validation" name="service_name[]" placeholder="Ex: Cap" value="<?php echo esc_attr( $service_name ); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="text" class="formControl" name="extra_service_description[]" placeholder="Ex: description" value="<?php echo esc_attr( $description ); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="number" pattern="[0-9]*" step="0.01" class="formControl mp_price_validation" name="service_price[]" placeholder="Ex: 10" value="<?php echo esc_attr( $service_price ); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<input type="number" pattern="[0-9]*" step="1" class="formControl mp_number_validation" name="service_qty[]" placeholder="Ex: 100" value="<?php echo esc_attr( $service_qty ); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<select name="service_qty_type[]" class='formControl'>
								<option value="inputbox" <?php echo esc_attr( $input_type == 'inputbox' ? 'selected' : '' ); ?>><?php esc_html_e( 'Input Box', 'tour-booking-manager' ); ?></option>
								<option value="dropdown" <?php echo esc_attr( $input_type == 'dropdown' ? 'selected' : '' ); ?>><?php esc_html_e( 'Dropdown List', 'tour-booking-manager' ); ?></option>
							</select>
						</label>
					</td>
					<td><?php TTBM_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}
			public function ttbm_hotel_config( $tour_id ) {
				$ttbm_hotels = TTBM_Function::get_hotel_list( $tour_id );
				$hotel_lists = TTBM_Query::get_hotel_list();
				$ttbm_type   = TTBM_Function::get_tour_type( $tour_id );
				$hotel_class = $ttbm_type == 'hotel' ? '' : 'dNone';
				?>
				<div class="_fdColumn_mT ttbm_tour_hotel_setting <?php echo esc_attr( $hotel_class ); ?>">
					<div class="divider"></div>
					<label>
						<span class="max_300"><?php esc_html_e( 'Hotel Configuration :', 'tour-booking-manager' ); ?></span>
						<select name="ttbm_hotels[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php esc_html_e( 'Please Select Hotel', 'tour-booking-manager' ); ?>">
							<?php
								foreach ( $hotel_lists->posts as $hotel ) {
									$hotel_id = $hotel->ID;
									?>
									<option value="<?php echo esc_attr( $hotel_id ) ?>" <?php echo in_array( $hotel_id, $ttbm_hotels ) ? 'selected' : ''; ?>><?php echo get_the_title( $hotel_id ); ?></option>
								<?php } ?>
						</select>
					</label>
					<p class="description">
						<?php esc_html_e( 'Select Hotel name that you want to include in this tour , Tour ticket price works based on hotel price configuration . To add new hotel  ', 'tour-booking-manager' ); ?>
						<a href="post-new.php?post_type=ttbm_hotel"><?php esc_html_e( 'click here', 'tour-booking-manager' ); ?></a>
					</p>
				</div>
				<?php
			}
			/*******************************/
			public function advertise_addon() {
				if ( ! class_exists( 'TTBMA_Seasonal_Pricing' ) ) {
					?>
					<div class="_dLayout_bgYellow_77">
						<div class="textColor_1 alignCenter">
							<span class="fas fa-dollar-sign fa-2x"></span> &nbsp;&nbsp;
							<strong>
								<?php esc_html_e( 'Seasonal pricing addon allow different pricing  based on  date range, time slot etc..  ', 'tour-booking-manager' ); ?>&nbsp;
								<a href="https://mage-people.com/product/seasonal-pricing-addon-for-woocommerce-tour-plugin/" target="_blank"><?php esc_html_e( 'Get your Seasonal price addon now', 'tour-booking-manager' ); ?></a>
							</strong>
						</div>
					</div>
					<?php
				}
				if ( ! class_exists( 'TTBMA_Group_Pricing' ) ) {
					?>
					<div class="_dLayout_bgColor_3">
						<div class="textColor_1 alignCenter">
							<span class="fas fa-fill-drip fa-2x"></span> &nbsp;&nbsp;
							<strong>
								<?php esc_html_e( 'Group price allow different pricing during buying based on quantity .', 'tour-booking-manager' ); ?>&nbsp;
								<a href="https://mage-people.com/product/group-pricing-or-bulk-qty-discount-addon-for-tour-plugin/" target="_blank"><?php esc_html_e( 'Get your Group pricing addon now', 'tour-booking-manager' ); ?></a>
							</strong>
						</div>
					</div>
					<?php
				}
			}
		}
		new TTBM_Setting_pricing();
	}