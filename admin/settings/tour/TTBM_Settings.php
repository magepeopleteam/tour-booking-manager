<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings')) {
		class TTBM_Settings {
			private static $date_migration_snapshots = array();
			private static $date_migration_notice_key = 'ttbm_date_migration_notice_';
			/**
			 * Prevents nested save_post recursion from wp_update_post() during validation.
			 *
			 * @var bool
			 */
			private static $saving_settings = false;
			/**
			 * True while an AJAX background auto-save is running. When set, save_settings()
			 * still writes every field and runs the date/booking migration exactly like a
			 * manual Update, but must NOT force the post to draft or queue admin-notice
			 * transients (the auto-save reports validation state back over JSON instead).
			 *
			 * @var bool
			 */
			private static $is_autosave = false;
			/**
			 * Validation errors collected by the most recent save_settings() run, so the
			 * auto-save AJAX handler can return them to the browser without re-computing.
			 *
			 * @var array
			 */
			private static $last_validation_errors = array();

			public function __construct() {
				add_action('add_meta_boxes', [$this, 'settings_meta']);
				add_action('admin_init', [$this, 'tour_settings_meta_box'], 10);
				add_action('save_post', [$this, 'capture_date_migration_snapshot'], 5, 1);
				add_action('save_post', array($this, 'save_settings'), 99, 1);
				add_action('save_post', [$this, 'sync_bookings_after_date_change'], 120, 1);
				add_action('wp_ajax_ttbm_autosave_tour', [$this, 'ajax_autosave_tour']);
				add_filter('wp_insert_post_data', [$this, 'filter_insert_post_data'], 99, 2);
				add_action('admin_notices', [$this, 'render_date_migration_notice']);
				add_action('admin_notices', [$this, 'render_title_required_notice']);
				add_action('admin_notices', [$this, 'render_location_required_notice']);
				add_action('admin_notices', [$this, 'render_featured_image_required_notice']);
				add_action('admin_notices', [$this, 'render_dates_required_notice']);
				add_action('admin_notices', [$this, 'render_tickets_required_notice']);
			}
			/**
			 * Force tour title from the custom Overview field during core save.
			 *
			 * @param array $data    Sanitized post data.
			 * @param array $postarr Raw post array.
			 * @return array
			 */
			public function filter_insert_post_data($data, $postarr) {
				if (!is_admin() || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
					return $data;
				}
				if (!isset($data['post_type']) || $data['post_type'] !== TTBM_Function::get_cpt_name()) {
					return $data;
				}
				$title = '';
				if (class_exists('TTBM_Settings_Hotel')) {
					$title = TTBM_Settings_Hotel::resolve_submitted_title_from_request();
				} else {
					if (isset($_POST['ttbm_post_title_ui'])) {
						$title = trim(sanitize_text_field(wp_unslash($_POST['ttbm_post_title_ui'])));
					}
					if ($title === '' && isset($_POST['post_title']) && !is_array($_POST['post_title'])) {
						$title = trim(sanitize_text_field(wp_unslash($_POST['post_title'])));
					}
				}
				if ($title !== '') {
					$data['post_title'] = $title;
				}
				return $data;
			}
			//************************//
			public function settings_meta() {
				$label = TTBM_Function::get_name();
				add_meta_box('ttbm_meta_box_panel', '' . $label . esc_html__(' Information Settings : ', 'tour-booking-manager') . get_the_title(get_the_id()), array($this, 'settings'), 'ttbm_tour', 'normal', 'high');
			}
			//******************************//
			public function settings() {
				$tour_id = get_the_id();
				$ttbm_label = TTBM_Function::get_name();
				?>
				<?php
				wp_nonce_field('ttbm_ticket_type_nonce', 'ttbm_ticket_type_nonce');
				// Always-submitted fields (outside collapsed/inactive tabs). Synced from UI via JS.
				$map_location = (string) get_post_meta($tour_id, 'ttbm_full_location_name', true);
				$map_lat = (string) get_post_meta($tour_id, 'ttbm_map_latitude', true);
				$map_lng = (string) get_post_meta($tour_id, 'ttbm_map_longitude', true);
				$tour_title = (string) get_the_title($tour_id);
				?>
                <input type="hidden" id="ttbm_post_title_submit" name="post_title" value="<?php echo esc_attr($tour_title); ?>">
                <input type="hidden" id="ttbm_full_location_name_submit" name="ttbm_full_location_name" value="<?php echo esc_attr($map_location); ?>">
                <input type="hidden" id="ttbm_map_latitude_submit" name="ttbm_map_latitude" value="<?php echo esc_attr($map_lat); ?>">
                <input type="hidden" id="ttbm_map_longitude_submit" name="ttbm_map_longitude" value="<?php echo esc_attr($map_lng); ?>">
                <div id="ttbm_content" class="ttbm_configuration">
                    <div class="ttbm_style ttbm_settings ">
                        <div class="ttbmTabs leftTabs d-flex justify-content-between">
                            <ul class="tabLists meta-sidebar _mL">
								<div class="meta-sidebar-toggle"><i class="mi mi-angle-right"></i></div>
                                <li data-tabs-target="#ttbm_general_info" title="<?php esc_attr_e('Overview', 'tour-booking-manager'); ?>"><i class="mi mi-settings"></i> <span><?php esc_html_e('Overview', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_settings_location" class="ttbm_settings_location"><i class="mi mi-marker"></i> <span><?php esc_html_e('Destination', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_settings_dates"><i class="mi mi-calendar"></i> <span><?php esc_html_e('Schedule', 'tour-booking-manager'); ?></span> </li>
								<?php do_action('ttbm_meta_box_tab_name', $tour_id); ?>
                                <li data-tabs-target="#ttbm_settings_pricing"><i class="mi mi-coins"></i> <span><?php esc_html_e(' Pricing & Services', 'tour-booking-manager'); ?></span>  </li>
								<?php do_action('ttbm_meta_box_tab_after_pricing'); ?>
                                <li data-tabs-target="#ttbm_settings_feature"><i class="mi mi-features"></i> <span><?php esc_html_e(' Features & Activities', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_settings_template"><i class="mi mi-table-layout"></i> <span><?php esc_html_e('Layout', 'tour-booking-manager'); ?></span> </li>
								<li data-tabs-target="#ttbm_settings_guide"><i class="mi mi-hiking"></i> <span><?php echo esc_html($ttbm_label) . '  ' . esc_html__('Guide ', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_daywise_settings"><i class="fas fa-list-ul"></i> <span><?php esc_html_e('Itinerary & F.A.Q', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_settings_related_tour"><i class="mi mi-link"></i> <span><?php esc_html_e('Related Tour', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_settings_extras"><i class="mi mi-envelope"></i> <span><?php esc_html_e('Contact info', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_settings_why_chose_us"><i class="mi mi-improve-user"></i> <span><?php esc_html_e('Promotional Text', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_display_settings"><i class="mi mi-dashboard-monitor"></i> <span><?php esc_html_e(' Display settings', 'tour-booking-manager'); ?></span> </li>
                                <li data-tabs-target="#ttbm_add_promotional_setting"><i class="mi mi-handshake-deal-loan"></i> <span><?php esc_html_e(' Promotional Deals', 'tour-booking-manager'); ?></span> </li>
								<?php do_action('add_ttbm_settings_tab_name'); ?>
								<?php if (is_plugin_active('mage-partial-payment-pro/mage_partial_pro.php')) : ?>
                                    <li data-tabs-target="#_mep_pp_deposits_type"><i class="far fa-money-bill-alt"></i>&nbsp;&nbsp;
									<span><?php esc_html_e('Partial Payment', 'tour-booking-manager'); ?> </span>                                  </li>
								<?php endif; ?>
                            </ul>
                            <div class="tabsContent">
								<?php
									do_action('ttbm_meta_box_tab_content', $tour_id);
									$this->partial_payment_settings($tour_id);
								?>
                            </div>
                            <div class="ttbm-right-sidebar" data-active-tab="#ttbm_general_info">
								<?php do_action('ttbm_right_sidebar_content', $tour_id); ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			//************************//
			public function tour_settings_meta_box() {
				$tour_label = TTBM_Function::get_name();
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
					'stay_night' => esc_html__('Turn on toggle for overnight stay.', 'tour-booking-manager'),
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
					'ttbm_auto_related_tour' => esc_html__('If no related tour is assigned, automatically show tours from the same location, activities, or categories. Default is ON.', 'tour-booking-manager'),
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
					'top_picks_and_deals' => __('Top Tours showcases your best-performing and most popular tour packages. Use it to highlight the most booked or highest-rated experiences. The tone is direct and value-driven, reflecting popularity and performance.', 'tour-booking-manager'),
				);
				$des = apply_filters('ttbm_filter_description_array', $des);
				return array_key_exists($key, $des) ? $des[$key] : '';
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

			private function is_tour_settings_save_request($tour_id): bool {
				if (!$tour_id || get_post_type($tour_id) != TTBM_Function::get_cpt_name()) {
					return false;
				}
				// AJAX auto-save is already protected by its dedicated nonce and the
				// edit_post capability check in ajax_autosave_tour(). Do not silently
				// skip the save if the classic metabox nonce was omitted by serialize().
				if (!self::$is_autosave && (!isset($_POST['ttbm_ticket_type_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_ticket_type_nonce'])), 'ttbm_ticket_type_nonce'))) {
					return false;
				}
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
					return false;
				}
				if (wp_is_post_revision($tour_id)) {
					return false;
				}
				return current_user_can('edit_post', $tour_id);
			}

			public function capture_date_migration_snapshot($tour_id): void {
				if (!$this->is_tour_settings_save_request($tour_id)) {
					return;
				}
				self::$date_migration_snapshots[$tour_id] = $this->get_date_migration_snapshot($tour_id);
			}

			private function get_date_migration_snapshot($tour_id): array {
				return array(
					'tour_type' => TTBM_Function::get_tour_type($tour_id),
					'ttbm_travel_type' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_type', 'fixed'),
					'ttbm_travel_start_date' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_date'),
					'ttbm_travel_start_time' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_start_time'),
					'ttbm_travel_end_date' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_date'),
					'ttbm_travel_end_time' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_end_time'),
					'ttbm_travel_reg_end_date' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_reg_end_date'),
					'reg_end_time' => TTBM_Global_Function::get_post_info($tour_id, 'reg_end_time'),
					'ttbm_particular_dates' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_particular_dates', array()),
					'ttbm_travel_repeated_start_date' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_date'),
					'ttbm_travel_repeated_start_time' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_time'),
					'ttbm_travel_repeated_after' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_after', 1),
					'ttbm_repeat_type' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_repeat_type'),
					'ttbm_repeat_number' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_repeat_number'),
					'ttbm_travel_repeated_end_date' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_end_date'),
					'mep_disable_ticket_time' => TTBM_Global_Function::get_post_info($tour_id, 'mep_disable_ticket_time', 'no'),
					'ttbm_enable_off_schedule' => TTBM_Global_Function::get_post_info($tour_id, 'ttbm_enable_off_schedule', 'no'),
					'mep_ticket_offdays' => TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_offdays', array()),
					'mep_ticket_off_dates' => TTBM_Global_Function::get_post_info($tour_id, 'mep_ticket_off_dates', array()),
				);
			}

			private function has_date_schedule_changed(int $tour_id): bool {
				$before = self::$date_migration_snapshots[$tour_id] ?? null;
				if (!is_array($before)) {
					return true;
				}
				return serialize($before) !== serialize($this->get_date_migration_snapshot($tour_id));
			}

			public function sync_bookings_after_date_change($tour_id): void {
				if (!$this->is_tour_settings_save_request($tour_id)) {
					return;
				}
				$before = self::$date_migration_snapshots[$tour_id] ?? array();
				unset(self::$date_migration_snapshots[$tour_id]);
				if (empty($before)) {
					return;
				}
				$after = $this->get_date_migration_snapshot($tour_id);
				$migration_map = $this->build_date_migration_map($tour_id, $before, $after);
				if (empty($migration_map)) {
					return;
				}
				$validation = $this->validate_booking_date_migration($tour_id, $migration_map);
				if (is_wp_error($validation)) {
					$this->restore_date_migration_snapshot($tour_id, $before);
					TTBM_Function::update_upcoming_date_month($tour_id, true);
					$this->set_date_migration_notice('error', $validation->get_error_message());
					return;
				}
				$this->migrate_bookings_for_date_changes($tour_id, $migration_map);
				TTBM_Function::update_upcoming_date_month($tour_id, true);
			}

			private function build_date_migration_map(int $tour_id, array $before, array $after): array {
				if (($before['tour_type'] ?? '') !== 'general' || ($after['tour_type'] ?? '') !== 'general') {
					return array();
				}
				$before_travel_type = $before['ttbm_travel_type'] ?? 'fixed';
				$after_travel_type = $after['ttbm_travel_type'] ?? 'fixed';
				if ($before_travel_type !== $after_travel_type) {
					return array();
				}
				$migration_map = array();
				if ($after_travel_type === 'fixed') {
					$old_date = $this->build_fixed_booking_date($before);
					$new_date = $this->build_fixed_booking_date($after);
					if ($old_date && $new_date && $old_date !== $new_date) {
						$migration_map[] = array(
							'old_date' => $old_date,
							'new_date' => $new_date,
						);
					}
				}
				if ($after_travel_type === 'particular') {
					$old_rows = is_array($before['ttbm_particular_dates'] ?? null) ? $before['ttbm_particular_dates'] : array();
					$new_rows = is_array($after['ttbm_particular_dates'] ?? null) ? $after['ttbm_particular_dates'] : array();
					$row_count = min(count($old_rows), count($new_rows));
					for ($i = 0; $i < $row_count; $i++) {
						$old_date = $this->build_particular_booking_date($old_rows[$i] ?? array());
						$new_date = $this->build_particular_booking_date($new_rows[$i] ?? array());
						if ($old_date && $new_date && $old_date !== $new_date) {
							$migration_map[] = array(
								'old_date' => $old_date,
								'new_date' => $new_date,
							);
						}
					}
				}
				if ($after_travel_type === 'repeated') {
					$migration_map = array_merge($migration_map, $this->build_repeated_date_migration_map($tour_id, $before, $after));
				}
				$unique_map = array();
				foreach ($migration_map as $migration) {
					$key = $migration['old_date'] . '|' . $migration['new_date'];
					$unique_map[$key] = $migration;
				}
				return array_values($unique_map);
			}

			private function build_repeated_date_migration_map(int $tour_id, array $before, array $after): array {
				$before_start = $before['ttbm_travel_repeated_start_date'] ?? '';
				$after_start = $after['ttbm_travel_repeated_start_date'] ?? '';
				if (!$before_start || !$after_start || strtotime($before_start) === false || strtotime($after_start) === false) {
					return array();
				}
				$delta_seconds = strtotime($after_start . ' 00:00:00') - strtotime($before_start . ' 00:00:00');
				if ($delta_seconds === 0) {
					return array();
				}
				$stable_keys = array(
					'ttbm_travel_repeated_after',
					'ttbm_repeat_type',
					'ttbm_repeat_number',
					'mep_disable_ticket_time',
					'ttbm_enable_off_schedule',
				);
				foreach ($stable_keys as $key) {
					if (($before[$key] ?? '') !== ($after[$key] ?? '')) {
						return array();
					}
				}
				if (($before['mep_ticket_offdays'] ?? array()) !== ($after['mep_ticket_offdays'] ?? array())) {
					return array();
				}
				if (($before['mep_ticket_off_dates'] ?? array()) !== ($after['mep_ticket_off_dates'] ?? array())) {
					return array();
				}
				$migration_map = array();
				foreach ($this->get_distinct_booking_dates_for_tour($tour_id) as $old_date) {
					$new_date = $this->shift_booking_date_by_seconds($old_date, $delta_seconds);
					if ($new_date && $new_date !== $old_date) {
						$migration_map[] = array(
							'old_date' => $old_date,
							'new_date' => $new_date,
						);
					}
				}
				return $migration_map;
			}

			private function build_fixed_booking_date(array $snapshot): string {
				$date = $snapshot['ttbm_travel_start_date'] ?? '';
				if (!$date || strtotime($date) === false) {
					return '';
				}
				return gmdate('Y-m-d', strtotime($date));
			}

			private function build_particular_booking_date(array $particular_date): string {
				$date = $particular_date['ttbm_particular_start_date'] ?? '';
				if (!$date || strtotime($date) === false) {
					return '';
				}
				$date = gmdate('Y-m-d', strtotime($date));
				$time = trim((string)($particular_date['ttbm_particular_start_time'] ?? ''));
				if ($time) {
					return gmdate('Y-m-d H:i', strtotime($date . ' ' . $time));
				}
				return $date;
			}

			private function get_distinct_booking_dates_for_tour(int $tour_id): array {
				$args = array(
					'post_type' => 'ttbm_booking',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'meta_query' => array(
						array(
							'key' => 'ttbm_id',
							'value' => $tour_id,
							'compare' => '=',
						),
					),
				);
				$booking_ids = get_posts($args);
				$dates = array();
				foreach ($booking_ids as $booking_id) {
					$booking_date = TTBM_Global_Function::get_post_info($booking_id, 'ttbm_date');
					if ($booking_date) {
						$dates[$booking_date] = $booking_date;
					}
				}
				return array_values($dates);
			}

			private function shift_booking_date_by_seconds(string $date, int $delta_seconds): string {
				if (!$date || strtotime($date) === false || $delta_seconds === 0) {
					return '';
				}
				$timestamp = strtotime($date) + $delta_seconds;
				if (TTBM_Global_Function::check_time_exit_date($date)) {
					return gmdate('Y-m-d H:i', $timestamp);
				}
				return gmdate('Y-m-d', $timestamp);
			}

			private function validate_booking_date_migration($tour_id, array $migration_map) {
				$ticket_types = TTBM_Function::get_ticket_type($tour_id);
				if (!is_array($ticket_types) || empty($ticket_types)) {
					return true;
				}
				$date_deltas = array();
				foreach ($migration_map as $migration) {
					foreach ($ticket_types as $ticket_type) {
						$ticket_name = $ticket_type['ticket_type_name'] ?? '';
						if (!$ticket_name) {
							continue;
						}
						$moved_qty = TTBM_Function::get_total_sold($tour_id, $migration['old_date'], $ticket_name);
						if ($moved_qty < 1) {
							continue;
						}
						$date_deltas[$migration['old_date']][$ticket_name] = ($date_deltas[$migration['old_date']][$ticket_name] ?? 0) - $moved_qty;
						$date_deltas[$migration['new_date']][$ticket_name] = ($date_deltas[$migration['new_date']][$ticket_name] ?? 0) + $moved_qty;
					}
				}
				foreach ($date_deltas as $date => $ticket_deltas) {
					$availability = TTBM_Function::get_ticket_availability_info($tour_id, $date);
					foreach ($ticket_deltas as $ticket_name => $delta) {
						if ($delta <= 0) {
							continue;
						}
						$available_qty = (int)($availability[$ticket_name]['available_qty'] ?? 0);
						if ($delta > $available_qty) {
							$formatted_date = $this->format_admin_migration_date($date);
							return new WP_Error(
								'ttbm_date_migration_capacity_conflict',
								sprintf(
									/* translators: 1: date, 2: ticket name */
									__('Cannot move booked seats to %1$s. Not enough stock is available for ticket type "%2$s".', 'tour-booking-manager'),
									$formatted_date,
									$ticket_name
								)
							);
						}
					}
				}
				return true;
			}

			private function migrate_bookings_for_date_changes($tour_id, array $migration_map): void {
				foreach ($migration_map as $migration) {
					$order_ids = $this->get_order_ids_for_date_migration($tour_id, $migration['old_date']);
					foreach ($order_ids as $order_id) {
						$this->update_order_items_for_date_migration((int)$order_id, $tour_id, $migration['old_date'], $migration['new_date']);
					}
					$this->update_booking_posts_for_date_migration('ttbm_booking', $tour_id, $migration['old_date'], $migration['new_date']);
					$this->update_booking_posts_for_date_migration('ttbm_service_booking', $tour_id, $migration['old_date'], $migration['new_date']);
				}
			}

			private function get_order_ids_for_date_migration($tour_id, string $old_date): array {
				$args = array(
					'post_type' => 'ttbm_booking',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'ttbm_id',
							'value' => $tour_id,
							'compare' => '=',
						),
						$this->get_date_meta_query_clause($old_date),
					),
				);
				$order_ids = array();
				$booking_ids = get_posts($args);
				foreach ($booking_ids as $booking_id) {
					$order_id = TTBM_Global_Function::get_post_info($booking_id, 'ttbm_order_id');
					if ($order_id) {
						$order_ids[] = (int)$order_id;
					}
				}
				return array_values(array_unique(array_filter($order_ids)));
			}

			private function update_order_items_for_date_migration(int $order_id, int $tour_id, string $old_date, string $new_date): void {
				$order = wc_get_order($order_id);
				if (!$order) {
					return;
				}
				$label_date_key = TTBM_Function::get_name() . ' ' . esc_html__('Date', 'tour-booking-manager');
				$new_display_date = TTBM_Global_Function::date_format($new_date, TTBM_Global_Function::check_time_exit_date($new_date) ? 'full' : 'date');
				foreach ($order->get_items() as $item) {
					$item_tour_id = (int)TTBM_Global_Function::data_sanitize(TTBM_Global_Function::get_order_item_meta($item->get_id(), '_ttbm_id'));
					$item_tour_id = (int)TTBM_Function::post_id_multi_language($item_tour_id);
					if ($item_tour_id !== (int)TTBM_Function::post_id_multi_language($tour_id)) {
						continue;
					}
					$item_date = TTBM_Global_Function::get_order_item_meta($item->get_id(), '_ttbm_date');
					if (!$this->booking_dates_match($item_date, $old_date)) {
						continue;
					}
					$item->update_meta_data('_ttbm_date', $new_date);
					$ticket_info = TTBM_Global_Function::data_sanitize(TTBM_Global_Function::get_order_item_meta($item->get_id(), '_ttbm_ticket_info'));
					if (is_array($ticket_info)) {
						foreach ($ticket_info as $key => $ticket) {
							if (is_array($ticket) && array_key_exists('ttbm_date', $ticket)) {
								$ticket_info[$key]['ttbm_date'] = $new_date;
							}
						}
						$item->update_meta_data('_ttbm_ticket_info', $ticket_info);
					}
					$service_info = TTBM_Global_Function::data_sanitize(TTBM_Global_Function::get_order_item_meta($item->get_id(), '_ttbm_service_info'));
					if (is_array($service_info)) {
						foreach ($service_info as $key => $service) {
							if (is_array($service) && array_key_exists('ttbm_date', $service)) {
								$service_info[$key]['ttbm_date'] = $new_date;
							}
						}
						$item->update_meta_data('_ttbm_service_info', $service_info);
					}
					if ($item->get_meta($label_date_key, true) !== '') {
						$item->update_meta_data($label_date_key, $new_display_date);
					}
					$item->save();
				}
				$order->save();
			}

			private function update_booking_posts_for_date_migration(string $post_type, int $tour_id, string $old_date, string $new_date): void {
				$args = array(
					'post_type' => $post_type,
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'fields' => 'ids',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'ttbm_id',
							'value' => $tour_id,
							'compare' => '=',
						),
						$this->get_date_meta_query_clause($old_date),
					),
				);
				$post_ids = get_posts($args);
				foreach ($post_ids as $post_id) {
					update_post_meta($post_id, 'ttbm_date', $new_date);
				}
			}

			private function get_date_meta_query_clause(string $date): array {
				if (TTBM_Global_Function::check_time_exit_date($date)) {
					return array(
						'key' => 'ttbm_date',
						'value' => $date,
						'compare' => '=',
					);
				}
				return array(
					'key' => 'ttbm_date',
					'value' => gmdate('Y-m-d', strtotime($date)),
					'compare' => 'LIKE',
				);
			}

			private function booking_dates_match(string $stored_date, string $expected_date): bool {
				if (!$stored_date || !$expected_date || strtotime($stored_date) === false || strtotime($expected_date) === false) {
					return false;
				}
				if (TTBM_Global_Function::check_time_exit_date($expected_date)) {
					return gmdate('Y-m-d H:i', strtotime($stored_date)) === gmdate('Y-m-d H:i', strtotime($expected_date));
				}
				return gmdate('Y-m-d', strtotime($stored_date)) === gmdate('Y-m-d', strtotime($expected_date));
			}

			private function restore_date_migration_snapshot(int $tour_id, array $snapshot): void {
				$date_keys = array(
					'ttbm_travel_type',
					'ttbm_travel_start_date',
					'ttbm_travel_start_time',
					'ttbm_travel_end_date',
					'ttbm_travel_end_time',
					'ttbm_travel_reg_end_date',
					'reg_end_time',
					'ttbm_particular_dates',
					'ttbm_travel_repeated_start_date',
					'ttbm_travel_repeated_start_time',
					'ttbm_travel_repeated_after',
					'ttbm_repeat_type',
					'ttbm_repeat_number',
					'ttbm_travel_repeated_end_date',
					'mep_disable_ticket_time',
					'ttbm_enable_off_schedule',
					'mep_ticket_offdays',
					'mep_ticket_off_dates',
				);
				foreach ($date_keys as $key) {
					update_post_meta($tour_id, $key, $snapshot[$key] ?? '');
				}
				clean_post_cache($tour_id);
			}

			private function format_admin_migration_date(string $date): string {
				if (!$date || strtotime($date) === false) {
					return $date;
				}
				$format = TTBM_Global_Function::check_time_exit_date($date) ? 'full' : 'date';
				return TTBM_Global_Function::date_format($date, $format);
			}

			private function set_date_migration_notice(string $type, string $message): void {
				$user_id = get_current_user_id();
				if (!$user_id) {
					return;
				}
				set_transient(self::$date_migration_notice_key . $user_id, array(
					'type' => $type,
					'message' => $message,
				), 5 * MINUTE_IN_SECONDS);
			}

			public function render_date_migration_notice(): void {
				if (!is_admin()) {
					return;
				}
				$screen = function_exists('get_current_screen') ? get_current_screen() : null;
				if ($screen && $screen->post_type !== TTBM_Function::get_cpt_name()) {
					return;
				}
				$user_id = get_current_user_id();
				if (!$user_id) {
					return;
				}
				$notice = get_transient(self::$date_migration_notice_key . $user_id);
				if (!$notice || empty($notice['message'])) {
					return;
				}
				delete_transient(self::$date_migration_notice_key . $user_id);
				$type = ($notice['type'] ?? 'error') === 'success' ? 'updated' : 'error';
				?>
				<div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
					<p><?php echo esc_html($notice['message']); ?></p>
				</div>
				<?php
			}
			public function render_title_required_notice(): void {
				$user_id = get_current_user_id();
				if (!get_transient('ttbm_title_required_' . $user_id)) return;
				delete_transient('ttbm_title_required_' . $user_id);
				?>
				<div class="notice notice-error is-dismissible">
					<p><strong><?php esc_html_e('Tour title is required.', 'tour-booking-manager'); ?></strong>
					<?php esc_html_e('The post was kept as a draft. Please enter a title and save again.', 'tour-booking-manager'); ?></p>
				</div>
				<?php
			}
			public function render_location_required_notice(): void {
				$user_id = get_current_user_id();
				if (!get_transient('ttbm_location_required_' . $user_id)) return;
				delete_transient('ttbm_location_required_' . $user_id);
				?>
				<div class="notice notice-error is-dismissible">
					<p><strong><?php esc_html_e('Tour location is required.', 'tour-booking-manager'); ?></strong>
					<?php esc_html_e('The post was kept as a draft. Please select a location and save again.', 'tour-booking-manager'); ?></p>
				</div>
				<?php
			}
			public function render_featured_image_required_notice(): void {
				$user_id = get_current_user_id();
				if (!get_transient('ttbm_featured_image_required_' . $user_id)) return;
				delete_transient('ttbm_featured_image_required_' . $user_id);
				?>
				<div class="notice notice-error is-dismissible">
					<p><strong><?php esc_html_e('Featured image is required.', 'tour-booking-manager'); ?></strong>
					<?php esc_html_e('The post was kept as a draft. Please upload a featured image and save again.', 'tour-booking-manager'); ?></p>
				</div>
				<?php
			}
			public function render_dates_required_notice(): void {
				$user_id = get_current_user_id();
				$message = get_transient('ttbm_dates_required_' . $user_id);
				if (!$message) {
					return;
				}
				delete_transient('ttbm_dates_required_' . $user_id);
				?>
				<div class="notice notice-error is-dismissible">
					<p><strong><?php esc_html_e('Date configuration is incomplete.', 'tour-booking-manager'); ?></strong>
					<?php echo esc_html($message); ?></p>
				</div>
				<?php
			}
			private function validate_date_fields(int $tour_id): ?string {
				$travel_type = isset($_POST['ttbm_travel_type']) && $_POST['ttbm_travel_type'] !== ''
					? sanitize_text_field(wp_unslash($_POST['ttbm_travel_type']))
					: TTBM_Function::get_travel_type($tour_id);

				if ($travel_type === 'fixed') {
					$required_fields = array(
						'ttbm_travel_start_date' => __('Start Date', 'tour-booking-manager'),
						'ttbm_travel_end_date'   => __('End Date', 'tour-booking-manager'),
						'ttbm_travel_start_time' => __('Start Time', 'tour-booking-manager'),
						'ttbm_travel_end_time'   => __('End Time', 'tour-booking-manager'),
					);
					$missing = array();
					foreach ($required_fields as $field_key => $label) {
						$field_submitted = array_key_exists($field_key, $_POST);
						$value = $field_submitted ? trim(sanitize_text_field(wp_unslash($_POST[ $field_key ]))) : '';
						if (!$field_submitted) {
							$value = trim((string) TTBM_Global_Function::get_post_info($tour_id, $field_key, ''));
						}
						if ($value === '') {
							$missing[] = $label;
						}
					}
					if (!empty($missing)) {
						return sprintf(
							/* translators: %s: comma-separated field labels */
							__('Fixed tour dates require: %s.', 'tour-booking-manager'),
							implode(', ', $missing)
						);
					}
				} elseif ($travel_type === 'particular') {
					$checkin_dates  = isset($_POST['ttbm_particular_start_date']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['ttbm_particular_start_date'])) : array();
					$checkout_dates = isset($_POST['ttbm_particular_end_date']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['ttbm_particular_end_date'])) : array();
					$checkin_times  = isset($_POST['ttbm_particular_start_time']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['ttbm_particular_start_time'])) : array();
					$row_count      = max(count($checkin_dates), count($checkout_dates), count($checkin_times));
					$has_complete   = false;

					for ($index = 0; $index < $row_count; $index++) {
						$start_date = trim($checkin_dates[ $index ] ?? '');
						$start_time = trim($checkin_times[ $index ] ?? '');
						$end_date   = trim($checkout_dates[ $index ] ?? '');

						if ($start_date === '' && $start_time === '' && $end_date === '') {
							continue;
						}

						if ($start_date === '' || $start_time === '' || $end_date === '') {
							return __('Each particular date entry must include check-in date, check-in time, and check-out date.', 'tour-booking-manager');
						}

						$has_complete = true;
					}

					if (!$has_complete) {
						return __('At least one particular date entry with check-in date, check-in time, and check-out date is required.', 'tour-booking-manager');
					}
				} elseif ($travel_type === 'repeated') {
					$missing = array();
					$start_date_submitted = array_key_exists('ttbm_travel_repeated_start_date', $_POST);
					$start_date = $start_date_submitted ? trim(sanitize_text_field(wp_unslash($_POST['ttbm_travel_repeated_start_date']))) : '';
					if (!$start_date_submitted) {
						$start_date = trim((string) TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_date', ''));
					}
					$start_time_submitted = array_key_exists('ttbm_travel_repeated_start_time', $_POST);
					$start_time = $start_time_submitted ? trim(sanitize_text_field(wp_unslash($_POST['ttbm_travel_repeated_start_time']))) : '';
					if (!$start_time_submitted && isset($_POST['ttbm_travel_start_time'])) {
						$start_time = trim(sanitize_text_field(wp_unslash($_POST['ttbm_travel_start_time'])));
						$start_time_submitted = true;
					}
					if (!$start_time_submitted) {
						$start_time = trim((string) TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_start_time', ''));
					}
					$repeat_type_submitted = array_key_exists('ttbm_repeat_type', $_POST);
					$repeat_type = $repeat_type_submitted ? trim(sanitize_text_field(wp_unslash($_POST['ttbm_repeat_type']))) : '';
					if (!$repeat_type_submitted) {
						$repeat_type = trim((string) TTBM_Global_Function::get_post_info($tour_id, 'ttbm_repeat_type', ''));
					}

					if ($start_date === '') {
						$missing[] = __('Start Date', 'tour-booking-manager');
					}
					if ($start_time === '') {
						$missing[] = __('Start Time', 'tour-booking-manager');
					}
					if ($repeat_type === '') {
						$missing[] = __('End Repeat Logic', 'tour-booking-manager');
					} elseif ($repeat_type === 'fixed') {
						$end_date_submitted = array_key_exists('ttbm_travel_repeated_end_date', $_POST);
						$end_date = $end_date_submitted ? trim(sanitize_text_field(wp_unslash($_POST['ttbm_travel_repeated_end_date']))) : '';
						if (!$end_date_submitted) {
							$end_date = trim((string) TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_repeated_end_date', ''));
						}
						if ($end_date === '') {
							$missing[] = __('End Date', 'tour-booking-manager');
						}
					}

					if (!empty($missing)) {
						return sprintf(
							/* translators: %s: comma-separated field labels */
							__('Repeated tour dates require: %s.', 'tour-booking-manager'),
							implode(', ', $missing)
						);
					}
				}

				return null;
			}
			public function render_tickets_required_notice(): void {
				$user_id = get_current_user_id();
				$message = get_transient('ttbm_tickets_required_' . $user_id);
				if (!$message) {
					return;
				}
				delete_transient('ttbm_tickets_required_' . $user_id);
				$row_number = 0;
				if (preg_match('/row (\d+)/i', (string) $message, $matches)) {
					$row_number = (int) $matches[1];
				}
				?>
				<div id="ttbm-tickets-required-notice" class="notice notice-error is-dismissible" data-row="<?php echo esc_attr($row_number); ?>">
					<p><strong><?php esc_html_e('Ticket configuration is incomplete.', 'tour-booking-manager'); ?></strong>
					<span data-message><?php echo esc_html($message); ?></span></p>
				</div>
				<?php
			}
			private function is_ticket_row_empty(string $name, string $price, string $capacity): bool {
				return $name === '' && $price === '' && $capacity === '';
			}

			private function resolve_ticket_hidden_text(string $name, string $hidden, int $tour_id): string {
				if ($hidden !== '') {
					return $hidden;
				}
				if ($name === '') {
					return '';
				}
				return preg_replace('/[{}()<>+ ]/', '_', $name) . '_' . $tour_id;
			}

			private function validate_ticket_fields(int $tour_id): ?string {
				$registration = isset($_POST['ttbm_display_registration']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_registration'])) ? 'on' : 'off';
				if ($registration !== 'on') {
					return null;
				}
				$tour_type = isset($_POST['ttbm_type']) ? sanitize_text_field(wp_unslash($_POST['ttbm_type'])) : 'general';
				if ($tour_type !== 'general') {
					return null;
				}

				$names         = isset($_POST['ticket_type_name']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['ticket_type_name'])) : array();
				$hidden_texts  = isset($_POST['ttbm_hidden_ticket_text']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['ttbm_hidden_ticket_text'])) : array();
				$ticket_price  = isset($_POST['ticket_type_price']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['ticket_type_price'])) : array();
				$qty           = isset($_POST['ticket_type_qty']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['ticket_type_qty'])) : array();
				$row_count     = max(count($names), count($hidden_texts), count($ticket_price), count($qty));
				$has_complete  = false;

				for ($index = 0; $index < $row_count; $index++) {
					$name     = trim($names[ $index ] ?? '');
					$hidden   = trim($hidden_texts[ $index ] ?? '');
					$price    = trim($ticket_price[ $index ] ?? '');
					$capacity = trim($qty[ $index ] ?? '');

					if ($this->is_ticket_row_empty($name, $price, $capacity)) {
						continue;
					}

					$hidden = $this->resolve_ticket_hidden_text($name, $hidden, $tour_id);

					$missing = array();
					if ($hidden === '') {
						$missing[] = __('Ticket ID', 'tour-booking-manager');
					}
					if ($price === '') {
						$missing[] = __('Reg. Price', 'tour-booking-manager');
					}
					if ($capacity === '') {
						$missing[] = __('Capacity', 'tour-booking-manager');
					}
					if ($name === '') {
						$missing[] = __('Ticket Name', 'tour-booking-manager');
					}

					if (!empty($missing)) {
						return sprintf(
							/* translators: 1: row number, 2: comma-separated field labels */
							__('Ticket type row %1$d is incomplete. Required: %2$s.', 'tour-booking-manager'),
							$index + 1,
							implode(', ', $missing)
						);
					}

					if (!is_numeric($price) || (float) $price < 0) {
						return sprintf(
							/* translators: %d: row number */
							__('Ticket type row %d: Reg. Price must be a valid number.', 'tour-booking-manager'),
							$index + 1
						);
					}

					if (!is_numeric($capacity) || (int) $capacity < 0) {
						return sprintf(
							/* translators: %d: row number */
							__('Ticket type row %d: Capacity must be a valid number.', 'tour-booking-manager'),
							$index + 1
						);
					}

					$has_complete = true;
				}

				if (!$has_complete) {
					return __('At least one ticket type with Ticket Name, Reg. Price, and Capacity is required when registration is enabled.', 'tour-booking-manager');
				}

				return null;
			}
			private function collect_save_validation_errors(int $tour_id): array {
				$errors = array();
				$user_id = get_current_user_id();

				$submitted_title = class_exists('TTBM_Settings_Hotel')
					? TTBM_Settings_Hotel::resolve_submitted_title_from_request()
					: (isset($_POST['ttbm_post_title_ui'])
						? trim(sanitize_text_field(wp_unslash($_POST['ttbm_post_title_ui'])))
						: (isset($_POST['post_title']) ? trim(sanitize_text_field(wp_unslash($_POST['post_title']))) : ''));
				if ($submitted_title === '') {
					$errors[] = array(
						'transient' => 'ttbm_title_required_' . $user_id,
						'message'   => 1,
					);
				}

				$location_enabled = isset($_POST['ttbm_display_location']) && in_array(sanitize_text_field(wp_unslash($_POST['ttbm_display_location'])), array('on', '1', 'yes', 'true'), true);
				$location_value   = isset($_POST['ttbm_location_name']) ? sanitize_text_field(wp_unslash($_POST['ttbm_location_name'])) : '';
				if ($location_enabled && $location_value === '') {
					$errors[] = array(
						'transient' => 'ttbm_location_required_' . $user_id,
						'message'   => 1,
					);
				}

				if (isset($_POST['_thumbnail_id'])) {
					$thumb_id = (int) $_POST['_thumbnail_id'];
				} else {
					$thumb_id = (int) get_post_thumbnail_id($tour_id);
				}
				if ($thumb_id <= 0) {
					$errors[] = array(
						'transient' => 'ttbm_featured_image_required_' . $user_id,
						'message'   => 1,
					);
				}

				$date_error = $this->validate_date_fields($tour_id);
				if ($date_error) {
					$errors[] = array(
						'transient' => 'ttbm_dates_required_' . $user_id,
						'message'   => $date_error,
					);
				}

				$ticket_error = $this->validate_ticket_fields($tour_id);
				if ($ticket_error) {
					$errors[] = array(
						'transient' => 'ttbm_tickets_required_' . $user_id,
						'message'   => $ticket_error,
					);
				}

				return $errors;
			}

			private function apply_save_validation_notices(array $errors): void {
				foreach ($errors as $error) {
					set_transient($error['transient'], $error['message'], 60);
				}
			}

			private function set_tour_draft_on_validation_failure(int $tour_id): void {
				if ('draft' === get_post_status($tour_id)) {
					return;
				}
				remove_action('save_post', array($this, 'capture_date_migration_snapshot'), 5);
				remove_action('save_post', array($this, 'save_settings'), 99);
				remove_action('save_post', array($this, 'sync_bookings_after_date_change'), 120);
				wp_update_post(
					array(
						'ID'          => $tour_id,
						'post_status' => 'draft',
					)
				);
				add_action('save_post', array($this, 'capture_date_migration_snapshot'), 5, 1);
				add_action('save_post', array($this, 'save_settings'), 99, 1);
				add_action('save_post', array($this, 'sync_bookings_after_date_change'), 120, 1);
			}

			/**
			 * Persist every switch rendered by the tour editor, including unchecked
			 * switches (which browsers normally omit from form submissions).
			 *
			 * Individual legacy save blocks remain in place for compatibility; this
			 * final authoritative pass prevents a tab-specific fallback from restoring
			 * an old "on" value after the user switched it off.
			 */
			private function save_rendered_toggle_states(int $tour_id): void {
				$rendered = isset($_POST['_ttbm_toggle_fields'])
					? array_map('sanitize_key', wp_unslash((array) $_POST['_ttbm_toggle_fields']))
					: array();
				if (empty($rendered)) {
					return;
				}

				$allowed = apply_filters('ttbm_tour_toggle_meta_keys', array(
					'ttbm_display_duration_night',
					'ttbm_display_price_start',
					'ttbm_display_max_people',
					'ttbm_display_min_age',
					'ttbm_display_start_location',
					'ttbm_display_location',
					'ttbm_display_map',
					'ttbm_display_description',
					'ttbm_travel_language_status',
					'ttbm_display_seat_details',
					'ttbm_display_hotels',
					'ttbm_display_duration',
					'ttbm_display_enquiry',
					'ttbm_auto_related_tour',
					'ttbm_display_tour_type',
					'ttbm_display_sidebar',
					'ttbm_display_order_tour',
					'ttbm_display_faq',
					'ttbm_display_activities',
					'ttbm_display_top_picks_deals',
					'ttbm_display_schedule',
					'ttbm_display_admin_note',
					'ttbm_display_registration',
					'ttbm_display_slider',
					'ttbm_display_hiphop',
					'ttbm_display_get_question',
					'ttbm_display_tour_guide',
					'ttbm_display_why_choose_us',
					'ttbm_display_related',
					'ttbm_display_include_service',
					'ttbm_display_exclude_service',
					'mep_disable_ticket_time',
					'ttbm_enable_off_schedule',
				));
				$allowed = array_fill_keys(array_map('sanitize_key', (array) $allowed), true);
				$yes_no_keys = array('mep_disable_ticket_time', 'ttbm_enable_off_schedule');

				foreach (array_unique($rendered) as $meta_key) {
					if (!isset($allowed[$meta_key])) {
						continue;
					}
					$is_on = isset($_POST[$meta_key]) && 'on' === sanitize_text_field(wp_unslash($_POST[$meta_key]));
					if (in_array($meta_key, $yes_no_keys, true)) {
						update_post_meta($tour_id, $meta_key, $is_on ? 'yes' : 'no');
					} else {
						update_post_meta($tour_id, $meta_key, $is_on ? 'on' : 'off');
					}
				}
			}

			//********************//
			public function save_settings($tour_id) {
				if (!$this->is_tour_settings_save_request($tour_id)) {
					return;
				}
				if (self::$saving_settings) {
					return;
				}
				self::$saving_settings = true;
				$validation_errors = $this->collect_save_validation_errors($tour_id);
				/*******Genarel********/
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$submitted_title = class_exists('TTBM_Settings_Hotel')
						? TTBM_Settings_Hotel::resolve_submitted_title_from_request()
						: (isset($_POST['ttbm_post_title_ui'])
							? trim(sanitize_text_field(wp_unslash($_POST['ttbm_post_title_ui'])))
							: (isset($_POST['post_title']) ? trim(sanitize_text_field(wp_unslash($_POST['post_title']))) : ''));
					if (!self::$is_autosave && $submitted_title !== '' && (string) get_post_field('post_title', $tour_id) !== $submitted_title) {
						remove_action('save_post', array($this, 'save_settings'), 99);
						wp_update_post([
							'ID' => $tour_id,
							'post_title' => $submitted_title,
						]);
						add_action('save_post', array($this, 'save_settings'), 99, 1);
					}
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
					if ( ! isset( $_POST['ttbm_display_price_start'] ) ) {
						$existing_display_price_start = get_post_meta( $tour_id, 'ttbm_display_price_start', true );
						$ttbm_display_price_start     = $existing_display_price_start ? $existing_display_price_start : 'on';
					}
					$ttbm_travel_start_price = isset($_POST['ttbm_travel_start_price']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_start_price'])) : '';
					if ( ! isset( $_POST['ttbm_travel_start_price'] ) ) {
						$ttbm_travel_start_price = get_post_meta( $tour_id, 'ttbm_travel_start_price', true );
					}
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
					$language = isset($_POST['ttbm_travel_language']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_travel_language'])) : array('en_US');
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
					$ttbm_display_map = isset($_POST['ttbm_display_map']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_map'])) ? 'on' : 'off';
					if ( $ttbm_display_location === 'off' ) {
						$ttbm_display_map = 'off';
					}
					update_post_meta($tour_id, 'ttbm_display_location', $ttbm_display_location);
					update_post_meta($tour_id, 'ttbm_location_name', $ttbm_location_name);
					$location = get_term_by('name', $ttbm_location_name, 'ttbm_tour_location');
					$ttbm_country_name = '';
					if ($location && isset($location->term_id)) {
						$ttbm_country_name = get_term_meta($location->term_id, 'ttbm_country_location', true);
					}
					update_post_meta($tour_id, 'ttbm_country_name', $ttbm_country_name);
					/***************/
					$previous_full_location = (string) get_post_meta($tour_id, 'ttbm_full_location_name', true);
					$previous_map_latitude = (string) get_post_meta($tour_id, 'ttbm_map_latitude', true);
					$previous_map_longitude = (string) get_post_meta($tour_id, 'ttbm_map_longitude', true);
					$ttbm_full_location_name = isset($_POST['ttbm_full_location_name'])
						? sanitize_textarea_field(wp_unslash($_POST['ttbm_full_location_name']))
						: $previous_full_location;
					// Visible UI field (backup when hidden submit field was stale/disabled).
					if (isset($_POST['ttbm_full_location_name_ui'])) {
						$ui_location = sanitize_textarea_field(wp_unslash($_POST['ttbm_full_location_name_ui']));
						if ('' !== $ui_location) {
							$ttbm_full_location_name = $ui_location;
						}
					}
					// Prefer UI lat/lng fields when present (same value as the map inputs above the fold).
					if (isset($_POST['ttbm_map_latitude_ui']) && '' !== trim((string) wp_unslash($_POST['ttbm_map_latitude_ui']))) {
						$map_latitude = sanitize_text_field(wp_unslash($_POST['ttbm_map_latitude_ui']));
					} else {
						$map_latitude = array_key_exists('ttbm_map_latitude', $_POST)
							? sanitize_text_field(wp_unslash($_POST['ttbm_map_latitude']))
							: $previous_map_latitude;
					}
					if (isset($_POST['ttbm_map_longitude_ui']) && '' !== trim((string) wp_unslash($_POST['ttbm_map_longitude_ui']))) {
						$map_longitude = sanitize_text_field(wp_unslash($_POST['ttbm_map_longitude_ui']));
					} else {
						$map_longitude = array_key_exists('ttbm_map_longitude', $_POST)
							? sanitize_text_field(wp_unslash($_POST['ttbm_map_longitude']))
							: $previous_map_longitude;
					}
					if (class_exists('TTBM_Settings_Location')) {
						list($map_latitude, $map_longitude) = TTBM_Settings_Location::resolve_map_coordinates(
							$ttbm_full_location_name,
							$map_latitude,
							$map_longitude,
							$previous_full_location
						);
					}
					update_post_meta($tour_id, 'ttbm_display_map', $ttbm_display_map);
					update_post_meta($tour_id, 'ttbm_full_location_name', $ttbm_full_location_name);
					update_post_meta($tour_id, 'ttbm_map_latitude', $map_latitude);
					update_post_meta($tour_id, 'ttbm_map_longitude', $map_longitude);
				}
				//*********Date**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$ttbm_travel_type = isset($_POST['ttbm_travel_type']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_type'])) : '';
					update_post_meta($tour_id, 'ttbm_travel_type', $ttbm_travel_type);
					/***************/
					$ttbm_travel_start_date = isset($_POST['ttbm_travel_start_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_start_date'])) : '';
					$ttbm_travel_start_time = isset($_POST['ttbm_travel_start_time']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_start_time'])) : '';
					$ttbm_travel_start_date = $ttbm_travel_start_date ? gmdate('Y-m-d', strtotime($ttbm_travel_start_date)) : '';
					update_post_meta($tour_id, 'ttbm_travel_start_date', $ttbm_travel_start_date);
					update_post_meta($tour_id, 'ttbm_travel_start_time', $ttbm_travel_start_time);
					$ttbm_travel_end_time = isset($_POST['ttbm_travel_end_time']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_end_time'])) : '';
					$ttbm_travel_end_date = isset($_POST['ttbm_travel_end_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_end_date'])) : '';
					$ttbm_travel_end_date = $ttbm_travel_end_date ? gmdate('Y-m-d', strtotime($ttbm_travel_end_date)) : '';
					update_post_meta($tour_id, 'ttbm_travel_end_date', $ttbm_travel_end_date);
					update_post_meta($tour_id, 'ttbm_travel_end_time', $ttbm_travel_end_time);
					$reg_end_time = isset($_POST['reg_end_time']) ? sanitize_text_field(wp_unslash($_POST['reg_end_time'])) : '';
					$ttbm_travel_reg_end_date = isset($_POST['ttbm_travel_reg_end_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_reg_end_date'])) : '';
					$ttbm_travel_reg_end_date = $ttbm_travel_reg_end_date ? gmdate('Y-m-d', strtotime($ttbm_travel_reg_end_date)) : '';
					update_post_meta($tour_id, 'ttbm_travel_reg_end_date', $ttbm_travel_reg_end_date);
					update_post_meta($tour_id, 'reg_end_time', $reg_end_time);
					/***************/
					$particular_dates = [];
					$checkin_dates = isset($_POST['ttbm_particular_start_date']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_particular_start_date'])) : [];
					$checkout_dates = isset($_POST['ttbm_particular_end_date']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_particular_end_date'])) : [];
					$checkin_times = isset($_POST['ttbm_particular_start_time']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_particular_start_time'])) : [];
					if (sizeof($checkin_dates) > 0) {
						foreach ($checkin_dates as $key => $checkin_date) {
							if ($checkin_date) {
								$particular_dates[$key]['ttbm_particular_start_date'] = gmdate('Y-m-d', strtotime($checkin_date));
								$particular_dates[$key]['ttbm_particular_end_date'] = array_key_exists($key, $checkout_dates) && $checkout_dates[$key] ? gmdate('Y-m-d', strtotime($checkout_dates[$key])) : '';
								$particular_dates[$key]['ttbm_particular_start_time'] = array_key_exists($key, $checkin_times) && $checkin_times[$key] ? $checkin_times[$key] : '';
							}
						}
					}
					update_post_meta($tour_id, 'ttbm_particular_dates', $particular_dates);
					/***************/
					$ttbm_travel_repeated_start_date = isset($_POST['ttbm_travel_repeated_start_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_repeated_start_date'])) : '';
					update_post_meta($tour_id, 'ttbm_travel_repeated_start_date', $ttbm_travel_repeated_start_date);
					$ttbm_travel_repeated_start_time = isset($_POST['ttbm_travel_repeated_start_time']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_repeated_start_time'])) : '';
					update_post_meta($tour_id, 'ttbm_travel_repeated_start_time', $ttbm_travel_repeated_start_time);
					// Repeated tours read their effective start time from ttbm_travel_start_time
					// (see TTBM_Function::get_time()), while the repeated tab writes to
					// ttbm_travel_repeated_start_time. Mirror the value so the time the admin
					// entered on the repeated tab actually applies on the frontend and is not
					// overwritten by the blank fixed-tab time field.
					if ($ttbm_travel_type === 'repeated' && $ttbm_travel_repeated_start_time !== '') {
						$ttbm_travel_start_time = $ttbm_travel_repeated_start_time;
						update_post_meta($tour_id, 'ttbm_travel_start_time', $ttbm_travel_start_time);
					}
					$ttbm_travel_repeated_after = isset($_POST['ttbm_travel_repeated_after']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_repeated_after'])) : 1;
					update_post_meta($tour_id, 'ttbm_travel_repeated_after', $ttbm_travel_repeated_after);
					$ttbm_repeat_type = isset($_POST['ttbm_repeat_type']) ? sanitize_text_field(wp_unslash($_POST['ttbm_repeat_type'])) : '';
					update_post_meta($tour_id, 'ttbm_repeat_type', $ttbm_repeat_type);
					$ttbm_repeat_number = isset($_POST['ttbm_repeat_number']) ? sanitize_text_field(wp_unslash($_POST['ttbm_repeat_number'])) : '';
					update_post_meta($tour_id, 'ttbm_repeat_number', $ttbm_repeat_number);
					$ttbm_travel_repeated_end_date = isset($_POST['ttbm_travel_repeated_end_date']) ? sanitize_text_field(wp_unslash($_POST['ttbm_travel_repeated_end_date'])) : '';
					if ($ttbm_repeat_type === 'occurrence') {
						if ($ttbm_travel_repeated_start_date) {
							$day_count = max(1, (int) $ttbm_repeat_number) * max(1, (int) $ttbm_travel_repeated_after);
							$ttbm_travel_repeated_end_date = gmdate('Y-m-d', strtotime($ttbm_travel_repeated_start_date . ' +' . $day_count . ' day'));
						} else {
							$ttbm_travel_repeated_end_date = '';
						}
					} elseif ($ttbm_repeat_type === 'fixed') {
						if ($ttbm_travel_repeated_end_date) {
							$parsed_date = strtotime($ttbm_travel_repeated_end_date);
							$ttbm_travel_repeated_end_date = ($parsed_date !== false) ? gmdate('Y-m-d', $parsed_date) : '';
						}
					} elseif ($ttbm_repeat_type === 'continue') {
						$ttbm_travel_repeated_end_date = '';
					}
					update_post_meta($tour_id, 'ttbm_travel_repeated_end_date', $ttbm_travel_repeated_end_date);
					$display_time = isset($_POST['mep_disable_ticket_time']) && sanitize_text_field(wp_unslash($_POST['mep_disable_ticket_time'])) ? 'yes' : 'no';
					update_post_meta($tour_id, 'mep_disable_ticket_time', $display_time);
					$enable_off_schedule = isset($_POST['ttbm_enable_off_schedule']) && sanitize_text_field(wp_unslash($_POST['ttbm_enable_off_schedule'])) ? 'yes' : 'no';
					update_post_meta($tour_id, 'ttbm_enable_off_schedule', $enable_off_schedule);
					$all_time_slot_infos = TTBM_Settings_Dates::time_slot_array();
					//echo '<pre>';print_r($all_time_slot_infos);echo '</pre>';die();
					if (sizeof($all_time_slot_infos) > 0) {
						foreach ($all_time_slot_infos as $meta_key => $value) {
							$label_key = array_key_exists('label_key', $value) && $value['label_key'] ? $value['label_key'] : '';
							$time_key = array_key_exists('time_key', $value) && $value['time_key'] ? $value['time_key'] : '';
							$default_time_info = [];
							$default_labels = isset($_POST[$label_key]) ? array_map('sanitize_text_field', wp_unslash($_POST[$label_key])) : [];
							$default_times = isset($_POST[$time_key]) ? array_map('sanitize_text_field', wp_unslash($_POST[$time_key])) : [];
							if (sizeof($default_times) > 0) {
								foreach ($default_times as $key => $default_time) {
									if ($default_time) {
										$default_time_info[$key]['mep_ticket_time_name'] = array_key_exists($key, $default_labels) && $default_labels[$key] ? $default_labels[$key] : '';
										$default_time_info[$key]['mep_ticket_time'] = $default_time;
									}
								}
							}
							//echo '<pre>';print_r($default_time_info);echo '</pre>';die();
							update_post_meta($tour_id, $meta_key, $default_time_info);
						}
					}
					/***************/
					$off_days = isset($_POST['mep_ticket_offdays']) ? sanitize_text_field(wp_unslash($_POST['mep_ticket_offdays'])) : '';
					$off_days = $off_days ? explode(',', $off_days) : '';
					update_post_meta($tour_id, 'mep_ticket_offdays', $off_days);
					$all_off_dates = [];
					$off_dates = isset($_POST['mep_ticket_off_dates']) ? array_map('sanitize_text_field', wp_unslash($_POST['mep_ticket_off_dates'])) : [];
					if (sizeof($off_dates) > 0) {
						foreach ($off_dates as $key => $off_date) {
							if ($off_date) {
								$all_off_dates[$key]['mep_ticket_off_date'] = $off_date;
							}
						}
					}
					update_post_meta($tour_id, 'mep_ticket_off_dates', $all_off_dates);
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
					$auto_related_tour = isset($_POST['ttbm_auto_related_tour']) && sanitize_text_field(wp_unslash($_POST['ttbm_auto_related_tour'])) ? 'on' : 'off';
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
					update_post_meta($tour_id, 'ttbm_auto_related_tour', $auto_related_tour);
					//*********FAQ**************//
					$faq = isset($_POST['ttbm_display_faq']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_faq'])) ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_faq', $faq);
					$display_activities = isset($_POST['ttbm_display_activities']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_activities'])) ? 'on' : 'off';
					$display_top_picks_deals = isset($_POST['ttbm_display_top_picks_deals']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_top_picks_deals'])) === 'on' ? 'on' : 'off';
					$top_picks_deals = array();
					$allowed_top_picks = array('feature', 'popular', 'trending', 'deal-discount');
					if (isset($_POST['ttbm_top_picks_deals']) && is_array($_POST['ttbm_top_picks_deals'])) {
						$top_picks_deals = array_map('sanitize_text_field', wp_unslash($_POST['ttbm_top_picks_deals']));
					}
					if (empty($top_picks_deals) && isset($_POST['ttbm_checked_top_picks_deals_holder'])) {
						$holder = sanitize_text_field(wp_unslash($_POST['ttbm_checked_top_picks_deals_holder']));
						if ($holder !== '') {
							$top_picks_deals = array_map('sanitize_text_field', explode(',', $holder));
						}
					}
					$top_picks_deals = array_values(array_intersect($top_picks_deals, $allowed_top_picks));
					//*********Activities**************//
					update_post_meta($tour_id, 'ttbm_display_activities', $display_activities);
					update_post_meta($tour_id, 'ttbm_display_top_picks_deals', $display_top_picks_deals);
					update_post_meta($tour_id, 'ttbm_top_picks_deals', $top_picks_deals);
					$activities = [];
					if (isset($_POST['ttbm_checked_activities_holder'])) {
						$activities_holder = sanitize_text_field(wp_unslash($_POST['ttbm_checked_activities_holder']));
						if ($activities_holder !== '') {
							$activities = array_map('sanitize_text_field', explode(',', $activities_holder));
						}
					} elseif (isset($_POST['ttbm_tour_activities'])) {
						$activities = array_map('sanitize_text_field', wp_unslash((array) $_POST['ttbm_tour_activities']));
					}
					update_post_meta($tour_id, 'ttbm_tour_activities', $activities);
					//*********Itenary**************//
					$daywise = isset($_POST['ttbm_display_schedule']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_schedule'])) ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_schedule', $daywise);
					//*********Admin note**************//
					$display_note = isset($_POST['ttbm_display_admin_note']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_admin_note'])) ? 'on' : 'off';
					$note = isset($_POST['ttbm_admin_note']) ? sanitize_text_field(wp_unslash($_POST['ttbm_admin_note'])) : '';
					update_post_meta($tour_id, 'ttbm_display_admin_note', $display_note);
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
						$price_value = trim($ticket_price[ $i ] ?? '');
						$capacity_value = trim($qty[ $i ] ?? '');
						$requires_capacity = ($registration === 'on' && $tour_type === 'general');
						if ($requires_capacity && ($price_value === '' || $capacity_value === '')) {
							continue;
						}
						if ($names[$i] && $price_value !== '' && is_numeric($price_value) && (float) $price_value >= 0) {
							if ($capacity_value === '') {
								$capacity_value = 0;
							}
							
							$new_ticket_type[$i]['ticket_type_icon'] = $icon[$i] ?? '';
							$new_ticket_type[$i]['ticket_type_name'] = $names[$i];
							$new_ticket_type[$i]['ticket_type_price'] = $ticket_price[$i];
							$new_ticket_type[$i]['sale_price'] = $sale_price[$i];
							$new_ticket_type[$i]['ticket_type_qty'] = $capacity_value;
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
					$slider = ( isset( $_POST['ttbm_display_slider'] ) && 'on' === sanitize_text_field( wp_unslash( $_POST['ttbm_display_slider'] ) ) ) ? 'on' : 'off';
					update_post_meta($tour_id, 'ttbm_display_slider', $slider);
					if ( isset( $_POST['ttbm_gallery_images'] ) ) {
						$images     = sanitize_text_field( wp_unslash( $_POST['ttbm_gallery_images'] ) );
						$all_images = array_values( array_filter( array_map( 'absint', explode( ',', $images ) ) ) );
						update_post_meta( $tour_id, 'ttbm_gallery_images', $all_images );
					}
				}
				//*********Place you see**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$place_info = array();
					$hiphop     = isset($_POST['ttbm_display_hiphop']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_hiphop'])) ? 'on' : 'off';
					if ( ! isset( $_POST['ttbm_display_hiphop'] ) ) {
						$existing_hiphop = get_post_meta( $tour_id, 'ttbm_display_hiphop', true );
						$hiphop          = ( $existing_hiphop === 'on' ) ? 'on' : 'off';
					}
					update_post_meta($tour_id, 'ttbm_display_hiphop', $hiphop);
					if ( isset( $_POST['ttbm_place_label'] ) || isset( $_POST['ttbm_city_place_id'] ) ) {
						$place_labels = isset($_POST['ttbm_place_label']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_place_label'])) : [];
						$place_ids    = isset($_POST['ttbm_city_place_id']) ? array_map('sanitize_text_field', wp_unslash($_POST['ttbm_city_place_id'])) : [];
						if (sizeof($place_ids) > 0) {
							foreach ($place_ids as $key => $place_id) {
								if ($place_id && $place_id > 0) {
									$place_name = $place_labels[$key];
									$place_info[$key]['ttbm_city_place_id'] = $place_id;
									$place_info[$key]['ttbm_place_label']   = $place_name ?: get_the_title($place_id);
								}
							}
						}
						update_post_meta($tour_id, 'ttbm_hiphop_places', $place_info);
					}
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
				//*********features**************//
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$display_feature = isset($_POST['ttbm_display_include_service']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_include_service'])) ? 'on' : 'off';
					$feature = isset($_POST['ttbm_service_included_in_price']) ? sanitize_text_field(wp_unslash($_POST['ttbm_service_included_in_price'])) : '';
					$feature_info = TTBM_Function::feature_id_to_array($feature);
					update_post_meta($tour_id, 'ttbm_display_include_service', $display_feature);
					update_post_meta($tour_id, 'ttbm_service_included_in_price', $feature_info);
					$display_ex_feature = isset($_POST['ttbm_display_exclude_service']) && sanitize_text_field(wp_unslash($_POST['ttbm_display_exclude_service'])) ? 'on' : 'off';
					$feature_ex = isset($_POST['ttbm_service_excluded_in_price']) ? sanitize_text_field(wp_unslash($_POST['ttbm_service_excluded_in_price'])) : '';
					$feature_info_ex = TTBM_Function::feature_id_to_array($feature_ex);
					update_post_meta($tour_id, 'ttbm_display_exclude_service', $display_ex_feature);
					update_post_meta($tour_id, 'ttbm_service_excluded_in_price', $feature_info_ex);
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
					$extra_sale_price = isset($_POST['service_sale_price']) ? array_map('sanitize_text_field', wp_unslash($_POST['service_sale_price'])) : [];
					$extra_count = count($extra_names);
					for ($i = 0; $i < $extra_count; $i++) {
						if ($extra_names[$i] && $extra_price[$i] >= 0 && $extra_qty[$i] > 0) {
							$new_extra_service[$i]['service_icon'] = $extra_icon[$i] ?? '';
							$new_extra_service[$i]['service_name'] = $extra_names[$i];
							$new_extra_service[$i]['service_price'] = $extra_price[$i];
							$new_extra_service[$i]['service_sale_price'] = $extra_sale_price[$i] ?? '';
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
				if ($this->has_date_schedule_changed($tour_id)) {
					TTBM_Function::update_upcoming_date_month($tour_id, true);
				} else {
					TTBM_Function::update_upcoming_date_month($tour_id);
				}
				$this->save_rendered_toggle_states($tour_id);
				self::$last_validation_errors = $validation_errors;
				// During a background auto-save we never unpublish the post or leave a
				// one-shot admin notice behind — the browser is told about any missing
				// required fields over JSON and surfaces them inline instead.
				if (!self::$is_autosave) {
					$this->apply_save_validation_notices($validation_errors);
					if (!empty($validation_errors)) {
						$this->set_tour_draft_on_validation_failure($tour_id);
					}
				}
				self::$saving_settings = false;
			}
			/**
			 * Background auto-save endpoint. Runs the exact same save pipeline a manual
			 * Update runs (snapshot -> save_settings -> booking/order date migration) so
			 * live date changes stay in sync, but without a page reload and without the
			 * force-to-draft behaviour. Guarded by a per-post lock so overlapping saves
			 * can never pile up and hammer the server.
			 */
			public function ajax_autosave_tour() {
				if (!check_ajax_referer('ttbm_autosave', 'nonce', false)) {
					wp_send_json_error(array('code' => 'bad_nonce', 'message' => __('Your session expired. Please reload the page.', 'tour-booking-manager')), 403);
				}
				$tour_id = isset($_POST['post_ID']) ? absint($_POST['post_ID']) : 0;
				if (!$tour_id || get_post_type($tour_id) !== TTBM_Function::get_cpt_name()) {
					wp_send_json_error(array('code' => 'bad_post', 'message' => __('Invalid tour.', 'tour-booking-manager')), 400);
				}
				if (!current_user_can('edit_post', $tour_id)) {
					wp_send_json_error(array('code' => 'forbidden', 'message' => __('You are not allowed to edit this tour.', 'tour-booking-manager')), 403);
				}
				if (!isset($_POST['ttbm_travel_type']) || !isset($_POST['post_title'])) {
					wp_send_json_error(array(
						'code'    => 'incomplete_payload',
						'message' => __('The tour form was not included in the auto-save request. Please reload the editor and try again.', 'tour-booking-manager'),
					), 400);
				}
				$lock_key = 'ttbm_autosaving_' . $tour_id;
				if (get_transient($lock_key)) {
					wp_send_json_error(array('code' => 'busy', 'message' => __('A save is already in progress.', 'tour-booking-manager')), 409);
				}
				set_transient($lock_key, 1, 30);
				self::$is_autosave = true;
				self::$last_validation_errors = array();
				try {
					// Featured image: mirror how core applies _thumbnail_id on Update.
					if (isset($_POST['_thumbnail_id'])) {
						$thumb_id = (int) $_POST['_thumbnail_id'];
						if ($thumb_id > 0) {
							set_post_thumbnail($tour_id, $thumb_id);
						} elseif ($thumb_id === -1) {
							delete_post_thumbnail($tour_id);
						}
					}
					$this->capture_date_migration_snapshot($tour_id);
					$this->save_settings($tour_id);
					// Run one normal WordPress update after Core meta is ready. This saves
					// post content/title and lets PRO or other add-ons attached to save_post
					// persist their tab fields too. Detach only this class's three callbacks
					// so the expensive Core pipeline is not executed twice.
					$post_update = array('ID' => $tour_id);
					if (isset($_POST['post_title']) && !is_array($_POST['post_title'])) {
						$post_update['post_title'] = sanitize_text_field(wp_unslash($_POST['post_title']));
					}
					if (isset($_POST['post_content']) && !is_array($_POST['post_content'])) {
						$post_update['post_content'] = wp_kses_post(wp_unslash($_POST['post_content']));
					}
					$requested_status = isset($_POST['requested_post_status']) ? sanitize_key(wp_unslash($_POST['requested_post_status'])) : '';
					$post_type_object = get_post_type_object(TTBM_Function::get_cpt_name());
					$publish_capability = $post_type_object && isset($post_type_object->cap->publish_posts)
						? $post_type_object->cap->publish_posts
						: 'publish_posts';
					if (empty(self::$last_validation_errors) && in_array($requested_status, array('draft', 'pending'), true)) {
						$post_update['post_status'] = $requested_status;
					} elseif (empty(self::$last_validation_errors) && 'publish' === $requested_status && current_user_can($publish_capability)) {
						$post_update['post_status'] = 'publish';
					} elseif (empty(self::$last_validation_errors) && 'publish' === $requested_status) {
						throw new \RuntimeException(__('You are not allowed to publish this tour.', 'tour-booking-manager'));
					}
					remove_action('save_post', array($this, 'capture_date_migration_snapshot'), 5);
					remove_action('save_post', array($this, 'save_settings'), 99);
					remove_action('save_post', array($this, 'sync_bookings_after_date_change'), 120);
					wp_update_post($post_update);
					add_action('save_post', array($this, 'capture_date_migration_snapshot'), 5, 1);
					add_action('save_post', array($this, 'save_settings'), 99, 1);
					add_action('save_post', array($this, 'sync_bookings_after_date_change'), 120, 1);
					$this->sync_bookings_after_date_change($tour_id);
				} catch (\Throwable $e) {
					self::$is_autosave = false;
					delete_transient($lock_key);
					$message = $e->getMessage() ?: __('Auto-save failed. Your changes were not saved.', 'tour-booking-manager');
					wp_send_json_error(array('code' => 'exception', 'message' => $message), 500);
				}
				self::$is_autosave = false;
				delete_transient($lock_key);
				$warnings = array();
				foreach (self::$last_validation_errors as $error) {
					$warnings[] = $this->describe_validation_error($error);
				}
				// If the booking/order date migration hit a capacity conflict it reverts
				// the date and leaves a notice transient meant for the next page load.
				// Consume it here so the browser hears about it right away instead.
				$user_id = get_current_user_id();
				if ($user_id) {
					$migration_notice = get_transient(self::$date_migration_notice_key . $user_id);
					if (is_array($migration_notice) && !empty($migration_notice['message'])) {
						$warnings[] = $migration_notice['message'];
						delete_transient(self::$date_migration_notice_key . $user_id);
					}
				}
				wp_send_json_success(array(
					'saved_at'      => current_time('timestamp'),
					'saved_at_text' => date_i18n(get_option('time_format'), current_time('timestamp')),
					'post_status'   => get_post_status($tour_id),
					'warnings'      => array_values(array_filter($warnings)),
					'persisted'     => array(
						'travel_type' => (string) get_post_meta($tour_id, 'ttbm_travel_type', true),
						'start_time'  => (string) get_post_meta($tour_id, 'ttbm_travel_repeated_start_time', true),
						'repeat_type' => (string) get_post_meta($tour_id, 'ttbm_repeat_type', true),
					),
				));
			}
			/**
			 * Turn a stored validation-error record into a human-readable message for the
			 * auto-save JSON response.
			 */
			private function describe_validation_error(array $error): string {
				$transient = isset($error['transient']) ? (string) $error['transient'] : '';
				if (is_string($error['message'] ?? null) && $error['message'] !== '' && !is_numeric($error['message'])) {
					return (string) $error['message'];
				}
				if (strpos($transient, 'ttbm_title_required_') === 0) {
					return __('Tour title is required.', 'tour-booking-manager');
				}
				if (strpos($transient, 'ttbm_location_required_') === 0) {
					return __('Tour location is required.', 'tour-booking-manager');
				}
				if (strpos($transient, 'ttbm_featured_image_required_') === 0) {
					return __('Featured image is required.', 'tour-booking-manager');
				}
				return __('Some required fields are incomplete.', 'tour-booking-manager');
			}
		}
		new TTBM_Settings();
	}
