<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings')) {
		class TTBM_Settings {
			public function __construct() {
				add_action('add_meta_boxes', [$this, 'settings_meta']);
				add_action('admin_init', [$this, 'tour_settings_meta_box'], 10);
				add_action('save_post', array($this, 'save_settings'), 99, 1);
			}
			//************************//
			public function settings_meta() {
				$label = TTBM_Function::get_name();
				add_meta_box('ttbm_meta_box_panel', '' . $label . esc_html__(' Information Settings : ', 'tour-booking-manager') . get_the_title(get_the_id()), array($this, 'settings'), 'ttbm_tour', 'normal', 'high');
			}
			//******************************//
			public function settings() {
				$tour_id = get_the_id();
				?>
                <div id="ttbm-settings-page">
                    <div class="ttbm_style ttbm_settings">
                        <div class="ttbmTabs leftTabs d-flex justify-content-between">
                            <ul class="tabLists">
                                <li data-tabs-target="#ttbm_general_info">
                                    <i class="fas fa-tools"></i><?php esc_html_e('General Info', 'tour-booking-manager'); ?>
                                </li>
								<?php do_action('ttbm_meta_box_tab_name', $tour_id); ?>
								<?php do_action('add_ttbm_settings_tab_name'); ?>
								<?php if (is_plugin_active('mage-partial-payment-pro/mage_partial_pro.php')) : ?>
                                    <li data-tabs-target="#_mep_pp_deposits_type">
                                        <i class="far fa-money-bill-alt"></i>&nbsp;&nbsp;<?php esc_html_e('Partial Payment', 'tour-booking-manager'); ?>
                                    </li>
								<?php endif; ?>
                            </ul>
                            <div class="tabsContent">
								<?php
									do_action('add_ttbm_settings_tab_content', $tour_id);
									do_action('ttbm_meta_box_tab_content', $tour_id);
									$this->partial_payment_settings($tour_id);
								?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			//************************//
			public function tour_settings_meta_box() {
				$tour_label = TTBM_Function::get_name();
				$ttbm_date_info_boxs = [
					'page_nav' => esc_html__('Date Configuration', 'tour-booking-manager'),
					'priority' => 10,
					'sections' => [
						'section_2' => [
							'title' => esc_html__('Date Configuration', 'tour-booking-manager'),
							'description' => esc_html__('Tour type and date time can be easily configured here, providing a crucial feature for recurring, fixed, or specific date tours.', 'tour-booking-manager'),
							'options' => apply_filters('ttbm_date_info_boxs_meta_box', [
								[
									'id' => 'ttbm_travel_type',
									'title' => $tour_label . esc_html__(' Type', 'tour-booking-manager'),
									'details' => esc_html__('Please Select the Type', 'tour-booking-manager'),
									'type' => 'select',
									'class' => 'omg',
									'default' => 'fixed',
									'args' => TTBM_Function::travel_type_array(),
								],
								[
									'id' => 'ttbm_travel_start_date',
									'title' => esc_html__(' Check In Date', 'tour-booking-manager'),
									'details' => esc_html__('Please Select the Start Date', 'tour-booking-manager'),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default' => '', // today date
									'type' => 'datepicker',
								],
								[
									'id' => 'ttbm_travel_start_date_time',
									'title' => esc_html__(' Check in Time', 'tour-booking-manager'),
									'details' => esc_html__('Please Select the Start Time', 'tour-booking-manager'),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default' => '', // today date
									'type' => 'time',
								],
								[
									'id' => 'ttbm_travel_end_date',
									'title' => esc_html__(' Check out Date', 'tour-booking-manager'),
									'details' => esc_html__('Please Enter the End Date', 'tour-booking-manager'),
									'type' => 'datepicker',
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default' => '', // today date
								],
								[
									'id' => 'ttbm_travel_reg_end_date',
									'title' => esc_html__(' Registration End Date', 'tour-booking-manager'),
									'details' => esc_html__('Please Select the Registration End Date', 'tour-booking-manager'),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default' => '', // today date
									'type' => 'datepicker',
								],
								[
									'id' => 'ttbm_particular_dates',
									'title' => esc_html__('Dates', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => esc_html__('Please Enter All Dates', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'ttbm_particular_start_date',
									'btn_text' => esc_html__('Add New Particular Date & Time', 'tour-booking-manager'),
									'fields' => array(array('type' => 'date', 'args' => '', 'default' => 'option_1', 'item_id' => 'ttbm_particular_start_date', 'name' => esc_html__('Check in Date', 'tour-booking-manager'),), array('type' => 'time', 'args' => '', 'default' => 'option_1', 'item_id' => 'ttbm_particular_start_time', 'name' => __('Check in Time', 'tour-booking-manager'),), array('type' => 'date', 'args' => '', 'default' => 'option_1', 'item_id' => 'ttbm_particular_end_date', 'name' => __('Check out Date', 'tour-booking-manager'),)),
								],
								[
									'id' => 'ttbm_travel_repeated_start_date',
									'title' => __(' First Tour Date of Recurring Tour', 'tour-booking-manager'),
									'details' => __('Please Select the First Tour Date of Recurring Tour span', 'tour-booking-manager'),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default' => '',
									// today date
									'type' => 'datepicker',
								],
								[
									'id' => 'ttbm_travel_repeated_end_date',
									'title' => __(' Last Tour Date of Recurring Tour', 'tour-booking-manager'),
									'details' => __('Please Select the Last Tour Date of Recurring Tour span', 'tour-booking-manager'),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default' => '',
									// today date
									'type' => 'datepicker',
								],
								array(
									'id' => 'ttbm_travel_repeated_after',
									'title' => __(' Repeated After', 'tour-booking-manager'),
									'details' => __('Please Enter the Duration of Repeat', 'tour-booking-manager'),
									'type' => 'text',
									'default' => '',
									'placeholder' => __('3', 'tour-booking-manager'),
								),
								array(
									'id' => 'mep_disable_ticket_time',
									'title' => __('Display Time?', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('If you want to display time please check this Yes', 'tour-booking-manager'),
									'type' => 'checkbox',
									'default' => '',
									'args' => array('yes' => __('Yes', 'tour-booking-manager')),
								),
								array(
									'id' => 'mep_ticket_times_global',
									'title' => __('Default Times', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time Default/Global Time',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'args' => '', 'default' => 'option_1', 'item_id' => 'mep_ticket_time', 'name' => 'Time',)),
								),
								array(
									'id' => 'mep_ticket_times_sat',
									'title' => __('Saturday Ticket Time', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time Fro Saturday',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'args' => '', 'default' => 'option_1', 'item_id' => 'mep_ticket_time', 'name' => 'Time',),),
								),
								array(
									'id' => 'mep_ticket_times_sun',
									'title' => __('Sunday Ticket Time', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time Fro Sunday',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'args' => '', 'default' => 'option_1', 'item_id' => 'mep_ticket_time', 'name' => 'Time',)),
								),
								array(
									'id' => 'mep_ticket_times_mon',
									'title' => __('Monday Ticket Time', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time For Monday',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'args' => '', 'default' => 'option_1', 'item_id' => 'mep_ticket_time', 'name' => 'Time',)),
								),
								array(
									'id' => 'mep_ticket_times_tue',
									'title' => __('Tuesday Ticket Time', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time For Tuesday',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'default' => 'option_1', 'args' => '', 'item_id' => 'mep_ticket_time', 'name' => 'Time',)),
								),
								array(
									'id' => 'mep_ticket_times_wed',
									'title' => __('Wednesday Ticket Time', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time For Wednesday',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'default' => 'option_1', 'args' => '', 'item_id' => 'mep_ticket_time', 'name' => 'Time',)),
								),
								array(
									'id' => 'mep_ticket_times_thu',
									'title' => __('Thursday Ticket Time', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time For Thursday',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'default' => 'option_1', 'args' => '', 'item_id' => 'mep_ticket_time', 'name' => 'Time',)),
								),
								array(
									'id' => 'mep_ticket_times_fri',
									'title' => __('Friday Ticket Time', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please Enter Add Ticket Times', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text' => 'Add New Time for Friday',
									'fields' => array(array('type' => 'text', 'args' => '', 'default' => '', 'item_id' => 'mep_ticket_time_name', 'name' => 'Time Slot Label',), array('type' => 'time', 'default' => '', 'args' => '', 'item_id' => 'mep_ticket_time', 'name' => 'Time',)),
								),
								array(
									'id' => 'mep_ticket_offdays',
									'title' => __('Ticket Offdays', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('Please select the offday days. Ticket will be not available on the selected days', 'tour-booking-manager'),
									'type' => 'select2',
									'class' => 'ttbm_select2',
									'default' => '',
									'multiple' => true,
									'args' => array('sun' => __('Sunday', 'tour-booking-manager'), 'mon' => __('Monday', 'tour-booking-manager'), 'tue' => __('Tuesday', 'tour-booking-manager'), 'wed' => __('Wednesday', 'tour-booking-manager'), 'thu' => __('Thursday', 'tour-booking-manager'), 'fri' => __('Friday', 'tour-booking-manager'), 'sat' => __('Saturday', 'tour-booking-manager'),),
								),
								array(
									'id' => 'mep_ticket_off_dates',
									'title' => __('Ticket Off Dates List', 'tour-booking-manager') . TTBM_Layout::pro_text(),
									'details' => __('If you want to off selling ticket on particular dates please select them', 'tour-booking-manager'),
									'collapsible' => true,
									'type' => 'repeatable',
									'title_field' => 'mep_ticket_off_date',
									'btn_text' => 'Add New Off Date',
									'fields' => array(array('type' => 'date', 'default' => 'option_1', 'args' => '', 'item_id' => 'mep_ticket_off_date', 'name' => 'OffDate',)),
								)
							])
						],
					],
				];
				$ttbm_date_config_boxs_args = ['meta_box_id' => 'ttbm_travel_date_config_meta_boxes',
					'meta_box_title' => '<i class="far fa-calendar-plus"></i>' . __('Date Configuration', 'tour-booking-manager'), 'screen' => [TTBM_Function::get_cpt_name()], 'context' => 'normal', 'priority' => 'high', 'callback_args' => [], 'nav_position' => 'none', 'item_name' => "MagePeople", 'item_version' => "2.0", 'panels' => ['ttbm_date_config_meta_boxs' => $ttbm_date_info_boxs]];
				new TtbmAddMetaBox($ttbm_date_config_boxs_args);
				$ttbm_tax_meta_boxs = [
					'page_nav' => $tour_label . __('Tax', 'tour-booking-manager'),
					'priority' => 10,
					'sections' => [
						'section_2' => [
							'title' => __('Tax Settings', 'tour-booking-manager'),
							'description' => '',
							'options' => [
								[
									'id' => '_tax_status',
									'title' => $tour_label . __(' Tax Status', 'tour-booking-manager'),
									'details' => __('Please Select Tax Status', 'tour-booking-manager'),
									'type' => 'select',
									'class' => 'omg',
									'default' => 'taxable',
									'args' => [
										'taxable' => __('Taxable', 'tour-booking-manager'),
										'shipping' => __('Shipping only', 'tour-booking-manager'),
										'none' => __('None', 'tour-booking-manager')
									]
								],
								[
									'id' => '_tax_class',
									'title' => $tour_label . __(' Tax Class', 'tour-booking-manager'),
									'details' => __('Please Select Tax Class', 'tour-booking-manager'),
									'type' => 'select',
									'class' => 'omg',
									'default' => 'none',
									'args' => TTBM_Global_Function::all_tax_list()
								],
							]
						],
					],
				];
				$ttbm_tax_meta_boxs_args = [
					'meta_box_id' => 'ttbm_tax_meta_boxes',
					'meta_box_title' => '<i class="fas fa-money-bill-wave"></i> ' . __('Tax', 'tour-booking-manager'),
					'screen' => [TTBM_Function::get_cpt_name()],
					'context' => 'normal',
					'priority' => 'low',
					'callback_args' => [],
					'nav_position' => 'none', // right, top, left, none
					'item_name' => "MagePeople",
					'item_version' => "2.0",
					'panels' => ['ttbm_tax_meta_boxs' => $ttbm_tax_meta_boxs],
				];
				if (get_option('woocommerce_calc_taxes') == 'yes') {
					new TtbmAddMetaBox($ttbm_tax_meta_boxs_args);
				}
			}
			//********* Display settings*************//
			public function partial_payment_settings($tour_id) {
				$values = get_post_custom($tour_id);
				echo '<div class="tabsItem" data-tabs="#_mep_pp_deposits_type">';
				do_action('wcpp_partial_product_settings', $values);
				echo '</div>';
			}
			//******************************//
			public static function des_array($key) {
				$des = array(
					'ttip_start_price' => esc_html__('If you would like to hide them, you can do so by switching the option.', 'tour-booking-manager'),
					'start_price' => esc_html__('Price Starts  are displayed on the tour details and tour list pages.', 'tour-booking-manager'),
					'ttip_max_people' => esc_html__('This number is displayed for informational purposes only and can be hidden by switching the option.', 'tour-booking-manager'),
					'max_people' => esc_html__('This tour only allows a maximum of X people', 'tour-booking-manager'),
					'age_range' => esc_html__('The age limit for this tour is X to Y years old. This is for information purposes only.', 'tour-booking-manager'),
					'start_place' => esc_html__('This will be the starting point for the tour group. The tour will begin from here.', 'tour-booking-manager'),
					'location' => esc_html__('Please select the name of the location you wish to create a tour.', 'tour-booking-manager'),
					'full_location' => esc_html__('Please Type Full Address of the location, it will use for the google map', 'tour-booking-manager'),
					'short_des' => esc_html__('For a Tour short description, toggle this switching option.', 'tour-booking-manager'),
					'duration' => esc_html__('Please enter the number of days and nights for your tour package.', 'tour-booking-manager'),
					'ttbm_new_location_name' => esc_html__('Please add the new location to the location list when creating a tour.', 'tour-booking-manager'),
					'ttbm_location_description' => esc_html__('The description is not always visible by default, but some themes may display it.', 'tour-booking-manager'),
					'ttbm_location_address' => esc_html__('Please Enter the Full Address of Your Location', 'tour-booking-manager'),
					'ttbm_location_country' => esc_html__('Please select your tour location country from the list below.', 'tour-booking-manager'),
					'ttbm_location_image' => esc_html__('Please select an image for your tour location.', 'tour-booking-manager'),
					'ttbm_display_registration' => esc_html__("If you don't want to use the tour registration feature, you can just keep it turned off.", 'tour-booking-manager'),
					'ttbm_short_code' => esc_html__('You can display this Ticket type list with the add to cart button anywhere.', 'tour-booking-manager'),
					'ttip_short_code' => esc_html__('Copy the shortcode and paste into any post or page', 'tour-booking-manager'),
					'ttbm_display_schedule' => esc_html__('Please find the detailed timeline for you tour as day 1, day 2 etc.', 'tour-booking-manager'),
					'add_new_feature_popup' => esc_html__('Add include/exclude features here', 'tour-booking-manager'),
					'ttip_add_new_feature_popup' => esc_html__('To include or exclude a feature from your tour, please select it from the list below. To create a new feature, go to the Tour page.', 'tour-booking-manager'),
					'ttbm_display_include_service' => esc_html__('The price of this tour includes the service, which you can keep hidden by turning it off.', 'tour-booking-manager'),
					'ttbm_display_exclude_service' => esc_html__('The price of this tour excludes the service, which you can keep hidden by turning it off.', 'tour-booking-manager'),
					'ttbm_feature_name' => esc_html__('The name is how it appears on your site.', 'tour-booking-manager'),
					'ttbm_feature_description' => esc_html__('The description is not prominent by default; however, some themes may show it.', 'tour-booking-manager'),
					'ttbm_display_hiphop' => esc_html__('By default Places You\'ll See  is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_place_you_see' => esc_html__('To create new place, go Tour->Places; or click on the Create New Place button', 'tour-booking-manager'),
					'ttbm_place_name' => esc_html__('The name is how it appears on your site.', 'tour-booking-manager'),
					'ttbm_place_description' => esc_html__('The description is not prominent by default; however, some themes may show it.', 'tour-booking-manager'),
					'ttbm_place_image' => esc_html__('Please Select Place Image.', 'tour-booking-manager'),
					'ttbm_display_faq' => esc_html__('Frequently Asked Questions about this tour that customers need to know', 'tour-booking-manager'),
					'ttbm_display_why_choose_us' => esc_html__('Why choose us section, write a key feature list that tourist get Trust to book. you can switch it off.', 'tour-booking-manager'),
					'why_chose_us' => esc_html__('Please add why to book feature list one by one.', 'tour-booking-manager'),
					'ttbm_display_activities' => esc_html__('By default Activities type is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'activities' => esc_html__('Add a list of tour activities for this tour.', 'tour-booking-manager'),
					'ttbm_activity_name' => esc_html__('The name is how it appears on your site.', 'tour-booking-manager'),
					'ttbm_activity_description' => esc_html__('The description is not prominent by default; however, some themes may show it.', 'tour-booking-manager'),
					'ttbm_display_related' => esc_html__('Please select a related tour from this list.', 'tour-booking-manager'),
					'ttbm_display_slider' => esc_html__('By default slider is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_display_slider_hotel' => esc_html__('By default slider is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_section_title_style' => esc_html__('By default Section title is style one', 'tour-booking-manager'),
					'ttbm_ticketing_system' => esc_html__('Select ticket purchase system type.', 'tour-booking-manager'),
					'ttip_ticketing_system' => esc_html__('By default, the ticket purchase system is open. Once you check the availability, you can choose the system that best suits your needs.', 'tour-booking-manager'),
					'ttbm_display_seat_details' => esc_html__('By default Seat Info is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_display_tour_type' => esc_html__('By default Tour type is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_display_hotels' => esc_html__('By default Display hotels is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_display_get_question' => esc_html__('By default Display Get a Questions is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_display_sidebar' => esc_html__('By default Sidebar Widget is Off but you can keep it ON by switching this option', 'tour-booking-manager'),
					'ttbm_display_duration' => esc_html__('By default Duration is ON but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_related_tour' => esc_html__('Please add related  Tour', 'tour-booking-manager'),
					'ttbm_contact_phone' => esc_html__('Please Enter contact phone no', 'tour-booking-manager'),
					'ttbm_contact_text' => esc_html__('Please Enter Contact Section Text', 'tour-booking-manager'),
					'ttbm_contact_email' => esc_html__('Please Enter contact phone email', 'tour-booking-manager'),
					'ttbm_gallery_images' => esc_html__('Please upload images for gallery', 'tour-booking-manager'),
					'ttbm_gallery_images_hotel' => esc_html__('Please upload images for gallery', 'tour-booking-manager'),
					'ttbm_type' => esc_html__('By default Type is General', 'tour-booking-manager'),
					'ttbm_display_advance' => esc_html__('By default Advance option is Off but you can keep it On by switching this option', 'tour-booking-manager'),
					'ttbm_display_extra_advance' => esc_html__('By default Advance option is on but you can keep it off by switching this option', 'tour-booking-manager'),
					'ttbm_display_hotel_distance' => esc_html__('Please add Distance Description', 'tour-booking-manager'),
					'ttbm_display_hotel_rating' => esc_html__('Please Select Hotel rating ', 'tour-booking-manager'),
					'ttbm_display_tour_guide' => esc_html__('You can keep off tour guide information by switching this option', 'tour-booking-manager'),
					'ttbm_tour_guide' => esc_html__('Select tour guide', 'tour-booking-manager'),
					'ttip_tour_guide' => esc_html__('To add tour guide, simply select from the list below.', 'tour-booking-manager'),
					'ttbm_guide_style' => esc_html__('To change tour guide style, please select style.', 'tour-booking-manager'),
					'ttbm_guide_image_style' => esc_html__('To change tour guide image, please select style.', 'tour-booking-manager'),
					'ttbm_guide_description_style' => esc_html__('To change tour guide description style, please select style.', 'tour-booking-manager'),
					'ttbm_display_admin_note' => esc_html__('By default Admin note is on but you can keep it off by switching this option.', 'tour-booking-manager'),
					'ttbm_admin_note' => esc_html__('This are the only text massage about this', 'tour-booking-manager'),
					'general_settings_description' => esc_html__('You can easily set up essential tour details in this section, including tour duration, location, Google Maps address, and maximum number of guests.', 'tour-booking-manager'),
					'price_settings_description' => esc_html__('You have the flexibility to configure your tour pricing or disable registration by toggling the options. This will ensure that all necessary tour information is accurately displayed.', 'tour-booking-manager'),
					'tour_general_settings' => esc_html__('Tour General Settings', 'tour-booking-manager'),
					'tour_settings_des' => esc_html__('Here you can set tour duration, night, price, people count and age etc.', 'tour-booking-manager'),
					'create_location' => esc_html__('If you would like to create a new location, click this button', 'tour-booking-manager'),
					'hotel_config_click' => esc_html__('Click Here', 'tour-booking-manager'),
					'hotel_config' => esc_html__('Tour ticket price works based on hotel price configuration . To add new hotel ', 'tour-booking-manager'),
					'ttip_hotel_config' => esc_html__('Select Hotel name that you want to include in this tour', 'tour-booking-manager'),
					'extra_service_descriptoin' => esc_html__('Additional features can be offered through this option. For example, organizers may provide a pickup service or other paid services as optional add-ons.', 'tour-booking-manager'),
					'gallery_settings_description' => esc_html__('Here gallery image can be added related to tour so that guest can understand about this trip.', 'tour-booking-manager'),
					'featrue_settings_description' => esc_html__('Here features can be configured. Package included feature and package excluded feature need to add from here.', 'tour-booking-manager'),
					'guide_settings_description' => esc_html__('Here you can setup tour guide information who will be guider to this tour and his details', 'tour-booking-manager'),
					'activity_settings_description' => esc_html__('Here tour activities can be added type tour type beach, hiking etc.', 'tour-booking-manager'),
					'places_visit_description' => esc_html__('Here tour places can be configured where guest will be visited with package', 'tour-booking-manager'),
					'daywise_details_description' => esc_html__('You have the ability to include detailed tour information and accompanying images here. For instance, you can outline the itinerary for each day of the tour, starting with the first day and continuing on to subsequent days', 'tour-booking-manager'),
					'faq_settings_description' => esc_html__('Frequently Asked Questions (FAQs) can be conveniently curated and included on this page, providing comprehensive answers to commonly asked questions.', 'tour-booking-manager'),
					'related_settings_description' => esc_html__('Here Other related tours can be added to pique the interest of individuals in our range of available tours.', 'tour-booking-manager'),
					'contact_settings_description' => esc_html__('Contact information for this trip can be added here.', 'tour-booking-manager'),
					'why_book_settings_description' => esc_html__('Here, you can write details about offer the opportunity to include highly compelling information pertaining to our tour, in order to entice potential customers into booking it.', 'tour-booking-manager'),
					'admin_note_settings_description' => esc_html__('Here you can write private note only for understanding admins about this tour.', 'tour-booking-manager'),
					'display_settings_description' => esc_html__('Display settings is somthing that you can use to control frontend display.', 'tour-booking-manager'),
					'ttip_ticket_type' => __('You can access it by clicking on the ticket types menu item in the left sidebar', 'tour-booking-manager'),
					'get_ticket_type' => __('You can import ticket types here . Create new ticket types <a href="post-new.php?post_type=ttbm_ticket_types">Click Me</a>', 'tour-booking-manager'),
				);
				$des = apply_filters('ttbm_filter_description_array', $des);
				return array_key_exists($key,$des)?$des[$key]:'';
			}
			public static function des_row($key) {
				?>
                <tr>
                    <td colspan="7" class="textInfo">
                        <p class="ttbm_description">
                            <span class="fas fa-info-circle"></span>
							<?php echo esc_html(self::des_array($key)); ?>
                        </p>
                    </td>
                </tr>
				<?php
			}
			public static function des_p($key) {
				echo esc_html(self::des_array($key));
			}
			//********************//
			public function save_settings($tour_id) {
				if (!isset($_POST['ttbm_ticket_type_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_ticket_type_nonce'])), 'ttbm_ticket_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $tour_id)) {
					return;
				}
				/*******Genarel********/
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$ttbm_travel_duration = isset($_POST['ttbm_travel_duration']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_duration'])) : '';
					$ttbm_travel_duration_type = isset($_POST['ttbm_travel_duration_type']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_duration_type'])) : 'day';
					update_post_meta($tour_id, 'ttbm_travel_duration', $ttbm_travel_duration);
					update_post_meta($tour_id, 'ttbm_travel_duration_type', $ttbm_travel_duration_type);
					$ttbm_display_duration = isset($_POST['ttbm_display_duration_night']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_duration_night'])) ? 'on' : 'off';
					$ttbm_travel_duration_night = isset($_POST['ttbm_travel_duration_night']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_duration_night'])) : '';
					update_post_meta($tour_id, 'ttbm_travel_duration_night', $ttbm_travel_duration_night);
					update_post_meta($tour_id, 'ttbm_display_duration_night', $ttbm_display_duration);
					/***************/
					$ttbm_display_price_start = isset($_POST['ttbm_display_price_start']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_price_start'])) ? 'on' : 'off';
					$ttbm_travel_start_price = isset($_POST['ttbm_travel_start_price']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_start_price'])) : '';
					update_post_meta($tour_id, 'ttbm_display_price_start', $ttbm_display_price_start);
					update_post_meta($tour_id, 'ttbm_travel_start_price', $ttbm_travel_start_price);
					/***************/
					$ttbm_display_max_people = isset($_POST['ttbm_display_max_people']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_max_people'])) ? 'on' : 'off';
					$ttbm_travel_max_people_allow = isset($_POST['ttbm_travel_max_people_allow']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_max_people_allow'])) : '';
					update_post_meta($tour_id, 'ttbm_display_max_people', $ttbm_display_max_people);
					update_post_meta($tour_id, 'ttbm_travel_max_people_allow', $ttbm_travel_max_people_allow);
					/***************/
					$ttbm_display_min_age = isset($_POST['ttbm_display_min_age']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_min_age'])) ? 'on' : 'off';
					$ttbm_travel_min_age = isset($_POST['ttbm_travel_min_age']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_min_age'])) : '';
					update_post_meta($tour_id, 'ttbm_display_min_age', $ttbm_display_min_age);
					update_post_meta($tour_id, 'ttbm_travel_min_age', $ttbm_travel_min_age);
					/***************/
					$visible_start_location = isset($_POST['ttbm_display_start_location']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_start_location'])) ? 'on' : 'off';
					$start_location = isset($_POST['ttbm_travel_start_place']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_start_place'])) : '';
					update_post_meta($tour_id, 'ttbm_display_start_location', $visible_start_location);
					update_post_meta($tour_id, 'ttbm_travel_start_place', $start_location);
					/***************/
					$ttbm_display_location = isset($_POST['ttbm_display_location']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_location'])) ? 'on' : 'off';
					$ttbm_location_name = isset($_POST['ttbm_location_name']) ? sanitize_text_field(wp_unslash($_POST['ttbm_location_name'])) : '';
					update_post_meta($tour_id, 'ttbm_display_location', $ttbm_display_location);
					update_post_meta($tour_id, 'ttbm_location_name', $ttbm_location_name);
					$location = get_term_by('name', $ttbm_location_name, 'ttbm_tour_location');
					$ttbm_country_name = '';
					if ($location && isset($location->term_id)) {
						$ttbm_country_name = get_term_meta($location->term_id, 'ttbm_country_location', true);
					}
					update_post_meta($tour_id, 'ttbm_country_name', $ttbm_country_name);
					/***************/
					$visible_description = isset($_POST['ttbm_display_description']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_description'])) ? 'on' : 'off';
					$description = isset($_POST['ttbm_short_description']) ? sanitize_text_field(wp_unslash($_POST['ttbm_short_description'])) : '';
					update_post_meta($tour_id, 'ttbm_display_description', $visible_description);
					update_post_meta($tour_id, 'ttbm_short_description', $description);
					/***************/
					$language_status = isset($_POST['ttbm_travel_language_status']) && sanitize_text_field(wp_unslash($_POST['ttbm_travel_language_status'])) ? 'on' : 'off';
					$language = isset($_POST['ttbm_travel_language']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_language'])) : 'en_US';
					update_post_meta($tour_id, 'ttbm_travel_language_status', $language_status);
					update_post_meta($tour_id, 'ttbm_travel_language', $language);
					/***************/
				}
				//*********Location**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$address = isset($_POST['ttbm_place_address']) ? sanitize_text_field(wp_unslash($_POST['ttbm_place_address'])) : '';
					$lat = isset($_POST['ttbm_place_lat']) ? sanitize_text_field(wp_unslash($_POST['ttbm_place_lat'])) : '';
					$lon = isset($_POST['ttbm_place_lon']) ? sanitize_text_field(wp_unslash($_POST['ttbm_place_lon'])) : '';
					update_post_meta($tour_id, 'ttbm_place_address', $address);
					update_post_meta($tour_id, 'ttbm_place_lat', $lat);
					update_post_meta($tour_id, 'ttbm_place_lon', $lon);
					/************************************/
					$ttbm_display_location = isset($_POST['ttbm_display_location']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_location'])) ? 'on' : 'off';
					$ttbm_location_name = isset($_POST['ttbm_location_name']) ? sanitize_text_field(wp_unslash($_POST['ttbm_location_name'])) : '';
					update_post_meta($tour_id, 'ttbm_display_location', $ttbm_display_location);
					update_post_meta($tour_id, 'ttbm_location_name', $ttbm_location_name);
					$location = get_term_by('name', $ttbm_location_name, 'ttbm_tour_location');
					$ttbm_country_name = '';
					if ($location && isset($location->term_id)) {
						$ttbm_country_name = get_term_meta($location->term_id, 'ttbm_country_location', true);
					}
					update_post_meta($tour_id, 'ttbm_country_name', $ttbm_country_name);
					/***************/
					$ttbm_display_map = isset($_POST['ttbm_display_map']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_map'])) ? 'on' : 'off';
					$ttbm_full_location_name = isset($_POST['ttbm_full_location_name']) ? sanitize_text_field(wp_unslash($_POST['ttbm_full_location_name'])) : '';
					update_post_meta($tour_id, 'ttbm_display_map', $ttbm_display_map);
					update_post_meta($tour_id, 'ttbm_full_location_name', $ttbm_full_location_name);
					/***************/
					$map_latitude = isset($_POST['ttbm_map_latitude']) ? sanitize_text_field(wp_unslash($_POST['ttbm_map_latitude'])) : '';
					$map_longitude = isset($_POST['ttbm_map_longitude']) ? sanitize_text_field(wp_unslash($_POST['ttbm_map_longitude'])) : '';
					update_post_meta($tour_id, 'ttbm_map_latitude', $map_latitude);
					update_post_meta($tour_id, 'ttbm_map_longitude', $map_longitude);
				}
				//*********Display**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$content_title_style = isset($_POST['ttbm_section_title_style']) ? sanitize_text_field(wp_unslash($_POST['ttbm_section_title_style'])) : 'style_1';
					$ttbm_travel_rank_tour = isset($_POST['ttbm_travel_rank_tour']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_rank_tour'])) : '';
					$ticketing_system = isset($_POST['ttbm_ticketing_system']) ? sanitize_text_field(wp_unslash($_POST['ttbm_ticketing_system'])) : 'availability_section';
					$seat_info = isset($_POST['ttbm_display_seat_details']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_seat_details'])) ? 'on' : 'off';
					$sidebar = isset($_POST['ttbm_display_sidebar']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_sidebar'])) ? 'on' : 'off';
					$tour_type = isset($_POST['ttbm_display_tour_type']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_tour_type'])) ? 'on' : 'off';
					$hotels = isset($_POST['ttbm_display_hotels']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_hotels'])) ? 'on' : 'off';
					$duration = isset($_POST['ttbm_display_duration']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_duration'])) ? 'on' : 'off';
					$ttbm_display_rank = isset($_POST['ttbm_display_order_tour']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_order_tour'])) ? 'on' : 'off';
					$display_enquiry = isset($_POST['ttbm_display_enquiry']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_enquiry'])) ? 'on' : 'off';
					$ttbm_template = isset($_POST['ttbm_theme_file']) ? sanitize_file_name(wp_unslash($_POST['ttbm_theme_file'])) : 'default.php';
					update_post_meta($tour_id, 'ttbm_travel_rank_tour', $ttbm_travel_rank_tour);
					update_post_meta($tour_id, 'ttbm_display_order_tour', $ttbm_display_rank);
					update_post_meta($tour_id, 'ttbm_section_title_style', $content_title_style);
					update_post_meta($tour_id, 'ttbm_ticketing_system', $ticketing_system);
					update_post_meta($tour_id, 'ttbm_display_seat_details', $seat_info);
					update_post_meta($tour_id, 'ttbm_display_sidebar', $sidebar);
					update_post_meta($tour_id, 'ttbm_display_tour_type', $tour_type);
					update_post_meta($tour_id, 'ttbm_display_hotels', $hotels);
					update_post_meta($tour_id, 'ttbm_display_duration', $duration);
					update_post_meta($tour_id, 'ttbm_theme_file', $ttbm_template);
					update_post_meta($tour_id, 'ttbm_display_enquiry', $display_enquiry);
					//*********FAQ**************//
					$faq = isset($_POST['ttbm_display_faq']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_faq'])) ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_faq', $faq);
					$display_activities = isset($_POST['ttbm_display_activities']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_activities'])) ? 'on' : 'off';
					//*********Activities**************//
					update_post_meta($tour_id, 'ttbm_display_activities', $display_activities);
					$activities = [];
					if (!empty($_POST['ttbm_checked_activities_holder'])) {
						$activities = explode(',', sanitize_text_field(wp_unslash($_POST['ttbm_checked_activities_holder'])));
					}
					update_post_meta($tour_id, 'ttbm_tour_activities', $activities);
					//*********Itenary**************//
					$daywise = isset($_POST['ttbm_display_schedule']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_schedule'])) ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_schedule', $daywise);
					//*********Admin note**************//
					$display_note = isset($_POST['ttbm_display_admin_note']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_admin_note'])) ? 'on' : 'off';
					$note = isset($_POST['ttbm_admin_note']) ? sanitize_text_field(wp_unslash($_POST['ttbm_admin_note'])) : '';
					update_post_meta($tour_id, 'ttbm_display_admin_note', $display_note);;
					update_post_meta($tour_id, 'ttbm_admin_note', $note);
				}
				//*********Ticket price**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$registration = isset($_POST['ttbm_display_registration']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_registration'])) ? 'on' : 'off';
					$advance_option = isset($_POST['ttbm_display_advance']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_advance'])) ? 'on' : 'off';
					$tour_type = isset($_POST['ttbm_type']) ? sanitize_text_field(wp_unslash($_POST['ttbm_type'])) : 'general';
					$ttbm_hotels = isset($_POST['ttbm_hotels']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_hotels'])) : [];
					update_post_meta($tour_id, 'ttbm_display_registration', $registration);
					update_post_meta($tour_id, 'ttbm_display_advance', $advance_option);
					update_post_meta($tour_id, 'ttbm_type', $tour_type);
					update_post_meta($tour_id, 'ttbm_hotels', $ttbm_hotels);
					$ttbm_travel_type = TTBM_Function::get_travel_type($tour_id);
					if ($ttbm_travel_type == 'particular') {
						$after_day = gmdate('Y-m-d', strtotime(' +500 day'));
						update_post_meta($tour_id, 'ttbm_travel_reg_end_date', $after_day);
					} elseif ($ttbm_travel_type == 'repeated') {
						update_post_meta($tour_id, 'ttbm_travel_reg_end_date', TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_date'));
					} else {
						update_post_meta($tour_id, 'ttbm_travel_reg_end_date', TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date'));
					}
					//*********Regular Ticket Price**************//
					$new_ticket_type = array();
					$icon = isset($_POST['ticket_type_icon']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_icon'])) : [];
					$names = isset($_POST['ticket_type_name']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_name'])) : [];
					$ticket_price = isset($_POST['ticket_type_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_price'])) : [];
					$sale_price = isset($_POST['ticket_type_sale_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_sale_price'])) : [];
					$qty = isset($_POST['ticket_type_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_qty'])) : [];
					$qty = apply_filters('ttbm_ticket_type_qty', $qty, $tour_id);
					$default_qty = isset($_POST['ticket_type_default_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_default_qty'])) : [];
					$rsv = isset($_POST['ticket_type_resv_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_resv_qty'])) : [];
					$rsv = apply_filters('ttbm_ticket_type_resv_qty', $rsv, $tour_id);
					$qty_type = isset($_POST['ticket_type_qty_type']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_qty_type'])) : [];
					$description = isset($_POST['ticket_type_description']) ? array_map('sanitize_text_field', wp_unslash($_POST['ticket_type_description'])) : [];
					$count = count($names);
					for ($i = 0; $i < $count; $i++) {
						if ($names[$i] && $ticket_price[$i] >= 0 && $qty[$i] > 0) {
							$new_ticket_type[$i]['ticket_type_icon'] = $icon[$i] ?? '';
							$new_ticket_type[$i]['ticket_type_name'] = $names[$i];
							$new_ticket_type[$i]['ticket_type_price'] = $ticket_price[$i];
							$new_ticket_type[$i]['sale_price'] = $sale_price[$i];
							$new_ticket_type[$i]['ticket_type_qty'] = $qty[$i];
							$new_ticket_type[$i]['ticket_type_default_qty'] = $default_qty[$i] ?? 0;
							$new_ticket_type[$i]['ticket_type_resv_qty'] = $rsv[$i] ?? 0;
							$new_ticket_type[$i]['ticket_type_qty_type'] = $qty_type[$i] ?? 'inputbox';
							$new_ticket_type[$i]['ticket_type_description'] = $description[$i] ?? '';
						}
					}
					$new_ticket_type = apply_filters('ttbm_ticket_type_arr_save', $new_ticket_type);
					update_post_meta($tour_id, 'ttbm_ticket_type', $new_ticket_type);
				}
				//*********Slider**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$slider = isset($_POST['ttbm_display_slider']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_slider'])) ? 'on' : 'off';
					$images = isset($_POST['ttbm_gallery_images']) ? sanitize_text_field(wp_unslash($_POST['ttbm_gallery_images'])) : '';
					$all_images = explode(',', $images);
					update_post_meta($tour_id, 'ttbm_display_slider', $slider);
					update_post_meta($tour_id, 'ttbm_gallery_images', $all_images);
				}
				//*********Place you see**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$place_info = array();
					$hiphop = isset($_POST['ttbm_display_hiphop']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_hiphop'])) ? 'on' : 'off';
					$place_labels = isset($_POST['ttbm_place_label']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_place_label'])) : [];
					$place_ids = isset($_POST['ttbm_city_place_id']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_city_place_id'])) : [];
					update_post_meta($tour_id, 'ttbm_display_hiphop', $hiphop);
					if (sizeof($place_ids) > 0) {
						foreach ($place_ids as $key => $place_id) {
							if ($place_id && $place_id > 0) {
								$place_name = $place_labels[$key];
								$place_info[$key]['ttbm_city_place_id'] = $place_id;
								$place_info[$key]['ttbm_place_label'] = $place_name ?: get_the_title($place_id);
							}
						}
					}
					update_post_meta($tour_id, 'ttbm_hiphop_places', $place_info);
				}
				//*********get a Question**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$get_question = isset($_POST['ttbm_display_get_question']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_get_question'])) ? 'on' : 'off';
					$email = isset($_POST['ttbm_contact_email']) ? sanitize_text_field(wp_unslash($_POST['ttbm_contact_email'])) : '';
					$phone = isset($_POST['ttbm_contact_phone']) ? sanitize_text_field(wp_unslash($_POST['ttbm_contact_phone'])) : '';
					$des = isset($_POST['ttbm_contact_text']) ? sanitize_text_field(wp_unslash($_POST['ttbm_contact_text'])) : '';
					update_post_meta($tour_id, 'ttbm_display_get_question', $get_question);
					update_post_meta($tour_id, 'ttbm_contact_email', $email);
					update_post_meta($tour_id, 'ttbm_contact_phone', $phone);
					update_post_meta($tour_id, 'ttbm_contact_text', $des);
				}
				//*********Guide**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$ttbm_display_tour_guide = isset($_POST['ttbm_display_tour_guide']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_tour_guide'])) ? 'on' : 'off';
					$ttbm_tour_guide = isset($_POST['ttbm_tour_guide']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_tour_guide'])) : [];
					$ttbm_guide_style = isset($_POST['ttbm_guide_style']) ? sanitize_text_field(wp_unslash($_POST['ttbm_guide_style'])) : 'carousel';
					$ttbm_guide_image_style = isset($_POST['ttbm_guide_image_style']) ? sanitize_text_field(wp_unslash($_POST['ttbm_guide_image_style'])) : 'squire';
					$ttbm_guide_description_style = isset($_POST['ttbm_guide_description_style']) ? sanitize_text_field(wp_unslash($_POST['ttbm_guide_description_style'])) : 'full';
					update_post_meta($tour_id, 'ttbm_display_tour_guide', $ttbm_display_tour_guide);
					update_post_meta($tour_id, 'ttbm_tour_guide', $ttbm_tour_guide);
					update_post_meta($tour_id, 'ttbm_guide_style', $ttbm_guide_style);
					update_post_meta($tour_id, 'ttbm_guide_image_style', $ttbm_guide_image_style);
					update_post_meta($tour_id, 'ttbm_guide_description_style', $ttbm_guide_description_style);
				}
				//*********Why choose us**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$why_chose_us_info = array();
					$why_choose_display = isset($_POST['ttbm_display_why_choose_us']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_why_choose_us'])) ? 'on' : 'off';
					$why_chose_infos = isset($_POST['ttbm_why_choose_us_texts']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_why_choose_us_texts'])) : [];
					update_post_meta($tour_id, 'ttbm_display_why_choose_us', $why_choose_display);
					if (sizeof($why_chose_infos) > 0) {
						foreach ($why_chose_infos as $why_chose) {
							if ($why_chose) {
								$why_chose_us_info[] = $why_chose;
							}
						}
					}
					update_post_meta($tour_id, 'ttbm_why_choose_us_texts', $why_chose_us_info);
				}
				//*********Related Tour**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$related = isset($_POST['ttbm_display_related']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_related'])) ? 'on' : 'off';
					$related_tours = isset($_POST['ttbm_related_tour']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_related_tour'])) : [];
					update_post_meta($tour_id, 'ttbm_display_related', $related);
					update_post_meta($tour_id, 'ttbm_related_tour', $related_tours);
				}
				//*********Extra service price**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$new_extra_service = array();
					$extra_icon = isset($_POST['service_icon']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_icon'])) : [];
					$extra_names = isset($_POST['service_name']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_name'])) : [];
					$extra_price = isset($_POST['service_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_price'])) : [];
					$extra_qty = isset($_POST['service_qty']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_qty'])) : [];
					$extra_qty_type = isset($_POST['service_qty_type']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_qty_type'])) : [];
					$extra_service_description = isset($_POST['extra_service_description']) ? array_map('sanitize_text_field', wp_unslash($_POST['extra_service_description'])) : [];
					$extra_count = count($extra_names);
					for ($i = 0; $i < $extra_count; $i++) {
						if ($extra_names[$i] && $extra_price[$i] >= 0 && $extra_qty[$i] > 0) {
							$new_extra_service[$i]['service_icon'] = $extra_icon[$i] ?? '';
							$new_extra_service[$i]['service_name'] = $extra_names[$i];
							$new_extra_service[$i]['service_price'] = $extra_price[$i];
							$new_extra_service[$i]['service_qty'] = $extra_qty[$i];
							$new_extra_service[$i]['service_qty_type'] = $extra_qty_type[$i] ?? 'inputbox';
							$new_extra_service[$i]['extra_service_description'] = $extra_service_description[$i] ?? '';
						}
					}
					$extra_service_data_arr = apply_filters('ttbm_extra_service_arr_save', $new_extra_service);
					update_post_meta($tour_id, 'ttbm_extra_service_data', $extra_service_data_arr);
				}
				do_action('wcpp_partial_settings_saved', $tour_id);
				do_action('ttbm_settings_save', $tour_id);
				TTBM_Function::update_upcoming_date_month($tour_id, true);
			}
		}
		new TTBM_Settings();
	}