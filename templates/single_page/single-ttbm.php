<?php
/**
 * Plugin Single Template
 */

defined( 'ABSPATH' ) || exit;

/**
 * --------------------------
 * HEADER AREA
 * --------------------------
 */
if ( wp_is_block_theme() ) {
    if ( function_exists( 'block_header_area' ) ) {
        ob_start();
        block_header_area();
        $header_html = trim( ob_get_clean() );

        if ( $header_html ) {
            wp_head();
            wp_body_open();
            echo '<div class="wp-site-blocks">';
            echo '<header class="wp-block-template-part site-header">';
            echo $header_html;
            echo '</header>';
            echo '</div>';
        } else {
            get_header();
        }
    } else {
        get_header();
    }
} else {
    get_header();
}

/**
 * --------------------------
 * MAIN CONTENT
 * --------------------------
 */

the_post();

do_action( 'ttbm_single_page_before_wrapper' );

if ( post_password_required() ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo get_the_password_form(); // WPCS: XSS ok.
} else {
    do_action( 'woocommerce_before_single_product' );

    $ttbm_post_id  = get_the_id();
    $tour_id       = TTBM_Function::post_id_multi_language( $ttbm_post_id );
    $template_name = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
    $all_dates     = TTBM_Function::get_date( $tour_id );
    $travel_type   = TTBM_Function::get_travel_type( $tour_id );
    $tour_type     = TTBM_Function::get_tour_type( $tour_id );

    $ttbm_display_registration = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' );
    $start_price   = TTBM_Function::get_tour_start_price( $tour_id );
    $total_seat    = TTBM_Function::get_total_seat( $tour_id );
    $available_seat = TTBM_Function::get_total_available( $tour_id );

    TTBM_Function::update_upcoming_date_month( $tour_id, true, $all_dates );
    ?>
    <div id="ttbm_content">
        <?php include_once( TTBM_Function::details_template_path() ); ?>
    </div>
    <?php
}

do_action( 'ttbm_single_page_after_wrapper' );

// ==============================
// FOOTER
// ==============================
if ( function_exists( 'block_footer_area' ) && wp_is_block_theme() ) {
    echo '<footer class="wp-block-template-part mep-site-footer">';
        block_footer_area();
    echo '</footer>';
    wp_footer();
} else {
    get_footer();
}