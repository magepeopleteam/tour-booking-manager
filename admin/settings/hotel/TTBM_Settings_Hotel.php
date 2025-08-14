<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Hotel')) {
		class TTBM_Settings_Hotel {
			public function __construct() {
				add_action('add_meta_boxes', [$this, 'hotel_settings_meta']);
				add_action('save_post', array($this, 'save_hotel'), 99, 1);
			}
			public function hotel_settings_meta() {
				$ttbm_label = TTBM_Function::get_name();
				add_meta_box('ttbm_meta_box_panel', '<span class="fas fa-hotel"></span>' . $ttbm_label . esc_html__(' Hotel Settings : ', 'tour-booking-manager') . get_the_title(get_the_id()), array($this, 'hotel_settings'), 'ttbm_hotel', 'normal', 'high');
			}
			public function hotel_settings() {
				$hotel_id = get_the_id();
				?>
                <div id="ttbm_content" class="ttbm_style ttbm_settings">
                    <div class="ttbmTabs leftTabs">
                        <ul class="tabLists">
                            <li data-tabs-target="#ttbm_general_info">
                                <span class="fas fa-cog"></span><?php esc_html_e('General Info', 'tour-booking-manager'); ?>
                            </li>
                            <li data-tabs-target="#ttbm_settings_pricing">
                                <span class="fas fa-money-bill"></span><?php esc_html_e(' Pricing', 'tour-booking-manager'); ?>
                            </li>
                            <li data-tabs-target="#ttbm_settings_feature">
                                <span class="fas fa-tasks"></span><?php esc_html_e(' Features', 'tour-booking-manager'); ?>
                            </li>
                            <li data-tabs-target="#ttbm_settings_gallery">
                                <span class="fas fa-images"></span><?php esc_html_e(' Hotel Gallery', 'tour-booking-manager'); ?>
                            </li>
                        </ul>
                        <div class="tabsContent tab-content">
							<?php
								wp_nonce_field('ttbm_hotel_type_nonce', 'ttbm_hotel_type_nonce');
								do_action('add_ttbm_settings_hotel_tab_content', $hotel_id);
							?>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function save_hotel($post_id) {
				if (!isset($_POST['ttbm_hotel_type_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_hotel_type_nonce'])), 'ttbm_hotel_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
					return;
				}
				if (get_post_type($post_id) == 'ttbm_hotel') {
					$slider = isset($_POST['ttbm_display_slider_hotel']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_slider_hotel'])) ? 'on' : 'off';
					$images = isset($_POST['ttbm_gallery_images_hotel']) ? sanitize_text_field(wp_unslash($_POST['ttbm_gallery_images_hotel'])) : '';
					$all_images = explode(',', $images);
					update_post_meta($post_id, 'ttbm_display_slider_hotel', $slider);
					update_post_meta($post_id, 'ttbm_gallery_images_hotel', $all_images);
				}
				if (get_post_type($post_id) == 'ttbm_hotel') {
					$ttbm_display_location = isset($_POST['ttbm_display_hotel_location']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_hotel_location'])) ? 'on' : 'off';
					$ttbm_location_name = isset($_POST['ttbm_hotel_location']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_location'])) : '';
					update_post_meta($post_id, 'ttbm_display_hotel_location', $ttbm_display_location);
					update_post_meta($post_id, 'ttbm_hotel_location', $ttbm_location_name);
					/***************/
					$ttbm_display_distance = isset($_POST['ttbm_display_hotel_distance']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_hotel_distance'])) ? 'on' : 'off';
					$ttbm_hotel_distance_des = isset($_POST['ttbm_hotel_distance_des']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_distance_des'])) : '';
					update_post_meta($post_id, 'ttbm_display_hotel_distance', $ttbm_display_distance);
					update_post_meta($post_id, 'ttbm_hotel_distance_des', $ttbm_hotel_distance_des);
				}
				if (get_post_type($post_id) == 'ttbm_hotel') {
					$advance_option = isset($_POST['ttbm_display_advance']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_advance'])) ? 'on' : 'off';
					update_post_meta($post_id, 'ttbm_display_advance', $advance_option);
					/************************/
					$old_ticket_type = TTBM_Global_Function::get_post_info($post_id, 'ttbm_room_details', array());
					$new_ticket_type = array();
					$icon = isset($_POST['room_type_icon']) ? array_map('sanitize_text_field', wp_unslash($_POST['room_type_icon'])) : [];
					$names = isset($_POST['ttbm_hotel_room_name']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_hotel_room_name'])) : [];
					$ticket_price = isset($_POST['ttbm_hotel_room_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_hotel_room_price'])) : [];
					$sale_price = isset($_POST['sale_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['sale_price'])) : [];
					$qty = isset($_POST['ttbm_hotel_room_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_hotel_room_qty'])) : [];
					$adult_qty = isset($_POST['ttbm_hotel_room_capacity_adult']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_hotel_room_capacity_adult'])) : [];
					$child_qty = isset($_POST['ttbm_hotel_room_capacity_child']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_hotel_room_capacity_child'])) : [];
					$rsv = isset($_POST['room_reserve_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['room_reserve_qty'])) : [];
					$qty_type = isset($_POST['room_qty_type']) ? array_map('sanitize_text_field', wp_unslash($_POST['room_qty_type'])) : [];
					$description = isset($_POST['room_description']) ? array_map('sanitize_text_field', wp_unslash($_POST['room_description'])) : [];
					$count = count($names);
					for ($i = 0; $i < $count; $i++) {
						if ($names[$i] && $ticket_price[$i] >= 0 && $qty[$i] > 0) {
							$new_ticket_type[$i]['room_type_icon'] = $icon[$i] ?? '';
							$new_ticket_type[$i]['ttbm_hotel_room_name'] = $names[$i];
							$new_ticket_type[$i]['ttbm_hotel_room_price'] = $ticket_price[$i];
							$new_ticket_type[$i]['sale_price'] = $sale_price[$i];
							$new_ticket_type[$i]['ttbm_hotel_room_qty'] = $qty[$i];
							$new_ticket_type[$i]['ttbm_hotel_room_capacity_adult'] = $adult_qty[$i] ?? 0;
							$new_ticket_type[$i]['ttbm_hotel_room_capacity_child'] = $child_qty[$i] ?? 0;
							$new_ticket_type[$i]['room_reserve_qty'] = $rsv[$i] ?? 0;
							$new_ticket_type[$i]['room_qty_type'] = $qty_type[$i] ?? 'inputbox';
							$new_ticket_type[$i]['room_description'] = $description[$i] ?? '';
						}
					}
					$ticket_type_list = apply_filters('ttbm_hotel_type_arr_save', $new_ticket_type);
					if (!empty($ticket_type_list) && $ticket_type_list != $old_ticket_type) {
						update_post_meta($post_id, 'ttbm_room_details', $ticket_type_list);
					} elseif (empty($ticket_type_list) && $old_ticket_type) {
						delete_post_meta($post_id, 'ttbm_room_details', $old_ticket_type);
					}
				}
				if (get_post_type($post_id) == 'ttbm_hotel') {
					$include_service = isset($_POST['ttbm_display_include_service']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_include_service'])) ? 'on' : 'off';
					$exclude_service = isset($_POST['ttbm_display_exclude_service']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_exclude_service'])) ? 'on' : 'off';
					update_post_meta($post_id, 'ttbm_display_include_service', $include_service);
					update_post_meta($post_id, 'ttbm_display_exclude_service', $exclude_service);
					$include = isset($_POST['ttbm_service_included_in_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_service_included_in_price'])) : [];
					$new_include = TTBM_Function::feature_id_to_array($include);
					update_post_meta($post_id, 'ttbm_service_included_in_price', $new_include);
					$exclude = isset($_POST['ttbm_service_excluded_in_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_service_excluded_in_price'])) : [];
					$new_exclude = TTBM_Function::feature_id_to_array($exclude);
					update_post_meta($post_id, 'ttbm_service_excluded_in_price', $new_exclude);
				}
			}
		}
		new TTBM_Settings_Hotel();
	}