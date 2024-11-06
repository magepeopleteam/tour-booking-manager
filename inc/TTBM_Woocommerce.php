<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Woocommerce')) {
		class TTBM_Woocommerce {
			public function __construct() {
				add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 90, 3);
				add_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'), 90, 1);
				add_filter('woocommerce_cart_item_thumbnail', array($this, 'cart_item_thumbnail'), 90, 3);
				add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 90, 2);
				//************//
				add_action('woocommerce_after_checkout_validation', array($this, 'after_checkout_validation'));
				add_action('woocommerce_checkout_create_order_line_item', array($this, 'checkout_create_order_line_item'), 90, 4);
				//add_action('woocommerce_checkout_order_processed', array($this, 'checkout_order_processed'), 10);
				//add_action('woocommerce_before_thankyou', array($this, 'woocommerce_before_thankyou'), 10);
				add_action('woocommerce_checkout_order_processed', array($this, 'woocommerce_before_thankyou'), 90);
				//   add_action('__experimental_woocommerce_blocks_checkout_order_processed', 'checkout_order_processed', 90);
				add_action('woocommerce_store_api_checkout_order_processed', array($this, 'woocommerce_before_thankyou'), 90);
				add_filter('woocommerce_order_status_changed', array($this, 'order_status_changed'), 10, 4);
				//*******************//
				//*******************//
				add_action('ttbm_wc_order_status_change', array($this, 'wc_order_status_change'), 10, 3);
			}
			public function add_cart_item_data($cart_item_data, $product_id) {
				$linked_ttbm_id = MP_Global_Function::get_post_info($product_id, 'link_ttbm_id', $product_id);
				$product_id = is_string(get_post_status($linked_ttbm_id)) ? $linked_ttbm_id : $product_id;
				$product_id = TTBM_Function::post_id_multi_language($product_id);
				if (get_post_type($product_id) == TTBM_Function::get_cpt_name()) {
					$total_price = $this->get_cart_total_price($product_id);
					$hotel_info = self::cart_hotel_info();
					$cart_item_data['ttbm_hotel_info'] = apply_filters('ttbm_hotel_info_filter', $hotel_info, $product_id);
					$cart_item_data['ttbm_date'] = MP_Global_Function::get_submit_info('ttbm_start_date');
					$cart_item_data['ttbm_ticket_info'] = self::cart_ticket_info($product_id);
					$cart_item_data['ttbm_user_info'] = apply_filters('ttbm_user_info_data', array(), $product_id);
					$cart_item_data['ttbm_extra_service_info'] = self::cart_extra_service_info($product_id);
					$cart_item_data['ttbm_tp'] = $total_price;
					$cart_item_data['line_total'] = $total_price;
					$cart_item_data['line_subtotal'] = $total_price;
					$cart_item_data = apply_filters('ttbm_add_cart_item', $cart_item_data, $product_id);
				}
				$cart_item_data['ttbm_translation_ttbm_id'] = apply_filters('ttbm_get_translation_post_id', $product_id);
				$cart_item_data['ttbm_id'] = $product_id;
                //echo '<pre>';print_r($cart_item_data['ttbm_user_info']);echo '</pre>';
               // echo '<pre>';print_r($cart_item_data);echo '</pre>';die();
				return $cart_item_data;
			}
			public function before_calculate_totals($cart_object) {
				foreach ($cart_object->cart_contents as $value) {
					$ttbm_id = array_key_exists('ttbm_id', $value) ? $value['ttbm_id'] : 0;
					$ttbm_id = TTBM_Function::post_id_multi_language($ttbm_id);
					if (get_post_type($ttbm_id) == TTBM_Function::get_cpt_name()) {
						$total_price = $value['ttbm_tp'];
						$value['data']->set_price($total_price);
						$value['data']->set_regular_price($total_price);
						$value['data']->set_sale_price($total_price);
						$value['data']->set_sold_individually('yes');
						$value['data']->get_price();
					}
				}
			}
			public function cart_item_thumbnail($thumbnail, $cart_item) {
				$ttbm_id = array_key_exists('ttbm_id', $cart_item) ? $cart_item['ttbm_id'] : 0;
				$ttbm_id = TTBM_Function::post_id_multi_language($ttbm_id);
				if (get_post_type($ttbm_id) == TTBM_Function::get_cpt_name()) {
					$thumbnail = '<div class="bg_image_area" data-href="' . get_the_permalink($ttbm_id) . '"><div data-bg-image="' . MP_Global_Function::get_image_url($ttbm_id) . '"></div></div>';
				}
				return $thumbnail;
			}
			public function get_item_data($item_data, $cart_item) {
				ob_start();
				$ttbm_id = array_key_exists('ttbm_id', $cart_item) ? $cart_item['ttbm_id'] : 0;
				$ttbm_id = TTBM_Function::post_id_multi_language($ttbm_id);
				if (get_post_type($ttbm_id) == TTBM_Function::get_cpt_name()) {
					$this->show_cart_item($cart_item, $ttbm_id);
					do_action('ttbm_show_cart_item', $cart_item, $ttbm_id);
				}
				$item_data[] = array('key' => esc_html__('Booking Details ', 'tour-booking-manager'), 'value' => ob_get_clean());
				return $item_data;
			}
			//**************//
			public function after_checkout_validation() {
				global $woocommerce;
				$items = $woocommerce->cart->get_cart();
				foreach ($items as $values) {
					$ttbm_id = array_key_exists('ttbm_id', $values) ? $values['ttbm_id'] : 0;
					$ttbm_id = TTBM_Function::post_id_multi_language($ttbm_id);
					if (get_post_type($ttbm_id) == TTBM_Function::get_cpt_name()) {
						do_action('ttbm_validate_cart_item', $values, $ttbm_id);
					}
				}
			}
			public function checkout_create_order_line_item($item, $cart_item_key, $values) {
				$ttbm_id = array_key_exists('ttbm_id', $values) ? $values['ttbm_id'] : 0;
				$ttbm_id = TTBM_Function::post_id_multi_language($ttbm_id);
				//echo '<pre>';print_r($values);echo '</pre>';die();
				if (get_post_type($ttbm_id) == TTBM_Function::get_cpt_name()) {
					$hotel_info = $values['ttbm_hotel_info'] ?: [];
					$ticket_type = $values['ttbm_ticket_info'] ?: [];
					$extra_service = $values['ttbm_extra_service_info'] ?: [];
					$user_info = $values['ttbm_user_info'] ?: [];
					$date = $values['ttbm_date'] ?: '';
					$data_format = MP_Global_Function::check_time_exit_date($date) ? 'full' : 'date';
					$start_date = MP_Global_Function::date_format( $date,$data_format);
					$location = MP_Global_Function::get_post_info($ttbm_id, 'ttbm_location_name');
					$date_text = TTBM_Function::get_name() . ' ' . esc_html__('Date', 'tour-booking-manager');
					$location_text = TTBM_Function::get_name() . ' ' . esc_html__('Location', 'tour-booking-manager');
					$item->add_meta_data($date_text, $start_date);
					if (!empty($location) && MP_Global_Function::get_post_info($ttbm_id, 'ttbm_display_location', 'on') != 'off') {
						$item->add_meta_data($location_text, $location);
					}
					if (sizeof($ticket_type) > 0) {
						if (sizeof($hotel_info) > 0) {
							$item->add_meta_data(esc_html__('Hotel Name', 'tour-booking-manager'), get_the_title($hotel_info['hotel_id']));
							$item->add_meta_data(esc_html__('Check In Date', 'tour-booking-manager'), $hotel_info['ttbm_checkin_date']);
							$item->add_meta_data(esc_html__('Check Out Date', 'tour-booking-manager'), $hotel_info['ttbm_checkout_date']);
							$item->add_meta_data(esc_html__('Duration ', 'tour-booking-manager'), $hotel_info['ttbm_hotel_num_of_day']);
						}
						foreach ($ticket_type as $ticket) {
							if (sizeof($hotel_info) > 0) {
								$item->add_meta_data(esc_html__('Room Name', 'tour-booking-manager'), $ticket['ticket_name']);
							} else {
								$item->add_meta_data(TTBM_Function::ticket_name_text(), $ticket['ticket_name']);
							}
							$item->add_meta_data(TTBM_Function::ticket_qty_text(), $ticket['ticket_qty']);
							if (sizeof($hotel_info) > 0) {
								$item->add_meta_data(TTBM_Function::ticket_price_text(), ' ( ' . MP_Global_Function::wc_price($ttbm_id, $ticket['ticket_price']) . ' x ' . $ticket['ticket_qty'] . 'x' . $hotel_info['ttbm_hotel_num_of_day'] . ') = ' . MP_Global_Function::wc_price($ttbm_id, ($ticket['ticket_price'] * $ticket['ticket_qty'] * $hotel_info['ttbm_hotel_num_of_day'])));
							} else {
								$item->add_meta_data(TTBM_Function::ticket_price_text(), ' ( ' . MP_Global_Function::wc_price($ttbm_id, $ticket['ticket_price']) . ' x ' . $ticket['ticket_qty'] . ') = ' . MP_Global_Function::wc_price($ttbm_id, ($ticket['ticket_price'] * $ticket['ticket_qty'])));
							}
						}
						if (sizeof($extra_service) > 0) {
							foreach ($extra_service as $service) {
								$item->add_meta_data(TTBM_Function::service_name_text(), $service['service_name']);
								$item->add_meta_data(TTBM_Function::service_qty_text(), $service['service_qty']);
								$item->add_meta_data(TTBM_Function::service_price_text(), ' ( ' . MP_Global_Function::wc_price($ttbm_id, $service['service_price']) . ' x ' . $service['service_qty'] . ') = ' . MP_Global_Function::wc_price($ttbm_id, ($service['service_price'] * $service['service_qty'])));
							}
						}
					}
					$item->add_meta_data('_ttbm_id', $ttbm_id);
					$item->add_meta_data('_ttbm_date', $date);
					$item->add_meta_data('_ttbm_hotel_info', $hotel_info);
					$item->add_meta_data('_ttbm_ticket_info', $ticket_type);
					$item->add_meta_data('_ttbm_user_info', $user_info);
					$item->add_meta_data('_ttbm_service_info', $extra_service);
					do_action('ttbm_checkout_create_order_line_item', $item, $values);
				}
			}
			public function checkout_order_processed($order_id) {
				if ($order_id) {
					$order = wc_get_order($order_id);
					$order_status = $order->get_status();
					if ($order_status != 'failed') {
						//$item_id = current( array_keys( $order->get_items() ) );
						foreach ($order->get_items() as $item_id => $item) {
							$ttbm_id = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_id');
							$ttbm_id = TTBM_Function::post_id_multi_language($ttbm_id);
							if (get_post_type($ttbm_id) == TTBM_Function::get_cpt_name()) {
								$ticket = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_ticket_info');
								$ticket_info = $ticket ? MP_Global_Function::data_sanitize($ticket) : [];
								$hotel = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_hotel_info');
								$hotel_info = $hotel ? MP_Global_Function::data_sanitize($hotel) : [];
								$user = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_user_info');
								$user_info = $user ? MP_Global_Function::data_sanitize($user) : [];
								$service = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_service_info');
								$service_info = $service ? MP_Global_Function::data_sanitize($service) : [];
								self::add_billing_data($ticket_info, $hotel_info, $user_info, $ttbm_id, $order_id);
								self::add_extra_service_data($service_info, $ttbm_id, $order_id);
							}
						}
					}
				}
			}
			public function woocommerce_before_thankyou($order_id) {
				if (is_object($order_id)) {
					$order_id = $order_id->get_id();
				}
				if ($order_id) {
					// echo "<pre>";print_r($order);echo "</pre>";exit;
					// $order_id = $order->get_id();
					$order = wc_get_order($order_id);
					$order_status = $order->get_status();
					if ($order_status != 'failed') {
						//$item_id = current( array_keys( $order->get_items() ) );
						foreach ($order->get_items() as $item_id => $item) {
							if (!self::check_duplicate_order($order_id, $item_id)) {
								$ttbm_id = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_id');
								$ttbm_id = TTBM_Function::post_id_multi_language($ttbm_id);
								if (get_post_type($ttbm_id) == TTBM_Function::get_cpt_name()) {
									$ticket = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_ticket_info');
									$ticket_info = $ticket ? MP_Global_Function::data_sanitize($ticket) : [];
									$hotel = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_hotel_info');
									$hotel_info = $hotel ? MP_Global_Function::data_sanitize($hotel) : [];
									$user = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_user_info');
									$user_info = $user ? MP_Global_Function::data_sanitize($user) : [];
									$service = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_service_info');
									$service_info = $service ? MP_Global_Function::data_sanitize($service) : [];
									self::add_billing_data($ticket_info, $hotel_info, $user_info, $ttbm_id, $order_id);
									self::add_extra_service_data($service_info, $ttbm_id, $order_id);
								}
							}
						}
					}
					do_action('ttbm_send_mail', $order_id);
					update_post_meta($order_id, 'ttbm_initial_email_send', 'yes');
				}
			}
			public function order_status_changed($order_id) {
				$order = wc_get_order($order_id);
				$order_status = $order->get_status();
				foreach ($order->get_items() as $item_id => $item_values) {
					$tour_id = MP_Global_Function::get_order_item_meta($item_id, '_ttbm_id');
					$tour_id = TTBM_Function::post_id_multi_language($tour_id);
					if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
						if ($order->has_status('processing')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
						if ($order->has_status('pending')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
						if ($order->has_status('on-hold')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
						if ($order->has_status('completed')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
						if ($order->has_status('cancelled')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
						if ($order->has_status('refunded')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
						if ($order->has_status('failed')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
						if ($order->has_status('requested')) {
							do_action('ttbm_wc_order_status_change', $order_status, $tour_id, $order_id);
						}
					}
				}
			}
			//**************************//
			public function show_cart_item($cart_item, $ttbm_id) {
				$ticket_type = $cart_item['ttbm_ticket_info'] ?: [];
				$extra_service = $cart_item['ttbm_extra_service_info'] ?: [];
				$tour_name = TTBM_Function::get_name();
				$location = MP_Global_Function::get_post_info($ttbm_id, 'ttbm_location_name');
				$date = $cart_item['ttbm_date'];
				$data_format = MP_Global_Function::check_time_exit_date($date) ? 'full' : 'date';
				$hotel_info = $cart_item['ttbm_hotel_info'] ?: array();
				?>
                <div class="mpStyle">
					<?php do_action('ttbm_before_cart_item_display', $cart_item, $ttbm_id); ?>
                    <div class="dLayout_xs bgTransparent marXsT">
                        <ul class="cart_list">
							<?php if (!empty($location) && MP_Global_Function::get_post_info($ttbm_id, 'ttbm_display_location', 'on') != 'off') { ?>
                                <li>
                                    <span class="fas fa-map-marker-alt"></span>&nbsp;
                                    <h6><?php echo esc_html($tour_name . ' ' . esc_html__('Location', 'tour-booking-manager')); ?> :&nbsp;</h6>
                                    <span><?php echo esc_html($location); ?></span>
                                </li>
							<?php } ?>
							<?php if (sizeof($hotel_info) > 0) { ?>
                                <li>
                                    <span class="fas fa-hotel"></span>&nbsp;
                                    <h6><?php esc_html_e('Hotel Name', 'tour-booking-manager'); ?> :&nbsp;</h6>
                                    <span><?php echo get_the_title($hotel_info['hotel_id']); ?></span>
                                </li>
                                <li>
                                    <span class="far fa-calendar-check"></span>&nbsp;
                                    <h6><?php esc_html_e('Checkin Date : ', 'tour-booking-manager'); ?>&nbsp;</h6>
                                    <span><?php echo esc_html($hotel_info['ttbm_checkin_date']); ?></span>
                                </li>
                                <li>
                                    <span class="fas fa-calendar-times"></span>&nbsp;
                                    <h6><?php esc_html_e('Checkout Date : ', 'tour-booking-manager'); ?>&nbsp;</h6>
                                    <span><?php echo esc_html($hotel_info['ttbm_checkout_date']); ?></span>
                                </li>
                                <li>
                                    <span class="fas fa-stopwatch"></span>&nbsp;
                                    <h6><?php esc_html_e('Duration : ', 'tour-booking-manager'); ?>&nbsp;</h6>
                                    <span><?php echo esc_html($hotel_info['ttbm_hotel_num_of_day']); ?>&nbsp;<?php echo esc_html__('Days', 'tour-booking-manager'); ?></span>
                                </li>
							<?php } else { ?>
                                <li>
                                    <span class="far fa-calendar-alt"></span>&nbsp;&nbsp;
                                    <h6><?php echo esc_html($tour_name . ' ' . esc_html__('Date', 'tour-booking-manager')); ?> :&nbsp;</h6>
                                    <span><?php echo esc_html(MP_Global_Function::date_format( $date,$data_format)); ?></span>
                                </li>
							<?php } ?>
                        </ul>
                    </div>
					<?php if (sizeof($ticket_type) > 0) { ?>
                        <h5 class="mb_xs">
							<?php if (sizeof($hotel_info) > 0) { ?>
								<?php esc_html_e('Room List ', 'tour-booking-manager'); ?>
							<?php } else { ?>
								<?php esc_html_e('Ticket List ', 'tour-booking-manager'); ?>
							<?php } ?>
                        </h5>
						<?php foreach ($ticket_type as $ticket) { ?>
                            <div class="dLayout_xs">
                                <ul class="cart_list">
									<?php if (sizeof($hotel_info) > 0) { ?>
                                        <li>
                                            <h6><?php esc_html_e('Room Name', 'tour-booking-manager'); ?> :&nbsp;</h6>
                                            <span>&nbsp; <?php echo esc_html($ticket['ticket_name']); ?></span>
                                        </li>
                                        <li>
                                            <h6><?php echo esc_html(TTBM_Function::ticket_qty_text()); ?> :&nbsp;</h6>
                                            <span><?php echo esc_html($ticket['ticket_qty']); ?></span>
                                        </li>
                                        <li>
                                            <h6><?php echo esc_html(TTBM_Function::ticket_price_text()); ?> :&nbsp;</h6>
                                            <span><?php echo ' ( ' . MP_Global_Function::wc_price($ttbm_id, $ticket['ticket_price']) . ' x ' . $ticket['ticket_qty'] . ' x ' . $hotel_info['ttbm_hotel_num_of_day'] . ') = ' . MP_Global_Function::wc_price($ttbm_id, ($ticket['ticket_price'] * $ticket['ticket_qty'] * $hotel_info['ttbm_hotel_num_of_day'])); ?></span>
                                        </li>
									<?php } else {
										 ?>
                                        <li>
                                            <h6><?php echo esc_html(TTBM_Function::ticket_name_text()); ?> :&nbsp;</h6>
                                            <span><?php echo esc_html($ticket['ticket_name']); ?></span>
                                        </li>
                                        <li>
                                            <h6><?php echo esc_html(TTBM_Function::ticket_qty_text()); ?> :&nbsp;</h6>
                                            <span><?php echo esc_html($ticket['ticket_qty']); ?></span>
                                        </li>
                                        <li>
                                            <h6><?php echo esc_html(TTBM_Function::ticket_price_text()); ?> :&nbsp;</h6>
                                            <span><?php echo ' ( ' . MP_Global_Function::wc_price($ttbm_id, $ticket['ticket_price']) . ' x ' . $ticket['ticket_qty'] . ') = ' . MP_Global_Function::wc_price($ttbm_id, ($ticket['ticket_price'] * $ticket['ticket_qty'])); ?></span>
                                        </li>
									<?php } ?>
                                </ul>
                            </div>
						<?php } ?>
					<?php } ?>

					<?php if (sizeof($extra_service) > 0) { ?>
                        <h5 class="mb_xs"><?php esc_html_e('Extra Services', 'tour-booking-manager'); ?></h5>
						<?php foreach ($extra_service as $service) { ?>
                            <div class="dLayout_xs">
                                <ul class="cart_list">
                                    <li>
                                        <h6><?php echo esc_html(TTBM_Function::service_name_text()); ?> :&nbsp;</h6>
                                        <span><?php echo esc_html($service['service_name']); ?></span>
                                    </li>
                                    <li>
                                        <h6><?php echo esc_html(TTBM_Function::service_qty_text()); ?> :&nbsp;</h6>
                                        <span><?php echo esc_html($service['service_qty']); ?></span>
                                    </li>
                                    <li>
                                        <h6><?php echo esc_html(TTBM_Function::service_price_text()); ?> :&nbsp;</h6>
                                        <span><?php echo ' ( ' . MP_Global_Function::wc_price($ttbm_id, $service['service_price']) . ' x ' . $service['service_qty'] . ') = ' . MP_Global_Function::wc_price($ttbm_id, ($service['service_price'] * $service['service_qty'])); ?></span>
                                    </li>
                                </ul>
                            </div>
						<?php } ?>
					<?php } ?>
					<?php do_action('ttbm_after_cart_item_display', $cart_item, $ttbm_id); ?>
                </div>
				<?php
			}
			public function wc_order_status_change($order_status, $tour_id, $order_id) {
				$args = array(
					'post_type' => 'ttbm_booking',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							array(
								'key' => 'ttbm_id',
								'value' => $tour_id,
								'compare' => '='
							),
							array(
								'key' => 'ttbm_order_id',
								'value' => $order_id,
								'compare' => '='
							)
						)
					)
				);
				$loop = new WP_Query($args);
				foreach ($loop->posts as $user) {
					$user_id = $user->ID;
					update_post_meta($user_id, 'ttbm_order_status', $order_status);
				}
				$args = array(
					'post_type' => 'ttbm_service_booking',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							array(
								'key' => 'ttbm_id',
								'value' => $tour_id,
								'compare' => '='
							),
							array(
								'key' => 'ttbm_order_id',
								'value' => $order_id,
								'compare' => '='
							)
						)
					)
				);
				$loop = new WP_Query($args);
				foreach ($loop->posts as $user) {
					$user_id = $user->ID;
					update_post_meta($user_id, 'ttbm_order_status', $order_status);
				}
			}
			//**********************//
			public static function add_extra_service_data($service_info, $ttbm_id, $order_id): array {
				$order = wc_get_order($order_id);
				$order_meta = get_post_meta($order_id);
				$order_status = $order->get_status();
				$payment_method = $order_meta['_payment_method_title'][0] ?? '';
				//$user_id = $order_meta['_customer_user'][0] ?? '';
				$user_id = $order->get_user_id() ?? '';
				$zdata = [];
				if (sizeof($service_info) > 0) {
					foreach ($service_info as $key => $_ticket) {
						$zdata[$key]['ttbm_service_name'] = $_ticket['service_name'];
						$zdata[$key]['ttbm_service_price'] = $_ticket['service_price'];
						$zdata[$key]['ttbm_service_total_price'] = ($_ticket['service_price'] * $_ticket['service_qty']);
						$zdata[$key]['ttbm_date'] = $_ticket['ttbm_date'];
						$zdata[$key]['ttbm_service_qty'] = $_ticket['service_qty'];
						$zdata[$key]['ttbm_id'] = $ttbm_id;
						$zdata[$key]['ttbm_order_id'] = $order_id;
						$zdata[$key]['ttbm_order_status'] = $order_status;
						$zdata[$key]['ttbm_payment_method'] = $payment_method;
						$zdata[$key]['ttbm_user_id'] = $user_id;
						self::add_cpt_data('ttbm_service_booking', '#' . $order_id . $zdata[$key]['ttbm_service_name'], $zdata[$key]);
					}
				}
				return $zdata;
			}
			public static function add_billing_data($ticket_info, $hotel_info, $user_info, $ttbm_id, $order_id) {
				$order = wc_get_order($order_id);
				$order_status = $order->get_status();
				$payment_method = $order->get_payment_method();
				$user_id = $order->get_user_id() ?? '';
				$billing_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
				$billing_email = $order->get_billing_email();
				$billing_phone = $order->get_billing_phone();
				$billing_address = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
				$hotel_id = sizeof($hotel_info) > 0 ? $hotel_info['hotel_id'] : 0;
				$checkin_date = sizeof($hotel_info) > 0 ? $hotel_info['ttbm_checkin_date'] : '';
				$checkout_date = sizeof($hotel_info) > 0 ? $hotel_info['ttbm_checkout_date'] : '';
				$num_of_day = sizeof($hotel_info) > 0 ? $hotel_info['ttbm_hotel_num_of_day'] : 1;
				if (sizeof($ticket_info) > 0) {
					$count = 0;

					foreach ($ticket_info as $_ticket) {

						//$qty = apply_filters('ttbm_group_ticket_qty_actual', $_ticket['ticket_qty'],$ttbm_id,$_ticket['ticket_name']);
						$qty = $_ticket['ticket_qty'];
						for ($key = 0; $key < $qty; $key++) {
							$group_attendee_id='';
							$group_count=0;
                            $actual_qty=apply_filters('ttbm_group_ticket_qty_actual', 1,$ttbm_id,$_ticket['ticket_name']);
                            for($j=0;$j<$actual_qty;$j++){
                                $group_id=$actual_qty>1?'on':'';
                                if($group_count>0){
                                    $current_group_id=MP_Global_Function::get_post_info($group_attendee_id, 'ttbm_group_id');
	                                $group_id=($current_group_id && $current_group_id !='on')?$current_group_id:$group_attendee_id;
                                }
	                            $zdata[$count]['ttbm_ticket_name'] = $_ticket['ticket_name'];
	                            $zdata[$count]['ttbm_ticket_price'] = $_ticket['ticket_price'] * $num_of_day;
	                            $zdata[$count]['ttbm_ticket_total_price'] = ($_ticket['ticket_price'] * $qty) * $num_of_day;
	                            $zdata[$count]['ttbm_date'] = $_ticket['ttbm_date'];
	                            $zdata[$count]['ttbm_ticket_qty'] = $_ticket['ticket_qty'];
	                            $zdata[$count]['ttbm_group_id'] = $group_id;
	                            $zdata[$count]['ttbm_hotel_id'] = $hotel_id;
	                            $zdata[$count]['ttbm_checkin_date'] = $checkin_date;
	                            $zdata[$count]['ttbm_checkout_date'] = $checkout_date;
	                            $zdata[$count]['ttbm_hotel_num_of_day'] = $num_of_day;
	                            $zdata[$count]['ttbm_id'] = $ttbm_id;
	                            $zdata[$count]['ttbm_order_id'] = $order_id;
	                            $zdata[$count]['ttbm_order_status'] = $order_status;
	                            $zdata[$count]['ttbm_payment_method'] = $payment_method;
	                            $zdata[$count]['ttbm_user_id'] = $user_id;
	                            $zdata[$count]['ttbm_billing_name'] = $billing_name;
	                            $zdata[$count]['ttbm_billing_email'] = $billing_email;
	                            $zdata[$count]['ttbm_billing_phone'] = $billing_phone;
	                            $zdata[$count]['ttbm_billing_address'] = $billing_address;
	                            $user_data = apply_filters('ttbm_user_booking_data_arr', $zdata[$count], $count, $user_info, $ttbm_id);
	                            $group_attendee_id=self::add_cpt_data('ttbm_booking', $user_data['ttbm_billing_name'], $user_data);
	                            $count++;
	                            $group_count++;
                            }

						}
					}
				}
			}
			public static function cart_ticket_info($tour_id) {
				$start_date = MP_Global_Function::get_submit_info('ttbm_start_date');
				$names = MP_Global_Function::get_submit_info('ticket_name', array());
				$qty = MP_Global_Function::get_submit_info('ticket_qty', array());
				$group_qty = MP_Global_Function::get_submit_info('ticket_qroup_qty', array());
				$max_qty = MP_Global_Function::get_submit_info('ticket_max_qty', array());
				$hotel_id = MP_Global_Function::get_submit_info('ttbm_tour_hotel_list', 0);
				$ticket_info = [];
				if (sizeof($names) > 0) {
					for ($i = 0; $i < count($names); $i++) {
						if ($qty[$i] > 0) {
							$name = $names[$i] ?? '';
							$ticket_info[$i]['ticket_name'] = $name;
							$ticket_info[$i]['ticket_price'] = TTBM_Function::get_price_by_name($name, $tour_id, $hotel_id, $qty[$i], $start_date);
							$ticket_info[$i]['ticket_qty'] = $qty[$i];
							$ticket_info[$i]['qroup_qty'] = $group_qty[$i];
							$ticket_info[$i]['ttbm_max_qty'] = $max_qty[$i] ?? '';
							$ticket_info[$i]['ttbm_date'] = $start_date ?? '';
						}
					}
				}
				return apply_filters('ttbm_cart_ticket_info_data_prepare', $ticket_info, $tour_id);
			}
			public static function cart_hotel_info(): array {
				$hotel_id = MP_Global_Function::get_submit_info('ttbm_tour_hotel_list', 0);
				$hotel_info = array();
				if ($hotel_id > 0) {
					$hotel_info['hotel_id'] = $hotel_id;
					$hotel_info['ttbm_checkin_date'] = MP_Global_Function::get_submit_info('ttbm_checkin_date');
					$hotel_info['ttbm_checkout_date'] = MP_Global_Function::get_submit_info('ttbm_checkout_date');
					$hotel_info['ttbm_hotel_num_of_day'] = MP_Global_Function::get_submit_info('ttbm_hotel_num_of_day');
				}
				return $hotel_info;
			}
			public static function cart_extra_service_info($tour_id): array {
				$start_date = MP_Global_Function::get_submit_info('ttbm_start_date');
				$service_name = MP_Global_Function::get_submit_info('service_name', array());
				$service_qty = MP_Global_Function::get_submit_info('service_qty', array());
				$extra_service = array();
				if (sizeof($service_name) > 0) {
					for ($i = 0; $i < count($service_name); $i++) {
						if ($service_qty[$i] > 0) {
							$name = $service_name[$i] ?? '';
							$extra_service[$i]['service_name'] = $name;
							$extra_service[$i]['service_price'] = TTBM_Function::get_extra_service_price_by_name($tour_id, $name);
							$extra_service[$i]['service_qty'] = $service_qty[$i];
							$extra_service[$i]['ttbm_date'] = $start_date ?? '';
						}
					}
				}
				return $extra_service;
			}
			public function get_cart_total_price($tour_id) {
				$names = MP_Global_Function::get_submit_info('ticket_name', array());
				$qty = MP_Global_Function::get_submit_info('ticket_qty', array());
				$hotel_id = MP_Global_Function::get_submit_info('ttbm_tour_hotel_list', 0);
				$start_date = MP_Global_Function::get_submit_info('ttbm_start_date');
				$total_price = 0;
				$count = count($names);
				if (sizeof($names) > 0) {
					for ($i = 0; $i < $count; $i++) {
						if ($qty[$i] > 0) {
							$price = TTBM_Function::get_price_by_name($names[$i], $tour_id, $hotel_id, $qty[$i], $start_date) * $qty[$i];
							if ($hotel_id > 0) {
								$price = $price * MP_Global_Function::get_submit_info('ttbm_hotel_num_of_day');
							}
							$total_price = $total_price + $price;
						}
					}
				}
				$service_name = MP_Global_Function::get_submit_info('service_name', array());
				$service_qty = MP_Global_Function::get_submit_info('service_qty', array());
				if (sizeof($service_name) > 0) {
					for ($i = 0; $i < count($service_name); $i++) {
						if ($service_qty[$i] > 0) {
							$name = $service_name[$i] ?? '';
							$price = TTBM_Function::get_extra_service_price_by_name($tour_id, $name) * $service_qty[$i];
							$total_price = $total_price + $price;
						}
					}
				}
				return $total_price;
			}
			public static function add_cpt_data($cpt_name, $title, $meta_data = array(), $status = 'publish', $cat = array()) {

					$new_post = array(
						'post_title' => $title,
						'post_content' => '',
						'post_category' => $cat,
						'tags_input' => array(),
						'post_status' => $status,
						'post_type' => $cpt_name
					);
					wp_reset_postdata();
					$post_id = wp_insert_post($new_post);
					if (sizeof($meta_data) > 0) {
						foreach ($meta_data as $key => $value) {
							update_post_meta($post_id, $key, $value);
						}
					}
					if ($cpt_name == 'ttbm_booking') {
						$ttbm_pin = $meta_data['ttbm_user_id'] . $meta_data['ttbm_order_id'] . $meta_data['ttbm_id'] . $post_id;
						update_post_meta($post_id, 'ttbm_pin', $ttbm_pin);
					}
					wp_reset_postdata();
                    return $post_id;
			}
			public static function check_duplicate_order($order_id, $ttbm_id) {
				$args = array(
					'post_type' => 'ttbm_booking',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'ttbm_order_id',
							'value' => $order_id,
							'compare' => '=',
						),
						array(
							'key' => 'ttbm_id',
							'value' => $ttbm_id,
							'compare' => '=',
						),
					),
				);
				$query = new WP_Query($args);
				if ($query->have_posts()) {
					return true;
				} else {
					return false;
				}
			}
		}
		new TTBM_Woocommerce();
	}