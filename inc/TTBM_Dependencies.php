<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Dependencies')) {
		class TTBM_Dependencies {
			public function __construct() {
				add_action('init', array($this, 'language_load'));
				$this->load_file();
				$this->appsero_init_tracker_ttbm();
				add_action('wp_enqueue_scripts', array($this, 'frontend_script'), 90);
				add_action('admin_enqueue_scripts', array($this, 'admin_script'), 90);
				add_action('ttbm_registration_enqueue', array($this, 'registration_enqueue'), 90);
				add_action('admin_init', array($this, 'ttbm_upgrade'));
				add_action('admin_head', array($this, 'js_constant'), 5);
				add_action('wp_head', array($this, 'js_constant'), 5);
			}
			public function language_load() {
				$plugin_dir = basename(dirname(__DIR__)) . "/languages/";
				load_plugin_textdomain('tour-booking-manager', false, $plugin_dir);
			}
			private function load_file() {
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Global_Function.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Global_Style.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Custom_Layout.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Custom_Slider.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/TTBM_Select_Icon_image.php';
				require_once TTBM_PLUGIN_DIR . '/admin/settings/TTBM_Setting_API.php';
				// Always load WooCommerce Installer (popup shows when Woo is not active)
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Woo_Installer.php';
				// Core plugin: tours/hotels display, admin, shortcodes. Loads regardless
				// of whether WooCommerce is active, so the plugin is fully usable without it.
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Function.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Booking_Normalizer.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Layout.php';
				require_once TTBM_PLUGIN_DIR . '/support/elementor/elementor-support.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Admin.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Frontend.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Query.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Shortcodes.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Theme_Align.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Filter_Pagination.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Hotel_Data_Display.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Tour_List.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Details_Layout.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Travel_List_Tab_Details.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Hotel_Details_Layout.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Booking.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Travel_List_CPT_Tabs.php';
				// WooCommerce-specific integration: cart/checkout hooks and the WC My
				// Account wishlist endpoint have no non-WC equivalent yet, so they stay
				// gated until the native checkout/booking phase lands.
				if (TTBM_Global_Function::has_woocommerce()) {
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Hotel_Booking.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Woocommerce.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Wishlist.php';
					require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Admin_Wishlist.php';
				}
				// Loaded last so the Pro Features placeholder menu sits at the bottom.
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Pro_Locked_Menus.php';
			}
			public function appsero_init_tracker_ttbm() {
				if (!class_exists('Appsero\Client')) {
					require_once TTBM_PLUGIN_DIR . '/lib/appsero/src/Client.php';
				}
				$client = new Appsero\Client('5e44d3f4-ddea-4784-8c15-4502ad6e7426', 'Tour Booking Manager For Woocommerce', __FILE__);
				$client->insights()->init();
			}
			public function global_enqueue() {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('mp_jquery_ui', TTBM_PLUGIN_URL . '/assets/jquery-ui.min.css', array(), '1.13.2', true);
				wp_enqueue_style('mp_font_awesome', TTBM_PLUGIN_URL . '/assets/all.min.css', array(), '6.7.2');
				wp_enqueue_style('mp_select_2', TTBM_PLUGIN_URL . '/assets/select_2/select2.min.css', array(), '4.0.13');
				wp_enqueue_script('mp_select_2', TTBM_PLUGIN_URL . '/assets/select_2/select2.min.js', array('jquery'), '4.0.13', true);
				wp_enqueue_style('mp_owl_carousel', TTBM_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.css', array(), '2.3.4');
				wp_enqueue_script('mp_owl_carousel', TTBM_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.js', array(), '2.3.4', true);
				wp_enqueue_style('ttbm_plugin_global', TTBM_PLUGIN_URL . '/assets/mp_style/ttbm_plugin_global.css', array(), TTBM_PLUGIN_VERSION);
				wp_enqueue_script('ttbm_plugin_global', TTBM_PLUGIN_URL . '/assets/mp_style/ttbm_plugin_global.js', array('jquery'), filemtime(TTBM_PLUGIN_DIR . '/assets/mp_style/ttbm_plugin_global.js'), true);
				$this->registration_enqueue();
				do_action('ttbm_common_script');
				wp_enqueue_style('mage-icons', TTBM_PLUGIN_URL . '/assets/mage-icon/css/mage-icon.css', array(), TTBM_PLUGIN_VERSION);

                $this->myplugin_enqueue_flatpickr();
			}
			/**
			 * Decide whether the tour/hotel frontend assets are needed on the
			 * current request, so they are not loaded on every page site-wide.
			 *
			 * Page builders (Elementor) and any edge case (widgets, theme-builder
			 * headers/footers, third-party builders) can force loading with:
			 *     add_filter('ttbm_load_assets', '__return_true');
			 * or force-disable by returning false from that filter.
			 *
			 * @return bool
			 */
			private function should_load_frontend_assets() {
				// Explicit override: a non-null return forces assets on/off.
				$override = apply_filters('ttbm_load_assets', null);
				if (null !== $override) {
					return (bool) $override;
				}
				$post_types = array('ttbm_tour', 'ttbm_hotel', 'ttbm_hotel_booking', 'ttbm_places', 'ttbm_guide');
				if (is_singular($post_types) || is_post_type_archive($post_types)) {
					return true;
				}
				$taxonomies = array('ttbm_tour_cat', 'ttbm_tour_org', 'ttbm_tour_location', 'ttbm_tour_features_list', 'ttbm_hotel_features_list', 'ttbm_hotel_activities_list', 'ttbm_tour_tag', 'ttbm_tour_activities');
				if (is_tax($taxonomies)) {
					return true;
				}
				// Wishlist lives under the WooCommerce My Account page.
				if (function_exists('is_account_page') && is_account_page()) {
					return true;
				}
				// WooCommerce order-received (thank-you) page. The Pro "Download Ticket"
				// button is printed here via the woocommerce_thankyou hook and relies on
				// the global click handler + styles in ttbm_plugin_global.{js,css}.
				// (My Account "view-order" is already covered by is_account_page() above.)
				// Only load when the order actually contains a tour, so the tour asset
				// bundle is not pulled into every unrelated thank-you page.
				if (function_exists('is_order_received_page') && is_order_received_page()) {
					global $wp;
					$order_id = isset($wp->query_vars['order-received']) ? absint($wp->query_vars['order-received']) : 0;
					if ($order_id && function_exists('wc_get_order')) {
						$order = wc_get_order($order_id);
						if ($order) {
							foreach ($order->get_items() as $item_id => $item) {
								$tour_id = wc_get_order_item_meta($item_id, '_ttbm_id');
								if ($tour_id && get_post_type($tour_id) === 'ttbm_tour') {
									return true;
								}
							}
						}
					}
				}
				// Shortcode or Elementor widget embedded in the current content.
				$post = get_post();
				if ($post instanceof WP_Post) {
					if ($post->post_content && (
						false !== strpos($post->post_content, '[ttbm-') ||
						false !== strpos($post->post_content, '[travel-') ||
						false !== strpos($post->post_content, '[wptravelly-')
					)) {
						return true;
					}
					$elementor_data = get_post_meta($post->ID, '_elementor_data', true);
					if (is_string($elementor_data) && false !== strpos($elementor_data, 'ttbm-tour-')) {
						return true;
					}
				}
				return false;
			}
			public function frontend_script() {
				if (!$this->should_load_frontend_assets()) {
					return;
				}
				$this->global_enqueue();
				wp_enqueue_script('jquery-ui-accordion');
				wp_enqueue_script('ttbm_script', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_script.js', array('jquery'), TTBM_PLUGIN_VERSION, true);
				wp_enqueue_script('ttbm_shortcode', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_shortcode.js', array('jquery'), TTBM_PLUGIN_VERSION, true);
				wp_enqueue_script('ttbm-confirm-btn', TTBM_PLUGIN_URL . '/assets/frontend/ttbm-confirm-btn.js', array('jquery'), TTBM_PLUGIN_VERSION, true);
                wp_enqueue_style('ttbm_hotel_lists', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_hotel_lists.css', array('ttbm_registration'), TTBM_PLUGIN_VERSION);
                wp_enqueue_style('ttbm_details', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_details.css', array('ttbm_hotel_lists'), filemtime(TTBM_PLUGIN_DIR . '/assets/frontend/ttbm_details.css'));

				wp_localize_script('ttbm_script', 'ttbm_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ttbm_frontend_nonce'),
					// Wishlist lives on the WooCommerce My Account page (see TTBM_Wishlist.php),
					// so there is no destination URL to give without WooCommerce.
					'wishlist_url' => TTBM_Global_Function::has_woocommerce() ? wc_get_account_endpoint_url('ttbm-wishlist') : ''
				));
				do_action('ttbm_frontend_script');
			}
			/**
			 * Whether the heavy TTBM admin bundle (editor, media, select2, color
			 * picker, codemirror, FontAwesome, plugin admin CSS/JS, and the
			 * `ttbm_admin_script` action consumed by the pricing addons) should
			 * load on the current admin screen.
			 *
			 * Without this guard, admin_script() ran unconditionally on every
			 * wp-admin page via admin_enqueue_scripts, not just tour/hotel
			 * editing screens.
			 *
			 * Override with: add_filter('ttbm_load_admin_assets', '__return_true');
			 *
			 * @param string $hook Current admin page hook suffix.
			 * @return bool
			 */
			private function should_load_admin_assets($hook = '') {
				// Explicit override: a non-null return forces assets on/off.
				$override = apply_filters('ttbm_load_admin_assets', null, $hook);
				if (null !== $override) {
					return (bool) $override;
				}
				$screen = function_exists('get_current_screen') ? get_current_screen() : null;
				$ttbm_post_types = array('ttbm_tour', 'ttbm_hotel', 'ttbm_hotel_booking', 'ttbm_places', 'ttbm_guide', 'ttbm_ticket_types', 'ttbm_enquiry');
				if ($screen) {
					if (!empty($screen->post_type) && in_array($screen->post_type, $ttbm_post_types, true)) {
						return true;
					}
					if (!empty($screen->taxonomy) && false !== strpos($screen->taxonomy, 'ttbm')) {
						return true;
					}
					if (!empty($screen->id) && false !== strpos($screen->id, 'ttbm')) {
						return true;
					}
				}
				if ($hook && false !== strpos($hook, 'ttbm')) {
					return true;
				}
				if (isset($_GET['page']) && false !== strpos(sanitize_text_field(wp_unslash($_GET['page'])), 'ttbm')) {
					return true;
				}
				return false;
			}
			public function admin_script($hook = '') {
				if (!$this->should_load_admin_assets($hook)) {
					return;
				}
				// Global toast utility (window.ttbmToast()) — loaded on every
				// TTBM admin screen so any feature's JS can call it, not just
				// the one that first needed it.
				wp_enqueue_script('ttbm-admin-toast', TTBM_PLUGIN_URL . '/assets/admin/ttbm-admin-toast.js', array('jquery'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-admin-toast.js'), true);
				wp_enqueue_style('ttbm-admin-toast', TTBM_PLUGIN_URL . '/assets/admin/ttbm-admin-toast.css', array(), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-admin-toast.css'));
				wp_enqueue_editor();
				wp_enqueue_media();
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				wp_enqueue_style('wp-codemirror');
				wp_enqueue_script('wp-codemirror');
				$this->global_enqueue();
				wp_enqueue_script('magepeople-options-framework', TTBM_PLUGIN_URL . '/assets/helper/js/mage-options-framework.js', array('jquery'), null);
				wp_localize_script('PickpluginsOptionsFramework', 'PickpluginsOptionsFramework_ajax', array('PickpluginsOptionsFramework_ajaxurl' => admin_url('admin-ajax.php')));
				wp_enqueue_style('mage-options-framework', TTBM_PLUGIN_URL . '/assets/helper/css/mage-options-framework.css');
				wp_enqueue_script('form-field-dependency', TTBM_PLUGIN_URL . '/assets/admin/form-field-dependency.js', array('jquery'), null, false);
				//================//
				wp_enqueue_style('jquery.timepicker.min', TTBM_PLUGIN_URL . '/assets/timepicker/timepicker.css', array(), '1.3.5');
				wp_enqueue_script('jquery.timepicker.min', TTBM_PLUGIN_URL . '/assets/timepicker/timepicker.js', array('jquery'), TTBM_PLUGIN_VERSION, true);
				//===================//
				wp_enqueue_script('ttbm_admin_settings', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_settings.js', array('jquery'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm_admin_settings.js'), true);
				//===================//
				wp_enqueue_script('ttbm_hotel_booking', TTBM_PLUGIN_URL . '/assets/admin/ttbm_hotel_booking.js', array('jquery', 'jquery-ui-datepicker'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm_hotel_booking.js'), true);
				wp_enqueue_script('ttbm_admin_script', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_script.js', array('jquery', 'ttbm_hotel_booking'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm_admin_script.js'), true);
				wp_enqueue_style('ttbm_admin', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin.css', array(), TTBM_PLUGIN_VERSION);
				wp_enqueue_style('ttbm_admin_modern', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_modern.css', array('ttbm_admin'), TTBM_PLUGIN_VERSION);
				wp_localize_script('ttbm_hotel_booking', 'ttbm_admin_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ttbm_admin_nonce'),
					'strings' => array(
						'name_required' => __( 'Name is required.', 'tour-booking-manager' ),
						'address_required' => __( 'Full address is required.', 'tour-booking-manager' ),
						'icon_required' => __( 'Icon is required.', 'tour-booking-manager' ),
						'saving' => __( 'Saving...', 'tour-booking-manager' ),
						'save_failed' => __( 'Something went wrong. Please try again.', 'tour-booking-manager' ),
						'request_failed' => __( 'Request failed. Please check your connection and try again.', 'tour-booking-manager' ),
						'no_tours_found' => __( 'No tours found.', 'tour-booking-manager' ),
					),
				));
				wp_localize_script('ttbm_admin_script', 'ttbm_admin_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ttbm_admin_nonce'),
				));
				// Background auto-save — only on the single tour add/edit screen.
				$autosave_screen = function_exists('get_current_screen') ? get_current_screen() : null;
				if ($autosave_screen && $autosave_screen->base === 'post' && $autosave_screen->post_type === TTBM_Function::get_cpt_name()) {
					global $post;
					$autosave_post_id = ($post && isset($post->ID)) ? (int) $post->ID : (isset($_GET['post']) ? (int) $_GET['post'] : 0);
					wp_enqueue_style('ttbm-autosave', TTBM_PLUGIN_URL . '/assets/admin/ttbm-autosave.css', array('ttbm-admin-toast'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-autosave.css'));
					wp_enqueue_script('ttbm-autosave', TTBM_PLUGIN_URL . '/assets/admin/ttbm-autosave.js', array('jquery', 'ttbm-admin-toast', 'ttbm_admin_settings', 'ttbm_admin_script'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-autosave.js'), true);
					wp_localize_script('ttbm-autosave', 'ttbm_autosave_vars', array(
						'ajax_url'     => admin_url('admin-ajax.php'),
						'nonce'        => wp_create_nonce('ttbm_autosave'),
						'post_id'      => $autosave_post_id,
						'debounce'     => (int) apply_filters('ttbm_autosave_debounce_ms', 1200),
						'min_interval' => (int) apply_filters('ttbm_autosave_min_interval_ms', 3000),
						'enabled'      => (bool) apply_filters('ttbm_enable_tour_autosave', true, $autosave_post_id),
						'i18n'         => array(
							'ready'        => __('Auto-save on', 'tour-booking-manager'),
							'unsaved'      => __('Unsaved changes', 'tour-booking-manager'),
							'saving'       => __('Saving…', 'tour-booking-manager'),
							'saved'        => __('Saved', 'tour-booking-manager'),
							'paused'       => __('Auto-save paused — complete required fields', 'tour-booking-manager'),
							'error'        => __('Auto-save failed — will retry', 'tour-booking-manager'),
							'just_now'     => __('just now', 'tour-booking-manager'),
							'one_min'      => __('1 min ago', 'tour-booking-manager'),
							/* translators: %d: minutes elapsed */
							'mins_ago'     => __('%d mins ago', 'tour-booking-manager'),
							'saved_toast'  => __('All changes auto-saved.', 'tour-booking-manager'),
							'paused_toast' => __('Auto-save paused: some required fields are missing.', 'tour-booking-manager'),
							'error_toast'  => __('Auto-save failed — your changes are not saved yet.', 'tour-booking-manager'),
						),
					));
				}
				do_action('ttbm_admin_script');
			}
			public function registration_enqueue() {
				wp_register_script(
					'moment',
					'https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js',
					array(),
					'2.29.4',
					true
				);
				wp_enqueue_script( 'moment' );
				wp_enqueue_style('ttbm_date_range_picker', TTBM_PLUGIN_URL . '/assets/date_range_picker/date_range_picker.min.css', array(), '1');
				wp_enqueue_script('ttbm_date_range_picker_js', TTBM_PLUGIN_URL . '/assets/date_range_picker/date_range_picker.js', array('jquery', 'moment'), '1', true);
				wp_enqueue_style('ttbm_registration', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_registration.css', array(), TTBM_PLUGIN_VERSION);
				wp_enqueue_style('ttbm_smart_booking', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_smart_booking.css', array('ttbm_registration'), TTBM_PLUGIN_VERSION);
				wp_enqueue_script('jquery-ui-autocomplete');
				wp_enqueue_script('ttbm_registration', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_registration.js', array('jquery', 'jquery-ui-autocomplete', 'ttbm_date_range_picker_js'), TTBM_PLUGIN_VERSION, true);
				wp_enqueue_script('ttbm_attendee_autocomplete', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_attendee_autocomplete.js', array('jquery', 'jquery-ui-autocomplete'), TTBM_PLUGIN_VERSION, true);
				wp_enqueue_script('ttbm_price_calculation', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_price_calculation.js', array('jquery'), TTBM_PLUGIN_VERSION, true);
				wp_enqueue_script('ttbm_smart_booking', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_smart_booking.js', array('jquery', 'ttbm_registration', 'ttbm_price_calculation'), TTBM_PLUGIN_VERSION, true);
				wp_enqueue_script('ttbm_hotel_script', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_hotel_script.js', array('jquery', 'moment', 'ttbm_date_range_picker_js'), TTBM_PLUGIN_VERSION, true);
				wp_enqueue_script('ttbm_filter_pagination_script', TTBM_PLUGIN_URL . '/assets/frontend/filter_pagination.js', array('jquery', 'mp_select_2', 'moment', 'ttbm_date_range_picker_js'), TTBM_PLUGIN_VERSION, true);
				wp_localize_script('ttbm_registration', 'ttbm_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ttbm_frontend_nonce')
				));
				wp_localize_script('ttbm_price_calculation', 'ttbm_price_calc_vars', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ttbm_frontend_nonce')
				));
				do_action('add_ttbm_registration_enqueue');
			}
			public function ttbm_upgrade() {
				if (get_option('ttbm_conflict_update') != 'completed') {
					$mp_global_settings = get_option('mp_global_settings');
					update_option('ttbm_global_settings', $mp_global_settings);
					$style_settings = get_option('mp_style_settings');
					update_option('ttbm_style_settings', $style_settings);
					$slider_settings = get_option('ttbm_slider_settings');
					update_option('ttbm_slider_settings', $slider_settings);
					$license_settings = get_option('mp_basic_license_settings');
					update_option('ttbm_license_settings', $license_settings);
					update_option('ttbm_conflict_update', 'completed');
				}
				if (get_option('ttbm_upgrade_global') != 'completed') {
					$basic_settings = get_option('ttbm_basic_gen_settings');
					$global_settings = get_option('mp_global_settings') ? get_option('mp_global_settings') : [];
					if (is_array($basic_settings) && array_key_exists('ttbm_date_format', $basic_settings) && $basic_settings['ttbm_date_format']) {
						$global_settings['date_format'] = $basic_settings['ttbm_date_format'];
					}
					if (is_array($basic_settings) && array_key_exists('ttbm_date_format_short', $basic_settings) && $basic_settings['ttbm_date_format_short']) {
						$global_settings['date_format_short'] = $basic_settings['ttbm_date_format_short'];
					}
					update_option('mp_global_settings', $global_settings);
					$style_settings = get_option('ttbm_basic_style_settings');
					if (is_array($style_settings) && sizeof($style_settings) > 0) {
						$current_style = get_option('mp_style_settings') ? get_option('mp_style_settings') : [];
						if (isset($style_settings['ttbm_default_text_color']) && $style_settings['ttbm_default_text_color']) {
							$current_style['default_text_color'] = $style_settings['ttbm_default_text_color'];
						}
						if (isset($style_settings['ttbm_theme_color']) && $style_settings['ttbm_theme_color']) {
							$current_style['theme_color'] = $style_settings['ttbm_theme_color'];
						}
						if (isset($style_settings['ttbm_theme_alternate_color']) && $style_settings['ttbm_theme_alternate_color']) {
							$current_style['theme_alternate_color'] = $style_settings['ttbm_theme_alternate_color'];
						}
						if (isset($style_settings['ttbm_warning_color']) && $style_settings['ttbm_warning_color']) {
							$current_style['warning_color'] = $style_settings['ttbm_warning_color'];
						}
						if (isset($style_settings['ttbm_button_color']) && $style_settings['ttbm_button_color']) {
							$current_style['button_color'] = $style_settings['ttbm_button_color'];
						}
						if (isset($style_settings['ttbm_button_bg']) && $style_settings['ttbm_button_bg']) {
							$current_style['button_bg'] = $style_settings['ttbm_button_bg'];
						}
						if (isset($style_settings['ttbm_section_bg']) && $style_settings['ttbm_section_bg']) {
							$current_style['section_bg'] = $style_settings['ttbm_section_bg'];
						}
						update_option('mp_style_settings', $current_style);
					}
					update_option('ttbm_upgrade_global', 'completed');
				}
			}
			public function myplugin_enqueue_flatpickr() {

                // Flatpickr CSS
                wp_enqueue_style(
                    'flatpickr-css',
                    'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
                    [],
                    null
                );

                // Flatpickr JS
                wp_enqueue_script(
                    'flatpickr-js',
                    'https://cdn.jsdelivr.net/npm/flatpickr',
                    ['jquery'],
                    null,
                    true
                );

                // Detect WordPress locale dynamically (e.g. 'pl_PL' -> 'pl', 'de_DE' -> 'de')
                $wp_locale    = get_locale();                          // e.g. 'pl_PL'
                $lang_code    = strtolower( substr( $wp_locale, 0, 2 ) ); // e.g. 'pl'

                // Enqueue the flatpickr locale JS only when the site is NOT in English
                if ( ! empty( $lang_code ) && $lang_code !== 'en' ) {
                    wp_enqueue_script(
                        'flatpickr-locale',
                        'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/' . $lang_code . '.js',
                        [ 'flatpickr-js' ],
                        null,
                        true
                    );
                }

                // Pass the locale code to JS so filter_pagination.js can use it dynamically
                wp_localize_script( 'flatpickr-js', 'ttbm_flatpickr_vars', array(
                    'locale' => ( $lang_code !== 'en' ) ? $lang_code : 'default',
                ) );
            }
			public function js_constant() {
				?>
                <script type="text/javascript">
                    let ttbm_currency_symbol = "";
                    let ttbm_currency_position = "";
                    let ttbm_currency_decimal = "";
                    let ttbm_currency_thousands_separator = "";
                    let ttbm_num_of_decimal = "";
                    let ttbm_ajax_url = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
                    let ttbm_site_url = " <?php echo esc_attr(get_site_url()) ?>";
                    let ttbm_empty_image_url = "<?php echo esc_attr(TTBM_PLUGIN_URL . '/assets/images/no_image.png'); ?>";
                    let ttbm_date_format = "<?php echo esc_attr(TTBM_Global_Function::get_settings('ttbm_global_settings', 'date_format', 'D d M , yy')); ?>";
                    let ttbm_date_format_without_year = "<?php echo esc_attr(TTBM_Global_Function::get_settings('ttbm_global_settings', 'date_format_without_year', 'D d M')); ?>";
                    let ttbm_ajax_is_logged_in = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
                </script>
				<?php
				if (TTBM_Global_Function::check_woocommerce() == 1) {
					?>
                    <script type="text/javascript">
                        ttbm_currency_symbol = "<?php echo get_woocommerce_currency_symbol(); ?>";
                        ttbm_currency_position = "<?php echo esc_html(get_option('woocommerce_currency_pos')); ?>";
                        ttbm_currency_decimal = "<?php echo esc_html(wc_get_price_decimal_separator()); ?>";
                        ttbm_currency_thousands_separator = "<?php echo esc_html(wc_get_price_thousand_separator()); ?>";
                        ttbm_num_of_decimal = "<?php echo esc_html(get_option('woocommerce_price_num_decimals', 2)); ?>";
                    </script>
					<?php
				}
			}
		}
		new TTBM_Dependencies();
	}
