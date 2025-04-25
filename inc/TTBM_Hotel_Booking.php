<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists( 'TTBM_Hotel_Booking' ) ) {
    class TTBM_Hotel_Booking{
        public function __construct() {

            add_action( 'wp_ajax_ttbm_get_hotel_room_list', array( $this, 'ttbm_get_hotel_room_list' ) );
            add_action( 'wp_ajax_nopriv_ttbm_get_hotel_room_list', array( $this, 'ttbm_get_hotel_room_list' ) );

            add_action( 'wp_ajax_ttbm_hotel_room_booking', array( $this, 'ttbm_hotel_room_booking' ) );
            add_action( 'wp_ajax_nopriv_ttbm_hotel_room_booking', array( $this, 'ttbm_hotel_room_booking' ) );

            add_action( 'ttbm_hotel_booking_panel', array( $this, 'hotel_booking_panel' ), 10, 4 );

            add_filter('woocommerce_add_cart_item_data', [$this, 'set_custom_price_cart_item'], 10, 2);
            add_action('woocommerce_before_calculate_totals', [$this, 'update_cart_item_price'], 10, 1);
            add_filter('woocommerce_get_item_data', [$this, 'display_custom_cart_item_data'], 10, 2);
            add_action('woocommerce_order_item_meta_end', [$this, 'display_order_meta'], 10, 3);

        }

        public function ttbm_get_hotel_room_list() {
            $hotel_id   = $_REQUEST['hotel_id'] ?? '';
            $date_range = $_REQUEST['date_range'] ?? "";
            $date       = explode( "    -    ", $date_range );
            $start_date = date( 'Y-m-d', strtotime( $date[0] ) );
            $end_date = date( 'Y-m-d', strtotime( $date[1] ) );
            do_action( 'ttbm_hotel_booking_panel', $start_date, $end_date, $hotel_id );
            die();
        }
        public function ttbm_hotel_room_booking() {

            $hotel_id   = $_REQUEST['hotel_id'] ?? '';
            $date_range = $_REQUEST['date_range'] ?? "";
            $room_data_info = json_decode( wp_unslash( $_REQUEST['room_data_info'] ), true );

            $room_output = "Room List\n\n";
            foreach ($room_data_info as $roomName => $info) {
                $qty = $info['quantity'];
                $price = $info['price'];
                $total = $price * $qty * 1;

                // Format room name: insert space before capital letters except first
                $formattedRoomName = preg_replace('/(?<!^)([A-Z])/', ' $1', $roomName);

                $room_output .= "Room Name :    {$formattedRoomName}\n";
                $room_output .= "Qty :  {$qty}\n";
                $room_output .= "Price :  ( " . number_format($price, 2) . "৳ x {$qty} x 1 ) = " . number_format($total, 2) . "৳\n\n";
            }

            $date       = explode( "    -    ", $date_range );
            $check_in = date( 'Y-m-d', strtotime( $date[0] ) );
            $check_out = date( 'Y-m-d', strtotime( $date[1] ) );

            $days = 2;

            $booking_request = [];
            if( !empty( $room_data_info ) ) {
                foreach ($room_data_info as $room_type => $info) {
                    if (!empty($info['quantity'])) {
                        $booking_request[$room_type] = $info['quantity'];
                    }
                }
            }

            error_log( print_r( [ '$booking_request' => $booking_request ], true ) );

            $post_id = get_post_meta( $hotel_id, 'link_wc_product', true);
            $price = $_REQUEST['price'] ?? 0;
            $quantity = intval( wp_unslash( 20 ) );
            $hotel_info = array();
            $hotel_info['hotel_id'] = $hotel_id;
            $hotel_info['ttbm_checkin_date'] = $check_in;
            $hotel_info['ttbm_checkout_date'] = $check_out;
            $hotel_info['ttbm_hotel_num_of_day'] = $days;

            $cart_item_data = [
                'ttbm_hotel_booking' => 'yes',
                'ttbm_hotel_id' => $post_id,
                'description_order' => $room_output,
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

            MP_Global_Function::pa_add_multiple_room_type_booking( $hotel_id, $booking_request, $check_in, $check_out);
            WC()->cart->empty_cart();

            $cart_item_key = WC()->cart->add_to_cart( $post_id, $quantity, 0, [], $cart_item_data );
            if ($cart_item_key) {
                wp_send_json_success('Item added to cart.');
            } else {
                wp_send_json_error('Failed to add to cart.');
            }

        }

        public function display_order_meta( $item_id, $item, $order ) {
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

        public function display_custom_cart_item_data( $item_data, $cart_item ) {

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
                        'name'  => $label,
                        'value' => esc_html(esc_html( $value ) )
                    ];
                }
            }
            return $item_data;
        }

        public function hotel_booking_panel( $tour_date = '', $end_Date= '', $hotel_id = '') {
            $action    = apply_filters( 'ttbm_form_submit_path', '', $hotel_id );
            ?>
            <form action="<?php echo esc_attr( $action ); ?>" method='post' class="mp_tour_ticket_form">
                <input type="hidden" name='ttbm_total_price' id="ttbm_total_price" value='0'/>
                <?php
                $file = TTBM_Function::template_path( 'ticket/hotel_book_ticket.php' );
                require_once $file;

                ?>
            </form>
            <?php
        }

        public function set_custom_price_cart_item( $cart_item_data, $product_id) {
            if( isset( $cart_item_data['ttbm_hotel_booking']) ) {

                if (isset($_POST['price']) && !empty($_POST['price'])) {
                    $cart_item_data['custom_price'] = floatval($_POST['price']);
                }
            }
            return $cart_item_data;
        }
        public function update_cart_item_price($cart) {

            if (is_admin() && !defined('DOING_AJAX')) return;
            foreach ($cart->get_cart() as $cart_item) {
                if( isset( $cart_item['ttbm_hotel_booking']) ) {
                    if (!empty($cart_item['custom_price'])) {
                        $cart_item['data']->set_price($cart_item['custom_price']);
                    }
                }
            }
        }




    }
    new TTBM_Hotel_Booking();
}