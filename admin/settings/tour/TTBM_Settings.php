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
				add_meta_box('mp_meta_box_panel', '' . $label . esc_html__(' Information Settings : ', 'tour-booking-manager') . get_the_title(get_the_id()), array($this, 'settings'), 'ttbm_tour', 'normal', 'high');
			}
			//******************************//
			public function settings() {
				$tour_id = get_the_id();
				?>
				<div class="mpStyle ttbm_settings">
					<div class="mpTabs leftTabs d-flex justify-content-between">
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
				$ttbm_tax_meta_boxs = ['page_nav' => $tour_label . __('Tax', 'tour-booking-manager'), 'priority' => 10, 'sections' => ['section_2' => ['title' => __('Tax Settings', 'tour-booking-manager'), 'description' => __('', 'tour-booking-manager'), 'options' => [['id' => '_tax_status', 'title' => $tour_label . __(' Tax Status', 'tour-booking-manager'), 'details' => __('Please Select Tax Status', 'tour-booking-manager'), 'type' => 'select', 'class' => 'omg', 'default' => 'taxable', 'args' => ['taxable' => __('Taxable', 'tour-booking-manager'), 'shipping' => __('Shipping only', 'tour-booking-manager'), 'none' => __('None', 'tour-booking-manager')]], ['id' => '_tax_class', 'title' => $tour_label . __(' Tax Class', 'tour-booking-manager'), 'details' => __('Please Select Tax Class', 'tour-booking-manager'), 'type' => 'select', 'class' => 'omg', 'default' => 'none', 'args' => MP_Global_Function::all_tax_list()],]],],];
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
				$ttbm_list_template = ['page_nav' => __('Template', 'tour-booking-manager'), 'priority' => 10, 'sections' => ['section_2' => ['title' => __('', 'tour-booking-manager'), 'description' => __('', 'tour-booking-manager'), 'options' => [['id' => 'ttbm_theme_file', 'title' => __(' Template', 'tour-booking-manager'), 'details' => __('Please Select a Template', 'tour-booking-manager'), 'type' => 'select', 'class' => 'omg', 'default' => 'fixed', 'args' => TTBM_Function::all_details_template()],]],],];
				$ttbm_list_template_meta_args = [
					'meta_box_id' => 'ttbm_list_thumbnail_meta_boxes',
					'meta_box_title' => __('Template', 'tour-booking-manager'),
					'screen' => [TTBM_Function::get_cpt_name()],
					'context' => 'side', // 'normal', 'side', and 'advanced'
					'priority' => 'low', // 'high', 'low'
					'callback_args' => [],
					'nav_position' => 'none', // right, top, left, none
					'item_name' => "MagePeople",
					'item_version' => "2.0",
					'panels' => ['ttbm_list_template_meta_box' => $ttbm_list_template],
				];
				new TtbmAddMetaBox($ttbm_list_template_meta_args);
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
					'hotel_config' => esc_html__('Tour ticket price works based on hotel price configuration . To add new hotel '),
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
					'ttip_ticket_type'               => __( 'You can access it by clicking on the ticket types menu item in the left sidebar', 'tour-booking-manager' ),
					'get_ticket_type'               => __( 'You can import ticket types here . Create new ticket types <a href="post-new.php?post_type=ttbm_ticket_types">Click Me</a>', 'tour-booking-manager' ),
				);
				$des = apply_filters('ttbm_filter_description_array', $des);
				return $des[$key];
			}
			public static function des_row($key) {
				?>
				<tr>
					<td colspan="7" class="textInfo">
						<p class="ttbm_description">
							<span class="fas fa-info-circle"></span>
							<?php echo self::des_array($key); ?>
						</p>
					</td>
				</tr>
				<?php
			}
			public static function des_p($key) {
				echo self::des_array($key);
			}
			//********************//
			public function save_settings($tour_id) {
				if (!isset($_POST['ttbm_ticket_type_nonce']) || !wp_verify_nonce($_POST['ttbm_ticket_type_nonce'], 'ttbm_ticket_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $tour_id)) {
					return;
				}
				$this->save_map($tour_id);
				do_action('wcpp_partial_settings_saved', $tour_id);
				do_action('ttbm_settings_save', $tour_id);
				TTBM_Function::update_upcoming_date_month($tour_id, true);
			}
			public function save_map($tour_id) {
				if (get_post_type($tour_id) == 'ttbm_places') {
					$address = MP_Global_Function::get_submit_info('ttbm_place_address');
					$lat = MP_Global_Function::get_submit_info('ttbm_place_lat');
					$lon = MP_Global_Function::get_submit_info('ttbm_place_lon');
					update_post_meta($tour_id, 'ttbm_place_address', $address);
					update_post_meta($tour_id, 'ttbm_place_lat', $lat);
					update_post_meta($tour_id, 'ttbm_place_lon', $lon);
				}
			}
		}
		new TTBM_Settings();
	}