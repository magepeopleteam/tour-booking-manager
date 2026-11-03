<?php
	if ( ! defined( 'ABSPATH' ) )die;

	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_type = TTBM_Function::get_tour_type( $ttbm_post_id );
	$language_labels = TTBM_Function::get_language_labels( $ttbm_post_id );
	$language_label_text = implode(', ', $language_labels);

	if ( $tour_type == 'general' && !empty($language_labels)) {
		?>
       <div class="item_icon<?php echo esc_attr( TTBM_Function::hero_stat_item_class() ); ?>" title="<?php echo esc_attr( __('Language', 'tour-booking-manager') ); ?>">
            <i class="mi mi-language"></i>
            <span><?php echo esc_html( $language_label_text ); ?></span>
        </div>
	<?php
	}
