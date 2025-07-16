<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Taxonomy')) {
		class TTBM_Taxonomy {
			public function __construct() {
				add_action('init', [$this, 'ttbm_taxonomy']);

			}
			public function ttbm_taxonomy() {
				$tour_label = TTBM_Function::get_name();
				$tour_cat_label = TTBM_Function::get_category_label();
				$tour_cat_slug = TTBM_Function::get_category_slug();
				$tour_org_label = TTBM_Function::get_organizer_label();
				$tour_org_slug = TTBM_Function::get_organizer_slug();
				$labels = [
					// translators: %1$s is the tour label, %2$s is the tour category label
					'name' => sprintf(__('%1$s %2$s', 'tour-booking-manager'), $tour_label, $tour_cat_label),
					// translators: %1$s is the tour label, %2$s is the tour category label
					'singular_name' => sprintf(__('%1$s %2$s', 'tour-booking-manager'), $tour_label, $tour_cat_label),
					'menu_name' => $tour_cat_label,
// translators: %1$s is the tour label, %2$s is the tour category label
					'all_items' => sprintf(__('All %1$s %2$s', 'tour-booking-manager'), $tour_label, $tour_cat_label),
					// translators: %s is the tour category label
					'parent_item' => sprintf(__('Parent %1$s', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'parent_item_colon' => sprintf(__('Parent %1$s:', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'new_item_name' => sprintf(__('New %1$s Name', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'add_new_item' => sprintf(__('Add New %1$s', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'edit_item' => sprintf(__('Edit %1$s', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'update_item' => sprintf(__('Update %1$s', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'view_item' => sprintf(__('View %1$s', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'separate_items_with_commas' => sprintf(__('Separate %1$s with commas', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'add_or_remove_items' => sprintf(__('Add or remove %1$s', 'tour-booking-manager'), $tour_cat_label),
					'choose_from_most_used' => __('Choose from the most used', 'tour-booking-manager'),
					// translators: %s is the tour category label
					'popular_items' => sprintf(__('Popular %1$s', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'search_items' => sprintf(__('Search %1$s', 'tour-booking-manager'), $tour_cat_label),
					'not_found' => __('Not Found', 'tour-booking-manager'),
					// translators: %s is the tour category label
					'no_terms' => sprintf(__('No %1$s', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'items_list' => sprintf(__('%1$s list', 'tour-booking-manager'), $tour_cat_label),
					// translators: %s is the tour category label
					'items_list_navigation' => sprintf(__('%1$s list navigation', 'tour-booking-manager'), $tour_cat_label),
				];
				$args = [
					'hierarchical' => true,
					"public" => true,
					'labels' => $labels,
					'show_ui' => true,
					'show_in_menu' => true, // THIS hides it from the admin menu
					'show_admin_column' => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var' => true,
					'rewrite' => ['slug' => $tour_cat_slug],
					'show_in_rest' => true,
					'rest_base' => 'ttbm_tour_cat'
				];
				register_taxonomy('ttbm_tour_cat', 'ttbm_tour', $args);
				$labels_tour_org = [
					'name' => $tour_org_label,
					'singular_name' => $tour_org_label,
					'menu_name' => $tour_org_label,
					// translators: %1$s is the tour label, %2$s is the tour organizer label
					'all_items' => sprintf(__('All %1$s %2$s', 'tour-booking-manager'), $tour_label, $tour_org_label),
					// translators: %1$s is the tour organizer label
					'parent_item' => sprintf(__('Parent %1$s', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'parent_item_colon' => sprintf(__('Parent %1$s:', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'new_item_name' => sprintf(__('New %1$s Name', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'add_new_item' => sprintf(__('Add New %1$s', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'edit_item' => sprintf(__('Edit %1$s', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'update_item' => sprintf(__('Update %1$s', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'view_item' => sprintf(__('View %1$s', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'separate_items_with_commas' => sprintf(__('Separate %1$s with commas', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'add_or_remove_items' => sprintf(__('Add or remove %1$s', 'tour-booking-manager'), $tour_org_label),
					'choose_from_most_used' => __('Choose from the most used', 'tour-booking-manager'),
					// translators: %1$s is the tour organizer label
					'popular_items' => sprintf(__('Popular %1$s', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'search_items' => sprintf(__('Search %1$s', 'tour-booking-manager'), $tour_org_label),
					'not_found' => __('Not Found', 'tour-booking-manager'),
					// translators: %1$s is the tour organizer label
					'no_terms' => sprintf(__('No %1$s', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'items_list' => sprintf(__('%1$s list', 'tour-booking-manager'), $tour_org_label),
					// translators: %1$s is the tour organizer label
					'items_list_navigation' => sprintf(__('%1$s list navigation', 'tour-booking-manager'), $tour_org_label),
				];
				$args_tour_org = [
					'hierarchical' => true,
					'public' => true,
					'labels' => $labels_tour_org,
					'show_ui' => true,
					'show_admin_column' => true,
					'show_in_menu' => false, // ✅ move it here
					'update_count_callback' => '_update_post_term_count',
					'query_var' => true,
					'rewrite' => ['slug' => $tour_org_slug],
					'show_in_rest' => true,
					'rest_base' => 'ttbm_org',
				];
				register_taxonomy('ttbm_tour_org', 'ttbm_tour', $args_tour_org);
				$labels_location = [
					'name' => __('Location', 'tour-booking-manager'),
					'singular_name' => __('Location', 'tour-booking-manager'),
					'menu_name' => __('Location', 'tour-booking-manager'),
				];
				$args_location = [
					'hierarchical' => true,
					"public" => true,
					'labels' => $labels_location,
					'show_ui' => true,
					'show_in_menu' => false, // THIS hides it from the admin menu
					'show_admin_column' => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var' => true,
					'rewrite' => ['slug' => 'location'],
					'show_in_rest' => true,
					'meta_box_cb' => false,
					'rest_base' => 'location',
				];
				register_taxonomy('ttbm_tour_location', 'ttbm_tour', $args_location);
				$labels_feature = [
					'name' => __('Features List', 'tour-booking-manager'),
					'singular_name' => __('Features List', 'tour-booking-manager'),
					'menu_name' => __('Features List', 'tour-booking-manager'),
				];
				$args_feature = [
					'hierarchical' => true,
					"public" => true,
					'labels' => $labels_feature,
					'show_ui' => true,
					'show_in_menu' => false, // THIS hides it from the admin menu
					'show_admin_column' => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var' => true,
					'rewrite' => ['slug' => 'features-list'],
					'show_in_rest' => true,
					'meta_box_cb' => false,
					'rest_base' => 'features_list',
				];
				register_taxonomy('ttbm_tour_features_list', 'ttbm_tour', $args_feature);
				$labels_tags = [
					'name' => __('Tags', 'tour-booking-manager'),
					'singular_name' => __('Tags', 'tour-booking-manager'),
					'search_items' => __('Search Tags', 'tour-booking-manager'),
					'all_items' => __('All Tags', 'tour-booking-manager'),
					'parent_item' => __('Parent Tag', 'tour-booking-manager'),
					'parent_item_colon' => __('Parent Tag:', 'tour-booking-manager'),
					'edit_item' => __('Edit Tag', 'tour-booking-manager'),
					'update_item' => __('Update Tag', 'tour-booking-manager'),
					'add_new_item' => __('Add New Tag', 'tour-booking-manager'),
					'new_item_name' => __('New Tag Name', 'tour-booking-manager'),
					'menu_name' => __('Tags', 'tour-booking-manager'),
				];
				register_taxonomy('ttbm_tour_tag', ['ttbm_tour'], [
					'hierarchical' => false,
					'labels' => $labels_tags,
					'show_ui' => true,
					'show_in_menu' => false, // THIS hides it from the admin menu
					'show_in_rest' => true,
					'show_admin_column' => true,
					'query_var' => true,
					'rewrite' => ['slug' => 'ttbm_tour_tag'],
				]);
				$labels = [
					'name' => esc_html__('Activities Type', 'tour-booking-manager'),
					'singular_name' => esc_html__('Activities Type', 'tour-booking-manager'),
					'search_items' => __('Search Activities Type', 'tour-booking-manager'),
					'all_items' => __('All Activities Type', 'tour-booking-manager'),
					'parent_item' => __('Parent Activities Type', 'tour-booking-manager'),
					'parent_item_colon' => __('Parent Activities Type:', 'tour-booking-manager'),
					'edit_item' => __('Edit Activities Type', 'tour-booking-manager'),
					'update_item' => __('Update Activities Type', 'tour-booking-manager'),
					'add_new_item' => __('Add New Activities Type', 'tour-booking-manager'),
					'new_item_name' => __('New Activities Type Name', 'tour-booking-manager'),
					'menu_name' => __('Activities Type', 'tour-booking-manager'),
				];
				register_taxonomy('ttbm_tour_activities', ['ttbm_tour'], [
					'hierarchical' => true,
					"public" => true,
					'labels' => $labels,
					'show_ui' => true,
					'show_in_menu' => false, // THIS hides it from the admin menu
					'show_admin_column' => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var' => true,
					'rewrite' => ['slug' => 'ttbm_tour_activities'],
					'show_in_rest' => true,
					'rest_base' => 'ttbm_tour_activities',
					'meta_box_cb' => false,
				]);
				new TTBM_Dummy_Import();
				flush_rewrite_rules();
			}
		}
		new TTBM_Taxonomy();
	}