<?php
	if (!defined('ABSPATH')) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
	$tour_date = $tour_date ?? current(TTBM_Function::get_date($tour_id));
	$extra_services = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_extra_service_data', array());
	
	// Check if availability column should be hidden (global setting)
	$hide_availability_column = TTBM_Function::get_general_settings('ttbm_hide_availability_column', 'off');
	if (sizeof($extra_services) > 0) {
		?>
		<div class="ttbm_extra_service_area">
			<?php do_action('ttbm_before_extra_service_list_table', $tour_id); ?>
			<h2 class="extra_service_title"><?php echo esc_html__('Available Extra Service List ', 'tour-booking-manager'); ?></h2>
			<div class="ttbm_widget_content" data-placeholder>
				<table class="mp_tour_ticket_extra">
					<thead>
						<tr>
							<th class="extra-service-title-header"><?php esc_html_e('Service', 'tour-booking-manager'); ?></th>
							<th class="extra-service-price-header"><?php esc_html_e('Price', 'tour-booking-manager'); ?></th>
							<?php if ($hide_availability_column !== 'on') { ?>
								<th class="extra-service-availability-header"><?php esc_html_e('Availability', 'tour-booking-manager'); ?></th>
							<?php } ?>
							<th class="extra-service-quantity-header"><?php esc_html_e('Quantity', 'tour-booking-manager'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php
						foreach ($extra_services as $service) {
							$service_name = array_key_exists('service_name', $service) ? $service['service_name'] : '';
							$service_price = array_key_exists('service_price', $service) ? $service['service_price'] : 0;
							$service_price = TTBM_Global_Function::wc_price($tour_id, $service_price);
							$service_price_raw = TTBM_Global_Function::price_convert_raw($service_price);
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
							<?php
							// Calculate availability status and display logic
							$is_sold_out = $available <= 0;
							$stock_status = $is_sold_out ? 'sold_out' : 'in_stock';
							$percentage_sold = $service_qty > 0 ? round((($service_qty - $available) / $service_qty) * 100) : 0;
							$sold_qty = $service_qty - $available;
							?>
							<tr>
								<th class="extra-service-title">
									<?php if ($service_icon) { ?>
										<span class="<?php echo esc_attr($service_icon); ?>"></span>
									<?php } ?>
									<?php echo esc_html($service_name); ?>
                                    <?php if ($description) { ?>
									    <div class="mT_xs person-description"><?php TTBM_Custom_Layout::load_more_text($description, 100); ?></div>
                                    <?php } ?>
								</th>
								<td class="textCenter extra-service-price"><?php 
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo mep_esc_html($service_price); ?></td>
								<?php if ($hide_availability_column !== 'on') { ?>
									<td class="ttbm-availability-info">
										<div class="ttbm_availability_container">
											<div class="ttbm_stock_info ttbm_stock_<?php echo esc_attr($stock_status); ?>">
												<?php if ($is_sold_out) { ?>
													<span class="ttbm_stock_status sold_out">
														<i class="fas fa-times-circle"></i>
														<?php esc_html_e('Sold Out', 'tour-booking-manager'); ?>
													</span>
												<?php } else { ?>
													<div class="ttbm_availability_details">
														<div class="ttbm_remaining_count">
															<span class="ttbm_available_number"><?php echo esc_html($available); ?></span>
															<span class="ttbm_available_label">
																<?php echo $available === 1 ? esc_html__('service left', 'tour-booking-manager') : esc_html__('services left', 'tour-booking-manager'); ?>
															</span>
														</div>
														
														<div class="ttbm_capacity_info">
															<span class="ttbm_capacity_text">
																<?php printf(esc_html__('%1$d of %2$d sold', 'tour-booking-manager'), $sold_qty, $service_qty); ?>
															</span>
															<div class="ttbm_progress_bar">
																<div class="ttbm_progress_fill ttbm_progress_<?php echo esc_attr($stock_status); ?>" style="width: <?php echo esc_attr($percentage_sold); ?>%"></div>
															</div>
														</div>
													</div>
												<?php } ?>
											</div>
										</div>
									</td>
								<?php } ?>
								<td><?php TTBM_Layout::qty_input($service_name, $available, $input_type, $default_qty, $min_qty, $max_qty, $service_price_raw, 'service_qty[]'); ?></td>
							</tr>
							<tr>
								<td colspan="<?php echo $hide_availability_column === 'on' ? '3' : '4'; ?>">
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