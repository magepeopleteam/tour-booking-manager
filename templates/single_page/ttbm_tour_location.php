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
	do_action( 'ttbm_single_location_page_before_wrapper' );
	//$status=$_GET['location_status'] ?? '' ;
	$loop   = TTBM_Query::ttbm_query( - 1, 'ASC', 0, 0,'','' );
	/* left_filter() and every sub-filter it calls read this array by key
	   ('activity-filter', 'price-filter', 'month-filter', …). The literal below
	   defined six of them, so the sidebar silently skipped every filter block
	   whose key was absent. Building on the same defaults the shortcodes use
	   keeps this page's sidebar identical to theirs and stops it drifting when
	   a new filter key is added. */
	$params = array(
		'column'           => 4,
		'show'             => 10,
		'search-filter'    => '',
		"pagination-style" => "load_more",
		"pagination"       => "yes",
		"style"            => "modern",
	);
	if ( method_exists( 'TTBM_Shortcode', 'default_attribute' ) ) {
		$params = wp_parse_args(
			$params,
			TTBM_Shortcode::default_attribute( 'modern', 10, 'no', 'yes', 'yes', 'yes' )
		);
	}
?>
	<?php /* ttbm_item_filter_area is this template's own name and appears nowhere else —
	     no stylesheet and no script has ever matched it. filter_pagination.js scopes every
	     handler with .closest('.ttbm_filter_area'), so the left filter on this page could
	     not filter anything. Adding the real hook class turns the filters on and brings the
	     page under the shared responsive layout; the original class stays so any site CSS
	     written against it keeps working. */ ?>
	<div class="ttbm_style ttbm-tour-list-shortcode ttbm_wraper placeholderLoader ttbm_filter_area ttbm_item_filter_area">
		<div class="left_filter">
			<div class="leftSidebar">
				<?php /* ttbm_left_filter is registered with one accepted arg, so passing $loop first
				     handed left_filter() a WP_Query where it expects the params array — fatal on
				     PHP 8 the moment a sub-filter does $params['activity-filter']. */ ?>
				<?php do_action( 'ttbm_left_filter', $params ); ?>
			</div>
			<div class="mainSection">
				<?php do_action( 'ttbm_all_list_item', $loop, $params ); ?>
				<?php do_action( 'ttbm_sort_result', $loop, $params ); ?>
				<?php do_action( 'ttbm_pagination', $params, $loop->post_count ); ?>
			</div>
		</div>
	</div>
<?php
	wp_reset_postdata();
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
?>