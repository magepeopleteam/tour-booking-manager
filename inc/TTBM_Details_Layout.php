<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('TTBM_Details_Layout')) {
        class TTBM_Details_Layout {
            public function __construct() {
                add_action('ttbm_details_title', array($this, 'details_title'));
                add_action('ttbm_section_title', array($this, 'section_title'), 10, 2);
                add_action('ttbm_section_titles', array($this, 'section_titles'), 10, 2);
                add_action('ttbm_slider', array($this, 'slider'));
                add_action('ttbm_details_location', array($this, 'details_location'));
                add_action('ttbm_description', array($this, 'description'));
                add_action('ttbm_include_exclude', array($this, 'include_exclude'));
                add_action('ttbm_include_feature', array($this, 'include_feature'));
                add_action('ttbm_exclude_service', array($this, 'exclude_service'));
                add_action('ttbm_short_details', array($this, 'short_details'),10);
                add_action('ttbm_location_map', array($this, 'location_map'), 10, 1);
                add_action('ttbm_smart_activity', array($this, 'smart_activity'));
                add_action('ttbm_activity', array($this, 'activity'));
                add_action('ttbm_hiphop_place', array($this, 'hiphop_place'));
                add_action('ttbm_day_wise_details', array($this, 'day_wise_details'));
                add_action('ttbm_faq', array($this, 'faq'));
                add_action('ttbm_why_choose_us', array($this, 'why_choose_us'));
                add_action('ttbm_get_a_question', array($this, 'get_a_question'));
                add_action('ttbm_tour_guide', array($this, 'tour_guide'));
                add_action('ttbm_details_particular_area', array($this, 'particular_area'));
                //add_action( 'ttbm_hotel_list', array( $this, 'hotel_list' ) );
                add_action('ttbm_related_tour', array($this, 'related_tour'));
                add_action('ttbm_dynamic_sidebar', array($this, 'dynamic_sidebar'), 10, 1);
                add_action('ttbm_registration', array($this, 'ticket_registration'));
            }

            public function ticket_registration(){
                $ttbm_post_id = $ttbm_post_id ?? get_the_id();
	            $tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
                $ttbm_display_registration = $ttbm_display_registration ?? MP_Global_Function::get_post_info( $tour_id, 'ttbm_display_registration', 'on' );
                if ( $ttbm_display_registration != 'off' ) {
                    ?>
                    <div class="ttbm-sidebar-booking">
                        <div class="ttbm-title-price"><?php _e("From",'tour-booking-manager'); ?><?php include(TTBM_Function::template_path('layout/start_price_box.php')); ?></div>
                        
                        <?php TTBM_Details_Layout::date_selection(); ?>
                        <button type="button" class="_dButton_bgBlue_fullWidth" data-target-popup="registration-popup">
                            <span class="fas fa-plus-square"></span>
                            <?php esc_html_e('Book Now','tour-booking-manager'); ?>				
                        </button>
   
                    </div>
                        
                    <?php
                }
            }

            public function date_selection(){
                $ttbm_post_id = $ttbm_post_id ?? get_the_id();
                $tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
                $all_dates     = $all_dates ?? TTBM_Function::get_date( $tour_id );
                $travel_type   = $travel_type ?? TTBM_Function::get_travel_type( $tour_id );
                $tour_type     = $tour_type ?? TTBM_Function::get_tour_type( $tour_id );
                $template_name = $template_name ?? MP_Global_Function::get_post_info( $tour_id, 'ttbm_theme_file', 'default.php' );
                if ( sizeof( $all_dates ) > 0 && $tour_type == 'general' && $travel_type != 'particular' ) {
                    $date          = current( $all_dates );
                    $check_ability = MP_Global_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' );
                    $time          = TTBM_Function::get_time( $tour_id, $date );
                    $time          = is_array( $time ) ? $time[0]['time'] : $time;
                    $date          = $time ? $date . ' ' . $time : $date;
                    $date=$time?date( 'Y-m-d H:i', strtotime( $date) ):date( 'Y-m-d', strtotime( $date) );
                    /************/
                    $date_format = MP_Global_Function::date_picker_format();
                    $now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
                    $hidden_date = $date ? date('Y-m-d', strtotime($date)) : '';
                    $visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
                    ?>
                    <div class="ttbm_registration_area <?php echo esc_attr( $check_ability ); ?>">
                        <input type="hidden" name="ttbm_id" value="<?php echo esc_attr( $tour_id ); ?>"/>
                        <?php
                            if ($travel_type == 'repeated' ) {
                                $time_slots = TTBM_Function::get_time( $tour_id, $all_dates[0] );
                                ?>
                                <div class="">
                                    <div class="">
                                        <label class="_allCenter">
                                            <span class="date-picker-icon">
                                            <i class="far fa-calendar-alt"></i>
                                            <input type="hidden" name="ttbm_date" value="<?php echo esc_attr($hidden_date); ?>" required/>
                                            <input id="ttbm_select_date" type="text" value="<?php echo esc_attr($visible_date); ?>" class="formControl mb-0 " placeholder="<?php echo esc_attr($now); ?>"  readonly required/>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <?php
                                do_action( 'mp_load_date_picker_js', '#ttbm_select_date', $all_dates );
                            }
                        ?>
                        
                    </div>
                <?php } 
            }

            public function particular_area(){
                include( TTBM_Function::template_path( 'ticket/particular_item_area.php' ) );
            }
            public function details_location(){
                include( TTBM_Function::template_path( 'layout/location.php' ) );
            }
            public function details_title() {
                include(TTBM_Function::template_path('layout/title_details_page.php'));
            }
            public function section_title($option_name, $default_title) {
                include(TTBM_Function::template_path('layout/title_section.php'));
            }
            public function section_titles($tour_id, $ttbm_title) {
                include(TTBM_Function::template_path('layout/section_title.php'));
            }
            public function slider() {
                include(TTBM_Function::template_path('layout/slider.php'));
            }
            public function description() {
                include(TTBM_Function::template_path('layout/description.php'));
            }
            //***************Feature************************//
            public function include_exclude() {
                include(TTBM_Function::template_path('layout/include_exclude.php'));
            }
            public function include_feature() {
                include(TTBM_Function::template_path('layout/include_feature.php'));
            }
            public function exclude_service() {
                include(TTBM_Function::template_path('layout/exclude_service.php'));
            }
            //*******************************************//
            public function short_details() {
                $ttbm_post_id = $ttbm_post_id ?? get_the_id();
	            $tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
                $tour_type = $tour_type ?? TTBM_Function::get_tour_type($tour_id);
                if ($tour_type != 'hotel') {
                    $count = 0;
                    ?>
					<div class="item_section">
                        <?php include(TTBM_Function::template_path('layout/duration_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/start_price_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/max_people_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/start_location_box.php')); ?>
                        
                        <?php include(TTBM_Function::template_path('layout/age_range_box.php')); ?>

                        <?php include(TTBM_Function::template_path('layout/seat_info.php')); ?>

                        <?php include(TTBM_Function::template_path('layout/language_box.php')); ?>
                        
					</div>
                    <?php
                }
                // display popup code here
                $this->get_enquiry_pop_up($tour_id);
            }


            public function get_enquiry_pop_up($tour_id) {
                if(isset($_POST['submit'])){
                    $name    = sanitize_text_field($_POST['name']);
                    $email   = sanitize_email($_POST['email']);
                    $subject = sanitize_text_field($_POST['subject']);
                    $message = sanitize_textarea_field($_POST['message']);
                    // Validate email
                    if (!is_email($email)) {
                        echo '<p style="color:red;">' . esc_html__('Invalid email address.', 'tour-booking-manager') . '</p>';
                        return;
                    }
                    $to = get_option('admin_email'); // Sends email to site admin

                    // Email headers
                    $headers = [
                        'From: ' . $name . ' <' . $email . '>',
                        'Reply-To: ' . $email,
                        'Content-Type: text/html; charset=UTF-8',
                    ];

                    // Email content
                    $body = "<h3>" . esc_html__('New Contact Form Submission', 'tour-booking-manager') . "</h3>";
                    $body .= "<p><strong>" . esc_html__('Name:', 'tour-booking-manager') . "</strong> $name</p>";
                    $body .= "<p><strong>" . esc_html__('Email:', 'tour-booking-manager') . "</strong> $email</p>";
                    $body .= "<p><strong>" . esc_html__('Subject:', 'tour-booking-manager') . "</strong> $subject</p>";
                    $body .= "<p><strong>" . esc_html__('Message:', 'tour-booking-manager') . "</strong><br>$message</p>";

                    // Send email
                    if (wp_mail($to, $subject, $body, $headers)) {
                        echo '<p style="color:green;">' . esc_html__('Your message has been sent successfully!', 'tour-booking-manager') . '</p>';
                    } else {
                        echo '<p style="color:red;">' . esc_html__('Failed to send message. Please try again later.', 'tour-booking-manager') . '</p>';
                    }
                }   
                ?>
                    <div class="mpPopup mpStyle" data-popup="get-enquiry-popup">
                        <div class="popupMainArea">
                            <div class="popupHeader allCenter">
                                <h2 class="_mR"><?php esc_html_e('Get Enquiry', 'bus-ticket-booking-with-seat-reservation'); ?></h2>
                                <span class="fas fa-times popupClose"></span>
                            </div>
                            <div class="popupBody">
                                <form action="" method="post">
                                    <fieldset>
                                    <legend><?php esc_html_e('Enquiry', 'tour-booking-manager'); ?></legend>
                                    <div class="get-enquiry-form">
                                        <label for="name"><?php esc_html_e('Name:', 'tour-booking-manager'); ?></label>
                                        <input type="text" name="name" id="name" placeholder="<?php esc_attr_e('Your Name', 'tour-booking-manager'); ?>" required>

                                        <label for="email"><?php esc_html_e('Email:', 'tour-booking-manager'); ?></label>
                                        <input type="email" name="email" id="email" placeholder="<?php esc_attr_e('Your Email', 'tour-booking-manager'); ?>" required>

                                        <label for="subject"><?php esc_html_e('Subject:', 'tour-booking-manager'); ?></label>
                                        <input type="text" name="subject" id="subject" placeholder="<?php esc_attr_e('Subject', 'tour-booking-manager'); ?>" required>

                                        <label for="message"><?php esc_html_e('Message:', 'tour-booking-manager'); ?></label>
                                        <textarea name="message" id="message" placeholder="<?php esc_attr_e('Your Message', 'tour-booking-manager'); ?>" rows="5" required></textarea>

                                        <button class="_dButton_fullWidth" type="submit"><?php esc_html_e('Send Message', 'tour-booking-manager'); ?></button>
                                    </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php
            }

            public function location_map($tour_id) {
                include(TTBM_Function::template_path('layout/location_map.php'));
            }
            //*******************************************//
            public function smart_activity() {
                include(TTBM_Function::template_path('layout/smart_activity.php'));
            }
            public function activity() {
                include(TTBM_Function::template_path('layout/activity.php'));
            }
            public function hiphop_place() {
                include(TTBM_Function::template_path('layout/hiphop_place.php'));
            }
            public function day_wise_details() {
                include(TTBM_Function::template_path('layout/day_wise_details.php'));
            }
            public function faq() {
                include(TTBM_Function::template_path('layout/faq.php'));
            }
            public function why_choose_us() {
                include(TTBM_Function::template_path('layout/why_choose_us.php'));
            }
            public function get_a_question() {
                include(TTBM_Function::template_path('layout/get_a_question.php'));
            }
            public function tour_guide() {
                include(TTBM_Function::template_path('layout/tour_guide.php'));
            }
            public function hotel_list() {
                //include( TTBM_Function::template_path( 'layout/hotel_list.php' ) );
                //echo '<pre>';print_r();echo '</pre>';
            }
            public function related_tour() {
                include(TTBM_Function::template_path('layout/related_tour.php'));
            }
            //********************************************//
            public function dynamic_sidebar($tour_id) {
                if (MP_Global_Function::get_post_info($tour_id, 'ttbm_display_sidebar', 'on') != 'off') {
                    dynamic_sidebar('ttbm_details_sidebar');
                }
            }
        }
        new TTBM_Details_Layout();
    }