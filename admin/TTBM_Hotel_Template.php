<?php
/**
 * Tempalate for hotel booking
 * @author Rubel Mia <rubelcuet10@gmail.com>
 * @version 1.0.0
 */

if (!defined('ABSPATH')) die;

if (!class_exists('TTBM_Hotel_Template')) {
    class TTBM_Hotel_Template{

        public function __construct() {
            add_action('ttbm_template_header',[$this, 'template_header']);
            /*add_action('mptrs_template_header',[$this, 'template_popup_tablely']);
            add_action('mptrs_template_header',[$this, 'template_popup_reviews']);
            add_action('mptrs_template_header',[$this, 'template_popup_restaurant']);

            add_action('mptrs_template_logo',[$this, 'template_logo']);
            add_action('mptrs_restaurant_info',[$this, 'restaurant_info']);
            add_action('mptrs_template_menus',[$this, 'display_restaurant_content']);
            add_action('mptrs_template_basket',[$this, 'display_restaurant_basket']);
            // add_action('mptrs_sidebar_content',[$this, 'display_sidebar_content']);
            add_action('mptrs_time_schedule',[$this, 'display_time_schedule']);*/
        }

        public function template_header( $post_id ){
//            $post_id = get_the_id();
            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
//            error_log( print_r($thumbnail_url, true));
            if ( has_post_thumbnail() ) : ?>
                <header class="mptrs-header-baner">
                    <img alt="<?php esc_attr( get_the_title() );?>" src=" <?php  echo esc_attr( $thumbnail_url );?>">
                </header>
            <?php endif; ?>
            <?php
        }

    }
}

new TTBM_Hotel_Template();