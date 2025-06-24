<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Global_File_Load')) {
		class TTBM_Global_File_Load {
			public function __construct() {
				$this->define_constants();
				$this->load_global_file();
				add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'), 80);
				add_action('transporter_panel_admin_enqueue_scripts', array($this, 'admin_enqueue'), 80);
				add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue'), 80);
				add_action('admin_head', array($this, 'add_admin_head'), 5);
				add_action('wp_head', array($this, 'add_frontend_head'), 5);
			}
			public function define_constants() {
				if (!defined('TTBM_GLOBAL_PLUGIN_DIR')) {
					define('TTBM_GLOBAL_PLUGIN_DIR', dirname(__FILE__));
				}
				if (!defined('TTBM_GLOBAL_PLUGIN_URL')) {
					define('TTBM_GLOBAL_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
				}
			}
			public function load_global_file() {
				require_once TTBM_GLOBAL_PLUGIN_DIR . '/class/TTBM_Global_Function.php';
				require_once TTBM_GLOBAL_PLUGIN_DIR . '/class/TTBM_Global_Style.php';
				require_once TTBM_GLOBAL_PLUGIN_DIR . '/class/TTBM_Custom_Layout.php';
				require_once TTBM_GLOBAL_PLUGIN_DIR . '/class/TTBM_Custom_Slider.php';
				require_once TTBM_GLOBAL_PLUGIN_DIR . '/class/TTBM_Select_Icon_image.php';
				require_once TTBM_GLOBAL_PLUGIN_DIR . '/class/TTBM_Setting_API.php';
				require_once TTBM_GLOBAL_PLUGIN_DIR . '/class/TTBM_Global_Settings.php';
			}
			public function global_enqueue() {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('mp_jquery_ui', TTBM_GLOBAL_PLUGIN_URL . '/assets/jquery-ui.min.css', array(), time(), true);
				wp_enqueue_style('mp_font_awesome', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css', array(), time());
				wp_enqueue_style('mp_select_2', TTBM_GLOBAL_PLUGIN_URL . '/assets/select_2/select2.min.css', array(), time());
				wp_enqueue_script('mp_select_2', TTBM_GLOBAL_PLUGIN_URL . '/assets/select_2/select2.min.js', array(), time(), true);
				wp_enqueue_style('mp_owl_carousel', TTBM_GLOBAL_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.css', array(), time());
				wp_enqueue_script('mp_owl_carousel', TTBM_GLOBAL_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.js', array(), time(), true);
				wp_enqueue_style('ttbm_plugin_global', TTBM_GLOBAL_PLUGIN_URL . '/assets/mp_style/ttbm_plugin_global.css', array(), time());
				wp_enqueue_script('ttbm_plugin_global', TTBM_GLOBAL_PLUGIN_URL . '/assets/mp_style/ttbm_plugin_global.js', array('jquery'), time(), true);
                wp_enqueue_style('mp_plugin_global', TTBM_GLOBAL_PLUGIN_URL . '/assets/mp_style/ttbm_popup_style.css', array(), time());
				do_action('add_ttbm_global_enqueue');
			}
			public function admin_enqueue() {
				$this->global_enqueue();
				wp_enqueue_editor();
				wp_enqueue_media();
				//admin script
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				wp_enqueue_style('wp-codemirror');
				wp_enqueue_script('wp-codemirror');
				//wp_enqueue_script('jquery-ui-accordion');
				//loading Time picker
				wp_enqueue_style('jquery.timepicker.min', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
				wp_enqueue_script('jquery.timepicker.min', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', array('jquery'), 1, true);
				//=====================//
				wp_enqueue_script('form-field-dependency', TTBM_GLOBAL_PLUGIN_URL . '/assets/admin/form-field-dependency.js', array('jquery'), null, false);
				// admin setting global
				wp_enqueue_script('ttbm_admin_settings', TTBM_GLOBAL_PLUGIN_URL . '/assets/admin/ttbm_admin_settings.js', array('jquery'), time(), true);
				wp_enqueue_style('ttbm_admin_settings', TTBM_GLOBAL_PLUGIN_URL . '/assets/admin/ttbm_admin_settings.css', array(), time());
				do_action('add_ttbm_admin_enqueue');
			}
			public function frontend_enqueue() {
				$this->global_enqueue();
				do_action('add_ttbm_frontend_enqueue');
			}
			public function add_admin_head() {
				$this->js_constant();
			}
			public function add_frontend_head() {
				$this->js_constant();
				$this->custom_css();
			}
			public function js_constant() {
				?>
				<script type="text/javascript">
					let ttbm_currency_symbol = "";
					let ttbm_currency_position = "";
					let ttbm_currency_decimal = "";
					let ttbm_currency_thousands_separator = "";
					let ttbm_num_of_decimal = "";
					let ttbm_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
					let ttbm_site_url = " <?php echo esc_attr( get_site_url() ) ?>";
					let ttbm_empty_image_url = "<?php echo esc_attr(TTBM_GLOBAL_PLUGIN_URL . '/assets/images/no_image.png'); ?>";
					let ttbm_date_format = "<?php echo esc_attr(TTBM_Global_Function::get_settings('ttbm_global_settings', 'date_format', 'D d M , yy')); ?>";
					let ttbm_date_format_without_year = "<?php echo esc_attr(TTBM_Global_Function::get_settings('ttbm_global_settings', 'date_format_without_year', 'D d M')); ?>";
				</script>
				<?php
				if (TTBM_Global_Function::check_woocommerce() == 1) {
					?>
					<script type="text/javascript">
						ttbm_currency_symbol = "<?php echo get_woocommerce_currency_symbol(); ?>";
						ttbm_currency_position = "<?php echo get_option('woocommerce_currency_pos'); ?>";
						ttbm_currency_decimal = "<?php echo wc_get_price_decimal_separator(); ?>";
						ttbm_currency_thousands_separator = "<?php echo wc_get_price_thousand_separator(); ?>";
						ttbm_num_of_decimal = "<?php echo get_option('woocommerce_price_num_decimals', 2); ?>";
					</script>
					<?php
				}
			}
			public function custom_css() {
				$custom_css = TTBM_Global_Function::get_settings('ttbm_custom_css', 'custom_css');
				ob_start();
				?>
				<style>
					<?php echo $custom_css; ?>
				</style>
				<?php
				echo ob_get_clean();
			}
		}
		new TTBM_Global_File_Load();
	}