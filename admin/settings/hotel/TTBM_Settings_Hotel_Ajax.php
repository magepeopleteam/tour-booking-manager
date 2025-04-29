<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Settings_Hotel_Ajax')) {
    class TTBM_Settings_Hotel_Ajax{
        public function __construct() {

            add_action('wp_ajax_get_ttbm_hotel_booking_all_lists', [$this, 'get_ttbm_hotel_booking_all_lists']);
            add_action('wp_ajax_nopriv_get_ttbm_hotel_booking_all_lists', [$this, 'get_ttbm_hotel_booking_all_lists']);

            add_action('wp_ajax_get_ttbm_hotel_booking_load_more_lists', [$this, 'get_ttbm_hotel_booking_load_more_lists']);
            add_action('wp_ajax_nopriv_get_ttbm_hotel_booking_load_more_lists', [$this, 'get_ttbm_hotel_booking_load_more_lists']);
        }

        public function get_ttbm_hotel_booking_all_lists() {
            $result = $post_count = $found_posts = 0;
            $result_data = '';
            $message = 'Something went wrong';
            if( isset( $_POST ) ){
                if( isset( $_POST['action'] ) && $_POST['action'] == 'get_ttbm_hotel_booking_all_lists' ){
                    if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ttbm_admin_nonce')) {

                        $result = 1;

                        $selected_hotel_id = isset( $_POST['hotel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['hotel_id'] ) ) : '' ;
                        $selected_date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '' ;

                        $posts_per_page = isset( $_POST['display_limit'] ) ? (int) $_POST['display_limit'] : -1;
                        $excluded_post_ids = [];

                        $query = TTBM_Hotel_Booking_Lists::ttbm_hotel_order_query( $selected_date, $selected_hotel_id, $excluded_post_ids, $posts_per_page );
                        $post_count = $query->post_count;
                        $found_posts = $query->found_posts;
                        $result_data = TTBM_Hotel_Booking_Lists::ttbm_display_booking_lists( $query );
                        $message = 'Seat Successfully Reserved!';

                    }
                }
            }


            wp_send_json_success([
                'message' => $message,
                'success' => $result,
                'data' => $result_data,
                'post_count' => $post_count,
                'found_posts' => $found_posts,
            ]);
        }

        public function get_ttbm_hotel_booking_load_more_lists() {
            $result = $post_count = $found_posts = 0;
            $result_data = '';
            $message = 'Something went wrong';
            if( isset( $_POST ) ){
                if( isset( $_POST['action'] ) && $_POST['action'] == 'get_ttbm_hotel_booking_load_more_lists' ){
                    if ( isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'ttbm_admin_nonce')) {

                        $result = 1;
                        $selected_hotel_id = isset( $_POST['hotel_id'] ) ? sanitize_text_field( wp_unslash( $_POST['hotel_id'] ) ) : '' ;
                        $selected_date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '' ;
                        $loaded_ids_str = isset( $_POST['loaded_ids_str'] ) ? sanitize_text_field( wp_unslash( $_POST['loaded_ids_str'] ) ) : [] ;

                        $posts_per_page = isset( $_POST['display_limit'] ) ? (int) $_POST['display_limit'] : -1;
                        $excluded_post_ids = explode( ',', $loaded_ids_str );
                        $query = TTBM_Hotel_Booking_Lists::ttbm_hotel_order_query( $selected_date, $selected_hotel_id, $excluded_post_ids, $posts_per_page );
                        $post_count = $query->post_count;
                        $found_posts = $query->found_posts;

                        $result_data = TTBM_Hotel_Booking_Lists::ttbm_display_booking_lists( $query, 'load_more' );
                        $message = 'Seat Successfully Reserved!';

                    }
                }
            }

            wp_send_json_success([
                'message' => $message,
                'success' => $result,
                'data' => $result_data,
                'post_count' => $post_count,
                'found_posts' => $found_posts,
            ]);
        }
    }

    new TTBM_Settings_Hotel_Ajax();
}