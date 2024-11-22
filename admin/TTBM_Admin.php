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
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Admin_Tour_List.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Welcome.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Quick_Setup.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Status.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_License.php';
				//**********//
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Settings_Global.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Ticket_Types.php';
				//**********//
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_General.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_pricing.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_extra_service.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Gallery.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Feature.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_guide.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_activity.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_place_you_see.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_faq_day_wise_details.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Related.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Contact.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_why_book_with_us.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Admin_Note.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/tour/TTBM_Settings_Display.php';
				//**********//
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Hotel.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Hotel_General.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/hotel/TTBM_Settings_Hotel_Price.php';
				//**********//
			}
			public function ttbm_widgets_init() {
				register_sidebar(['name' => esc_html__('Tour Booking Details Page Sidebar', 'tour-booking-manager'), 'id' => 'ttbm_details_sidebar', 'description' => esc_html__('Widgets in this area will be shown on tour booking details page sidebar.', 'tour-booking-manager'), 'before_widget' => '<div id="%1$s" class="ttbm_default_widget ttbm_sidebar_widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4 class="ttbm_title_style_3">', 'after_title' => '</h4>',]);
			}
			//************Disable Gutenberg************************//
			public function disable_gutenberg($current_status, $post_type) {
				$user_status = MP_Global_Function::get_settings('mp_global_settings', 'disable_block_editor', 'yes');
				if ($post_type === TTBM_Function::get_cpt_name() && $user_status == 'yes') {
					return false;
				}
				return $current_status;
			}
			//**************Post duplicator*********************//
			public function ttbm_duplicate() {
				global $wpdb;
				if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'ttbm_duplicate' == $_REQUEST['action']))) {
					wp_die('No post to duplicate has been supplied!');
				}
				if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
					return;
				}
				$post_id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']));
				$post = get_post($post_id);
				$current_user = wp_get_current_user();
				$new_post_author = $current_user->ID;
				if (isset($post) && $post != null) {
					$args = array('comment_status' => $post->comment_status, 'ping_status' => $post->ping_status, 'post_author' => $new_post_author, 'post_content' => $post->post_content, 'post_excerpt' => $post->post_excerpt, 'post_name' => $post->post_name, 'post_parent' => $post->post_parent, 'post_password' => $post->post_password, 'post_status' => 'draft', 'post_title' => $post->post_title, 'post_type' => $post->post_type, 'to_ping' => $post->to_ping, 'menu_order' => $post->menu_order);
					$new_post_id = wp_insert_post($args);
					$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
					foreach ($taxonomies as $taxonomy) {
						$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
						wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
					}
					$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id AND meta_key !='total_booking'");
					if (count($post_meta_infos) != 0) {
						$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
						foreach ($post_meta_infos as $meta_info) {
							$meta_key = $meta_info->meta_key;
							if ($meta_key == '_wp_old_slug') {
								continue;
							}
							$meta_value = addslashes($meta_info->meta_value);
							$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
						}
						$sql_query .= implode(" UNION ALL ", $sql_query_sel);
						$wpdb->query($sql_query);
						$table_name = $wpdb->prefix . 'postmeta';
						$bi = $wpdb->insert($table_name, array('post_id' => $new_post_id, 'meta_key' => 'total_booking', 'meta_value' => 0), array('%d', '%s', '%d'));
					}
					wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
					exit;
				} else {
					wp_die('Post creation failed, could not find original post: ' . $post_id);
				}
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