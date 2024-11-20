<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Global')) {
		class TTBM_Settings_Global {
			protected $settings_api;
			public function __construct() {
				$this->settings_api = new MAGE_Setting_API;
				add_action('admin_menu', array($this, 'global_settings_menu'));
				add_action('admin_init', array($this, 'admin_init'));
				add_filter('mp_settings_sec_reg', array($this, 'settings_sec_reg'), 30);
				add_filter('mp_settings_sec_reg', array($this, 'slider_sec_reg'), 80);
				add_filter('mp_settings_sec_fields', array($this, 'settings_sec_fields'), 30);
			}
			public function global_settings_menu() {
				$label = TTBM_Function::get_name();
				add_submenu_page('edit.php?post_type=ttbm_tour', $label . esc_html__(' Settings', 'tour-booking-manager'), $label . esc_html__(' Settings', 'tour-booking-manager'), 'manage_options', 'ttbm_settings_page', array($this, 'settings_page'));
			}
			public function settings_page() {
				?>
                <div class="mpStyle mp_global_settings">
                    <div class="mpPanel">
                        <div class="mpPanelHeader"><?php echo esc_html(esc_html__(' Global Settings', 'tour-booking-manager')); ?></div>
                        <div class="mpPanelBody mp_zero">
                            <div class="mpTabs leftTabs">
								<?php $this->settings_api->show_navigation(); ?>
                                <div class="tabsContent">
									<?php $this->settings_api->show_forms(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function admin_init() {
				$this->settings_api->set_sections($this->get_settings_sections());
				$this->settings_api->set_fields($this->get_settings_fields());
				$this->settings_api->admin_init();
			}
			public function get_settings_sections() {
				$sections = array();
				return apply_filters('mp_settings_sec_reg', $sections);
			}
			public function get_settings_fields() {
				$settings_fields = array();
				return apply_filters('mp_settings_sec_fields', $settings_fields);
			}
			public function settings_sec_reg($default_sec): array {
				$label = TTBM_Function::get_name();
				$sections = array(
					array(
						'id' => 'ttbm_basic_gen_settings',
						'title' => $label . ' ' . __('Settings', 'tour-booking-manager')
					),
					array(
						'id' => 'ttbm_basic_translation_settings',
						'title' => $label . ' ' . __('Translation Settings', 'tour-booking-manager')
					)
				);
				return array_merge($default_sec, $sections);
			}
			public function slider_sec_reg($default_sec): array {
				$sections = array(
					array(
						'id' => 'mp_slider_settings',
						'title' => __('Slider Settings', 'tour-booking-manager')
					)
				);
				return array_merge($default_sec, $sections);
			}
			public function settings_sec_fields($default_fields): array {
				$settings_fields = array(
					'ttbm_basic_gen_settings' => apply_filters('ttbm_basic_gen_settings_arr', array(
						array(
							'name' => 'ttbm_set_book_status',
							'label' => esc_html__('Seat Booked Status', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select when and which order status Seat Will be Booked/Reduced.', 'tour-booking-manager'),
							'type' => 'multicheck',
							'default' => array(
								'processing' => 'processing',
								'completed' => 'completed'
							),
							'options' => array(
								'on-hold' => esc_html__('On Hold', 'tour-booking-manager'),
								'pending' => esc_html__('Pending', 'tour-booking-manager'),
								'processing' => esc_html__('Processing', 'tour-booking-manager'),
								'completed' => esc_html__('Completed', 'tour-booking-manager'),
							)
						),
						array(
							'name' => 'ttbm_travel_label',
							'label' => esc_html__('Travel Label', 'tour-booking-manager'),
							'desc' => esc_html__('If you like to change the travel label in the dashboard menu, you can change it here.', 'tour-booking-manager'),
							'type' => 'text',
							'default' => 'Travel'
						),
						array(
							'name' => 'ttbm_travel_slug',
							'label' => esc_html__('Travel Slug', 'tour-booking-manager'),
							'desc' => esc_html__('Please enter the slug name you want. Remember, after changing this slug; you need to flush permalink; go to', 'tour-booking-manager') . '<strong>' . esc_html__('Settings-> Permalinks', 'tour-booking-manager') . '</strong> ' . esc_html__('hit the Save Settings button.', 'tour-booking-manager'),
							'type' => 'text',
							'default' => 'travel'
						),
						array(
							'name' => 'ttbm_travel_icon',
							'label' => esc_html__('Travel Icon', 'tour-booking-manager'),
							'desc' => esc_html__('If you want to change the travel icon in the dashboard menu, you can change it from here, and the Dashboard icon only supports the Dashicons, So please go to ', 'tour-booking-manager') . '<a href=https://developer.wordpress.org/resource/dashicons/#calendar-alt target=_blank>' . esc_html__('Dashicons Library.', 'tour-booking-manager') . '</a>' . esc_html__('and copy your icon code and paste it here.', 'tour-booking-manager'),
							'type' => 'text',
							'default' => 'dashicons-admin-site-alt2'
						),
						array(
							'name' => 'ttbm_travel_cat_label',
							'label' => esc_html__('Travel category Label', 'tour-booking-manager'),
							'desc' => esc_html__('If you want to change the travel category label in the dashboard menu, you can change it here.', 'tour-booking-manager'),
							'type' => 'text',
							'default' => 'Category'
						),
						array(
							'name' => 'ttbm_travel_cat_slug',
							'label' => esc_html__('Travel Category Slug', 'tour-booking-manager'),
							'desc' => esc_html__('Please enter the slug name you want for travel category. Remember after change this slug you need to flush permalink, Just go to', 'tour-booking-manager') . '<strong>' . esc_html__('Settings-> Permalinks', 'tour-booking-manager') . '</strong> ' . esc_html__('hit the Save Settings button.', 'tour-booking-manager'),
							'type' => 'text',
							'default' => 'travel-category'
						),
						array(
							'name' => 'ttbm_travel_org_label',
							'label' => esc_html__('Travel Organizer Label', 'tour-booking-manager'),
							'desc' => esc_html__('If you want to change the travel category label in the dashboard menu you can change here', 'tour-booking-manager'),
							'type' => 'text',
							'default' => 'Organizer'
						),
						array(
							'name' => 'ttbm_travel_org_slug',
							'label' => esc_html__('Travel Organizer Slug', 'tour-booking-manager'),
							'desc' => esc_html__('Please enter the slug name you want for the travel organizer. Remember, after changing this slug, you need to flush the permalinks. Just go to', 'tour-booking-manager') . '<strong>' . esc_html__('Settings-> Permalinks', 'tour-booking-manager') . '</strong> ' . esc_html__('hit the Save Settings button.', 'tour-booking-manager'),
							'type' => 'text',
							'default' => 'travel-organizer'
						),
						array(
							'name' => 'ttbm_expire',
							'label' => esc_html__('Expired Tour both Visibility', 'tour-booking-manager'),
							'desc' => esc_html__('If you want to visible expired tours, please select ', 'tour-booking-manager') . '<strong>' . esc_html__('Yes', 'tour-booking-manager') . '</strong>' . esc_html__('or to make it hidden, select', 'tour-booking-manager') . '<strong>' . esc_html__('No', 'tour-booking-manager') . '</strong>' . esc_html__('. Default is', 'tour-booking-manager') . '<strong>' . esc_html__('No', 'tour-booking-manager') . '</strong>',
							'type' => 'select',
							'default' => 'no',
							'options' => array(
								'yes' => esc_html__('Yes', 'tour-booking-manager'),
								'no' => esc_html__('No', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'ttbm_ticket_expire_time',
							'label' => esc_html__('Tour Expire before Hours', 'tour-booking-manager'),
							'desc' => esc_html__('Please enter the Hour that you want attendee can not book/register the ticket before start of the Tour', 'tour-booking-manager'),
							'type' => 'text',
							'default' => '0',
							'placeholder' => '15'
						),
					)),
					'ttbm_basic_translation_settings' => apply_filters('ttbm_basic_translation_settings_arr', array(
						array(
							'name' => 'ttbm_no_seat_availabe',
							'label' => esc_html__('Sorry, Not Available', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Sorry, Not Available.', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => esc_html__('Sorry, Not Available', 'tour-booking-manager'),
						),
						array(
							'name' => 'ttbm_string_location',
							'label' => esc_html__('Location', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Location', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => esc_html__('Location', 'tour-booking-manager'),
						),
						array(
							'name' => 'ttbm_string_date',
							'label' => esc_html__('Date', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Date', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_ticket_name',
							'label' => esc_html__('Ticket Name', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of:', 'tour-booking-manager') . '<strong>' . esc_html__('Ticket Name', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_ticket_qty',
							'label' => esc_html__('Ticket Qty', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Ticket Qty', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_ticket_price',
							'label' => esc_html__('Ticket Price', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of:', 'tour-booking-manager') . '<strong>' . esc_html__('Ticket Price', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_service_name',
							'label' => esc_html__('Service Name', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Service Name', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_service_qty',
							'label' => esc_html__('Service Qty', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Service Qty', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_service_price',
							'label' => esc_html__('Service Price', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Service Price', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_already_started',
							'label' => esc_html__('Sorry, The Tour Already Started or Finished', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Sorry, The Tour Already Started or Finished', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_book_now',
							'label' => esc_html__('Book Now', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Book Now', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_schedule_details',
							'label' => esc_html__('Schedule Details', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Schedule Details', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_available_service_list',
							'label' => esc_html__('Available Extra Service List', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Available Extra Service List', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_faq',
							'label' => esc_html__('F.A.Q', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of:', 'tour-booking-manager') . '<strong>' . esc_html__('F.A.Q', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_exclude_price_list',
							'label' => esc_html__("What's Excluded", 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Service Exclude', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ""
						),
						array(
							'name' => 'ttbm_string_include_price_list',
							'label' => esc_html__("What's Included", 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Service Included', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ""
						),
						array(
							'name' => 'ttbm_string_total_seats',
							'label' => esc_html__('Total Seats', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Total Seats', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_activities',
							'label' => esc_html__('Activities', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Activities', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_tour_location',
							'label' => esc_html__('Location Map', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Location Map', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_related_tour',
							'label' => esc_html__('You may like Tour', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('You may like Tour', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_choice_hotel',
							'label' => esc_html__('Choice Your Hotel', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Choice Your Hotel', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_why_with_us',
							'label' => esc_html__('Why Book With Us?', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Why Book With Us?', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_get_question',
							'label' => esc_html__('Get a Question?', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Get a Question?', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
						array(
							'name' => 'ttbm_string_availabe_ticket_list',
							'label' => esc_html__('Available Ticket List', 'tour-booking-manager'),
							'desc' => esc_html__('Enter the translated text of: ', 'tour-booking-manager') . '<strong>' . esc_html__('Available Ticket List', 'tour-booking-manager') . '</stong>',
							'type' => 'text',
							'default' => ''
						),
					)),
					'mp_slider_settings' => array(
						array(
							'name' => 'slider_type',
							'label' => esc_html__('Slider Type', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Type Default Slider', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'slider',
							'options' => array(
								'slider' => esc_html__('Slider', 'tour-booking-manager'),
								'single_image' => esc_html__('Post Thumbnail', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'slider_style',
							'label' => esc_html__('Slider Style', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Style Default Style One', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'style_1',
							'options' => array(
								'style_1' => esc_html__('Style One', 'tour-booking-manager'),
								'style_2' => esc_html__('Style Two', 'tour-booking-manager'),
							)
						),
						array(
							'name' => 'indicator_visible',
							'label' => esc_html__('Slider Indicator Visible?', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Indicator Visible or Not? Default ON', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'tour-booking-manager'),
								'off' => esc_html__('Off', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'indicator_type',
							'label' => esc_html__('Slider Indicator Type', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Indicator Type Default Icon', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'icon',
							'options' => array(
								'icon' => esc_html__('Icon Indicator', 'tour-booking-manager'),
								'image' => esc_html__('image Indicator', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'showcase_visible',
							'label' => esc_html__('Slider Showcase Visible?', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Showcase Visible or Not? Default ON', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'tour-booking-manager'),
								'off' => esc_html__('Off', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'showcase_position',
							'label' => esc_html__('Slider Showcase Position', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Showcase Position Default Right', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'right',
							'options' => array(
								'top' => esc_html__('At Top Position', 'tour-booking-manager'),
								'right' => esc_html__('At Right Position', 'tour-booking-manager'),
								'bottom' => esc_html__('At Bottom Position', 'tour-booking-manager'),
								'left' => esc_html__('At Left Position', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'popup_image_indicator',
							'label' => esc_html__('Slider Popup Image Indicator', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Popup Indicator Image ON or Off? Default ON', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'tour-booking-manager'),
								'off' => esc_html__('Off', 'tour-booking-manager')
							)
						),
						array(
							'name' => 'popup_icon_indicator',
							'label' => esc_html__('Slider Popup Icon Indicator', 'tour-booking-manager'),
							'desc' => esc_html__('Please Select Slider Popup Indicator Icon ON or Off? Default ON', 'tour-booking-manager'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'tour-booking-manager'),
								'off' => esc_html__('Off', 'tour-booking-manager')
							)
						)
					),
				);
				return array_merge($default_fields, $settings_fields);
			}
		}
		new  TTBM_Settings_Global();
	}