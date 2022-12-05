<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	get_header();
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
	get_footer();
?>