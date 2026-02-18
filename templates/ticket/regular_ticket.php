<?php
// FIXED: Removed leading tab before PHP opening tag - 2025-01-21 by Shahnur Alam
/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
	die;
}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
	$tour_date = $tour_date ?? current(TTBM_Function::get_date($tour_id));
	$ticket_lists = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticket_type', array());
	$available_seat = TTBM_Function::get_total_available($tour_id, $tour_date);
	$availability_info = TTBM_Function::get_ticket_availability_info($tour_id, $tour_date);
	
	// Check if availability column should be hidden (global setting)
	$hide_availability_column = TTBM_Function::get_general_settings('ttbm_hide_availability_column', 'off');
	if (sizeof($ticket_lists) > 0) {
		do_action('ttbm_before_ticket_type_area', $tour_id, $tour_date);
		?>
		<div class="ttbm_ticket_area ttbm_enhanced_ticket_area">
			<!-- Hidden elements for JavaScript functionality -->
			<span class="ttbm_last_updated" data-tour-id="<?php echo esc_attr($tour_id); ?>" data-tour-date="<?php echo esc_attr($tour_date); ?>" style="display: none;"></span>
			<span id="ttbm_total_available" style="display: none;"><?php echo esc_html($available_seat); ?></span>
			<div class="">
				<?php
					$option_name = 'ttbm_string_availabe_ticket_list';
					$default_title = esc_html__('Available Ticket List ', 'tour-booking-manager');
				?>
				<div class="ttbm_widget_content" data-placeholder>
					<table class="mp_tour_ticket_type ttbm_enhanced_table">
						<thead class="ttbm_table_header">
							<tr>
								<th class="ttbm_ticket_info"><?php esc_html_e('Ticket Type', 'tour-booking-manager'); ?></th>
								<th class="ttbm_price_info"><?php esc_html_e('Price', 'tour-booking-manager'); ?></th>
								<?php if ($hide_availability_column !== 'on') { ?>
									<th class="ttbm_availability_info"><?php esc_html_e('Availability', 'tour-booking-manager'); ?></th>
								<?php } ?>
								<th class="ttbm_quantity_info"><?php esc_html_e('Quantity', 'tour-booking-manager'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php
							foreach ($ticket_lists as $index => $ticket) {
								$ticket_name = array_key_exists('ticket_type_name', $ticket) ? $ticket['ticket_type_name'] : '';
								$price = TTBM_Function::get_price_by_name($ticket_name, $tour_id, '', '', $tour_date);
								$regular_price = TTBM_Function::check_discount_price_exit($tour_id, $ticket_name, '', '', $tour_date);
								$ticket_price = TTBM_Global_Function::wc_price($tour_id, $price);
								$ticket_price_raw = TTBM_Global_Function::price_convert_raw($ticket_price);
								$ticket_qty = array_key_exists('ticket_type_qty', $ticket) && $ticket['ticket_type_qty'] > 0 ? $ticket['ticket_type_qty'] : 0;
								$reserve = array_key_exists('ticket_type_resv_qty', $ticket) && $ticket['ticket_type_resv_qty'] > 0 ? $ticket['ticket_type_resv_qty'] : 0;
								$ticket_qty_type = array_key_exists('ticket_type_qty_type', $ticket) ? $ticket['ticket_type_qty_type'] : 'inputbox';
								$default_qty = array_key_exists('ticket_type_default_qty', $ticket) && $ticket['ticket_type_default_qty'] > 0 ? $ticket['ticket_type_default_qty'] : 0;
								$min_qty = apply_filters('ttbm_ticket_type_min_qty', 0);
								$max_qty = apply_filters('ttbm_ticket_type_max_qty', 0);
                                $sold_type = TTBM_Function::get_total_sold($tour_id, $tour_date, $ticket_name);
								// Enhanced availability info
								$ticket_info = isset($availability_info[$ticket_name]) ? $availability_info[$ticket_name] : array();
								$total_capacity = $ticket_info['total_capacity'] ?? $ticket_qty;
								$sold_qty = $ticket_info['sold_qty'] ?? $sold_type;
								$stock_status = $ticket_info['stock_status'] ?? 'in_stock';
								$percentage_sold = $ticket_info['percentage_sold'] ?? 0;

                                // Allow addons (e.g., Buy X Get Y) to include extra sold quantities like free tickets
                                $sold_type = apply_filters('ttbm_sold_qty', $sold_type, $tour_id, $tour_date, $ticket_name);
                                
                                // Calculate available quantity
                                if (isset($ticket_info['available_qty'])) {
                                    $available = $ticket_info['available_qty'];
                                } else {
                                    $available = (int)$ticket_qty - ($sold_type + (int)$reserve);
                                }
                                
								$available = apply_filters('ttbm_group_ticket_qty', $available,$tour_id,$ticket_name);
								$available = max(0, floor($available)); // Ensure availability is always a whole number
								$ticket_type_icon = array_key_exists('ticket_type_icon', $ticket) ? $ticket['ticket_type_icon'] : '';
								$description = array_key_exists('ticket_type_description', $ticket) ? $ticket['ticket_type_description'] : '';
								
								// Use the locally calculated $available value to ensure consistency
								$is_sold_out = ($available <= 0);
								$stock_status = $is_sold_out ? 'sold_out' : 'in_stock';
								
								// Set row classes based on stock status
								$row_classes = array('ttbm_ticket_row', 'ttbm_stock_' . $stock_status);
								if ($is_sold_out) {
									$row_classes[] = 'ttbm_sold_out';
								}
								?>
								<tr class="<?php echo esc_attr(implode(' ', $row_classes)); ?>" data-ticket-name="<?php echo esc_attr($ticket_name); ?>">
									<td class="ttbm-person-info">
										<div class="person-info">
											<?php if ($ticket_type_icon) { ?>
												<span class="ttbm_ticket_icon <?php echo esc_attr($ticket_type_icon); ?>"></span>
											<?php } ?>
											<div class="ttbm_ticket_details">
												<p class="ttbm_ticket_name"><?php echo esc_html($ticket_name); ?></p>
												<?php if ($description) { ?>
													<div class="ttbm_ticket_description"><?php TTBM_Custom_Layout::load_more_text($description, 100); ?></div>
												<?php } ?>
											</div>
										</div>
									</td>
									
									<td class="ttbm-regular-price" data-regular-price="<?php echo esc_attr(TTBM_Global_Function::price_convert_raw(TTBM_Global_Function::wc_price($tour_id, $regular_price))); ?>" data-base-price="<?php echo esc_attr($ticket['ticket_type_price']); ?>">
										<div class="ttbm_price_container">
											<?php if ($regular_price) { ?>
												<span class="ttbm_regular_price strikeLine"><?php echo wc_price($regular_price); ?></span>
											<?php } ?>
											<span class="ttbm_sale_price"><?php echo mep_esc_html($ticket_price); ?></span>
											<?php if ($regular_price) { ?>
												<span class="ttbm_discount_badge">
													<?php 
													$discount_percentage = round((($regular_price - $price) / $regular_price) * 100);
													echo esc_html($discount_percentage . '% OFF');
													?>
												</span>
											<?php } ?>
										</div>
									</td>
									
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
																	<?php echo $available === 1 ? esc_html__('ticket left', 'tour-booking-manager') : esc_html__('tickets left', 'tour-booking-manager'); ?>
																</span>
															</div>
															
															<div class="ttbm_capacity_info">
																<span class="ttbm_capacity_text">
																	<?php printf(esc_html__('%1$d of %2$d sold', 'tour-booking-manager'), $sold_qty, $total_capacity); ?>
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
									
									<td class="ttbm-select-quantity">
										<?php if (!$is_sold_out) { ?>
											<?php TTBM_Layout::qty_input($ticket_name, $available, $ticket_qty_type, $default_qty, $min_qty, $max_qty, $ticket_price_raw, 'ticket_qty['.$index.']', $tour_id); ?>
										<?php } else { ?>
											<div class="ttbm_sold_out_message">
												<span class="ttbm_not_available"><?php esc_html_e('Not Available', 'tour-booking-manager'); ?></span>
											</div>
										<?php } ?>
									</td>
								</tr>

								<tr class="ttbm_hidden_inputs">
									<td colspan="<?php echo $hide_availability_column === 'on' ? '3' : '4'; ?>">
										<?php do_action('ttbm_input_data',$ticket_name,$tour_id); ?>
										<input type="hidden" name='tour_id[<?php echo $index; ?>]' value='<?php echo esc_html($tour_id); ?>'>
										<input type="hidden" name='ticket_name[<?php echo $index; ?>]' value='<?php echo esc_html($ticket_name); ?>'>
										<input type="hidden" name='ticket_max_qty[<?php echo $index; ?>]' value='<?php echo esc_html($max_qty); ?>'>
										<input type="hidden" name='ticket_available_qty[<?php echo $index; ?>]' value='<?php echo esc_html($available); ?>'>
										<input type="hidden" name='ticket_capacity[<?php echo $index; ?>]' value='<?php echo esc_html($total_capacity); ?>'>
									</td>
								</tr>
								<?php do_action('ttbm_after_ticket_type_item', $tour_id, $ticket); ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php include(TTBM_Function::template_path('ticket/extra_service.php')); ?>
		</div>
		<?php
		do_action('ttbm_load_seat_plan', $tour_id, $tour_date);
		do_action('ttbm_book_now_before', $tour_id);
		include(TTBM_Function::template_path('ticket/book_now.php'));
	}
	else {
		?>
		<div class="dLayout allCenter bgWarning">
			<h3 class="textWhite"><?php esc_html_e('No Ticket Available ! ', 'tour-booking-manager'); ?></h3>
		</div>
		<?php
	}
?>
