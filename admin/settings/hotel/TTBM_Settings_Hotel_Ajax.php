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

            add_action('wp_ajax_get_ttbm_hotel_search_by_title', [$this, 'get_ttbm_hotel_search_by_title']);
            add_action('wp_ajax_nopriv_get_ttbm_hotel_search_by_title', [$this, 'get_ttbm_hotel_search_by_title']);

            add_action('wp_ajax_ttbm_load_more_hotel_lists_admin', [$this, 'ttbm_load_more_hotel_lists_admin']);
            add_action('wp_ajax_nopriv_ttbm_load_more_hotel_lists_admin', [$this, 'ttbm_load_more_hotel_lists_admin']);
        }

        public function get_ttbm_hotel_search_by_title() {
	        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'] )), 'ttbm_admin_nonce' ) ) {
		        wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
		        die;
	        }
            $search_term = isset( $_POST['search_term'] ) ? sanitize_text_field( wp_unslash( $_POST['search_term'] ) ) : '';
            $display_limit = 20;
            $success = false;
            $nonce = '';

            if( empty( $search_term ) ) {
                $args = array(
                    'post_type' => 'ttbm_hotel',
                    'post_status'    => array( 'publish', 'draft' ),
                    'posts_per_page' => $display_limit,
                );
            }else {
                $args = array(
                    'post_type' => 'ttbm_hotel',
                    'post_status'    => array( 'publish', 'draft' ),
                    's' => $search_term,
                    'fields' => 'ids',
                    'posts_per_page' => $display_limit,
                );
            }

            $query = new WP_Query( $args );

//          $result_data = TTBM_Hotel_Booking_Lists::ttbm_display_Hotel_lists( $query );
            $result_data = TTBM_Hotel_Booking_Lists::display_hotel_lists_as_table( $query );
            if( $result_data){
                $success = true;
            }

            wp_send_json_success([
                'result_data' => $result_data,
                'success' => $success,
            ]);
        }

        public function get_ttbm_hotel_booking_all_lists() {
            $result = $post_count = $found_posts = 0;
            $result_data = '';
            $message = 'Something went wrong';
            if( isset( $_POST ) ){
                if( isset( $_POST['action'] ) &&sanitize_text_field(wp_unslash($_POST['action'] ))== 'get_ttbm_hotel_booking_all_lists' ){
                    if (isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {

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
                if( isset( $_POST['action'] ) && sanitize_text_field(wp_unslash($_POST['action'] )) == 'get_ttbm_hotel_booking_load_more_lists' ){
                    if ( isset($_POST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {

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

        public function ttbm_load_more_hotel_lists_admin() {
            $result = $post_count = $found_posts = 0;
            $result_data = '';
            $message = 'Something went wrong';
            if( isset( $_POST ) ){
                if( isset( $_POST['action'] ) && sanitize_text_field(wp_unslash($_POST['action'] ))== 'ttbm_load_more_hotel_lists_admin' ){

                    if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['nonce'] )), 'ttbm_admin_nonce')) {
                        $result = 1;

                        $loaded_ids_str = isset( $_POST['loaded_ids_str'] ) ? sanitize_text_field( wp_unslash( $_POST['loaded_ids_str'] ) ) : [] ;


                        $posts_per_page = isset( $_POST['display_limit'] ) ? (int) $_POST['display_limit'] : -1;
                        $excluded_post_ids = explode( ',', $loaded_ids_str );

                        $query = TTBM_Hotel_Booking_Lists::ttbm_hotel_list_query( $posts_per_page, $excluded_post_ids );
                        $post_count = $query->post_count;
                        $found_posts = $query->found_posts;

                        $result_data = TTBM_Hotel_Booking_Lists::display_hotel_lists_as_table( $query, 'load_more' );
                        $message = 'Seat Successfully Reserved!';

                    }
                }
            }

            wp_send_json_success([
                'message' => $message,
                'success' => $result,
                'result_data' => $result_data,
                'post_count' => $post_count,
                'found_posts' => $found_posts,
            ]);
        }
    }

    new TTBM_Settings_Hotel_Ajax();
}