<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id      = $tour_id ?? TTBM_Function::post_id_multi_language( $ttbm_post_id );
$tour_date    = $tour_date ?? current( TTBM_Function::get_date( $tour_id ) );
$extra_services = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_extra_service_data', array() );
if ( ! is_array( $extra_services ) || empty( $extra_services ) ) {
	return;
}
?>
<div class="ttbm_extra_service_area ttbm_smart_addon_area">
	<?php do_action( 'ttbm_before_extra_service_list_table', $tour_id ); ?>
	<h3 class="ttbm_smart_addon_title"><?php esc_html_e( 'Optional Add-ons', 'tour-booking-manager' ); ?></h3>
	<div class="ttbm_smart_addon_list">
		<?php
		foreach ( $extra_services as $service ) {
			$service_name              = $service['service_name'] ?? '';
			$service_price_value       = $service['service_price'] ?? 0;
			$service_sale_price_value  = $service['service_sale_price'] ?? '';
			$has_sale                  = $service_sale_price_value !== '' && floatval( $service_sale_price_value ) > 0;
			$active_price_value        = $has_sale ? $service_sale_price_value : $service_price_value;
			$service_price             = TTBM_Global_Function::wc_price( $tour_id, $service_price_value );
			$service_sale_price_format = $has_sale ? TTBM_Global_Function::wc_price( $tour_id, $service_sale_price_value ) : '';
			$service_price_raw         = TTBM_Global_Function::price_convert_raw(
				TTBM_Global_Function::wc_price( $tour_id, $active_price_value )
			);
			$service_qty               = $service['service_qty'] ?? 0;
			$reserve                   = apply_filters( 'ttbm_service_reserve_qty', 0 );
			$input_type                = $service['service_qty_type'] ?? 'inputbox';
			$default_qty               = apply_filters( 'ttbm_service_type_default_qty', 0 );
			$min_qty                   = apply_filters( 'ttbm_service_type_min_qty', 0 );
			$max_qty                   = apply_filters( 'ttbm_service_type_max_qty', 0 );
			$service_inventory         = max( 0, (int) $service_qty );
			$sold_type                 = TTBM_Query::query_all_service_sold( $tour_id, $tour_date, $service_name );
			$available                 = max( 0, $service_inventory - ( (int) $sold_type + (int) $reserve ) );
			$is_sold_out               = $service_inventory <= 0 || $available <= 0;
			$data_ticket_name          = preg_replace( '/[^A-Za-z0-9\-]/', '', $service_name );
			$effective_max             = $max_qty > 0 ? min( (int) $max_qty, $available ) : (int) $available;
			?>
			<div class="ttbm_smart_addon_card ttbm_stock_<?php echo $is_sold_out ? 'sold_out' : 'in_stock'; ?>" data-addon-name="<?php echo esc_attr( $data_ticket_name ); ?>" data-available="<?php echo esc_attr( $effective_max ); ?>" data-service-inventory="<?php echo esc_attr( $service_inventory ); ?>">
				<div class="ttbm_smart_addon_row">
					<label class="ttbm_smart_addon_label<?php echo $is_sold_out ? ' is-disabled' : ''; ?>">
						<input type="checkbox" class="ttbm_smart_addon_check"<?php disabled( $is_sold_out ); ?> aria-label="<?php echo esc_attr( $service_name ); ?>">
						<span class="ttbm_smart_addon_checkmark" aria-hidden="true"></span>
						<span class="ttbm_smart_addon_name"><?php echo esc_html( $service_name ); ?></span>
					</label>
					<div class="ttbm_smart_addon_end">
						<span class="ttbm_smart_addon_price">
							+<?php echo wp_kses_post( $has_sale ? $service_sale_price_format : $service_price ); ?>
						</span>
						<?php if ( ! $is_sold_out && $input_type === 'inputbox' ) { ?>
							<div class="ttbm_smart_addon_qty">
								<div class="ticket-type-name" data-ticket-type-name="<?php echo esc_attr( $data_ticket_name ); ?>">
									<div class="groupContent qtyIncDec" data-ticket-type-name="<?php echo esc_attr( $data_ticket_name ); ?>">
										<div class="decQty addonGroupContent" aria-label="<?php esc_attr_e( 'Decrease quantity', 'tour-booking-manager' ); ?>">
											<span class="qty-btn-icon" aria-hidden="true"></span>
										</div>
										<label>
											<input type="text"
												class="formControl inputIncDec"
												data-price="<?php echo esc_attr( $service_price_raw ); ?>"
												data-available="<?php echo esc_attr( $effective_max ); ?>"
												name="service_qty[]"
												value="0"
												min="0"
												max="<?php echo esc_attr( $effective_max ); ?>"
											/>
										</label>
										<div class="incQty addonGroupContent" aria-label="<?php esc_attr_e( 'Increase quantity', 'tour-booking-manager' ); ?>">
											<span class="qty-btn-icon" aria-hidden="true"></span>
										</div>
									</div>
								</div>
							</div>
						<?php } elseif ( ! $is_sold_out && $input_type === 'dropdown' ) { ?>
							<div class="ttbm_smart_addon_qty ttbm_smart_addon_qty--dropdown">
								<?php
								ob_start();
								TTBM_Layout::qty_input( $service_name, $available, $input_type, $default_qty, $min_qty, $max_qty, $service_price_raw, 'service_qty[]', $tour_id );
								$qty_markup = ob_get_clean();
								echo $qty_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</div>
						<?php } ?>
					</div>
				</div>
				<?php if ( $is_sold_out ) { ?>
					<div class="ttbm_smart_addon_status">
						<span class="ttbm_smart_addon_sold_out"><?php esc_html_e( 'Sold out', 'tour-booking-manager' ); ?></span>
					</div>
				<?php } ?>
				<div class="ttbm_hidden_inputs">
					<input type="hidden" name="tour_id[]" value="<?php echo esc_attr( $tour_id ); ?>">
					<input type="hidden" name="service_name[]" value="<?php echo esc_attr( $service_name ); ?>">
					<input type="hidden" name="service_max_qty[]" value="<?php echo esc_attr( $max_qty ); ?>">
					<?php do_action( 'ttbm_after_service_type_item', $tour_id, $service ); ?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>
