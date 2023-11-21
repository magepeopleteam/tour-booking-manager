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
	$status=$_GET['location_status'] ?? '' ;
	$loop   = TTBM_Query::ttbm_query( - 1, 'ASC', 0, 0,'','',$status );
	$params = array(
		'column'           => 4,
		'show'             => 10,
		'search-filter'    => '',
		"pagination-style" => "load_more",
		"pagination"       => "yes",
		"style"            => "modern",
	);
?>
	<div class="mpStyle ttbm_wraper placeholderLoader ttbm_item_filter_area">
		<div class="left_filter">
			<div class="leftSidebar">
				<?php do_action( 'ttbm_left_filter', $loop, $params ); ?>
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