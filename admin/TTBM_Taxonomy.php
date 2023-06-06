<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Taxonomy')) {
    class TTBM_Taxonomy {
        public function __construct() {
            add_action('init', [$this, 'ttbm_taxonomy']);
            add_action('admin_init', [$this, 'ttbm_taxonomy_edit']);
        }
        public function ttbm_taxonomy() {
            $tour_label = TTBM_Function::get_name();
            $tour_cat_label = TTBM_Function::get_category_label();
            $tour_cat_slug = TTBM_Function::get_category_slug();
            $tour_org_label = TTBM_Function::get_organizer_label();
            $tour_org_slug = TTBM_Function::get_organizer_slug();
            $labels = [
                'name' => $tour_label . ' ' . $tour_cat_label,
                'singular_name' => $tour_label . ' ' . $tour_cat_label,
                'menu_name' => $tour_cat_label,
                'all_items' => esc_html__('All ', 'tour-booking-manager') . ' ' . $tour_label . ' ' . $tour_cat_label,
                'parent_item' => esc_html__('Parent ', 'tour-booking-manager') . ' ' . $tour_cat_label,
                'parent_item_colon' => esc_html__('Parent ', 'tour-booking-manager') . ' ' . $tour_cat_label,
                'new_item_name' => esc_html__('New ' . $tour_cat_label . ' Name', 'tour-booking-manager'),
                'add_new_item' => esc_html__('Add New ' . $tour_cat_label, 'tour-booking-manager'),
                'edit_item' => esc_html__('Edit ' . $tour_cat_label, 'tour-booking-manager'),
                'update_item' => esc_html__('Update ' . $tour_cat_label, 'tour-booking-manager'),
                'view_item' => esc_html__('View ' . $tour_cat_label, 'tour-booking-manager'),
                'separate_items_with_commas' => esc_html__('Separate ' . $tour_cat_label . ' with commas', 'tour-booking-manager'),
                'add_or_remove_items' => esc_html__('Add or remove ' . $tour_cat_label, 'tour-booking-manager'),
                'choose_from_most_used' => esc_html__('Choose from the most used', 'tour-booking-manager'),
                'popular_items' => esc_html__('Popular ' . $tour_cat_label, 'tour-booking-manager'),
                'search_items' => esc_html__('Search ' . $tour_cat_label, 'tour-booking-manager'),
                'not_found' => esc_html__('Not Found', 'tour-booking-manager'),
                'no_terms' => esc_html__('No ' . $tour_cat_label, 'tour-booking-manager'),
                'items_list' => esc_html__($tour_cat_label . ' list', 'tour-booking-manager'),
                'items_list_navigation' => esc_html__($tour_cat_label . ' list navigation', 'tour-booking-manager'),
            ];
            $args = [
                'hierarchical' => true,
                "public" => true,
                'labels' => $labels,
                'show_ui' => true,
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
                'all_items' => __('All ' . $tour_label . ' ' . $tour_org_label, 'tour-booking-manager'),
                'parent_item' => __('Parent ' . $tour_org_label, 'tour-booking-manager'),
                'parent_item_colon' => __('Parent ' . $tour_org_label . ':', 'tour-booking-manager'),
                'new_item_name' => __('New ' . $tour_org_label . ' Name', 'tour-booking-manager'),
                'add_new_item' => __('Add New ' . $tour_org_label, 'tour-booking-manager'),
                'edit_item' => __('Edit ' . $tour_org_label, 'tour-booking-manager'),
                'update_item' => __('Update ' . $tour_org_label, 'tour-booking-manager'),
                'view_item' => __('View ' . $tour_org_label, 'tour-booking-manager'),
                'separate_items_with_commas' => __('Separate ' . $tour_org_label . ' with commas', 'tour-booking-manager'),
                'add_or_remove_items' => __('Add or remove ' . $tour_org_label, 'tour-booking-manager'),
                'choose_from_most_used' => __('Choose from the most used', 'tour-booking-manager'),
                'popular_items' => __('Popular ' . $tour_org_label, 'tour-booking-manager'),
                'search_items' => __('Search ' . $tour_org_label, 'tour-booking-manager'),
                'not_found' => __('Not Found', 'tour-booking-manager'),
                'no_terms' => __('No ' . $tour_org_label, 'tour-booking-manager'),
                'items_list' => __($tour_org_label . ' list', 'tour-booking-manager'),
                'items_list_navigation' => __($tour_org_label . ' list navigation', 'tour-booking-manager'),
            ];
            $args_tour_org = [
                'hierarchical' => true,
                "public" => true,
                'labels' => $labels_tour_org,
                'show_ui' => true,
                'show_admin_column' => true,
                'update_count_callback' => '_update_post_term_count',
                'query_var' => true,
                'rewrite' => ['slug' => $tour_org_slug],
                'show_in_rest' => true,
                'rest_base' => 'ttbm_org',
            ];
            register_taxonomy('ttbm_tour_org', 'ttbm_tour', $args_tour_org);
            $labels_location = [
                'name' => _x('Location', 'tour-booking-manager'),
                'singular_name' => _x('Location', 'tour-booking-manager'),
                'menu_name' => __('Location', 'tour-booking-manager'),
            ];
            $args_location = [
                'hierarchical' => true,
                "public" => true,
                'labels' => $labels_location,
                'show_ui' => true,
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
                'name' => _x('Features List', 'tour-booking-manager'),
                'singular_name' => _x('Features List', 'tour-booking-manager'),
                'menu_name' => __('Features List', 'tour-booking-manager'),
            ];
            $args_feature = [
                'hierarchical' => true,
                "public" => true,
                'labels' => $labels_feature,
                'show_ui' => true,
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
                'name' => _x('Tags', 'tour-booking-manager'),
                'singular_name' => _x('Tags', 'tour-booking-manager'),
                'search_items' => __('Search Tags'),
                'all_items' => __('All Tags'),
                'parent_item' => __('Parent Tag'),
                'parent_item_colon' => __('Parent Tag:'),
                'edit_item' => __('Edit Tag'),
                'update_item' => __('Update Tag'),
                'add_new_item' => __('Add New Tag'),
                'new_item_name' => __('New Tag Name'),
                'menu_name' => __('Tags'),
            ];
            register_taxonomy('ttbm_tour_tag', ['ttbm_tour'], [
                'hierarchical' => false,
                'labels' => $labels_tags,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => ['slug' => 'ttbm_tour_tag'],
            ]);
            $labels = [
                'name' => esc_html__('Activities Type', 'tour-booking-manager'),
                'singular_name' => esc_html__('Activities Type', 'tour-booking-manager'),
                'search_items' => __('Search Activities Type'),
                'all_items' => __('All Activities Type'),
                'parent_item' => __('Parent Activities Type'),
                'parent_item_colon' => __('Parent Activities Type:'),
                'edit_item' => __('Edit Activities Type'),
                'update_item' => __('Update Activities Type'),
                'add_new_item' => __('Add New Activities Type'),
                'new_item_name' => __('New Activities Type Name'),
                'menu_name' => esc_html__('Activities Type', 'tour-booking-manager'),
            ];
            register_taxonomy('ttbm_tour_activities', ['ttbm_tour'], [
                'hierarchical' => true,
                "public" => true,
                'labels' => $labels,
                'show_ui' => true,
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
        public function ttbm_taxonomy_edit() {
            $feature_icon = [
                [
                    'id' => 'ttbm_feature_icon',
                    'title' => esc_html__('Feature Icon', 'tour-booking-manager'),
                    'details' => esc_html__('Please select a suitable icon for this feature', 'tour-booking-manager'),
                    'type' => 'mp_icon',
                    'default' => 'fas fa-forward',
                ],
            ];
            $args = [
                'taxonomy' => 'ttbm_tour_features_list',
                'options' => $feature_icon,
            ];
            new TaxonomyEdit($args);
            $activities_icon = [
                [
                    'id' => 'ttbm_activities_icon',
                    'title' => esc_html__('Activities Icon', 'tour-booking-manager'),
                    'details' => esc_html__('Please select a suitable icon for this Activities', 'tour-booking-manager'),
                    'type' => 'mp_icon',
                    'default' => 'far fa-check-circle',
                ],
            ];
            $args_activities = [
                'taxonomy' => 'ttbm_tour_activities',
                'options' => $activities_icon,
            ];
            new TaxonomyEdit($args_activities);
            $full_address = [
                [
                    'id' => 'ttbm_location_address',
                    'title' => esc_html__('Full Address ', 'tour-booking-manager'),
                    'details' => esc_html__('Please Type Location Full Address', 'tour-booking-manager'),
                    'type' => 'textarea',
                    'default' => '',
                ],
            ];
            $full_address_args = [
                'taxonomy' => 'ttbm_tour_location',
                'options' => $full_address,
            ];
            new TaxonomyEdit($full_address_args);
            $country_location = [
                [
                    'id' => 'ttbm_country_location',
                    'title' => esc_html__('Country ', 'tour-booking-manager'),
                    'details' => esc_html__('Please Select Location Country', 'tour-booking-manager'),
                    'args' => ttbm_get_coutnry_arr(),
                    'type' => 'select',
                ],
            ];
            $country_location_args = [
                'taxonomy' => 'ttbm_tour_location',
                'options' => $country_location,
            ];
            new TaxonomyEdit($country_location_args);
            $location_image = [
                [
                    'id' => 'ttbm_location_image',
                    'title' => esc_html__('Location Image ', 'tour-booking-manager'),
                    'details' => esc_html__('Please select Location Image.', 'tour-booking-manager'),
                    'placeholder' => 'https://i.imgur.com/GD3zKtz.png',
                    'type' => 'media',
                ],
            ];
            $ttbm_location_args = [
                'taxonomy' => 'ttbm_tour_location',
                'options' => $location_image,
            ];
            new TaxonomyEdit($ttbm_location_args);
        }
    }
    new TTBM_Taxonomy();
}