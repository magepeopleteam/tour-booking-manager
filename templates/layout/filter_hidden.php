<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (isset($_GET['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
		$title_filter = isset($_GET['title_filter']) ? sanitize_text_field(wp_unslash($_GET['title_filter'])) : '';
		$location_filter = isset($_GET['location_filter']) ? sanitize_text_field(wp_unslash($_GET['location_filter'])) : '';
		$type_filter = isset($_GET['type_filter']) ? sanitize_text_field(wp_unslash($_GET['type_filter'])) : '';
		$category_filter = isset($_GET['category_filter']) ? sanitize_text_field(wp_unslash($_GET['category_filter'])) : '';
		$organizer_filter = isset($_GET['organizer_filter']) ? sanitize_text_field(wp_unslash($_GET['organizer_filter'])) : '';
		$country_filter = isset($_GET['country_filter']) ? sanitize_text_field(wp_unslash($_GET['country_filter'])) : '';
		$duration_filter = isset($_GET['duration_filter']) ? sanitize_text_field(wp_unslash($_GET['duration_filter'])) : '';
		$activity_filter = isset($_GET['activity_filter']) ? sanitize_text_field(wp_unslash($_GET['activity_filter'])) : '';
		$month_filter = isset($_GET['month_filter']) ? sanitize_text_field(wp_unslash($_GET['month_filter'])) : '';
		if ($title_filter) {
			?>
            <input type="hidden" name="title_filter" value="<?php echo esc_attr($title_filter); ?>"/>
			<?php
		}
		if ($location_filter) {
			?>
            <input type="hidden" name="location_filter" value="<?php echo esc_attr($location_filter); ?>"/>
			<?php
		}
		if ($type_filter) {
			?>
            <input type="hidden" name="type_filter" value="<?php echo esc_attr($type_filter); ?>"/>
			<?php
		}
		if ($category_filter) {
			?>
            <input type="hidden" name="category_filter" value="<?php echo esc_attr($category_filter); ?>"/>
			<?php
		}
		if ($organizer_filter) {
			?>
            <input type="hidden" name="organizer_filter" value="<?php echo esc_attr($organizer_filter); ?>"/>
			<?php
		}
		if ($country_filter) {
			?>
            <input type="hidden" name="country_filter" value="<?php echo esc_attr($country_filter); ?>"/>
			<?php
		}
		if ($duration_filter) {
			?>
            <input type="hidden" name="duration_filter" value="<?php echo esc_attr($duration_filter); ?>"/>
			<?php
		}
		if ($activity_filter) {
			?>
            <input type="hidden" name="activity_filter" value="<?php echo esc_attr($activity_filter); ?>"/>
			<?php
		}
		if ($month_filter) {
			?>
            <input type="hidden" name="month_filter" value="<?php echo esc_attr($month_filter); ?>"/>
			<?php
		}
	}