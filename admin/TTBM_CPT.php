<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_CPT')) {
		class TTBM_CPT {
			public function __construct() {
				add_action('init', [$this, 'ttbm_cpt']);
				add_filter('manage_ttbm_tour_posts_columns', [$this, 'set_custom_columns']);
				add_action('manage_ttbm_tour_posts_custom_column', [$this, 'custom_column_data'], 10, 2);
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
				$args = array(
					'public' => true,
					'label' => esc_html__( 'Ticket Types', 'tour-booking-manager' ),
					'supports' => array( 'title' ),
					'show_in_menu' => 'edit.php?post_type=ttbm_tour',
					'capability_type' => 'post',
				);
				register_post_type( 'ttbm_ticket_types', $args );
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
				$ttbm_travel_type = MP_Global_Function::get_post_info($post_id, 'ttbm_travel_type');
				switch ($column) {
					case 'ttbm_location' :
						echo TTBM_Function::get_full_location($post_id);
						break;
					case 'ttbm_status' :
						echo 'status';
						break;
					case 'ttbm_start_date' :
						$upcoming_date = MP_Global_Function::get_post_info($post_id, 'ttbm_upcoming_date');
						if ($upcoming_date) {
							?>
                            <span class="textSuccess"><?php echo esc_html(MP_Global_Function::date_format($upcoming_date)); ?></span>
							<?php
						} else {
							?>
                            <span class="textWarning"><?php esc_html_e('Expired !', 'tour-booking-manager'); ?></span>
							<?php
						}
						break;
					case 'ttbm_end_date' :
						if ($ttbm_travel_type == 'fixed') {
							echo MP_Global_Function::date_format(TTBM_Function::get_reg_end_date($post_id));
						}
						break;
				}
			}
		}
		new TTBM_CPT();
	}