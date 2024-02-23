

<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Settings_filters')) {
    class TTBM_Settings_filter {
        public function __construct() {


            add_action('ttbm_settings_save', [$this, 'save_custom_filters']);
        }



        public function save_custom_filters($tour_id) {
            if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {

                /* If Order not exist, create the order */
                $args = array(
                    'post_title' => 'Custom filter',
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => 'custom_filters'
                );

                $post_id = wp_insert_post($args);


            }
        }
    }
    new TTBM_Settings_filter();
}