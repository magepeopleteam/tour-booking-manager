<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Global_Style')) {
		class TTBM_Global_Style {
			public function __construct() {
				add_action('wp_head', array($this, 'add_global_style'), 100);
				add_action('admin_head', array($this, 'add_global_style'), 100);
			}
			public function add_global_style() {
				$default_color = TTBM_Global_Function::get_style_settings('default_text_color', '#303030');
				$theme_color = TTBM_Global_Function::get_style_settings('theme_color', '#6b35e8');
				$theme_color_secondary = TTBM_Global_Function::get_style_settings('theme_color_secondary', '#3F13A4');
				$theme_color_rgb = $this->hexToRgb($theme_color);
				$alternate_color = TTBM_Global_Function::get_style_settings('theme_alternate_color', '#fff');
				$warning_color = TTBM_Global_Function::get_style_settings('warning_color', '#E67C30');
				$button_color = TTBM_Global_Function::get_style_settings('button_color', $alternate_color);
				$button_bg = TTBM_Global_Function::get_style_settings('button_bg', '#ea8125');
				$section_bg = TTBM_Global_Function::get_style_settings('section_bg', '#FAFCFE');
				?>
                <style>
					:root {
						--dcontainer_width: 1320px;
						--sidebarleft: 280px;
						--sidebarright: 300px;
						--mainsection: calc(100% - 300px);
						--ttbm_mpl: 40px;
						--ttbm_mp: 20px;
						--ttbm_mp_negetive: -20px;
						--ttbm_mp_xs: 10px;
						--ttbm_mp_xs_negative: -10px;
						--dbrl: 10px;
						--dbr: 5px;
						--dbr_d: 8px;
						--dshadow: 0 0 2px #665F5F7A;
					}
					/*****Font size********/
					:root {
						--ttbm_fs_small: 12px;
						--ttbm_fs: 14px;
						--ttbm_fs_label: 16px;
						--ttbm_fs_h6: 16px;
						--ttbm_fs_h5: 18px;
						--ttbm_fs_h4: 22px;
						--ttbm_fs_h3: 25px;
						--ttbm_fs_h2: 30px;
						--ttbm_fs_h1: 35px;
						--fw: normal;
						--fw-thin: 300; /*font weight medium*/
						--fw-normal: 500; /*font weight medium*/
						--fw-medium: 600; /*font weight medium*/
						--fw-bold: bold; /*font weight bold*/
					}
					/*****Button********/
					:root {
						--button_bg: <?php echo esc_attr($button_bg); ?>;
						--color_button: <?php echo esc_attr($button_color); ?>;
						--button_height: 40px;
						--button_height_xs: 30px;
						--button_width: 120px;
						--button_shadows: 0 8px 12px rgb(51 65 80 / 6%), 0 14px 44px rgb(51 65 80 / 11%);
					}
					/*******Color***********/
					:root {
						--d_color: <?php echo esc_attr($default_color); ?>;
						--color_border: #DDD;
						--color_active: #0E6BB7;
						--color_section: <?php echo esc_attr($section_bg); ?>;
						--color_theme: <?php echo esc_attr($theme_color); ?>;
						--color_theme_rgb: <?php echo esc_attr($theme_color_rgb); ?>;
						--color_theme_secondary: <?php echo esc_attr($theme_color_secondary); ?>;
						--color_theme_rbg: <?php echo esc_attr($theme_color_rgb); ?>;
						--color_theme_ee: <?php echo esc_attr($theme_color).'ee'; ?>;
						--color_theme_cc: <?php echo esc_attr($theme_color).'cc'; ?>;
						--color_theme_aa: <?php echo esc_attr($theme_color).'aa'; ?>;
						--color_theme_88: <?php echo esc_attr($theme_color).'88'; ?>;
						--color_theme_77: <?php echo esc_attr($theme_color).'77'; ?>;
						--color_theme_alter: <?php echo esc_attr($alternate_color); ?>;
						--color_warning: <?php echo esc_attr($warning_color); ?>;
						--color_black: #1b2021;
						--color_success: #00A656;
						--color_danger: #C00;
						--color_required: #C00;
						--color_white: #FFFFFF;
						--color_light: #F2F2F2;
						--color_light_1: #BBB;
						--color_light_2: #EAECEE;
						--color_light_3: #878787;
						--color_light_4: #f9f9f9;
						--color_info: #666;
						--color_yellow: #FEBB02;
						--color_blue: #815DF2;
						--color_navy_blue: #007CBA;
						--color_1: #0C5460;
						--color_2: #caf0ffcc;
						--color_3: #FAFCFE;
						--color_4: #6148BA;
						--color_5: #BCB;
					}
					@media only screen and (max-width: 1024px) {
						:root {
							--ttbm_fs_small: 11px;
							--ttbm_fs: 13px;
							--ttbm_fs_label: 15px;
							--ttbm_fs_h6: 15px;
							--ttbm_fs_h5: 16px;
							--ttbm_fs_h4: 20px;
							--ttbm_fs_h3: 22px;
							--ttbm_fs_h2: 25px;
							--ttbm_fs_h1: 30px;
							--ttbm_mpl: 32px;
							--ttbm_mp: 16px;
							--ttbm_mp_negetive: -16px;
							--ttbm_mp_xs: 8px;
							--ttbm_mp_xs_negative: -8px;
						}
					}
					@media only screen and (max-width: 700px) {
						:root {
							--ttbm_fs_small: 10px;
							--ttbm_fs: 12px;
							--ttbm_fs_label: 13px;
							--ttbm_fs_h6: 15px;
							--ttbm_fs_h5: 16px;
							--ttbm_fs_h4: 18px;
							--ttbm_fs_h3: 20px;
							--ttbm_fs_h2: 22px;
							--ttbm_fs_h1: 24px;
							--ttbm_mp: 10px;
							--ttbm_mp_xs: 5px;
							--ttbm_mp_xs_negative: -5px;
							--ttbm_buttton_fs: 14px;
						}
					}
                </style>
				<?php
			}
			public function hexToRgb($hex) {
				$hex = str_replace("#", "", $hex);
				if (strlen($hex) == 3) {
					$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
					$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
					$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
				} else {
					$r = hexdec(substr($hex, 0, 2));
					$g = hexdec(substr($hex, 2, 2));
					$b = hexdec(substr($hex, 4, 2));
				}
				return $r . ',' . $g . ',' . $b;
			}
		}
		new TTBM_Global_Style();
	}