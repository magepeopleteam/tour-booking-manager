<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

$today     = date('Y/m/d');
$tomorrow  = date('Y/m/d', strtotime('+1 day'));

$date_range = $today . '    -    ' . $tomorrow;
?>
<div class="ttbm_registration_area availability_section">
    <div class="justifyBetween ttbm_date_time_select">
        <div class="justifyBetween ttbm_select_date_area">
            <h4 class="ttbm-title"><?php esc_html_e('Make your booking', 'tour-booking-manager'); ?></h4>
            <div class="dFlex justifyBetween booking-button">
                <label class="_allCenter">
                    <span class="date_time_label mR_xs"><?php esc_html_e('Select Date Range:', 'tour-booking-manager'); ?></span>
                    <input type="text" name="ttbm_hotel_date_range" class="formControl" value="<?php echo esc_attr( $date_range );?>" placeholder="<?php esc_attr_e('Checkin - Checkout', 'tour-booking-manager'); ?>">
                </label>
                <button class="navy_blueButton ttbm_check_ability ttbm_hotel_room_check_availability" type="button">
                    <?php esc_html_e('See Availability', 'tour-booking-manager'); ?>
                </button>
            </div>
            <?php wp_nonce_field('ttbm_check_availability_action', 'ttbm_nonce'); ?>
        </div>
    </div>
    <div class="ttbm_booking_panel placeholder_area pRelative"></div>
</div>