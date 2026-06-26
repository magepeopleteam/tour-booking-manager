<?php

// Template Name: Hotel Booking



if ( ! defined( 'ABSPATH' ) ) {

    exit;

}

$ttbm_post_id = $ttbm_post_id ?? get_the_id();

?>



<div class="ttbm_default_theme ttbm-hotel-details-theme">

    <div class='ttbm_style'>

        <div class="ttbm-hotel-details ttbm-hotel-details-page ttbm_hotel_item">

            <div class="ttbm-hotel-details-inner">

                <div class="ttbm-hero-header ttbm_hotel_hero_header">

                    <div class="title-section ttbm_hotel_hero_header__main">

                        <?php do_action( 'ttbm_details_title' ); ?>

                        <?php do_action( 'ttbm_single_location' ); ?>

                    </div>

                    <?php do_action( 'show_sharing_meta' ); ?>

                </div>

                <div class="ttbm-hotel-hero-gallery">

                    <div class="slider-area">

                        <?php do_action( 'ttbm_hotel_slider' ); ?>

                    </div>

                </div>

                <?php do_action( 'ttbm_single_features' ); ?>

                <div class="ttbm-content-area">

                    <div class="ttbm-content-left">

                        <div class="content-details">

                            <?php the_content(); ?>

                        </div>

                        <?php do_action( 'ttbm_single_popular_features' ); ?>

                        <div class="ttbm_hotel_content_area ttbm_hotel_details_booking_section" id="ttbm_hotel_content_area">

                            <input type="hidden" id="ttbm_booking_hotel_id" value="<?php echo esc_attr( $ttbm_post_id ); ?>">

                            <?php do_action( 'ttbm_make_hotel_booking', $ttbm_post_id ); ?>

                        </div>

                    </div>

                    <div class="ttbm-content-right">
                        <div class="review-container ttbm_hotel_sidebar_review">
                            <?php do_action( 'ttbm_single_review_testimonial' ); ?>
                        </div>
                        <?php do_action( 'ttbm_single_sidebar' ); ?>
                        <?php do_action( 'ttbm_single_activity' ); ?>
                    </div>

                </div>

                <?php do_action( 'ttbm_single_faq' ); ?>

                <?php do_action( 'ttbm_single_hotel_area' ); ?>

                <div class="ttbm-hero-meta ttbm-hotel-map-section">
                    <div class="review-map-container review-map-container--map-only">
                        <?php do_action( 'ttbm_single_hotel_location' ); ?>
                    </div>
                </div>

                <?php do_action( 'ttbm_related_tour' ); ?>

            </div>

        </div>

    </div>

    <?php do_action( 'ttbm_single_tour_after' ); ?>

</div>


