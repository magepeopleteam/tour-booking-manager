<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('TTBM_Details_Layout')) {
        class TTBM_Details_Layout {
            public function __construct() {
                add_action('ttbm_details_title', array($this, 'details_title'));
                add_action('ttbm_section_title', array($this, 'section_title'), 10, 2);
                add_action('ttbm_section_titles', array($this, 'section_titles'), 10, 2);
                add_action('ttbm_slider', array($this, 'slider'));
                add_action('ttbm_description', array($this, 'description'));
                add_action('ttbm_include_feature', array($this, 'include_feature'));
                add_action('ttbm_exclude_service', array($this, 'exclude_service'));
                add_action('ttbm_short_details', array($this, 'short_details'),10);
                add_action('ttbm_location_map', array($this, 'location_map'), 10, 1);
                add_action('ttbm_activity', array($this, 'activity'));
                add_action('ttbm_hiphop_place', array($this, 'hiphop_place'));
                add_action('ttbm_day_wise_details', array($this, 'day_wise_details'));
                add_action('ttbm_faq', array($this, 'faq'));
                add_action('ttbm_why_choose_us', array($this, 'why_choose_us'));
                add_action('ttbm_get_a_question', array($this, 'get_a_question'));
                add_action('ttbm_tour_guide', array($this, 'tour_guide'));
                //add_action( 'ttbm_hotel_list', array( $this, 'hotel_list' ) );
                add_action('ttbm_related_tour', array($this, 'related_tour'));
                add_action('ttbm_dynamic_sidebar', array($this, 'dynamic_sidebar'), 10, 1);
            }
            public function details_title() {
                include(TTBM_Function::template_path('layout/title_details_page.php'));
            }
            public function section_title($option_name, $default_title) {
                include(TTBM_Function::template_path('layout/title_section.php'));
            }
            public function section_titles($tour_id, $ttbm_title) {
                include(TTBM_Function::template_path('layout/section_title.php'));
            }
            public function slider() {
                include(TTBM_Function::template_path('layout/slider.php'));
            }
            public function description() {
                include(TTBM_Function::template_path('layout/description.php'));
            }
            //***************Feature************************//
            public function include_feature() {
                include(TTBM_Function::template_path('layout/include_feature.php'));
            }
            public function exclude_service() {
                include(TTBM_Function::template_path('layout/exclude_service.php'));
            }
            //*******************************************//
            public function short_details() {
                $ttbm_post_id = $ttbm_post_id ?? get_the_id();
	            $tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
                $tour_type = $tour_type ?? TTBM_Function::get_tour_type($tour_id);
                if ($tour_type != 'hotel') {
                    $count = 0;
                    ?>
					<div class="flexWrap item_section">
                        <?php include(TTBM_Function::template_path('layout/duration_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/start_price_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/max_people_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/start_location_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/age_range_box.php')); ?>

                        <?php include(TTBM_Function::template_path('layout/seat_info.php')); ?>

                        <?php include(TTBM_Function::template_path('layout/language_box.php')); ?>
                        
					</div>
                    <?php
                }
            }
            public function location_map($tour_id) {
                include(TTBM_Function::template_path('layout/location_map.php'));
            }
            //*******************************************//
            public function activity() {
                include(TTBM_Function::template_path('layout/activity.php'));
            }
            public function hiphop_place() {
                include(TTBM_Function::template_path('layout/hiphop_place.php'));
            }
            public function day_wise_details() {
                include(TTBM_Function::template_path('layout/day_wise_details.php'));
            }
            public function faq() {
                include(TTBM_Function::template_path('layout/faq.php'));
            }
            public function why_choose_us() {
                include(TTBM_Function::template_path('layout/why_choose_us.php'));
            }
            public function get_a_question() {
                include(TTBM_Function::template_path('layout/get_a_question.php'));
            }
            public function tour_guide() {
                include(TTBM_Function::template_path('layout/tour_guide.php'));
            }
            public function hotel_list() {
                //include( TTBM_Function::template_path( 'layout/hotel_list.php' ) );
                //echo '<pre>';print_r();echo '</pre>';
            }
            public function related_tour() {
                include(TTBM_Function::template_path('layout/related_tour.php'));
            }
            //********************************************//
            public function dynamic_sidebar($tour_id) {
                if (MP_Global_Function::get_post_info($tour_id, 'ttbm_display_sidebar', 'on') != 'off') {
                    dynamic_sidebar('ttbm_details_sidebar');
                }
            }
        }
        new TTBM_Details_Layout();
    }