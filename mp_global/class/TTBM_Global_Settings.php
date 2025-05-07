<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Global_Settings')) {
		class TTBM_Global_Settings {
			public function __construct() {
				add_filter('ttbm_settings_sec_reg', array($this, 'settings_sec_reg'), 10, 1);
				add_filter('ttbm_settings_sec_reg', array($this, 'global_sec_reg'), 90, 1);
				add_filter('ttbm_settings_sec_fields', array($this, 'settings_sec_fields'), 10, 1);
				add_action('wsa_form_bottom_ttbm_license_settings', [$this, 'license_settings'], 5);
				add_action('ttbm_basic_license_list', [$this, 'licence_area']);
			}
			public function settings_sec_reg($default_sec): array {
				$sections = array(
					array(
						'id' => 'ttbm_global_settings',
						'title' => esc_html__('Global Settings', 'tour-booking-manager')
					),
				);
				return array_merge($default_sec, $sections);
			}
            public function global_sec_reg($default_sec): array {
				$sections = array(
					array(
						'id' => 'ttbm_style_settings',
						'title' => esc_html__('Style Settings', 'tour-booking-manager')
					),
					array(
						'id' => 'ttbm_custom_css',
						'title' => esc_html__('Custom CSS', 'tour-booking-manager')
					),
					array(
						'id' => 'ttbm_license_settings',
						'title' => esc_html__('Mage-People License', 'tour-booking-manager')
					)
				);
				return array_merge($default_sec, $sections);
			}
			public function settings_sec_fields($default_fields): array {
				$current_date = current_time('Y-m-d');
				$settings_fields = array(
					'ttbm_global_settings' => apply_filters('filter_ttbm_global_settings', array(
						array(
							'name' => 'disable_block_editor',
							'label' => esc_html__('Disable Block/Gutenberg Editor', 'tour-booking-manager'),
							'desc' => esc_html__('If you want to disable WordPress\'s new Block/Gutenberg editor, please select Yes.', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'yes',
							'options' => array(
								'yes' => esc_html__('Yes', 'tour-booking-manager'),
								'no' => esc_html__('No', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'date_format',
							'label' => esc_html__('Date Picker Format', 'tour-booking-manager'),
							'desc' => esc_html__('If you want to change Date Picker Format, please select format. Default  is D d M , yy.', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'D d M , yy',
							'options' => array(
								'yy-mm-dd' => $current_date,
								'yy/mm/dd' => date_i18n('Y/m/d', strtotime($current_date)),
								'yy-dd-mm' => date_i18n('Y-d-m', strtotime($current_date)),
								'yy/dd/mm' => date_i18n('Y/d/m', strtotime($current_date)),
								'dd-mm-yy' => date_i18n('d-m-Y', strtotime($current_date)),
								'dd/mm/yy' => date_i18n('d/m/Y', strtotime($current_date)),
								'mm-dd-yy' => date_i18n('m-d-Y', strtotime($current_date)),
								'mm/dd/yy' => date_i18n('m/d/Y', strtotime($current_date)),
								'd M , yy' => date_i18n('j M , Y', strtotime($current_date)),
								'D d M , yy' => date_i18n('D j M , Y', strtotime($current_date)),
								'M d , yy' => date_i18n('M  j, Y', strtotime($current_date)),
								'D M d , yy' => date_i18n('D M  j, Y', strtotime($current_date)),
							)
						),
						array(
							'name' => 'date_format_short',
							'label' => esc_html__('Short Date  Format', 'tour-booking-manager'),
							'desc' => esc_html__('If you want to change Short Date  Format, please select format. Default  is M , Y.', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'M , Y',
							'options' => array(
								'D , M d' => date_i18n('D , M d', strtotime($current_date)),
								'M , Y' => date_i18n('M , Y', strtotime($current_date)),
								'M , y' => date_i18n('M , y', strtotime($current_date)),
								'M - Y' => date_i18n('M - Y', strtotime($current_date)),
								'M - y' => date_i18n('M - y', strtotime($current_date)),
								'F , Y' => date_i18n('F , Y', strtotime($current_date)),
								'F , y' => date_i18n('F , y', strtotime($current_date)),
								'F - Y' => date_i18n('F - y', strtotime($current_date)),
								'F - y' => date_i18n('F - y', strtotime($current_date)),
								'm - Y' => date_i18n('m - Y', strtotime($current_date)),
								'm - y' => date_i18n('m - y', strtotime($current_date)),
								'm , Y' => date_i18n('m , Y', strtotime($current_date)),
								'm , y' => date_i18n('m , y', strtotime($current_date)),
								'F' => date_i18n('F', strtotime($current_date)),
								'm' => date_i18n('m', strtotime($current_date)),
								'M' => date_i18n('M', strtotime($current_date)),
							)
						),
					)),
					'ttbm_style_settings' => apply_filters('filter_ttbm_style_settings', array(
						array(
							'name' => 'theme_color',
							'label' => esc_html__('Theme Color', 'tour-booking-manager'),
							'desc' => esc_html__('Select Default Theme Color', 'tour-booking-manager'),
							'type' => 'color',
							'default' => '#F12971'
						),
                        array(
                            'name' => 'theme_color_secondary',
                            'label' => esc_html__('Theme Color Secondary', 'tour-booking-manager'),
                            'desc' => esc_html__('Select Default Secondary Theme Color', 'tour-booking-manager'),
                            'type' => 'color',
                            'default' => '#3F13A4'
                        ),
						array(
							'name' => 'theme_alternate_color',
							'label' => esc_html__('Theme Alternate Color', 'tour-booking-manager'),
							'desc' => esc_html__('Select Default Theme Alternate  Color that means, if background theme color then it will be text color.', 'tour-booking-manager'),
							'type' => 'color',
							'default' => '#fff'
						),
						array(
							'name' => 'default_text_color',
							'label' => esc_html__('Default Text Color', 'tour-booking-manager'),
							'desc' => esc_html__('Select Default Text  Color.', 'tour-booking-manager'),
							'type' => 'color',
							'default' => '#303030'
						),
						// array(
						// 	'name' => 'default_font_size',
						// 	'label' => esc_html__('Default Font Size', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Default Font Size(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '15'
						// ),
						// array(
						// 	'name' => 'font_size_h1',
						// 	'label' => esc_html__('Font Size h1 Title', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size Main Title(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '35'
						// ),
						// array(
						// 	'name' => 'font_size_h2',
						// 	'label' => esc_html__('Font Size h2 Title', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size h2 Title(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '25'
						// ),
						// array(
						// 	'name' => 'font_size_h3',
						// 	'label' => esc_html__('Font Size h3 Title', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size h3 Title(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '22'
						// ),
						// array(
						// 	'name' => 'font_size_h4',
						// 	'label' => esc_html__('Font Size h4 Title', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size h4 Title(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '20'
						// ),
						// array(
						// 	'name' => 'font_size_h5',
						// 	'label' => esc_html__('Font Size h5 Title', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size h5 Title(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '18'
						// ),
						// array(
						// 	'name' => 'font_size_h6',
						// 	'label' => esc_html__('Font Size h6 Title', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size h6 Title(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '16'
						// ),
						// array(
						// 	'name' => 'button_font_size',
						// 	'label' => esc_html__('Button Font Size ', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size Button(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '18'
						// ),
						array(
							'name' => 'button_color',
							'label' => esc_html__('Button Text Color', 'tour-booking-manager'),
							'desc' => esc_html__('Select Button Text  Color.', 'tour-booking-manager'),
							'type' => 'color',
							'default' => '#FFF'
						),
						array(
							'name' => 'button_bg',
							'label' => esc_html__('Button Background Color', 'tour-booking-manager'),
							'desc' => esc_html__('Select Button Background  Color.', 'tour-booking-manager'),
							'type' => 'color',
							'default' => '#222'
						),
						// array(
						// 	'name' => 'font_size_label',
						// 	'label' => esc_html__('Label Font Size ', 'tour-booking-manager'),
						// 	'desc' => esc_html__('Type Font Size Label(in PX Unit).', 'tour-booking-manager'),
						// 	'type' => 'number',
						// 	'default' => '18'
						// ),
						array(
							'name' => 'warning_color',
							'label' => esc_html__('Warning Color', 'tour-booking-manager'),
							'desc' => esc_html__('Select Warning  Color.', 'tour-booking-manager'),
							'type' => 'color',
							'default' => '#E67C30'
						),
						array(
							'name' => 'section_bg',
							'label' => esc_html__('Section Background color', 'tour-booking-manager'),
							'desc' => esc_html__('Select Background  Color.', 'tour-booking-manager'),
							'type' => 'color',
							'default' => '#FAFCFE'
						),
					)),
					'ttbm_custom_css' => apply_filters('filter_ttbm_custom_css', array(
						array(
							'name' => 'custom_css',
							'label' => esc_html__('Custom CSS', 'tour-booking-manager'),
							'desc' => esc_html__('Write Your Custom CSS Code Here', 'tour-booking-manager'),
							'type' => 'textarea',
						)
					))
				);
				return array_merge($default_fields, $settings_fields);
			}
			public function license_settings() {
				?>
				<div class="ttbm_license_settings">
					<div>
						<?php esc_html_e('Thank you for using our plugin. Our some plugin  free and no license is required. We have some Additional addon to enhance feature of this plugin functionality. If you have any addon you need to enter a valid license for that plugin below.', 'tour-booking-manager'); ?> 
					</div>
					<?php $this->licence_area(); ?>
				</div>
				<?php
			}
			public function licence_area(){
				?>
				<table>
					<thead>
					<tr>
						<th colspan="4"><?php esc_html_e('Plugin Name', 'tour-booking-manager'); ?></th>
						<th><?php esc_html_e('Type', 'tour-booking-manager'); ?></th>
						<th><?php esc_html_e('Order No', 'tour-booking-manager'); ?></th>
						<th colspan="2"><?php esc_html_e('Expire on', 'tour-booking-manager'); ?></th>
						<th colspan="3"><?php esc_html_e('License Key', 'tour-booking-manager'); ?></th>
						<th><?php esc_html_e('Status', 'tour-booking-manager'); ?></th>
						<th colspan="2"><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php do_action('ttbm_license_page_plugin_list'); ?>
					</tbody>
				</table>
				<?php
			}
		}
		new TTBM_Global_Settings();
	}