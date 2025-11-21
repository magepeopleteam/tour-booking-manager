<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Hotel_Booking')) {
		class TTBM_Hotel_Booking {
			public function __construct() {
				add_action('wp_ajax_ttbm_get_hotel_room_list', array($this, 'ttbm_get_hotel_room_list'));
				add_action('wp_ajax_nopriv_ttbm_get_hotel_room_list', array($this, 'ttbm_get_hotel_room_list'));
				add_action('wp_ajax_ttbm_hotel_room_booking', array($this, 'ttbm_hotel_room_booking'));
				add_action('wp_ajax_nopriv_ttbm_hotel_room_booking', array($this, 'ttbm_hotel_room_booking'));
				add_action('ttbm_hotel_booking_panel', array($this, 'hotel_booking_panel'), 10, 4);
				add_filter('woocommerce_add_cart_item_data', [$this, 'set_custom_price_cart_item'], 10, 2);
				add_action('woocommerce_before_calculate_totals', [$this, 'update_cart_item_price'], 10, 1);
				add_filter('woocommerce_get_item_data', [$this, 'display_custom_cart_item_data'], 10, 2);
				add_action('woocommerce_order_item_meta_end', [$this, 'display_order_meta'], 10, 3);
				add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);
				add_action('woocommerce_new_order', [$this, 'mptrs_woocommerce_new_order'], 10, 1);
				add_action('woocommerce_order_status_changed', [$this, 'custom_function_on_order_status_change'], 10, 4);
			}
			function custom_function_on_order_status_change( $order_id, $old_status, $new_status, $order) {
				if ($new_status === 'processing') {
					$orderPostId = '';
					foreach ($order->get_items() as $item_id => $item) {
						$product = $item->get_product();
						$orderPostId = $product->get_id();
					}
					$ttbm_booking_data = maybe_unserialize(get_post_meta($orderPostId, '_ttbm_hotel_booking_data', true));
					if (isset($ttbm_booking_data['hotel_booking'])) {
						$payment_method = $order->get_payment_method_title();
						$order_description = isset($ttbm_booking_data['hotel_room_ordered_data_info']) ? $ttbm_booking_data['hotel_room_ordered_data_info'] : [];
						$checkin_date = isset($ttbm_booking_data['ttbm_hotel_info']['ttbm_checkin_date']) ? $ttbm_booking_data['ttbm_hotel_info']['ttbm_checkin_date'] : '';
						$checkout_date = isset($ttbm_booking_data['ttbm_hotel_info']['ttbm_checkout_date']) ? $ttbm_booking_data['ttbm_hotel_info']['ttbm_checkout_date'] : '';
						$hotel_id = isset($ttbm_booking_data['ttbm_hotel_info']['hotel_id']) ? $ttbm_booking_data['ttbm_hotel_info']['hotel_id'] : '';
						$number_of_days = isset($ttbm_booking_data['booking_days']) ? $ttbm_booking_data['booking_days'] : '';
						$price = isset($ttbm_booking_data['price']) ? $ttbm_booking_data['price'] : 0;
						$hotel_title = get_the_title($hotel_id);
						$hotel_booking_status = 'In Progress';
						$order_title = 'Hotel Booking #' . $order_id;

						$order_created_date = $order->get_date_created()->date('Y-m-d H:i:s');;
						$order_status = $new_status;
						$customer_id = $order->get_customer_id();
						$customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
						$customer_email = $order->get_billing_email();
						$custom_order_id = wp_insert_post(array(
							'post_title' => $order_title,
							'post_type' => 'ttbm_hotel_booking',
							'post_status' => 'publish',
							'post_author' => 1,
						));
						if ($custom_order_id) {
							// Store meta data in the custom post
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_order_id', $order_id);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_date', $order_created_date);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_status', $order_status);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_customer_id', $customer_id);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_customer_name', $customer_name);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_customer_email', $customer_email);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_room_info', $order_description);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_checkin_date', $checkin_date);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_checkout_date', $checkout_date);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_status', $hotel_booking_status);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_days', $number_of_days);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_price', $price);
							update_post_meta($custom_order_id, '_ttbm_hotel_id', $hotel_id);
							update_post_meta($custom_order_id, '_ttbm_hotel_title', $hotel_title);
							update_post_meta($custom_order_id, '_ttbm_hotel_booking_payment_method', $payment_method);
						}
					}
				}
			}
			public function mptrs_woocommerce_new_order($order_id) {
				$order = wc_get_order($order_id);
				$orderPostId = '';
				foreach ($order->get_items() as $item_id => $item) {
					$product = $item->get_product();
					$orderPostId = $product->get_id();
				}
				if (!empty($orderPostId)) {
					$mptrs_booking_data = maybe_unserialize(get_post_meta($orderPostId, '_ttbm_hotel_booking_data', true));
					if (is_array($mptrs_booking_data) && !empty($mptrs_booking_data)) {
						$hotel_id = $mptrs_booking_data['ttbm_hotel_info']['hotel_id'];
						$check_in = $mptrs_booking_data['ttbm_hotel_info']['ttbm_checkin_date'];
						$check_out = $mptrs_booking_data['ttbm_hotel_info']['ttbm_checkout_date'];
						$room_data_info = $mptrs_booking_data['hotel_room_ordered_data_info'];
						$booking_request = [];
						if (!empty($room_data_info)) {
							foreach ($room_data_info as $room_type => $info) {
								if (!empty($info['quantity'])) {
									$booking_request[$room_type] = $info['quantity'];
								}
							}
						}
						if (!empty($booking_request)) {
//							TTBM_Global_Function::pa_add_multiple_room_type_booking($hotel_id, $booking_request, $check_in, $check_out);
						}
					}
				}
			}
			function add_order_item_meta($item, $cart_item_key, $values, $order) {
				if (isset($values['ttbm_hotel_booking'])) {
					$meta_fields = [
						'description_order' => __('Hotel Booking Details', 'tour-booking-manager'),
						'booking_seats' => __('Seats', 'tour-booking-manager'),
						'checkin_date' => __('Check In Date', 'tour-booking-manager'),
						'checkout_date' => __('Check Out Date', 'tour-booking-manager'),
					];
					foreach ($meta_fields as $key => $label) {
						if (!empty($values[$key])) {
							$value = is_array($values[$key]) ? implode(', ', $values[$key]) : $values[$key];
							$item->add_meta_data($label, $value, true);
						}
					}
					$customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
					if (!empty($customer_name)) {
						$item->add_meta_data('Customer Name', $customer_name, true);
					}
				}
				if (isset($values['ttbm_hotel_booking'])) {
					$order_data = [];
					$product_id = isset($values['ttbm_hotel_id']) ? $values['ttbm_hotel_id'] : '';
					$order_data = array(
						'hotel_room_ordered_data_info' => $values['room_ordered_data_info'],
						'price' => $values['price'],
						'ttbm_hotel_info' => $values['ttbm_hotel_info'],
						'quantity' => $values['quantity'],
						'booking_days' => $values['days'],
						'hotel_booking' => $values['ttbm_hotel_booking'],
					);
					$order_data = maybe_serialize($order_data);
					if (!empty($product_id)) {
						update_post_meta($product_id, '_ttbm_hotel_booking_data', $order_data);
					}
				}
			}
			public function ttbm_get_hotel_room_list() {
				if (!isset( $_POST['nonce' ] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_frontend_nonce' ) ) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$hotel_id = isset($_REQUEST['hotel_id']) ? sanitize_text_field(wp_unslash($_REQUEST['hotel_id'])) : '';
				$date_range = isset($_REQUEST['date_range']) ? sanitize_text_field(wp_unslash($_REQUEST['date_range'])) : '';
				$date = explode(" - ", $date_range);
				$start_date = gmdate('Y-m-d', strtotime($date[0]));
				$end_date = gmdate('Y-m-d', strtotime($date[1]));
				do_action('ttbm_hotel_booking_panel', $start_date, $end_date, $hotel_id);
				die();
			}
			public function ttbm_hotel_room_booking() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_frontend_nonce' ) ) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}

				$hotel_id = isset($_REQUEST['hotel_id']) ? sanitize_text_field(wp_unslash($_REQUEST['hotel_id'])) : '';
				$date_range = isset($_REQUEST['date_range']) ? sanitize_text_field(wp_unslash($_REQUEST['date_range'])) : '';
				$room_data_info = isset($_REQUEST['room_data_info']) ? json_decode(sanitize_text_field(wp_unslash($_REQUEST['room_data_info'])), true) : [];
				$room_output = "Room List\n\n";
				$currency_symbol = get_woocommerce_currency_symbol();
				$room_output .= '<div class="ttbm_hotel_ordered_room_list">';
				foreach ($room_data_info as $roomName => $info) {
					$qty = $info['quantity'];
					$price = $info['price'];
					$total = $price * $qty * 1;
					// Format room name: insert space before capital letters except first
					$formattedRoomName = preg_replace('/(?<!^)([A-Z])/', ' $1', $roomName);
					// Build the output
					$room_output .= " <div class='ttbm_hotel_room'>";
					$room_output .= " <div class='ttbm_hotel_room_name'> Room Name: {$formattedRoomName}</div> ";
					$room_output .= " <div class='ttbm_hotel_qty'>Qty: {$qty}</div> ";
					$room_output .= " <div class='ttbm_hotel_price'>Price: ( " . number_format($price, 2) . " {$currency_symbol} x {$qty} x 1 ) = " . number_format($total, 2) . " {$currency_symbol}</div> ";
					$room_output .= " </div> ";
				}
				$room_output .= '</div>';
				$date = explode(" - ", $date_range);
				$check_in = gmdate('Y-m-d', strtotime($date[0]));
				$check_out = gmdate('Y-m-d', strtotime($date[1]));
				$check_in_date = gmdate('Y-m-d', strtotime($date[0]));
				$check_out_date = gmdate('Y-m-d', strtotime($date[1]));
				$datetime1 = new DateTime($check_in_date);
				$datetime2 = new DateTime($check_out_date);
				$interval = $datetime1->diff($datetime2);
				$days = $interval->days;
				$post_id = get_post_meta($hotel_id, 'link_wc_product', true);
				$price = isset($_REQUEST['price']) ? sanitize_text_field(wp_unslash($_REQUEST['price'])) : 0;
				$quantity = intval(wp_unslash(20));
				$hotel_info = array();
				$hotel_info['hotel_id'] = $hotel_id;
				$hotel_info['ttbm_checkin_date'] = $check_in;
				$hotel_info['ttbm_checkout_date'] = $check_out;
				$hotel_info['ttbm_hotel_num_of_day'] = $days;
				$cart_item_data = [
					'ttbm_hotel_booking' => 'yes',
					'ttbm_hotel_id' => $post_id,
					'description_order' => $room_output,
					'room_ordered_data_info' => $room_data_info,
					'price' => $price,
					'ttbm_tp' => $price,
					'line_total' => $price,
					'line_subtotal' => $price,
					'checkin_date' => $check_in,
					'checkout_date' => $check_out,
					'days' => $days,
					'ttbm_hotel_info' => apply_filters('ttbm_hotel_info_filter', $hotel_info, $hotel_id),
				];
				if (!class_exists('WC_Cart')) {
					wp_send_json_error('WooCommerce is not active.');
				}
				WC()->cart->empty_cart();
				$cart_item_key = WC()->cart->add_to_cart($post_id, $quantity, 0, [], $cart_item_data);
				if ($cart_item_key) {
					wp_send_json_success('Item added to cart.');
				} else {
					wp_send_json_error('Failed to add to cart.');
				}
			}
			public function display_order_meta($item_id, $item, $order) {
				$meta_fields = [
					'description_order' => 'Book Details',
					'booking_seats' => 'Seats',
					'checkin_date' => 'Check In Date',
					'checkout_date' => 'Check Out Date',
				];
				echo '<div class="mptrs-order-meta"><h4 class="mptrs-meta-title">Order Details</h4>';
				foreach ($meta_fields as $key => $label) {
					$value = $item->get_meta($key, true);
					if (!empty($value)) {
						$value = preg_replace('/<\/li>,/', '</li>', $value);
						echo '<p><strong>' . esc_attr($label) . ':</strong> ' . wp_kses_post($value) . '</p>';
					}
				}
				echo '</div>';
			}
			public function display_custom_cart_item_data($item_data, $cart_item) {
				$fields = [
					'description_order' => 'Book Details',
					'booking_seats' => 'Seats',
					'checkin_date' => 'Check In Date',
					'checkout_date' => 'Check Out Date',
				];
				foreach ($fields as $key => $label) {
					if (!empty($cart_item[$key])) {
						$value = is_array($cart_item[$key]) ? implode(', ', $cart_item[$key]) : $cart_item[$key];
						$item_data[] = [
							'name' => $label,
							'value' => wp_kses_post($value),
						];
					}
				}
				return $item_data;
			}
			public function hotel_booking_panel($tour_date = '', $end_Date = '', $hotel_id = '') {
				$action = apply_filters('ttbm_form_submit_path', '', $hotel_id);
				?>
                <form action="<?php echo esc_attr($action); ?>" method='post' class="mp_tour_ticket_form">
                    <input type="hidden" name='ttbm_total_price' id="ttbm_total_price" value='0'/>
					<?php
						wp_nonce_field('ttbm_hotel_nonce', 'ttbm_hotel_nonce');
                        require_once TTBM_Function::template_path('ticket/hotel_book_ticket.php');
                        ?>
                </form>
				<?php
			}
			public function set_custom_price_cart_item( $cart_item_data, $product_id) {

				if (isset($cart_item_data['ttbm_hotel_booking']) && (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce'))) {
                    $cart_item_data['custom_price'] = isset($_POST['price']) ? floatval(sanitize_text_field(wp_unslash($_POST['price']))) :0;

				}
				return $cart_item_data;
			}
			public function update_cart_item_price($cart) {
				if (is_admin() && !defined('DOING_AJAX'))
					return;
				foreach ($cart->get_cart() as $cart_item) {
					if (isset($cart_item['ttbm_hotel_booking'])) {
						if (!empty($cart_item['custom_price'])) {
							$cart_item['data']->set_price($cart_item['custom_price']);
						}
					}
				}
			}
		}
		new TTBM_Hotel_Booking();
	}