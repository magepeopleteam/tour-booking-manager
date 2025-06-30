<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}


$ttbm_post_id = $ttbm_post_id ?? get_the_id();
//$room_lists = TTBM_Global_Function::get_post_info( $hotel_id, 'ttbm_room_details', array() );
$date_range = $_REQUEST['date_range'] ?: "";
if ( $hotel_id && $date_range ) {
    $hotel_date = explode( "-", $date_range );
    $date1      = date( 'Y-m-d', strtotime( $hotel_date[0] ) );
    $date2      = date( 'Y-m-d', strtotime( $hotel_date[1] ) );
    $days       = date_diff( date_create( $date1 ), date_create( $date2 ) );

    $room_lists_new = TTBM_Global_Function::pa_get_full_room_ticket_info( $hotel_id, $date1, $date2 );

    ?>
    <input type="hidden" name='ttbm_tour_hotel_list' value='<?php echo esc_attr( $hotel_id ); ?>'>
    <input type="hidden" name='ttbm_hotel_num_of_day' value='<?php echo esc_attr( $days->days ); ?>'>
    <input type="hidden" name='ttbm_checkin_date' value='<?php echo esc_attr( $date1 ); ?>'>
    <input type="hidden" name='ttbm_checkout_date' value='<?php echo esc_attr( $date2 ); ?>'>
    <div class="">
        <?php
        $option_name   = 'ttbm_string_availabe_ticket_list';
        $default_title = esc_html__( 'Available Room List ', 'tour-booking-manager' );
        include( TTBM_Function::template_path( 'layout/title_section.php' ) );
        ?>
        <div class="ttbm_widget_content" data-placeholder>
            <table class="mp_tour_ticket_type">
                <!-- <thead>
                <tr>
                    <th><?php echo esc_html( TTBM_Function::ticket_name_text() ); ?></th>
                    <th><?php echo esc_html( TTBM_Function::ticket_price_text() ); ?></th>
                    <th><?php echo esc_html( TTBM_Function::ticket_qty_text() ); ?></th>
                </tr>
                </thead> -->
                <tbody>
                <?php
                foreach ( $room_lists_new as $ticket ) {
                    $room_name        = array_key_exists( 'ttbm_hotel_room_name', $ticket ) ? $ticket['ttbm_hotel_room_name'] : '';
                    $price            = array_key_exists( 'ttbm_hotel_room_price', $ticket ) ? $ticket['ttbm_hotel_room_price'] : 0;
                    $sale_price            = array_key_exists( 'sale_price', $ticket ) ? $ticket['sale_price'] : '';
                    $price            = TTBM_Global_Function::wc_price( $hotel_id, $price );
                    $ticket_price_raw = TTBM_Global_Function::price_convert_raw( $price );
                    $ticket_qty       = array_key_exists( 'ttbm_hotel_room_qty', $ticket ) ? $ticket['ttbm_hotel_room_qty'] : 0;
                    $reserve          = 0;
                    $min_qty          = apply_filters( 'ttbm_ticket_type_min_qty', 0 );
                    $max_qty          = apply_filters( 'ttbm_ticket_type_max_qty', 0 );
//                    $sold_type        = TTBM_Function::get_total_sold( $tour_id, $tour_date, $room_name, $hotel_id );
                    $sold_type = 0;
//                    $available        = $ticket_qty - ( $sold_type + $reserve );
                    $available        = $ticket['available'];
                    ?>
                    <tr>
                        <td class="ttbm-hotel-room-info">
                            <p><?php echo TTBM_Global_Function::esc_html( $room_name ); ?></p>
                            <?php
                            $adult_qty = array_key_exists( 'ttbm_hotel_room_capacity_adult', $ticket ) ? $ticket['ttbm_hotel_room_capacity_adult'] : 0;
                            $child_qty = array_key_exists( 'ttbm_hotel_room_capacity_child', $ticket ) ? $ticket['ttbm_hotel_room_capacity_child'] : 0;
                            if ( $adult_qty > 0 ) {
                                for ( $i = 0; $i < $adult_qty; $i ++ ) {
                                    ?>
                                    <i class="fas fa-user-alt"></i>
                                    <?php
                                }
                            }
                            if ( $child_qty > 0 ) {
                                for ( $i = 0; $i < $child_qty; $i ++ ) {
                                    ?>
                                    <i class="fas fa-child-dress"></i>
                                    <?php
                                }
                            }
                            ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($sale_price) { ?>
                                <span class="strikeLine"><?php echo TTBM_Global_Function::wc_price($hotel_id, $sale_price); ?></span>
                            <?php } ?>
                            <?php echo TTBM_Global_Function::esc_html( $price ); ?>/
                            <?php esc_html_e( 'Night ', 'tour-booking-manager' ); ?>&nbsp;X
                            <?php echo esc_html( $days->days ); ?>
                        </td>
                        <td style="text-align: right;" class="ttbm_hotel_room_incDec"><?php TTBM_Layout::qty_input( $room_name, $available, 'inputbox', 0, $min_qty, $max_qty, $ticket_price_raw, 'ticket_qty[]' ); ?></td>
                    </tr>
                    <tr>
                        <td colspan=3>
                            <input type="hidden" name='hotel_id[]' value='<?php echo esc_html( $hotel_id ); ?>'>
                            <input type="hidden" name='ticket_name[]' value='<?php echo esc_html( $room_name ); ?>'>
                            <input type="hidden" name='ticket_max_qty[]' value='<?php echo esc_html( $max_qty ); ?>'>
                            <?php do_action( 'ttbm_after_ticket_type_item', $hotel_id, $ticket ); ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    include( TTBM_Function::template_path( 'ticket/extra_service.php' ) );
    do_action( 'ttbm_book_now_before', $hotel_id );
    include( TTBM_Function::template_path( 'ticket/hotel_book_now.php' ) );
} else {
    ?>
    <div class="dLayout allCenter _mT_bgWarning" data-placeholder>
        <h3 class="textWhite"><?php esc_html_e( 'No Room available !', 'tour-booking-manager' ); ?></h3>
    </div>
    <?php
}
?>