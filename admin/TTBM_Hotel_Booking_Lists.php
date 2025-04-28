<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Hotel_Booking_Lists')) {
    class TTBM_Hotel_Booking_Lists{

        public function __construct() {
            add_action('admin_menu', array($this, 'hotel_booking_list_menu'), 1);
        }

        public function hotel_booking_list_menu() {

            $label = __('Hotel Booking Lists', 'tour-booking-manager');
            add_submenu_page(
                'edit.php?post_type=ttbm_tour',
                $label,
                $label,
                'manage_options',
                'ttbm_hotel_booking_lists',
                array($this, 'ttbm_hotel_order')
            );

        }

        public function ttbm_hotel_order() {
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

            $args = array(
                'post_type'      => 'ttbm_hotel_booking',
                'posts_per_page' => 20,
                'paged'          => $paged,
            );
            $query = new WP_Query($args);
            ?>

            <div class="ttbm_total_booking_wrapper">
                <h2 class="ttbm_total_booking_title"><?php echo esc_attr__( 'Travel Order List', 'tour-booking-manager' )?></h2>

                <div class="ttbm_total_booking_filter_section">
                    <span class="ttbm_total_booking_filter_label"><?php echo esc_attr__( 'Filter List By:', 'tour-booking-manager' )?></span>
                    <div class="ttbm_total_booking_filter_options">
                        <label class="ttbm_total_booking_radio_container">
                            <input type="radio" name="filter_type" value="travel" checked>
                            <span class="ttbm_total_booking_radio_text"><?php echo esc_attr__( 'Hotel', 'tour-booking-manager' )?></span>
                        </label>
                    </div>
                    <div class="ttbm_total_booking_filter_controls">
                        <select class="ttbm_total_booking_select">
                            <option><?php echo esc_attr__( 'Select Travel', 'tour-booking-manager' )?></option>
                        </select>
                        <button class="ttbm_total_booking_filter_btn"<?php echo esc_attr__( 'Filter', 'tour-booking-manager' )?>></button>
                        <button class="ttbm_total_booking_reset_btn"><?php echo esc_attr__( 'Reset', 'tour-booking-manager' )?></button>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="ttbm_total_booking_summary">
                    <div class="ttbm_total_booking_found"><?php echo esc_attr__( 'Total ', 'tour-booking-manager' )?> <span><?php echo $query->found_posts; ?></span><?php echo esc_attr__( ' Order Found', 'tour-booking-manager' )?></div>
                    <div class="ttbm_total_booking_showing"><?php echo esc_attr__( 'Showing ', 'tour-booking-manager' )?>  <span><?php echo $query->post_count; ?></span><?php echo esc_attr__( ' Order', 'tour-booking-manager' )?> </div>
                    <div class="ttbm_total_booking_per_page">
                        <span><?php echo esc_attr__( 'Guest Per Page', 'tour-booking-manager' )?></span>
                        <input type="number" value="20" class="ttbm_total_booking_page_input">
                    </div>
                </div>

                <!-- Orders Table -->
                <table class="ttbm_total_booking_table">
                    <thead class="ttbm_total_booking_thead">
                    <tr>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Sl.', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Order ID', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Billing Information', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Hotel', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Total Days', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Rooms', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Check In Date', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Check Out Date', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Order Date', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Order Status', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Paid Amount', 'tour-booking-manager' )?></th>
                        <th class="ttbm_total_booking_th"><?php echo esc_attr__( 'Payment Method', 'tour-booking-manager' )?></th>
<!--                        <th class="ttbm_total_booking_th">Action</th>-->
                    </tr>
                    </thead>
                    <tbody class="ttbm_total_booking_tbody">

                    <?php
                    if ( $query->have_posts() ) :
                        $sl = 1 + ( ( $paged - 1 ) * 20 );
                        while ( $query->have_posts() ) : $query->the_post();
                            $order_id = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_order_id', true);
                            $billing_name = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_customer_name', true);
                            $travel_name = get_post_meta(get_the_ID(), '_ttbm_hotel_title', true);
                            $booking_days = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_days', true);
                            $hotel_infos = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_room_info', true);
                            $check_in = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_checkin_date', true);
                            $check_in = date('F j, Y', strtotime($check_in));
                            $check_out = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_checkout_date', true);
                            $check_out = date('F j, Y', strtotime($check_out));
                            $order_date = get_the_date('F j, Y');
                            $order_status = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_status', true);
                            $paid_amount = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_price', true);
                            $paid_amount = str_replace(',', '', $paid_amount);
                            $payment_method = get_post_meta(get_the_ID(), '_ttbm_hotel_booking_payment_method', true);
                            ?>

                            <tr class="ttbm_total_booking_tr">
                                <td class="ttbm_total_booking_td"><?php echo $sl++; ?></td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_order_id">#<?php echo esc_html($order_id); ?></td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_billing">
                                    <?php echo esc_html($billing_name); ?>
                                    <div class="ttbm_booking_user_more_info_holder">
                                        <div class="ttbm_booking_user_more_info" style="display: none">
                                            name: rubel
                                            address: niamat
                                            phone: dsf
                                        </div>
                                        <button class="ttbm_total_booking_view_more"><?php echo esc_attr__( 'View More', 'tour-booking-manager' )?></button>
                                    </div>
                                </td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_travel">
                                    <?php echo esc_html($travel_name); ?>
                                </td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_qty"><?php echo esc_html( $booking_days ); ?></td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_ticket">
                                    <?php
                                    if( is_array( $hotel_infos ) && !empty( $hotel_infos ) ) {
                                        $hotel_info_count = count( $hotel_infos );
                                        if( $hotel_info_count > 1 ) {
                                            $comma = ',';
                                        }else{
                                            $comma = '';
                                        }
                                        foreach ($hotel_infos as $room_name => $room_infos ) { ?>
                                            <div class="ttbm_booking_room_data"><?php echo esc_attr( $room_name )?>(<?php echo esc_attr( $room_infos['quantity'])?>)</div>
                                        <?php
                                        }
                                    }

                                    ?>
                                </td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_tour_date"><?php echo esc_html($check_in); ?></td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_tour_date"><?php echo esc_html($check_out); ?></td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_order_date"><?php echo esc_html($order_date); ?></td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_status"><?php echo esc_html($order_status); ?></td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_amount">
                                    <?php echo wp_kses_post( wc_price( $paid_amount ) ); ?>
                                </td>
                                <td class="ttbm_total_booking_td ttbm_total_booking_payment"><?php echo esc_html($payment_method); ?></td>
                                <!--<td class="ttbm_total_booking_td ttbm_total_booking_action">
                                    <button class="ttbm_total_booking_action_btn">
                                        <i class="ttbm_total_booking_icon"></i>
                                    </button>
                                </td>-->
                            </tr>

                        <?php endwhile;
                        wp_reset_postdata();
                    else : ?>
                        <tr><td colspan="14"><?php echo esc_attr__( 'No bookings found.', 'tour-booking-manager' )?></td></tr>
                    <?php endif; ?>

                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="ttbm_total_booking_pagination">
                    <?php
                    echo paginate_links(array(
                        'total' => $query->max_num_pages,
                        'current' => $paged,
                        'prev_text' => __('« Prev'),
                        'next_text' => __('Next »'),
                    ));
                    ?>
                </div>
            </div>

            <?php
        }



    }

    new TTBM_Hotel_Booking_Lists();
}