<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

$ttbm_post_id    = $ttbm_post_id ?? get_the_id();
$display_hotel_rating= TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_display_hotel_rating');
$display_hotel_location= TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_display_hotel_location');
$display_hotel_distance= TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_display_hotel_distance');


?>
<div class="justifyBetween mB">
        <div class="ttbm_hotel_container">

            <?php if( $display_hotel_rating === 'on' ){
                $hotel_rating= TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_hotel_rating');
                $rating = $hotel_rating.' Stars';
                ?>
            <div class="ttbm_hotel_rating">
                <span class="ttbm_hotel_star">★</span>
                <span><?php echo esc_attr( $rating )?></span>
            </div>
            <?php }?>

            <?php if( $display_hotel_location === 'on' ){
                $hotel_location= TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_hotel_location');
                ?>
            <div class="ttbm_hotel_location">
                <span class="ttbm_hotel_location_icon">📍</span>
                <span class="ttbm_hotel_location_text"><?php echo esc_attr( $hotel_location )?></span>
            </div>
            <?php }?>

            <?php if( $display_hotel_distance === 'on' ){
                $hotel_distance_des= TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_hotel_distance_des');
                ?>
            <div class="ttbm_hotel_nearby_title"><?php esc_html_e( 'What\'s Nearby', 'tour-booking-manager' );?></div>

            <div class="ttbm_hotel_nearby_item">
                <span class="ttbm_hotel_nearby_icon">📍</span>
                <span class="ttbm_hotel_nearby_text">
                <span class="ttbm_hotel_distance"> <?php echo esc_attr( $hotel_distance_des )?> </span>
            </div>
            <?php }?>

        </div>
</div>
