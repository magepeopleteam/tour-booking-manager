<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists('TTBM_Settings_Global') ) {
		class TTBM_Settings_Global {
			protected $settings_api;
			public function __construct() {
				$this->settings_api = new MAGE_Setting_API;
				add_action( 'admin_menu', array( $this, 'global_settings_menu' ) );
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_filter( 'ttbm_settings_sec_reg', array( $this, 'settings_sec_reg' ), 10 );
				add_filter( 'ttbm_settings_sec_fields', array( $this, 'settings_sec_fields' ), 10 );
			}
			public function global_settings_menu() {
				$event_label = TTBM_Function::get_name();
				add_submenu_page( 'edit.php?post_type=ttbm_tour', $event_label . esc_html__( ' Settings', 'tour-booking-manager' ), $event_label . esc_html__( ' Settings', 'tour-booking-manager' ), 'manage_options', 'ttbm_settings_page', array( $this, 'settings_page' ) );
			}
			public function settings_page() {
				$plugin_data = get_plugin_data( __FILE__ );
				?>
				<div class="mp_settings_panel_header">
					<?php echo $plugin_data['Name']; ?>
					<small><?php echo $plugin_data['Version']; ?></small>
				</div>
				<div class="mp_settings_panel">
					<?php $this->settings_api->show_navigation(); ?>
					<?php $this->settings_api->show_forms(); ?>
				</div>
				<?php
			}
			public function admin_init() {
				$this->settings_api->set_sections( $this->get_settings_sections() );
				$this->settings_api->set_fields( $this->get_settings_fields() );
				$this->settings_api->admin_init();
			}
			public function get_settings_sections() {
				$sections = array();
				return apply_filters( 'ttbm_settings_sec_reg', $sections );
			}
			public function get_settings_fields() {
				$settings_fields = array();
				return apply_filters( 'ttbm_settings_sec_fields', $settings_fields );
			}
			public function settings_sec_reg( $default_sec ): array {
				$sections = array(
					array(
						'id'    => 'ttbm_basic_gen_settings',
						'title' => __( 'General Settings', 'tour-booking-manager' )
					),
					array(
						'id'    => 'ttbm_basic_translation_settings',
						'title' => __( 'Translation Settings', 'tour-booking-manager' )
					),
					array(
						'id'    => 'ttbm_basic_style_settings',
						'title' => __( 'Style Settings', 'tour-booking-manager' )
					),
					array(
						'id'    => 'ttbm_custom_css',
						'title' => __( 'Custom CSS', 'tour-booking-manager' )
					)
				);
				return array_merge( $default_sec, $sections );
			}
			public function settings_sec_fields( $default_fields ): array {
				$current_date    = current_time( 'Y-m-d' );
				$settings_fields = array(
					'ttbm_basic_gen_settings'         => apply_filters( 'ttbm_basic_gen_settings_arr', array(
						array(
							'name'    => 'ttbm_disable_block_editor',
							'label'   => esc_html__( 'Disable Block/Gutenberg Editor', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you want to disable WordPress\'s new Block/Gutenberg editor for Tour, please select Yes.', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'yes',
							'options' => array(
								'yes' => esc_html__( 'Yes', 'tour-booking-manager' ),
								'no'  => esc_html__( 'No', 'tour-booking-manager' )
							)
						),
						array(
							'name'    => 'ttbm_date_format',
							'label'   => esc_html__( 'Date Picker Format', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you want to change Date Picker Format, please select format. Default  is D d M , yy.', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'D d M , yy',
							'options' => array(
								'yy-mm-dd'   => $current_date,
								'yy/mm/dd'   => date_i18n( 'Y/m/d', strtotime( $current_date ) ),
								'yy-dd-mm'   => date_i18n( 'Y-d-m', strtotime( $current_date ) ),
								'yy/dd/mm'   => date_i18n( 'Y/d/m', strtotime( $current_date ) ),
								'dd-mm-yy'   => date_i18n( 'd-m-Y', strtotime( $current_date ) ),
								'dd/mm/yy'   => date_i18n( 'd/m/Y', strtotime( $current_date ) ),
								'mm-dd-yy'   => date_i18n( 'm-d-Y', strtotime( $current_date ) ),
								'mm/dd/yy'   => date_i18n( 'm/d/Y', strtotime( $current_date ) ),
								'd M , yy'   => date_i18n( 'j M , Y', strtotime( $current_date ) ),
								'D d M , yy' => date_i18n( 'D j M , Y', strtotime( $current_date ) ),
								'M d , yy'   => date_i18n( 'M  j, Y', strtotime( $current_date ) ),
								'D M d , yy' => date_i18n( 'D M  j, Y', strtotime( $current_date ) ),
							)
						),
						array(
							'name'    => 'ttbm_date_format_short',
							'label'   => esc_html__( 'Short Date  Format', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you want to change Short Date  Format, please select format. Default  is M , Y.', 'tour-booking-manager' ),
							'type'    => 'select',
							'default' => 'M , Y',
							'options' => array(
								'M , Y' => date_i18n( 'M , Y', strtotime( $current_date ) ),
								'M , y' => date_i18n( 'M , y', strtotime( $current_date ) ),
								'M - Y' => date_i18n( 'M - Y', strtotime( $current_date ) ),
								'M - y' => date_i18n( 'M - y', strtotime( $current_date ) ),
								'F , Y' => date_i18n( 'F , Y', strtotime( $current_date ) ),
								'F , y' => date_i18n( 'F , y', strtotime( $current_date ) ),
								'F - Y' => date_i18n( 'F - y', strtotime( $current_date ) ),
								'F - y' => date_i18n( 'F - y', strtotime( $current_date ) ),
								'm - Y' => date_i18n( 'm - Y', strtotime( $current_date ) ),
								'm - y' => date_i18n( 'm - y', strtotime( $current_date ) ),
								'm , Y' => date_i18n( 'm , Y', strtotime( $current_date ) ),
								'm , y' => date_i18n( 'm , y', strtotime( $current_date ) ),
								'F'     => date_i18n( 'F', strtotime( $current_date ) ),
								'm'     => date_i18n( 'm', strtotime( $current_date ) ),
								'M'     => date_i18n( 'M', strtotime( $current_date ) ),
							)
						),
						array(
							'name'    => 'ttbm_set_book_status',
							'label'   => esc_html__( 'Seat Booked Status', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please Select when and which order status Seat Will be Booked/Reduced.', 'tour-booking-manager' ),
							'type'    => 'multicheck',
							'default' => array(
								'processing' => 'processing',
								'completed'  => 'completed'
							),
							'options' => array(
								'on-hold'    => esc_html__( 'On Hold', 'tour-booking-manager' ),
								'pending'    => esc_html__( 'Pending', 'tour-booking-manager' ),
								'processing' => esc_html__( 'Processing', 'tour-booking-manager' ),
								'completed'  => esc_html__( 'Completed', 'tour-booking-manager' ),
							)
						),
						array(
							'name'    => 'ttbm_travel_label',
							'label'   => esc_html__( 'Travel Label', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you like to change the travel label in the dashboard menu, you can change it here.', 'tour-booking-manager' ),
							'type'    => 'text',
							'default' => 'Travel'
						),
						array(
							'name'    => 'ttbm_travel_slug',
							'label'   => esc_html__( 'Travel Slug', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please enter the slug name you want. Remember, after changing this slug; you need to flush permalink; go to', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Settings-> Permalinks', 'tour-booking-manager' ) . '</strong> ' . esc_html__( 'hit the Save Settings button.', 'tour-booking-manager' ),
							'type'    => 'text',
							'default' => 'travel'
						),
						array(
							'name'    => 'ttbm_travel_icon',
							'label'   => esc_html__( 'Travel Icon', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you want to change the travel icon in the dashboard menu, you can change it from here, and the Dashboard icon only supports the Dashicons, So please go to ', 'tour-booking-manager' ) . '<a href=https://developer.wordpress.org/resource/dashicons/#calendar-alt target=_blank>' . esc_html__( 'Dashicons Library.', 'tour-booking-manager' ) . '</a>' . esc_html__( 'and copy your icon code and paste it here.', 'tour-booking-manager' ),
							'type'    => 'text',
							'default' => 'dashicons-admin-site-alt2'
						),
						array(
							'name'    => 'ttbm_travel_cat_label',
							'label'   => esc_html__( 'Travel category Label', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you want to change the travel category label in the dashboard menu, you can change it here.', 'tour-booking-manager' ),
							'type'    => 'text',
							'default' => 'Category'
						),
						array(
							'name'    => 'ttbm_travel_cat_slug',
							'label'   => esc_html__( 'Travel Category Slug', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please enter the slug name you want for travel category. Remember after change this slug you need to flush permalink, Just go to', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Settings-> Permalinks', 'tour-booking-manager' ) . '</strong> ' . esc_html__( 'hit the Save Settings button.', 'tour-booking-manager' ),
							'type'    => 'text',
							'default' => 'travel-category'
						),
						array(
							'name'    => 'ttbm_travel_org_label',
							'label'   => esc_html__( 'Travel Organizer Label', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you want to change the travel category label in the dashboard menu you can change here', 'tour-booking-manager' ),
							'type'    => 'text',
							'default' => 'Organizer'
						),
						array(
							'name'    => 'ttbm_travel_org_slug',
							'label'   => esc_html__( 'Travel Organizer Slug', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Please enter the slug name you want for the travel organizer. Remember, after changing this slug, you need to flush the permalinks. Just go to', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Settings-> Permalinks', 'tour-booking-manager' ) . '</strong> ' . esc_html__( 'hit the Save Settings button.', 'tour-booking-manager' ),
							'type'    => 'text',
							'default' => 'travel-organizer'
						),
						array(
							'name'    => 'ttbm_expire',
							'label'   => esc_html__( 'Expired Tour both Visibility', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'If you want to visible expired tours, please select ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Yes', 'tour-booking-manager' ) . '</strong>' . esc_html__( 'or to make it hidden, select', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'No', 'tour-booking-manager' ) . '</strong>' . esc_html__( '. Default is', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'No', 'tour-booking-manager' ) . '</strong>',
							'type'    => 'select',
							'default' => 'no',
							'options' => array(
								'yes' => esc_html__( 'Yes', 'tour-booking-manager' ),
								'no'  => esc_html__( 'No', 'tour-booking-manager' )
							)
						),
						array(
							'name'        => 'ttbm_ticket_expire_time',
							'label'       => esc_html__( 'Tour Expire before Hours', 'tour-booking-manager' ),
							'desc'        => esc_html__( 'Please enter the Hour that you want attendee can not book/register the ticket before start of the Tour', 'tour-booking-manager' ),
							'type'        => 'text',
							'default'     => '0',
							'placeholder' => '15'
						),
					) ),
					'ttbm_basic_translation_settings' => apply_filters( 'ttbm_basic_translation_settings_arr', array(
						array(
							'name'    => 'ttbm_no_seat_availabe',
							'label'   => esc_html__( 'Sorry, Not Available', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Sorry, Not Available.', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' =>  esc_html__( 'Sorry, Not Available', 'tour-booking-manager' ),
						),
						array(
							'name'    => 'ttbm_string_location',
							'label'   => esc_html__( 'Location', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Location', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => esc_html__( 'Location', 'tour-booking-manager' ),
						),
						array(
							'name'    => 'ttbm_string_date',
							'label'   => esc_html__( 'Date', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Date', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_ticket_name',
							'label'   => esc_html__( 'Ticket Name', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of:', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Ticket Name', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_ticket_qty',
							'label'   => esc_html__( 'Ticket Qty', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Ticket Qty', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_ticket_price',
							'label'   => esc_html__( 'Ticket Price', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of:', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Ticket Price', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_service_name',
							'label'   => esc_html__( 'Service Name', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Service Name', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_service_qty',
							'label'   => esc_html__( 'Service Qty', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Service Qty', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_service_price',
							'label'   => esc_html__( 'Service Price', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Service Price', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_already_started',
							'label'   => esc_html__( 'Sorry, The Tour Already Started or Finished', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Sorry, The Tour Already Started or Finished', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_book_now',
							'label'   => esc_html__( 'Book Now', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Book Now', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_schedule_details',
							'label'   => esc_html__( 'Schedule Details', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Schedule Details', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_available_service_list',
							'label'   => esc_html__( 'Available Extra Service List', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Available Extra Service List', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_faq',
							'label'   => esc_html__( 'F.A.Q', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of:', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'F.A.Q', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_exclude_price_list',
							'label'   => esc_html__( "What's Excluded", 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Service Exclude', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ""
						),
						array(
							'name'    => 'ttbm_string_include_price_list',
							'label'   => esc_html__( "What's Included", 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Service Included', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ""
						),
						array(
							'name'    => 'ttbm_string_total_seats',
							'label'   => esc_html__( 'Total Seats', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Total Seats', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_activities',
							'label'   => esc_html__( 'Activities', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Activities', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_tour_location',
							'label'   => esc_html__( 'Location Map', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Location Map', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_related_tour',
							'label'   => esc_html__( 'You may like Tour', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'You may like Tour', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_choice_hotel',
							'label'   => esc_html__( 'Choice Your Hotel', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Choice Your Hotel', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_why_with_us',
							'label'   => esc_html__( 'Why Book With Us?', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Why Book With Us?', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_get_question',
							'label'   => esc_html__( 'Get a Question?', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Get a Question?', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
						array(
							'name'    => 'ttbm_string_availabe_ticket_list',
							'label'   => esc_html__( 'Available Ticket List', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Enter the translated text of: ', 'tour-booking-manager' ) . '<strong>' . esc_html__( 'Available Ticket List', 'tour-booking-manager' ) . '</stong>',
							'type'    => 'text',
							'default' => ''
						),
					) ),
					'ttbm_basic_style_settings'       => apply_filters( 'ttbm_basic_style_settings_arr', array(
						array(
							'name'    => 'ttbm_theme_color',
							'label'   => esc_html__( 'Theme Color', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Select Default Theme Color', 'tour-booking-manager' ),
							'type'    => 'color',
							'default' => '#0793C9'
						),
						array(
							'name'    => 'ttbm_theme_alternate_color',
							'label'   => esc_html__( 'Theme Alternate Color', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Select Default Theme Alternate  Color that means, if background theme color then it will be text color.', 'tour-booking-manager' ),
							'type'    => 'color',
							'default' => '#fff'
						),
						array(
							'name'    => 'ttbm_default_text_color',
							'label'   => esc_html__( 'Default Text Color', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Select Default Text  Color.', 'tour-booking-manager' ),
							'type'    => 'color',
							'default' => '#000'
						),
						array(
							'name'    => 'ttbm_default_font_size',
							'label'   => esc_html__( 'Default Font Size', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Default Font Size(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '15'
						),
						array(
							'name'    => 'ttbm_font_size_h1',
							'label'   => esc_html__( 'Font Size h1 Title', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size Main Title(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '35'
						),
						array(
							'name'    => 'ttbm_font_size_h2',
							'label'   => esc_html__( 'Font Size h2 Title', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size h2 Title(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '25'
						),
						array(
							'name'    => 'ttbm_font_size_h3',
							'label'   => esc_html__( 'Font Size h3 Title', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size h3 Title(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '22'
						),
						array(
							'name'    => 'ttbm_font_size_h4',
							'label'   => esc_html__( 'Font Size h4 Title', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size h4 Title(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '20'
						),
						array(
							'name'    => 'ttbm_font_size_h5',
							'label'   => esc_html__( 'Font Size h5 Title', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size h5 Title(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '18'
						),
						array(
							'name'    => 'ttbm_font_size_h6',
							'label'   => esc_html__( 'Font Size h6 Title', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size h6 Title(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '16'
						),
						array(
							'name'    => 'ttbm_font_size_button',
							'label'   => esc_html__( 'Button Font Size ', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size Button(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '18'
						),
						array(
							'name'    => 'ttbm_button_color',
							'label'   => esc_html__( 'Button Text Color', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Select Button Text  Color.', 'tour-booking-manager' ),
							'type'    => 'color',
							'default' => '#FFF'
						),
						array(
							'name'    => 'ttbm_button_bg',
							'label'   => esc_html__( 'Button Background Color', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Select Button Background  Color.', 'tour-booking-manager' ),
							'type'    => 'color',
							'default' => '#222'
						),
						array(
							'name'    => 'ttbm_font_size_label',
							'label'   => esc_html__( 'Label Font Size ', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Type Font Size Label(in PX Unit).', 'tour-booking-manager' ),
							'type'    => 'number',
							'default' => '18'
						),
						array(
							'name'    => 'ttbm_warning_color',
							'label'   => esc_html__( 'Warning Color', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Select Warning  Color.', 'tour-booking-manager' ),
							'type'    => 'color',
							'default' => '#E67C30'
						),
						array(
							'name'    => 'ttbm_section_bg',
							'label'   => esc_html__( 'Section Background color', 'tour-booking-manager' ),
							'desc'    => esc_html__( 'Select Background  Color.', 'tour-booking-manager' ),
							'type'    => 'color',
							'default' => '#FAFCFE'
						),
					) ),
					'ttbm_basic_custom_css_settings'  => apply_filters( 'ttbm_basic_custom_css_settings_arr', array() ),
					'ttbm_custom_css'                 => array(
						array(
							'name'  => 'custom_css',
							'label' => esc_html__( 'Custom CSS', 'tour-booking-manager' ),
							'desc'  => esc_html__( 'Write Your Custom CSS Code Here', 'tour-booking-manager' ),
							'type'  => 'textarea',
						)
					),
					'ttbm_basic_license_settings'     => apply_filters( 'ttbm_basic_license_settings_arr', array() ),
					'ttbm_license'                    => array()
				);
				return array_merge( $default_fields, $settings_fields );
			}
		}
		new  TTBM_Settings_Global();
	}