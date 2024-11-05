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
		<div class='mpStyle ttbm_wraper'>
			<div class="ttbm_container">
				<div class="ttbm_details_page">
                    <div class="ttbm_details_page_header">
                        <?php do_action( 'ttbm_details_title' ); ?>
                        <div class="dFlex justifyStart">
							<?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?> 
							<?php
							if ( is_plugin_active( 'tour-booking-manager-pro/tour-booking-manager-pro.php' ) ):
								$location = TTBM_Function::get_full_location( $ttbm_post_id );
								if ( $location && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_location', 'on' ) != 'off' ) {
									?>
								<span class="pL_xs pR_xs">|</span>
							<?php }
							endif; 
							?>
							<?php include( TTBM_Function::template_path( 'layout/location.php' ) ); ?>
						</div>
                    </div>
					<div class="ttbm_content_area">
						<div class="ttbm_content__left">
							<?php do_action( 'ttbm_slider' ); ?>
							<?php do_action( 'ttbm_short_details' ); ?>
							<?php include( TTBM_Function::template_path( 'ticket/registration.php' ) ); ?>
							<?php include( TTBM_Function::template_path( 'ticket/particular_item_area.php' ) ); ?>
							<?php do_action( 'ttbm_description' ); ?>
							<?php do_action( 'ttbm_registration_before', $ttbm_post_id ); ?>
							<?php do_action( 'ttbm_hiphop_place' ); ?>
							<?php do_action( 'ttbm_day_wise_details' ); ?>
							<?php do_action( 'ttbm_faq' ); ?>
                            <?php do_action( 'ttbm_review' ); ?>
						</div>
						<div class="ttbm_content__right">
							<?php do_action( 'ttbm_include_feature' ); ?>
							<?php do_action( 'ttbm_exclude_service' ); ?>
							<?php do_action( 'ttbm_activity' ); ?>
							<?php //do_action( 'ttbm_hotel_list' ); ?>
							<?php do_action( 'ttbm_location_map', $ttbm_post_id ); ?>
							<?php do_action( 'ttbm_why_choose_us' ); ?>
							<?php do_action( 'ttbm_get_a_question' ); ?>
							<?php do_action( 'ttbm_tour_guide' ); ?>
							<?php do_action( 'ttbm_dynamic_sidebar', $ttbm_post_id ); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php do_action( 'ttbm_single_tour_after' ); ?>
	</div>
<?php do_action( 'ttbm_related_tour' ); ?>