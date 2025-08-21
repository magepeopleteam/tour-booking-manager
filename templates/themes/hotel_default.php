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
        <div class="ttbm-hotel-area ttbm_hotel_item">
            <div class="ttbm-hero-header">
                <div class="title-section">
                    <?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>
                    <h1 class="title"><?php do_action( 'ttbm_details_title' ); ?></h1>
                    <p class="location-info">
                        <i class="mi mi-marker"></i> 
                        <?php echo __('197/2 Moo 1, Tambol Pongyang, Amphur Mae Rim, 50180 Mae Rim, Thailand','tour-booking-manager'); ?>
                        <a href="#"><?php echo __('Great location - show map','tour-booking-manager'); ?></a>
                    </p>
                    
                    <?php
                    if ( is_plugin_active( 'tour-booking-manager-pro/tour-booking-manager-pro.php' ) ):
                        $location = TTBM_Function::get_full_location( $ttbm_post_id );
                        if ( $location && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_hotel_location', 'on' ) != 'off' ) {
                            ?>
                            <span class="pL_xs pR_xs">|</span>
                        <?php }
                    endif;
                    ?>
                    <?php include( TTBM_Function::template_path( 'layout/location.php' ) ); ?>
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
            <div class="ttbm-feature-list">
                <div class="feature-items">
                    <i class="mi mi-house"></i>
                    <span>Apartments</span>
                </div>
                <div class="feature-items">
                    <i class="mi mi-deck"></i>
                    <span>Balcony</span>
                </div>
                <div class="feature-items">
                    <i class="mi mi-cup"></i>
                    <span>Very Good Breakfast</span>
                </div>
                <div class="feature-items">
                    <i class="mi mi-eye"></i>
                    <span>View</span>
                </div>
                <div class="feature-items">
                    <i class="mi mi-wifi"></i>
                    <span>Free Wifi</span>
                </div>
                <div class="feature-items">
                    <i class="mi mi-food-service"></i>
                    <span>Room Service</span>
                </div>
                <div class="feature-items">
                    <i class="mi mi-restaurants"></i>
                    <span>Restaurants</span>
                </div>
                
                <div class="feature-items">
                    <i class="mi mi-airport-shuttle"></i>
                    <span>Airport Shuttle</span>
                </div>
                <div class="feature-items">
                    <i class="mi mi-bath-taking"></i>
                    <span>Bath</span>
                </div>
            </div>
            <div class="ttbm-content-area">
                <div class="ttbm-content-left">
                    <div class="content-details">
                        <?php the_content(); ?>
                    </div>
                    <div class="popular-facilities">
                        <h2>Popular Facilities</h2>
                        <ul>
                            <li>
                                <i class="mi mi-swimmer"></i>
                                2 swimming pools
                            </li>
                            <li>
                                <i class="mi mi-parking"></i>
                                Free parking
                            </li>
                            <li>
                                <i class="mi mi-wifi"></i>
                                Free Wifi
                            </li>
                            <li>
                                <i class="mi mi-family"></i>
                                Family rooms
                            </li>
                            <li>
                                <i class="mi mi-flower-tulip"></i>
                                Spa
                            </li>
                        </ul>
                    </div>
                    <div class="ttbm_hotel_content_area">
                        <input type="hidden" id="ttbm_booking_hotel_id" value="<?php echo esc_attr( $ttbm_post_id )?>">
                        <?php do_action('ttbm_make_hotel_booking', $ttbm_post_id );?>
                    </div>
                   
                </div>
                <div class="ttbm-content-right">
                    <div class="widgets property-highlights">
                        <h2>Property highlights</h2>
                        <div class="widgets-text">
                            <i class="mi mi-marker"></i>
                            Top Location: Highly rated by recent guests (8.9)
                        </div>
                    </div>
                    <div class="widgets breakfast-info">
                        <h2>Breakfast info</h2>
                        <div class="widgets-text">
                            <i class="mi mi-burger-glass"></i>
                            American, Buffet
                        </div>
                    </div>
                    <div class="widgets parking-info">
                        <h2>Parking info</h2>
                        <div class="widgets-text">
                            <i class="mi mi-parking-circle"></i>
                            Free Parking Available On Site
                        </div>
                    </div>
                    <div class="widgets activities-info">
                        <h2>activities</h2>
                        <ul class="widgets-text">
                            <li>Hiking</li>
                            <li>Jumping</li>
                            <li>Running</li>
                            <li>Swimming</li>
                        </ul>
                    </div>
                    <button class="widgets-button">Reserve</button>
                </div>
            </div>
            <!-- FAQ Section -->
            <?php do_action( 'ttbm_single_faq' ); ?>
            <!-- FAQ Section -->
        </div>
    </div>
    <?php do_action( 'ttbm_single_tour_after' ); ?>
</div>
<?php do_action( 'ttbm_related_tour' ); ?>