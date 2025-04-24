<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists( 'TTBM_Hotel_Booking' ) ) {
    class TTBM_Hotel_Booking{
        public function __construct() {

            add_action( 'wp_ajax_ttbm_get_hotel_room_list', array( $this, 'ttbm_get_hotel_room_list' ) );
            add_action( 'wp_ajax_nopriv_ttbm_get_hotel_room_list', array( $this, 'ttbm_get_hotel_room_list' ) );

            add_action( 'ttbm_hotel_booking_panel', array( $this, 'hotel_booking_panel' ), 10, 4 );
        }

        public function ttbm_get_hotel_room_list() {
            $tour_id    = $_REQUEST['tour_id'] ?? '';
            $hotel_id   = $_REQUEST['hotel_id'] ?? '';
            $date_range = $_REQUEST['date_range'] ?? "";
            $date       = explode( "    -    ", $date_range );
            $start_date = date( 'Y-m-d', strtotime( $date[0] ) );
            $end_date = date( 'Y-m-d', strtotime( $date[1] ) );
            do_action( 'ttbm_hotel_booking_panel', $start_date, $end_date, $hotel_id );
            die();
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


    }
    new TTBM_Hotel_Booking();
}