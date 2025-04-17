<?php
// Template Name: Default Theme

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id = get_the_ID();
//error_log( print_r( [ ''], true ) );
?>
<main class="mptrs-default-template">
    <?php do_action('ttbm_template_header', $post_id ); ?>
    <div class="mptrs-header">
<!--        --><?php //do_action('mptrs_template_logo'); ?>
        <div class="mptrs-restaurant-info">
<!--            --><?php //do_action('mptrs_restaurant_info'); ?>
        </div>
    </div>
    <div class="mptrs-content">
        <div class="mptrs-content-left">
<!--            --><?php //do_action('mptrs_template_menus'); ?>
        </div>
        <div class="mptrs-content-right">
            ssss de
<!--            --><?php //do_action('mptrs_template_basket'); ?>
<!--            --><?php //do_action('mptrs_sidebar_content'); ?>
        </div>
    </div>
</main>
