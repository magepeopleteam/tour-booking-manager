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
	}
	$term_id = get_queried_object()->name;
	$status=$_GET['location_status'] ?? '' ;
	$shortcode = "[travel-list city='" . $term_id . "' style='modern' show='10'  pagination='yes' sidebar-filter='yes'";
	$status?$shortcode .= " status='" . $status . "'":'';
	$shortcode .= "]";
	do_action( 'ttbm_single_location_page_before_wrapper' );
	echo do_shortcode( $shortcode );
	do_action( 'ttbm_single_location_page_after_wrapper' );
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
