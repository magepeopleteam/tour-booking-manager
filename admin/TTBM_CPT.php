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
// translators: %1$s is the tour label
					'archives' => sprintf( esc_html__( '%1$s List', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'attributes' => sprintf( esc_html__( '%1$s List', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'parent_item_colon' => sprintf( esc_html__( '%1$s Item:', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'all_items' => sprintf( esc_html__( 'All %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'add_new_item' => sprintf( esc_html__( 'Add New %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'add_new' => sprintf( esc_html__( 'Add New %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'new_item' => sprintf( esc_html__( 'New %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'edit_item' => sprintf( esc_html__( 'Edit %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'update_item' => sprintf( esc_html__( 'Update %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'view_item' => sprintf( esc_html__( 'View %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'view_items' => sprintf( esc_html__( 'View %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'search_items' => sprintf( esc_html__( 'Search %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'not_found' => sprintf( esc_html__( '%1$s not found', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'not_found_in_trash' => sprintf( esc_html__( '%1$s not found in Trash', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'featured_image' => sprintf( esc_html__( '%1$s Feature Image', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'set_featured_image' => sprintf( esc_html__( 'Set %1$s featured image', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'remove_featured_image' => sprintf( esc_html__( 'Remove %1$s featured image', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'use_featured_image' => sprintf( esc_html__( 'Use as %1$s featured image', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'insert_into_item' => sprintf( esc_html__( 'Insert into %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'uploaded_to_this_item' => sprintf( esc_html__( 'Uploaded to this %1$s', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'items_list' => sprintf( esc_html__( '%1$s list', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'items_list_navigation' => sprintf( esc_html__( '%1$s list navigation', 'tour-booking-manager' ), $tour_label ),
					// translators: %1$s is the tour label
					'filter_items_list' => sprintf( esc_html__( 'Filter %1$s list', 'tour-booking-manager' ), $tour_label ),
				];
				$args = [
					'public' => true,
					'labels' => $labels,
					'menu_icon' => $tour_icon,
					'supports' => ['title', 'thumbnail', 'editor', 'excerpt'],
					'rewrite' => ['slug' => $tour_slug],
					'show_in_rest' => true,
					'capability_type' => 'post',
					'has_archive' => true,
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
					// 'show_in_menu' => 'edit.php?post_type=ttbm_tour',
					'show_in_menu' => false,
					'capability_type' => 'post',
				];
				register_post_type('ttbm_hotel', $args);
				
				$args = [
					'public' => true,
					'label' => esc_html__('Places', 'tour-booking-manager'),
					'supports' => ['title', 'thumbnail', 'editor'],
					// 'show_in_menu' => 'edit.php?post_type=ttbm_tour',
					'show_in_menu' => false,
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

                register_post_type('ttbm_hotel_booking', array(
                    'labels' => array(
'name' => __('Hotel Bookings', 'tour-booking-manager'),
                        'singular_name' => __('Hotel Booking', 'tour-booking-manager'),
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'show_ui' => false,
                    'show_in_menu' => true, // Ensure it's visible in the admin menu
                    'supports' => array('title', 'editor', 'custom-fields'),
                ));

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
				$ttbm_travel_type = TTBM_Global_Function::get_post_info($post_id, 'ttbm_travel_type');
				switch ($column) {
					case 'ttbm_location' :
						echo esc_html(TTBM_Function::get_full_location($post_id));
						break;
					case 'ttbm_status' :
						echo 'status';
						break;
					case 'ttbm_start_date' :
						$upcoming_date = TTBM_Global_Function::get_post_info($post_id, 'ttbm_upcoming_date');
						if ($upcoming_date) {
							?>
                            <span class="textSuccess"><?php echo esc_html(TTBM_Global_Function::date_format($upcoming_date)); ?></span>
							<?php
						} else {
							?>
                            <span class="textWarning"><?php esc_html_e('Expired !', 'tour-booking-manager'); ?></span>
							<?php
						}
						break;
					case 'ttbm_end_date' :
						if ($ttbm_travel_type == 'fixed') {
							echo esc_html(TTBM_Global_Function::date_format(TTBM_Function::get_reg_end_date($post_id)));
						}
						break;
				}
			}
		}
		new TTBM_CPT();
	}