<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.

    if (!class_exists('TTBM_Dummy_Import')) {

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        class TTBM_Dummy_Import {
            public function __construct() {
                //update_option('ttbm_dummy_already_inserted','no');
                $this->dummy_import();
            }
            private function dummy_import() {
                $ttbm_dummy_post = get_option('ttbm_dummy_already_inserted');
                $all_post = MP_Global_Function::query_post_type(TTBM_Function::get_cpt_name());
                if ($all_post->post_count == 0 && $ttbm_dummy_post != 'yes') {
                    $dummy_data = $this->dummy_data();
                    foreach ($dummy_data as $type => $dummy) {
                        if ($type == 'taxonomy') {
                            foreach ($dummy as $taxonomy => $dummy_taxonomy) {
                                $check_taxonomy = MP_Global_Function::get_taxonomy($taxonomy);
                                if (is_string($check_taxonomy) || sizeof($check_taxonomy) == 0) {
                                    foreach ($dummy_taxonomy as $taxonomy_data) {
                                        $query = wp_insert_term($taxonomy_data['name'], $taxonomy);
                                        if ($taxonomy == 'ttbm_tour_location') {
                                            if (is_array($query) && $query['term_id'] != '') {
                                                $term_id = $query['term_id'];
                                                update_term_meta($term_id, 'ttbm_country_location', $taxonomy_data['country']);
                                            }
                                        }
                                    }
                                }
                                //echo '<pre>'; print_r( $query); echo '</pre>';
                            }
                        }
                        if ($type == 'custom_post') {
                            foreach ($dummy as $custom_post => $dummy_post) {
                                $post = MP_Global_Function::query_post_type($custom_post);
                                if ($post->post_count == 0) {
                                    foreach ($dummy_post as $dummy_data) {
                                        $title = $dummy_data['name'];
                                        $content = $dummy_data['content'];
                                        $post_id = wp_insert_post([
                                            'post_title' => $title,
                                            'post_content' => $content,
                                            'post_status' => 'publish',
                                            'post_type' => $custom_post
                                        ]);
                                        if (array_key_exists('post_data', $dummy_data)) {
                                            foreach ($dummy_data['post_data'] as $meta_key => $data) {
                                                if ($meta_key == 'feature_image') {

                                                    $url = $data;
                                                    $desc = "The Demo Dummy Image of the event";
                                                    $image = media_sideload_image($url, $post_id, $desc, 'id');
                                                    set_post_thumbnail($post_id, $image);
    
                                                } else {
                                                    update_post_meta($post_id, $meta_key, $data);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    update_option('ttbm_dummy_already_inserted', 'yes');
                }
            }
            public function dummy_data(): array {
                return [
                    'taxonomy' => [
                        'ttbm_tour_cat' => [
                            0 => ['name' => 'Fixed Tour'],
                            1 => ['name' => 'Flexible Tour']
                        ],
                        'ttbm_tour_org' => [
                            0 => ['name' => 'Autotour'],
                            1 => ['name' => 'Holiday Partner'],
                            2 => ['name' => 'Zayman']
                        ],
                        'ttbm_tour_location' => [
                            0 => ['name' => 'Bandarban', 'country' => 'Bangladesh'],
                            1 => ['name' => 'Coxbazar', 'country' => 'Bangladesh'],
                            2 => ['name' => 'Las Vegas', 'country' => 'United States'],
                            3 => ['name' => 'Naples Italy', 'country' => 'Italy'],
                            4 => ['name' => 'Rangamati', 'country' => 'Bangladesh'],
                            5 => ['name' => 'Sajek', 'country' => 'Bangladesh'],
                            6 => ['name' => 'Sapuland', 'country' => 'Afghanistan'],
                        ],
                        'ttbm_tour_features_list' => [
                            0 => ['name' => 'Accommodation'],
                            1 => ['name' => 'Additional Services'],
                            2 => ['name' => 'Airport Transfer'],
                            3 => ['name' => 'BBQ Night'],
                            4 => ['name' => 'Breakfast'],
                            5 => ['name' => 'Concert Ticket'],
                            6 => ['name' => 'Flights'],
                            7 => ['name' => 'Guide'],
                            8 => ['name' => 'Hotel Rent'],
                            9 => ['name' => 'Insurance'],
                            10 => ['name' => 'Lunch'],
                            11 => ['name' => 'Meals'],
                            12 => ['name' => 'Newspaper'],
                            13 => ['name' => 'Outing Ticket'],
                            14 => ['name' => 'Transport'],
                            15 => ['name' => 'Welcome Drinks'],
                        ],
                        'ttbm_tour_tag' => [
                            0 => ['name' => 'Cultural'],
                            1 => ['name' => 'Relax']
                        ],
                        'ttbm_tour_activities' => [
                            0 => ['name' => 'Beach'],
                            1 => ['name' => 'City Tours'],
                            2 => ['name' => 'Hiking'],
                            3 => ['name' => 'Rural'],
                            4 => ['name' => 'Snow & Ice']
                        ],
                    ],
                    'custom_post' => [
                        'ttbm_places' => [
                            0 => ['name' => 'Bogura'],
                            1 => ['name' => 'Dim Pahar'],
                            2 => ['name' => 'Ravello'],
                            3 => ['name' => 'Amalfi'],
                            4 => ['name' => 'Positano'],
                            5 => ['name' => 'Pompeii'],
                            6 => ['name' => 'Capri'],
                            7 => ['name' => 'Sorrento'],
                            8 => ['name' => 'Naples'],
                            9 => ['name' => 'Brandenburger Tor'],
                            10 => ['name' => 'Rotes Rathaus (Neptune Fountain)'],
                            11 => ['name' => 'Alexanderplatz (Alexa)'],
                            12 => ['name' => 'Gendarmenmarkt / Taubenstr'],
                            13 => ['name' => 'Checkpoint Charlie'],
                            14 => ['name' => 'Berliner Mauer / Martin-Gropius-Bau'],
                            15 => ['name' => 'Dolphine Square'],
                            16 => ['name' => 'Moheshkhali'],
                            17 => ['name' => 'Inani Beach'],
                            18 => ['name' => 'Ramu'],
                            19 => ['name' => 'Himchori'],
                        ],
                        'ttbm_guide' => [
                            0 => ['name' => 'Adam Smith'],
                            1 => ['name' => 'Mahim'],
                            2 => ['name' => 'Shamim'],
                            3 => ['name' => 'Sumon'],
                            4 => ['name' => 'Rabiul'],
                        ],
                        'ttbm_tour' => [
                            0 => [
                                'name' => 'The Mentalist Tickets: Las Vegas',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'feature_image' => 'https://img.freepik.com/free-photo/blue-villa-beautiful-sea-hotel_1203-5316.jpg',
                                    'ttbm_travel_duration' => 2,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 3,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => 250,
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 10,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => 'Las Vegas',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => 'Watch Gerry McCambridge perform comedy, magic, and mind reading live on stage at the amazing 75-minute Las Vegas show, The Mentalist! McCambridge has been nominated “Best Magician in Las Vegas”, so come and see him live for a mind-blowing night.',
                                    //date_settings
                                    'ttbm_travel_type' => 'fixed',
                                    'ttbm_travel_start_date' => date('Y-m-d', strtotime(' +25 day')),
                                    'ttbm_travel_reg_end_date' => date('Y-m-d', strtotime(' +30 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 15,
                                            'sale_price' => 10,
                                            'ticket_type_qty' => 150,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 5,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 10,
                                            'sale_price' => 7,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 25,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'Breakfast',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'BBQ Night',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'Beach',
                                        1 => 'Hiking',
                                        2 => 'Snow & Ice',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => [120, 130, 140, 150, 160, 170, 180, 190, 200, 210, 220, 230, 240, 250, 260, 270, 280, 290, 300],
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitage to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'availability_section',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            1 => [
                                'name' => 'Highlights of Naples and the Amalfi Coast',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'feature_image' => 'https://img.freepik.com/free-photo/beautiful-mountains-ratchaprapha-dam-khao-sok-national-park-surat-thani-province-thailand_335224-851.jpg',
                                    'ttbm_travel_duration' => 1,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 1,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => 180,
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 5,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => 'Naple',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => '',
                                    //date_settings
                                    'ttbm_travel_type' => 'fixed',
                                    'ttbm_travel_start_date' => date('Y-m-d', strtotime(' +35 day')),
                                    'ttbm_travel_reg_end_date' => date('Y-m-d', strtotime(' +36 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 55,
                                            'sale_price' => 40,
                                            'ticket_type_qty' => 220,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 5,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 70,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'Breakfast',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'BBQ Night',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'City Tours',
                                        1 => 'Hiking',
                                        2 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => [120, 130, 140, 150, 160, 170, 180, 190, 200, 210, 220, 230, 240, 250, 260, 270, 280, 290, 300],
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'availability_section',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            2 => [
                                'name' => 'Deep-Sea Exploration on a Shampan',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'feature_image' => 'https://img.freepik.com/free-photo/photographer-taking-picture-ocean-coast_657883-287.jpg',
                                    'ttbm_travel_duration' => 1,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 1,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => '',
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 5,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => '',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => '',
                                    //date_settings
                                    'ttbm_travel_type' => 'repeated',
                                    'ttbm_travel_repeated_after' => '4',
                                    'ttbm_travel_repeated_start_date' => date('Y-m-d', strtotime(' +15 day')),
                                    'ttbm_travel_repeated_end_date' => date('Y-m-d', strtotime(' +365 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 55,
                                            'sale_price' => 40,
                                            'ticket_type_qty' => 220,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 5,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 70,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'Breakfast',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'BBQ Night',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'City Tours',
                                        1 => 'Hiking',
                                        2 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => [120, 130, 140, 150, 160, 170, 180, 190, 200, 210, 220, 230, 240, 250, 260, 270, 280, 290, 300],
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'regular_ticket',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            3 => [
                                'name' => 'Beach Hopping at Inani, Himchari, Patuartek',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'feature_image' => 'https://img.freepik.com/free-photo/pileh-blue-lagoon-phi-phi-island-thailand_231208-1487.jpg',
                                    'ttbm_travel_duration' => 2,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 1,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => '',
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 12,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => '',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => '',
                                    //date_settings
                                    'ttbm_travel_type' => 'repeated',
                                    'ttbm_travel_repeated_after' => '7',
                                    'ttbm_travel_repeated_start_date' => date('Y-m-d', strtotime(' +25 day')),
                                    'ttbm_travel_repeated_end_date' => date('Y-m-d', strtotime(' +365 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 105,
                                            'sale_price' => 100,
                                            'ticket_type_qty' => 200,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 2,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 90,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'BBQ Night',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'Breakfast',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'City Tours',
                                        1 => 'Hiking',
                                        2 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => [120, 130, 140, 150, 160, 170, 180, 190, 200, 210, 220, 230, 240, 250, 260, 270, 280, 290, 300],
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'regular_ticket',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                            4 => [
                                'name' => 'Boga Lake : A Relaxing Gateway Tour',
                                'content' => '

                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                    
                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur.
                                ',
                                'post_data' => [
                                    //General_settings
                                    'feature_image' => 'https://img.freepik.com/free-photo/godafoss-waterfall-sunset-winter-iceland-guy-red-jacket-looks-godafoss-waterfall_335224-673.jpg',
                                    'ttbm_travel_duration' => 4,
                                    'ttbm_travel_duration_type' => 'day',
                                    'ttbm_display_duration_night' => 'on',
                                    'ttbm_travel_duration_night' => 5,
                                    'ttbm_display_price_start' => 'on',
                                    'ttbm_travel_start_price' => '',
                                    'ttbm_display_max_people' => 'on',
                                    'ttbm_display_min_age' => 'on',
                                    'ttbm_travel_min_age' => 18,
                                    'ttbm_display_start_location' => 'on',
                                    'ttbm_travel_start_place' => '',
                                    'ttbm_display_location' => 'off',
                                    'ttbm_location_name' => '',
                                    'ttbm_display_map' => 'off',
                                    'ttbm_display_description' => 'on',
                                    'ttbm_short_description' => '',
                                    //date_settings
                                    'ttbm_travel_type' => 'repeated',
                                    'ttbm_travel_repeated_after' => '15',
                                    'ttbm_travel_repeated_start_date' => date('Y-m-d', strtotime(' +35 day')),
                                    'ttbm_travel_repeated_end_date' => date('Y-m-d', strtotime(' +365 day')),
                                    //price_settings
                                    'ttbm_display_registration' => 'on',
                                    'ttbm_display_advance' => 'off',
                                    'ttbm_type' => 'general',
                                    'ttbm_hotels' => array(),
                                    'ttbm_ticket_type' => [
                                        0 => [
                                            'ticket_type_icon' => 'fas fa-user-tie',
                                            'ticket_type_name' => 'Adult',
                                            'ticket_type_price' => 105,
                                            'sale_price' => 100,
                                            'ticket_type_qty' => 200,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 2,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ],
                                        1 => [
                                            'ticket_type_icon' => 'fas fa-snowman',
                                            'ticket_type_name' => 'Child',
                                            'ticket_type_price' => 100,
                                            'sale_price' => 90,
                                            'ticket_type_qty' => 100,
                                            'ticket_type_default_qty' => 0,
                                            'ticket_type_resv_qty' => 20,
                                            'ticket_type_qty_type' => 'inputbox',
                                            'ticket_type_description' => '',
                                        ]
                                    ],
                                    'ttbm_extra_service_data' => [
                                        0 => [
                                            'service_icon' => 'fas fa-graduation-cap',
                                            'service_name' => 'Cap',
                                            'service_price' => 6,
                                            'service_qty' => 500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                        1 => [
                                            'service_icon' => 'fas fa-coffee',
                                            'service_name' => 'Coffe',
                                            'service_price' => 4,
                                            'service_qty' => 1500,
                                            'service_qty_type' => 'inputbox',
                                            'extra_service_description' => '',
                                        ],
                                    ],
                                    'ttbm_display_include_service' => 'on',
                                    'ttbm_service_included_in_price' => [
                                        0 => 'Accommodation',
                                        1 => 'BBQ Night',
                                        2 => 'Welcome Drinks',
                                        3 => 'Lunch',
                                        4 => 'Transport',
                                    ],
                                    'ttbm_display_exclude_service' => 'on',
                                    'ttbm_service_excluded_in_price' => [
                                        0 => 'Airport Transfer',
                                        1 => 'Breakfast',
                                        2 => 'Guide',
                                        3 => 'Insurance',
                                        4 => 'Outing Ticket',
                                    ],
                                    //Place_you_see_settings
                                    //day wise details_settings
                                    //faq_settings
                                    'ttbm_display_faq' => 'on',
                                    'mep_event_faq' => [
                                        0 => [
                                            'ttbm_faq_title' => 'What can I expect to see at The Mentalist at Planet Hollywood Resort and Casino?',
                                            'ttbm_faq_content' => 'Comedy, magic and mind-reading! The Mentalist has the ability to get inside the minds of audience members, revealing everything from their names, hometowns and anniversaries to their wildest wishes.',
                                        ],
                                        1 => [
                                            'ttbm_faq_title' => 'Where is The Mentalist located?',
                                            'ttbm_faq_content' => 'The V Theater is located inside the Miracle Mile Shops at the Planet Hollywood Resort & Casino.',
                                        ],
                                        2 => [
                                            'ttbm_faq_title' => 'Can I purchase alcohol at the venue during The Mentalist!?',
                                            'ttbm_faq_content' => 'Absolutely! Drinks are available for purchase at the Showgirl Bar outside of the theater and may be brought into the showroom, however, no other outside food or drink will be allowed in the theater.',
                                        ],
                                        3 => [
                                            'ttbm_faq_title' => 'Is The Mentalist appropriate for children?',
                                            'ttbm_faq_content' => 'Due to language, this show is recommended for guests 16 years old and over.',
                                        ],
                                        4 => [
                                            'ttbm_faq_title' => 'Do I need to exchange my ticket upon arrival at The Mentalist!?',
                                            'ttbm_faq_content' => 'Please pick up your tickets at the V Theater Box Office with a valid photo ID for the lead traveler at least 30 minutes prior to show time (box office opens at 11 am). Seating will begin 15 minutes before showtime.',
                                        ],
                                    ],
                                    //why chose us_settings
                                    'ttbm_why_choose_us_texts' => [
                                        0 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        1 => 'Enjoy a taste of Las Vegas glitz at the mind-bending magic show',
                                        2 => 'Watch as Gerry McCambridge performs comedy and magic',
                                    ],
                                    //activities_settings
                                    'ttbm_display_activities' => 'on',
                                    'ttbm_tour_activities' => [
                                        0 => 'Hiking',
                                        1 => 'Rural',
                                    ],
                                    //gallery_settings
                                    'ttbm_gallery_images' => [120, 130, 140, 150, 160, 170, 180, 190, 200, 210, 220, 230, 240, 250, 260, 270, 280, 290, 300],
                                    //extras_settings
                                    'ttbm_display_get_question' => 'on',
                                    'ttbm_contact_email' => 'example.gmail.com',
                                    'ttbm_contact_phone' => '123456789',
                                    'ttbm_contact_text' => 'Do not hesitate to give us a call. We are an expert team and we are happy to talk to you.',
                                    'ttbm_display_tour_guide' => 'on',
                                    //Related tour_settings
                                    //Display_settings
                                    'ttbm_section_title_style' => 'ttbm_title_style_2',
                                    'ttbm_ticketing_system' => 'regular_ticket',
                                    'ttbm_display_seat_details' => 'on',
                                    'ttbm_display_sidebar' => 'off',
                                    'ttbm_display_tour_type' => 'on',
                                    'ttbm_display_hotels' => 'on',
                                    'ttbm_display_duration' => 'on',
                                ]
                            ],
                        ]
                    ]
                ];
            }
        }
    }