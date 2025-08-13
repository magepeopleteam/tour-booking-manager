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
                    <?php do_action( 'ttbm_make_hotel_booking', $ttbm_post_id );?>
                    <?php 
                        $faqs = 
                        [
                            [
                                'id' => 'breakfast',
                                'question' => 'Do they serve breakfast?',
                                'answer' => 'Yes, we offer a complimentary continental breakfast served daily from 6:30 AM to 10:00 AM in our main dining area.'
                            ],
                            [
                                'id' => 'checkin-checkout',
                                'question' => 'What are the check-in and check-out times?',
                                'answer' => 'Check-in time is 3:00 PM and check-out time is 11:00 AM. Early check-in and late check-out may be available upon request.'
                            ],
                            [
                                'id' => 'airport-shuttle',
                                'question' => 'Is there an airport shuttle service?',
                                'answer' => 'Yes, we provide complimentary airport shuttle service. Please contact the front desk to schedule your pickup time.'
                            ],
                            [
                                'id' => 'parking',
                                'question' => 'Can I park there?',
                                'answer' => 'Free self-parking is available for all guests. We also offer valet parking service for an additional fee.'
                            ],
                            [
                                'id' => 'restaurant',
                                'question' => 'Is there a restaurant?',
                                'answer' => 'Yes, our on-site restaurant serves lunch and dinner with a variety of local and international dishes.'
                            ],
                            [
                                'id' => 'private-bathroom',
                                'question' => 'Are there rooms with a private bathroom?',
                                'answer' => 'All of our rooms feature private bathrooms with modern amenities including complimentary toiletries.'
                            ],
                            [
                                'id' => 'nearby-attractions',
                                'question' => 'What restaurants, attractions, and public transit options are nearby?',
                                'answer' => 'We are located near several restaurants, shopping centers, and tourist attractions. Public transportation is easily accessible within a 5-minute walk.'
                            ],
                            [
                                'id' => 'spa',
                                'question' => 'Is the spa open?',
                                'answer' => 'Our spa is open daily from 9:00 AM to 8:00 PM. Advanced booking is recommended for spa treatments.'
                            ],
                            [
                                'id' => 'swimming-pool',
                                'question' => 'Is the swimming pool open?',
                                'answer' => 'Yes, our indoor swimming pool is open 24/7 for guest use. Pool towels are provided at the front desk.'
                            ],
                            [
                                'id' => 'balcony-rooms',
                                'question' => 'Are there rooms with a balcony?',
                                'answer' => 'Yes, we offer several room types with private balconies overlooking the city or garden areas.'
                            ],
                            [
                                'id' => 'balcony-rooms',
                                'question' => 'Are there rooms with a balcony?',
                                'answer' => 'Yes, we offer several room types with private balconies overlooking the city or garden areas.'
                            ],
                            [
                                'id' => 'balcony-rooms',
                                'question' => 'Are there rooms with a balcony?',
                                'answer' => 'Yes, we offer several room types with private balconies overlooking the city or garden areas.'
                            ],
                            [
                                'id' => 'balcony-rooms',
                                'question' => 'Are there rooms with a balcony?',
                                'answer' => 'Yes, we offer several room types with private balconies overlooking the city or garden areas.'
                            ]
                        ];
                    ?>
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
            <div class="faq-area">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-groups">
                    <?php 
                        $counter = 0;
                        foreach ($faqs as $faq) {
                            if ($counter % 5 === 0) {
                                echo '<div class="faq-group">';
                            }
                        ?>
                            <div class="faq-item" data-faq="<?php echo $faq['id']; ?>">
                                <div class="faq-question">
                                    <i class="mi mi-speech-bubble-story"></i>
                                    <span><?php echo $faq['question']; ?></span>
                                </div>
                            </div>
                        <?php
                        $counter++;
                        if ($counter % 5 === 0 || $counter === count($faqs)) {
                            echo '</div>';
                        }
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php do_action( 'ttbm_single_tour_after' ); ?>
</div>
<?php do_action( 'ttbm_related_tour' ); ?>