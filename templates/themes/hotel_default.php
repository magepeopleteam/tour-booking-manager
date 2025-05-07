<?php
// Template Name: Default Theme

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
$class_location = $class_location ?? '';
?>
    <div class="ttbm_default_theme">
        <div class='ttbm_style ttbm_wraper'>
            <div class="ttbm_hotel_item fdColumn">
                <div class="ttbm_container">
                <div class="ttbm_details_page">
                    <div class="ttbm_details_page_header">
                        <?php do_action( 'ttbm_details_title' ); ?>
                        <div class="dFlex justifyStart">
                            <?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>
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

                        <div class="ttbm_content_area ttbm_hotel_content_area">
                            <input type="hidden" id="ttbm_booking_hotel_id" value="<?php echo esc_attr( $ttbm_post_id )?>">
                            <div class="ttbm_content__left">
                                <?php do_action( 'ttbm_hotel_slider' ); ?>

                                <?php do_action( 'ttbm_make_hotel_booking', $ttbm_post_id );?>

                                <?php do_action( 'ttbm_description' ); ?>
                            </div>
                            <div class="ttbm_content__right">
                                <?php do_action( 'ttbm_hotel_location_details' ); ?>
                                <?php do_action( 'ttbm_include_feature' ); ?>
                                <?php do_action( 'ttbm_exclude_service' ); ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            </div>
        </div>
        <?php do_action( 'ttbm_single_tour_after' ); ?>
    </div>
<?php do_action( 'ttbm_related_tour' ); ?>