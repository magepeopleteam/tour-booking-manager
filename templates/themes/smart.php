<?php
	// Template Name: Smart Theme
	
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$class_location = $class_location ?? '';
?>
<div class="ttbm_smart_theme ">
	<div class='mpStyle ttbm_wraper'>
        <div class="ttbm_container">
            <div class="ttbm_details_page">
	            <?php do_action( 'ttbm_details_title' ); ?>
                    <div class="_dFlex_wrap">
	                    <?php do_action( 'ttbm_details_title_after', $ttbm_post_id ); ?>
	                    <?php do_action( 'ttbm_details_location'); ?>
                    </div>

            </div>
        </div>
		<div class="ttbm_container">
			<div class="ttbm_details_page ttbm_content_area">
				<div class="ttbm_content__left">

					<?php do_action( 'ttbm_slider' ); ?>
					<?php do_action( 'ttbm_short_details' ); ?>
					<?php do_action( 'ttbm_details_particular_area' ); ?>
					<?php do_action( 'ttbm_description' ); ?>
					<?php do_action( 'ttbm_include_feature' ); ?>
					<?php do_action( 'ttbm_exclude_service' ); ?>
					<?php do_action( 'ttbm_registration_before', $ttbm_post_id ); ?>
					<?php do_action( 'ttbm_hiphop_place' ); ?>
					<?php do_action( 'ttbm_day_wise_details' ); ?>
					<?php do_action( 'ttbm_faq' ); ?>
					<?php do_action( 'ttbm_review' ); ?>
				</div>
				<div class="ttbm_content__right">
					<?php do_action( 'ttbm_registration' ); ?>
					
					<?php do_action( 'ttbm_activity' ); ?>
					<?php do_action( 'ttbm_location_map', $ttbm_post_id ); ?>
					<?php do_action( 'ttbm_why_choose_us' ); ?>
					<?php do_action( 'ttbm_get_a_question' ); ?>
					<?php do_action( 'ttbm_tour_guide' ); ?>
					<?php do_action( 'ttbm_dynamic_sidebar', $ttbm_post_id ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php do_action( 'ttbm_single_tour_after' ); ?>

	<?php do_action( 'ttbm_related_tour' ); ?>
</div>
