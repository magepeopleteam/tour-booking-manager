<?php
// Template Name: Hotel Booking

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
$class_location = $class_location ?? '';
?>
<div class="ttbm_default_theme">
    <div class='ttbm_style'>
        <div class="ttbm-hotel-area ">
            <div class="ttbm-hero-header">
                <div class="title-section">
                    <span class="review-star">
                        <i class="mi mi-star"></i>
                        <i class="mi mi-star"></i>
                        <i class="mi mi-star"></i>
                        <i class="mi mi-star"></i>
                        <i class="mi mi-star"></i>
                    </span>
                    <h1 class="title"><?php the_title(); ?></h1>
                    <p class="location-info">
                        <i class="mi mi-marker"></i> 
                        <?php echo __('197/2 Moo 1, Tambol Pongyang, Amphur Mae Rim, 50180 Mae Rim, Thailand','tour-booking-manager'); ?>
                        <a href="#"><?php echo __('Great location - show map','tour-booking-manager'); ?></a>
                    </p>
                </div>
                <div class="sharing-meta">
                    <div class="sharing-info">
                        <span>
                            <i class="mi mi-heart"></i>
                            <i class="mi mi-share"></i>
                        </span>
                        <button><?php echo __('Reserve','tour-booking-manager'); ?></button>
                    </div>
                    <div class="price-match">
                        <button><i class="mi mi-tags"></i> <?php echo __('We Price Match','tour-booking-manager'); ?></button>
                    </div>
                </div>
            </div>
            <div class="ttbm-hero-area">
                <div class="slider-area">
                    <?php do_action( 'ttbm_hotel_slider' ); ?>
                </div>
                <div class="review-map-container">
                    <div class="review-container">
                        <div class="review-rating">
                            <div class="review">
                                <h3>Excellant</h3>
                                <p>787 reviews</p>
                            </div>
                            <div class="review-rate">
                                7.8
                            </div>
                        </div>
                        <div class="review-testimonial">
                            <h2>Guests who stayed here loved</h2>
                            <div class="testimonial">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit.
                                Lorem ipsum dolor sit amet consectetur adipisicing elit.
                            </div>
                        </div>
                        <div class="service-rating">
                            <h3>Wifi</h3>
                            <div class="service-rate">
                                7.8
                            </div>
                        </div>
                    </div>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d116833.97311724129!2d90.33711639865318!3d23.780818544256658!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8b087026b81%3A0x8fa563bbdd5904c2!2sDhaka!5e0!3m2!1sen!2sbd!4v1754986559961!5m2!1sen!2sbd" width="100%" height="250px" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
            <div class="ttbm-content-area"></div>
            <div class="ttbm-facilities-area"></div>
            <div class="ttbm-booking-area"></div>
            <div class="ttbm-review-area"></div>
            <div class="ttbm-testimonial-area"></div>
            <div class="ttbm-faq-area"></div>
        </div>
    </div>
    <?php do_action( 'ttbm_single_tour_after' ); ?>
</div>
<?php do_action( 'ttbm_related_tour' ); ?>