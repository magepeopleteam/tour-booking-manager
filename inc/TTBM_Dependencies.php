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
				add_action('add_mp_frontend_enqueue', array($this, 'frontend_script'), 90);
				add_action('add_mp_admin_enqueue', array($this, 'admin_script'), 90);
				add_action('ttbm_registration_enqueue', array($this, 'registration_enqueue'), 90);
				add_action('admin_init', array($this, 'ttbm_upgrade'));
			}
			public function language_load() {
				$plugin_dir = basename(dirname(__DIR__)) . "/languages/";
				load_plugin_textdomain('tour-booking-manager', false, $plugin_dir);
			}
			private function load_file() {
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Function.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Layout.php';
				require_once TTBM_PLUGIN_DIR . '/support/elementor/elementor-support.php';
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Admin.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Frontend.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Query.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Shortcodes.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Filter_Pagination.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Tour_List.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Details_Layout.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Booking.php';
				require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Woocommerce.php';
			}
			public function appsero_init_tracker_ttbm() {
				if (!class_exists('Appsero\Client')) {
					require_once TTBM_PLUGIN_DIR . '/lib/appsero/src/Client.php';
				}
				$client = new Appsero\Client('5e44d3f4-ddea-4784-8c15-4502ad6e7426', 'Tour Booking Manager For Woocommerce', __FILE__);
				$client->insights()->init();
			}
			public function global_enqueue() {
				$this->registration_enqueue();
				do_action('ttbm_common_script');
			}
			public function frontend_script() {
				$this->global_enqueue();
				wp_enqueue_script('jquery-ui-accordion');
				wp_enqueue_style('ttbm_style', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_style.css', array(), time());
				wp_enqueue_script('ttbm_script', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_script.js', array('jquery'), time(), true);
				do_action('ttbm_frontend_script');
			}
			public function admin_script() {
				$this->global_enqueue();
				wp_enqueue_script('magepeople-options-framework', TTBM_PLUGIN_URL . '/assets/helper/js/mage-options-framework.js', array('jquery'), null);
				wp_localize_script('PickpluginsOptionsFramework', 'PickpluginsOptionsFramework_ajax', array('PickpluginsOptionsFramework_ajaxurl' => admin_url('admin-ajax.php')));
				//wp_enqueue_script('form-field-dependency', TTBM_PLUGIN_URL . '/assets/helper/js/form-field-dependency.js', array('jquery'), null);
				wp_enqueue_style('mage-options-framework', TTBM_PLUGIN_URL . '/assets/helper/css/mage-options-framework.css');
				wp_enqueue_script('ttbm_admin_script', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_script.js', array('jquery'), time(), true);
				wp_enqueue_style('ttbm_admin_style', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_style.css', array(), time());
				wp_enqueue_style('mp_main_settings', TTBM_PLUGIN_URL . '/assets/admin/mp_main_settings.css', array(), time());
				do_action('ttbm_admin_script');
			}
			public function registration_enqueue() {
				wp_enqueue_style('ttbm_filter_pagination_style', TTBM_PLUGIN_URL . '/assets/frontend/filter_pagination.css', array(), time());
				wp_enqueue_script('ttbm_filter_pagination_script', TTBM_PLUGIN_URL . '/assets/frontend/filter_pagination.js', array('jquery'), time(), true);
				wp_enqueue_style('ttbm_date_range_picker', TTBM_PLUGIN_URL . '/assets/date_range_picker/date_range_picker.min.css', array(), '1');
				wp_enqueue_script('ttbm_date_range_picker_js', TTBM_PLUGIN_URL . '/assets/date_range_picker/date_range_picker.js', array('jquery', 'moment'), '1', true);
				wp_enqueue_style('ttbm_registration_style', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_registration.css', array(), time());
				wp_enqueue_script('ttbm_registration_script', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_registration.js', array('jquery'), time(), true);
				wp_enqueue_script('ttbm_price_calculation', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_price_calculation.js', array('jquery'), time(), true);
				// Google Font
				wp_enqueue_style('google-font', 'https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&display=swap', array(), time());
				do_action('add_ttbm_registration_enqueue');
			}
			public function ttbm_upgrade() {
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
					$custom_css = get_option('ttbm_custom_css');
					update_option('mp_add_custom_css', $custom_css);
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
						if (isset($style_settings['ttbm_default_font_size']) && $style_settings['ttbm_default_font_size']) {
							$current_style['default_font_size'] = $style_settings['ttbm_default_font_size'];
						}
						if (isset($style_settings['ttbm_font_size_h1']) && $style_settings['ttbm_font_size_h1']) {
							$current_style['font_size_h1'] = $style_settings['ttbm_font_size_h1'];
						}
						if (isset($style_settings['ttbm_font_size_h2']) && $style_settings['ttbm_font_size_h2']) {
							$current_style['font_size_h2'] = $style_settings['ttbm_font_size_h2'];
						}
						if (isset($style_settings['ttbm_font_size_h3']) && $style_settings['ttbm_font_size_h3']) {
							$current_style['font_size_h3'] = $style_settings['ttbm_font_size_h3'];
						}
						if (isset($style_settings['ttbm_font_size_h4']) && $style_settings['ttbm_font_size_h4']) {
							$current_style['font_size_h4'] = $style_settings['ttbm_font_size_h4'];
						}
						if (isset($style_settings['ttbm_font_size_h5']) && $style_settings['ttbm_font_size_h5']) {
							$current_style['font_size_h5'] = $style_settings['ttbm_font_size_h5'];
						}
						if (isset($style_settings['ttbm_font_size_h6']) && $style_settings['ttbm_font_size_h6']) {
							$current_style['font_size_h6'] = $style_settings['ttbm_font_size_h6'];
						}
						if (isset($style_settings['ttbm_font_size_label']) && $style_settings['ttbm_font_size_label']) {
							$current_style['font_size_label'] = $style_settings['ttbm_font_size_label'];
						}
						if (isset($style_settings['ttbm_font_size_button']) && $style_settings['ttbm_font_size_button']) {
							$current_style['button_font_size'] = $style_settings['ttbm_font_size_button'];
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
		}
		new TTBM_Dependencies();
	}
