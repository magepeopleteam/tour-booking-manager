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
			$sold_type                 = TTBM_Query::query_all_service_sold( $tour_id, $tour_date, $service_name );
			$available                 = $service_qty - ( $sold_type + $reserve );
			$is_sold_out               = $available <= 0;
			$data_ticket_name          = preg_replace( '/[^A-Za-z0-9\-]/', '', $service_name );
			$effective_max             = $max_qty > 0 ? min( (int) $max_qty, (int) $available ) : (int) $available;
			?>
			<div class="ttbm_smart_addon_card ttbm_stock_<?php echo $is_sold_out ? 'sold_out' : 'in_stock'; ?>" data-addon-name="<?php echo esc_attr( $data_ticket_name ); ?>">
				<label class="ttbm_smart_addon_label<?php echo $is_sold_out ? ' is-disabled' : ''; ?>">
					<input type="checkbox" class="ttbm_smart_addon_check"<?php disabled( $is_sold_out ); ?> aria-label="<?php echo esc_attr( $service_name ); ?>">
					<span class="ttbm_smart_addon_checkmark" aria-hidden="true"></span>
					<span class="ttbm_smart_addon_name"><?php echo esc_html( $service_name ); ?></span>
					<span class="ttbm_smart_addon_price">
						+<?php echo wp_kses_post( $has_sale ? $service_sale_price_format : $service_price ); ?>
					</span>
				</label>
				<div class="ttbm_smart_addon_qty">
					<?php if ( ! $is_sold_out && $input_type === 'inputbox' ) { ?>
						<div class="ticket-type-name" data-ticket-type-name="<?php echo esc_attr( $data_ticket_name ); ?>">
							<div class="groupContent qtyIncDec" data-ticket-type-name="<?php echo esc_attr( $data_ticket_name ); ?>">
								<div class="decQty addonGroupContent" aria-label="<?php esc_attr_e( 'Decrease quantity', 'tour-booking-manager' ); ?>">
									<span class="qty-btn-icon" aria-hidden="true"></span>
								</div>
								<label>
									<input type="text"
										class="formControl inputIncDec"
										data-price="<?php echo esc_attr( $service_price_raw ); ?>"
										name="service_qty[]"
										value="0"
										min="<?php echo esc_attr( $min_qty ); ?>"
										max="<?php echo esc_attr( $effective_max ); ?>"
									/>
								</label>
								<div class="incQty addonGroupContent" aria-label="<?php esc_attr_e( 'Increase quantity', 'tour-booking-manager' ); ?>">
									<span class="qty-btn-icon" aria-hidden="true"></span>
								</div>
							</div>
						</div>
					<?php } else { ?>
						<input type="hidden" name="service_qty[]" value="0"/>
						<span class="ttbm_smart_addon_sold_out"><?php esc_html_e( 'Sold out', 'tour-booking-manager' ); ?></span>
					<?php } ?>
				</div>
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
