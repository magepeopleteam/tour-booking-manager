<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( wp_is_block_theme() ) 
	{  
?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php
		$block_content = do_blocks( '
			<!-- wp:group {"layout":{"type":"constrained"}} -->
			<div class="wp-block-group">
			<!-- wp:post-content /-->
			</div>
			<!-- /wp:group -->'
			);
		wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div class="wp-site-blocks">
	<header class="wp-block-template-part site-header">
		<?php block_header_area(); ?>
	</header>
	</div>
	<?php
	}
	else 
	{
		get_header();	
		the_post();
	}
		
	do_action( 'ttbm_single_page_before_wrapper' );
	if ( post_password_required() ) {
		echo get_the_password_form(); // WPCS: XSS ok.
	} else {
		do_action( 'woocommerce_before_single_product' );
        $ttbm_post_id=get_the_id();
        $tour_id=TTBM_Function::post_id_multi_language($ttbm_post_id);
		$template_name = MP_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
		$all_dates                 = TTBM_Function::get_date( $tour_id );
		$travel_type               = TTBM_Function::get_travel_type( $tour_id );
		$tour_type                 = TTBM_Function::get_tour_type( $tour_id );
		$ttbm_display_registration = MP_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' );
		$start_price = TTBM_Function::get_tour_start_price( $tour_id );
		$total_seat     = TTBM_Function::get_total_seat( $tour_id );
		$available_seat = TTBM_Function::get_total_available( $tour_id );
		TTBM_Function::update_upcoming_date_month($tour_id,true,$all_dates);
		include_once( TTBM_Function::details_template_path() );
	}
	do_action( 'ttbm_single_page_after_wrapper' );
	if ( wp_is_block_theme() ) 
	{
		// Code for block themes goes here.
		?>
		<footer class="wp-block-template-part">
			<?php block_footer_area(); ?>
		</footer>
		<?php wp_footer(); ?>
		</body>    
		<?php
	} 
	else 
	{
		get_footer();
	}