<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Admin')) {
		class TTBM_Admin {
			public function __construct() {
				add_action('upgrader_process_complete', [$this, 'flush_rewrite']);
				$this->load_ttbm_admin();
				add_filter('use_block_editor_for_post_type', [$this, 'disable_gutenberg'], 10, 2);
				add_action('widgets_init', [$this, 'ttbm_widgets_init']);
				add_action('admin_action_ttbm_duplicate', [$this, 'ttbm_duplicate']);
				add_filter('post_row_actions', [$this, 'post_duplicator'], 10, 2);
				add_filter('wp_mail_content_type', array($this, 'email_content_type'));
			}
			public function flush_rewrite() {
				update_option('rewrite_rules', '');
			}
			private function load_ttbm_admin() {
				require_once TTBM_PLUGIN_DIR . '/lib/classes/class-form-fields-generator.php';
				require_once TTBM_PLUGIN_DIR . '/lib/classes/class-meta-box.php';
				require_once TTBM_PLUGIN_DIR . '/lib/classes/class-taxonomy-edit.php';
				//**************//
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Dummy_Import.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_CPT.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Taxonomy.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Hidden_Product.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Hidden_Hotel_Product.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Hotel_Template.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Admin_Tour_List.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Travel_Tab_Data_Add_Display_Ajax.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Hotel_Booking_Lists.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Welcome.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Quick_Setup.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Status.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_License.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Get_Enquiry.php';
				//**********//
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Settings_Global.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Ticket_Types.php';
				//**********//
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_General.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Location.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Dates.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_pricing.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_extra_service.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Gallery.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Feature.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_guide.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_activity.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_place_you_see.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_itinery_builder.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_faq.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Related.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Contact.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Promotional_Text.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Admin_Note.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Display.php';
                require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Promotional_Deals.php';
				//**********//
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Hotel.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Hotel_Ajax.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Hotel_General.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Gallery_Hotel.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Hotel_Price.php';
				//**********//
			}
			public function ttbm_widgets_init() {
				register_sidebar(['name' => esc_html__('Tour Booking Details Page Sidebar', 'tour-booking-manager'), 'id' => 'ttbm_details_sidebar', 'description' => esc_html__('Widgets in this area will be shown on tour booking details page sidebar.', 'tour-booking-manager'), 'before_widget' => '<div id="%1$s" class="ttbm_default_widget ttbm_sidebar_widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="ttbm_title_style_3">', 'after_title' => '</h4>',]);
			}
			//************Disable Gutenberg************************//
			public function disable_gutenberg($current_status, $post_type) {
				$user_status = TTBM_Global_Function::get_settings('ttbm_global_settings', 'disable_block_editor', 'yes');
				if ($post_type === TTBM_Function::get_cpt_name() && $user_status == 'yes') {
					return false;
				}
				return $current_status;
			}
			//**************Post duplicator*********************//
			public function ttbm_duplicate() {
				// Check if post ID is provided
				if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'ttbm_duplicate' == $_REQUEST['action']))) {
					wp_die(esc_html__('No post to duplicate has been supplied!', 'tour-booking-manager'));
				}
				// Verify nonce
				if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['duplicate_nonce'])), basename(__FILE__))) {
					wp_die(esc_html__('Security check failed!', 'tour-booking-manager'));
				}
				$post_id = isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']);
				// Try to get post from cache first
				$post = wp_cache_get('ttbm_post_' . $post_id, 'ttbm');
				if (false === $post) {
					$post = get_post($post_id);
					if ($post) {
						wp_cache_set('ttbm_post_' . $post_id, $post, 'ttbm', 3600); // Cache for 1 hour
					}
				}
				if (!$post) {
					wp_die(esc_html__('Original post not found!', 'tour-booking-manager'));
				}
				$current_user = wp_get_current_user();
				$new_post_author = $current_user->ID;
				$args = array(
					'comment_status' => $post->comment_status,
					'ping_status' => $post->ping_status,
					'post_author' => $new_post_author,
					'post_content' => wp_slash($post->post_content),
					'post_excerpt' => $post->post_excerpt,
					'post_name' => $post->post_name,
					'post_parent' => $post->post_parent,
					'post_password' => $post->post_password,
					'post_status' => 'draft',
					'post_title' => $post->post_title . ' (' . esc_html__('Copy', 'tour-booking-manager') . ')',
					'post_type' => $post->post_type,
					'to_ping' => $post->to_ping,
					'menu_order' => $post->menu_order,
				);
				$new_post_id = wp_insert_post($args);
				if (is_wp_error($new_post_id)) {
					wp_die(
						esc_html__('Failed to create duplicate post: ', 'tour-booking-manager') . ' ' . esc_html($new_post_id->get_error_message())
					);
				}
				// Cache the new post immediately
				wp_cache_set('ttbm_post_' . $new_post_id, get_post($new_post_id), 'ttbm', 3600);
				// Copy taxonomies - uses WordPress functions which handle caching internally
				$taxonomies = get_object_taxonomies($post->post_type);
				foreach ($taxonomies as $taxonomy) {
					$post_terms = get_the_terms($post_id, $taxonomy);
					if (!empty($post_terms) && !is_wp_error($post_terms)) {
						$term_slugs = wp_list_pluck($post_terms, 'slug');
						wp_set_object_terms($new_post_id, $term_slugs, $taxonomy, false);
					}
				}
				// Copy post meta (excluding 'total_booking') - uses get_post_meta which is cached
				$post_meta = get_post_meta($post_id);
				if (!empty($post_meta)) {
					foreach ($post_meta as $meta_key => $meta_values) {
						if ($meta_key === 'total_booking' || $meta_key === '_wp_old_slug') {
							continue;
						}
						foreach ($meta_values as $meta_value) {
							// Unserialize data to ensure proper storage
							$meta_value = maybe_unserialize($meta_value);
							update_post_meta($new_post_id, $meta_key, wp_slash($meta_value));
						}
					}
				}
				// Initialize 'total_booking' - uses update_post_meta which handles caching
				update_post_meta($new_post_id, 'total_booking', 0);
				// Clear any relevant cache after operations
				wp_cache_delete('ttbm_post_' . $post_id, 'ttbm');
				clean_post_cache($new_post_id);
				// Redirect to edit new post - properly escaped
				wp_safe_redirect(
					esc_url_raw(
						admin_url('post.php?action=edit&post=' . absint($new_post_id))
					)
				);
				exit;
			}
			public function post_duplicator($actions, $post) {
				if (current_user_can('edit_posts')) {
					$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=ttbm_duplicate&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="' . esc_html__('Duplicate Post', 'tour-booking-manager') . '" rel="permalink">' . esc_html__('Duplicate', 'tour-booking-manager') . '</a>';
				}
				return $actions;
			}
			//*************************//
			public function email_content_type() {
				return "text/html";
			}
		}
		new TTBM_Admin();
	}