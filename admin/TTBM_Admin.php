<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('TTBM_Admin')) {
        class TTBM_Admin {
            public function __construct() {
                add_action('upgrader_process_complete', [$this, 'flush_rewrite']);
                $this->load_ttbm_admin();
                add_action('init', [$this, 'ttbm_taxonomy']);
                add_action('admin_init', [$this, 'ttbm_taxonomy_edit']);
                add_action('init', [$this, 'ttbm_cpt']);
                add_filter('use_block_editor_for_post_type', [$this, 'disable_gutenberg'], 10, 2);
                add_action('widgets_init', [$this, 'ttbm_widgets_init']);
                add_filter('manage_ttbm_tour_posts_columns', [$this, 'set_custom_columns']);
                add_action('manage_ttbm_tour_posts_custom_column', [$this, 'custom_column_data'], 10, 2);
            }
            public function set_custom_columns($columns) {
                unset($columns['date']);
                unset($columns['taxonomy-ttbm_tour_features_list']);
                unset($columns['taxonomy-ttbm_tour_tag']);
                unset($columns['taxonomy-ttbm_tour_activities']);
                unset($columns['taxonomy-ttbm_tour_location']);
                $columns['ttbm_location'] = esc_html__('Location', 'tour-booking-manager');
                $columns['ttbm_start_date'] = esc_html__('Upcoming Date', 'tour-booking-manager');
                $columns['ttbm_end_date'] = esc_html__('Reg. End Date', 'tour-booking-manager');
                return $columns;
            }
            public function custom_column_data($column, $post_id) {
                TTBM_Function::update_upcoming_date_month($post_id);
                switch ($column) {
                    case 'ttbm_location' :
                        echo TTBM_Function::get_full_location($post_id);
                        break;
                    case 'ttbm_status' :
                        echo 'status';
                        break;
                    case 'ttbm_start_date' :
                        $upcoming_date = TTBM_Function::get_post_info($post_id, 'ttbm_upcoming_date');
                        if ($upcoming_date) {
                            ?>
                            <span class="textSuccess"><?php echo esc_html(TTBM_Function::datetime_format($upcoming_date, 'date-text')); ?></span>
                            <?php
                        } else {
                            ?>
                            <span class="textWarning"><?php esc_html_e('Expired !', 'tour-booking-manager'); ?></span>
                            <?php
                        }
                        break;
                    case 'ttbm_end_date' :
                        echo TTBM_Function::datetime_format(TTBM_Function::get_reg_end_date($post_id), 'date-text');
                        break;
                }
            }
            public function flush_rewrite() {
                flush_rewrite_rules();
            }
            private function load_ttbm_admin() {
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Dummy_Import.php';
                require_once TTBM_PLUGIN_DIR . '/admin/MAGE_Setting_API.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Settings_Global.php';
                require_once TTBM_PLUGIN_DIR . '/lib/classes/class-form-fields-generator.php';
                require_once TTBM_PLUGIN_DIR . '/lib/classes/class-meta-box.php';
                require_once TTBM_PLUGIN_DIR . '/lib/classes/class-taxonomy-edit.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Hidden_Product.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Welcome.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Quick_Setup.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Status.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Settings.php';
                require_once TTBM_PLUGIN_DIR . '/admin/settings/TTBM_Setting_guide.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Settings_Hotel.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Settings_Hotel_Price.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Setting_pricing.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Setting_Feature.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Setting_place_you_see.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Setting_faq_day_wise_details.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Setting_why_book_with_us.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Setting_activity.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Save.php';
                require_once TTBM_PLUGIN_DIR . '/admin/TTBM_LIcense.php';
                require_once TTBM_PLUGIN_DIR . '/select_icon_popup/Select_Icon_Popup.php';
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
            public function ttbm_cpt() {
                $tour_label = TTBM_Function::get_name();
                $tour_slug = TTBM_Function::get_slug();
                $tour_icon = TTBM_Function::get_icon();
                $labels = [
                    'name' => $tour_label,
                    'singular_name' => $tour_label,
                    'menu_name' => $tour_label,
                    'name_admin_bar' => $tour_label,
                    'archives' => $tour_label . ' ' . esc_html__(' List', 'tour-booking-manager'),
                    'attributes' => $tour_label . ' ' . esc_html__(' List', 'tour-booking-manager'),
                    'parent_item_colon' => $tour_label . ' ' . esc_html__(' Item:', 'tour-booking-manager'),
                    'all_items' => esc_html__('All ', 'tour-booking-manager') . ' ' . $tour_label,
                    'add_new_item' => esc_html__('Add New ', 'tour-booking-manager') . ' ' . $tour_label,
                    'add_new' => esc_html__('Add New ', 'tour-booking-manager') . ' ' . $tour_label,
                    'new_item' => esc_html__('New ', 'tour-booking-manager') . ' ' . $tour_label,
                    'edit_item' => esc_html__('Edit ', 'tour-booking-manager') . ' ' . $tour_label,
                    'update_item' => esc_html__('Update ', 'tour-booking-manager') . ' ' . $tour_label,
                    'view_item' => esc_html__('View ', 'tour-booking-manager') . ' ' . $tour_label,
                    'view_items' => esc_html__('View ', 'tour-booking-manager') . ' ' . $tour_label,
                    'search_items' => esc_html__('Search ', 'tour-booking-manager') . ' ' . $tour_label,
                    'not_found' => $tour_label . ' ' . esc_html__(' Not found', 'tour-booking-manager'),
                    'not_found_in_trash' => $tour_label . ' ' . esc_html__(' Not found in Trash', 'tour-booking-manager'),
                    'featured_image' => $tour_label . ' ' . esc_html__(' Feature Image', 'tour-booking-manager'),
                    'set_featured_image' => esc_html__('Set ', 'tour-booking-manager') . ' ' . $tour_label . ' ' . esc_html__(' featured image', 'tour-booking-manager'),
                    'remove_featured_image' => esc_html__('Remove ', 'tour-booking-manager') . ' ' . $tour_label . ' ' . esc_html__(' featured image', 'tour-booking-manager'),
                    'use_featured_image' => esc_html__('Use as ' . $tour_label . ' featured image', 'tour-booking-manager') . ' ' . $tour_label . ' ' . esc_html__(' featured image', 'tour-booking-manager'),
                    'insert_into_item' => esc_html__('Insert into ', 'tour-booking-manager') . ' ' . $tour_label,
                    'uploaded_to_this_item' => esc_html__('Uploaded to this ', 'tour-booking-manager') . ' ' . $tour_label,
                    'items_list' => $tour_label . ' ' . esc_html__(' list', 'tour-booking-manager'),
                    'items_list_navigation' => $tour_label . ' ' . esc_html__(' list navigation', 'tour-booking-manager'),
                    'filter_items_list' => esc_html__('Filter ', 'tour-booking-manager') . ' ' . $tour_label . ' ' . esc_html__(' list', 'tour-booking-manager')
                ];
                $args = [
                    'public' => true,
                    'labels' => $labels,
                    'menu_icon' => $tour_icon,
                    'supports' => ['title', 'thumbnail', 'editor', 'excerpt'],
                    'rewrite' => ['slug' => $tour_slug],
                    'show_in_rest' => true
                ];
                register_post_type('ttbm_tour', $args);
                $args = [
                    'public' => true,
                    'label' => esc_html__('Hotel', 'tour-booking-manager'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => 'edit.php?post_type=ttbm_tour',
                    'capability_type' => 'post',
                ];
                register_post_type('ttbm_hotel', $args);
                $args = [
                    'public' => true,
                    'label' => esc_html__('Places', 'tour-booking-manager'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => 'edit.php?post_type=ttbm_tour',
                    'capability_type' => 'post',
                ];
                register_post_type('ttbm_places', $args);
                $args = [
                    'public' => true,
                    'label' => esc_html__('Guide Information', 'tour-booking-manager'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => 'edit.php?post_type=ttbm_tour',
                    'capability_type' => 'post',
                ];
                register_post_type('ttbm_guide', $args);
            }
            public function disable_gutenberg($current_status, $post_type) {
                $user_status = TTBM_Function::get_general_settings('ttbm_disable_block_editor', 'yes');
                if ($post_type === 'ttbm_tour' && $user_status == 'yes') {
                    return false;
                }
                return $current_status;
            }
            public function ttbm_widgets_init() {
                register_sidebar([
                    'name' => esc_html__('Tour Booking Details Page Sidebar', 'tour-booking-manager'),
                    'id' => 'ttbm_details_sidebar',
                    'description' => esc_html__('Widgets in this area will be shown on tour booking details page sidebar.', 'tour-booking-manager'),
                    'before_widget' => '<div id="%1$s" class="ttbm_default_widget ttbm_sidebar_widget %2$s">',
                    'after_widget' => '</div>',
                    'before_title' => '<h4 class="ttbm_title_style_3">',
                    'after_title' => '</h4>',
                ]);
            }
        }
        new TTBM_Admin();
    }