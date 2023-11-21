<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$location_filter = $_GET['location_filter'] ?? '';
	if($location_filter){
		?>
		<input type="hidden" name="location_filter" value="<?php echo esc_attr($location_filter);?>" />
		<?php
	}
	$type_filter = $_GET['type_filter'] ?? '';
	if($type_filter){
		?>
		<input type="hidden" name="type_filter" value="<?php echo esc_attr($type_filter);?>" />
		<?php
	}
	$category_filter = $_GET['category_filter'] ?? '';
	if($category_filter){
		?>
		<input type="hidden" name="category_filter" value="<?php echo esc_attr($category_filter);?>" />
		<?php
	}
	$organizer_filter = $_GET['organizer_filter'] ?? '';
	if($organizer_filter){
		?>
		<input type="hidden" name="organizer_filter" value="<?php echo esc_attr($organizer_filter);?>" />
		<?php
	}
	$country_filter = $_GET['country_filter'] ?? '';
	if($country_filter){
		?>
		<input type="hidden" name="country_filter" value="<?php echo esc_attr($country_filter);?>" />
		<?php
	}
	$duration_filter = $_GET['duration_filter'] ?? '';
	if($duration_filter){
		?>
		<input type="hidden" name="duration_filter" value="<?php echo esc_attr($duration_filter);?>" />
		<?php
	}
	$activity_filter = $_GET['activity_filter'] ?? '';
	if($activity_filter){
		?>
		<input type="hidden" name="activity_filter" value="<?php echo esc_attr($activity_filter);?>" />
		<?php
	}
	$month_filter = $_GET['month_filter'] ?? '';
	if($month_filter){
		?>
		<input type="hidden" name="month_filter" value="<?php echo esc_attr($month_filter);?>" />
		<?php
	}