<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$tour_id       = $tour_id ?? get_the_id();
	$title_style   = MP_Global_Function::get_post_info($tour_id,'ttbm_section_title_style','ttbm_title_style_2');
	$title_class   = $title_style == 'style_1' ? 'ttbm_widget_title' : $title_style;
	$option_name   = $option_name ?? '';
	$default_title = $default_title ?? '';
	if ( $tour_id && $option_name ) {
		?>
		<h4 class="<?php echo esc_attr( $title_class ); ?>" data-placeholder>
			<?php TTBM_Function::translation_settings( $option_name, $default_title ); ?>
		</h4>
	<?php } ?>