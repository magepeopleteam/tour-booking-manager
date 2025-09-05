<?php
// Template Name: Hotel Booking

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
?>
<div class="ttbm_default_theme">
    <div class='ttbm_style'>
        <div class="ttbm-hotel-details ttbm_hotel_item">
            <div class="ttbm-hero-header">
                <div class="title-section">
                    <?php //do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>
                    <h1 class="title"><?php do_action( 'ttbm_details_title' ); ?></h1>
                    <?php do_action('ttbm_single_location'); ?>
                </div>
                <?php do_action('show_sharing_meta'); ?>
            </div>
            <div class="ttbm-hero-area">
                <div class="slider-area">
                    <?php do_action( 'ttbm_hotel_slider' ); ?>
                </div>
                <div class="review-map-container">
                    <div class="review-container">
                        <?php do_action('ttbm_single_review_testimonial'); ?>
                    </div>
                    <?php do_action('ttbm_single_hotel_location'); ?>
                </div>
            </div>
            <!-- show features -->
            <?php do_action( 'ttbm_single_features'); ?>
            <div class="ttbm-content-area">
                <div class="ttbm-content-left">
                    <div class="content-details">
                        <?php the_content(); ?>
                    </div>
                    <?php do_action( 'ttbm_single_popular_features'); ?>
                    <div class="ttbm_hotel_content_area" id="ttbm_hotel_content_area">
                        <input type="hidden" id="ttbm_booking_hotel_id" value="<?php echo esc_attr( $ttbm_post_id )?>">
                        <?php do_action('ttbm_make_hotel_booking', $ttbm_post_id );?>
                    </div>
                </div>
                <div class="ttbm-content-right">
                    <?php do_action( 'ttbm_single_sidebar' ); ?>
                    <?php do_action( 'ttbm_single_activity' ); ?>
                    
                    <a class="widgets-button button" href="#ttbm_hotel_content_area"><?php echo esc_html__('Reserve','tour-booking-manager'); ?></a>
                </div>
            </div>
            <!-- FAQ Section -->
            <?php do_action( 'ttbm_single_faq' ); ?>
            <!-- FAQ Section -->
             <?php do_action( 'ttbm_single_hotel_area'); ?>
        </div>
    </div>
    <?php do_action( 'ttbm_single_tour_after' ); ?>
</div>
<?php do_action( 'ttbm_related_tour' ); ?>