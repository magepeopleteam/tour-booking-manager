<?php
	if (!defined('ABSPATH')) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
	$tour_date = $tour_date ?? current(TTBM_Function::get_date($tour_id));
	$extra_services = MP_Global_Function::get_post_info($tour_id, 'ttbm_extra_service_data', array());
	if (sizeof($extra_services) > 0) {
		?>
		<div class="ttbm_default_widget ttbm_extra_service_area">
			<?php do_action('ttbm_before_extra_service_list_table', $tour_id); ?>
			<h2 class="extra_service_title"><?php echo esc_html__('Available Extra Service List ', 'tour-booking-manager'); ?></h2>
			<div class="ttbm_widget_content" data-placeholder>
				<table class="mp_tour_ticket_extra">
					<thead>
					<tr>
						<th><?php echo TTBM_Function::service_name_text(); ?></th>
						<th><?php echo TTBM_Function::service_price_text(); ?></th>
						<th><?php echo TTBM_Function::service_qty_text(); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
						foreach ($extra_services as $service) {
							$service_name = array_key_exists('service_name', $service) ? $service['service_name'] : '';
							$service_price = array_key_exists('service_price', $service) ? $service['service_price'] : 0;
							$service_price = MP_Global_Function::wc_price($tour_id, $service_price);
							$service_price_raw = MP_Global_Function::price_convert_raw($service_price);
							$service_qty = array_key_exists('service_qty', $service) ? $service['service_qty'] : 0;
							$reserve = apply_filters('ttbm_service_reserve_qty', 0);
							$input_type = array_key_exists('service_qty_type', $service) ? $service['service_qty_type'] : 'inputbox';
							$default_qty = apply_filters('ttbm_service_type_default_qty', 0);
							$min_qty = apply_filters('ttbm_service_type_min_qty', 0);
							$max_qty = apply_filters('ttbm_service_type_max_qty', 0);
							$sold_type = TTBM_Query::query_all_service_sold($tour_id, $tour_date, $service_name);
							$available = $service_qty - ($sold_type + $reserve);
							$service_icon = array_key_exists('service_icon', $service) ? $service['service_icon'] : '';
							$description = array_key_exists('extra_service_description', $service) ? $service['extra_service_description'] : '';
							?>
							<tr>
								<th>
									<?php if ($service_icon) { ?>
										<span class="<?php echo esc_attr($service_icon); ?>"></span>
									<?php } ?>
									<?php echo MP_Global_Function::esc_html($service_name); ?>
                                    <?php if ($description) { ?>
									    <div class="mT_xs"><?php MP_Custom_Layout::load_more_text($description, 100); ?></div>
                                    <?php } ?>
								</th>
								<td class="text-center"><?php echo MP_Global_Function::esc_html($service_price); ?></td>
								<td><?php TTBM_Layout::qty_input($service_name, $available, $input_type, $default_qty, $min_qty, $max_qty, $service_price_raw, 'service_qty[]'); ?></td>
							</tr>
							<tr>
								<td colspan=3>
									<input type="hidden" name='tour_id[]' value='<?php echo esc_html($tour_id); ?>'>
									<input type="hidden" name='service_name[]' value='<?php echo esc_html($service_name); ?>'>
									<input type="hidden" name='service_max_qty[]' value='<?php echo esc_html($max_qty); ?>'>
									<?php do_action('ttbm_after_service_type_item', $tour_id, $service); ?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php } ?>