<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Hotel_Details_Layout')) {
    class TTBM_Hotel_Details_Layout{

        public function __construct() {
            add_action('ttbm_hotel_slider', array($this, 'hotel_slider'));
            add_action('ttbm_make_hotel_booking', array($this, 'make_hotel_booking'));

        }
        public function hotel_slider() {
            include(TTBM_Function::template_path('layout/hotel_slider.php'));
        }
        public function make_hotel_booking() {
            include(TTBM_Function::template_path('layout/make_hotel_booking.php'));
        }


    }
    new TTBM_Hotel_Details_Layout();
}

