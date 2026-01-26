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
				// Always load Quick Setup (needed even when WooCommerce is not active)
				require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Quick_Setup.php';
				if (TTBM_Global_Function::check_woocommerce() == 1) {
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Function.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Layout.php';
					require_once TTBM_PLUGIN_DIR . '/support/elementor/elementor-support.php';
					require_once TTBM_PLUGIN_DIR . '/admin/TTBM_Admin.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Frontend.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Query.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Shortcodes.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Filter_Pagination.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Hotel_Data_Display.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Tour_List.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Details_Layout.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Travel_List_Tab_Details.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Hotel_Details_Layout.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Booking.php';
					require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Hotel_Booking.php';
require_once TTBM_PLUGIN_DIR . '/inc/TTBM_Woocommerce.php';
				}
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
				wp_enqueue_script('mp_select_2', TTBM_PLUGIN_URL . '/assets/select_2/select2.min.js', array(), '4.0.13', true);
				wp_enqueue_style('mp_owl_carousel', TTBM_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.css', array(), '2.3.4');
				wp_enqueue_script('mp_owl_carousel', TTBM_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.js', array(), '2.3.4', true);
				wp_enqueue_style('ttbm_plugin_global', TTBM_PLUGIN_URL . '/assets/mp_style/ttbm_plugin_global.css', array(), time());
				wp_enqueue_script('ttbm_plugin_global', TTBM_PLUGIN_URL . '/assets/mp_style/ttbm_plugin_global.js', array('jquery'), time(), true);
				$this->registration_enqueue();
				do_action('ttbm_common_script');
				wp_enqueue_style('mage-icons', TTBM_PLUGIN_URL . '/assets/mage-icon/css/mage-icon.css', array(), time());

                $this->myplugin_enqueue_flatpickr();
			}
			public function frontend_script() {
				$this->global_enqueue();
				wp_enqueue_script('jquery-ui-accordion');
				wp_enqueue_script('ttbm_script', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_script.js', array('jquery'), time(), true);
				wp_enqueue_script('ttbm_shortcode', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_shortcode.js', array('jquery'), time(), true);
                wp_enqueue_style('ttbm_hotel_lists', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_hotel_lists.css', array(), time());

				wp_localize_script('ttbm_script', 'ttbm_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ttbm_frontend_nonce')
				));
				do_action('ttbm_frontend_script');
			}
			public function admin_script() {
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
				wp_enqueue_script('jquery.timepicker.min', TTBM_PLUGIN_URL . '/assets/timepicker/timepicker.js', array('jquery'), time(), true);
				//===================//
				wp_enqueue_script('ttbm_admin_settings', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_settings.js', array('jquery'), time(), true);
				//===================//
				wp_enqueue_script('ttbm_admin_script', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_script.js', array('jquery'), time(), true);
				wp_enqueue_script('ttbm_hotel_booking', TTBM_PLUGIN_URL . '/assets/admin/ttbm_hotel_booking.js', array('jquery'), time(), true);
				wp_enqueue_style('ttbm_admin', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin.css', array(), time());
				wp_localize_script('ttbm_admin_script', 'ttbm_admin_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('ttbm_admin_nonce')
				));
				do_action('ttbm_admin_script');
			}
			public function registration_enqueue() {
				wp_enqueue_style('ttbm_date_range_picker', TTBM_PLUGIN_URL . '/assets/date_range_picker/date_range_picker.min.css', array(), '1');
				wp_enqueue_script('ttbm_date_range_picker_js', TTBM_PLUGIN_URL . '/assets/date_range_picker/date_range_picker.js', array('jquery', 'moment'), '1', true);
				wp_enqueue_style('ttbm_registration', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_registration.css', array(), time());
				wp_enqueue_script('jquery-ui-autocomplete');
				wp_enqueue_script('ttbm_registration', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_registration.js', array('jquery', 'jquery-ui-autocomplete'), time(), true);
				wp_enqueue_script('ttbm_attendee_autocomplete', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_attendee_autocomplete.js', array('jquery', 'jquery-ui-autocomplete'), time(), true);
				wp_enqueue_script('ttbm_price_calculation', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_price_calculation.js', array('jquery'), time(), true);
			wp_enqueue_script('ttbm_hotel_script', TTBM_PLUGIN_URL . '/assets/frontend/ttbm_hotel_script.js', array('jquery'), time(), true);
			wp_enqueue_script('ttbm_filter_pagination_script', TTBM_PLUGIN_URL . '/assets/frontend/filter_pagination.js', array('jquery'), time(), true);
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

            function myplugin_enqueue_flatpickr() {

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
