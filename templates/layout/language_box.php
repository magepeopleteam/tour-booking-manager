<?php
	if ( ! defined( 'ABSPATH' ) )die;

	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$status = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_travel_language_status');
	$tour_type = TTBM_Function::get_tour_type( $ttbm_post_id );

    $language = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_travel_language');
    $language_lists = MP_Global_Function::get_languages();
    foreach($language_lists as $key => $value):
        if($key == $language){
            $language =  $value;
        }
    endforeach;
	if ( $tour_type == 'general' && $status != 'off' && !empty($language)) {
		?>
        <div class="item_icon">
            <i class="fas fa-language"></i>
            <?php echo __('Language :','tour-booking-manager') . esc_html( $language ); ?>
        </div>
	<?php
	}
